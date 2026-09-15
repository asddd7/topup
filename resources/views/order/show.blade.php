@extends('layouts.app')

@section('content')

<div class="order-detail-page">

    <div class="order-detail-container">


        {{-- =====================================================
             BACK
        ====================================================== --}}

        @auth

            <div class="order-detail-back">

                <a
                    href="{{ route('order.index') }}"
                    class="order-back-button"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Pesanan</span>
                </a>

            </div>

        @endauth


        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="order-detail-header">

            <div>

                <span class="order-detail-eyebrow">
                    <i class="fa-solid fa-receipt"></i>
                    DETAIL TRANSAKSI
                </span>

                <h1 class="order-detail-title">
                    Detail Pesanan
                </h1>

                <p class="order-detail-description">
                    Informasi lengkap mengenai pesanan Anda.
                </p>

            </div>

        </div>


        {{-- =====================================================
             MAIN CARD
        ====================================================== --}}

        <div class="order-detail-card">


            {{-- =================================================
                 ORDER HEADER
            ================================================== --}}

            <div class="order-detail-card-header">

                <div class="order-detail-game">

                    <div class="order-detail-game-image">

                        @if($order->game?->game_logo)

                            <img
                                src="{{ asset('storage/' . $order->game->game_logo) }}"
                                alt="{{ $order->game->game_name }}"
                            >

                        @else

                            <div class="order-detail-placeholder">
                                <i class="fa-solid fa-gamepad"></i>
                            </div>

                        @endif

                    </div>


                    <div class="order-detail-game-text">

                        <span class="order-detail-small-label">
                            GAME
                        </span>

                        <h2>
                            {{ $order->game?->game_name ?? '-' }}
                        </h2>

                        <span class="order-detail-invoice">
                            {{ $order->invoice_number }}
                        </span>

                    </div>

                </div>


                {{-- STATUS --}}
                <div class="order-detail-status">

                    <span class="order-detail-small-label">
                        STATUS
                    </span>

                    @if($order->status === 'Waiting Payment')

                        <span class="order-status status-waiting">
                            <i class="fa-solid fa-hourglass-half"></i>
                            Waiting Payment
                        </span>

                    @elseif($order->status === 'Paid')

                        <span class="order-status status-paid">
                            <i class="fa-solid fa-circle-check"></i>
                            Paid
                        </span>

                    @elseif($order->status === 'Processing')

                        <span class="order-status status-processing">
                            <i class="fa-solid fa-spinner"></i>
                            Processing
                        </span>

                    @elseif($order->status === 'Completed')

                        <span class="order-status status-completed">
                            <i class="fa-solid fa-check"></i>
                            Completed
                        </span>

                    @elseif($order->status === 'Cancelled')

                        <span class="order-status status-cancelled">
                            <i class="fa-solid fa-xmark"></i>
                            Cancelled
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
                 PLAYER DATA
            ================================================== --}}

            <div class="order-detail-section">

                <div class="order-detail-section-heading">

                    <div class="order-detail-section-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>

                        <h3>
                            Data Player
                        </h3>

                        <p>
                            Informasi akun yang digunakan untuk top up.
                        </p>

                    </div>

                </div>


                <div class="order-player-data">

                    @foreach($order->game->player_fields ?? [] as $field)

                        <div class="order-player-item">

                            <span>
                                {{ $field['label'] }}
                            </span>

                            <strong>
                                {{ $order->player_data[$field['name']] ?? '-' }}
                            </strong>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- =================================================
                 ORDER ITEMS
            ================================================== --}}

            <div class="order-detail-section">

                <div class="order-detail-section-heading">

                    <div class="order-detail-section-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>

                    <div>

                        <h3>
                            Produk Pesanan
                        </h3>

                        <p>
                            Detail item yang Anda pesan.
                        </p>

                    </div>

                </div>


                <div class="order-detail-items">

                    @foreach($order->details as $detail)

                        <div class="order-detail-item">

                            <div class="order-detail-item-info">

                                <div class="order-detail-item-image">

                                    @if($detail->item?->image)

                                        <img
                                            src="{{ asset('storage/' . $detail->item->image) }}"
                                            alt="{{ $detail->item->item_name }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <div class="order-detail-placeholder">
                                            <i class="fa-solid fa-box-open"></i>
                                        </div>

                                    @endif

                                </div>


                                <div class="order-detail-item-text">

                                    <span class="order-detail-small-label">
                                        ITEM
                                    </span>

                                    <h4>
                                        {{ $detail->item?->item_name ?? '-' }}
                                    </h4>

                                    <span>
                                        Qty {{ $detail->qty }}
                                    </span>

                                </div>

                            </div>


                            <div class="order-detail-item-price">

                                Rp
                                {{ number_format((float) $detail->subtotal, 0, ',', '.') }}

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- =================================================
                 TOTAL
            ================================================== --}}

            <div class="order-detail-total">

                <span>
                    Total Pembayaran
                </span>

                <strong>
                    Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}
                </strong>

            </div>


            {{-- =================================================
                 WAITING PAYMENT
            ================================================== --}}

            @if($order->status === 'Waiting Payment')

                <div class="order-payment-alert">

                    <div class="order-payment-alert-icon">

                        <i class="fa-solid fa-credit-card"></i>

                    </div>


                    <div class="order-payment-alert-content">

                        <h4>
                            Menunggu Pembayaran
                        </h4>

                        <p>
                            Selesaikan pembayaran melalui Midtrans
                            untuk melanjutkan pesanan Anda.
                        </p>

                    </div>


                    <a
                        href="{{ route('midtrans.payment', ['order' => $order]) }}"
                        class="order-midtrans-button"
                    >

                        <i class="fa-solid fa-credit-card"></i>

                        <span>
                            Lanjutkan Pembayaran
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </div>

            @endif


        </div>

    </div>

</div>

@endsection