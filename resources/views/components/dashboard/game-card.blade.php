<article class="market-game-card">

    <a href="{{ route('game.show', $game->id) }}">

        <div class="market-game-image">

            @if($game->game_logo)

                <img
                    src="{{ asset('storage/'.$game->game_logo) }}"
                    alt="{{ $game->game_name }}"
                    loading="lazy"
                >

            @else

                <div class="market-product-placeholder">

                    <i class="fa-solid fa-gamepad"></i>

                </div>

            @endif


            <div class="market-game-overlay">

                <span class="market-game-action">
                    <i class="fa-solid fa-bolt me-1"></i>
                    Top Up Sekarang
                </span>

            </div>

        </div>


        <div class="market-game-info">

            <h3 class="market-game-title">
                {{ $game->game_name }}
            </h3>


            @if($game->publisher)

                <div class="market-game-publisher">
                    {{ $game->publisher }}
                </div>

            @endif

        </div>

    </a>

</article>