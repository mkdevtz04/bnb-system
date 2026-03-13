{{-- ═══════════════════ PREMIUM NAVBAR ═══════════════════ --}}
<style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
        --nav-ink:     #0f1117;
        --nav-gold:    #b8965a;
        --nav-gold-lt: #f5edd8;
        --nav-muted:   rgba(15,17,23,0.55);
        --nav-border:  rgba(15,17,23,0.10);
        --nav-h:       68px;
    }

    /* ── NAVBAR SHELL ── */
    .premium-navbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        height: var(--nav-h);
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--nav-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        font-family: 'Outfit', 'DM Sans', sans-serif;
        transition: box-shadow 0.3s;
    }
    .premium-navbar.scrolled {
        box-shadow: 0 4px 24px rgba(15,17,23,0.09);
    }
    @media (max-width: 768px) {
        .premium-navbar { padding: 0 20px; }
    }

    /* Push content below fixed nav; hero bleeds up */
    body { padding-top: var(--nav-h); }
    .hero { margin-top: calc(-1 * var(--nav-h)); padding-top: var(--nav-h); }

    /* ── LOGO ── */
    .navbar-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        flex-shrink: 0;
    }
    .logo-icon {
        width: 34px; height: 34px;
        background: var(--nav-ink);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .logo-icon svg { width: 18px; height: 18px; fill: var(--nav-gold); }
    .navbar-logo span {
        font-family: 'Outfit', sans-serif;
        font-size: 17px; font-weight: 600;
        color: var(--nav-ink);
        letter-spacing: -0.01em;
    }
    .navbar-logo span em { font-style: normal; color: var(--nav-gold); }

    /* ── DESKTOP CENTRE LINKS ── */
    .navbar-links {
        display: flex;
        align-items: center;
        gap: 4px;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
    @media (max-width: 1024px) { .navbar-links { display: none; } }

    .navbar-links a {
        font-size: 13px; font-weight: 500;
        color: var(--nav-muted);
        text-decoration: none;
        padding: 8px 13px;
        border-radius: 8px;
        white-space: nowrap;
        transition: color 0.2s, background 0.2s;
    }
    .navbar-links a:hover { color: var(--nav-ink); background: rgba(15,17,23,0.05); }

    /* ── RIGHT ACTIONS ── */
    .navbar-actions { display: flex; align-items: center; gap: 6px; }

    .btn-ghost-premium {
        font-size: 13px; font-weight: 500;
        color: var(--nav-muted);
        background: none; border: none; cursor: pointer;
        padding: 8px 13px; border-radius: 8px;
        font-family: 'Outfit', 'DM Sans', sans-serif;
        display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none; white-space: nowrap;
        transition: color 0.2s, background 0.2s;
    }
    .btn-ghost-premium:hover { color: var(--nav-ink); background: rgba(15,17,23,0.05); }

    .btn-primary-premium {
        font-size: 13px; font-weight: 600;
        color: #fff; background: var(--nav-ink);
        border: none; border-radius: 10px;
        padding: 9px 18px; cursor: pointer;
        text-decoration: none; white-space: nowrap;
        display: inline-flex; align-items: center;
        font-family: 'Outfit', 'DM Sans', sans-serif;
        letter-spacing: 0.02em;
        transition: background 0.2s, transform 0.15s;
    }
    .btn-primary-premium:hover { background: #1a3a5c; transform: translateY(-1px); }

    .btn-logout-premium {
        font-size: 13px; font-weight: 500;
        color: #c0392b; background: none; border: none;
        cursor: pointer; padding: 8px 12px; border-radius: 8px;
        font-family: 'Outfit', 'DM Sans', sans-serif;
        transition: background 0.2s;
    }
    .btn-logout-premium:hover { background: #fdf0ef; }

    /* Hide text actions on mobile — only show toggle */
    @media (max-width: 768px) {
        .btn-help,
        .btn-bookings,
        .btn-logout-premium,
        .btn-primary-premium { display: none; }
    }

    /* ── MOBILE TOGGLE ─────────────────────────────────────────
       Solid dark background + white icon = visible on ANY surface
       ─────────────────────────────────────────────────────────── */
    .mobile-toggle {
        display: none;
        width: 44px; height: 44px;
        background: var(--nav-ink);      /* always dark */
        border: none; border-radius: 12px;
        cursor: pointer;
        align-items: center; justify-content: center;
        flex-shrink: 0;
        padding: 0;
        transition: background 0.2s, transform 0.15s;
    }
    .mobile-toggle:hover { background: #1a3a5c; transform: scale(1.05); }
    .mobile-toggle i {
        font-size: 16px;
        color: #ffffff;                  /* always white */
        line-height: 1;
        pointer-events: none;
    }
    @media (max-width: 768px) {
        .mobile-toggle { display: flex; }
    }

    /* ── MOBILE OVERLAY + SLIDE DRAWER ── */
    .mobile-menu-overlay {
        position: fixed;
        inset: 0;
        z-index: 1100;
        background: rgba(15,17,23,0.45);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        opacity: 0; visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
    }
    .mobile-menu-overlay.active { opacity: 1; visibility: visible; }

    .mobile-menu-content {
        position: absolute;
        top: 0; right: 0;
        width: min(340px, 88vw);
        height: 100%;
        background: #fff;
        display: flex; flex-direction: column;
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .mobile-menu-overlay.active .mobile-menu-content { transform: translateX(0); }

    /* Drawer header */
    .mobile-menu-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 20px;
        border-bottom: 1px solid var(--nav-border);
        flex-shrink: 0;
    }
    .mobile-menu-brand {
        display: flex; align-items: center; gap: 9px; text-decoration: none;
    }
    .mobile-menu-brand .logo-icon { width: 30px; height: 30px; border-radius: 8px; }
    .mobile-menu-brand .logo-icon svg { width: 15px; height: 15px; }
    .mobile-menu-brand span { font-size: 15px; font-weight: 600; color: var(--nav-ink); }
    .mobile-menu-brand span em { font-style: normal; color: var(--nav-gold); }

    .mobile-menu-close {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: rgba(15,17,23,0.06);
        border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }
    .mobile-menu-close:hover { background: rgba(15,17,23,0.12); }
    .mobile-menu-close i { font-size: 15px; color: var(--nav-ink); }

    /* Drawer links */
    .mobile-links { padding: 12px 12px; flex: 1; }
    .mobile-links a {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 12px;
        font-size: 15px; font-weight: 500;
        color: var(--nav-ink);
        text-decoration: none;
        border-radius: 10px;
        transition: background 0.18s, color 0.18s;
    }
    .mobile-links a:hover { background: rgba(15,17,23,0.05); color: var(--nav-gold); }
    .mobile-links a i {
        width: 20px; text-align: center;
        font-size: 14px; color: var(--nav-muted); flex-shrink: 0;
    }
    .mobile-links hr { border: none; border-top: 1px solid var(--nav-border); margin: 8px 0; }

    /* Drawer auth section */
    .mobile-auth-section {
        padding: 16px;
        border-top: 1px solid var(--nav-border);
        flex-shrink: 0;
    }
    .mobile-user-row {
        display: flex; align-items: center; gap: 12px;
        padding: 12px; margin-bottom: 10px;
        background: rgba(15,17,23,0.04);
        border-radius: 12px;
    }
    .mobile-user-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--nav-ink); color: #fff;
        font-size: 16px; font-weight: 600;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .mobile-user-name { font-size: 14px; font-weight: 600; color: var(--nav-ink); }
    .mobile-user-email { font-size: 12px; color: var(--nav-muted); margin-top: 2px; }

    .mobile-logout-btn {
        display: flex; align-items: center; gap: 10px;
        width: 100%; padding: 13px 12px;
        font-size: 15px; font-weight: 500; color: #c0392b;
        background: none; border: none; border-radius: 10px;
        cursor: pointer; font-family: 'Outfit', 'DM Sans', sans-serif;
        transition: background 0.18s;
    }
    .mobile-logout-btn:hover { background: #fdf0ef; }
    .mobile-logout-btn i { font-size: 14px; }

    .mobile-signin-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 14px;
        font-size: 14px; font-weight: 600; color: #fff;
        background: var(--nav-ink); border: none; border-radius: 12px;
        cursor: pointer; text-decoration: none;
        font-family: 'Outfit', 'DM Sans', sans-serif;
        margin-top: 6px;
        transition: background 0.2s;
    }
    .mobile-signin-btn:hover { background: #1a3a5c; }
</style>

<nav class="premium-navbar" id="premiumNav">

    {{-- Logo --}}
    <a href="{{ url('/') }}" class="navbar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
            </svg>
        </div>
        <span>Coastal<em>Charmz</em></span>
    </a>

    {{-- Desktop centre links --}}
    <div class="navbar-links">
        <a href="{{ url('/') }}#apartments">Our Apartments</a>
        <a href="#amenities">Amenities</a>
        <a href="#gallery">Gallery</a>
        <a href="#neighborhood">Neighborhood</a>
        <a href="#contact">Contact Host</a>
    </div>

    {{-- Desktop right actions --}}
    <div class="navbar-actions">
        <button class="btn-ghost-premium btn-help">
            <i class="fa-regular fa-circle-question"></i> Help
        </button>

        @auth
            @if(Auth::user()->role == 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-primary-premium">Admin Extranet</a>
            @else
                <a href="{{ route('bookings.history') }}" class="btn-ghost-premium btn-bookings">
                    <i class="fa-solid fa-calendar-check"></i> My Bookings
                </a>
                <a href="{{ route('dashboard') }}" class="btn-primary-premium">Dashboard</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout-premium">Logout</button>
            </form>
        @else
            <button class="btn-primary-premium"
                onclick="typeof openAuthModal==='function' ? openAuthModal() : window.location.href='{{ route('login') }}'">
                Sign In
            </button>
        @endauth

        {{-- ▼ MOBILE TOGGLE — dark bg + white icon, always visible ▼ --}}
        <button class="mobile-toggle" onclick="toggleMobileMenu()" aria-label="Open menu">
            <i class="fa-solid fa-bars" id="mobileToggleIcon"></i>
        </button>
    </div>

</nav>

{{-- ── MOBILE SLIDE-IN DRAWER (outside <nav> so it is not clipped by nav's 68px height) ── --}}
<div class="mobile-menu-overlay" id="mobileMenu" onclick="handleOverlayClick(event)">
    <div class="mobile-menu-content">

        {{-- Drawer header --}}
        <div class="mobile-menu-header">
            <a href="{{ url('/') }}" class="mobile-menu-brand">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                    </svg>
                </div>
                <span>Coastal<em>Charmz</em></span>
            </a>
            <button class="mobile-menu-close" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Nav links --}}
        <div class="mobile-links">
            <a href="{{ url('/') }}#apartments" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-building"></i> Our Apartments
            </a>
            <a href="#amenities" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-spa"></i> Amenities
            </a>
            <a href="#gallery" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-images"></i> Gallery
            </a>
            <a href="#neighborhood" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-map-location-dot"></i> Neighborhood
            </a>
            <a href="#contact" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-envelope"></i> Contact Host
            </a>

            @auth
                <hr>
                @if(Auth::user()->role == 'admin')
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-gauge"></i> Admin Extranet
                    </a>
                @else
                    <a href="{{ route('bookings.history') }}">
                        <i class="fa-solid fa-calendar-check"></i> My Bookings
                    </a>
                    <a href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-gauge"></i> My Dashboard
                    </a>
                @endif
            @endauth
        </div>

        {{-- Auth section pinned at bottom of drawer --}}
        <div class="mobile-auth-section">
            @auth
                <div class="mobile-user-row">
                    <div class="mobile-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="mobile-user-name">{{ Auth::user()->name }}</div>
                        <div class="mobile-user-email">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" style="display:flex; align-items:center; gap:12px; padding:13px 12px; font-size:15px; font-weight:500; color:var(--nav-ink); text-decoration:none; border-radius:10px; transition:background 0.18s;">
                    <i class="fa-solid fa-arrow-right-to-bracket" style="width:20px; text-align:center; font-size:14px; color:var(--nav-muted);"></i> Log in
                </a>
                <button class="mobile-signin-btn"
                    onclick="toggleMobileMenu(); setTimeout(() => typeof openAuthModal==='function' && openAuthModal(), 320);">
                    <i class="fa-solid fa-envelope"></i> Sign in with email
                </button>
            @endauth
        </div>

    </div>
</div>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('mobileToggleIcon');
        const isOpen = menu.classList.contains('active');
        menu.classList.toggle('active', !isOpen);
        document.body.classList.toggle('overflow-hidden', !isOpen);
        // swap bars ↔ xmark
        if (icon) {
            icon.className = isOpen ? 'fa-solid fa-bars' : 'fa-solid fa-xmark';
        }
    }

    // Close when tapping the dark backdrop (not the drawer)
    function handleOverlayClick(e) {
        if (e.target === document.getElementById('mobileMenu')) {
            toggleMobileMenu();
        }
    }

    // Scroll shadow
    window.addEventListener('scroll', () => {
        const nav = document.getElementById('premiumNav');
        if (nav) nav.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
</script>