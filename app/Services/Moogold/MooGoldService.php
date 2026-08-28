<?php

namespace App\Services\MooGold;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MooGoldService
{
    protected string $baseUrl;

    protected string $partnerId;

    protected string $secretKey;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl =
            rtrim(
                config('moogold.base_url'),
                '/'
            );

        $this->partnerId =
            (string) config(
                'moogold.partner_id'
            );

        $this->secretKey =
            (string) config(
                'moogold.secret_key'
            );

        $this->timeout =
            (int) config(
                'moogold.timeout',
                30
            );
    }


protected function request(
    string $path,
    array $data = []
): array {

    $timestamp = time();

    /*
    |--------------------------------------------------------------------------
    | PAYLOAD
    |--------------------------------------------------------------------------
    */

    $body = [
        'path' => $path,
        ...$data,
    ];

    $payload = json_encode(
        $body,
        JSON_UNESCAPED_SLASHES
    );

    if ($payload === false) {

        throw new RuntimeException(
            'Gagal membuat payload MooGold.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AUTH SIGNATURE
    |--------------------------------------------------------------------------
    */

    $stringToSign =
        $payload .
        $timestamp .
        $path;

    $auth = hash_hmac(
        'SHA256',
        $stringToSign,
        $this->secretKey
    );


    /*
    |--------------------------------------------------------------------------
    | BASIC AUTH
    |--------------------------------------------------------------------------
    */

    $basicAuth = base64_encode(
        $this->partnerId .
        ':' .
        $this->secretKey
    );


    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

    $url =
        $this->baseUrl .
        '/' .
        ltrim($path, '/');


    /*
    |--------------------------------------------------------------------------
    | DEBUG
    |--------------------------------------------------------------------------
    */

    Log::info(
        'MooGold DEBUG request',
        [
            'url' =>
                $url,

            'path' =>
                $path,

            'payload' =>
                $payload,

            'timestamp' =>
                $timestamp,

            'partner_id_length' =>
                strlen($this->partnerId),

            'secret_key_length' =>
                strlen($this->secretKey),

            'auth_length' =>
                strlen($auth),

            'basic_auth_length' =>
                strlen($basicAuth),
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | HTTP REQUEST
    |--------------------------------------------------------------------------
    */

    $response = Http::timeout(
        $this->timeout
    )
        ->withHeaders([

            'timestamp' =>
                (string) $timestamp,

            'auth' =>
                $auth,

            'Authorization' =>
                'Basic ' . $basicAuth,

            'Content-Type' =>
                'application/json',

        ])
        ->withBody(
            $payload,
            'application/json'
        )
        ->post($url);


    /*
    |--------------------------------------------------------------------------
    | LOG RESPONSE
    |--------------------------------------------------------------------------
    */

    Log::info(
        'MooGold API response',
        [
            'path' =>
                $path,

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
            'MooGold API error HTTP ' .
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

    $result = $response->json();

    if (!is_array($result)) {

        throw new RuntimeException(
            'Response MooGold tidak valid.'
        );
    }


    return $result;
}

    /**
     * =========================================================
     * CHECK BALANCE
     * =========================================================
     */
    public function balance(): array
    {
        return $this->request(
            'user/balance'
        );
    }


    /**
     * =========================================================
     * PRODUCT LIST
     * =========================================================
     */
    public function products(
        int $categoryId
    ): array {

        return $this->request(
            'product/list_product',
            [
                'category_id' =>
                    $categoryId,
            ]
        );
    }


    /**
     * =========================================================
     * PRODUCT DETAIL
     * =========================================================
     */
    public function product(
        int $productId
    ): array {

        return $this->request(
            'product/product_detail',
            [
                'product_id' =>
                    $productId,
            ]
        );
    }



/**
 * =========================================================
 * CREATE ORDER
 * =========================================================
 */
public function createOrder(
    string $category,
    string $productId,
    string $quantity,
    string $userId,
    ?string $serverId,
    string $partnerOrderId
): array {

    $data = [

        'category' =>
            $category,

        'product-id' =>
            $productId,

        'quantity' =>
            $quantity,

        'User ID' =>
            $userId,

        'Server' =>
            $serverId ?? '',

    ];


    return $this->request(
        'order/create_order',
        [

            'data' =>
                $data,

            'partnerOrderId' =>
                $partnerOrderId,

        ]
    );
}


    /**
     * =========================================================
     * ORDER DETAIL
     * =========================================================
     */
    public function order(
        int $orderId
    ): array {

        return $this->request(
            'order/order_detail',
            [
                'order_id' =>
                    $orderId,
            ]
        );
    }
}