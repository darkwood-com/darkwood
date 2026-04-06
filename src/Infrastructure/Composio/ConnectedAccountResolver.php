<?php

declare(strict_types=1);

namespace App\Infrastructure\Composio;

/**
 * Picks a connected account for an action, matching composio-mcp Entity::execute behaviour:
 * optional explicit id, otherwise first account whose toolkit slug matches the action metadata.
 */
final class ConnectedAccountResolver
{
    public function __construct(private ComposioClient $client)
    {
    }

    /**
     * @param array<string, mixed> $actionMetadata from GET /v3/tools/{name}
     */
    public function resolve(string $userId, array $actionMetadata, ?string $connectedAccountId): string
    {
        if ($connectedAccountId !== null && $connectedAccountId !== '') {
            return $connectedAccountId;
        }

        $items = $this->client->listConnectedAccountItems($userId);
        if ($items === []) {
            throw new \RuntimeException('No connected accounts found for this user.');
        }

        $toolkitSlug = null;
        if (isset($actionMetadata['toolkit']) && \is_array($actionMetadata['toolkit'])) {
            $slug = $actionMetadata['toolkit']['slug'] ?? null;
            $toolkitSlug = \is_string($slug) ? $slug : null;
        }

        if ($toolkitSlug !== null) {
            foreach ($items as $account) {
                if (!\is_array($account)) {
                    continue;
                }
                $accToolkit = $account['toolkit'] ?? null;
                $accSlug = \is_array($accToolkit) ? ($accToolkit['slug'] ?? null) : null;
                if (\is_string($accSlug) && $accSlug === $toolkitSlug) {
                    $id = $account['id'] ?? null;
                    if (\is_string($id) && $id !== '') {
                        return $id;
                    }
                }
            }
        }

        throw new \RuntimeException('No connected account found for this action\'s toolkit.');
    }
}
