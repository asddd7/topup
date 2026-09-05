<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Midtrans\MidtransOrderService;
use Illuminate\Http\JsonResponse;
use Throwable;

class MidtransOrderController extends Controller
{
    public function __construct(
        protected MidtransOrderService $midtransOrderService
    ) {
    }

    /**
     * Create Midtrans Snap transaction for an order.
     */
    public function createSnap(Order $order): JsonResponse
    {
        try {
            $transaction =
                $this->midtransOrderService
                    ->createForOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Midtrans Snap transaction berhasil dibuat.',
                'data' => [
                    'order_id' =>
                        $transaction->order_id,

                    'midtrans_order_id' =>
                        $transaction->midtrans_order_id,

                    'gross_amount' =>
                        $transaction->gross_amount,

                    'snap_token' =>
                        $transaction->snap_token,

                    'snap_redirect_url' =>
                        $transaction->snap_redirect_url,

                    'transaction_status' =>
                        $transaction->transaction_status,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}