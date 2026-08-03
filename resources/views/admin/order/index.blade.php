@extends('admin.layouts.app')


@section('content')


<div class="container-fluid">


<h3 class="fw-bold mb-4">

<i class="fa-solid fa-cart-shopping"></i>

Manajemen Order

</h3>



<div class="card shadow">


<div class="card-body">



<form>

<select name="status"
class="form-select w-auto"
onchange="this.form.submit()">


<option value="">
Semua Status
</option>


@foreach($statusList as $itemStatus)

<option value="{{ $itemStatus }}"
@if($status==$itemStatus)
selected
@endif
>

{{ $itemStatus }}

</option>

@endforeach


</select>


</form>




<table class="table table-hover mt-3">


<thead>

<tr>

<th>
Invoice
</th>

<th>
User
</th>

<th>
Game
</th>

<th>
Payment
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


@foreach($orders as $order)


<tr>


<td>

{{$order->invoice_number}}

</td>



<td>

{{$order->user->name ?? 'Guest'}}

</td>



<td>

{{$order->game->game_name}}

</td>



<td>

{{$order->payment->payment_name}}

</td>



<td>

Rp {{number_format($order->total_price)}}

</td>




<td>


@if($order->status=='Waiting Payment')

<span class="badge bg-secondary">
Waiting Payment
</span>

@elseif($order->status=='Completed')

<span class="badge bg-success">
Completed
</span>

@elseif($order->status=='Paid')

<span class="badge bg-primary">
Paid
</span>


@elseif($order->status=='Processing')

<span class="badge bg-warning">
Processing
</span>


@elseif($order->status=='Success')

<span class="badge bg-success">
Success
</span>


@elseif($order->status=='Cancelled')

<span class="badge bg-danger">
Canceled
</span>


@endif

</td>




<td>


<a href="{{route('admin.order.show',$order)}}"

class="btn btn-sm btn-primary">

<i class="fa-solid fa-eye"></i>

</a>



@if($order->status=='Paid')


<span class="badge bg-danger ms-1">

Menunggu Konfirmasi

</span>


@endif



</td>


</tr>



@endforeach


</tbody>


</table>


</div>


</div>



</div>


@endsection