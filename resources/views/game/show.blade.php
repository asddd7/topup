@extends('layouts.app')

@section('title')
    {{ $game->game_name }}
@endsection

@section('content')

<div
    id="game-show-page"
    class="game-show-page"
    data-game-id="{{ $game->id }}"
>
@if ($errors->any())
    <div class="container mt-3">
        <div class="alert alert-danger">
            <strong>Order gagal:</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
    {{-- =====================================================
         GAME HERO
    ====================================================== --}}
    @include('game.partials.hero')


    {{-- =====================================================
         TOP UP ITEMS
    ====================================================== --}}
    @include('game.partials.items')


    {{-- =====================================================
         CHECKOUT
    ====================================================== --}}
    @include('game.partials.checkout')

</div>


{{-- =========================================================
     GAME SHOW CSS
========================================================= --}}
@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/game-show.css') }}"
    >
@endpush


{{-- =========================================================
     GAME SHOW JS
========================================================= --}}
@push('scripts')
    <script>
    window.gameShowConfig = {
        gameId: @json($game->id),

        voucherCalculateUrl:
            @json(route('voucher.calculate')),

        validatePlayerUrl:
            @json(route('game.validate-player'))
    };
    </script>

    <script
        src="{{ asset('assets/js/game-show.js') }}"
        defer
    ></script>

@endpush

@endsection