@extends('admin.layouts.app')

@section('title','Dashboard Admin')

@section('content')

<div class="container-fluid">

<h2 class="mb-4">

Dashboard Admin

</h2>

<div class="card shadow-sm mb-4">

<div class="card-body">

Selamat datang,

<strong>{{ Auth::user()->name }}</strong>

</div>

</div>


<h4 class="fw-bold mb-3">

Shortcut Pengaturan Game

</h4>

<div class="row">

@foreach($games as $game)

<div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">

<a
href="{{ route('admin.game.manage',$game->id) }}"
class="text-decoration-none">

<div class="card shadow h-100 text-center">

<div class="card-body">

@if($game->game_logo)

<img
src="{{ asset('storage/'.$game->game_logo) }}"
class="img-fluid rounded mb-3"
style="
height:80px;
object-fit:contain;
">

@endif

<h6>

{{ $game->game_name }}

</h6>

<small class="text-muted">

{{ $game->items()->count() }}

Item

</small>

<br>

<small class="text-success">

{{ $game->discounts()->count() }}

Voucher

</small>

</div>

</div>

</a>

</div>

@endforeach

</div>

</div>

@endsection