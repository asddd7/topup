<?php

namespace App\Jobs\Providers\Ditusi;

use App\Models\OrderDetail;
use App\Integrations\Ditusi\DitusiOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDitusiOrder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum attempts.
     */
    public int $tries = 6;

    /**
     * Backoff between retries.
     */
    public array $backoff = [
        30,
        60,
        120,
        300,
        600,
    ];

    /**
     * Unique lock duration.
     */
    public int $uniqueFor = 1800;

    public function __construct(
        public int $orderDetailId
    ) {
    }

    /**
     * Unique job ID.
     */
    public function uniqueId(): string
    {
        return 'ditusi-order-detail:' .
            $this->orderDetailId;
    }

    /**
     * Prevent two jobs from processing
     * the same OrderDetail simultaneously.
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping(
                'ditusi-order-detail:' .
                $this->orderDetailId
            ),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(
        DitusiOrderService $ditusiOrderService
    ): void {
        $orderDetail = OrderDetail::query()
            ->with([
                'order',
                'item',
            ])
            ->find($this->orderDetailId);

        if (!$orderDetail) {
            Log::warning(
                'ProcessDitusiOrder: OrderDetail tidak ditemukan.',
                [
                    'order_detail_id' =>
                        $this->orderDetailId,
                ]
            );

            return;
        }

        $order = $orderDetail->order;

        if (!$order) {
            Log::warning(
                'ProcessDitusiOrder: Order tidak ditemukan.',
                [
                    'order_detail_id' =>
                        $this->orderDetailId,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Hanya proses order yang sudah Paid / Processing
        |--------------------------------------------------------------------------
        |
        | Paid:
        |   Order baru saja dibayar dan fulfillment boleh dimulai.
        |
        | Processing:
        |   Job retry / recovery.
        |
        */

        if (!in_array(
            $order->status,
            [
                'Paid',
                'Processing',
            ],
            true
        )) {
            Log::info(
                'ProcessDitusiOrder: status Order tidak memungkinkan fulfillment.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'status' =>
                        $order->status,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | DITUSI harus aktif dan memiliki mapping product
        |--------------------------------------------------------------------------
        */

        $item = $orderDetail->item;

        if (!$item) {
            Log::warning(
                'ProcessDitusiOrder: Item tidak ditemukan.',
                [
                    'order_detail_id' =>
                        $orderDetail->id,
                ]
            );

            return;
        }

        if (!$item->ditusi_enabled) {
            Log::info(
                'ProcessDitusiOrder: DITUSI tidak aktif untuk item.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'item_id' =>
                        $item->id,
                ]
            );

            return;
        }

        if (
            !$item->ditusi_product_code
        ) {
            Log::warning(
                'ProcessDitusiOrder: DITUSI product code kosong.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'item_id' =>
                        $item->id,
                ]
            );

            return;
        }

        Log::info(
            'ProcessDitusiOrder: mulai fulfillment.',
            [
                'order_id' =>
                    $order->id,

                'invoice_number' =>
                    $order->invoice_number,

                'order_detail_id' =>
                    $orderDetail->id,

                'item_id' =>
                    $item->id,

                'ditusi_product_code' =>
                    $item->ditusi_product_code,

                'attempt' =>
                    $this->attempts(),
            ]
        );

        try {
            $ditusiOrder =
                $ditusiOrderService
                    ->createFromOrderDetail(
                        $orderDetail
                    );

            Log::info(
                'ProcessDitusiOrder: fulfillment DITUSI berhasil diproses.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'ditusi_order_id' =>
                        $ditusiOrder->id,

                    'transaction_reference_id' =>
                        $ditusiOrder
                            ->transaction_reference_id,

                    'ditusi_transaction_id' =>
                        $ditusiOrder
                            ->ditusi_transaction_id,

                    'status' =>
                        $ditusiOrder->status,
                ]
            );

        } catch (Throwable $e) {

            Log::error(
                'ProcessDitusiOrder: fulfillment DITUSI gagal.',
                [
                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'attempt' =>
                        $this->attempts(),

                    'error' =>
                        $e->getMessage(),

                    'exception' =>
                        get_class($e),
                ]
            );

            throw $e;
        }
    }

    /**
     * Final failure after all retries.
     */
    public function failed(
        Throwable $exception
    ): void {
        Log::error(
            'ProcessDitusiOrder FAILED permanently.',
            [
                'order_detail_id' =>
                    $this->orderDetailId,

                'attempts' =>
                    $this->attempts(),

                'error' =>
                    $exception->getMessage(),

                'exception' =>
                    get_class($exception),
            ]
        );
    }
}

