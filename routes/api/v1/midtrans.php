<?php

use App\Http\Controllers\Api\V1\MidtransNotificationController;
use App\Http\Controllers\Api\V1\MidtransOrderController;
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