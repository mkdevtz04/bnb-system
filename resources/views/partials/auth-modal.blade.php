{{-- Sign-in modal for the nav button, so a visitor browsing apartments does not
     lose their place. It reuses <x-google-signin>, so there is one Google
     implementation rather than one per surface — the duplication that previously
     left two OTP controllers fighting over the same route. --}}
<div x-data="{ open: false }" x-show="open" x-cloak @keydown.escape.window="open = false"
     @open-auth.window="open = true"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     style="background: rgba(15,23,42,.45);" role="dialog" aria-modal="true" aria-labelledby="auth-title">

    <div class="card w-full" style="max-width: 400px; box-shadow: var(--shadow-lg);" @click.outside="open = false">
        <div class="card-pad">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h2 id="auth-title" style="font-size:20px;">Sign in</h2>
                    <p class="muted mt-1" style="font-size:13.5px;">
                        To book a stay and message the host.
                    </p>
                </div>
                <button type="button" @click="open = false" class="btn btn-ghost btn-sm" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <x-google-signin label="Continue with Google" />

            <div class="my-4 flex items-center gap-3" aria-hidden="true">
                <span style="flex:1; height:1px; background:var(--ink-200);"></span>
                <span class="muted" style="font-size:12.5px;">or</span>
                <span style="flex:1; height:1px; background:var(--ink-200);"></span>
            </div>

            <a href="{{ route('login') }}" class="btn btn-secondary btn-block">
                <i class="fa-solid fa-key" style="font-size:13px;"></i> Sign in with a password
            </a>

            <p class="muted mt-4 text-center" style="font-size:12px; line-height:1.5;">
                New here? Signing in with Google creates your account automatically.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // The nav's Sign in button sits outside this component's tree.
    window.openAuthModal = () => window.dispatchEvent(new CustomEvent('open-auth'));
</script>
@endpush
