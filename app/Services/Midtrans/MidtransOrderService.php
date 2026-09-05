<?php

namespace App\Services\Midtrans;

use App\Models\MidtransTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MidtransOrderService
{
    public function __construct(
        protected MidtransService $midtrans
    ) {
    }

    /**
     * =========================================================
     * CREATE / RESOLVE SNAP TRANSACTION
     * =========================================================
     */
    public function createForOrder(
        Order $order
    ): MidtransTransaction {

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONS
        |--------------------------------------------------------------------------
        */

        $order->loadMissing([
            'details.item',
            'game',
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDATE ORDER
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
            throw new RuntimeException(
                'Order tidak dapat dibuatkan pembayaran Midtrans. '
                . 'Status saat ini: '
                . $order->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | APPLICATION LOCK
        |--------------------------------------------------------------------------
        */

        $lock = Cache::lock(
            'midtrans-create-snap:' . $order->id,
            120
        );

        $lockAcquired = false;

        try {

            if (!$lock->get()) {
                throw new RuntimeException(
                    'Pembuatan pembayaran Midtrans untuk order ini '
                    . 'sedang diproses. Silakan coba kembali.'
                );
            }

            $lockAcquired = true;

            /*
            |--------------------------------------------------------------------------
            | GET / CREATE LATEST ATTEMPT
            |--------------------------------------------------------------------------
            */

            $transaction =
                $this->getOrCreateLatestAttempt(
                    $order
                );

            /*
            |--------------------------------------------------------------------------
            | EXISTING SNAP TOKEN
            |--------------------------------------------------------------------------
            |
            | Jika token sudah ada, cek status Midtrans.
            |
            */

            if (
                !empty(
                    $transaction->snap_token
                )
            ) {

                $statusResponse =
                    $this->midtrans
                        ->getTransactionStatus(
                            $transaction
                                ->midtrans_order_id
                        );

                /*
                |--------------------------------------------------------------------------
                | MIDTRANS 404
                |--------------------------------------------------------------------------
                |
                | Transaction status belum tersedia.
                |
                | Ini normal untuk Snap yang tokennya sudah dibuat
                | tetapi belum digunakan customer.
                |
                | Selama Snap Token lokal masih ada, token digunakan kembali.
                |
                */

                if ($statusResponse === null) {

                    Log::info(
                        'Midtrans transaction belum memiliki status. '
                        . 'Snap Token existing tetap digunakan.',
                        [
                            'order_id' =>
                                $order->id,

                            'attempt_number' =>
                                $transaction
                                    ->attempt_number,

                            'midtrans_order_id' =>
                                $transaction
                                    ->midtrans_order_id,

                            'snap_token_exists' =>
                                true,
                        ]
                    );

                    return $transaction->fresh();
                }

                /*
                |--------------------------------------------------------------------------
                | GET STATUS
                |--------------------------------------------------------------------------
                */

                $transactionStatus =
                    strtolower(
                        trim(
                            (string)
                            data_get(
                                $statusResponse,
                                'transaction_status'
                            )
                        )
                    );

                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $transactionStatus,
                        [
                            'capture',
                            'settlement',
                        ],
                        true
                    )
                ) {

                    Log::info(
                        'Midtrans payment attempt sudah berhasil.',
                        [
                            'order_id' =>
                                $order->id,

                            'attempt_number' =>
                                $transaction
                                    ->attempt_number,

                            'midtrans_order_id' =>
                                $transaction
                                    ->midtrans_order_id,

                            'transaction_status' =>
                                $transactionStatus,
                        ]
                    );

                    return $transaction->fresh();
                }

                /*
                |--------------------------------------------------------------------------
                | PENDING
                |--------------------------------------------------------------------------
                */

                if (
                    $transactionStatus === 'pending'
                ) {

                    Log::info(
                        'Midtrans payment attempt masih pending. '
                        . 'Snap Token existing digunakan.',
                        [
                            'order_id' =>
                                $order->id,

                            'attempt_number' =>
                                $transaction
                                    ->attempt_number,

                            'midtrans_order_id' =>
                                $transaction
                                    ->midtrans_order_id,
                        ]
                    );

                    return $transaction->fresh();
                }

                /*
                |--------------------------------------------------------------------------
                | CLOSED TRANSACTION
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $transactionStatus,
                        [
                            'expire',
                            'cancel',
                            'deny',
                            'failure',
                        ],
                        true
                    )
                ) {

                    Log::info(
                        'Midtrans payment attempt sudah ditutup. '
                        . 'Membuat attempt pembayaran berikutnya.',
                        [
                            'order_id' =>
                                $order->id,

                            'previous_attempt_number' =>
                                $transaction
                                    ->attempt_number,

                            'previous_midtrans_order_id' =>
                                $transaction
                                    ->midtrans_order_id,

                            'transaction_status' =>
                                $transactionStatus,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | SAVE OLD ATTEMPT
                    |--------------------------------------------------------------------------
                    */

                    $transaction->update([
                        'transaction_id' =>
                            data_get(
                                $statusResponse,
                                'transaction_id'
                            ),

                        'transaction_status' =>
                            $transactionStatus,

                        'payment_type' =>
                            data_get(
                                $statusResponse,
                                'payment_type'
                            ),

                        'fraud_status' =>
                            data_get(
                                $statusResponse,
                                'fraud_status'
                            ),

                        'response_payload' =>
                            $statusResponse,

                        'expired_at' =>
                            $transactionStatus === 'expire'
                                ? now()
                                : $transaction->expired_at,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE NEXT ATTEMPT
                    |--------------------------------------------------------------------------
                    */

                    $transaction =
                        $this->createNextAttempt(
                            $order,
                            $transaction
                        );

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | UNKNOWN STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionStatus === ''
                    ) {
                        throw new RuntimeException(
                            'Midtrans mengembalikan status transaksi kosong.'
                        );
                    }

                    throw new RuntimeException(
                        'Status transaksi Midtrans tidak dikenali: '
                        . $transactionStatus
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | REFRESH AFTER ATTEMPT RESOLUTION
            |--------------------------------------------------------------------------
            */

            $transaction->refresh();

            /*
            |--------------------------------------------------------------------------
            | SAFETY CHECK
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $transaction->snap_token
                )
            ) {

                Log::info(
                    'Snap Token sudah tersedia setelah refresh.',
                    [
                        'order_id' =>
                            $order->id,

                        'attempt_number' =>
                            $transaction
                                ->attempt_number,

                        'midtrans_order_id' =>
                            $transaction
                                ->midtrans_order_id,
                    ]
                );

                return $transaction;
            }

            /*
            |--------------------------------------------------------------------------
            | RELOAD ORDER
            |--------------------------------------------------------------------------
            */

            $transactionOrder =
                Order::query()
                    ->with([
                        'details.item',
                        'game',
                        'user',
                    ])
                    ->findOrFail(
                        $transaction->order_id
                    );

            /*
            |--------------------------------------------------------------------------
            | RECHECK ORDER STATUS
            |--------------------------------------------------------------------------
            */

            if (
                !in_array(
                    $transactionOrder->status,
                    [
                        'Pending',
                        'Waiting Payment',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Order tidak dapat dibuatkan pembayaran Midtrans. '
                    . 'Status saat ini: '
                    . $transactionOrder->status
                );
            }

            /*
            |--------------------------------------------------------------------------
            | BUILD SNAP PARAMS
            |--------------------------------------------------------------------------
            */

            $params =
                $this->buildSnapParams(
                    $transactionOrder,
                    $transaction
                );

            /*
            |--------------------------------------------------------------------------
            | CREATE SNAP TOKEN
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Membuat Snap Token Midtrans.',
                [
                    'order_id' =>
                        $transactionOrder->id,

                    'attempt_number' =>
                        $transaction
                            ->attempt_number,

                    'midtrans_order_id' =>
                        $transaction
                            ->midtrans_order_id,
                ]
            );

            $snapToken =
                $this->midtrans
                    ->createSnapToken(
                        $params
                    );

            /*
            |--------------------------------------------------------------------------
            | VALIDATE TOKEN
            |--------------------------------------------------------------------------
            */

            if (
                empty($snapToken)
            ) {
                throw new RuntimeException(
                    'Midtrans tidak mengembalikan Snap Token.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE TOKEN
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $transaction,
                    $snapToken,
                    $params
                ) {

                    $lockedTransaction =
                        MidtransTransaction::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $transaction->id
                            );

                    if (
                        empty(
                            $lockedTransaction
                                ->snap_token
                        )
                    ) {

                        $lockedTransaction->update([
                            'snap_token' =>
                                $snapToken,

                            'request_payload' =>
                                $params,

                            'transaction_id' =>
                                null,

                            'transaction_status' =>
                                null,

                            'payment_type' =>
                                null,

                            'fraud_status' =>
                                null,

                            'paid_at' =>
                                null,

                            'expired_at' =>
                                null,
                        ]);

                    } else {

                        Log::info(
                            'Snap Token sudah disimpan oleh proses lain.',
                            [
                                'midtrans_transaction_id' =>
                                    $lockedTransaction->id,

                                'midtrans_order_id' =>
                                    $lockedTransaction
                                        ->midtrans_order_id,
                            ]
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE ORDER STATUS
                    |--------------------------------------------------------------------------
                    */

                    $lockedOrder =
                        Order::query()
                            ->lockForUpdate()
                            ->find(
                                $lockedTransaction
                                    ->order_id
                            );

                    if (
                        $lockedOrder &&
                        $lockedOrder->status === 'Pending'
                    ) {

                        $lockedOrder->update([
                            'status' =>
                                'Waiting Payment',
                        ]);
                    }
                }
            );

            /*
            |--------------------------------------------------------------------------
            | FINAL RESULT
            |--------------------------------------------------------------------------
            */

            $result =
                MidtransTransaction::query()
                    ->findOrFail(
                        $transaction->id
                    );

            if (
                empty(
                    $result->snap_token
                )
            ) {
                throw new RuntimeException(
                    'Snap Token Midtrans berhasil dibuat tetapi gagal disimpan.'
                );
            }

            Log::info(
                'Midtrans payment attempt berhasil dibuat.',
                [
                    'order_id' =>
                        $result->order_id,

                    'attempt_number' =>
                        $result->attempt_number,

                    'midtrans_transaction_id' =>
                        $result->id,

                    'midtrans_order_id' =>
                        $result->midtrans_order_id,
                ]
            );

            return $result;

        } catch (Throwable $e) {

            Log::error(
                'Gagal membuat Midtrans transaction.',
                [
                    'order_id' =>
                        $order->id,

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );

            throw $e;

        } finally {

            if ($lockAcquired) {
                $lock->release();
            }
        }
    }


    /**
     * =========================================================
     * GET OR CREATE LATEST ATTEMPT
     * =========================================================
     */
    protected function getOrCreateLatestAttempt(
        Order $order
    ): MidtransTransaction {

        return DB::transaction(
            function () use ($order) {

                $lockedOrder =
                    Order::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $order->id
                        );

                if (
                    !in_array(
                        $lockedOrder->status,
                        [
                            'Pending',
                            'Waiting Payment',
                        ],
                        true
                    )
                ) {
                    throw new RuntimeException(
                        'Order tidak dapat dibuatkan pembayaran Midtrans. '
                        . 'Status saat ini: '
                        . $lockedOrder->status
                    );
                }

                $latest =
                    MidtransTransaction::query()
                        ->where(
                            'order_id',
                            $lockedOrder->id
                        )
                        ->lockForUpdate()
                        ->orderByDesc(
                            'attempt_number'
                        )
                        ->first();

                if ($latest) {
                    return $latest;
                }

                /*
                |--------------------------------------------------------------------------
                | ATTEMPT #1
                |--------------------------------------------------------------------------
                */

                $transaction =
                    MidtransTransaction::create([
                        'order_id' =>
                            $lockedOrder->id,

                        'attempt_number' =>
                            1,

                        'midtrans_order_id' =>
                            $lockedOrder
                                ->invoice_number
                            . '-MT1',

                        'gross_amount' =>
                            $lockedOrder
                                ->total_price,
                    ]);

                Log::info(
                    'Midtrans payment attempt #1 dibuat.',
                    [
                        'order_id' =>
                            $lockedOrder->id,

                        'attempt_number' =>
                            1,

                        'midtrans_order_id' =>
                            $transaction
                                ->midtrans_order_id,
                    ]
                );

                return $transaction;
            }
        );
    }


    /**
     * =========================================================
     * CREATE NEXT PAYMENT ATTEMPT
     * =========================================================
     */
    protected function createNextAttempt(
        Order $order,
        MidtransTransaction $previous
    ): MidtransTransaction {

        return DB::transaction(
            function () use (
                $order,
                $previous
            ) {

                $lockedOrder =
                    Order::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $order->id
                        );

                $latest =
                    MidtransTransaction::query()
                        ->where(
                            'order_id',
                            $lockedOrder->id
                        )
                        ->lockForUpdate()
                        ->orderByDesc(
                            'attempt_number'
                        )
                        ->first();

                /*
                |--------------------------------------------------------------------------
                | ANOTHER PROCESS ALREADY CREATED NEXT ATTEMPT
                |--------------------------------------------------------------------------
                */

                if (
                    $latest &&
                    $latest->id !==
                    $previous->id
                ) {
                    return $latest;
                }

                $nextAttemptNumber =
                    (
                        (int)
                        $previous->attempt_number
                    ) + 1;

                $midtransOrderId =
                    $lockedOrder
                        ->invoice_number
                    . '-MT'
                    . $nextAttemptNumber;

                $transaction =
                    MidtransTransaction::create([
                        'order_id' =>
                            $lockedOrder->id,

                        'attempt_number' =>
                            $nextAttemptNumber,

                        'midtrans_order_id' =>
                            $midtransOrderId,

                        'gross_amount' =>
                            $lockedOrder
                                ->total_price,

                        'snap_token' =>
                            null,

                        'transaction_id' =>
                            null,

                        'transaction_status' =>
                            null,

                        'payment_type' =>
                            null,

                        'fraud_status' =>
                            null,

                        'paid_at' =>
                            null,

                        'expired_at' =>
                            null,
                    ]);

                Log::info(
                    'Midtrans payment attempt baru dibuat.',
                    [
                        'order_id' =>
                            $lockedOrder->id,

                        'attempt_number' =>
                            $nextAttemptNumber,

                        'previous_attempt_number' =>
                            $previous
                                ->attempt_number,

                        'midtrans_order_id' =>
                            $midtransOrderId,
                    ]
                );

                return $transaction;
            }
        );
    }


    /**
     * =========================================================
     * BUILD SNAP PARAMS
     * =========================================================
     */
    protected function buildSnapParams(
        Order $order,
        MidtransTransaction $transaction
    ): array {

        $itemDetails = [];

        /*
        |--------------------------------------------------------------------------
        | ORDER DETAILS
        |--------------------------------------------------------------------------
        */

        foreach (
            $order->details as $detail
        ) {

            $price =
                (int)
                round(
                    (float)
                    $detail->price
                );

            $quantity =
                (int)
                $detail->qty;

            if (
                $price <= 0 ||
                $quantity <= 0
            ) {
                throw new RuntimeException(
                    'Detail order memiliki harga atau quantity yang tidak valid.'
                );
            }

            $itemDetails[] = [
                'id' =>
                    (string)
                    $detail->item_id,

                'price' =>
                    $price,

                'quantity' =>
                    $quantity,

                'name' =>
                    $detail
                        ->item
                        ?->item_name
                    ?? 'Top Up Item',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK ITEM
        |--------------------------------------------------------------------------
        */

        if (
            empty($itemDetails)
        ) {

            $itemDetails[] = [
                'id' =>
                    'ORDER-' .
                    $order->id,

                'price' =>
                    (int)
                    round(
                        (float)
                        $transaction
                            ->gross_amount
                    ),

                'quantity' =>
                    1,

                'name' =>
                    'Game Top Up',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | GROSS AMOUNT
        |--------------------------------------------------------------------------
        */

        $grossAmount =
            (int)
            round(
                (float)
                $transaction
                    ->gross_amount
            );

        if (
            $grossAmount <= 0
        ) {
            throw new RuntimeException(
                'Gross amount transaksi Midtrans tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ITEM TOTAL
        |--------------------------------------------------------------------------
        */

        $itemTotal = 0;

        foreach (
            $itemDetails as $item
        ) {

            $itemTotal +=
                (
                    (int)
                    $item['price']
                )
                *
                (
                    (int)
                    $item['quantity']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ADJUSTMENT
        |--------------------------------------------------------------------------
        */

        $difference =
            $grossAmount -
            $itemTotal;

        if (
            $difference !== 0
        ) {

            $itemDetails[] = [
                'id' =>
                    'ADJUSTMENT-' .
                    $order->id,

                'price' =>
                    $difference,

                'quantity' =>
                    1,

                'name' =>
                    $difference > 0
                        ? 'Additional Charge'
                        : 'Discount',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL VALIDATION
        |--------------------------------------------------------------------------
        */

        $finalItemTotal = 0;

        foreach (
            $itemDetails as $item
        ) {

            $finalItemTotal +=
                (
                    (int)
                    $item['price']
                )
                *
                (
                    (int)
                    $item['quantity']
                );
        }

        if (
            $finalItemTotal !==
            $grossAmount
        ) {
            throw new RuntimeException(
                'Total item Midtrans tidak sama dengan gross amount.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        |--------------------------------------------------------------------------
        */

        $customer = [];

        if (
            $order->user
        ) {

            $customer['first_name'] =
                $order->user->name;

            if (
                $order->user->email
            ) {
                $customer['email'] =
                    $order->user->email;
            }

        } else {

            $customer['first_name'] =
                $order->guest_name
                ?? 'Guest Customer';

            if (
                $order->guest_email
            ) {
                $customer['email'] =
                    $order->guest_email;
            }

            if (
                $order->guest_phone
            ) {
                $customer['phone'] =
                    $order->guest_phone;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SNAP PARAMS
        |--------------------------------------------------------------------------
        |
        | callbacks.finish menentukan halaman setelah customer selesai
        | menggunakan Snap.
        |
        */

        return [
            'transaction_details' => [
                'order_id' =>
                    $transaction
                        ->midtrans_order_id,

                'gross_amount' =>
                    $grossAmount,
            ],

            'item_details' =>
                $itemDetails,

            'customer_details' =>
                $customer,

            'callbacks' => [
                'finish' =>
                    route(
                        'midtrans.result',
                        [
                            'order' =>
                                $order->id,
                        ]
                    ),
            ],
        ];
    }
}