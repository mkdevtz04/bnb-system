<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? 'Sign in · ' . config('app.name', 'CoastalCharmz'))</title>

    {{-- Same fonts and stylesheet as the rest of the site. This layout used to
         pull Figtree from a different CDN and style itself with stock Breeze
         greys, which is why the sign-in page looked like a different product. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div class="flex min-h-screen flex-col items-center justify-center" style="padding: 32px 16px;
                background: linear-gradient(180deg, var(--brand-50) 0%, var(--surface) 55%);">

        <a href="{{ url('/') }}" class="navbar-brand" style="font-size:23px; margin-bottom:24px;">
            <i class="fa-solid fa-location-dot" style="color:var(--brand-600);"></i>
            Coastal<em>Charmz</em>
        </a>

        <main class="card card-pad w-full" style="max-width: 420px;">
            {{ $slot }}
        </main>

        <p class="muted mt-6 text-center" style="font-size:12.5px; max-width:420px; line-height:1.6;">
            &copy; {{ date('Y') }} CoastalCharmz ·
            <a href="{{ url('/') }}" style="color:var(--brand-700); text-decoration:none;">Back to the site</a>
        </p>
    </div>

    @stack('scripts')
</body>
</html>
