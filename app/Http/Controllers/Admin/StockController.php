<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Game;
use App\Models\User;
use App\Models\Item;
use App\Models\Notification;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

class StockController extends BaseAdminController
{

    public function index(Request $request)
    {

        $items = Item::with([
                'game',
                'category'
            ])

            ->when($request->keyword,function($q) use($request){

                $q->where('item_name','like','%'.$request->keyword.'%');

            })

            ->when($request->game,function($q) use($request){

                $q->where('game_id',$request->game);

            })

            ->when($request->category,function($q) use($request){

                $q->where('category_id',$request->category);

            })

            ->orderBy('item_name')

            ->paginate(20)

            ->withQueryString();


        return view(
            'admin.stock.index',
            [

                'items'=>$items,

                'games'=>Game::orderBy('game_name')->get(),

                'categories'=>ItemCategory::orderBy('category_name')->get()

            ]
        );

    }


    public function update(Request $request, Item $item)
    {

        $request->validate([

            'stock'=>'required|integer|min:1'

        ]);

        $old = $item->stock;

$item->increment('stock', $request->stock);

$item->refresh();

if ($item->stock >= 10) {

    Notification::where('item_id', $item->id)
        ->where('title', 'Stock Rendah')
        ->delete();

} else {

    foreach (User::where('role_id',1)->get() as $admin) {

        Notification::updateOrCreate(
            [
                'user_id'=>$admin->id,
                'item_id'=>$item->id,
                'title'=>'Stock Rendah'
            ],
            [
                'message'=>$item->item_name.' tersisa '.$item->stock,
                'is_read'=>0
            ]
        );
    }

}

$this->activity->log(

            'Item',

            'Stock Update',

            'Menambah stock '.$item->item_name,

            $item,

            [

                'stock'=>$old

            ],

            [

                'stock'=>$item->fresh()->stock

            ]

        );

        return back()->with(

            'success',

            'Stock berhasil ditambahkan.'

        );

    }

}