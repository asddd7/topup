<header class="topup-topbar">

    <div class="topup-topbar-container">

        {{-- =====================================================
             LEFT : LOGO + APP NAME
        ====================================================== --}}

        <div class="topup-topbar-left">

            {{-- MOBILE SIDEBAR BUTTON --}}
            <button
                type="button"
                class="btn topup-mobile-menu d-lg-none"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebar"
                aria-label="Toggle sidebar"
            >
                <i class="fa-solid fa-bars"></i>
            </button>


            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="topup-brand">

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
                        Admin Dashboard
                    </small>

                </div>

            </a>

        </div>


        {{-- =====================================================
             CENTER : NAVIGATION
        ====================================================== --}}

        <nav class="topup-topbar-nav d-none d-lg-block">

            <ul>

                <li>
                    <a href="{{ url('/') }}">
                        <i class="fa-solid fa-house"></i>
                        Dashboard
                    </a>
                </li>


                <li>
                    <a href="#">
                        <i class="fa-solid fa-gamepad"></i>
                        Games
                    </a>
                </li>


                <li>
                    <a href="#">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Orders
                    </a>
                </li>


                <li>
                    <a href="#">
                        <i class="fa-solid fa-users"></i>
                        Users
                    </a>
                </li>


                <li>
                    <a href="#">
                        <i class="fa-solid fa-chart-line"></i>
                        Reports
                    </a>
                </li>

            </ul>

        </nav>


        {{-- =====================================================
             RIGHT : NOTIFICATION + USER
        ====================================================== --}}

        <div class="topup-topbar-right">


            {{-- =================================================
                 NOTIFICATION
            ================================================== --}}

            <div class="dropdown">

                <button
                    type="button"
                    class="btn topup-notification-btn position-relative"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifikasi"
                >

                    <i class="fa-regular fa-bell"></i>


                    @if(($notificationCount ?? 0) > 0)

                        <span class="topup-notification-badge">

                            {{ $notificationCount }}

                        </span>

                    @endif

                </button>


                <ul class="dropdown-menu dropdown-menu-end shadow topup-notification-dropdown">


                    {{-- HEADER --}}

                    <li class="topup-notification-header">

                        <div>
                            <strong>Notifikasi</strong>

                            @if(($notificationCount ?? 0) > 0)

                                <span class="badge bg-danger">
                                    {{ $notificationCount }}
                                </span>

                            @endif

                        </div>

                    </li>


                    {{-- NOTIFICATION LIST --}}

                    @forelse(($notifications ?? collect()) as $notif)

                        <li>

                            <a
                                href="#"
                                class="dropdown-item topup-notification-item"
                            >

                                <div class="topup-notification-title">

                                    {{ $notif->title }}

                                </div>


                                <div class="topup-notification-message">

                                    {{ $notif->message }}

                                </div>


                                <small class="topup-notification-time">

                                    {{ $notif->created_at?->diffForHumans() }}

                                </small>

                            </a>

                        </li>

                    @empty

                        <li>

                            <div class="topup-no-notification">

                                <i class="fa-regular fa-bell-slash"></i>

                                <div>
                                    Tidak ada notifikasi
                                </div>

                            </div>

                        </li>

                    @endforelse


                </ul>

            </div>


            {{-- =================================================
                 USER PROFILE
            ================================================== --}}

            <div class="dropdown">


                <button
                    type="button"
                    class="topup-user-button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    {{-- AVATAR --}}

                    <div class="topup-user-avatar">

                        {{ strtoupper(substr(optional(Auth::user())->name ?? 'U', 0, 1)) }}

                    </div>


                    {{-- USER INFO --}}

                    <div class="topup-user-info d-none d-xl-block">

                        <strong>
                            {{ optional(Auth::user())->name }}
                        </strong>

                        <small>
                            {{ optional(Auth::user())->email }}
                        </small>

                    </div>


                    <i class="fa-solid fa-chevron-down topup-user-arrow d-none d-xl-block"></i>

                </button>


                {{-- USER DROPDOWN --}}

                <ul class="dropdown-menu dropdown-menu-end shadow topup-user-dropdown">


                    {{-- USER HEADER --}}

                    <li class="topup-user-dropdown-header">

                        <div class="topup-user-avatar large">

                            {{ strtoupper(substr(optional(Auth::user())->name ?? 'U', 0, 1)) }}

                        </div>


                        <div>

                            <strong>
                                {{ optional(Auth::user())->name }}
                            </strong>

                            <small>
                                {{ optional(Auth::user())->email }}
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


                    {{-- SETTINGS --}}

                    <li>

                        <a
                            href="#"
                            class="dropdown-item"
                        >

                            <i class="fa-solid fa-gear"></i>

                            Settings

                        </a>

                    </li>


                    <li>
                        <hr class="dropdown-divider">
                    </li>


                    {{-- LOGOUT --}}

                    @auth

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

                    @endauth


                </ul>

            </div>


        </div>

    </div>

</header>