<?php

use App\Services\MooGold\MooGoldService;
use App\Services\MooGold\MooGoldCatalogService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Api\V1\Admin\MooGoldController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ItemController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\MooGoldProductMappingController;


Route::prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | MOO GOLD CONNECTION TEST
    |--------------------------------------------------------------------------
    */
    Route::get('/moogold/config-test', function () {

        return response()->json([

            'base_url' =>
                config('moogold.base_url'),

            'partner_id_exists' =>
                !empty(config('moogold.partner_id')),

            'partner_id_length' =>
                strlen((string) config('moogold.partner_id')),

            'secret_key_exists' =>
                !empty(config('moogold.secret_key')),

            'secret_key_length' =>
                strlen((string) config('moogold.secret_key')),

        ]);

    });


    Route::get(
        '/moogold/test-balance',
        function (MooGoldService $mooGold) {

            try {

                $result = $mooGold->balance();

                return response()->json([

                    'success' => true,

                    'message' =>
                        'Berhasil terhubung ke MooGold.',

                    'data' => $result,

                ]);

            } catch (\Throwable $e) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        $e->getMessage(),

                ], 500);
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | OUTBOUND IP
    |--------------------------------------------------------------------------
    */

    Route::get('/moogold/outbound-ip', function () {

        $ipv4 = null;
        $ipv6 = null;

        try {

            $ipv4 = trim(
                Http::timeout(10)
                    ->get('https://api.ipify.org')
                    ->body()
            );

        } catch (\Throwable $e) {

            $ipv4 =
                'ERROR: ' .
                $e->getMessage();
        }


        try {

            $ipv6 = trim(
                Http::timeout(10)
                    ->get('https://api6.ipify.org')
                    ->body()
            );

        } catch (\Throwable $e) {

            $ipv6 =
                'Tidak tersedia';
        }


        return response()->json([

            'success' => true,

            'outbound_ipv4' =>
                $ipv4,

            'outbound_ipv6' =>
                $ipv6,

            'server' => [

                'php' =>
                    PHP_VERSION,

                'host' =>
                    gethostname(),

            ],

        ]);

    });


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
    | MOO GOLD PRODUCT MAPPING
    |--------------------------------------------------------------------------
    */

    Route::prefix('moogold/product-mapping')
        ->controller(MooGoldProductMappingController::class)
        ->group(function () {

            Route::get(
                '/',
                'index'
            );

            Route::post(
                '/sync-category',
                'syncCategory'
            );

            Route::get(
                '/games',
                'games'
            );

            Route::get(
                '/categories',
                'categories'
            );

            Route::get(
                '/{mapping}',
                'show'
            );

            Route::put(
                '/{mapping}',
                'update'
            );

        });


    /*
    |--------------------------------------------------------------------------
    | MOO GOLD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/moogold/balance',
        [MooGoldController::class, 'balance']
    );

    Route::get(
        '/moogold/categories',
        [MooGoldController::class, 'categories']
    );

    Route::post(
        '/moogold/sync/{productId}',
        [MooGoldController::class, 'syncProduct']
    );

    Route::get(
        '/moogold/products/{categoryId}',
        [MooGoldController::class, 'products']
    );

    Route::get(
        '/moogold/product/{productId}',
        [MooGoldController::class, 'product']
    );

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