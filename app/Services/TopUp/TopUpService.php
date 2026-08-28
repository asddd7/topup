<?php

namespace App\Services\TopUp;

use App\Models\Discount;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\MooGold\MooGoldService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TopUpService
{
    /**
     * =========================================================
     * COMPLETE ORDER
     * =========================================================
     *
     * Alur:
     *
     * Paid
     *   ↓
     * Lock Order
     *   ↓
     * Load Detail
     *   ↓
     * Check Stock
     *   ↓
     * Reduce Stock
     *   ↓
     * Process Discount
     *   ↓
     * Record Discount Usage
     *   ↓
     * Completed
     *
     * Catatan:
     * - Tidak melakukan approve pembayaran.
     * - Tidak melakukan reject pembayaran.
     * - Tidak menghitung ulang promo.
     * - Promo diambil dari order_discounts.
     * - Stock dikurangi hanya ketika order diselesaikan.
     */
    public function complete(Order $order): array
    {
        try {

            $completedOrder = DB::transaction(
                function () use ($order) {

                    /*
                    |--------------------------------------------------------------------------
                    | LOCK ORDER
                    |--------------------------------------------------------------------------
                    */

                    $lockedOrder = Order::query()
                        ->lockForUpdate()
                        ->find($order->id);

                    if (!$lockedOrder) {

                        throw new \RuntimeException(
                            'Order tidak ditemukan.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if ($lockedOrder->status !== 'Paid') {

                        throw new \RuntimeException(
                            'Order hanya dapat diselesaikan ketika statusnya Paid.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | LOAD RELATIONSHIP
                    |--------------------------------------------------------------------------
                    */

                    $lockedOrder->load([
                        'game',
                        'details.item',
                        'orderDiscounts.discount',
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | DETAIL ORDER
                    |--------------------------------------------------------------------------
                    */

                    if ($lockedOrder->details->isEmpty()) {

                        throw new \RuntimeException(
                            'Order tidak memiliki detail item.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | LOCK + VALIDATE ITEMS
                    |--------------------------------------------------------------------------
                    */

                    $items = [];


                    foreach ($lockedOrder->details as $detail) {

                        $item = Item::query()
                            ->lockForUpdate()
                            ->find($detail->item_id);


                        /*
                        |--------------------------------------------------------------------------
                        | ITEM EXISTS
                        |--------------------------------------------------------------------------
                        */

                        if (!$item) {

                            throw new \RuntimeException(
                                'Item dengan ID ' .
                                $detail->item_id .
                                ' tidak ditemukan.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | ITEM ACTIVE
                        |--------------------------------------------------------------------------
                        */

                        if (!$item->is_active) {

                            throw new \RuntimeException(
                                'Item "' .
                                $item->item_name .
                                '" sudah tidak aktif.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | QTY
                        |--------------------------------------------------------------------------
                        */

                        if ((int) $detail->qty <= 0) {

                            throw new \RuntimeException(
                                'Qty item "' .
                                $item->item_name .
                                '" tidak valid.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | STOCK
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int) $item->stock <
                            (int) $detail->qty
                        ) {

                            throw new \RuntimeException(

                                'Stock item "' .
                                $item->item_name .
                                '" tidak mencukupi. ' .

                                'Stock tersedia: ' .
                                $item->stock .

                                ', dibutuhkan: ' .
                                $detail->qty .
                                '.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SIMPAN ITEM LOCK
                        |--------------------------------------------------------------------------
                        */

                        $items[$item->id] = $item;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REDUCE STOCK
                    |--------------------------------------------------------------------------
                    */

                    foreach ($lockedOrder->details as $detail) {

                        $item = $items[$detail->item_id];


                        $item->decrement(
                            'stock',
                            $detail->qty
                        );


                        $item->refresh();


                        /*
                        |--------------------------------------------------------------------------
                        | LOW STOCK
                        |--------------------------------------------------------------------------
                        */

                        $this->handleLowStockNotification(
                            $item
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PROCESS DISCOUNT
                    |--------------------------------------------------------------------------
                    */

                    $this->processDiscounts(
                        $lockedOrder
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE ORDER
                    |--------------------------------------------------------------------------
                    */

                    $old = $lockedOrder->toArray();


                    $lockedOrder->update([
                        'status' => 'Completed',
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT LOG
                    |--------------------------------------------------------------------------
                    |
                    | Enum:
                    |
                    | Pending
                    | Paid
                    | Failed
                    | Expired
                    | Refund
                    |
                    */

                    $lockedOrder
                        ->paymentLogs()
                        ->create([

                            'status' =>
                                'Paid',

                            'message' =>
                                'Order berhasil diproses dan diselesaikan.',

                            'logged_at' =>
                                now(),
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | NOTIFICATION USER
                    |--------------------------------------------------------------------------
                    */

                    if ($lockedOrder->user_id) {

                        Notification::create([

                            'user_id' =>
                                $lockedOrder->user_id,

                            'order_id' =>
                                $lockedOrder->id,

                            'title' =>
                                'Order Selesai',

                            'message' =>
                                'Order ' .
                                $lockedOrder->invoice_number .
                                ' berhasil diproses dan diselesaikan.',

                            'is_read' =>
                                0,

                            'read_at' =>
                                null,
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RETURN
                    |--------------------------------------------------------------------------
                    */

                    return $lockedOrder;
                }
            );


            /*
            |--------------------------------------------------------------------------
            | LOAD FINAL RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $completedOrder->load([
                'game',
                'payment',
                'details.item.category',
                'paymentLogs',
                'orderDiscounts.discount',
            ]);


            /*
            |--------------------------------------------------------------------------
            | LOG
            |--------------------------------------------------------------------------
            */

            Log::info(
                'TopUp order completed',
                [
                    'order_id' =>
                        $completedOrder->id,

                    'invoice' =>
                        $completedOrder->invoice_number,

                    'status' =>
                        $completedOrder->status,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return [

                'success' =>
                    true,

                'message' =>
                    'Order berhasil diproses dan diselesaikan.',

                'order' =>
                    $completedOrder,
            ];


        } catch (\RuntimeException $e) {

            return [

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),
            ];


        } catch (\Throwable $e) {

            Log::error(
                'TopUp order completion failed',
                [
                    'order_id' =>
                        $order->id,

                    'invoice' =>
                        $order->invoice_number,

                    'error' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );


            return [

                'success' =>
                    false,

                'message' =>
                    'Gagal memproses order.',
            ];
        }
    }


    /**
     * =========================================================
     * PROCESS DISCOUNTS
     * =========================================================
     *
     * Promo tidak dihitung ulang.
     *
     * Sumber:
     * order_discounts
     */
    protected function processDiscounts(
        Order $order
    ): void {

        foreach (
            $order->orderDiscounts
            as $orderDiscount
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCK DISCOUNT
            |--------------------------------------------------------------------------
            */

            $discount = Discount::query()
                ->lockForUpdate()
                ->find(
                    $orderDiscount->discount_id
                );


            if (!$discount) {

                throw new \RuntimeException(
                    'Promo dengan ID ' .
                    $orderDiscount->discount_id .
                    ' tidak ditemukan.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ACTIVE
            |--------------------------------------------------------------------------
            */

            if (!$discount->is_active) {

                throw new \RuntimeException(
                    'Promo "' .
                    $discount->discount_name .
                    '" sudah tidak aktif.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */

            $today =
                now()->startOfDay();


            if (
                $discount->start_date &&
                $today->lt(
                    $discount->start_date
                )
            ) {

                throw new \RuntimeException(
                    'Promo "' .
                    $discount->discount_name .
                    '" belum mulai.'
                );
            }


            if (
                $discount->end_date &&
                $today->gt(
                    $discount->end_date
                )
            ) {

                throw new \RuntimeException(
                    'Promo "' .
                    $discount->discount_name .
                    '" sudah berakhir.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | GLOBAL QUOTA
            |--------------------------------------------------------------------------
            */

            if (
                $discount->usage_limit !== null &&
                $discount->quota_used >=
                $discount->usage_limit
            ) {

                throw new \RuntimeException(
                    'Promo "' .
                    $discount->discount_name .
                    '" sudah habis.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | USER QUOTA
            |--------------------------------------------------------------------------
            */

            $usagePerUser =
                (int) (
                    $discount->usage_per_user
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | 0 = UNLIMITED
            |--------------------------------------------------------------------------
            */

            if (
                $usagePerUser > 0 &&
                $order->user_id
            ) {

                $userUsageCount =
                    DB::table(
                        'discount_usages'
                    )
                        ->where(
                            'discount_id',
                            $discount->id
                        )
                        ->where(
                            'user_id',
                            $order->user_id
                        )
                        ->lockForUpdate()
                        ->count();


                if (
                    $userUsageCount >=
                    $usagePerUser
                ) {

                    throw new \RuntimeException(
                        'User sudah mencapai batas penggunaan promo "' .
                        $discount->discount_name .
                        '".'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | INCREMENT QUOTA
            |--------------------------------------------------------------------------
            */

            $discount->increment(
                'quota_used'
            );


            /*
            |--------------------------------------------------------------------------
            | DISCOUNT USAGE
            |--------------------------------------------------------------------------
            */

            $discount
                ->usages()
                ->create([

                    'order_id' =>
                        $order->id,

                    'user_id' =>
                        $order->user_id,

                    'discount_amount' =>
                        (float)
                        $orderDiscount
                            ->discount_amount,
                ]);
        }
    }


    /**
     * =========================================================
     * LOW STOCK NOTIFICATION
     * =========================================================
     */
    protected function handleLowStockNotification(
        Item $item
    ): void {

        /*
        |--------------------------------------------------------------------------
        | LOAD GAME
        |--------------------------------------------------------------------------
        */

        $item->loadMissing(
            'game'
        );


        /*
        |--------------------------------------------------------------------------
        | LOW STOCK
        |--------------------------------------------------------------------------
        */

        if ($item->stock < 10) {

            $gameName =
                $item->game?->game_name
                ?? 'Game Tidak Diketahui';


            $message =
                $gameName .
                ' - ' .
                $item->item_name .
                ' tersisa ' .
                $item->stock;


            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */

            $admins = User::query()
                ->where(
                    'role_id',
                    1
                )
                ->get();


            foreach ($admins as $admin) {

                Notification::updateOrCreate(

                    [
                        'user_id' =>
                            $admin->id,

                        'item_id' =>
                            $item->id,

                        'title' =>
                            'Stock Rendah',
                    ],

                    [
                        'message' =>
                            $message,

                        'is_read' =>
                            0,

                        'read_at' =>
                            null,
                    ]
                );
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | STOCK NORMAL
        |--------------------------------------------------------------------------
        */

        Notification::where(
            'item_id',
            $item->id
        )
            ->where(
                'title',
                'Stock Rendah'
            )
            ->delete();
    }


    /**
     * =========================================================
     * PROCESS PROVIDER
     * =========================================================
     *
     * Method ini khusus komunikasi dengan provider.
     *
     * Tidak mengurangi stock.
     * Tidak mengubah Completed.
     * Tidak memproses discount.
     *
     * Itu tanggung jawab complete().
     */
public function processProvider(
    Order $order,
    MooGoldService $mooGold
): array {

    try {

        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER
        |--------------------------------------------------------------------------
        */

        $order->load([
            'game',
            'details.item',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PLAYER UID
        |--------------------------------------------------------------------------
        */

        if (!$order->player_uid) {

            return [
                'success' => false,
                'message' => 'Player UID tidak tersedia.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | ORDER DETAIL
        |--------------------------------------------------------------------------
        */

        if ($order->details->isEmpty()) {

            return [
                'success' => false,
                'message' => 'Order tidak memiliki item.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Untuk tahap awal
        |--------------------------------------------------------------------------
        |
        | Satu order = satu produk MooGold.
        |
        */

        $detail =
            $order->details->first();

        $item =
            $detail->item;


        if (!$item) {

            return [
                'success' => false,
                'message' => 'Item order tidak ditemukan.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | MOO GOLD CONFIG
        |--------------------------------------------------------------------------
        */

        if (!$item->moogold_type) {

            return [
                'success' => false,
                'message' =>
                    'MooGold Type belum dikonfigurasi untuk item "' .
                    $item->item_name .
                    '".',
            ];
        }


        if (!$item->moogold_offer_id) {

            return [
                'success' => false,
                'message' =>
                    'MooGold Offer ID belum dikonfigurasi untuk item "' .
                    $item->item_name .
                    '".',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER MOO GOLD
        |--------------------------------------------------------------------------
        */

        $result =
            $mooGold->createOrder(

                (string)
                $item->moogold_type,

                (string)
                $item->moogold_offer_id,

                (string)
                $detail->qty,

                (string)
                $order->player_uid,

                $order->server_id
                    ? (string) $order->server_id
                    : null,

                (string)
                $order->invoice_number

            );


        /*
        |--------------------------------------------------------------------------
        | MOO GOLD FAILED
        |--------------------------------------------------------------------------
        */

        if (
            !isset($result['status']) ||
            $result['status'] !== true
        ) {

            return [

                'success' =>
                    false,

                'message' =>
                    $result['message']
                    ??
                    'MooGold gagal membuat order.',

                'response' =>
                    $result,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | MOO GOLD ORDER ID
        |--------------------------------------------------------------------------
        */

        $mooGoldOrderId =
            data_get(
                $result,
                'account_details.order_id'
            );


        if (!$mooGoldOrderId) {

            return [

                'success' =>
                    false,

                'message' =>
                    'MooGold berhasil merespons tetapi Order ID tidak ditemukan.',

                'response' =>
                    $result,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE LOCAL ORDER
        |--------------------------------------------------------------------------
        */

        $order->update([

            'status' =>
                'Processing',

            'moogold_order_id' =>
                (string)
                $mooGoldOrderId,

        ]);


        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        $order->paymentLogs()->create([

            'status' =>
                'Paid',

            'message' =>
                'Order berhasil dikirim ke MooGold. ' .
                'MooGold Order ID: ' .
                $mooGoldOrderId,

            'logged_at' =>
                now(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return [

            'success' =>
                true,

            'message' =>
                'Order berhasil dikirim ke MooGold dan sedang diproses.',

            'order' =>
                $order->fresh(),

            'moogold_order_id' =>
                $mooGoldOrderId,

        ];


    } catch (\Throwable $e) {

        Log::error(

            'MooGold process provider failed',

            [

                'order_id' =>
                    $order->id,

                'invoice' =>
                    $order->invoice_number,

                'error' =>
                    $e->getMessage(),

            ]

        );


        return [

            'success' =>
                false,

            'message' =>
                'Gagal memproses order ke MooGold: ' .
                $e->getMessage(),

        ];
    }
}
}