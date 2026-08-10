<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{

protected $fillable = [

    'code',
    'game_id',
    'item_id',
    'payment_id',

    'discount_name',

    'discount_type',

    'amount',

    'start_date',

    'end_date',

    'is_active',

    'trigger_type',

    'minimum_purchase',

    'usage_limit',

    'usage_per_user',

    'quota_used'

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