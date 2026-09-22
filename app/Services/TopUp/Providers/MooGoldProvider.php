<?php

namespace App\Services\TopUp\Providers;

use App\Integrations\MooGold\MooGoldOrderService;
use App\Jobs\Providers\MooGold\ProcessMooGoldOrder;
use App\Models\OrderDetail;
use App\Services\TopUp\Contracts\TopUpProvider;
use Illuminate\Database\Eloquent\Model;

class MooGoldProvider implements TopUpProvider
{
    public function __construct(
        protected MooGoldOrderService $service
    ) {
    }

    public function key(): string
    {
        return 'moogold';
    }

    public function supports(
        OrderDetail $orderDetail
    ): bool {

        $orderDetail->loadMissing('item');

        $item = $orderDetail->item;

        if (!$item) {
            return false;
        }

        return
            !empty($item->moogold_category_id)
            &&
            !empty($item->moogold_variation_id);
    }

    public function dispatch(
        OrderDetail $orderDetail
    ): void {

        ProcessMooGoldOrder::dispatch(
            $orderDetail->id
        );
    }

    public function create(
        OrderDetail $orderDetail
    ): Model {

        return $this->service->createFromOrderDetail(
            $orderDetail
        );
    }
}