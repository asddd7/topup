<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moogold_product_mappings', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | MooGold
            |--------------------------------------------------------------------------
            */

            $table->string('moogold_category_id');

            $table->string('moogold_product_id');

            $table->string('product_name');

            $table->json('product_data')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Local Mapping
            |--------------------------------------------------------------------------
            */

            $table->foreignId('game_id')
                ->nullable()
                ->constrained('games')
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('item_categories')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);


            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate product
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['moogold_category_id', 'moogold_product_id'],
                'mg_category_product_unique'
            );

            $table->index('game_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moogold_product_mappings');
    }
};