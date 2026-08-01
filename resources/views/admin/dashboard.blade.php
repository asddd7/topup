@extends('admin.layouts.app')

@section('title','Dashboard Admin')


@section('content')


<div class="container-fluid">


<h3 class="fw-bold mb-4">

Dashboard Admin

</h3>




<div class="row g-3 mb-4">


<div class="col-md-3">

<div class="card shadow-sm border-0">

<div class="card-body">


<h6>
Total Pesanan
</h6>


<h2 class="fw-bold">

{{$totalOrder}}

</h2>


</div>

</div>

</div>





<div class="col-md-3">

<div class="card shadow-sm border-0">

<div class="card-body">


<h6>
Menunggu Pembayaran
</h6>


<h2 class="fw-bold text-warning">

{{$waitingPayment}}

</h2>


</div>

</div>

</div>






<div class="col-md-3">

<div class="card shadow-sm border-0">

<div class="card-body">


<h6>
Jumlah Game
</h6>


<h2 class="fw-bold">

{{$totalGame}}

</h2>


</div>

</div>

</div>





<div class="col-md-3">

<div class="card shadow-sm border-0">

<div class="card-body">


<h6>
Pendapatan
</h6>


<h4 class="fw-bold text-success">

Rp {{number_format($income)}}

</h4>


</div>

</div>

</div>



</div>





<h5 class="fw-bold mb-3">

Shortcut

</h5>



<div class="row g-3 mb-4">

<div class="col-md-2">


<a href="{{route('admin.payment-confirmation.index')}}"
class="text-decoration-none">


<div class="card shadow-sm text-center p-3">


<i class="fa-solid fa-money-check-dollar fa-2x text-success"></i>


<h6 class="mt-2">

Konfirmasi

</h6>
    @php
        $waiting = \App\Models\Order::where('status','Paid')->count();
    @endphp

    @if($waiting)
        <span class="badge bg-danger float-end">
            {{ $waiting }}
        </span>
    @endif

</div>


</a>


</div>





<div class="col-md-2">


<a href="{{route('admin.game.index')}}"
class="text-decoration-none">


<div class="card shadow-sm text-center p-3">


<i class="fa-solid fa-gamepad fa-2x text-danger"></i>


<h6 class="mt-2">

Game

</h6>


</div>


</a>


</div>





<div class="col-md-2">


<a href="{{route('admin.discount.index')}}"
class="text-decoration-none">


<div class="card shadow-sm text-center p-3">


<i class="fa-solid fa-ticket fa-2x text-warning"></i>


<h6 class="mt-2">

Voucher

</h6>


</div>


</a>


</div>

<div class="col-md-2">

<a href="{{ route('admin.stock.index') }}"
class="text-decoration-none">

<div class="card shadow-sm text-center p-3">

<i class="fa-solid fa-boxes-stacked fa-2x text-primary"></i>

<h6 class="mt-2">
    Tambah Stock
</h6>

</div>

</a>

</div>



<div class="col-md-2">


<a href="{{route('admin.setting.index')}}"
class="text-decoration-none">


<div class="card shadow-sm text-center p-3">


<i class="fa-solid fa-gears fa-2x text-secondary"></i>


<h6 class="mt-2">

Setting

</h6>


</div>


</a>


</div>


</div>





<div class="card shadow-sm">


<div class="card-header">

Order Terbaru

</div>



<div class="card-body">


<table class="table">


<tr>

<th>Invoice</th>

<th>Game</th>

<th>Total</th>

<th>Status</th>

</tr>



@foreach($recentOrders as $order)


<tr>

<td>

{{$order->invoice_number}}

</td>


<td>

{{$order->game->game_name}}

</td>


<td>

Rp {{number_format($order->total_price)}}

</td>


<td>

<span class="badge bg-warning">

{{$order->status}}

</span>

</td>


</tr>


@endforeach


</table>


</div>


</div>


</div>


@endsection