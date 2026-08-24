<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\MeController;

use App\Http\Controllers\Api\V1\User\GameController;
use App\Http\Controllers\Api\V1\User\CatalogController;


/*
|--------------------------------------------------------------------------
| USER AUTH API
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Guest
    |--------------------------------------------------------------------------
    */

    Route::middleware('guest:user-api')->group(function () {

        Route::post(
            'register',
            RegisterController::class
        );

        Route::post(
            'login',
            LoginController::class
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Authenticated
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:user-api')->group(function () {

        Route::get(
            'me',
            MeController::class
        );

        Route::post(
            'logout',
            LogoutController::class
        );

    });

});


/*
|--------------------------------------------------------------------------
| GAME API
|--------------------------------------------------------------------------
*/

Route::prefix('games')->group(function () {

    Route::get(
        '/',
        [GameController::class, 'index']
    );

    Route::get(
        '/{game}',
        [GameController::class, 'show']
    );

});


/*
|--------------------------------------------------------------------------
| USER CATALOG API
|--------------------------------------------------------------------------
*/

Route::prefix('catalog')->group(function () {

    Route::get(
        '/categories',
        [CatalogController::class, 'categories']
    );

    Route::get(
        '/games/{game}/items',
        [CatalogController::class, 'items']
    );

    Route::get(
        '/games/{game}/categories',
        [CatalogController::class, 'gameCategories']
    );

    Route::get(
        '/items/{item}',
        [CatalogController::class, 'item']
    );

});


/*
|--------------------------------------------------------------------------
| API TEST
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {

    return response()->json([
        'success' => true,
        'message' => 'User API v1 berhasil.',
    ]);

});