<section class="dashboard-products">

    <div class="dashboard-section-container">

        {{-- HEADER --}}

        <div class="dashboard-section-header">

            <span class="section-eyebrow">
                PALING BANYAK DIBELI
            </span>

            <div class="section-title-row">

                <h2 class="dashboard-section-title">
                    Top Seller
                </h2>


                @if($topSellers->count() > 0)

                    <div class="marketplace-nav">

                        <button
                            type="button"
                            class="marketplace-arrow"
                            id="topSellerPrev"
                        >

                            <i class="fa-solid fa-chevron-left"></i>

                        </button>


                        <button
                            type="button"
                            class="marketplace-arrow"
                            id="topSellerNext"
                        >

                            <i class="fa-solid fa-chevron-right"></i>

                        </button>

                    </div>

                @endif

            </div>


            <p class="dashboard-section-description">

                Produk game yang paling banyak diminati pengguna.

            </p>

        </div>


        {{-- PRODUCTS --}}

        @if($topSellers->count() > 0)

            <div
                class="top-seller-carousel"
                id="topSellerCarousel"
            >

                @foreach($topSellers as $item)

                    @include(
                        'components.dashboard.product-card',
                        ['item' => $item]
                    )

                @endforeach

            </div>

        @else

            @include(
                'components.dashboard.empty-state',
                [
                    'icon' => 'fa-box-open',
                    'message' => 'Belum ada produk Top Seller.'
                ]
            )

        @endif

    </div>

</section>