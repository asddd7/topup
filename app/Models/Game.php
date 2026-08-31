<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Game extends Model
{

protected $fillable = [

    'game_name',
    'slug',
    'game_logo',
    'publisher',
    'player_fields',

    'login_guide',
    'description',

    'sort_order',
    'is_active'

];

protected $casts = [

    'player_fields'=>'array',

    'is_active'=>'boolean'

];

public function items()
{
    return $this->hasMany(
        Item::class,
        'game_id'
    );
}

public function banners()
{

return $this->hasMany(
    Banner::class
);

}
public function discounts()
{
    return $this->hasMany(
        Discount::class,
        'game_id'
    );
}

public function moogoldProductMappings(): HasMany
{
    return $this->hasMany(
        MooGoldProductMapping::class,
        'game_id'
    );
}

}