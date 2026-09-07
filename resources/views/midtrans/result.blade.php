@extends('layouts.app')

@section('title', 'Status Pembayaran')

@section('content')

<div class="container py-5">

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4 p-md-5">

                {{-- =====================================================
                     STATUS NORMALIZATION
                ====================================================== --}}

                @php

                    $orderStatus =
                        (string) $order->status;

                    /*
                    |--------------------------------------------------------------------------
                    | Transaction status dari database lebih dipercaya.
                    |
                    | Query parameter dari callback hanya digunakan
                    | sebagai fallback tampilan.
                    |--------------------------------------------------------------------------
                    */

                    $displayTransactionStatus =
                        strtolower(
                            trim(
                                (string)
                                (
                                    $transaction?->transaction_status
                                    ?? ''
                                )
                            )
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Guest token
                    |--------------------------------------------------------------------------
                    */

                    $guestToken =
                        request()->query('token');

                    /*
                    |--------------------------------------------------------------------------
                    | Order URL
                    |--------------------------------------------------------------------------
                    */

                    $orderUrl =
                        route(
                            'order.show',
                            $order->invoice_number
                        );

                    if (
                        !$order->user_id
                        &&
                        $guestToken
                    ) {

                        $orderUrl .=
                            '?token=' .
                            urlencode(
                                $guestToken
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Payment URL
                    |--------------------------------------------------------------------------
                    */

                    $paymentUrl =
                        route(
                            'midtrans.payment',
                            [
                                'order' =>
                                    $order->id,

                                'payment' =>
                                    $order->midtrans_payment_type,
                            ]
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Midtrans payment route dapat ditambah token
                    | untuk flow guest.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$order->user_id
                        &&
                        $guestToken
                    ) {

                        $paymentUrl .=
                            '?payment=' .
                            urlencode(
                                (string)
                                $order->midtrans_payment_type
                            )
                            . '&token=' .
                            urlencode(
                                $guestToken
                            );
                    }

                @endphp


                {{-- =====================================================
                     PAID / SUCCESS
                ====================================================== --}}

                @if (
                    in_array(
                        $orderStatus,
                        [
                            'Paid',
                            'Processing',
                            'Completed',
                        ],
                        true
                    )
                )

                    <div class="text-center mb-4">

                        <div class="display-4 mb-3 text-success">
                            ✓
                        </div>

                        <h3 class="fw-bold">
                            Pembayaran Berhasil
                        </h3>

                        <p class="text-muted mb-0">

                            Pembayaran Anda telah diterima.

                            @if (
                                $orderStatus === 'Paid'
                            )

                                Order sedang dijadwalkan
                                untuk diproses.

                            @elseif (
                                $orderStatus === 'Processing'
                            )

                                Order Anda sedang diproses.

                            @elseif (
                                $orderStatus === 'Completed'
                            )

                                Order Anda telah selesai.

                            @endif

                        </p>

                    </div>


                {{-- =====================================================
                     WAITING PAYMENT
                ====================================================== --}}

                @elseif (
                    $orderStatus === 'Waiting Payment'
                )

                    <div class="text-center mb-4">

                        <div class="display-4 mb-3 text-warning">
                            ⏳
                        </div>

                        <h3 class="fw-bold">
                            Menunggu Konfirmasi Pembayaran
                        </h3>

                        <p class="text-muted mb-0">

                            Jika Anda sudah menyelesaikan pembayaran,
                            sistem sedang menunggu konfirmasi
                            dari Midtrans.

                        </p>

                    </div>


                {{-- =====================================================
                     CANCELLED / FAILED
                ====================================================== --}}

                @elseif (
                    in_array(
                        $orderStatus,
                        [
                            'Cancelled',
                            'Failed',
                        ],
                        true
                    )
                )

                    <div class="text-center mb-4">

                        <div class="display-4 mb-3 text-danger">
                            ✕
                        </div>

                        <h3 class="fw-bold">
                            Pembayaran Tidak Berhasil
                        </h3>

                        <p class="text-muted mb-0">

                            Transaksi pembayaran tidak dapat
                            diproses.

                            Silakan lakukan pembayaran kembali.

                        </p>

                    </div>


                {{-- =====================================================
                     OTHER STATUS
                ====================================================== --}}

                @else

                    <div class="text-center mb-4">

                        <div class="display-4 mb-3 text-primary">
                            ℹ
                        </div>

                        <h3 class="fw-bold">
                            Status Order
                        </h3>

                        <p class="text-muted mb-0">

                            Status order Anda sedang diperbarui.

                        </p>

                    </div>

                @endif


                <hr class="my-4">


                {{-- =====================================================
                     ORDER INFORMATION
                ====================================================== --}}

                <div class="mb-4">

                    <h5 class="mb-3">
                        Detail Order
                    </h5>


                    {{-- Invoice --}}

                    <div
                        class="d-flex justify-content-between mb-2"
                    >

                        <span class="text-muted">
                            Invoice
                        </span>

                        <strong>
                            {{ $order->invoice_number }}
                        </strong>

                    </div>


                    {{-- Game --}}

                    <div
                        class="d-flex justify-content-between mb-2"
                    >

                        <span class="text-muted">
                            Game
                        </span>

                        <strong>
                            {{ $order->game?->game_name ?? '-' }}
                        </strong>

                    </div>


                    {{-- Total --}}

                    <div
                        class="d-flex justify-content-between mb-2"
                    >

                        <span class="text-muted">
                            Total
                        </span>

                        <strong>
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </strong>

                    </div>


                    {{-- Order Status --}}

                    <div
                        class="d-flex justify-content-between mb-2"
                    >

                        <span class="text-muted">
                            Status Order
                        </span>

                        <span
                            class="
                                badge
                                @if (
                                    in_array(
                                        $orderStatus,
                                        [
                                            'Paid',
                                            'Processing',
                                            'Completed',
                                        ],
                                        true
                                    )
                                )
                                    text-bg-success
                                @elseif (
                                    $orderStatus === 'Waiting Payment'
                                )
                                    text-bg-warning
                                @elseif (
                                    in_array(
                                        $orderStatus,
                                        [
                                            'Cancelled',
                                            'Failed',
                                        ],
                                        true
                                    )
                                )
                                    text-bg-danger
                                @else
                                    text-bg-secondary
                                @endif
                            "
                        >
                            {{ $orderStatus }}
                        </span>

                    </div>


                    {{-- =================================================
                         MIDTRANS TRANSACTION
                    ================================================== --}}

                    @if ($transaction)

                        <hr>


                        <div
                            class="
                                d-flex
                                justify-content-between
                                mb-2
                            "
                        >

                            <span class="text-muted">
                                Midtrans Order ID
                            </span>

                            <strong>
                                {{ $transaction->midtrans_order_id }}
                            </strong>

                        </div>


                        @if (
                            $displayTransactionStatus !== ''
                        )

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    mb-2
                                "
                            >

                                <span class="text-muted">
                                    Status Midtrans
                                </span>

                                <strong>
                                    {{ $displayTransactionStatus }}
                                </strong>

                            </div>

                        @endif


                        @if (
                            $transaction->payment_type
                        )

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    mb-2
                                "
                            >

                                <span class="text-muted">
                                    Metode Pembayaran
                                </span>

                                <strong>
                                    {{
                                        strtoupper(
                                            $transaction
                                                ->payment_type
                                        )
                                    }}
                                </strong>

                            </div>

                        @endif


                        @if (
                            $transaction->paid_at
                        )

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    mb-2
                                "
                            >

                                <span class="text-muted">
                                    Dibayar Pada
                                </span>

                                <strong>
                                    {{
                                        $transaction
                                            ->paid_at
                                            ->format(
                                                'd M Y H:i'
                                            )
                                    }}
                                </strong>

                            </div>

                        @endif


                        @if (
                            $transaction->expired_at
                        )

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    mb-2
                                "
                            >

                                <span class="text-muted">
                                    Kedaluwarsa
                                </span>

                                <strong>
                                    {{
                                        $transaction
                                            ->expired_at
                                            ->format(
                                                'd M Y H:i'
                                            )
                                    }}
                                </strong>

                            </div>

                        @endif

                    @endif

                </div>


                {{-- =====================================================
                     IMPORTANT INFORMATION
                ====================================================== --}}

                @if (
                    $orderStatus === 'Waiting Payment'
                )

                    <div
                        class="alert alert-info"
                    >

                        <strong>
                            Catatan:
                        </strong>

                        Halaman ini belum menentukan
                        pembayaran berhasil.

                        Status pembayaran akan diperbarui
                        setelah webhook Midtrans diterima
                        dan diverifikasi oleh server.

                    </div>

                @endif


                {{-- =====================================================
                     ACTIONS
                ====================================================== --}}

                <div class="d-grid gap-2">


                    {{-- =============================================
                         PAID / PROCESSING / COMPLETED
                    ============================================== --}}

                    @if (
                        in_array(
                            $orderStatus,
                            [
                                'Paid',
                                'Processing',
                                'Completed',
                            ],
                            true
                        )
                    )

                        <a
                            href="{{ $orderUrl }}"
                            class="btn btn-primary"
                        >
                            Lihat Detail Order
                        </a>


                    {{-- =============================================
                         WAITING PAYMENT
                    ============================================== --}}

                    @elseif (
                        $orderStatus === 'Waiting Payment'
                    )

                        <a
                            href="{{ $paymentUrl }}"
                            class="btn btn-primary"
                        >
                            Lanjutkan Pembayaran
                        </a>


                        <a
                            href="{{ $orderUrl }}"
                            class="btn btn-outline-primary"
                        >
                            Refresh Status Order
                        </a>


                    {{-- =============================================
                         OTHER
                    ============================================== --}}

                    @else

                        <a
                            href="{{ $orderUrl }}"
                            class="btn btn-outline-primary"
                        >
                            Lihat Order
                        </a>

                    @endif


                    <a
                        href="{{ route('game.show', $order->game_id) }}"
                        class="btn btn-outline-secondary"
                    >
                        Kembali ke Game
                    </a>

                </div>


                {{-- =====================================================
                     CALLBACK INFORMATION
                ====================================================== --}}

                @if (
                    $midtransOrderId
                )

                    <div
                        class="
                            alert
                            alert-light
                            border
                            mt-4
                            mb-0
                            small
                        "
                    >

                        <strong>
                            Informasi Callback
                        </strong>

                        <br>

                        Midtrans Order ID:
                        {{ $midtransOrderId }}


                        @if (
                            $statusCode
                        )

                            <br>

                            Status Code:
                            {{ $statusCode }}

                        @endif


                        @if (
                            $transactionStatus
                        )

                            <br>

                            Transaction Status Callback:
                            {{ $transactionStatus }}

                        @endif

                    </div>

                @endif


            </div>

        </div>

    </div>

</div>

</div>

@endsection
