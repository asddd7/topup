<?php

namespace App\Services\TopUp\Providers;

use App\Integrations\Ditusi\DitusiOrderService;
use App\Models\OrderDetail;
use App\Services\TopUp\Contracts\TopUpProvider;
use Illuminate\Database\Eloquent\Model;

class DitusiProvider implements TopUpProvider
{
    public function __construct(
        protected DitusiOrderService $service
    ) {
    }

    public function key(): string
    {
        return 'ditusi';
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
            (bool) $item->ditusi_enabled
            &&
            trim(
                (string) $item->ditusi_product_code
            ) !== '';
    }

    public function create(
        OrderDetail $orderDetail
    ): Model {

        return $this->service->createFromOrderDetail(
            $orderDetail
        );
    }
}