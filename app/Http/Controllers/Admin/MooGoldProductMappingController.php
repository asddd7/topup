<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\MooGoldProductMapping;
use App\Services\MooGold\MooGoldService;
use Illuminate\Http\JsonResponse;
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
/**
 * =========================================================
 * SYNC MOO GOLD CATEGORY
 * =========================================================
 *
 * POST
 * /admin/moogold/product-mapping/sync-category
 */
public function syncCategory(
    Request $request,
    MooGoldService $mooGold
): JsonResponse {

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'category_id' => [
            'required',
            'integer',
            'min:1',
        ],
    ]);


    $categoryId = (int) $validated['category_id'];


    /*
    |--------------------------------------------------------------------------
    | AMBIL PRODUCT DARI MOOGOLD
    |--------------------------------------------------------------------------
    */

    try {

        $result = $mooGold->products(
            $categoryId
        );

    } catch (\Throwable $e) {

        return response()->json([

            'success' => false,

            'message' =>
                'Gagal mengambil product dari MooGold: ' .
                $e->getMessage(),

        ], 500);
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI RESPONSE
    |--------------------------------------------------------------------------
    */

    $products =
        $result['data']
        ?? $result;


    if (!is_array($products)) {

        return response()->json([

            'success' => false,

            'message' =>
                'Format response product MooGold tidak valid.',

        ], 500);
    }


    /*
    |--------------------------------------------------------------------------
    | STATISTICS
    |--------------------------------------------------------------------------
    */

    $created = 0;

    $updated = 0;

    $skipped = 0;


    /*
    |--------------------------------------------------------------------------
    | LOOP PRODUCT
    |--------------------------------------------------------------------------
    */

    foreach ($products as $product) {

        /*
        |--------------------------------------------------------------------------
        | Ambil Product ID
        |--------------------------------------------------------------------------
        */

        $productId =
            $product['ID']
            ?? $product['id']
            ?? $product['product_id']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Ambil Product Name
        |--------------------------------------------------------------------------
        */

        $productName =
            $product['post_title']
            ?? $product['Product_Name']
            ?? $product['product_name']
            ?? $product['name']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Product wajib memiliki ID
        |--------------------------------------------------------------------------
        */

        if (!$productId) {

            $skipped++;

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Jika nama kosong
        |--------------------------------------------------------------------------
        */

        if (!$productName) {

            $productName =
                'MooGold Product #' . $productId;
        }


        /*
        |--------------------------------------------------------------------------
        | Cari Mapping
        |--------------------------------------------------------------------------
        |
        | Jangan menggunakan firstOrCreate sederhana karena
        | kita ingin memperbarui product_data setiap sync.
        |
        */

        $mapping =
            MooGoldProductMapping::query()
                ->where(
                    'moogold_category_id',
                    $categoryId
                )
                ->where(
                    'moogold_product_id',
                    (string) $productId
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | DATA MAPPING
        |--------------------------------------------------------------------------
        |
        | PERHATIKAN:
        |
        | game_id
        | category_id
        | is_active
        |
        | TIDAK kita ubah ketika sync.
        |
        | Jadi mapping lokal yang sudah dibuat admin
        | tetap aman.
        |
        */

        $mappingData = [

            'moogold_category_id' =>
                $categoryId,

            'moogold_product_id' =>
                (string) $productId,

            'product_name' =>
                $productName,

            'product_data' =>
                json_encode(
                    $product,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ),

        ];


        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        if (!$mapping) {

            $mappingData['game_id'] =
                null;

            $mappingData['category_id'] =
                null;

            $mappingData['is_active'] =
                true;


            MooGoldProductMapping::create(
                $mappingData
            );


            $created++;

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $mapping->update(
            $mappingData
        );


        $updated++;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' => true,

        'message' =>
            'MooGold Category berhasil disinkronkan.',

        'data' => [

            'category_id' =>
                $categoryId,

            'total_products' =>
                count($products),

            'created' =>
                $created,

            'updated' =>
                $updated,

            'skipped' =>
                $skipped,

        ],

    ]);
}



public function syncItems(
    MooGoldProductMapping $mapping,
    MooGoldService $mooGold
): JsonResponse {

    /*
    |--------------------------------------------------------------------------
    | Validasi Mapping
    |--------------------------------------------------------------------------
    */

    if (
        !$mapping->game_id ||
        !$mapping->category_id
    ) {

        return response()->json([

            'success' => false,

            'message' =>
                'Product belum dihubungkan ke Game dan Category lokal.'

        ], 422);

    }


    /*
    |--------------------------------------------------------------------------
    | Validasi Mapping Active
    |--------------------------------------------------------------------------
    */

    if (!$mapping->is_active) {

        return response()->json([

            'success' => false,

            'message' =>
                'Product mapping tidak aktif.'

        ], 422);

    }


    /*
    |--------------------------------------------------------------------------
    | Ambil Detail Product MooGold
    |--------------------------------------------------------------------------
    */

    try {

        $result =
            $mooGold->product(
                $mapping->moogold_product_id
            );

    } catch (\Throwable $e) {

        return response()->json([

            'success' => false,

            'message' =>
                'Gagal mengambil product dari MooGold: ' .
                $e->getMessage(),

        ], 500);

    }


    /*
    |--------------------------------------------------------------------------
    | Ambil Data Product
    |--------------------------------------------------------------------------
    */

    $product =
        $result['data'] ??
        $result;


    /*
    |--------------------------------------------------------------------------
    | Validasi Variation
    |--------------------------------------------------------------------------
    */

    $variations =
        $product['Variation'] ??
        [];


    if (empty($variations)) {

        return response()->json([

            'success' => false,

            'message' =>
                'Product MooGold tidak memiliki variation.'

        ], 422);

    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    $created = 0;

    $updated = 0;


    /*
    |--------------------------------------------------------------------------
    | Sync Variations
    |--------------------------------------------------------------------------
    */

    foreach (
        $variations as $variation
    ) {

        $variationId =
            $variation['variation_id'] ??
            null;


        if (!$variationId) {

            continue;

        }


        /*
        |--------------------------------------------------------------------------
        | Data
        |--------------------------------------------------------------------------
        */

        $itemData = [

            /*
            |--------------------------------------------------------------------------
            | Local
            |--------------------------------------------------------------------------
            */

            'game_id' =>
                $mapping->game_id,

            'category_id' =>
                $mapping->category_id,


            /*
            |--------------------------------------------------------------------------
            | Item
            |--------------------------------------------------------------------------
            */

            'item_name' =>
                $variation['variation_name']
                ?? $mapping->product_name,

            'qty' =>
                1,

            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */

            'price' =>
                $variation['variation_price']
                ?? 0,


            /*
            |--------------------------------------------------------------------------
            | Stock
            |--------------------------------------------------------------------------
            */

            'stock' =>
                (
                    ($variation['stock_status'] ?? '')
                    === 'instock'
                )
                    ? 999999
                    : 0,


            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            'image' =>
                $product['Image_URL']
                ?? null,


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'is_active' =>
                (
                    ($variation['stock_status'] ?? '')
                    === 'instock'
                ),


            /*
            |--------------------------------------------------------------------------
            | MooGold
            |--------------------------------------------------------------------------
            */

            'moogold_category_id' =>
                $mapping->moogold_category_id,

            'moogold_product_id' =>
                $mapping->moogold_product_id,

            'moogold_variation_id' =>
                $variationId,

            'moogold_price' =>
                $variation['variation_price']
                ?? null,

            'moogold_stock_status' =>
                $variation['stock_status']
                ?? null,

            'moogold_synced_at' =>
                now(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Cari Item Berdasarkan Variation ID
        |--------------------------------------------------------------------------
        */

        $item =
            Item::query()
                ->where(
                    'moogold_variation_id',
                    $variationId
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        if (!$item) {

            Item::create(
                $itemData
            );

            $created++;

            continue;

        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $item->update(
            $itemData
        );

        $updated++;

    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' => true,

        'message' =>
            'Variation MooGold berhasil disinkronkan ke Item.',

        'data' => [

            'mapping_id' =>
                $mapping->id,

            'product_id' =>
                $mapping->moogold_product_id,

            'total_variations' =>
                count($variations),

            'created' =>
                $created,

            'updated' =>
                $updated,

        ],

    ]);

}
}