<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MidtransTransaction extends Model
{
    protected $fillable = [

        'order_id',

        'midtrans_order_id',

        'snap_token',

        'snap_redirect_url',

        'transaction_id',

        'transaction_status',

        'payment_type',

        'fraud_status',

        'gross_amount',

        'request_payload',

        'response_payload',

        'notification_payload',

        'paid_at',

        'expired_at',

    ];


    protected $casts = [

        'gross_amount' => 'decimal:2',

        'request_payload' => 'array',

        'response_payload' => 'array',

        'notification_payload' => 'array',

        'paid_at' => 'datetime',

        'expired_at' => 'datetime',

    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }
}