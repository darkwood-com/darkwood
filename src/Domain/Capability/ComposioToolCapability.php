<?php

declare(strict_types=1);

namespace App\Domain\Capability;

/**
 * Holds the configured Composio action(s) string from COMPOSIO_ACTION (verbatim).
 * Parsing comma-separated tool names is done in Infrastructure.
 */
final readonly class ComposioToolCapability
{
    public function __construct(private string $actionName)
    {
    }

    public function actionName(): string
    {
        return $this->actionName;
    }
}
