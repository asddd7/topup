<article class="market-product-card">

    <a
        href="{{ route('game.show', $item->game_id) }}"
        class="market-product-link"
    >

        {{-- IMAGE --}}

        <div class="market-product-image">

            @if($item->image)

                <img
                    src="{{ asset('storage/'.$item->image) }}"
                    alt="{{ $item->item_name }}"
                    loading="lazy"
                >

            @else

                <div class="market-product-placeholder">

                    <i class="fa-solid fa-gamepad"></i>

                </div>

            @endif


            {{-- BADGE --}}

            <span class="market-product-badge">

                <i class="fa-solid fa-fire"></i>

                BEST SELLER

            </span>

        </div>


        {{-- INFO --}}

        <div class="market-product-info">

            <span class="market-game-name">

                {{ $item->game->game_name }}

            </span>


            <h3 class="market-product-name">

                {{ $item->item_name }}

            </h3>


            <div class="market-price">

                Rp {{ number_format($item->price, 0, ',', '.') }}

            </div>

        </div>

    </a>

</article>