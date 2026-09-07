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
     * MIDTRANS PAYMENT
     * =========================================================
     */
    public function payment(
        Request $request,
        Order $order
    ): View {

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        $this->authorizeOrder(
            $request,
            $order
        );


        /*
        |--------------------------------------------------------------------------
        | STATUS CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $order->status,
                [
                    'Pending',
                    'Waiting Payment',
                ],
                true
            )
        ) {

            return redirect()->route(
                'midtrans.result',
                [
                    'order' => $order->id,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE / RESOLVE SNAP TRANSACTION
        |--------------------------------------------------------------------------
        */

        $transaction =
            $this->midtransOrderService
                ->createForOrder(
                    $order
                );


        /*
        |--------------------------------------------------------------------------
        | LOAD RELATION
        |--------------------------------------------------------------------------
        */

        $order->loadMissing([
            'game',
            'details.item',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PAYMENT PAGE
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
     */
    public function result(
        Request $request,
        Order $order
    ): View {

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        $this->authorizeOrder(
            $request,
            $order
        );


        /*
        |--------------------------------------------------------------------------
        | REFRESH ORDER
        |--------------------------------------------------------------------------
        */

        $order =
            $order->fresh(
                [
                    'game',
                    'details.item',
                    'payment',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | GET LATEST MIDTRANS TRANSACTION
        |--------------------------------------------------------------------------
        */

        $transaction =
            $order
                ->midtransTransactions()
                ->latest(
                    'attempt_number'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | RESULT PAGE
        |--------------------------------------------------------------------------
        */

        return view(
            'midtrans.result',
            [
                'order' =>
                    $order,

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


    /**
     * =========================================================
     * AUTHORIZE ORDER
     * =========================================================
     */
    private function authorizeOrder(
        Request $request,
        Order $order
    ): void {

        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATED USER
        |--------------------------------------------------------------------------
        */

        if ($order->user_id) {

            if (!auth()->check()) {

                abort(
                    403,
                    'Silakan login untuk mengakses order ini.'
                );
            }


            if (
                (int) $order->user_id !==
                (int) auth()->id()
            ) {

                abort(
                    403,
                    'Anda tidak memiliki akses ke order ini.'
                );
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | GUEST ORDER
        |--------------------------------------------------------------------------
        */

        if (
            !$request->filled(
                'token'
            )
            ||
            !hash_equals(
                (string)
                $order->guest_token,

                (string)
                $request->token
            )
        ) {

            abort(
                403,
                'Token order tidak valid.'
            );
        }
    }
}