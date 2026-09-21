<?php

use Illuminate\Support\Facades\Route;

use App\Integrations\Ditusi\DitusiService;


/*
|--------------------------------------------------------------------------
| DITUSI
|--------------------------------------------------------------------------
*/

Route::prefix('admin/ditusi')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | TEST PRODUCT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-product',
        function (DitusiService $ditusi) {

            $gameCode =
                request()->query('gameCode');

            $productCode =
                request()->query('productCode');

            return response()->json([

                'success' => true,

                'environment' =>
                    'DITUSI SANDBOX',

                'gameCode' =>
                    $gameCode,

                'productCode' =>
                    $productCode,

                'data' =>
                    $ditusi->products(
                        $gameCode,
                        $productCode
                    ),

            ]);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TEST GAMES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-games',
        function (DitusiService $ditusi) {

            return response()->json([

                'success' => true,

                'environment' =>
                    'DITUSI SANDBOX',

                'data' =>
                    $ditusi->games(),

            ]);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TEST TRANSACTION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-transaction',
        function (DitusiService $ditusi) {

            $productCode =
                request()->query(
                    'productCode',
                    'BTK60-S46'
                );

            $amount =
                (int) request()->query(
                    'amount',
                    1
                );

            $transactionReferenceId =
                request()->query(
                    'reference',
                    'TEST-DITUSI-' .
                    now()->format('YmdHis') .
                    '-' .
                    random_int(1000, 9999)
                );

            $initialPrice =
                request()->query(
                    'initialPrice'
                );

            $formDetails = [

                'user_id' =>
                    request()->query(
                        'user_id',
                        '1963315211'
                    ),

                'additional_id' =>
                    request()->query(
                        'additional_id',
                        '19248'
                    ),

            ];

            return response()->json([

                'success' => true,

                'environment' =>
                    'DITUSI SANDBOX',

                'productCode' =>
                    $productCode,

                'amount' =>
                    $amount,

                'transactionReferenceId' =>
                    $transactionReferenceId,

                'initialPrice' =>
                    $initialPrice !== null
                        ? (int) $initialPrice
                        : null,

                'formDetails' =>
                    $formDetails,

                'data' =>
                    $ditusi->createTransaction(
                        productCode:
                            $productCode,

                        amount:
                            $amount,

                        transactionReferenceId:
                            $transactionReferenceId,

                        formDetails:
                            $formDetails,

                        initialPrice:
                            $initialPrice !== null
                                ? (int) $initialPrice
                                : null,
                    ),

            ]);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TEST TRANSACTION STATUS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/test-transaction-status',
        function (DitusiService $ditusi) {

            $transactionId =
                request()->query(
                    'transactionId',
                    'D2C-16902220334421'
                );

            return response()->json([

                'success' => true,

                'environment' =>
                    'DITUSI SANDBOX',

                'transactionId' =>
                    $transactionId,

                'data' =>
                    $ditusi->getTransactionStatus(
                        $transactionId
                    ),

            ]);

        }
    );

});