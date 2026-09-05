<?php

namespace App\Jobs;

use App\Models\MooGoldOrder;
use App\Services\MooGold\MooGoldOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckMooGoldOrderStatus implements
    ShouldQueue,
    ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * =========================================================
     * RETRY CONFIGURATION
     * =========================================================
     */
    public int $tries = 5;

    public array $backoff = [
        60,
        120,
        300,
        600,
    ];

    /**
     * =========================================================
     * UNIQUE LOCK
     * =========================================================
     *
     * Satu MooGoldOrder hanya boleh mempunyai satu
     * CheckMooGoldOrderStatus aktif.
     *
     * Ini mencegah:
     *
     * Check #1
     * Check #2
     * Check #3
     *
     * berjalan bersamaan untuk transaksi yang sama.
     */
    public int $uniqueFor = 1800;

    /**
     * =========================================================
     * CONSTRUCTOR
     * =========================================================
     */
    public function __construct(
        public int $mooGoldOrderId
    ) {
    }

    /**
     * =========================================================
     * UNIQUE ID
     * =========================================================
     */
    public function uniqueId(): string
    {
        return 'check-moogold-order-status-' .
            $this->mooGoldOrderId;
    }

    /**
     * =========================================================
     * HANDLE
     * =========================================================
     */
    public function handle(
        MooGoldOrderService $service
    ): void {

        /*
        |--------------------------------------------------------------------------
        | LOAD
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder =
            MooGoldOrder::with([
                'order',
            ])->find(
                $this->mooGoldOrderId
            );

        if (!$mooGoldOrder) {

            Log::warning(
                'CheckMooGoldOrderStatus: MooGoldOrder tidak ditemukan.',
                [
                    'moo_gold_order_id' =>
                        $this->mooGoldOrderId,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER ID BELUM TERSEDIA
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $mooGoldOrder->moogold_order_id
            )
        ) {

            Log::warning(
                'CheckMooGoldOrderStatus dilewati karena '
                . 'MooGold Order ID belum tersedia.',
                [
                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'partner_order_id' =>
                        $mooGoldOrder->external_order_id,

                    'status' =>
                        $mooGoldOrder->moogold_status,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL STATUS
        |--------------------------------------------------------------------------
        */

        $currentStatus =
            strtolower(
                trim(
                    (string)
                    $mooGoldOrder->moogold_status
                )
            );

        if (
            in_array(
                $currentStatus,
                [
                    'success',
                    'successful',
                    'completed',
                    'complete',
                    'successfully',
                    'failed',
                    'refunded',
                ],
                true
            )
        ) {

            Log::info(
                'MooGold order sudah final. Tidak perlu dicek ulang.',
                [
                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'moogold_order_id' =>
                        $mooGoldOrder->moogold_order_id,

                    'partner_order_id' =>
                        $mooGoldOrder->external_order_id,

                    'status' =>
                        $currentStatus,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK STATUS
        |--------------------------------------------------------------------------
        */

        $result =
            $service->checkStatus(
                $mooGoldOrder
            );

        /*
        |--------------------------------------------------------------------------
        | STATUS TERBARU
        |--------------------------------------------------------------------------
        */

        $status =
            strtolower(
                trim(
                    (string)
                    $result->moogold_status
                )
            );

        Log::info(
            'MooGold order status checked.',
            [
                'moo_gold_order_id' =>
                    $result->id,

                'moogold_order_id' =>
                    $result->moogold_order_id,

                'partner_order_id' =>
                    $result->external_order_id,

                'status' =>
                    $status,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PROCESSING
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,
                [
                    'pending',
                    'processing',
                    'sending',
                    'creating',
                    'unknown',
                ],
                true
            )
        ) {

            $nextCheck =
                now()->addMinutes(2);

            self::dispatch(
                $result->id
            )->delay(
                $nextCheck
            );

            Log::info(
                'MooGold order akan dicek kembali.',
                [
                    'moo_gold_order_id' =>
                        $result->id,

                    'moogold_order_id' =>
                        $result->moogold_order_id,

                    'partner_order_id' =>
                        $result->external_order_id,

                    'status' =>
                        $status,

                    'next_check' =>
                        $nextCheck,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,
                [
                    'success',
                    'successful',
                    'completed',
                    'complete',
                    'successfully',
                ],
                true
            )
        ) {

            Log::info(
                'MooGold order COMPLETED.',
                [
                    'moo_gold_order_id' =>
                        $result->id,

                    'moogold_order_id' =>
                        $result->moogold_order_id,

                    'partner_order_id' =>
                        $result->external_order_id,

                    'status' =>
                        $status,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | FAILED / REFUNDED
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $status,
                [
                    'failed',
                    'refunded',
                ],
                true
            )
        ) {

            Log::warning(
                'MooGold order berakhir gagal/refund.',
                [
                    'moo_gold_order_id' =>
                        $result->id,

                    'moogold_order_id' =>
                        $result->moogold_order_id,

                    'partner_order_id' =>
                        $result->external_order_id,

                    'status' =>
                        $status,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | UNKNOWN STATUS
        |--------------------------------------------------------------------------
        */

        Log::warning(
            'CheckMooGoldOrderStatus mendapatkan status '
            . 'yang belum dikenali.',
            [
                'moo_gold_order_id' =>
                    $result->id,

                'moogold_order_id' =>
                    $result->moogold_order_id,

                'partner_order_id' =>
                    $result->external_order_id,

                'status' =>
                    $status,
            ]
        );
    }

    /**
     * =========================================================
     * FAILED
     * =========================================================
     */
    public function failed(
        ?Throwable $exception
    ): void {

        Log::error(
            'CheckMooGoldOrderStatus gagal setelah seluruh retry.',
            [
                'moo_gold_order_id' =>
                    $this->mooGoldOrderId,

                'error' =>
                    $exception?->getMessage(),
            ]
        );
    }
}