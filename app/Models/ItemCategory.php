<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    
    protected $fillable = [
        'category_name',
        'use_qty',
    ];

    public function items()
    {
        return $this->hasMany(Item::class,'category_id');
    }
}