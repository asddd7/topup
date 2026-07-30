<div class="modal fade"
id="createPaymentModal">


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content rounded-4 shadow">


<div class="modal-header bg-primary text-white">


<h5 class="modal-title">

<i class="fa-solid fa-credit-card me-2"></i>

Tambah Payment

</h5>


<button class="btn-close btn-close-white"
data-bs-dismiss="modal"></button>


</div>



<form action="{{route('admin.payment.store')}}"
method="POST"
enctype="multipart/form-data">


@csrf


<div class="modal-body">


<div class="mb-3">

<label>Nama Payment</label>

<input
name="payment_name"
class="form-control"
placeholder="BCA / Dana / QRIS"
required>

</div>



<div class="mb-3">

<label>Nomor Payment</label>

<input
name="payment_number"
class="form-control"
placeholder="123456789"
required>

</div>



<div class="mb-3">

<label>Nama Pemilik</label>

<input
name="account_name"
class="form-control"
required>

</div>



<div class="mb-3">

<label>Tipe Payment</label>

<select
name="payment_type"
class="form-select">


<option value="Bank">

Bank

</option>


<option value="E-Wallet">

E-Wallet

</option>


<option value="QRIS">

QRIS

</option>


</select>

</div>



<div class="mb-3">

<label>Logo / QR</label>

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
checked
class="form-check-input">


<label>

Aktif

</label>


</div>



</div>


<div class="modal-footer">


<button
class="btn btn-primary">

Simpan

</button>


</div>


</form>


</div>


</div>


</div>