<?php

namespace App\Services;

use App\Models\Discount;
use Carbon\Carbon;

class PromotionService
{
    public function calculate(
        float $subtotal,
        int $gameId,
        int $itemId,
        ?int $paymentId = null,
        ?string $voucherCode = null,
        $user = null
    ): array {

        $discount = $this->findDiscount(
            $subtotal,
            $gameId,
            $itemId,
            $paymentId,
            $voucherCode,
            $user
        );

        if (!$discount) {

            return [

                'status' => false,

                'discount' => 0,

                'discount_id' => null,

                'total' => $subtotal,

                'message' => 'Promo tidak ditemukan'

            ];

        }

        if ($discount->discount_type == 'percent') {

            $discountValue =
                $subtotal * ($discount->amount / 100);

        } else {

            $discountValue = $discount->amount;

        }

        $discountValue = min($discountValue, $subtotal);

        return [

            'status' => true,

            'discount_id' => $discount->id,

            'discount' => $discountValue,

            'total' => $subtotal - $discountValue,

            'message' => $discount->discount_name

        ];
    }

    protected function findDiscount(
        float $subtotal,
        int $gameId,
        int $itemId,
        ?int $paymentId,
        ?string $voucherCode,
        $user
    ) {

        $today = Carbon::today();

        $query = Discount::query()

            ->where('is_active',1)

            ->where(function($q) use ($gameId){

                $q->whereNull('game_id')

                  ->orWhere('game_id',$gameId);

            })

            ->where(function($q) use ($itemId){

                $q->whereNull('item_id')

                  ->orWhere('item_id',$itemId);

            })

            ->where(function($q){

                $q->whereNull('start_date')

                  ->orWhere('start_date','<=',today());

            })

            ->where(function($q){

                $q->whereNull('end_date')

                  ->orWhere('end_date','>=',today());

            });

        /**
         * Voucher
         */

        if($voucherCode){

            return $query

                ->where('trigger_type','voucher')

                ->where('code',$voucherCode)

                ->first();

        }

        /**
         * Payment
         */

        if($paymentId){

            return $query

                ->where('trigger_type','payment_method')

                ->where('payment_id',$paymentId)

                ->first();

        }

        /**
         * Automatic
         */

        return $query

            ->where('trigger_type','automatic')

            ->first();

    }
}