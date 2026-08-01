<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<title>
@yield('title','TOPUP | ADMIN')
</title>

@if(setting('app_favicon'))

<link
    rel="icon"
    type="image/png"
    href="{{ asset('storage/'.setting('app_favicon')) }}">

@endif
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
rel="stylesheet">

<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('styles')
<link 
href="{{ asset('assets/css/footer.css') }}"
rel="stylesheet">
@include('admin.layouts.topbar')
</head>


<body>


<div class="page-wrapper">
    
@include('admin.layouts.sidebar')

<main class="main-content">



<div class="content-wrapper">


@yield('content')


</div>





</main>


</div>
@include('admin.profile.modal')


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


@stack('scripts')


</body>

@include('admin.layouts.footer')
</html>