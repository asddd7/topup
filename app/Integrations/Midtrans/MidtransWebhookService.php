<?php

namespace App\Integrations\Midtrans;

use App\Models\MidtransTransaction;
use App\Models\Order;
use App\Services\TopUp\TopUpFulfillmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MidtransWebhookService
{
    public function __construct(
        protected MidtransOrderService $midtransOrderService,
        protected TopUpFulfillmentService $fulfillmentService
    ) {
    }

    /**
     * =========================================================
     * HANDLE WEBHOOK
     * =========================================================
     */
    public function handle(
        array $payload
    ): array {

        /*
        |--------------------------------------------------------------------------
        | REQUIRED DATA
        |--------------------------------------------------------------------------
        */

        $midtransOrderId =
            trim(
                (string) data_get(
                    $payload,
                    'order_id'
                )
            );

        $transactionStatus =
            $this->normalizeStatus(
                data_get(
                    $payload,
                    'transaction_status'
                )
            );

        if (
            $midtransOrderId === ''
        ) {
            throw new RuntimeException(
                'Webhook Midtrans tidak memiliki order_id.'
            );
        }

        if (
            $transactionStatus === ''
        ) {
            throw new RuntimeException(
                'Webhook Midtrans tidak memiliki transaction_status.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PROCESS DATABASE
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

                    if (
                        !$transaction
                    ) {
                        throw new RuntimeException(
                            'Midtrans transaction tidak ditemukan untuk order_id: '
                            . $midtransOrderId
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

                    if (
                        !$order
                    ) {
                        throw new RuntimeException(
                            'Order tidak ditemukan untuk Midtrans transaction.'
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
                        round(
                            $payloadGrossAmount,
                            2
                        )
                        !==
                        round(
                            $transactionGrossAmount,
                            2
                        )
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
                    | UPDATE MIDTRANS TRANSACTION
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

                    if (
                        $this->midtransOrderService
                            ->isSuccessfulTransactionStatus(
                                $transactionStatus,
                                data_get(
                                    $payload,
                                    'fraud_status'
                                )
                            )
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | PAID AT
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$transaction->paid_at
                        ) {
                            $transaction->update([
                                'paid_at' =>
                                    now(),
                            ]);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | ONLY TRANSITION TO PAID ONCE
                        |--------------------------------------------------------------------------
                        */

                        $becamePaid = false;

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

                            $becamePaid = true;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | PAYMENT SUCCESS BUT ORDER ALREADY MOVED
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$becamePaid
                            &&
                            !in_array(
                                $order->status,
                                [
                                    'Paid',
                                    'Processing',
                                    'Completed',
                                ],
                                true
                            )
                        ) {
                            Log::warning(
                                'Midtrans payment berhasil tetapi status Order tidak sesuai.',
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
                        }

                        return [
                            'status' =>
                                'success',

                            'order_id' =>
                                $order->id,

                            'transaction_id' =>
                                $transaction->id,

                            'became_paid' =>
                                $becamePaid,
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

                        if (
                            $order->status ===
                            'Pending'
                        ) {
                            $order->update([
                                'status' =>
                                    'Waiting Payment',
                            ]);
                        }

                        return [
                            'status' =>
                                'pending',

                            'order_id' =>
                                $order->id,

                            'transaction_id' =>
                                $transaction->id,

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

                        /*
                        |--------------------------------------------------------------------------
                        | EXPIRE
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $transactionStatus ===
                            'expire'
                        ) {
                            $transaction->update([
                                'expired_at' =>
                                    $transaction->expired_at
                                    ?? now(),
                            ]);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | JANGAN DOWNGRADE ORDER YANG SUDAH FULFILLMENT
                        |--------------------------------------------------------------------------
                        */

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
                                    'Cancelled',
                            ]);
                        }

                        return [
                            'status' =>
                                'closed',

                            'order_id' =>
                                $order->id,

                            'transaction_id' =>
                                $transaction->id,

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
                        'Webhook Midtrans menerima status yang tidak dipetakan.',
                        [
                            'midtrans_order_id' =>
                                $midtransOrderId,

                            'transaction_status' =>
                                $transactionStatus,

                            'order_id' =>
                                $order->id,
                        ]
                    );

                    return [
                        'status' =>
                            'unknown',

                        'order_id' =>
                            $order->id,

                        'transaction_id' =>
                            $transaction->id,

                        'became_paid' =>
                            false,
                    ];
                }
            );

        /*
        |--------------------------------------------------------------------------
        | AFTER DATABASE COMMIT
        |--------------------------------------------------------------------------
        |
        | Hanya dispatch fulfillment ketika Order BENAR-BENAR baru
        | berpindah menjadi Paid.
        |--------------------------------------------------------------------------
        */

        $fulfillmentResults = [];

        if (
            $result['became_paid'] === true
        ) {

            $order =
                Order::query()
                    ->find(
                        $result['order_id']
                    );

            if (
                !$order
            ) {
                Log::warning(
                    'Order tidak ditemukan setelah Midtrans menjadi Paid.',
                    [
                        'order_id' =>
                            $result['order_id'],
                    ]
                );
            } elseif (
                $order->status !== 'Paid'
            ) {
                Log::warning(
                    'Fulfillment tidak didispatch karena Order bukan Paid.',
                    [
                        'order_id' =>
                            $order->id,

                        'status' =>
                            $order->status,
                    ]
                );
            } else {

                /*
                |--------------------------------------------------------------------------
                | GENERIC FULFILLMENT
                |--------------------------------------------------------------------------
                |
                | Provider ditentukan oleh:
                | TopUpFulfillmentService
                |        ↓
                | TopUpProviderRegistry
                |        ↓
                | MooGoldProvider / DitusiProvider
                |--------------------------------------------------------------------------
                */

                $fulfillmentResults =
                    $this->fulfillmentService
                        ->dispatchOrder(
                            $order
                        );

                Log::info(
                    'Fulfillment order didispatch setelah Midtrans Paid.',
                    [
                        'order_id' =>
                            $order->id,

                        'results' =>
                            $fulfillmentResults,
                    ]
                );
            }
        }

        return [
            'status' =>
                $result['status'],

            'order_id' =>
                $result['order_id'],

            'transaction_id' =>
                $result['transaction_id'],

            'became_paid' =>
                $result['became_paid'],

            'fulfillment' =>
                $fulfillmentResults,
        ];
    }

    /**
     * =========================================================
     * NORMALIZE STATUS
     * =========================================================
     */
    protected function normalizeStatus(
        mixed $status
    ): string {
        return strtolower(
            trim(
                (string) $status
            )
        );
    }
}