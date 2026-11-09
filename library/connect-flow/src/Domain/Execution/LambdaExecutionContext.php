<?php

declare(strict_types=1);

namespace App\Domain\Execution;

/**
 * Runtime context for primitive execution (e.g. Composio user id, merged tool input).
 */
final readonly class LambdaExecutionContext
{
    /**
     * @param array<string, mixed> $composioInput
     */
    public function __construct(
        public string $composioUserId = '',
        public array $composioInput = [],
        public ?string $composioConnectedAccountId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $composioInput
     */
    public function withComposioInput(array $composioInput): self
    {
        return new self($this->composioUserId, $composioInput, $this->composioConnectedAccountId);
    }
}
