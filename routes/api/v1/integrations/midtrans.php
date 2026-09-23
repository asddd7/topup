<?php

use Illuminate\Support\Facades\Route;

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
| Endpoint kompatibilitas.
|
| Endpoint lama tetap tersedia tetapi menggunakan
| MidtransWebhookController yang sama.
|
*/

    Route::post(
        '/midtrans/notification',
        [
            MidtransWebhookController::class,
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
| MidtransWebhookController
|     ↓
| MidtransWebhookService
|     ↓
| verifikasi signature
|     ↓
| update transaksi
|     ↓
| Order = Paid
|     ↓
| COMMIT
|     ↓
| TopUpFulfillmentService
|     ↓
| ProviderRegistry
|     ↓
| MooGoldProvider
|     ↓
| ProcessMooGoldOrder
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

