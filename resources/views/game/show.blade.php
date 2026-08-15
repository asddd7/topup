@extends('layouts.app')

@section('title')
    {{ $game->game_name }}
@endsection

@section('content')

<div
    class="game-show-page"
    data-game-id="{{ $game->id }}"
>

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
                @json(route('voucher.calculate'))
        };
    </script>

    <script
        src="{{ asset('assets/js/game-show.js') }}"
        defer
    ></script>

@endpush

@endsection