<?php

namespace App\Integrations\MooGold;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use RuntimeException;

class MooGoldService
{
    public function __construct(
        protected MooGoldClient $client
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | BALANCE
    |--------------------------------------------------------------------------
    */

    public function balance(): array
    {
        return $this->client->request(
            'user/balance'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY
    |--------------------------------------------------------------------------
    */

    public function categories(): array
    {
        return $this->client->request(
            'product/list_category'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    public function products(
        int $categoryId
    ): array {

        return $this->client->request(
            'product/list_product',
            [
                'category_id' =>
                    $categoryId,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT DETAIL
    |--------------------------------------------------------------------------
    */

    public function product(
        int $productId
    ): array {

        return $this->client->request(
            'product/product_detail',
            [
                'product_id' =>
                    $productId,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SERVER LIST
    |--------------------------------------------------------------------------
    */

    public function serverList(
        int $productId
    ): array {

        return $this->client->request(
            'product/server_list',
            [
                'product_id' =>
                    $productId,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE PRODUCT
    |--------------------------------------------------------------------------
    */

    public function validateProduct(
        int $productId,
        array $data
    ): array {

        return $this->client->request(
            'product/validate',
            [
                'data' => [
                    'product-id' =>
                        $productId,

                    ...$data,
                ],
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

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
                (string) $userId,

        ];


        /*
        |--------------------------------------------------------------------------
        | SERVER ID
        |--------------------------------------------------------------------------
        |
        | JANGAN diubah menjadi "Server".
        |
        */

        if (
            $server !== null &&
            $server !== ''
        ) {

            $data['Server ID'] =
                (string) $server;

        }


        return $this->client->request(
            'order/create_order',
            [
                'data' =>
                    $data,

                'partnerOrderId' =>
                    $externalId,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ORDER DETAIL
    |--------------------------------------------------------------------------
    */

    public function order(
        int $orderId
    ): array {

        return $this->client->request(
            'order/order_detail',
            [
                'order_id' =>
                    $orderId,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ORDER DETAIL BY PARTNER ORDER ID
    |--------------------------------------------------------------------------
    */

    public function orderByPartnerOrderId(
        string $partnerOrderId
    ): array {

        return $this->client->request(
            'order/order_detail_partner_id',
            [
                'partner_order_id' =>
                    $partnerOrderId,
            ]
        );

    }

public function transactionHistory(
    string $startDate,
    string $endDate,
    ?string $status = null,
    int $page = 1,
    int $limit = 20
): array {

    $data = [
        'start_date' => $startDate,
        'end_date'   => $endDate,
        'page'       => max($page, 1),
        'limit'      => min(max($limit, 1), 100),
    ];

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    if (
        $status !== null &&
        $status !== ''
    ) {
        $data['status'] = $status;
    }

    /*
    |--------------------------------------------------------------------------
    | REQUEST
    |--------------------------------------------------------------------------
    */

    return $this->client->request(
        'order/transaction_history',
        $data
    );
}
}