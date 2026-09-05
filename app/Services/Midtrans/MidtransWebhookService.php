<?php

namespace App\Services\Midtrans;

use App\Jobs\ProcessMooGoldOrder;
use App\Models\MidtransTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MidtransWebhookService
{
    /**
     * =========================================================
     * HANDLE WEBHOOK
     * =========================================================
     */
    public function handle(
        array $payload
    ): MidtransTransaction {

        /*
        |--------------------------------------------------------------------------
        | VALIDATE REQUIRED PAYLOAD
        |--------------------------------------------------------------------------
        */

        $midtransOrderId =
            trim(
                (string) data_get(
                    $payload,
                    'order_id'
                )
            );

        if (
            $midtransOrderId === ''
        ) {
            throw new RuntimeException(
                'Webhook Midtrans tidak memiliki order_id.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE SIGNATURE
        |--------------------------------------------------------------------------
        */

        $this->validateSignature(
            $payload
        );


        /*
        |--------------------------------------------------------------------------
        | PROCESS TRANSACTION
        |--------------------------------------------------------------------------
        */

        $result =
            DB::transaction(
                function () use (
                    $payload,
                    $midtransOrderId
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

                    if (!$order) {

                        throw new RuntimeException(
                            'Order tidak ditemukan untuk Midtrans transaction.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NORMALIZE STATUS
                    |--------------------------------------------------------------------------
                    */

                    $transactionStatus =
                        $this->normalizeStatus(
                            data_get(
                                $payload,
                                'transaction_status'
                            )
                        );

                    if (
                        $transactionStatus === ''
                    ) {

                        throw new RuntimeException(
                            'Webhook Midtrans tidak memiliki transaction_status.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT DATA
                    |--------------------------------------------------------------------------
                    */

                    $transactionId =
                        data_get(
                            $payload,
                            'transaction_id'
                        );

                    $paymentType =
                        data_get(
                            $payload,
                            'payment_type'
                        );

                    $fraudStatus =
                        data_get(
                            $payload,
                            'fraud_status'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE MIDTRANS TRANSACTION
                    |--------------------------------------------------------------------------
                    */

                    $transaction->update([

                        'transaction_id' =>
                            $transactionId,

                        'transaction_status' =>
                            $transactionStatus,

                        'payment_type' =>
                            $paymentType,

                        'fraud_status' =>
                            $fraudStatus,

                        'response_payload' =>
                            $payload,

                        'notification_payload' =>
                            $payload,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS: SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->isPaymentSuccess(
                            $transactionStatus,
                            $fraudStatus
                        )
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE PAID AT
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
                        | UPDATE ORDER
                        |--------------------------------------------------------------------------
                        |
                        | Jangan menurunkan status Processing / Completed
                        | apabila webhook dikirim ulang.
                        |
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
                                    'Paid',
                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MARK FOR DISPATCH
                        |--------------------------------------------------------------------------
                        */

                        return [
                            'transaction_id' =>
                                $transaction->id,

                            'order_id' =>
                                $order->id,

                            'should_dispatch' =>
                                true,
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS: PENDING
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus ===
                        'pending'
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
                            'transaction_id' =>
                                $transaction->id,

                            'order_id' =>
                                $order->id,

                            'should_dispatch' =>
                                false,
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS: EXPIRE
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

                        /*
                        |--------------------------------------------------------------------------
                        | Jangan mengubah order yang sudah Paid/Processing
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
                                    'Expired',
                            ]);
                        }

                        return [
                            'transaction_id' =>
                                $transaction->id,

                            'order_id' =>
                                $order->id,

                            'should_dispatch' =>
                                false,
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS: CANCEL / DENY / FAILURE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $transactionStatus,
                            [
                                'cancel',
                                'deny',
                                'failure',
                            ],
                            true
                        )
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Jangan membatalkan order yang sudah Paid
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
                            'transaction_id' =>
                                $transaction->id,

                            'order_id' =>
                                $order->id,

                            'should_dispatch' =>
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
                        'transaction_id' =>
                            $transaction->id,

                        'order_id' =>
                            $order->id,

                        'should_dispatch' =>
                            false,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | RELOAD TRANSACTION
        |--------------------------------------------------------------------------
        */

        $transaction =
            MidtransTransaction::query()
                ->findOrFail(
                    $result['transaction_id']
                );


        /*
        |--------------------------------------------------------------------------
        | DISPATCH MOO GOLD
        |--------------------------------------------------------------------------
        |
        | Dispatch dilakukan SETELAH database transaction commit.
        |
        | Ini penting agar ProcessMooGoldOrder tidak membaca
        | status Order sebelum status Paid benar-benar tersimpan.
        |
        */

        if (
            $result['should_dispatch']
        ) {

            $this->dispatchMooGoldOrders(
                $result['order_id']
            );
        }


        return $transaction;
    }


    /**
     * =========================================================
     * VALIDATE MIDTRANS SIGNATURE
     * =========================================================
     *
     * SHA512(
     *
     * order_id
     * +
     * status_code
     * +
     * gross_amount
     * +
     * server_key
     *
     * )
     */
    protected function validateSignature(
        array $payload
    ): void {

        $orderId =
            (string) data_get(
                $payload,
                'order_id'
            );

        $statusCode =
            (string) data_get(
                $payload,
                'status_code'
            );

        $grossAmount =
            (string) data_get(
                $payload,
                'gross_amount'
            );

        $signatureKey =
            (string) data_get(
                $payload,
                'signature_key'
            );

        if (
            $orderId === ''
            ||
            $statusCode === ''
            ||
            $grossAmount === ''
            ||
            $signatureKey === ''
        ) {

            throw new RuntimeException(
                'Payload webhook Midtrans tidak lengkap untuk validasi signature.'
            );
        }


        $serverKey =
            (string) config(
                'midtrans.server_key'
            );

        if (
            $serverKey === ''
        ) {

            throw new RuntimeException(
                'MIDTRANS_SERVER_KEY belum dikonfigurasi.'
            );
        }


        $expectedSignature =
            hash(
                'sha512',
                $orderId
                . $statusCode
                . $grossAmount
                . $serverKey
            );


        if (
            !hash_equals(
                $expectedSignature,
                $signatureKey
            )
        ) {

            Log::warning(
                'Webhook Midtrans ditolak karena signature tidak valid.',
                [
                    'order_id' =>
                        $orderId,

                    'status_code' =>
                        $statusCode,

                    'gross_amount' =>
                        $grossAmount,
                ]
            );

            throw new RuntimeException(
                'Signature webhook Midtrans tidak valid.'
            );
        }
    }


    /**
     * =========================================================
     * PAYMENT SUCCESS
     * =========================================================
     */
    protected function isPaymentSuccess(
        string $transactionStatus,
        ?string $fraudStatus
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | SETTLEMENT
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus ===
            'settlement'
        ) {
            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | CAPTURE
        |--------------------------------------------------------------------------
        |
        | Untuk transaksi card,
        | capture dianggap berhasil hanya jika fraud status accept.
        |
        */

        if (
            $transactionStatus ===
            'capture'
        ) {

            return strtolower(
                trim(
                    (string)
                    $fraudStatus
                )
            ) === 'accept';
        }


        return false;
    }


    /**
     * =========================================================
     * DISPATCH MOO GOLD
     * =========================================================
     */
    protected function dispatchMooGoldOrders(
        int $orderId
    ): void {

        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER DETAILS
        |--------------------------------------------------------------------------
        */

        $order =
            Order::query()
                ->with([
                    'details',
                ])
                ->find(
                    $orderId
                );

        if (!$order) {

            Log::warning(
                'Order tidak ditemukan saat dispatch MooGold.',
                [
                    'order_id' =>
                        $orderId,
                ]
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SAFETY CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $order->status,
                [
                    'Paid',
                    'Processing',
                ],
                true
            )
        ) {

            Log::warning(
                'Dispatch MooGold dilewati karena status order bukan fulfillment status.',
                [
                    'order_id' =>
                        $order->id,

                    'status' =>
                        $order->status,
                ]
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DISPATCH EACH ORDER DETAIL
        |--------------------------------------------------------------------------
        */

        foreach (
            $order->details as $detail
        ) {

            ProcessMooGoldOrder::dispatch(
                (int) $detail->id
            );

            Log::info(
                'ProcessMooGoldOrder berhasil di-dispatch dari webhook Midtrans.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $detail->id,
                ]
            );
        }
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