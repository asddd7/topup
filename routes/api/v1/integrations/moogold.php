<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Integrations\MooGold\MooGoldService;
use App\Http\Controllers\Api\V1\Admin\MooGoldController;


/*
|--------------------------------------------------------------------------
| MOO GOLD
|--------------------------------------------------------------------------
*/

Route::prefix('admin/moogold')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | CONFIG TEST
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/config-test',
        function () {

            return response()->json([

                'base_url' =>
                    config('moogold.base_url'),

                'partner_id_exists' =>
                    !empty(
                        config('moogold.partner_id')
                    ),

                'partner_id_length' =>
                    strlen(
                        (string)
                        config('moogold.partner_id')
                    ),

                'secret_key_exists' =>
                    !empty(
                        config('moogold.secret_key')
                    ),

                'secret_key_length' =>
                    strlen(
                        (string)
                        config('moogold.secret_key')
                    ),

            ]);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TEST BALANCE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-balance',
        function (
            MooGoldService $mooGold
        ) {

            try {

                $result =
                    $mooGold->balance();

                return response()->json([

                    'success' => true,

                    'message' =>
                        'Berhasil terhubung ke MooGold.',

                    'data' =>
                        $result,

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
    | TEST SERVER LIST
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-server-list/{productId}',
        function (
            int $productId,
            MooGoldService $mooGold
        ) {

            try {

                $result =
                    $mooGold->serverList(
                        $productId
                    );

                return response()->json([

                    'success' => true,

                    'product_id' =>
                        $productId,

                    'message' =>
                        'Server list berhasil diambil dari MooGold.',

                    'servers' =>
                        $result,

                ]);

            } catch (\Throwable $e) {

                \Log::error(
                    'Test MooGold server list gagal.',
                    [
                        'product_id' =>
                            $productId,

                        'message' =>
                            $e->getMessage(),
                    ]
                );

                return response()->json([

                    'success' => false,

                    'product_id' =>
                        $productId,

                    'message' =>
                        $e->getMessage(),

                ], 500);

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | TEST ORDER DETAIL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-order-detail/{partnerOrderId}',
        function (
            string $partnerOrderId,
            MooGoldService $mooGold
        ) {

            try {

                $result =
                    $mooGold->orderByPartnerOrderId(
                        $partnerOrderId
                    );

                return response()->json([

                    'success' => true,

                    'partner_order_id' =>
                        $partnerOrderId,

                    'data' =>
                        $result,

                ]);

            } catch (\Throwable $e) {

                return response()->json([

                    'success' => false,

                    'partner_order_id' =>
                        $partnerOrderId,

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

    Route::get(
        '/outbound-ip',
        function () {

            $ipv4 = null;
            $ipv6 = null;


            try {

                $ipv4 =
                    trim(
                        Http::timeout(10)
                            ->get(
                                'https://api.ipify.org'
                            )
                            ->body()
                    );

            } catch (\Throwable $e) {

                $ipv4 =
                    'ERROR: ' .
                    $e->getMessage();

            }


            try {

                $ipv6 =
                    trim(
                        Http::timeout(10)
                            ->get(
                                'https://api6.ipify.org'
                            )
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

        }
    );


    /*
    |--------------------------------------------------------------------------
    | REAL MOO GOLD API
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'auth:sanctum',
        'admin',
    ])->group(function () {


        Route::get(
            '/balance',
            [
                MooGoldController::class,
                'balance',
            ]
        );


        Route::get(
            '/categories',
            [
                MooGoldController::class,
                'categories',
            ]
        );


        Route::post(
            '/sync/{productId}',
            [
                MooGoldController::class,
                'syncProduct',
            ]
        );


        Route::get(
            '/products/{categoryId}',
            [
                MooGoldController::class,
                'products',
            ]
        );


        Route::get(
            '/product/{productId}',
            [
                MooGoldController::class,
                'product',
            ]
        );

    });

});