@extends('layouts.app')

@section('title','Login')

@section('content')

<div class="auth-overlay">

    <div class="card auth-card position-relative">

        <div class="card-body p-4">

            <a href="{{ url('/') }}"
               class="btn btn-light auth-close">

                <i class="fa-solid fa-xmark"></i>

            </a>

            <div class="logo mb-3">

                <i class="fa-solid fa-gamepad"></i>

            </div>

            <h3 class="text-center fw-bold">

                Login

            </h3>

            <p class="text-center text-muted mb-4">

                Masuk ke akun Anda

            </p>

            <form method="POST"
                  action="{{ route('login') }}">

                @csrf

                <div class="mb-3">

                    <label class="form-label">

                        Email

                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control auth-input @error('email') is-invalid @enderror"
                        required>

                    @error('email')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Password

                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control auth-input @error('password') is-invalid @enderror"
                        required>

                    @error('password')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <div class="form-check mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remember">

                    <label class="form-check-label">

                        Remember Me

                    </label>

                </div>

                <button class="btn btn-primary auth-btn w-100">

                    <i class="fa-solid fa-right-to-bracket me-2"></i>

                    Login

                </button>

            </form>

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