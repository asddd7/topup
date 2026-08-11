<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'game_id',
        'player_data',
        'payment_id',
        'discount_id',
        'player_uid',
        'server_id',
        'nickname',
        'subtotal',
        'discount',
        'total_price',
        'payment_proof',
        'status',
        'notes',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_token',
    ];

    protected $casts = [
        'player_data' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Legacy:
     * Menunjukkan promo pertama yang digunakan.
     *
     * Jangan gunakan ini untuk menghitung total promo.
     * Gunakan orderDiscounts().
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(
            Discount::class,
            'discount_id'
        );
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Semua discount yang digunakan.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(
            Discount::class,
            'order_discounts'
        )
        ->withPivot('discount_amount')
        ->withTimestamps();
    }

    /**
     * Detail penggunaan discount.
     */
    public function orderDiscounts()
    {
        return $this->hasMany(
            \App\Models\OrderDiscount::class
        );
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }
}