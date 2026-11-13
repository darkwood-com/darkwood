<?php

namespace App\ComposioSdk;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ComposioToolSet
{
    private $client;
    private $apiKey;
    private $runtime;
    private $entityId;

    public function __construct(
        HttpClientInterface $httpClient,
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?string $runtime = null,
        string $entityId = "default"
    ) {
        $clientApiKey = $apiKey ?? getenv("COMPOSIO_API_KEY") ?? $this->loadUserData()['apiKey'] ?? null;
        
        if (!$clientApiKey) {
            throw new \Exception("API key is required, please pass it either by using `COMPOSIO_API_KEY` environment variable or during initialization");
        }

        $this->apiKey = $clientApiKey;
        $this->client = new Composio($httpClient, $this->apiKey, $baseUrl);
        $this->runtime = $runtime;
        $this->entityId = $entityId;
    }

    private function loadUserData(): array
    {
        $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
        $userDataPath = $homeDir . DIRECTORY_SEPARATOR . '.composio' . DIRECTORY_SEPARATOR . 'userData.json';
        
        if (!file_exists($userDataPath)) {
            return [];
        }

        $content = file_get_contents($userDataPath);
        return json_decode($content, true) ?? [];
    }

    public function execute_action(
        string $action,
        array $params,
        string $entityId = "default"
    ): array {
        return $this->client->getEntity($entityId)->execute($action, $params);
    }
} 