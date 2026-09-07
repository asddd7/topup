<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PromotionService;

class VoucherController extends Controller
{
    public function __construct(
        protected PromotionService $promotion
    ) {
    }


    public function check(Request $request)
    {
        $request->validate([

            'code' => 'required',

            'game_id' => 'required|integer',

            'item_id' => 'required|integer',

            'price' => 'required|numeric',

            'midtrans_payment_type' =>
                'nullable|string|max:100',

        ]);


        return response()->json(

            $this->promotion->calculate(

                subtotal: (float) $request->price,

                gameId: (int) $request->game_id,

                itemId: (int) $request->item_id,

                paymentType:
                    $request->midtrans_payment_type
                        ?: null,

                voucherCode:
                    $request->code,

                user: auth()->user()

            )

        );
    }


    public function paymentPromo(Request $request)
    {
        $request->validate([

            'midtrans_payment_type' =>
                'required|string|max:100',

            'game_id' =>
                'required|integer',

            'item_id' =>
                'required|integer',

            'subtotal' =>
                'required|numeric',

            'voucher_code' =>
                'nullable|string',

        ]);


        return response()->json(

            $this->promotion->calculate(

                subtotal:
                    (float) $request->subtotal,

                gameId:
                    (int) $request->game_id,

                itemId:
                    (int) $request->item_id,

                paymentType:
                    $request->midtrans_payment_type,

                voucherCode:
                    $request->voucher_code,

                user:
                    auth()->user()

            )

        );
    }
}