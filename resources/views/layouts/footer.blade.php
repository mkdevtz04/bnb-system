{{-- White footer, separated from the page by a border rather than a dark block,
     so the whole site reads as one white surface. --}}
<footer style="background: var(--surface); border-top: 1px solid var(--ink-200); margin-top: 64px;">
    <div class="shell" style="padding: 48px 16px 32px;">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <a href="{{ url('/') }}" class="navbar-brand" style="font-size:20px;">
                    <i class="fa-solid fa-location-dot" style="color:var(--brand-600);"></i>
                    Coastal<em>Charmz</em>
                </a>
                <p class="muted" style="margin-top:12px; font-size:13.5px; line-height:1.6;">
                    Serviced apartments booked direct. No middleman, no hidden fees.
                </p>
            </div>

            <div>
                <h4 style="font-size:14px; font-family:var(--font-body); font-weight:600; margin-bottom:12px;">Book</h4>
                <ul class="muted" style="font-size:13.5px; line-height:2;">
                    <li><a href="{{ route('apartments.search') }}" style="color:var(--brand-700); text-decoration:none;">Find a stay</a></li>
                    @auth
                        <li><a href="{{ route('bookings.history') }}" style="color:var(--brand-700); text-decoration:none;">My bookings</a></li>
                    @endauth
                    <li><a href="{{ url('/#contact') }}" style="color:var(--brand-700); text-decoration:none;">Contact the host</a></li>
                </ul>
            </div>

            <div>
                <h4 style="font-size:14px; font-family:var(--font-body); font-weight:600; margin-bottom:12px;">Good to know</h4>
                <ul class="muted" style="font-size:13.5px; line-height:2;">
                    <li>Check-in from 2:00 PM</li>
                    <li>Check-out by 11:00 AM</li>
                    <li>Free cancellation before check-in</li>
                </ul>
            </div>

            <div>
                <h4 style="font-size:14px; font-family:var(--font-body); font-weight:600; margin-bottom:12px;">Payment</h4>
                <p class="muted" style="font-size:13.5px; line-height:1.6;">
                    Bookings are confirmed by the host. You are not charged at the time of booking.
                </p>
            </div>
        </div>

        <div class="muted" style="margin-top:36px; padding-top:20px; border-top:1px solid var(--ink-200); font-size:12.5px;">
            &copy; {{ date('Y') }} CoastalCharmz. All rights reserved.
        </div>
    </div>
</footer>
