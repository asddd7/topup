<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Item;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends BaseAdminController
{

    public function index()
    {
        $games = Game::latest()->get();

        return view('admin.game.index', compact('games'));
    }


    public function create()
    {
        return view('admin.game.create');
    }


    public function store(Request $request)
    {

        $request->validate([

            'game_name'=>'required',
            'publisher'=>'nullable',
            'game_logo'=>'nullable|image|max:2048',
            'player_fields.*.type'=>'nullable|in:text,number,email'

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

                    'name' => $field['name'] ?? '',

                    'label' => $field['label'] ?? '',

                    'placeholder' => $field['placeholder'] ?? '',

                    'type' => $field['type'] ?? 'text',

                    'required' => isset($field['required'])

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
        return view('admin.game.edit',compact('game'));
    }



    public function update(Request $request, Game $game)
    {

        $request->validate([

            'game_name'=>'required',

            'publisher'=>'nullable',

            'player_input_type'=>'nullable',

            'game_logo'=>'nullable|image|max:2048',

            'player_fields.*.type'=>'nullable|in:text,number,email'

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

                'name'=>$field['name'] ?? '',

                'label'=>$field['label'] ?? '',

                'placeholder'=>$field['placeholder'] ?? '',

                'type'=>$field['type'] ?? 'text',

                'required'=>isset($field['required'])

            ];

        }

        $game->update([

            'game_name'=>$request->game_name,

            'publisher'=>$request->publisher,

            'player_fields'=>$playerFields,

            'game_logo'=>$logo,

            'is_active'=>$request->has('is_active')

        ]);

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