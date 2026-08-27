<section class="dashboard-games">

    <div class="dashboard-section-container">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

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


        {{-- =====================================================
                    SEARCH GAME
        ====================================================== --}}

        @if($games->count() > 0)

            <div class="dashboard-game-search">

                <label
                    for="gameSearchInput"
                    class="dashboard-search-label"
                >
                    Cari Game
                </label>


                <div class="dashboard-search-box">

                    <input
                        type="text"
                        id="gameSearchInput"
                        class="dashboard-search-input"
                        placeholder="Cari game favoritmu..."
                        autocomplete="off"
                    >


                    <button
                        type="button"
                        id="clearGameSearch"
                        class="dashboard-search-button"
                        aria-label="Cari game"
                    >

                        <i class="fa-solid fa-magnifying-glass"></i>

                    </button>

                </div>


                <div
                    class="dashboard-search-info"
                    id="gameSearchInfo"
                >

                    Menampilkan

                    <strong id="gameResultCount">
                        {{ $games->count() }}
                    </strong>

                    game

                </div>

            </div>

        @endif


        {{-- =====================================================
             DIVIDER
        ====================================================== --}}

        @if($games->count() > 0)

            <div class="game-market-divider">

                <span class="game-market-line"></span>

                <span class="game-market-label">
                    PILIH GAME FAVORITMU
                </span>

                <span class="game-market-line"></span>

            </div>


            {{-- =================================================
                 GAME GRID
            ================================================== --}}

            <div
                class="games-grid"
                id="gamesGrid"
            >

                @foreach($games as $game)

                    <div
                        class="game-search-item"
                        data-game-name="{{ strtolower($game->game_name) }}"
                    >

                        @include(
                            'components.dashboard.game-card',
                            ['game' => $game]
                        )

                    </div>

                @endforeach

            </div>


            {{-- =================================================
                 SEARCH EMPTY STATE
            ================================================== --}}

            <div
                class="game-search-empty"
                id="gameSearchEmpty"
                style="display: none;"
            >

                <div class="game-search-empty-icon">

                    <i class="fa-solid fa-gamepad"></i>

                </div>

                <h3>
                    Game tidak ditemukan
                </h3>

                <p>
                    Coba gunakan nama game yang berbeda.
                </p>

                <button
                    type="button"
                    id="resetGameSearch"
                    class="btn btn-outline-primary"
                >

                    <i class="fa-solid fa-rotate-left me-1"></i>

                    Tampilkan Semua Game

                </button>

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


{{-- =========================================================
     SEARCH SCRIPT
========================================================= --}}

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const searchInput =
            document.getElementById(
                'gameSearchInput'
            );


        const clearButton =
            document.getElementById(
                'clearGameSearch'
            );


        const resetButton =
            document.getElementById(
                'resetGameSearch'
            );


        const resultCount =
            document.getElementById(
                'gameResultCount'
            );


        const searchEmpty =
            document.getElementById(
                'gameSearchEmpty'
            );


        const gameItems =
            document.querySelectorAll(
                '.game-search-item'
            );


        /*
        |--------------------------------------------------------------------------
        | Tidak ada search
        |--------------------------------------------------------------------------
        */

        if (!searchInput) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER GAME
        |--------------------------------------------------------------------------
        */

        function filterGames() {

            const keyword =
                searchInput.value
                    .trim()
                    .toLowerCase();


            let visibleCount = 0;


            /*
            |--------------------------------------------------------------------------
            | LOOP GAME
            |--------------------------------------------------------------------------
            */

            gameItems.forEach(
                function (item) {

                    const gameName =
                        item.dataset.gameName || '';


                    const matched =
                        gameName.includes(
                            keyword
                        );


                    if (matched) {

                        item.style.display = '';

                        visibleCount++;

                    } else {

                        item.style.display = 'none';

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | RESULT COUNT
            |--------------------------------------------------------------------------
            */

            if (resultCount) {

                resultCount.innerText =
                    visibleCount;

            }


            /*
            |--------------------------------------------------------------------------
            | EMPTY STATE
            |--------------------------------------------------------------------------
            */

            if (searchEmpty) {

                if (
                    keyword !== '' &&
                    visibleCount === 0
                ) {

                    searchEmpty.style.display =
                        'block';

                } else {

                    searchEmpty.style.display =
                        'none';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | CLEAR BUTTON
            |--------------------------------------------------------------------------
            */

            if (clearButton) {

                clearButton.hidden =
                    keyword === '';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | INPUT EVENT
        |--------------------------------------------------------------------------
        */

        searchInput.addEventListener(
            'input',
            filterGames
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR
        |--------------------------------------------------------------------------
        */

        if (clearButton) {

            clearButton.addEventListener(
                'click',
                function () {

                    searchInput.value = '';

                    filterGames();

                    searchInput.focus();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        if (resetButton) {

            resetButton.addEventListener(
                'click',
                function () {

                    searchInput.value = '';

                    filterGames();

                    searchInput.focus();

                }
            );

        }

    }

);

</script>

@endpush