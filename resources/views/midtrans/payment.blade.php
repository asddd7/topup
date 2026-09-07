@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')

<div class="container py-5">

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <h4 class="mb-4">
                    Pembayaran Order
                </h4>


                {{-- =====================================================
                     ORDER INFO
                ====================================================== --}}

                <div class="mb-3">

                    <div class="text-muted small">
                        Invoice
                    </div>

                    <div class="fw-semibold">
                        {{ $order->invoice_number }}
                    </div>

                </div>


                <div class="mb-3">

                    <div class="text-muted small">
                        Game
                    </div>

                    <div class="fw-semibold">
                        {{ $order->game?->game_name }}
                    </div>

                </div>


                <div class="mb-3">

                    <div class="text-muted small">
                        Total Pembayaran
                    </div>

                    <div class="fs-4 fw-bold">
                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </div>

                </div>


                <hr>


                {{-- =====================================================
                     PAYMENT BUTTON
                ====================================================== --}}

                <button
                    id="pay-button"
                    type="button"
                    class="btn btn-primary w-100"
                >
                    Bayar Sekarang
                </button>


                <div
                    class="text-center text-muted small mt-3"
                >
                    Pembayaran diproses dengan aman melalui Midtrans.
                </div>

            </div>

        </div>

    </div>

</div>

</div>

@endsection

@push('scripts')


@if ($isProduction)

    <script
        src="https://app.midtrans.com/snap/snap.js"
        data-client-key="{{ $clientKey }}"
    ></script>

@else

    <script
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ $clientKey }}"
    ></script>

@endif


<script>

document
    .getElementById(
        'pay-button'
    )
    .addEventListener(
        'click',
        function () {

            window.snap.pay(
                @json($transaction->snap_token),
                {

                    onSuccess: function () {

                        window.location.href =
                            @json(
                                route(
                                    'midtrans.result',
                                    [
                                        'order' =>
                                            $order->id,
                                    ]
                                )
                            );
                    },


                    onPending: function () {

                        window.location.href =
                            @json(
                                route(
                                    'midtrans.result',
                                    [
                                        'order' =>
                                            $order->id,
                                    ]
                                )
                            );
                    },


                    onError: function () {

                        window.location.href =
                            @json(
                                route(
                                    'midtrans.result',
                                    [
                                        'order' =>
                                            $order->id,
                                    ]
                                )
                            );
                    },


                    onClose: function () {

                        /*
                        |--------------------------------------------------------------------------
                        | Customer menutup Snap.
                        |
                        | Tidak mengubah status order.
                        | Order tetap Waiting Payment.
                        |--------------------------------------------------------------------------
                        */

                    }

                }
            );

        }
    );

</script>


@endpush
