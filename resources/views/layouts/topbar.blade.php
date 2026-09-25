    <header class="topup-topbar">

        {{-- =====================================================
            TOPBAR MAIN
        ====================================================== --}}

        <div class="topup-topbar-container">

            {{-- =================================================
                LEFT : LOGO + APP NAME
            ================================================== --}}

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
                <a
                    href="{{ route('dashboard') }}"
                    class="topup-brand"
                >

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


            {{-- =================================================
                RIGHT
            ================================================== --}}

            <div class="topup-topbar-right">

                {{-- THEME --}}
                <button
                    type="button"
                    id="themeToggle"
                    class="theme-toggle"
                    aria-label="Ganti tema"
                >
                    <i class="fa-solid fa-moon"></i>
                </button>


                {{-- =================================================
                    USER / GUEST
                ================================================== --}}

                @auth

                    {{-- USER PROFILE --}}
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


                            <i
                                class="fa-solid fa-chevron-down topup-user-arrow d-none d-xl-block"
                            ></i>

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

                    {{-- GUEST --}}
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


        {{-- =====================================================
            GAME SEARCH
            Hanya tampil jika halaman memiliki $games
        ====================================================== --}}

        @if(isset($games) && $games->count() > 0)

            <div class="topup-game-search">

                <div class="topup-game-search-container">

                    <label
                        for="gameSearchInput"
                        class="topup-search-label"
                    >
                        Cari Game
                    </label>


                    <div class="topup-search-box">

                        <input
                            type="text"
                            id="gameSearchInput"
                            class="topup-search-input"
                            placeholder="Cari game favoritmu..."
                            autocomplete="off"
                        >


                        {{-- SEARCH / CLEAR BUTTON --}}
                        <span class="topup-search-action">

                            <button
                                type="button"
                                id="clearGameSearch"
                                class="topup-search-button"
                                aria-label="Bersihkan pencarian"
                                title="Bersihkan pencarian"
                                hidden
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                            <i
                                class="fa-solid fa-magnifying-glass topup-search-icon"
                                aria-hidden="true"
                            ></i>

                        </span>

                    </div>


                    <div
                        class="topup-search-info"
                        id="gameSearchInfo"
                    >
                        Menampilkan

                        <strong id="gameResultCount">
                            {{ $games->count() }}
                        </strong>

                        game
                    </div>

                </div>

            </div>

        @endif

    </header>
