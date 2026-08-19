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
                'is_active' => true,
            ],

            [
                'id' => 2,
                'game_name' => 'Free Fire',
                'publisher' => 'Garena',
                'is_active' => true,
            ],

            [
                'id' => 3,
                'game_name' => 'PUBG Mobile',
                'publisher' => 'Tencent',
                'is_active' => true,
            ],

            [
                'id' => 4,
                'game_name' => 'Valorant',
                'publisher' => 'Riot Games',
                'is_active' => true,
            ],

            [
                'id' => 5,
                'game_name' => 'Steam Wallet',
                'publisher' => 'Valve',
                'is_active' => true,
            ],

        ]);
    }
}