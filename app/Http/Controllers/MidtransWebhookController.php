<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMooGoldOrder;
use App\Models\MidtransTransaction;
use App\Models\Order;
use App\Services\Midtrans\MidtransOrderService;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtrans,
        protected MidtransOrderService $midtransOrderService
    ) {
    }

    /**
     * =========================================================
     * HANDLE MIDTRANS WEBHOOK
     * =========================================================
     *
     * Payment flow:
     *
     * Midtrans
     *      ↓
     * verify signature
     *      ↓
     * find MidtransTransaction
     *      ↓
     * validate gross amount
     *      ↓
     * save notification
     *      ↓
     * settlement / capture
     *      ↓
     * Order = Paid
     *      ↓
     * COMMIT
     *      ↓
     * ProcessMooGoldOrder
     */
    public function handle(
        Request $request
    ): JsonResponse {
        $payload = $request->all();

        $midtransOrderId =
            trim(
                (string) data_get(
                    $payload,
                    'order_id'
                )
            );

        $transactionStatus =
            strtolower(
                trim(
                    (string) data_get(
                        $payload,
                        'transaction_status'
                    )
                )
            );

        Log::info(
            'Midtrans webhook diterima.',
            [
                'order_id' =>
                    $midtransOrderId,

                'transaction_status' =>
                    $transactionStatus,

                'transaction_id' =>
                    data_get(
                        $payload,
                        'transaction_id'
                    ),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | REQUIRED DATA
        |--------------------------------------------------------------------------
        */

        if ($midtransOrderId === '') {
            return response()->json(
                [
                    'success' => false,
                    'message' =>
                        'order_id tidak ditemukan.',
                ],
                422
            );
        }

        if ($transactionStatus === '') {
            return response()->json(
                [
                    'success' => false,
                    'message' =>
                        'transaction_status tidak ditemukan.',
                ],
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY SIGNATURE
        |--------------------------------------------------------------------------
        */

        if (
            !$this->midtrans->verifySignature(
                $payload
            )
        ) {
            Log::warning(
                'Midtrans webhook signature tidak valid.',
                [
                    'order_id' =>
                        $midtransOrderId,

                    'transaction_status' =>
                        $transactionStatus,
                ]
            );

            return response()->json(
                [
                    'success' => false,
                    'message' =>
                        'Invalid signature.',
                ],
                403
            );
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | PROCESS PAYMENT
            |--------------------------------------------------------------------------
            */

            $result =
                DB::transaction(
                    function () use (
                        $payload,
                        $midtransOrderId,
                        $transactionStatus
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | LOCK MIDTRANS TRANSACTION
                        |--------------------------------------------------------------------------
                        */

                        $transaction =
                            MidtransTransaction::query()
                                ->where(
                                    'midtrans_order_id',
                                    $midtransOrderId
                                )
                                ->lockForUpdate()
                                ->first();

                        if (!$transaction) {
                            Log::warning(
                                'Midtrans webhook untuk transaction yang tidak ditemukan.',
                                [
                                    'midtrans_order_id' =>
                                        $midtransOrderId,
                                ]
                            );

                            throw new RuntimeException(
                                'Midtrans transaction tidak ditemukan.'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | LOCK ORDER
                        |--------------------------------------------------------------------------
                        */

                        $order =
                            Order::query()
                                ->lockForUpdate()
                                ->find(
                                    $transaction->order_id
                                );

                        if (!$order) {
                            throw new RuntimeException(
                                'Order terkait Midtrans transaction tidak ditemukan.'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATE GROSS AMOUNT
                        |--------------------------------------------------------------------------
                        */

                        $payloadGrossAmount =
                            (float) data_get(
                                $payload,
                                'gross_amount'
                            );

                        $transactionGrossAmount =
                            (float) $transaction->gross_amount;

                        if (
                            round($payloadGrossAmount, 2)
                            !==
                            round($transactionGrossAmount, 2)
                        ) {
                            Log::error(
                                'Midtrans gross amount tidak sesuai.',
                                [
                                    'order_id' =>
                                        $order->id,

                                    'midtrans_order_id' =>
                                        $midtransOrderId,

                                    'payload_gross_amount' =>
                                        $payloadGrossAmount,

                                    'transaction_gross_amount' =>
                                        $transactionGrossAmount,
                                ]
                            );

                            throw new RuntimeException(
                                'Gross amount Midtrans tidak sesuai.'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SAVE WEBHOOK
                        |--------------------------------------------------------------------------
                        */

                        $transaction->update([
                            'transaction_id' =>
                                data_get(
                                    $payload,
                                    'transaction_id'
                                ),

                            'transaction_status' =>
                                $transactionStatus,

                            'payment_type' =>
                                data_get(
                                    $payload,
                                    'payment_type'
                                ),

                            'fraud_status' =>
                                data_get(
                                    $payload,
                                    'fraud_status'
                                ),

                            'notification_payload' =>
                                $payload,

                            'response_payload' =>
                                $payload,
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | SUCCESS
                        |--------------------------------------------------------------------------
                        */

                        $isSuccessful =
                            $this->midtransOrderService
                                ->isSuccessfulTransactionStatus(
                                    $transactionStatus,
                                    data_get(
                                        $payload,
                                        'fraud_status'
                                    )
                                );

                        if ($isSuccessful) {
                            /*
                            |--------------------------------------------------------------------------
                            | PAID TIME
                            |--------------------------------------------------------------------------
                            */

                            if (!$transaction->paid_at) {
                                $transaction->update([
                                    'paid_at' =>
                                        now(),
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | ORDER → PAID
                            |--------------------------------------------------------------------------
                            */

                            if ($order->status === 'Paid') {
                                return [
                                    'status' =>
                                        'success',

                                    'order_id' =>
                                        $order->id,

                                    'became_paid' =>
                                        false,
                                ];
                            }

                            if (
                                in_array(
                                    $order->status,
                                    [
                                        'Pending',
                                        'Waiting Payment',
                                    ],
                                    true
                                )
                            ) {
                                $order->update([
                                    'status' =>
                                        'Paid',
                                ]);

                                return [
                                    'status' =>
                                        'success',

                                    'order_id' =>
                                        $order->id,

                                    'became_paid' =>
                                        true,
                                ];
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | PAYMENT SUCCEEDED BUT ORDER ALREADY MOVED
                            |--------------------------------------------------------------------------
                            */

                            Log::warning(
                                'Midtrans payment berhasil tetapi status Order sudah berubah.',
                                [
                                    'order_id' =>
                                        $order->id,

                                    'order_status' =>
                                        $order->status,

                                    'midtrans_order_id' =>
                                        $midtransOrderId,

                                    'transaction_status' =>
                                        $transactionStatus,
                                ]
                            );

                            return [
                                'status' =>
                                    'success',

                                'order_id' =>
                                    $order->id,

                                'became_paid' =>
                                    false,
                            ];
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | PENDING
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $this->midtransOrderService
                                ->isPendingTransactionStatus(
                                    $transactionStatus
                                )
                        ) {
                            return [
                                'status' =>
                                    'pending',

                                'order_id' =>
                                    $order->id,

                                'became_paid' =>
                                    false,
                            ];
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | CLOSED
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $this->midtransOrderService
                                ->isClosedTransactionStatus(
                                    $transactionStatus
                                )
                        ) {
                            if (
                                $transactionStatus ===
                                'expire'
                            ) {
                                $transaction->update([
                                    'expired_at' =>
                                        now(),
                                ]);
                            }

                            return [
                                'status' =>
                                    'closed',

                                'order_id' =>
                                    $order->id,

                                'became_paid' =>
                                    false,
                            ];
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | UNKNOWN STATUS
                        |--------------------------------------------------------------------------
                        */

                        Log::warning(
                            'Midtrans webhook mengirim status yang tidak dikenali.',
                            [
                                'midtrans_order_id' =>
                                    $midtransOrderId,

                                'transaction_status' =>
                                    $transactionStatus,
                            ]
                        );

                        return [
                            'status' =>
                                'unknown',

                            'order_id' =>
                                $order->id,

                            'became_paid' =>
                                false,
                        ];
                    }
                );

            /*
            |--------------------------------------------------------------------------
            | DISPATCH MOOGOLD ONLY AFTER COMMIT
            |--------------------------------------------------------------------------
            */

            if (
                $result['status'] === 'success'
                &&
                $result['became_paid'] === true
            ) {
                $order =
                    Order::query()
                        ->with('details')
                        ->findOrFail(
                            $result['order_id']
                        );

                /*
                |--------------------------------------------------------------------------
                | FINAL SAFETY CHECK
                |--------------------------------------------------------------------------
                */

                if ($order->status !== 'Paid') {
                    Log::warning(
                        'ProcessMooGoldOrder tidak didispatch karena Order bukan Paid.',
                        [
                            'order_id' =>
                                $order->id,

                            'status' =>
                                $order->status,
                        ]
                    );

                    return response()->json([
                        'success' =>
                            true,

                        'status' =>
                            'success',
                    ]);
                }

                foreach ($order->details as $detail) {
                    ProcessMooGoldOrder::dispatch(
                        $detail->id
                    );

                    Log::info(
                        'ProcessMooGoldOrder didispatch setelah Order menjadi Paid.',
                        [
                            'order_id' =>
                                $order->id,

                            'order_detail_id' =>
                                $detail->id,
                        ]
                    );
                }
            }

            return response()->json([
                'success' =>
                    true,

                'status' =>
                    $result['status'],
            ]);

        } catch (Throwable $e) {
            Log::error(
                'Gagal memproses Midtrans webhook.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'transaction_status' =>
                        $transactionStatus,

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | NON-2XX
            |--------------------------------------------------------------------------
            |
            | Supaya Midtrans dapat melakukan retry notification.
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Webhook processing failed.',
                ],
                500
            );
        }
    }
}