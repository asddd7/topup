<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMooGoldOrder;
use App\Models\MidtransTransaction;
use App\Models\PaymentLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use App\Services\Midtrans\MidtransService;

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

        $notification =
            $request->all();


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
            | Return 200
            |--------------------------------------------------------------------------
            |
            | Jangan membuat Midtrans terus-menerus mengirim ulang
            | notification hanya karena order lokal tidak ditemukan.
            |
            */

            return response()->json([
                'success' => true,
                'message' => 'Notification diterima, transaksi lokal tidak ditemukan.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SERVER-SIDE STATUS CHECK
        |--------------------------------------------------------------------------
        |
        | Jangan mempercayai transaction_status dari notification
        | sebagai satu-satunya sumber kebenaran.
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


            /*
            |--------------------------------------------------------------------------
            | RETURN 500
            |--------------------------------------------------------------------------
            |
            | Midtrans dapat retry notification.
            | Kita tidak boleh menganggap pembayaran berhasil
            | jika status server belum berhasil diverifikasi.
            |
            */

            return response()->json([
                'success' => false,
                'message' => 'Status transaksi belum dapat diverifikasi.',
            ], 500);
        }


        /*
        |--------------------------------------------------------------------------
        | PROCESS
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

                        $lockedTransaction =
                            MidtransTransaction::query()
                                ->lockForUpdate()
                                ->with('order')
                                ->findOrFail(
                                    $transaction->id
                                );


                        $order =
                            $lockedTransaction->order;


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
                                (array)
                                $statusResponse,

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


                        if (
                            !$paymentLog
                        ) {

                            $paymentLog =
                                PaymentLog::create([

                                    'order_id' =>
                                        $order->id,

                                    'status' =>
                                        $paymentStatus,

                                ]);

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | DO NOT MOVE PAID BACKWARD
                            |--------------------------------------------------------------------------
                            |
                            | Notification bisa datang berulang
                            | atau dalam urutan berbeda.
                            |
                            */

                            if (
                                $paymentLog->status !== 'Paid'
                                ||
                                $paymentStatus === 'Paid'
                            ) {

                                $paymentLog->update([

                                    'status' =>
                                        $paymentStatus,

                                ]);
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PAID
                        |--------------------------------------------------------------------------
                        */

                        $isPaid =
                            $paymentStatus ===
                            'Paid';


                        if (
                            $isPaid
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | UPDATE PAID TIME
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


                        } elseif (
                            $paymentStatus ===
                            'Expired'
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


                        } elseif (
                            in_array(
                                $paymentStatus,
                                [
                                    'Failed',
                                    'Refund',
                                ],
                                true
                            )
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


                        return [

                            'order_id' =>
                                $order->id,

                            'payment_status' =>
                                $paymentStatus,

                            'is_paid' =>
                                $isPaid,

                        ];
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | DISPATCH MOOGOLD
            |--------------------------------------------------------------------------
            |
            | HANYA setelah DB commit berhasil dan payment
            | benar-benar Paid.
            |
            */

            if (
                $result['is_paid']
            ) {

                $this->dispatchMooGold(
                    $result['order_id']
                );
            }


            Log::info(
                'Midtrans notification berhasil diproses.',
                [

                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'order_id' =>
                        $result['order_id'],

                    'payment_status' =>
                        $result['payment_status'],

                    'moogold_dispatched' =>
                        $result['is_paid'],

                ]
            );


            return response()->json([
                'success' => true,
                'message' => 'Notification berhasil diproses.',
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
                'message' => 'Notification gagal diproses.',
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
            (string)
            config(
                'midtrans.server_key'
            );


        if (
            $serverKey === ''
        ) {

            return false;
        }


        $orderId =
            (string)
            (
                $notification['order_id']
                ?? ''
            );


        $statusCode =
            (string)
            (
                $notification['status_code']
                ?? ''
            );


        $grossAmount =
            (string)
            (
                $notification['gross_amount']
                ?? ''
            );


        $signatureKey =
            (string)
            (
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
                    (string)
                    $fraudStatus
                )
            );


        /*
        |--------------------------------------------------------------------------
        | SETTLEMENT
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus ===
            'settlement'
        ) {

            return 'Paid';
        }


        /*
        |--------------------------------------------------------------------------
        | CAPTURE
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus ===
            'capture'
        ) {

            /*
            |--------------------------------------------------------------------------
            | Credit Card Fraud
            |--------------------------------------------------------------------------
            |
            | Capture dengan fraud_status deny jangan dianggap Paid.
            |
            */

            if (
                $fraudStatus ===
                'deny'
            ) {

                return 'Failed';
            }


            return 'Paid';
        }


        /*
        |--------------------------------------------------------------------------
        | PENDING
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus ===
            'pending'
        ) {

            return 'Pending';
        }


        /*
        |--------------------------------------------------------------------------
        | EXPIRE
        |--------------------------------------------------------------------------
        */

        if (
            $transactionStatus ===
            'expire'
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
     * DISPATCH MOOGOLD
     * =========================================================
     */
    protected function dispatchMooGold(
        int $orderId
    ): void {

        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER DETAILS
        |--------------------------------------------------------------------------
        */

        $order =
            \App\Models\Order::query()
                ->with('orderDetails')
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

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ONLY PAID
        |--------------------------------------------------------------------------
        */

        if (
            $order->status !==
            'Paid'
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

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DISPATCH EACH ORDER DETAIL
        |--------------------------------------------------------------------------
        */

        foreach (
            $order->orderDetails as
            $detail
        ) {

            ProcessMooGoldOrder::dispatch(
                $detail->id
            );


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
    }
}