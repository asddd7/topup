@extends('admin.layouts.app')


@section('title','Manajemen Voucher')


@section('content')


<div class="container-fluid">


<div class="card shadow-sm">


<div class="card-header d-flex justify-content-between align-items-center">


<h5 class="mb-0 fw-bold">

<i class="fa-solid fa-ticket me-2"></i>

Data Voucher Diskon

</h5>


<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#createDiscountModal">


<i class="fa-solid fa-plus"></i>

Tambah Voucher


</button>


</div>



<div class="card-body">


@if(session('success'))

<div class="alert alert-success">

{{session('success')}}

</div>

@endif

<div class="row g-3 mb-4">

    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <small class="text-muted">
                    Total Promo
                </small>

                <h3 class="fw-bold mb-0">
                    {{ $discounts->count() }}
                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <small class="text-muted">
                    Promo Aktif
                </small>

                <h3 class="fw-bold text-success mb-0">

                    {{ $discounts->where('is_active', 1)->count() }}

                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <small class="text-muted">
                    Voucher Habis
                </small>

                <h3 class="fw-bold text-danger mb-0">

                    {{ $discounts
                        ->whereNotNull('usage_limit')
                        ->filter(function ($discount) {

                            return
                                (int) $discount->quota_used
                                >=
                                (int) $discount->usage_limit;

                        })
                        ->count()
                    }}

                </h3>

            </div>

        </div>

    </div>

</div>

<div class="table-responsive">


<table class="table table-hover align-middle">


<thead>

<tr>

<th>No</th>

<th>Kode</th>

<th>Nama</th>

<th>Target</th>

<th>Tipe</th>

<th>Diskon</th>

<th>Quota</th>

<th>Periode</th>

<th>Status</th>

<th width="150">
Action
</th>


</tr>

</thead>


<tbody>

@forelse($discounts as $discount)

    @php

        /*
        |--------------------------------------------------------------------------
        | Quota
        |--------------------------------------------------------------------------
        */

        $usageLimit = $discount->usage_limit;

        $quotaUsed = (int) (
            $discount->quota_used ?? 0
        );

        if ($usageLimit !== null) {

            $remainingQuota = max(
                (int) $usageLimit - $quotaUsed,
                0
            );

        } else {

            $remainingQuota = null;

        }

    @endphp


    <tr>

        {{-- NO --}}
        <td>
            {{ $loop->iteration }}
        </td>


        {{-- KODE --}}
        <td>

            @if($discount->code)

                <span class="badge bg-dark">
                    {{ $discount->code }}
                </span>

            @else

                <span class="text-muted">
                    -
                </span>

            @endif

        </td>


        {{-- NAMA --}}
        <td>

            <div class="fw-semibold">
                {{ $discount->discount_name }}
            </div>

            <small class="text-muted">

                @switch($discount->trigger_type)

                    @case('voucher')
                        Voucher
                        @break

                    @case('automatic')
                        Otomatis
                        @break

                    @case('new_user')
                        User Baru
                        @break

                    @case('flash_sale')
                        Flash Sale
                        @break

                    @case('payment_method')
                        Metode Pembayaran
                        @break

                    @default
                        -

                @endswitch

            </small>

        </td>


        {{-- TARGET --}}
        <td>

            @if($discount->game)

                <span class="badge bg-primary">
                    {{ $discount->game->game_name }}
                </span>

            @elseif($discount->item)

                <span class="badge bg-info text-dark">
                    {{ $discount->item->item_name }}
                </span>

            @else

                <span class="badge bg-success">
                    Semua Produk
                </span>

            @endif

        </td>


        {{-- TIPE --}}
        <td>

            @if($discount->discount_type === 'percent')

                <span class="badge bg-primary">
                    Persen
                </span>

            @else

                <span class="badge bg-warning text-dark">
                    Nominal
                </span>

            @endif

        </td>


        {{-- NILAI DISKON --}}
        <td>

            @if($discount->discount_type === 'percent')

                <strong>
                    {{ rtrim(rtrim(number_format($discount->amount, 2, '.', ''), '0'), '.') }}%
                </strong>

            @else

                <strong>
                    Rp {{ number_format($discount->amount, 0, ',', '.') }}
                </strong>

            @endif

        </td>


        {{-- QUOTA --}}
        <td>

            @if($discount->usage_limit === null)

                <span class="badge bg-info">
                    Unlimited
                </span>

            @else

                @php
                    $totalVoucher = (int) $discount->usage_limit;

                    $terpakai = (int) $discount->quota_used;

                    $sisaVoucher = max(
                        $totalVoucher - $terpakai,
                        0
                    );
                @endphp

                <div class="mb-1">

                    <strong>
                        {{ $sisaVoucher }}
                    </strong>

                    /
                    {{ $totalVoucher }}

                </div>

                <small class="text-muted">

                    Terpakai:
                    {{ $terpakai }}

                </small>

            @endif

        </td>


        {{-- PERIODE --}}
        <td>

            <div>
                {{ $discount->start_date
                    ? \Carbon\Carbon::parse($discount->start_date)->format('d/m/Y')
                    : '-'
                }}
            </div>

            <small class="text-muted">
                s/d
            </small>

            <div>
                {{ $discount->end_date
                    ? \Carbon\Carbon::parse($discount->end_date)->format('d/m/Y')
                    : '-'
                }}
            </div>

        </td>


        {{-- STATUS --}}
        <td>

            @if($discount->is_active)

                @if(
                    $usageLimit !== null &&
                    $remainingQuota <= 0
                )

                    <span class="badge bg-danger">
                        Habis
                    </span>

                @else

                    <span class="badge bg-success">
                        Aktif
                    </span>

                @endif

            @else

                <span class="badge bg-secondary">
                    Nonaktif
                </span>

            @endif

        </td>


        {{-- ACTION --}}
        <td>

            <button
                class="btn btn-warning btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#editDiscountModal{{ $discount->id }}"
                title="Edit"
            >

                <i class="fa-solid fa-pen"></i>

            </button>


            <form
                action="{{ route(
                    'admin.discount.destroy',
                    $discount->id
                ) }}"
                method="POST"
                class="d-inline"
            >

                @csrf

                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm(
                        'Hapus promo {{ addslashes($discount->discount_name) }}?'
                    )"
                    title="Hapus"
                >

                    <i class="fa-solid fa-trash"></i>

                </button>

            </form>

        </td>

    </tr>

@empty

    <tr>

        <td
            colspan="9"
            class="text-center py-5"
        >

            <i
                class="fa-solid fa-ticket fa-2x text-muted mb-3"
            ></i>

            <div class="text-muted">
                Belum ada promo.
            </div>

        </td>

    </tr>

@endforelse

</tbody>


</table>


</div>


</div>


</div>


</div>



@include('admin.discount.create')


@foreach($discounts as $discount)

@include(
'admin.discount.edit',
[
'discount'=>$discount
]
)

@endforeach



@endsection