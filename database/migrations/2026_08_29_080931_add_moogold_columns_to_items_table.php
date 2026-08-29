<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {

            $table->unsignedBigInteger('moogold_category_id')
                ->nullable()
                ->after('id');

            $table->unsignedBigInteger('moogold_product_id')
                ->nullable()
                ->after('moogold_category_id');

            $table->unsignedBigInteger('moogold_variation_id')
                ->nullable()
                ->after('moogold_product_id');

            /*
             * Harga modal dari MooGold.
             * items.price tetap harga jual website.
             */
            $table->decimal('moogold_price', 15, 2)
                ->nullable()
                ->after('price');

            /*
             * instock / outofstock
             */
            $table->string('moogold_stock_status', 30)
                ->nullable()
                ->after('moogold_price');

            /*
             * Menandai apakah item berhasil disinkronkan
             */
            $table->timestamp('moogold_synced_at')
                ->nullable()
                ->after('moogold_stock_status');

            /*
             * Mencegah satu variation MooGold
             * masuk dua kali.
             */
            $table->unique(
                'moogold_variation_id',
                'items_moogold_variation_unique'
            );

        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {

            $table->dropUnique(
                'items_moogold_variation_unique'
            );

            $table->dropColumn([
                'moogold_category_id',
                'moogold_product_id',
                'moogold_variation_id',
                'moogold_price',
                'moogold_stock_status',
                'moogold_synced_at',
            ]);

        });
    }
};