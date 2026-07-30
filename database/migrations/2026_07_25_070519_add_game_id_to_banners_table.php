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
        Schema::table('banners', function (Blueprint $table) {

            $table->foreignId('game_id')
                ->nullable()
                ->after('id')
                ->constrained('games')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {

            $table->dropForeign(['game_id']);

            $table->dropColumn('game_id');

        });
    }
};
