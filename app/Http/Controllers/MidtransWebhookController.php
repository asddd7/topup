<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMidtransSignatureException;
use App\Integrations\Midtrans\MidtransWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransWebhookController extends Controller
{

    public function __construct(
        protected MidtransWebhookService $webhookService
    ) {
    }

    /**
     * =========================================================
     * HANDLE MIDTRANS WEBHOOK
     * =========================================================
     *
     * Midtrans
     *      ↓
     * Controller
     *      ↓
     * verify signature
     *      ↓
     * MidtransWebhookService
     *      ↓
     * update payment + order
     *      ↓
     * COMMIT
     *      ↓
     * TopUpFulfillmentService
     *      ↓
     * ProviderRegistry
     *      ↓
     * Provider Job
     */
    public function handle(
        Request $request
    ): JsonResponse {

        $payload = $request->all();

        $midtransOrderId =
            trim(
                (string) data_get(
                    $payload,
                    'order_id'
                )
            );

        $transactionStatus =
            strtolower(
                trim(
                    (string) data_get(
                        $payload,
                        'transaction_status'
                    )
                )
            );

        /*
        |--------------------------------------------------------------------------
        | LOG WEBHOOK MASUK
        |--------------------------------------------------------------------------
        */

        Log::info(
            '=== MIDTRANS WEBHOOK MASUK KE LARAVEL ===',
            [
                'method' =>
                    $request->method(),

                'url' =>
                    $request->fullUrl(),

                'scheme' =>
                    $request->getScheme(),

                'secure' =>
                    $request->isSecure(),

                'host' =>
                    $request->getHost(),

                'headers' => [
                    'x_forwarded_proto' =>
                        $request->header(
                            'X-Forwarded-Proto'
                        ),

                    'x_forwarded_for' =>
                        $request->header(
                            'X-Forwarded-For'
                        ),
                ],
            ]
        );

        Log::info(
            'Midtrans webhook diterima.',
            [
                'order_id' =>
                    $midtransOrderId,

                'transaction_status' =>
                    $transactionStatus,

                'transaction_id' =>
                    data_get(
                        $payload,
                        'transaction_id'
                    ),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | REQUIRED DATA
        |--------------------------------------------------------------------------
        */

        if (
            $midtransOrderId === ''
        ) {
            return response()->json(
                [
                    'success' => false,
                    'message' =>
                        'order_id tidak ditemukan.',
                ],
                422
            );
        }

        if (
            $transactionStatus === ''
        ) {
            return response()->json(
                [
                    'success' => false,
                    'message' =>
                        'transaction_status tidak ditemukan.',
                ],
                422
            );
        }


        try {
            $result = $this->webhookService->handle($payload);

            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'order_id' => $result['order_id'],
                'became_paid' => $result['became_paid'],
            ]);

        } catch (InvalidMidtransSignatureException $e) {

            Log::warning(
                'Midtrans webhook ditolak karena signature tidak valid.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'transaction_status' =>
                        $transactionStatus,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid signature.',
                ],
                403
            );

        } catch (Throwable $e) {

            Log::error(
                'Gagal memproses Midtrans webhook.',
                [
                    'midtrans_order_id' =>
                        $midtransOrderId,

                    'transaction_status' =>
                        $transactionStatus,

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Webhook processing failed.',
                ],
                500
            );
        }
    }
}