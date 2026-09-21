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
     * Menentukan apakah provider dapat memproses
     * OrderDetail tertentu.
     */
    public function supports(
        OrderDetail $orderDetail
    ): bool;

    /**
     * Membuat transaksi provider.
     */
    public function create(
        OrderDetail $orderDetail
    ): Model;
}