<div class="payment-box">

    <div class="checkout-section-title">

        <div class="checkout-section-icon">

            <i class="fa-solid fa-wallet"></i>

        </div>

        <div>

            <h3>
                Metode Pembayaran
            </h3>

            <p>
                Pilih metode pembayaran yang tersedia.
            </p>

        </div>

    </div>


    @php

        $paymentLabels = [

            'credit_card' => [
                'name' => 'Kartu Kredit',
                'type' => 'Credit Card',
                'icon' => 'fa-credit-card',
            ],

            'bca_va' => [
                'name' => 'BCA Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'bni_va' => [
                'name' => 'BNI Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'bri_va' => [
                'name' => 'BRI Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'bsi_va' => [
                'name' => 'BSI Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'cimb_va' => [
                'name' => 'CIMB Niaga Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'danamon_va' => [
                'name' => 'Danamon Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'permata_va' => [
                'name' => 'Permata Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'seabank_va' => [
                'name' => 'SeaBank Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'saqu_va' => [
                'name' => 'Bank Saqu Virtual Account',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'other_va' => [
                'name' => 'Virtual Account Lainnya',
                'type' => 'Virtual Account',
                'icon' => 'fa-building-columns',
            ],

            'echannel' => [
                'name' => 'Mandiri Bill Payment',
                'type' => 'Mandiri',
                'icon' => 'fa-building-columns',
            ],

            'gopay' => [
                'name' => 'GoPay',
                'type' => 'E-Wallet',
                'icon' => 'fa-wallet',
            ],

            'ovo' => [
                'name' => 'OVO',
                'type' => 'E-Wallet',
                'icon' => 'fa-wallet',
            ],

            'dana' => [
                'name' => 'DANA',
                'type' => 'E-Wallet',
                'icon' => 'fa-wallet',
            ],

            'shopeepay' => [
                'name' => 'ShopeePay',
                'type' => 'E-Wallet',
                'icon' => 'fa-wallet',
            ],

            'other_qris' => [
                'name' => 'QRIS',
                'type' => 'QR Payment',
                'icon' => 'fa-qrcode',
            ],

            'alfamart' => [
                'name' => 'Alfamart',
                'type' => 'Retail',
                'icon' => 'fa-store',
            ],

            'indomaret' => [
                'name' => 'Indomaret',
                'type' => 'Retail',
                'icon' => 'fa-store',
            ],

            'akulaku' => [
                'name' => 'Akulaku',
                'type' => 'PayLater',
                'icon' => 'fa-credit-card',
            ],

            'kredivo' => [
                'name' => 'Kredivo',
                'type' => 'PayLater',
                'icon' => 'fa-credit-card',
            ],

        ];

    @endphp


    <div class="payment-list">

        @forelse($paymentChannels as $channel)

            @php

                $code =
                    $channel['name'];

                $meta =
                    $paymentLabels[$code]
                    ?? [
                        'name' =>
                            ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $code
                                )
                            ),

                        'type' =>
                            'Midtrans',

                        'icon' =>
                            'fa-wallet',
                    ];

            @endphp


            <label
                class="payment-card"
                for="payment_{{ $code }}"
            >

                <input
                    type="radio"
                    id="payment_{{ $code }}"
                    class="payment-radio"
                    name="midtrans_payment_type"
                    value="{{ $code }}"
                    data-payment-type="{{ $code }}"
                    required
                >


                <div class="payment-check">

                    <i class="fa-solid fa-check"></i>

                </div>


                <div class="payment-image payment-placeholder">

                    <i
                        class="
                            fa-solid
                            {{ $meta['icon'] }}
                        "
                    ></i>

                </div>


                <div class="payment-info">

                    <h4>
                        {{ $meta['name'] }}
                    </h4>

                    <span>
                        {{ $meta['type'] }}
                    </span>

                    <small>
                        Midtrans
                    </small>

                </div>


                <div class="payment-price">

                    <small>
                        Total
                    </small>

                    <strong>

                        Rp
                        <span
                            class="payment-total"
                            data-payment-total="{{ $code }}"
                        >
                            0
                        </span>

                    </strong>

                </div>

            </label>

        @empty

            <div class="payment-empty">

                <i class="fa-solid fa-wallet"></i>

                <p>
                    Tidak ada metode pembayaran Midtrans
                    yang sedang aktif.
                </p>

            </div>

        @endforelse

    </div>

</div>