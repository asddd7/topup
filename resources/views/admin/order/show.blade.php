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


<option>
Waiting Payment
</option>


<option>
Processing
</option>


<option>
Success
</option>


<option>
Failed
</option>


</select>



<textarea
name="notes"
class="form-control mt-3"
placeholder="Catatan admin"></textarea>



<button class="btn btn-primary mt-3">

Update Status

</button>


</form>





@if($order->status=='Waiting Payment')


<form action="{{route('admin.order.confirm',$order)}}"
method="POST"
class="mt-2">

@csrf


<button class="btn btn-success w-100">

Konfirmasi Pembayaran

</button>


</form>



<form action="{{route('admin.order.reject',$order)}}"
method="POST"
class="mt-2">

@csrf


<button class="btn btn-danger w-100">

Tolak Pembayaran

</button>


</form>


@endif



</div>


</div>


</div>


@endsection