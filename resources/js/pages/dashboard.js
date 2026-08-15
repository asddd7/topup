document.addEventListener('DOMContentLoaded', function () {

    const carousel =
        document.getElementById('topSellerCarousel');

    const prev =
        document.getElementById('topSellerPrev');

    const next =
        document.getElementById('topSellerNext');


    if (!carousel) {
        return;
    }


    next?.addEventListener('click', function () {

        carousel.scrollBy({

            left: carousel.clientWidth * 0.75,

            behavior: 'smooth'

        });

    });


    prev?.addEventListener('click', function () {

        carousel.scrollBy({

            left: -(carousel.clientWidth * 0.75),

            behavior: 'smooth'

        });

    });

});