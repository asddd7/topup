<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\MooGold\MooGoldService;
use App\Models\Order;
use App\Services\TopUp\TopUpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * POST /api/v1/admin/orders/{order}/payment/approve
     *
     * Admin menyetujui pembayaran.
     */
    public function approvePayment(
        Request $request,
        Order $order
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Pastikan order masih menunggu pembayaran
        |--------------------------------------------------------------------------
        */

        if ($order->status !== 'Waiting Payment') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat diverifikasi pada status saat ini.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Pastikan bukti pembayaran tersedia
        |--------------------------------------------------------------------------
        */

        if (!$order->payment_proof) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti pembayaran belum tersedia.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Update order
        |--------------------------------------------------------------------------
        */

        $order->update([
            'status' => 'Paid',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Payment Log
        |--------------------------------------------------------------------------
        */

        $order->paymentLogs()->create([
            'status' => 'Paid',

            'message' =>
                'Pembayaran telah diverifikasi dan diterima.',

            'logged_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Load relationship
        |--------------------------------------------------------------------------
        */

        $order->load([
            'game',
            'payment',
            'details.item.category',
            'paymentLogs',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diverifikasi.',
            'data' => [
                'order' => new OrderResource($order),
            ],
        ]);
    }


    /**
     * POST /api/v1/admin/orders/{order}/payment/reject
     *
     * Admin menolak pembayaran.
     */
    public function rejectPayment(
        Request $request,
        Order $order
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Pastikan order masih menunggu pembayaran
        |--------------------------------------------------------------------------
        */

        if ($order->status !== 'Waiting Payment') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat ditolak pada status saat ini.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Pastikan bukti pembayaran tersedia
        |--------------------------------------------------------------------------
        */

        if (!$order->payment_proof) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti pembayaran belum tersedia.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Alasan penolakan
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Update order
        |--------------------------------------------------------------------------
        */

        $order->update([
            'status' => 'Pending',
            'notes' => $validated['notes'] ?? null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Payment Log
        |--------------------------------------------------------------------------
        */

        $order->paymentLogs()->create([
            'status' => 'Failed',

            'message' =>
                'Pembayaran ditolak.' .
                (
                    !empty($validated['notes'])
                        ? ' Alasan: ' . $validated['notes']
                        : ''
                ),

            'logged_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Load relationship
        |--------------------------------------------------------------------------
        */

        $order->load([
            'game',
            'payment',
            'details.item.category',
            'paymentLogs',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil ditolak.',
            'data' => [
                'order' => new OrderResource($order),
            ],
        ]);
    }

/**
 * POST /api/v1/admin/orders/{order}/complete
 *
 * Memproses fulfillment item dan menyelesaikan order.
 */
public function completeOrder(
    Request $request,
    Order $order,
    TopUpService $topUpService
): JsonResponse {

    if ($order->status !== 'Paid') {

        return response()->json([
            'success' => false,
            'message' =>
                'Order hanya dapat diproses setelah pembayaran berstatus Paid.',
        ], 422);
    }


    $result =
        $topUpService->complete($order);


    if (!$result['success']) {

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }


    $completedOrder =
        $result['order'];


    return response()->json([
        'success' => true,

        'message' =>
            $result['message'],

        'data' => [
            'order' =>
                new OrderResource(
                    $completedOrder
                ),
        ],
    ]);
}
/**
 * POST /api/v1/admin/orders/{order}/process
 *
 * Memproses top-up order melalui provider.
 */
public function processOrder(
    Request $request,
    Order $order,
    TopUpService $topUpService,
    MooGoldService $mooGold
): JsonResponse {

    if ($order->status !== 'Paid') {

        return response()->json([
            'success' => false,
            'message' =>
                'Order hanya dapat diproses ketika statusnya Paid.',
        ], 422);
    }


$result =
    $topUpService->processProvider(
        $order,
        $mooGold
    );


    if (!$result['success']) {

        return response()->json([
            'success' => false,
            'message' =>
                $result['message'],

            'data' => [
                'order' => [
                    'id' =>
                        $order->id,

                    'invoice' =>
                        $order->invoice_number,

                    'status' =>
                        $order->status,
                ],
            ],
        ], 422);
    }


    $order->refresh();

    $order->load([
        'game',
        'payment',
        'details.item.category',
        'paymentLogs',
    ]);


    return response()->json([
        'success' => true,

        'message' =>
            'Top-up berhasil diproses.',

        'data' => [
            'order' =>
                new OrderResource($order),
        ],
    ]);
}
}