<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
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

        return view(
            'admin.game.index',
            compact(
                'games',
                'categories'
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

            'game_name'=>'required',
            'publisher'=>'nullable',
            'game_logo'=>'nullable|image|max:2048',
            'player_fields.*.type' => 'nullable|in:text,number,email,select',
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

                    'name'        => $field['name'] ?? '',

                    'label'       => $field['label'] ?? '',

                    'placeholder' => $field['placeholder'] ?? '',

                    'type'        => $field['type'] ?? 'text',

                    'options'     => $field['options'] ?? '',

                    'required'    => isset($field['required'])

                ];

            }

        }

        $game = Game::create([

            'game_name'=>$request->game_name,

            'publisher'=>$request->publisher,

            'player_input_type'=>$request->player_input_type,

            'game_logo'=>$logo,

            'player_fields'=>$playerFields,

            'is_active'=>true

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

        $game->load('itemCategories');

        return view(
            'admin.game.edit',
            compact('game', 'categories')
        );
    }



    public function update(Request $request, Game $game)
    {

        $request->validate([

            'game_name'=>'required',

            'publisher'=>'nullable',

            'player_input_type'=>'nullable',

            'game_logo'=>'nullable|image|max:2048',

            'player_fields.*.type' => 'nullable|in:text,number,email,select',

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

            $playerFields[]=[

                'name'        => $field['name'] ?? '',

                'label'       => $field['label'] ?? '',

                'placeholder' => $field['placeholder'] ?? '',

                'type'        => $field['type'] ?? 'text',

                'options'     => $field['options'] ?? '',

                'required'    => isset($field['required'])

            ];

        }

        $game->update([

            'game_name'=>$request->game_name,

            'publisher'=>$request->publisher,

            'player_fields'=>$playerFields,

            'game_logo'=>$logo,

            'is_active'=>$request->has('is_active')

        ]);

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