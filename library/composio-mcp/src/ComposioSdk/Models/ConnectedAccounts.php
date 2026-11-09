<?php

namespace App\ComposioSdk\Models;

use App\ComposioSdk\Composio;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ConnectedAccounts
{
    public function __construct(private readonly Composio $client)
    {
    }

    /**
     * Retrieves a list of all connected accounts in the Composio platform.
     * 
     * It supports pagination and filtering based on various parameters such as app ID, integration ID, and connected account ID. The response includes an array of connection objects, each containing details like the connector ID, connection parameters, status, creation/update timestamps, and associated app information.
     * 
     * @param array $data The data for the request.
     * @param int|null $data['page'] Page number to fetch
     * @param int|null $data['pageSize'] Page size to assume
     * @param string|null $data['integrationId'] Filter by using specific Integration
     * @param string|null $data['user_uuid'] Filter by user UUID
     * @return array A promise that resolves to the list of all connected accounts.
     * @throws \Exception If the request fails.
     */
    public function list(array $data = []): array
    {
        $url = $this->client->baseUrl . '/v3/connected_accounts';
        $response = $this->client->http->request('GET', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey
            ],
            'query' => [
                'user_ids' => $data['user_uuid'] ?? null,
                'cursor' => $data['page'] ?? null,
                'limit' => $data['pageSize'] ?? null,
                'toolkit_slugs' => $data['integrationId'] ?? null,
                'statuses' => $data['statuses'] ?? null,
                'auth_config_ids' => $data['auth_config_ids'] ?? null,
                'order_by' => $data['order_by'] ?? null,
                'labels' => $data['labels'] ?? null,
            ]
        ]);

        return $response->toArray();
    }

    /**
     * Connects an account to the Composio platform.
     * 
     * This method allows you to connect an external app account with Composio. It requires the integration ID in the request body and returns the connection status, connection ID, and a redirect URL (if applicable) for completing the connection flow.
     * 
     * @param array $data The data for the request.
     * @param array $data['requestBody'] The request body containing integration details
     * @return array A promise that resolves to the connection status and details.
     * @throws \Exception If the request fails.
     */
    public function create(array $data = []): array
    {
        $url = $this->client->baseUrl . '/v1/connectedAccounts';
        $response = $this->client->http->request('POST', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey
            ],
            'json' => $data['requestBody'] ?? [],
        ]);
        return $response->toArray();
    }

    /**
     * Retrieves details of a specific account connected to the Composio platform by providing its connected account ID.
     * 
     * The response includes the integration ID, connection parameters (such as scope, base URL, client ID, token type, access token, etc.), connection ID, status, and creation/update timestamps.
     * 
     * @param array $data The data for the request.
     * @param string $data['connectedAccountId'] The unique identifier of the connection.
     * @return array A promise that resolves to the details of the connected account.
     * @throws \Exception If the request fails.
     */
    public function get(array $data): array
    {
        if (!isset($data['connectedAccountId'])) {
            throw new \InvalidArgumentException('connectedAccountId is required');
        }

        $url = $this->client->baseUrl . '/v1/connectedAccounts/' . urlencode($data['connectedAccountId']);
        $response = $this->client->http->request('GET', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey
            ]
        ]);
        return $response->toArray();
    }

    /**
     * Initiates a new connected account on the Composio platform.
     * 
     * This method allows you to start the process of connecting an external app account with Composio. It requires the integration ID and optionally the entity ID, additional parameters, and a redirect URL.
     * 
     * @param array $data The data for the request.
     * @return array The connection request model.
     * @throws \Exception If the request fails.
     */
    public function initiate(array $data): array
    {
        $url = $this->client->baseUrl . '/v1/connectedAccounts';
        $response = $this->client->http->request('POST', $url, [
            'headers' => [
                'x-api-key' => $this->client->apiKey
            ],
            'json' => $data
        ]);
        return $response->toArray();
    }
}