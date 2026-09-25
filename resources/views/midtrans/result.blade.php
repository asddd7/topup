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
                        | PAYMENT STEPPER
                        |--------------------------------------------------------------------------
                        */

                        $paymentStep =
                            match ($orderStatus) {

                                'Paid',
                                'Processing',
                                'Completed'
                                    => 3,

                                'Waiting Payment'
                                    => 2,

                                'Cancelled',
                                'Failed'
                                    => 2,

                                default
                                    => 1,

                            };


                        /*
                        |--------------------------------------------------------------------------
                        | TRANSACTION STATUS
                        |--------------------------------------------------------------------------
                        |
                        | Status dari database lebih dipercaya.
                        |
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
                        | GUEST TOKEN
                        |--------------------------------------------------------------------------
                        */

                        $guestToken =
                            request()->query('token');


                        /*
                        |--------------------------------------------------------------------------
                        | LIVE STATUS URL
                        |--------------------------------------------------------------------------
                        */

                        $statusUrl =
                            route(
                                'midtrans.status',
                                [
                                    'order' =>
                                        $order->id,
                                ]
                            );


                        if (
                            !$order->user_id &&
                            $guestToken
                        ) {

                            $statusUrl .=
                                '?' .
                                http_build_query([
                                    'token' =>
                                        $guestToken,
                                ]);

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | ORDER URL
                        |--------------------------------------------------------------------------
                        */

                        $orderUrl =
                            route(
                                'order.show',
                                $order->invoice_number
                            );


                        if (
                            !$order->user_id &&
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
                        | PAYMENT URL
                        |--------------------------------------------------------------------------
                        */

                        $paymentUrl =
                            route(
                                'midtrans.payment',
                                [
                                    'order' =>
                                        $order->id,
                                ]
                            );


                        $query = [
                            'payment' =>
                                $order->midtrans_payment_type,
                        ];


                        if (
                            !$order->user_id &&
                            $guestToken
                        ) {

                            $query['token'] =
                                $guestToken;

                        }


                        $paymentUrl .=
                            '?' .
                            http_build_query(
                                $query
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | GAME URL
                        |--------------------------------------------------------------------------
                        */

                        $gameUrl =
                            route(
                                'game.show',
                                $order->game_id
                            );

                    @endphp


                    {{-- =====================================================
                         PAYMENT STEPS
                    ====================================================== --}}

                    <div class="payment-stepper mb-4">

                        <ol class="payment-stepper-list">


                            {{-- =================================================
                                 STEP 1 - DETAIL
                            ================================================== --}}

                            <li
                                class="
                                    payment-stepper-item
                                    step-start
                                    {{ $paymentStep >= 2 ? 'completed' : '' }}
                                "
                                data-payment-step="1"
                            >

                                <span class="payment-stepper-label">
                                    Detail
                                </span>


                                <span class="payment-stepper-icon">

                                    @if ($paymentStep >= 2)

                                        <i class="fa-solid fa-check"></i>

                                    @else

                                        <i class="fa-solid fa-circle"></i>

                                    @endif

                                </span>


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

                                    @if ($paymentStep === 2)
                                        active

                                    @elseif ($paymentStep >= 3)
                                        completed

                                    @endif
                                "
                                data-payment-step="2"
                            >

                                <span class="payment-stepper-label">
                                    Pembayaran
                                </span>


                                <span class="payment-stepper-icon">

                                    @if ($paymentStep >= 3)

                                        <i class="fa-solid fa-check"></i>

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

                                        <i class="fa-solid fa-xmark"></i>

                                    @else

                                        <i class="fa-solid fa-wallet"></i>

                                    @endif

                                </span>


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
                                    {{ $paymentStep >= 3 ? 'active' : '' }}
                                "
                                data-payment-step="3"
                            >

                                <span class="payment-stepper-label">
                                    Selesai
                                </span>


                                <span class="payment-stepper-icon">

                                    @if ($paymentStep >= 3)

                                        <i class="fa-solid fa-check"></i>

                                    @else

                                        <i class="fa-solid fa-circle"></i>

                                    @endif

                                </span>


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


                    {{-- =====================================================
                         LIVE PAYMENT STATUS
                    ====================================================== --}}

                    @php

                        $statusConfig =
                            match ($orderStatus) {

                                'Paid' => [

                                    'type' =>
                                        'success',

                                    'icon' =>
                                        '✓',

                                    'title' =>
                                        'Pembayaran Berhasil',

                                    'message' =>
                                        'Pembayaran Anda telah diterima. Order sedang dijadwalkan untuk diproses.',

                                ],

                                'Processing' => [

                                    'type' =>
                                        'success',

                                    'icon' =>
                                        '✓',

                                    'title' =>
                                        'Pembayaran Berhasil',

                                    'message' =>
                                        'Pembayaran Anda telah diterima. Order Anda sedang diproses.',

                                ],

                                'Completed' => [

                                    'type' =>
                                        'success',

                                    'icon' =>
                                        '✓',

                                    'title' =>
                                        'Pembayaran Berhasil',

                                    'message' =>
                                        'Pembayaran Anda telah diterima. Order Anda telah selesai.',

                                ],

                                'Waiting Payment' => [

                                    'type' =>
                                        'warning',

                                    'icon' =>
                                        '⏳',

                                    'title' =>
                                        'Menunggu Konfirmasi Pembayaran',

                                    'message' =>
                                        'Jika Anda sudah menyelesaikan pembayaran, sistem sedang menunggu konfirmasi dari Midtrans.',

                                ],

                                'Cancelled',
                                'Failed' => [

                                    'type' =>
                                        'danger',

                                    'icon' =>
                                        '✕',

                                    'title' =>
                                        'Pembayaran Tidak Berhasil',

                                    'message' =>
                                        'Transaksi pembayaran tidak dapat diproses. Silakan lakukan pembayaran kembali.',

                                ],

                                default => [

                                    'type' =>
                                        'primary',

                                    'icon' =>
                                        'ℹ',

                                    'title' =>
                                        'Status Order',

                                    'message' =>
                                        'Status order Anda sedang diperbarui.',

                                ],

                            };

                    @endphp


                    <div
                        id="paymentStatusSection"
                        class="text-center mb-4"
                        data-order-status="{{ $orderStatus }}"
                    >

                        <div
                            id="paymentStatusIcon"
                            class="
                                display-4
                                mb-3
                                text-{{ $statusConfig['type'] }}
                            "
                        >
                            {{ $statusConfig['icon'] }}
                        </div>


                        <h3
                            id="paymentStatusTitle"
                            class="fw-bold"
                        >
                            {{ $statusConfig['title'] }}
                        </h3>


                        <p
                            id="paymentStatusMessage"
                            class="text-muted mb-0"
                        >
                            {{ $statusConfig['message'] }}
                        </p>

                    </div>


                    <hr class="my-4">


                    {{-- =====================================================
                         ORDER INFORMATION
                    ====================================================== --}}

                    <div class="mb-4">

                        <h5 class="mb-3">
                            Detail Order
                        </h5>


                        {{-- =================================================
                             INVOICE
                        ================================================== --}}

                        <div
                            class="
                                d-flex
                                justify-content-between
                                align-items-center
                                gap-3
                                mb-2
                            "
                        >

                            <span class="text-muted">
                                Invoice
                            </span>

                            <strong
                                class="text-end"
                            >
                                {{ $order->invoice_number }}
                            </strong>

                        </div>


                        {{-- =================================================
                             GAME
                        ================================================== --}}

                        <div
                            class="
                                d-flex
                                justify-content-between
                                align-items-center
                                gap-3
                                mb-2
                            "
                        >

                            <span class="text-muted">
                                Game
                            </span>

                            <strong
                                class="text-end"
                            >
                                {{ $order->game?->game_name ?? '-' }}
                            </strong>

                        </div>


                        {{-- =================================================
                             TOTAL
                        ================================================== --}}

                        <div
                            class="
                                d-flex
                                justify-content-between
                                align-items-center
                                gap-3
                                mb-2
                            "
                        >

                            <span class="text-muted">
                                Total
                            </span>

                            <strong
                                class="text-end"
                            >
                                Rp
                                {{ number_format(
                                    $order->total_price,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>

                        </div>


                        {{-- =================================================
                             ORDER STATUS
                        ================================================== --}}

                        <div
                            class="
                                d-flex
                                justify-content-between
                                align-items-center
                                gap-3
                                mb-2
                            "
                        >

                            <span class="text-muted">
                                Status Order
                            </span>


                            <span
                                id="orderStatusBadge"
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
                                        $orderStatus ===
                                        'Waiting Payment'
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


                            {{-- =================================================
                                 MIDTRANS ORDER ID
                            ================================================== --}}

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                    gap-3
                                    mb-2
                                "
                            >

                                <span class="text-muted">
                                    Midtrans Order ID
                                </span>

                                <strong
                                    class="text-end"
                                >
                                    {{ $transaction->midtrans_order_id }}
                                </strong>

                            </div>


                            {{-- =================================================
                                 MIDTRANS STATUS
                            ================================================== --}}

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                    gap-3
                                    mb-2
                                    {{ $displayTransactionStatus === '' ? 'd-none' : '' }}"
                                "
                                id="midtransTransactionStatusRow"
                            >

                                <span class="text-muted">
                                    Status Midtrans
                                </span>

                                <strong
                                    id="midtransTransactionStatus"
                                    class="text-end"
                                >
                                    {{ $displayTransactionStatus }}
                                </strong>

                            </div>


                            {{-- =================================================
                                 PAYMENT TYPE
                            ================================================== --}}

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                    gap-3
                                    mb-2
                                    {{ !$transaction->payment_type ? 'd-none' : '' }}"
                                "
                                id="midtransPaymentTypeRow"
                            >

                                <span class="text-muted">
                                    Metode Pembayaran
                                </span>

                                <strong
                                    id="midtransPaymentType"
                                    class="text-end"
                                >
                                    {{
                                        $transaction->payment_type
                                            ? strtoupper(
                                                $transaction->payment_type
                                            )
                                            : ''
                                    }}
                                </strong>

                            </div>


                            {{-- =================================================
                                 PAID AT
                            ================================================== --}}

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                    gap-3
                                    mb-2
                                    {{ !$transaction->paid_at ? 'd-none' : '' }}"
                                "
                                id="midtransPaidAtRow"
                            >

                                <span class="text-muted">
                                    Dibayar Pada
                                </span>

                                <strong
                                    id="midtransPaidAt"
                                    class="text-end"
                                >
                                    @if ($transaction->paid_at)

                                        {{
                                            $transaction
                                                ->paid_at
                                                ->format(
                                                    'd M Y H:i'
                                                )
                                        }}

                                    @endif
                                </strong>

                            </div>


                            {{-- =================================================
                                 EXPIRED AT
                            ================================================== --}}

                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                    gap-3
                                    mb-2
                                    {{ !$transaction->expired_at ? 'd-none' : '' }}"
                                "
                                id="midtransExpiredAtRow"
                            >

                                <span class="text-muted">
                                    Kedaluwarsa
                                </span>

                                <strong
                                    id="midtransExpiredAt"
                                    class="text-end"
                                >
                                    @if ($transaction->expired_at)

                                        {{
                                            $transaction
                                                ->expired_at
                                                ->format(
                                                    'd M Y H:i'
                                                )
                                        }}

                                    @endif
                                </strong>

                            </div>

                        @endif

                    </div>


                    {{-- =====================================================
                         WAITING PAYMENT NOTICE
                    ====================================================== --}}

                    <div
                        id="paymentWaitNotice"
                        class="alert alert-info"
                        @if (
                            $orderStatus !==
                            'Waiting Payment'
                        )
                            style="display:none;"
                        @endif
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


                    {{-- =====================================================
                         ACTIONS
                    ====================================================== --}}

                    <div
                        id="paymentActions"
                        class="d-grid gap-2"
                    >

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
                            $orderStatus ===
                            'Waiting Payment'
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
                            href="{{ $gameUrl }}"
                            class="btn btn-outline-secondary"
                        >
                            Kembali ke Game
                        </a>

                    </div>


                    {{-- =====================================================
                         CALLBACK INFORMATION
                    ====================================================== --}}

                    @if ($midtransOrderId)

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


                            @if ($statusCode)

                                <br>

                                Status Code:
                                {{ $statusCode }}

                            @endif


                            @if ($transactionStatus)

                                <br>

                                Transaction Status Callback:
                                <span id="callbackTransactionStatus">
                                    {{ $transactionStatus }}
                                </span>

                            @endif

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | CONFIG
        |--------------------------------------------------------------------------
        */

        const statusUrl =
            @json($statusUrl);


        const orderUrl =
            @json($orderUrl);


        const paymentUrl =
            @json($paymentUrl);


        const gameUrl =
            @json($gameUrl);


        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const statusSection =
            document.getElementById(
                'paymentStatusSection'
            );

        const statusIcon =
            document.getElementById(
                'paymentStatusIcon'
            );

        const statusTitle =
            document.getElementById(
                'paymentStatusTitle'
            );

        const statusMessage =
            document.getElementById(
                'paymentStatusMessage'
            );

        const statusBadge =
            document.getElementById(
                'orderStatusBadge'
            );

        const waitNotice =
            document.getElementById(
                'paymentWaitNotice'
            );

        const actions =
            document.getElementById(
                'paymentActions'
            );

        const transactionStatus =
            document.getElementById(
                'midtransTransactionStatus'
            );

        const transactionStatusRow =
            document.getElementById(
                'midtransTransactionStatusRow'
            );

        const paymentType =
            document.getElementById(
                'midtransPaymentType'
            );

        const paymentTypeRow =
            document.getElementById(
                'midtransPaymentTypeRow'
            );

        const paidAt =
            document.getElementById(
                'midtransPaidAt'
            );

        const paidAtRow =
            document.getElementById(
                'midtransPaidAtRow'
            );

        const expiredAt =
            document.getElementById(
                'midtransExpiredAt'
            );

        const expiredAtRow =
            document.getElementById(
                'midtransExpiredAtRow'
            );

        const callbackStatus =
            document.getElementById(
                'callbackTransactionStatus'
            );


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            !statusSection ||
            !statusIcon ||
            !statusTitle ||
            !statusMessage ||
            !statusBadge ||
            !actions
        ) {

            console.warn(
                'Midtrans live status: element halaman tidak lengkap.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | STATE
        |--------------------------------------------------------------------------
        */

        let currentStatus =
            String(
                statusSection.dataset.orderStatus
                || ''
            ).trim();


        let pollingActive =
            true;


        let pollingTimeout =
            null;


        let requestInProgress =
            false;


        /*
        |--------------------------------------------------------------------------
        | STATUS CONFIG
        |--------------------------------------------------------------------------
        */

        function getStatusConfig(
            status
        ) {

            switch (status) {

                case 'Paid':

                    return {

                        type:
                            'success',

                        icon:
                            '✓',

                        title:
                            'Pembayaran Berhasil',

                        message:
                            'Pembayaran Anda telah diterima. Order sedang dijadwalkan untuk diproses.',

                        step:
                            3
                    };


                case 'Processing':

                    return {

                        type:
                            'success',

                        icon:
                            '✓',

                        title:
                            'Pembayaran Berhasil',

                        message:
                            'Pembayaran Anda telah diterima. Order Anda sedang diproses.',

                        step:
                            3
                    };


                case 'Completed':

                    return {

                        type:
                            'success',

                        icon:
                            '✓',

                        title:
                            'Pembayaran Berhasil',

                        message:
                            'Pembayaran Anda telah diterima. Order Anda telah selesai.',

                        step:
                            3
                    };


                case 'Waiting Payment':

                    return {

                        type:
                            'warning',

                        icon:
                            '⏳',

                        title:
                            'Menunggu Konfirmasi Pembayaran',

                        message:
                            'Jika Anda sudah menyelesaikan pembayaran, sistem sedang menunggu konfirmasi dari Midtrans.',

                        step:
                            2
                    };


                case 'Cancelled':
                case 'Failed':

                    return {

                        type:
                            'danger',

                        icon:
                            '✕',

                        title:
                            'Pembayaran Tidak Berhasil',

                        message:
                            'Transaksi pembayaran tidak dapat diproses. Silakan lakukan pembayaran kembali.',

                        step:
                            2
                    };


                default:

                    return {

                        type:
                            'primary',

                        icon:
                            'ℹ',

                        title:
                            'Status Order',

                        message:
                            'Status order Anda sedang diperbarui.',

                        step:
                            1
                    };

            }

        }


        /*
        |--------------------------------------------------------------------------
        | BADGE
        |--------------------------------------------------------------------------
        */

        function getBadgeClass(
            status
        ) {

            if (
                [
                    'Paid',
                    'Processing',
                    'Completed'
                ].includes(status)
            ) {

                return 'text-bg-success';
            }


            if (
                status ===
                'Waiting Payment'
            ) {

                return 'text-bg-warning';
            }


            if (
                [
                    'Cancelled',
                    'Failed'
                ].includes(status)
            ) {

                return 'text-bg-danger';
            }


            return 'text-bg-secondary';

        }


        /*
        |--------------------------------------------------------------------------
        | STEPPER
        |--------------------------------------------------------------------------
        */

        function updateStepper(
            step,
            status
        ) {

            document
                .querySelectorAll(
                    '.payment-stepper-item'
                )
                .forEach(
                    function (
                        item,
                        index
                    ) {

                        const stepNumber =
                            index + 1;


                        item.classList.remove(
                            'active',
                            'completed'
                        );


                        const icon =
                            item.querySelector(
                                '.payment-stepper-icon i'
                            );


                        if (!icon) {

                            return;
                        }


                        if (
                            stepNumber === 1
                        ) {

                            if (
                                step >= 2
                            ) {

                                item.classList.add(
                                    'completed'
                                );

                                icon.className =
                                    'fa-solid fa-check';

                            }
                            else {

                                icon.className =
                                    'fa-solid fa-circle';

                            }


                            return;
                        }


                        if (
                            stepNumber === 2
                        ) {

                            if (
                                step >= 3
                            ) {

                                item.classList.add(
                                    'completed'
                                );

                                icon.className =
                                    'fa-solid fa-check';

                            }
                            else if (
                                [
                                    'Cancelled',
                                    'Failed'
                                ].includes(
                                    status
                                )
                            ) {

                                item.classList.add(
                                    'active'
                                );

                                icon.className =
                                    'fa-solid fa-xmark';

                            }
                            else {

                                item.classList.add(
                                    'active'
                                );

                                icon.className =
                                    'fa-solid fa-wallet';

                            }


                            return;
                        }


                        if (
                            stepNumber === 3
                        ) {

                            if (
                                step >= 3
                            ) {

                                item.classList.add(
                                    'active'
                                );

                                icon.className =
                                    'fa-solid fa-check';

                            }
                            else {

                                icon.className =
                                    'fa-solid fa-circle';

                            }

                        }

                    }
                );

        }


        /*
        |--------------------------------------------------------------------------
        | ACTION BUTTONS
        |--------------------------------------------------------------------------
        */

        function updateActions(
            status
        ) {

            if (
                [
                    'Paid',
                    'Processing',
                    'Completed'
                ].includes(
                    status
                )
            ) {

                actions.innerHTML = `

                    <a
                        href="${orderUrl}"
                        class="btn btn-primary"
                    >
                        Lihat Detail Order
                    </a>

                    <a
                        href="${gameUrl}"
                        class="btn btn-outline-secondary"
                    >
                        Kembali ke Game
                    </a>

                `;

                return;
            }


            if (
                status ===
                'Waiting Payment'
            ) {

                actions.innerHTML = `

                    <a
                        href="${paymentUrl}"
                        class="btn btn-primary"
                    >
                        Lanjutkan Pembayaran
                    </a>

                    <a
                        href="${orderUrl}"
                        class="btn btn-outline-primary"
                    >
                        Lihat Detail Order
                    </a>

                    <a
                        href="${gameUrl}"
                        class="btn btn-outline-secondary"
                    >
                        Kembali ke Game
                    </a>

                `;

                return;
            }


            actions.innerHTML = `

                <a
                    href="${orderUrl}"
                    class="btn btn-outline-primary"
                >
                    Lihat Order
                </a>

                <a
                    href="${gameUrl}"
                    class="btn btn-outline-secondary"
                >
                    Kembali ke Game
                </a>

            `;

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

        function updateStatusUI(
            status
        ) {

            const config =
                getStatusConfig(
                    status
                );


            statusSection.dataset.orderStatus =
                status;


            statusIcon.className =
                'display-4 mb-3 text-' +
                config.type;


            statusIcon.textContent =
                config.icon;


            statusTitle.textContent =
                config.title;


            statusMessage.textContent =
                config.message;


            statusBadge.className =
                'badge ' +
                getBadgeClass(
                    status
                );


            statusBadge.textContent =
                status;


            if (
                waitNotice
            ) {

                waitNotice.style.display =
                    status ===
                    'Waiting Payment'
                        ? ''
                        : 'none';

            }


            updateActions(
                status
            );


            updateStepper(
                config.step,
                status
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE TRANSACTION
        |--------------------------------------------------------------------------
        */

        function updateTransactionUI(
            data
        ) {

            /*
            |--------------------------------------------------------------------------
            | MIDTRANS STATUS
            |--------------------------------------------------------------------------
            */

            if (
                transactionStatus
            ) {

                const value =
                    String(
                        data.transaction_status
                        || ''
                    ).trim();


                if (
                    value !== ''
                ) {

                    transactionStatus.textContent =
                        value;


                    if (
                        transactionStatusRow
                    ) {

                        transactionStatusRow.classList.remove(
                            'd-none'
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TYPE
            |--------------------------------------------------------------------------
            */

            if (
                paymentType
            ) {

                const value =
                    String(
                        data.payment_type
                        || ''
                    ).trim();


                if (
                    value !== ''
                ) {

                    paymentType.textContent =
                        value.toUpperCase();


                    if (
                        paymentTypeRow
                    ) {

                        paymentTypeRow.classList.remove(
                            'd-none'
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | PAID AT
            |--------------------------------------------------------------------------
            */

            if (
                paidAt
            ) {

                const value =
                    String(
                        data.paid_at
                        || ''
                    ).trim();


                if (
                    value !== ''
                ) {

                    paidAt.textContent =
                        value;


                    if (
                        paidAtRow
                    ) {

                        paidAtRow.classList.remove(
                            'd-none'
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | EXPIRED AT
            |--------------------------------------------------------------------------
            */

            if (
                expiredAt
            ) {

                const value =
                    String(
                        data.expired_at
                        || ''
                    ).trim();


                if (
                    value !== ''
                ) {

                    expiredAt.textContent =
                        value;


                    if (
                        expiredAtRow
                    ) {

                        expiredAtRow.classList.remove(
                            'd-none'
                        );

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | CALLBACK
            |--------------------------------------------------------------------------
            */

            if (
                callbackStatus &&
                data.transaction_status
            ) {

                callbackStatus.textContent =
                    data.transaction_status;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS CHANGE ANIMATION
        |--------------------------------------------------------------------------
        */

        function animateStatusChange() {

            statusSection.style.transition =
                'opacity 0.2s ease, transform 0.2s ease';


            statusSection.style.opacity =
                '0.35';


            statusSection.style.transform =
                'translateY(3px)';


            window.setTimeout(
                function () {

                    statusSection.style.opacity =
                        '1';


                    statusSection.style.transform =
                        'translateY(0)';

                },
                150
            );

        }


        /*
        |--------------------------------------------------------------------------
        | STOP POLLING
        |--------------------------------------------------------------------------
        */

        function stopPolling() {

            pollingActive =
                false;


            if (
                pollingTimeout
            ) {

                window.clearTimeout(
                    pollingTimeout
                );

                pollingTimeout =
                    null;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SCHEDULE NEXT POLL
        |--------------------------------------------------------------------------
        */

        function scheduleNextPoll() {

            if (
                !pollingActive
            ) {

                return;

            }


            if (
                pollingTimeout
            ) {

                window.clearTimeout(
                    pollingTimeout
                );

            }


            pollingTimeout =
                window.setTimeout(
                    checkStatus,
                    3000
                );

        }


        /*
        |--------------------------------------------------------------------------
        | CHECK STATUS
        |--------------------------------------------------------------------------
        */

        async function checkStatus() {

            if (
                !pollingActive ||
                requestInProgress
            ) {

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | STOP REQUEST WHILE TAB HIDDEN
            |--------------------------------------------------------------------------
            */

            if (
                document.hidden
            ) {

                scheduleNextPoll();

                return;

            }


            requestInProgress =
                true;


            try {

                /*
                |--------------------------------------------------------------------------
                | CACHE BUST
                |--------------------------------------------------------------------------
                */

                const separator =
                    statusUrl.includes('?')
                        ? '&'
                        : '?';


                const url =
                    statusUrl +
                    separator +
                    '_=' +
                    Date.now();


                const response =
                    await fetch(
                        url,
                        {
                            method:
                                'GET',

                            credentials:
                                'same-origin',

                            headers: {

                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'

                            },

                            cache:
                                'no-store'
                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | ACCESS ERROR
                |--------------------------------------------------------------------------
                */

                if (
                    response.status ===
                    403
                ) {

                    console.error(
                        'Midtrans live status: 403 Forbidden. Periksa guest token atau login.'
                    );


                    stopPolling();


                    return;
                }


                if (
                    response.status ===
                    404
                ) {

                    console.error(
                        'Midtrans live status: order tidak ditemukan.'
                    );


                    stopPolling();


                    return;
                }


                if (
                    !response.ok
                ) {

                    scheduleNextPoll();


                    return;
                }


                const data =
                    await response.json();


                if (
                    !data ||
                    data.success !== true
                ) {

                    scheduleNextPoll();


                    return;
                }


                const newStatus =
                    String(
                        data.order_status
                        || ''
                    ).trim();


                /*
                |--------------------------------------------------------------------------
                | TRANSACTION DATA
                |--------------------------------------------------------------------------
                */

                updateTransactionUI(
                    data
                );


                /*
                |--------------------------------------------------------------------------
                | ORDER STATUS
                |--------------------------------------------------------------------------
                */

                if (
                    newStatus &&
                    newStatus !==
                    currentStatus
                ) {

                    console.log(
                        'Midtrans order status:',
                        currentStatus,
                        '→',
                        newStatus
                    );


                    currentStatus =
                        newStatus;


                    animateStatusChange();


                    updateStatusUI(
                        newStatus
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | FINAL STATUS
                |--------------------------------------------------------------------------
                */

                if (
                    [
                        'Completed',
                        'Cancelled',
                        'Failed'
                    ].includes(
                        newStatus
                    )
                ) {

                    stopPolling();


                    return;

                }


            }
            catch (
                error
            ) {

                console.debug(
                    'Midtrans live status check gagal:',
                    error
                );

            }
            finally {

                requestInProgress =
                    false;

            }


            /*
            |--------------------------------------------------------------------------
            | NEXT POLL
            |--------------------------------------------------------------------------
            */

            scheduleNextPoll();

        }


        /*
        |--------------------------------------------------------------------------
        | TAB KEMBALI AKTIF
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'visibilitychange',
            function () {

                if (
                    !document.hidden &&
                    pollingActive
                ) {

                    checkStatus();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL CHECK
        |--------------------------------------------------------------------------
        */

        checkStatus();


        /*
        |--------------------------------------------------------------------------
        | START POLLING
        |--------------------------------------------------------------------------
        */

        if (
            ![
                'Completed',
                'Cancelled',
                'Failed'
            ].includes(
                currentStatus
            )
        ) {

            scheduleNextPoll();

        }
        else {

            stopPolling();

        }

    }
);

</script>

@endpush
