@extends('layouts.app')


@section('content')


<div class="container-fluid">


<div class="banner-wrapper">
    
    <div id="bannerCarousel"
     class="carousel slide"
     data-bs-ride="carousel"
     data-bs-interval="3000">

        @foreach($banners as $banner)


<div class="carousel-item 
{{ $loop->first ? 'active' : '' }}">


@if($banner->url != '#')

<a href="{{ $banner->url }}"
@if($banner->link && !$banner->game_id)
target="_blank"
@endif>

<div class="banner-frame">

    <img
        src="{{ asset('storage/'.$banner->image) }}"
        class="banner-image d-block w-100">

</div>

</a>

@else

<div class="banner-frame">

    <img
        src="{{ asset('storage/'.$banner->image) }}"
        class="banner-image d-block w-100">

    <!-- Tombol kiri -->

    <button class="carousel-control-prev"
            type="button"
            data-bs-target="#bannerCarousel"
            data-bs-slide="prev">

        <span class="carousel-control-prev-icon"></span>

    </button>



    <!-- Tombol kanan -->

    <button class="carousel-control-next"
            type="button"
            data-bs-target="#bannerCarousel"
            data-bs-slide="next">

        <span class="carousel-control-next-icon"></span>

    </button>

</div>

@endif


</div>


@endforeach


    </div>




</div>

<div class="section-box">

<h4 class="section-title">

🔥 Top Seller

</h4>

    <div class="row">

        @foreach($topSellers as $item)

        <div class="col-6 col-md-3 col-lg-2 mb-3">

            <a href="{{ route('game.show',$item->game_id) }}"
               class="text-decoration-none text-dark">

                <div class="card top-card">

                    @if($item->image)

                        <img src="{{ asset('storage/'.$item->image) }}"
                             class="card-img-top"
                             style="height:130px;object-fit:cover;">

                    @endif

                    <div class="card-body text-center">

                        <small class="text-muted">

                            {{ $item->game->game_name }}

                        </small>

                        <h6 class="mt-2">

                            {{ $item->item_name }}

                        </h6>

                    <div class="price">

                    Rp {{ number_format($item->price) }}

                    </div>

                    </div>

                </div>

            </a>

        </div>

        @endforeach

    </div>

</div>

<div class="section-box">

    <h4 class="section-title">

        🎮 Semua Game

    </h4>

    <div class="row">

@foreach($games as $game)

<div class="col-4 col-md-3 col-lg-2 mb-4">

<a href="{{ route('game.show',$game->id) }}"
class="game-card">

@if($game->game_logo)

<img
src="{{ asset('storage/'.$game->game_logo) }}"
class="game-logo">

@else

<div class="game-placeholder">

<i class="fa-solid fa-gamepad"></i>

</div>

@endif

<div class="game-name">

{{ $game->game_name }}

</div>

</a>

</div>

@endforeach

    </div>

</div>

</div>


</div>


@endsection