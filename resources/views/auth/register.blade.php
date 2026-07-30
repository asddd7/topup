@extends('layouts.app')

@section('title','Register')

@section('content')

<div class="auth-overlay">

    <div class="card auth-card position-relative">

    <div class="card-body p-4">
        <div class="logo mb-3">

            <i class="fa-solid fa-user-plus"></i>

        </div>

        <h3 class="text-center fw-bold">

            Register

        </h3>

        <p class="text-center text-muted">

            Buat akun baru

        </p>

        <form method="POST" action="{{ route('register') }}">

            @csrf

            <div class="mb-3">

                <label>Nama</label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    required>

            </div>

            <div class="mb-3">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required>

            </div>

            <div class="mb-3">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required>

            </div>

            <div class="mb-4">

                <label>Konfirmasi Password</label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    required>

            </div>

            <button class="btn btn-success w-100">

                Register

            </button>

        </form>

        <hr>

        <p class="text-center">

            Sudah punya akun?

            <a href="{{ route('login') }}">

                Login

            </a>

        </p>
        </div>
    </div>

</div>

@endsection