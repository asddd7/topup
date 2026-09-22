<?php

namespace App\Services\TopUp\Contracts;

use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Model;

interface TopUpProvider
{
    /**
     * Nama internal provider.
     */
    public function key(): string;

    /**
     * Apakah provider mendukung OrderDetail ini.
     */
    public function supports(
        OrderDetail $orderDetail
    ): bool;

    /**
     * Dispatch fulfillment.
     */
    public function dispatch(
        OrderDetail $orderDetail
    ): void;

    /**
     * Membuat transaksi provider secara langsung.
     *
     * Biasanya digunakan oleh job/provider handler.
     */
    public function create(
        OrderDetail $orderDetail
    ): Model;
}