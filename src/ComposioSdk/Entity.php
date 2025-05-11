<?php

namespace App\ComposioSdk;

class Entity
{
    private Composio $client;
    public $id;

    public function __construct(Composio $client, string $id = 'DEFAULT_ENTITY_ID')
    {
        $this->client = $client;
        $this->id = $id;
    }

    public function execute(string $actionName, ?array $params = null, ?string $text = null, ?string $connectedAccountId = null): array
    {
        $action = $this->client->actions->get([
            'actionName' => $actionName
        ]);

        if (!$action) {
            throw new \Exception("Could not find action: " . $actionName);
        }

        /*$app = $this->client->apps->get([
            'appKey' => $action['appKey']
        ]);

        if (($app['yaml'] ?? null) && ($app['yaml']['no_auth'] ?? false)) {
            return $this->client->actions->execute([
                'actionName' => $actionName,
                'requestBody' => [
                    'input' => $params,
                    'appName' => $action['appKey']
                ]
            ]);
        }*/

        $connectedAccount = null;
        if ($connectedAccountId) {
            $connectedAccount = $this->client->connectedAccounts->get([
                'connectedAccountId' => $connectedAccountId
            ]);
        } else {
            $connectedAccounts = $this->client->connectedAccounts->list([
                'user_uuid' => $this->id
            ]);

            if (empty($connectedAccounts['items'])) {
                throw new \Exception('No connected account found');
            }

            $connectedAccount = $connectedAccounts['items'][0];
        }

        return $this->client->actions->execute([
            'actionName' => $actionName,
            'requestBody' => [
                'connectedAccountId' => $connectedAccount['id'],
                'input' => $params,
                'appName' => $action['appKey'] ?? null,
                'text' => $text
            ]
        ]);
    }

    public function getConnection(?string $app = null, ?string $connectedAccountId = null): ?array
    {
        if ($connectedAccountId) {
            return $this->client->connectedAccounts->get([
                'connectedAccountId' => $connectedAccountId
            ]);
        }

        $latestAccount = null;
        $latestCreationDate = null;
        $connectedAccounts = $this->client->connectedAccounts->list([
            'user_uuid' => $this->id
        ]);

        if (empty($connectedAccounts['items'])) {
            return null;
        }

        foreach ($connectedAccounts['items'] as $connectedAccount) {
            if ($app === $connectedAccount['appName']) {
                $creationDate = new \DateTime($connectedAccount['createdAt']);
                if ((!$latestAccount || ($latestCreationDate && $creationDate > $latestCreationDate)) 
                    && $connectedAccount['status'] === "ACTIVE") {
                    $latestCreationDate = $creationDate;
                    $latestAccount = $connectedAccount;
                }
            }
        }

        if (!$latestAccount) {
            return null;
        }

        return $this->client->connectedAccounts->get([
            'connectedAccountId' => $latestAccount['id']
        ]);
    }

    public function setupTrigger(string $app, string $triggerName, array $config): array
    {
        $connectedAccount = $this->getConnection($app);
        if (!$connectedAccount) {
            throw new \Exception("Could not find a connection with app='{$app}' and entity='{$this->id}'");
        }

        return $this->client->triggers->setup([
            'triggerName' => $triggerName,
            'connectedAccountId' => $connectedAccount['id'],
            'requestBody' => [
                'triggerConfig' => $config
            ]
        ]);
    }

    public function disableTrigger(string $triggerId): array
    {
        return $this->client->activeTriggers->disable(['triggerId' => $triggerId]);
    }

    public function getConnections(): array
    {
        $connectedAccounts = $this->client->connectedAccounts->list([
            'user_uuid' => $this->id
        ]);
        return $connectedAccounts['items'] ?? [];
    }

    public function getActiveTriggers(): array
    {
        $connectedAccounts = $this->getConnections();
        $activeTriggers = $this->client->activeTriggers->list([
            'connectedAccountIds' => implode(',', array_column($connectedAccounts, 'id'))
        ]);
        return $activeTriggers['triggers'] ?? [];
    }

    public function initiateConnection(
        string $appName,
        ?string $authMode = null,
        ?array $authConfig = null,
        ?string $redirectUrl = null,
        ?string $integrationId = null
    ): array {
        $app = $this->client->apps->get(['appKey' => $appName]);
        $timestamp = str_replace(['-', ':', '.'], '', (new \DateTime())->format('c'));

        $integration = $integrationId ? $this->client->integrations->get(['integrationId' => $integrationId]) : null;

        if (!$integration && $authMode) {
            $integration = $this->client->integrations->create([
                'appId' => $app['appId'],
                'name' => "integration_{$timestamp}",
                'authScheme' => $authMode,
                'authConfig' => $authConfig,
                'useComposioAuth' => false
            ]);
        }

        if (!$integration && !$authMode) {
            $integration = $this->client->integrations->create([
                'appId' => $app['appId'],
                'name' => "integration_{$timestamp}",
                'useComposioAuth' => true
            ]);
        }

        return $this->client->connectedAccounts->initiate([
            'integrationId' => $integration['id'],
            'userUuid' => $this->id,
            'redirectUri' => $redirectUrl
        ]);
    }
} 