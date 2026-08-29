<?php

namespace App\Services\MooGold;

use App\Models\MooGoldProductMapping;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MooGoldProductMappingService
{
    public function __construct(
        protected MooGoldService $mooGold
    ) {
    }

    /**
     * =========================================================
     * SYNC CATEGORY
     * =========================================================
     *
     * Ambil seluruh product dari satu kategori MooGold
     * kemudian simpan/update ke tabel mapping.
     *
     * Contoh:
     *
     * syncCategory('50');
     */
    public function syncCategory(string|int $moogoldCategoryId): array
    {
        $response = $this->mooGold->products(
            $moogoldCategoryId
        );

        if (!is_array($response)) {
            throw new RuntimeException(
                'Response products MooGold tidak valid.'
            );
        }

        $products = $this->extractProducts($response);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $products,
            $moogoldCategoryId,
            &$created,
            &$updated,
            &$skipped
        ) {

            foreach ($products as $product) {

                /*
                |--------------------------------------------------------------------------
                | MooGold menggunakan:
                |
                | ID
                | post_title
                |--------------------------------------------------------------------------
                */

                $productId = $this->getProductId($product);

                if (!$productId) {
                    $skipped++;
                    continue;
                }

                $productName = $this->getProductName($product);

                /*
                |--------------------------------------------------------------------------
                | Cari mapping yang sudah ada
                |--------------------------------------------------------------------------
                */

                $mapping = MooGoldProductMapping::where(
                    'moogold_category_id',
                    (string) $moogoldCategoryId
                )
                ->where(
                    'moogold_product_id',
                    $productId
                )
                ->first();

                if ($mapping) {

                    /*
                    |--------------------------------------------------------------------------
                    | Update data MooGold
                    |
                    | PENTING:
                    | game_id dan category_id TIDAK disentuh.
                    |
                    | Jadi mapping lokal yang sudah dibuat Admin
                    | tidak akan hilang ketika sync ulang.
                    |--------------------------------------------------------------------------
                    */

                    $mapping->update([
                        'product_name' => $productName,
                        'product_data' => $product,
                    ]);

                    $updated++;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Product baru
                    |--------------------------------------------------------------------------
                    */

                    MooGoldProductMapping::create([
                        'moogold_category_id' => (string) $moogoldCategoryId,
                        'moogold_product_id' => $productId,
                        'product_name' => $productName,
                        'product_data' => $product,

                        /*
                        |--------------------------------------------------------------------------
                        | Belum ditentukan Admin
                        |--------------------------------------------------------------------------
                        */

                        'game_id' => null,
                        'category_id' => null,

                        'is_active' => true,
                    ]);

                    $created++;
                }
            }
        });

        return [
            'category_id' => (string) $moogoldCategoryId,
            'total_products' => count($products),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }


    /**
     * =========================================================
     * EXTRACT PRODUCTS
     * =========================================================
     */

    protected function extractProducts(array $response): array
    {
        /*
        |--------------------------------------------------------------------------
        | Response:
        |
        | [
        |     [
        |         "ID" => "332",
        |         "post_title" => "Ragnarok M: Eternal Love (SEA)"
        |     ],
        |     ...
        | ]
        |--------------------------------------------------------------------------
        */

        if (isset($response['products']) && is_array($response['products'])) {
            return $response['products'];
        }

        if (isset($response['data']) && is_array($response['data'])) {

            if (array_is_list($response['data'])) {
                return $response['data'];
            }

            if (
                isset($response['data']['products']) &&
                is_array($response['data']['products'])
            ) {
                return $response['data']['products'];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Response langsung berupa list product
        |--------------------------------------------------------------------------
        */

        if (array_is_list($response)) {
            return $response;
        }

        return [];
    }


    /**
     * =========================================================
     * PRODUCT ID
     * =========================================================
     *
     * MooGold:
     *
     * "ID": "332"
     */
    protected function getProductId(array $product): ?string
    {
        if (isset($product['ID'])) {
            return (string) $product['ID'];
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (isset($product['product_id'])) {
            return (string) $product['product_id'];
        }

        if (isset($product['id'])) {
            return (string) $product['id'];
        }

        return null;
    }


    /**
     * =========================================================
     * PRODUCT NAME
     * =========================================================
     *
     * MooGold:
     *
     * "post_title": "Ragnarok M: Eternal Love (SEA)"
     */
    protected function getProductName(array $product): string
    {
        if (isset($product['post_title'])) {
            return (string) $product['post_title'];
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (isset($product['product_name'])) {
            return (string) $product['product_name'];
        }

        if (isset($product['name'])) {
            return (string) $product['name'];
        }

        return 'Unnamed Product';
    }
}