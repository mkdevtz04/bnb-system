<x-app-layout>
    @push('styles')
    <style>

        .show-container {
            padding: 40px 20px;
            background: var(--bg);
            min-height: calc(100vh - 64px);
        }

        .show-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .show-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }

        .show-header .location {
            color: var(--muted);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .main-gallery {
            background: #fff;
            padding: 12px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 32px;
        }

        .main-image {
            width: 100%;
            height: 480px;
            border-radius: 12px;
            object-fit: cover;
            background: var(--blue-light);
            cursor: zoom-in;
        }

        .thumb-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 12px;
        }

        .thumb {
            height: 100px;
            width: 100%;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .thumb:hover, .thumb.active { border-color: var(--blue); transform: scale(1.02); }

        .details-card {
            background: #fff;
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 32px;
        }

        .details-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--text);
            border-bottom: 2px solid var(--blue-light);
            display: inline-block;
            padding-bottom: 4px;
        }

        .amenity-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 24px;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: var(--bg);
            border-radius: 12px;
            color: var(--text);
            font-size: 15px;
            font-weight: 500;
            border: 1px solid transparent;
            transition: 0.2s;
        }
        .amenity-item:hover { border-color: var(--blue-light); background: #fff; transform: translateY(-2px); }

        .amenity-item i {
            color: var(--blue);
            font-size: 18px;
        }

        .booking-sidebar {
            background: #fff;
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: 0 12px 48px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
            position: sticky;
            top: 100px;
        }

        .price-tag { margin-bottom: 24px; }
        .price-tag .amount { font-size: 32px; font-weight: 800; color: var(--blue); }
        .price-tag .unit { color: var(--muted); font-size: 14px; }

        .form-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; margin-top: 16px; }
        .booking-input { width: 100%; padding: 12px; border: 1.5px solid var(--border); border-radius: 10px; outline: none; transition: 0.2s; background: var(--bg); font-weight: 500; }
        .booking-input:focus { border-color: var(--blue); background: #fff; box-shadow: 0 0 0 4px var(--blue-light); }

        .price-summary {
            background: var(--bg);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            display: none;
        }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        .summary-total { display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); font-weight: 700; font-size: 16px; color: var(--text); }

        .btn-confirm {
            width: 100%;
            background: var(--blue);
            color: #fff;
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 24px;
            text-decoration: none;
        }

        .btn-confirm:disabled { background: #cbd5e0; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-confirm:hover:not(:disabled) { background: var(--blue-dark); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(26,110,255,0.2); }

        /* Flatpickr Custom */
        .flatpickr-calendar { box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; border: none !important; }
        .flatpickr-day.selected { background: var(--blue) !important; border-color: var(--blue) !important; }
        
        @media (max-width: 1024px) {
            .show-grid { gap: 24px; padding: 0 16px; }
            .main-image { height: 380px; }
        }

        @media (max-width: 900px) {
            .show-grid { grid-template-columns: 1fr; }
            .booking-sidebar { position: static; margin-top: 32px; box-shadow: var(--shadow); }
        }

        @media (max-width: 640px) {
            .show-header h1 { font-size: 28px; }
            .main-image { height: 300px; }
            .amenity-grid { grid-template-columns: 1fr; gap: 12px; }
            .thumb-grid { grid-template-columns: repeat(3, 1fr); }
            .thumb:nth-child(4) { display: none; }
        }
    </style>
    @endpush

    <div class="show-container">
        <div class="show-grid">
            
            {{-- Left Column --}}
            <div class="main-content">
                <div class="show-header">
                    @auth
                        <a href="{{ Auth::user()->role == 'admin' ? route('admin.dashboard') : route('dashboard') }}" style="color:var(--blue); text-decoration:none; font-size:14px; font-weight:600; margin-bottom:16px; display:inline-block;">
                            <i class="fa-solid fa-chevron-left"></i> Back to Apartments
                        </a>
                    @endauth
                    <h1>{{ $apartment->name }}</h1>
                    <div class="location">
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $apartment->floor }} Floor • Prime Residential Area • City Center
                    </div>
                </div>

                <div class="main-gallery">
                    @if($apartment->images->count() > 0)
                        <img src="{{ \Storage::url($apartment->images->first()->image_path) }}" class="main-image" id="mainImage">
                        @if($apartment->images->count() > 1)
                            <div class="thumb-grid">
                                @foreach($apartment->images as $i => $image)
                                    <img src="{{ \Storage::url($image->image_path) }}" class="thumb {{ $i==0 ? 'active' : '' }}" onclick="changeImage(this)">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="main-image" style="display:flex; align-items:center; justify-content:center; background:#f0f4f8; border-radius:12px;">
                            <i class="fa-solid fa-building fa-4x" style="color:#d1d5db;"></i>
                        </div>
                    @endif
                </div>

                <div class="details-card">
                    <h2>About this place</h2>
                    <p style="color:var(--muted); line-height:1.7; font-size:16px;">{{ $apartment->description }}</p>

                    <h2 style="margin-top:48px;">Amenities & Features</h2>
                    <div class="amenity-grid">
                        <div class="amenity-item"><i class="fa-solid fa-bed"></i> <strong>{{ $apartment->bedrooms }}</strong> Bedrooms</div>
                        <div class="amenity-item"><i class="fa-solid fa-bath"></i> <strong>{{ $apartment->bathrooms }}</strong> Bathrooms</div>
                        <div class="amenity-item"><i class="fa-solid fa-users"></i> Up to <strong>{{ $apartment->max_guests }}</strong> Guests</div>
                        <div class="amenity-item"><i class="fa-solid fa-wifi"></i> Free High-Speed WiFi</div>
                        <div class="amenity-item"><i class="fa-solid fa-snowflake"></i> Air Conditioning</div>
                        <div class="amenity-item"><i class="fa-solid fa-laptop-code"></i> Workspace/Office Space</div>
                        <div class="amenity-item"><i class="fa-solid fa-kitchen-set"></i> Full Gourmet Kitchen</div>
                        <div class="amenity-item"><i class="fa-solid fa-shield-halved"></i> 24/7 Security Access</div>
                    </div>
                </div>
            </div>

            {{-- Right Column (Booking) --}}
            <aside>
                <div class="booking-sidebar">
                    <div class="price-tag">
                        <span class="amount">${{ number_format($apartment->price_per_night) }}</span>
                        <span class="unit">/ night</span>
                    </div>

                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                        @csrf
                        <input type="hidden" name="apartment_id" value="{{ $apartment->id }}">
                        
                        <label class="form-label">Pick Availability</label>
                        <input type="text" id="date_range" class="booking-input" placeholder="Select dates..." readonly>
                        <input type="hidden" name="check_in" id="check_in">
                        <input type="hidden" name="check_out" id="check_out">

                        <label class="form-label">Number of Guests</label>
                        <select name="guests" class="booking-input">
                            @for($i = 1; $i <= $apartment->max_guests; $i++)
                                <option value="{{ $i }}">{{ $i }} {{ $i > 1 ? 'Guests' : 'Guest' }}</option>
                            @endfor
                        </select>

                        <div class="price-summary" id="priceSummary">
                            <div class="summary-row">
                                <span id="summary-nights-label">$0 x 0 nights</span>
                                <span id="summary-stay-total">$0</span>
                            </div>
                            <div class="summary-row">
                                <span>Service fee</span>
                                <span>$0</span>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span id="summary-total">$0</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-confirm" id="bookBtn" disabled>
                            <i class="fa-solid fa-bolt"></i>
                            Check Availability
                        </button>
                    </form>

                    <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--border); font-size:13px; color:var(--muted); text-align:center;">
                        <i class="fa-solid fa-circle-info"></i> Your dates are safe. You won't be charged yet.
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <script>
        function changeImage(el) {
            document.getElementById('mainImage').src = el.src;
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        const pricePerNight = {{ $apartment->price_per_night }};
        const unavailableDates = {!! json_encode($unavailableDates) !!};

        flatpickr("#date_range", {
            mode: "range",
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: unavailableDates,
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const start = selectedDates[0];
                    const end = selectedDates[1];
                    const diffTime = Math.abs(end - start);
                    const diffNights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    if (diffNights > 0) {
                        // Update hidden fields
                        document.getElementById('check_in').value = instance.formatDate(start, "Y-m-d");
                        document.getElementById('check_out').value = instance.formatDate(end, "Y-m-d");

                        // Show summary
                        const stayTotal = pricePerNight * diffNights;
                        document.getElementById('priceSummary').style.display = 'block';
                        document.getElementById('summary-nights-label').innerText = `$${pricePerNight} x ${diffNights} nights`;
                        document.getElementById('summary-stay-total').innerText = `$${stayTotal}`;
                        document.getElementById('summary-total').innerText = `$${stayTotal}`;
                        
                        // Enable button
                        document.getElementById('bookBtn').disabled = false;
                        document.getElementById('bookBtn').innerHTML = '<i class="fa-solid fa-bolt"></i> Reserve Now';
                    }
                } else {
                    document.getElementById('priceSummary').style.display = 'none';
                    document.getElementById('bookBtn').disabled = true;
                    document.getElementById('bookBtn').innerHTML = '<i class="fa-solid fa-bolt"></i> Check Availability';
                }
            }
        });
    </script>
</x-app-layout>
