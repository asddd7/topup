<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {

            $table->json('player_fields')
                  ->nullable()
                  ->after('publisher');

            $table->dropColumn([
                'player_input_type',
                'input_label',
                'input_label_2',
                'input_placeholder',
                'input_placeholder_2'
            ]);

        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {

            $table->enum('player_input_type',[
                'uid',
                'uid_server',
                'riot_id',
                'email',
                'login',
                'none'
            ]);

            $table->string('input_label')->nullable();

            $table->string('input_label_2')->nullable();

            $table->string('input_placeholder')->nullable();

            $table->string('input_placeholder_2')->nullable();

            $table->dropColumn('player_fields');

        });
    }
};