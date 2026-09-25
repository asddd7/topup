<?php

namespace App\Jobs\Providers\MooGold;

use App\Models\MooGoldOrder;
use App\Integrations\MooGold\MooGoldOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckMooGoldOrderStatus implements
    ShouldQueue,
    ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * =========================================================
     * RETRY CONFIGURATION
     * =========================================================
     *
     * Retry hanya berlaku jika handle() melempar exception.
     *
     * Ini TIDAK menentukan interval normal pengecekan.
     */
    public int $tries = 5;


    public array $backoff = [
        30,
        60,
        120,
        300,
    ];


    /**
     * =========================================================
     * UNIQUE LOCK
     * =========================================================
     *
     * Satu order hanya mempunyai satu status check
     * pada saat yang sama.
     *
     * Lock dilepas saat job mulai diproses.
     *
     * Ini cocok karena job ini akan menjadwalkan
     * pengecekan berikutnya.
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


        /*
        |--------------------------------------------------------------------------
        | NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (
            !$mooGoldOrder
        ) {

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
        | CURRENT STATUS
        |--------------------------------------------------------------------------
        */

        $currentStatus =
            strtolower(
                trim(
                    (string)
                    $mooGoldOrder->moogold_status
                )
            );


        /*
        |--------------------------------------------------------------------------
        | FINAL STATUS
        |--------------------------------------------------------------------------
        */

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
        | LATEST STATUS
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
        |
        | Kalau belum selesai, cek lagi 15 detik kemudian.
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
                now()->addSeconds(15);


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
        | UNKNOWN
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
