<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\MidtransNotificationController;
use App\Http\Controllers\Api\V1\MidtransOrderController;
use App\Http\Controllers\MidtransWebhookController;


/*
|--------------------------------------------------------------------------
| MIDTRANS
|--------------------------------------------------------------------------
|
| Semua endpoint Midtrans ditempatkan di file ini.
|
*/


/*
|--------------------------------------------------------------------------
| MIDTRANS SERVER NOTIFICATION
|--------------------------------------------------------------------------
|
| Endpoint ini menerima notification dari Midtrans.
|
*/

Route::post(
    '/midtrans/notification',
    [
        MidtransNotificationController::class,
        'handle',
    ]
)->name(
    'midtrans.notification'
);


/*
|--------------------------------------------------------------------------
| MIDTRANS SNAP
|--------------------------------------------------------------------------
|
| Membuat Snap Token untuk sebuah Order.
|
*/

Route::post(
    '/midtrans/orders/{order}/snap',
    [
        MidtransOrderController::class,
        'createSnap',
    ]
)->name(
    'midtrans.orders.snap'
);


/*
|--------------------------------------------------------------------------
| MIDTRANS WEBHOOK
|--------------------------------------------------------------------------
|
| Endpoint utama untuk memproses status pembayaran Midtrans.
|
| Alur:
|
| Midtrans
|     ↓
| webhook
|     ↓
| verifikasi signature
|     ↓
| validasi transaksi
|     ↓
| Order = Paid
|     ↓
| dispatch ProcessMooGoldOrder
|
*/

Route::post(
    '/midtrans/webhook',
    [
        MidtransWebhookController::class,
        'handle',
    ]
)->name(
    'midtrans.webhook'
);

