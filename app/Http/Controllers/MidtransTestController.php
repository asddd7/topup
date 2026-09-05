<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Midtrans\MidtransOrderService;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MidtransTestController extends Controller
{
    public function __construct(
        protected MidtransOrderService $midtransOrderService,
        protected MidtransService $midtrans
    ) {
    }


    /**
     * =========================================================
     * PAYMENT PAGE
     * =========================================================
     */
    public function payment(
        Order $order
    ): View {

        /*
        |--------------------------------------------------------------------------
        | CREATE / RESOLVE SNAP PAYMENT
        |--------------------------------------------------------------------------
        */

        $transaction =
            $this->midtransOrderService
                ->createForOrder($order);


        $order->loadMissing([
            'game',
        ]);


        return view(
            'midtrans.test-payment',
            [
                'order' =>
                    $order,

                'transaction' =>
                    $transaction,

                'clientKey' =>
                    config(
                        'midtrans.client_key'
                    ),

                'isProduction' =>
                    (bool)
                    config(
                        'midtrans.is_production',
                        false
                    ),
            ]
        );
    }


    /**
     * =========================================================
     * PAYMENT RESULT
     * =========================================================
     *
     * Halaman ini hanya menampilkan hasil pembayaran.
     *
     * Status tetap diverifikasi ke Midtrans dari backend.
     */
    public function result(
        Request $request,
        Order $order
    ): View {

        $transaction =
            $order
                ->midtransTransactions()
                ->latest(
                    'attempt_number'
                )
                ->first();


        return view(
            'midtrans.result',
            [
                'order' =>
                    $order->fresh(),

                'transaction' =>
                    $transaction,

                'midtransOrderId' =>
                    $request->query(
                        'order_id'
                    ),

                'statusCode' =>
                    $request->query(
                        'status_code'
                    ),

                'transactionStatus' =>
                    $request->query(
                        'transaction_status'
                    ),
            ]
        );
    }
}