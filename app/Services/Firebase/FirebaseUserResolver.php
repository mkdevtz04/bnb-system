<?php

namespace App\Services\Firebase;

use App\Exceptions\FirebaseAccountConflictException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns a verified Firebase identity into a local user.
 *
 * The delicate part is matching an incoming Google identity to an account that
 * already exists here. Matching on email is the obvious approach and also the
 * dangerous one: if an unverified email were accepted, someone could create a
 * Firebase account claiming admin@admin.com and be handed that account. So an
 * existing account is only ever linked when the provider asserts the address is
 * verified. Google always verifies the addresses it returns; the check is here
 * because this class must stay correct if another provider is enabled later.
 *
 * Two further rules:
 *
 *  - `role` is never read from the token and never changed here. A new Firebase
 *    sign-in is always a guest. An existing admin signing in with Google keeps
 *    the role their row already has.
 *  - the local `password` is left untouched, so an admin who also has a password
 *    can still use it.
 */
class FirebaseUserResolver
{
    /**
     * @throws FirebaseAccountConflictException
     */
    public function resolve(FirebaseIdentity $identity): User
    {
        return DB::transaction(function () use ($identity) {
            // 1. Seen this Firebase account before — the uid is the stable key,
            //    because a Google account's email address can change.
            $user = User::where('firebase_uid', $identity->uid)->first();

            if ($user) {
                $this->refreshProfile($user, $identity);

                return $user;
            }

            if (blank($identity->email)) {
                throw new FirebaseAccountConflictException(
                    'Your Google account did not share an email address, so we cannot sign you in.'
                );
            }

            $existing = User::where('email', $identity->email)->first();

            // 2. An account with this address already exists. Claiming it
            //    requires the provider to vouch for the address.
            if ($existing) {
                if (! $identity->emailVerified) {
                    throw new FirebaseAccountConflictException(
                        'An account already uses that email address. Please sign in with your password instead.'
                    );
                }

                $existing->forceFill(['firebase_uid' => $identity->uid]);
                $this->refreshProfile($existing, $identity);

                return $existing;
            }

            // 3. Brand new guest.
            $user = new User([
                'email' => $identity->email,
                'name' => $identity->displayName(),
                'avatar_url' => $identity->picture,
                // Unusable local password: this account signs in through Google.
                // It is a random value rather than null so nothing can ever
                // authenticate against an empty or predictable hash.
                'password' => bcrypt(Str::random(64)),
                'role' => 'user',
            ]);

            // firebase_uid and email_verified_at are intentionally not mass
            // assignable, so they are set explicitly. Passing them to create()
            // would have them silently dropped — which is exactly what guarding
            // them is for, and exactly why this has to be forceFill.
            $user->forceFill([
                'firebase_uid' => $identity->uid,
                'email_verified_at' => $identity->emailVerified ? now() : null,
            ])->save();

            return $user;
        });
    }

    /**
     * Keep the parts of the profile Google owns up to date, without trampling a
     * name the guest has since set themselves.
     */
    private function refreshProfile(User $user, FirebaseIdentity $identity): void
    {
        $changes = [];

        if (filled($identity->picture) && $user->avatar_url !== $identity->picture) {
            $changes['avatar_url'] = $identity->picture;
        }

        if ($identity->emailVerified && $user->email_verified_at === null) {
            $changes['email_verified_at'] = now();
        }

        // Only fill a name that was never really set — an OTP-era placeholder or
        // an empty column. A name the guest edited in their profile wins.
        if (blank($user->name) || str_starts_with($user->name, 'Guest User - ')) {
            $changes['name'] = $identity->displayName();
        }

        if ($changes !== []) {
            $user->forceFill($changes);
        }

        if ($user->isDirty()) {
            $user->save();
        }
    }
}
