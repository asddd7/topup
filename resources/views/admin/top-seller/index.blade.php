@extends('admin.layouts.app')

@section('title', 'Pengaturan Top Seller')

@section('content')

<div class="top-seller-page">

    <div class="container-fluid">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="top-seller-header">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div class="top-seller-header-content">

                    <h3 class="top-seller-title">
                        Pengaturan Top Seller
                    </h3>

                    <p class="top-seller-description">
                        Cari item, filter game dan stock, lalu pilih item
                        yang ingin ditampilkan sebagai Top Seller.
                    </p>

                </div>


                <a
                    href="{{ route('admin.dashboard') }}"
                    class="top-seller-back-button"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    <span>
                        Kembali
                    </span>

                </a>

            </div>

        </div>


        {{-- =====================================================
             SUCCESS MESSAGE
        ====================================================== --}}

        @if(session('success'))

            <div class="alert alert-success mb-3">

                <i class="fa-solid fa-circle-check me-1"></i>

                {{ session('success') }}

            </div>

        @endif


        {{-- =====================================================
             SEARCH / FILTER
        ====================================================== --}}

        <div class="top-seller-filter-card card shadow-sm border-0">

            <div class="card-body">

                <form
                    method="GET"
                    action="{{ route('top-seller.index') }}"
                >

                    <div class="row g-3 align-items-end">

                        {{-- SEARCH --}}

                        <div class="col-md-5">

                            <label class="form-label">
                                Cari Item
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="fa-solid fa-magnifying-glass"></i>

                                </span>

                                <input
                                    type="search"
                                    name="search"
                                    value="{{ $search }}"
                                    class="form-control"
                                    placeholder="Nama item atau game..."
                                    autocomplete="off"
                                >

                            </div>

                        </div>


                        {{-- GAME --}}

                        <div class="col-md-3">

                            <label class="form-label">
                                Game
                            </label>

                            <select
                                name="game_id"
                                class="form-select"
                            >

                                <option value="">
                                    Semua Game
                                </option>

                                @foreach($games as $game)

                                    <option
                                        value="{{ $game->id }}"
                                        {{ (string) $gameId === (string) $game->id ? 'selected' : '' }}
                                    >

                                        {{ $game->game_name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- STOCK --}}

                        <div class="col-md-2">

                            <label class="form-label">
                                Stock
                            </label>

                            <select
                                name="stock_status"
                                class="form-select"
                            >

                                <option
                                    value="all"
                                    {{ $stockStatus === 'all' ? 'selected' : '' }}
                                >
                                    Semua
                                </option>

                                <option
                                    value="instock"
                                    {{ $stockStatus === 'instock' ? 'selected' : '' }}
                                >
                                    In Stock
                                </option>

                                <option
                                    value="outofstock"
                                    {{ $stockStatus === 'outofstock' ? 'selected' : '' }}
                                >
                                    Out of Stock
                                </option>

                                <option
                                    value="unknown"
                                    {{ $stockStatus === 'unknown' ? 'selected' : '' }}
                                >
                                    Unknown
                                </option>

                            </select>

                        </div>


                        {{-- BUTTON --}}

                        <div class="col-md-2">

                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary flex-grow-1"
                                >

                                    <i class="fa-solid fa-filter me-1"></i>

                                    Filter

                                </button>


                                <a
                                    href="{{ route('top-seller.index') }}"
                                    class="btn btn-outline-secondary"
                                    title="Reset Filter"
                                    aria-label="Reset Filter"
                                >

                                    <i class="fa-solid fa-rotate-left"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- =====================================================
             RESULT INFO
        ====================================================== --}}

        <div class="top-seller-result-info d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div>

                Menampilkan

                <strong>
                    {{ $items->count() }}
                </strong>

                item

                @if($search)

                    untuk:

                    <strong>
                        "{{ $search }}"
                    </strong>

                @endif

            </div>


            @if($stockStatus !== 'all')

                <span class="top-seller-filter-badge">

                    <i class="fa-solid fa-filter me-1"></i>

                    Stock:

                    {{ $stockStatus === 'instock'
                        ? 'In Stock'
                        : ($stockStatus === 'outofstock'
                            ? 'Out of Stock'
                            : 'Unknown')
                    }}

                </span>

            @endif

        </div>


        {{-- =====================================================
             TOP SELLER FORM
        ====================================================== --}}

        <form
            method="POST"
            action="{{ route('top-seller.update') }}"
        >

            @csrf


            {{-- =================================================
                 KEEP FILTER STATE
            ================================================== --}}

            <input
                type="hidden"
                name="search"
                value="{{ $search }}"
            >

            <input
                type="hidden"
                name="game_id"
                value="{{ $gameId }}"
            >

            <input
                type="hidden"
                name="stock_status"
                value="{{ $stockStatus }}"
            >


            {{-- =================================================
                 ITEM LIST
            ================================================== --}}

            <div class="top-seller-list-card card shadow-sm border-0">


                {{-- HEADER --}}

                <div class="card-header">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                        <strong>
                            Daftar Item
                        </strong>


                        @if($items->count())

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <i class="fa-solid fa-save me-1"></i>

                                Simpan Top Seller

                            </button>

                        @endif

                    </div>

                </div>


                {{-- BODY --}}

                <div class="card-body">

                    <div class="row g-3">

                        @forelse($items as $item)

                            @php

                                $stockStatusItem = strtolower(
                                    trim($item->moogold_stock_status ?? '')
                                );

                                $stockStatusItemNormalized = str_replace(
                                    ' ',
                                    '',
                                    $stockStatusItem
                                );

                                $isOutOfStock =
                                    $stockStatusItemNormalized === 'outofstock';

                                $isInStock =
                                    $stockStatusItemNormalized === 'instock';

                            @endphp


                            {{-- =====================================
                                 VISIBLE ITEM
                            ====================================== --}}

                            <input
                                type="hidden"
                                name="visible_items[]"
                                value="{{ $item->id }}"
                            >


                            {{-- =====================================
                                 ITEM
                            ====================================== --}}

                            <div class="col-12 col-md-6 col-lg-4">

                                <label
                                    class="top-seller-item-card {{ $isOutOfStock ? 'is-out-of-stock' : '' }}"
                                >

                                    <div class="card-body">

                                        <div class="d-flex align-items-center gap-3">


                                            {{-- =================================
                                                 IMAGE
                                            ================================== --}}

                                            <div class="top-seller-item-image">

                                                @if($item->image)

                                                    <img
                                                        src="{{ asset('storage/' . $item->image) }}"
                                                        alt="{{ $item->item_name }}"
                                                        loading="lazy"
                                                    >

                                                @else

                                                    <div class="top-seller-image-placeholder">

                                                        <i class="fa-solid fa-box"></i>

                                                    </div>

                                                @endif

                                            </div>


                                            {{-- =================================
                                                 INFO
                                            ================================== --}}

                                            <div class="top-seller-item-content flex-grow-1">

                                                <div class="top-seller-item-name text-truncate">

                                                    {{ $item->item_name }}

                                                </div>


                                                <span class="top-seller-game-name">

                                                    {{ $item->game->game_name ?? '-' }}

                                                </span>


                                                <span class="top-seller-item-price">

                                                    Rp {{ number_format($item->price) }}

                                                </span>


                                                {{-- STOCK STATUS --}}

                                                <div class="mt-2">

                                                    @if($isInStock)

                                                        <span class="top-seller-stock in-stock">

                                                            <i class="fa-solid fa-circle-check"></i>

                                                            In Stock

                                                        </span>

                                                    @elseif($isOutOfStock)

                                                        <span class="top-seller-stock out-of-stock">

                                                            <i class="fa-solid fa-circle-xmark"></i>

                                                            Out of Stock

                                                        </span>

                                                    @else

                                                        <span class="top-seller-stock unknown">

                                                            <i class="fa-solid fa-circle-question"></i>

                                                            {{ $item->moogold_stock_status ?: 'Unknown' }}

                                                        </span>

                                                    @endif

                                                </div>

                                            </div>


                                            {{-- =================================
                                                 CHECKBOX
                                            ================================== --}}

                                            <div class="flex-shrink-0">

                                                <input
                                                    type="checkbox"
                                                    name="top_seller[]"
                                                    value="{{ $item->id }}"
                                                    class="top-seller-checkbox form-check-input"
                                                    {{ $item->top_seller && !$isOutOfStock ? 'checked' : '' }}
                                                    {{ $isOutOfStock ? 'disabled' : '' }}
                                                    aria-label="Jadikan {{ $item->item_name }} sebagai Top Seller"
                                                >

                                            </div>

                                        </div>

                                    </div>

                                </label>

                            </div>

                        @empty


                            {{-- =====================================
                                 EMPTY
                            ====================================== --}}

                            <div class="col-12">

                                <div class="top-seller-empty">

                                    <i class="fa-solid fa-box-open"></i>

                                    <h6>
                                        Item tidak ditemukan
                                    </h6>

                                    <p>
                                        Coba ubah kata pencarian atau filter
                                        yang digunakan.
                                    </p>

                                </div>

                            </div>


                        @endforelse

                    </div>

                </div>


                {{-- =================================================
                     FOOTER
                ================================================== --}}

                @if($items->count())

                    <div class="card-footer text-end">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fa-solid fa-save me-1"></i>

                            Simpan Perubahan

                        </button>

                    </div>

                @endif


            </div>

        </form>

    </div>

</div>

@endsection