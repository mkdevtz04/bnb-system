<?php

namespace App\Console\Commands;

use App\Exceptions\InvalidFirebaseTokenException;
use App\Services\Firebase\FirebaseTokenVerifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Checks that Google sign-in is wired up, and explains a failing token.
 *
 * Run it with no arguments to confirm configuration and that Google's
 * certificates are reachable. Paste a token to find out exactly why it was
 * rejected — the browser console logs the token on a failed sign-in attempt.
 */
class FirebaseCheck extends Command
{
    protected $signature = 'firebase:check {token? : A Firebase ID token to verify}';

    protected $description = 'Diagnose Google sign-in configuration and token verification';

    public function handle(FirebaseTokenVerifier $verifier): int
    {
        $this->components->info('Firebase configuration');

        $projectId = config('firebase.project_id');
        $apiKey = config('firebase.api_key');
        $authDomain = config('firebase.auth_domain');

        $this->line('  FIREBASE_PROJECT_ID   '.$this->shown($projectId));
        $this->line('  FIREBASE_API_KEY      '.$this->masked($apiKey));
        $this->line('  FIREBASE_AUTH_DOMAIN  '.$this->shown($authDomain));
        $this->newLine();

        if (blank($projectId) || blank($apiKey) || blank($authDomain)) {
            $this->components->error('Incomplete. Google sign-in will not appear until all three are set.');

            return self::FAILURE;
        }

        // A mismatch here is a classic cause of an "aud" rejection: the browser
        // signs in against one project while the server validates against another.
        $expectedDomain = "{$projectId}.firebaseapp.com";
        if ($authDomain !== $expectedDomain) {
            $this->components->warn(
                "FIREBASE_AUTH_DOMAIN is '{$authDomain}' but the project id suggests '{$expectedDomain}'. "
                .'That is fine for a custom domain, but check they belong to the same project.'
            );
        }

        $this->components->info('Google signing certificates');

        try {
            // no-store plus a cache-buster, because the Date header is also used
            // below as a reference clock. A proxy serving a cached response would
            // report a stale time and look like clock drift.
            $response = Http::timeout(8)
                ->withHeaders(['Cache-Control' => 'no-cache, no-store'])
                ->get(config('firebase.certificates_url'), ['_' => microtime(true)]);

            if (! $response->successful()) {
                $this->components->error('Google returned HTTP '.$response->status().'.');

                return self::FAILURE;
            }

            $certificates = $response->json();
            $this->line('  reachable, '.count($certificates).' certificate(s): '
                .implode(', ', array_map(fn ($kid) => substr($kid, 0, 8).'…', array_keys($certificates))));

            $googleDate = $response->header('Date');
        } catch (\Throwable $e) {
            $this->components->error('Could not reach Google: '.$e->getMessage());

            return self::FAILURE;
        }

        $cached = Cache::get(config('firebase.certificates_cache_key'));
        $this->line('  cached locally: '.(is_array($cached) ? count($cached).' certificate(s)' : 'no'));
        $this->newLine();

        $this->checkBrowserReachableHosts($authDomain);

        // A machine whose clock is wrong rejects every token as expired or
        // not-yet-valid, which looks like a broken integration rather than a
        // broken clock. Google's Date header is a free reference time.
        $this->components->info('Server clock');
        $this->line('  this server  '.date('c'));

        if ($googleDate && ($googleTime = strtotime($googleDate))) {
            $drift = time() - $googleTime;
            $this->line('  Google       '.date('c', $googleTime));

            $leeway = (int) config('firebase.leeway', 60);

            if (abs($drift) > $leeway) {
                $this->components->error(
                    "Clock is out by {$drift}s, beyond the {$leeway}s tolerance. "
                    .'Every token will be rejected until this machine’s time is corrected.'
                );
            } else {
                $this->line('  drift        '.$drift.'s (within the '.$leeway.'s tolerance)');
            }
        }

        $this->newLine();

        $token = $this->argument('token');

        if (! $token) {
            $this->components->info('Configuration looks usable.');
            $this->line('  Pass a token to check verification:  php artisan firebase:check "eyJhbGci…"');
            $this->newLine();
            $this->line('  Remember Firebase authorises <options=bold>localhost</> but not <options=bold>127.0.0.1</>.');
            $this->line('  Add 127.0.0.1 under Authentication → Settings → Authorised domains, or browse to localhost.');

            return self::SUCCESS;
        }

        $this->components->info('Verifying token');

        try {
            $identity = $verifier->verify($token);
        } catch (InvalidFirebaseTokenException $e) {
            $this->components->error($e->reason());

            foreach ($e->context() as $key => $value) {
                $this->line(sprintf('  %-12s %s', $key, is_scalar($value) ? $value : json_encode($value)));
            }

            return self::FAILURE;
        }

        $this->components->info('Token is valid.');
        $this->line('  uid       '.$identity->uid);
        $this->line('  email     '.($identity->email ?? '—').($identity->emailVerified ? ' (verified)' : ' (NOT verified)'));
        $this->line('  name      '.($identity->name ?? '—'));
        $this->line('  provider  '.($identity->provider ?? '—'));

        return self::SUCCESS;
    }

    /**
     * Check the hosts the *browser* needs, not just the ones this server uses.
     *
     * A perfectly configured project still cannot sign anyone in if the network
     * blocks the domain the popup loads from. That is invisible from the server
     * side, because the server never contacts it — so it gets checked explicitly.
     */
    private function checkBrowserReachableHosts(string $authDomain): void
    {
        $this->components->info('Hosts the browser needs');

        $hosts = [
            $authDomain => 'signInWithPopup loads its handler from here',
            'accounts.google.com' => 'Google Identity Services',
            'identitytoolkit.googleapis.com' => 'token exchange',
            'securetoken.googleapis.com' => 'token refresh',
        ];

        $popupHostBlocked = false;

        foreach ($hosts as $host => $purpose) {
            try {
                $response = Http::timeout(10)->connectTimeout(5)->get("https://{$host}/");
                // Any HTTP answer means reachable; 404 from an API root is fine.
                $this->line(sprintf('  <fg=green>ok</>       %-34s %s', $host, $purpose));
                unset($response);
            } catch (\Throwable) {
                $this->line(sprintf('  <fg=red>BLOCKED</>  %-34s %s', $host, $purpose));

                if ($host === $authDomain) {
                    $popupHostBlocked = true;
                }
            }
        }

        $this->newLine();

        if ($popupHostBlocked) {
            $this->components->warn(
                "This network cannot reach {$authDomain}, which is where Firebase's popup "
                .'loads its handler. Google sign-in will fail here until FIREBASE_GOOGLE_CLIENT_ID '
                .'is set, which switches to Google Identity Services and avoids that domain entirely.'
            );

            if (blank(config('firebase.google_client_id'))) {
                $this->line('  Google Cloud console → APIs & Services → Credentials →');
                $this->line('  OAuth 2.0 Client IDs → "Web client (auto created by Google Service)".');
                $this->newLine();
            }
        }
    }

    private function shown(?string $value): string
    {
        return blank($value) ? '<fg=red>(empty)</>' : $value;
    }

    private function masked(?string $value): string
    {
        return blank($value) ? '<fg=red>(empty)</>' : substr($value, 0, 8).'…'.substr($value, -4);
    }
}
