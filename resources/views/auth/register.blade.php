@extends('layouts.app')

@section('title', 'Register')

@section('content')

<div class="auth-overlay">

    <div class="card auth-card position-relative">

        {{-- DECORATION --}}
        <div class="auth-decoration auth-decoration-one"></div>
        <div class="auth-decoration auth-decoration-two"></div>

        <div class="card-body">

            {{-- CLOSE --}}
            <a
                href="{{ route('dashboard') }}"
                class="auth-close"
                aria-label="Tutup"
            >
                <i class="fa-solid fa-xmark"></i>
            </a>


            {{-- LOGO --}}
            <div class="logo">

                @if(setting('app_logo'))

                    <img
                        src="{{ asset('storage/' . setting('app_logo')) }}"
                        alt="{{ setting('app_name', 'TopUp Game') }}"
                    >

                @else

                    <i class="fa-solid fa-user-plus"></i>

                @endif

            </div>


            {{-- TITLE --}}
            <div class="auth-heading text-center">

                <span class="auth-badge">
                    <i class="fa-solid fa-user-plus"></i>
                    Daftar Akun
                </span>

                <h3>
                    Buat Akun
                </h3>

                <p>
                    Daftar untuk mulai menggunakan akun Anda
                </p>

            </div>


            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('register') }}"
                class="auth-form"
            >

                @csrf


                {{-- NAME --}}
                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >
                        Nama
                    </label>

                    <div class="auth-input-wrapper">

                        <i class="fa-regular fa-user input-icon"></i>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control auth-input @error('name') is-invalid @enderror"
                            placeholder="Masukkan nama Anda"
                            autocomplete="name"
                            required
                            autofocus
                        >

                    </div>

                    @error('name')

                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- EMAIL --}}
                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label"
                    >
                        Email
                    </label>

                    <div class="auth-input-wrapper">

                        <i class="fa-regular fa-envelope input-icon"></i>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control auth-input @error('email') is-invalid @enderror"
                            placeholder="Masukkan email Anda"
                            autocomplete="email"
                            required
                        >

                    </div>

                    @error('email')

                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- PASSWORD --}}
                <div class="mb-3">

                    <label
                        for="password"
                        class="form-label"
                    >
                        Password
                    </label>

                    <div class="auth-input-wrapper">

                        <i class="fa-solid fa-lock input-icon"></i>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control auth-input auth-password @error('password') is-invalid @enderror"
                            placeholder="Buat password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="toggleRegisterPassword('password', this)"
                            aria-label="Tampilkan password"
                        >
                            <i class="fa-regular fa-eye"></i>
                        </button>

                    </div>

                    @error('password')

                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- CONFIRM PASSWORD --}}
                <div class="mb-4">

                    <label
                        for="password_confirmation"
                        class="form-label"
                    >
                        Konfirmasi Password
                    </label>

                    <div class="auth-input-wrapper">

                        <i class="fa-solid fa-lock input-icon"></i>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control auth-input auth-password @error('password_confirmation') is-invalid @enderror"
                            placeholder="Ulangi password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="toggleRegisterPassword('password_confirmation', this)"
                            aria-label="Tampilkan password"
                        >
                            <i class="fa-regular fa-eye"></i>
                        </button>

                    </div>

                    @error('password_confirmation')

                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- REGISTER --}}
                <button
                    type="submit"
                    class="btn auth-btn w-100"
                >

                    <span>
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Buat Akun
                    </span>

                    <i class="fa-solid fa-arrow-right auth-btn-arrow"></i>

                </button>

            </form>


            {{-- LOGIN --}}
            <div class="auth-register">

                <div class="auth-register-line"></div>

                <span>
                    Sudah punya akun?
                </span>

                <a href="{{ route('login') }}">
                    Login
                    <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>

            </div>


            {{-- SECURITY --}}
            <div class="auth-security">

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    Data Anda terlindungi dan aman
                </span>

            </div>

        </div>

    </div>

</div>


<script>
    function toggleRegisterPassword(inputId, button) {

        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');

        if (!input || !icon) {
            return;
        }

        if (input.type === 'password') {

            input.type = 'text';

            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');

            button.setAttribute(
                'aria-label',
                'Sembunyikan password'
            );

        } else {

            input.type = 'password';

            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');

            button.setAttribute(
                'aria-label',
                'Tampilkan password'
            );

        }

    }
</script>

@endsection