@extends('layouts.app')

@section('content')

<div class="order-history-page">

    <div class="order-history-container">


        {{-- =====================================================
             PAGE HEADER
        ====================================================== --}}

        <div class="order-history-header">

            <div>

                <span class="order-history-eyebrow">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    RIWAYAT TRANSAKSI
                </span>

                <h1 class="order-history-title">
                    Pesanan Saya
                </h1>

                <p class="order-history-description">
                    Lihat riwayat pesanan dan status transaksi Anda.
                </p>

            </div>

        </div>


        {{-- =====================================================
             ORDER LIST
        ====================================================== --}}

        @forelse($orders as $order)

            @php

                $detail = $order->details->first();

                $game = $order->game;

                $item = $detail?->item;

            @endphp


            <article class="order-history-card">


                {{-- =================================================
                     ORDER TOP
                ================================================== --}}

                <div class="order-card-top">


                    {{-- GAME --}}
                    <div class="order-game-info">

                        <div class="order-game-image">

                            @if($game?->game_logo)

                                <img
                                    src="{{ asset('storage/' . $game->game_logo) }}"
                                    alt="{{ $game->game_name }}"
                                    loading="lazy"
                                >

                            @else

                                <div class="order-image-placeholder">
                                    <i class="fa-solid fa-gamepad"></i>
                                </div>

                            @endif

                        </div>


                        <div class="order-game-text">

                            <span class="order-label">
                                GAME
                            </span>

                            <h2>
                                {{ $game?->game_name ?? '-' }}
                            </h2>

                        </div>

                    </div>


                    {{-- STATUS --}}
                    <div class="order-status-wrapper">

                        <span class="order-label">
                            STATUS
                        </span>

                        @if($order->status === 'Waiting Payment')

                            <span class="order-status status-waiting">
                                <i class="fa-solid fa-hourglass-half"></i>
                                {{ $order->status }}
                            </span>

                        @elseif($order->status === 'Paid')

                            <span class="order-status status-paid">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ $order->status }}
                            </span>

                        @elseif($order->status === 'Processing')

                            <span class="order-status status-processing">
                                <i class="fa-solid fa-spinner"></i>
                                {{ $order->status }}
                            </span>

                        @elseif($order->status === 'Completed')

                            <span class="order-status status-completed">
                                <i class="fa-solid fa-check"></i>
                                {{ $order->status }}
                            </span>

                        @elseif($order->status === 'Cancelled')

                            <span class="order-status status-cancelled">
                                <i class="fa-solid fa-xmark"></i>
                                {{ $order->status }}
                            </span>

                        @else

                            <span class="order-status status-default">
                                <i class="fa-solid fa-clock"></i>
                                {{ $order->status }}
                            </span>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     PRODUCT
                ================================================== --}}

                <div class="order-product-row">


                    <div class="order-product-info">

                        <div class="order-product-image">

                            @if($item?->image)

                                <img
                                    src="{{ asset('storage/' . $item->image) }}"
                                    alt="{{ $item->item_name }}"
                                    loading="lazy"
                                >

                            @else

                                <div class="order-image-placeholder">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>

                            @endif

                        </div>


                        <div class="order-product-text">

                            <span class="order-label">
                                PRODUK
                            </span>

                            <h3>
                                {{ $item?->item_name ?? 'Produk tidak tersedia' }}
                            </h3>

                            @if($detail)

                                <span class="order-product-qty">
                                    Qty {{ $detail->qty }}
                                </span>

                            @endif

                        </div>

                    </div>


                    {{-- TOTAL --}}
                    <div class="order-price">

                        <span class="order-label">
                            TOTAL
                        </span>

                        <strong>
                            Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}
                        </strong>

                    </div>

                </div>


                {{-- =================================================
                     ORDER META
                ================================================== --}}

                <div class="order-card-meta">

                    <div class="order-invoice">

                        <span class="order-label">
                            INVOICE
                        </span>

                        <strong>
                            {{ $order->invoice_number }}
                        </strong>

                    </div>


                    <div class="order-action">


                        {{-- DETAIL --}}
                        <a
                            href="{{ route(
                                'order.show',
                                [
                                    'invoice' => $order->invoice_number,
                                    'token' => $order->guest_token
                                ]
                            ) }}"
                            class="order-detail-button"
                        >

                            <i class="fa-solid fa-eye"></i>

                            <span>
                                Detail
                            </span>

                        </a>


                        {{-- BAYAR --}}
                        @if($order->status === 'Waiting Payment')

                            <a
                                href="{{ route('midtrans.payment', ['order' => $order]) }}"
                                class="order-payment-button"
                            >

                                <i class="fa-solid fa-credit-card"></i>

                                <span>
                                    Bayar
                                </span>

                            </a>

                        @endif

                    </div>

                </div>


            </article>


        @empty


            {{-- =================================================
                 EMPTY
            ================================================== --}}

            <div class="order-empty-state">

                <div class="order-empty-icon">

                    <i class="fa-solid fa-receipt"></i>

                </div>

                <h3>
                    Belum Ada Pesanan
                </h3>

                <p>
                    Riwayat pesanan Anda akan muncul di halaman ini.
                </p>

                <a
                    href="{{ route('dashboard') }}"
                    class="order-empty-button"
                >
                    <i class="fa-solid fa-gamepad me-1"></i>
                    Mulai Top Up
                </a>

            </div>

        @endforelse


    </div>

</div>

@endsection