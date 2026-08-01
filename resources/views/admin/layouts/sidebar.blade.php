<div
class="offcanvas-lg offcanvas-start bg-white shadow"

tabindex="-1"

id="sidebar"

style="width:260px;">

<div class="offcanvas-header">

    <h4 class="fw-bold text-primary">

       {{ setting('app_name') }} | ADMIN

    </h4>

    <button

    class="btn-close"

    data-bs-dismiss="offcanvas">

    </button>

</div>

<div class="offcanvas-body p-0">

    <div class="list-group list-group-flush">

        <a href="{{ route('admin.dashboard') }}"
           class="list-group-item">

            <i class="fa-solid fa-house me-2"></i>

            Dashboard

        </a>

        <a href="{{route('admin.game.index')}}"
        class="list-group-item">

        <i class="fa-solid fa-store me-2"></i>

        Game
        </a>



            <a href="{{ route('admin.item-category.index') }}"
            class="list-group-item">

                <i class="fa-solid fa-layer-group me-2"></i>

                Kategori Item

            </a>
            
<a href="{{ route('admin.stock.index') }}"
class="list-group-item">

    <i class="fa-solid fa-boxes-stacked me-2"></i>

    Manajemen Stock

</a>


        <a href="{{route('admin.discount.index')}}"
        class="list-group-item">


        <i class="fa-solid fa-ticket me-2"></i>

        Voucher Diskon


        </a>

        <a href="{{ route('admin.banner.index') }}"
        class="list-group-item">

        <i class="fa-solid fa-images me-2"></i>

        Banner

        </a>

<a href="{{ route('admin.payment.index') }}"
class="list-group-item">

    <i class="fa-solid fa-credit-card me-2"></i>

    Metode Pembayaran

</a>

<a href="{{ route('admin.payment-confirmation.index') }}"
class="list-group-item">

    <i class="fa-solid fa-money-check-dollar me-2"></i>

    Konfirmasi Pembayaran

    @php
        $waiting = \App\Models\Order::where('status','Paid')->count();
    @endphp

    @if($waiting)
        <span class="badge bg-danger float-end">
            {{ $waiting }}
        </span>
    @endif

</a>        


        <a href="#"
        class="list-group-item"
        data-bs-toggle="modal"
        data-bs-target="#adminProfileModal">

        <i class="fa-solid fa-user-shield me-2"></i>

            Profil Admin


        </a>

    <a href="{{ route('admin.setting.index') }}"
class="list-group-item">

    <i class="fa-solid fa-gears me-2"></i>

    Pengaturan

</a>

<a href="{{ route('admin.activity-log.index') }}"
class="list-group-item">

    <i class="fa-solid fa-clock-rotate-left me-2"></i>

    Activity Log

</a>

        @auth
        <form
            action="{{ route('logout') }}"
            method="POST">

            @csrf

            <button
                class="list-group-item list-group-item-action text-danger border-0 w-100 text-start">

                <i class="fa-solid fa-right-from-bracket me-2"></i>

                Logout

            </button>

        </form>
        @endauth
    </div>

</div>

</div>