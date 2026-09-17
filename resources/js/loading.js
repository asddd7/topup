/* =========================================================
   GLOBAL LOADING
========================================================= */

const globalLoading =
    document.getElementById('globalLoading');


/* =========================================================
   SHOW
========================================================= */

function showGlobalLoading()
{
    if (!globalLoading) {
        return;
    }

    globalLoading.classList.add('is-active');

    globalLoading.setAttribute(
        'aria-hidden',
        'false'
    );
}


/* =========================================================
   HIDE
========================================================= */

function hideGlobalLoading()
{
    if (!globalLoading) {
        return;
    }

    globalLoading.classList.remove('is-active');

    globalLoading.setAttribute(
        'aria-hidden',
        'true'
    );
}


/* =========================================================
   GLOBAL
========================================================= */

window.showGlobalLoading =
    showGlobalLoading;

window.hideGlobalLoading =
    hideGlobalLoading;

    /* =========================================================
   AUTO LINK LOADING
========================================================= */

document.addEventListener(
    'click',
    function (event) {

        const link =
            event.target.closest('a');

        if (!link) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Abaikan:
        | - target baru
        | - download
        | - javascript
        | - anchor #
        | - modal Bootstrap
        |--------------------------------------------------------------------------
        */

        if (
            link.target === '_blank' ||
            link.hasAttribute('download') ||
            link.getAttribute('href')?.startsWith('#') ||
            link.getAttribute('href')?.startsWith('javascript:') ||
            link.hasAttribute('data-bs-toggle')
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | External link
        |--------------------------------------------------------------------------
        */

        const url =
            new URL(
                link.href,
                window.location.href
            );

        if (
            url.origin !==
            window.location.origin
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Show
        |--------------------------------------------------------------------------
        */

        showGlobalLoading();

    }
);