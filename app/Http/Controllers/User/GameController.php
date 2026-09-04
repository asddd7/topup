<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\MooGold\MooGoldService;
use RuntimeException;
use App\Models\Game;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | WEB
    |--------------------------------------------------------------------------
    */

    public function show(Game $game)
    {
        $itemsQuery = Item::query()
            ->where('game_id', $game->id)
            ->where('is_active', 1)
            ->with('category');

        /*
        |--------------------------------------------------------------------------
        | MOBILE LEGENDS INDONESIA
        |--------------------------------------------------------------------------
        |
        | Untuk sementara Railway hanya menampilkan produk
        | MooGold Mobile Legends Indonesia.
        |
        */

        if ($game->id === 1) {

            $itemsQuery
                ->where('moogold_product_id', 2362359);

        }

        $items = $itemsQuery
            ->orderBy('category_id')
            ->orderBy('price')
            ->get();


        $payments = Payment::query()
            ->where('is_active', 1)
            ->orderBy('payment_type')
            ->get();


        return view(
            'game.show',
            compact(
                'game',
                'items',
                'payments'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME LIST
    |--------------------------------------------------------------------------
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
                'games' => $games,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME DETAIL
    |--------------------------------------------------------------------------
    */

    public function showApi(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail game berhasil diambil.',
            'data' => [
                'game' => $game,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME ITEMS
    |--------------------------------------------------------------------------
    */

    public function items(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        $items = Item::query()
            ->where('game_id', $game->id)
            ->where('is_active', 1)
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Item game berhasil diambil.',
            'data' => [
                'game' => $game,
                'items' => $items,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API - GAME PAYMENTS
    |--------------------------------------------------------------------------
    */

    public function payments(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game tidak tersedia.',
            ], 404);
        }

        $payments = Payment::query()
            ->where('is_active', 1)
            ->orderBy('payment_type')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil diambil.',
            'data' => [
                'game' => $game,
                'payments' => $payments,
            ],
        ]);
    }

/*
|--------------------------------------------------------------------------
| MOO GOLD VALIDATION PRODUCT ID
|--------------------------------------------------------------------------
|
| Product yang dipakai untuk ORDER bisa berbeda dengan product
| yang dipakai untuk VALIDATION.
|
| Contoh:
|
| Mobile Legends Indonesia
| Order Product:
| 2362359
|
| Validation Product:
| 15145
|
*/

protected function getValidationProductId(
    Item $item
): ?int
{
    /*
    |--------------------------------------------------------------------------
    | MOBILE LEGENDS INDONESIA
    |--------------------------------------------------------------------------
    |
    | Order:
    | Product ID 2362359
    |
    | Validation:
    | Global Product ID 15145
    | dll.
    */

    if (
        (int) $item->moogold_product_id === 2362359
    ) {
        return 15145;
    }

    if (
        (int) $item->moogold_product_id === 36926589
    ) {
        return 4233885;
    }


    /*
    |--------------------------------------------------------------------------
    | DEFAULT
    |--------------------------------------------------------------------------
    */

    if (
        !empty($item->moogold_variation_id)
    ) {
        return (int)
            $item->moogold_variation_id;
    }


    return null;
}

public function validatePlayer(
    Request $request,
    MooGoldService $mooGold
): JsonResponse {

    $validated = $request->validate([
        'item_id' => [
            'required',
            'integer',
            'exists:items,id',
        ],

        'user_id' => [
            'required',
            'string',
            'max:100',
        ],

        'server_id' => [
            'nullable',
            'string',
            'max:100',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | ITEM
    |--------------------------------------------------------------------------
    */

    $item = Item::query()
        ->where('id', $validated['item_id'])
        ->where('is_active', 1)
        ->first();

    if (!$item) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan atau tidak aktif.',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | MOO GOLD MAPPING
    |--------------------------------------------------------------------------
    */

    if (
        empty($item->moogold_product_id)
    ) {
        return response()->json([
            'success' => true,
            'message' => 'Produk ini tidak memerlukan validasi MooGold.',
            'data' => [
                'valid' => null,
                'validation_available' => false,
                'nickname' => null,
            ],
        ]);
    }

/*
|--------------------------------------------------------------------------
| PRODUCT ID UNTUK VALIDATION
|--------------------------------------------------------------------------
|
| Product ID untuk validation dapat berbeda dengan
| product / variation yang digunakan saat create order.
|
*/

$productId =
    $this->getValidationProductId(
        $item
    );

    if (!$productId) {

    return response()->json([
        'success' => true,

        'message' =>
            'Validasi player tidak tersedia untuk produk ini.',

        'data' => [
            'valid' => null,
            'validation_available' => false,
            'nickname' => null,
        ],
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | PLAYER DATA
    |--------------------------------------------------------------------------
    */

    $playerData = [
        'User ID' => (string) $validated['user_id'],
    ];

    if (!empty($validated['server_id'])) {
        $playerData['Server ID'] =
            (string) $validated['server_id'];
    }

    try {

        $result = $mooGold->validateProduct(
            $productId,
            $playerData
        );

        \Log::info(
            'MooGold player validation',
            [
                'item_id' =>
                    $item->id,

                /*
                |--------------------------------------------------------------------------
                | Product asli item
                |--------------------------------------------------------------------------
                */

                'order_product_id' =>
                    $item->moogold_product_id,

                'order_variation_id' =>
                    $item->moogold_variation_id,

                /*
                |--------------------------------------------------------------------------
                | Product yang dipakai khusus validation
                |--------------------------------------------------------------------------
                */

                'validation_product_id' =>
                    $productId,

                'user_id' =>
                    $validated['user_id'],

                'server_id' =>
                    $validated['server_id'] ?? null,

                'response' =>
                    $result,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            data_get($result, 'status')
            ??
            data_get($result, 'success');

        $message = (string) (
            data_get($result, 'message')
            ??
            data_get($result, 'data.message')
            ??
            ''
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION UNAVAILABLE
        |--------------------------------------------------------------------------
        |
        | MooGold mengembalikan status false tetapi pesan menunjukkan
        | bahwa fitur validation memang tidak tersedia.
        |
        */

        $validationUnavailable =
            str_contains(
                strtolower($message),
                'validation is not available'
            );

        if ($validationUnavailable) {

            return response()->json([
                'success' => true,

                'message' =>
                    'Validasi player tidak tersedia untuk produk ini.',

                'data' => [
                    'valid' => null,

                    'validation_available' =>
                        false,

                    'nickname' =>
                        null,

                    'raw' =>
                        $result,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EXPLICIT INVALID
        |--------------------------------------------------------------------------
        */

        if (
            $status === false ||
            $status === 0 ||
            $status === 'false'
        ) {

            return response()->json([
                'success' => false,

                'message' =>
                    $message
                    ?: 'User ID atau Server ID tidak valid.',

                'data' => [
                    'valid' => false,

                    'validation_available' =>
                        true,

                    'nickname' =>
                        null,

                    'raw' =>
                        $result,
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | NICKNAME
        |--------------------------------------------------------------------------
        */
                
        $nickname =
            data_get($result, 'nickname')
            ??
            data_get($result, 'username')
            ??
            data_get($result, 'Username')
            ??
            data_get($result, 'data.nickname')
            ??
            data_get($result, 'data.username')
            ??
            data_get($result, 'data.Username')
            ??
            data_get($result, 'data.account_name')
            ??
            null;

        /*
        |--------------------------------------------------------------------------
        | VALID
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' =>
                'ID pemain berhasil divalidasi.',

            'data' => [
                'valid' => true,

                'validation_available' =>
                    true,

                'nickname' =>
                    $nickname,

                'raw' =>
                    $result,
            ],
        ]);

    } catch (RuntimeException $exception) {

        \Log::warning(
            'MooGold player validation gagal',
            [
                'item_id' =>
                    $item->id,

                'message' =>
                    $exception->getMessage(),
            ]
        );

        return response()->json([
            'success' => false,

            'message' =>
                $exception->getMessage()
                ?: 'User ID atau Server ID tidak valid.',

            'data' => [
                'valid' => false,

                'validation_available' =>
                    true,

                'nickname' =>
                    null,
            ],
        ], 422);
    }
}

}