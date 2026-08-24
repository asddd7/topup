<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\ItemResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * GET /api/v1/games
     *
     * Menampilkan seluruh game aktif.
     */
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->where('is_active', 1)
            ->orderBy('game_name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar game berhasil diambil.',
            'data' => [
                'games' => GameResource::collection($games),
            ],
        ]);
    }


    /**
     * GET /api/v1/games/{game}
     *
     * Menampilkan detail game beserta item aktif.
     */
    public function show(Game $game): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Cek game aktif
        |--------------------------------------------------------------------------
        */

        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Load items + category
        |--------------------------------------------------------------------------
        */

        $game->load([
            'items' => function ($query) {
                $query
                    ->where('is_active', 1)
                    ->orderBy('top_seller', 'desc')
                    ->orderBy('price');
            },

            'items.category',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Detail game berhasil diambil.',
            'data' => [
                'game' => new GameResource($game),

                'items' => ItemResource::collection(
                    $game->items
                ),
            ],
        ]);
    }
}