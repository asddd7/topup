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
        Schema::table('games', function (Blueprint $table) {
            $table->string('moogold_server_id')
                ->nullable()
                ->after('publisher');

            $table->string('moogold_server_name')
                ->nullable()
                ->after('moogold_server_id');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'moogold_server_id',
                'moogold_server_name',
            ]);
        });
    }
};
