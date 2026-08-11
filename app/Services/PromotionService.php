<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PromotionService
{
    /**
     * ============================================================
     * CALCULATE PROMOTION
     * ============================================================
     *
     * Urutan promo:
     *
     * 1. Voucher
     * 2. Payment Method
     * 3. Automatic
     * 4. Flash Sale
     * 5. New User
     *
     * PromotionService hanya menghitung promo.
     *
     * QUOTA TIDAK DIINCREMENT DI SINI.
     *
     * Quota akan diproses ketika order berhasil dibuat.
     */
    public function calculate(
        float $subtotal,
        int $gameId,
        int $itemId,
        ?int $paymentId = null,
        ?string $voucherCode = null,
        $user = null,
        bool $lockForUpdate = false
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Validasi subtotal
        |--------------------------------------------------------------------------
        */

        if ($subtotal <= 0) {

            return $this->invalidResponse(
                'Subtotal tidak valid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil promo yang eligible
        |--------------------------------------------------------------------------
        */

        $discounts = $this->findDiscounts(

            subtotal: $subtotal,

            gameId: $gameId,

            itemId: $itemId,

            paymentId: $paymentId,

            voucherCode: $voucherCode,

            user: $user,

            lockForUpdate: $lockForUpdate

        );


        /*
        |--------------------------------------------------------------------------
        | Hitung stacking
        |--------------------------------------------------------------------------
        */

        $remaining = $subtotal;

        $totalDiscount = 0;

        $applied = [];


        foreach ($discounts as $discount) {

            /*
            |--------------------------------------------------------------------------
            | Total sudah habis
            |--------------------------------------------------------------------------
            */

            if ($remaining <= 0) {
                break;
            }


            /*
            |--------------------------------------------------------------------------
            | Minimum purchase
            |--------------------------------------------------------------------------
            */

            if (
                !$this->meetsMinimumPurchase(
                    $discount,
                    $subtotal
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Global quota
            |--------------------------------------------------------------------------
            */

            if (
                !$this->hasAvailableQuota(
                    $discount
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | User quota
            |--------------------------------------------------------------------------
            */

            if (
                !$this->hasAvailableUserQuota(
                    $discount,
                    $user
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | New user
            |--------------------------------------------------------------------------
            */

            if (
                $discount->trigger_type === 'new_user'
                &&
                !$this->isNewUser($user)
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Hitung nominal discount
            |--------------------------------------------------------------------------
            */

            $discountAmount =
                $this->calculateDiscountAmount(
                    discount: $discount,
                    remaining: $remaining
                );


            /*
            |--------------------------------------------------------------------------
            | Discount tidak valid
            |--------------------------------------------------------------------------
            */

            if ($discountAmount <= 0) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Apply discount
            |--------------------------------------------------------------------------
            */

            $remaining =
                max(
                    $remaining - $discountAmount,
                    0
                );


            $totalDiscount +=
                $discountAmount;


            /*
            |--------------------------------------------------------------------------
            | Simpan promo yang digunakan
            |--------------------------------------------------------------------------
            */

            $applied[] =
                $this->formatAppliedDiscount(
                    discount: $discount,
                    discountAmount: $discountAmount
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return $this->buildResponse(

            applied: $applied,

            totalDiscount: $totalDiscount,

            remaining: $remaining

        );
    }


    /**
     * ============================================================
     * INVALID RESPONSE
     * ============================================================
     */

    protected function invalidResponse(
        string $message
    ): array {

        return [

            'status' => false,

            'discounts' => [],

            'discount_total' => 0,

            'total' => 0,

            'message' => $message,

        ];
    }


    /**
     * ============================================================
     * MINIMUM PURCHASE
     * ============================================================
     */

    protected function meetsMinimumPurchase(
        Discount $discount,
        float $subtotal
    ): bool {

        $minimum =
            (float) (
                $discount->minimum_purchase ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | 0 = tanpa minimum
        |--------------------------------------------------------------------------
        */

        if ($minimum <= 0) {
            return true;
        }


        return $subtotal >= $minimum;
    }


    /**
     * ============================================================
     * GLOBAL QUOTA
     * ============================================================
     */

    protected function hasAvailableQuota(
        Discount $discount
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | NULL = unlimited
        |--------------------------------------------------------------------------
        */

        if (
            $discount->usage_limit === null
        ) {

            return true;
        }


        return
            (int) $discount->quota_used
            <
            (int) $discount->usage_limit;
    }


    /**
     * ============================================================
     * USER QUOTA
     * ============================================================
     */

    protected function hasAvailableUserQuota(
        Discount $discount,
        $user
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | 0 = unlimited
        |--------------------------------------------------------------------------
        */

        $limit =
            (int) (
                $discount->usage_per_user ?? 0
            );


        if ($limit <= 0) {
            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Guest
        |--------------------------------------------------------------------------
        |
        | Guest tidak mempunyai user_id.
        |
        */

        if (
            !$user ||
            !$user->id
        ) {

            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Hitung penggunaan promo user
        |--------------------------------------------------------------------------
        |
        | Order Cancelled tidak dihitung.
        |
        */

        $usageCount =
            Order::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->whereNotIn(
                    'status',
                    [
                        'Cancelled'
                    ]
                )

                ->whereHas(
                    'orderDiscounts',
                    function ($query) use ($discount) {

                        $query->where(
                            'discount_id',
                            $discount->id
                        );

                    }
                )

                ->count();


        return $usageCount < $limit;
    }


    /**
     * ============================================================
     * NEW USER
     * ============================================================
     */

    protected function isNewUser(
        $user
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Guest bukan new user
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            !$user->id
        ) {

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | User dianggap new user jika
        | belum mempunyai order non-cancelled
        |--------------------------------------------------------------------------
        */

        return !Order::query()

            ->where(
                'user_id',
                $user->id
            )

            ->whereNotIn(
                'status',
                [
                    'Cancelled'
                ]
            )

            ->exists();
    }


    /**
     * ============================================================
     * CALCULATE DISCOUNT AMOUNT
     * ============================================================
     */

    protected function calculateDiscountAmount(
        Discount $discount,
        float $remaining
    ): float {

        /*
        |--------------------------------------------------------------------------
        | Percent
        |--------------------------------------------------------------------------
        */

        if (
            $discount->discount_type === 'percent'
        ) {

            $percentage =
                min(
                    max(
                        (float) $discount->amount,
                        0
                    ),
                    100
                );


            $amount =
                $remaining *
                (
                    $percentage / 100
                );

        }

        /*
        |--------------------------------------------------------------------------
        | Fixed
        |--------------------------------------------------------------------------
        */

        else {

            $amount =
                max(
                    (float) $discount->amount,
                    0
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Jangan lebih besar dari remaining
        |--------------------------------------------------------------------------
        */

        return min(
            $amount,
            $remaining
        );
    }


    /**
     * ============================================================
     * FORMAT APPLIED DISCOUNT
     * ============================================================
     */

    protected function formatAppliedDiscount(
        Discount $discount,
        float $discountAmount
    ): array {

        return [

            'id' =>
                (int) $discount->id,

            'name' =>
                $discount->discount_name,

            'code' =>
                $discount->code,

            'trigger_type' =>
                $discount->trigger_type,

            'discount_type' =>
                $discount->discount_type,

            'amount' =>
                (float) $discount->amount,

            'discount' =>
                round(
                    $discountAmount,
                    2
                ),

        ];
    }


    /**
     * ============================================================
     * BUILD RESPONSE
     * ============================================================
     */

    protected function buildResponse(
        array $applied,
        float $totalDiscount,
        float $remaining
    ): array {

        $hasPromo =
            !empty($applied);


        return [

            'status' =>
                $hasPromo,

            'discounts' =>
                $applied,

            'discount_total' =>
                round(
                    $totalDiscount,
                    2
                ),

            'total' =>
                round(
                    max(
                        $remaining,
                        0
                    ),
                    2
                ),

            'message' =>
                $hasPromo
                    ? 'Promo berhasil diterapkan'
                    : 'Tidak ada promo yang berlaku',

        ];
    }


    /**
     * ============================================================
     * FIND DISCOUNTS
     * ============================================================
     */

    protected function findDiscounts(
        float $subtotal,
        int $gameId,
        int $itemId,
        ?int $paymentId,
        ?string $voucherCode,
        $user,
        bool $lockForUpdate = false
    ): Collection {

        $today =
            Carbon::today();


        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            Discount::query()

                /*
                |--------------------------------------------------------------------------
                | Active
                |--------------------------------------------------------------------------
                */

                ->where(
                    'is_active',
                    1
                )

                /*
                |--------------------------------------------------------------------------
                | Game
                |--------------------------------------------------------------------------
                |
                | NULL = semua game
                |
                */

                ->where(
                    function ($query) use ($gameId) {

                        $query

                            ->whereNull(
                                'game_id'
                            )

                            ->orWhere(
                                'game_id',
                                $gameId
                            );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Item
                |--------------------------------------------------------------------------
                |
                | NULL = semua item
                |
                */

                ->where(
                    function ($query) use ($itemId) {

                        $query

                            ->whereNull(
                                'item_id'
                            )

                            ->orWhere(
                                'item_id',
                                $itemId
                            );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Start date
                |--------------------------------------------------------------------------
                */

                ->where(
                    function ($query) use ($today) {

                        $query

                            ->whereNull(
                                'start_date'
                            )

                            ->orWhereDate(
                                'start_date',
                                '<=',
                                $today
                            );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | End date
                |--------------------------------------------------------------------------
                */

                ->where(
                    function ($query) use ($today) {

                        $query

                            ->whereNull(
                                'end_date'
                            )

                            ->orWhereDate(
                                'end_date',
                                '>=',
                                $today
                            );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Global quota
                |--------------------------------------------------------------------------
                */

                ->where(
                    function ($query) {

                        $query

                            ->whereNull(
                                'usage_limit'
                            )

                            ->orWhereColumn(
                                'quota_used',
                                '<',
                                'usage_limit'
                            );
                    }
                );


        $discounts =
            collect();


        /*
        |--------------------------------------------------------------------------
        | 1. VOUCHER
        |--------------------------------------------------------------------------
        */

        if (
            filled($voucherCode)
        ) {

            $voucherQuery =
                (clone $baseQuery)

                    ->where(
                        'trigger_type',
                        'voucher'
                    )

                    ->where(
                        'code',
                        strtoupper(
                            trim(
                                $voucherCode
                            )
                        )
                    );


            $this->applyLock(
                $voucherQuery,
                $lockForUpdate
            );


            $voucher =
                $voucherQuery->first();


            if ($voucher) {

                $discounts->push(
                    $voucher
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. PAYMENT METHOD
        |--------------------------------------------------------------------------
        */

        if ($paymentId !== null) {

            $paymentQuery =
                (clone $baseQuery)

                    ->where(
                        'trigger_type',
                        'payment_method'
                    )

                    ->where(
                        'payment_id',
                        $paymentId
                    )

                    ->orderByDesc(
                        'amount'
                    );


            $this->applyLock(
                $paymentQuery,
                $lockForUpdate
            );


            foreach (
                $paymentQuery->get()
                as $promo
            ) {

                $discounts->push(
                    $promo
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. AUTOMATIC
        |--------------------------------------------------------------------------
        */

        $automaticQuery =
            (clone $baseQuery)

                ->where(
                    'trigger_type',
                    'automatic'
                )

                ->orderByDesc(
                    'amount'
                );


        $this->applyLock(
            $automaticQuery,
            $lockForUpdate
        );


        foreach (
            $automaticQuery->get()
            as $promo
        ) {

            $discounts->push(
                $promo
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. FLASH SALE
        |--------------------------------------------------------------------------
        */

        $flashSaleQuery =
            (clone $baseQuery)

                ->where(
                    'trigger_type',
                    'flash_sale'
                )

                ->orderByDesc(
                    'amount'
                );


        $this->applyLock(
            $flashSaleQuery,
            $lockForUpdate
        );


        foreach (
            $flashSaleQuery->get()
            as $promo
        ) {

            $discounts->push(
                $promo
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. NEW USER
        |--------------------------------------------------------------------------
        */

        if (
            $this->isNewUser($user)
        ) {

            $newUserQuery =
                (clone $baseQuery)

                    ->where(
                        'trigger_type',
                        'new_user'
                    )

                    ->orderByDesc(
                        'amount'
                    );


            $this->applyLock(
                $newUserQuery,
                $lockForUpdate
            );


            foreach (
                $newUserQuery->get()
                as $promo
            ) {

                $discounts->push(
                    $promo
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Remove duplicate promo
        |--------------------------------------------------------------------------
        */

        return $discounts

            ->unique(
                'id'
            )

            ->values();
    }


    /**
     * ============================================================
     * APPLY LOCK
     * ============================================================
     */

    protected function applyLock(
        Builder $query,
        bool $lockForUpdate
    ): void {

        if ($lockForUpdate) {

            $query->lockForUpdate();
        }
    }
}