@extends('admin.layouts.app')


@section('content')


<div class="container">


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


<p>

Player UID :

{{$order->player_uid}}

</p>


<p>

Server :

{{$order->server_id}}

</p>



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





@if($order->status == 'Paid')

<div class="d-grid gap-2 mt-3">

<form action="{{ route('admin.order.confirm',$order) }}"
      method="POST">

    @csrf

    <button class="btn btn-success">

        <i class="fa-solid fa-circle-check me-2"></i>

        Konfirmasi Pembayaran

    </button>

</form>

<form action="{{ route('admin.order.reject',$order) }}"
      method="POST">

    @csrf

    <button class="btn btn-danger">

        <i class="fa-solid fa-circle-xmark me-2"></i>

        Tolak Pembayaran

    </button>

</form>

</div>

@endif



</div>


</div>


</div>


@endsection