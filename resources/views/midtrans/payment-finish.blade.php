<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pembayaran Berhasil</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);
        }

        .icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f7ee;
            color: #16a34a;
            font-size: 38px;
            font-weight: bold;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 26px;
        }

        .message {
            margin: 0 0 24px;
            color: #666;
            line-height: 1.6;
        }

        .info {
            text-align: left;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 16px 0;
            margin-bottom: 24px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 8px 0;
        }

        .label {
            color: #777;
        }

        .value {
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 13px 18px;
            border-radius: 10px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="card">

    <div class="icon">
        ✓
    </div>

    <h1>
        Pembayaran Selesai
    </h1>

    <p class="message">
        Terima kasih. Kamu telah menyelesaikan proses pembayaran.
        Sistem sedang melakukan konfirmasi pembayaran.
    </p>

    <div class="info">

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
                Payment Attempt
            </span>

            <span class="value">
                MT{{ $transaction->attempt_number }}
            </span>
        </div>

        <div class="row">
            <span class="label">
                Midtrans Order ID
            </span>

            <span class="value">
                {{ $midtransOrderId }}
            </span>
        </div>

        <div class="row">
            <span class="label">
                Status Midtrans
            </span>

            <span class="value">
                {{ $transactionStatus ?? 'Menunggu konfirmasi' }}
            </span>
        </div>

        <div class="row">
            <span class="label">
                Status Order
            </span>

            <span class="value">
                {{ $order->status }}
            </span>
        </div>

    </div>

    <a
        href="{{ url('/') }}"
        class="btn"
    >
        Kembali ke Beranda
    </a>

</div>

</body>
</html>