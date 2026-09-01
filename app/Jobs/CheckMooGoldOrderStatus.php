<?php

namespace App\Jobs;

use App\Models\MooGoldOrder;
use App\Services\MooGold\MooGoldOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckMooGoldOrderStatus implements ShouldQueue
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

    public int $backoff = 60;


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
     * HANDLE
     * =========================================================
     */

    public function handle(
        MooGoldOrderService $service
    ): void {

        /*
        |--------------------------------------------------------------------------
        | LOAD MOOGOLD ORDER
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder = MooGoldOrder::find(
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
        | JIKA SUDAH FINAL
        |--------------------------------------------------------------------------
        |
        | Tidak perlu request lagi ke MooGold.
        |
        */

        $currentStatus = strtolower(
            (string) $mooGoldOrder->moogold_status
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

                    'status' =>
                        $currentStatus,
                ]
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK STATUS KE MOOGOLD
        |--------------------------------------------------------------------------
        */

        $result = $service->checkStatus(
            $mooGoldOrder
        );


        /*
        |--------------------------------------------------------------------------
        | AMBIL STATUS TERBARU
        |--------------------------------------------------------------------------
        */

        $status = strtolower(
            (string) $result->moogold_status
        );

        Log::info(
            'DEBUG MooGold status result.',
            [
                'moo_gold_order_id' => $result->id,
                'moogold_order_id' => $result->moogold_order_id,
                'status' => $status,
                'result_class' => get_class($result),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MooGold order status checked.',
            [

                'moo_gold_order_id' =>
                    $result->id,

                'moogold_order_id' =>
                    $result->moogold_order_id,

                'status' =>
                    $status,

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | JIKA MASIH PROCESSING / PENDING
        |--------------------------------------------------------------------------
        |
        | Jadwalkan pengecekan berikutnya.
        |
        */

        if (
            in_array(
                $status,
                [
                    'pending',
                    'processing',
                    'sending',
                ],
                true
            )
        ) {

            $nextCheck = now()->addMinutes(2);

            CheckMooGoldOrderStatus::dispatch(
                $result->id
            )->delay($nextCheck);

            Log::info(
                'MooGold order akan dicek kembali.',
                [
                    'moo_gold_order_id' => $result->id,
                    'moogold_order_id' => $result->moogold_order_id,
                    'status' => $status,
                    'next_check' => $nextCheck,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL STATUS
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

                    'status' =>
                        $status,

                ]
            );
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

                    'status' =>
                        $status,

                ]
            );
        }
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
            'CheckMooGoldOrderStatus gagal setelah retry.',
            [

                'moo_gold_order_id' =>
                    $this->mooGoldOrderId,

                'error' =>
                    $exception?->getMessage(),

            ]
        );
    }
}

