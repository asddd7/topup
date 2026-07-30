<div class="modal fade"
     id="editDiscountModal{{$discount->id}}"
     tabindex="-1">


<div class="modal-dialog modal-lg modal-dialog-centered">


<div class="modal-content rounded-4 shadow border-0">



<div class="modal-header bg-warning">


<h5 class="modal-title">

<i class="fa-solid fa-ticket me-2"></i>

Edit Promo

</h5>


<button
class="btn-close"
data-bs-dismiss="modal">

</button>


</div>




<form
action="{{route('admin.discount.update',$discount->id)}}"
method="POST">


@csrf

@method('PUT')



<div class="modal-body">



{{-- TRIGGER --}}

<h6 class="fw-bold text-primary">

Informasi Promo

</h6>



<div class="mb-3">

<label class="form-label">

Trigger Promo

</label>


<select
name="trigger_type"
id="trigger_type{{$discount->id}}"
class="form-select">


<option value="voucher"
{{$discount->trigger_type=='voucher'?'selected':''}}>

Voucher

</option>


<option value="automatic"
{{$discount->trigger_type=='automatic'?'selected':''}}>

Otomatis

</option>


<option value="new_user"
{{$discount->trigger_type=='new_user'?'selected':''}}>

User Baru

</option>


<option value="first_order"
{{$discount->trigger_type=='first_order'?'selected':''}}>

Order Pertama

</option>


<option value="birthday"
{{$discount->trigger_type=='birthday'?'selected':''}}>

Birthday

</option>


<option value="flash_sale"
{{$discount->trigger_type=='flash_sale'?'selected':''}}>

Flash Sale

</option>


<option value="event"
{{$discount->trigger_type=='event'?'selected':''}}>

Event

</option>


<option value="payment_method"
{{$discount->trigger_type=='payment_method'?'selected':''}}>

Payment Method

</option>


</select>

</div>





<div class="mb-3"
id="voucherCode{{$discount->id}}">


<label>

Kode Voucher

</label>


<input
name="code"
class="form-control"
value="{{$discount->code}}">


</div>




<div class="mb-3">


<label>

Nama Promo

</label>


<input
name="discount_name"
class="form-control"
value="{{$discount->discount_name}}">


</div>



<hr>



{{-- TARGET --}}

<h6 class="fw-bold text-primary">

Target Promo

</h6>



<div class="mb-3">

<label>

Game

</label>


<select
name="game_id"
class="form-select">


<option value="">

Semua Game

</option>


@foreach($games as $game)


<option value="{{$game->id}}"
{{$discount->game_id==$game->id?'selected':''}}>


{{$game->game_name}}


</option>


@endforeach


</select>


</div>




<div class="mb-3">


<label>

Item

</label>


<select
name="item_id"
class="form-select">


<option value="">

Semua Item

</option>


@foreach($items as $item)


<option value="{{$item->id}}"
{{$discount->item_id==$item->id?'selected':''}}>


{{$item->item_name}}


</option>


@endforeach


</select>


</div>




<hr>



{{-- DISKON --}}

<h6 class="fw-bold text-primary">

Nilai Diskon

</h6>



<div class="row">


<div class="col-md-6">


<label>

Tipe Diskon

</label>


<select
name="discount_type"
class="form-select">


<option value="percent"
{{$discount->discount_type=='percent'?'selected':''}}>

Persentase (%)

</option>


<option value="fixed"
{{$discount->discount_type=='fixed'?'selected':''}}>

Nominal

</option>


</select>


</div>



<div class="col-md-6">


<label>

Jumlah

</label>


<input
type="number"
name="amount"
class="form-control"
value="{{$discount->amount}}">


</div>


</div>





<div class="row mt-3">


<div class="col-md-4">


<label>

Minimum Pembelian

</label>


<input
type="number"
name="minimum_purchase"
class="form-control"
value="{{$discount->minimum_purchase}}">


</div>




<div class="col-md-4">


<label>

Kuota Voucher

</label>


<input
type="number"
name="usage_limit"
class="form-control"
value="{{$discount->usage_limit}}">


</div>




<div class="col-md-4">


<label>

Maks/User

</label>


<input
type="number"
name="usage_per_user"
class="form-control"
value="{{$discount->usage_per_user}}">


</div>



</div>



<hr>



{{-- PERIODE --}}


<h6 class="fw-bold text-primary">

Periode Promo

</h6>



<div class="row">


<div class="col">


<label>

Mulai

</label>


<input
type="date"
name="start_date"
class="form-control"
value="{{$discount->start_date}}">


</div>



<div class="col">


<label>

Berakhir

</label>


<input
type="date"
name="end_date"
class="form-control"
value="{{$discount->end_date}}">


</div>


</div>




<div class="form-check form-switch mt-4">


<input
type="checkbox"
name="is_active"
value="1"
class="form-check-input"
{{$discount->is_active?'checked':''}}>


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


<i class="fa-solid fa-save"></i>

Update

</button>


</div>



</form>



</div>

</div>

</div>




<script>

document.addEventListener('DOMContentLoaded',function(){


let trigger =
document.getElementById(
'trigger_type{{$discount->id}}'
);


let voucher =
document.getElementById(
'voucherCode{{$discount->id}}'
);



function checkVoucher(){


if(trigger.value === 'voucher'){

voucher.style.display='';

}else{

voucher.style.display='none';

}


}



trigger.addEventListener(
'change',
checkVoucher
);


checkVoucher();


});


</script>