<div
class="offcanvas-lg offcanvas-start bg-white shadow"

tabindex="-1"

id="sidebar"

style="width:260px;">

<div class="offcanvas-header">

    <h4 class="fw-bold text-primary">

       {{ setting('app_name') }} | ADMIN

    </h4>

</div>

<div class="offcanvas-body p-0">

    <div class="list-group list-group-flush">

        <a href="{{ route('admin.dashboard') }}"
           class="list-group-item">

            <i class="fa-solid fa-house me-2"></i>

            Dashboard

        </a>


        {{-- GAME --}}

        <a href="{{ route('admin.game.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-store me-2"></i>

            Game

        </a>


        {{-- MOO GOLD PRODUCT MAPPING --}}

        <a href="{{ route('admin.moogold.product-mapping') }}"
           class="list-group-item">

            <i class="fa-solid fa-link me-2"></i>

            MooGold Product Mapping

        </a>


        {{-- KATEGORI ITEM --}}

        <a href="{{ route('admin.item-category.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-layer-group me-2"></i>

            Kategori Item

        </a>


        {{-- STOCK --}}

        <a href="{{ route('admin.stock.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-boxes-stacked me-2"></i>

            Manajemen Stock

        </a>


        {{-- DISKON --}}

        <a href="{{ route('admin.discount.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-ticket me-2"></i>

            Voucher Diskon

        </a>


        {{-- BANNER --}}

        <a href="{{ route('admin.banner.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-images me-2"></i>

            Banner

        </a>


        {{-- PAYMENT --}}

        <a href="{{ route('admin.payment.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-credit-card me-2"></i>

            Metode Pembayaran

        </a>


        {{-- ORDER --}}

        <a href="{{ route('admin.order.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-shopping-cart me-2"></i>

            Pesanan


            @php

                $waitingPayment =
                    \App\Models\Order::where(
                        'status',
                        'Paid'
                    )->count();

            @endphp


            @if($waitingPayment)

                <span class="badge bg-danger float-end">

                    {{ $waitingPayment }}

                </span>

            @endif

        </a>


        {{-- PROFILE --}}

        <a href="#"
           class="list-group-item"
           data-bs-toggle="modal"
           data-bs-target="#adminProfileModal">

            <i class="fa-solid fa-user-shield me-2"></i>

            Profil Admin

        </a>


        {{-- SETTING --}}

        <a href="{{ route('admin.setting.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-gears me-2"></i>

            Pengaturan

        </a>


        {{-- ACTIVITY LOG --}}

        <a href="{{ route('admin.activity-log.index') }}"
           class="list-group-item">

            <i class="fa-solid fa-clock-rotate-left me-2"></i>

            Activity Log

        </a>


        {{-- LOGOUT --}}

        @auth

        <form
            action="{{ route('logout') }}"
            method="POST">

            @csrf

            <button
                class="list-group-item
                       list-group-item-action
                       text-danger
                       border-0
                       w-100
                       text-start">

                <i class="fa-solid fa-right-from-bracket me-2"></i>

                Logout

            </button>

        </form>

        @endauth

    </div>

</div>

</div>