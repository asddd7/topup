<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align databases created before the duplicate MooGold migrations were
     * corrected. The canonical index is kept and the redundant index is
     * removed without touching item data.
     */
    public function up(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        $canonicalIndex = 'items_moogold_variation_unique';
        $legacyIndex = 'items_moogold_variation_id_unique';

        $hasCanonicalIndex = Schema::hasIndex('items', $canonicalIndex);
        $hasLegacyIndex = Schema::hasIndex('items', $legacyIndex);

        if ($hasCanonicalIndex && $hasLegacyIndex) {
            Schema::table('items', function (Blueprint $table) use ($legacyIndex) {
                $table->dropUnique($legacyIndex);
            });

            return;
        }

        if (! $hasCanonicalIndex && $hasLegacyIndex) {
            Schema::table('items', function (Blueprint $table) use ($canonicalIndex, $legacyIndex) {
                $table->dropUnique($legacyIndex);
                $table->unique('moogold_variation_id', $canonicalIndex);
            });
        }
    }

    /**
     * This is a schema reconciliation migration. It intentionally does not
     * recreate the redundant index when rolled back.
     */
    public function down(): void
    {
        // No destructive rollback for an index cleanup migration.
    }
};
