<div class="card shadow-sm border-0 mb-4">

    <div class="card-body d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center gap-3">

            <button
                class="btn btn-outline-primary d-lg-none"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebar">

                <i class="fa-solid fa-bars"></i>

            </button>

            @if(setting('app_logo'))

                <img
                    src="{{ asset('storage/'.setting('app_logo')) }}"
                    alt="Logo"
                    style="height:45px; width:auto;">

            @endif

            <div>

                <div class="fw-bold fs-5">
                    {{ setting('app_name','TopUp Game') }}
                </div>

                <small class="text-muted">
                    Admin Dashboard
                </small>

            </div>

        </div>

        <div class="d-flex align-items-center gap-3">

<div class="dropdown">


<button
    class="btn btn-light position-relative"
    data-bs-toggle="dropdown">


<i class="fa-regular fa-bell fs-5"></i>


@if(($notificationCount ?? 0) > 0)

<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

{{ $notificationCount ?? 0 }}

</span>

@endif


</button>


<ul class="dropdown-menu dropdown-menu-end shadow notification-dropdown">


<li class="dropdown-header fw-bold">

    Notifikasi

</li>



@forelse($notifications as $notif)


<li>

<a href="#"
class="dropdown-item">

    <div class="notification-title">

        {{ $notif->title }}

    </div>


    <div class="notification-message">

        {{ $notif->message }}

    </div>


</a>

</li>


@endforeach



@if($notifications->count()==0)

<li>

<div class="text-center text-muted p-3">

Tidak ada notifikasi

</div>

</li>

@endif


</ul>


</div>

            <div class="text-end">

                <div class="fw-semibold">
                    {{ optional(Auth::user())->name }}
                </div>

                <small class="text-muted">
                    {{ optional(Auth::user())->email }}
                </small>

            </div>

        </div>

    </div>

</div>