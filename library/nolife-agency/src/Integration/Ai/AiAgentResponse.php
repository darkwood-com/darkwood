<?php

namespace App\Integration\Ai;

use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentOutput;

final readonly class AiAgentResponse
{
    /**
     * @param array<string, mixed> $payload
     * @param list<string> $artifacts
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public array $payload,
        public array $artifacts = [],
        public array $metadata = [],
    ) {
    }

    public static function fromAgentOutput(AgentOutput $output): self
    {
        return new self($output->payload, $output->artifacts, $output->metadata);
    }

    /**
     * @param mixed $content
     * @param array<string, mixed> $metadata
     */
    public static function fromSymfonyAiContent(string $agentKey, AgentContext $context, mixed $content, array $metadata = []): self
    {
        return new self(
            payload: [
                'agent' => $agentKey,
                'run_id' => $context->runId(),
                'requester' => $context->requester(),
                'trace_id' => $context->traceId(),
                'status' => 'ok',
                'content' => self::normalizeContent($content),
            ],
            metadata: array_merge(['source' => 'symfony_ai'], $metadata),
        );
    }

    public function toAgentOutput(): AgentOutput
    {
        return new AgentOutput($this->payload, $this->artifacts, $this->metadata);
    }

    private static function normalizeContent(mixed $content): mixed
    {
        if (is_scalar($content) || null === $content || is_array($content)) {
            return $content;
        }

        return json_decode(json_encode($content, \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR);
    }
}
