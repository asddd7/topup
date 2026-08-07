
@extends('layouts.app')


@section('title')

{{ $game->game_name }}

@endsection



@section('content')


<div class="container py-4">



<div class="card shadow border-0 rounded-4">



<div class="card-body">



<div class="row align-items-center">



<div class="col-md-3 text-center">


@if($game->game_logo)


<img src="{{asset('storage/'.$game->game_logo)}}"
class="rounded shadow"
width="160">


@else


<i class="fa-solid fa-gamepad fa-5x text-primary"></i>


@endif


</div>




<div class="col-md-9">


<h2 class="fw-bold">

{{$game->game_name}}

</h2>


<p class="text-muted">

{{$game->publisher}}

</p>



<a href="#topup"
class="btn btn-primary">


<i class="fa-solid fa-bolt"></i>

Top Up Sekarang


</a>


</div>


</div>



</div>


</div>





<div class="card shadow border-0 rounded-4 mt-4"
id="topup">


<div class="card-header bg-primary text-white">


<h5 class="mb-0">

<i class="fa-solid fa-coins me-2"></i>

Pilih Nominal Top Up

</h5>


</div>




<div class="card-body">



<div class="row">



@foreach($items as $item)


<div class="col-md-4 mb-3">


<div class="card h-100 shadow-sm">



@if($item->image)


<img src="{{asset('storage/'.$item->image)}}"
class="card-img-top item-image">


@endif




<div class="card-body">


<h6 class="fw-bold">

{{$item->item_name}}

</h6>


<p class="text-muted">

{{$item->qty}} Item

</p>


<h5 class="text-primary fw-bold">

Rp {{number_format($item->price)}}

</h5>



<button 
type="button"
class="btn btn-primary w-100"
onclick="selectItem(
'{{ $item->id }}',
'{{ $item->item_name }}',
'{{ $item->price }}'
)">

Pilih

</button>


</div>



</div>


</div>


@endforeach



</div>


</div>


</div>


</div>

<div class="card shadow border-0 rounded-4 mt-4"
id="checkout">


<div class="card-header bg-success text-white">

<h5 class="mb-0">

<i class="fa-solid fa-cart-shopping me-2"></i>

Detail Pembelian

</h5>

</div>



<div class="card-body">


<form action="{{route('order.store')}}"
method="POST">


@csrf



<input type="hidden"
name="game_id"
value="{{$game->id}}">

<input type="hidden"
name="item_id"
id="item_id">

@foreach($game->player_fields ?? [] as $field)

<div class="mb-3">

<label class="form-label">
    {{ $field['label'] }}
</label>

@if(($field['type'] ?? '') == 'select')

<select
    name="{{ $field['name'] }}"
    class="form-select"
    @if(!empty($field['required'])) required @endif>

    <option value="">Pilih {{ $field['label'] }}</option>

    @foreach(explode(',', $field['options']) as $option)

        <option value="{{ trim($option) }}">
            {{ trim($option) }}
        </option>

    @endforeach

</select>

@else

<input
    type="{{ $field['type'] ?? 'text' }}"
    name="{{ $field['name'] }}"
    class="form-control"
    placeholder="{{ $field['placeholder'] ?? '' }}">

@endif

</div>

@endforeach




<div class="alert alert-primary">

<div class="mb-3">

<label class="form-label">

Kode Voucher

</label>


<div class="input-group">

<input
type="text"
id="code"
class="form-control"
placeholder="Masukkan voucher">


<button
type="button"
class="btn btn-outline-primary"
onclick="checkVoucher()">

Gunakan

</button>

</div>


<small id="voucher_message"
class="text-muted">

Masukkan kode voucher

</small>


</div>

<input
type="hidden"
name="voucher"
id="voucher">

<input type="hidden"
name="discount_id"
id="discount_id"
value="">

<input type="hidden"
name="subtotal"
id="subtotal">

<input type="hidden"
id="original_subtotal">


<input type="hidden"
name="discount"
id="discount"
value="0">


<input type="hidden"
name="total_price"
id="total_price">
<h6>
Item :
<span id="selected_item">
Belum dipilih
</span>
</h6>


<p class="mb-1">

Harga :

Rp <span id="original_price">
0
</span>

</p>


<p class="mb-1 text-danger">

Diskon :

Rp <span id="discount_price">
0
</span>

</p>


<h5 class="fw-bold">

Total :

Rp <span id="final_price">
0
</span>

</h5>


</div>

<div class="card shadow-sm mt-4">

    <div class="card-header bg-dark text-white">
        <i class="fa-solid fa-wallet me-2"></i>
        Pilih Metode Pembayaran
    </div>

    <div class="card-body">

        @foreach($payments as $payment)

        <label class="payment-card border rounded-3 p-3 mb-3 d-flex align-items-center">

        <input
        type="radio"
        class="payment-radio"
        name="payment_id"
        value="{{ $payment->id }}"
        required>

            @if($payment->image)

                <img
                    src="{{ asset('storage/'.$payment->image) }}"
                    width="60"
                    class="rounded me-3">

            @endif

            <div class="flex-grow-1">

                <h6 class="mb-1 fw-bold">
                    {{ $payment->payment_name }}
                </h6>

                <small class="text-muted d-block">
                    {{ $payment->payment_type }}
                </small>

                <small class="fw-semibold">
                    {{ $payment->payment_number }}
                </small>

                <br>

                <small class="text-secondary">
                    a.n {{ $payment->account_name }}
                </small>

            </div>

        </label>

        @endforeach

    </div>

</div>

<button
class="btn btn-success w-100">

<i class="fa-solid fa-bolt"></i>

Beli Sekarang

</button>



</form>


</div>

</div>


</div>
<script>
let selectedPrice = 0;


function selectItem(id,name,price)
{

document.getElementById('discount_id').value="";
document.getElementById('discount').value=0;
document.getElementById('discount_price').innerHTML="0";
document.getElementById('voucher').value="";
document.getElementById('voucher_message').innerHTML=
"Masukkan voucher";


document.getElementById('item_id').value=id;


document.getElementById('selected_item')
.innerHTML=name;


selectedPrice=parseInt(price);


document.getElementById('original_price')
.innerHTML=formatRupiah(selectedPrice);


document.getElementById('final_price')
.innerHTML=formatRupiah(selectedPrice);


document.getElementById('subtotal')
.value=selectedPrice;


document.getElementById('original_subtotal')
.value=selectedPrice;


document.getElementById('total_price')
.value=selectedPrice;


window.location.href="#checkout";

}




function checkVoucher()
{

let code=document.getElementById('code').value;


if(code=="")
{
return;
}


fetch(
"{{ route('voucher.check') }}",
{
method:"POST",

headers:{

"Content-Type":"application/json",

"X-CSRF-TOKEN":
document
.querySelector('meta[name="csrf-token"]')
.content

},

body:JSON.stringify({

code:code,

price:selectedPrice,

game_id:"{{$game->id}}",

item_id:
document.getElementById('item_id').value

})
}

)

.then(res=>res.json())

.then(data=>{


if(data.status)
{


document.getElementById('voucher').value=code;

document.getElementById('discount_id')
.value=data.discount_id;



document.getElementById('discount')
.value=data.discount;



document.getElementById('discount_price')
.innerHTML=
formatRupiah(data.discount);



document.getElementById('final_price')
.innerHTML=
formatRupiah(data.total);



document.getElementById('total_price')
.value=data.total;


document.getElementById('voucher_message')
.innerHTML=
"Voucher berhasil digunakan";


}
else{


document.getElementById('voucher_message')
.innerHTML=
"Voucher tidak valid";


}


});

}



function formatRupiah(number)
{

return new Intl.NumberFormat('id-ID')
.format(number);

}

document
.querySelectorAll('.payment-radio')
.forEach(function(payment){


payment.addEventListener(
'change',
function(){


fetch(
"{{ route('payment.promo') }}",
{

method:"POST",

headers:{

"Content-Type":"application/json",

"X-CSRF-TOKEN":
document
.querySelector('meta[name="csrf-token"]')
.content

},

body:JSON.stringify({

payment_id:this.value,

subtotal:
parseInt(
document.getElementById('original_subtotal').value
),

game_id:"{{$game->id}}",

item_id:
document.getElementById('item_id').value

})


}

)


.then(res=>res.json())

.then(data=>{


if(data.status){


document
.getElementById('discount_id')
.value=data.discount_id;



document
.getElementById('discount')
.value=data.discount;



document
.getElementById('discount_price')
.innerHTML=
formatRupiah(data.discount);



document
.getElementById('final_price')
.innerHTML=
formatRupiah(data.total);



document
.getElementById('total_price')
.value=data.total;



document
.getElementById('voucher_message')
.innerHTML=
data.message;


}


});


});


});

const paymentArea =
document.getElementById('paymentArea');


function togglePromoTarget() {


if(trigger.value === 'voucher'){

    voucherArea.style.display='';


}else{

    voucherArea.style.display='none';

}



if(trigger.value === 'payment_method'){

    paymentArea.style.display='';

}else{

    paymentArea.style.display='none';

}


}


trigger.addEventListener(
'change',
togglePromoTarget
);


togglePromoTarget();

document
.querySelectorAll('.player-input')
.forEach(function(input){


let type = input.dataset.type;



if(type === 'number'){


input.addEventListener('input',function(){


this.value =
this.value.replace(/[^0-9]/g,'');


});


}



if(type === 'email'){


input.addEventListener('input',function(){


this.value =
this.value.replace(/\s/g,'');


});


}


});
</script>
@endsection