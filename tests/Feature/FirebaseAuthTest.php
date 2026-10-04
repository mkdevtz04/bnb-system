<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Google sign-in.
 *
 * These tests mint real RS256 tokens with a throwaway key pair and serve that
 * key pair's certificate as if it were Google's, so the verifier runs its actual
 * signature check rather than a mocked one. That is the only way to be sure the
 * checks are wired up: a mocked verifier would pass whatever it was told to.
 */
class FirebaseAuthTest extends TestCase
{
    use RefreshDatabase;

    private const PROJECT = 'coastalcharmz-test';

    private const KID = 'test-key-1';

    private string $privateKey;

    private string $certificate;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('firebase.project_id', self::PROJECT);
        config()->set('firebase.api_key', 'test-api-key');
        config()->set('firebase.auth_domain', self::PROJECT.'.firebaseapp.com');

        [$this->privateKey, $this->certificate] = $this->makeKeyPair();

        // Stand in for Google's certificate endpoint.
        Cache::put(config('firebase.certificates_cache_key'), [self::KID => $this->certificate], 3600);
    }

    /**
     * A self-signed X.509 certificate plus its private key.
     *
     * Deliberately a real certificate rather than a bare public key, because that
     * is the shape Google actually serves and therefore the shape the verifier has
     * to parse. Generating one also exercises openssl_pkey_get_public() against a
     * certificate, which is the production code path.
     *
     * The explicit config is not optional: PHP's OpenSSL functions need an
     * openssl.cnf, and plenty of installations (this one included) ship without
     * one, which makes openssl_pkey_new() fail outright. Writing a minimal config
     * keeps these tests runnable anywhere instead of only on machines that happen
     * to have OpenSSL fully set up.
     *
     * @return array{0: string, 1: string}
     */
    private function makeKeyPair(): array
    {
        $config = ['digest_alg' => 'sha256', 'config' => $this->opensslConfigPath()];

        $key = openssl_pkey_new($config + [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $this->assertNotFalse($key, 'Could not generate a test key pair.');

        $csr = openssl_csr_new(['commonName' => 'securetoken.test'], $key, $config);
        $x509 = openssl_csr_sign($csr, null, $key, 1, $config);

        openssl_x509_export($x509, $certificate);
        openssl_pkey_export($key, $privateKey, null, $config);

        return [$privateKey, $certificate];
    }

    private function opensslConfigPath(): string
    {
        $path = sys_get_temp_dir().'/coastalcharmz-test-openssl.cnf';

        if (! is_file($path)) {
            file_put_contents($path, "[req]\ndistinguished_name = dn\n[dn]\n");
        }

        return $path;
    }

    private function token(array $overrides = [], ?string $signingKey = null): string
    {
        $now = time();

        $claims = array_merge([
            'iss' => 'https://securetoken.google.com/'.self::PROJECT,
            'aud' => self::PROJECT,
            'sub' => 'firebase-uid-abc123',
            'iat' => $now - 10,
            'exp' => $now + 3600,
            'auth_time' => $now - 10,
            'email' => 'traveller@example.com',
            'email_verified' => true,
            'name' => 'Jo Traveller',
            'picture' => 'https://lh3.googleusercontent.com/a/photo',
            'firebase' => ['sign_in_provider' => 'google.com'],
        ], $overrides);

        return JWT::encode($claims, $signingKey ?? $this->privateKey, 'RS256', self::KID);
    }

    private function attempt(string $token)
    {
        return $this->postJson(route('firebase.callback'), ['id_token' => $token]);
    }

    // ── Happy path ──────────────────────────────────────────────────────────

    public function test_a_valid_token_signs_in_and_creates_a_guest(): void
    {
        $this->attempt($this->token())
            ->assertOk()
            ->assertJson(['success' => true, 'redirect' => route('dashboard')]);

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'firebase_uid' => 'firebase-uid-abc123',
            'email' => 'traveller@example.com',
            'name' => 'Jo Traveller',
            'role' => 'user',
        ]);
    }

    public function test_signing_in_twice_reuses_the_same_account(): void
    {
        $this->attempt($this->token())->assertOk();
        auth()->logout();
        $this->attempt($this->token())->assertOk();

        $this->assertSame(1, User::where('firebase_uid', 'firebase-uid-abc123')->count());
    }

    /**
     * The uid is the key, not the email — a Google account can change address.
     */
    public function test_a_changed_email_still_resolves_to_the_same_account(): void
    {
        $this->attempt($this->token())->assertOk();
        auth()->logout();

        $this->attempt($this->token(['email' => 'new-address@example.com']))->assertOk();

        $this->assertSame(1, User::count());
    }

    public function test_an_existing_admin_keeps_their_role_and_lands_in_admin(): void
    {
        User::factory()->create(['email' => 'traveller@example.com', 'role' => 'admin']);

        $this->attempt($this->token())
            ->assertOk()
            ->assertJson(['redirect' => route('admin.dashboard')]);

        $this->assertDatabaseHas('users', [
            'email' => 'traveller@example.com',
            'role' => 'admin',
            'firebase_uid' => 'firebase-uid-abc123',
        ]);
    }

    /**
     * A token cannot promote its holder. Even if Firebase custom claims carried a
     * role, this application decides roles from its own table.
     */
    public function test_a_token_cannot_grant_itself_the_admin_role(): void
    {
        $this->attempt($this->token(['role' => 'admin', 'admin' => true]))
            ->assertOk()
            ->assertJson(['redirect' => route('dashboard')]);

        $this->assertSame('user', User::firstOrFail()->role);
    }

    // ── Rejections ──────────────────────────────────────────────────────────

    public function test_a_token_signed_by_someone_else_is_rejected(): void
    {
        [$attackerKey] = $this->makeKeyPair();

        $this->attempt($this->token(signingKey: $attackerKey))->assertStatus(422);

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_an_unsigned_token_is_rejected(): void
    {
        // The "alg: none" classic. If the verifier trusted the header's algorithm
        // instead of pinning RS256, this would sign anyone in as anyone.
        $claims = json_encode(['iss' => 'https://securetoken.google.com/'.self::PROJECT, 'aud' => self::PROJECT, 'sub' => 'x', 'exp' => time() + 3600]);
        $b64 = fn (string $v) => rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
        $forged = $b64(json_encode(['alg' => 'none', 'typ' => 'JWT'])).'.'.$b64($claims).'.';

        $this->attempt($forged)->assertStatus(422);
        $this->assertGuest();
    }

    public function test_a_token_for_a_different_firebase_project_is_rejected(): void
    {
        $this->attempt($this->token(['aud' => 'somebody-elses-project']))->assertStatus(422);

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_a_token_from_an_unexpected_issuer_is_rejected(): void
    {
        $this->attempt($this->token(['iss' => 'https://evil.example.com/'.self::PROJECT]))->assertStatus(422);

        $this->assertGuest();
    }

    public function test_an_expired_token_is_rejected(): void
    {
        $this->attempt($this->token(['iat' => time() - 7200, 'exp' => time() - 3600]))->assertStatus(422);

        $this->assertGuest();
    }

    public function test_a_token_with_no_subject_is_rejected(): void
    {
        $this->attempt($this->token(['sub' => '']))->assertStatus(422);

        $this->assertGuest();
    }

    public function test_garbage_is_rejected(): void
    {
        $this->attempt(str_repeat('a', 64))->assertStatus(422);

        $this->assertGuest();
    }

    /**
     * The account-takeover case. An account already holds the address, and the
     * provider has not verified that the person signing in owns it, so the
     * accounts must not be linked.
     */
    public function test_an_unverified_email_cannot_claim_an_existing_account(): void
    {
        User::factory()->create(['email' => 'traveller@example.com', 'role' => 'admin']);

        $this->attempt($this->token(['email_verified' => false]))->assertStatus(422);

        $this->assertGuest();
        $this->assertNull(User::firstOrFail()->firebase_uid);
    }

    public function test_an_unverified_email_may_still_create_a_brand_new_account(): void
    {
        $this->attempt($this->token(['email_verified' => false]))->assertOk();

        $user = User::firstOrFail();
        $this->assertSame('firebase-uid-abc123', $user->firebase_uid);
        // Not marked verified, because nobody has verified it.
        $this->assertNull($user->email_verified_at);
    }

    public function test_a_token_without_an_email_is_rejected(): void
    {
        $this->attempt($this->token(['email' => null]))->assertStatus(422);

        $this->assertGuest();
    }

    // ── Configuration and limits ────────────────────────────────────────────

    public function test_the_endpoint_reports_unavailable_when_firebase_is_not_configured(): void
    {
        config()->set('firebase.project_id', null);

        $this->attempt($this->token())->assertStatus(503);

        $this->assertGuest();
    }

    public function test_the_endpoint_is_rate_limited(): void
    {
        [$attackerKey] = $this->makeKeyPair();
        $forged = $this->token(signingKey: $attackerKey);

        for ($i = 0; $i < 20; $i++) {
            $this->attempt($forged)->assertStatus(422);
        }

        $this->attempt($forged)->assertStatus(429);
    }

    public function test_a_missing_token_fails_validation(): void
    {
        $this->postJson(route('firebase.callback'), [])->assertStatus(422);
    }

    /**
     * Google rotates its signing certificates every few hours. When a token
     * arrives signed by a key the cache has never seen, the cache must be
     * refreshed rather than the token rejected — otherwise every sign-in fails
     * until the cache happens to expire, which looks like a broken integration.
     */
    public function test_a_rotated_signing_key_refreshes_the_certificate_cache(): void
    {
        // The cache holds only the old certificate.
        Cache::put(config('firebase.certificates_cache_key'), ['old-kid' => $this->certificate], 3600);

        // Google has rotated, and now serves the one our token is signed with.
        Http::fake([
            config('firebase.certificates_url') => Http::response(
                [self::KID => $this->certificate],
                200,
                ['Cache-Control' => 'public, max-age=19000'],
            ),
        ]);

        $this->attempt($this->token())->assertOk();

        $this->assertAuthenticated();
        $this->assertSame(
            [self::KID => $this->certificate],
            Cache::get(config('firebase.certificates_cache_key')),
        );
    }

    /**
     * The retry above must not turn every bad token into a second call to Google.
     */
    public function test_a_forged_token_does_not_trigger_a_certificate_refetch(): void
    {
        [$attackerKey] = $this->makeKeyPair();

        Http::fake([
            config('firebase.certificates_url') => Http::response([self::KID => $this->certificate], 200),
        ]);

        $this->attempt($this->token(signingKey: $attackerKey))->assertStatus(422);

        Http::assertNothingSent();
        $this->assertGuest();
    }

    /**
     * Certificates are fetched from Google when the cache is cold, and cached for
     * as long as Google's own Cache-Control says.
     */
    public function test_certificates_are_fetched_and_cached_when_absent(): void
    {
        Cache::forget(config('firebase.certificates_cache_key'));

        Http::fake([
            config('firebase.certificates_url') => Http::response(
                [self::KID => $this->certificate],
                200,
                ['Cache-Control' => 'public, max-age=19000'],
            ),
        ]);

        $this->attempt($this->token())->assertOk();

        $this->assertSame(
            [self::KID => $this->certificate],
            Cache::get(config('firebase.certificates_cache_key')),
        );
    }
}
