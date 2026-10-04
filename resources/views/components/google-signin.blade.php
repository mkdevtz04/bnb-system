@props([
    'label' => 'Continue with Google',
])

@php
    // Public identifiers, not secrets — they appear in any Firebase web app's
    // source. Passed through Blade rather than VITE_ variables so the project can
    // be pointed somewhere else by editing .env, with no rebuild.
    $firebase = [
        'apiKey' => config('firebase.api_key'),
        'authDomain' => config('firebase.auth_domain'),
        'projectId' => config('firebase.project_id'),
        'clientId' => config('firebase.google_client_id'),
        'callbackUrl' => route('firebase.callback'),
    ];

    $configured = filled($firebase['apiKey']) && filled($firebase['authDomain']) && filled($firebase['projectId']);

    // With a Google client id we can use Google Identity Services, which never
    // touches the Firebase Hosting domain that some networks reset. Without it we
    // fall back to Firebase's popup. See config/firebase.php.
    $usesIdentityServices = $configured && filled($firebase['clientId']);
@endphp

@if (! $configured)
    {{-- Fail visibly but harmlessly. Password sign-in still works, so the page is
         not broken — just missing one option. --}}
    <div class="alert alert-warning" role="status">
        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
        <div>
            <strong>Google sign-in isn't set up yet.</strong>
            <div style="font-size:13px; margin-top:2px;">
                Add <code>FIREBASE_API_KEY</code>, <code>FIREBASE_AUTH_DOMAIN</code> and
                <code>FIREBASE_PROJECT_ID</code> to <code>.env</code> to switch it on.
            </div>
        </div>
    </div>
@else
    {{-- No x-init here: Alpine calls a data object's init() automatically, so
         adding x-init="init()" would run it twice and render two Google buttons. --}}
    <div x-data="googleSignIn(@js($firebase), @js($usesIdentityServices))" class="w-full">

        @if ($usesIdentityServices)
            {{-- Google renders its own button here. Keeping a placeholder the same
                 height stops the form jumping while the library loads. --}}
            <div x-ref="gis" class="flex justify-center" style="min-height:44px;"></div>

            <div x-show="loading" x-cloak class="btn btn-block"
                 style="background:var(--surface); color:var(--ink-500); border-color:var(--ink-200);
                        padding:12px 18px; margin-top:-44px; pointer-events:none;">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
                <span>Loading Google sign-in…</span>
            </div>
        @else
            <button type="button" class="btn btn-block" @click="startPopup()" :disabled="busy"
                    style="background:var(--surface); color:var(--ink-800); border-color:var(--ink-300);
                           padding:12px 18px; font-size:15.5px;">
                <template x-if="!busy">
                    {{-- Google's mark, inline so it needs no network request. --}}
                    <svg width="19" height="19" viewBox="0 0 48 48" aria-hidden="true" style="flex-shrink:0;">
                        <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.9 2.4 30.3 0 24 0 14.6 0 6.4 5.4 2.6 13.2l7.9 6.2C12.3 13.6 17.6 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.1 24.5c0-1.6-.1-2.8-.4-4.1H24v8.4h12.6c-.3 2.1-1.6 5.2-4.6 7.3l7.7 6c4.5-4.2 6.4-10.1 6.4-17.6z"/>
                        <path fill="#FBBC05" d="M10.5 28.6c-.5-1.5-.8-3-.8-4.6s.3-3.1.8-4.6l-7.9-6.2C1 16.4 0 20.1 0 24s1 7.6 2.6 10.8l7.9-6.2z"/>
                        <path fill="#34A853" d="M24 48c6.3 0 11.6-2.1 15.5-5.7l-7.7-6c-2.1 1.4-4.8 2.4-7.8 2.4-6.4 0-11.7-4.1-13.5-9.9l-7.9 6.2C6.4 42.6 14.6 48 24 48z"/>
                    </svg>
                </template>
                <template x-if="busy">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </template>
                <span x-text="busy ? 'Signing you in…' : @js($label)"></span>
            </button>
        @endif

        <div x-show="error" x-cloak class="alert alert-error mt-3" role="alert" style="font-size:13.5px;">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span x-text="error"></span>
        </div>
    </div>
@endif

@once
    @push('scripts')
    <script>
        function googleSignIn(config, useIdentityServices) {
            return {
                busy: false,
                loading: useIdentityServices,
                error: '',

                async init() {
                    if (!useIdentityServices) return;

                    try {
                        const module = await window.loadGoogleSignIn();

                        await module.renderGoogleButton(
                            config,
                            this.$refs.gis,
                            (url) => window.location.assign(url),
                            (message) => { if (message) this.error = message; },
                        );
                    } catch (e) {
                        // Surface the real reason when there is one — a refused
                        // origin is a setup problem with a specific fix, and
                        // hiding it behind "check your connection" sends whoever
                        // is debugging in entirely the wrong direction.
                        this.error = e?.message || 'Could not load Google sign-in. Check your connection and try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async startPopup() {
                    if (this.busy) return;

                    this.busy = true;
                    this.error = '';

                    let module;

                    try {
                        // Fetched only now, so the Firebase SDK never reaches a
                        // visitor who does not sign in. The loader is defined in
                        // app.js so that Vite can code-split it.
                        module = await window.loadGoogleSignIn();
                    } catch {
                        this.error = 'Could not load Google sign-in. Check your connection and try again.';
                        this.busy = false;

                        return;
                    }

                    try {
                        window.location.assign(await module.signInWithGoogle(config));
                        // Deliberately stays busy: the page is navigating away, and
                        // re-enabling the button would invite a second popup.
                        return;
                    } catch (e) {
                        const message = module.describeError(e);
                        if (message) this.error = message;
                    }

                    this.busy = false;
                },
            };
        }
    </script>
    @endpush
@endonce
