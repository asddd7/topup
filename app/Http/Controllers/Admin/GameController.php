<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Integrations\MooGold\MooGoldService;
use App\Models\Game;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends BaseAdminController
{
    /**
     * ============================================================
     * GAME INDEX
     * ============================================================
     */
    public function index()
    {
        $games = Game::with('itemCategories')
            ->latest()
            ->get();

        $categories = ItemCategory::query()
            ->orderBy('category_name')
            ->get();

        $items = Item::query()
            ->whereNotNull('moogold_product_id')
            ->where('moogold_product_id', '>', 0)
            ->orderBy('item_name')
            ->get([
                'id',
                'game_id',
                'item_name',
                'moogold_product_id',
            ])
            ->groupBy('game_id');

        return view(
            'admin.game.index',
            compact(
                'games',
                'categories',
                'items'
            )
        );
    }

    /**
     * ============================================================
     * CREATE GAME PAGE
     * ============================================================
     */
    public function create()
    {
        $categories = ItemCategory::query()
            ->orderBy('category_name')
            ->get();

        return view(
            'admin.game.create',
            compact('categories')
        );
    }

    /**
     * ============================================================
     * STORE GAME
     * ============================================================
     */
    public function store(Request $request)
    {
        $request->validate([
            'game_name' => [
                'required',
                'string',
                'max:255',
            ],

            'publisher' => [
                'nullable',
                'string',
                'max:255',
            ],

            'game_logo' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'moogold_server_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'moogold_server_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields' => [
                'nullable',
                'array',
            ],

            'player_fields.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields.*.label' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields.*.placeholder' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields.*.type' => [
                'nullable',
                'in:text,number,email,select,server',
            ],

            'player_fields.*.source' => [
                'nullable',
                'in:manual,moogold_server_list',
            ],

            'player_fields.*.input_mode' => [
                'nullable',
                'in:input,readonly',
            ],

            'player_fields.*.options' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'player_fields.*.required' => [
                'nullable',
            ],

            'player_fields.*.moogold_field' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_ids' => [
                'nullable',
                'array',
            ],

            'category_ids.*' => [
                'integer',
                'exists:item_categories,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | GAME LOGO
        |--------------------------------------------------------------------------
        */

        $logo = null;

        if ($request->hasFile('game_logo')) {
            $logo = $request
                ->file('game_logo')
                ->store(
                    'games',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | PLAYER FIELDS
        |--------------------------------------------------------------------------
        */

        $playerFields =
            $this->normalizePlayerFields(
                $request->input(
                    'player_fields',
                    []
                )
            );

        /*
        |--------------------------------------------------------------------------
        | CREATE GAME
        |--------------------------------------------------------------------------
        */

        $game = Game::create([
            'game_name' =>
                $request->game_name,

            'publisher' =>
                $request->publisher,

            'game_logo' =>
                $logo,

            'moogold_server_id' =>
                $request->moogold_server_id,

            'moogold_server_name' =>
                $request->moogold_server_name,

            'player_fields' =>
                $playerFields,

            'is_active' =>
                $request->boolean('is_active'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | SYNC CATEGORY
        |--------------------------------------------------------------------------
        */

        $game->itemCategories()->sync(
            $request->input(
                'category_ids',
                []
            )
        );

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $this->activity->log(
            'Game',
            'Create',
            'Create game : ' .
                $game->game_name,
            $game,
            null,
            $game->fresh()->toArray()
        );

        return redirect()
            ->route(
                'admin.game.index'
            )
            ->with(
                'success',
                'Game berhasil ditambahkan'
            );
    }

    /**
     * ============================================================
     * EDIT GAME
     * ============================================================
     */
    public function edit(Game $game)
    {
        $categories = ItemCategory::query()
            ->orderBy('category_name')
            ->get();

        $items = Item::query()
            ->where(
                'game_id',
                $game->id
            )
            ->whereNotNull(
                'moogold_product_id'
            )
            ->where(
                'moogold_product_id',
                '>',
                0
            )
            ->orderBy('item_name')
            ->get([
                'id',
                'item_name',
                'moogold_product_id',
            ]);

        $game->load(
            'itemCategories'
        );

        return view(
            'admin.game.edit',
            compact(
                'game',
                'categories',
                'items'
            )
        );
    }

    /**
     * ============================================================
     * UPDATE GAME
     * ============================================================
     */
    public function update(
        Request $request,
        Game $game
    ) {
        $request->validate([
            'game_name' => [
                'required',
                'string',
                'max:255',
            ],

            'publisher' => [
                'nullable',
                'string',
                'max:255',
            ],

            'game_logo' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'moogold_server_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'moogold_server_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields' => [
                'nullable',
                'array',
            ],

            'player_fields.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields.*.label' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields.*.placeholder' => [
                'nullable',
                'string',
                'max:255',
            ],

            'player_fields.*.type' => [
                'nullable',
                'in:text,number,email,select,server',
            ],

            'player_fields.*.source' => [
                'nullable',
                'in:manual,moogold_server_list',
            ],

            'player_fields.*.input_mode' => [
                'nullable',
                'in:input,readonly',
            ],

            'player_fields.*.options' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'player_fields.*.required' => [
                'nullable',
            ],

            'player_fields.*.moogold_field' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_ids' => [
                'nullable',
                'array',
            ],

            'category_ids.*' => [
                'integer',
                'exists:item_categories,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | OLD DATA
        |--------------------------------------------------------------------------
        */

        $old =
            $game->toArray();

        /*
        |--------------------------------------------------------------------------
        | GAME LOGO
        |--------------------------------------------------------------------------
        */

        $logo =
            $game->game_logo;

        if ($request->hasFile('game_logo')) {
            $logo = $request
                ->file('game_logo')
                ->store(
                    'games',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | PLAYER FIELDS
        |--------------------------------------------------------------------------
        */

        $playerFields =
            $this->normalizePlayerFields(
                $request->input(
                    'player_fields',
                    []
                )
            );

        /*
        |--------------------------------------------------------------------------
        | UPDATE GAME
        |--------------------------------------------------------------------------
        */

        $game->update([
            'game_name' =>
                $request->game_name,

            'publisher' =>
                $request->publisher,

            'game_logo' =>
                $logo,

            'moogold_server_id' =>
                $request->moogold_server_id,

            'moogold_server_name' =>
                $request->moogold_server_name,

            'player_fields' =>
                $playerFields,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | SYNC CATEGORY
        |--------------------------------------------------------------------------
        */

        $game->itemCategories()->sync(
            $request->input(
                'category_ids',
                []
            )
        );

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $this->activity->log(
            'Game',
            'Update',
            'Update game : ' .
                $game->game_name,
            $game,
            $old,
            $game->fresh()->toArray()
        );

        return redirect()
            ->route(
                'admin.game.index'
            )
            ->with(
                'success',
                'Game berhasil diperbarui'
            );
    }

    /**
     * ============================================================
     * MOO GOLD SERVER LIST
     * ============================================================
     */
    public function moogoldServers(
        Game $game,
        Item $item,
        MooGoldService $mooGold
    ) {
        if (
            (int) $item->game_id !==
            (int) $game->id
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Item bukan milik game ini.',
            ], 403);
        }

        if (
            empty(
                $item->moogold_product_id
            ) ||
            (int)
                $item->moogold_product_id <=
                0
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Item belum memiliki MooGold Product ID.',
            ], 422);
        }

        try {

            $response =
                $mooGold->serverList(
                    (int)
                        $item
                            ->moogold_product_id
                );

            $servers = [];

            foreach (
                $response
                as $name => $id
            ) {
                $servers[] = [
                    'id' =>
                        (string) $id,

                    'name' =>
                        (string) $name,
                ];
            }

            return response()->json([
                'success' =>
                    true,

                'product_id' =>
                    (int)
                        $item
                            ->moogold_product_id,

                'servers' =>
                    $servers,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Gagal mengambil MooGold server list.',
                [
                    'game_id' =>
                        $game->id,

                    'item_id' =>
                        $item->id,

                    'moogold_product_id' =>
                        $item
                            ->moogold_product_id,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Gagal mengambil server dari MooGold.',
            ], 500);
        }
    }

    /**
     * ============================================================
     * DELETE GAME
     * ============================================================
     */
    public function destroy(Game $game)
    {
        $old =
            $game->toArray();

        $this->activity->log(
            'Game',
            'Delete',
            'Delete game : ' .
                $game->game_name,
            $game,
            $old,
            null
        );

        $game->delete();

        return back()
            ->with(
                'success',
                'Game berhasil dihapus'
            );
    }

    /**
     * ============================================================
     * NORMALIZE PLAYER FIELDS
     * ============================================================
     *
     * Aturan:
     *
     * User ID
     *     -> selalu input
     *     -> source manual
     *
     * Server / Server ID / Region / Region ID
     *     -> type server
     *
     * Server + readonly
     *     -> source moogold_server_list
     *
     * Server + input
     *     -> source manual
     *
     * Zone / Zone ID
     *     -> bukan server game-level
     * ============================================================
     */
    private function normalizePlayerFields(
        array $fields
    ): array {
        $playerFields = [];

        foreach ($fields as $field) {

            if (
                empty(
                    $field['label']
                )
            ) {
                continue;
            }

            $name =
                trim(
                    (string)
                        ($field['name'] ?? '')
                );

            $label =
                trim(
                    (string)
                        ($field['label'] ?? '')
                );

            $placeholder =
                (string)
                    ($field['placeholder'] ?? '');

            $type =
                strtolower(
                    trim(
                        (string)
                            ($field['type'] ?? 'text')
                    )
                );

            $source =
                $field['source']
                ?? 'manual';

            $inputMode =
                strtolower(
                    trim(
                        (string)
                            ($field['input_mode'] ?? 'input')
                    )
                );

            $options =
                (string)
                    ($field['options'] ?? '');

            $moogoldField =
                trim(
                    (string)
                        ($field['moogold_field'] ?? '')
                );

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE INTERNAL NAME
            |--------------------------------------------------------------------------
            */

            $normalizedName =
                $this->normalizeFieldName(
                    $name
                );

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE MOO GOLD FIELD
            |--------------------------------------------------------------------------
            */

            $normalizedMooGoldField =
                $this->normalizeFieldName(
                    $moogoldField
                );

            /*
            |--------------------------------------------------------------------------
            | USER ID
            |--------------------------------------------------------------------------
            */

            $isUserId =
                in_array(
                    $normalizedName,
                    [
                        'uid',
                        'user_id',
                        'userid',
                        'role_id',
                        'player_id',
                        'account_id',
                        'game_id',
                    ],
                    true
                )
                ||
                in_array(
                    $normalizedMooGoldField,
                    [
                        'uid',
                        'user_id',
                        'userid',
                        'role_id',
                        'player_id',
                        'account_id',
                        'game_id',
                    ],
                    true
                );

            if ($isUserId) {

                $normalizedName =
                    'user_id';

                $type =
                    $type === 'number'
                        ? 'number'
                        : 'text';

                $source =
                    'manual';

                $inputMode =
                    'input';
            }

            /*
            |--------------------------------------------------------------------------
            | SERVER / REGION
            |--------------------------------------------------------------------------
            */

            $isServer =
                in_array(
                    $normalizedMooGoldField,
                    [
                        'server',
                        'server_id',
                        'region',
                        'region_id',
                    ],
                    true
                );

            if ($type === 'server') {
                $isServer = true;
            }

            if ($isServer) {

                $type =
                    'server';

                $normalizedName =
                    $normalizedName !== ''
                        ? $normalizedName
                        : 'server';

                /*
                |--------------------------------------------------------------------------
                | READONLY SERVER
                |--------------------------------------------------------------------------
                */

                if (
                    $inputMode ===
                    'readonly'
                ) {
                    $source =
                        'moogold_server_list';
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | INPUT SERVER
                    |--------------------------------------------------------------------------
                    */

                    $source =
                        'manual';

                    $inputMode =
                        'input';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            if ($normalizedName === '') {
                $normalizedName =
                    'player_field_' .
                    (count($playerFields) + 1);
            }

            if (
                !in_array(
                    $type,
                    [
                        'text',
                        'number',
                        'email',
                        'select',
                        'server',
                    ],
                    true
                )
            ) {
                $type =
                    'text';
            }

            if (
                !in_array(
                    $source,
                    [
                        'manual',
                        'moogold_server_list',
                    ],
                    true
                )
            ) {
                $source =
                    'manual';
            }

            if (
                !in_array(
                    $inputMode,
                    [
                        'input',
                        'readonly',
                    ],
                    true
                )
            ) {
                $inputMode =
                    'input';
            }

            $playerFields[] = [
                'name' =>
                    $normalizedName,

                'label' =>
                    $label,

                'placeholder' =>
                    $placeholder,

                'type' =>
                    $type,

                'source' =>
                    $source,

                'input_mode' =>
                    $inputMode,

                'options' =>
                    $options,

                'required' =>
                    isset(
                        $field['required']
                    ),

                'moogold_field' =>
                    $moogoldField !== ''
                        ? $moogoldField
                        : null,
            ];
        }

        return $playerFields;
    }

    /**
     * ============================================================
     * NORMALIZE FIELD NAME
     * ============================================================
     */
    private function normalizeFieldName(
        string $field
    ): string {
        $normalized =
            strtolower(
                trim(
                    preg_replace(
                        '/[^a-z0-9]+/',
                        '_',
                        $field
                    ),
                    '_'
                )
            );

        return $normalized;
    }
}