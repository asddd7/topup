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

            'payment_id' => 'nullable|integer',

        ]);


        return response()->json(

            $this->promotion->calculate(

                subtotal: (float) $request->price,

                gameId: (int) $request->game_id,

                itemId: (int) $request->item_id,

                paymentId:
                    $request->payment_id
                        ? (int) $request->payment_id
                        : null,

                voucherCode:
                    $request->code,

                user: auth()->user()

            )

        );
    }


    public function paymentPromo(Request $request)
    {
        $request->validate([

            'payment_id' => 'required|integer',

            'game_id' => 'required|integer',

            'item_id' => 'required|integer',

            'subtotal' => 'required|numeric',

            'voucher_code' => 'nullable|string',

        ]);


        return response()->json(

            $this->promotion->calculate(

                subtotal:
                    (float) $request->subtotal,

                gameId:
                    (int) $request->game_id,

                itemId:
                    (int) $request->item_id,

                paymentId:
                    (int) $request->payment_id,

                voucherCode:
                    $request->voucher_code,

                user: auth()->user()

            )

        );
    }
}