<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/ditusi/test-product', function (
    \App\Services\Ditusi\DitusiService $ditusi
) {
    $gameCode = request()->query('gameCode');
    $productCode = request()->query('productCode');

    return response()->json([
        'success' => true,
        'environment' => 'DITUSI SANDBOX',
        'gameCode' => $gameCode,
        'productCode' => $productCode,
        'data' => $ditusi->products(
            $gameCode,
            $productCode
        ),
    ]);
});

Route::get('/admin/ditusi/test-games', function (
    \App\Services\Ditusi\DitusiService $ditusi
) {
    return response()->json([
        'success' => true,
        'environment' => 'DITUSI SANDBOX',
        'data' => $ditusi->games(),
    ]);
});

Route::get('/admin/ditusi/test-transaction', function (
    \App\Services\Ditusi\DitusiService $ditusi
) {
    $productCode = request()->query(
        'productCode',
        'BTK60-S46'
    );

    $amount = (int) request()->query(
        'amount',
        1
    );

    $transactionReferenceId = request()->query(
        'reference',
        'TEST-DITUSI-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)
    );

    $initialPrice = request()->query('initialPrice');

    $formDetails = [
        'user_id' => request()->query(
            'user_id',
            '1963315211'
        ),
        'additional_id' => request()->query(
            'additional_id',
            '19248'
        ),
    ];

    return response()->json([
        'success' => true,
        'environment' => 'DITUSI SANDBOX',
        'productCode' => $productCode,
        'amount' => $amount,
        'transactionReferenceId' => $transactionReferenceId,
        'initialPrice' => $initialPrice !== null
            ? (int) $initialPrice
            : null,
        'formDetails' => $formDetails,
        'data' => $ditusi->createTransaction(
            productCode: $productCode,
            amount: $amount,
            transactionReferenceId: $transactionReferenceId,
            formDetails: $formDetails,
            initialPrice: $initialPrice !== null
                ? (int) $initialPrice
                : null,
        ),
    ]);
});

Route::get('/admin/ditusi/test-transaction-status', function (
    \App\Services\Ditusi\DitusiService $ditusi
) {
    $transactionId = request()->query(
        'transactionId',
        'D2C-16902220334421'
    );

    return response()->json([
        'success' => true,
        'environment' => 'DITUSI SANDBOX',
        'transactionId' => $transactionId,
        'data' => $ditusi->getTransactionStatus(
            $transactionId
        ),
    ]);
});