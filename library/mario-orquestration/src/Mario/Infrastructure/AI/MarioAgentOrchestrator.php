<?php

namespace App\Mario\Infrastructure\AI;

use App\Mario\Domain\Level\GameplayObject;
use App\Mario\Domain\Level\GeneratedLevel;
use App\Mario\Domain\Level\TerrainElement;
use App\Mario\Domain\Level\ValidationReport;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

final class MarioAgentOrchestrator
{
    public function __construct(private readonly MarioMultiAgentFactory $multiAgentFactory)
    {
    }

    /**
     * @return array{fake_mode: bool, selected_agent: string, result_text: string, payload: array<string, mixed>, validation: ValidationReport}
     */
    public function orchestrate(string $prompt, bool $forceFake): array
    {
        $selectedAgent = $this->selectAgent($prompt);
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? null;
        $fakeMode = $forceFake || null === $apiKey || '' === trim((string) $apiKey);

        if ($fakeMode) {
            $payload = $this->buildDeterministicPayload();

            return [
                'fake_mode' => true,
                'selected_agent' => $selectedAgent,
                'result_text' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'payload' => $payload,
                'validation' => $this->validatePayload($payload),
            ];
        }

        $multiAgent = $this->multiAgentFactory->create($apiKey);
        $messageBag = new MessageBag(Message::ofUser($this->buildRealPrompt($prompt)));
        $result = $multiAgent->call($messageBag);
        $content = $result->getContent();
        $resultText = \is_string($content) ? $content : json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $payload = $this->extractJsonPayload($resultText);

        return [
            'fake_mode' => false,
            'selected_agent' => $selectedAgent,
            'result_text' => $resultText,
            'payload' => $payload,
            'validation' => $this->validatePayload($payload),
        ];
    }

    private function buildRealPrompt(string $prompt): string
    {
        return $prompt.' Return valid JSON for a copyright-safe mario-like platformer that can be imported in Godot.';
    }

    private function selectAgent(string $prompt): string
    {
        $lower = mb_strtolower($prompt);

        $rules = [
            MarioMultiAgentFactory::LAYOUT => ['level', 'terrain', 'platform', 'map', 'layout'],
            MarioMultiAgentFactory::GAMEPLAY => ['enemy', 'coin', 'block', 'bonus', 'gameplay', 'obstacle'],
            MarioMultiAgentFactory::GODOT_EXPORT => ['godot', 'export', 'json', 'scene'],
            MarioMultiAgentFactory::QA => ['validate', 'qa', 'test', 'playable', 'review'],
        ];

        foreach ($rules as $agentName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $agentName;
                }
            }
        }

        return MarioMultiAgentFactory::FALLBACK;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDeterministicPayload(): array
    {
        $level = new GeneratedLevel(
            name: 'agent_generated_platformer_level',
            tileSize: 16,
            width: 220,
            height: 15,
            playerStartX: 2,
            playerStartY: 12,
            goalX: 210,
            goalY: 12,
            terrain: [
                new TerrainElement('ground', 0, 13, 40, 2),
                new TerrainElement('gap', 40, 13, 3, 2),
                new TerrainElement('ground', 43, 13, 60, 2),
                new TerrainElement('pipe_like_obstacle', 28, 11, 2, 2),
                new TerrainElement('stairs', 180, 9, null, null, ['steps' => 5]),
            ],
            objects: [
                new GameplayObject('coin', 14, 8),
                new GameplayObject('block', 16, 9, ['kind' => 'question', 'content' => 'coin']),
                new GameplayObject('enemy', 22, 12, ['kind' => 'walker']),
            ],
            metadata: [
                'generated_by' => 'symfony_ai_multi_agent',
                'copyright_safe' => true,
                'inspiration' => 'classic_2d_platformer',
            ],
        );

        return $level->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function extractJsonPayload(string $resultText): array
    {
        $decoded = json_decode($resultText, true);
        if (\is_array($decoded)) {
            return $decoded;
        }

        return [
            'name' => 'agent_generated_platformer_level',
            'tile_size' => 16,
            'width' => 220,
            'height' => 15,
            'player_start' => ['x' => 2, 'y' => 12],
            'goal' => ['x' => 210, 'y' => 12],
            'terrain' => [],
            'objects' => [],
            'metadata' => [
                'generated_by' => 'symfony_ai_multi_agent',
                'copyright_safe' => true,
                'inspiration' => 'classic_2d_platformer',
                'raw_output' => $resultText,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): ValidationReport
    {
        $errors = [];

        foreach (['name', 'tile_size', 'width', 'height', 'player_start', 'goal', 'terrain', 'objects', 'metadata'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $payload)) {
                $errors[] = sprintf('Missing key: %s', $requiredKey);
            }
        }

        if (array_key_exists('terrain', $payload) && !\is_array($payload['terrain'])) {
            $errors[] = 'terrain must be an array.';
        }

        if (array_key_exists('objects', $payload) && !\is_array($payload['objects'])) {
            $errors[] = 'objects must be an array.';
        }

        if (array_key_exists('metadata', $payload) && !\is_array($payload['metadata'])) {
            $errors[] = 'metadata must be an object.';
        }

        return new ValidationReport([] === $errors, $errors, []);
    }
}
