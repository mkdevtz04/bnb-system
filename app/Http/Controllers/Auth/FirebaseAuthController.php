<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\FirebaseAccountConflictException;
use App\Exceptions\InvalidFirebaseTokenException;
use App\Http\Controllers\Controller;
use App\Services\Firebase\FirebaseTokenVerifier;
use App\Services\Firebase\FirebaseUserResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Completes a Google sign-in that the browser started through Firebase.
 *
 * The browser runs the Google popup and posts the resulting ID token here. This
 * endpoint is public, so the token is treated as untrusted input until
 * FirebaseTokenVerifier has checked its signature and every claim.
 */
class FirebaseAuthController extends Controller
{
    public function __construct(
        private readonly FirebaseTokenVerifier $verifier,
        private readonly FirebaseUserResolver $resolver,
    ) {}

    public function callback(Request $request): JsonResponse
    {
        if (! config('firebase.project_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in is not configured on this site yet.',
            ], 503);
        }

        $request->validate([
            // A JWT, so the length bound is generous but not unbounded.
            'id_token' => ['required', 'string', 'min:32', 'max:4096'],
        ]);

        // Verification is a signature check against cached certificates, which is
        // cheap — but it is still an unauthenticated endpoint, so a single host
        // cannot sit here hammering it.
        $limiterKey = 'firebase-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 20)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many sign-in attempts. Please wait a minute and try again.',
            ], 429);
        }

        RateLimiter::hit($limiterKey, 300);

        try {
            $identity = $this->verifier->verify($request->string('id_token')->toString());
            $user = $this->resolver->resolve($identity);
        } catch (InvalidFirebaseTokenException|FirebaseAccountConflictException $e) {
            // The log gets the precise technical reason; the response below gets
            // the vague one. Logging only the public message, as this used to,
            // makes a real misconfiguration undiagnosable from the log.
            //
            // The token itself is never logged: it is a bearer credential, and a
            // log file is not the place for one.
            Log::warning('Firebase sign-in rejected', [
                'reason' => $e instanceof InvalidFirebaseTokenException ? $e->reason() : $e->getMessage(),
                'ip' => $request->ip(),
                ...($e instanceof InvalidFirebaseTokenException ? $e->context() : []),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        RateLimiter::clear($limiterKey);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => $user->isAdmin() ? route('admin.dashboard') : route('dashboard'),
        ]);
    }
}
