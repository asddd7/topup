<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ItemController;
use App\Http\Controllers\Api\V1\Admin\OrderController;


Route::prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        Route::post(
            'login',
            [AuthController::class, 'login']
        );


        Route::middleware([
            'auth:sanctum',
            'admin',
        ])->group(function () {

            Route::post(
                'logout',
                [AuthController::class, 'logout']
            );

            Route::get(
                'me',
                [AuthController::class, 'me']
            );

        });

    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN PROTECTED
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'auth:sanctum',
        'admin',
    ])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | ORDER PAYMENT
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/orders/{order}/payment/approve',
            [OrderController::class, 'approvePayment']
        );

        Route::post(
            '/orders/{order}/payment/reject',
            [OrderController::class, 'rejectPayment']
        );


        Route::post(
            '/orders/{order}/complete',
            [OrderController::class, 'completeOrder']
        );

        Route::post(
            '/orders/{order}/process',
            [OrderController::class, 'processOrder']
        );

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */

        Route::prefix('categories')
            ->controller(CategoryController::class)
            ->group(function () {

                Route::get('/', 'index');

                Route::post('/', 'store');

                Route::get(
                    '{category}',
                    'show'
                );

                Route::put(
                    '{category}',
                    'update'
                );

                Route::delete(
                    '{category}',
                    'destroy'
                );

            });


        /*
        |--------------------------------------------------------------------------
        | ITEMS
        |--------------------------------------------------------------------------
        */

        Route::prefix('items')
            ->controller(ItemController::class)
            ->group(function () {

                Route::get('/', 'index');

                Route::post('/', 'store');

                Route::get(
                    '{item}',
                    'show'
                );

                Route::put(
                    '{item}',
                    'update'
                );

                Route::delete(
                    '{item}',
                    'destroy'
                );

            });

    });

});