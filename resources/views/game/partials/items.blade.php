<section
    class="game-items-section"
    id="topup"
>

<div class="container">

    {{-- =================================================
         SECTION HEADER
    ================================================== --}}
    <div class="game-section-header">

        <div>

            <span class="game-section-eyebrow">
                PILIH PRODUK
            </span>

            <h2>
                Pilih Nominal Top Up
            </h2>

            <p>
                Pilih nominal yang ingin kamu beli.
            </p>

        </div>

    </div>


    {{-- =================================================
         ITEMS GRID
    ================================================== --}}
    <div class="game-items-grid">

        @forelse($items as $item)

            @php

                /*
                |--------------------------------------------------------------------------
                | MOO GOLD VALIDATION
                |--------------------------------------------------------------------------
                |
                | Item dianggap bisa divalidasi ke MooGold
                | jika mempunyai product ID + variation ID.
                |
                */

                $requiresPlayerValidation =
                    !empty($item->moogold_product_id) &&
                    !empty($item->moogold_variation_id);


                /*
                |--------------------------------------------------------------------------
                | AUTOMATIC PRICE
                |--------------------------------------------------------------------------
                |
                | Harga asli tetap berasal dari:
                |
                | $item->price
                |
                | Harga automatic hanya untuk display.
                |
                */

                $automaticPrice =
                    $item->automatic_price
                    ?? [
                        'has_discount' => false,
                        'original_price' => (float) $item->price,
                        'discount_total' => 0,
                        'final_price' => (float) $item->price,
                        'discounts' => [],
                    ];

                $hasAutomaticDiscount =
                    !empty(
                        $automaticPrice['has_discount']
                    )
                    &&
                    (float) (
                        $automaticPrice['final_price']
                        ?? $item->price
                    ) < (float) $item->price;

            @endphp


            <div
                class="game-item-card"
                data-item-id="{{ $item->id }}"
                data-requires-player-validation="{{ $requiresPlayerValidation ? '1' : '0' }}"
            >

                {{-- =================================================
                     IMAGE
                ================================================== --}}
                <div class="game-item-image">

                    @if($item->image)

                        <img
                            src="{{ asset('storage/'.$item->image) }}"
                            alt="{{ $item->item_name }}"
                            loading="lazy"
                        >

                    @else

                        <div class="game-item-placeholder">

                            <i class="fa-solid fa-coins"></i>

                        </div>

                    @endif

                </div>


                {{-- =================================================
                     BODY
                ================================================== --}}
                <div class="game-item-body">

                    {{-- QUANTITY --}}
                    <span class="game-item-qty">

                        {{ $item->qty }} Item

                    </span>


                    {{-- NAME --}}
                    <h3>
                        {{ $item->item_name }}
                    </h3>


                    {{-- =================================================
                         PRICE
                    ================================================== --}}
                    @if($hasAutomaticDiscount)

                        <div class="game-item-price has-automatic-discount">

                            {{-- HARGA ASLI --}}
                            <span class="game-item-price-original">

                                Rp
                                {{ number_format(
                                    (float) ($automaticPrice['original_price'] ?? $item->price),
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </span>


                            {{-- HARGA PROMO --}}
                            <span class="game-item-price-discount">

                                Rp
                                {{ number_format(
                                    (float) ($automaticPrice['final_price'] ?? $item->price),
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </span>

                        </div>

                    @else

                        <div class="game-item-price">

                            Rp
                            {{ number_format(
                                (float) $item->price,
                                0,
                                ',',
                                '.'
                            ) }}

                        </div>

                    @endif


                    {{-- =================================================
                         BUTTON
                    ================================================== --}}
                    <button
                        type="button"
                        class="game-item-button"
                        onclick="selectItem(
                            @js($item->id),
                            @js($item->item_name),
                            @js($item->price),
                            @js($requiresPlayerValidation)
                        )"
                    >

                        <span>
                            Pilih
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </div>

            </div>

        @empty

            {{-- =================================================
                 EMPTY STATE
            ================================================== --}}
            <div class="game-empty-state">

                <i class="fa-solid fa-box-open"></i>

                <h3>
                    Produk belum tersedia
                </h3>

                <p>
                    Belum ada nominal top up untuk game ini.
                </p>

            </div>

        @endforelse

    </div>

</div>

</section>