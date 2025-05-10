<?php

namespace App\ComposioSdk;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\ComposioSdk\Models\ConnectedAccounts;
use App\ComposioSdk\Models\Apps;
use App\ComposioSdk\Models\Actions;
use App\ComposioSdk\Models\Triggers;
use App\ComposioSdk\Models\Integrations;
use App\ComposioSdk\Models\ActiveTriggers;

class Composio
{
    public string $apiKey;
    public string $baseUrl;
    public HttpClientInterface $http;

    public ConnectedAccounts $connectedAccounts;
    public Apps $apps;
    public Actions $actions;
    public Triggers $triggers;
    public Integrations $integrations;
    public ActiveTriggers $activeTriggers;
    public array $config;

    public function __construct(
        HttpClientInterface $httpClient,
        ?string $apiKey = null,
        ?string $baseUrl = null
    )
    {
        $this->apiKey = $apiKey ?? $_ENV['COMPOSIO_API_KEY'] ?? '';
        if (!$this->apiKey) {
            throw new \InvalidArgumentException('API key is missing');
        }
        $this->baseUrl = $baseUrl ?? $this->getApiUrlBase();
        $this->http = $httpClient;
        $this->config = [
            'HEADERS' => [
                'X-API-Key' => $this->apiKey
            ]
        ];
        // Models should be instantiated with $this as dependency (to be implemented)
        $this->connectedAccounts = new ConnectedAccounts($this);
        $this->apps = new Apps($this);
        $this->actions = new Actions($this);
        $this->triggers = new Triggers($this);
        $this->integrations = new Integrations($this);
        $this->activeTriggers = new ActiveTriggers($this);
    }

    public function getClientId(): ?string
    {
        $response = $this->http->request('GET', $this->baseUrl . '/v1/client/auth/client_info', [
            'headers' => [
                'X-API-KEY' => $this->apiKey
            ]
        ]);
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new \RuntimeException('HTTP Error: ' . $statusCode);
        }
        $data = $response->toArray();
        return $data['client']['id'] ?? null;
    }

    private function getApiUrlBase(): string
    {
        return 'https://backend.composio.dev/api';
    }

    public static function generateAuthKey(HttpClientInterface $httpClient, ?string $baseUrl = null): ?string
    {
        $response = $httpClient->request('GET', ($baseUrl ?? 'https://backend.composio.dev/api') . '/v1/cli/generate_cli_session', [
            'headers' => [
                'Authorization' => ''
            ]
        ]);
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new \RuntimeException('HTTP Error: ' . $statusCode);
        }
        $data = $response->toArray();
        return $data['key'] ?? null;
    }

    public static function validateAuthSession(HttpClientInterface $httpClient, string $key, string $code, ?string $baseUrl = null): ?string
    {
        $response = $httpClient->request('GET', ($baseUrl ?? 'https://backend.composio.dev/api') . '/v1/cli/verify_cli_code', [
            'headers' => [
                'Authorization' => ''
            ],
            'query' => [
                'key' => $key,
                'code' => $code
            ]
        ]);
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new \RuntimeException('HTTP Error: ' . $statusCode);
        }
        $data = $response->toArray();
        return $data['apiKey'] ?? null;
    }

    public function getEntity(string $id = 'default'): Entity
    {
        return new Entity($this, $id);
    }
} 