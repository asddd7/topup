<?php

namespace App\Services;

use App\Models\Discount;
use Carbon\Carbon;

class PromotionService
{
    /**
     * Hitung promo berdasarkan kondisi order.
     */
    public function calculate(
        float $subtotal,
        int $gameId,
        int $itemId,
        ?int $paymentId = null,
        ?string $voucherCode = null,
        $user = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Validasi subtotal
        |--------------------------------------------------------------------------
        */

        if ($subtotal <= 0) {

            return [
                'status' => false,
                'discount' => 0,
                'discount_id' => null,
                'total' => $subtotal,
                'message' => 'Subtotal tidak valid.'
            ];

        }


        /*
        |--------------------------------------------------------------------------
        | Cari promo
        |--------------------------------------------------------------------------
        */

        $discount = $this->findDiscount(
            $subtotal,
            $gameId,
            $itemId,
            $paymentId,
            $voucherCode,
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Promo tidak ditemukan
        |--------------------------------------------------------------------------
        */

        if (!$discount) {

            return [
                'status' => false,
                'discount' => 0,
                'discount_id' => null,
                'total' => $subtotal,
                'message' => 'Promo tidak ditemukan atau tidak memenuhi syarat.'
            ];

        }


        /*
        |--------------------------------------------------------------------------
        | Minimum pembelian
        |--------------------------------------------------------------------------
        */

        if (
            $discount->minimum_purchase !== null &&
            $subtotal < $discount->minimum_purchase
        ) {

            return [
                'status' => false,
                'discount' => 0,
                'discount_id' => null,
                'total' => $subtotal,
                'message' =>
                    'Minimum pembelian Rp ' .
                    number_format($discount->minimum_purchase)
            ];

        }


        /*
        |--------------------------------------------------------------------------
        | Hitung discount
        |--------------------------------------------------------------------------
        */

        if ($discount->discount_type === 'percent') {

            $discountValue =
                $subtotal * ($discount->amount / 100);

        } else {

            $discountValue =
                $discount->amount;

        }


        /*
        |--------------------------------------------------------------------------
        | Discount tidak boleh melebihi subtotal
        |--------------------------------------------------------------------------
        */

        $discountValue =
            min($discountValue, $subtotal);


        $total =
            $subtotal - $discountValue;


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            'status' => true,

            'discount_id' => $discount->id,

            'discount' => $discountValue,

            'total' => $total,

            'message' => $discount->discount_name,

            'discount_name' => $discount->discount_name,

            'discount_type' => $discount->discount_type,

            'amount' => $discount->amount,

            'trigger_type' => $discount->trigger_type

        ];

    }


    /**
     * Cari promo yang sesuai.
     */
    protected function findDiscount(
        float $subtotal,
        int $gameId,
        int $itemId,
        ?int $paymentId,
        ?string $voucherCode,
        $user
    ) {

        $today = Carbon::today();


        /*
        |--------------------------------------------------------------------------
        | Query dasar promo
        |--------------------------------------------------------------------------
        */

        $query = Discount::query()

            ->where('is_active', 1)

            /*
            |--------------------------------------------------------------------------
            | Game
            |--------------------------------------------------------------------------
            */

            ->where(function ($q) use ($gameId) {

                $q->whereNull('game_id')
                  ->orWhere('game_id', $gameId);

            })

            /*
            |--------------------------------------------------------------------------
            | Item
            |--------------------------------------------------------------------------
            */

            ->where(function ($q) use ($itemId) {

                $q->whereNull('item_id')
                  ->orWhere('item_id', $itemId);

            })

            /*
            |--------------------------------------------------------------------------
            | Tanggal mulai
            |--------------------------------------------------------------------------
            */

            ->where(function ($q) use ($today) {

                $q->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', $today);

            })

            /*
            |--------------------------------------------------------------------------
            | Tanggal berakhir
            |--------------------------------------------------------------------------
            */

            ->where(function ($q) use ($today) {

                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $today);

            })

            /*
            |--------------------------------------------------------------------------
            | Kuota
            |--------------------------------------------------------------------------
            */

            ->where(function ($q) {

                $q->whereNull('usage_limit')
                  ->orWhereColumn(
                      'quota_used',
                      '<',
                      'usage_limit'
                  );

            });


        /*
        |--------------------------------------------------------------------------
        | VOUCHER
        |--------------------------------------------------------------------------
        */

        if ($voucherCode) {

            $voucherCode =
                strtoupper(trim($voucherCode));


            return $query

                ->where('trigger_type', 'voucher')

                ->whereRaw(
                    'UPPER(code) = ?',
                    [$voucherCode]
                )

                ->first();

        }


        /*
        |--------------------------------------------------------------------------
        | PROMO PAYMENT METHOD
        |--------------------------------------------------------------------------
        */

        if ($paymentId) {

            return $query

                ->where('trigger_type', 'payment_method')

                ->where(function ($q) use ($paymentId) {

                    $q->whereNull('payment_id')
                      ->orWhere('payment_id', $paymentId);

                })

                ->orderByDesc('amount')

                ->first();

        }


        /*
        |--------------------------------------------------------------------------
        | PROMO AUTOMATIC
        |--------------------------------------------------------------------------
        */

        return $query

            ->where('trigger_type', 'automatic')

            ->orderByDesc('amount')

            ->first();

    }
}