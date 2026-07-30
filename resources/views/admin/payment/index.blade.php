@extends('admin.layouts.app')


@section('title','Metode Pembayaran')


@section('content')


<div class="container-fluid">


<div class="card shadow-sm">


<div class="card-header d-flex justify-content-between">


<h5 class="fw-bold">

<i class="fa-solid fa-credit-card me-2"></i>

Metode Pembayaran

</h5>



<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#createPaymentModal">


<i class="fa-solid fa-plus"></i>

Tambah Payment


</button>


</div>




<div class="card-body">


@if(session('success'))

<div class="alert alert-success">

{{session('success')}}

</div>

@endif



<div class="table-responsive">


<table class="table table-hover">


<thead>

<tr>

<th>No</th>

<th>Image</th>

<th>Nama</th>

<th>Nomor</th>

<th>Pemilik</th>

<th>Tipe</th>

<th>Status</th>

<th>Aksi</th>

</tr>

</thead>



<tbody>


@foreach($payments as $payment)


<tr>


<td>
{{$loop->iteration}}
</td>


<td>


@if($payment->image)

<img src="{{asset('storage/'.$payment->image)}}"
width="60"
class="rounded">


@else

<i class="fa-solid fa-image text-muted"></i>

@endif


</td>



<td>

{{$payment->payment_name}}

</td>


<td>

{{$payment->payment_number}}

</td>



<td>

{{$payment->account_name}}

</td>



<td>

{{$payment->payment_type}}

</td>



<td>

@if($payment->is_active)

<span class="badge bg-success">
Aktif
</span>

@else

<span class="badge bg-danger">
Nonaktif
</span>

@endif

</td>



<td>


<button
class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editPaymentModal{{$payment->id}}">

<i class="fa-solid fa-pen"></i>


</button>



<form
action="{{route('admin.payment.destroy',$payment->id)}}"
method="POST"
class="d-inline">


@csrf
@method('DELETE')


<button
class="btn btn-danger btn-sm"
onclick="return confirm('Hapus payment?')">


<i class="fa-solid fa-trash"></i>


</button>


</form>


</td>


</tr>


@endforeach


</tbody>


</table>


</div>


</div>


</div>


</div>


@include('admin.payment.create')


@foreach($payments as $payment)

@include('admin.payment.edit')

@endforeach



@endsection