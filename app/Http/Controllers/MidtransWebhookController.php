<?php

namespace App\Http\Controllers;

use App\Services\Midtrans\MidtransWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransWebhookService $service
    ) {
    }


    /**
     * =========================================================
     * MIDTRANS PAYMENT WEBHOOK
     * =========================================================
     */
    public function handle(
        Request $request
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | GET PAYLOAD
        |--------------------------------------------------------------------------
        */

        $payload =
            $request->all();


        /*
        |--------------------------------------------------------------------------
        | LOG WEBHOOK RECEIVED
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Webhook Midtrans diterima.',
            [
                'order_id' =>
                    data_get(
                        $payload,
                        'order_id'
                    ),

                'transaction_status' =>
                    data_get(
                        $payload,
                        'transaction_status'
                    ),

                'transaction_id' =>
                    data_get(
                        $payload,
                        'transaction_id'
                    ),
            ]
        );


        try {

            /*
            |--------------------------------------------------------------------------
            | HANDLE WEBHOOK
            |--------------------------------------------------------------------------
            */

            $transaction =
                $this->service
                    ->handle(
                        $payload
                    );


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Webhook Midtrans berhasil diproses.',

                    'data' =>
                        [
                            'midtrans_transaction_id' =>
                                $transaction->id,

                            'order_id' =>
                                $transaction->order_id,

                            'midtrans_order_id' =>
                                $transaction
                                    ->midtrans_order_id,

                            'transaction_status' =>
                                $transaction
                                    ->transaction_status,
                        ],
                ],
                200
            );

        } catch (
            RuntimeException $e
        ) {

            Log::warning(
                'Webhook Midtrans ditolak.',
                [
                    'order_id' =>
                        data_get(
                            $payload,
                            'order_id'
                        ),

                    'error' =>
                        $e->getMessage(),
                ]
            );


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        $e->getMessage(),
                ],
                400
            );

        } catch (
            Throwable $e
        ) {

            Log::error(
                'Webhook Midtrans gagal diproses.',
                [
                    'order_id' =>
                        data_get(
                            $payload,
                            'order_id'
                        ),

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class(
                            $e
                        ),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | RETURN 500
            |--------------------------------------------------------------------------
            |
            | Midtrans dapat melakukan retry notification.
            |
            */

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Terjadi kesalahan saat memproses webhook Midtrans.',
                ],
                500
            );
        }
    }
}