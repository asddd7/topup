<?php

namespace App\Models;

use App\Models\Game;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\MooGoldProductMapping;
use App\Models\MooGoldProductVariation;
use App\Services\MooGold\MooGoldService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function variations(): HasMany
    {
        return $this->hasMany(
            MooGoldProductVariation::class,
            'moogold_product_mapping_id'
        );
    }
}