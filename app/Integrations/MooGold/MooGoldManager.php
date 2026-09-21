<?php

namespace App\Integrations\MooGold;

use RuntimeException;

class MooGoldManager
{
    /*
    |--------------------------------------------------------------------------
    | DEFAULT ACCOUNT
    |--------------------------------------------------------------------------
    */

    public function defaultAccount(): string
    {
        return (string) config(
            'moogold.default_account',
            'primary'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET ACCOUNT
    |--------------------------------------------------------------------------
    */

    public function account(
        ?string $account = null
    ): MooGoldService {

        $accountName =
            $account
                ?? $this->defaultAccount();


        /*
        |--------------------------------------------------------------------------
        | CHECK ACCOUNT
        |--------------------------------------------------------------------------
        */

        $accountConfig =
            config(
                'moogold.accounts.' .
                $accountName
            );


        if (!is_array($accountConfig)) {

            throw new RuntimeException(
                "MooGold account [{$accountName}] tidak ditemukan."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CLIENT
        |--------------------------------------------------------------------------
        */

        $client =
            new MooGoldClient(
                account: $accountName
            );


        /*
        |--------------------------------------------------------------------------
        | SERVICE
        |--------------------------------------------------------------------------
        */

        return new MooGoldService(
            $client
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK ACCOUNT EXISTS
    |--------------------------------------------------------------------------
    */

    public function hasAccount(
        string $account
    ): bool {

        return is_array(
            config(
                'moogold.accounts.' .
                $account
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AVAILABLE ACCOUNTS
    |--------------------------------------------------------------------------
    */

    public function accounts(): array
    {
        $accounts =
            config(
                'moogold.accounts',
                []
            );


        if (!is_array($accounts)) {

            return [];

        }


        return array_keys(
            $accounts
        );
    }
}