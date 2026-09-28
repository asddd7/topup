<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('app_name', config('app.name', 'TopUp Game')) }}</title>

        @if(setting('app_favicon'))
            <link rel="icon" href="{{ asset('storage/' . setting('app_favicon')) }}">
        @endif

        <script>
            (function () {
                const savedTheme = localStorage.getItem('topup-theme');
                const systemDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
                const theme = savedTheme === 'dark' || savedTheme === 'light'
                    ? savedTheme
                    : (systemDark ? 'dark' : 'light');

                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.style.colorScheme = theme;
            })();
        </script>

        <link href="{{ asset('assets/css/theme.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/global.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/ui-enhancements.css') }}" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="guest-page">
        <div class="guest-shell">
            <div class="guest-header">
                <a href="{{ route('dashboard') }}" class="guest-brand" aria-label="Kembali ke dashboard">
                    <x-application-logo class="guest-logo" />
                    <span>{{ setting('app_name', 'TopUp Game') }}</span>
                </a>

                <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ganti tema">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>

            <main class="guest-card">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
