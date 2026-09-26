@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

<div class="admin-dashboard-page">

    <div class="admin-dashboard-container">


        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="admin-dashboard-header">

            <h1 class="admin-dashboard-title">
                Dashboard Admin
            </h1>

            <p class="admin-dashboard-subtitle">
                Ringkasan pesanan, game, pendapatan, dan aktivitas terbaru.
            </p>

        </div>


        {{-- =====================================================
             STATISTICS
        ====================================================== --}}

        <div class="admin-stat-grid">


            {{-- TOTAL ORDER --}}

            <div class="admin-stat-card primary">

                <div class="admin-stat-body">

                    <div class="admin-stat-copy">
                        <span class="admin-stat-label">
                            Total Pesanan
                        </span>

                        <h2 class="admin-stat-value">
                            {{ $totalOrder }}
                        </h2>
                    </div>

                    <span class="admin-stat-icon" aria-hidden="true">
                        <i class="fa-solid fa-receipt"></i>
                    </span>

                </div>

            </div>


            {{-- WAITING PAYMENT --}}

            <div class="admin-stat-card warning">

                <div class="admin-stat-body">

                    <div class="admin-stat-copy">
                        <span class="admin-stat-label">
                            Menunggu Pembayaran
                        </span>

                        <h2 class="admin-stat-value">
                            {{ $waitingPayment }}
                        </h2>
                    </div>

                    <span class="admin-stat-icon" aria-hidden="true">
                        <i class="fa-regular fa-clock"></i>
                    </span>

                </div>

            </div>


            {{-- TOTAL GAME --}}

            <div class="admin-stat-card primary">

                <div class="admin-stat-body">

                    <div class="admin-stat-copy">
                        <span class="admin-stat-label">
                            Jumlah Game
                        </span>

                        <h2 class="admin-stat-value">
                            {{ $totalGame }}
                        </h2>
                    </div>

                    <span class="admin-stat-icon" aria-hidden="true">
                        <i class="fa-solid fa-gamepad"></i>
                    </span>

                </div>

            </div>


            {{-- INCOME --}}

            <div class="admin-stat-card success">

                <div class="admin-stat-body">

                    <div class="admin-stat-copy">
                        <span class="admin-stat-label">
                            Pendapatan
                        </span>

                        <h2 class="admin-stat-value currency">
                            Rp {{ number_format($income) }}
                        </h2>
                    </div>

                    <span class="admin-stat-icon" aria-hidden="true">
                        <i class="fa-solid fa-wallet"></i>
                    </span>

                </div>

            </div>

        </div>


        {{-- =====================================================
             SHORTCUT
        ====================================================== --}}

        <section class="admin-shortcut-section">

            <h2 class="admin-shortcut-title">
                Shortcut
            </h2>


            <div class="admin-shortcut-grid">


                {{-- GAME --}}

                <a
                    href="{{ route('admin.game.index') }}"
                    class="admin-shortcut-card"
                >

                    <div class="admin-shortcut-icon">

                        <i class="fa-solid fa-gamepad"></i>

                    </div>

                    <span class="admin-shortcut-label">
                        Game
                    </span>

                </a>


                {{-- VOUCHER --}}

                <a
                    href="{{ route('admin.discount.index') }}"
                    class="admin-shortcut-card"
                >

                    <div class="admin-shortcut-icon">

                        <i class="fa-solid fa-ticket"></i>

                    </div>

                    <span class="admin-shortcut-label">
                        Voucher
                    </span>

                </a>


                {{-- TOP SELLER --}}

                <a
                    href="{{ route('top-seller.index') }}"
                    class="admin-shortcut-card"
                >

                    <div class="admin-shortcut-icon">

                        <i class="fa-solid fa-star"></i>

                    </div>

                    <span class="admin-shortcut-label">
                        Top Seller
                    </span>

                </a>


                {{-- SETTING --}}

                <a
                    href="{{ route('admin.setting.index') }}"
                    class="admin-shortcut-card"
                >

                    <div class="admin-shortcut-icon">

                        <i class="fa-solid fa-gears"></i>

                    </div>

                    <span class="admin-shortcut-label">
                        Setting
                    </span>

                </a>

            </div>

        </section>


        {{-- =====================================================
             RECENT ORDERS
        ====================================================== --}}

        <div class="admin-recent-order-card">


            {{-- HEADER --}}

            <div class="admin-recent-order-header">

                <h2 class="admin-recent-order-title">
                    Order Terbaru
                </h2>

                <a href="{{ route('admin.order.index') }}"
                   class="admin-recent-order-link">
                    Semua Pesanan
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>

            </div>


            {{-- TABLE --}}

            <div class="admin-recent-order-body">

                <table class="admin-recent-order-table table">

                    <thead>

                        <tr>

                            <th>
                                Invoice
                            </th>

                            <th>
                                Game
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($recentOrders as $order)

                            <tr>

                                <td>

                                    <a class="admin-recent-order-invoice"
                                       href="{{ route('admin.order.show', $order->id) }}">
                                        {{ $order->invoice_number }}
                                    </a>

                                </td>


                                <td>

                                    <span class="admin-recent-order-game">

                                        {{ $order->game->game_name }}

                                    </span>

                                </td>


                                <td>

                                    <span class="admin-recent-order-total">

                                        Rp {{ number_format($order->total_price) }}

                                    </span>

                                </td>


                                <td>

                                    @if($order->status === 'Waiting Payment')

                                        <span class="admin-order-status waiting">

                                            <i class="fa-regular fa-clock"></i>

                                            Waiting Payment

                                        </span>

                                    @elseif($order->status === 'Paid')

                                        <span class="admin-order-status paid">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Paid

                                        </span>

                                    @elseif($order->status === 'Processing')

                                        <span class="admin-order-status processing">

                                            <i class="fa-solid fa-spinner"></i>

                                            Processing

                                        </span>

                                    @elseif($order->status === 'Completed')

                                        <span class="admin-order-status completed">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Completed

                                        </span>

                                    @elseif($order->status === 'Cancelled')

                                        <span class="admin-order-status cancelled">

                                            <i class="fa-solid fa-circle-xmark"></i>

                                            Cancelled

                                        </span>

                                    @elseif($order->status === 'Success')

                                        <span class="admin-order-status success">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Success

                                        </span>

                                    @else

                                        <span class="admin-order-status waiting">

                                            {{ $order->status }}

                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center py-4"
                                >

                                    <span class="text-muted small">

                                        Belum ada pesanan.

                                    </span>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection