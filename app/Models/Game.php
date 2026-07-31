<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Game extends Model
{

protected $fillable = [

    'game_name',
    'slug',
    'game_logo',
    'publisher',

    'player_input_type',

    'input_label',
    'input_label_2',

    'input_placeholder',
    'input_placeholder_2',

    'login_guide',
    'description',

    'sort_order',
    'is_active'

];

public function items()
{
    return $this->hasMany(Item::class);
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

}