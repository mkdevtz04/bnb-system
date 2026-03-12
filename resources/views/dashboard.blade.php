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
                <div class="flex gap-4">
                    <a href="{{ route('profile.edit') }}" class="btn-book" style="width: auto; padding: 12px 24px; background: white; color: var(--text); border: 1.5px solid var(--border);">
                        <i class="fa-solid fa-user-gear"></i>
                        Edit Profile
                    </a>
                    <a href="{{ route('bookings.history') }}" class="btn-book" style="width: auto; padding: 12px 24px; background: white; color: var(--blue); border: 1.5px solid var(--blue);">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Booking History
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                {{-- Recent Bookings --}}
                <div class="lg:col-span-2">
                    <div class="section-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <h2 style="font-size: 20px; font-weight: 700; color: var(--text);">My Recent Bookings</h2>
                        <a href="{{ route('bookings.history') }}" style="font-size: 13px; color: var(--blue); font-weight: 600;">View all</a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($myBookings as $booking)
                            <div style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 16px; display: flex; align-items: center; gap: 20px;">
                                <div style="width: 100px; height: 70px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: var(--blue-light);">
                                    @if($booking->apartment->images->count() > 0)
                                        <img src="{{ \Storage::url($booking->apartment->images->first()->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                                            <i class="fa-solid fa-image" style="color:var(--blue);"></i>
                                        </div>
                                    @endif
                                </div>
                                <div style="flex-grow: 1;">
                                    <h4 style="font-weight: 700; color: var(--text); margin-bottom: 4px;">{{ $booking->apartment->name }}</h4>
                                    <p style="font-size: 12px; color: var(--muted);">{{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}</p>
                                </div>
                                <div style="text-align: right;">
                                    @php
                                        $statusColors = [
                                            'pending' => ['bg' => '#FFFBEB', 'text' => '#92400E'],
                                            'confirmed' => ['bg' => '#ECFDF5', 'text' => '#065F46'],
                                            'cancelled' => ['bg' => '#FEF2F2', 'text' => '#991B1B'],
                                        ];
                                        $colors = $statusColors[$booking->status] ?? ['bg' => '#F3F4F6', 'text' => '#374151'];
                                    @endphp
                                    <span style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                        {{ strtoupper($booking->status) }}
                                    </span>
                                    <p style="font-size: 13px; font-weight: 700; color: var(--text); mt-1">${{ number_format($booking->total_price) }}</p>
                                    @if($booking->status !== 'cancelled')
                                        <a href="{{ route('messages.show', $booking) }}" style="font-size: 11px; color: var(--blue); font-weight: 700; display: block; margin-top: 4px;">
                                            <i class="fa-solid fa-message"></i> Chat with Host
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="background: white; border-radius: 16px; border: 1.5px dashed var(--border); padding: 40px; text-align: center;">
                                <p style="color: var(--muted); font-size: 14px;">You haven't made any bookings yet.</p>
                                <a href="#explore" style="color: var(--blue); font-weight: 700; font-size: 14px; margin-top: 8px; display: inline-block;">Start exploring →</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Notifications/Reminders --}}
                <div>
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h2 style="font-size: 20px; font-weight: 700; color: var(--text);">Alerts & Reminders</h2>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($notifications as $notification)
                            <div style="background: {{ str_contains($notification->data['message'], 'confirmed') ? '#ECFDF5' : '#E8F0FF' }}; border: 1px solid {{ str_contains($notification->data['message'], 'confirmed') ? '#A7F3D0' : '#BFDBFE' }}; border-radius: 16px; padding: 16px; position: relative;">
                                <div style="display: flex; gap: 12px;">
                                    <span style="font-size: 20px;">{{ str_contains($notification->data['message'], 'confirmed') ? '✨' : '📅' }}</span>
                                    <div>
                                        <p style="font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 4px;">{{ $notification->data['message'] }}</p>
                                        <p style="font-size: 11px; color: #4B5563;">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 24px; text-align: center;">
                                <i class="fa-solid fa-bell-slash" style="color: var(--muted); font-size: 24px; margin-bottom: 12px;"></i>
                                <p style="color: var(--muted); font-size: 13px;">No new alerts at this time.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div id="explore" class="section-header" style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 700; color: var(--text);">Explore All Apartments</h2>
            </div>

            @if($apartments->count() > 0)
                <div class="apt-grid">
                    @foreach($apartments as $apt)
                        <div class="apt-card">
                            <div class="apt-image-wrapper">
                                @if($apt->images->count() > 0)
                                    <img src="{{ \Storage::url($apt->images->first()->image_path) }}" alt="{{ $apt->name }}">
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
