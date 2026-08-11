
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
    name="item_id"
    id="item_id">

<input
    type="hidden"
    name="subtotal"
    id="original_subtotal"
    value="0">

<input
    type="hidden"
    name="discount_total"
    id="discount_total"
    value="0">

<input
    type="hidden"
    name="total_price"
    id="total_price"
    value="0">

<input
    type="hidden"
    name="discount_ids"
    id="discount_ids"
    value="">


<input
type="hidden"
name="voucher"
id="voucher">
<h6>
Item :
<span id="selected_item">
Belum dipilih
</span>
</h6>


<div class="mt-3">

    <div class="d-flex justify-content-between">

        <span>Harga</span>

        <strong>
            Rp <span id="original_price">0</span>
        </strong>

    </div>


    <div class="d-flex justify-content-between">

        <span>Diskon</span>

        <strong class="text-danger">

            - Rp
            <span id="discount_price">
                0
            </span>

        </strong>

    </div>


    <hr>


    <div class="d-flex justify-content-between">

        <strong>Total</strong>

        <strong class="text-success fs-5">

            Rp
            <span id="final_price">
                0
            </span>

        </strong>

    </div>

</div>

<div class="card mt-3 shadow-sm">

    <div class="card-body">

        <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-tags me-2"></i>
            Promo
        </h6>


        <div id="promoList">

            <div class="text-muted small">
                Belum ada promo
            </div>

        </div>

    </div>

</div>

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
let selectedPaymentId = null;
let selectedVoucher = null;
let appliedPromos = [];


/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/

function formatRupiah(number)
{
    return new Intl.NumberFormat('id-ID').format(
        Math.round(parseFloat(number) || 0)
    );
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
| Mencegah nama promo/kode promo memasukkan HTML ke halaman.
|--------------------------------------------------------------------------
*/

function escapeHtml(value)
{
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


/*
|--------------------------------------------------------------------------
| PILIH ITEM
|--------------------------------------------------------------------------
*/

function selectItem(id, name, price)
{
    selectedPrice = parseFloat(price) || 0;

    /*
    |--------------------------------------------------------------------------
    | Reset promo
    |--------------------------------------------------------------------------
    */

    selectedVoucher = null;
    appliedPromos = [];


    /*
    |--------------------------------------------------------------------------
    | Reset voucher
    |--------------------------------------------------------------------------
    */

    const codeInput =
        document.getElementById('code');

    const voucherInput =
        document.getElementById('voucher');

    if (codeInput) {
        codeInput.value = '';
    }

    if (voucherInput) {
        voucherInput.value = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Set item
    |--------------------------------------------------------------------------
    */

    const itemInput =
        document.getElementById('item_id');

    if (itemInput) {
        itemInput.value = id;
    }


    /*
    |--------------------------------------------------------------------------
    | Tampilkan item
    |--------------------------------------------------------------------------
    */

    const selectedItem =
        document.getElementById('selected_item');

    if (selectedItem) {
        selectedItem.innerText = name;
    }


    /*
    |--------------------------------------------------------------------------
    | Harga awal
    |--------------------------------------------------------------------------
    */

    const originalPrice =
        document.getElementById('original_price');

    if (originalPrice) {
        originalPrice.innerText =
            formatRupiah(selectedPrice);
    }


    /*
    |--------------------------------------------------------------------------
    | Reset discount
    |--------------------------------------------------------------------------
    */

    const discountPrice =
        document.getElementById('discount_price');

    if (discountPrice) {
        discountPrice.innerText = '0';
    }


    /*
    |--------------------------------------------------------------------------
    | Reset total
    |--------------------------------------------------------------------------
    */

    const finalPrice =
        document.getElementById('final_price');

    if (finalPrice) {
        finalPrice.innerText =
            formatRupiah(selectedPrice);
    }


    /*
    |--------------------------------------------------------------------------
    | Hidden input
    |--------------------------------------------------------------------------
    */

    const originalSubtotal =
        document.getElementById('original_subtotal');

    if (originalSubtotal) {
        originalSubtotal.value =
            selectedPrice;
    }


    const totalPrice =
        document.getElementById('total_price');

    if (totalPrice) {
        totalPrice.value =
            selectedPrice;
    }


    const discountTotal =
        document.getElementById('discount_total');

    if (discountTotal) {
        discountTotal.value = 0;
    }


    const discountIds =
        document.getElementById('discount_ids');

    if (discountIds) {
        discountIds.value = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Reset daftar promo
    |--------------------------------------------------------------------------
    */

    const promoList =
        document.getElementById('promoList');

    if (promoList) {

        promoList.innerHTML = `
            <div class="text-muted small">
                ${
                    selectedPaymentId
                        ? 'Menghitung promo...'
                        : 'Pilih metode pembayaran untuk melihat promo.'
                }
            </div>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | Scroll ke checkout
    |--------------------------------------------------------------------------
    */

    window.location.href = '#checkout';


    /*
    |--------------------------------------------------------------------------
    | Jika payment sudah dipilih,
    | langsung hitung promo
    |--------------------------------------------------------------------------
    */

    if (selectedPaymentId) {
        calculatePromos();
    }
}


/*
|--------------------------------------------------------------------------
| PAYMENT METHOD
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('.payment-radio')
    .forEach(function(payment) {

        payment.addEventListener(
            'change',
            function() {

                selectedPaymentId =
                    parseInt(this.value) || null;

                calculatePromos();

            }
        );

    });


/*
|--------------------------------------------------------------------------
| VOUCHER
|--------------------------------------------------------------------------
*/

function checkVoucher()
{
    const codeInput =
        document.getElementById('code');

    if (!codeInput) {
        return;
    }


    const code =
        codeInput.value.trim();


    /*
    |--------------------------------------------------------------------------
    | Jika kosong → hapus voucher
    |--------------------------------------------------------------------------
    */

    if (code === '') {

        selectedVoucher = null;

    } else {

        selectedVoucher = code;

    }


    /*
    |--------------------------------------------------------------------------
    | Simpan ke hidden input
    |--------------------------------------------------------------------------
    */

    const voucherInput =
        document.getElementById('voucher');

    if (voucherInput) {

        voucherInput.value =
            selectedVoucher ?? '';

    }


    /*
    |--------------------------------------------------------------------------
    | Hitung ulang semua promo
    |--------------------------------------------------------------------------
    */

    calculatePromos();
}


/*
|--------------------------------------------------------------------------
| HAPUS VOUCHER
|--------------------------------------------------------------------------
*/

function removeVoucher()
{
    selectedVoucher = null;


    const codeInput =
        document.getElementById('code');

    if (codeInput) {
        codeInput.value = '';
    }


    const voucherInput =
        document.getElementById('voucher');

    if (voucherInput) {
        voucherInput.value = '';
    }


    calculatePromos();
}


/*
|--------------------------------------------------------------------------
| CALCULATE PROMOS
|--------------------------------------------------------------------------
*/

function calculatePromos()
{
    /*
    |--------------------------------------------------------------------------
    | Belum pilih item
    |--------------------------------------------------------------------------
    */

    if (!selectedPrice) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil item ID
    |--------------------------------------------------------------------------
    */

    const itemInput =
        document.getElementById('item_id');

    if (!itemInput) {
        return;
    }


    const itemId =
        itemInput.value;


    if (!itemId) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Tampilkan loading
    |--------------------------------------------------------------------------
    */

    const promoList =
        document.getElementById('promoList');

    if (promoList) {

        promoList.innerHTML = `
            <div class="text-muted small">
                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                Menghitung promo...
            </div>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Request ke server
    |--------------------------------------------------------------------------
    */

    fetch(
        "{{ route('voucher.calculate') }}",
        {
            method: "POST",

            headers: {

                "Content-Type":
                    "application/json",

                "Accept":
                    "application/json",

                "X-CSRF-TOKEN":
                    document
                        .querySelector(
                            'meta[name="csrf-token"]'
                        )
                        .content

            },

            body: JSON.stringify({

                subtotal:
                    selectedPrice,

                game_id:
                    "{{ $game->id }}",

                item_id:
                    itemId,

                payment_id:
                    selectedPaymentId,

                voucher_code:
                    selectedVoucher

            })

        }
    )
    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                'Gagal menghitung promo'
            );

        }

        return response.json();

    })
    .then(function(data) {

        /*
        |--------------------------------------------------------------------------
        | Simpan promo yang diterapkan
        |--------------------------------------------------------------------------
        */

        appliedPromos =
            data.discounts || [];


        /*
        |--------------------------------------------------------------------------
        | Render hasil
        |--------------------------------------------------------------------------
        */

        renderPromoResult(data);

    })
    .catch(function(error) {

        console.error(
            'Promo Error:',
            error
        );


        appliedPromos = [];


        if (promoList) {

            promoList.innerHTML = `
                <div class="text-danger small">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    Gagal memuat promo.
                </div>
            `;

        }

    });
}


/*
|--------------------------------------------------------------------------
| RENDER PROMO STACKING
|--------------------------------------------------------------------------
*/

function renderPromoResult(data)
{
    const promoList =
        document.getElementById('promoList');


    if (!promoList) {
        return;
    }


    let html = '';


    /*
    |--------------------------------------------------------------------------
    | Tidak ada promo
    |--------------------------------------------------------------------------
    */

    if (
        !data.discounts ||
        data.discounts.length === 0
    ) {

        html = `
            <div class="text-muted small">
                <i class="fa-solid fa-tag me-1"></i>
                Tidak ada promo yang digunakan.
            </div>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Ada promo
    |--------------------------------------------------------------------------
    */

    else {

        data.discounts.forEach(
            function(promo, index) {

                const promoName =
                    escapeHtml(
                        promo.name ||
                        'Promo'
                    );


                const promoCode =
                    promo.code
                        ? escapeHtml(
                            promo.code
                        )
                        : escapeHtml(
                            promo.trigger_type ||
                            'Promo'
                        );


                const promoDiscount =
                    parseFloat(
                        promo.discount ??
                        promo.amount ??
                        0
                    );


                html += `

                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-center
                            border-bottom
                            pb-2
                            mb-2
                        "
                    >

                        <div>

                            <div class="fw-semibold">

                                <span
                                    class="
                                        badge
                                        bg-success
                                        me-1
                                    "
                                >
                                    ${index + 1}
                                </span>

                                ${promoName}

                            </div>

                            <small
                                class="text-muted"
                            >

                                ${promoCode}

                            </small>

                        </div>


                        <div
                            class="
                                text-danger
                                fw-semibold
                            "
                        >

                            - Rp
                            ${formatRupiah(
                                promoDiscount
                            )}

                        </div>

                    </div>

                `;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Total promo
        |--------------------------------------------------------------------------
        */

        html += `

            <div
                class="
                    d-flex
                    justify-content-between
                    mt-3
                "
            >

                <strong>
                    Total Diskon
                </strong>

                <strong
                    class="text-danger"
                >

                    - Rp
                    ${formatRupiah(
                        data.discount_total || 0
                    )}

                </strong>

            </div>

        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Masukkan HTML
    |--------------------------------------------------------------------------
    */

    promoList.innerHTML =
        html;


    /*
    |--------------------------------------------------------------------------
    | Total diskon
    |--------------------------------------------------------------------------
    */

    const discountTotal =
        parseFloat(
            data.discount_total || 0
        );


    /*
    |--------------------------------------------------------------------------
    | Total akhir
    |--------------------------------------------------------------------------
    */

    const total =
        parseFloat(
            data.total ?? selectedPrice
        );


    /*
    |--------------------------------------------------------------------------
    | Update tampilan diskon
    |--------------------------------------------------------------------------
    */

    const discountPrice =
        document.getElementById(
            'discount_price'
        );

    if (discountPrice) {

        discountPrice.innerText =
            formatRupiah(
                discountTotal
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Update tampilan total
    |--------------------------------------------------------------------------
    */

    const finalPrice =
        document.getElementById(
            'final_price'
        );

    if (finalPrice) {

        finalPrice.innerText =
            formatRupiah(
                total
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Hidden input
    |--------------------------------------------------------------------------
    | Catatan:
    | Nilai ini hanya untuk tampilan/form.
    | Server tetap menghitung ulang promo.
    |--------------------------------------------------------------------------
    */

    const discountInput =
        document.getElementById(
            'discount_total'
        );

    if (discountInput) {

        discountInput.value =
            discountTotal;

    }


    const totalInput =
        document.getElementById(
            'total_price'
        );

    if (totalInput) {

        totalInput.value =
            total;

    }


    /*
    |--------------------------------------------------------------------------
    | Simpan ID promo untuk kebutuhan frontend
    |--------------------------------------------------------------------------
    */

    const discountIds =
        document.getElementById(
            'discount_ids'
        );

    if (discountIds) {

        discountIds.value =
            (data.discounts || [])
                .map(function(promo) {

                    return promo.id;

                })
                .filter(Boolean)
                .join(',');

    }


    /*
    |--------------------------------------------------------------------------
    | Update pesan voucher
    |--------------------------------------------------------------------------
    */

    const voucherMessage =
        document.getElementById(
            'voucher_message'
        );


    if (
        voucherMessage &&
        selectedVoucher
    ) {

        const voucherPromo =
            (data.discounts || [])
                .find(function(promo) {

                    return (
                        promo.trigger_type ===
                        'voucher'
                    );

                });


        if (voucherPromo) {

            voucherMessage.innerHTML = `
                <span class="text-success">
                    <i class="fa-solid fa-circle-check me-1"></i>
                    Voucher berhasil digunakan.
                </span>
            `;

        } else {

            voucherMessage.innerHTML = `
                <span class="text-danger">
                    <i class="fa-solid fa-circle-xmark me-1"></i>
                    Voucher tidak berlaku.
                </span>
            `;

        }

    }
}


/*
|--------------------------------------------------------------------------
| VALIDASI FORM
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('form')
    .forEach(function(form) {

        form.addEventListener(
            'submit',
            function(event) {

                /*
                |--------------------------------------------------------------------------
                | Pastikan item dipilih
                |--------------------------------------------------------------------------
                */

                const itemId =
                    document.getElementById(
                        'item_id'
                    )?.value;


                if (!itemId) {

                    event.preventDefault();

                    alert(
                        'Silakan pilih nominal top up terlebih dahulu.'
                    );

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Pastikan payment dipilih
                |--------------------------------------------------------------------------
                */

                if (!selectedPaymentId) {

                    const paymentSelected =
                        document.querySelector(
                            '.payment-radio:checked'
                        );


                    if (!paymentSelected) {

                        event.preventDefault();

                        alert(
                            'Silakan pilih metode pembayaran.'
                        );

                        return;

                    }


                    selectedPaymentId =
                        parseInt(
                            paymentSelected.value
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Voucher hidden input
                |--------------------------------------------------------------------------
                */

                const voucherInput =
                    document.getElementById(
                        'voucher'
                    );


                if (voucherInput) {

                    voucherInput.value =
                        selectedVoucher ?? '';

                }

            }
        );

    });


/*
|--------------------------------------------------------------------------
| INPUT PLAYER
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('.player-input')
    .forEach(function(input) {

        const type =
            input.dataset.type;


        /*
        |--------------------------------------------------------------------------
        | Number
        |--------------------------------------------------------------------------
        */

        if (type === 'number') {

            input.addEventListener(
                'input',
                function() {

                    this.value =
                        this.value.replace(
                            /[^0-9]/g,
                            ''
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        if (type === 'email') {

            input.addEventListener(
                'input',
                function() {

                    this.value =
                        this.value.replace(
                            /\s/g,
                            ''
                        );

                }
            );

        }

    });


/*
|--------------------------------------------------------------------------
| INITIAL PAYMENT
|--------------------------------------------------------------------------
| Jika sebelumnya ada payment yang sudah terpilih,
| baca dari radio button.
|--------------------------------------------------------------------------
*/

const initialPayment =
    document.querySelector(
        '.payment-radio:checked'
    );


if (initialPayment) {

    selectedPaymentId =
        parseInt(
            initialPayment.value
        );

}


/*
|--------------------------------------------------------------------------
| DEBUG OPTIONAL
|--------------------------------------------------------------------------
*/

console.log(
    'Promo stacking checkout aktif.'
);

</script>
@endsection