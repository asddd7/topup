<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;
use RuntimeException;

class MidtransTestController extends Controller
{
    public function payment(Order $order): View
    {
        $order->loadMissing([
            'midtransTransaction',
            'game',
        ]);

        if (!$order->midtransTransaction) {
            throw new RuntimeException(
                'Transaksi Midtrans untuk order ini belum dibuat.'
            );
        }

        if (
            empty(
                $order->midtransTransaction->snap_token
            )
        ) {
            throw new RuntimeException(
                'Snap Token untuk order ini belum tersedia.'
            );
        }

        return view(
            'midtrans.test-payment',
            [
                'order' => $order,
                'transaction' =>
                    $order->midtransTransaction,
                'clientKey' =>
                    config('midtrans.client_key'),
                'isProduction' =>
                    (bool) config(
                        'midtrans.is_production',
                        false
                    ),
            ]
        );
    }
}