<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moogold_product_variations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('moogold_product_mapping_id')
                ->constrained('moogold_product_mappings')
                ->cascadeOnDelete();

            $table->string('moogold_variation_id', 100);

            $table->string('variation_name');

            $table->decimal('variation_price', 15, 2)
                ->nullable();

            $table->string('stock_status', 50)
                ->nullable();

            /*
             * Category lokal ditentukan PER VARIATION.
             */
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('item_categories')
                ->nullOnDelete();

            $table->boolean('is_active')
                ->default(true);

            $table->json('variation_data')
                ->nullable();

            $table->timestamp('synced_at')
                ->nullable();

            $table->timestamps();

            /*
             * Satu variation MooGold hanya boleh
             * muncul satu kali di sistem.
             */
            $table->unique('moogold_variation_id');

            $table->index(
                ['moogold_product_mapping_id', 'category_id'],
                'mpv_mapping_category_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moogold_product_variations');
    }
};