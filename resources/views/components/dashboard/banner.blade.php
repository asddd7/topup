<div class="dashboard-banner-section">

    <div
        id="bannerCarousel"
        class="carousel slide"
        data-bs-ride="carousel"
        data-bs-interval="3500"
    >

        <div class="carousel-inner">

            @foreach($banners as $banner)

                <div
                    class="carousel-item {{ $loop->first ? 'active' : '' }}"
                >

                    @if($banner->url != '#')

                        <a
                            href="{{ $banner->url }}"
                            class="banner-link"

                            @if($banner->link && !$banner->game_id)
                                target="_blank"
                            @endif
                        >

                            <article class="promo-banner">

                                <img
                                    src="{{ asset('storage/'.$banner->image) }}"
                                    alt="Banner Promo"
                                    class="promo-banner-image"
                                >

                                <div class="promo-banner-overlay">

                                    <div class="promo-banner-content">

                                        <span class="promo-badge">

                                            <i class="fa-solid fa-fire"></i>

                                            PROMO

                                        </span>

                                    </div>

                                </div>

                            </article>

                        </a>

                    @else

                        <article class="promo-banner">

                            <img
                                src="{{ asset('storage/'.$banner->image) }}"
                                alt="Banner Promo"
                                class="promo-banner-image"
                            >

                            <div class="promo-banner-overlay">

                                <div class="promo-banner-content">

                                    <span class="promo-badge">

                                        <i class="fa-solid fa-fire"></i>

                                        PROMO

                                    </span>

                                </div>

                            </div>

                        </article>

                    @endif

                </div>

            @endforeach

        </div>


        {{-- PREVIOUS --}}

        <button
            class="carousel-control-prev banner-control"
            type="button"
            data-bs-target="#bannerCarousel"
            data-bs-slide="prev"
        >

            <span class="banner-arrow">

                <i class="fa-solid fa-chevron-left"></i>

            </span>

        </button>


        {{-- NEXT --}}

        <button
            class="carousel-control-next banner-control"
            type="button"
            data-bs-target="#bannerCarousel"
            data-bs-slide="next"
        >

            <span class="banner-arrow">

                <i class="fa-solid fa-chevron-right"></i>

            </span>

        </button>


        {{-- INDICATORS --}}

        <div class="carousel-indicators banner-indicators">

            @foreach($banners as $banner)

                <button
                    type="button"
                    data-bs-target="#bannerCarousel"
                    data-bs-slide-to="{{ $loop->index }}"
                    class="{{ $loop->first ? 'active' : '' }}"
                ></button>

            @endforeach

        </div>

    </div>

</div>