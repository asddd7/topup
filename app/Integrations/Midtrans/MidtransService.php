<?php

namespace App\Integrations\Midtrans;

class MidtransService
{
    public function __construct(
        protected MidtransClient $client
    ) {
    }


    public function createSnapToken(
        array $params
    ): string {

        return $this->client->createSnapToken(
            $params
        );
    }


    public function getSnapRedirectUrl(
        array $params
    ): string {

        return $this->client->getSnapRedirectUrl(
            $params
        );
    }


    public function getTransactionStatus(
        string $orderId
    ): ?array {

        return $this->client->getTransactionStatus(
            $orderId
        );
    }


    public function clientKey(): ?string
    {
        return $this->client->clientKey();
    }


    public function isProduction(): bool
    {
        return $this->client->isProduction();
    }


    public function verifySignature(
        array $payload
    ): bool {

        return $this->client->verifySignature(
            $payload
        );
    }


    public function getSnapPaymentChannels(): array
    {
        return $this->client->getSnapPaymentChannels();
    }
}