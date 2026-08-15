<section
    class="game-checkout-section"
    id="checkout"
>

    <div class="container">

        <div class="checkout-card">

            {{-- =================================================
                 HEADER
            ================================================== --}}
            <div class="checkout-header">

                <div>

                    <span class="checkout-eyebrow">
                        CHECKOUT
                    </span>

                    <h2>
                        Detail Pembelian
                    </h2>

                </div>

                <div class="checkout-icon">

                    <i class="fa-solid fa-cart-shopping"></i>

                </div>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}
            <div class="checkout-body">

                <form
                    action="{{ route('order.store') }}"
                    method="POST"
                    id="checkoutForm"
                >

                    @csrf


                    {{-- GAME ID --}}
                    <input
                        type="hidden"
                        name="game_id"
                        value="{{ $game->id }}"
                    >


                    {{-- =================================================
                         PLAYER FIELDS
                    ================================================== --}}
                    @include('game.partials.player-fields')


                    {{-- =================================================
                         VOUCHER
                    ================================================== --}}
                    @include('game.partials.voucher')


                    {{-- =================================================
                         PRICE SUMMARY
                    ================================================== --}}
                    <div class="price-summary">

                        <div class="price-row">

                            <span>
                                Item
                            </span>

                            <strong id="selected_item">
                                Belum dipilih
                            </strong>

                        </div>


                        <div class="price-row">

                            <span>
                                Harga
                            </span>

                            <strong>
                                Rp
                                <span id="original_price">
                                    0
                                </span>
                            </strong>

                        </div>


                        <div class="price-row">

                            <span>
                                Diskon
                            </span>

                            <strong class="discount-value">

                                - Rp
                                <span id="discount_price">
                                    0
                                </span>

                            </strong>

                        </div>


                        <div class="price-divider"></div>


                        <div class="price-row price-total">

                            <span>
                                Total
                            </span>

                            <strong>

                                Rp
                                <span id="final_price">
                                    0
                                </span>

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         PROMO
                    ================================================== --}}
                    @include('game.partials.promo')


                    {{-- =================================================
                         PAYMENT
                    ================================================== --}}
                    @include('game.partials.payments')


                    {{-- =================================================
                         HIDDEN INPUT
                    ================================================== --}}

                    <input
                        type="hidden"
                        name="item_id"
                        id="item_id"
                    >

                    <input
                        type="hidden"
                        name="subtotal"
                        id="original_subtotal"
                        value="0"
                    >

                    <input
                        type="hidden"
                        name="discount_total"
                        id="discount_total"
                        value="0"
                    >

                    <input
                        type="hidden"
                        name="total_price"
                        id="total_price"
                        value="0"
                    >

                    <input
                        type="hidden"
                        name="discount_ids"
                        id="discount_ids"
                        value=""
                    >

                    <input
                        type="hidden"
                        name="voucher"
                        id="voucher"
                        value=""
                    >


                    {{-- =================================================
                         SUBMIT
                    ================================================== --}}
                    <button
                        type="submit"
                        class="checkout-submit"
                    >

                        <i class="fa-solid fa-bolt"></i>

                        Beli Sekarang

                    </button>

                </form>

            </div>

        </div>

    </div>

</section>