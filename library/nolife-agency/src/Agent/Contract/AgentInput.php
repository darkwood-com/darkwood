<?php

namespace App\Agent\Contract;

final readonly class AgentInput
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private array $payload,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
