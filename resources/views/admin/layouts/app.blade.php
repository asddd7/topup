<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>{{ setting('app_name', 'TopUp Game') }}</title>

    @if(setting('app_favicon'))
        <link rel="icon" href="{{ asset('storage/' . setting('app_favicon')) }}">
    @endif

    {{-- SET THEME BEFORE PAGE RENDER --}}
    <script>
        (function () {
            const savedTheme = localStorage.getItem('topup-theme');

            const systemDark =
                window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches;

            const theme =
                savedTheme === 'dark' || savedTheme === 'light'
                    ? savedTheme
                    : (systemDark ? 'dark' : 'light');

            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;
        })();
    </script>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet">

    <link
        href="{{ asset('assets/css/admin.css') }}"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')

</head>
<body class="admin-layout">

    @include('components.loading')

    @include('admin.layouts.topbar')

    <div class="page-wrapper">

        @include('admin.layouts.sidebar')

        <main class="main-content">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>

    </div>

    @auth
        @include('profile.modal')
    @endauth

    @include('admin.layouts.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

@vite([
    'resources/js/app.js'
])

@stack('scripts')

</body>
</html>