@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="auth-overlay">

    <div class="card auth-card position-relative">

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
                        style="
                            width:55px;
                            height:55px;
                            object-fit:contain;
                        "
                    >

                @else

                    <i class="fa-solid fa-gamepad"></i>

                @endif

            </div>


            {{-- TITLE --}}
            <h3 class="text-center fw-bold">

                Login

            </h3>


            <p class="text-center text-muted mb-4">

                Masuk ke akun Anda

            </p>


            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('login') }}"
            >

                @csrf


                {{-- EMAIL --}}
                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control auth-input @error('email') is-invalid @enderror"
                        placeholder="Masukkan email Anda"
                        autocomplete="email"
                        required
                    >

                    @error('email')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- PASSWORD --}}
                <div class="mb-3">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control auth-input @error('password') is-invalid @enderror"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                    @error('password')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- REMEMBER --}}
                <div class="form-check mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remember"
                        id="remember"
                    >

                    <label
                        class="form-check-label"
                        for="remember"
                    >
                        Ingat saya
                    </label>

                </div>


                {{-- LOGIN --}}
                <button
                    type="submit"
                    class="btn auth-btn w-100"
                >

                    <i class="fa-solid fa-right-to-bracket me-2"></i>

                    Login

                </button>

            </form>


            {{-- REGISTER --}}
            <hr>

            <div class="text-center">

                Belum punya akun?

                <a href="{{ route('register') }}">
                    Daftar Sekarang
                </a>

            </div>

        </div>

    </div>

</div>

@endsection