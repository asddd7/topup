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
        Schema::table('item_categories', function (Blueprint $table) {

            $table->boolean('use_qty')
                ->default(true)
                ->after('category_name');

        });
    }

    public function down(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {

            $table->dropColumn('use_qty');

        });
    }
};
