<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\UserLoginController;

Route::prefix('auth')->group(function () {

    Route::post('/login', UserLoginController::class);

});

Route::get('/test', function () {

    return response()->json([
        'success' => true,
        'message' => 'User API v1 berhasil.',
    ]);

});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {

    return response()->json([
        'success' => true,
        'user' => $request->user(),
    ]);

});