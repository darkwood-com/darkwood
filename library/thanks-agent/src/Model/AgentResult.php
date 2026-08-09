<?php

declare(strict_types=1);

namespace App\Model;

final readonly class AgentResult
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public AgentId $agentId,
        public string $runId,
        public string $output,
        public int $value,
        public float $latencyMs,
        public int $messages,
        public int $tokens,
        public float $cost,
        public bool $didWork,
        public array $payload = [],
        public bool $success = true,
        public ?string $error = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'agent' => $this->agentId->value,
            'runId' => $this->runId,
            'output' => $this->output,
            'value' => $this->value,
            'latencyMs' => round($this->latencyMs, 2),
            'messages' => $this->messages,
            'tokens' => $this->tokens,
            'cost' => round($this->cost, 6),
            'didWork' => $this->didWork,
            'success' => $this->success,
            'error' => $this->error,
            'payload' => $this->payload,
        ];
    }
}
