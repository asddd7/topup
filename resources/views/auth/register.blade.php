@extends('layouts.app')


@section('title', 'Register')


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


                    <i class="fa-solid fa-user-plus"></i>


                @endif


            </div>


            {{-- TITLE --}}
            <h3 class="text-center fw-bold">

                Buat Akun

            </h3>


            <p class="text-center text-muted mb-4">

                Daftar untuk mulai menggunakan akun Anda

            </p>


            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('register') }}"
            >


                @csrf


                {{-- NAME --}}
                <div class="mb-3">


                    <label class="form-label">

                        Nama

                    </label>


                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control auth-input @error('name') is-invalid @enderror"
                        placeholder="Masukkan nama Anda"
                        autocomplete="name"
                        required
                    >


                    @error('name')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror


                </div>


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
                        placeholder="Buat password"
                        autocomplete="new-password"
                        required
                    >


                    @error('password')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror


                </div>


                {{-- CONFIRM PASSWORD --}}
                <div class="mb-4">


                    <label class="form-label">

                        Konfirmasi Password

                    </label>


                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control auth-input @error('password_confirmation') is-invalid @enderror"
                        placeholder="Ulangi password"
                        autocomplete="new-password"
                        required
                    >


                    @error('password_confirmation')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror


                </div>


                {{-- REGISTER --}}
                <button
                    type="submit"
                    class="btn auth-btn w-100"
                >


                    <i class="fa-solid fa-user-plus me-2"></i>


                    Daftar


                </button>


            </form>


            {{-- LOGIN --}}
            <hr>


            <div class="text-center">


                Sudah punya akun?


                <a href="{{ route('login') }}">

                    Login

                </a>


            </div>


        </div>


    </div>


</div>


@endsection