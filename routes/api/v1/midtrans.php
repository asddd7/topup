<?php

use App\Http\Controllers\Api\V1\MidtransNotificationController;
use App\Http\Controllers\Api\V1\MidtransOrderController;
use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/midtrans/notification',
    [
        MidtransNotificationController::class,
        'handle',
    ]
);

Route::post(
    '/midtrans/orders/{order}/snap',
    [
        MidtransOrderController::class,
        'createSnap',
    ]
);

Route::post(
    '/midtrans/webhook',
    [MidtransWebhookController::class, 'handle']
)->name(
    'midtrans.webhook'
);