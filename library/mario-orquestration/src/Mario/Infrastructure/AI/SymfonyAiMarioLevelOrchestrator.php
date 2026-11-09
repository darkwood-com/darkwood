<?php

namespace App\Mario\Infrastructure\AI;

use App\Mario\Application\Orchestration\MarioLevelOrchestrator;
use App\Mario\Domain\Level\Level;
use App\Mario\Domain\Level\LevelLayer;
use App\Mario\Infrastructure\AI\Agent\GameplayAgentFactory;
use App\Mario\Infrastructure\AI\Agent\GodotExportAgentFactory;
use App\Mario\Infrastructure\AI\Agent\LayoutAgentFactory;
use App\Mario\Infrastructure\AI\Agent\QAAgentFactory;
use Symfony\AI\Agent\MultiAgent\MultiAgent;

final class SymfonyAiMarioLevelOrchestrator implements MarioLevelOrchestrator
{
    public function __construct(
        private readonly LayoutAgentFactory $layoutAgentFactory,
        private readonly GameplayAgentFactory $gameplayAgentFactory,
        private readonly GodotExportAgentFactory $godotExportAgentFactory,
        private readonly QAAgentFactory $qaAgentFactory,
    ) {
    }

    public function generate(string $reference, string $target): array
    {
        $this->buildFutureMultiAgentReference();

        $layoutAgent = $this->layoutAgentFactory->createDeterministic();
        $gameplayAgent = $this->gameplayAgentFactory->createDeterministic();
        $exportAgent = $this->godotExportAgentFactory->createDeterministic();
        $qaAgent = $this->qaAgentFactory->createDeterministic();

        $layers = (new LevelLayer())
            ->withTerrain($layoutAgent->generate($reference))
            ->withObjects($gameplayAgent->generate($reference));

        $level = new Level(
            name: 'world_1_1_inspired',
            tileSize: 16,
            width: 224,
            height: 15,
            layers: $layers,
            metadata: [
                'inspiration' => 'super_mario_bros_world_1_1',
                'copyright_safe' => true,
                'generated_by' => 'symfony_ai_multi_agent',
            ],
        );

        $payload = $exportAgent->export($level);
        $validation = $qaAgent->validate($level, $payload);

        return [
            'level' => $level,
            'payload' => $payload,
            'validation' => $validation,
        ];
    }

    private function buildFutureMultiAgentReference(): void
    {
        if (class_exists(MultiAgent::class)) {
            // Reserved integration hook for real Symfony AI multi-agent orchestration.
        }
    }
}
