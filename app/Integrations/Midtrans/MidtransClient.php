<?php

namespace App\Integrations\Midtrans;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;
use Throwable;

class MidtransClient
{
    protected string $serverKey;

    protected string $clientKey;

    protected bool $isProduction;

    protected int $timeout;

    protected string $account;

    public function __construct(
        ?string $serverKey = null,
        ?string $clientKey = null,
        ?bool $isProduction = null,
        ?int $timeout = null,
        ?string $account = null
    ) {
        $accountName =
            $account
                ?? config(
                    'midtrans.default_account',
                    'primary'
                );

        $accountConfig =
            config(
                'midtrans.accounts.' .
                $accountName
            );

        if (!is_array($accountConfig)) {

            throw new RuntimeException(
                "Midtrans account [{$accountName}] tidak ditemukan."
            );

        }

        $this->serverKey =
            (string) (
                $serverKey
                    ?? ($accountConfig['server_key'] ?? '')
            );

        $this->clientKey =
            (string) (
                $clientKey
                    ?? ($accountConfig['client_key'] ?? '')
            );

        $this->isProduction =
            (bool) (
                $isProduction
                    ?? ($accountConfig['is_production'] ?? false)
            );

        $this->timeout =
            (int) (
                $timeout
                    ?? config(
                        'midtrans.timeout',
                        15
                    )
            );

        $this->account =
            $accountName;

        $this->configureSdk();
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURE SDK
    |--------------------------------------------------------------------------
    */

    protected function configureSdk(): void
    {
        Config::$serverKey =
            $this->serverKey;

        Config::$isProduction =
            $this->isProduction;

        Config::$isSanitized =
            true;

        Config::$is3ds =
            true;
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE SNAP TOKEN
    |--------------------------------------------------------------------------
    */

    public function createSnapToken(
        array $params
    ): string {

        $this->ensureConfigured();

        $this->configureSdk();


        try {

            $snapToken =
                Snap::getSnapToken(
                    $params
                );


            if (
                empty($snapToken)
            ) {

                throw new RuntimeException(
                    'Midtrans tidak mengembalikan Snap Token.'
                );

            }


            Log::info(
                'Midtrans Snap token berhasil dibuat.',
                [
                    'order_id' =>
                        data_get(
                            $params,
                            'transaction_details.order_id'
                        ),

                    'gross_amount' =>
                        data_get(
                            $params,
                            'transaction_details.gross_amount'
                        ),
                ]
            );


            return $snapToken;

        } catch (Throwable $e) {

            Log::error(
                'Gagal membuat Midtrans Snap token.',
                [
                    'order_id' =>
                        data_get(
                            $params,
                            'transaction_details.order_id'
                        ),

                    'error' =>
                        $e->getMessage(),
                ]
            );


            if (
                $e instanceof RuntimeException
            ) {

                throw $e;

            }


            throw new RuntimeException(
                'Gagal membuat Snap token Midtrans.',
                previous: $e
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SNAP REDIRECT URL
    |--------------------------------------------------------------------------
    */

    public function getSnapRedirectUrl(
        array $params
    ): string {

        $this->ensureConfigured();

        $this->configureSdk();


        try {

            $redirectUrl =
                Snap::getSnapUrl(
                    $params
                );


            if (
                empty($redirectUrl)
            ) {

                throw new RuntimeException(
                    'Midtrans tidak mengembalikan Snap Redirect URL.'
                );

            }


            return $redirectUrl;

        } catch (Throwable $e) {

            Log::error(
                'Gagal membuat Midtrans Snap redirect URL.',
                [
                    'order_id' =>
                        data_get(
                            $params,
                            'transaction_details.order_id'
                        ),

                    'error' =>
                        $e->getMessage(),
                ]
            );


            if (
                $e instanceof RuntimeException
            ) {

                throw $e;

            }


            throw new RuntimeException(
                'Gagal membuat Snap redirect Midtrans.',
                previous: $e
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSACTION STATUS
    |--------------------------------------------------------------------------
    */

    public function getTransactionStatus(
        string $orderId
    ): ?array {

        $this->ensureConfigured();

        $this->configureSdk();


        try {

            $response =
                Transaction::status(
                    $orderId
                );


            Log::info(
                'Midtrans transaction status berhasil diambil.',
                [
                    'order_id' =>
                        $orderId,

                    'transaction_status' =>
                        $response->transaction_status
                        ?? null,
                ]
            );


            return (array) $response;

        } catch (Throwable $e) {

            $message =
                $e->getMessage();

            $code =
                (int) $e->getCode();


            $messageLower =
                strtolower(
                    $message
                );


            $isNotFound =
                $code === 404
                ||
                str_contains(
                    $messageLower,
                    '404'
                )
                ||
                str_contains(
                    $messageLower,
                    'transaction not found'
                )
                ||
                str_contains(
                    $messageLower,
                    'not found'
                );


            if ($isNotFound) {

                Log::info(
                    'Midtrans transaction belum ditemukan.',
                    [
                        'order_id' =>
                            $orderId,

                        'http_code' =>
                            $code,

                        'error' =>
                            $message,
                    ]
                );


                return null;
            }


            Log::error(
                'Gagal mengambil Midtrans transaction status.',
                [
                    'order_id' =>
                        $orderId,

                    'error_code' =>
                        $code,

                    'error' =>
                        $message,

                    'exception' =>
                        get_class($e),
                ]
            );


            throw new RuntimeException(
                'Gagal mengambil status transaksi Midtrans: ' .
                $message,
                previous: $e
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CLIENT KEY
    |--------------------------------------------------------------------------
    */

    public function clientKey(): ?string
    {
        return $this->clientKey !== ''
            ? $this->clientKey
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | ENVIRONMENT
    |--------------------------------------------------------------------------
    */

    public function isProduction(): bool
    {
        return $this->isProduction;
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY WEBHOOK SIGNATURE
    |--------------------------------------------------------------------------
    */

    public function verifySignature(
        array $payload
    ): bool {

        $orderId =
            (string) data_get(
                $payload,
                'order_id'
            );

        $statusCode =
            (string) data_get(
                $payload,
                'status_code'
            );

        $grossAmount =
            (string) data_get(
                $payload,
                'gross_amount'
            );

        $signatureKey =
            (string) data_get(
                $payload,
                'signature_key'
            );


        if (
            $orderId === ''
            ||
            $statusCode === ''
            ||
            $grossAmount === ''
            ||
            $signatureKey === ''
        ) {

            Log::warning(
                'Midtrans signature tidak dapat diverifikasi karena field tidak lengkap.',
                [
                    'has_order_id' =>
                        $orderId !== '',

                    'has_status_code' =>
                        $statusCode !== '',

                    'has_gross_amount' =>
                        $grossAmount !== '',

                    'has_signature_key' =>
                        $signatureKey !== '',
                ]
            );


            return false;
        }


        $expectedSignature =
            hash(
                'sha512',
                $orderId .
                $statusCode .
                $grossAmount .
                $this->serverKey
            );


        $isValid =
            hash_equals(
                $expectedSignature,
                $signatureKey
            );


        Log::info(
            'Midtrans signature verification.',
            [
                'order_id' =>
                    $orderId,

                'status_code' =>
                    $statusCode,

                'gross_amount' =>
                    $grossAmount,

                'server_key_present' =>
                    $this->serverKey !== '',

                'is_production' =>
                    $this->isProduction,

                'signature_valid' =>
                    $isValid,

                'expected_signature_prefix' =>
                    substr(
                        $expectedSignature,
                        0,
                        12
                    ),

                'received_signature_prefix' =>
                    substr(
                        $signatureKey,
                        0,
                        12
                    ),
            ]
        );


        return $isValid;
    }


    /*
    |--------------------------------------------------------------------------
    | SNAP PAYMENT CHANNELS
    |--------------------------------------------------------------------------
    */

    public function getSnapPaymentChannels(): array
    {
        $cacheKey =
            'midtrans:snap-payment-channels:' .
            sha1(
                $this->serverKey
            );


        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () {

                $this->ensureConfigured();


                $baseUrl =
                    $this->isProduction
                        ? 'https://app.midtrans.com'
                        : 'https://app.sandbox.midtrans.com';


                $response =
                    Http::withBasicAuth(
                        $this->serverKey,
                        ''
                    )
                    ->acceptJson()
                    ->timeout(15)
                    ->get(
                        $baseUrl .
                        '/snap/v3/merchant-preferences'
                    );


                if (
                    !$response->successful()
                ) {

                    throw new RuntimeException(
                        'Gagal mengambil payment channels dari Midtrans.'
                    );

                }


                $channels =
                    $response->json(
                        'payment_channels'
                    );


                if (
                    !is_array($channels)
                ) {

                    return [];
                }


                return collect(
                    $channels
                )
                ->filter(
                    function ($channel) {

                        return
                            is_array($channel)
                            &&
                            !empty(
                                $channel['name']
                            )
                            &&
                            (
                                $channel['enabled']
                                ?? false
                            ) === true;

                    }
                )
                ->map(
                    function ($channel) {

                        return [

                            'name' =>
                                (string)
                                $channel['name'],

                            'enabled' =>
                                true,

                        ];

                    }
                )
                ->values()
                ->all();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    protected function ensureConfigured(): void
    {
        if (
            $this->serverKey === ''
        ) {

            throw new RuntimeException(
                'MIDTRANS_SERVER_KEY belum dikonfigurasi.'
            );

        }
    }
}