<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Integrations\MooGold\MooGoldService;
use RuntimeException;
use App\Models\Game;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Integrations\Midtrans\MidtransService;
use App\Services\PromotionService;

class GameController extends Controller
{

public function __construct(
    protected MidtransService $midtrans,
    protected PromotionService $promotion
) {
}

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
            ->whereDoesntHave('bundleItems', function ($query) {
                $query->where('is_active', false);
            })
            ->with(['category', 'bundleItems']);

        /*
        |--------------------------------------------------------------------------
        | MOBILE LEGENDS INDONESIA
        |--------------------------------------------------------------------------
        */

        if ($game->id === 1) {

            $itemsQuery
                ->where(function ($query) {
                    $query
                        ->where('moogold_product_id', 2362359)
                        ->orWhereHas('bundleItems', function ($bundleQuery) {
                            $bundleQuery->where(
                                'moogold_product_id',
                                2362359
                            );
                        });
                });

        }

        $items = $itemsQuery
            ->orderBy('category_id')
            ->orderBy('price')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | AUTOMATIC PROMOTION DISPLAY PRICE
        |--------------------------------------------------------------------------
        |
        | Harga database tidak diubah.
        |
        | Kita hanya menambahkan:
        |
        | $item->automatic_price
        |
        | untuk kebutuhan tampilan harga coret.
        |
        */

        $items->each(function ($item) use ($game) {

            $item->automatic_price =
                $this->promotion
                    ->calculateAutomaticDisplayPrice(
                        price:
                            (float) $item->price,

                        gameId:
                            (int) $game->id,

                        itemId:
                            (int) $item->id,

                        user:
                            auth()->user()
                    );

        });


        /*
        |--------------------------------------------------------------------------
        | MIDTRANS PAYMENT CHANNELS
        |--------------------------------------------------------------------------
        |
        | TETAP dipertahankan.
        |
        */

        try {

            $paymentChannels =
                $this->midtrans
                    ->getSnapPaymentChannels();

        } catch (\Throwable $e) {

            \Log::error(
                'Gagal mengambil Midtrans payment channels.',
                [
                    'game_id' =>
                        $game->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            $paymentChannels = [];

        }


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'game.show',
            compact(
                'game',
                'items',
                'paymentChannels'
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

        try {

            $paymentChannels =
                $this->midtrans
                    ->getSnapPaymentChannels();

        } catch (\Throwable $e) {

            \Log::error(
                'Gagal mengambil Midtrans payment channels API.',
                [
                    'game_id' =>
                        $game->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Metode pembayaran Midtrans tidak dapat diambil.',
            ], 503);
        }


        return response()->json([
            'success' => true,
            'message' =>
                'Metode pembayaran Midtrans berhasil diambil.',
            'data' => [
                'game' =>
                    $game,

                'payments' =>
                    $paymentChannels,
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
        'game_id' => [
            'required',
            'integer',
            'exists:games,id',
        ],

        'item_id' => [
            'required',
            'integer',
            'exists:items,id',
        ],

        'player_data' => [
            'required',
            'array',
        ],

        'player_data.*' => [
            'nullable',
            'string',
            'max:100',
        ],
    ]);

    $item = Item::with(['game', 'bundleItems'])
        ->where('id', $validated['item_id'])
        ->where('game_id', $validated['game_id'])
        ->where('is_active', 1)
        ->first();

    if (!$item) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan atau tidak aktif.',
        ], 404);
    }

    $game = $item->game;

        $item = $item->bundleItems->first(
            fn ($component) => !empty(
                $component->moogold_product_id
            )
        ) ?? $item;

    if (!$game) {
        return response()->json([
            'success' => false,
            'message' => 'Game untuk produk ini tidak ditemukan.',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Produk tidak menggunakan MooGold
    |--------------------------------------------------------------------------
    */
    if (empty($item->moogold_product_id)) {
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
    | Tentukan product ID untuk validasi
    |--------------------------------------------------------------------------
    */
    $productId = $this->getValidationProductId($item);

    if (!$productId) {
        return response()->json([
            'success' => true,
            'message' => 'Validasi player tidak tersedia untuk produk ini.',
            'data' => [
                'valid' => null,
                'validation_available' => false,
                'nickname' => null,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Bangun payload MooGold secara dynamic dari player_fields
    |--------------------------------------------------------------------------
    |
    | Contoh Game 15:
    |
    | player_data:
    | [
    |     'user_id' => '00088624',
    |     'region'  => 'SEA',
    | ]
    |
    | player_fields:
    | user_id -> User ID
    | region  -> Region
    |
    | Hasil:
    |
    | [
    |     'User ID' => '00088624',
    |     'Region'  => 'SEA',
    | ]
    |
    |--------------------------------------------------------------------------
    */

    $playerData = $validated['player_data'];

    $moogoldPlayerData = [];

    foreach (($game->player_fields ?? []) as $field) {
        $fieldName = $field['name'] ?? null;
        $moogoldField = $field['moogold_field'] ?? null;

        if (!$fieldName || !$moogoldField) {
            continue;
        }

        if (!array_key_exists($fieldName, $playerData)) {
            continue;
        }

        $value = trim((string) $playerData[$fieldName]);

        if ($value === '') {
            continue;
        }

        $moogoldPlayerData[$moogoldField] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan field wajib sudah terisi
    |--------------------------------------------------------------------------
    */
    foreach (($game->player_fields ?? []) as $field) {
        $fieldName = $field['name'] ?? null;
        $label = $field['label'] ?? $fieldName;
        $required = (bool) ($field['required'] ?? false);

        if (!$required || !$fieldName) {
            continue;
        }

        $value = trim((string) ($playerData[$fieldName] ?? ''));

        if ($value === '') {
            return response()->json([
                'success' => false,
                'message' => "{$label} wajib diisi.",
                'data' => [
                    'valid' => false,
                    'validation_available' => true,
                    'nickname' => null,
                ],
            ], 422);
        }
    }

    if (empty($moogoldPlayerData)) {
        return response()->json([
            'success' => false,
            'message' => 'Data player tidak ditemukan.',
            'data' => [
                'valid' => false,
                'validation_available' => true,
                'nickname' => null,
            ],
        ], 422);
    }

    \Log::info('=== PLAYER VALIDATION MOO GOLD ===', [
        'game_id' => $game->id,
        'item_id' => $item->id,
        'order_product_id' => $item->moogold_product_id,
        'order_variation_id' => $item->moogold_variation_id,
        'validation_product_id' => $productId,
        'player_data' => $playerData,
        'moogold_player_data' => $moogoldPlayerData,
    ]);

    try {
        $result = $mooGold->validateProduct(
            $productId,
            $moogoldPlayerData
        );

        \Log::info('MooGold player validation response', [
            'item_id' => $item->id,
            'validation_product_id' => $productId,
            'response' => $result,
        ]);

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
        | MooGold tidak menyediakan validation
        |--------------------------------------------------------------------------
        */
        $validationUnavailable =
            str_contains(
                strtolower($message),
                'validation is not available'
            );

        if ($validationUnavailable) {
            return response()->json([
                'success' => true,
                'message' => 'Validasi player tidak tersedia untuk produk ini.',
                'data' => [
                    'valid' => null,
                    'validation_available' => false,
                    'nickname' => null,
                    'raw' => $result,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Player tidak valid
        |--------------------------------------------------------------------------
        */
        if (
            $status === false ||
            $status === 0 ||
            $status === 'false'
        ) {
            return response()->json([
                'success' => false,
                'message' => $message ?: 'Data player tidak valid.',
                'data' => [
                    'valid' => false,
                    'validation_available' => true,
                    'nickname' => null,
                    'raw' => $result,
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil nickname jika tersedia
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

        return response()->json([
            'success' => true,
            'message' => 'ID pemain berhasil divalidasi.',
            'data' => [
                'valid' => true,
                'validation_available' => true,
                'nickname' => $nickname,
                'raw' => $result,
            ],
        ]);
    } catch (RuntimeException $exception) {
        \Log::warning('MooGold player validation gagal', [
            'item_id' => $item->id,
            'validation_product_id' => $productId,
            'player_data' => $playerData,
            'moogold_player_data' => $moogoldPlayerData,
            'message' => $exception->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage()
                ?: 'Data player tidak valid.',
            'data' => [
                'valid' => false,
                'validation_available' => true,
                'nickname' => null,
            ],
        ], 422);
    }
}

}