<?php

declare(strict_types=1);

namespace App\Infrastructure\Composio;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Thin HTTP adapter for Composio REST v3 (no SDK).
 */
final class ComposioClient
{
    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey,
        private string $baseUrl,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getTool(string $actionName): array
    {
        $this->assertApiKey();

        $url = rtrim($this->baseUrl, '/') . '/v3/tools/' . rawurlencode($actionName);
        $response = $this->http->request('GET', $url, [
            'headers' => $this->headers(),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf(
                'Composio get tool failed (%s): %s',
                $actionName,
                $response->getContent(false)
            ));
        }

        return $response->toArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listConnectedAccountItems(string $userId): array
    {
        $this->assertApiKey();

        $url = rtrim($this->baseUrl, '/') . '/v3/connected_accounts';
        $response = $this->http->request('GET', $url, [
            'headers' => $this->headers(),
            'query' => array_filter([
                'user_ids' => $userId,
            ], static fn (mixed $v): bool => $v !== null && $v !== ''),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Composio list connected accounts failed: ' . $response->getContent(false));
        }

        $data = $response->toArray();
        $items = $data['items'] ?? [];

        return \is_array($items) ? $items : [];
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return array<string, mixed>
     */
    public function executeTool(
        string $actionName,
        string $connectedAccountId,
        array $arguments,
        string $userId,
    ): array {
        $this->assertApiKey();

        $url = rtrim($this->baseUrl, '/') . '/v3/tools/execute/' . rawurlencode($actionName);
        $payload = [
            'connected_account_id' => $connectedAccountId,
            'arguments' => $arguments,
            'user_id' => $userId,
        ];

        $response = $this->http->request('POST', $url, [
            'headers' => $this->headers() + ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Composio execute failed: ' . $response->getContent(false));
        }

        return $response->toArray();
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'x-api-key' => $this->apiKey,
        ];
    }

    private function assertApiKey(): void
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('COMPOSIO_API_KEY is not set.');
        }
    }
}
