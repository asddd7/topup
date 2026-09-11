<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
*/
Route::get('/v1/admin/moogold/test-category', function (
    \App\Services\MooGold\MooGoldService $mooGold
) {
    return response()->json([
        'success' => true,
        'data' => $mooGold->categories(),
    ]);
});

Route::get('/v1/admin/ditusi/test-product', function (
    \App\Services\Ditusi\DitusiService $ditusi
) {

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
});
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ADMIN API
    |--------------------------------------------------------------------------
    */

    require __DIR__.'/api/v1/admin.php';


    /*
    |--------------------------------------------------------------------------
    | USER API
    |--------------------------------------------------------------------------
    */

    require __DIR__.'/api/v1/user.php';


    /*
    |--------------------------------------------------------------------------
    | MIDTRANS API
    |--------------------------------------------------------------------------
    */

    require __DIR__.'/api/v1/midtrans.php';

});