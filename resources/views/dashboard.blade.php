@extends('layouts.app')

@section('content')

<div class="dashboard-page">

    <div class="container-fluid">

        @include('components.dashboard.banner')

        @include('components.dashboard.top-seller')

        @include('components.dashboard.games-section')

    </div>

</div>

@endsection