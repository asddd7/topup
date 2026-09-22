<?php

namespace App\Services\TopUp;

use App\Models\OrderDetail;
use App\Services\TopUp\Contracts\TopUpProvider;
use App\Services\TopUp\Providers\DitusiProvider;
use App\Services\TopUp\Providers\MooGoldProvider;
use InvalidArgumentException;

class TopUpProviderRegistry
{
    /**
     * @var array<int, TopUpProvider>
     */
    protected array $providers;

    public function __construct(
        MooGoldProvider $mooGold,
        DitusiProvider $ditusi,
    ) {
        $this->providers = [
            $mooGold,
            $ditusi,
        ];
    }

    /**
     * Semua provider terdaftar.
     *
     * @return array<int, TopUpProvider>
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Semua provider yang mendukung OrderDetail.
     *
     * @return array<int, TopUpProvider>
     */
    public function forOrderDetail(
        OrderDetail $orderDetail
    ): array {

        return array_values(
            array_filter(
                $this->providers,
                fn (TopUpProvider $provider) =>
                    $provider->supports($orderDetail)
            )
        );
    }

    /**
     * Cari provider berdasarkan key.
     */
    public function get(
        string $key
    ): TopUpProvider {

        $normalized = strtolower(
            trim($key)
        );

        foreach ($this->providers as $provider) {

            if ($provider->key() === $normalized) {
                return $provider;
            }
        }

        throw new InvalidArgumentException(
            "Top-up provider [{$key}] tidak ditemukan."
        );
    }

    /**
     * Resolve tepat satu provider untuk OrderDetail.
     *
     * Return null jika tidak ada provider yang cocok.
     *
     * Throw exception jika lebih dari satu provider cocok,
     * karena kita tidak boleh mengirim satu OrderDetail
     * ke lebih dari satu provider secara otomatis.
     */
    public function resolveForOrderDetail(
        OrderDetail $orderDetail
    ): ?TopUpProvider {

        $providers =
            $this->forOrderDetail(
                $orderDetail
            );

        if (count($providers) === 0) {
            return null;
        }

        if (count($providers) > 1) {

            $providerNames =
                array_map(
                    fn (TopUpProvider $provider) =>
                        $provider->key(),
                    $providers
                );

            throw new InvalidArgumentException(
                'OrderDetail #' .
                $orderDetail->id .
                ' memiliki lebih dari satu provider aktif: ' .
                implode(', ', $providerNames) .
                '.'
            );
        }

        return $providers[0];
    }
}