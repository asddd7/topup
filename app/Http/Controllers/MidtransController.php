<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Midtrans\MidtransOrderService;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MidtransController extends Controller
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
                ->createForOrder(
                    $order
                );


        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER
        |--------------------------------------------------------------------------
        */

        $order->loadMissing([
            'game',
            'details.item',
        ]);


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'midtrans.payment',
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
     * Halaman hanya menampilkan hasil redirect Snap.
     *
     * Status resmi tetap berasal dari:
     *
     * Midtrans → Webhook → Database
     *
     * BUKAN dari query parameter browser.
     */
    public function result(
        Request $request,
        Order $order
    ): View {

        /*
        |--------------------------------------------------------------------------
        | REFRESH ORDER
        |--------------------------------------------------------------------------
        */

        $order =
            $order->fresh([
                'game',
                'details.item',
            ]);


        /*
        |--------------------------------------------------------------------------
        | LATEST MIDTRANS ATTEMPT
        |--------------------------------------------------------------------------
        */

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
                    $order,

                'transaction' =>
                    $transaction,

                /*
                |--------------------------------------------------------------------------
                | Callback data hanya untuk display/debug.
                |--------------------------------------------------------------------------
                */

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