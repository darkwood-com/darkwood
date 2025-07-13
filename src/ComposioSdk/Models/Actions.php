<?php

namespace App\ComposioSdk\Models;

use App\ComposioSdk\Composio;

class Actions
{
    private Composio $client;

    public function __construct(Composio $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves details of a specific action in the Composio platform by providing its action name.
     *
     * @param array $data The data for the request (should include 'actionName').
     * @return array|null The details of the action, or null on failure.
     */
    public function get(array $data): ?array
    {
        if (empty($data['actionName'])) {
            throw new \InvalidArgumentException('actionName is required');
        }
        $url = $this->client->baseUrl . '/v3/tools/' . $data['actionName'];
        $response = $this->client->http->request('GET', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $result = $response->toArray();
        return $result;
    }

    /**
     * Retrieves a list of all actions in the Composio platform.
     *
     * @param array $data The data for the request (query params: apps, actions, tags, useCase, showEnabledOnly, usecaseLimit, filterImportantActions).
     * @return array|null The list of actions, or null on failure.
     */
    public function list(array $data = []): ?array
    {
        $url = $this->client->baseUrl . '/v3/tools';
        $query = [];
        foreach ([
            'apps', 'actions', 'tags', 'useCase', 'showEnabledOnly', 'usecaseLimit', 'filterImportantActions'
        ] as $param) {
            if (isset($data[$param])) {
                $query[$param] = $data[$param];
            }
        }
        $response = $this->client->http->request('GET', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey
            ],
            //'query' => $query
        ]);
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $result = $response->toArray();
        return $result;
    }

    /**
     * Executes a specific action in the Composio platform.
     *
     * @param array $data The data for the request (should include 'actionName' and 'requestBody').
     * @return array|null The execution status and response data, or null on failure.
     */
    public function execute(array $data): ?array
    {
        if (empty($data['actionName']) || empty($data['requestBody'])) {
            throw new \InvalidArgumentException('actionName and requestBody are required');
        }
        
        $url = $this->client->baseUrl . '/v3/tools/execute/' . urlencode($data['actionName']);
        
        $requestBody = $data['requestBody'];
        
        // Map the request body to the expected format for the v3 API
        $payload = [];
        
        if (isset($requestBody['connectedAccountId'])) {
            $payload['connected_account_id'] = $requestBody['connectedAccountId'];
        }
        
        if (isset($requestBody['input'])) {
            $payload['arguments'] = $requestBody['input'];
        }
        
        if (isset($requestBody['text'])) {
            $payload['text'] = $requestBody['text'];
        }
        
        if (isset($requestBody['user_id'])) {
            $payload['user_id'] = $requestBody['user_id'];
        }
        
        $response = $this->client->http->request('POST', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => $payload
        ]);
        
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $result = $response->toArray();
        return $result;
    }
} 