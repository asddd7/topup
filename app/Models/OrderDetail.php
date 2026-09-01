<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderDetail extends Model
{
    protected $fillable = [

        'order_id',

        'item_id',

        'qty',

        'price',

        'subtotal',

    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }


    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class
        );
    }


    /**
     * 1 OrderDetail = 1 MooGoldOrder
     */
    public function mooGoldOrder(): HasOne
    {
        return $this->hasOne(
            MooGoldOrder::class,
            'order_detail_id'
        );
    }
}