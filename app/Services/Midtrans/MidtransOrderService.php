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
     *
     * Flow:
     *
     * Pending / Waiting Payment
     *      ↓
     * latest attempt
     *      ↓
     * existing token?
     *      ├── belum ada status → reuse token
     *      ├── pending → reuse token
     *      ├── settlement/capture → reuse transaction
     *      └── expire/cancel/deny/failure → create next attempt
     *      ↓
     * create Snap token
     */
    public function createForOrder(
        Order $order,
        ?string $paymentType = null
    ): MidtransTransaction {
        $order->loadMissing([
            'details.item',
            'game',
            'user',
        ]);

        $this->ensureOrderCanPay($order);

        $lock = Cache::lock(
            'midtrans-create-snap:' . $order->id,
            120
        );

        $lockAcquired = false;

        try {
            if (!$lock->get()) {
                throw new RuntimeException(
                    'Pembuatan pembayaran Midtrans untuk order ini sedang diproses. '
                    . 'Silakan coba kembali.'
                );
            }

            $lockAcquired = true;

            /*
            |--------------------------------------------------------------------------
            | GET / CREATE LATEST ATTEMPT
            |--------------------------------------------------------------------------
            */

            $transaction =
                $this->getOrCreateLatestAttempt($order);

            /*
            |--------------------------------------------------------------------------
            | RESOLVE EXISTING SNAP TOKEN
            |--------------------------------------------------------------------------
            */

            if (!empty($transaction->snap_token)) {
                $transaction =
                    $this->resolveExistingAttempt(
                        $order,
                        $transaction
                    );

                /*
                |--------------------------------------------------------------------------
                | EXISTING ATTEMPT STILL USABLE
                |--------------------------------------------------------------------------
                */

                if (!empty($transaction->snap_token)) {
                    return $transaction->fresh();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE SNAP TOKEN
            |--------------------------------------------------------------------------
            */

            $transaction->refresh();

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

            $this->ensureOrderCanPay(
                $transactionOrder
            );

            $params =
                $this->buildSnapParams(
                    $transactionOrder,
                    $transaction,
                    $paymentType
                );

            Log::info(
                'Membuat Snap Token Midtrans.',
                [
                    'order_id' =>
                        $transactionOrder->id,

                    'attempt_number' =>
                        $transaction->attempt_number,

                    'midtrans_order_id' =>
                        $transaction->midtrans_order_id,
                ]
            );

            $snapToken =
                $this->midtrans
                    ->createSnapToken($params);

            if (empty($snapToken)) {
                throw new RuntimeException(
                    'Midtrans tidak mengembalikan Snap Token.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE SNAP TOKEN SAFELY
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

                    /*
                    |--------------------------------------------------------------------------
                    | ANOTHER PROCESS ALREADY SAVED TOKEN
                    |--------------------------------------------------------------------------
                    */

                    if (empty($lockedTransaction->snap_token)) {
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

                            'notification_payload' =>
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
                                    $lockedTransaction->midtrans_order_id,
                            ]
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ORDER → WAITING PAYMENT
                    |--------------------------------------------------------------------------
                    */

                    $lockedOrder =
                        Order::query()
                            ->lockForUpdate()
                            ->find(
                                $lockedTransaction->order_id
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

            if (empty($result->snap_token)) {
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
     * RESOLVE EXISTING ATTEMPT
     * =========================================================
     */
protected function resolveExistingAttempt(
    Order $order,
    MidtransTransaction $transaction
): MidtransTransaction {

    /*
    |--------------------------------------------------------------------------
    | DANA
    |--------------------------------------------------------------------------
    |
    | Midtrans mensyaratkan Get Status DANA menggunakan transaction_id.
    |
    | transaction_id baru tersedia setelah webhook/notification.
    |
    | Sebelum itu, Snap Token existing tetap dianggap usable.
    |--------------------------------------------------------------------------
    */

    $paymentType =
        strtolower(
            trim(
                (string) (
                    $transaction->midtrans_payment_type
                    ?? $order->midtrans_payment_type
                    ?? ''
                )
            )
        );


    if (
        $paymentType === 'dana'
        &&
        empty($transaction->transaction_id)
    ) {

        Log::info(
            'DANA belum memiliki transaction_id. '
            . 'Tidak menjalankan Get Status Midtrans. '
            . 'Snap Token existing tetap digunakan.',
            [
                'order_id' =>
                    $order->id,

                'attempt_number' =>
                    $transaction->attempt_number,

                'midtrans_order_id' =>
                    $transaction->midtrans_order_id,

                'payment_type' =>
                    $paymentType,
            ]
        );

        return $transaction;
    }

        $transactionStatus =
            strtolower(
                trim(
                    (string) data_get(
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
            $this->isSuccessfulTransactionStatus(
                $transactionStatus,
                data_get(
                    $statusResponse,
                    'fraud_status'
                )
            )
        ) {
            Log::info(
                'Midtrans payment attempt sudah berhasil.',
                [
                    'order_id' =>
                        $order->id,

                    'attempt_number' =>
                        $transaction->attempt_number,

                    'midtrans_order_id' =>
                        $transaction->midtrans_order_id,

                    'transaction_status' =>
                        $transactionStatus,
                ]
            );

            return $transaction;
        }

        /*
        |--------------------------------------------------------------------------
        | PENDING
        |--------------------------------------------------------------------------
        */

        if (
            $this->isPendingTransactionStatus(
                $transactionStatus
            )
        ) {
            Log::info(
                'Midtrans payment attempt masih pending. '
                . 'Snap Token existing digunakan.',
                [
                    'order_id' =>
                        $order->id,

                    'attempt_number' =>
                        $transaction->attempt_number,

                    'midtrans_order_id' =>
                        $transaction->midtrans_order_id,
                ]
            );

            return $transaction;
        }

        /*
        |--------------------------------------------------------------------------
        | CLOSED
        |--------------------------------------------------------------------------
        */

        if (
            $this->isClosedTransactionStatus(
                $transactionStatus
            )
        ) {
            Log::info(
                'Midtrans payment attempt sudah ditutup. '
                . 'Membuat attempt pembayaran berikutnya.',
                [
                    'order_id' =>
                        $order->id,

                    'previous_attempt_number' =>
                        $transaction->attempt_number,

                    'previous_midtrans_order_id' =>
                        $transaction->midtrans_order_id,

                    'transaction_status' =>
                        $transactionStatus,
                ]
            );

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

            return $this->createNextAttempt(
                $order,
                $transaction
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UNKNOWN
        |--------------------------------------------------------------------------
        */

        if ($transactionStatus === '') {
            throw new RuntimeException(
                'Midtrans mengembalikan status transaksi kosong.'
            );
        }

        throw new RuntimeException(
            'Status transaksi Midtrans tidak dikenali: '
            . $transactionStatus
        );
    }

    /**
     * =========================================================
     * GET / CREATE LATEST ATTEMPT
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

                $this->ensureOrderCanPay(
                    $lockedOrder
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

                if ($latest) {
                    return $latest;
                }

                $transaction =
                    MidtransTransaction::create([
                        'order_id' =>
                            $lockedOrder->id,

                        'attempt_number' =>
                            1,

                        'midtrans_order_id' =>
                            $lockedOrder->invoice_number . '-MT1',

                        'gross_amount' =>
                            (int) round(
                                (float) $lockedOrder->total_price
                            ),

                        'midtrans_payment_type' =>
                            $lockedOrder->midtrans_payment_type,
                    ]);

                Log::info(
                    'Midtrans payment attempt #1 dibuat.',
                    [
                        'order_id' =>
                            $lockedOrder->id,

                        'attempt_number' =>
                            1,

                        'midtrans_order_id' =>
                            $transaction->midtrans_order_id,
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
                    $latest->id !== $previous->id
                ) {
                    return $latest;
                }

                $nextAttemptNumber =
                    (int) $previous->attempt_number + 1;

                $midtransOrderId =
                    $lockedOrder->invoice_number
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
                            (int) round(
                                (float) $lockedOrder->total_price
                            ),

                        'midtrans_payment_type' =>
                            $lockedOrder->midtrans_payment_type,

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

                        'request_payload' =>
                            null,

                        'response_payload' =>
                            null,

                        'notification_payload' =>
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
                            $previous->attempt_number,

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
        MidtransTransaction $transaction,
        ?string $paymentType = null
    ): array {
        $itemDetails = [];

        foreach ($order->details as $detail) {
            $price =
                (int) round(
                    (float) $detail->price
                );

            $quantity =
                (int) $detail->qty;

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
                    (string) $detail->item_id,

                'price' =>
                    $price,

                'quantity' =>
                    $quantity,

                'name' =>
                    $detail->item?->item_name
                    ?? 'Top Up Item',
            ];
        }

        if (empty($itemDetails)) {
            $itemDetails[] = [
                'id' =>
                    'ORDER-' . $order->id,

                'price' =>
                    (int) round(
                        (float) $transaction->gross_amount
                    ),

                'quantity' =>
                    1,

                'name' =>
                    'Game Top Up',
            ];
        }

        $grossAmount =
            (int) round(
                (float) $transaction->gross_amount
            );

        if ($grossAmount <= 0) {
            throw new RuntimeException(
                'Gross amount transaksi Midtrans tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ADJUST ITEM TOTAL
        |--------------------------------------------------------------------------
        */

        $itemTotal = 0;

        foreach ($itemDetails as $item) {
            $itemTotal +=
                (int) $item['price']
                * (int) $item['quantity'];
        }

        $difference =
            $grossAmount - $itemTotal;

        if ($difference !== 0) {
            $itemDetails[] = [
                'id' =>
                    'ADJUSTMENT-' . $order->id,

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
        | FINAL ITEM TOTAL VALIDATION
        |--------------------------------------------------------------------------
        */

        $finalItemTotal = 0;

        foreach ($itemDetails as $item) {
            $finalItemTotal +=
                (int) $item['price']
                * (int) $item['quantity'];
        }

        if ($finalItemTotal !== $grossAmount) {
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

        if ($order->user) {
            $customer['first_name'] =
                $order->user->name;

            if ($order->user->email) {
                $customer['email'] =
                    $order->user->email;
            }
        } else {
            $customer['first_name'] =
                $order->guest_name
                ?? 'Guest Customer';

            if ($order->guest_email) {
                $customer['email'] =
                    $order->guest_email;
            }

            if ($order->guest_phone) {
                $customer['phone'] =
                    $order->guest_phone;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SNAP PARAMS
        |--------------------------------------------------------------------------
        */

        $params = [
            'transaction_details' => [
                'order_id' =>
                    $transaction->midtrans_order_id,

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


        /*
        |--------------------------------------------------------------------------
        | PAYMENT CHANNEL
        |--------------------------------------------------------------------------
        |
        | Kalau user memilih satu payment method dari game.show,
        | Snap diarahkan langsung ke metode tersebut.
        |
        | Kalau null, Snap tetap menampilkan semua channel aktif.
        |--------------------------------------------------------------------------
        */

        if (
            !empty($paymentType)
        ) {

            $params['enabled_payments'] = [
                $paymentType,
            ];

        }


        return $params;
    }

    /**
     * =========================================================
     * ORDER PAYMENT VALIDATION
     * =========================================================
     */
    protected function ensureOrderCanPay(
        Order $order
    ): void {
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
    }

    /**
     * =========================================================
     * SUCCESS STATUS
     * =========================================================
     */
    public function isSuccessfulTransactionStatus(
        string $transactionStatus,
        ?string $fraudStatus = null
    ): bool {
        $transactionStatus =
            strtolower(
                trim($transactionStatus)
            );

        $fraudStatus =
            strtolower(
                trim(
                    (string) $fraudStatus
                )
            );

        if ($transactionStatus === 'settlement') {
            return true;
        }

        if ($transactionStatus === 'capture') {
            return in_array(
                $fraudStatus,
                [
                    '',
                    'accept',
                ],
                true
            );
        }

        return false;
    }

    /**
     * =========================================================
     * PENDING STATUS
     * =========================================================
     */
    public function isPendingTransactionStatus(
        string $transactionStatus
    ): bool {
        return strtolower(
            trim($transactionStatus)
        ) === 'pending';
    }

    /**
     * =========================================================
     * CLOSED STATUS
     * =========================================================
     */
    public function isClosedTransactionStatus(
        string $transactionStatus
    ): bool {
        return in_array(
            strtolower(
                trim($transactionStatus)
            ),
            [
                'expire',
                'cancel',
                'deny',
                'failure',
            ],
            true
        );
    }
}