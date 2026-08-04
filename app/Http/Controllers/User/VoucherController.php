<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PromotionService;


class VoucherController extends Controller
{

    public function __construct(
        protected PromotionService $promotion
    )
    {
    }



    public function check(Request $request)
    {

        $request->validate([

            'code'=>'required',

            'game_id'=>'required',

            'item_id'=>'required',

            'price'=>'required'

        ]);


        return response()->json(

            $this->promotion->calculate(

                subtotal:$request->price,

                gameId:$request->game_id,

                itemId:$request->item_id,

                voucherCode:$request->code

            )

        );

    }



    // ============================
    // PROMO METODE PEMBAYARAN
    // ============================

    public function paymentPromo(Request $request)
    {

        $request->validate([

            'payment_id'=>'required',

            'game_id'=>'required',

            'item_id'=>'required',

            'subtotal'=>'required'

        ]);


        return response()->json(

            $this->promotion->calculate(

                subtotal:$request->subtotal,

                gameId:$request->game_id,

                itemId:$request->item_id,

                paymentId:$request->payment_id

            )

        );

    }


}