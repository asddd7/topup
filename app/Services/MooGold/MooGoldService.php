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
    | REQUEST BODY
    |--------------------------------------------------------------------------
    */

    $body = [
        'path' => $path,
        ...$data,
    ];

    $json = json_encode(
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
    |
    | MooGold:
    |
    | HMAC SHA256
    | body + timestamp + path
    |
    */

    $auth = hash_hmac(
        'sha256',
        $json . $timestamp . $path,
        $this->secretKey
    );


    /*
    |--------------------------------------------------------------------------
    | REQUEST
    |--------------------------------------------------------------------------
    */

$response = Http::timeout(
    $this->timeout
)
    ->withBasicAuth(
        $this->partnerId,
        $this->secretKey
    )
    ->withHeaders([

        'timestamp' => $timestamp,

        'auth' => $auth,

        'Accept' => 'application/json',

    ])
    ->withBody(
        $json,
        'application/json'
    )
    ->post(
        $this->baseUrl . '/' . $path
    );


    /*
    |--------------------------------------------------------------------------
    | DEBUG LOG
    |--------------------------------------------------------------------------
    */

    Log::info(
        'MooGold API request',
        [
            'url' =>
                $this->baseUrl . '/' . $path,

            'path' =>
                $path,

            'status' =>
                $response->status(),

            'response' =>
                $response->body(),
        ]
    );


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
 * CATEGORY LIST
 * =========================================================
 */
public function categories(): array
{
    return $this->request(
        'product/list_category'
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
 * SERVER LIST
 * =========================================================
 */
public function serverList(
    int $productId
): array {

    return $this->request(
        'product/server_list',
        [
            'product_id' => $productId,
        ]
    );
}


/**
 * =========================================================
 * VALIDATE PRODUCT
 * =========================================================
 */
public function validateProduct(
    int $productId,
    array $data
): array {

    return $this->request(
        'product/validate',
        [
            'data' => [
                'product-id' => $productId,
                ...$data,
            ],
        ]
    );
}

public function createOrder(
    int $categoryId,
    string $externalId,
    string $variationId,
    int $quantity,
    string $userId,
    ?string $server = null
): array {

    $data = [

        'category' =>
            (string) $categoryId,

        'product-id' =>
            (string) $variationId,

        'quantity' =>
            (string) $quantity,

        'User ID' =>
            $userId,

    ];

    if (
        $server !== null &&
        $server !== ''
    ) {

        $data['Server ID'] = $server;
    }

    return $this->request(

        'order/create_order',

        [

            'data' =>
                $data,

            'partnerOrderId' =>
                $externalId,

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
/**
 * =========================================================
 * ORDER DETAIL BY PARTNER ORDER ID
 * =========================================================
 *
 * Digunakan untuk:
 *
 * - Recovery setelah timeout
 * - Recovery setelah server restart
 * - Mencegah double create_order
 * - Mencari transaksi berdasarkan partnerOrderId
 */
public function orderByPartnerOrderId(
    string $partnerOrderId
): array {

    return $this->request(
        'order/order_detail_partner_id',
        [
            'partner_order_id' =>
                $partnerOrderId,
        ]
    );
}
}