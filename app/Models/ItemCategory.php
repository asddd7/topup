<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends Model
{

    protected $table = 'item_categories';

    protected $fillable = [
        'category_name',
        'use_qty',
    ];

    protected $casts = [
        'use_qty' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(
            Item::class,
            'category_id'
        );
    }
}