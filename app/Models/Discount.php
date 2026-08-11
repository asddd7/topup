<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{

    protected $fillable = [

        'discount_name',
        'code',
        'trigger_type',

        'discount_type',
        'amount',

        'minimum_purchase',

        'game_id',
        'item_id',
        'payment_id',

        'start_date',
        'end_date',

        'usage_limit',
        'quota_used',

        'is_active',

    ];

    protected $casts = [

        'amount' =>
            'decimal:2',

        'minimum_purchase' =>
            'decimal:2',

        'start_date' =>
            'date',

        'end_date' =>
            'date',

        'is_active' =>
            'boolean',

    ];



public function game()
{
    return $this->belongsTo(Game::class);
}



public function item()
{
    return $this->belongsTo(Item::class);
}

public function payment()
{
    return $this->belongsTo(Payment::class);
}

public function usages()
{
    return $this->hasMany(
        DiscountUsage::class
    );
}
}