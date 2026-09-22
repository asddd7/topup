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

        $provider =
            $this->registry
                ->resolveForOrderDetail(
                    $detail
                );

        /*
        |--------------------------------------------------------------------------
        | NO PROVIDER
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | DISPATCH PROVIDER
        |--------------------------------------------------------------------------
        */

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