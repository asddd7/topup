<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemCategory;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        ItemCategory::insert([

            [
                'id'=>1,
                'category_name'=>'Diamond'
            ],

            [
                'id'=>2,
                'category_name'=>'UC'
            ],

            [
                'id'=>3,
                'category_name'=>'Voucher'
            ],

            [
                'id'=>4,
                'category_name'=>'Genesis Crystal'
            ],

        ]);
    }
}