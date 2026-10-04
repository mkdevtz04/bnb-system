@php
    $user = auth()->user();
    $unread = $user?->unreadNotifications()->count() ?? 0;
    // Indexed on (receiver_id, is_read), so this is cheap enough to run on every
    // authenticated page load.
    $unreadMessages = $user?->receivedMessages()->where('is_read', false)->count() ?? 0;
@endphp

<nav class="navbar" x-data="{ menu: false }">
    <a href="{{ url('/') }}" class="navbar-brand">
        <i class="fa-solid fa-location-dot" style="color:var(--brand-600);"></i>
        Coastal<em>Charmz</em>
    </a>

    <div class="flex items-center gap-1">
        <a href="{{ route('apartments.search') }}" class="navbar-link">
            <i class="fa-solid fa-magnifying-glass"></i> Find a stay
        </a>
        @auth
            <a href="{{ route('messages.index') }}" class="navbar-link">
                <i class="fa-regular fa-comments"></i> Messages
                @if ($unreadMessages > 0)
                    <span class="badge badge-brand" style="padding:1px 6px; font-size:11px;">{{ $unreadMessages }}</span>
                @endif
            </a>
        @else
            <a href="{{ url('/#contact') }}" class="navbar-link">
                <i class="fa-regular fa-envelope"></i> Contact host
            </a>
        @endauth
    </div>

    <div class="flex items-center gap-2">
        @auth
            {{-- Notifications --}}
            <div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
                <button type="button" class="navbar-link" style="display:inline-flex;" @click="open = !open"
                        aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    @if ($unread > 0)
                        <span class="badge badge-brand" style="padding:1px 6px; font-size:11px;">{{ $unread }}</span>
                    @endif
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                     class="card absolute right-0 mt-2 w-80 overflow-hidden" style="box-shadow:var(--shadow-lg);">
                    <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid var(--ink-200);">
                        <strong style="font-size:14px;">Notifications</strong>
                        @if ($unread > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button class="btn btn-ghost btn-sm">Mark all read</button>
                            </form>
                        @endif
                    </div>

                    <div class="divide-y-soft max-h-80 overflow-y-auto">
                        @forelse ($user->unreadNotifications()->limit(6)->get() as $note)
                            <form method="POST" action="{{ route('notifications.read', $note->id) }}">
                                @csrf
                                <button class="block w-full px-4 py-3 text-left hover:bg-slate-50" style="font-size:13.5px;">
                                    {{ $note->data['message'] ?? 'Update on your booking' }}
                                    <span class="muted block" style="font-size:12px;">{{ $note->created_at->diffForHumans() }}</span>
                                </button>
                            </form>
                        @empty
                            <p class="muted px-4 py-6 text-center" style="font-size:13.5px;">You're all caught up.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Account --}}
            <div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
                <button type="button" class="flex items-center gap-2" @click="open = !open">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="" referrerpolicy="no-referrer"
                             class="avatar" style="object-fit:cover;">
                    @else
                        <span class="avatar">{{ $user->initials }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-down hidden sm:inline muted" style="font-size:11px;"></i>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                     class="card absolute right-0 mt-2 w-60 overflow-hidden" style="box-shadow:var(--shadow-lg);">
                    <div class="px-4 py-3" style="border-bottom:1px solid var(--ink-200);">
                        <div class="font-semibold" style="font-size:14px;">{{ $user->display_name }}</div>
                        <div class="muted truncate" style="font-size:12.5px;">{{ $user->email }}</div>
                    </div>

                    <div class="py-1">
                        @if ($user->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 hover:bg-slate-50" style="font-size:14px;">
                                <i class="fa-solid fa-gauge w-5 muted"></i> Admin
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-slate-50" style="font-size:14px;">
                                <i class="fa-solid fa-gauge w-5 muted"></i> Dashboard
                            </a>
                        @endif
                        <a href="{{ route('bookings.history') }}" class="block px-4 py-2.5 hover:bg-slate-50" style="font-size:14px;">
                            <i class="fa-solid fa-calendar-check w-5 muted"></i> My bookings
                        </a>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 hover:bg-slate-50" style="font-size:14px;">
                            <i class="fa-regular fa-user w-5 muted"></i> Profile
                        </a>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" style="border-top:1px solid var(--ink-200);">
                        @csrf
                        <button class="block w-full px-4 py-2.5 text-left hover:bg-slate-50" style="font-size:14px; color:var(--red-600);">
                            <i class="fa-solid fa-arrow-right-from-bracket w-5"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>
        @else
            <button type="button" class="btn btn-primary btn-sm"
                    onclick="window.openAuthModal ? window.openAuthModal() : window.location.assign(@js(route('login')))">
                Sign in
            </button>
        @endauth

        {{-- Mobile menu --}}
        <button type="button" class="navbar-link lg:hidden" style="display:inline-flex;" @click="menu = !menu"
                aria-label="Menu">
            <i class="fa-solid" :class="menu ? 'fa-xmark' : 'fa-bars'"></i>
        </button>
    </div>

    <div x-show="menu" x-cloak x-transition class="navbar-drawer">
        <a href="{{ route('apartments.search') }}">
            <i class="fa-solid fa-magnifying-glass w-6 muted"></i> Find a stay
        </a>
        <a href="{{ url('/#contact') }}">
            <i class="fa-regular fa-envelope w-6 muted"></i> Contact host
        </a>
        @auth
            <a href="{{ route('bookings.history') }}">
                <i class="fa-solid fa-calendar-check w-6 muted"></i> My bookings
            </a>
        @endauth
    </div>
</nav>
