<?php

declare(strict_types=1);

namespace App\Service\Bonzai;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BonzaiApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(BONZAI_API_TOKEN)%')]
        private string $apiToken,
    ) {}

    /** @return array<string, mixed> */
    public function getOrder(string $orderId): array
    {
        if ('' === trim($this->apiToken)) {
            throw new \RuntimeException('Bonzai API verification is not configured.');
        }

        $response = $this->httpClient->request('GET', 'https://www.bonzai.pro/api/v1/orders/' . rawurlencode($orderId), [
            'auth_bearer' => $this->apiToken,
            'headers' => ['Accept' => 'application/json'],
            'timeout' => 10,
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Bonzai order verification failed.');
        }

        return $response->toArray();
    }
}
