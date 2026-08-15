<header class="topup-topbar">

    <div class="topup-topbar-container">

        {{-- =====================================================
             LEFT : LOGO + APP NAME
        ====================================================== --}}

 <div class="topup-topbar-left">

    {{-- BACK BUTTON --}}
    @if(!request()->routeIs('dashboard'))

        <a
            href="{{ route('dashboard') }}"
            class="topup-back-button"
            aria-label="Kembali"
            title="Kembali"
        >
            <i class="fa-solid fa-arrow-left"></i>
        </a>

    @endif


    {{-- LOGO --}}
    <a href="{{ route('dashboard') }}" class="topup-brand">

        @if(setting('app_logo'))

            <img
                src="{{ asset('storage/' . setting('app_logo')) }}"
                alt="{{ setting('app_name', 'TopUp Game') }}"
                class="topup-brand-logo"
            >

        @else

            <div class="topup-brand-icon">
                <i class="fa-solid fa-gamepad"></i>
            </div>

        @endif

        <div class="topup-brand-info">

            <div class="topup-brand-name">
                {{ setting('app_name', 'TopUp Game') }}
            </div>

            <small>
                {{ Auth::check() && Auth::user()->role_id == 1
                    ? 'Admin Dashboard'
                    : 'Top Up Game'
                }}
            </small>

        </div>

    </a>

</div>


 {{-- =====================================================
     CENTER : USER NAVIGATION
====================================================== --}}

<div class="topup-topbar-navigation">

    {{-- NAVIGATION --}}
    <nav class="topup-topbar-nav d-none d-lg-block">

        <ul>

            {{-- DASHBOARD --}}
            <li>
                <a href="{{ route('dashboard') }}">

                    <i class="fa-solid fa-house"></i>

                    Dashboard

                </a>
            </li>


            {{-- GAME --}}
            <li>
                <a href="{{ route('game.index') }}">

                    <i class="fa-solid fa-gamepad"></i>

                    Game

                </a>
            </li>


            {{-- PESANAN --}}
            @auth

                @if(Auth::user()->role_id == 2)

                    <li>
                        <a href="{{ route('order.index') }}">

                            <i class="fa-solid fa-cart-shopping"></i>

                            Pesanan Saya

                        </a>
                    </li>

                @endif

            @endauth

        </ul>

    </nav>

</div>


        {{-- =====================================================
             RIGHT
        ====================================================== --}}

        <div class="topup-topbar-right">

            {{-- =================================================
                 USER / GUEST
            ================================================== --}}

            @auth

                {{-- ============================
                     USER PROFILE
                ============================= --}}

                <div class="dropdown">

                    <button
                        type="button"
                        class="topup-user-button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >

                        {{-- AVATAR --}}
                        <div class="topup-user-avatar">

                            {{ strtoupper(
                                substr(
                                    optional(Auth::user())->name ?? 'U',
                                    0,
                                    1
                                )
                            ) }}

                        </div>


                        {{-- USER INFO --}}
                        <div class="topup-user-info d-none d-xl-block">

                            <strong>
                                {{ Auth::user()->name }}
                            </strong>

                            <small>
                                {{ Auth::user()->email }}
                            </small>

                        </div>


                        <i class="fa-solid fa-chevron-down topup-user-arrow d-none d-xl-block"></i>

                    </button>


                    {{-- DROPDOWN --}}
                    <ul class="dropdown-menu dropdown-menu-end shadow topup-user-dropdown">

                        <li class="topup-user-dropdown-header">

                            <div class="topup-user-avatar large">

                                {{ strtoupper(
                                    substr(
                                        Auth::user()->name ?? 'U',
                                        0,
                                        1
                                    )
                                ) }}

                            </div>

                            <div>

                                <strong>
                                    {{ Auth::user()->name }}
                                </strong>

                                <small>
                                    {{ Auth::user()->email }}
                                </small>

                            </div>

                        </li>


                        <li>
                            <hr class="dropdown-divider">
                        </li>


                        {{-- PROFILE --}}
                        <li>

                            <a
                                href="#"
                                class="dropdown-item"
                                data-bs-toggle="modal"
                                data-bs-target="#profileModal"
                            >

                                <i class="fa-regular fa-user"></i>

                                My Profile

                            </a>

                        </li>


                        {{-- PESANAN --}}
                        @if(Auth::user()->role_id == 2)

                            <li>

                                <a
                                    href="{{ route('order.index') }}"
                                    class="dropdown-item"
                                >

                                    <i class="fa-solid fa-cart-shopping"></i>

                                    Pesanan Saya

                                </a>

                            </li>

                        @endif


                        <li>
                            <hr class="dropdown-divider">
                        </li>


                        {{-- LOGOUT --}}
                        <li>

                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                            >

                                @csrf

                                <button
                                    type="submit"
                                    class="dropdown-item topup-logout"
                                >

                                    <i class="fa-solid fa-right-from-bracket"></i>

                                    Logout

                                </button>

                            </form>

                        </li>

                    </ul>

                </div>


            @else

                {{-- =================================================
                     GUEST
                ================================================== --}}

                <a
                    href="{{ route('login') }}"
                    class="topup-login-button"
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    <span>
                        Login
                    </span>

                </a>

            @endauth


        </div>

    </div>

</header>