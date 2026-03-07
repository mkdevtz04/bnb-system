<x-app-layout>
    <style>
        :root {
            --blue: #1A6EFF;
            --blue-dark: #1258d6;
            --blue-light: #e8f0ff;
            --gold: #F5A623;
            --text: #1a1d23;
            --muted: #6b7280;
            --border: #e5e7eb;
            --card-bg: #fff;
            --bg: #f8f9fc;
            --radius: 14px;
            --shadow: 0 4px 24px rgba(26,110,255,0.08);
            --shadow-hover: 0 8px 32px rgba(26,110,255,0.16);
        }

        .dashboard-container {
            padding: 40px;
            background: #f8f9fc;
            min-height: calc(100vh - 64px);
        }

        .welcome-hero {
            background: #fff;
            padding: 32px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--border);
        }

        .welcome-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
        }

        .welcome-text p {
            color: var(--muted);
            font-size: 15px;
        }

        .apt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .apt-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            border: 1.5px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
        }

        .apt-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--blue);
        }

        .apt-image-wrapper {
            height: 200px;
            position: relative;
            overflow: hidden;
            background: var(--blue-light);
        }

        .apt-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .apt-card:hover .apt-image-wrapper img {
            transform: scale(1.1);
        }

        .apt-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(255,255,255,0.9);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: var(--blue);
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .apt-price {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: var(--blue);
            color: #fff;
            padding: 6px 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
        }

        .apt-content {
            padding: 24px;
        }

        .apt-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
        }

        .apt-meta {
            display: flex;
            gap: 16px;
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 20px;
        }

        .apt-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-book {
            width: 100%;
            background: var(--blue);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-book:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26,110,255,0.25);
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: #fff;
            border-radius: 20px;
            border: 2px dashed var(--border);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--blue-light);
            margin-bottom: 16px;
        }
    </style>

    <div class="dashboard-container">
        <div class="max-w-7xl mx-auto">
            
            {{-- Welcome Header --}}
            <div class="welcome-hero">
                <div class="welcome-text">
                    <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
                    <p>Ready to find your next luxury getaway? All our apartments are fully sanitized and ready for your arrival.</p>
                </div>
            </div>

            {{-- Apartment List --}}
            <div class="section-header" style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 700; color: var(--text);">Explore All Apartments</h2>
            </div>

            @if($apartments->count() > 0)
                <div class="apt-grid">
                    @foreach($apartments as $apt)
                        <div class="apt-card">
                            <div class="apt-image-wrapper">
                                @if($apt->images->count() > 0)
                                    <img src="{{ asset('storage/' . $apt->images->first()->image_path) }}" alt="{{ $apt->name }}">
                                @else
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f0f4f8;">
                                        <i class="fa-solid fa-image fa-2x" style="color:#cbd5e0;"></i>
                                    </div>
                                @endif
                                <div class="apt-badge">{{ $apt->floor ?? 'Ground' }} Floor</div>
                                <div class="apt-price">${{ number_format($apt->price_per_night) }}<small style="font-size:10px; opacity:0.8;">/night</small></div>
                            </div>
                            
                            <div class="apt-content">
                                <h3>{{ $apt->name }}</h3>
                                <div class="apt-meta">
                                    <span><i class="fa-solid fa-bed"></i> {{ $apt->bedrooms }} Beds</span>
                                    <span><i class="fa-solid fa-bath"></i> {{ $apt->bathrooms }} Baths</span>
                                    <span><i class="fa-solid fa-users"></i> {{ $apt->max_guests }} Guests</span>
                                </div>
                                
                                <a href="{{ route('apartments.show', $apt) }}" class="btn-book">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    Book This Apartment
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-house-chimney-crack"></i>
                    <h3>No apartments available right now</h3>
                    <p>Please check back later or contact the host for upcoming listings.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
