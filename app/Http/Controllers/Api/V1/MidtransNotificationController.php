<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMooGoldOrder;
use App\Models\MidtransTransaction;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MidtransNotificationController extends Controller
{
    public function __construct(
        protected MidtransService $midtrans
    ) {
    }

    /**
     * =========================================================
     * HANDLE MIDTRANS NOTIFICATION
     * =========================================================
     */
    public function handle(
        Request $request
    ): JsonResponse {
        $notification = $request->all();

        Log::info(
            'Midtrans notification diterima.',
            [
                'order_id' =>
                    $notification['order_id']
                    ?? null,

                'transaction_status' =>
                    $notification['transaction_status']
                    ?? null,

                'status_code' =>
                    $notification['status_code']
                    ?? null,

                'transaction_id' =>
                    $notification['transaction_id']
                    ?? null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        $midtransOrderId =
            trim(
                (string) (
                    $notification['order_id']
                    ?? ''
                )
            );

        if (
            $midtransOrderId === ''
        ) {
            Log::warning(
                'Midtrans notification ditolak: order_id kosong.'
            );

            return response()->json([
                'success' => false,
                'message' => 'order_id tidak ditemukan.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY SIGNATURE
        |--------------------------------------------------------------------------
        |
        | SHA512:
        |
        | order_id + status_code + gross_amount + server_key
        |
        */

        if (
            !$this->verifySignature(
                $notification
            )
        ) {
            Log::warning(
                'Midtrans notification ditolak: signature tidak valid.',
                [
                    'order_id' =>
                        $midtransOrderId,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | FIND LOCAL TRANSACTION
        |--------------------------------------------------------------------------
        */

        $transaction =
            MidtransTransaction::query()
                ->with('order')
                ->where(
                    'midtrans_order_id',
                    $midtransOrderId
                )
                ->first();

        if (
            !$transaction
        ) {
            Log::warning(
                'Midtrans notification untuk transaksi yang tidak ditemukan.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | RETURN 200
            |--------------------------------------------------------------------------
            |
            | Notification valid, tetapi transaksi lokal belum ditemukan.
            | Jangan membuat Midtrans retry tanpa akhir.
            |
            */

            return response()->json([
                'success' => true,
                'message' =>
                    'Notification diterima, transaksi lokal tidak ditemukan.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SERVER-SIDE STATUS CHECK
        |--------------------------------------------------------------------------
        |
        | Jangan menjadikan payload webhook sebagai satu-satunya
        | sumber kebenaran.
        |
        */

        try {
            $statusResponse =
                $this->midtrans
                    ->getTransactionStatus(
                        $midtransOrderId
                    );
        } catch (Throwable $e) {
            Log::error(
                'Gagal melakukan verifikasi status transaksi Midtrans.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'midtrans_transaction_id' =>
                        $transaction->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Status transaksi belum dapat diverifikasi.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATE MIDTRANS RESPONSE
        |--------------------------------------------------------------------------
        */

        $statusOrderId =
            trim(
                (string) data_get(
                    $statusResponse,
                    'order_id',
                    ''
                )
            );

        if (
            $statusOrderId === ''
            ||
            $statusOrderId !== $midtransOrderId
        ) {
            Log::error(
                'Midtrans status response memiliki order_id yang tidak sesuai.',
                [
                    'expected_order_id' =>
                        $midtransOrderId,

                    'response_order_id' =>
                        $statusOrderId,

                    'midtrans_transaction_id' =>
                        $transaction->id,
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Data transaksi Midtrans tidak sesuai.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATE GROSS AMOUNT
        |--------------------------------------------------------------------------
        |
        | Nominal Midtrans harus sama dengan total order lokal.
        |
        */

        $midtransGrossAmount =
            $this->normalizeAmount(
                data_get(
                    $statusResponse,
                    'gross_amount'
                )
            );

        $localGrossAmount =
            $this->normalizeAmount(
                $transaction->gross_amount
            );

        if (
            $midtransGrossAmount === null
            ||
            $localGrossAmount === null
            ||
            bccomp(
                $midtransGrossAmount,
                $localGrossAmount,
                2
            ) !== 0
        ) {
            Log::error(
                'Midtrans gross amount tidak sesuai dengan transaksi lokal.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'local_gross_amount' =>
                        $localGrossAmount,

                    'midtrans_gross_amount' =>
                        $midtransGrossAmount,

                    'midtrans_transaction_id' =>
                        $transaction->id,
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Nominal transaksi Midtrans tidak sesuai.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | PROCESS TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {
            $result =
                DB::transaction(
                    function () use (
                        $transaction,
                        $notification,
                        $statusResponse
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | LOCK MIDTRANS TRANSACTION
                        |--------------------------------------------------------------------------
                        */

                        $lockedTransaction =
                            MidtransTransaction::query()
                                ->lockForUpdate()
                                ->findOrFail(
                                    $transaction->id
                                );

                        /*
                        |--------------------------------------------------------------------------
                        | LOAD + LOCK ORDER
                        |--------------------------------------------------------------------------
                        */

                        $order =
                            Order::query()
                                ->lockForUpdate()
                                ->find(
                                    $lockedTransaction->order_id
                                );

                        if (
                            !$order
                        ) {
                            throw new RuntimeException(
                                'Order untuk transaksi Midtrans tidak ditemukan.'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | STATUS DATA
                        |--------------------------------------------------------------------------
                        */

                        $transactionStatus =
                            strtolower(
                                trim(
                                    (string) (
                                        data_get(
                                            $statusResponse,
                                            'transaction_status'
                                        )
                                        ??
                                        $notification[
                                            'transaction_status'
                                        ]
                                        ??
                                        ''
                                    )
                                )
                            );

                        $paymentType =
                            data_get(
                                $statusResponse,
                                'payment_type'
                            )
                            ??
                            (
                                $notification[
                                    'payment_type'
                                ]
                                ??
                                null
                            );

                        $fraudStatus =
                            data_get(
                                $statusResponse,
                                'fraud_status'
                            )
                            ??
                            (
                                $notification[
                                    'fraud_status'
                                ]
                                ??
                                null
                            );

                        $transactionId =
                            data_get(
                                $statusResponse,
                                'transaction_id'
                            )
                            ??
                            (
                                $notification[
                                    'transaction_id'
                                ]
                                ??
                                null
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE MIDTRANS TRANSACTION
                        |--------------------------------------------------------------------------
                        */

                        $lockedTransaction->update([
                            'transaction_id' =>
                                $transactionId,

                            'transaction_status' =>
                                $transactionStatus,

                            'payment_type' =>
                                $paymentType,

                            'fraud_status' =>
                                $fraudStatus,

                            'response_payload' =>
                                (array) $statusResponse,

                            'notification_payload' =>
                                $notification,
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | MAP PAYMENT STATUS
                        |--------------------------------------------------------------------------
                        */

                        $paymentStatus =
                            $this->mapPaymentStatus(
                                $transactionStatus,
                                $fraudStatus
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | ORDER STATUS BEFORE UPDATE
                        |--------------------------------------------------------------------------
                        */

                        $previousOrderStatus =
                            $order->status;

                        /*
                        |--------------------------------------------------------------------------
                        | PAYMENT LOG
                        |--------------------------------------------------------------------------
                        */

                        $paymentLog =
                            PaymentLog::query()
                                ->where(
                                    'order_id',
                                    $order->id
                                )
                                ->latest('id')
                                ->lockForUpdate()
                                ->first();

                        $paymentMessage =
                            $this->paymentMessage(
                                $paymentStatus,
                                $transactionStatus,
                                $fraudStatus
                            );

                        if (
                            !$paymentLog
                        ) {
                            $paymentLog =
                                PaymentLog::create([
                                    'order_id' =>
                                        $order->id,

                                    'status' =>
                                        $paymentStatus,

                                    'message' =>
                                        $paymentMessage,

                                    'logged_at' =>
                                        now(),
                                ]);
                        } else {
                            /*
                            |--------------------------------------------------------------------------
                            | IDEMPOTENT PAYMENT LOG
                            |--------------------------------------------------------------------------
                            |
                            | Jika status sama, cukup update informasi
                            | terakhir tanpa membuat row baru.
                            |
                            */

                            $paymentLog->update([
                                'status' =>
                                    $paymentStatus,

                                'message' =>
                                    $paymentMessage,

                                'logged_at' =>
                                    now(),
                            ]);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | PAID
                        |--------------------------------------------------------------------------
                        */

                        $isPaid =
                            $paymentStatus === 'Paid';

                        if (
                            $isPaid
                        ) {
                            /*
                            |--------------------------------------------------------------------------
                            | PAID TIME
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !$lockedTransaction->paid_at
                            ) {
                                $lockedTransaction->paid_at =
                                    now();
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | ORDER STATUS
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
                                $order->status =
                                    'Paid';
                            }

                            $lockedTransaction->save();
                            $order->save();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | EXPIRED
                        |--------------------------------------------------------------------------
                        */

                        elseif (
                            $paymentStatus === 'Expired'
                        ) {
                            if (
                                !$lockedTransaction->expired_at
                            ) {
                                $lockedTransaction->expired_at =
                                    now();
                            }

                            $lockedTransaction->save();

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
                                $order->status =
                                    'Cancelled';

                                $order->save();
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | FAILED
                        |--------------------------------------------------------------------------
                        */

                        elseif (
                            $paymentStatus === 'Failed'
                        ) {
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
                                $order->status =
                                    'Cancelled';

                                $order->save();
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | REFUND
                        |--------------------------------------------------------------------------
                        */

                        elseif (
                            $paymentStatus === 'Refund'
                        ) {
                            /*
                            |--------------------------------------------------------------------------
                            | Jangan membatalkan order yang sedang
                            | diproses/completed hanya karena webhook
                            | refund masuk.
                            |
                            | Status order untuk refund akan kita
                            | tangani terpisah jika diperlukan.
                            |--------------------------------------------------------------------------
                            */
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SHOULD DISPATCH MOOGOLD
                        |--------------------------------------------------------------------------
                        |
                        | Bukan hanya berdasarkan "baru saja Paid".
                        |
                        | Jika order sudah Paid tetapi dispatch sebelumnya
                        | gagal, webhook berikutnya tetap bisa melakukan
                        | recovery.
                        |
                        */

                        return [
                            'order_id' =>
                                $order->id,

                            'payment_status' =>
                                $paymentStatus,

                            'previous_order_status' =>
                                $previousOrderStatus,

                            'current_order_status' =>
                                $order->status,

                            'is_paid' =>
                                $isPaid,
                        ];
                    }
                );

            /*
            |--------------------------------------------------------------------------
            | DISPATCH MOOGOLD AFTER COMMIT
            |--------------------------------------------------------------------------
            */

            $moogoldDispatched = false;

            if (
                $result['is_paid']
            ) {
                $moogoldDispatched =
                    $this->dispatchMooGold(
                        $result['order_id']
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | LOG RESULT
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Midtrans notification berhasil diproses.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'order_id' =>
                        $result['order_id'],

                    'payment_status' =>
                        $result['payment_status'],

                    'previous_order_status' =>
                        $result['previous_order_status'],

                    'current_order_status' =>
                        $result['current_order_status'],

                    'moogold_dispatched' =>
                        $moogoldDispatched,
                ]
            );

            return response()->json([
                'success' => true,
                'message' =>
                    'Notification berhasil diproses.',
            ]);
        } catch (Throwable $e) {
            Log::error(
                'Gagal memproses Midtrans notification.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'midtrans_transaction_id' =>
                        $transaction->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Notification gagal diproses.',
            ], 500);
        }
    }

    /**
     * =========================================================
     * VERIFY MIDTRANS SIGNATURE
     * =========================================================
     */
    protected function verifySignature(
        array $notification
    ): bool {
        $serverKey =
            (string) config(
                'midtrans.server_key'
            );

        if (
            $serverKey === ''
        ) {
            return false;
        }

        $orderId =
            (string) (
                $notification['order_id']
                ?? ''
            );

        $statusCode =
            (string) (
                $notification['status_code']
                ?? ''
            );

        $grossAmount =
            (string) (
                $notification['gross_amount']
                ?? ''
            );

        $signatureKey =
            (string) (
                $notification['signature_key']
                ?? ''
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
            return false;
        }

        $expectedSignature =
            hash(
                'sha512',
                $orderId
                .
                $statusCode
                .
                $grossAmount
                .
                $serverKey
            );

        return hash_equals(
            $expectedSignature,
            $signatureKey
        );
    }

    /**
     * =========================================================
     * MAP MIDTRANS STATUS
     * =========================================================
     */
    protected function mapPaymentStatus(
        string $transactionStatus,
        ?string $fraudStatus = null
    ): string {
        $transactionStatus =
            strtolower(
                trim(
                    $transactionStatus
                )
            );

        $fraudStatus =
            strtolower(
                trim(
                    (string) $fraudStatus
                )
            );

        /*
        |--------------------------------------------------------------------------
        | SETTLEMENT
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus === 'settlement'
        ) {
            return 'Paid';
        }

        /*
        |--------------------------------------------------------------------------
        | CAPTURE
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus === 'capture'
        ) {
            if (
                $fraudStatus === 'deny'
            ) {
                return 'Failed';
            }

            if (
                $fraudStatus === 'challenge'
            ) {
                return 'Pending';
            }

            return 'Paid';
        }

        /*
        |--------------------------------------------------------------------------
        | PENDING
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus === 'pending'
        ) {
            return 'Pending';
        }

        /*
        |--------------------------------------------------------------------------
        | EXPIRE
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus === 'expire'
        ) {
            return 'Expired';
        }

        /*
        |--------------------------------------------------------------------------
        | CANCEL / DENY
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $transactionStatus,
                [
                    'cancel',
                    'deny',
                ],
                true
            )
        ) {
            return 'Failed';
        }

        /*
        |--------------------------------------------------------------------------
        | REFUND
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $transactionStatus,
                [
                    'refund',
                    'partial_refund',
                ],
                true
            )
        ) {
            return 'Refund';
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        return 'Pending';
    }

    /**
     * =========================================================
     * PAYMENT LOG MESSAGE
     * =========================================================
     */
    protected function paymentMessage(
        string $paymentStatus,
        string $transactionStatus,
        ?string $fraudStatus = null
    ): string {
        $transactionStatus =
            strtolower(
                trim(
                    $transactionStatus
                )
            );

        $fraudStatus =
            strtolower(
                trim(
                    (string) $fraudStatus
                )
            );

        return match ($paymentStatus) {
            'Paid' =>
                'Pembayaran Midtrans berhasil dikonfirmasi.',

            'Pending' =>
                $fraudStatus === 'challenge'
                    ? 'Pembayaran Midtrans sedang dalam proses verifikasi.'
                    : 'Pembayaran Midtrans masih menunggu penyelesaian.',

            'Expired' =>
                'Pembayaran Midtrans telah kedaluwarsa.',

            'Failed' =>
                'Pembayaran Midtrans gagal atau dibatalkan.',

            'Refund' =>
                'Pembayaran Midtrans telah direfund.',

            default =>
                'Status pembayaran Midtrans: '
                . $transactionStatus . '.',
        };
    }

    /**
     * =========================================================
     * NORMALIZE AMOUNT
     * =========================================================
     */
    protected function normalizeAmount(
        mixed $amount
    ): ?string {
        if (
            $amount === null
            ||
            $amount === ''
        ) {
            return null;
        }

        $normalized =
            number_format(
                (float) $amount,
                2,
                '.',
                ''
            );

        return $normalized;
    }

    /**
     * =========================================================
     * DISPATCH MOOGOLD
     * =========================================================
     */
    protected function dispatchMooGold(
        int $orderId
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER + DETAILS
        |--------------------------------------------------------------------------
        |
        | Order memakai relasi "details()", bukan "orderDetails()".
        |
        */

        $order =
            Order::query()
                ->with([
                    'details.mooGoldOrder',
                ])
                ->find(
                    $orderId
                );

        if (
            !$order
        ) {
            Log::error(
                'Order tidak ditemukan saat dispatch MooGold.',
                [
                    'order_id' =>
                        $orderId,
                ]
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | ONLY PAID
        |--------------------------------------------------------------------------
        */

        if (
            $order->status !== 'Paid'
        ) {
            Log::warning(
                'Dispatch MooGold dibatalkan karena order belum Paid.',
                [
                    'order_id' =>
                        $order->id,

                    'status' =>
                        $order->status,
                ]
            );

            return false;
        }

        $dispatched = false;

        /*
        |--------------------------------------------------------------------------
        | DISPATCH EACH ORDER DETAIL
        |--------------------------------------------------------------------------
        */

        foreach (
            $order->details as
            $detail
        ) {
            /*
            |--------------------------------------------------------------------------
            | CHECK EXISTING MOOGOLD ORDER
            |--------------------------------------------------------------------------
            |
            | Jika detail sudah mempunyai MooGold Order ID,
            | jangan dispatch ulang.
            |
            */

            $mooGoldOrder =
                $detail->mooGoldOrder;

            if (
                $mooGoldOrder
                &&
                !empty(
                    $mooGoldOrder->moogold_order_id
                )
            ) {
                Log::info(
                    'MooGold fulfillment dilewati karena order sudah dibuat.',
                    [
                        'order_id' =>
                            $order->id,

                        'order_detail_id' =>
                            $detail->id,

                        'moogold_order_id' =>
                            $mooGoldOrder->moogold_order_id,
                    ]
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | DISPATCH JOB
            |--------------------------------------------------------------------------
            */

            ProcessMooGoldOrder::dispatch(
                $detail->id
            );

            $dispatched = true;

            Log::info(
                'ProcessMooGoldOrder berhasil di-dispatch setelah pembayaran Paid.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $detail->id,
                ]
            );
        }

        return $dispatched;
    }
}