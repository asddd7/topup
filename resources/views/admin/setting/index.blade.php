@extends('admin.layouts.app')

@section('title','Pengaturan Website')

@section('content')

<div class="admin-setting-page">

    <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center setting-page-header">

        <div>

            <h3 class="fw-bold mb-1">
                <i class="fa-solid fa-gears me-2"></i>
                Pengaturan Website
            </h3>

            <small>
                Kelola seluruh konfigurasi website TopUp.
            </small>

        </div>

    </div>

    @if(session('success'))

    <div class="alert alert-success">

        <i class="fa-solid fa-circle-check me-2"></i>

        {{ session('success') }}

    </div>

    @endif

    <form
        action="{{ route('admin.setting.update',1) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

    <div class="card setting-card border-0">

            <div class="card-body">

                <ul class="nav nav-pills setting-tabs mb-4" id="setting-tab">

                    <li class="nav-item">
                        <button
                            type="button"
                            class="nav-link active"
                            data-bs-toggle="pill"
                            data-bs-target="#general">

                            <i class="fa-solid fa-sliders me-1"></i>

                            General

                        </button>
                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#contact">

                            <i class="fa-solid fa-phone me-1"></i>

                            Contact

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#social">

                            <i class="fa-solid fa-share-nodes me-1"></i>

                            Social

                        </button>

                    </li>

                    <li class="nav-item">

                        <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#system">

                            <i class="fa-solid fa-server me-1"></i>

                            System

                        </button>

                    </li>

                </ul>

                <div class="tab-content">

                    {{-- GENERAL --}}

                    <div
                        class="tab-pane fade show active"
                        id="general">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Nama Website
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="app_name"
                                    value="{{ $settings['app_name']->setting_value ?? '' }}">

                            </div>

                        </div>

                        <hr>

                        <h5 class="setting-section-title">
                            Logo Website
                        </h5>

                        <div class="row align-items-center">

                            <div class="col-md-3 mb-3 mb-md-0">

                                <div class="setting-media-preview">

                                    @if(!empty($settings['app_logo']->setting_value ?? ''))

                                        <img
                                            src="{{ asset('storage/'.$settings['app_logo']->setting_value) }}"
                                            style="max-height:120px;">

                                    @else

                                        <div class="setting-media-empty">
                                            Belum ada logo
                                        </div>

                                    @endif

                                </div>

                            </div>

                            <div class="col-md-9">

                                <label class="form-label">
                                    Upload Logo
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    name="app_logo"
                                    accept=".png,.jpg,.jpeg,.svg,.webp">

                                <small class="setting-file-help">
                                    Disarankan PNG transparan ukuran 300x300.
                                </small>

                            </div>

                        </div>

                        <hr>

                        <h5 class="setting-section-title">
                            Favicon Website
                        </h5>

                        <div class="row align-items-center">

                            <div class="col-md-3 mb-3 mb-md-0">

                                <div class="setting-media-preview">

                                    @if(!empty($settings['app_favicon']->setting_value ?? ''))

                                        <img
                                            src="{{ asset('storage/'.$settings['app_favicon']->setting_value) }}"
                                            style="max-height:64px;">

                                    @else

                                        <div class="setting-media-empty">
                                            Belum ada favicon
                                        </div>

                                    @endif

                                </div>

                            </div>

                            <div class="col-md-9">

                                <label class="form-label">
                                    Upload Favicon
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    name="app_favicon"
                                    accept=".png,.jpg,.jpeg,.ico,.svg,.webp">

                                <small class="setting-file-help">
                                    Ukuran disarankan 32x32 atau 64x64 pixel.
                                </small>

                            </div>

                        </div>

                    </div>

                    {{-- CONTACT --}}

                    <div
                        class="tab-pane fade"
                        id="contact">

                        <div class="mb-3">

                            <label class="form-label">

                                Whatsapp

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="whatsapp"
                                value="{{ $settings['whatsapp']->setting_value ?? '' }}">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Email

                            </label>

                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                value="{{ $settings['email']->setting_value ?? '' }}">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Alamat

                            </label>

                            <textarea
                                class="form-control"
                                rows="3"
                                name="address">{{ $settings['address']->setting_value ?? '' }}</textarea>

                        </div>

                    </div>

                    {{-- SOCIAL --}}

                    <div
                        class="tab-pane fade"
                        id="social">

                        <input
                            class="form-control mb-3"
                            placeholder="Facebook"
                            name="facebook"
                            value="{{ $settings['facebook']->setting_value ?? '' }}">

                        <input
                            class="form-control mb-3"
                            placeholder="Instagram"
                            name="instagram"
                            value="{{ $settings['instagram']->setting_value ?? '' }}">

                        <input
                            class="form-control"
                            placeholder="Youtube"
                            name="youtube"
                            value="{{ $settings['youtube']->setting_value ?? '' }}">

                    </div>

                    {{-- SYSTEM --}}

                    <div
                        class="tab-pane fade"
                        id="system">

                        <input type="hidden" name="maintenance" value="0">

                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="maintenance"
                                value="1"
                                {{ (($settings['maintenance']->setting_value ?? 0) == 1) ? 'checked' : '' }}>

                            <label class="form-check-label">
                                Maintenance Mode
                            </label>
                        </div>

                        <input type="hidden" name="allow_guest_checkout" value="0">

                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="allow_guest_checkout"
                                value="1"
                                {{ (($settings['allow_guest_checkout']->setting_value ?? 1) == 1) ? 'checked' : '' }}>

                            <label class="form-check-label">
                                Izinkan Guest Checkout
                            </label>
                        </div>

                        <hr>

                        <div class="setting-wallet-section">

                            <h5 class="setting-section-title">
                                MooGold Wallet
                            </h5>

                            <div class="setting-wallet-card">

                                <div class="setting-wallet-balance-box">

                                    <div>
                                        <span class="setting-wallet-label">
                                            Saldo Saat Ini
                                        </span>

                                        <div class="setting-wallet-balance">

                                            <span id="moogoldBalance">
                                                —
                                            </span>

                                            <small id="moogoldCurrency">
                                                IDR
                                            </small>

                                        </div>

                                        <small
                                            id="moogoldBalanceStatus"
                                            class="setting-wallet-status">
                                            Klik refresh untuk mengambil saldo terbaru.
                                        </small>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn btn-outline-primary setting-wallet-refresh"
                                        id="refreshMoogoldBalance">

                                        <i class="fa-solid fa-rotate"></i>

                                        <span>Refresh Saldo</span>

                                    </button>

                                </div>


                                <div class="setting-wallet-reload">

                                    <label class="form-label">
                                        Nominal Reload
                                    </label>

                                    <div class="row g-2">

                                        <div class="col-md-8">

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                class="form-control"
                                                id="moogoldReloadAmount"
                                                placeholder="Contoh: 1000">

                                        </div>

                                        <div class="col-md-4">

                                            <button
                                                type="button"
                                                class="btn btn-primary w-100 setting-wallet-reload-button"
                                                id="reloadMoogoldBalance">

                                                <i class="fa-solid fa-wallet me-2"></i>

                                                Reload Saldo

                                            </button>

                                        </div>

                                    </div>

                                    <small class="setting-file-help">
                                        Payment method: USDT-TRC20.
                                    </small>

                                </div>


                                <div
                                    id="moogoldReloadResult"
                                    class="setting-wallet-result d-none">

                                    <div class="setting-wallet-result-title">
                                        <i class="fa-solid fa-circle-check me-2"></i>
                                        Request Reload Berhasil
                                    </div>

                                    <div class="setting-wallet-result-grid">

                                        <div>
                                            <span>Order ID</span>
                                            <strong id="moogoldReloadOrderId">-</strong>
                                        </div>

                                        <div>
                                            <span>Amount</span>
                                            <strong id="moogoldReloadAmount">-</strong>
                                        </div>

                                        <div>
                                            <span>Currency</span>
                                            <strong id="moogoldReloadCurrency">-</strong>
                                        </div>

                                    </div>

                                    <div class="setting-wallet-address">

                                        <label>
                                            Payment Address
                                        </label>

                                        <div class="input-group">

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="moogoldPaymentAddress"
                                                readonly>

                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary"
                                                id="copyMoogoldPaymentAddress">

                                                <i class="fa-regular fa-copy"></i>

                                            </button>

                                        </div>

                                        <small>
                                            Kirim pembayaran USDT-TRC20 ke address di atas.
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="card-footer bg-white">

            <button class="btn btn-primary setting-save-button">
                <i class="fa-solid fa-floppy-disk me-2"></i>
                Simpan Pengaturan
            </button>

            </div>

        </div>

    </form>

</div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | LAST ACTIVE TAB
    |--------------------------------------------------------------------------
    */

    const activeTab = localStorage.getItem('setting-active-tab');

    if (activeTab) {

        const trigger = document.querySelector(
            '[data-bs-target="' + activeTab + '"]'
        );

        if (trigger) {

            bootstrap.Tab
                .getOrCreateInstance(trigger)
                .show();

        }

    }

    document
        .querySelectorAll('[data-bs-toggle="pill"]')
        .forEach(tab => {

            tab.addEventListener('shown.bs.tab', function (e) {

                localStorage.setItem(
                    'setting-active-tab',
                    e.target.dataset.bsTarget
                );

            });

        });


    /*
    |--------------------------------------------------------------------------
    | MOOGOLD WALLET
    |--------------------------------------------------------------------------
    */

    const balanceUrl =
        'http://127.0.0.1:8000/api/v1/admin/moogold/test-balance';

    const reloadUrl =
        @json(route('admin.setting.moogold.reload-balance'));

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');


    const balanceEl =
        document.getElementById('moogoldBalance');

    const currencyEl =
        document.getElementById('moogoldCurrency');

    const balanceStatusEl =
        document.getElementById('moogoldBalanceStatus');

    const refreshButton =
        document.getElementById('refreshMoogoldBalance');

    const reloadButton =
        document.getElementById('reloadMoogoldBalance');

    const amountInput =
        document.getElementById('moogoldReloadAmount');

    const resultBox =
        document.getElementById('moogoldReloadResult');

    const resultOrderId =
        document.getElementById('moogoldReloadOrderId');

    const resultAmount =
        document.getElementById('moogoldReloadAmount');

    const resultCurrency =
        document.getElementById('moogoldReloadCurrency');

    const paymentAddress =
        document.getElementById('moogoldPaymentAddress');

    const copyButton =
        document.getElementById('copyMoogoldPaymentAddress');


        /*
        |--------------------------------------------------------------------------
        | GET BALANCE
        |--------------------------------------------------------------------------
        */

    async function loadMoogoldBalance() {

        if (!balanceEl) {
            return;
        }

        refreshButton?.setAttribute('disabled', 'disabled');

        if (refreshButton) {

            refreshButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Memuat...</span>
            `;

        }

        balanceStatusEl.textContent =
            'Menghubungkan ke MooGold...';

        try {

            const response = await fetch(balanceUrl, {

                method: 'GET',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }

            });

            const result = await response.json();

            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil saldo MooGold.'
                );

            }

            const data = result.data || {};

            balanceEl.textContent =
                formatBalance(data.balance);

            currencyEl.textContent =
                data.currency || 'IDR';

            balanceStatusEl.textContent =
                result.message ||
                'Saldo berhasil diperbarui.';

        } catch (error) {

            balanceEl.textContent = '—';

            currencyEl.textContent = 'IDR';

            balanceStatusEl.textContent =
                error.message ||
                'Gagal mengambil saldo MooGold.';

        } finally {

            refreshButton?.removeAttribute('disabled');

            if (refreshButton) {

                refreshButton.innerHTML = `
                    <i class="fa-solid fa-rotate"></i>
                    <span>Refresh Saldo</span>
                `;

            }

        }
    }


    /*
    |--------------------------------------------------------------------------
    | RELOAD BALANCE
    |--------------------------------------------------------------------------
    */

    async function reloadMoogoldBalance() {

        const amount = amountInput?.value?.trim();

        if (!amount || Number(amount) <= 0) {

            amountInput?.focus();

            return;

        }

        reloadButton?.setAttribute('disabled', 'disabled');

        if (reloadButton) {

            reloadButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin me-2"></i>
                Memproses...
            `;

        }

        resultBox?.classList.add('d-none');

        try {

            const response = await fetch(reloadUrl, {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },

                body: JSON.stringify({
                    amount: amount
                })

            });

            const result = await response.json();

            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal membuat request reload.'
                );

            }

            const data = result.data || {};

            resultOrderId.textContent =
                data.order_id ?? '-';

            resultAmount.textContent =
                data.amount ?? '-';

            resultCurrency.textContent =
                data.wallet_currency ?? '-';

            paymentAddress.value =
                data.payment_address ?? '';

            resultBox?.classList.remove('d-none');

        } catch (error) {

            alert(
                error.message ||
                'Gagal membuat request reload saldo.'
            );

        } finally {

            reloadButton?.removeAttribute('disabled');

            if (reloadButton) {

                reloadButton.innerHTML = `
                    <i class="fa-solid fa-wallet me-2"></i>
                    Reload Saldo
                `;

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */

    refreshButton?.addEventListener(
        'click',
        loadMoogoldBalance
    );

    reloadButton?.addEventListener(
        'click',
        reloadMoogoldBalance
    );


    copyButton?.addEventListener('click', async function () {

        const value =
            paymentAddress?.value;

        if (!value) {
            return;
        }

        try {

            await navigator.clipboard.writeText(value);

            this.innerHTML =
                '<i class="fa-solid fa-check"></i>';

            setTimeout(() => {

                this.innerHTML =
                    '<i class="fa-regular fa-copy"></i>';

            }, 1500);

        } catch (error) {

            paymentAddress.select();

            document.execCommand('copy');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | LOAD BALANCE ON PAGE LOAD
    |--------------------------------------------------------------------------
    */

    loadMoogoldBalance();

});
</script>
@endpush
@endsection