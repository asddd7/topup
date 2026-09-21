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
}