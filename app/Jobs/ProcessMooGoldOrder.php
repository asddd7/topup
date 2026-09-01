<?php

namespace App\Jobs;

use App\Models\OrderDetail;
use App\Services\MooGold\MooGoldOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessMooGoldOrder implements
    ShouldQueue,
    ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * =========================================================
     * RETRY
     * =========================================================
     */

    public int $tries = 3;

    public int $backoff = 30;


    /**
     * =========================================================
     * UNIQUE LOCK
     * =========================================================
     */

    public int $uniqueFor = 600;


    /**
     * =========================================================
     * CONSTRUCTOR
     * =========================================================
     */

    public function __construct(
        public int $orderDetailId
    ) {
    }


    /**
     * =========================================================
     * UNIQUE ID
     * =========================================================
     */

    public function uniqueId(): string
    {
        return 'process-moogold-order-' .
            $this->orderDetailId;
    }


    /**
     * =========================================================
     * HANDLE
     * =========================================================
     */

    public function handle(
        MooGoldOrderService $service
    ): void {

        $detail = OrderDetail::with([
            'order',
            'item',
        ])->find(
            $this->orderDetailId
        );


        if (!$detail) {

            Log::warning(
                'ProcessMooGoldOrder: OrderDetail tidak ditemukan.',
                [
                    'order_detail_id' =>
                        $this->orderDetailId,
                ]
            );

            return;
        }


        if (!$detail->order) {

            Log::warning(
                'ProcessMooGoldOrder: Order tidak ditemukan.',
                [
                    'order_detail_id' =>
                        $detail->id,
                ]
            );

            return;
        }


        $order = $detail->order;


        /*
        |--------------------------------------------------------------------------
        | HARUS PAID
        |--------------------------------------------------------------------------
        */

        if ($order->status !== 'Paid') {

            Log::info(
                'ProcessMooGoldOrder dilewati karena Order tidak Paid.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $detail->id,

                    'status' =>
                        $order->status,
                ]
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | PROCESS
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder =
            $service->createFromOrderDetail(
                $detail
            );


        Log::info(
            'ProcessMooGoldOrder selesai.',
            [
                'order_id' =>
                    $order->id,

                'order_detail_id' =>
                    $detail->id,

                'moo_gold_order_id' =>
                    $mooGoldOrder->id,

                'moogold_order_id' =>
                    $mooGoldOrder->moogold_order_id,

                'moogold_status' =>
                    $mooGoldOrder->moogold_status,
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
            'ProcessMooGoldOrder gagal setelah retry.',
            [
                'order_detail_id' =>
                    $this->orderDetailId,

                'error' =>
                    $exception?->getMessage(),
            ]
        );
    }
}