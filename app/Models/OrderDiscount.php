<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDiscount extends Model
{
    protected $fillable = [
        'order_id',
        'discount_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(
            Order::class
        );
    }

    public function discount()
    {
        return $this->belongsTo(
            Discount::class
        );
    }
}