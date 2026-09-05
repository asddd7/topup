<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Midtrans Sandbox Payment
    </title>

    <style>
        body {
            margin: 0;
            padding: 40px 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
        }

        .card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .label {
            color: #666;
        }

        .value {
            font-weight: 600;
            text-align: right;
        }

        .amount {
            font-size: 24px;
            margin: 20px 0;
        }

        button {
            width: 100%;
            border: 0;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .info {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            background: #f0f7ff;
            font-size: 14px;
            color: #345;
        }
    </style>

    @if (!$isProduction)
        <script
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ $clientKey }}"
        ></script>
    @else
        <script
            src="https://app.midtrans.com/snap/snap.js"
            data-client-key="{{ $clientKey }}"
        ></script>
    @endif
</head>

<body>

<div class="container">

    <div class="card">

        <h1>
            Midtrans Sandbox
        </h1>

        <div class="row">
            <span class="label">
                Order ID
            </span>

            <span class="value">
                {{ $order->id }}
            </span>
        </div>

        <div class="row">
            <span class="label">
                Invoice
            </span>

            <span class="value">
                {{ $order->invoice_number }}
            </span>
        </div>

        <div class="row">
            <span class="label">
                Status
            </span>

            <span class="value">
                {{ $order->status }}
            </span>
        </div>

        <div class="row">
            <span class="label">
                Midtrans Order ID
            </span>

            <span class="value">
                {{ $transaction->midtrans_order_id }}
            </span>
        </div>

        <div class="amount">
            Rp {{ number_format(
                (float) $transaction->gross_amount,
                2,
                ',',
                '.'
            ) }}
        </div>

        <button
            type="button"
            id="pay-button"
        >
            Bayar dengan Midtrans Sandbox
        </button>

        <div class="info">
            Ini adalah halaman testing.
            Jangan gunakan data pembayaran nyata.
        </div>

    </div>

</div>

<script>
    const snapToken = @json(
        $transaction->snap_token
    );

    const payButton =
        document.getElementById('pay-button');

    payButton.addEventListener(
        'click',
        function () {

            if (!snapToken) {
                alert(
                    'Snap Token tidak tersedia.'
                );

                return;
            }

            payButton.disabled = true;

            window.snap.pay(
                snapToken,
                {
                    onSuccess: function (result) {

                        console.log(
                            'Midtrans success:',
                            result
                        );

                        alert(
                            'Pembayaran Sandbox berhasil.'
                        );

                        window.location.reload();
                    },

                    onPending: function (result) {

                        console.log(
                            'Midtrans pending:',
                            result
                        );

                        alert(
                            'Pembayaran masih pending.'
                        );

                        payButton.disabled = false;
                    },

                    onError: function (result) {

                        console.error(
                            'Midtrans error:',
                            result
                        );

                        alert(
                            'Pembayaran gagal.'
                        );

                        payButton.disabled = false;
                    },

                    onClose: function () {

                        console.log(
                            'Popup Midtrans ditutup.'
                        );

                        payButton.disabled = false;
                    }
                }
            );
        }
    );
</script>

</body>
</html>