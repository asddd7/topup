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
    Request $request,
    Order $order
): View {

    /*
    |--------------------------------------------------------------------------
    | PAYMENT TYPE
    |--------------------------------------------------------------------------
    |
    | Payment yang dipilih user pada game.show sudah disimpan
    | ke orders.midtrans_payment_type.
    |
    */

    $paymentType =
        $order->midtrans_payment_type;


    /*
    |--------------------------------------------------------------------------
    | CREATE / RESOLVE SNAP PAYMENT
    |--------------------------------------------------------------------------
    */

    $transaction =
        $this->midtransOrderService
            ->createForOrder(
                $order,
                $paymentType
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
    | PAYMENT LABEL
    |--------------------------------------------------------------------------
    */

    $paymentLabels = [

        'credit_card' =>
            'Kartu Kredit',

        'bca_va' =>
            'BCA Virtual Account',

        'bni_va' =>
            'BNI Virtual Account',

        'bri_va' =>
            'BRI Virtual Account',

        'cimb_va' =>
            'CIMB Niaga Virtual Account',

        'danamon_va' =>
            'Danamon Virtual Account',

        'bsi_va' =>
            'BSI Virtual Account',

        'seabank_va' =>
            'SeaBank Virtual Account',

        'saqu_va' =>
            'Bank Saqu Virtual Account',

        'permata_va' =>
            'Permata Virtual Account',

        'echannel' =>
            'Mandiri Bill Payment',

        'gopay' =>
            'GoPay',

        'ovo' =>
            'OVO',

        'dana' =>
            'DANA',

        'shopeepay' =>
            'ShopeePay',

        'other_qris' =>
            'QRIS',

        'alfamart' =>
            'Alfamart',

        'indomaret' =>
            'Indomaret',

        'akulaku' =>
            'Akulaku',

        'kredivo' =>
            'Kredivo',

    ];


    $normalizedPaymentType =
        strtolower(
            trim(
                (string) $paymentType
            )
        );


    $paymentName =
        $paymentLabels[
            $normalizedPaymentType
        ]
        ??
        ucwords(
            str_replace(
                '_',
                ' ',
                $normalizedPaymentType
            )
        );


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

            'paymentType' =>
                $normalizedPaymentType,

            'paymentName' =>
                $paymentName,
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