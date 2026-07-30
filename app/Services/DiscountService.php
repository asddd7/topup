<?php

namespace App\Services;


use App\Models\Discount;
use Carbon\Carbon;


class DiscountService
{


    /**
     * Cari discount berdasarkan kode voucher
     */
    public function findVoucher($code)
    {

        return Discount::where('code',$code)

            ->where('trigger_type','voucher')

            ->where('is_active',1)

            ->where(function($q){

                $q->whereNull('start_date')
                ->orWhereDate(
                    'start_date',
                    '<=',
                    Carbon::today()
                );

            })

            ->where(function($q){

                $q->whereNull('end_date')
                ->orWhereDate(
                    'end_date',
                    '>=',
                    Carbon::today()
                );

            })

            ->first();

    }





    /**
     * Validasi apakah discount cocok
     */
    public function validateDiscount(
        Discount $discount,
        $game_id=null,
        $item_id=null,
        $total=0
    )
    {


        /*
        cek minimum pembelian
        */

        if($total < $discount->minimum_purchase){

            return false;

        }



        /*
        cek game
        NULL = semua game
        */

        if(
            $discount->game_id &&
            $discount->game_id != $game_id
        ){

            return false;

        }



        /*
        cek item
        NULL = semua item
        */

        if(
            $discount->item_id &&
            $discount->item_id != $item_id
        ){

            return false;

        }



        return true;


    }







    /**
     * Hitung nilai diskon
     */
    public function calculate(
        Discount $discount,
        $price
    )
    {


        if(
            $discount->discount_type == 'percent'
        ){

            $discountAmount =
                ($price *
                $discount->amount)
                /100;


        }
        else{


            $discountAmount =
                $discount->amount;


        }




        /*
        jangan sampai minus
        */

        if($discountAmount > $price){

            $discountAmount=$price;

        }



        return [

            'discount'=>
                $discountAmount,


            'total'=>
                $price-$discountAmount

        ];


    }






    /**
     * Cek automatic discount
     */
    public function automaticDiscount(
        $game_id=null,
        $item_id=null,
        $total=0
    )
    {


        $discount = Discount::where(
                'trigger_type',
                'automatic'
            )

            ->where('is_active',1)

            ->first();



        if(!$discount){

            return null;

        }



        if(
            $this->validateDiscount(
                $discount,
                $game_id,
                $item_id,
                $total
            )
        ){

            return $discount;

        }


        return null;


    }



}