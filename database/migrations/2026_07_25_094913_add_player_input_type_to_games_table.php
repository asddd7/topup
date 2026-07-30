<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up(): void
{

Schema::table('games', function(Blueprint $table){

    $table->enum('player_input_type',[

        'uid',
        'uid_server',
        'riot_id',
        'email',
        'none',
        'login',

    ])
    ->default('uid')
    ->after('publisher');


});


}



public function down(): void
{

Schema::table('games', function(Blueprint $table){

    $table->dropColumn(
        'player_input_type'
    );

});

}

};