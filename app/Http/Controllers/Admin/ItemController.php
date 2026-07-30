<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Game;
use App\Models\ItemCategory;
use Illuminate\Http\Request;


class ItemController extends Controller
{

    public function index(Game $game)
    {
        $items = Item::where('game_id', $game->id)
            ->latest()
            ->get();

        $categories = ItemCategory::orderBy('category_name')->get();

        return view(
            'admin.item.index',
            compact(
                'game',
                'items',
                'categories'
            )
        );
    }

public function store(    Request $request,
    Game $game)
{

    $request->validate([

        'game_id'=>'required',
        'category_id'=>'required',
        'item_name'=>'required',
        'qty'=>'required|integer',
        'price'=>'required',
        'image'=>'nullable|image|max:2048'

    ]);


    $image=null;


    if($request->hasFile('image')){

        $image=$request->file('image')
        ->store('items','public');

    }



Item::create([

    'game_id' => $game->id,

    'category_id' => $request->category_id,

    'item_name' => $request->item_name,

    'qty' => $request->qty,

    'price' => $request->price,

    'stock' => $request->stock,

    'description' => $request->description,

    'top_seller' => $request->has('top_seller'),

    'image' => $image,

    'is_active' => $request->has('is_active')

]);


    return back()
    ->with('success','Item berhasil ditambahkan');

}

public function update(
    Request $request,
    Game $game,
    Item $item
)
{

    $request->validate([

        'category_id'=>'required',
        'item_name'=>'required',
        'qty'=>'required|integer',
        'price'=>'required',
        'image'=>'nullable|image|max:2048'

    ]);


    $image = $item->image;


    if($request->hasFile('image')){

        $image = $request->file('image')
            ->store('items','public');

    }



    $item->update([

        // pastikan item tetap milik game ini
        'game_id'=>$game->id,

        'category_id'=>$request->category_id,

        'item_name'=>$request->item_name,

        'qty'=>$request->qty,

        'price'=>$request->price,

        'stock'=>$request->stock ?? 0,

        'description'=>$request->description,

        'top_seller'=>$request->has('top_seller'),

        'image'=>$image,

        'is_active'=>$request->has('is_active')

    ]);



    return redirect()

        ->route(
            'admin.game.items',
            $game->id
        )

        ->with(
            'success',
            'Item berhasil diperbarui'
        );

}

    public function edit(Game $game, Item $item)
{

    return view(
        'admin.item.edit',
        compact(
            'game',
            'item'
        )
    );

}

public function destroy(
    Game $game,
    Item $item
)
{

    // cek item memang milik game tersebut
    if($item->game_id != $game->id){

        abort(403);

    }


    $item->delete();


    return back()
        ->with(
            'success',
            'Item berhasil dihapus'
        );

}
}