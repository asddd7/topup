<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MooGoldProductMapping extends Model
{
    protected $table = 'moogold_product_mappings';

    protected $fillable = [
        'moogold_category_id',
        'moogold_product_id',
        'product_name',
        'product_data',
        'game_id',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'product_data' => 'array',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function game(): BelongsTo
    {
        return $this->belongsTo(
            Game::class,
            'game_id'
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