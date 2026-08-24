<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    /**
     * GET /api/v1/catalog/categories
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
                'categories' => $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->category_name,
                        'use_qty' => (bool) $category->use_qty,
                        'items_count' => $category->items_count,
                    ];
                })->values(),
            ],
        ]);
    }


    /**
     * GET /api/v1/catalog/games/{game}/items
     */
    public function items(Game $game): JsonResponse
    {
        $items = Item::query()
            ->with('category')
            ->where('game_id', $game->id)
            ->where('is_active', 1)
            ->orderBy('top_seller', 'desc')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Catalog item berhasil diambil.',
            'data' => [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->game_name,
                    'image' => $game->image
                        ? asset('storage/' . $game->image)
                        : null,
                ],

                'items' => $items->map(function ($item) {
                    return $this->formatItem($item);
                })->values(),
            ],
        ]);
    }


    /**
     * GET /api/v1/catalog/games/{game}/categories
     */
    public function gameCategories(Game $game): JsonResponse
    {
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
                }
            ])
            ->orderBy('category_name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Catalog berdasarkan kategori berhasil diambil.',
            'data' => [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->game_name,
                    'image' => $game->image
                        ? asset('storage/' . $game->image)
                        : null,
                ],

                'categories' => $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->category_name,
                        'use_qty' => (bool) $category->use_qty,

                        'items' => $category->items
                            ->map(function ($item) {
                                return $this->formatItem($item);
                            })
                            ->values(),
                    ];
                })->values(),
            ],
        ]);
    }


    /**
     * GET /api/v1/catalog/items/{item}
     */
    public function item(Item $item): JsonResponse
    {
        if (!$item->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak tersedia.',
            ], 404);
        }

        $item->load([
            'game',
            'category',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail item berhasil diambil.',
            'data' => [
                'item' => $this->formatItem($item, true),
            ],
        ]);
    }


    /**
     * Format item untuk seluruh response catalog.
     */
    private function formatItem(Item $item, bool $detail = false): array
    {
        $data = [
            'id' => $item->id,

            'name' => $item->item_name,

            'qty' => $item->qty,

            'price' => (float) $item->price,

            'formatted_price' => 'Rp ' . number_format(
                $item->price,
                0,
                ',',
                '.'
            ),

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

        if ($detail && $item->game) {
            $data['game'] = [
                'id' => $item->game->id,
                'name' => $item->game->game_name,
                'image' => $item->game->image
                    ? asset('storage/' . $item->game->image)
                    : null,
            ];
        }

        return $data;
    }
}