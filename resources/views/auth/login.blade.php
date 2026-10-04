{{-- Google is the way in for guests. The password form is kept, collapsed,
     because the host signs in that way and because it is the fallback if the
     Firebase project is ever misconfigured — without it a bad FIREBASE_PROJECT_ID
     would lock everybody out of their own site, admin included. --}}
<x-guest-layout>
    <div class="text-center">
        <h1 style="font-size:23px; margin-bottom:6px;">Sign in</h1>
        <p class="muted" style="font-size:14px;">
            To manage your bookings and message the host.
        </p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    {{-- Errors from the password form below, surfaced at the top where they are
         read. The password form stays open when it is the one that failed. --}}
    @if ($errors->any())
        <div class="alert alert-error mt-4" role="alert" style="font-size:13.5px;">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-5">
        <x-google-signin label="Continue with Google" />
    </div>

    <div class="my-5 flex items-center gap-3" aria-hidden="true">
        <span style="flex:1; height:1px; background:var(--ink-200);"></span>
        <span class="muted" style="font-size:12.5px;">or</span>
        <span style="flex:1; height:1px; background:var(--ink-200);"></span>
    </div>

    <div x-data="{ open: @js($errors->any()) }">
        <button type="button" class="btn btn-ghost btn-block" @click="open = !open"
                :aria-expanded="open ? 'true' : 'false'">
            <i class="fa-solid fa-key" style="font-size:13px;"></i>
            <span>Sign in with a password</span>
            <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size:11px;"></i>
        </button>

        <form method="POST" action="{{ route('login') }}" class="mt-4 stack" x-show="open" x-cloak x-transition>
            @csrf

            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       autocomplete="username"
                       class="input @error('email') input-error @enderror">
            </div>

            <div class="field">
                <label class="field-label" for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="input @error('password') input-error @enderror">
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2" style="font-size:13.5px; color:var(--ink-700);">
                    <input id="remember_me" type="checkbox" name="remember"
                           style="width:15px; height:15px; accent-color: var(--brand-600);">
                    Remember me
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="font-size:13px; color:var(--brand-700); text-decoration:none;">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign in</button>
        </form>
    </div>

    <p class="muted mt-6 text-center" style="font-size:12.5px; line-height:1.6;">
        New here? Signing in with Google creates your account automatically.
    </p>
</x-guest-layout>
