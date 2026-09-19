@extends('admin.layouts.app')

@section('title', 'History Pembelian')

@section('content')

<div class="transaction-history-page">

    <div class="container-fluid py-4">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="transaction-history-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

            <div>

                <h3 class="transaction-history-title">

                    <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>

                    History Pembelian

                </h3>

                <p class="transaction-history-description">

                    Lihat riwayat transaksi pembelian dari MooGold berdasarkan
                    tanggal dan status.

                </p>

            </div>


            <div class="page-header-actions">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="btn btn-outline-secondary"
                >

                    <i class="fa-solid fa-arrow-left me-1"></i>

                    Kembali

                </a>

            </div>

        </div>


        {{-- =====================================================
             API ERROR
        ====================================================== --}}

        @if($apiError)

            <div class="alert alert-danger d-flex align-items-start gap-2">

                <i class="fa-solid fa-circle-exclamation mt-1"></i>

                <div>

                    <strong>
                        Gagal mengambil history MooGold
                    </strong>

                    <div class="small mt-1">
                        {{ $apiError }}
                    </div>

                </div>

            </div>

        @endif


        {{-- =====================================================
             VALIDATION ERROR
        ====================================================== --}}

        @if($errors->any())

            <div class="alert alert-danger">

                <div class="fw-semibold mb-1">
                    Terdapat kesalahan:
                </div>

                <ul class="mb-0 ps-3">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =====================================================
             FILTER
        ====================================================== --}}

        <div class="transaction-history-filter card border-0 shadow-sm rounded-4 mb-4">

            <div class="card-body">

                <div class="d-flex align-items-center gap-2 mb-3">

                    <i class="fa-solid fa-filter text-primary"></i>

                    <h5 class="transaction-history-filter-title fw-bold mb-0">
                        Filter History
                    </h5>

                </div>


                <form
                    method="GET"
                    action="{{ route('admin.transaction-history.index') }}"
                >

                    <input
                        type="hidden"
                        name="page"
                        value="1"
                    >


                    <div class="row g-3 align-items-end">

                        {{-- START DATE --}}

                        <div class="col-12 col-md-4">

                            <label
                                for="start_date"
                                class="form-label"
                            >
                                Tanggal Mulai
                            </label>

                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                class="form-control"
                                value="{{ $startDate }}"
                                required
                            >

                        </div>


                        {{-- END DATE --}}

                        <div class="col-12 col-md-4">

                            <label
                                for="end_date"
                                class="form-label"
                            >
                                Tanggal Akhir
                            </label>

                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                class="form-control"
                                value="{{ $endDate }}"
                                required
                            >

                        </div>


                        {{-- STATUS --}}

                        <div class="col-12 col-md-4">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status
                            </label>

                            <select
                                id="status"
                                name="status"
                                class="form-select"
                            >

                                <option value="">
                                    Semua Status
                                </option>

                                <option
                                    value="processing"
                                    {{ $status == 'processing' ? 'selected' : '' }}
                                >
                                    Processing
                                </option>

                                <option
                                    value="completed"
                                    {{ $status == 'completed' ? 'selected' : '' }}
                                >
                                    Completed
                                </option>

                                <option
                                    value="refunded"
                                    {{ $status == 'refunded' ? 'selected' : '' }}
                                >
                                    Refunded
                                </option>

                            </select>

                        </div>


                        {{-- LIMIT --}}

                        <div class="col-12 col-md-4">

                            <label
                                for="limit"
                                class="form-label"
                            >
                                Jumlah Data
                            </label>

                            <select
                                id="limit"
                                name="limit"
                                class="form-select"
                            >

                                @foreach([20, 50, 100] as $option)

                                    <option
                                        value="{{ $option }}"
                                        {{ (int) $limit == $option ? 'selected' : '' }}
                                    >
                                        {{ $option }} data
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- ACTION --}}

                        <div class="col-12 col-md-8">

                            <div class="filter-actions d-flex flex-wrap gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    <i class="fa-solid fa-magnifying-glass me-1"></i>

                                    Tampilkan

                                </button>


                                <a
                                    href="{{ route('admin.transaction-history.index') }}"
                                    class="btn btn-outline-secondary"
                                >

                                    <i class="fa-solid fa-rotate-left me-1"></i>

                                    Reset

                                </a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- =====================================================
             PREPARE DATA
        ====================================================== --}}

        @php

            $orderCollection = collect($orders);

            $orderCount = $orderCollection->count();


            /*
            |--------------------------------------------------------------------------
            | VALUE HELPER
            |--------------------------------------------------------------------------
            */

            $getValue = function (
                $order,
                array $keys,
                $default = '-'
            ) {

                foreach ($keys as $key) {

                    $value = data_get(
                        $order,
                        $key
                    );

                    if (
                        $value !== null &&
                        $value !== ''
                    ) {

                        return $value;

                    }

                }

                return $default;

            };


            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */

            $currentPage = (int) $currentPage;

            $limit = (int) $limit;

            $hasPreviousPage =
                $currentPage > 1;

            $hasNextPage =
                $orderCount >= $limit;


            /*
            |--------------------------------------------------------------------------
            | QUERY PARAMS
            |--------------------------------------------------------------------------
            */

            $queryParams = [

                'start_date' =>
                    $startDate,

                'end_date' =>
                    $endDate,

                'status' =>
                    $status,

                'limit' =>
                    $limit,

            ];

        @endphp


        {{-- =====================================================
             RESULT HEADER
        ====================================================== --}}

        <div class="transaction-history-result-header d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

            <div>

                <h5 class="transaction-history-result-title">
                    Data Transaksi
                </h5>

                <div class="transaction-history-result-meta">

                    Periode:

                    <strong>
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                    </strong>

                    s/d

                    <strong>
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </strong>

                    @if($status)

                        <span class="mx-1">
                            •
                        </span>

                        Status:

                        <strong>
                            {{ ucfirst($status) }}
                        </strong>

                    @endif

                </div>

            </div>


            <div class="small text-muted">

                Halaman

                <strong>
                    {{ $currentPage }}
                </strong>

                <span class="mx-1">
                    •
                </span>

                {{ $orderCount }} data

            </div>

        </div>

        {{-- =====================================================
            BULK PRINT TOOLBAR
        ====================================================== --}}

        <div class="transaction-history-bulk-toolbar mb-3">

            <div class="transaction-history-bulk-info">

                <div class="transaction-history-bulk-select">

                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="selectAllTransactions"
                    >

                    <label
                        for="selectAllTransactions"
                        class="transaction-history-bulk-select-label"
                    >
                        Pilih Semua
                    </label>

                </div>

                <span
                    class="transaction-history-selected-count"
                    id="selectedTransactionCount"
                >
                    0 dipilih
                </span>

            </div>


            <button
                type="button"
                class="btn btn-primary btn-sm transaction-history-bulk-print-button"
                id="bulkPrintReceiptButton"
                disabled
            >

                <i class="fa-solid fa-print me-1"></i>

                Cetak Dipilih

            </button>

        </div>

        {{-- =====================================================
             TABLE CARD
        ====================================================== --}}

        <div class="transaction-history-table-card card border-0 shadow-sm rounded-4 overflow-hidden">

            <div class="card-body p-0">

                @if($orderCollection->isNotEmpty())

                    <div class="transaction-history-table-wrapper table-responsive">

                        <table class="transaction-history-table table table-hover align-middle mb-0">

                        <thead>

                            <tr>

                                <th class="column-number px-3 py-3">

                                    <div class="transaction-history-check-all">

                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="tableSelectAllTransactions"
                                        >

                                        <span>#</span>

                                    </div>

                                </th>

                                <th class="column-order py-3">
                                    Order
                                </th>

                                <th class="column-game py-3">
                                    Game
                                </th>

                                <th class="column-item py-3">
                                    Item
                                </th>

                                <th class="column-status py-3">
                                    Status
                                </th>

                                <th class="column-amount py-3">
                                    Amount
                                </th>

                                <th class="column-date py-3">
                                    Tanggal
                                </th>

                                <th class="column-action py-3 text-center">
                                    Detail
                                </th>

                            </tr>

                        </thead>


                            <tbody>

                                @foreach($orderCollection as $index => $order)

                                    @php

                                        /*
                                        |--------------------------------------------------------------------------
                                        | ORDER ID
                                        |--------------------------------------------------------------------------
                                        */

                                        $orderId = $getValue(
                                            $order,
                                            [
                                                'order_id',
                                                'orderId',
                                                'id',
                                            ]
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | ITEMS
                                        |--------------------------------------------------------------------------
                                        */

                                        $items = collect(
                                            data_get(
                                                $order,
                                                'items',
                                                []
                                            )
                                        )->values();


                                        $firstItem =
                                            $items->first();


                                        /*
                                        |--------------------------------------------------------------------------
                                        | PRODUCT
                                        |--------------------------------------------------------------------------
                                        */

                                        $productName = '-';

                                        $quantity = 1;

                                        $itemPrice = null;


                                        if (is_array($firstItem)) {

                                            $productName =
                                                trim(
                                                    (string) (
                                                        $firstItem['product']
                                                        ?? '-'
                                                    )
                                                );


                                            $quantity =
                                                $firstItem['quantity']
                                                ?? 1;


                                            $itemPrice =
                                                $firstItem['price']
                                                ?? null;

                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | GAME
                                        |--------------------------------------------------------------------------
                                        */

                                        $gameName =
                                            $getValue(
                                                $order,
                                                [
                                                    'game',
                                                    'game_name',
                                                ],
                                                null
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | ITEM
                                        |--------------------------------------------------------------------------
                                        */

                                        $itemName =
                                            $getValue(
                                                $order,
                                                [
                                                    'item',
                                                    'item_name',
                                                ],
                                                null
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | PARSE PRODUCT
                                        |--------------------------------------------------------------------------
                                        |
                                        | Example:
                                        |
                                        | Mobile Legends (Indonesia) - Weekly Pass
                                        |
                                        */

                                        if (
                                            (
                                                $gameName === null ||
                                                $gameName === ''
                                            ) ||
                                            (
                                                $itemName === null ||
                                                $itemName === ''
                                            )
                                        ) {

                                            if (
                                                $productName !== '-' &&
                                                is_string($productName)
                                            ) {

                                                $separatorPosition =
                                                    strrpos(
                                                        $productName,
                                                        ' - '
                                                    );


                                                if (
                                                    $separatorPosition !== false
                                                ) {

                                                    $gameName =
                                                        trim(
                                                            substr(
                                                                $productName,
                                                                0,
                                                                $separatorPosition
                                                            )
                                                        );


                                                    $itemName =
                                                        trim(
                                                            substr(
                                                                $productName,
                                                                $separatorPosition + 3
                                                            )
                                                        );

                                                } else {

                                                    $gameName =
                                                        $productName;

                                                    $itemName =
                                                        '-';

                                                }

                                            } else {

                                                $gameName =
                                                    $gameName
                                                    ?: '-';

                                                $itemName =
                                                    $itemName
                                                    ?: '-';

                                            }

                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | STATUS
                                        |--------------------------------------------------------------------------
                                        */

                                        $orderStatusRaw =
                                            $getValue(
                                                $order,
                                                [
                                                    'status',
                                                    'order_status',
                                                    'transaction_status',
                                                ]
                                            );


                                        $orderStatus =
                                            strtolower(
                                                trim(
                                                    (string)
                                                    $orderStatusRaw
                                                )
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | AMOUNT
                                        |--------------------------------------------------------------------------
                                        */

                                        $amount =
                                            $getValue(
                                                $order,
                                                [
                                                    'total',
                                                    'amount',
                                                    'gross_amount',
                                                    'price',
                                                ]
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | CURRENCY
                                        |--------------------------------------------------------------------------
                                        */

                                        $currency =
                                            $getValue(
                                                $order,
                                                [
                                                    'currency',
                                                ],
                                                'IDR'
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | DATE
                                        |--------------------------------------------------------------------------
                                        */

                                        $orderDateRaw =
                                            $getValue(
                                                $order,
                                                [
                                                    'date_created',
                                                    'created_at',
                                                    'order_date',
                                                    'order_created_at',
                                                    'transaction_time',
                                                    'date',
                                                ]
                                            );


                                        $orderDate =
                                            '-';


                                        if (
                                            $orderDateRaw !== '-' &&
                                            $orderDateRaw !== null &&
                                            $orderDateRaw !== ''
                                        ) {

                                            try {

                                                $orderDate =
                                                    \Carbon\Carbon::parse(
                                                        $orderDateRaw
                                                    )->format(
                                                        'd/m/Y H:i:s'
                                                    );

                                            } catch (
                                                \Throwable $e
                                            ) {

                                                $orderDate =
                                                    (string)
                                                    $orderDateRaw;

                                            }

                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | STATUS CLASS
                                        |--------------------------------------------------------------------------
                                        */

                                        $statusClass =
                                            match ($orderStatus) {

                                                'completed',
                                                'complete',
                                                'success',
                                                'successful'

                                                    => 'bg-success-subtle text-success',

                                                'processing',
                                                'process'

                                                    => 'bg-primary-subtle text-primary',

                                                'refunded',
                                                'refund'

                                                    => 'bg-warning-subtle text-warning-emphasis',

                                                'failed',
                                                'cancelled',
                                                'canceled'

                                                    => 'bg-danger-subtle text-danger',

                                                default

                                                    => 'bg-secondary-subtle text-secondary',

                                            };


                                        /*
                                        |--------------------------------------------------------------------------
                                        | ROW NUMBER
                                        |--------------------------------------------------------------------------
                                        */

                                        $rowNumber =
                                            (
                                                ($currentPage - 1)
                                                * $limit
                                            )
                                            + $index
                                            + 1;


                                        /*
                                        |--------------------------------------------------------------------------
                                        | DETAIL ID
                                        |--------------------------------------------------------------------------
                                        */

                                        $detailId =
                                            'history-detail-' . $index;

                                    @endphp


                                    {{-- =================================================
                                         MAIN ROW
                                    ================================================== --}}

                                    <tr>

                                        {{-- NUMBER + CHECKBOX --}}

                                        <td class="px-3">

                                            <div class="transaction-history-row-select">

                                                <input
                                                    type="checkbox"
                                                    class="form-check-input transaction-history-select"
                                                    value="{{ $index }}"
                                                    data-order-id="{{ $orderId }}"
                                                    data-status="{{ $orderStatusRaw }}"
                                                    data-amount="{{ is_numeric($amount) ? $amount : '' }}"
                                                    data-game="{{ $gameName }}"
                                                    data-item="{{ $itemName }}"
                                                    data-quantity="{{ $quantity }}"
                                                    data-date="{{ $orderDate }}"
                                                    data-currency="{{ $currency }}"
                                                >

                                                <span>
                                                    {{ $rowNumber }}
                                                </span>

                                            </div>

                                        </td>


                                        {{-- ORDER --}}

                                        <td>

                                            <span class="transaction-history-order-id">

                                                {{ $orderId }}

                                            </span>

                                        </td>


                                        {{-- GAME --}}

                                        <td>

                                            <span class="transaction-history-game">

                                                {{ $gameName }}

                                            </span>

                                        </td>


                                        {{-- ITEM --}}

                                        <td>

                                            <span class="transaction-history-item">

                                                {{ $itemName }}

                                                @if(
                                                    is_numeric($quantity) &&
                                                    (int) $quantity > 1
                                                )

                                                    <small class="d-block text-muted">

                                                        Qty: {{ $quantity }}

                                                    </small>

                                                @endif

                                            </span>

                                        </td>


                                        {{-- STATUS --}}

                                        <td>

                                            <span
                                                class="transaction-history-status badge rounded-pill {{ $statusClass }}"
                                            >

                                                {{ $orderStatusRaw }}

                                            </span>

                                        </td>


                                        {{-- AMOUNT --}}

                                        <td>

                                            <span class="transaction-history-amount">

                                                @if(is_numeric($amount))

                                                    {{ strtoupper($currency) === 'IDR' ? 'Rp' : $currency }}

                                                    {{ number_format(
                                                        (float) $amount,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}

                                                @else

                                                    {{ $amount }}

                                                @endif

                                            </span>

                                        </td>


                                        {{-- DATE --}}

                                        <td>

                                            <span class="transaction-history-date text-nowrap">

                                                {{ $orderDate }}

                                            </span>

                                        </td>


                                        {{-- DETAIL --}}

                                        <td class="text-center">

                                            <div class="d-inline-flex gap-1">

                                                <button
                                                    type="button"
                                                    class="transaction-history-detail-button btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $detailId }}"
                                                    aria-expanded="false"
                                                    aria-controls="{{ $detailId }}"
                                                    title="Lihat detail"
                                                >

                                                    <i class="fa-solid fa-eye"></i>

                                                </button>


                                                <button
                                                    type="button"
                                                    class="transaction-history-print-button btn btn-sm btn-outline-secondary"

                                                    data-order-id="{{ $orderId }}"

                                                    data-status="{{ $orderStatusRaw }}"

                                                    data-amount="{{ is_numeric($amount) ? $amount : '' }}"

                                                    data-game="{{ $gameName }}"

                                                    data-item="{{ $itemName }}"

                                                    data-quantity="{{ $quantity }}"

                                                    data-date="{{ $orderDate }}"

                                                    data-currency="{{ $currency }}"

                                                    title="Cetak receipt"
                                                >

                                                    <i class="fa-solid fa-print"></i>

                                                </button>

                                            </div>

                                        </td>

                                    </tr>


                                    {{-- =================================================
                                         DETAIL ROW
                                    ================================================== --}}

                                    <tr
                                        class="transaction-history-detail-row collapse"
                                        id="{{ $detailId }}"
                                    >

                                        <td colspan="8">

                                            <div class="transaction-history-detail-content">

                                                <div class="transaction-history-detail-title">

                                                    Detail Response MooGold

                                                </div>


                                                <pre class="transaction-history-json">{{ json_encode(
                                                    $order,
                                                    JSON_PRETTY_PRINT |
                                                    JSON_UNESCAPED_SLASHES |
                                                    JSON_UNESCAPED_UNICODE
                                                ) }}</pre>

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    {{-- =================================================
                         EMPTY STATE
                    ================================================== --}}

                    <div class="transaction-history-empty">

                        <div class="transaction-history-empty-icon">

                            <i class="fa-solid fa-clock-rotate-left"></i>

                        </div>


                        <h5 class="transaction-history-empty-title">

                            Tidak ada history transaksi

                        </h5>


                        <p class="transaction-history-empty-description">

                            Tidak ditemukan transaksi MooGold pada
                            periode dan filter yang dipilih.

                        </p>

                    </div>

                @endif

            </div>


            {{-- =====================================================
                 PAGINATION
            ====================================================== --}}

            @if(
                $hasPreviousPage ||
                $hasNextPage
            )

                <div class="transaction-history-pagination">

                    <div class="d-flex justify-content-between align-items-center gap-2">

                        {{-- PREVIOUS --}}

                        <div>

                            @if($hasPreviousPage)

                                <a
                                    href="{{
                                        route(
                                            'admin.transaction-history.index',
                                            array_merge(
                                                $queryParams,
                                                [
                                                    'page' =>
                                                        $currentPage - 1,
                                                ]
                                            )
                                        )
                                    }}"
                                    class="btn btn-outline-secondary btn-sm"
                                >

                                    <i class="fa-solid fa-chevron-left me-1"></i>

                                    <span class="pagination-label">
                                        Sebelumnya
                                    </span>

                                </a>

                            @else

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    disabled
                                >

                                    <i class="fa-solid fa-chevron-left me-1"></i>

                                    <span class="pagination-label">
                                        Sebelumnya
                                    </span>

                                </button>

                            @endif

                        </div>


                        {{-- CURRENT PAGE --}}

                        <div class="text-muted small">

                            Halaman

                            <strong>
                                {{ $currentPage }}
                            </strong>

                        </div>


                        {{-- NEXT --}}

                        <div>

                            @if($hasNextPage)

                                <a
                                    href="{{
                                        route(
                                            'admin.transaction-history.index',
                                            array_merge(
                                                $queryParams,
                                                [
                                                    'page' =>
                                                        $currentPage + 1,
                                                ]
                                            )
                                        )
                                    }}"
                                    class="btn btn-outline-primary btn-sm"
                                >

                                    <span class="pagination-label">
                                        Berikutnya
                                    </span>

                                    <i class="fa-solid fa-chevron-right ms-1"></i>

                                </a>

                            @else

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    disabled
                                >

                                    <span class="pagination-label">
                                        Berikutnya
                                    </span>

                                    <i class="fa-solid fa-chevron-right ms-1"></i>

                                </button>

                            @endif

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>

</div>


{{-- =====================================================
     RECEIPT MODAL
====================================================== --}}

<div
    class="modal fade"
    id="receiptModal"
    tabindex="-1"
    aria-labelledby="receiptModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content receipt-modal">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title"
                        id="receiptModalLabel"
                    >

                        <i class="fa-solid fa-receipt me-2"></i>

                        Cetak Receipt

                    </h5>


                    <small class="text-muted">

                        Atur harga yang akan ditampilkan pada receipt.

                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <div class="modal-body">

                <div class="receipt-editor">


                    {{-- ORDER --}}

                    <div class="receipt-editor-row">

                        <span>
                            Order ID
                        </span>

                        <strong id="receiptOrderId">
                            -
                        </strong>

                    </div>


                    {{-- GAME --}}

                    <div class="receipt-editor-row">

                        <span>
                            Game
                        </span>

                        <strong id="receiptGame">
                            -
                        </strong>

                    </div>


                    {{-- ITEM --}}

                    <div class="receipt-editor-row">

                        <span>
                            Item
                        </span>

                        <strong id="receiptItem">
                            -
                        </strong>

                    </div>


                    {{-- QUANTITY --}}

                    <div class="receipt-editor-row">

                        <span>
                            Quantity
                        </span>

                        <strong id="receiptQuantity">
                            -
                        </strong>

                    </div>


                    {{-- STATUS --}}

                    <div class="receipt-editor-row">

                        <span>
                            Status
                        </span>

                        <strong id="receiptStatus">
                            -
                        </strong>

                    </div>


                    {{-- DATE --}}

                    <div class="receipt-editor-row">

                        <span>
                            Tanggal
                        </span>

                        <strong id="receiptDate">
                            -
                        </strong>

                    </div>


                    {{-- PRICE --}}

                    <div class="receipt-price-editor">

                        <label
                            for="receiptPrice"
                            class="form-label"
                        >
                            Harga Receipt
                        </label>


                        <div class="input-group">

                            <span class="input-group-text">
                                Rp
                            </span>

                            <input
                                type="number"
                                id="receiptPrice"
                                class="form-control"
                                min="0"
                                step="1"
                                placeholder="50000"
                            >

                        </div>


                        <small>

                            Harga ini hanya digunakan untuk
                            cetakan receipt. Tidak mengubah
                            transaksi MooGold.

                        </small>

                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >

                    Batal

                </button>


                <button
                    type="button"
                    class="btn btn-primary"
                    id="printReceiptButton"
                >

                    <i class="fa-solid fa-print me-2"></i>

                    Cetak Receipt

                </button>

            </div>

        </div>

    </div>

</div>

{{-- =====================================================
     BULK RECEIPT MODAL
====================================================== --}}

<div
    class="modal fade"
    id="bulkReceiptModal"
    tabindex="-1"
    aria-labelledby="bulkReceiptModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content receipt-modal">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title"
                        id="bulkReceiptModalLabel"
                    >

                        <i class="fa-solid fa-layer-group me-2"></i>

                        Cetak Receipt Terpilih

                    </h5>

                    <small class="text-muted">

                        Periksa dan ubah harga setiap receipt sebelum mencetak.

                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <div class="modal-body">

                <div class="bulk-receipt-toolbar">

                    <div>

                        <span class="small text-muted">
                            Jumlah receipt
                        </span>

                        <strong id="bulkReceiptCount">
                            0
                        </strong>

                    </div>


                    <div class="bulk-receipt-apply-price">

                        <div class="input-group input-group-sm">

                            <span class="input-group-text">
                                Rp
                            </span>

                            <input
                                type="number"
                                id="bulkApplyPrice"
                                class="form-control"
                                min="0"
                                step="1"
                                placeholder="Harga semua"
                            >

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="applyBulkPrice"
                            >
                                Terapkan
                            </button>

                        </div>

                    </div>

                </div>


                <div
                    class="bulk-receipt-list"
                    id="bulkReceiptList"
                >

                    {{-- Diisi oleh JavaScript --}}

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Batal
                </button>


                <button
                    type="button"
                    class="btn btn-primary"
                    id="printBulkReceiptButton"
                >

                    <i class="fa-solid fa-print me-2"></i>

                    Cetak Semua

                </button>

            </div>

        </div>

    </div>

</div>

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const receiptModalElement =
        document.getElementById('receiptModal');

    const receiptModal =
        receiptModalElement
            ? bootstrap.Modal.getOrCreateInstance(
                receiptModalElement
            )
            : null;


    const bulkReceiptModalElement =
        document.getElementById('bulkReceiptModal');

    const bulkReceiptModal =
        bulkReceiptModalElement
            ? bootstrap.Modal.getOrCreateInstance(
                bulkReceiptModalElement
            )
            : null;


    /*
    |--------------------------------------------------------------------------
    | INDIVIDUAL RECEIPT
    |--------------------------------------------------------------------------
    */

    const receiptOrderId =
        document.getElementById('receiptOrderId');

    const receiptGame =
        document.getElementById('receiptGame');

    const receiptItem =
        document.getElementById('receiptItem');

    const receiptQuantity =
        document.getElementById('receiptQuantity');

    const receiptStatus =
        document.getElementById('receiptStatus');

    const receiptDate =
        document.getElementById('receiptDate');

    const receiptPrice =
        document.getElementById('receiptPrice');

    const printReceiptButton =
        document.getElementById('printReceiptButton');


    /*
    |--------------------------------------------------------------------------
    | BULK RECEIPT
    |--------------------------------------------------------------------------
    */

    const selectAllTransactions =
        document.getElementById(
            'selectAllTransactions'
        );

    const tableSelectAllTransactions =
        document.getElementById(
            'tableSelectAllTransactions'
        );

    const selectedTransactionCount =
        document.getElementById(
            'selectedTransactionCount'
        );

    const bulkPrintReceiptButton =
        document.getElementById(
            'bulkPrintReceiptButton'
        );

    const bulkReceiptList =
        document.getElementById(
            'bulkReceiptList'
        );

    const bulkReceiptCount =
        document.getElementById(
            'bulkReceiptCount'
        );

    const bulkApplyPrice =
        document.getElementById(
            'bulkApplyPrice'
        );

    const applyBulkPrice =
        document.getElementById(
            'applyBulkPrice'
        );

    const printBulkReceiptButton =
        document.getElementById(
            'printBulkReceiptButton'
        );


    let currentReceipt = null;


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value) {

        const number = Number(value);

        if (Number.isNaN(number)) {

            return 'Rp -';

        }


        return new Intl.NumberFormat(
            'id-ID',
            {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }
        ).format(number);

    }


    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    function getSelectedTransactions() {

        return Array.from(
            document.querySelectorAll(
                '.transaction-history-select:checked'
            )
        ).map(function (checkbox) {

            return {

                orderId:
                    checkbox.dataset.orderId || '-',

                game:
                    checkbox.dataset.game || '-',

                item:
                    checkbox.dataset.item || '-',

                quantity:
                    checkbox.dataset.quantity || '1',

                status:
                    checkbox.dataset.status || '-',

                amount:
                    checkbox.dataset.amount || '',

                date:
                    checkbox.dataset.date || '-',

                currency:
                    checkbox.dataset.currency || 'IDR'

            };

        });

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SELECTION
    |--------------------------------------------------------------------------
    */

    function updateBulkSelection() {

        const checkboxes =
            Array.from(
                document.querySelectorAll(
                    '.transaction-history-select'
                )
            );

        const selected =
            checkboxes.filter(
                checkbox => checkbox.checked
            );

        const count =
            selected.length;


        if (selectedTransactionCount) {

            selectedTransactionCount.textContent =
                `${count} dipilih`;

        }


        if (bulkPrintReceiptButton) {

            bulkPrintReceiptButton.disabled =
                count === 0;

        }


        const allSelected =
            checkboxes.length > 0 &&
            selected.length === checkboxes.length;


        const someSelected =
            selected.length > 0 &&
            selected.length < checkboxes.length;


        [
            selectAllTransactions,
            tableSelectAllTransactions
        ].forEach(function (checkbox) {

            if (!checkbox) {
                return;
            }

            checkbox.checked =
                allSelected;

            checkbox.indeterminate =
                someSelected;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | CHECKBOX EVENTS
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.transaction-history-select'
        )
        .forEach(function (checkbox) {

            checkbox.addEventListener(
                'change',
                updateBulkSelection
            );

        });


    function setAllTransactionsSelected(
        checked
    ) {

        document
            .querySelectorAll(
                '.transaction-history-select'
            )
            .forEach(function (checkbox) {

                checkbox.checked = checked;

            });


        updateBulkSelection();

    }


    selectAllTransactions?.addEventListener(
        'change',
        function () {

            setAllTransactionsSelected(
                this.checked
            );

        }
    );


    tableSelectAllTransactions?.addEventListener(
        'change',
        function () {

            setAllTransactionsSelected(
                this.checked
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INDIVIDUAL RECEIPT MODAL
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.transaction-history-print-button'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    currentReceipt = {

                        orderId:
                            this.dataset.orderId || '-',

                        game:
                            this.dataset.game || '-',

                        item:
                            this.dataset.item || '-',

                        quantity:
                            this.dataset.quantity || '1',

                        status:
                            this.dataset.status || '-',

                        amount:
                            this.dataset.amount || '',

                        date:
                            this.dataset.date || '-',

                        currency:
                            this.dataset.currency || 'IDR'

                    };


                    receiptOrderId.textContent =
                        currentReceipt.orderId;

                    receiptGame.textContent =
                        currentReceipt.game;

                    receiptItem.textContent =
                        currentReceipt.item;

                    receiptQuantity.textContent =
                        currentReceipt.quantity;

                    receiptStatus.textContent =
                        currentReceipt.status;

                    receiptDate.textContent =
                        currentReceipt.date;


                    receiptPrice.value =
                        currentReceipt.amount &&
                        !Number.isNaN(
                            Number(
                                currentReceipt.amount
                            )
                        )
                            ? Number(
                                currentReceipt.amount
                            )
                            : '';


                    receiptModal?.show();

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | BUILD BULK EDITOR
    |--------------------------------------------------------------------------
    */

    function renderBulkReceiptList() {

        const selected =
            getSelectedTransactions();


        if (bulkReceiptCount) {

            bulkReceiptCount.textContent =
                selected.length;

        }


        if (!bulkReceiptList) {
            return;
        }


        bulkReceiptList.innerHTML = '';


        selected.forEach(function (
            receipt,
            index
        ) {

            const row =
                document.createElement('div');


            row.className =
                'bulk-receipt-item';


            row.dataset.index =
                index;


            row.innerHTML = `

                <div class="bulk-receipt-item-number">
                    ${index + 1}
                </div>

                <div class="bulk-receipt-item-info">

                    <strong>
                        ${escapeHtml(
                            receipt.orderId
                        )}
                    </strong>

                    <span>
                        ${escapeHtml(
                            receipt.game
                        )}
                    </span>

                    <small>
                        ${escapeHtml(
                            receipt.item
                        )}
                        × ${escapeHtml(
                            receipt.quantity
                        )}
                    </small>

                </div>


                <div class="bulk-receipt-item-date">

                    ${escapeHtml(
                        receipt.date
                    )}

                </div>


                <div class="bulk-receipt-item-price">

                    <div class="input-group input-group-sm">

                        <span class="input-group-text">
                            Rp
                        </span>

                        <input
                            type="number"
                            class="form-control bulk-receipt-price"
                            min="0"
                            step="1"
                            value="${escapeHtml(
                                receipt.amount
                            )}"
                            data-index="${index}"
                        >

                    </div>

                </div>

            `;


            bulkReceiptList.appendChild(row);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | OPEN BULK MODAL
    |--------------------------------------------------------------------------
    */

    bulkPrintReceiptButton?.addEventListener(
        'click',
        function () {

            const selected =
                getSelectedTransactions();


            if (selected.length === 0) {

                return;

            }


            renderBulkReceiptList();

            bulkReceiptModal?.show();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | APPLY PRICE TO ALL
    |--------------------------------------------------------------------------
    */

    applyBulkPrice?.addEventListener(
        'click',
        function () {

            const value =
                bulkApplyPrice?.value;


            if (
                !value ||
                Number.isNaN(
                    Number(value)
                ) ||
                Number(value) < 0
            ) {

                bulkApplyPrice?.focus();

                return;

            }


            document
                .querySelectorAll(
                    '.bulk-receipt-price'
                )
                .forEach(function (input) {

                    input.value =
                        value;

                });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INDIVIDUAL PRINT
    |--------------------------------------------------------------------------
    */

    printReceiptButton?.addEventListener(
        'click',
        function () {

            if (!currentReceipt) {
                return;
            }


            const price =
                Number(
                    receiptPrice.value
                );


            if (
                !receiptPrice.value ||
                Number.isNaN(price) ||
                price < 0
            ) {

                receiptPrice.focus();

                return;

            }


            printReceipts(
                [
                    {
                        ...currentReceipt,
                        amount: price
                    }
                ]
            );


            receiptModal?.hide();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | BULK PRINT
    |--------------------------------------------------------------------------
    */

    printBulkReceiptButton?.addEventListener(
        'click',
        function () {

            const selected =
                getSelectedTransactions();


            if (selected.length === 0) {
                return;
            }


            const priceInputs =
                Array.from(
                    document.querySelectorAll(
                        '.bulk-receipt-price'
                    )
                );


            const receipts = [];


            for (
                let index = 0;
                index < selected.length;
                index++
            ) {

                const priceInput =
                    priceInputs[index];


                const price =
                    Number(
                        priceInput?.value
                    );


                if (
                    !priceInput ||
                    priceInput.value === '' ||
                    Number.isNaN(price) ||
                    price < 0
                ) {

                    priceInput?.focus();

                    return;

                }


                receipts.push({

                    ...selected[index],

                    amount:
                        price

                });

            }


            printReceipts(
                receipts
            );


            bulkReceiptModal?.hide();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | PRINT ENGINE
    |--------------------------------------------------------------------------
    */

    function printReceipts(
        receipts
    ) {

        if (
            !Array.isArray(receipts) ||
            receipts.length === 0
        ) {

            return;

        }


        const appName =
            @json(setting('app_name'));


        const appLogo =
            @json(
                setting('app_logo')
                    ? asset(
                        'storage/' .
                        setting('app_logo')
                    )
                    : null
            );


        const whatsapp =
            @json(
                setting('whatsapp')
            );


        const email =
            @json(
                setting('email')
            );


        const address =
            @json(
                setting('address')
            );


        const printWindow =
            window.open(
                '',
                '_blank',
                'width=900,height=900'
            );


        if (!printWindow) {

            alert(
                'Popup diblokir browser. Izinkan popup untuk mencetak receipt.'
            );

            return;

        }


        const receiptHtml =
            receipts
                .map(function (
                    receipt,
                    index
                ) {

                    const quantity =
                        Number(
                            receipt.quantity
                        ) || 1;


                    const price =
                        Number(
                            receipt.amount
                        ) || 0;


                    const subtotal =
                        price * quantity;


                    return `

                        <section
                            class="print-receipt
                            ${index < receipts.length - 1
                                ? 'print-receipt-page'
                                : ''}"
                        >

                            <div class="print-receipt-inner">


                                <!-- HEADER -->

                                <header
                                    class="print-receipt-header"
                                >

                                    <div
                                        class="print-brand"
                                    >

                                        ${
                                            appLogo
                                            ? `
                                                <img
                                                    src="${escapeHtml(appLogo)}"
                                                    alt=""
                                                    class="print-logo"
                                                >
                                            `
                                            : ''
                                        }


                                        <div>

                                            <div
                                                class="print-store-name"
                                            >

                                                ${escapeHtml(
                                                    appName ||
                                                    'TopUp'
                                                )}

                                            </div>

                                            <div
                                                class="print-document-title"
                                            >

                                                BUKTI TRANSAKSI

                                            </div>

                                        </div>

                                    </div>


                                    <div
                                        class="print-status ${escapeHtml(
                                            String(
                                                receipt.status
                                            ).toLowerCase()
                                        )}"
                                    >

                                        ${escapeHtml(
                                            receipt.status
                                        )}

                                    </div>

                                </header>


                                <!-- ORDER META -->

                                <div
                                    class="print-order-meta"
                                >

                                    <div>

                                        <span>
                                            Order ID
                                        </span>

                                        <strong>
                                            ${escapeHtml(
                                                receipt.orderId
                                            )}
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Tanggal
                                        </span>

                                        <strong>
                                            ${escapeHtml(
                                                receipt.date
                                            )}
                                        </strong>

                                    </div>

                                </div>


                                <!-- PRODUCT -->

                                <div
                                    class="print-section-title"
                                >

                                    Detail Pembelian

                                </div>


                                <div
                                    class="print-product-card"
                                >

                                    <div
                                        class="print-game"
                                    >

                                        ${escapeHtml(
                                            receipt.game
                                        )}

                                    </div>


                                    <div
                                        class="print-item"
                                    >

                                        ${escapeHtml(
                                            receipt.item
                                        )}

                                    </div>

                                </div>


                                <table
                                    class="print-item-table"
                                >

                                    <thead>

                                        <tr>

                                            <th>
                                                Produk
                                            </th>

                                            <th
                                                class="align-right"
                                            >
                                                Qty
                                            </th>

                                            <th
                                                class="align-right"
                                            >
                                                Harga
                                            </th>

                                            <th
                                                class="align-right"
                                            >
                                                Subtotal
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <tr>

                                            <td>

                                                <strong>
                                                    ${escapeHtml(
                                                        receipt.item
                                                    )}
                                                </strong>

                                                <small>
                                                    ${escapeHtml(
                                                        receipt.game
                                                    )}
                                                </small>

                                            </td>

                                            <td
                                                class="align-right"
                                            >

                                                ${escapeHtml(
                                                    receipt.quantity
                                                )}

                                            </td>

                                            <td
                                                class="align-right"
                                            >

                                                ${formatRupiah(
                                                    price
                                                )}

                                            </td>

                                            <td
                                                class="align-right"
                                            >

                                                ${formatRupiah(
                                                    subtotal
                                                )}

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>


                                <!-- TOTAL -->

                                <div
                                    class="print-total-box"
                                >

                                    <span>
                                        Total Pembayaran
                                    </span>

                                    <strong>
                                        ${formatRupiah(
                                            subtotal
                                        )}
                                    </strong>

                                </div>


                                <!-- FOOTER -->

                                <footer
                                    class="print-footer"
                                >

                                    <div
                                        class="print-thank-you"
                                    >

                                        Terima kasih telah
                                        melakukan transaksi.

                                    </div>


                                    <div
                                        class="print-contact"
                                    >

                                        ${
                                            whatsapp
                                            ? `
                                                <span>
                                                    WhatsApp:
                                                    ${escapeHtml(
                                                        whatsapp
                                                    )}
                                                </span>
                                            `
                                            : ''
                                        }


                                        ${
                                            email
                                            ? `
                                                <span>
                                                    Email:
                                                    ${escapeHtml(
                                                        email
                                                    )}
                                                </span>
                                            `
                                            : ''
                                        }


                                        ${
                                            address
                                            ? `
                                                <span>
                                                    ${escapeHtml(
                                                        address
                                                    )}
                                                </span>
                                            `
                                            : ''
                                        }

                                    </div>


                                    <div
                                        class="print-generated"
                                    >

                                        Receipt ini dicetak dari
                                        ${escapeHtml(
                                            appName ||
                                            'TopUp'
                                        )}

                                    </div>

                                </footer>


                            </div>

                        </section>

                    `;

                })
                .join('');


        const html = `

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Receipt ${escapeHtml(
            appName || 'TopUp'
        )}
    </title>


    <style>

        @page {
            size: A4;
            margin: 10mm;
        }


        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;
        }


        body {
            background: #ffffff;

            color: #111827;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;

            -webkit-print-color-adjust:
                exact;

            print-color-adjust:
                exact;
        }


        .print-receipt {
            width: 100%;
        }


        .print-receipt-page {
            break-after: page;
            page-break-after: always;
        }


        .print-receipt-inner {

            width: 100%;

            max-width: 760px;

            margin: 0 auto;

            padding: 28px 30px;

            border: 1px solid #d9dee7;

            border-radius: 12px;

            background: #ffffff;

        }


        .print-receipt-header {

            display: flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap: 20px;

            padding-bottom: 18px;

            border-bottom:
                2px solid #1f2937;

        }


        .print-brand {

            display: flex;

            align-items:
                center;

            gap: 12px;

        }


        .print-logo {

            width: 48px;

            height: 48px;

            object-fit: contain;

        }


        .print-store-name {

            color: #111827;

            font-size: 21px;

            font-weight: 800;

            line-height: 1.1;

        }


        .print-document-title {

            margin-top: 4px;

            color: #6b7280;

            font-size: 10px;

            font-weight: 700;

            letter-spacing:
                .14em;

        }


        .print-status {

            display: inline-flex;

            align-items:
                center;

            justify-content:
                center;

            min-width: 92px;

            padding: 7px 12px;

            border: 1px solid #d1d5db;

            border-radius: 999px;

            color: #374151;

            font-size: 10px;

            font-weight: 700;

            text-transform:
                uppercase;

        }


        .print-status.completed,
        .print-status.success,
        .print-status.successful {

            border-color:
                #86efac;

            background:
                #f0fdf4;

            color:
                #166534;

        }


        .print-status.processing,
        .print-status.process {

            border-color:
                #93c5fd;

            background:
                #eff6ff;

            color:
                #1d4ed8;

        }


        .print-status.refunded,
        .print-status.refund {

            border-color:
                #fde68a;

            background:
                #fffbeb;

            color:
                #92400e;

        }


        .print-order-meta {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 12px;

            margin-top: 18px;

        }


        .print-order-meta > div {

            padding: 11px 13px;

            border:
                1px solid #e5e7eb;

            border-radius: 8px;

            background: #f9fafb;

        }


        .print-order-meta span {

            display: block;

            margin-bottom: 4px;

            color: #6b7280;

            font-size: 10px;

        }


        .print-order-meta strong {

            display: block;

            color: #111827;

            font-size: 12px;

        }


        .print-section-title {

            margin-top: 24px;

            margin-bottom: 9px;

            color: #111827;

            font-size: 11px;

            font-weight: 800;

            text-transform:
                uppercase;

            letter-spacing:
                .08em;

        }


        .print-product-card {

            padding: 14px;

            border-left:
                4px solid #4f6fce;

            border-radius: 6px;

            background: #f7f9fd;

        }


        .print-game {

            color: #111827;

            font-size: 14px;

            font-weight: 700;

        }


        .print-item {

            margin-top: 3px;

            color: #64748b;

            font-size: 12px;

        }


        .print-item-table {

            width: 100%;

            margin-top: 14px;

            border-collapse:
                collapse;

        }


        .print-item-table th {

            padding: 9px 8px;

            border-bottom:
                1px solid #d1d5db;

            color: #6b7280;

            font-size: 10px;

            font-weight: 700;

            text-transform:
                uppercase;

        }


        .print-item-table td {

            padding: 12px 8px;

            border-bottom:
                1px solid #e5e7eb;

            vertical-align:
                top;

        }


        .print-item-table td strong {

            display: block;

            color: #111827;

            font-size: 12px;

        }


        .print-item-table td small {

            display: block;

            margin-top: 3px;

            color: #6b7280;

            font-size: 10px;

        }


        .align-right {

            text-align: right;

        }


        .print-total-box {

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap: 20px;

            margin-top: 18px;

            padding: 15px 16px;

            border-radius: 8px;

            background: #111827;

            color: #ffffff;

        }


        .print-total-box span {

            font-size: 11px;

            font-weight: 600;

        }


        .print-total-box strong {

            font-size: 17px;

            font-weight: 800;

        }


        .print-footer {

            margin-top: 22px;

            padding-top: 14px;

            border-top:
                1px dashed #cbd5e1;

            text-align: center;

        }


        .print-thank-you {

            color: #111827;

            font-size: 11px;

            font-weight: 700;

        }


        .print-contact {

            display: flex;

            flex-wrap: wrap;

            justify-content:
                center;

            gap: 4px 14px;

            margin-top: 7px;

            color: #64748b;

            font-size: 9px;

        }


        .print-generated {

            margin-top: 9px;

            color: #94a3b8;

            font-size: 8px;

        }


        @media print {

            body {

                background:
                    #ffffff;

            }


            .print-receipt-inner {

                max-width: none;

                border: 0;

                border-radius: 0;

                padding: 0;

            }

        }


    </style>

</head>


<body>

    ${receiptHtml}

</body>

</html>
        `;


        printWindow.document.open();

        printWindow.document.write(
            html
        );

        printWindow.document.close();


        /*
        |--------------------------------------------------------------------------
        | AUTO PRINT
        |--------------------------------------------------------------------------
        */

        printWindow.onload =
            function () {

                setTimeout(
                    function () {

                        printWindow.focus();

                        printWindow.print();

                    },
                    350
                );

            };

    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL STATE
    |--------------------------------------------------------------------------
    */

    updateBulkSelection();

});
</script>

@endpush
@endsection