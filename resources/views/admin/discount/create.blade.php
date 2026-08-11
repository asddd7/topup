<div class="modal fade"
     id="createDiscountModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content shadow border-0 rounded-4">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-ticket me-2"></i>
                    Tambah Voucher / Promo
                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('admin.discount.store') }}"
                  method="POST">

                @csrf

                <div class="modal-body">

                    {{-- ========================= --}}
                    {{-- INFORMASI PROMO --}}
                    {{-- ========================= --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Informasi Promo
                    </h6>

                    <div class="mb-3">

                        <label class="form-label">
                            Trigger Promo
                        </label>

                        <select
                            name="trigger_type"
                            id="trigger_type"
                            class="form-select">

                            <option value="voucher">
                                Voucher
                            </option>

                            <option value="automatic">
                                Otomatis
                            </option>

                            <option value="new_user">
                                User Baru
                            </option>

                            <option value="flash_sale">
                                Flash Sale
                            </option>

                            <option value="payment_method">
                                Metode Pembayaran
                            </option>

                        </select>

                    </div>

                    <div class="mb-3"
                         id="voucherCodeArea">

                        <label class="form-label">
                            Kode Voucher
                        </label>

                        <input
                            type="text"
                            name="code"
                            class="form-control"
                            placeholder="Contoh : RAMADAN50">

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Promo
                        </label>

                        <input
                            type="text"
                            name="discount_name"
                            class="form-control"
                            placeholder="Promo Ramadan"
                            required>

                    </div>

                    <hr>

                    {{-- ========================= --}}
                    {{-- TARGET --}}
                    {{-- ========================= --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Target Promo
                    </h6>

                    <div class="mb-3">

                        <label class="form-label">
                            Game
                        </label>

                        <select
                            name="game_id"
                            class="form-select">

                            <option value="">
                                Semua Game
                            </option>

                            @foreach($games as $game)

                                <option value="{{ $game->id }}">
                                    {{ $game->game_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Item
                        </label>

                        <select
                            name="item_id"
                            class="form-select">

                            <option value="">
                                Semua Item
                            </option>

                            @foreach($items as $item)

                                <option value="{{ $item->id }}">
                                    {{ $item->item_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <hr>

                    <div
                        class="mb-3"
                        id="paymentArea"
                    >

                        <label class="form-label">
                            Metode Pembayaran
                        </label>

                        <select
                            name="payment_id"
                            class="form-select"
                        >

                            <option value="">
                                Semua Pembayaran
                            </option>

                            @foreach($payments as $payment)

                                <option value="{{ $payment->id }}">

                                    {{ $payment->payment_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>
                    <hr>

                    {{-- ========================= --}}
                    {{-- DISKON --}}
                    {{-- ========================= --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Nilai Diskon
                    </h6>

                    <div class="row">

                        <div class="col-md-6">

                            <label class="form-label">
                                Tipe Diskon
                            </label>

                            <select
                                name="discount_type"
                                class="form-select">

                                <option value="percent">
                                    Persentase (%)
                                </option>

                                <option value="fixed">
                                    Nominal (Rp)
                                </option>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Jumlah
                            </label>

                            <input
                                type="number"
                                name="amount"
                                class="form-control"
                                required>

                        </div>

                    </div>

                    <div class="row mt-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                Minimal Pembelian
                            </label>

                            <input
                                type="number"
                                name="minimum_purchase"
                                value="0"
                                class="form-control">

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                Kuota Voucher
                            </label>

                            <input
                                type="number"
                                name="usage_limit"
                                class="form-control"
                                placeholder="Unlimited">

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                Maks/User
                            </label>

                        <input
                            type="number"
                            name="usage_per_user"
                            value="{{ old('usage_per_user', 1) }}"
                            min="0"
                            step="1"
                            class="form-control">

                        <small class="text-muted">
                            0 = tidak dibatasi
                        </small>
                        </div>

                    </div>

                    <hr>

                    {{-- ========================= --}}
                    {{-- PERIODE --}}
                    {{-- ========================= --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Periode Promo
                    </h6>

                    <div class="row">

                        <div class="col">

                            <label class="form-label">
                                Mulai
                            </label>

                            <input
                                type="date"
                                name="start_date"
                                class="form-control">

                        </div>

                        <div class="col">

                            <label class="form-label">
                                Berakhir
                            </label>

                            <input
                                type="date"
                                name="end_date"
                                class="form-control">

                        </div>

                    </div>

                    <div class="form-check form-switch mt-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked>

                        <label class="form-check-label">
                            Promo Aktif
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
                        class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>

                        Simpan Promo

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const trigger =
        document.getElementById('trigger_type');

    const voucherArea =
        document.getElementById('voucherCodeArea');

    const paymentArea =
        document.getElementById('paymentArea');


    function toggleForm() {

        /*
        |--------------------------------------------------------------------------
        | Voucher
        |--------------------------------------------------------------------------
        */

        if (voucherArea) {

            voucherArea.style.display =
                trigger.value === 'voucher'
                    ? ''
                    : 'none';

        }


        /*
        |--------------------------------------------------------------------------
        | Payment Method
        |--------------------------------------------------------------------------
        */

        if (paymentArea) {

            paymentArea.style.display =
                trigger.value === 'payment_method'
                    ? ''
                    : 'none';

        }

    }


    if (trigger) {

        trigger.addEventListener(
            'change',
            toggleForm
        );

        toggleForm();

    }

});
</script>