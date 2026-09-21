<?php

namespace App\Integrations\Ditusi;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DitusiClient
{
    protected string $baseUrl;

    protected string $clientId;

    protected string $clientKey;

    protected int $timeout;

    protected string $accessTokenUrl;

    protected string $account;


    public function __construct(
        ?string $baseUrl = null,
        ?string $clientId = null,
        ?string $clientKey = null,
        ?int $timeout = null,
        ?string $accessTokenUrl = null,
        ?string $account = null
    ) {
        $this->baseUrl =
            rtrim(
                $baseUrl
                    ?? config('ditusi.base_url'),
                '/'
            );

        $accountName =
            $account
                ?? config(
                    'ditusi.default_account',
                    'primary'
                );

        $accountConfig =
            config(
                'ditusi.accounts.' .
                $accountName
            );

        if (!is_array($accountConfig)) {

            throw new RuntimeException(
                "DITUSI account [{$accountName}] tidak ditemukan."
            );

        }

        $this->clientId =
            (string) (
                $clientId
                    ?? ($accountConfig['client_id'] ?? '')
            );

        $this->clientKey =
            (string) (
                $clientKey
                    ?? ($accountConfig['client_key'] ?? '')
            );

        $this->account = $accountName;

        $this->timeout =
            (int) (
                $timeout
                    ?? config(
                        'ditusi.timeout',
                        30
                    )
            );

        $this->accessTokenUrl =
            rtrim(
                (string) (
                    $accessTokenUrl
                        ?? config(
                            'ditusi.access_token_url'
                        )
                ),
                '/'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESS TOKEN
    |--------------------------------------------------------------------------
    */

    public function getAccessToken(): string
    {
        $this->ensureConfigured();


        /*
        |--------------------------------------------------------------------------
        | ACCOUNT-SPECIFIC CACHE
        |--------------------------------------------------------------------------
        |
        | Penting untuk multi API key.
        |
        */

        $cacheKey =
            'ditusi:access-token:' .
            sha1(
                $this->baseUrl .
                '|' .
                $this->clientId .
                '|' .
                $this->accessTokenUrl
            );


        return Cache::remember(
            $cacheKey,
            now()->addMinutes(9),
            function () {

                $timestamp =
                    now()->toIso8601String();


                $signature =
                    hash(
                        'sha256',
                        $this->clientKey .
                        ':' .
                        $timestamp
                    );


                Log::info(
                    'DITUSI access token request',
                    [
                        'url' =>
                            $this->accessTokenUrl,

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
                        $this->accessTokenUrl
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


    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    public function get(
        string $endpoint,
        array $query = []
    ): array {

        return $this->request(
            'GET',
            $endpoint,
            $query
        );

    }


    /*
    |--------------------------------------------------------------------------
    | POST
    |--------------------------------------------------------------------------
    */

    public function post(
        string $endpoint,
        array $body = []
    ): array {

        return $this->request(
            'POST',
            $endpoint,
            $body
        );

    }


    /*
    |--------------------------------------------------------------------------
    | REQUEST
    |--------------------------------------------------------------------------
    */

    protected function request(
        string $method,
        string $endpoint,
        array $data = []
    ): array {

        $method =
            strtoupper(
                trim($method)
            );


        $path =
            $this->buildPath(
                $endpoint
            );


        $timestamp =
            now()->toIso8601String();


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE JSON
        |--------------------------------------------------------------------------
        |
        | GET tanpa query => {}
        |
        */

        if (
            $method === 'GET' &&
            empty($data)
        ) {

            $json = '{}';

        } else {

            $json =
                json_encode(
                    $data,
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE
                );

        }


        if ($json === false) {

            throw new RuntimeException(
                'Gagal membuat JSON signature DITUSI.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

        $signatureString =
            $path .
            ':' .
            $this->clientKey .
            ':' .
            $timestamp .
            ':' .
            $json;


        $signature =
            hash(
                'sha256',
                $signatureString
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
            '/' .
            ltrim(
                $endpoint,
                '/'
            );


        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'DITUSI API request',
            [
                'method' =>
                    $method,

                'url' =>
                    $url,

                'path' =>
                    $path,

                'data' =>
                    $data,

                'json_for_signature' =>
                    $json,

                'timestamp' =>
                    $timestamp,

                'signature' =>
                    $signature,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | HTTP
        |--------------------------------------------------------------------------
        */

        $http =
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
            ]);


        if ($method === 'GET') {

            $response =
                $http->get(
                    $url,
                    $data
                );

        } elseif ($method === 'POST') {

            $response =
                $http->post(
                    $url,
                    $data
                );

        } else {

            throw new RuntimeException(
                'HTTP method DITUSI tidak didukung: ' .
                $method
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        Log::info(
            'DITUSI API response',
            [
                'method' =>
                    $method,

                'url' =>
                    $url,

                'status' =>
                    $response->status(),

                'body' =>
                    $response->body(),
            ]
        );


        if ($response->failed()) {

            throw new RuntimeException(
                'DITUSI API error HTTP ' .
                $response->status() .
                ': ' .
                $response->body()
            );

        }


        $result =
            $response->json();


        if (!is_array($result)) {

            throw new RuntimeException(
                'Response DITUSI tidak valid.'
            );

        }


        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD SIGNATURE PATH
    |--------------------------------------------------------------------------
    */

    protected function buildPath(
        string $endpoint
    ): string {

        $basePath =
            parse_url(
                $this->baseUrl,
                PHP_URL_PATH
            );


        $basePath =
            rtrim(
                (string) $basePath,
                '/'
            );


        return
            $basePath .
            '/' .
            ltrim(
                $endpoint,
                '/'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    protected function ensureConfigured(): void
    {
        if ($this->baseUrl === '') {

            throw new RuntimeException(
                'DITUSI Base URL belum dikonfigurasi.'
            );

        }


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
    }
}