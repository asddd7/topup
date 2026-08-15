/* =========================================================
   GAME SHOW
========================================================= */

let selectedPrice = 0;

let selectedPaymentId = null;

let selectedVoucher = null;

let appliedPromos = [];


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
   SELECT ITEM
========================================================= */

function selectItem(id, name, price)
{
    selectedPrice =
        parseFloat(price) || 0;


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
       ORIGINAL PRICE
    ----------------------------------------------------- */

    const originalPrice =
        document.getElementById('original_price');

    if (originalPrice) {

        originalPrice.innerText =
            formatRupiah(selectedPrice);

    }


    /* -----------------------------------------------------
       DISCOUNT
    ----------------------------------------------------- */

    const discountPrice =
        document.getElementById('discount_price');

    if (discountPrice) {

        discountPrice.innerText =
            '0';

    }


    /* -----------------------------------------------------
       FINAL PRICE
    ----------------------------------------------------- */

    const finalPrice =
        document.getElementById('final_price');

    if (finalPrice) {

        finalPrice.innerText =
            formatRupiah(selectedPrice);

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
}


/* =========================================================
   PAYMENT METHOD
========================================================= */

document
    .querySelectorAll('.payment-radio')
    .forEach(function(payment) {

        payment.addEventListener(
            'change',
            function() {

                selectedPaymentId =
                    parseInt(this.value) || null;

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
        document.getElementById('code');


    if (!codeInput) {
        return;
    }


    const code =
        codeInput.value.trim();


    if (code === '') {

        selectedVoucher = null;

    } else {

        selectedVoucher = code;

    }


    const voucherInput =
        document.getElementById('voucher');


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
        document.getElementById('code');

    if (codeInput) {

        codeInput.value =
            '';

    }


    const voucherInput =
        document.getElementById('voucher');

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
        document.getElementById('item_id');


    if (!itemInput) {
        return;
    }


    const itemId =
        itemInput.value;


    if (!itemId) {
        return;
    }


    const promoList =
        document.getElementById('promoList');


    if (promoList) {

        promoList.innerHTML = `

            <div class="promo-empty">

                <i class="fa-solid fa-spinner fa-spin"></i>

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


        renderPromoResult(data);

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

                <i class="fa-solid fa-tag me-1"></i>

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
   FORM VALIDATION
========================================================= */

const checkoutForm =
    document.getElementById(
        'checkoutForm'
    );


if (checkoutForm) {

    checkoutForm.addEventListener(
        'submit',
        function(event) {

            /* ---------------------------------------------
               ITEM
            --------------------------------------------- */

            const itemId =
                document.getElementById(
                    'item_id'
                )?.value;


            if (!itemId) {

                event.preventDefault();

                alert(
                    'Silakan pilih nominal top up terlebih dahulu.'
                );

                return;

            }


            /* ---------------------------------------------
               PAYMENT
            --------------------------------------------- */

            const paymentSelected =
                document.querySelector(
                    '.payment-radio:checked'
                );


            if (!paymentSelected) {

                event.preventDefault();

                alert(
                    'Silakan pilih metode pembayaran.'
                );

                return;

            }


            selectedPaymentId =
                parseInt(
                    paymentSelected.value
                );


            /* ---------------------------------------------
               VOUCHER
            --------------------------------------------- */

            const voucherInput =
                document.getElementById(
                    'voucher'
                );


            if (voucherInput) {

                voucherInput.value =
                    selectedVoucher ?? '';

            }

        }
    );

}


/* =========================================================
   PLAYER INPUT
========================================================= */

document
    .querySelectorAll('.player-input')
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