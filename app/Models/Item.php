<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

protected $fillable=[

    'game_id',
    'category_id',
    'item_name',
    'qty',
    'price',
    'stock',
    'description',
    'image',
    'is_active',
    'top_seller'

];


public function game()
{
    return $this->belongsTo(Game::class);
}


public function category()
{
    return $this->belongsTo(ItemCategory::class);
}

}