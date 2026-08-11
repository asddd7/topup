<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Discount;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseAdminController
{
    /**
     * =========================================================
     * ORDER INDEX
     * =========================================================
     */
    public function index(Request $request)
    {
        $statusList = [
            'Pending',
            'Waiting Payment',
            'Paid',
            'Processing',
            'Completed',
            'Cancelled',
        ];

        $status = $request->status;

        $orders = Order::with([
            'user',
            'game',
            'payment',
        ])
            ->when(
                $status,
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )
            ->latest()
            ->get();

        return view(
            'admin.order.index',
            compact(
                'orders',
                'status',
                'statusList'
            )
        );
    }


    /**
     * =========================================================
     * ORDER DETAIL
     * =========================================================
     */
    public function show(Order $order)
    {
        $order->load([
            'user',
            'game',
            'payment',
            'discount',
            'discounts',
            'orderDiscounts.discount',
            'details.item',
            'paymentLogs',
        ]);

        return view(
            'admin.order.show',
            compact('order')
        );
    }


    /**
     * =========================================================
     * UPDATE ORDER STATUS
     * =========================================================
     */
    public function update(
        Request $request,
        Order $order
    ) {
        $request->validate([
            'status' => [
                'required',
                'in:Pending,Waiting Payment,Paid,Processing,Completed,Cancelled',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $old = $order->toArray();

        $order->update([
            'status' => $request->status,
            'notes'  => $request->notes,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        $this->activity->log(
            'Order',
            'Update Status',
            'Update status order ' .
                $order->invoice_number .
                ' dari ' .
                ($old['status'] ?? '-') .
                ' menjadi ' .
                $order->status,
            $order,
            $old,
            $order->fresh()->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Payment Log
        |--------------------------------------------------------------------------
        */

        $order->paymentLogs()->create([
            'status' => $request->status,
            'message' => 'Status diperbarui admin',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Jika Completed
        |--------------------------------------------------------------------------
        */

        if ($request->status === 'Completed') {
            Notification::where(
                'order_id',
                $order->id
            )->update([
                'is_read' => 1,
                'read_at' => now(),
            ]);
        }

        return back()->with(
            'success',
            'Order berhasil diperbarui'
        );
    }


    /**
     * =========================================================
     * CONFIRM PAYMENT
     *
     * Alur:
     *
     * Paid
     *   ↓
     * Lock Order
     *   ↓
     * Check Stock
     *   ↓
     * Reduce Stock
     *   ↓
     * Process Discount
     *   ↓
     * quota_used + 1
     *   ↓
     * discount_usages
     *   ↓
     * Completed
     * =========================================================
     */
    public function confirm(Order $order)
    {
        /*
        |--------------------------------------------------------------------------
        | Status awal harus Paid
        |--------------------------------------------------------------------------
        */

        if ($order->status !== 'Paid') {
            return back()->with(
                'error',
                'Order harus berstatus Paid.'
            );
        }

        try {
            DB::transaction(function () use ($order) {

                /*
                |--------------------------------------------------------------------------
                | LOCK ORDER
                |--------------------------------------------------------------------------
                */

                $lockedOrder = Order::where(
                    'id',
                    $order->id
                )
                    ->lockForUpdate()
                    ->first();

                if (!$lockedOrder) {
                    throw new \RuntimeException(
                        'Order tidak ditemukan.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Pastikan belum diproses
                |--------------------------------------------------------------------------
                */

                if ($lockedOrder->status !== 'Paid') {
                    throw new \RuntimeException(
                        'Order sudah diproses atau status sudah berubah.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | LOAD RELATIONS
                |--------------------------------------------------------------------------
                */

                $lockedOrder->load([
                    'details.item',
                    'orderDiscounts.discount',
                ]);

                /*
                |--------------------------------------------------------------------------
                | CEK DETAIL ORDER
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedOrder->details->isEmpty()
                ) {
                    throw new \RuntimeException(
                        'Order tidak memiliki detail item.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CEK STOCK
                |--------------------------------------------------------------------------
                |
                | Semua item di-lock terlebih dahulu.
                |
                */

                foreach (
                    $lockedOrder->details as $detail
                ) {
                    $item = Item::where(
                        'id',
                        $detail->item_id
                    )
                        ->lockForUpdate()
                        ->first();

                    if (!$item) {
                        throw new \RuntimeException(
                            'Item ID ' .
                                $detail->item_id .
                                ' tidak ditemukan.'
                        );
                    }

                    if (
                        $item->stock <
                        $detail->qty
                    ) {
                        throw new \RuntimeException(
                            'Stock ' .
                                $item->item_name .
                                ' tidak mencukupi. ' .
                                'Stock tersedia: ' .
                                $item->stock .
                                ', kebutuhan: ' .
                                $detail->qty
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | KURANGI STOCK
                |--------------------------------------------------------------------------
                */

                foreach (
                    $lockedOrder->details as $detail
                ) {
                    $item = Item::where(
                        'id',
                        $detail->item_id
                    )
                        ->lockForUpdate()
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | Kurangi stock
                    |--------------------------------------------------------------------------
                    */

                    $item->decrement(
                        'stock',
                        $detail->qty
                    );

                    $item->refresh();

                    /*
                    |--------------------------------------------------------------------------
                    | LOW STOCK NOTIFICATION
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

                        $admins = User::where(
                            'role_id',
                            1
                        )->get();

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

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | Jika stock sudah normal,
                        | hapus notifikasi low stock.
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
                }

                /*
                |--------------------------------------------------------------------------
                | PROCESS DISCOUNT
                |--------------------------------------------------------------------------
                |
                | PENTING:
                |
                | Kita TIDAK menghitung ulang PromotionService di sini.
                |
                | Promo sudah tersimpan ketika order dibuat:
                |
                | order_discounts
                |
                */

                foreach (
                    $lockedOrder->orderDiscounts
                    as $orderDiscount
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Ambil discount dan lock row
                    |--------------------------------------------------------------------------
                    */

                    $discount = Discount::where(
                        'id',
                        $orderDiscount->discount_id
                    )
                        ->lockForUpdate()
                        ->first();

                    if (!$discount) {
                        throw new \RuntimeException(
                            'Promo dengan ID ' .
                                $orderDiscount->discount_id .
                                ' tidak ditemukan.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Promo harus aktif
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
                    | CHECK DATE
                    |--------------------------------------------------------------------------
                    */

                    $today = now()->startOfDay();

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
                    | CHECK GLOBAL QUOTA
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
                    | CHECK USER QUOTA
                    |--------------------------------------------------------------------------
                    */

                    $usagePerUser =
                        (int) (
                            $discount->usage_per_user
                            ?? 0
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | 0 = unlimited
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $usagePerUser > 0 &&
                        $lockedOrder->user_id
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
                                    $lockedOrder->user_id
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
                    | CATAT DISCOUNT USAGE
                    |--------------------------------------------------------------------------
                    */

                    $discount->usages()->create([

                        'order_id' =>
                            $lockedOrder->id,

                        'user_id' =>
                            $lockedOrder->user_id,

                        'discount_amount' =>
                            (float)
                            $orderDiscount->discount_amount,

                    ]);
                }

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
                | ACTIVITY LOG
                |--------------------------------------------------------------------------
                */

                $this->activity->log(

                    'Order',

                    'Confirm Payment',

                    'Konfirmasi pembayaran ' .
                        $lockedOrder->invoice_number,

                    $lockedOrder,

                    $old,

                    $lockedOrder
                        ->fresh()
                        ->toArray()

                );

                /*
                |--------------------------------------------------------------------------
                | PAYMENT LOG
                |--------------------------------------------------------------------------
                */

                $lockedOrder
                    ->paymentLogs()
                    ->create([

                        'status' =>
                            'Completed',

                        'message' =>
                            'Pembayaran berhasil dikonfirmasi Admin dan order selesai.',

                    ]);

                /*
                |--------------------------------------------------------------------------
                | NOTIFICATION ORDER
                |--------------------------------------------------------------------------
                */

                Notification::where(
                    'order_id',
                    $lockedOrder->id
                )
                    ->update([

                        'is_read' =>
                            1,

                        'read_at' =>
                            now(),

                    ]);
            });

        } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dikonfirmasi dan voucher berhasil dicatat.',
        ]);
    }


    /**
     * =========================================================
     * REJECT PAYMENT
     * =========================================================
     */
    public function reject(Order $order)
    {
        /*
        |--------------------------------------------------------------------------
        | Pastikan order memang Paid
        |--------------------------------------------------------------------------
        */

        if ($order->status !== 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak sedang menunggu konfirmasi pembayaran.',
            ], 422);
        }

        $old = $order->toArray();

        /*
        |--------------------------------------------------------------------------
        | Kembalikan ke Waiting Payment
        |--------------------------------------------------------------------------
        */

        $order->update([

            'status' =>
                'Waiting Payment',

            'payment_proof' =>
                null,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        $this->activity->log(

            'Order',

            'Reject Payment',

            'Menolak pembayaran order ' .
                $order->invoice_number,

            $order,

            $old,

            $order
                ->fresh()
                ->toArray()

        );

        /*
        |--------------------------------------------------------------------------
        | Payment Log
        |--------------------------------------------------------------------------
        */

        $order
            ->paymentLogs()
            ->create([

                'status' =>
                    'Failed',

                'message' =>
                    'Pembayaran ditolak. Silakan upload ulang.',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Notification User
        |--------------------------------------------------------------------------
        */

        if ($order->user_id) {

            Notification::create([

                'user_id' =>
                    $order->user_id,

                'order_id' =>
                    $order->id,

                'title' =>
                    'Pembayaran Ditolak',

                'message' =>
                    'Pembayaran untuk order ' .
                    $order->invoice_number .
                    ' ditolak. Silakan upload ulang bukti pembayaran.',

            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil ditolak.',
        ]);
    }
}