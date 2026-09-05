<?php

use App\Http\Controllers\Api\V1\MidtransNotificationController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/midtrans/notification',
    [
        MidtransNotificationController::class,
        'handle',
    ]
);