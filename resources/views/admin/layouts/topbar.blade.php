<div class="card shadow-sm border-0 mb-4">

<div class="card-body d-flex justify-content-between align-items-center">

<div>

<button

class="btn btn-outline-primary d-lg-none"

data-bs-toggle="offcanvas"

data-bs-target="#sidebar">

<i class="fa-solid fa-bars"></i>

</button>

</div>

<div class="d-flex align-items-center gap-3">

<i class="fa-regular fa-bell fs-5"></i>

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