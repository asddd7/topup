<?php

namespace App\Services;

use App\Models\Item;

class ItemBundlePricingService
{
    public function allocate(
        Item $item,
        float $totalAmount,
        int $purchaseQuantity = 1
    ): array {
        $components = $item->bundleItems;

        if ($components->isEmpty()) {
            return [[
                'item' => $item,
                'qty' => $purchaseQuantity,
                'price' => round(
                    $totalAmount / max(1, $purchaseQuantity),
                    2
                ),
                'subtotal' => $totalAmount,
            ]];
        }

        $weights = $components->map(
            fn ($component) => max(
                0,
                (float) $component->price
                    * max(1, (int) $component->pivot->quantity)
            )
        );
        $weightTotal = $weights->sum();

        if ($weightTotal <= 0) {
            $weights = $components->map(
                fn ($component) => max(
                    1,
                    (int) $component->pivot->quantity
                )
            );
            $weightTotal = $weights->sum();
        }

        $totalCents = (int) round($totalAmount * 100);
        $remainingCents = $totalCents;
        $lastIndex = $components->count() - 1;
        $details = [];

        foreach ($components->values() as $index => $component) {
            $quantity = max(
                1,
                (int) $component->pivot->quantity
            ) * max(1, $purchaseQuantity);

            $componentCents = $index === $lastIndex
                ? $remainingCents
                : min(
                    $remainingCents,
                    (int) round(
                        $totalCents
                            * $weights[$index]
                            / $weightTotal
                    )
                );
            $componentSubtotal = $componentCents / 100;

            $details[] = [
                'item' => $component,
                'qty' => $quantity,
                'price' => round(
                    $componentSubtotal / $quantity,
                    2
                ),
                'subtotal' => $componentSubtotal,
            ];

            $remainingCents -= $componentCents;
        }

        return $details;
    }
}