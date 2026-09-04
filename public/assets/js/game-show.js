/* =========================================================
   GAME SHOW
========================================================= */

const gameShowPage =
    document.querySelector('#game-show-page');


let selectedPrice = 0;


let playerValidation = {
    valid: false,
    confirmed: false,
    validationAvailable: false,
    nickname: null,
    userId: null,
    serverId: null,
    itemId: null,
    requiresValidation: false
};


let selectedPaymentId = null;

let selectedVoucher = null;

let appliedPromos = [];

let checkoutProcessing = false;

let playerValidationTimer = null;


/* =========================================================
   CONFIG
========================================================= */

const gameConfig =
    window.gameShowConfig || {};


/* =========================================================
   FORMAT RUPIAH
========================================================= */

function formatRupiah(number)
{
    return new Intl.NumberFormat('id-ID').format(
        Math.round(parseFloat(number) || 0)
    );
}


/* =========================================================
   ESCAPE HTML
========================================================= */

function escapeHtml(value)
{
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


/* =========================================================
   RESET PLAYER VALIDATION
========================================================= */

function resetPlayerValidation()
{
    /*
    |--------------------------------------------------------------------------
    | Simpan status apakah item membutuhkan validasi
    |--------------------------------------------------------------------------
    */

    const requiresValidation =
        playerValidation.requiresValidation;


    playerValidation = {
        valid: false,
        confirmed: false,
        validationAvailable: false,
        nickname: null,
        userId: null,
        serverId: null,
        itemId: null,

        requiresValidation:
            requiresValidation
    };


    const nicknameInput =
        document.getElementById(
            'player_nickname'
        );


    if (nicknameInput) {

        nicknameInput.value = '';

    }


    const result =
        document.getElementById(
            'player_validation_result'
        );


    if (result) {

        result.innerHTML = '';

    }
}


/* =========================================================
   RENDER PLAYER VALIDATION
========================================================= */

function renderPlayerValidation(
    type,
    message
)
{
    const result =
        document.getElementById(
            'player_validation_result'
        );


    if (!result) {
        return;
    }


    /* -----------------------------------------------------
       LOADING
    ----------------------------------------------------- */

    if (type === 'loading') {

        result.innerHTML = `

            <div class="small text-muted">

                <i
                    class="
                        fa-solid
                        fa-spinner
                        fa-spin
                        me-1
                    "
                ></i>

                Memeriksa ID Player...

            </div>

        `;

        return;
    }


    /* -----------------------------------------------------
       SUCCESS
    ----------------------------------------------------- */

    if (type === 'success') {

        result.innerHTML = `

            <div
                class="
                    alert
                    alert-success
                    py-2
                    mb-0
                "
            >

                <i
                    class="
                        fa-solid
                        fa-circle-check
                        me-1
                    "
                ></i>

                <strong>
                    ID Player berhasil diverifikasi.
                </strong>

                <div class="mt-1">

                    ${escapeHtml(message)}

                </div>

            </div>

        `;

        return;
    }


    /* -----------------------------------------------------
       WARNING
    ----------------------------------------------------- */

    if (type === 'warning') {

        result.innerHTML = `

            <div
                class="
                    alert
                    alert-warning
                    py-2
                    mb-0
                "
            >

                <i
                    class="
                        fa-solid
                        fa-triangle-exclamation
                        me-1
                    "
                ></i>

                <strong>
                    Validasi Player Tidak Tersedia
                </strong>

                <div class="mt-1">

                    ${escapeHtml(message)}

                </div>

            </div>

        `;

        return;
    }


    /* -----------------------------------------------------
       ERROR
    ----------------------------------------------------- */

    result.innerHTML = `

        <div
            class="
                alert
                alert-danger
                py-2
                mb-0
            "
        >

            <i
                class="
                    fa-solid
                    fa-circle-xmark
                    me-1
                "
            ></i>

            ${escapeHtml(message)}

        </div>

    `;
}


/* =========================================================
   PLAYER FIELD CHANGE + AUTO VALIDATION
========================================================= */

document
    .querySelectorAll(
        '[data-moogold-field]'
    )
    .forEach(function(input) {


        input.addEventListener(
            'input',
            function() {

                /*
                |--------------------------------------------------------------------------
                | Reset validation sebelumnya
                |--------------------------------------------------------------------------
                */

                resetPlayerValidation();


                /*
                |--------------------------------------------------------------------------
                | Cancel timer sebelumnya
                |--------------------------------------------------------------------------
                */

                if (playerValidationTimer) {

                    clearTimeout(
                        playerValidationTimer
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Ambil item
                |--------------------------------------------------------------------------
                */

                const itemId =
                    document.querySelector(
                        '#item_id'
                    )?.value?.trim() || '';


                /*
                |--------------------------------------------------------------------------
                | Ambil User ID
                |--------------------------------------------------------------------------
                */

                const userIdInput =
                    document.querySelector(
                        '[data-moogold-field="user-id"]'
                    );


                const userId =
                    userIdInput?.value?.trim() || '';


                /*
                |--------------------------------------------------------------------------
                | Ambil Server ID
                |--------------------------------------------------------------------------
                */

                const serverIdInput =
                    document.querySelector(
                        '[data-moogold-field="server-id"]'
                    );


                const serverId =
                    serverIdInput?.value?.trim() || '';


                /*
                |--------------------------------------------------------------------------
                | Belum pilih produk
                |--------------------------------------------------------------------------
                */

                if (!itemId) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Produk tidak membutuhkan validasi
                |--------------------------------------------------------------------------
                */

                if (
                    !playerValidation.requiresValidation
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | User ID belum lengkap
                |--------------------------------------------------------------------------
                */

                if (!userId) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Server ID wajib tetapi belum lengkap
                |--------------------------------------------------------------------------
                */

                if (
                    serverIdInput &&
                    serverIdInput.required &&
                    !serverId
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Debounce
                |--------------------------------------------------------------------------
                |
                | Tunggu 700ms setelah user berhenti mengetik.
                |
                */

                playerValidationTimer =
                    setTimeout(
                        async function() {

                            const result =
                                await validatePlayerForCheckout(
                                    itemId,
                                    userId,
                                    serverId
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Jangan lakukan apa-apa jika invalid/error
                            |--------------------------------------------------------------------------
                            */

                            if (
                                result.status !== 'valid'
                                &&
                                result.status !== 'unavailable'
                            ) {
                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Pastikan input tidak berubah
                            |--------------------------------------------------------------------------
                            */

                            const currentUserId =
                                document
                                    .querySelector(
                                        '[data-moogold-field="user-id"]'
                                    )
                                    ?.value
                                    ?.trim() || '';


                            const currentServerId =
                                document
                                    .querySelector(
                                        '[data-moogold-field="server-id"]'
                                    )
                                    ?.value
                                    ?.trim() || '';


                            if (
                                currentUserId !== userId
                            ) {
                                return;
                            }


                            if (
                                currentServerId !== serverId
                            ) {
                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Validasi berhasil.
                            | Popup TIDAK ditampilkan di sini.
                            |
                            | Popup hanya muncul saat user klik
                            | tombol Beli Sekarang.
                            |--------------------------------------------------------------------------
                            */

                        },
                        700
                    );

            }
        );


        input.addEventListener(
            'change',
            function() {

                /*
                |--------------------------------------------------------------------------
                | Trigger sama seperti input
                |--------------------------------------------------------------------------
                */

                if (
                    typeof input.dispatchEvent ===
                    'function'
                ) {

                    input.dispatchEvent(
                        new Event(
                            'input',
                            {
                                bubbles: true
                            }
                        )
                    );

                }

            }
        );

    });


/* =========================================================
   VALIDATE PLAYER FOR CHECKOUT
========================================================= */

async function validatePlayerForCheckout(
    itemId,
    userId,
    serverId
)
{
    renderPlayerValidation(
        'loading'
    );


    const csrf =
        document.querySelector(
            'meta[name="csrf-token"]'
        );


    if (!csrf) {

        renderPlayerValidation(
            'error',
            'CSRF token tidak ditemukan.'
        );


        return {
            status: 'error',
            message: 'CSRF token tidak ditemukan.'
        };

    }


    if (
        !gameConfig.validatePlayerUrl
    ) {

        renderPlayerValidation(
            'error',
            'URL validasi player tidak tersedia.'
        );


        return {
            status: 'error',
            message:
                'URL validasi player tidak tersedia.'
        };

    }


    try {

        const response =
            await fetch(
                gameConfig.validatePlayerUrl,
                {
                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrf.content

                    },

                    body: JSON.stringify({

                        item_id:
                            itemId,

                        user_id:
                            userId,

                        server_id:
                            serverId

                    })

                }
            );


        let data = {};

        try {

            data =
                await response.json();

        } catch (jsonError) {

            data = {};

        }


        /* =================================================
           VALIDATION UNAVAILABLE
        ================================================= */

        if (
            response.ok &&
            data.data &&
            data.data.validation_available === false
        ) {

            playerValidation = {

                valid: true,

                confirmed: false,

                validationAvailable: false,

                nickname:
                    data.data.nickname ||
                    null,

                userId:
                    userId,

                serverId:
                    serverId,

                itemId:
                    itemId,

                requiresValidation: true

            };


            renderPlayerValidation(
                'warning',
                data.message ||
                'Validasi player tidak tersedia untuk produk ini.'
            );


            return {

                status: 'unavailable',

                message:
                    data.message ||
                    'Validasi player tidak tersedia untuk produk ini.',

                nickname:
                    data.data.nickname ||
                    null

            };

        }


        /* =================================================
           INVALID
        ================================================= */

        if (
            !response.ok ||
            !data.data ||
            data.data.valid !== true
        ) {

            const message =
                data.message ||
                'User ID atau Server ID tidak valid.';


            playerValidation = {

                valid: false,

                confirmed: false,

                validationAvailable: true,

                nickname: null,

                userId:
                    userId,

                serverId:
                    serverId,

                itemId:
                    itemId,

                requiresValidation: true

            };


            renderPlayerValidation(
                'error',
                message
            );


            return {

                status: 'invalid',

                message:
                    message

            };

        }


        /* =================================================
           VALID
        ================================================= */

        const nickname =
            data.data.nickname ||
            'Player ditemukan';


        playerValidation = {

            valid: true,

            confirmed: false,

            validationAvailable: true,

            nickname:
                nickname,

            userId:
                userId,

            serverId:
                serverId,

            itemId:
                itemId,

            requiresValidation: true

        };


        const nicknameInput =
            document.getElementById(
                'player_nickname'
            );


        if (nicknameInput) {

            nicknameInput.value =
                nickname;

        }


        renderPlayerValidation(
            'success',
            nickname
        );


        return {

            status: 'valid',

            nickname:
                nickname

        };

    } catch (error) {

        console.error(
            'Player validation error:',
            error
        );


        const message =
            error.message ||
            'Gagal melakukan validasi player.';


        renderPlayerValidation(
            'error',
            message
        );


        return {

            status: 'error',

            message:
                message

        };

    }
}


/* =========================================================
   PLAYER CONFIRMATION MODAL
========================================================= */

async function showPlayerConfirmation()
{
    const userId =
        playerValidation.userId ||
        '';


    const serverId =
        playerValidation.serverId ||
        '';


    const nickname =
        playerValidation.nickname ||
        'Tidak tersedia';


    /* =====================================================
       SWEETALERT2
    ===================================================== */

    if (
        typeof Swal !== 'undefined'
    ) {

        /* -------------------------------------------------
           VALIDATION AVAILABLE
        ------------------------------------------------- */

        if (
            playerValidation.validationAvailable
        ) {

            const result =
                await Swal.fire({

                    icon: 'success',

                    title:
                        'Data Player Ditemukan',

                    html: `

                        <div
                            class="
                                text-start
                                mt-3
                            "
                        >

                            <div class="mb-2">

                                <strong>
                                    User ID
                                </strong>

                                <div>
                                    ${escapeHtml(userId)}
                                </div>

                            </div>


                            ${
                                serverId
                                    ? `
                                        <div class="mb-2">

                                            <strong>
                                                Server ID
                                            </strong>

                                            <div>
                                                ${escapeHtml(serverId)}
                                            </div>

                                        </div>
                                    `
                                    : ''
                            }


                            <div class="mb-2">

                                <strong>
                                    Nickname
                                </strong>

                                <div
                                    class="
                                        text-success
                                        fw-bold
                                    "
                                >

                                    ${escapeHtml(nickname)}

                                </div>

                            </div>

                        </div>


                        <div
                            class="
                                alert
                                alert-info
                                text-start
                                small
                                mt-3
                                mb-0
                            "
                        >

                            Pastikan data player sudah benar
                            sebelum melanjutkan pembelian.

                        </div>

                    `,

                    showCancelButton: true,

                    confirmButtonText:
                        'Lanjutkan',

                    cancelButtonText:
                        'Batal',

                    reverseButtons: true

                });


            return result.isConfirmed;

        }


        /* -------------------------------------------------
           VALIDATION UNAVAILABLE
        ------------------------------------------------- */

        const result =
            await Swal.fire({

                icon: 'warning',

                title:
                    'Validasi Player Tidak Tersedia',

                html: `

                    <div
                        class="
                            text-start
                            mt-3
                        "
                    >

                        <div class="mb-2">

                            <strong>
                                User ID
                            </strong>

                            <div>
                                ${escapeHtml(userId)}
                            </div>

                        </div>


                        ${
                            serverId
                                ? `
                                    <div class="mb-2">

                                        <strong>
                                            Server ID
                                        </strong>

                                        <div>
                                            ${escapeHtml(serverId)}
                                        </div>

                                    </div>
                                `
                                : ''
                        }


                        <div
                            class="
                                alert
                                alert-warning
                                small
                                mt-3
                                mb-0
                            "
                        >

                            MooGold tidak menyediakan
                            validasi untuk produk ini.

                            <br><br>

                            Pastikan User ID dan Server ID
                            sudah benar.

                            <br><br>

                            Apakah Anda tetap ingin
                            melanjutkan pembelian?

                        </div>

                    </div>

                `,

                showCancelButton: true,

                confirmButtonText:
                    'Tetap Lanjutkan',

                cancelButtonText:
                    'Batal',

                reverseButtons: true

            });


        return result.isConfirmed;

    }


    /* =====================================================
       FALLBACK JIKA SWEETALERT2 TIDAK TERSEDIA
    ===================================================== */

    return window.confirm(
        playerValidation.validationAvailable
            ? `Data Player:\n\n` +
              `User ID: ${userId}\n` +
              `Server ID: ${serverId}\n` +
              `Nickname: ${nickname}\n\n` +
              `Lanjutkan pembelian?`
            : `Validasi player tidak tersedia untuk produk ini.\n\n` +
              `User ID: ${userId}\n` +
              `Server ID: ${serverId}\n\n` +
              `Tetap lanjutkan pembelian?`
    );
}


/* =========================================================
   SELECT ITEM
========================================================= */

window.selectItem = function(
    id,
    name,
    price,
    requiresPlayerValidation = false
)
{
    selectedPrice =
        parseFloat(price) || 0;


    /* -----------------------------------------------------
       RESET PLAYER VALIDATION
    ----------------------------------------------------- */

    resetPlayerValidation();


    /* -----------------------------------------------------
       RESET PROMO
    ----------------------------------------------------- */

    selectedVoucher = null;

    appliedPromos = [];


    /* -----------------------------------------------------
       RESET VOUCHER
    ----------------------------------------------------- */

    const codeInput =
        document.getElementById('code');


    if (codeInput) {

        codeInput.value = '';

    }


    const voucherInput =
        document.getElementById('voucher');


    if (voucherInput) {

        voucherInput.value = '';

    }


    /* -----------------------------------------------------
       ITEM ID
    ----------------------------------------------------- */

    const itemInput =
        document.getElementById('item_id');


    if (itemInput) {

        itemInput.value = id;

    }


    /* -----------------------------------------------------
       ITEM NAME
    ----------------------------------------------------- */

    const selectedItem =
        document.getElementById('selected_item');


    if (selectedItem) {

        selectedItem.innerText =
            name;

    }


    /* -----------------------------------------------------
       PLAYER VALIDATION STATE
    ----------------------------------------------------- */

    playerValidation.requiresValidation =
        Boolean(
            requiresPlayerValidation
        );


    /* -----------------------------------------------------
       TAMPILKAN / SEMBUNYIKAN VALIDASI
    ----------------------------------------------------- */

    const playerValidationBox =
        document.querySelector(
            '.player-validation'
        );


    if (playerValidationBox) {

        playerValidationBox.style.display =
            requiresPlayerValidation
                ? ''
                : 'none';

    }


    /* -----------------------------------------------------
       ORIGINAL PRICE
    ----------------------------------------------------- */

    const originalPrice =
        document.getElementById(
            'original_price'
        );


    if (originalPrice) {

        originalPrice.innerText =
            formatRupiah(
                selectedPrice
            );

    }


    /* -----------------------------------------------------
       DISCOUNT
    ----------------------------------------------------- */

    const discountPrice =
        document.getElementById(
            'discount_price'
        );


    if (discountPrice) {

        discountPrice.innerText =
            '0';

    }


    /* -----------------------------------------------------
       FINAL PRICE
    ----------------------------------------------------- */

    const finalPrice =
        document.getElementById(
            'final_price'
        );


    if (finalPrice) {

        finalPrice.innerText =
            formatRupiah(
                selectedPrice
            );

    }


    /* -----------------------------------------------------
       HIDDEN SUBTOTAL
    ----------------------------------------------------- */

    const subtotal =
        document.getElementById(
            'original_subtotal'
        );


    if (subtotal) {

        subtotal.value =
            selectedPrice;

    }


    /* -----------------------------------------------------
       HIDDEN TOTAL
    ----------------------------------------------------- */

    const total =
        document.getElementById(
            'total_price'
        );


    if (total) {

        total.value =
            selectedPrice;

    }


    /* -----------------------------------------------------
       HIDDEN DISCOUNT
    ----------------------------------------------------- */

    const discount =
        document.getElementById(
            'discount_total'
        );


    if (discount) {

        discount.value =
            0;

    }


    /* -----------------------------------------------------
       DISCOUNT IDS
    ----------------------------------------------------- */

    const discountIds =
        document.getElementById(
            'discount_ids'
        );


    if (discountIds) {

        discountIds.value =
            '';

    }


    /* -----------------------------------------------------
       PROMO LIST
    ----------------------------------------------------- */

    const promoList =
        document.getElementById(
            'promoList'
        );


    if (promoList) {

        promoList.innerHTML = `

            <div class="promo-empty">

                ${
                    selectedPaymentId
                        ? 'Menghitung promo...'
                        : 'Pilih metode pembayaran untuk melihat promo.'
                }

            </div>

        `;

    }


    /* -----------------------------------------------------
       SCROLL CHECKOUT
    ----------------------------------------------------- */

    const checkout =
        document.getElementById(
            'checkout'
        );


    if (checkout) {

        checkout.scrollIntoView({

            behavior: 'smooth',

            block: 'start'

        });

    }


    /* -----------------------------------------------------
       CALCULATE PROMO
    ----------------------------------------------------- */

    if (selectedPaymentId) {

        calculatePromos();

    }

};


/* =========================================================
   PAYMENT METHOD
========================================================= */

document
    .querySelectorAll(
        '.payment-radio'
    )
    .forEach(function(payment) {

        payment.addEventListener(
            'change',
            function() {

                selectedPaymentId =
                    parseInt(
                        this.value
                    ) || null;


                calculatePromos();

            }
        );

    });


/* =========================================================
   CHECK VOUCHER
========================================================= */

function checkVoucher()
{
    const codeInput =
        document.getElementById(
            'code'
        );


    if (!codeInput) {
        return;
    }


    const code =
        codeInput.value.trim();


    if (code === '') {

        selectedVoucher = null;

    } else {

        selectedVoucher =
            code;

    }


    const voucherInput =
        document.getElementById(
            'voucher'
        );


    if (voucherInput) {

        voucherInput.value =
            selectedVoucher ?? '';

    }


    calculatePromos();
}


/* =========================================================
   REMOVE VOUCHER
========================================================= */

function removeVoucher()
{
    selectedVoucher = null;


    const codeInput =
        document.getElementById(
            'code'
        );


    if (codeInput) {

        codeInput.value =
            '';

    }


    const voucherInput =
        document.getElementById(
            'voucher'
        );


    if (voucherInput) {

        voucherInput.value =
            '';

    }


    calculatePromos();
}


/* =========================================================
   CALCULATE PROMOS
========================================================= */

function calculatePromos()
{
    if (!selectedPrice) {
        return;
    }


    const itemInput =
        document.getElementById(
            'item_id'
        );


    if (!itemInput) {
        return;
    }


    const itemId =
        itemInput.value;


    if (!itemId) {
        return;
    }


    const promoList =
        document.getElementById(
            'promoList'
        );


    if (promoList) {

        promoList.innerHTML = `

            <div class="promo-empty">

                <i
                    class="
                        fa-solid
                        fa-spinner
                        fa-spin
                    "
                ></i>

                Menghitung promo...

            </div>

        `;

    }


    /* -----------------------------------------------------
       CSRF
    ----------------------------------------------------- */

    const csrf =
        document.querySelector(
            'meta[name="csrf-token"]'
        );


    if (!csrf) {

        console.error(
            'CSRF token tidak ditemukan.'
        );

        return;

    }


    /* -----------------------------------------------------
       REQUEST
    ----------------------------------------------------- */

    fetch(
        gameConfig.voucherCalculateUrl,
        {
            method: 'POST',

            headers: {

                'Content-Type':
                    'application/json',

                'Accept':
                    'application/json',

                'X-CSRF-TOKEN':
                    csrf.content

            },

            body: JSON.stringify({

                subtotal:
                    selectedPrice,

                game_id:
                    gameConfig.gameId,

                item_id:
                    itemId,

                payment_id:
                    selectedPaymentId,

                voucher_code:
                    selectedVoucher

            })

        }
    )

    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                'Gagal menghitung promo'
            );

        }

        return response.json();

    })

    .then(function(data) {

        appliedPromos =
            data.discounts || [];


        renderPromoResult(
            data
        );

    })

    .catch(function(error) {

        console.error(
            'Promo Error:',
            error
        );


        appliedPromos = [];


        if (promoList) {

            promoList.innerHTML = `

                <div class="text-danger small">

                    <i
                        class="
                            fa-solid
                            fa-circle-exclamation
                            me-1
                        "
                    ></i>

                    Gagal memuat promo.

                </div>

            `;

        }

    });
}


/* =========================================================
   RENDER PROMO
========================================================= */

function renderPromoResult(data)
{
    const promoList =
        document.getElementById(
            'promoList'
        );


    if (!promoList) {
        return;
    }


    let html = '';


    /* -----------------------------------------------------
       NO PROMO
    ----------------------------------------------------- */

    if (
        !data.discounts ||
        data.discounts.length === 0
    ) {

        html = `

            <div class="promo-empty">

                <i
                    class="
                        fa-solid
                        fa-tag
                        me-1
                    "
                ></i>

                Tidak ada promo yang digunakan.

            </div>

        `;

    }


    /* -----------------------------------------------------
       PROMO
    ----------------------------------------------------- */

    else {

        data.discounts.forEach(
            function(promo, index) {

                const promoName =
                    escapeHtml(
                        promo.name ||
                        'Promo'
                    );


                const promoCode =
                    promo.code
                        ? escapeHtml(
                            promo.code
                        )
                        : escapeHtml(
                            promo.trigger_type ||
                            'Promo'
                        );


                const promoDiscount =
                    parseFloat(
                        promo.discount ??
                        promo.amount ??
                        0
                    );


                html += `

                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-center
                            border-bottom
                            pb-2
                            mb-2
                        "
                    >

                        <div>

                            <div class="fw-semibold">

                                <span
                                    class="
                                        badge
                                        bg-success
                                        me-1
                                    "
                                >
                                    ${index + 1}
                                </span>

                                ${promoName}

                            </div>


                            <small class="text-muted">

                                ${promoCode}

                            </small>

                        </div>


                        <div
                            class="
                                text-danger
                                fw-semibold
                            "
                        >

                            - Rp
                            ${formatRupiah(
                                promoDiscount
                            )}

                        </div>

                    </div>

                `;

            }
        );


        /* -------------------------------------------------
           TOTAL DISCOUNT
        ------------------------------------------------- */

        html += `

            <div
                class="
                    d-flex
                    justify-content-between
                    mt-3
                "
            >

                <strong>
                    Total Diskon
                </strong>

                <strong class="text-danger">

                    - Rp
                    ${formatRupiah(
                        data.discount_total || 0
                    )}

                </strong>

            </div>

        `;

    }


    promoList.innerHTML =
        html;


    /* -----------------------------------------------------
       DISCOUNT TOTAL
    ----------------------------------------------------- */

    const discountTotal =
        parseFloat(
            data.discount_total || 0
        );


    /* -----------------------------------------------------
       TOTAL
    ----------------------------------------------------- */

    const total =
        parseFloat(
            data.total ?? selectedPrice
        );


    /* -----------------------------------------------------
       UPDATE DISCOUNT
    ----------------------------------------------------- */

    const discountPrice =
        document.getElementById(
            'discount_price'
        );


    if (discountPrice) {

        discountPrice.innerText =
            formatRupiah(
                discountTotal
            );

    }


    /* -----------------------------------------------------
       UPDATE TOTAL
    ----------------------------------------------------- */

    const finalPrice =
        document.getElementById(
            'final_price'
        );


    if (finalPrice) {

        finalPrice.innerText =
            formatRupiah(
                total
            );

    }


    /* -----------------------------------------------------
       HIDDEN DISCOUNT
    ----------------------------------------------------- */

    const discountInput =
        document.getElementById(
            'discount_total'
        );


    if (discountInput) {

        discountInput.value =
            discountTotal;

    }


    /* -----------------------------------------------------
       HIDDEN TOTAL
    ----------------------------------------------------- */

    const totalInput =
        document.getElementById(
            'total_price'
        );


    if (totalInput) {

        totalInput.value =
            total;

    }


    /* -----------------------------------------------------
       DISCOUNT IDS
    ----------------------------------------------------- */

    const discountIds =
        document.getElementById(
            'discount_ids'
        );


    if (discountIds) {

        discountIds.value =
            (data.discounts || [])
                .map(function(promo) {

                    return promo.id;

                })
                .filter(Boolean)
                .join(',');

    }


    /* -----------------------------------------------------
       VOUCHER MESSAGE
    ----------------------------------------------------- */

    const voucherMessage =
        document.getElementById(
            'voucher_message'
        );


    if (
        voucherMessage &&
        selectedVoucher
    ) {

        const voucherPromo =
            (data.discounts || [])
                .find(function(promo) {

                    return (
                        promo.trigger_type ===
                        'voucher'
                    );

                });


        if (voucherPromo) {

            voucherMessage.innerHTML = `

                <span class="text-success">

                    <i
                        class="
                            fa-solid
                            fa-circle-check
                            me-1
                        "
                    ></i>

                    Voucher berhasil digunakan.

                </span>

            `;

        } else {

            voucherMessage.innerHTML = `

                <span class="text-danger">

                    <i
                        class="
                            fa-solid
                            fa-circle-xmark
                            me-1
                        "
                    ></i>

                    Voucher tidak berlaku.

                </span>

            `;

        }

    }
}


/* =========================================================
   FORM VALIDATION + PLAYER CONFIRMATION
========================================================= */

const checkoutForm =
    document.getElementById(
        'checkoutForm'
    );


if (checkoutForm) {

    checkoutForm.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            /* =================================================
               ANTI DOUBLE SUBMIT
            ================================================= */

            if (checkoutProcessing) {
                return;
            }


            /*
             * LOCK SEJAK AWAL.
             *
             * Ini penting agar ketika user klik tombol
             * Beli Sekarang dua kali dengan cepat,
             * SweetAlert tidak muncul dua kali.
             */

            checkoutProcessing = true;


            const submitButton =
                checkoutForm.querySelector(
                    '.checkout-submit'
                );


            if (submitButton) {

                submitButton.disabled = true;

                submitButton.dataset.originalText =
                    submitButton.innerHTML;

                submitButton.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

            }


            try {

                /* =============================================
                   1. CEK PRODUK
                ============================================= */

                const itemId =
                    document.querySelector(
                        '#item_id'
                    )?.value?.trim() || '';


                if (!itemId) {

                    await Swal.fire({

                        icon: 'warning',

                        title:
                            'Pilih Produk',

                        text:
                            'Silakan pilih produk terlebih dahulu.',

                        confirmButtonText:
                            'OK'

                    });

                    return;

                }


                /* =============================================
                   2. CEK PAYMENT
                ============================================= */

                if (!selectedPaymentId) {

                    await Swal.fire({

                        icon: 'warning',

                        title:
                            'Pilih Pembayaran',

                        text:
                            'Silakan pilih metode pembayaran terlebih dahulu.',

                        confirmButtonText:
                            'OK'

                    });

                    return;

                }


                /* =============================================
                   3. AMBIL PLAYER FIELD
                ============================================= */

                const userIdInput =
                    document.querySelector(
                        '[data-moogold-field="user-id"]'
                    );


                const serverIdInput =
                    document.querySelector(
                        '[data-moogold-field="server-id"]'
                    );


                const userId =
                    userIdInput?.value?.trim() || '';


                const serverId =
                    serverIdInput?.value?.trim() || '';


                /* =============================================
                   4. CEK USER ID
                ============================================= */

                if (
                    userIdInput &&
                    !userId
                ) {

                    await Swal.fire({

                        icon: 'warning',

                        title:
                            'User ID Belum Diisi',

                        text:
                            'Silakan masukkan User ID terlebih dahulu.',

                        confirmButtonText:
                            'OK'

                    });

                    return;

                }


                /* =============================================
                   5. CEK SERVER ID
                ============================================= */

                if (
                    serverIdInput &&
                    serverIdInput.required &&
                    !serverId
                ) {

                    await Swal.fire({

                        icon: 'warning',

                        title:
                            'Server ID Belum Diisi',

                        text:
                            'Silakan masukkan Server ID terlebih dahulu.',

                        confirmButtonText:
                            'OK'

                    });

                    return;

                }


                /* =============================================
                6. VALIDASI + KONFIRMASI PLAYER MOO GOLD
                ============================================= */

                if (
                    userIdInput &&
                    playerValidation.requiresValidation
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Cek apakah UID + Server + Item yang sekarang
                    | sudah berhasil divalidasi
                    |--------------------------------------------------------------------------
                    */

                    const alreadyValidated =
                        playerValidation.valid === true
                        &&
                        String(
                            playerValidation.userId
                        ) === String(
                            userId
                        )
                        &&
                        String(
                            playerValidation.serverId || ''
                        ) === String(
                            serverId
                        )
                        &&
                        String(
                            playerValidation.itemId
                        ) === String(
                            itemId
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Jika belum tervalidasi, validasi sekarang
                    |--------------------------------------------------------------------------
                    */

                    if (!alreadyValidated) {

                        const validationResult =
                            await validatePlayerForCheckout(
                                itemId,
                                userId,
                                serverId
                            );


                        if (
                            validationResult.status ===
                                'invalid'
                            ||
                            validationResult.status ===
                                'error'
                        ) {

                            return;

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | WAJIB KONFIRMASI USER
                    |
                    | Validasi berhasil BUKAN berarti user sudah
                    | mengonfirmasi data player.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !playerValidation.confirmed
                    ) {

                        const confirmed =
                            await showPlayerConfirmation();


                        /*
                        |--------------------------------------------------------------------------
                        | Jika klik BATAL:
                        |
                        | Jangan submit.
                        | confirmed tetap false.
                        |
                        | Jika user klik Beli Sekarang lagi,
                        | popup akan muncul lagi.
                        |--------------------------------------------------------------------------
                        */

                        if (!confirmed) {

                            playerValidation.confirmed =
                                false;

                            return;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | User klik LANJUTKAN
                        |--------------------------------------------------------------------------
                        */

                        playerValidation.confirmed =
                            true;

                    }

                }


                /* =============================================
                   7. SET VOUCHER
                ============================================= */

                const voucherInput =
                    document.querySelector(
                        '#voucher'
                    );


                if (voucherInput) {

                    voucherInput.value =
                        selectedVoucher || '';

                }


                /* =============================================
                   8. SUBMIT FORM ASLI
                ============================================= */

                HTMLFormElement.prototype.submit.call(
                    checkoutForm
                );

            }

            finally {

                /*
                 * Kalau masih berada di halaman ini,
                 * unlock kembali tombol.
                 *
                 * Kalau form berhasil submit,
                 * browser akan berpindah halaman.
                 */

                checkoutProcessing = false;


                if (submitButton) {

                    submitButton.disabled = false;


                    if (
                        submitButton.dataset.originalText
                    ) {

                        submitButton.innerHTML =
                            submitButton.dataset.originalText;

                    }

                }

            }

        }
    );

}


/* =========================================================
   PLAYER INPUT
========================================================= */

document
    .querySelectorAll(
        '.player-input'
    )
    .forEach(function(input) {

        const type =
            input.dataset.type;


        /* -------------------------------------------------
           NUMBER
        ------------------------------------------------- */

        if (type === 'number') {

            input.addEventListener(
                'input',
                function() {

                    this.value =
                        this.value.replace(
                            /[^0-9]/g,
                            ''
                        );

                }
            );

        }


        /* -------------------------------------------------
           EMAIL
        ------------------------------------------------- */

        if (type === 'email') {

            input.addEventListener(
                'input',
                function() {

                    this.value =
                        this.value.replace(
                            /\s/g,
                            ''
                        );

                }
            );

        }

    });


/* =========================================================
   INITIAL PAYMENT
========================================================= */

const initialPayment =
    document.querySelector(
        '.payment-radio:checked'
    );


if (initialPayment) {

    selectedPaymentId =
        parseInt(
            initialPayment.value
        );

}


/* =========================================================
   DEBUG
========================================================= */

console.log(
    'Game Show checkout aktif.'
);
