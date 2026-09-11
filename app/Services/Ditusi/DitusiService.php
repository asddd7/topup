<?php

namespace App\Services\Ditusi;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DitusiService
{
    protected string $baseUrl;

    protected string $clientId;

    protected string $clientKey;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config(
                'services.ditusi.base_url'
            ),
            '/'
        );

        $this->clientId = (string) config(
            'services.ditusi.client_id'
        );

        $this->clientKey = (string) config(
            'services.ditusi.client_key'
        );

        $this->timeout = (int) config(
            'services.ditusi.timeout',
            30
        );

        Log::info(
            'DITUSI API configuration check',
            [
                'base_url' =>
                    $this->baseUrl,

                'client_id_present' =>
                    $this->clientId !== '',

                'client_id_length' =>
                    strlen($this->clientId),

                'client_key_present' =>
                    $this->clientKey !== '',

                'client_key_length' =>
                    strlen($this->clientKey),
            ]
        );
    }

    /**
     * =========================================================
     * ACCESS TOKEN
     * =========================================================
     */
    protected function getAccessToken(): string
    {
        if ($this->clientId === '') {
            throw new RuntimeException(
                'DITUSI Client ID belum dikonfigurasi.'
            );
        }

        if ($this->clientKey === '') {
            throw new RuntimeException(
                'DITUSI Client Key belum dikonfigurasi.'
            );
        }

        return Cache::remember(
            'ditusi:access-token',
            now()->addMinutes(9),
            function () {

                /*
                |--------------------------------------------------------------------------
                | DITUSI menggunakan ISO 8601 timestamp
                |--------------------------------------------------------------------------
                */

                $timestamp =
                    now()->toIso8601String();

                /*
                |--------------------------------------------------------------------------
                | ACCESS TOKEN SIGNATURE
                |--------------------------------------------------------------------------
                |
                | sha256(clientKey:timestamp)
                |--------------------------------------------------------------------------
                */

                $signature =
                    hash(
                        'sha256',
                        $this->clientKey .
                        ':' .
                        $timestamp
                    );

                $url = 'https://api.ditusi.co.id/api/v1/access-token';

                Log::info(
                    'DITUSI access token request',
                    [
                        'url' =>
                            $url,

                        'client_id' =>
                            $this->clientId,

                        'timestamp' =>
                            $timestamp,
                    ]
                );

                $response =
                    Http::timeout(
                        $this->timeout
                    )
                    ->withHeaders([
                        'X-CLIENT-ID' =>
                            $this->clientId,

                        'X-TIMESTAMP' =>
                            $timestamp,

                        'X-SIGNATURE' =>
                            $signature,

                        'Content-Type' =>
                            'application/json',

                        'Accept' =>
                            'application/json',
                    ])
                    ->get(
                        $url
                    );

                Log::info(
                    'DITUSI access token response',
                    [
                        'status' =>
                            $response->status(),

                        'body' =>
                            $response->body(),
                    ]
                );

                if ($response->failed()) {
                    throw new RuntimeException(
                        'DITUSI access token gagal. HTTP ' .
                        $response->status() .
                        ': ' .
                        $response->body()
                    );
                }

                $result =
                    $response->json();

                if (!is_array($result)) {
                    throw new RuntimeException(
                        'Response access token DITUSI tidak valid.'
                    );
                }

                $token =
                    $result['accessToken']
                    ?? $result['data']['accessToken']
                    ?? null;

                if (
                    !is_string($token) ||
                    $token === ''
                ) {
                    throw new RuntimeException(
                        'Access token DITUSI tidak ditemukan.'
                    );
                }

                return $token;
            }
        );
    }

public function games(?string $gameCode = null): array
{
    $query = [];

    if ($gameCode !== null && $gameCode !== '') {
        $query['gameCode'] = $gameCode;
    }

$path = '/api/dev/v1/game';

$timestamp = now()->toIso8601String();

$json = empty($query)
    ? '{}'
    : json_encode(
        $query,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

$signatureString =
    $path .
    ':' .
    $this->clientKey .
    ':' .
    $timestamp .
    ':' .
    $json;

$signature = hash(
    'sha256',
    $signatureString
);

    $token = $this->getAccessToken();

    $url = $this->baseUrl . '/game';

    Log::info('DITUSI game request', [
        'url' => $url,
        'path' => $path,
        'query' => $query,
        'json_for_signature' => $json,
        'timestamp' => $timestamp,
        'signature_string' => $signatureString,
        'signature' => $signature,
    ]);

    $response = Http::timeout($this->timeout)
        ->withHeaders([
            'X-CLIENT-ID' => $this->clientId,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->get($url, $query);

    Log::info('DITUSI game response', [
        'status' => $response->status(),
        'body' => $response->body(),
    ]);

    if ($response->failed()) {
        throw new RuntimeException(
            'DITUSI game API error HTTP ' .
            $response->status() .
            ': ' .
            $response->body()
        );
    }

    $result = $response->json();

    if (!is_array($result)) {
        throw new RuntimeException(
            'Response game DITUSI tidak valid.'
        );
    }

    return $result;
}

    /**
     * =========================================================
     * PRODUCT LIST
     * =========================================================
     *
     * GET /api/dev/v1/product
     *
     * Minimal:
     *
     * gameCode
     * atau
     * productCode
     */
    public function products(
        ?string $gameCode = null,
        ?string $productCode = null
    ): array {

        if (
            empty($gameCode) &&
            empty($productCode)
        ) {
            throw new RuntimeException(
                'DITUSI product membutuhkan gameCode atau productCode.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

        $query = [];

        if (
            $gameCode !== null &&
            $gameCode !== ''
        ) {
            $query['gameCode'] =
                $gameCode;
        }

        if (
            $productCode !== null &&
            $productCode !== ''
        ) {
            $query['productCode'] =
                $productCode;
        }

        /*
        |--------------------------------------------------------------------------
        | FULL DITUSI PATH
        |--------------------------------------------------------------------------
        |
        | Dokumentasi DITUSI meminta PATH lengkap:
        |
        | /api/dev/v1/product
        |--------------------------------------------------------------------------
        */

        $path =
            parse_url(
                $this->baseUrl,
                PHP_URL_PATH
            );

        $path =
            rtrim(
                (string) $path,
                '/'
            ) .
            '/product';

        /*
        |--------------------------------------------------------------------------
        | TIMESTAMP
        |--------------------------------------------------------------------------
        */

        $timestamp =
            now()->toIso8601String();

        /*
        |--------------------------------------------------------------------------
        | JSON BODY UNTUK SIGNATURE
        |--------------------------------------------------------------------------
        */

        $json =
            json_encode(
                $query,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            );

        if ($json === false) {
            throw new RuntimeException(
                'Gagal membuat JSON signature DITUSI.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        |
        | sha256(
        |     PATH:
        |     clientKey:
        |     timestamp:
        |     JSON_BODY
        | )
        |--------------------------------------------------------------------------
        */

        $signature =
            hash(
                'sha256',
                $path .
                ':' .
                $this->clientKey .
                ':' .
                $timestamp .
                ':' .
                $json
            );

        /*
        |--------------------------------------------------------------------------
        | TOKEN
        |--------------------------------------------------------------------------
        */

        $token =
            $this->getAccessToken();

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        $url =
            $this->baseUrl .
            '/product';

        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'DITUSI product request',
            [
                'url' =>
                    $url,

                'path' =>
                    $path,

                'query' =>
                    $query,

                'json_for_signature' =>
                    $json,

                'timestamp' =>
                    $timestamp,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */

        $response =
            Http::timeout(
                $this->timeout
            )
            ->withHeaders([
                'X-CLIENT-ID' =>
                    $this->clientId,

                'X-TIMESTAMP' =>
                    $timestamp,

                'X-SIGNATURE' =>
                    $signature,

                'Authorization' =>
                    'Bearer ' . $token,

                'Content-Type' =>
                    'application/json',

                'Accept' =>
                    'application/json',
            ])
            ->get(
                $url,
                $query
            );

        /*
        |--------------------------------------------------------------------------
        | RESPONSE LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'DITUSI product response',
            [
                'status' =>
                    $response->status(),

                'body' =>
                    $response->body(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        if ($response->failed()) {

            throw new RuntimeException(
                'DITUSI product API error HTTP ' .
                $response->status() .
                ': ' .
                $response->body()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */

        $result =
            $response->json();

        if (!is_array($result)) {
            throw new RuntimeException(
                'Response product DITUSI tidak valid.'
            );
        }

        return $result;
    }

    public function createTransaction(  
        string $productCode,
        int $amount,
        string $transactionReferenceId,
        array $formDetails,
        ?int $initialPrice = null,
        ?string $endUserIpAddress = null,
    ): array {
        $path = '/api/dev/v1/transaction';
        $timestamp = now()->toIso8601String();

        $body = [
            'productCode' => $productCode,
            'amount' => $amount,
            'transactionReferenceId' => $transactionReferenceId,
            'formDetails' => $formDetails,
        ];

        if ($initialPrice !== null) {
            $body['initialPrice'] = $initialPrice;
        }

        if ($endUserIpAddress !== null && $endUserIpAddress !== '') {
            $body['endUserIpAddress'] = $endUserIpAddress;
        }

        $json = json_encode(
            $body,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            throw new RuntimeException(
                'Gagal membuat JSON signature DITUSI.'
            );
        }

        $signatureString =
            $path .
            ':' .
            $this->clientKey .
            ':' .
            $timestamp .
            ':' .
            $json;

        $signature = hash('sha256', $signatureString);

        $token = $this->getAccessToken();

        $url = $this->baseUrl . '/transaction';

        Log::info('DITUSI transaction request', [
            'url' => $url,
            'path' => $path,
            'body' => $body,
            'json_for_signature' => $json,
            'timestamp' => $timestamp,
            'signature_string' => $signatureString,
            'signature' => $signature,
        ]);

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'X-CLIENT-ID' => $this->clientId,
                'X-TIMESTAMP' => $timestamp,
                'X-SIGNATURE' => $signature,
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, $body);

        Log::info('DITUSI transaction response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'DITUSI transaction API error HTTP ' .
                $response->status() .
                ': ' .
                $response->body()
            );
        }

        $result = $response->json();

        if (!is_array($result)) {
            throw new RuntimeException(
                'Response transaction DITUSI tidak valid.'
            );
        }

        return $result;
    }

public function getTransactionStatus(
    string $transactionId
): array {
    $transactionId = trim($transactionId);

    if ($transactionId === '') {
        throw new RuntimeException(
            'Transaction ID DITUSI tidak boleh kosong.'
        );
    }

    $path = '/api/dev/v1/transaction/' . $transactionId;
    $timestamp = now()->toIso8601String();

    // GET tanpa query menggunakan {}
    $json = '{}';

    $signatureString =
        $path . ':' .
        $this->clientKey . ':' .
        $timestamp . ':' .
        $json;

    $signature = hash(
        'sha256',
        $signatureString
    );

    $token = $this->getAccessToken();

    $url = $this->baseUrl .
        '/transaction/' .
        urlencode($transactionId);

    Log::info('DITUSI transaction status request', [
        'url' => $url,
        'path' => $path,
        'transaction_id' => $transactionId,
        'timestamp' => $timestamp,
        'signature_string' => $signatureString,
        'signature' => $signature,
    ]);

    $response = Http::timeout($this->timeout)
        ->withHeaders([
            'X-CLIENT-ID' => $this->clientId,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->get($url);

    Log::info('DITUSI transaction status response', [
        'status' => $response->status(),
        'body' => $response->body(),
    ]);

    if ($response->failed()) {
        throw new RuntimeException(
            'DITUSI transaction status API error HTTP ' .
            $response->status() . ': ' .
            $response->body()
        );
    }

    $result = $response->json();

    if (!is_array($result)) {
        throw new RuntimeException(
            'Response transaction status DITUSI tidak valid.'
        );
    }

    return $result;
}
}