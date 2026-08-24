<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * GET /api/v1/games
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
                'games' => $games->map(function ($game) {
                    return [
                        'id' => $game->id,
                        'name' => $game->game_name,
                        'slug' => $game->slug ?? null,
                        'image' => $game->image
                            ? asset('storage/' . $game->image)
                            : null,
                        'description' => $game->description ?? null,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * GET /api/v1/games/{game}
     */
    public function show(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        $game->load([
            'items' => function ($query) {
                $query
                    ->where('is_active', 1)
                    ->orderBy('top_seller', 'desc')
                    ->orderBy('price');
            },
            'items.category',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail game berhasil diambil.',
            'data' => [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->game_name,
                    'slug' => $game->slug ?? null,
                    'image' => $game->image
                        ? asset('storage/' . $game->image)
                        : null,
                    'description' => $game->description ?? null,
                ],

                'items' => $game->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->item_name,
                        'qty' => $item->qty,
                        'price' => (float) $item->price,
                        'stock' => $item->stock,
                        'description' => $item->description,
                        'image' => $item->image
                            ? asset('storage/' . $item->image)
                            : null,
                        'is_active' => (bool) $item->is_active,
                        'top_seller' => (bool) $item->top_seller,

                        'category' => $item->category ? [
                            'id' => $item->category->id,
                            'name' => $item->category->category_name,
                            'use_qty' => (bool) $item->category->use_qty,
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }
}