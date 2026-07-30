<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        Game::insert([

            [
                'id' => 1,
                'game_name' => 'Mobile Legends',
                'publisher' => 'Moonton',
                'player_input_type' => 'uid_server',
                'is_active' => true,
            ],

            [
                'id' => 2,
                'game_name' => 'Free Fire',
                'publisher' => 'Garena',
                'player_input_type' => 'uid',
                'is_active' => true,
            ],

            [
                'id' => 3,
                'game_name' => 'PUBG Mobile',
                'publisher' => 'Tencent',
                'player_input_type' => 'uid',
                'is_active' => true,
            ],

            [
                'id' => 4,
                'game_name' => 'Valorant',
                'publisher' => 'Riot Games',
                'player_input_type' => 'riot_id',
                'is_active' => true,
            ],

            [
                'id' => 5,
                'game_name' => 'Steam Wallet',
                'publisher' => 'Valve',
                'player_input_type' => 'email',
                'is_active' => true,
            ],

        ]);
    }
}