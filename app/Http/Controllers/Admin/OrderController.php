<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\MooGold\MooGoldService;
use App\Models\Discount;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TopUp\TopUpService;
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
 * APPROVE PAYMENT
 * =========================================================
 *
 * Waiting Payment
 *       ↓
 *     Paid
 *
 * Tidak mengurangi stock.
 * Tidak menyelesaikan order.
 */
public function approve(Order $order)
{
    /*
    |--------------------------------------------------------------------------
    | Status harus Waiting Payment
    |--------------------------------------------------------------------------
    */

    if ($order->status !== 'Waiting Payment') {

        return back()->with(
            'error',
            'Order hanya dapat disetujui ketika status Waiting Payment.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Bukti pembayaran wajib ada
    |--------------------------------------------------------------------------
    */

    if (!$order->payment_proof) {

        return back()->with(
            'error',
            'Bukti pembayaran belum tersedia.'
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
            | CEK STATUS
            |--------------------------------------------------------------------------
            */

            if ($lockedOrder->status !== 'Waiting Payment') {

                throw new \RuntimeException(
                    'Order sudah diproses atau status sudah berubah.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */

            $old = $lockedOrder->toArray();

            $lockedOrder->update([
                'status' => 'Paid',
            ]);


            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

            $this->activity->log(

                'Order',

                'Approve Payment',

                'Menyetujui pembayaran order ' .
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
            |
            | PaymentLog hanya boleh menggunakan enum:
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
                        'Pembayaran telah diverifikasi dan diterima oleh Admin.',

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
                        'Pembayaran Diterima',

                    'message' =>
                        'Pembayaran order ' .
                        $lockedOrder->invoice_number .
                        ' telah diverifikasi. Order sedang diproses.',

                    'is_read' =>
                        0,

                    'read_at' =>
                        null,

                ]);
            }

        });


        return back()->with(
            'success',
            'Pembayaran berhasil disetujui. Order sekarang berstatus Paid.'
        );


    } catch (\Throwable $e) {

        return back()->with(
            'error',
            $e->getMessage()
        );
    }
}

/**
 * =========================================================
 * CONFIRM / COMPLETE ORDER
 * =========================================================
 *
 * Paid
 *   ↓
 * TopUpService::complete()
 *   ↓
 * Lock Order
 *   ↓
 * Check Item
 *   ↓
 * Check Stock
 *   ↓
 * Reduce Stock
 *   ↓
 * Process Discount
 *   ↓
 * Completed
 */
public function confirm(
    Order $order,
    TopUpService $topUpService,
    MooGoldService $mooGold
) {

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    if ($order->status !== 'Paid') {

        return back()->with(
            'error',
            'Order harus berstatus Paid.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPLETE
    |--------------------------------------------------------------------------
    */

    $result = $topUpService->complete($order);


    /*
    |--------------------------------------------------------------------------
    | FAILED
    |--------------------------------------------------------------------------
    */

    if (!$result['success']) {

        return back()->with(
            'error',
            $result['message']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('admin.order.show', $order)
        ->with(
            'success',
            $result['message']
        );
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