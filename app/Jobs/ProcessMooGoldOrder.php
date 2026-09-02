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
     *
     * Memberikan kesempatan recovery beberapa kali.
     *
     * Penting:
     * retry TIDAK membuat Partner Order ID baru.
     */
    public int $tries = 6;

    /**
     * Backoff bertahap.
     *
     * Retry:
     *
     * 30 detik
     * 60 detik
     * 120 detik
     * 300 detik
     * 600 detik
     */
    public array $backoff = [
        30,
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
     * Jangan terlalu pendek.
     *
     * Tujuannya mencegah job identik masuk bersamaan.
     *
     * Ini bukan satu-satunya idempotency mechanism.
     * Partner Order ID + DB UNIQUE tetap menjadi proteksi utama.
     */
    public int $uniqueFor = 1800;

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

        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER DETAIL
        |--------------------------------------------------------------------------
        */

        $detail =
            OrderDetail::with([
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

        $order =
            $detail->order;

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

        /*
        |--------------------------------------------------------------------------
        | SUCCESS LOG
        |--------------------------------------------------------------------------
        */

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

                'partner_order_id' =>
                    $mooGoldOrder->external_order_id,

                'moogold_status' =>
                    $mooGoldOrder->moogold_status,

                'attempts' =>
                    $mooGoldOrder->attempts,
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
            'ProcessMooGoldOrder gagal setelah seluruh retry.',
            [
                'order_detail_id' =>
                    $this->orderDetailId,

                'error' =>
                    $exception?->getMessage(),
            ]
        );
    }
}