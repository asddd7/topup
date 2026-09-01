<?php

namespace App\Services\MooGold;

use App\Jobs\CheckMooGoldOrderStatus;
use App\Models\OrderDetail;
use App\Models\MooGoldOrder;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MooGoldOrderService
{
    public function __construct(
        protected MooGoldService $mooGold
    ) {
    }

    /**
     * =========================================================
     * CREATE MOOGOLD ORDER
     * =========================================================
     *
     * 1 OrderDetail = 1 MooGoldOrder
     *
     * Anti Double Order:
     * - order_detail_id UNIQUE
     * - firstOrCreate()
     * - jika sudah memiliki MooGold Order ID,
     *   jangan create order lagi
     */
    public function createFromOrderDetail(
        OrderDetail $orderDetail
    ): MooGoldOrder {

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATION
        |--------------------------------------------------------------------------
        */

        $orderDetail->loadMissing([
            'order',
            'item',
        ]);

        $order = $orderDetail->order;
        $item  = $orderDetail->item;


        /*
        |--------------------------------------------------------------------------
        | VALIDATION RELATION
        |--------------------------------------------------------------------------
        */

        if (!$order) {

            throw new RuntimeException(
                'Order tidak ditemukan.'
            );
        }

        if (!$item) {

            throw new RuntimeException(
                'Item pada OrderDetail tidak ditemukan.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI ORDER STATUS
        |--------------------------------------------------------------------------
        |
        | MooGold hanya boleh diproses jika pembayaran sudah Paid.
        |
        */

        if ($order->status !== 'Paid') {

            throw new RuntimeException(
                'Order belum berstatus Paid. '
                . 'MooGold tidak boleh diproses.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CEK MAPPING MOOGOLD
        |--------------------------------------------------------------------------
        */

        if (
            empty($item->moogold_category_id) ||
            empty($item->moogold_variation_id)
        ) {

            throw new RuntimeException(
                'Item "' .
                $item->item_name .
                '" belum memiliki mapping MooGold.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PLAYER DATA
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | {
        |     "uid": "1963315211",
        |     "server": "19248"
        | }
        |
        */

        $playerData = $order->player_data;

        if (!is_array($playerData)) {

            $playerData = json_decode(
                (string) $playerData,
                true
            );
        }

        if (!is_array($playerData)) {

            throw new RuntimeException(
                'Player data order tidak valid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | USER ID
        |--------------------------------------------------------------------------
        */

        $userId =
            $playerData['uid']
            ?? $playerData['user_id']
            ?? $playerData['User ID']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | SERVER
        |--------------------------------------------------------------------------
        */

        $server =
            $playerData['server']
            ?? $playerData['server_id']
            ?? $playerData['Server']
            ?? null;


        if (empty($userId)) {

            throw new RuntimeException(
                'Player UID / User ID belum tersedia di player_data.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EXTERNAL ORDER ID
        |--------------------------------------------------------------------------
        |
        | Harus deterministic.
        |
        | Order 22 + Detail 22:
        |
        | MG-22-22
        |
        | Jika Job dijalankan ulang,
        | external ID tetap sama.
        |
        */

        $externalOrderId =
            'MG-' .
            $order->id .
            '-' .
            $orderDetail->id;


        /*
        |--------------------------------------------------------------------------
        | REQUEST PAYLOAD
        |--------------------------------------------------------------------------
        |
        | Simpan snapshot request yang akan dikirim ke MooGold.
        |
        */

        $requestPayload = [

            'category' =>
                (string) $item->moogold_category_id,

            'product-id' =>
                (string) $item->moogold_variation_id,

            'quantity' =>
                (string) $orderDetail->qty,

            'User ID' =>
                (string) $userId,

        ];


        if (
            $server !== null &&
            $server !== ''
        ) {

            $requestPayload['Server ID'] =
                (string) $server;
        }


        /*
        |--------------------------------------------------------------------------
        | FIND / CREATE LOCAL MOOGOLD ORDER
        |--------------------------------------------------------------------------
        |
        | order_detail_id memiliki UNIQUE constraint.
        |
        | Artinya:
        |
        | 1 OrderDetail
        |      ↓
        | 1 MooGoldOrder
        |
        */

        $mooGoldOrder = MooGoldOrder::firstOrCreate(

            [
                'order_detail_id' =>
                    $orderDetail->id,
            ],

            [
                'order_id' =>
                    $order->id,

                'item_id' =>
                    $item->id,

                'external_order_id' =>
                    $externalOrderId,

                'moogold_category_id' =>
                    $item->moogold_category_id,

                'moogold_product_id' =>
                    $item->moogold_product_id,

                'moogold_variation_id' =>
                    $item->moogold_variation_id,

                'moogold_status' =>
                    'pending',

                'request_payload' =>
                    $requestPayload,

                'attempts' =>
                    0,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | ANTI DOUBLE CREATE
        |--------------------------------------------------------------------------
        |
        | Jika sudah memiliki MooGold Order ID,
        | transaksi MooGold sudah pernah berhasil dibuat.
        |
        | Jangan pernah create_order lagi.
        |
        */

        if (
            !empty(
                $mooGoldOrder->moogold_order_id
            )
        ) {

            Log::info(
                'MooGold order sudah pernah dibuat. '
                . 'Tidak mengirim create_order ulang.',
                [

                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'moogold_order_id' =>
                        $mooGoldOrder->moogold_order_id,

                    'external_order_id' =>
                        $mooGoldOrder->external_order_id,

                    'moogold_status' =>
                        $mooGoldOrder->moogold_status,

                ]
            );

            return $mooGoldOrder->fresh();
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE REQUEST SNAPSHOT
        |--------------------------------------------------------------------------
        |
        | Berguna jika record sebelumnya dibuat tetapi
        | belum sempat dikirim ke MooGold.
        |
        */

        $mooGoldOrder->update([

            'order_id' =>
                $order->id,

            'item_id' =>
                $item->id,

            'external_order_id' =>
                $externalOrderId,

            'moogold_category_id' =>
                $item->moogold_category_id,

            'moogold_product_id' =>
                $item->moogold_product_id,

            'moogold_variation_id' =>
                $item->moogold_variation_id,

            'request_payload' =>
                $requestPayload,

        ]);


        /*
        |--------------------------------------------------------------------------
        | SEND TO MOOGOLD
        |--------------------------------------------------------------------------
        */

        try {

            /*
            |--------------------------------------------------------------------------
            | INCREMENT ATTEMPTS
            |--------------------------------------------------------------------------
            */

            $mooGoldOrder->increment(
                'attempts'
            );

            $mooGoldOrder->update([

                'last_attempt_at' =>
                    now(),

                'moogold_status' =>
                    'processing',

                'error_message' =>
                    null,

            ]);


            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */

            $response =
                $this->mooGold->createOrder(

                    (int)
                    $item->moogold_category_id,

                    $externalOrderId,

                    (string)
                    $item->moogold_variation_id,

                    (int)
                    $orderDetail->qty,

                    (string)
                    $userId,

                    $server !== null
                        ? (string) $server
                        : null

                );


            /*
            |--------------------------------------------------------------------------
            | EXTRACT RESULT
            |--------------------------------------------------------------------------
            */

            $moogoldOrderId =
                $this->extractOrderId(
                    $response
                );

            $moogoldStatus =
                $this->extractStatus(
                    $response
                );


            /*
            |--------------------------------------------------------------------------
            | SAVE MOOGOLD RESPONSE
            |--------------------------------------------------------------------------
            */

            $mooGoldOrder->update([

                'response_payload' =>
                    $response,

                'ordered_at' =>
                    now(),

                'moogold_status' =>
                    $moogoldStatus,

                'moogold_order_id' =>
                    $moogoldOrderId,

                'error_message' =>
                    null,

            ]);


            /*
            |--------------------------------------------------------------------------
            | SYNC LOCAL ORDER STATUS
            |--------------------------------------------------------------------------
            */

            $localOrderStatus =
                $this->mapOrderStatus(
                    $moogoldStatus
                );


            $order->update([

                'moogold_order_id' =>
                    $moogoldOrderId,

                'moogold_status' =>
                    $moogoldStatus,

                'moogold_response' =>
                    $response,

                'moogold_ordered_at' =>
                    now(),

                'status' =>
                    $localOrderStatus,

            ]);


            /*
            |--------------------------------------------------------------------------
            | DISPATCH STATUS CHECK
            |--------------------------------------------------------------------------
            |
            | Hanya jika MooGold memberikan Order ID.
            |
            */

            if ($moogoldOrderId) {

                /*
                |--------------------------------------------------------------------------
                | Jika sudah final, tidak perlu polling.
                |--------------------------------------------------------------------------
                */

                if (
                    !$this->isFinalStatus(
                        $moogoldStatus
                    )
                ) {

                    CheckMooGoldOrderStatus::dispatch(
                        $mooGoldOrder->id
                    )->delay(
                        now()->addMinutes(2)
                    );

                    Log::info(
                        'CheckMooGoldOrderStatus berhasil dijadwalkan.',
                        [

                            'order_id' =>
                                $order->id,

                            'order_detail_id' =>
                                $orderDetail->id,

                            'moo_gold_order_id' =>
                                $mooGoldOrder->id,

                            'moogold_order_id' =>
                                $moogoldOrderId,

                            'moogold_status' =>
                                $moogoldStatus,

                            'next_check' =>
                                now()->addMinutes(2),

                        ]
                    );

                } else {

                    Log::info(
                        'MooGold order sudah final. '
                        . 'Status check tidak dijadwalkan.',
                        [

                            'order_id' =>
                                $order->id,

                            'order_detail_id' =>
                                $orderDetail->id,

                            'moo_gold_order_id' =>
                                $mooGoldOrder->id,

                            'moogold_order_id' =>
                                $moogoldOrderId,

                            'moogold_status' =>
                                $moogoldStatus,

                        ]
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | LOG SUCCESS
            |--------------------------------------------------------------------------
            */

            Log::info(
                'MooGold order berhasil diproses.',
                [

                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'moogold_order_id' =>
                        $moogoldOrderId,

                    'external_order_id' =>
                        $externalOrderId,

                    'moogold_status' =>
                        $moogoldStatus,

                ]
            );


            return $mooGoldOrder->fresh();

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | SAVE ERROR
            |--------------------------------------------------------------------------
            */

            $mooGoldOrder->update([

                'moogold_status' =>
                    'failed',

                'error_message' =>
                    $e->getMessage(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | LOG ERROR
            |--------------------------------------------------------------------------
            */

            Log::error(
                'MooGold order failed.',
                [

                    'order_id' =>
                        $order->id,

                    'order_detail_id' =>
                        $orderDetail->id,

                    'moo_gold_order_id' =>
                        $mooGoldOrder->id,

                    'external_order_id' =>
                        $externalOrderId,

                    'attempts' =>
                        $mooGoldOrder->fresh()->attempts,

                    'error' =>
                        $e->getMessage(),

                ]
            );


            throw $e;
        }
    }


    /**
     * =========================================================
     * CHECK & SYNC MOOGOLD ORDER STATUS
     * =========================================================
     */
    public function checkStatus(
        MooGoldOrder $mooGoldOrder
    ): MooGoldOrder {

        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $mooGoldOrder->moogold_order_id
            )
        ) {

            throw new RuntimeException(
                'MooGold Order ID belum tersedia.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CEK JIKA SUDAH FINAL
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
            $this->isFinalStatus(
                $currentStatus
            )
        ) {

            return $mooGoldOrder->fresh();
        }


        /*
        |--------------------------------------------------------------------------
        | REQUEST KE MOOGOLD
        |--------------------------------------------------------------------------
        */

        $response =
            $this->mooGold->order(
                (int)
                $mooGoldOrder->moogold_order_id
            );


        /*
        |--------------------------------------------------------------------------
        | EXTRACT STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            $this->extractStatus(
                $response
            );


        /*
        |--------------------------------------------------------------------------
        | SIMPAN RESPONSE
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder->response_payload =
            $response;

        $mooGoldOrder->moogold_status =
            $status;


        /*
        |--------------------------------------------------------------------------
        | AMBIL ORDER
        |--------------------------------------------------------------------------
        */

        $order =
            $mooGoldOrder->order;


        /*
        |--------------------------------------------------------------------------
        | SYNC ORDER STATUS
        |--------------------------------------------------------------------------
        */

        switch ($status) {

            case 'pending':

            case 'processing':

                if ($order) {

                    $order->status =
                        'Processing';
                }

                break;


            case 'success':

            case 'successful':

            case 'completed':

            case 'complete':

            case 'successfully':

                if ($order) {

                    $order->status =
                        'Completed';
                }

                if (
                    !$mooGoldOrder->completed_at
                ) {

                    $mooGoldOrder->completed_at =
                        now();
                }

                $mooGoldOrder->error_message =
                    null;

                break;


            case 'refunded':

                if ($order) {

                    $order->status =
                        'Cancelled';
                }

                $mooGoldOrder->error_message =
                    'MooGold order refunded.';

                break;


            case 'failed':

                if ($order) {

                    $order->status =
                        'Cancelled';
                }

                $mooGoldOrder->error_message =
                    'MooGold order failed.';

                break;


            default:

                Log::warning(
                    'Unknown MooGold order status.',
                    [

                        'moo_gold_order_id' =>
                            $mooGoldOrder->id,

                        'moogold_order_id' =>
                            $mooGoldOrder->moogold_order_id,

                        'status' =>
                            $status,

                        'response' =>
                            $response,

                    ]
                );

                $mooGoldOrder->save();

                return $mooGoldOrder->fresh();
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE MOO GOLD ORDER
        |--------------------------------------------------------------------------
        */

        $mooGoldOrder->save();


        /*
        |--------------------------------------------------------------------------
        | SAVE LOCAL ORDER
        |--------------------------------------------------------------------------
        */

        if ($order) {

            $order->moogold_order_id =
                $mooGoldOrder->moogold_order_id;

            $order->moogold_status =
                $status;

            $order->moogold_response =
                $response;

            $order->save();
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN FRESH
        |--------------------------------------------------------------------------
        */

        return $mooGoldOrder->fresh();
    }


    /**
     * =========================================================
     * MAP MOOGOLD STATUS → LOCAL ORDER STATUS
     * =========================================================
     */
    protected function mapOrderStatus(
        string $status
    ): string {

        return match (
            strtolower(trim($status))
        ) {

            'success',
            'successful',
            'completed',
            'complete',
            'successfully'
                => 'Completed',

            'failed',
            'refunded'
                => 'Cancelled',

            default
                => 'Processing',
        };
    }


    /**
     * =========================================================
     * FINAL STATUS
     * =========================================================
     */
    protected function isFinalStatus(
        string $status
    ): bool {

        return in_array(

            strtolower(
                trim($status)
            ),

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
        );
    }


    /**
     * =========================================================
     * EXTRACT MOOGOLD ORDER ID
     * =========================================================
     */
    protected function extractOrderId(
        array $response
    ): ?string {

        $orderId =
            $response['order_id']
            ?? $response['orderId']
            ?? $response['order']['order_id']
            ?? $response['data']['order_id']
            ?? $response['data']['orderId']
            ?? null;


        if (
            $orderId === null ||
            $orderId === ''
        ) {

            return null;
        }


        return (string) $orderId;
    }


    /**
     * =========================================================
     * EXTRACT MOOGOLD STATUS
     * =========================================================
     */
    protected function extractStatus(
        array $response
    ): string {

        $status =
            $response['order_status']
            ?? $response['status']
            ?? $response['data']['order_status']
            ?? $response['data']['status']
            ?? 'processing';


        return strtolower(
            trim(
                (string) $status
            )
        );
    }


    /**
     * =========================================================
     * BUILD ORDER PAYLOAD
     * =========================================================
     */
    public function buildOrderPayload(
        OrderDetail $orderDetail
    ): array {

        $orderDetail->loadMissing([
            'order',
            'item',
        ]);

        $order = $orderDetail->order;
        $item  = $orderDetail->item;


        if (!$order) {

            throw new RuntimeException(
                'Order tidak ditemukan.'
            );
        }


        if (!$item) {

            throw new RuntimeException(
                'Item pada OrderDetail tidak ditemukan.'
            );
        }


        if (
            empty($item->moogold_category_id) ||
            empty($item->moogold_variation_id)
        ) {

            throw new RuntimeException(
                'Item "' .
                $item->item_name .
                '" belum memiliki mapping MooGold.'
            );
        }


        $playerData =
            $order->player_data;


        if (!is_array($playerData)) {

            $playerData =
                json_decode(
                    (string) $playerData,
                    true
                );
        }


        if (!is_array($playerData)) {

            throw new RuntimeException(
                'Player data order tidak valid.'
            );
        }


        $userId =
            $playerData['uid']
            ?? $playerData['user_id']
            ?? $playerData['User ID']
            ?? null;


        $server =
            $playerData['server']
            ?? $playerData['server_id']
            ?? $playerData['Server']
            ?? null;


        if (empty($userId)) {

            throw new RuntimeException(
                'Player UID / User ID belum tersedia.'
            );
        }


        $data = [

            'category' =>
                (string)
                $item->moogold_category_id,

            'product-id' =>
                (string)
                $item->moogold_variation_id,

            'quantity' =>
                (string)
                $orderDetail->qty,

            'User ID' =>
                (string)
                $userId,

        ];


        if (
            $server !== null &&
            $server !== ''
        ) {

            $data['Server ID'] =
                (string) $server;
        }


        return [

            'path' =>
                'order/create_order',

            'partnerOrderId' =>
                'MG-' .
                $order->id .
                '-' .
                $orderDetail->id,

            'data' =>
                $data,

        ];
    }
}