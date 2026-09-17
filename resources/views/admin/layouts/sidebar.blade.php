<div
    class="offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="sidebar"
    aria-labelledby="adminSidebarLabel"
>

    {{-- =====================================================
         SIDEBAR HEADER
    ====================================================== --}}

    <div class="offcanvas-header">

        <div class="d-flex align-items-center gap-2">

            <div class="admin-sidebar-brand-icon">

                @if(setting('app_logo'))

                    <img
                        src="{{ asset('storage/' . setting('app_logo')) }}"
                        alt="{{ setting('app_name', 'TOPUP') }}"
                    >

                @else

                    <i class="fa-solid fa-gamepad"></i>

                @endif

            </div>


            <div>

                <h4
                    id="adminSidebarLabel"
                    class="admin-sidebar-brand-title"
                >
                    {{ setting('app_name', 'TOPUP') }}
                </h4>

                <small class="admin-sidebar-brand-subtitle">
                    ADMIN PANEL
                </small>

            </div>

        </div>


        {{-- CLOSE MOBILE --}}

        <button
            type="button"
            class="btn-close d-lg-none"
            data-bs-dismiss="offcanvas"
            aria-label="Tutup"
        ></button>

    </div>


    {{-- =====================================================
         SIDEBAR BODY
    ====================================================== --}}

    <div class="offcanvas-body p-0">

        <nav class="admin-sidebar-nav">

            {{-- =================================================
                 OVERVIEW
            ================================================== --}}

            <div class="admin-sidebar-group">

                <div class="admin-sidebar-group-title">
                    Overview
                </div>


                {{-- DASHBOARD --}}

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-house"></i>

                    <span>
                        Dashboard
                    </span>

                </a>

            </div>


            {{-- =================================================
                 PRODUCT
            ================================================== --}}

            <div class="admin-sidebar-group">

                <div class="admin-sidebar-group-title">
                    Product
                </div>


                {{-- GAME --}}

                <a
                    href="{{ route('admin.game.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.game.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-gamepad"></i>

                    <span>
                        Game
                    </span>

                </a>


                {{-- MOO GOLD PRODUCT MAPPING --}}

                <a
                    href="{{ route('admin.moogold.product-mapping.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.moogold.product-mapping.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-link"></i>

                    <span>
                        MooGold Product Mapping
                    </span>

                </a>


                {{-- KATEGORI ITEM --}}

                <a
                    href="{{ route('admin.item-category.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.item-category.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-layer-group"></i>

                    <span>
                        Kategori Item
                    </span>

                </a>


                {{-- TOP SELLER --}}

                <a
                    href="{{ route('top-seller.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('top-seller.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-star"></i>

                    <span>
                        Top Seller
                    </span>

                </a>

            </div>


            {{-- =================================================
                SALES
            ================================================== --}}

            <div class="admin-sidebar-group">

                <div class="admin-sidebar-group-title">
                    Sales
                </div>


                {{-- VOUCHER --}}

                <a
                    href="{{ route('admin.discount.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.discount.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-ticket"></i>

                    <span>
                        Voucher Diskon
                    </span>

                </a>


                {{-- ORDER --}}

                <a
                    href="{{ route('admin.order.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.order.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-cart-shopping"></i>

                    <span>
                        Pesanan
                    </span>


                    @php

                        $waitingOrderCount = \App\Models\Order::where(
                            'status',
                            'Paid'
                        )->count();

                    @endphp


                    @if($waitingOrderCount > 0)

                        <span class="admin-sidebar-badge">
                            {{ $waitingOrderCount }}
                        </span>

                    @endif

                </a>


                {{-- HISTORY PEMBELIAN --}}

                <a
                    href="{{ route('admin.transaction-history.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.transaction-history.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-clock-rotate-left"></i>

                    <span>
                        History Pembelian
                    </span>

                </a>


                {{-- PAYMENT --}}

                <a
                    href="{{ route('admin.payment.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-credit-card"></i>

                    <span>
                        Metode Pembayaran
                    </span>

                </a>

            </div>


            {{-- =================================================
                 CONTENT
            ================================================== --}}

            <div class="admin-sidebar-group">

                <div class="admin-sidebar-group-title">
                    Content
                </div>


                {{-- BANNER --}}

                <a
                    href="{{ route('admin.banner.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.banner.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-images"></i>

                    <span>
                        Banner
                    </span>

                </a>

            </div>


            {{-- =================================================
                 SYSTEM
            ================================================== --}}

            <div class="admin-sidebar-group">

                <div class="admin-sidebar-group-title">
                    System
                </div>


                {{-- SETTING --}}

                <a
                    href="{{ route('admin.setting.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.setting.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-gears"></i>

                    <span>
                        Pengaturan
                    </span>

                </a>


                {{-- ACTIVITY LOG --}}

                <a
                    href="{{ route('admin.activity-log.index') }}"
                    class="admin-sidebar-link {{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }}"
                >

                    <i class="fa-solid fa-clock-rotate-left"></i>

                    <span>
                        Activity Log
                    </span>

                </a>


                {{-- PROFILE --}}

                <a
                    href="#"
                    class="admin-sidebar-link"
                    data-bs-toggle="modal"
                    data-bs-target="#adminProfileModal"
                >

                    <i class="fa-solid fa-user-shield"></i>

                    <span>
                        Profil Admin
                    </span>

                </a>

            </div>


            {{-- =================================================
                 LOGOUT
            ================================================== --}}

            @auth

                <div class="admin-sidebar-logout-section">

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="admin-sidebar-link admin-sidebar-logout"
                        >

                            <i class="fa-solid fa-right-from-bracket"></i>

                            <span>
                                Logout
                            </span>

                        </button>

                    </form>

                </div>

            @endauth

        </nav>

    </div>

</div>