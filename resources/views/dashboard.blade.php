@extends('layouts.app')

@section('content')

<div class="dashboard-page">

    <div class="container-fluid">

        {{-- =====================================================
             BANNER
        ====================================================== --}}

        @include('components.dashboard.banner')


        {{-- =====================================================
             TOP SELLER
        ====================================================== --}}

        @include('components.dashboard.top-seller')


        {{-- =====================================================
             SEMUA GAME
        ====================================================== --}}

        @include('components.dashboard.games-section')

    </div>

</div>

@endsection


@push('scripts')

<script src="{{ asset('js/dashboard.js') }}"></script>

@endpush