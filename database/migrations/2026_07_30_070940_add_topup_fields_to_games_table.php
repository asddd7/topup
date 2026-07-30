<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {

            $table->string('slug')
                ->nullable()
                ->after('game_name');

            $table->string('input_label')
                ->nullable()
                ->after('player_input_type');

            $table->string('input_label_2')
                ->nullable()
                ->after('input_label');

            $table->string('input_placeholder')
                ->nullable()
                ->after('input_label_2');

            $table->string('input_placeholder_2')
                ->nullable()
                ->after('input_placeholder');

            $table->text('login_guide')
                ->nullable()
                ->after('input_placeholder_2');

            $table->text('description')
                ->nullable()
                ->after('login_guide');

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {

            $table->dropColumn([
                'slug',
                'input_label',
                'input_label_2',
                'input_placeholder',
                'input_placeholder_2',
                'login_guide',
                'description',
                'sort_order'
            ]);

        });
    }
};