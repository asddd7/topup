<div class="modal fade"
id="editPaymentModal{{$payment->id}}">


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content rounded-4 shadow">


<div class="modal-header bg-warning">


<h5 class="modal-title">

<i class="fa-solid fa-credit-card me-2"></i>

Edit Payment

</h5>


<button 
class="btn-close"
data-bs-dismiss="modal">

</button>


</div>



<form action="{{route('admin.payment.update',$payment->id)}}"
method="POST"
enctype="multipart/form-data">


@csrf

@method('PUT')



<div class="modal-body">


<div class="mb-3">

<label>
Nama Payment
</label>


<input
name="payment_name"
class="form-control"
value="{{$payment->payment_name}}"
required>

</div>




<div class="mb-3">

<label>
Nomor Payment
</label>


<input
name="payment_number"
class="form-control"
value="{{$payment->payment_number}}"
required>

</div>




<div class="mb-3">

<label>
Nama Pemilik
</label>


<input
name="account_name"
class="form-control"
value="{{$payment->account_name}}"
required>

</div>




<div class="mb-3">

<label>
Tipe Payment
</label>


<select
name="payment_type"
class="form-select">


<option value="Bank"
{{$payment->payment_type=='Bank'?'selected':''}}>
Bank
</option>


<option value="E-Wallet"
{{$payment->payment_type=='E-Wallet'?'selected':''}}>
E-Wallet
</option>


<option value="QRIS"
{{$payment->payment_type=='QRIS'?'selected':''}}>
QRIS
</option>


</select>

</div>




<div class="mb-3">

<label>
Logo / QR
</label>


@if($payment->image)

<br>

<img src="{{asset('storage/'.$payment->image)}}"
width="100"
class="rounded mb-2">

@endif



<input
type="file"
name="image"
class="form-control">


</div>




<div class="form-check">


<input
type="checkbox"
name="is_active"
value="1"

class="form-check-input"

{{$payment->is_active?'checked':''}}>


<label>
Aktif
</label>


</div>



</div>




<div class="modal-footer">


<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Batal

</button>


<button
class="btn btn-warning">

Update

</button>


</div>



</form>


</div>


</div>


</div>