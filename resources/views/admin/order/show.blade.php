@extends('admin.layouts.app')

@section('content')

<div class="container">

    {{-- =====================================================
         FLASH MESSAGE
    ====================================================== --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fa-solid fa-circle-check me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fa-solid fa-circle-exclamation me-2"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif

<div class="container">

@auth

    <div class="d-flex gap-2 mb-3">

        <a
            href="{{ route('admin.order.index') }}"
            class="btn btn-secondary"
        >

            <i class="fa-solid fa-arrow-left me-1"></i>

            Kembali

        </a>

    </div>

@endauth
<div class="card shadow">


<div class="card-header bg-primary text-white">

<h5>

Detail Order

</h5>

</div>



<div class="card-body">


<h4>

{{$order->invoice_number}}

</h4>


<hr>


<p>

Game :

{{$order->game->game_name}}

</p>


<p>

User :

{{$order->user->name ?? 'Guest'}}

</p>


<h5 class="mt-4">
    Data Player
</h5>

<table class="table table-bordered">

@foreach($order->game->player_fields ?? [] as $field)

<tr>

    <th width="220">

        {{ $field['label'] }}

    </th>

    <td>

        {{ $order->player_data[$field['name']] ?? '-' }}

    </td>

</tr>

@endforeach

</table>



<h5>

Item

</h5>


<ul>

@foreach($order->details as $detail)


<li>

{{$detail->item->item_name}}

x{{$detail->qty}}

-
Rp {{number_format($detail->subtotal)}}


</li>


@endforeach

</ul>



<hr>


<h4>

Total :

Rp {{number_format($order->total_price)}}

</h4>




@if($order->payment_proof)


<h5>

Bukti Pembayaran

</h5>


<img

src="{{asset('storage/'.$order->payment_proof)}}"

width="300"

class="img-thumbnail">


@endif




<hr>



<form action="{{route('admin.order.update',$order)}}"
method="POST">


@csrf

@method('PUT')



<select name="status"
class="form-select">


@php
$statusList = [

    'Pending',

    'Waiting Payment',

    'Paid',

    'Cancelled'

];
@endphp


@foreach($statusList as $status)

<option value="{{ $status }}"
@if($order->status == $status)
selected
@endif
>

{{ $status }}

</option>

@endforeach


</select>



<textarea
name="notes"
class="form-control mt-3"
placeholder="Catatan admin"></textarea>



<button class="btn btn-primary mt-3">

Update Status

</button>


</form>

{{-- =========================================================
     PAYMENT VERIFICATION
========================================================= --}}

@if($order->status === 'Waiting Payment')

    <div class="d-grid gap-2 mt-3">

        <form
            action="{{ route('admin.order.approve', $order) }}"
            method="POST"
        >

            @csrf

            <button
                type="submit"
                class="btn btn-success"
            >

                <i class="fa-solid fa-circle-check me-2"></i>

                Approve Pembayaran

            </button>

        </form>

    </div>

@endif
{{-- =========================================================
     PROCESS ORDER
========================================================= --}}
@if($order->status === 'Paid')

    <div class="d-grid gap-2 mt-3">

        <form
            action="{{ route('admin.order.confirm', $order) }}"
            method="POST"
        >

            @csrf

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-box-open me-2"></i>

                Proses & Selesaikan Order

            </button>

        </form>

    </div>

@endif
</div>


</div>


</div>


@endsection