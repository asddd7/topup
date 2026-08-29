<?php

namespace App\Services\MooGold;

use App\Models\Game;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MooGoldCatalogService
{
    public function __construct(
        protected MooGoldService $mooGold
    ) {
    }


    /**
     * =========================================================
     * SYNC PRODUCT
     * =========================================================
     *
     * Contoh:
     *
     * Product ID 15145
     * Mobile Legends
     *
     */
    public function syncProduct(
        int $productId,
        int $moogoldCategoryId,
        int $gameId,
        int $categoryId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Ambil detail product dari MooGold
        |--------------------------------------------------------------------------
        */

        $product =
            $this->mooGold->product(
                $productId
            );


        if (
            empty($product['Product_Name'])
        ) {

            throw new RuntimeException(
                'Product MooGold tidak memiliki Product_Name.'
            );
        }


        $productName =
            trim(
                $product['Product_Name']
            );


        /*
        |--------------------------------------------------------------------------
        | Cari / buat Game
        |--------------------------------------------------------------------------
        */

$game = Game::find($gameId);

if (!$game) {

    throw new RuntimeException(
        "Game dengan ID {$gameId} tidak ditemukan."
    );
}


        /*
        |--------------------------------------------------------------------------
        | Variation
        |--------------------------------------------------------------------------
        */

        $variations =
            $product['Variation']
            ?? [];


        if (!is_array($variations)) {

            throw new RuntimeException(
                'Variation MooGold tidak valid.'
            );
        }


        $created = 0;
        $updated = 0;
        $disabled = 0;


        /*
        |--------------------------------------------------------------------------
        | Sync setiap variation
        |--------------------------------------------------------------------------
        */

        foreach ($variations as $variation) {

            $variationId =
                isset($variation['variation_id'])
                    ? (int) $variation['variation_id']
                    : null;


            if (!$variationId) {
                continue;
            }


            $variationName =
                trim(
                    $variation['variation_name']
                    ?? ''
                );


            $variationPrice =
                isset(
                    $variation['variation_price']
                )
                    ? (float)
                        $variation['variation_price']
                    : null;


            $stockStatus =
                strtolower(
                    trim(
                        $variation['stock_status']
                        ?? 'outofstock'
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Bersihkan nama item
            |--------------------------------------------------------------------------
            */

            $itemName =
                $this->cleanVariationName(
                    $variationName
                );


            if ($itemName === '') {

                $itemName =
                    'MooGold #' .
                    $variationId;
            }


            /*
            |--------------------------------------------------------------------------
            | Tentukan status provider
            |--------------------------------------------------------------------------
            */

            $providerAvailable =
                $stockStatus === 'instock';


            /*
            |--------------------------------------------------------------------------
            | Cari item berdasarkan variation ID
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
            | Harga jual baru hanya untuk item BARU
            |--------------------------------------------------------------------------
            |
            | Item lama jangan ditimpa price.
            |
            */

            if (!$item) {

                /*
                |--------------------------------------------------------------------------
                | Default markup sementara
                |--------------------------------------------------------------------------
                |
                | Nanti kita pindahkan ke Settings.
                |
                */

                $sellingPrice =
                    $this->calculateSellingPrice(
                        $variationPrice
                    );


                $item =
                    Item::create([

                        'game_id' => 
                            $gameId,

                        'category_id' =>
                            $categoryId,

                        'item_name' =>
                            $itemName,

                        'qty' =>
                            1,

                        'price' =>
                            $sellingPrice,

                        'stock' =>
                            $providerAvailable
                                ? 1
                                : 0,

                        'description' =>
                            null,

                        'image' =>
                            null,

                        'is_active' =>
                            $providerAvailable,

                        'moogold_category_id' =>
                            $moogoldCategoryId,

                        'moogold_product_id' =>
                            $productId,

                        'moogold_variation_id' =>
                            $variationId,

                        'moogold_price' =>
                            $variationPrice,

                        'moogold_stock_status' =>
                            $stockStatus,

                        'moogold_synced_at' =>
                            now(),

                    ]);


                $created++;

            } else {

                /*
                |--------------------------------------------------------------------------
                | UPDATE ITEM
                |--------------------------------------------------------------------------
                */

                $item->update([

                    'item_name' =>
                        $itemName,

                    'moogold_category_id' =>
                        $moogoldCategoryId,

                    'moogold_product_id' =>
                        $productId,

                    'moogold_price' =>
                        $variationPrice,

                    'moogold_stock_status' =>
                        $stockStatus,

                    'moogold_synced_at' =>
                        now(),

                    /*
                    | Jangan mengubah price.
                    */
                    'is_active' =>
                        $providerAvailable,

                    'stock' =>
                        $providerAvailable
                            ? 1
                            : 0,

                ]);


                if ($providerAvailable) {
                    $updated++;
                } else {
                    $disabled++;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Return result
        |--------------------------------------------------------------------------
        */

        return [

            'game_id' =>
                $game->id,

            'game_name' =>
                $game->game_name,

            'product_id' =>
                $productId,

            'product_name' =>
                $productName,

            'total_variations' =>
                count($variations),

            'created' =>
                $created,

            'updated' =>
                $updated,

            'disabled' =>
                $disabled,

        ];
    }


    /**
     * =========================================================
     * CLEAN VARIATION NAME
     * =========================================================
     */
    protected function cleanVariationName(
        string $name
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Hapus prefix "Mobile Legends - "
        |--------------------------------------------------------------------------
        */

        $name =
            preg_replace(
                '/^.*?\s-\s/',
                '',
                $name
            );


        /*
        |--------------------------------------------------------------------------
        | Hapus "(#123456)"
        |--------------------------------------------------------------------------
        */

        $name =
            preg_replace(
                '/\s*\(#\d+\)\s*$/',
                '',
                $name
            );


        return trim(
            $name
        );
    }


    /**
     * =========================================================
     * CALCULATE SELLING PRICE
     * =========================================================
     *
     * Sementara menggunakan markup 20%.
     *
     * Nanti kita pindahkan ke Settings.
     */
    protected function calculateSellingPrice(
        ?float $cost
    ): ?float {

        if (
            $cost === null
        ) {
            return null;
        }


        $price =
            $cost * 1.20;


        /*
        |--------------------------------------------------------------------------
        | Bulatkan ke atas ke Rp100
        |--------------------------------------------------------------------------
        */

        return
            ceil(
                $price / 100
            ) * 100;
    }
}