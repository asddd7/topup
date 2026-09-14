@extends('layouts.app')

@section('title', 'Login')

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

                    <i class="fa-solid fa-gamepad"></i>

                @endif

            </div>


            {{-- TITLE --}}
            <div class="auth-heading text-center">

                <span class="auth-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    Akun Aman
                </span>

                <h3>
                    Selamat Datang
                </h3>

                <p>
                    Masuk ke akun Anda untuk melanjutkan
                </p>

            </div>


            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('login') }}"
                class="auth-form"
            >

                @csrf


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
                            autofocus
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
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
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


                {{-- REMEMBER --}}
                <div class="auth-options mb-4">

                    <label class="remember-option">

                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                        >

                        <span class="custom-check">
                            <i class="fa-solid fa-check"></i>
                        </span>

                        <span>
                            Ingat saya
                        </span>

                    </label>

                </div>


                {{-- LOGIN --}}
                <button
                    type="submit"
                    class="btn auth-btn w-100"
                >

                    <span>
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Login ke Akun
                    </span>

                </button>

            </form>


            {{-- REGISTER --}}
            <div class="auth-register">

                <div class="auth-register-line"></div>

                <span>
                    Belum punya akun?
                </span>

                <a href="{{ route('register') }}">
                    Daftar Sekarang
                    <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>

            </div>


            {{-- FOOTER --}}
            <div class="auth-security">

                <i class="fa-solid fa-lock"></i>

                <span>
                    Data Anda terlindungi dan aman
                </span>

            </div>

        </div>

    </div>

</div>


<script>
    function togglePassword() {

        const input = document.getElementById('password');
        const button = document.querySelector('.password-toggle i');

        if (!input || !button) {
            return;
        }

        if (input.type === 'password') {

            input.type = 'text';

            button.classList.remove('fa-eye');
            button.classList.add('fa-eye-slash');

        } else {

            input.type = 'password';

            button.classList.remove('fa-eye-slash');
            button.classList.add('fa-eye');

        }

    }
</script>

@endsection