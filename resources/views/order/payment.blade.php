@extends('layouts.app')

@section('title','Pembayaran')

@section('content')

<div class="container py-4">


<div class="mb-3">

@auth

<a href="{{ route('order.index') }}"
class="btn btn-secondary">

<i class="fa-solid fa-arrow-left me-1"></i>

Kembali ke Riwayat Pesanan

</a>


@else


<a href="
{{ route('order.show',$order->invoice_number) }}
@if($order->guest_token)
?token={{$order->guest_token}}
@endif
"
class="btn btn-secondary">


<i class="fa-solid fa-arrow-left me-1"></i>

Kembali ke Detail Pesanan


</a>


@endauth


</div>

<div class="row">

<div class="col-lg-8">

<div class="card shadow border-0">



<div class="card-header bg-success text-white">

<h5>

Invoice Pembayaran

</h5>

</div>

<div class="card-body">

<table class="table">

<tr>

<th>Invoice</th>

<td>

{{ $order->invoice_number }}

</td>

</tr>

<tr>

<th>Status</th>

<td>

<span class="badge bg-warning">

{{ $order->status }}

</span>

</td>

</tr>

<tr>

<th>Total</th>

<td>

<h4 class="text-success">

Rp {{ number_format($order->total_price) }}

</h4>

</td>

</tr>

<tr>

<th>Metode</th>

<td>

{{ $order->payment->payment_name }}

</td>

</tr>

<tr>

<th>No Pembayaran</th>

<td>

{{ $order->payment->payment_number }}

</td>

</tr>

<tr>

<th>Atas Nama</th>

<td>

{{ $order->payment->account_name }}

</td>

</tr>

@if(!empty($order->player_data))

@foreach($order->player_data as $key => $value)

<tr>

    <th>

        @php

            $label = collect($order->game->player_fields ?? [])
                        ->firstWhere('name', $key)['label'] ?? ucwords(str_replace('_',' ',$key));

        @endphp

        {{ $label }}

    </th>

    <td>

        {{ $value }}

    </td>

</tr>

@endforeach

@endif

</table>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card shadow">

<div class="card-body text-center">

@if($order->payment->image)

<img
src="{{ asset('storage/'.$order->payment->image) }}"
class="img-fluid mb-3">

@endif

@if(
$order->status == 'Waiting Payment'
)


<form
action="{{ route('order.uploadProof',$order->invoice_number) }}"
method="POST"
enctype="multipart/form-data">

@csrf

@if($order->guest_token)

<input
type="hidden"
name="token"
value="{{ $order->guest_token }}">

@endif

@csrf


<input
type="file"
name="payment_proof"
class="form-control mb-3"
required>



<button
class="btn btn-success w-100">


<i class="fa-solid fa-upload"></i>

Upload Bukti Pembayaran


</button>


</form>


@else


<div class="alert alert-info">


Pembayaran sudah diproses.


</div>


@endif

@if(!$order->user_id)

@php
$link = url('/order/'.$order->invoice_number).'?token='.$order->guest_token;
@endphp

<div class="alert alert-info">

<div class="d-flex align-items-center mb-2">

<i class="fa-solid fa-link me-2"></i>

<strong>Simpan link pesanan Anda</strong>

</div>

<p class="small text-muted mb-3">
Link ini digunakan untuk melihat status pesanan dan mengunggah ulang bukti pembayaran jika diperlukan.
</p>

<div class="input-group mb-3">

<input
type="text"
class="form-control"
id="guestOrderLink"
value="{{ $link }}"
readonly>

<button
type="button"
class="btn btn-primary"
onclick="copyOrderLink()">

<i class="fa-solid fa-copy me-1"></i>

Copy

</button>

</div>

<a
href="{{ $link }}"
class="btn btn-outline-primary w-100">

<i class="fa-solid fa-arrow-up-right-from-square me-1"></i>

Buka Link Pesanan

</a>

</div>

@endif
</div>

</div>

</div>

</div>

</div>
@push('scripts')

<script>

function copyOrderLink(){

    const input = document.getElementById('guestOrderLink');

    if(navigator.clipboard){

        navigator.clipboard.writeText(input.value)
        .then(()=>{

            Swal.fire({

                icon:'success',

                title:'Link berhasil disalin',

                text:'Silakan simpan link ini agar pesanan dapat dibuka kembali.',

                timer:1800,

                showConfirmButton:false

            });

        });

    }else{

        input.select();
        input.setSelectionRange(0,99999);

        document.execCommand('copy');

        Swal.fire({

            icon:'success',

            title:'Link berhasil disalin',

            timer:1800,

            showConfirmButton:false

        });

    }

}

</script>

@endpush
@endsection