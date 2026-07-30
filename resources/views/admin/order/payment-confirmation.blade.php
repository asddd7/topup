@extends('admin.layouts.app')


@section('content')


<div class="container-fluid py-4">


<h3 class="fw-bold mb-4">

<i class="fa-solid fa-money-check-dollar me-2"></i>

Konfirmasi Pembayaran

</h3>



<div class="card shadow border-0 rounded-4">


<div class="card-body">



<table class="table table-hover align-middle">


<thead class="table-dark">


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
Bukti
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

{{$order->user->name ?? 'Guest'}}

</td>



<td>

{{$order->game->game_name}}

</td>



<td>

{{$order->payment->payment_name}}

<br>

<small>

{{$order->payment->payment_number}}

</small>

</td>




<td>

Rp {{number_format($order->total_price)}}

</td>




<td>


@if($order->payment_proof)


<button
class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#proof{{$order->id}}">


<i class="fa-solid fa-image"></i>

Lihat


</button>


@else


<span class="text-muted">

Belum upload

</span>


@endif



</td>




<td>


<span class="badge bg-warning">


{{$order->status}}


</span>


</td>




<td>



@if($order->status == 'Paid')



<button
class="btn btn-success btn-sm"
onclick="confirmPayment(
{{$order->id}}
)">


<i class="fa-solid fa-check"></i>

Approve


</button>




<button
class="btn btn-danger btn-sm"
onclick="rejectPayment(
{{$order->id}}
)">


<i class="fa-solid fa-xmark"></i>

Reject


</button>


@endif



</td>



</tr>





@if($order->payment_proof)


<!-- MODAL BUKTI -->

<div class="modal fade"
id="proof{{$order->id}}">


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content rounded-4">



<div class="modal-header">


<h5>

Bukti Pembayaran

</h5>


<button
class="btn-close"
data-bs-dismiss="modal">

</button>


</div>




<div class="modal-body text-center">



<img

src="{{asset(
'storage/'.$order->payment_proof
)}}"

class="img-fluid rounded shadow">



</div>



</div>


</div>


</div>



@endif



@empty


<tr>

<td colspan="8"
class="text-center">


Tidak ada pembayaran menunggu


</td>

</tr>



@endforelse




</tbody>



</table>




</div>

</div>


</div>



@endsection
@push('scripts')

<script>


function confirmPayment(id)
{


Swal.fire({

title:'Konfirmasi pembayaran?',

text:'Order akan diproses',

icon:'question',

showCancelButton:true,

confirmButtonText:'Ya, Approve'


})
.then((result)=>{


if(result.isConfirmed)
{


fetch(
'/admin/order/'+id+'/confirm',
{

method:'POST',

headers:{


'X-CSRF-TOKEN':
'{{csrf_token()}}',

'Accept':
'application/json',

'Content-Type':
'application/json'


}


}

)

.then(res=>res.json())

.then(data=>{


Swal.fire(

'Berhasil',

data.message,

'success'

)
.then(()=>{

location.reload();

});


})
.catch(err=>{


Swal.fire(
'Error',
'Terjadi kesalahan server',
'error'
);


});


}


});


}





function rejectPayment(id)
{


Swal.fire({

title:'Tolak pembayaran?',

text:'User harus melakukan pembayaran ulang',

icon:'warning',

showCancelButton:true,

confirmButtonColor:'#d33',

confirmButtonText:'Tolak'


})
.then((result)=>{


if(result.isConfirmed)
{


fetch(

'/admin/order/'+id+'/reject',

{


method:'POST',


headers:{


'X-CSRF-TOKEN':
'{{csrf_token()}}',

'Accept':
'application/json',

'Content-Type':
'application/json'


}


}

)

.then(res=>res.json())

.then(data=>{


Swal.fire(

'Ditolak',

data.message,

'error'

)

.then(()=>{

location.reload();

});


});


}


});


}



</script>

@endpush