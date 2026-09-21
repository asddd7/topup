<?php

namespace App\Integrations\MooGold;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MooGoldClient
{
    protected string $baseUrl;

    protected string $partnerId;

    protected string $secretKey;

    protected int $timeout;

    public function __construct(
        ?string $baseUrl = null,
        ?string $partnerId = null,
        ?string $secretKey = null,
        ?int $timeout = null
    ) {
        $this->baseUrl =
            rtrim(
                $baseUrl
                    ?? config('moogold.base_url'),
                '/'
            );

        $this->partnerId =
            (string) (
                $partnerId
                    ?? config('moogold.partner_id')
            );

        $this->secretKey =
            (string) (
                $secretKey
                    ?? config('moogold.secret_key')
            );

        $this->timeout =
            (int) (
                $timeout
                    ?? config('moogold.timeout', 30)
            );

        Log::info(
            'MooGold client configuration check',
            [
                'base_url' =>
                    $this->baseUrl,

                'partner_id_present' =>
                    $this->partnerId !== '',

                'partner_id_length' =>
                    strlen(
                        $this->partnerId
                    ),

                'secret_key_present' =>
                    $this->secretKey !== '',

                'secret_key_length' =>
                    strlen(
                        $this->secretKey
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REQUEST
    |--------------------------------------------------------------------------
    */

    public function request(
        string $path,
        array $data = []
    ): array {

        /*
        |--------------------------------------------------------------------------
        | TIMESTAMP
        |--------------------------------------------------------------------------
        */

        $timestamp =
            time();


        /*
        |--------------------------------------------------------------------------
        | REQUEST BODY
        |--------------------------------------------------------------------------
        */

        $body = [
            'path' =>
                $path,

            ...$data,
        ];


        /*
        |--------------------------------------------------------------------------
        | DEBUG REQUEST
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MooGold API request debug',
            [
                'url' =>
                    $this->baseUrl .
                    '/' .
                    $path,

                'path' =>
                    $path,

                'method' =>
                    'POST',

                'body' =>
                    $body,

                'timestamp' =>
                    $timestamp,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */

        $json =
            json_encode(
                $body,
                JSON_UNESCAPED_SLASHES
            );


        if ($json === false) {

            throw new RuntimeException(
                'Gagal membuat JSON request MooGold.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

        $auth =
            hash_hmac(
                'sha256',
                $json .
                $timestamp .
                $path,
                $this->secretKey
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
            ->withOptions([
                CURLOPT_IPRESOLVE =>
                    CURL_IPRESOLVE_V4,
            ])
            ->withBasicAuth(
                $this->partnerId,
                $this->secretKey
            )
            ->withHeaders([
                'timestamp' =>
                    $timestamp,

                'auth' =>
                    $auth,

                'Accept' =>
                    'application/json',
            ])
            ->withBody(
                $json,
                'application/json'
            )
            ->post(
                $this->baseUrl .
                '/' .
                $path
            );


        /*
        |--------------------------------------------------------------------------
        | RESPONSE LOG
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MooGold API request',
            [
                'url' =>
                    $this->baseUrl .
                    '/' .
                    $path,

                'path' =>
                    $path,

                'status' =>
                    $response->status(),

                'response' =>
                    $response->body(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        */

        if ($response->failed()) {

            Log::error(
                'MooGold API HTTP error',
                [
                    'path' =>
                        $path,

                    'status' =>
                        $response->status(),

                    'body' =>
                        $response->body(),
                ]
            );

            throw new RuntimeException(
                'MooGold API error HTTP ' .
                $response->status() .
                ': ' .
                $response->body()
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PARSE JSON
        |--------------------------------------------------------------------------
        */

        $result =
            $response->json();


        if (!is_array($result)) {

            throw new RuntimeException(
                'Response MooGold tidak valid.'
            );

        }


        return $result;
    }
}