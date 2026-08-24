<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\ItemResource;
use App\Models\Game;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    /**
     * GET /api/v1/catalog/categories
     *
     * Menampilkan seluruh kategori.
     */
    public function categories(): JsonResponse
    {
        $categories = ItemCategory::query()
            ->withCount('items')
            ->orderBy('category_name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => [
                'categories' => CategoryResource::collection(
                    $categories
                ),
            ],
        ]);
    }


    /**
     * GET /api/v1/catalog/games/{game}/items
     *
     * Menampilkan seluruh item aktif dari game tertentu.
     */
    public function items(Game $game): JsonResponse
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
        | Ambil item aktif
        |--------------------------------------------------------------------------
        */

        $items = Item::query()
            ->with('category')
            ->where('game_id', $game->id)
            ->where('is_active', 1)
            ->orderBy('top_seller', 'desc')
            ->orderBy('price')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Catalog item berhasil diambil.',
            'data' => [
                'game' => new GameResource($game),

                'items' => ItemResource::collection(
                    $items
                ),
            ],
        ]);
    }


    /**
     * GET /api/v1/catalog/games/{game}/categories
     *
     * Menampilkan catalog game berdasarkan kategori.
     */
    public function gameCategories(Game $game): JsonResponse
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
        | Ambil kategori yang mempunyai item aktif
        |--------------------------------------------------------------------------
        */

        $categories = ItemCategory::query()

            ->whereHas('items', function ($query) use ($game) {
                $query
                    ->where('game_id', $game->id)
                    ->where('is_active', 1);
            })

            ->with([
                'items' => function ($query) use ($game) {
                    $query
                        ->where('game_id', $game->id)
                        ->where('is_active', 1)
                        ->orderBy('top_seller', 'desc')
                        ->orderBy('price');
                },

                'items.category',
            ])

            ->orderBy('category_name')

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Catalog berdasarkan kategori berhasil diambil.',
            'data' => [
                'game' => new GameResource($game),

                'categories' => CategoryResource::collection(
                    $categories
                ),
            ],
        ]);
    }


    /**
     * GET /api/v1/catalog/items/{item}
     *
     * Menampilkan detail item.
     */
    public function item(Item $item): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Cek item aktif
        |--------------------------------------------------------------------------
        */

        if (!$item->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak tersedia.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Load relasi
        |--------------------------------------------------------------------------
        */

        $item->load([
            'game',
            'category',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Detail item berhasil diambil.',
            'data' => [
                'item' => new ItemResource($item),
            ],
        ]);
    }
}