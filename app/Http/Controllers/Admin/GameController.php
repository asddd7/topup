<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Integrations\MooGold\MooGoldService;
use App\Models\Item;
use App\Models\Game;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

class GameController extends BaseAdminController
{

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


    public function store(Request $request)
    {

        $request->validate([

            'game_name' => 'required',
            'publisher' => 'nullable',
            'game_logo' => 'nullable|image|max:2048',

            'moogold_server_id' => 'nullable|string|max:255',
            'moogold_server_name' => 'nullable|string|max:255',

            'player_fields.*.type' => 'nullable|in:text,number,email,select,server',
            'player_fields.*.source' => 'nullable|in:manual,moogold_server_list',

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:item_categories,id'],

        ]);


        $logo = null;


        if($request->hasFile('game_logo')){

            $logo = $request->file('game_logo')
                    ->store('games','public');

        }


        $playerFields = [];

        if($request->filled('player_fields')){

            foreach($request->player_fields as $field){

                if(empty($field['label'])){
                    continue;
                }

                $playerFields[] = [

                    'name'          => $field['name'] ?? '',

                    'label'         => $field['label'] ?? '',

                    'placeholder'   => $field['placeholder'] ?? '',

                    'type'          => $field['type'] ?? 'text',

                    'source'        => $field['source'] ?? 'manual',

                    'options'       => $field['options'] ?? '',

                    'required'      => isset($field['required']),

                    'moogold_field' => $field['moogold_field'] ?? null,

                ];

            }

        }

        $game = Game::create([

            'game_name'   => $request->game_name,

            'publisher'   => $request->publisher,

            'game_logo'   => $logo,

            'moogold_server_id'   => $request->moogold_server_id,

            'moogold_server_name' => $request->moogold_server_name,

            'player_fields' => $playerFields,

            'is_active'   => true,

        ]);

        $game->itemCategories()->sync(
            $request->input('category_ids', [])
        );

        $this->activity->log(
            'Game',
            'Create',
            'Create game : '.$game->game_name,
            $game,
            null,
            $game->toArray()
        );

        return redirect()
        ->route('admin.game.index')
        ->with('success','Game berhasil ditambahkan');

    }



    public function edit(Game $game)
    {
        $categories = ItemCategory::query()
            ->orderBy('category_name')
            ->get();

        $items = Item::query()
            ->where('game_id', $game->id)
            ->whereNotNull('moogold_product_id')
            ->where('moogold_product_id', '>', 0)
            ->orderBy('item_name')
            ->get([
                'id',
                'item_name',
                'moogold_product_id',
            ]);

        $game->load('itemCategories');

        return view(
            'admin.game.edit',
            compact(
                'game',
                'categories',
                'items'
            )
        );
    }



    public function update(Request $request, Game $game)
    {

        $request->validate([

            'game_name' => 'required',

            'publisher' => 'nullable',

            'game_logo' => 'nullable|image|max:2048',

            'moogold_server_id' => 'nullable|string|max:255',

            'moogold_server_name' => 'nullable|string|max:255',

            'player_fields.*.type' => 'nullable|in:text,number,email,select,server',
            'player_fields.*.source' => 'nullable|in:manual,moogold_server_list',

            'category_ids' => ['nullable', 'array'],

            'category_ids.*' => ['integer', 'exists:item_categories,id'],

        ]);

        $old = $game->toArray();

        $logo = $game->game_logo;


        if($request->hasFile('game_logo')){

            $logo = $request->file('game_logo')
                ->store('games','public');

        }

        $playerFields=[];

        foreach($request->player_fields ?? [] as $field){

            if(empty($field['label'])){

                continue;

            }

                $playerFields[] = [

                    'name'          => $field['name'] ?? '',

                    'label'         => $field['label'] ?? '',

                    'placeholder'   => $field['placeholder'] ?? '',

                    'type'          => $field['type'] ?? 'text',

                    'source'        => $field['source'] ?? 'manual',

                    'options'       => $field['options'] ?? '',

                    'required'      => isset($field['required']),

                    'moogold_field' => $field['moogold_field'] ?? null,

                ];

        }

        $playerFields[] = [

            'name'          => $field['name'] ?? '',

            'label'         => $field['label'] ?? '',

            'placeholder'   => $field['placeholder'] ?? '',

            'type'          => $field['type'] ?? 'text',

            'source'        => $field['source'] ?? 'manual',

            'options'       => $field['options'] ?? '',

            'required'      => isset($field['required']),

            'moogold_field' => $field['moogold_field'] ?? null,

        ];

        $game->itemCategories()->sync(
            $request->input('category_ids', [])
        );

        $this->activity->log(
            'Game',
            'Update',
            'Update game : '.$game->game_name,
            $game,
            $old,
            $game->fresh()->toArray()
        );


        return redirect()
            ->route('admin.game.index')
            ->with('success','Game berhasil diperbarui');

    }

    public function moogoldServers(
        Game $game,
        Item $item,
        MooGoldService $mooGold
    ) {
        if ((int) $item->game_id !== (int) $game->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item bukan milik game ini.',
            ], 403);
        }

        if (
            empty($item->moogold_product_id) ||
            (int) $item->moogold_product_id <= 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Item belum memiliki MooGold Product ID.',
            ], 422);
        }

        try {

            $response = $mooGold->serverList(
                (int) $item->moogold_product_id
            );

            $servers = [];

            foreach ($response as $name => $id) {

                $servers[] = [
                    'id' => (string) $id,
                    'name' => (string) $name,
                ];

            }

            return response()->json([
                'success' => true,
                'product_id' => (int) $item->moogold_product_id,
                'servers' => $servers,
            ]);

        } catch (\Throwable $e) {

            \Log::error(
                'Gagal mengambil MooGold server list.',
                [
                    'game_id' => $game->id,
                    'item_id' => $item->id,
                    'moogold_product_id' => $item->moogold_product_id,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil server dari MooGold.',
            ], 500);
        }
    }    


    public function destroy(Game $game)
    {
        $old = $game->toArray();
        $this->activity->log(
            'Game',
            'Delete',
            'Delete game : '.$game->game_name,
            $game,
            $old,
            null
        );
        $game->delete();


        return back()
        ->with('success','Game berhasil dihapus');

    }

}