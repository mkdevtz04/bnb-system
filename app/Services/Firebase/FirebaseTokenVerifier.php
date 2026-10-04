<?php

namespace App\Services\Firebase;

use App\Exceptions\InvalidFirebaseTokenException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifies a Firebase ID token.
 *
 * The browser does the Google popup and hands us a token. That token arrives
 * from the client, so until every check below passes it is just a string a
 * stranger posted to us — never a identity. Anyone can POST to the callback, so
 * skipping any one of these checks would let them sign in as anybody.
 *
 * The checks are the ones Google documents for verifying ID tokens:
 *
 *   alg        must be RS256, so a token cannot be re-signed with "none" or with
 *              a symmetric algorithm using a public key as the shared secret
 *   kid        must match one of Google's currently published certificates
 *   signature  must verify against that certificate
 *   aud        must be exactly our project id, or a token minted for a different
 *              Firebase project would be accepted here
 *   iss        must be https://securetoken.google.com/<project id>
 *   exp / iat  must place the token in its validity window
 *   sub        must be a non-empty Firebase uid of at most 128 characters
 *   auth_time  must not be in the future
 *
 * No service account key is involved: verification uses Google's public
 * certificates, so this application holds no Firebase secret at all.
 */
class FirebaseTokenVerifier
{
    public function __construct(private readonly ?string $projectId = null) {}

    /**
     * @throws InvalidFirebaseTokenException
     */
    public function verify(string $idToken): FirebaseIdentity
    {
        $projectId = $this->projectId ?: config('firebase.project_id');

        if (blank($projectId)) {
            throw new InvalidFirebaseTokenException(
                'Firebase is not configured on this server.',
                'FIREBASE_PROJECT_ID is empty.',
            );
        }

        JWT::$leeway = (int) config('firebase.leeway', 60);

        try {
            // JWT::decode enforces alg, kid, signature and exp/iat for us. It
            // throws rather than returning a partial result, so there is no
            // "unverified but parsed" object that could leak into the app.
            $payload = $this->decode($idToken);

            // Decoded claims come back as nested stdClass objects. A plain
            // (array) cast only converts the top level, which leaves claims like
            // firebase.sign_in_provider as an object and makes array access on
            // them fatal. Round-tripping through JSON converts the whole tree.
            $claims = json_decode(json_encode($payload), true);
        } catch (ExpiredException) {
            throw new InvalidFirebaseTokenException(
                'That sign-in has expired. Please try again.',
                'Token exp is in the past. Check this server’s clock against real time.',
                $this->peek($idToken),
            );
        } catch (SignatureInvalidException) {
            throw new InvalidFirebaseTokenException(
                'That sign-in could not be verified.',
                'Signature did not match any of Google’s current certificates.',
                $this->peek($idToken),
            );
        } catch (InvalidFirebaseTokenException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new InvalidFirebaseTokenException(
                'That sign-in could not be verified.',
                get_class($e).': '.$e->getMessage(),
                $this->peek($idToken),
            );
        }

        $this->assertAudience($claims, $projectId);
        $this->assertIssuer($claims, $projectId);
        $this->assertSubject($claims);
        $this->assertAuthTime($claims);

        return FirebaseIdentity::fromClaims($claims);
    }

    /**
     * @throws InvalidFirebaseTokenException
     */
    private function assertAudience(array $claims, string $projectId): void
    {
        $aud = $claims['aud'] ?? null;

        if ($aud !== $projectId) {
            throw new InvalidFirebaseTokenException(
                'That sign-in was issued for a different application.',
                "Token aud is '{$aud}' but FIREBASE_PROJECT_ID is '{$projectId}'. "
                    .'The browser and this server are pointed at different Firebase projects.',
            );
        }
    }

    /**
     * @throws InvalidFirebaseTokenException
     */
    private function assertIssuer(array $claims, string $projectId): void
    {
        $iss = $claims['iss'] ?? null;

        if ($iss !== "https://securetoken.google.com/{$projectId}") {
            throw new InvalidFirebaseTokenException(
                'That sign-in came from an unexpected issuer.',
                "Token iss is '{$iss}', expected 'https://securetoken.google.com/{$projectId}'.",
            );
        }
    }

    /**
     * @throws InvalidFirebaseTokenException
     */
    private function assertSubject(array $claims): void
    {
        $sub = $claims['sub'] ?? null;

        if (! is_string($sub) || $sub === '' || mb_strlen($sub) > 128) {
            throw new InvalidFirebaseTokenException(
                'That sign-in is missing a valid account id.',
                'Token sub is empty or longer than 128 characters.',
            );
        }
    }

    /**
     * @throws InvalidFirebaseTokenException
     */
    private function assertAuthTime(array $claims): void
    {
        $authTime = $claims['auth_time'] ?? null;

        if ($authTime !== null && $authTime > time() + (int) config('firebase.leeway', 60)) {
            throw new InvalidFirebaseTokenException(
                'That sign-in is dated in the future.',
                'Token auth_time is ahead of this server’s clock by more than the allowed leeway.',
            );
        }
    }

    /**
     * Read a token's header and claims WITHOUT verifying it, purely so a failure
     * can be explained in the log.
     *
     * Nothing from here is ever trusted or returned to the caller — by the time
     * this runs, verification has already failed. The signature segment is
     * dropped, so a log line cannot be reassembled into a usable credential.
     *
     * @return array<string, mixed>
     */
    private function peek(string $idToken): array
    {
        $segments = explode('.', $idToken);

        if (count($segments) < 2) {
            return ['token_shape' => 'not a JWT (expected three dot-separated segments)'];
        }

        $decode = function (string $segment): array {
            $json = base64_decode(strtr($segment, '-_', '+/'), true);

            return is_string($json) ? (json_decode($json, true) ?: []) : [];
        };

        $header = $decode($segments[0]);
        $claims = $decode($segments[1]);

        return array_filter([
            'alg' => $header['alg'] ?? null,
            'kid' => $header['kid'] ?? null,
            'aud' => $claims['aud'] ?? null,
            'iss' => $claims['iss'] ?? null,
            'exp' => isset($claims['exp']) ? date('c', (int) $claims['exp']) : null,
            'server_time' => date('c'),
        ], fn ($value) => $value !== null);
    }

    /**
     * Decode and verify, re-fetching Google's certificates once if the cached
     * set cannot account for the token.
     *
     * Google rotates its signing certificates every few hours. A token signed
     * with a freshly rotated key will not match anything in a cache populated
     * before the rotation, and JWT::decode rejects it for an unknown "kid". With
     * a single attempt that becomes a real sign-in outage: every login fails
     * until the cache happens to expire, with nothing obviously wrong anywhere.
     *
     * So a failure against cached certificates is retried once against freshly
     * fetched ones. The retry only happens when the first attempt used the cache,
     * which stops a genuinely bad token from causing two requests to Google.
     */
    private function decode(string $idToken): object
    {
        $cached = Cache::get(config('firebase.certificates_cache_key'));
        $usedCache = is_array($cached) && $cached !== [];

        try {
            return JWT::decode($idToken, $this->publicKeys());
        } catch (ExpiredException|SignatureInvalidException $e) {
            // These say the token itself is wrong, not that our keys are stale.
            throw $e;
        } catch (Throwable $e) {
            if (! $usedCache) {
                throw $e;
            }

            Cache::forget(config('firebase.certificates_cache_key'));

            return JWT::decode($idToken, $this->publicKeys());
        }
    }

    /**
     * Google's current signing certificates, keyed by kid.
     *
     * Cached for exactly as long as Google says they are good for, rather than
     * being pinned or re-fetched on every request.
     *
     * @return array<string, Key>
     *
     * @throws InvalidFirebaseTokenException
     */
    private function publicKeys(): array
    {
        $certificates = Cache::get(config('firebase.certificates_cache_key'));

        if (! is_array($certificates) || $certificates === []) {
            $certificates = $this->fetchCertificates();
        }

        $keys = [];

        foreach ($certificates as $kid => $certificate) {
            // openssl_pkey_get_public() accepts an X.509 certificate and returns
            // the public key inside it.
            $publicKey = openssl_pkey_get_public($certificate);

            if ($publicKey !== false) {
                $keys[(string) $kid] = new Key($publicKey, 'RS256');
            }
        }

        if ($keys === []) {
            throw new InvalidFirebaseTokenException('Could not load Google’s signing certificates.');
        }

        return $keys;
    }

    /**
     * @return array<string, string>
     *
     * @throws InvalidFirebaseTokenException
     */
    private function fetchCertificates(): array
    {
        try {
            $response = Http::timeout(8)->retry(2, 200)->get(config('firebase.certificates_url'));
        } catch (Throwable) {
            throw new InvalidFirebaseTokenException('Could not reach Google to verify that sign-in.');
        }

        if (! $response->successful()) {
            throw new InvalidFirebaseTokenException('Could not reach Google to verify that sign-in.');
        }

        $certificates = $response->json();

        if (! is_array($certificates) || $certificates === []) {
            throw new InvalidFirebaseTokenException('Google returned no signing certificates.');
        }

        Cache::put(
            config('firebase.certificates_cache_key'),
            $certificates,
            $this->cacheTtl($response->header('Cache-Control')),
        );

        return $certificates;
    }

    /**
     * Honour Google's own max-age so we stop trusting a certificate at the same
     * moment they do.
     */
    private function cacheTtl(?string $cacheControl): int
    {
        $floor = (int) config('firebase.certificates_min_ttl', 300);

        if ($cacheControl && preg_match('/max-age=(\d+)/i', $cacheControl, $matches)) {
            return max($floor, (int) $matches[1]);
        }

        return $floor;
    }
}
