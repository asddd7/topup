<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MooGoldOrder extends Model
{
    protected $table = 'moogold_orders';

    protected $fillable = [

        'order_id',
        'order_detail_id',
        'item_id',

        'external_order_id',

        'moogold_category_id',
        'moogold_product_id',
        'moogold_variation_id',

        'moogold_order_id',
        'moogold_status',

        'request_payload',
        'response_payload',

        'error_message',

        'attempts',
        'last_attempt_at',

        'ordered_at',
        'completed_at',
    ];

    protected $casts = [

        'request_payload' => 'array',

        'response_payload' => 'array',

        'attempts' => 'integer',

        'last_attempt_at' => 'datetime',

        'ordered_at' => 'datetime',

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