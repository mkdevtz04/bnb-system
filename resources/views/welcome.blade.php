<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coastalcharmz – Your Trip Starts Here</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: #fff;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 40px; height: 64px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .navbar-logo { display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .navbar-logo .logo-icon { width: 32px; height: 32px; background: var(--blue); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .navbar-logo .logo-icon svg { width: 20px; height: 20px; fill: #fff; }
        .navbar-logo span { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: var(--blue); }
        .navbar-logo span em { color: var(--gold); font-style: normal; }
        .navbar-links { display: flex; align-items: center; gap: 28px; }
        .navbar-links a { text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; transition: color .2s; }
        .navbar-links a:hover { color: var(--blue); }
        .navbar-actions { display: flex; align-items: center; gap: 12px; }
        .btn-ghost { background: none; border: none; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500; color: var(--muted); padding: 8px 14px; border-radius: 8px; transition: background .2s; }
        .btn-ghost:hover { background: var(--blue-light); color: var(--blue); }
        .btn-primary { background: var(--blue); color: #fff; border: none; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600; padding: 10px 22px; border-radius: 10px; transition: background .2s, transform .15s; }
        .btn-primary:hover { background: var(--blue-dark); transform: translateY(-1px); }

        /* ─── HERO ─── */
        .hero {
            margin-top: 64px;
            position: relative;
            min-height: 540px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #0a2540 0%, #1A6EFF 60%, #4f99ff 100%);
        }
        .hero-bg-overlay {
            position: absolute; inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 1;
        }
        .hero-shapes { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
        .hero-shapes span {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.06);
            animation: floatShape 8s ease-in-out infinite;
        }
        .hero-shapes span:nth-child(1) { width: 320px; height: 320px; top: -80px; right: -60px; animation-delay: 0s; }
        .hero-shapes span:nth-child(2) { width: 200px; height: 200px; bottom: -40px; left: 10%; animation-delay: 2s; }
        .hero-shapes span:nth-child(3) { width: 120px; height: 120px; top: 30%; left: 5%; animation-delay: 4s; }
        @keyframes floatShape { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

        .hero-content { position: relative; text-align: center; color: #010203; padding: 60px 20px 30px; }
        .hero-content h1 { font-family: 'Playfair Display', serif; font-size: clamp(32px, 5vw, 58px); font-weight: 700; line-height: 1.15; margin-bottom: 14px; text-shadow: 0 2px 20px rgba(0,0,0,0.3); }
        .hero-content p { font-size: 17px; font-weight: 300; opacity: 0.88; margin-bottom: 36px; letter-spacing: 0.01em; }

        /* ─── SEARCH BOX ─── */
        .search-box {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.18);
            padding: 10px 16px 16px;
            max-width: 880px; margin: 0 auto;
            position: relative; z-index: 2;
        }
        .search-tabs { display: flex; gap: 6px; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .search-tab { background: none; border: none; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; color: var(--muted); padding: 6px 14px; border-radius: 8px; display: flex; align-items: center; gap: 6px; transition: all .2s; }
        .search-tab.active { background: var(--blue-light); color: var(--blue); }
        .search-tab i { font-size: 12px; }
        .search-fields { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr auto; gap: 10px; align-items: end; }
        .search-field label { display: block; font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .search-field input, .search-field select {
            width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--text);
            background: #fff; outline: none; transition: border-color .2s;
        }
        .search-field input:focus, .search-field select:focus { border-color: var(--blue); }
        .btn-search { background: var(--blue); color: #fff; border: none; cursor: pointer; border-radius: 12px; padding: 12px 28px; font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: background .2s, transform .15s; white-space: nowrap; }
        .btn-search:hover { background: var(--blue-dark); transform: translateY(-1px); }

        /* ─── SECTION COMMONS ─── */
        .section { padding: 64px 40px; }
        .section-alt { background: var(--bg); }
        .section-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 32px; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: var(--text); }
        .section-subtitle { font-size: 14px; color: var(--muted); margin-top: 4px; }
        .section-link { font-size: 14px; font-weight: 600; color: var(--blue); text-decoration: none; display: flex; align-items: center; gap: 4px; }
        .section-link:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 0 auto; }

        /* ─── WHY TRUST ─── */
        .trust-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .trust-card { text-align: center; padding: 32px 24px; border-radius: var(--radius); border: 1.5px solid var(--border); transition: box-shadow .25s, transform .25s; }
        .trust-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-4px); }
        .trust-icon { width: 56px; height: 56px; margin: 0 auto 16px; background: var(--blue-light); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
        .trust-icon i { font-size: 22px; color: var(--blue); }
        .trust-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .trust-card p { font-size: 14px; color: var(--muted); line-height: 1.6; }

        /* ─── FILTER TABS ─── */
        .filter-tabs { display: flex; gap: 8px; margin-bottom: 28px; flex-wrap: wrap; }
        .filter-tab { padding: 8px 18px; border-radius: 40px; border: 1.5px solid var(--border); background: #fff; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; cursor: pointer; transition: all .2s; color: var(--muted); }
        .filter-tab.active, .filter-tab:hover { background: var(--blue); color: #fff; border-color: var(--blue); }

        /* ─── DESTINATION CARDS ─── */
        .dest-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .dest-card { border-radius: var(--radius); overflow: hidden; position: relative; aspect-ratio: 3/4; background: linear-gradient(135deg, #c9d6e8, #a8bbd6); cursor: pointer; transition: transform .3s, box-shadow .3s; }
        .dest-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 16px 48px rgba(0,0,0,0.18); }
        .dest-card-img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s; }
        .dest-card:hover .dest-card-img { transform: scale(1.07); }
        .dest-card-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, var(--blue-light), #c8d8f8); display: flex; align-items: center; justify-content: center; }
        .dest-card-placeholder i { font-size: 48px; color: var(--blue); opacity: 0.4; }
        .dest-card-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 24px 18px 18px; background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 100%); color: #fff; }
        .dest-card-overlay h3 { font-size: 17px; font-weight: 700; }
        .dest-card-overlay p { font-size: 12px; opacity: 0.85; margin-top: 2px; }
        .dest-badge { position: absolute; top: 14px; left: 14px; background: var(--gold); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }

        /* ─── DEALS ─── */
        .deals-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .deal-card { border-radius: var(--radius); overflow: hidden; border: 1.5px solid var(--border); background: #fff; transition: box-shadow .25s, transform .25s; cursor: pointer; }
        .deal-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-4px); }
        .deal-img-wrap { aspect-ratio: 16/10; background: linear-gradient(135deg, #d4e4f5, #b8cfe8); overflow: hidden; position: relative; }
        .deal-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .deal-card:hover .deal-img-wrap img { transform: scale(1.07); }
        .deal-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .deal-img-placeholder i { font-size: 36px; color: var(--blue); opacity: 0.35; }
        .deal-fav { position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; background: rgba(255,255,255,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .deal-fav i { font-size: 14px; color: #e74c3c; }
        .deal-status { position: absolute; top: 10px; left: 10px; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
        .status-available { background: #d4f5e8; color: #16a34a; }
        .status-limited { background: #fff3cd; color: #d97706; }
        .deal-body { padding: 14px 16px; }
        .deal-body h3 { font-size: 14px; font-weight: 700; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .deal-location { font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 4px; margin-bottom: 10px; }
        .deal-footer { display: flex; align-items: center; justify-content: space-between; }
        .deal-rating { display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; }
        .deal-rating i { color: var(--gold); font-size: 12px; }
        .deal-price { font-size: 13px; color: var(--muted); }
        .deal-price strong { font-size: 17px; color: var(--blue); font-weight: 700; }

        /* ─── PROMO BANDS ─── */
        .promo-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .promo-card { background: var(--blue-light); border-radius: var(--radius); padding: 22px 20px; border: 1.5px solid #d0e3ff; }
        .promo-card h4 { font-size: 14px; font-weight: 700; margin-bottom: 6px; color: var(--blue); }
        .promo-card p { font-size: 13px; color: var(--muted); line-height: 1.5; }

        /* ─── SIGHTS ─── */
        .sights-grid { display: grid; grid-template-columns: repeat(3, 1fr) repeat(3, 1fr); grid-template-rows: 1fr 1fr; gap: 14px; }
        .sight-card { border-radius: var(--radius); overflow: hidden; position: relative; cursor: pointer; transition: transform .3s; }
        .sight-card:nth-child(1), .sight-card:nth-child(4) { grid-column: span 2; }
        .sight-card:hover { transform: scale(1.02); }
        .sight-img-wrap { aspect-ratio: 16/9; background: linear-gradient(135deg, #c5d8ee, #a3bcd6); overflow: hidden; }
        .sight-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .sight-img-placeholder i { font-size: 40px; color: var(--blue); opacity: 0.3; }
        .sight-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .sight-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 14px; background: linear-gradient(to top, rgba(0,0,0,0.65), transparent); color: #fff; }
        .sight-overlay h3 { font-size: 15px; font-weight: 700; }
        .sight-flag { font-size: 14px; margin-left: 4px; }

        /* ─── BARCELONA THINGS ─── */
        .things-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
        .thing-card { border-radius: var(--radius); overflow: hidden; text-align: center; cursor: pointer; transition: transform .25s; }
        .thing-card:hover { transform: translateY(-4px); }
        .thing-img-wrap { aspect-ratio: 1; background: linear-gradient(135deg, #d8e8f5, #b8cde6); border-radius: var(--radius); overflow: hidden; margin-bottom: 10px; }
        .thing-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .thing-img-placeholder i { font-size: 32px; color: var(--blue); opacity: 0.35; }
        .thing-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .thing-card span { font-size: 13px; font-weight: 500; color: var(--text); }

        /* ─── EXPLORE IN MOTION ─── */
        .motion-section { background: #0a1a2e; color: #fff; padding: 64px 40px; }
        .motion-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; }
        .motion-content .section-title { color: #fff; font-size: 36px; line-height: 1.2; }
        .motion-content p { color: rgba(255,255,255,0.7); font-size: 15px; margin: 16px 0 28px; }
        .btn-outline-white { background: none; border: 2px solid rgba(255,255,255,0.4); color: #fff; border-radius: 10px; padding: 12px 26px; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; }
        .btn-outline-white:hover { background: rgba(255,255,255,0.1); border-color: #fff; }
        .motion-images { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .motion-img-card { border-radius: 12px; overflow: hidden; aspect-ratio: 4/3; background: linear-gradient(135deg, #1a3a5c, #2a5080); position: relative; }
        .motion-img-card.tall { aspect-ratio: 3/4; }
        .motion-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .motion-img-placeholder i { font-size: 36px; color: rgba(255,255,255,0.3); }
        .motion-img-card img { width: 100%; height: 100%; object-fit: cover; }
        .motion-img-label { position: absolute; bottom: 10px; left: 12px; color: #fff; font-size: 12px; font-weight: 600; }
        .motion-img-rating { display: flex; gap: 2px; margin-top: 2px; }
        .motion-img-rating i { font-size: 10px; color: var(--gold); }

        /* ─── HOMES GUESTS LOVE ─── */
        .homes-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

        /* ─── TESTIMONIAL ─── */
        .testimonial-section { background: var(--bg); padding: 64px 40px; text-align: center; }
        .testimonial-inner { max-width: 640px; margin: 0 auto; }
        .testimonial-top { font-size: 13px; color: var(--muted); margin-bottom: 24px; }
        .testimonial-avatars { display: flex; justify-content: center; gap: -10px; margin-bottom: 20px; }
        .testimonial-avatars img, .testimonial-avatar-placeholder {
            width: 44px; height: 44px; border-radius: 50%; border: 3px solid #fff;
            margin-left: -10px; background: linear-gradient(135deg, var(--blue-light), #b8d0ff);
            display: flex; align-items: center; justify-content: center;
        }
        .testimonial-avatars .testimonial-avatar-placeholder:first-child { margin-left: 0; }
        .testimonial-avatar-placeholder i { font-size: 18px; color: var(--blue); }
        .testimonial-main { position: relative; padding: 32px; background: #fff; border-radius: 20px; box-shadow: var(--shadow); }
        .testimonial-main::before { content: '\201C'; position: absolute; top: -16px; left: 28px; font-size: 80px; color: var(--blue); font-family: 'Playfair Display', serif; line-height: 1; opacity: 0.15; }
        .testimonial-main p { font-size: 16px; color: var(--text); line-height: 1.7; font-style: italic; }
        .testimonial-author { margin-top: 20px; }
        .testimonial-author strong { display: block; font-weight: 700; font-size: 15px; }
        .testimonial-author span { font-size: 13px; color: var(--muted); }
        .testimonial-stars { display: flex; justify-content: center; gap: 3px; margin-top: 8px; }
        .testimonial-stars i { color: var(--gold); font-size: 14px; }

        /* ─── FOOTER ─── */
        footer { background: #0d1b2e; color: rgba(255,255,255,0.7); padding: 60px 40px 30px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-top { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 48px; }
        .footer-brand .footer-logo { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; text-decoration: none; }
        .footer-brand .footer-logo span { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; color: #fff; }
        .footer-brand p { font-size: 13px; line-height: 1.7; max-width: 220px; }
        .footer-apps { display: flex; gap: 10px; margin-top: 20px; }
        .store-btn { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 14px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: background .2s; }
        .store-btn:hover { background: rgba(255,255,255,0.14); }
        .store-btn i { font-size: 18px; color: #fff; }
        .store-btn div { font-size: 10px; color: rgba(255,255,255,0.6); line-height: 1.3; }
        .store-btn div strong { display: block; font-size: 13px; color: #fff; }
        .footer-col h5 { font-size: 13px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 16px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { text-decoration: none; color: rgba(255,255,255,0.6); font-size: 13px; transition: color .2s; }
        .footer-col ul li a:hover { color: #fff; }
        .footer-col .phone { font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .footer-col .email-link { font-size: 13px; color: var(--blue); text-decoration: none; }
        .footer-social { display: flex; gap: 10px; margin-top: 16px; }
        .social-btn { width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .2s; text-decoration: none; color: rgba(255,255,255,0.7); }
        .social-btn:hover { background: var(--blue); color: #fff; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 24px; display: flex; align-items: center; justify-content: space-between; }
        .footer-bottom p { font-size: 12px; }
        .footer-bottom-links { display: flex; gap: 20px; }
        .footer-bottom-links a { font-size: 12px; color: rgba(255,255,255,0.5); text-decoration: none; }
        .footer-bottom-links a:hover { color: #fff; }
        .payment-icons { display: flex; gap: 8px; align-items: center; }
        .pay-icon { background: rgba(255,255,255,0.1); border-radius: 4px; padding: 4px 10px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); }

        /* ─── CAROUSEL ARROWS ─── */
        .section-nav { display: flex; gap: 8px; }
        .nav-btn { width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid var(--border); background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .2s; }
        .nav-btn:hover { background: var(--blue); border-color: var(--blue); color: #fff; }
        .nav-btn i { font-size: 12px; color: inherit; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1024px) {
            .dest-grid, .deals-grid, .homes-grid { grid-template-columns: repeat(2, 1fr); }
            .things-grid { grid-template-columns: repeat(3, 1fr); }
            .footer-top { grid-template-columns: 1fr 1fr; }
            .motion-inner { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .navbar-links { display: none; }
            .section { padding: 48px 20px; }
            .search-fields { grid-template-columns: 1fr 1fr; }
            .trust-grid, .promo-grid { grid-template-columns: 1fr; }
            .sights-grid { grid-template-columns: 1fr 1fr; }
            .sights-grid .sight-card:nth-child(1), .sights-grid .sight-card:nth-child(4) { grid-column: span 1; }
            .footer-top { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; gap: 16px; }
        }

        /* ─── ANIMATIONS ─── */
        /* ─── AUTH MODAL ─── */
        .auth-modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
            z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden; transition: opacity .3s, visibility .3s;
        }
        .auth-modal-overlay.active { opacity: 1; visibility: visible; }
        .auth-modal {
            background: #fff; border-radius: 20px;
            width: 100%; max-width: 440px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.2);
            transform: translateY(20px) scale(0.98);
            transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .auth-modal-overlay.active .auth-modal { transform: translateY(0) scale(1); }
        .auth-header {
            display: flex; align-items: center; justify-content: center;
            padding: 16px; border-bottom: 1px solid var(--border);
            position: relative;
        }
        .auth-header h3 { font-size: 16px; font-weight: 700; }
        .auth-close {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            background: none; border: none; font-size: 16px; color: var(--muted);
            cursor: pointer; width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: background .2s;
        }
        .auth-close:hover { background: var(--bg); color: var(--text); }
        .auth-body { padding: 32px; }
        .auth-title { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text); }
        .form-group input {
            width: 100%; padding: 14px 16px; border: 1.5px solid var(--border);
            border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: 15px;
            outline: none; transition: border-color .2s;
        }
        .form-group input:focus { border-color: var(--blue); }
        .btn-auth {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            font-family: 'DM Sans', sans-serif; transition: background .2s, transform .2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-auth-primary { background: var(--blue); color: #fff; margin-bottom: 24px; }
        .btn-auth-primary:hover { background: var(--blue-dark); }
        .btn-auth-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .auth-divider {
            display: flex; align-items: center; text-align: center; margin-bottom: 24px;
            color: var(--muted); font-size: 13px;
        }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; border-bottom: 1px solid var(--border); }
        .auth-divider::before { margin-right: 12px; }
        .auth-divider::after { margin-left: 12px; }
        .btn-auth-social { background: #fff; border: 1.5px solid var(--border); color: var(--text); margin-bottom: 16px; font-weight: 500; }
        .btn-auth-social:hover { background: var(--bg); border-color: #d1d5db; }
        .btn-auth-social i { font-size: 18px; }
        .btn-auth-social.btn-apple i { font-size: 20px; }
        .auth-footer { font-size: 12px; color: var(--muted); line-height: 1.6; padding-top: 10px; }
        .auth-footer a { color: var(--blue); text-decoration: underline; font-weight: 500; }

        /* OTP STYLES */
        .otp-inputs { display: flex; gap: 12px; justify-content: center; margin-bottom: 32px; margin-top: 16px; }
        .otp-inputs input {
            width: 56px; height: 64px; text-align: center; font-size: 32px;
            font-weight: 700; border: 1.5px solid var(--border); border-radius: 12px;
            color: var(--text); background: transparent;
        }
        .otp-inputs input:focus { border-color: var(--blue); outline: none; }
        .otp-sub { font-size: 14px; color: var(--text); margin-bottom: 24px; line-height: 1.6; }
        .otp-sub strong { color: var(--text); font-weight: 700; }
        .resend-link { font-size: 14px; font-weight: 600; color: var(--text); text-decoration: underline; cursor: pointer; display: inline-block; margin-bottom: 24px;}
        .resend-link:hover { color: var(--blue); }

        .modal-step { display: none; }
        .modal-step.active { display: block; animation: stepFadeIn .3s; }
        @keyframes stepFadeIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body>

{{-- ═══════════════════ NAVBAR ═══════════════════ --}}
<nav class="navbar">
    <a href="{{ url('/') }}" class="navbar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
            </svg>
        </div>
        <span>Coastal<em>Charmz</em></span>
    </a>

    <div class="navbar-links">
        <a href="#">Our Apartments</a>
        <a href="#">Amenities</a>
        <a href="#">Gallery</a>
        <a href="#">Neighborhood</a>
        <a href="#">Contact Host</a>
    </div>

    <div class="navbar-actions">
        <button class="btn-ghost"><i class="fa-regular fa-circle-question"></i> Help</button>
        @auth
            <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-ghost" style="color: #e74c3c;">Logout</button>
            </form>
        @else
            <a class="btn-primary" href="{{ route('login') }}">Login</a>
            <button class="btn-primary" onclick="openAuthModal()">Sign Up/Login</button>
        @endauth
    </div>
</nav>

{{-- ═══════════════════ HERO ═══════════════════ --}}
<section class="hero">
    <div class="hero-bg-overlay" style="background-image: url('{{ asset('images/luxury-decor.jpg') }}');"></div>
    <div class="hero-shapes"><span></span><span></span><span></span></div>
    <div class="hero-content">
        <h1>Your Home Away From Home</h1>
        <p>Discover our exclusive fully-furnished premium apartments</p>

        <div class="search-box">
            <form action="{{ route('apartments.search') }}" method="GET">
                <div class="search-fields" style="grid-template-columns: 2fr 1.5fr 1.5fr 1.5fr auto;">
                    <div class="search-field">
                        <label>Select Apartment</label>
                        <select name="apartment_id">
                            <option value="">All Apartments</option>
                            @foreach($apartments as $apt)
                                <option value="{{ $apt->id }}">{{ $apt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="search-field">
                        <label>Check In</label>
                        <input type="date" name="check_in" required>
                    </div>
                    <div class="search-field">
                        <label>Check Out</label>
                        <input type="date" name="check_out" required>
                    </div>
                    <div class="search-field">
                        <label>Guests</label>
                        <select name="guests">
                            <option value="2">1-2 Guests</option>
                            <option value="4">3-4 Guests</option>
                            <option value="6">5+ Guests</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-search"><i class="fa-solid fa-calendar-check"></i> Check Availability</button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- ═══════════════════ WHY TRUST ═══════════════════ --}}
<section class="section">
    <div class="container">
        <div class="section-header"><div><h2 class="section-title">Why Stay With Us</h2></div></div>
        <div class="trust-grid">
            <div class="trust-card fade-up">
                <div class="trust-icon"><i class="fa-solid fa-couch"></i></div>
                <h3>Premium Furnishings</h3>
                <p>Every apartment is designed with luxury, comfort, and sophisticated style in mind.</p>
            </div>
            <div class="trust-card fade-up" style="transition-delay:.1s">
                <div class="trust-icon"><i class="fa-solid fa-wifi"></i></div>
                <h3>High-Speed WiFi</h3>
                <p>Stay seamlessly connected or work remotely with our complimentary fast internet.</p>
            </div>
            <div class="trust-card fade-up" style="transition-delay:.2s">
                <div class="trust-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                <h3>Prime Locations</h3>
                <p>All our properties are situated in the best, safest neighborhoods of the city.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ TRENDING DESTINATIONS ═══════════════════ --}}
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Our Exclusive Apartments</h2>
                <p class="section-subtitle">Browse through our beautifully designed spaces</p>
            </div>
            <a href="#" class="section-link">View all properties <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab active">All</button>
            <button class="filter-tab">1 Bedroom</button>
            <button class="filter-tab">2 Bedrooms</button>
            <button class="filter-tab">Penthouses</button>
        </div>

        <div class="dest-grid">
            @forelse($apartments as $i => $apt)
            <a href="{{ route('apartments.show', $apt->id) }}" class="dest-card fade-up" style="transition-delay:{{ $i * 0.1 }}s; text-decoration: none;">
                @if($apt->images->count() > 0)
                    <img src="{{ asset('storage/' . $apt->images->first()->image_path) }}" class="dest-card-img" alt="{{ $apt->name }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                @else
                    <div class="dest-card-placeholder">
                        <i class="fa-solid fa-building"></i>
                    </div>
                @endif
                
                @if($i == 0)
                    <div class="dest-badge">Popular</div>
                @elseif($i == 1)
                    <div class="dest-badge">New</div>
                @endif

                <div class="dest-card-overlay">
                    <h3>{{ $apt->name }}</h3>
                    <p>{{ ucwords($apt->floor) }} Floor • {{ $apt->bedrooms }} Bed • {{ $apt->bathrooms }} Bath</p>
                </div>
            </a>
            @empty
                <div style="grid-column: span 4; text-align: center; padding: 40px; background: var(--blue-light); border-radius: 12px; color: var(--blue);">
                    <i class="fa-solid fa-hotel fa-3x" style="margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>No apartments registered yet. Please check back later!</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ═══════════════════ DEALS FOR THE WEEKEND ═══════════════════ --}}
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Available This Weekend</h2>
                <p class="section-subtitle">Book your last-minute luxury getaway</p>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <div class="section-nav">
                    <button class="nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <div class="deals-grid">
            @foreach($apartments->take(4) as $i => $apt)
            <a href="{{ route('apartments.show', $apt->id) }}" class="deal-card fade-up" style="transition-delay:{{ $i * 0.1 }}s; text-decoration: none;">
                <div class="deal-img-wrap">
                    @if($apt->images->count() > 0)
                        <img src="{{ asset('storage/' . $apt->images->first()->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $apt->name }}">
                    @else
                        <div class="deal-img-placeholder">
                            <i class="fa-solid fa-building"></i>
                        </div>
                    @endif
                    <div class="deal-fav"><i class="fa-solid fa-heart"></i></div>
                    <span class="deal-status status-available">
                        Available
                    </span>
                </div>
                <div class="deal-body">
                    <h3>{{ $apt->name }}</h3>
                    <div class="deal-location"><i class="fa-solid fa-map-pin"></i> {{ $apt->floor }} Floor • Central</div>
                    <div class="deal-footer">
                        <div class="deal-rating">
                            <i class="fa-solid fa-star"></i> 5.0
                        </div>
                        <div class="deal-price">
                            <strong> ${{ number_format($apt->price_per_night) }}</strong><span style="font-size:11px; color:var(--muted)">/night</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>



{{-- ═══════════════════ TESTIMONIAL ═══════════════════ --}}
<section class="testimonial-section">
    <div class="container">
        <div class="testimonial-inner">
            <p class="testimonial-top">What our guests think of Coastalcharmz</p>
            <div class="testimonial-avatars">
                @for($i = 0; $i < 5; $i++)
                <div class="testimonial-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                @endfor
            </div>
            <div class="testimonial-main">
                <p>"This place is exactly like the picture posted on Coastalcharmz. Great service, we had a great stay!"</p>
                <div class="testimonial-author">
                    <strong>Ethan Rogrinho</strong>
                    <span>🇧🇷 Brazil · Verified Guest</span>
                </div>
                <div class="testimonial-stars">
                    @for($i = 0; $i < 5; $i++)
                    <i class="fa-solid fa-star"></i>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ FOOTER ═══════════════════ --}}
<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="footer-logo">
                    <span>coastalcharmz</span>
                </a>
                <p>Find and book premium, fully-furnished apartments curated by an experienced host to make your stay unforgettable.</p>
                <div class="footer-apps">
                    <div class="store-btn"><i class="fab fa-apple"></i><div>Download on the<strong>App Store</strong></div></div>
                    <div class="store-btn"><i class="fab fa-google-play"></i><div>Get it on<strong>Google Play</strong></div></div>
                </div>
            </div>

            <div class="footer-col">
                <h5>Our Apartments</h5>
                <ul>
                    <li><a href="#">The Cozy Reading Room</a></li>
                    <li><a href="#">The Dining Suite</a></li>
                    <li><a href="#">The Yellow Lamp Loft</a></li>
                    <li><a href="#">The Executive Workspace</a></li>
                    <li><a href="#">Residential Area View</a></li>
                    <li><a href="#">Luxury Decor Penthouse</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Resources</h5>
                <ul>
                    <li><a href="#">About the Host</a></li>
                    <li><a href="#">Neighborhood Guide</a></li>
                    <li><a href="#">Check-in Instructions</a></li>
                    <li><a href="#">House Rules</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Reviews</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Support</h5>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Cancellation Policy</a></li>
                    <li><a href="#">Contact Host</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Get in Touch</h5>
                <div class="phone">+1 (800) 123-456</div>
                <a href="mailto:support@Coastalcharmz.com" class="email-link">support@Coastalcharmz.com</a>
                <div class="footer-social">
                    <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Coastalcharmz. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
            <div class="payment-icons">
                <span class="pay-icon">VISA</span>
                <span class="pay-icon">MC</span>
                <span class="pay-icon">AMEX</span>
                <span class="pay-icon">PayPal</span>
            </div>
        </div>
    </div>
</footer>

<script>
    // Scroll animation observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            this.closest('.filter-tabs').querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Search tabs
    document.querySelectorAll('.search-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Favourite toggle
    document.querySelectorAll('.deal-fav').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-regular');
            icon.classList.toggle('fa-solid');
        });
    });

    // Auth Modal Logic
    let authOverlay, step1, step2, emailInput, displayEmail, btnContinueEmail, otpInputs;

    document.addEventListener('DOMContentLoaded', () => {
        authOverlay = document.getElementById('authOverlay');
        step1 = document.getElementById('authStep1');
        step2 = document.getElementById('authStep2');
        emailInput = document.getElementById('authEmail');
        displayEmail = document.getElementById('displayEmail');
        btnContinueEmail = document.getElementById('btnContinueEmail');

        // Close on overlay click
        authOverlay.addEventListener('click', (e) => {
            if (e.target === authOverlay) window.closeAuthModal();
        });

        // Enable button based on input
        emailInput.addEventListener('input', (e) => {
            btnContinueEmail.disabled = e.target.value.trim() === '';
        });

        // OTP Input Logic
        otpInputs = document.querySelectorAll('.otp-inputs input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('keyup', (e) => {
                if (e.key >= 0 && e.key <= 9) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    } else {
                        // All filled, pretend to confirm code...
                        const code = Array.from(otpInputs).map(i => i.value).join('');
                        if(code.length === 4) {
                            window.confirmOtp();
                        }
                    }
                } else if (e.key === 'Backspace') {
                    if (index > 0 && input.value === '') {
                        otpInputs[index - 1].focus();
                    }
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
        btnContinueEmail.disabled = true;
    };

    window.closeAuthModal = function() {
        if (!authOverlay) return;
        authOverlay.classList.remove('active');
        setTimeout(() => {
            step1.classList.remove('active');
            step2.classList.remove('active');
        }, 300);
    };

    window.goStep2 = async function() {
        const email = emailInput.value.trim();
        if (!email) return;

        btnContinueEmail.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
        btnContinueEmail.disabled = true;

        try {
            const res = await fetch('/auth/otp/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            });

            const data = await res.json();
            btnContinueEmail.innerHTML = 'Continue with email';
            btnContinueEmail.disabled = false;

            if (res.ok && data.success) {
                step1.classList.remove('active');
                step2.classList.add('active');
                displayEmail.innerText = email;
                if (otpInputs) {
                    otpInputs.forEach(i => i.value = '');
                    setTimeout(() => otpInputs[0].focus(), 100);
                }
            } else {
                alert(data.message || 'Error sending code.');
            }
        } catch (error) {
            btnContinueEmail.innerHTML = 'Continue with email';
            btnContinueEmail.disabled = false;
            alert('Something went wrong. Please try again.');
            console.error(error);
        }
    };

    window.confirmOtp = async function() {
        const btnConfirmCode = document.getElementById('btnConfirmCode');
        const code = Array.from(otpInputs).map(i => i.value).join('');
        
        if (code.length !== 4) return;
        
        const originalText = btnConfirmCode.innerHTML;
        if(btnConfirmCode) btnConfirmCode.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Confirming...';

        try {
            const res = await fetch('/auth/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: emailInput.value.trim(), code: code })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                window.location.href = data.redirect; // Laravel controller decides dashboard vs admin
            } else {
                if(btnConfirmCode) btnConfirmCode.innerHTML = originalText;
                alert(data.message || 'Invalid code. Please try again.');
            }
        } catch (error) {
            if(btnConfirmCode) btnConfirmCode.innerHTML = originalText;
            alert('Something went wrong verifying the code.');
            console.error(error);
        }
    };

</script>

{{-- ═══════════════════ AUTH MODAL HTML ═══════════════════ --}}
<div class="auth-modal-overlay" id="authOverlay">
    <div class="auth-modal">
        <!-- STEP 1: Email / Social -->
        <div class="modal-step active" id="authStep1">
            <div class="auth-header">
                <button class="auth-close" onclick="closeAuthModal()"><i class="fa-solid fa-xmark"></i></button>
                <h3>Sign in or sign up</h3>
            </div>
            <div class="auth-body">
                <h2 class="auth-title">Welcome to CoastalCharmz</h2>

                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" id="authEmail" placeholder="e.g., mail@example.com" autocomplete="email">
                </div>

                <button class="btn-auth btn-auth-primary" id="btnContinueEmail" disabled onclick="goStep2()">
                    Continue with email
                </button>

                <div class="auth-divider">or</div>

                <div style="display: flex; justify-content: space-between; gap: 16px;">
                    <a href="{{ route('login') }}" class="btn-auth btn-auth-social" style="flex:1; text-decoration:none;" title="Continue with password">
                        <i class="fa-solid fa-key"></i>
                    </a>

                    <button class="btn-auth btn-auth-social" style="flex:1" title="Continue with Google">
                        <i class="fa-brands fa-google" style="color:#DB4437;"></i>
                    </button>
                    <button class="btn-auth btn-auth-social btn-apple" style="flex:1" title="Continue with Apple">
                        <i class="fa-brands fa-apple"></i>
                    </button>
                    <button class="btn-auth btn-auth-social" style="flex:1" title="Continue with Facebook">
                        <i class="fa-brands fa-facebook" style="color:#1877F2;"></i>
                    </button>
                </div>
                
                <div class="auth-footer">
                    By moving forward, you agree to our <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.
                </div>
            </div>
        </div>

        <!-- STEP 2: OTP Verification -->
        <div class="modal-step" id="authStep2">
            <div class="auth-header">
                <button class="auth-close" onclick="document.getElementById('authStep2').classList.remove('active'); document.getElementById('authStep1').classList.add('active');"><i class="fa-solid fa-chevron-left"></i></button>
                <h3>Let's confirm it's you</h3>
            </div>
            <div class="auth-body" style="padding-top:20px; text-align:center;">
                <h2 style="font-size:18px; font-weight:700; margin-bottom:12px;">Enter your verification code</h2>
                <p class="otp-sub">We've sent a 4-digit code to:<br><strong id="displayEmail">mail@example.com</strong></p>

                <div class="otp-inputs">
                    <input type="text" maxlength="1" placeholder="-" />
                    <input type="text" maxlength="1" placeholder="-" />
                    <input type="text" maxlength="1" placeholder="-" />
                    <input type="text" maxlength="1" placeholder="-" />
                </div>

                <span class="resend-link">Get a new code</span>

                <button class="btn-auth btn-auth-primary" id="btnConfirmCode" onclick="confirmOtp()">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>