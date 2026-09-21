<?php

namespace App\Integrations\Ditusi;

use RuntimeException;

class DitusiService
{
    public function __construct(
        protected DitusiClient $client
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | GAMES
    |--------------------------------------------------------------------------
    */

    public function games(
        ?string $gameCode = null
    ): array {

        $query = [];


        if (
            $gameCode !== null &&
            $gameCode !== ''
        ) {

            $query['gameCode'] =
                $gameCode;

        }


        return $this->client->get(
            'game',
            $query
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
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


        return $this->client->get(
            'product',
            $query
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE TRANSACTION
    |--------------------------------------------------------------------------
    */

    public function createTransaction(
        string $productCode,
        int $amount,
        string $transactionReferenceId,
        array $formDetails,
        ?int $initialPrice = null,
        ?string $endUserIpAddress = null
    ): array {

        $body = [

            'productCode' =>
                $productCode,

            'amount' =>
                $amount,

            'transactionReferenceId' =>
                $transactionReferenceId,

            'formDetails' =>
                $formDetails,

        ];


        if ($initialPrice !== null) {

            $body['initialPrice'] =
                $initialPrice;

        }


        if (
            $endUserIpAddress !== null &&
            $endUserIpAddress !== ''
        ) {

            $body['endUserIpAddress'] =
                $endUserIpAddress;

        }


        return $this->client->post(
            'transaction',
            $body
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSACTION STATUS
    |--------------------------------------------------------------------------
    */

    public function getTransactionStatus(
        string $transactionId
    ): array {

        $transactionId =
            trim(
                $transactionId
            );


        if ($transactionId === '') {

            throw new RuntimeException(
                'Transaction ID DITUSI tidak boleh kosong.'
            );

        }


        return $this->client->get(
            'transaction/' .
            rawurlencode(
                $transactionId
            )
        );
    }
}