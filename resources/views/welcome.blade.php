<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coastalcharmz – Your Trip Starts Here</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        .fade-up { opacity: 0; transform: translateY(30px); transition: opacity .6s ease, transform .6s ease; }
        .fade-up.visible { opacity: 1; transform: none; }
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
        <a href="#">Hotels</a>
        <a href="#">Villas</a>
        <a href="#">Guest Houses</a>
        <a href="#">Experiences</a>
        <a href="#">Camping</a>
        <a href="#">Rooms</a>
    </div>

    <div class="navbar-actions">
        <button class="btn-ghost"><i class="fa-regular fa-circle-question"></i> Help</button>
        <button class="btn-ghost">Login</button>
        <button class="btn-primary">Sign Up</button>
    </div>
</nav>

{{-- ═══════════════════ HERO ═══════════════════ --}}
<section class="hero">
    <div class="hero-bg-overlay" style="background-image: url('{{ asset('images/table-bookshelf.jpg') }}');"></div>
    <div class="hero-shapes"><span></span><span></span><span></span></div>
    <div class="hero-content">
        <h1>Your Trip Starts Here</h1>
        <p>Find unique stays across hotels, villas, and more</p>

        <div class="search-box">
            <div class="search-tabs">
                <button class="search-tab active"><i class="fa-solid fa-building"></i> Hotel</button>
                <button class="search-tab"><i class="fa-solid fa-house"></i> House</button>
                <button class="search-tab"><i class="fa-solid fa-umbrella-beach"></i> Guest House</button>
                <button class="search-tab"><i class="fa-solid fa-tent"></i> Camping</button>
                <button class="search-tab"><i class="fa-solid fa-star"></i> Experiences</button>
                <button class="search-tab"><i class="fa-solid fa-door-open"></i> Rooms</button>
            </div>
            <div class="search-fields">
                <div class="search-field">
                    <label>Location</label>
                    <input type="text" placeholder="Where are you going?">
                </div>
                <div class="search-field">
                    <label>Check In</label>
                    <input type="date">
                </div>
                <div class="search-field">
                    <label>Check Out</label>
                    <input type="date">
                </div>
                <div class="search-field">
                    <label>Rooms &amp; Guests</label>
                    <select>
                        <option>1 room, 2 guests</option>
                        <option>1 room, 1 guest</option>
                        <option>2 rooms, 4 guests</option>
                    </select>
                </div>
                <button class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ WHY TRUST ═══════════════════ --}}
<section class="section">
    <div class="container">
        <div class="section-header"><div><h2 class="section-title">Why Travellers Trust Coastalcharmz</h2></div></div>
        <div class="trust-grid">
            <div class="trust-card fade-up">
                <div class="trust-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>No Hidden Fees</h3>
                <p>Transparent pricing with no hidden costs or surprise charges at checkout.</p>
            </div>
            <div class="trust-card fade-up" style="transition-delay:.1s">
                <div class="trust-icon"><i class="fa-solid fa-bolt"></i></div>
                <h3>Instant Booking</h3>
                <p>Confirm your stay in seconds right after you find what you need.</p>
            </div>
            <div class="trust-card fade-up" style="transition-delay:.2s">
                <div class="trust-icon"><i class="fa-solid fa-sliders"></i></div>
                <h3>Flexibility</h3>
                <p>Flexible cancellation options that work around your schedule and needs.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ TRENDING DESTINATIONS ═══════════════════ --}}
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Trending Destinations</h2>
                <p class="section-subtitle">Most popular picks from our travellers</p>
            </div>
            <a href="#" class="section-link">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab active">Going Fast</button>
            <button class="filter-tab">Summer Escape</button>
            <button class="filter-tab">Autumn Getaway</button>
            <button class="filter-tab">Winter Cruise</button>
        </div>

        <div class="dest-grid">
            @php
                $destinations = [
                    ['name' => 'Paris, France', 'desc' => 'The city of love & lights', 'badge' => 'Trending', 'icon' => 'fa-eiffel-tower'],
                    ['name' => 'Santorini, Greece', 'desc' => 'Iconic whitewashed cliffs', 'badge' => null, 'icon' => 'fa-water'],
                    ['name' => 'Bali, Indonesia', 'desc' => 'Tropical paradise & culture', 'badge' => null, 'icon' => 'fa-umbrella-beach'],
                    ['name' => 'Kyoto, Japan', 'desc' => 'Ancient temples & cherry blossoms', 'badge' => 'Hot', 'icon' => 'fa-torii-gate'],
                ];
            @endphp

            @foreach($destinations as $i => $dest)
            <div class="dest-card fade-up" style="transition-delay:{{ $i * 0.1 }}s">
                {{-- Replace with: <img src="{{ asset('images/dest-' . $i+1 . '.jpg') }}" class="dest-card-img" alt="{{ $dest['name'] }}"> --}}
                <div class="dest-card-placeholder">
                    <i class="fa-solid {{ $dest['icon'] }}"></i>
                </div>
                @if($dest['badge'])
                <div class="dest-badge">{{ $dest['badge'] }}</div>
                @endif
                <div class="dest-card-overlay">
                    <h3>{{ $dest['name'] }}</h3>
                    <p>{{ $dest['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ DEALS FOR THE WEEKEND ═══════════════════ --}}
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Deals for the Weekend</h2>
                <p class="section-subtitle">Limited-time offers on top stays</p>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <div class="section-nav">
                    <button class="nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <a href="#" class="section-link">See all <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="deals-grid">
            @php
                $deals = [
                    ['name' => 'Seaside Kennedy Villa', 'location' => 'Miami, USA', 'rating' => 4.9, 'price' => 175, 'old' => 220, 'status' => 'available', 'icon' => 'fa-umbrella-beach'],
                    ['name' => 'Imperial Bungalow', 'location' => 'Phuket, Thailand', 'rating' => 4.7, 'price' => 140, 'old' => 180, 'status' => 'limited', 'icon' => 'fa-tree'],
                    ['name' => 'Santorini Sunset Suites', 'location' => 'Santorini, Greece', 'rating' => 4.8, 'price' => 315, 'old' => 390, 'status' => 'available', 'icon' => 'fa-water'],
                    ['name' => 'MaDellia Resort', 'location' => 'Maldives', 'rating' => 5.0, 'price' => 482, 'old' => 610, 'status' => 'limited', 'icon' => 'fa-fish'],
                ];
            @endphp

            @foreach($deals as $i => $deal)
            <div class="deal-card fade-up" style="transition-delay:{{ $i * 0.1 }}s">
                <div class="deal-img-wrap">
                    {{-- Replace with: <img src="{{ asset('images/deal-' . $i+1 . '.jpg') }}" alt="{{ $deal['name'] }}"> --}}
                    <div class="deal-img-placeholder">
                        <i class="fa-solid {{ $deal['icon'] }}"></i>
                    </div>
                    <div class="deal-fav"><i class="fa-solid fa-heart"></i></div>
                    <span class="deal-status {{ $deal['status'] === 'available' ? 'status-available' : 'status-limited' }}">
                        {{ $deal['status'] === 'available' ? 'Available' : 'Limited' }}
                    </span>
                </div>
                <div class="deal-body">
                    <h3>{{ $deal['name'] }}</h3>
                    <div class="deal-location"><i class="fa-solid fa-location-dot"></i> {{ $deal['location'] }}</div>
                    <div class="deal-footer">
                        <div class="deal-rating">
                            <i class="fa-solid fa-star"></i> {{ $deal['rating'] }}
                        </div>
                        <div class="deal-price">
                            <del style="color:#ccc; font-size:12px">${{ $deal['old'] }}</del>
                            <strong> ${{ $deal['price'] }}</strong><span style="font-size:11px; color:var(--muted)">/night</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ TRAVEL MORE SPEND LESS ═══════════════════ --}}
<section class="section section-alt">
    <div class="container">
        <div class="section-header"><h2 class="section-title">Travel more, spend less</h2></div>
        <div class="promo-grid">
            <div class="promo-card fade-up">
                <h4>5% discounts on stays</h4>
                <p>Get discounts on participating accommodations across our platform.</p>
            </div>
            <div class="promo-card fade-up" style="transition-delay:.1s">
                <h4>Traveller of Season</h4>
                <p>Exclusive rewards for travel enthusiasts and loyal users.</p>
            </div>
            <div class="promo-card fade-up" style="transition-delay:.2s">
                <h4>Exclusive Deals</h4>
                <p>Enjoy exclusive deals with partner properties around the world.</p>
            </div>
            <div class="promo-card fade-up" style="transition-delay:.3s">
                <h4>Weekend Special</h4>
                <p>Save up to 10% on weekend stays at select properties.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ TOP SIGHTS ═══════════════════ --}}
<section class="section">
    <div class="container">
        <div class="section-header">
            <div><h2 class="section-title">Top Sights to See</h2></div>
            <a href="#" class="section-link">Explore all <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="sights-grid">
            @php
                $sights = [
                    ['name' => 'Sassnitz', 'flag' => '🇩🇪', 'icon' => 'fa-mountain'],
                    ['name' => 'Binz', 'flag' => '🇩🇪', 'icon' => 'fa-water'],
                    ['name' => 'Sagard', 'flag' => '🇩🇪', 'icon' => 'fa-tree'],
                    ['name' => 'Burgee', 'flag' => '🇩🇪', 'icon' => 'fa-city'],
                    ['name' => 'Freedom', 'flag' => '🇺🇸', 'icon' => 'fa-monument'],
                    ['name' => 'Old Town', 'flag' => '🇵🇱', 'icon' => 'fa-church'],
                ];
            @endphp

            @foreach($sights as $i => $sight)
            <div class="sight-card fade-up" style="transition-delay:{{ $i * 0.08 }}s">
                <div class="sight-img-wrap">
                    {{-- Replace with: <img src="{{ asset('images/sight-' . $i+1 . '.jpg') }}" alt="{{ $sight['name'] }}"> --}}
                    <div class="sight-img-placeholder">
                        <i class="fa-solid {{ $sight['icon'] }}"></i>
                    </div>
                </div>
                <div class="sight-overlay">
                    <h3>{{ $sight['name'] }} <span class="sight-flag">{{ $sight['flag'] }}</span></h3>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ THINGS TO DO ═══════════════════ --}}
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <div><h2 class="section-title">Top Things to Do in Barcelona</h2></div>
        </div>

        <div class="filter-tabs" style="margin-bottom:24px">
            <button class="filter-tab active">Explore</button>
            <button class="filter-tab">Events</button>
            <button class="filter-tab">Museums</button>
            <button class="filter-tab">Dine</button>
            <button class="filter-tab">Food</button>
            <button class="filter-tab">Night Life</button>
        </div>

        <div class="things-grid">
            @php
                $things = [
                    ['name' => 'Sagrada Familia', 'icon' => 'fa-church'],
                    ['name' => 'Park Güell', 'icon' => 'fa-tree'],
                    ['name' => 'Casa Milà', 'icon' => 'fa-building'],
                    ['name' => 'Palau Sant Jordi', 'icon' => 'fa-landmark'],
                    ['name' => 'Arc de Triomf', 'icon' => 'fa-archway'],
                    ['name' => 'Casa Batlló', 'icon' => 'fa-city'],
                ];
            @endphp

            @foreach($things as $i => $thing)
            <div class="thing-card fade-up" style="transition-delay:{{ $i * 0.08 }}s">
                <div class="thing-img-wrap">
                    {{-- Replace with: <img src="{{ asset('images/thing-' . $i+1 . '.jpg') }}" alt="{{ $thing['name'] }}"> --}}
                    <div class="thing-img-placeholder">
                        <i class="fa-solid {{ $thing['icon'] }}"></i>
                    </div>
                </div>
                <span>{{ $thing['name'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ EXPLORE IN MOTION ═══════════════════ --}}
<section class="motion-section">
    <div class="motion-inner">
        <div class="motion-content">
            <h2 class="section-title">Step Into a World<br>of Luxury</h2>
            <p>Immerse yourself in captivating visuals from our most iconic and indulgent destinations.</p>
            <button class="btn-outline-white">Explore All Videos</button>
        </div>
        <div class="motion-images">
            <div class="motion-img-card tall">
                {{-- Replace with: <img src="{{ asset('images/motion-1.jpg') }}" alt="Luxury destination"> --}}
                <div class="motion-img-placeholder"><i class="fa-solid fa-play-circle fa-2x"></i></div>
                <div class="motion-img-label">Maldives <div class="motion-img-rating"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="motion-img-card">
                    {{-- Replace with: <img src="{{ asset('images/motion-2.jpg') }}" alt="Luxury stay"> --}}
                    <div class="motion-img-placeholder"><i class="fa-solid fa-water"></i></div>
                    <div class="motion-img-label">Bora Bora</div>
                </div>
                <div class="motion-img-card">
                    {{-- Replace with: <img src="{{ asset('images/motion-3.jpg') }}" alt="Mountain resort"> --}}
                    <div class="motion-img-placeholder"><i class="fa-solid fa-mountain"></i></div>
                    <div class="motion-img-label">Swiss Alps</div>
                </div>
                <div class="motion-img-card">
                    {{-- Replace with: <img src="{{ asset('images/motion-4.jpg') }}" alt="City view"> --}}
                    <div class="motion-img-placeholder"><i class="fa-solid fa-city"></i></div>
                    <div class="motion-img-label">New York <div class="motion-img-rating"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════ HOMES GUESTS LOVE ═══════════════════ --}}
<section class="section">
    <div class="container">
        <div class="section-header">
            <div><h2 class="section-title">Homes Guests Love</h2></div>
            <div style="display:flex; gap:12px; align-items:center;">
                <div class="section-nav">
                    <button class="nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <div class="homes-grid">
            @php
                $homes = [
                    ['name' => 'Azure Horizon Hotel', 'location' => 'Dubai, UAE', 'rating' => 4.9, 'price' => 240, 'icon' => 'fa-hotel'],
                    ['name' => 'Palm Grove House', 'location' => 'Maldives', 'rating' => 4.8, 'price' => 375, 'icon' => 'fa-umbrella-beach'],
                    ['name' => 'Casa Tranquila Guest…', 'location' => 'Palma, Spain', 'rating' => 4.7, 'price' => 315, 'icon' => 'fa-house'],
                    ['name' => 'Villa San Martino Gues…', 'location' => 'Tuscany, Italy', 'rating' => 4.9, 'price' => 392, 'icon' => 'fa-archway'],
                ];
            @endphp

            @foreach($homes as $i => $home)
            <div class="deal-card fade-up" style="transition-delay:{{ $i * 0.1 }}s">
                <div class="deal-img-wrap">
                    {{-- Replace with: <img src="{{ asset('images/home-' . $i+1 . '.jpg') }}" alt="{{ $home['name'] }}"> --}}
                    <div class="deal-img-placeholder">
                        <i class="fa-solid {{ $home['icon'] }}"></i>
                    </div>
                    <div class="deal-fav"><i class="fa-regular fa-heart"></i></div>
                </div>
                <div class="deal-body">
                    <h3>{{ $home['name'] }}</h3>
                    <div class="deal-location"><i class="fa-solid fa-location-dot"></i> {{ $home['location'] }}</div>
                    <div class="deal-footer">
                        <div class="deal-rating"><i class="fa-solid fa-star"></i> {{ $home['rating'] }}</div>
                        <div class="deal-price"><strong>${{ $home['price'] }}</strong><span style="font-size:11px;color:var(--muted)">/night</span></div>
                    </div>
                </div>
            </div>
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
                <p>Find and book unique stays across hotels, villas, guest houses, and more around the world.</p>
                <div class="footer-apps">
                    <div class="store-btn"><i class="fab fa-apple"></i><div>Download on the<strong>App Store</strong></div></div>
                    <div class="store-btn"><i class="fab fa-google-play"></i><div>Get it on<strong>Google Play</strong></div></div>
                </div>
            </div>

            <div class="footer-col">
                <h5>Explore</h5>
                <ul>
                    <li><a href="#">Trending Destinations</a></li>
                    <li><a href="#">Famous Personalities</a></li>
                    <li><a href="#">Services Premium</a></li>
                    <li><a href="#">Adventurous Drops</a></li>
                    <li><a href="#">Beach Deals</a></li>
                    <li><a href="#">Jungle</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Property Types</h5>
                <ul>
                    <li><a href="#">Hotels</a></li>
                    <li><a href="#">Villas</a></li>
                    <li><a href="#">Guest Houses</a></li>
                    <li><a href="#">Glamping</a></li>
                    <li><a href="#">Camping</a></li>
                    <li><a href="#">Gyms</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Support</h5>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Get Support</a></li>
                    <li><a href="#">Item Support</a></li>
                    <li><a href="#">Contact Us</a></li>
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
</script>
</body>
</html>