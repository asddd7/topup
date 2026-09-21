<?php

namespace App\Services\TopUp;

use App\Integrations\Ditusi\DitusiService;
use App\Integrations\MooGold\MooGoldService;
use InvalidArgumentException;

class ProviderRouter
{
    public function __construct(
        protected ProviderManager $providers
    ) {
    }

    /**
     * Resolve provider.
     */
    public function resolve(
        string $provider,
        ?string $account = null
    ): MooGoldService|DitusiService {

        $provider = $this->normalize($provider);

        return $this->providers->get(
            provider: $provider,
            account: $account
        );
    }

    /**
     * MooGold default account.
     */
    public function mooGold(
        ?string $account = null
    ): MooGoldService {

        return $this->providers->mooGold(
            $account
        );
    }

    /**
     * DITUSI.
     */
    public function ditusi(): DitusiService
    {
        return $this->providers->ditusi();
    }

    /**
     * Normalisasi provider name.
     */
    public function normalize(
        string $provider
    ): string {

        $provider = strtolower(
            trim($provider)
        );

        return match ($provider) {
            'moo',
            'moogold',
            'moo_gold' => 'moogold',

            'ditusi',
            'dti' => 'ditusi',

            default => throw new InvalidArgumentException(
                "Provider [{$provider}] tidak dikenal."
            ),
        };
    }
}