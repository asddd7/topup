<?php

namespace App\Services\TopUp;

use App\Integrations\Ditusi\DitusiService;
use App\Integrations\MooGold\MooGoldManager;
use App\Integrations\MooGold\MooGoldService;
use InvalidArgumentException;

class ProviderManager
{
    public function __construct(
        protected MooGoldManager $mooGoldManager,
        protected DitusiService $ditusiService,
    ) {
    }

    /**
     * Ambil MooGold service berdasarkan account.
     */
    public function mooGold(
        ?string $account = null
    ): MooGoldService {
        return $this->mooGoldManager->account($account);
    }

    /**
     * Ambil DITUSI service.
     */
    public function ditusi(): DitusiService
    {
        return $this->ditusiService;
    }

    /**
     * Resolve provider berdasarkan nama.
     */
    public function get(
        string $provider,
        ?string $account = null
    ): MooGoldService|DitusiService {

        return match (strtolower(trim($provider))) {

            'moogold' =>
                $this->mooGold($account),

            'ditusi' =>
                $this->ditusi(),

            default =>
                throw new InvalidArgumentException(
                    "Provider top-up [{$provider}] tidak didukung."
                ),
        };
    }
}