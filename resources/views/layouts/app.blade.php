<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? config('app.name', 'CoastalCharmz'))</title>
    <meta name="description" content="@yield('meta_description', 'Serviced apartments booked direct, with no hidden fees.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a href="#main" class="sr-only-focusable btn btn-primary" style="position:fixed;top:8px;left:8px;z-index:100;">
        Skip to content
    </a>

    @include('layouts.navigation')

    <main id="main" style="padding-top: var(--nav-h); min-height: 70vh;">
        <x-flash />

        {{-- Both composition styles are supported so pages can migrate one at a
             time: @extends views fill the section, component views pass a slot. --}}
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>

    @include('layouts.footer')

    @stack('scripts')
</body>
</html>
