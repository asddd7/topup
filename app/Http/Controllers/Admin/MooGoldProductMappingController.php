<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Items;
use App\Models\ItemCategory;
use App\Models\MooGoldProductMapping;
use Illuminate\Http\Request;

class MooGoldProductMappingController extends Controller
{
    /**
     * Halaman utama Product Mapping
     */
    public function index()
    {
        return view(
            'moogold.admin.product-mapping.index'
        );
    }


    /**
     * Data Product MooGold
     *
     * GET
     * /admin/moogold/product-mapping/data
     */
    public function data(Request $request)
    {
        $query = MooGoldProductMapping::query()
            ->with([
                'game:id,game_name',
                'category:id,category_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | MooGold Category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'moogold_category_id',
                (string) $request->category_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Game
        |--------------------------------------------------------------------------
        */

        if ($request->filled('game_id')) {
            $query->where(
                'game_id',
                $request->game_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mapping Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('mapped')) {

            if ($request->mapped === '1') {
                $query->whereNotNull('game_id');
            }

            if ($request->mapped === '0') {
                $query->whereNull('game_id');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'product_name',
                    'like',
                    "%{$search}%"
                );

                $q->orWhere(
                    'moogold_product_id',
                    'like',
                    "%{$search}%"
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max(
                $request->integer('per_page', 50),
                10
            ),
            100
        );

        $mappings = $query
            ->orderBy('product_name')
            ->paginate($perPage);


        return response()->json([
            'success' => true,
            'data' => $mappings,
        ]);
    }


    /**
     * Games
     */
    public function games()
    {
        $games = Game::query()
            ->select([
                'id',
                'game_name',
            ])
            ->where('is_active', true)
            ->orderBy('game_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $games,
        ]);
    }


    /**
     * Categories berdasarkan Game
     */
public function categories(Request $request)
{
    $request->validate([
        'game_id' => ['required', 'integer'],
    ]);

    $categories = ItemCategory::query()
        ->select(
            'item_categories.id',
            'item_categories.category_name',
            'item_categories.use_qty'
        )
        ->whereHas('items', function ($query) use ($request) {
            $query->where('game_id', $request->game_id);
        })
        ->orderBy('item_categories.category_name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}


    /**
     * Update Mapping
     */
    public function update(
        Request $request,
        MooGoldProductMapping $mapping
    ) {

        $validated = $request->validate([
            'game_id' => [
                'nullable',
                'exists:games,id',
            ],

            'category_id' => [
                'nullable',
                'exists:item_categories,id',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Pastikan Category milik Game
        |--------------------------------------------------------------------------
        */

        if (
            !empty($validated['game_id']) &&
            !empty($validated['category_id'])
        ) {

            $validCategory = ItemCategory::query()
                ->where(
                    'id',
                    $validated['category_id']
                )
                ->where(
                    'game_id',
                    $validated['game_id']
                )
                ->exists();

            if (!$validCategory) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Kategori lokal tidak termasuk dalam game yang dipilih.',
                ], 422);
            }
        }


        $mapping->update(
            $validated
        );


        $mapping->load([
            'game:id,game_name',
            'category:id,category_name',
        ]);


        return response()->json([
            'success' => true,
            'message' =>
                'Mapping berhasil disimpan.',
            'data' => $mapping,
        ]);
    }
}