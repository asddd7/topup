<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Item;
use App\Models\Game;
use App\Models\ItemCategory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class ItemController extends BaseAdminController
{

public function index(Request $request, Game $game)
{

    $query = Item::with([
        'category',
        'game',
        'bundleItems',
    ])
    ->where('game_id',$game->id);



    /*
    |--------------------------------------------------------------------------
    | SEARCH ITEM
    |--------------------------------------------------------------------------
    */

    if($request->search)
    {

        $query->where(
            'item_name',
            'like',
            '%'.$request->search.'%'
        );

    }




    /*
    |--------------------------------------------------------------------------
    | FILTER CATEGORY
    |--------------------------------------------------------------------------
    */

    if($request->category_id)
    {

        $query->where(
            'category_id',
            $request->category_id
        );

    }




    /*
    |--------------------------------------------------------------------------
    | FILTER STATUS
    |--------------------------------------------------------------------------
    */

    if($request->status !== null && $request->status !== '')
    {

        $query->where(
            'is_active',
            $request->status
        );

    }





    /*
    |--------------------------------------------------------------------------
    | FILTER TOP SELLER
    |--------------------------------------------------------------------------
    */

    if($request->top_seller !== null && $request->top_seller !== '')
    {

        $query->where(
            'top_seller',
            $request->top_seller
        );

    }



    $items = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();




    $categories = ItemCategory::orderBy(
        'category_name'
    )->get();

    $bundleOptions = Item::query()
        ->where('game_id', $game->id)
        ->whereDoesntHave('bundleItems')
        ->orderBy('item_name')
        ->get();



    return view(
        'admin.item.index',
        compact(
            'game',
            'items',
            'categories',
            'bundleOptions'
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
        'image'=>'nullable|image|max:2048',
        'bundle_items'=>'nullable|array',
        'bundle_items.*'=>[
            'integer',
            'distinct',
            Rule::exists('items', 'id')
                ->where('game_id', $game->id),
        ],
        'bundle_quantities'=>'nullable|array',
        'bundle_quantities.*'=>'integer|min:1|max:1000',

    ]);

    $bundleComponents = $this->validatedBundleComponents(
        $request,
        $game
    );


    $image=null;


    if($request->hasFile('image')){

        $image=$request->file('image')
        ->store('items','public');

    }



$item = Item::create([

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

$item->bundleItems()->sync($bundleComponents);

$this->activity->log(
    'Item',
    'Create',
    'Create item : '.$item->item_name,
    $item,
    null,
    $item->toArray()
);

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
        'image'=>'nullable|image|max:2048',
        'bundle_items'=>'nullable|array',
        'bundle_items.*'=>[
            'integer',
            'distinct',
            Rule::exists('items', 'id')
                ->where('game_id', $game->id),
        ],
        'bundle_quantities'=>'nullable|array',
        'bundle_quantities.*'=>'integer|min:1|max:1000',

    ]);

    $bundleComponents = $this->validatedBundleComponents(
        $request,
        $game,
        $item
    );
    $old = $item->toArray();

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

    $item->bundleItems()->sync($bundleComponents);

$this->activity->log(
    'Item',
    'Update',
    'Update item : '.$item->item_name,
    $item,
    $old,
    $item->fresh()->toArray()
);


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

    $bundleOptions = Item::query()
        ->where('game_id', $game->id)
        ->where('id', '!=', $item->id)
        ->whereDoesntHave('bundleItems')
        ->orderBy('item_name')
        ->get();

    $item->load('bundleItems');

    return view(
        'admin.item.edit',
        compact(
            'game',
            'item',
            'bundleOptions'
        )
    );

}

private function validatedBundleComponents(
    Request $request,
    Game $game,
    ?Item $item = null
): array {
    $ids = array_map(
        'intval',
        $request->input('bundle_items', [])
    );

    $validCount = Item::query()
        ->where('game_id', $game->id)
        ->whereIn('id', $ids)
        ->whereDoesntHave('bundleItems')
        ->when($item, fn ($query) => $query->where('id', '!=', $item->id))
        ->count();

    if ($validCount !== count($ids)) {
        throw ValidationException::withMessages([
            'bundle_items' => 'Komponen harus berupa item biasa dari game yang sama.',
        ]);
    }

    $quantities = $request->input('bundle_quantities', []);

    return collect($ids)
        ->mapWithKeys(fn ($id) => [
            $id => [
                'quantity' => max(
                    1,
                    (int) ($quantities[$id] ?? 1)
                ),
            ],
        ])
        ->all();
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

    if ($item->bundles()->exists()) {
        return back()->with(
            'error',
            'Item ini masih digunakan dalam bundle. Hapus dari bundle terlebih dahulu.'
        );
    }

$old = $item->toArray();
$this->activity->log(
    'Item',
    'Delete',
    'Delete item : '.$item->item_name,
    $item,
    $old,
    null
);
    $item->delete();


    return back()
        ->with(
            'success',
            'Item berhasil dihapus'
        );

}
}