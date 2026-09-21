<?php

namespace App\Jobs\Providers\Ditusi;

use App\Models\DitusiOrder;
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

class CheckDitusiOrderStatus implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum attempts for one queued execution.
     */
    public int $tries = 5;

    /**
     * Queue retry backoff.
     */
    public array $backoff = [
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
        public int $ditusiOrderId
    ) {
    }

    /**
     * Unique job ID.
     */
    public function uniqueId(): string
    {
        return 'ditusi-status:' .
            $this->ditusiOrderId;
    }

    /**
     * Prevent simultaneous status checks.
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping(
                'ditusi-status:' .
                $this->ditusiOrderId
            ),
        ];
    }

    /**
     * Execute status check.
     */
    public function handle(
        DitusiOrderService $ditusiOrderService
    ): void {
        $ditusiOrder = DitusiOrder::query()
            ->with([
                'order',
                'orderDetail',
            ])
            ->find($this->ditusiOrderId);

        if (!$ditusiOrder) {
            Log::warning(
                'CheckDitusiOrderStatus: DitusiOrder tidak ditemukan.',
                [
                    'ditusi_order_id' =>
                        $this->ditusiOrderId,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Tidak ada transaction ID
        |--------------------------------------------------------------------------
        */

        if (!$ditusiOrder->ditusi_transaction_id) {
            Log::warning(
                'CheckDitusiOrderStatus: transaction ID belum tersedia.',
                [
                    'ditusi_order_id' =>
                        $ditusiOrder->id,

                    'transaction_reference_id' =>
                        $ditusiOrder
                            ->transaction_reference_id,

                    'status' =>
                        $ditusiOrder->status,
                ]
            );

            /*
             * Jangan melakukan check ke DITUSI dengan
             * transaction ID kosong.
             *
             * ProcessDitusiOrder bertugas melakukan recovery/create.
             */
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Jangan polling transaksi final
        |--------------------------------------------------------------------------
        */

        if (in_array(
            strtolower(
                trim(
                    (string) $ditusiOrder->status
                )
            ),
            [
                'success',
                'refunded',
                'failed',
            ],
            true
        )) {
            Log::info(
                'CheckDitusiOrderStatus: status sudah final.',
                [
                    'ditusi_order_id' =>
                        $ditusiOrder->id,

                    'transaction_id' =>
                        $ditusiOrder
                            ->ditusi_transaction_id,

                    'status' =>
                        $ditusiOrder->status,
                ]
            );

            return;
        }

        Log::info(
            'CheckDitusiOrderStatus: checking transaction.',
            [
                'ditusi_order_id' =>
                    $ditusiOrder->id,

                'transaction_id' =>
                    $ditusiOrder
                        ->ditusi_transaction_id,

                'current_status' =>
                    $ditusiOrder->status,

                'attempt' =>
                    $this->attempts(),
            ]
        );

        try {

            $ditusiOrder =
                $ditusiOrderService
                    ->checkStatus(
                        $ditusiOrder
                    );

            $status = strtolower(
                trim(
                    (string) $ditusiOrder->status
                )
            );

            Log::info(
                'CheckDitusiOrderStatus: status received.',
                [
                    'ditusi_order_id' =>
                        $ditusiOrder->id,

                    'transaction_id' =>
                        $ditusiOrder
                            ->ditusi_transaction_id,

                    'status' =>
                        $status,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Final SUCCESS
            |--------------------------------------------------------------------------
            */

            if ($status === 'success') {

                Log::info(
                    'CheckDitusiOrderStatus: transaksi SUCCESS.',
                    [
                        'ditusi_order_id' =>
                            $ditusiOrder->id,

                        'transaction_id' =>
                            $ditusiOrder
                                ->ditusi_transaction_id,

                        'voucher_code' =>
                            $ditusiOrder
                                ->voucher_code,
                    ]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Final REFUNDED
            |--------------------------------------------------------------------------
            */

            if ($status === 'refunded') {

                Log::warning(
                    'CheckDitusiOrderStatus: transaksi REFUNDED.',
                    [
                        'ditusi_order_id' =>
                            $ditusiOrder->id,

                        'transaction_id' =>
                            $ditusiOrder
                                ->ditusi_transaction_id,
                    ]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Final FAILED
            |--------------------------------------------------------------------------
            */

            if ($status === 'failed') {

                Log::error(
                    'CheckDitusiOrderStatus: transaksi FAILED.',
                    [
                        'ditusi_order_id' =>
                            $ditusiOrder->id,

                        'transaction_id' =>
                            $ditusiOrder
                                ->ditusi_transaction_id,
                    ]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Pending / Processing
            |--------------------------------------------------------------------------
            |
            | DITUSI:
            |
            | PENDING ORDER
            | PROCESS
            |
            | Keduanya belum final.
            |
            */

            if (in_array(
                $status,
                [
                    'pending',
                    'processing',
                ],
                true
            )) {

                self::dispatch(
                    $ditusiOrder->id
                )->delay(
                    now()->addMinutes(2)
                );

                Log::info(
                    'CheckDitusiOrderStatus: transaksi belum final, check berikutnya dijadwalkan.',
                    [
                        'ditusi_order_id' =>
                            $ditusiOrder->id,

                        'transaction_id' =>
                            $ditusiOrder
                                ->ditusi_transaction_id,

                        'status' =>
                            $status,

                        'next_check' =>
                            now()
                                ->addMinutes(2)
                                ->toDateTimeString(),
                    ]
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Unknown status
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'CheckDitusiOrderStatus: status DITUSI tidak dikenal.',
                [
                    'ditusi_order_id' =>
                        $ditusiOrder->id,

                    'transaction_id' =>
                        $ditusiOrder
                            ->ditusi_transaction_id,

                    'status' =>
                        $status,
                ]
            );

            /*
             * Tetap polling untuk status yang belum kita kenali.
             */
            self::dispatch(
                $ditusiOrder->id
            )->delay(
                now()->addMinutes(2)
            );

        } catch (Throwable $e) {

            Log::error(
                'CheckDitusiOrderStatus: gagal check status.',
                [
                    'ditusi_order_id' =>
                        $ditusiOrder->id,

                    'transaction_id' =>
                        $ditusiOrder
                            ->ditusi_transaction_id,

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
     * Final failure after queue retries.
     */
    public function failed(
        Throwable $exception
    ): void {
        Log::error(
            'CheckDitusiOrderStatus FAILED permanently.',
            [
                'ditusi_order_id' =>
                    $this->ditusiOrderId,

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
