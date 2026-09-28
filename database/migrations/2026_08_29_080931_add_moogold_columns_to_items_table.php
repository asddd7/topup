<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasVariationUniqueIndex = Schema::hasIndex(
            'items',
            'items_moogold_variation_unique'
        );

        Schema::table('items', function (Blueprint $table) use ($hasVariationUniqueIndex) {

            if (! Schema::hasColumn('items', 'moogold_category_id')) {
                $table->unsignedBigInteger('moogold_category_id')
                    ->nullable()
                    ->after('id');
            }

            // This column is created by the earlier MooGold fields migration.
            if (! Schema::hasColumn('items', 'moogold_product_id')) {
                $table->unsignedBigInteger('moogold_product_id')
                    ->nullable()
                    ->after('moogold_category_id');
            }

            if (! Schema::hasColumn('items', 'moogold_variation_id')) {
                $table->unsignedBigInteger('moogold_variation_id')
                    ->nullable()
                    ->after('moogold_product_id');
            }

            /*
             * Harga modal dari MooGold.
             * items.price tetap harga jual website.
             */
            if (! Schema::hasColumn('items', 'moogold_price')) {
                $table->decimal('moogold_price', 15, 2)
                    ->nullable()
                    ->after('price');
            }

            /*
             * instock / outofstock
             */
            if (! Schema::hasColumn('items', 'moogold_stock_status')) {
                $table->string('moogold_stock_status', 30)
                    ->nullable()
                    ->after('moogold_price');
            }

            /*
             * Menandai apakah item berhasil disinkronkan
             */
            if (! Schema::hasColumn('items', 'moogold_synced_at')) {
                $table->timestamp('moogold_synced_at')
                    ->nullable()
                    ->after('moogold_stock_status');
            }

            /*
             * Mencegah satu variation MooGold
             * masuk dua kali.
             */
            if (! $hasVariationUniqueIndex) {
                $table->unique(
                    'moogold_variation_id',
                    'items_moogold_variation_unique'
                );
            }

        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {

            if (Schema::hasIndex('items', 'items_moogold_variation_unique')) {
                $table->dropUnique('items_moogold_variation_unique');
            }

            $columns = [
                'moogold_category_id',
                'moogold_variation_id',
                'moogold_price',
                'moogold_stock_status',
                'moogold_synced_at',
            ];

            $columns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('items', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }

        });
    }
};
