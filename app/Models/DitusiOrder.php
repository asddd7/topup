<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DitusiOrder extends Model
{
    protected $fillable = [
        'order_id',
        'order_detail_id',
        'item_id',
        'transaction_reference_id',
        'ditusi_transaction_id',
        'product_code',
        'amount',
        'initial_price',
        'status',
        'request_payload',
        'response_payload',
        'voucher_code',
        'error_message',
        'attempts',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'initial_price' => 'integer',
        'attempts' => 'integer',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'voucher_code' => 'array',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}