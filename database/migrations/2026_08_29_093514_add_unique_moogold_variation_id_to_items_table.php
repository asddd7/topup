<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * The preceding MooGold columns migration already creates the
         * named unique index. Keep this migration safe for databases that
         * were created from either migration history.
         */
        if (
            Schema::hasColumn('items', 'moogold_variation_id')
            && ! Schema::hasIndex('items', ['moogold_variation_id'], 'unique')
        ) {
            Schema::table('items', function (Blueprint $table) {
                $table->unique(
                    'moogold_variation_id',
                    'items_moogold_variation_id_unique'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('items', 'items_moogold_variation_id_unique')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropUnique('items_moogold_variation_id_unique');
            });
        }
    }
};
