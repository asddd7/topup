{{-- =========================================================
     BOTTOM NAVIGATION
========================================================= --}}

<nav
    id="sidebar"
    class="topup-bottom-nav"
    aria-label="Navigasi utama"
>

    <div class="topup-bottom-nav-inner">


        {{-- DASHBOARD --}}
        <a
            href="{{ route('dashboard') }}"
            class="topup-bottom-nav-item
                {{ request()->routeIs('dashboard') ? 'active' : '' }}"
        >

            <span class="topup-bottom-nav-icon">
                <i class="fa-solid fa-house"></i>
            </span>

            <span class="topup-bottom-nav-label">
                Dashboard
            </span>

        </a>


        {{-- PESANAN --}}
        @auth

            <a
                href="{{ route('order.index') }}"
                class="topup-bottom-nav-item
                    {{ request()->routeIs('order.*') ? 'active' : '' }}"
            >

                <span class="topup-bottom-nav-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </span>

                <span class="topup-bottom-nav-label">
                    Pesanan
                </span>

            </a>

        @endauth


        {{-- LOGIN --}}
        @guest

            <a
                href="{{ route('login') }}"
                class="topup-bottom-nav-item
                    {{ request()->routeIs('login') ? 'active' : '' }}"
            >

                <span class="topup-bottom-nav-icon">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </span>

                <span class="topup-bottom-nav-label">
                    Login
                </span>

            </a>

        @endguest


        {{-- LOGOUT --}}
        @auth

            <form
                action="{{ route('logout') }}"
                method="POST"
                class="topup-bottom-nav-form"
            >

                @csrf

                <button
                    type="submit"
                    class="topup-bottom-nav-item topup-bottom-nav-logout"
                >

                    <span class="topup-bottom-nav-icon">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </span>

                    <span class="topup-bottom-nav-label">
                        Logout
                    </span>

                </button>

            </form>

        @endauth


    </div>

</nav>