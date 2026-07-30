@extends('layouts.app')


@section('content')


<div class="container py-4">


<h4 class="fw-bold mb-4">

<i class="fa-solid fa-clock-rotate-left me-2"></i>

Riwayat Pesanan

</h4>



<div class="card shadow-sm border-0 rounded-4">


<div class="card-body">



<div class="table-responsive">


<table class="table align-middle">


<thead class="table-dark">

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


<th>
Action
</th>


</tr>

</thead>




<tbody>


@forelse($orders as $order)



<tr>


<td>


<strong>

{{$order->invoice_number}}

</strong>


</td>




<td>

{{$order->game->game_name}}

</td>




<td>

Rp {{number_format($order->total_price)}}

</td>




<td>


@if($order->status == 'Waiting Payment')


<span class="badge bg-warning">

{{$order->status}}

</span>


@elseif($order->status == 'Paid')


<span class="badge bg-info">

{{$order->status}}

</span>


@elseif($order->status == 'Processing')


<span class="badge bg-primary">

{{$order->status}}

</span>


@elseif($order->status == 'Completed')


<span class="badge bg-success">

{{$order->status}}

</span>


@else


<span class="badge bg-danger">

{{$order->status}}

</span>


@endif



</td>




<td>



<a href="{{ route(
    'order.show',
    [
        'invoice'=>$order->invoice_number,
        'token'=>$order->guest_token
    ]
) }}"
class="btn btn-primary btn-sm">


<i class="fa-solid fa-eye me-1"></i>

Detail


</a>



@if(
$order->status == 'Waiting Payment'
)


<a href="{{ route(
    'order.payment',
    [
        'invoice'=>$order->invoice_number,
        'token'=>$order->guest_token
    ]
) }}"
class="btn btn-success btn-sm">


<i class="fa-solid fa-credit-card me-1"></i>

Bayar


</a>


@endif



</td>



</tr>




@empty


<tr>

<td colspan="5"
class="text-center text-muted">


Belum ada pesanan.


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