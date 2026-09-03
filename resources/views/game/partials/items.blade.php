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

            @endphp


            <div
                class="game-item-card"
                data-item-id="{{ $item->id }}"
                data-requires-player-validation="{{ $requiresPlayerValidation ? '1' : '0' }}"
            >

                <div class="game-item-image">

                    @if($item->image)

                        <img
                            src="{{ asset('storage/'.$item->image) }}"
                            alt="{{ $item->item_name }}"
                        >

                    @else

                        <div class="game-item-placeholder">

                            <i class="fa-solid fa-coins"></i>

                        </div>

                    @endif

                </div>


                <div class="game-item-body">

                    <span class="game-item-qty">

                        {{ $item->qty }} Item

                    </span>


                    <h3>
                        {{ $item->item_name }}
                    </h3>


                    <div class="game-item-price">

                        Rp {{ number_format($item->price, 0, ',', '.') }}

                    </div>


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
