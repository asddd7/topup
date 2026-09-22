<?php

namespace App\Services\TopUp;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\TopUp\Contracts\TopUpProvider;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TopUpFulfillmentService
{
    public function __construct(
        protected TopUpProviderRegistry $registry
    ) {
    }

    /**
     * Dispatch fulfillment untuk seluruh OrderDetail.
     */
    public function dispatchOrder(
        Order $order
    ): array {

        if ($order->status !== 'Paid') {
            throw new RuntimeException(
                'Order belum berstatus Paid.'
            );
        }

        $order->loadMissing([
            'details.item',
        ]);

        if ($order->details->isEmpty()) {
            throw new RuntimeException(
                'Order tidak memiliki OrderDetail.'
            );
        }

        $results = [];

        foreach ($order->details as $detail) {

            $detail->setRelation(
                'order',
                $order
            );

            $results[] =
                $this->dispatchDetail(
                    $detail
                );
        }

        return $results;
    }

    /**
     * Dispatch satu OrderDetail.
     */
    public function dispatchDetail(
        OrderDetail $detail
    ): array {

        $detail->loadMissing([
            'order',
            'item',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ORDER STATUS
        |--------------------------------------------------------------------------
        */

        if (
            !$detail->order
            ||
            !in_array(
                $detail->order->status,
                [
                    'Paid',
                    'Processing',
                ],
                true
            )
        ) {
            Log::warning(
                'Fulfillment tidak didispatch karena Order belum dalam status fulfillment.',
                [
                    'order_id' =>
                        $detail->order_id,

                    'order_detail_id' =>
                        $detail->id,

                    'status' =>
                        $detail->order?->status,
                ]
            );

            return [
                'success' =>
                    false,

                'order_detail_id' =>
                    $detail->id,

                'provider' =>
                    null,

                'status' =>
                    'invalid_order_status',
            ];
        }

        $provider =
            $this->registry
                ->resolveForOrderDetail(
                    $detail
                );

        if (!$provider) {

            Log::warning(
                'OrderDetail tidak memiliki provider fulfillment.',
                [
                    'order_id' =>
                        $detail->order_id,

                    'order_detail_id' =>
                        $detail->id,
                ]
            );

            return [
                'success' =>
                    false,

                'order_detail_id' =>
                    $detail->id,

                'provider' =>
                    null,

                'status' =>
                    'no_provider',
            ];
        }

        $provider->dispatch(
            $detail
        );

        Log::info(
            'Fulfillment provider didispatch.',
            [
                'order_id' =>
                    $detail->order_id,

                'order_detail_id' =>
                    $detail->id,

                'provider' =>
                    $provider->key(),
            ]
        );

        return [
            'success' =>
                true,

            'order_detail_id' =>
                $detail->id,

            'provider' =>
                $provider->key(),

            'status' =>
                'dispatched',
        ];
    }
}