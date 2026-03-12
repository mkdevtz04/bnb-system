{{-- ═══════════════════ PREMIUM NAVBAR ═══════════════════ --}}
<nav class="premium-navbar">
    <a href="{{ url('/') }}" class="navbar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
            </svg>
        </div>
        <span>Coastal<em>Charmz</em></span>
    </a>

    <div class="navbar-links">
        <a href="{{ url('/') }}#apartments">Our Apartments</a>
        <a href="#amenities">Amenities</a>
        <a href="#gallery">Gallery</a>
        <a href="#neighborhood">Neighborhood</a>
        <a href="#contact">Contact Host</a>
    </div>

    <div class="navbar-actions">
        <button class="btn-ghost-premium btn-help"><i class="fa-regular fa-circle-question"></i> Help</button>
        @auth
            @if(Auth::user()->role == 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-primary-premium">Admin Extranet</a>
            @else
                <a href="{{ route('bookings.history') }}" class="btn-ghost-premium btn-bookings"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                <a href="{{ route('dashboard') }}" class="btn-primary-premium">Dashboard</a>
            @endif
            
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout-premium" style="color: #e74c3c;">Logout</button>
            </form>
        @else
            <button class="btn-primary-premium" onclick="typeof openAuthModal === 'function' ? openAuthModal() : window.location.href='{{ route('login') }}'">Sign In / Login</button>
        @endauth
        
        <button class="mobile-toggle" onclick="toggleMobileMenu()">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenu">
        <div class="mobile-menu-content">
            <button class="mobile-menu-close" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="mobile-links">
                <a href="{{ url('/') }}#apartments" onclick="toggleMobileMenu()">Our Apartments</a>
                <a href="#amenities" onclick="toggleMobileMenu()">Amenities</a>
                <a href="#gallery" onclick="toggleMobileMenu()">Gallery</a>
                <a href="#neighborhood" onclick="toggleMobileMenu()">Neighborhood</a>
                @auth
                    <hr>
                    @if(Auth::user()->role == 'admin')
                        <a href="{{ route('admin.dashboard') }}">Admin Extranet</a>
                    @else
                        <a href="{{ route('bookings.history') }}">My Bookings</a>
                        <a href="{{ route('dashboard') }}">My Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mobile-logout-btn">Logout</button>
                    </form>
                @else
                    <hr>
                    <a href="{{ route('login') }}">Login / Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('active');
        document.body.classList.toggle('overflow-hidden');
    }
</script>
