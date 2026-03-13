<x-app-layout>
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:     #0f1117;
            --ink-60:  rgba(15,17,23,0.6);
            --ink-20:  rgba(15,17,23,0.12);
            --cream:   #faf8f4;
            --sand:    #f2ede4;
            --blue:    #1a3a5c;
            --blue-md: #2a5a8c;
            --blue-lt: #e8f0f9;
            --white:   #ffffff;
            --r-sm: 10px;
            --r-md: 16px;
            --r-lg: 24px;
            --r-xl: 36px;
            --shadow-card: 0 2px 24px rgba(15,17,23,0.07), 0 1px 4px rgba(15,17,23,0.04);
            --shadow-hover: 0 12px 40px rgba(15,17,23,0.13), 0 2px 8px rgba(15,17,23,0.06);
            --transition: 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { font-family: 'Outfit', sans-serif; color: var(--ink); background: var(--white); }

        /* ── HERO ── */
        .hero {
            position: relative;
            min-height: 92vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: var(--ink);
        }
        .hero-bg {
            position: absolute; inset: 0;
            background-size: cover;
            background-position: center 40%;
            background-repeat: no-repeat;
            opacity: 0.55;
            transform: scale(1.04);
            animation: heroZoom 20s ease-in-out infinite alternate;
        }
        @keyframes heroZoom { to { transform: scale(1.0); } }
        .hero-vignette {
            position: absolute; inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(15,17,23,0.35) 0%,
                rgba(15,17,23,0.05) 40%,
                rgba(15,17,23,0.55) 100%
            );
        }
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: var(--white);
            padding: 0 24px 40px;
            width: 100%;
            max-width: 1000px;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 10px;
            font-size: 11px; font-weight: 500; letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--blue-lt);
            margin-bottom: 24px;
            opacity: 0;
            animation: fadeUp 0.8s 0.2s forwards;
        }
        .hero-eyebrow::before, .hero-eyebrow::after {
            content: ''; display: block; width: 28px; height: 1px; background: var(--blue-lt); opacity: 0.7;
        }
        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(44px, 7vw, 84px);
            font-weight: 300;
            line-height: 1.05;
            letter-spacing: -0.01em;
            margin-bottom: 18px;
            opacity: 0;
            animation: fadeUp 0.9s 0.35s forwards;
        }
        .hero-title em { font-style: italic; font-weight: 300; }
        .hero-sub {
            font-size: 16px;
            font-weight: 300;
            color: rgba(255,255,255,0.75);
            margin-bottom: 52px;
            letter-spacing: 0.02em;
            opacity: 0;
            animation: fadeUp 0.9s 0.5s forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── SEARCH BOX ── */
        .search-wrap {
            opacity: 0;
            animation: fadeUp 0.9s 0.65s forwards;
        }
        .search-box {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border-radius: var(--r-lg);
            padding: 20px 20px 20px;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 24px 80px rgba(0,0,0,0.25);
        }
        .search-label-row {
            display: grid;
            grid-template-columns: 2fr 1.4fr 1.4fr 1.4fr auto;
            gap: 10px;
            align-items: end;
        }
        @media (max-width: 900px) { .search-label-row { grid-template-columns: 1fr 1fr; gap: 14px; } }
        @media (max-width: 560px) { .search-label-row { grid-template-columns: 1fr; } }
        .sf label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-60);
            margin-bottom: 7px;
        }
        .sf input, .sf select {
            width: 100%;
            padding: 13px 15px;
            border: 1.5px solid var(--ink-20);
            border-radius: var(--r-sm);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            color: var(--ink);
            background: var(--cream);
            outline: none;
            transition: border-color var(--transition), background var(--transition);
        }
        .sf input:focus, .sf select:focus {
            border-color: var(--blue-md);
            background: var(--white);
        }
        .btn-check {
            background: var(--ink);
            color: var(--white);
            border: none;
            border-radius: var(--r-sm);
            padding: 14px 26px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            white-space: nowrap;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
        }
        .btn-check:hover {
            background: var(--blue);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15,17,23,0.25);
        }
        @media (max-width: 900px) { .btn-check { grid-column: span 2; } }
        @media (max-width: 560px) { .btn-check { grid-column: span 1; justify-content: center; } }

        /* ── SECTION SCAFFOLDING ── */
        .section { padding: 96px 48px; }
        .section-alt { background: var(--cream); }
        @media (max-width: 768px) { .section { padding: 64px 24px; } }
        .container { max-width: 1200px; margin: 0 auto; }
        .sec-head { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 48px; }
        @media (max-width: 600px) { .sec-head { flex-direction: column; align-items: flex-start; gap: 18px; } }
        .sec-eyebrow {
            font-size: 10px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase;
            color: var(--blue-md); margin-bottom: 10px;
        }
        .sec-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 400;
            line-height: 1.15;
            color: var(--ink);
        }
        .sec-sub { font-size: 14px; color: var(--ink-60); margin-top: 6px; font-weight: 300; }
        .sec-link {
            font-size: 12px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--ink); text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            border-bottom: 1px solid var(--ink-20);
            padding-bottom: 2px;
            transition: color var(--transition), border-color var(--transition);
            white-space: nowrap;
        }
        .sec-link:hover { color: var(--blue-md); border-color: var(--blue-md); }

        /* ── TRUST TRIO ── */
        .trust-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        @media (max-width: 768px) { .trust-grid { grid-template-columns: 1fr; } }
        .trust-card {
            padding: 36px 28px;
            border: 1px solid var(--ink-20);
            border-radius: var(--r-lg);
            background: var(--white);
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }
        .trust-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); border-color: var(--blue-md); }
        .trust-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: var(--blue);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .trust-icon i { font-size: 20px; color: #ffffff; }
        .trust-card h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
        .trust-card p { font-size: 14px; color: var(--ink-60); line-height: 1.65; font-weight: 300; }

        /* ── FILTER TABS ── */
        .filter-tabs { display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap; }
        .filter-tab {
            padding: 8px 20px;
            border-radius: 40px;
            border: 1.5px solid var(--ink-20);
            background: transparent;
            font-family: 'Outfit', sans-serif;
            font-size: 12px; font-weight: 500; letter-spacing: 0.06em;
            cursor: pointer; transition: all var(--transition);
            color: var(--ink-60);
        }
        .filter-tab.active, .filter-tab:hover { background: var(--ink); color: var(--white); border-color: var(--ink); }

        /* ── APARTMENT DESTINATION CARDS ── */
        .dest-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
        @media (max-width: 1100px) { .dest-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .dest-grid { grid-template-columns: 1fr; } }
        .dest-card {
            border-radius: var(--r-lg);
            overflow: hidden;
            position: relative;
            aspect-ratio: 3/4;
            background: var(--sand);
            cursor: pointer;
            display: block;
            text-decoration: none;
            transition: transform var(--transition), box-shadow var(--transition);
        }
        .dest-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: var(--shadow-hover); }
        .dest-card-img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.6s; }
        .dest-card:hover .dest-card-img { transform: scale(1.06); }
        .dest-placeholder {
            width: 100%; height: 100%;
            background: var(--sand);
            display: flex; align-items: center; justify-content: center;
        }
        .dest-placeholder i { font-size: 44px; color: var(--ink-20); }
        .dest-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(15,17,23,0.78) 0%, rgba(15,17,23,0) 55%);
            padding: 20px 18px 22px;
            display: flex; flex-direction: column; justify-content: flex-end;
        }
        .dest-overlay h3 { font-size: 17px; font-weight: 600; color: var(--white); line-height: 1.2; margin-bottom: 4px; }
        .dest-overlay p { font-size: 12px; color: rgba(255,255,255,0.7); font-weight: 300; }
        .dest-badge {
            position: absolute; top: 14px; left: 14px;
            background: var(--blue-md);
            color: var(--white);
            font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
            padding: 4px 11px; border-radius: 20px;
        }

        /* ── DEAL CARDS ── */
        .deals-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
        @media (max-width: 1100px) { .deals-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .deals-grid { grid-template-columns: 1fr; } }
        .deal-card {
            border-radius: var(--r-md);
            overflow: hidden;
            border: 1px solid var(--ink-20);
            background: var(--white);
            cursor: pointer;
            display: block;
            text-decoration: none;
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }
        .deal-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); border-color: var(--blue-md); }
        .deal-img-wrap { aspect-ratio: 16/10; overflow: hidden; position: relative; background: var(--sand); }
        .deal-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.55s; }
        .deal-card:hover .deal-img-wrap img { transform: scale(1.06); }
        .deal-no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .deal-no-img i { font-size: 32px; color: var(--ink-20); }
        .deal-fav {
            position: absolute; top: 11px; right: 11px;
            width: 30px; height: 30px; border-radius: 50%;
            background: rgba(255,255,255,0.9);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: none;
            transition: transform var(--transition);
        }
        .deal-fav:hover { transform: scale(1.15); }
        .deal-fav i { font-size: 13px; color: #e05252; }
        .deal-status {
            position: absolute; top: 11px; left: 11px;
            font-size: 10px; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase;
            padding: 4px 10px; border-radius: 20px;
        }
        .status-available { background: #dcf5e7; color: #166534; }
        .status-limited   { background: #fef3c7; color: #92400e; }
        .deal-body { padding: 14px 16px 16px; }
        .deal-body h3 { font-size: 14px; font-weight: 600; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .deal-location { font-size: 12px; color: var(--ink-60); display: flex; align-items: center; gap: 5px; margin-bottom: 12px; font-weight: 300; }
        .deal-foot { display: flex; align-items: center; justify-content: space-between; }
        .deal-rating { display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; }
        .deal-rating i { color: var(--blue-md); font-size: 11px; }
        .deal-price { font-size: 12px; color: var(--ink-60); }
        .deal-price strong { font-size: 18px; color: var(--ink); font-weight: 600; }

        /* ── AMENITIES ── */
        .amenity-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        @media (max-width: 900px) { .amenity-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .amenity-grid { grid-template-columns: 1fr; } }
        .amenity-card {
            background: var(--white);
            border: 1px solid var(--ink-20);
            border-radius: var(--r-lg);
            padding: 32px 24px;
            text-align: center;
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }
        .amenity-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); border-color: var(--blue-md); }
        .amenity-icon {
            width: 60px; height: 60px; border-radius: 50%;
            background: var(--blue);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
        }
        .amenity-icon i { font-size: 22px; color: #ffffff; }
        .amenity-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 8px; }
        .amenity-card p { font-size: 13px; color: var(--ink-60); line-height: 1.6; font-weight: 300; }

        /* ── NEIGHBORHOOD ── */
        .nbhd-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: center; }
        @media (max-width: 900px) { .nbhd-wrap { grid-template-columns: 1fr; gap: 40px; } }
        .nbhd-img { width: 100%; height: 500px; object-fit: cover; border-radius: var(--r-xl); display: block; }
        .nbhd-item { display: flex; gap: 16px; align-items: flex-start; margin-top: 28px; }
        .nbhd-num {
            flex-shrink: 0;
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--blue-lt);
            color: var(--blue);
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px; font-weight: 600; font-style: italic;
            display: flex; align-items: center; justify-content: center;
        }
        .nbhd-item h4 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .nbhd-item p { font-size: 13px; color: var(--ink-60); font-weight: 300; line-height: 1.6; }

        /* ── TESTIMONIAL ── */
        .testimonial-section { background: var(--ink); padding: 96px 48px; }
        @media (max-width: 768px) { .testimonial-section { padding: 64px 24px; } }
        .testimonial-inner { max-width: 660px; margin: 0 auto; text-align: center; }
        .test-eyebrow { font-size: 10px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: var(--blue-lt); margin-bottom: 40px; }
        .test-avatars { display: flex; justify-content: center; margin-bottom: 32px; }
        .test-av {
            width: 42px; height: 42px; border-radius: 50%; border: 2.5px solid var(--ink);
            background: #2a3b52;
            display: flex; align-items: center; justify-content: center;
            margin-left: -10px;
        }
        .test-av:first-child { margin-left: 0; }
        .test-av i { font-size: 16px; color: rgba(255,255,255,0.4); }
        .test-quote {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(22px, 3.5vw, 34px);
            font-weight: 300;
            font-style: italic;
            line-height: 1.45;
            color: var(--white);
            margin-bottom: 32px;
        }
        .test-quote::before { content: '\201C'; color: var(--blue-lt); }
        .test-quote::after  { content: '\201D'; color: var(--blue-lt); }
        .test-author strong { display: block; font-size: 14px; font-weight: 600; color: var(--white); margin-bottom: 4px; }
        .test-author span { font-size: 12px; color: rgba(255,255,255,0.45); }
        .test-stars { display: flex; justify-content: center; gap: 4px; margin-bottom: 16px; }
        .test-stars i { font-size: 13px; color: var(--blue-lt); }

        /* ── NAV BTNS ── */
        .nav-btns { display: flex; gap: 8px; }
        .nav-btn {
            width: 36px; height: 36px; border-radius: 50%;
            border: 1.5px solid var(--ink-20);
            background: transparent;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all var(--transition);
        }
        .nav-btn:hover { background: var(--ink); border-color: var(--ink); color: var(--white); }
        .nav-btn i { font-size: 11px; }

        /* ── FADE UP ANIM ── */
        .fade-up { opacity: 0; transform: translateY(24px); transition: opacity 0.6s, transform 0.6s; }
        .fade-up.visible { opacity: 1; transform: translateY(0); }

        /* ── AUTH MODAL ── */
        .auth-overlay {
            position: fixed; inset: 0;
            background: rgba(15,17,23,0.6);
            backdrop-filter: blur(6px);
            z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .auth-overlay.active { opacity: 1; visibility: visible; }
        .auth-modal {
            background: var(--white);
            border-radius: var(--r-xl);
            width: 100%; max-width: 440px;
            box-shadow: 0 32px 80px rgba(15,17,23,0.3);
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }
        .auth-overlay.active .auth-modal { transform: translateY(0) scale(1); }
        .auth-top {
            display: flex; align-items: center; justify-content: center;
            padding: 18px; border-bottom: 1px solid var(--ink-20);
            position: relative;
        }
        .auth-top h3 { font-size: 14px; font-weight: 600; letter-spacing: 0.03em; }
        .auth-close {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            background: none; border: none; width: 32px; height: 32px;
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; color: var(--ink-60);
            transition: background var(--transition);
        }
        .auth-close:hover { background: var(--cream); }
        .auth-body { padding: 32px; }
        .auth-title { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 400; margin-bottom: 28px; }
        .fg { margin-bottom: 18px; }
        .fg label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-60); margin-bottom: 7px; }
        .fg input {
            width: 100%; padding: 14px 16px;
            border: 1.5px solid var(--ink-20); border-radius: var(--r-sm);
            font-family: 'Outfit', sans-serif; font-size: 14px;
            color: var(--ink); background: var(--cream);
            outline: none; transition: border-color var(--transition), background var(--transition);
        }
        .fg input:focus { border-color: var(--blue-md); background: var(--white); }
        .btn-auth-main {
            width: 100%; padding: 14px; border: none; border-radius: var(--r-sm);
            background: var(--ink); color: var(--white);
            font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.05em;
            cursor: pointer; margin-bottom: 24px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background var(--transition);
        }
        .btn-auth-main:hover { background: var(--blue); }
        .btn-auth-main:disabled { opacity: 0.5; cursor: not-allowed; }
        .divider {
            display: flex; align-items: center; gap: 12px;
            font-size: 12px; color: var(--ink-60); margin-bottom: 20px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--ink-20); }
        .social-btns { display: flex; gap: 10px; margin-bottom: 20px; }
        .btn-social {
            flex: 1; height: 44px; border: 1.5px solid var(--ink-20);
            border-radius: var(--r-sm); background: var(--white);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background var(--transition), border-color var(--transition);
            font-size: 17px;
        }
        .btn-social:hover { background: var(--cream); border-color: var(--ink-60); }
        .auth-note { font-size: 11px; color: var(--ink-60); line-height: 1.7; }
        .auth-note a { color: var(--blue-md); text-decoration: underline; }
        /* OTP */
        .otp-row { display: flex; gap: 10px; justify-content: center; margin: 20px 0 28px; }
        .otp-row input {
            width: 56px; height: 64px; text-align: center;
            font-size: 30px; font-weight: 600; letter-spacing: 0;
            border: 1.5px solid var(--ink-20); border-radius: var(--r-sm);
            background: var(--cream); color: var(--ink);
            font-family: 'Outfit', sans-serif; outline: none;
            transition: border-color var(--transition);
        }
        .otp-row input:focus { border-color: var(--blue-md); background: var(--white); }
        .otp-note { font-size: 13px; color: var(--ink-60); margin-bottom: 22px; font-weight: 300; line-height: 1.6; }
        .otp-note strong { color: var(--ink); font-weight: 600; }
        .resend { font-size: 13px; font-weight: 600; text-decoration: underline; cursor: pointer; display: inline-block; margin-bottom: 22px; color: var(--ink-60); }
        .resend:hover { color: var(--ink); }
        .modal-step { display: none; }
        .modal-step.active { display: block; animation: stepIn 0.3s; }
        @keyframes stepIn { from { opacity:0; transform: translateX(12px); } to { opacity:1; transform: none; } }
    </style>
    @endpush

    {{-- ══════════════ HERO ══════════════ --}}
    <section class="hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/luxury-decor.jpg') }}');"></div>
        <div class="hero-vignette"></div>
        <div class="hero-content">
            <div class="hero-eyebrow"><span>Premium Serviced Apartments</span></div>
            <h1 class="hero-title">Your Home<br><em>Away From Home</em></h1>
            <p class="hero-sub">Discover our exclusive fully-furnished luxury apartments</p>

            <div class="search-wrap">
                <div class="search-box">
                    <form action="{{ route('apartments.search') }}" method="GET">
                        <div class="search-label-row">
                            <div class="sf">
                                <label>Select Apartment</label>
                                <select name="apartment_id">
                                    <option value="">All Apartments</option>
                                    @foreach($apartments as $apt)
                                        <option value="{{ $apt->id }}">{{ $apt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sf">
                                <label>Check In</label>
                                <input type="date" name="check_in" required>
                            </div>
                            <div class="sf">
                                <label>Check Out</label>
                                <input type="date" name="check_out" required>
                            </div>
                            <div class="sf">
                                <label>Guests</label>
                                <select name="guests">
                                    <option value="2">1–2 Guests</option>
                                    <option value="4">3–4 Guests</option>
                                    <option value="6">5+ Guests</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-check">
                                <i class="fa-solid fa-calendar-check"></i> Check Availability
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ WHY STAY ══════════════ --}}
    <section class="section">
        <div class="container">
            <div class="sec-head">
                <div>
                    <p class="sec-eyebrow">Our Promise</p>
                    <h2 class="sec-title">Why Stay With Us</h2>
                </div>
            </div>
            <div class="trust-grid">
                <div class="trust-card fade-up">
                    <div class="trust-icon"><i class="fa-solid fa-couch"></i></div>
                    <h3>Premium Furnishings</h3>
                    <p>Every apartment is curated with luxury, comfort, and sophisticated style in mind.</p>
                </div>
                <div class="trust-card fade-up" style="transition-delay:.1s">
                    <div class="trust-icon"><i class="fa-solid fa-wifi"></i></div>
                    <h3>High-Speed WiFi</h3>
                    <p>Stay seamlessly connected or work remotely with complimentary fast internet.</p>
                </div>
                <div class="trust-card fade-up" style="transition-delay:.2s">
                    <div class="trust-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <h3>Prime Locations</h3>
                    <p>All properties are situated in the best, safest neighbourhoods of the city.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ APARTMENTS GRID ══════════════ --}}
    <section class="section section-alt">
        <div class="container">
            <div class="sec-head">
                <div>
                    <p class="sec-eyebrow">Properties</p>
                    <h2 class="sec-title">Our Exclusive Apartments</h2>
                    <p class="sec-sub">Browse our beautifully designed spaces</p>
                </div>
                <a href="#" class="sec-link">View all <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="filter-tabs">
                <button class="filter-tab active">All</button>
                <button class="filter-tab">1 Bedroom</button>
                <button class="filter-tab">2 Bedrooms</button>
                <button class="filter-tab">Penthouses</button>
            </div>

            <div class="dest-grid">
                @forelse($apartments as $i => $apt)
                <a href="{{ route('apartments.show', $apt->id) }}" class="dest-card fade-up" style="transition-delay:{{ $i * 0.08 }}s;">
                    @if($apt->images->count() > 0)
                        <img src="{{ \Storage::url($apt->images->first()->image_path) }}" class="dest-card-img" alt="{{ $apt->name }}">
                    @else
                        <div class="dest-placeholder"><i class="fa-solid fa-building"></i></div>
                    @endif
                    @if($i == 0)<div class="dest-badge">Popular</div>
                    @elseif($i == 1)<div class="dest-badge">New</div>
                    @endif
                    <div class="dest-overlay">
                        <h3>{{ $apt->name }}</h3>
                        <p>{{ ucwords($apt->floor) }} Floor &bull; {{ $apt->bedrooms }} Bed &bull; {{ $apt->bathrooms }} Bath</p>
                    </div>
                </a>
                @empty
                <div style="grid-column:span 4; text-align:center; padding:48px 24px; background:var(--cream); border-radius:var(--r-lg); border:1px dashed var(--ink-20);">
                    <i class="fa-solid fa-hotel" style="font-size:40px; color:var(--ink-20); margin-bottom:14px; display:block;"></i>
                    <p style="color:var(--ink-60); font-size:14px;">No apartments registered yet. Please check back soon.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ══════════════ AVAILABLE THIS WEEKEND ══════════════ --}}
    <section class="section">
        <div class="container">
            <div class="sec-head">
                <div>
                    <p class="sec-eyebrow">Last Minute</p>
                    <h2 class="sec-title">Available This Weekend</h2>
                    <p class="sec-sub">Book your luxury last-minute getaway</p>
                </div>
                <div class="nav-btns">
                    <button class="nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>

            <div class="deals-grid">
                @foreach($apartments->take(4) as $i => $apt)
                <a href="{{ route('apartments.show', $apt->id) }}" class="deal-card fade-up" style="transition-delay:{{ $i * 0.08 }}s;">
                    <div class="deal-img-wrap">
                        @if($apt->images->count() > 0)
                            <img src="{{ \Storage::url($apt->images->first()->image_path) }}" alt="{{ $apt->name }}">
                        @else
                            <div class="deal-no-img"><i class="fa-solid fa-building"></i></div>
                        @endif
                        <button class="deal-fav" onclick="event.preventDefault(); this.querySelector('i').classList.toggle('fa-regular'); this.querySelector('i').classList.toggle('fa-solid');">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                        <span class="deal-status status-available">Available</span>
                    </div>
                    <div class="deal-body">
                        <h3>{{ $apt->name }}</h3>
                        <div class="deal-location"><i class="fa-solid fa-map-pin"></i> {{ $apt->floor }} Floor &bull; Central</div>
                        <div class="deal-foot">
                            <div class="deal-rating"><i class="fa-solid fa-star"></i> 5.0</div>
                            <div class="deal-price"><strong>${{ number_format($apt->price_per_night) }}</strong><span style="font-size:11px; color:var(--ink-60)">/night</span></div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════ AMENITIES ══════════════ --}}
    <section class="section section-alt" id="amenities">
        <div class="container">
            <div class="sec-head" style="justify-content:center; text-align:center;">
                <div>
                    <p class="sec-eyebrow">Inclusions</p>
                    <h2 class="sec-title">World-Class Amenities</h2>
                    <p class="sec-sub">Designed to provide the ultimate comfort and convenience</p>
                </div>
            </div>
            <div class="amenity-grid">
                <div class="amenity-card fade-up">
                    <div class="amenity-icon"><i class="fa-solid fa-person-swimming"></i></div>
                    <h3>Private Pools</h3>
                    <p>Crystal-clear infinity pools with breathtaking views.</p>
                </div>
                <div class="amenity-card fade-up" style="transition-delay:.08s">
                    <div class="amenity-icon"><i class="fa-solid fa-spa"></i></div>
                    <h3>Luxury Spa</h3>
                    <p>In-house spa treatments by world-renowned therapists.</p>
                </div>
                <div class="amenity-card fade-up" style="transition-delay:.16s">
                    <div class="amenity-icon"><i class="fa-solid fa-utensils"></i></div>
                    <h3>Private Chef</h3>
                    <p>Fine dining prepared in your own kitchen by top chefs.</p>
                </div>
                <div class="amenity-card fade-up" style="transition-delay:.24s">
                    <div class="amenity-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3>24/7 Security</h3>
                    <p>Your safety is our priority with around-the-clock protection.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ NEIGHBOURHOOD ══════════════ --}}
    <section class="section" id="neighborhood">
        <div class="container">
            <div class="nbhd-wrap">
                <div>
                    <img
                        src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1600&auto=format&fit=crop"
                        class="nbhd-img" alt="City neighbourhood">
                </div>
                <div>
                    <p class="sec-eyebrow">The Area</p>
                    <h2 class="sec-title" style="font-size:clamp(32px,4vw,52px);">Explore the Vibrant<br>Heart of the City</h2>
                    <p style="font-size:15px; color:var(--ink-60); font-weight:300; line-height:1.8; margin: 20px 0 8px;">
                        CoastalCharmz properties are perfectly positioned to give you access to the best the city has to offer — from Michelin-starred restaurants to hidden cultural gems, everything is within reach.
                    </p>
                    <div class="nbhd-item fade-up">
                        <div class="nbhd-num">1</div>
                        <div>
                            <h4>Fine Dining District</h4>
                            <p>The world's best culinary experiences are just blocks away.</p>
                        </div>
                    </div>
                    <div class="nbhd-item fade-up" style="transition-delay:.1s">
                        <div class="nbhd-num">2</div>
                        <div>
                            <h4>Elite Shopping</h4>
                            <p>Access to luxury brands and boutiques along the main avenue.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ TESTIMONIAL ══════════════ --}}
    <section class="testimonial-section">
        <div class="container">
            <div class="testimonial-inner">
                <p class="test-eyebrow">Guest Experiences</p>
                <div class="test-avatars">
                    @for($i = 0; $i < 5; $i++)
                    <div class="test-av"><i class="fa-solid fa-user"></i></div>
                    @endfor
                </div>
                <div class="test-stars">
                    @for($i = 0; $i < 5; $i++)<i class="fa-solid fa-star"></i>@endfor
                </div>
                <p class="test-quote">This place is exactly like the picture posted on Coastalcharmz. Great service, we had a great stay!</p>
                <div class="test-author">
                    <strong>Ethan Rogrinho</strong>
                    <span>🇧🇷 Brazil · Verified Guest</span>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        // Scroll observer
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));

        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                this.closest('.filter-tabs').querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ── AUTH MODAL ──
        let authOverlay, step1, step2, emailInput, displayEmail, btnContinue, otpInputs;

        document.addEventListener('DOMContentLoaded', () => {
            authOverlay  = document.getElementById('authOverlay');
            step1        = document.getElementById('authStep1');
            step2        = document.getElementById('authStep2');
            emailInput   = document.getElementById('authEmail');
            displayEmail = document.getElementById('displayEmail');
            btnContinue  = document.getElementById('btnContinueEmail');

            if (authOverlay) authOverlay.addEventListener('click', e => { if (e.target === authOverlay) window.closeAuthModal(); });
            if (emailInput) emailInput.addEventListener('input', e => { btnContinue.disabled = !e.target.value.trim(); });

            otpInputs = document.querySelectorAll('.otp-row input');
            otpInputs.forEach((inp, i) => {
                inp.addEventListener('keyup', e => {
                    if (e.key >= 0 && e.key <= 9) {
                        if (i < otpInputs.length - 1) otpInputs[i+1].focus();
                        else {
                            const code = Array.from(otpInputs).map(x => x.value).join('');
                            if (code.length === 4) window.confirmOtp();
                        }
                    } else if (e.key === 'Backspace' && i > 0 && inp.value === '') {
                        otpInputs[i-1].focus();
                    }
                });
            });
        });

        window.openAuthModal = function() {
            if (!authOverlay) return;
            authOverlay.classList.add('active');
            step1.classList.add('active');
            step2.classList.remove('active');
            emailInput.value = '';
            btnContinue.disabled = true;
        };
        window.closeAuthModal = function() {
            if (!authOverlay) return;
            authOverlay.classList.remove('active');
            setTimeout(() => { step1.classList.remove('active'); step2.classList.remove('active'); }, 300);
        };
        window.goStep2 = async function() {
            const email = emailInput.value.trim();
            if (!email) return;
            btnContinue.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…';
            btnContinue.disabled = true;
            try {
                const res  = await fetch('/auth/otp/send', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({email}) });
                const data = await res.json();
                btnContinue.innerHTML = 'Continue with email';
                btnContinue.disabled = false;
                if (res.ok && data.success) {
                    step1.classList.remove('active'); step2.classList.add('active');
                    displayEmail.innerText = email;
                    if (otpInputs) { otpInputs.forEach(i => i.value = ''); setTimeout(() => otpInputs[0].focus(), 100); }
                } else { alert(data.message || 'Error sending code.'); }
            } catch(err) { btnContinue.innerHTML = 'Continue with email'; btnContinue.disabled = false; alert('Something went wrong.'); }
        };
        window.confirmOtp = async function() {
            const btn  = document.getElementById('btnConfirmCode');
            const code = Array.from(otpInputs).map(i => i.value).join('');
            if (code.length !== 4) return;
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Confirming…';
            try {
                const res  = await fetch('/auth/otp/verify', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({email: emailInput.value.trim(), code}) });
                const data = await res.json();
                if (res.ok && data.success) { window.location.href = data.redirect; }
                else { btn.innerHTML = orig; alert(data.message || 'Invalid code. Please try again.'); }
            } catch(err) { btn.innerHTML = orig; alert('Something went wrong.'); }
        };
    </script>
    @endpush

    {{-- ══════════════ AUTH MODAL HTML ══════════════ --}}
    <div class="auth-overlay" id="authOverlay">
        <div class="auth-modal">
            <!-- Step 1 -->
            <div class="modal-step active" id="authStep1">
                <div class="auth-top">
                    <button class="auth-close" onclick="closeAuthModal()"><i class="fa-solid fa-xmark"></i></button>
                    <h3>Sign in or create account</h3>
                </div>
                <div class="auth-body">
                    <h2 class="auth-title">Welcome to CoastalCharmz</h2>
                    <div class="fg">
                        <label>Email address</label>
                        <input type="email" id="authEmail" placeholder="mail@example.com" autocomplete="email">
                    </div>
                    <button class="btn-auth-main" id="btnContinueEmail" disabled onclick="goStep2()">
                        Continue with email
                    </button>
                    <div class="divider">or</div>
                    <div class="social-btns">
                        <a href="{{ route('login') }}" class="btn-social" title="Password login" style="text-decoration:none;">
                            <i class="fa-solid fa-key" style="color:var(--ink);"></i>
                        </a>
                        <button class="btn-social" title="Google"><i class="fa-brands fa-google" style="color:#DB4437;"></i></button>
                        <button class="btn-social" title="Apple"><i class="fa-brands fa-apple"></i></button>
                        <button class="btn-social" title="Facebook"><i class="fa-brands fa-facebook" style="color:#1877F2;"></i></button>
                    </div>
                    <p class="auth-note">By continuing, you agree to our <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="modal-step" id="authStep2">
                <div class="auth-top">
                    <button class="auth-close" onclick="document.getElementById('authStep2').classList.remove('active'); document.getElementById('authStep1').classList.add('active');"><i class="fa-solid fa-chevron-left"></i></button>
                    <h3>Verify your identity</h3>
                </div>
                <div class="auth-body" style="text-align:center; padding-top:24px;">
                    <h2 style="font-size:20px; font-weight:600; margin-bottom:12px;">Enter verification code</h2>
                    <p class="otp-note">We sent a 4-digit code to:<br><strong id="displayEmail">mail@example.com</strong></p>
                    <div class="otp-row">
                        <input type="text" maxlength="1" placeholder="–">
                        <input type="text" maxlength="1" placeholder="–">
                        <input type="text" maxlength="1" placeholder="–">
                        <input type="text" maxlength="1" placeholder="–">
                    </div>
                    <span class="resend">Get a new code</span>
                    <button class="btn-auth-main" id="btnConfirmCode" onclick="confirmOtp()">Continue</button>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>