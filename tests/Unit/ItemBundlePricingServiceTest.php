<?php

use App\Models\Item;
use App\Services\ItemBundlePricingService;

it('allocates the bundle total across component order details', function () {
    $firstComponent = new Item(['price' => 100]);
    $firstComponent->setRelation('pivot', (object) ['quantity' => 1]);

    $secondComponent = new Item(['price' => 200]);
    $secondComponent->setRelation('pivot', (object) ['quantity' => 1]);

    $bundle = new Item(['price' => 10]);
    $bundle->setRelation(
        'bundleItems',
        collect([$firstComponent, $secondComponent])
    );

    $details = (new ItemBundlePricingService())->allocate($bundle, 10);

    expect($details)->toHaveCount(2)
        ->and(round(array_sum(array_column($details, 'subtotal')), 2))
        ->toBe(10.0)
        ->and($details[0]['item'])->toBe($firstComponent)
        ->and($details[1]['item'])->toBe($secondComponent);
});

it('multiplies component quantities for repeated bundle purchases', function () {
    $component = new Item(['price' => 50]);
    $component->setRelation('pivot', (object) ['quantity' => 2]);

    $bundle = new Item(['price' => 100]);
    $bundle->setRelation('bundleItems', collect([$component]));

    $details = (new ItemBundlePricingService())->allocate($bundle, 300, 3);

    expect($details[0]['qty'])->toBe(6)
        ->and($details[0]['price'])->toEqual(50.0)
        ->and($details[0]['subtotal'])->toEqual(300.0);
});