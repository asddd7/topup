<section class="dashboard-games">

    <div class="dashboard-section-container">

        {{-- HEADER --}}

        <div class="dashboard-section-header">

            <span class="section-eyebrow">
                GAME PILIHAN
            </span>

            <h2 class="dashboard-section-title">
                Semua Game
            </h2>

            <p class="dashboard-section-description">

                Pilih game favoritmu dan mulai top up sekarang.

            </p>

        </div>


        @if($games->count() > 0)


            {{-- DIVIDER --}}

            <div class="game-market-divider">

                <span class="game-market-line"></span>

                <span class="game-market-label">

                    PILIH GAME FAVORITMU

                </span>

                <span class="game-market-line"></span>

            </div>


            {{-- GAME GRID --}}

            <div class="games-grid">

                @foreach($games as $game)

                    @include(
                        'components.dashboard.game-card',
                        ['game' => $game]
                    )

                @endforeach

            </div>


        @else

            @include(
                'components.dashboard.empty-state',
                [
                    'icon' => 'fa-gamepad',
                    'message' => 'Belum ada game tersedia.'
                ]
            )

        @endif

    </div>

</section>