<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>{{ setting('app_name') }}</title>

    {{-- Bootstrap --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet">

    {{-- Font Awesome --}}
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet">

    {{-- MAIN CSS --}}
    <link
        href="{{ asset('assets/css/app.css') }}"
        rel="stylesheet">

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')

</head>

<body>

    @include('layouts.topbar')

    <div class="page-wrapper">

        <main class="main-content">

            <div class="content-wrapper">

                @yield('content')

            </div>

        </main>

    </div>

    @auth
        @include('profile.modal')
    @endauth

    @include('layouts.footer')

    {{-- Bootstrap JS --}}
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    {{-- VITE --}}
    @vite([
        'resources/js/app.js'
    ])

    @stack('scripts')

</body>

</html>