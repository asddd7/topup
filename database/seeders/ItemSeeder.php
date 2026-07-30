<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {

        Item::insert([

            [

                'game_id'=>1,

                'category_id'=>1,

                'item_name'=>'86 Diamond',

                'qty'=>86,

                'price'=>20000,

                'stock'=>99999,

                'description'=>'86 Diamond ML',

                'top_seller'=>true,

                'is_active'=>true,

            ],

            [

                'game_id'=>1,

                'category_id'=>1,

                'item_name'=>'172 Diamond',

                'qty'=>172,

                'price'=>40000,

                'stock'=>99999,

                'description'=>'172 Diamond ML',

                'top_seller'=>true,

                'is_active'=>true,

            ],

            [

                'game_id'=>2,

                'category_id'=>1,

                'item_name'=>'70 Diamond',

                'qty'=>70,

                'price'=>10000,

                'stock'=>99999,

                'description'=>'Free Fire',

                'top_seller'=>true,

                'is_active'=>true,

            ],

            [

                'game_id'=>4,

                'category_id'=>3,

                'item_name'=>'475 VP',

                'qty'=>475,

                'price'=>50000,

                'stock'=>99999,

                'description'=>'Valorant Points',

                'top_seller'=>true,

                'is_active'=>true,

            ],

        ]);

    }
}