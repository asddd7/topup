<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MooGoldProductVariation extends Model
{

    protected $table = 'moogold_product_variations';
    
    protected $fillable = [
        'moogold_product_mapping_id',
        'moogold_variation_id',
        'variation_name',
        'variation_price',
        'stock_status',
        'category_id',
        'is_active',
        'variation_data',
        'synced_at',
    ];

    protected $casts = [
        'variation_price' => 'decimal:2',
        'variation_data'  => 'array',
        'is_active'       => 'boolean',
        'synced_at'       => 'datetime',
    ];

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(
            MooGoldProductMapping::class,
            'moogold_product_mapping_id'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ItemCategory::class,
            'category_id'
        );
    }
}