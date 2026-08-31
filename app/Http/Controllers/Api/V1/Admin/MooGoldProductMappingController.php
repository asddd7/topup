<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\MooGoldProductMapping;
use App\Services\MooGold\MooGoldProductMappingService;
use Illuminate\Http\Request;
use Throwable;

class MooGoldProductMappingController extends Controller
{
    public function __construct(
        protected MooGoldProductMappingService $mappingService
    ) {
    }

    /**
     * =========================================================
     * INDEX
     * =========================================================
     *
     * GET
     * /api/v1/admin/moogold/product-mapping
     */
public function index(Request $request)
{
    $query = MooGoldProductMapping::query()
        ->with([
            'game:id,game_name',
            'category:id,category_name',
        ]);

    if ($request->filled('category_id')) {

        $query->where(
            'moogold_category_id',
            (string) $request->category_id
        );
    }

    if ($request->filled('game_id')) {

        $query->where(
            'game_id',
            $request->game_id
        );
    }

    if ($request->filled('mapped')) {

        if ($request->mapped === '1') {
            $query->whereNotNull('game_id');
        }

        if ($request->mapped === '0') {
            $query->whereNull('game_id');
        }
    }

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
     * =========================================================
     * SHOW
     * =========================================================
     */

    public function show(MooGoldProductMapping $mapping)
    {
        $mapping->load([
            'game:id,name',
            'category:id,name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $mapping,
        ]);
    }


    /**
     * =========================================================
     * UPDATE MAPPING
     * =========================================================
     *
     * PUT
     * /api/v1/admin/moogold/product-mapping/{mapping}
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
    | Validasi Category terhadap Game melalui ITEMS
    |--------------------------------------------------------------------------
    */

    if (
        !empty($validated['game_id']) &&
        !empty($validated['category_id'])
    ) {

        $categoryBelongsToGame = Item::query()
            ->where(
                'game_id',
                $validated['game_id']
            )
            ->where(
                'category_id',
                $validated['category_id']
            )
            ->exists();

        if (!$categoryBelongsToGame) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Kategori lokal tidak tersedia untuk Game yang dipilih.',
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
        'message' => 'Mapping berhasil disimpan.',
        'data' => $mapping,
    ]);
}


    /**
     * =========================================================
     * GAMES
     * =========================================================
     *
     * GET
     * /api/v1/admin/moogold/product-mapping/games
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
 * =========================================================
 * CATEGORIES
 * =========================================================
 *
 * GET
 * /api/v1/admin/moogold/product-mapping/categories?game_id=2
 */
public function categories(Request $request)
{
    $validated = $request->validate([
        'game_id' => [
            'required',
            'exists:games,id',
        ],
    ]);

    $categories = ItemCategory::query()
        ->select([
            'item_categories.id',
            'item_categories.category_name',
            'item_categories.use_qty',
        ])
        ->whereHas('items', function ($query) use ($validated) {

            $query->where(
                'game_id',
                $validated['game_id']
            );

        })
        ->orderBy('category_name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}


    /**
     * =========================================================
     * SYNC CATEGORY
     * =========================================================
     *
     * POST
     * /api/v1/admin/moogold/product-mapping/sync-category
     */
    public function syncCategory(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        try {

            $result = $this->mappingService->syncCategory(
                $validated['category_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'MooGold category berhasil disinkronkan.',
                'data' => $result,
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan sync category MooGold.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}