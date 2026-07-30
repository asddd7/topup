<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Game extends Model
{

protected $fillable=[

'game_name',
'game_logo',
'publisher',
'player_input_type',
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


}