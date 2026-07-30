<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{

    protected $fillable = [

        'game_id',
        'title',
        'image',
        'link',
        'description',
        'is_active',
        'sort_order'

    ];


    public function game()
    {
        return $this->belongsTo(Game::class);
    }


    public function getUrlAttribute()
    {

        if($this->game_id)
        {
            return route(
                'game.show',
                $this->game_id
            );
        }


        if($this->link)
        {
            return $this->link;
        }


        return '#';

    }

}