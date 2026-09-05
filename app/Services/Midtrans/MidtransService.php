<?php

namespace App\Services\Midtrans;

use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;
use Throwable;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey =
            (string) config(
                'midtrans.server_key'
            );

        Config::$isProduction =
            (bool) config(
                'midtrans.is_production',
                false
            );

        Config::$isSanitized = true;

        Config::$is3ds = true;
    }


    /**
     * =========================================================
     * CREATE SNAP TOKEN
     * =========================================================
     */
    public function createSnapToken(
        array $params
    ): string {

        $this->ensureConfigured();


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


    /**
     * =========================================================
     * GET SNAP REDIRECT URL
     * =========================================================
     */
    public function getSnapRedirectUrl(
        array $params
    ): string {

        $this->ensureConfigured();


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


    /**
     * =========================================================
     * GET TRANSACTION STATUS
     * =========================================================
     *
     * Digunakan untuk:
     *
     * - verifikasi notification
     * - reconciliation
     * - recovery jika notification terlambat
     * - memastikan status aktual di Midtrans
     *
     */
    public function getTransactionStatus(
        string $orderId
    ): object {

        $this->ensureConfigured();


        if (
            trim($orderId) === ''
        ) {

            throw new RuntimeException(
                'Midtrans Order ID tidak boleh kosong.'
            );
        }


        try {

            $response =
                Transaction::status(
                    $orderId
                );


            Log::info(
                'Status transaksi Midtrans berhasil diperiksa.',
                [

                    'order_id' =>
                        $orderId,

                    'transaction_status' =>
                        data_get(
                            $response,
                            'transaction_status'
                        ),

                    'status_code' =>
                        data_get(
                            $response,
                            'status_code'
                        ),

                ]
            );


            return $response;

        } catch (Throwable $e) {

            Log::error(
                'Gagal mengambil status transaksi Midtrans.',
                [

                    'order_id' =>
                        $orderId,

                    'error' =>
                        $e->getMessage(),

                ]
            );


            throw new RuntimeException(
                'Gagal mengambil status transaksi Midtrans.',
                previous: $e
            );
        }
    }


    /**
     * =========================================================
     * GET CLIENT KEY
     * =========================================================
     */
    public function clientKey(): ?string
    {
        return config(
            'midtrans.client_key'
        );
    }


    /**
     * =========================================================
     * CHECK ENVIRONMENT
     * =========================================================
     */
    public function isProduction(): bool
    {
        return (bool) config(
            'midtrans.is_production',
            false
        );
    }


    /**
     * =========================================================
     * ENSURE CONFIGURED
     * =========================================================
     */
    protected function ensureConfigured(): void
    {
        if (
            empty(
                config(
                    'midtrans.server_key'
                )
            )
        ) {

            throw new RuntimeException(
                'MIDTRANS_SERVER_KEY belum dikonfigurasi.'
            );
        }
    }
}