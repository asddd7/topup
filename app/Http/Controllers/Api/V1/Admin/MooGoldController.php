<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\MooGold\MooGoldService;
use App\Services\MooGold\MooGoldCatalogService;
use Illuminate\Http\JsonResponse;

class MooGoldController extends Controller
{
    public function __construct(
        protected MooGoldService $mooGold
    ) {
    }


    /**
     * =========================================================
     * BALANCE
     * =========================================================
     */
    public function balance(): JsonResponse
    {
        try {

            $result =
                $this->mooGold->balance();

            return response()->json([

                'success' => true,

                'data' => $result,

            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }

    public function categories(): JsonResponse
{
    try {

        $result =
            $this->mooGold->categories();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * =========================================================
     * PRODUCTS
     * =========================================================
     */
    public function products(
        int $categoryId
    ): JsonResponse {

        try {

            $result =
                $this->mooGold
                    ->products($categoryId);

            return response()->json([

                'success' => true,

                'data' => $result,

            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * =========================================================
     * PRODUCT DETAIL
     * =========================================================
     */
    public function product(
        int $productId
    ): JsonResponse {

        try {

            $result =
                $this->mooGold
                    ->product($productId);

            return response()->json([

                'success' => true,

                'data' => $result,

            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }

public function syncProduct(
    int $productId,
    MooGoldCatalogService $catalog
): JsonResponse {

    try {

        $gameId = request()->integer('game_id');
        $categoryId = request()->integer('category_id');

        if (!$gameId) {

            return response()->json([
                'success' => false,
                'message' => 'game_id wajib diisi.'
            ], 422);
        }

        if (!$categoryId) {

            return response()->json([
                'success' => false,
                'message' => 'category_id wajib diisi.'
            ], 422);
        }

        $result =
            $catalog->syncProduct(
                productId: $productId,
                moogoldCategoryId: 50,
                gameId: $gameId,
                categoryId: $categoryId
            );

        return response()->json([

            'success' => true,

            'message' =>
                'Katalog MooGold berhasil disinkronkan.',

            'data' =>
                $result,

        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([

            'success' => false,

            'message' =>
                $e->getMessage(),

        ], 500);
    }
}
}