<article class="game-product-card">

    <a
        href="{{ route('game.show', $game->id) }}"
        class="game-product-link"
    >

        {{-- IMAGE --}}

        <div class="game-image-wrapper">

            @if($game->game_logo)

                <img
                    src="{{ asset('storage/'.$game->game_logo) }}"
                    alt="{{ $game->game_name }}"
                    class="game-product-image"
                    loading="lazy"
                >

            @else

                <div class="game-placeholder">

                    <i class="fa-solid fa-gamepad"></i>

                </div>

            @endif


            {{-- BADGE --}}

            <div class="game-badge">

                <i class="fa-solid fa-bolt"></i>

                TOP UP

            </div>


            {{-- HOVER --}}

            <div class="game-hover-overlay">

                <span class="game-topup-button">

                    <i class="fa-solid fa-cart-shopping"></i>

                    Top Up Sekarang

                </span>

            </div>

        </div>


        {{-- GAME INFO --}}

        <div class="game-product-info">

            <h3 class="game-product-name">

                {{ $game->game_name }}

            </h3>


            <div class="game-product-action">

                <span>
                    Top Up
                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </div>

        </div>

    </a>

</article>