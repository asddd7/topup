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

                    {{-- Selalu kembali ke halaman 1 ketika filter berubah --}}

                    <input
                        type="hidden"
                        name="page"
                        value="1"
                    >


                    <div class="row g-3 align-items-end">

                        {{-- =================================================
                             START DATE
                        ================================================== --}}

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


                        {{-- =================================================
                             END DATE
                        ================================================== --}}

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


                        {{-- =================================================
                             STATUS
                        ================================================== --}}

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
                                    {{ $status === 'processing' ? 'selected' : '' }}
                                >
                                    Processing
                                </option>

                                <option
                                    value="completed"
                                    {{ $status === 'completed' ? 'selected' : '' }}
                                >
                                    Completed
                                </option>

                                <option
                                    value="refunded"
                                    {{ $status === 'refunded' ? 'selected' : '' }}
                                >
                                    Refunded
                                </option>

                            </select>

                        </div>


                        {{-- =================================================
                             LIMIT
                        ================================================== --}}

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
                                        {{ (int) $limit === $option ? 'selected' : '' }}
                                    >
                                        {{ $option }} data
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- =================================================
                             ACTION
                        ================================================== --}}

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
            | Helper untuk mengambil value dari response MooGold
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
            | Pagination
            |--------------------------------------------------------------------------
            */

            $currentPage = (int) $currentPage;

            $limit = (int) $limit;

            $hasPreviousPage =
                $currentPage > 1;

            /*
            |--------------------------------------------------------------------------
            | Karena API response yang diberikan belum mempunyai
            | total_pages / has_more, sementara menggunakan jumlah
            | data dibandingkan limit.
            |--------------------------------------------------------------------------
            */

            $hasNextPage =
                $orderCount >= $limit;


            /*
            |--------------------------------------------------------------------------
            | Query pagination
            |--------------------------------------------------------------------------
            */

            $queryParams = [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status'     => $status,
                'limit'      => $limit,
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
             TABLE CARD
        ====================================================== --}}

        <div class="transaction-history-table-card card border-0 shadow-sm rounded-4 overflow-hidden">

            <div class="card-body p-0">

                @if($orderCollection->isNotEmpty())

                    {{-- =================================================
                         TABLE
                    ================================================== --}}

                    <div class="transaction-history-table-wrapper table-responsive">

                        <table class="transaction-history-table table table-hover align-middle mb-0">

                            <thead>

                                <tr>

                                    <th class="column-number px-3 py-3">
                                        #
                                    </th>

                                    <th class="column-order py-3">
                                        Order
                                    </th>

                                    <th class="column-partner py-3">
                                        Partner Order
                                    </th>

                                    <th class="column-status py-3">
                                        Status
                                    </th>

                                    <th class="column-amount py-3">
                                        Amount
                                    </th>

                                    <th class="column-customer py-3">
                                        Customer
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
                                        | PARTNER ORDER ID
                                        |--------------------------------------------------------------------------
                                        */

                                        $partnerOrderId = $getValue(
                                            $order,
                                            [
                                                'partner_order_id',
                                                'partnerOrderId',
                                                'external_order_id',
                                            ]
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | STATUS
                                        |--------------------------------------------------------------------------
                                        */

                                        $orderStatusRaw = $getValue(
                                            $order,
                                            [
                                                'status',
                                                'order_status',
                                                'transaction_status',
                                            ]
                                        );

                                        $orderStatus = strtolower(
                                            trim(
                                                (string) $orderStatusRaw
                                            )
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | AMOUNT
                                        |--------------------------------------------------------------------------
                                        */

                                        $amount = $getValue(
                                            $order,
                                            [
                                                'amount',
                                                'gross_amount',
                                                'total',
                                                'price',
                                            ]
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | CUSTOMER
                                        |--------------------------------------------------------------------------
                                        */

                                        $customer = $getValue(
                                            $order,
                                            [
                                                'customer',
                                                'customer_name',
                                                'username',
                                                'user_id',
                                                'User ID',
                                            ]
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | DATE
                                        |--------------------------------------------------------------------------
                                        */

                                        $orderDate = $getValue(
                                            $order,
                                            [
                                                'created_at',
                                                'order_date',
                                                'order_created_at',
                                                'transaction_time',
                                                'date',
                                            ]
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | STATUS CLASS
                                        |--------------------------------------------------------------------------
                                        */

                                        $statusClass = match ($orderStatus) {

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

                                        {{-- NUMBER --}}

                                        <td class="px-3">

                                            {{ $rowNumber }}

                                        </td>


                                        {{-- ORDER ID --}}

                                        <td>

                                            <span class="transaction-history-order-id">

                                                {{ $orderId }}

                                            </span>

                                        </td>


                                        {{-- PARTNER ORDER ID --}}

                                        <td>

                                            <code class="transaction-history-partner-id">

                                                {{ $partnerOrderId }}

                                            </code>

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

                                                @if(
                                                    is_numeric($amount)
                                                )

                                                    Rp
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


                                        {{-- CUSTOMER --}}

                                        <td>

                                            <span class="transaction-history-customer">

                                                {{ $customer }}

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
                                                    data-partner-order-id="{{ $partnerOrderId }}"
                                                    data-status="{{ $orderStatusRaw }}"
                                                    data-amount="{{ is_numeric($amount) ? $amount : '' }}"
                                                    data-customer="{{ $customer }}"
                                                    data-date="{{ $orderDate }}"
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

                        {{-- =================================================
                             PREVIOUS
                        ================================================== --}}

                        <div>

                            @if($hasPreviousPage)

                                <a
                                    href="{{
                                        route(
                                            'admin.transaction-history.index',
                                            array_merge(
                                                $queryParams,
                                                [
                                                    'page' => $currentPage - 1,
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


                        {{-- =================================================
                             CURRENT PAGE
                        ================================================== --}}

                        <div class="text-muted small">

                            Halaman

                            <strong>
                                {{ $currentPage }}
                            </strong>

                        </div>


                        {{-- =================================================
                             NEXT
                        ================================================== --}}

                        <div>

                            @if($hasNextPage)

                                <a
                                    href="{{
                                        route(
                                            'admin.transaction-history.index',
                                            array_merge(
                                                $queryParams,
                                                [
                                                    'page' => $currentPage + 1,
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

                                <div class="receipt-editor-row">

                                    <span>
                                        Order ID
                                    </span>

                                    <strong id="receiptOrderId">
                                        -
                                    </strong>

                                </div>


                                <div class="receipt-editor-row">

                                    <span>
                                        Partner Order
                                    </span>

                                    <strong id="receiptPartnerOrderId">
                                        -
                                    </strong>

                                </div>


                                <div class="receipt-editor-row">

                                    <span>
                                        Customer
                                    </span>

                                    <strong id="receiptCustomer">
                                        -
                                    </strong>

                                </div>


                                <div class="receipt-editor-row">

                                    <span>
                                        Status
                                    </span>

                                    <strong id="receiptStatus">
                                        -
                                    </strong>

                                </div>


                                <div class="receipt-editor-row">

                                    <span>
                                        Tanggal
                                    </span>

                                    <strong id="receiptDate">
                                        -
                                    </strong>

                                </div>


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
                                        Harga ini hanya digunakan untuk cetakan receipt.
                                        Tidak mengubah transaksi MooGold.
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
        </div>

    </div>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | RECEIPT
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

    const receiptOrderId =
        document.getElementById('receiptOrderId');

    const receiptPartnerOrderId =
        document.getElementById('receiptPartnerOrderId');

    const receiptCustomer =
        document.getElementById('receiptCustomer');

    const receiptStatus =
        document.getElementById('receiptStatus');

    const receiptDate =
        document.getElementById('receiptDate');

    const receiptPrice =
        document.getElementById('receiptPrice');

    const printReceiptButton =
        document.getElementById('printReceiptButton');


    let currentReceipt = null;


    /*
    |--------------------------------------------------------------------------
    | NUMBER FORMAT
    |--------------------------------------------------------------------------
    */

function formatRupiah(value) {

    const number = Number(value);

    if (Number.isNaN(number)) {
        return 'Rp -';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(number);

}


function escapeHtml(value) {

    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


document
    .querySelectorAll('.transaction-history-print-button')
    .forEach(button => {

        button.addEventListener('click', function () {

            currentReceipt = {
                orderId:
                    this.dataset.orderId || '-',

                partnerOrderId:
                    this.dataset.partnerOrderId || '-',

                status:
                    this.dataset.status || '-',

                amount:
                    this.dataset.amount || '',

                customer:
                    this.dataset.customer || '-',

                date:
                    this.dataset.date || '-'
            };

            receiptOrderId.textContent =
                currentReceipt.orderId;

            receiptPartnerOrderId.textContent =
                currentReceipt.partnerOrderId;

            receiptCustomer.textContent =
                currentReceipt.customer;

            receiptStatus.textContent =
                currentReceipt.status;

            receiptDate.textContent =
                currentReceipt.date;

            receiptPrice.value =
                currentReceipt.amount
                && !Number.isNaN(
                    Number(currentReceipt.amount)
                )
                    ? Number(currentReceipt.amount)
                    : '';

            receiptModal?.show();

        });

    });


printReceiptButton?.addEventListener(
    'click',
    function () {

        if (!currentReceipt) {
            return;
        }

        const price =
            Number(receiptPrice.value);

        if (
            !receiptPrice.value ||
            Number.isNaN(price) ||
            price < 0
        ) {
            receiptPrice.focus();
            return;
        }

        const appName =
            @json(setting('app_name'));

        const printWindow =
            window.open(
                '',
                '_blank',
                'width=480,height=800'
            );

        if (!printWindow) {

            alert(
                'Popup diblokir browser. Izinkan popup untuk mencetak receipt.'
            );

            return;
        }

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
        Receipt ${escapeHtml(currentReceipt.orderId)}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #ffffff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }

        .receipt {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .receipt-header {
            text-align: center;
            padding-bottom: 18px;
            border-bottom: 1px dashed #9ca3af;
        }

        .receipt-app-name {
            margin-bottom: 4px;
            font-size: 20px;
            font-weight: 700;
        }

        .receipt-title {
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .receipt-info {
            padding: 18px 0;
            border-bottom: 1px dashed #9ca3af;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 9px;
        }

        .receipt-row:last-child {
            margin-bottom: 0;
        }

        .receipt-label {
            color: #6b7280;
        }

        .receipt-value {
            max-width: 65%;
            text-align: right;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .receipt-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 0;
            font-size: 17px;
            font-weight: 700;
        }

        .receipt-footer {
            padding-top: 18px;
            border-top: 1px dashed #9ca3af;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            line-height: 1.6;
        }

        @media print {
            body {
                padding: 0;
            }

            .receipt {
                max-width: none;
            }
        }
    </style>

</head>

<body>

    <div class="receipt">

        <div class="receipt-header">

            <div class="receipt-app-name">
                ${escapeHtml(appName || 'TopUp')}
            </div>

            <div class="receipt-title">
                Bukti Transaksi
            </div>

        </div>

        <div class="receipt-info">

            <div class="receipt-row">
                <span class="receipt-label">
                    Order ID
                </span>

                <span class="receipt-value">
                    ${escapeHtml(currentReceipt.orderId)}
                </span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">
                    Partner Order
                </span>

                <span class="receipt-value">
                    ${escapeHtml(currentReceipt.partnerOrderId)}
                </span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">
                    Customer
                </span>

                <span class="receipt-value">
                    ${escapeHtml(currentReceipt.customer)}
                </span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">
                    Status
                </span>

                <span class="receipt-value">
                    ${escapeHtml(currentReceipt.status)}
                </span>
            </div>

            <div class="receipt-row">
                <span class="receipt-label">
                    Tanggal
                </span>

                <span class="receipt-value">
                    ${escapeHtml(currentReceipt.date)}
                </span>
            </div>

        </div>

        <div class="receipt-total">

            <span>
                Total
            </span>

            <span>
                ${formatRupiah(price)}
            </span>

        </div>

        <div class="receipt-footer">

            Terima kasih telah melakukan transaksi.

            <br>

            Receipt ini merupakan bukti transaksi
            dari website ${escapeHtml(appName || 'TopUp')}.

        </div>

    </div>

    <script>

        window.onload = function () {

            window.print();

            window.onafterprint = function () {
                window.close();
            };

        };

    <\/script>

</body>

</html>
        `;

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();

        receiptModal?.hide();

    }
);

});
</script>
@endpush
@endsection