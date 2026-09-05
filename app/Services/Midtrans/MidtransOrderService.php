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
     * CREATE SNAP TRANSACTION
     * =========================================================
     */
    public function createForOrder(
        Order $order
    ): MidtransTransaction {

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATION
        |--------------------------------------------------------------------------
        */

        $order->loadMissing([
            'details.item',
            'game',
            'user',
            'midtransTransaction',
        ]);


        /*
        |--------------------------------------------------------------------------
        | VALIDATE STATUS
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
        | EXISTING TOKEN
        |--------------------------------------------------------------------------
        */

        if (
            $order->midtransTransaction &&
            !empty(
                $order->midtransTransaction->snap_token
            )
        ) {

            Log::info(
                'Midtrans Snap token sudah tersedia.',
                [

                    'order_id' =>
                        $order->id,

                    'midtrans_transaction_id' =>
                        $order
                            ->midtransTransaction
                            ->id,

                    'midtrans_order_id' =>
                        $order
                            ->midtransTransaction
                            ->midtrans_order_id,

                ]
            );


            return $order
                ->midtransTransaction
                ->fresh();
        }


        /*
        |--------------------------------------------------------------------------
        | APPLICATION LOCK
        |--------------------------------------------------------------------------
        |
        | Mencegah dua request aplikasi membuat Snap
        | untuk order yang sama secara bersamaan.
        |
        */

        $lock = Cache::lock(
            'midtrans-create-snap:' . $order->id,
            120
        );


        try {

            if (
                !$lock->get()
            ) {

                throw new RuntimeException(
                    'Pembuatan pembayaran Midtrans untuk order ini '
                    . 'sedang diproses. Silakan coba kembali.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CREATE / GET LOCAL TRANSACTION
            |--------------------------------------------------------------------------
            |
            | Hanya operasi database lokal dilakukan di transaction.
            | Tidak ada HTTP request Midtrans di sini.
            |
            */

            $transaction =
                DB::transaction(
                    function () use ($order) {

                        $lockedOrder =
                            Order::query()
                                ->lockForUpdate()
                                ->with([
                                    'details.item',
                                    'game',
                                    'user',
                                ])
                                ->findOrFail(
                                    $order->id
                                );


                        /*
                        |--------------------------------------------------------------------------
                        | CHECK STATUS AGAIN
                        |--------------------------------------------------------------------------
                        */

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


                        /*
                        |--------------------------------------------------------------------------
                        | GET / CREATE TRANSACTION
                        |--------------------------------------------------------------------------
                        */

                        $transaction =
                            MidtransTransaction::query()
                                ->where(
                                    'order_id',
                                    $lockedOrder->id
                                )
                                ->lockForUpdate()
                                ->first();


                        if (
                            !$transaction
                        ) {

                            $transaction =
                                MidtransTransaction::create([

                                    'order_id' =>
                                        $lockedOrder->id,

                                    'midtrans_order_id' =>
                                        $lockedOrder
                                            ->invoice_number,

                                    'gross_amount' =>
                                        $lockedOrder
                                            ->total_price,

                                ]);

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | PROTECT MIDTRANS ORDER ID
                            |--------------------------------------------------------------------------
                            |
                            | Jangan pernah mengganti ID setelah
                            | transaksi Midtrans terbentuk.
                            |
                            */

                            if (
                                empty(
                                    $transaction
                                        ->midtrans_order_id
                                )
                            ) {

                                $transaction->update([

                                    'midtrans_order_id' =>
                                        $lockedOrder
                                            ->invoice_number,

                                ]);
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | TOKEN ALREADY EXISTS
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty(
                                $transaction
                                    ->snap_token
                            )
                        ) {

                            return $transaction
                                ->fresh();
                        }


                        return $transaction
                            ->fresh();
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | TOKEN MAY HAVE BEEN CREATED BY PREVIOUS REQUEST
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $transaction->snap_token
                )
            ) {

                return $transaction;
            }


            /*
            |--------------------------------------------------------------------------
            | RELOAD ORDER
            |--------------------------------------------------------------------------
            */

            $transaction->loadMissing('order');


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
            |
            | IMPORTANT:
            |
            | HTTP request ke Midtrans berada DI LUAR
            | DB transaction.
            |
            */

            $snapToken =
                $this->midtrans
                    ->createSnapToken(
                        $params
                    );


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


                    /*
                    |--------------------------------------------------------------------------
                    | CHECK AGAIN
                    |--------------------------------------------------------------------------
                    |
                    | Secara normal lock aplikasi sudah mencegah
                    | duplicate request.
                    |
                    | Check ini menjadi safety net tambahan.
                    |
                    */

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

                        ]);

                    } else {

                        Log::warning(
                            'Snap token sudah tersedia saat proses save.',
                            [

                                'order_id' =>
                                    $lockedTransaction
                                        ->order_id,

                                'midtrans_transaction_id' =>
                                    $lockedTransaction
                                        ->id,

                            ]
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE ORDER
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
                        $lockedOrder->status ===
                        'Pending'
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
            | RESULT
            |--------------------------------------------------------------------------
            */

            $result =
                MidtransTransaction::query()
                    ->findOrFail(
                        $transaction->id
                    );


            Log::info(
                'Midtrans transaction berhasil dibuat.',
                [

                    'order_id' =>
                        $result->order_id,

                    'invoice_number' =>
                        $transactionOrder
                            ->invoice_number,

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

                ]
            );


            throw $e;

        } finally {

            $lock->release();
        }
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


        foreach (
            $order->details as
            $detail
        ) {

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
            empty(
                $itemDetails
            )
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
        | CHECK ITEM TOTAL
        |--------------------------------------------------------------------------
        */

        $itemTotal = 0;


        foreach (
            $itemDetails as
            $item
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
        |
        | Jika total detail berbeda dengan total order,
        | tambahkan adjustment agar total item sama dengan
        | gross_amount.
        |
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
            $itemDetails as
            $item
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
        | CUSTOMER DETAILS
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
        | PARAMS
        |--------------------------------------------------------------------------
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

        ];
    }
}