<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * GET /api/v1/payments
     *
     * Menampilkan metode pembayaran aktif.
     */
    public function index(): JsonResponse
    {
        $payments = Payment::query()
            ->where('is_active', 1)
            ->orderBy('payment_name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar metode pembayaran berhasil diambil.',
            'data' => [
                'payments' => PaymentResource::collection($payments),
            ],
        ]);
    }
}