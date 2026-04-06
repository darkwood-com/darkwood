<?php

declare(strict_types=1);

namespace App\Domain\Capability;

/**
 * Maps the MVP to a single Composio tool (action) name configured via COMPOSIO_ACTION.
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
