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

        </div>

    </div>

</div>

@endsection