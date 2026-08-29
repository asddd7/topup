<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Game;

class Item extends Model
{
    protected $fillable = [

        'game_id',
        'category_id',
        'item_name',
        'qty',
        'price',
        'stock',
        'description',
        'image',
        'is_active',

        // MooGold
        'moogold_category_id',
        'moogold_product_id',
        'moogold_variation_id',
        'moogold_price',
        'moogold_stock_status',
        'moogold_synced_at',

    ];

protected function casts(): array
{
    return [

        'qty' => 'integer',

        'price' => 'decimal:2',

        'stock' => 'integer',

        'moogold_price' => 'decimal:2',

        'is_active' => 'boolean',

        'top_seller' => 'boolean',

        'moogold_synced_at' => 'datetime',

    ];
}

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }
}