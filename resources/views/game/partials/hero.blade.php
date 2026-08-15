<section class="game-hero-section">

    <div class="container">

        <div class="game-hero-card">

            <div class="game-hero-content">

                {{-- =================================================
                     GAME LOGO
                ================================================== --}}
                <div class="game-hero-logo">

                    @if($game->game_logo)

                        <img
                            src="{{ asset('storage/'.$game->game_logo) }}"
                            alt="{{ $game->game_name }}"
                        >

                    @else

                        <div class="game-logo-placeholder">

                            <i class="fa-solid fa-gamepad"></i>

                        </div>

                    @endif

                </div>


                {{-- =================================================
                     GAME INFO
                ================================================== --}}
                <div class="game-hero-info">

                    <span class="game-hero-eyebrow">
                        GAME TOP UP
                    </span>

                    <h1>
                        {{ $game->game_name }}
                    </h1>

                    @if($game->publisher)

                        <p>
                            <i class="fa-solid fa-building"></i>

                            {{ $game->publisher }}
                        </p>

                    @endif


                    <a
                        href="#topup"
                        class="game-topup-btn"
                    >

                        <i class="fa-solid fa-bolt"></i>

                        Top Up Sekarang

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>