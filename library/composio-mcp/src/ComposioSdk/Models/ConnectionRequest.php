<?php

namespace App\ComposioSdk\Models;

use App\ComposioSdk\Composio;

class ConnectionRequest
{
    public string $connectionStatus;
    public string $connectedAccountId;
    public ?string $redirectUrl;

    /**
     * Connection request model.
     * @param string $connectionStatus The status of the connection.
     * @param string $connectedAccountId The unique identifier of the connected account.
     * @param string|null $redirectUrl The redirect URL for completing the connection flow.
     * @param Composio $client The Composio client instance.
     */
    public function __construct(
        private readonly Composio $client,
        string $connectionStatus,
        string $connectedAccountId,
        ?string $redirectUrl = null
    ) {
        $this->connectionStatus = $connectionStatus;
        $this->connectedAccountId = $connectedAccountId;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Save user access data.
     * @param array $data The data to save.
     * @param array $data['fieldInputs'] The field inputs to save.
     * @param string|null $data['redirectUrl'] The redirect URL for completing the connection flow.
     * @param string|null $data['entityId'] The entity ID associated with the user.
     * @return array The response from the server.
     * @throws \Exception If the request fails.
     */
    public function saveUserAccessData(array $data): array
    {
        $connectedAccount = $this->client->connectedAccounts->get([
            'connectedAccountId' => $this->connectedAccountId,
        ]);

        return $this->client->createConnection([
            'requestBody' => [
                'integrationId' => $connectedAccount['integrationId'],
                'data' => $data['fieldInputs'],
                'redirectUri' => $data['redirectUrl'] ?? null,
                'userUuid' => $data['entityId'] ?? null,
            ]
        ]);
    }

    /**
     * Wait until the connection becomes active.
     * @param int $timeout The timeout period in seconds.
     * @return array The connected account model.
     * @throws \Exception If the connection does not become active within the timeout period.
     */
    public function waitUntilActive(int $timeout = 60): array
    {
        $startTime = time();
        while (time() - $startTime < $timeout) {
            $connection = $this->client->connectedAccounts->get([
                'connectedAccountId' => $this->connectedAccountId,
            ]);
            
            if ($connection['status'] === 'ACTIVE') {
                return $connection;
            }
            
            sleep(1);
        }

        throw new \Exception('Connection did not become active within the timeout period.');
    }
}