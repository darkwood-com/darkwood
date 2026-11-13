<?php

namespace App\Integration\Ai;

final readonly class ConfiguredAiAgentClient implements AiAgentClient
{
    public function __construct(
        private ?string $adapter,
        private SymfonyAiAgentClient $symfonyAiClient,
        private NullAiAgentClient $nullAiClient,
    ) {
    }

    public function name(): string
    {
        return $this->selectedClient()->name();
    }

    public function run(AiAgentRequest $request): AiAgentResponse
    {
        return $this->selectedClient()->run($request);
    }

    public function resolvedClientClass(): string
    {
        return $this->selectedClient()::class;
    }

    private function selectedClient(): AiAgentClient
    {
        return match ($this->adapter ?? 'null') {
            'symfony_ai' => $this->symfonyAiClient,
            'null' => $this->nullAiClient,
            default => throw new \RuntimeException(sprintf('Unsupported NOLIFE_AI_ADAPTER value "%s". Expected "null" or "symfony_ai".', (string) $this->adapter)),
        };
    }
}
