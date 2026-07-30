@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

<div class="container-fluid">

    <h2 class="mb-4">
        Dashboard Admin
    </h2>

    <div class="card shadow-sm">

        <div class="card-body">

            Selamat datang,
            <strong>{{ Auth::user()->name }}</strong>

        </div>

    </div>

</div>

@endsection