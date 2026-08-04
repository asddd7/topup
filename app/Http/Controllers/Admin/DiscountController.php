<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;

use App\Models\Discount;
use App\Models\Game;
use App\Models\Item;
use App\Models\Payment;

class DiscountController extends BaseAdminController
{

    public function index()
    {

        $discounts = Discount::with([
            'game',
            'item'
        ])
        ->get();



        $games = Game::where('is_active',1)
            ->get();


        $items = Item::where('is_active',1)
            ->get();

        $payments = Payment::where('is_active',1)
        ->get();

        $payments = Payment::where('is_active',1)
        ->get();


    return view('admin.discount.index',[
        'discounts' => Discount::latest()->get(),
        'games' => Game::all(),
        'items' => Item::all(),
        'payments' => Payment::where('is_active',1)->get(),
    ]);

    }




    public function create()
    {

        $games = Game::where('is_active',1)
            ->orderBy('game_name')
            ->get();


        $items = Item::where('is_active',1)
            ->orderBy('item_name')
            ->get();


        $payments = Payment::all();

        return view(
            'admin.discount.create',
            compact(
                'games',
                'items',
                'payments'
            )
        );

    }





public function store(Request $request)
{
    $request->validate([

        'code' => 'nullable|max:255|unique:discounts,code',

        'discount_name' => 'required|max:255',

        'discount_type' => 'required|in:percent,fixed',

        'amount' => 'required|numeric|min:1',

        'game_id' => 'nullable|exists:games,id',

        'item_id' => 'nullable|exists:items,id',

        'trigger_type' => 'required',

        'minimum_purchase' => 'nullable|numeric|min:0',

        'usage_limit' => 'nullable|integer|min:1',

        'usage_per_user' => 'nullable|integer|min:1',

        'payment_id'=>'nullable|exists:payments,id',

    ]);

    $code = $request->code;

    if (empty($code)) {

        $code = strtoupper($request->trigger_type)
            . '-'
            . strtoupper(\Illuminate\Support\Str::random(6));

    }
    $code = $request->filled('code')
    ? strtoupper($request->code)
    : strtoupper($request->trigger_type).'-'.str()->upper(str()->random(6));

    $discount = Discount::create([

        'code' => $code,

        'game_id' => $request->game_id,

        'item_id' => $request->item_id,

        'discount_name' => $request->discount_name,

        'discount_type' => $request->discount_type,

        'amount' => $request->amount,

        'start_date' => $request->start_date,

        'end_date' => $request->end_date,

        'is_active' => $request->has('is_active'),

        // baru
        'trigger_type' => $request->trigger_type,

        'minimum_purchase' => $request->minimum_purchase ?? 0,

        'usage_limit' => $request->usage_limit,

        'usage_per_user' => $request->usage_per_user ?? 1,

        'quota_used' => 0,

        'payment_id'=>$request->payment_id,

    ]);
$this->activity->log(
    'Discount',
    'Create',
    'Create discount : '.$discount->discount_name,
    $discount,
    null,
    $discount->toArray()
);


    return redirect()
        ->route('admin.discount.index')
        ->with('success','Discount berhasil dibuat');
}






    public function edit(Discount $discount)
    {

        $games = Game::where('is_active',1)
            ->get();


        $items = Item::where('is_active',1)
            ->get();



        return view(
            'admin.discount.edit',
            compact(
                'discount',
                'games',
                'items'
            )
        );

    }






public function update(Request $request, Discount $discount)
{

    $request->validate([

        'code' => 'required_if:trigger_type,voucher|nullable|max:255',

        'discount_name'=>'required',

        'discount_type'=>'required',

        'amount'=>'required|numeric',

        'game_id'=>'nullable',

        'item_id'=>'nullable',

        'trigger_type'=>'required',

        'minimum_purchase'=>'nullable|numeric',

        'usage_limit'=>'nullable|integer',

        'usage_per_user'=>'nullable|integer',

        'payment_id'=>'nullable|exists:payments,id',

    ]);
    $old = $discount->toArray();

    $discount->update([

        'code' => $request->filled('code')
        ? strtoupper($request->code)
        : $discount->code,

        'game_id'=>$request->game_id,

        'item_id'=>$request->item_id,

        'discount_name'=>$request->discount_name,

        'discount_type'=>$request->discount_type,

        'amount'=>$request->amount,

        'start_date'=>$request->start_date,

        'end_date'=>$request->end_date,

        'is_active'=>$request->has('is_active'),

        'trigger_type'=>$request->trigger_type,

        'minimum_purchase'=>$request->minimum_purchase ?? 0,

        'usage_limit'=>$request->usage_limit,

        'usage_per_user'=>$request->usage_per_user ?? 1,

        'payment_id'=>$request->payment_id,

    ]);

$this->activity->log(
    'Discount',
    'Update',
    'Update discount : '.$discount->discount_name,
    $discount,
    $old,
    $discount->fresh()->toArray()
);

    return redirect()
        ->route('admin.discount.index')
        ->with('success','Discount berhasil diupdate');

}




    public function destroy(Discount $discount)
    {
$old = $discount->toArray();
$this->activity->log(
    'Discount',
    'Delete',
    'Delete discount : '.$discount->discount_name,
    $discount,
    $old,
    null
);
        $discount->delete();


        return back()
        ->with(
            'success',
            'Discount berhasil dihapus'
        );

    }


}