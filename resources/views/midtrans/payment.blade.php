@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">
                {{-- =====================================================
                    PAYMENT STEPS
                ====================================================== --}}

                <div class="payment-stepper mb-4">

                    <ol class="payment-stepper-list">

                        {{-- =================================================
                            STEP 1 - DETAIL
                        ================================================== --}}

                        <li
                            class="payment-stepper-item step-start completed"
                        >

                            <span class="payment-stepper-label">
                                Detail
                            </span>

                            {{-- Desktop circle --}}

                            <span class="payment-stepper-icon">

                                <i class="fa-solid fa-check"></i>

                            </span>

                            {{-- Mobile icon --}}

                            <i
                                class="
                                    payment-stepper-mobile-icon
                                    fa-solid
                                    fa-file-lines
                                "
                                aria-hidden="true"
                            ></i>

                        </li>


                        {{-- =================================================
                            STEP 2 - PEMBAYARAN
                        ================================================== --}}

                        <li
                            class="
                                payment-stepper-item
                                step-center
                                active
                            "
                        >

                            <span class="payment-stepper-label">
                                Pembayaran
                            </span>

                            {{-- Desktop circle --}}

                            <span class="payment-stepper-icon">

                                <i class="fa-solid fa-check"></i>

                            </span>

                            {{-- Mobile icon --}}

                            <i
                                class="
                                    payment-stepper-mobile-icon
                                    fa-solid
                                    fa-wallet
                                "
                                aria-hidden="true"
                            ></i>

                        </li>


                        {{-- =================================================
                            STEP 3 - SELESAI
                        ================================================== --}}

                        <li
                            class="
                                payment-stepper-item
                                step-end
                            "
                        >

                            <span class="payment-stepper-label">
                                Selesai
                            </span>

                            {{-- Desktop circle --}}

                            <span class="payment-stepper-icon">

                                <i class="fa-solid fa-check"></i>

                            </span>

                            {{-- Mobile icon --}}

                            <i
                                class="
                                    payment-stepper-mobile-icon
                                    fa-solid
                                    fa-circle-check
                                "
                                aria-hidden="true"
                            ></i>

                        </li>

                    </ol>

                </div>
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
                            Metode Pembayaran
                        </div>

                        <div class="fw-semibold fs-5">

                            {{ $paymentName }}

                        </div>

                    </div>


                    <div class="mb-3">

                        <div class="text-muted small">
                            Total Pembayaran
                        </div>

                        <div class="fs-4 fw-bold">

                            Rp
                            {{ number_format(
                                $order->total_price,
                                0,
                                ',',
                                '.'
                            ) }}

                        </div>

                    </div>


                    <hr>


                    {{-- =====================================================
                         SELECTED PAYMENT SUMMARY
                    ====================================================== --}}

                    <div
                        class="
                            alert
                            alert-primary
                            d-flex
                            align-items-center
                            gap-3
                            mb-4
                        "
                    >

                        <div
                            class="
                                rounded-circle
                                bg-white
                                d-flex
                                align-items-center
                                justify-content-center
                            "
                            style="
                                width:44px;
                                height:44px;
                            "
                        >

                            <i class="fa-solid fa-wallet"></i>

                        </div>


                        <div>

                            <div class="small text-muted">
                                Anda memilih
                            </div>

                            <strong>
                                {{ $paymentName }}
                            </strong>

                        </div>

                    </div>


                    {{-- =====================================================
                         PAYMENT BUTTON
                    ====================================================== --}}

                    <button
                        id="pay-button"
                        type="button"
                        class="btn btn-primary w-100"
                    >

                        <i class="fa-solid fa-lock me-1"></i>

                        Bayar Rp
                        {{ number_format(
                            $order->total_price,
                            0,
                            ',',
                            '.'
                        ) }}

                        dengan
                        {{ $paymentName }}

                    </button>


                    <div
                        class="
                            text-center
                            text-muted
                            small
                            mt-3
                        "
                    >

                        Pembayaran diproses dengan aman
                        melalui Midtrans.

                    </div>


                    <div
                        class="
                            text-center
                            mt-3
                        "
                    >

                        <small class="text-muted">
                            Metode pembayaran sudah dipilih
                            pada halaman checkout.
                        </small>

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

document.addEventListener('DOMContentLoaded', function () {

    const button =
        document.getElementById('pay-button');


    /*
    |--------------------------------------------------------------------------
    | RESULT URL
    |--------------------------------------------------------------------------
    */

    const resultUrl =
        @json(
            route(
                'midtrans.result',
                [
                    'order' => $order->id,
                ]
            )
        );


    /*
    |--------------------------------------------------------------------------
    | PAYMENT BUTTON
    |--------------------------------------------------------------------------
    */

    if (!button) {
        return;
    }


    button.addEventListener('click', function () {

        /*
        |--------------------------------------------------------------------------
        | CEGAH DOUBLE CLICK
        |--------------------------------------------------------------------------
        */

        if (button.disabled) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CEK MIDTRANS SNAP
        |--------------------------------------------------------------------------
        */

        if (
            typeof window.snap === 'undefined' ||
            typeof window.snap.pay !== 'function'
        ) {

            button.disabled =
                false;

            button.innerHTML =
                '<i class="fa-solid fa-triangle-exclamation me-1"></i> Pembayaran tidak tersedia';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | LOADING
        |--------------------------------------------------------------------------
        */

        button.disabled =
            true;

        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Membuka pembayaran...';


        /*
        |--------------------------------------------------------------------------
        | OPEN MIDTRANS SNAP
        |--------------------------------------------------------------------------
        */

        try {

            window.snap.pay(
                @json($transaction->snap_token),
                {

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT FILTER
                    |--------------------------------------------------------------------------
                    |
                    | Backend sudah membuat Snap token dengan payment type
                    | yang dipilih customer.
                    |
                    | Filter ini menjadi lapisan tambahan di frontend.
                    |
                    */

                    enabledPayments: [
                        @json($paymentType)
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    onSuccess: function () {

                        window.location.href =
                            resultUrl;

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | PENDING
                    |--------------------------------------------------------------------------
                    */

                    onPending: function () {

                        window.location.href =
                            resultUrl;

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | ERROR
                    |--------------------------------------------------------------------------
                    */

                    onError: function () {

                        window.location.href =
                            resultUrl;

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | CLOSE
                    |--------------------------------------------------------------------------
                    */

                    onClose: function () {

                        /*
                        |--------------------------------------------------------------------------
                        | Customer menutup Snap.
                        |--------------------------------------------------------------------------
                        |
                        | Jangan ubah status order menjadi Cancelled.
                        | Order tetap Waiting Payment.
                        |
                        */

                        button.disabled =
                            false;

                        button.innerHTML =
                            '<i class="fa-solid fa-lock me-1"></i> Bayar Rp {{ number_format($order->total_price, 0, ',', '.') }} dengan {{ $paymentName }}';

                    }

                }
            );

        } catch (error) {

            console.error(
                'Midtrans Snap error:',
                error
            );


            button.disabled =
                false;

            button.innerHTML =
                '<i class="fa-solid fa-lock me-1"></i> Bayar Rp {{ number_format($order->total_price, 0, ',', '.') }} dengan {{ $paymentName }}';

        }

    });

});

</script>

@endpush