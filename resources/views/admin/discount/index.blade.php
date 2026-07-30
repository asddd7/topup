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

<th>Periode</th>

<th>Status</th>

<th width="150">
Action
</th>


</tr>

</thead>



<tbody>


@foreach($discounts as $discount)


<tr>


<td>
{{$loop->iteration}}
</td>



<td>

<span class="badge bg-dark">

{{$discount->code}}

</span>

</td>



<td>

{{$discount->discount_name}}

</td>



<td>


@if($discount->game)

Game :
{{$discount->game->game_name}}


@elseif($discount->item)

Item :
{{$discount->item->item_name}}


@else

<span class="text-success">

Semua Produk

</span>


@endif


</td>




<td>


@if($discount->discount_type=='percent')


<span class="badge bg-primary">

Persen

</span>


@else


<span class="badge bg-warning">

Nominal

</span>


@endif



</td>



<td>


@if($discount->discount_type=='percent')


{{$discount->amount}}%


@else


Rp {{number_format($discount->amount)}}


@endif



</td>



<td>


{{ $discount->start_date ?? '-' }}

s/d

{{ $discount->end_date ?? '-' }}


</td>




<td>


@if($discount->is_active)

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
data-bs-target="#editDiscountModal{{$discount->id}}">


<i class="fa-solid fa-pen"></i>


</button>



<form
action="{{route('admin.discount.destroy',$discount->id)}}"
method="POST"
class="d-inline">


@csrf

@method('DELETE')


<button
class="btn btn-danger btn-sm"
onclick="return confirm('Hapus voucher ini?')">


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