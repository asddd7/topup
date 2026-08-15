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


    <div class="payment-list">

        @forelse($payments as $payment)

            <label
                class="payment-card"
                for="payment_{{ $payment->id }}"
            >

                <input
                    type="radio"
                    id="payment_{{ $payment->id }}"
                    class="payment-radio"
                    name="payment_id"
                    value="{{ $payment->id }}"
                    required
                >


                <div class="payment-check">

                    <i class="fa-solid fa-check"></i>

                </div>


                @if($payment->image)

                    <div class="payment-image">

                        <img
                            src="{{ asset('storage/'.$payment->image) }}"
                            alt="{{ $payment->payment_name }}"
                        >

                    </div>

                @else

                    <div class="payment-image payment-placeholder">

                        <i class="fa-solid fa-wallet"></i>

                    </div>

                @endif


                <div class="payment-info">

                    <h4>
                        {{ $payment->payment_name }}
                    </h4>

                    <span>
                        {{ $payment->payment_type }}
                    </span>

                    <strong>
                        {{ $payment->payment_number }}
                    </strong>

                    <small>
                        a.n {{ $payment->account_name }}
                    </small>

                </div>

            </label>

        @empty

            <div class="payment-empty">

                <i class="fa-solid fa-wallet"></i>

                <p>
                    Metode pembayaran belum tersedia.
                </p>

            </div>

        @endforelse

    </div>

</div>