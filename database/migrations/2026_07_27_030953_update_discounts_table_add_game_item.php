<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {

        Schema::table('discounts', function(Blueprint $table){


            $table->foreignId('game_id')
                ->nullable()
                ->after('code')
                ->constrained('games')
                ->nullOnDelete();


            $table->foreignId('item_id')
                ->nullable()
                ->after('game_id')
                ->constrained('items')
                ->nullOnDelete();


        });

    }



    public function down(): void
    {

        Schema::table('discounts', function(Blueprint $table){


            $table->dropForeign([
                'game_id'
            ]);


            $table->dropForeign([
                'item_id'
            ]);


            $table->dropColumn([
                'game_id',
                'item_id'
            ]);


        });

    }

};