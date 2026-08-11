<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Discount;
use App\Models\Game;
use App\Models\Item;
use App\Models\Payment;
use App\Models\DiscountUsage;

class DiscountController extends BaseAdminController
{

    public function index()
    {
        $discounts = Discount::with([
            'game',
            'item',
            'payment'
        ])->latest()->get();

        $games = Game::where('is_active', 1)
            ->orderBy('game_name')
            ->get();

        $items = Item::where('is_active', 1)
            ->orderBy('item_name')
            ->get();

        $payments = Payment::where('is_active', 1)
            ->orderBy('payment_name')
            ->get();

        return view('admin.discount.index', compact(
            'discounts',
            'games',
            'items',
            'payments'
        ));
    }




    public function create()
    {

        $games = Game::where('is_active',1)
            ->orderBy('game_name')
            ->get();


        $items = Item::where('is_active',1)
            ->orderBy('item_name')
            ->get();


        $payments = Payment::where('is_active', 1)
            ->orderBy('payment_name')
            ->get();

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

        'code' => [
            'nullable',
            'required_if:trigger_type,voucher',
            'max:255',
            'unique:discounts,code',
        ],

        'discount_name' => [
            'required',
            'max:255',
        ],

        'discount_type' => [
            'required',
            'in:percent,fixed'
        ],

        'amount' => [
            'required',
            'numeric',
            'min:1'
        ],

        'game_id' => [
            'nullable',
            'exists:games,id',
        ],

        'item_id' => [
            'nullable',
            'exists:items,id',
        ],

        'trigger_type' => [
            'required',
            'in:voucher,automatic,new_user,flash_sale,payment_method',
        ],

        'minimum_purchase' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'usage_limit' => [
            'nullable',
            'integer',
            'min:1',
        ],

        /*
        |--------------------------------------------------------------------------
        | 0 = unlimited
        |--------------------------------------------------------------------------
        */

        'usage_per_user' => [
            'nullable',
            'integer',
            'min:0',
        ],

        'payment_id' => [
            'nullable',
            'exists:payments,id',
        ],

        'start_date' => [
            'nullable',
            'date',
        ],

        'end_date' => [
            'nullable',
            'date',
            'after_or_equal:start_date',
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | Voucher Code
    |--------------------------------------------------------------------------
    */

    $code = null;

    if ($request->trigger_type === 'voucher') {

        $code = strtoupper(
            trim((string) $request->input('code'))
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Usage Per User
    |--------------------------------------------------------------------------
    |
    | 0 = unlimited
    |
    */

    $usagePerUser = $request->has('usage_per_user')
        ? (int) $request->input('usage_per_user')
        : 0;


    /*
    |--------------------------------------------------------------------------
    | Create Discount
    |--------------------------------------------------------------------------
    */

    $discount = Discount::create([

        'code' => $code,

        'game_id' =>
            $request->input('game_id'),

        'item_id' =>
            $request->input('item_id'),

        'discount_name' =>
            $request->input('discount_name'),

        'discount_type' =>
            $request->input('discount_type'),

        'amount' =>
            $request->input('amount'),

        'start_date' =>
            $request->input('start_date'),

        'end_date' =>
            $request->input('end_date'),

        'is_active' =>
            $request->boolean('is_active'),

        'trigger_type' =>
            $request->input('trigger_type'),

        'minimum_purchase' =>
            $request->input('minimum_purchase', 0),

        'usage_limit' =>
            $request->input('usage_limit'),

        'usage_per_user' =>
            $usagePerUser,

        'quota_used' =>
            0,

        'payment_id' =>
            $request->input('payment_id'),

    ]);


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    $this->activity->log(
        'Discount',
        'Create',
        'Create discount : ' . $discount->discount_name,
        $discount,
        null,
        $discount->toArray()
    );


    return redirect()
        ->route('admin.discount.index')
        ->with(
            'success',
            'Discount berhasil dibuat'
        );
}

    public function edit(Discount $discount)
    {

        $games = Game::where('is_active',1)
            ->get();


        $items = Item::where('is_active',1)
            ->get();



        $payments = Payment::where('is_active', 1)
            ->orderBy('payment_name')
            ->get();

        return view(
            'admin.discount.edit',
            compact(
                'discount',
                'games',
                'items',
                'payments'
            )
        );

    }   

public function update(
    Request $request,
    Discount $discount
) {
    $request->validate([

        'code' => [
            'nullable',
            'required_if:trigger_type,voucher',
            'max:255',
            Rule::unique('discounts', 'code')
                ->ignore($discount->id),
        ],

        'discount_name' => [
            'required',
            'max:255',
        ],

        'discount_type' => [
            'required',
            'in:percent,fixed',
        ],

        'amount' => [
            'required',
            'numeric',
            'min:1',
        ],

        'game_id' => [
            'nullable',
            'exists:games,id',
        ],

        'item_id' => [
            'nullable',
            'exists:items,id',
        ],

        'payment_id' => [
            'nullable',
            'exists:payments,id',
        ],

        'trigger_type' => [
            'required',
            'in:voucher,automatic,new_user,flash_sale,payment_method',
        ],

        'minimum_purchase' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'usage_limit' => [
            'nullable',
            'integer',
            'min:1',
        ],

        'usage_per_user' => [
            'nullable',
            'integer',
            'min:0',
        ],

        'start_date' => [
            'nullable',
            'date',
        ],

        'end_date' => [
            'nullable',
            'date',
            'after_or_equal:start_date',
        ],

    ]);


    /*
    |--------------------------------------------------------------------------
    | Old Data
    |--------------------------------------------------------------------------
    */

    $old = $discount->toArray();


    /*
    |--------------------------------------------------------------------------
    | Voucher Code
    |--------------------------------------------------------------------------
    */

    $code = null;

    if ($request->trigger_type === 'voucher') {

        $code = strtoupper(
            trim($request->code)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Usage Per User
    |--------------------------------------------------------------------------
    |
    | 0 = unlimited
    |
    |--------------------------------------------------------------------------
    */

    $usagePerUser = $request->has('usage_per_user')
        ? (int) $request->input('usage_per_user')
        : 0;


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $discount->update([

        'code' =>
            $code,

        'game_id' =>
            $request->input('game_id'),

        'item_id' =>
            $request->input('item_id'),

        'discount_name' =>
            $request->input('discount_name'),

        'discount_type' =>
            $request->input('discount_type'),

        'amount' =>
            $request->input('amount'),

        'start_date' =>
            $request->input('start_date'),

        'end_date' =>
            $request->input('end_date'),

        'is_active' =>
            $request->boolean('is_active'),

        'trigger_type' =>
            $request->input('trigger_type'),

        'minimum_purchase' =>
            $request->input('minimum_purchase', 0),

        'usage_limit' =>
            $request->input('usage_limit'),

        'usage_per_user' =>
            $usagePerUser,

        'payment_id' =>
            $request->input('payment_id'),

    ]);


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    $this->activity->log(
        'Discount',
        'Update',
        'Update discount : ' . $discount->discount_name,
        $discount,
        $old,
        $discount->fresh()->toArray()
    );


    return redirect()
        ->route('admin.discount.index')
        ->with(
            'success',
            'Discount berhasil diupdate'
        );
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