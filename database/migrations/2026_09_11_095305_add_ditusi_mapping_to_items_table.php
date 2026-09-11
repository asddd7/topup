<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {

            $table->string(
                'ditusi_game_code'
            )->nullable()->after(
                'moogold_variation_id'
            );

            $table->string(
                'ditusi_product_code'
            )->nullable()->after(
                'ditusi_game_code'
            );

            $table->boolean(
                'ditusi_enabled'
            )->default(false)->after(
                'ditusi_product_code'
            );

            $table->index(
                'ditusi_product_code'
            );
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {

            $table->dropIndex([
                'ditusi_product_code'
            ]);

            $table->dropColumn([
                'ditusi_game_code',
                'ditusi_product_code',
                'ditusi_enabled',
            ]);
        });
    }
};