@extends('admin.layouts.app')

@section('title','Pengaturan Website')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                <i class="fa-solid fa-gears me-2 text-primary"></i>
                Pengaturan Website
            </h3>

            <small class="text-muted">
                Kelola seluruh konfigurasi website TopUp.
            </small>

        </div>

    </div>

    @if(session('success'))

    <div class="alert alert-success">

        <i class="fa-solid fa-circle-check me-2"></i>

        {{ session('success') }}

    </div>

    @endif

    <form
        action="{{ route('admin.setting.update',1) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="card shadow border-0 rounded-4">

            <div class="card-body">

                <ul class="nav nav-pills mb-4" id="setting-tab">

                    <li class="nav-item">
                        <button
                            type="button"
                            class="nav-link active"
                            data-bs-toggle="pill"
                            data-bs-target="#general">

                            <i class="fa-solid fa-sliders me-1"></i>

                            General

                        </button>
                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#website">

                            <i class="fa-solid fa-globe me-1"></i>

                            Website

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#game">

                            <i class="fa-solid fa-gamepad me-1"></i>

                            Game

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#contact">

                            <i class="fa-solid fa-phone me-1"></i>

                            Contact

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#social">

                            <i class="fa-solid fa-share-nodes me-1"></i>

                            Social

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#system">

                            <i class="fa-solid fa-server me-1"></i>

                            System

                        </button>

                    </li>

                </ul>

                <div class="tab-content">

                    {{-- GENERAL --}}

                    <div
                        class="tab-pane fade show active"
                        id="general">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Nama Website

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="app_name"
                                    value="{{ $settings['app_name']->setting_value ?? '' }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Nama Perusahaan

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="company_name"
                                    value="{{ $settings['company_name']->setting_value ?? '' }}">

                            </div>

                        </div>

                    </div>

                    {{-- WEBSITE --}}

                    <div
                        class="tab-pane fade"
                        id="website">

                        <div class="mb-3">

                            <label class="form-label">

                                Hero Title

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="hero_title"
                                value="{{ $settings['hero_title']->setting_value ?? '' }}">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Hero Subtitle

                            </label>

                            <textarea
                                class="form-control"
                                rows="3"
                                name="hero_subtitle">{{ $settings['hero_subtitle']->setting_value ?? '' }}</textarea>

                        </div>

                    </div>

                    {{-- GAME --}}

                    <div
                        class="tab-pane fade"
                        id="game">

                        <div class="table-responsive">

                            <table class="table table-bordered align-middle">

                                <thead class="table-light">

                                <tr>

                                    <th width="220">
                                        Game
                                    </th>

                                    <th>
                                        Label Input 1
                                    </th>

                                    <th>
                                        Placeholder 1
                                    </th>

                                    <th>
                                        Label Input 2
                                    </th>

                                    <th>
                                        Placeholder 2
                                    </th>

                                </tr>

                                </thead>

                                <tbody>

                                @foreach($games as $game)

                                <tr>

                                    <td>

                                        <strong>

                                            {{ $game->game_name }}

                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            {{ ucfirst(str_replace('_',' ',$game->player_input_type)) }}

                                        </small>

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="games[{{ $game->id }}][input_label]"
                                            value="{{ $game->input_label }}">

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="games[{{ $game->id }}][input_placeholder]"
                                            value="{{ $game->input_placeholder }}">

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="games[{{ $game->id }}][input_label_2]"
                                            value="{{ $game->input_label_2 }}">

                                    </td>

                                    <td>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="games[{{ $game->id }}][input_placeholder_2]"
                                            value="{{ $game->input_placeholder_2 }}">

                                    </td>

                                </tr>

                                @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                    {{-- CONTACT --}}

                    <div
                        class="tab-pane fade"
                        id="contact">

                        <div class="mb-3">

                            <label class="form-label">

                                Whatsapp

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="whatsapp"
                                value="{{ $settings['whatsapp']->setting_value ?? '' }}">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Email

                            </label>

                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                value="{{ $settings['email']->setting_value ?? '' }}">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Alamat

                            </label>

                            <textarea
                                class="form-control"
                                rows="3"
                                name="address">{{ $settings['address']->setting_value ?? '' }}</textarea>

                        </div>

                    </div>

                    {{-- SOCIAL --}}

                    <div
                        class="tab-pane fade"
                        id="social">

                        <input
                            class="form-control mb-3"
                            placeholder="Facebook"
                            name="facebook"
                            value="{{ $settings['facebook']->setting_value ?? '' }}">

                        <input
                            class="form-control mb-3"
                            placeholder="Instagram"
                            name="instagram"
                            value="{{ $settings['instagram']->setting_value ?? '' }}">

                        <input
                            class="form-control"
                            placeholder="Youtube"
                            name="youtube"
                            value="{{ $settings['youtube']->setting_value ?? '' }}">

                    </div>

                    {{-- SYSTEM --}}

                    <div
                        class="tab-pane fade"
                        id="system">

                        <div class="form-check form-switch mb-3">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="maintenance"
                                value="1"
                                {{ (($settings['maintenance']->setting_value ?? 0) == 1) ? 'checked' : '' }}>

                            <label class="form-check-label">

                                Maintenance Mode

                            </label>

                        </div>

                        <div class="form-check form-switch">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="allow_guest_checkout"
                                value="1"
                                {{ (($settings['allow_guest_checkout']->setting_value ?? 1) == 1) ? 'checked' : '' }}>

                            <label class="form-check-label">

                                Izinkan Guest Checkout

                            </label>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card-footer bg-white">

                <button
                    class="btn btn-primary">

                    <i class="fa-solid fa-floppy-disk me-2"></i>

                    Simpan Pengaturan

                </button>

            </div>

        </div>

    </form>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // buka tab terakhir
    const activeTab = localStorage.getItem('setting-active-tab');

    if(activeTab){
        const trigger = document.querySelector(
            '[data-bs-target="' + activeTab + '"]'
        );

        if(trigger){
            bootstrap.Tab.getOrCreateInstance(trigger).show();
        }
    }

    // simpan tab yang dipilih
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {

        tab.addEventListener('shown.bs.tab', function(e){

            localStorage.setItem(
                'setting-active-tab',
                e.target.dataset.bsTarget
            );

        });

    });

});
</script>
@endpush
@endsection