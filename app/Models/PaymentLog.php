<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable=[
        'order_id',
        'status',
        'message',
        'logged_at'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}