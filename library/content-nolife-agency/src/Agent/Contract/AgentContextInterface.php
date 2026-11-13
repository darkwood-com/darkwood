<?php

namespace App\Agent\Contract;

interface AgentContextInterface
{
    public function runId(): string;

    public function requester(): string;

    public function projectKey(): ?string;

    public function traceId(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;
}
