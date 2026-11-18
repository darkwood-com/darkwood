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
        if ('' === trim($prompt) || mb_strlen($prompt) > 2000) {
            throw new \InvalidArgumentException('Prompt must contain between 1 and 2000 characters.');
        }
        $selectedAgent = $this->selectAgent($prompt);
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
        $fakeMode = $forceFake;

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

        if (!\is_string($apiKey) || '' === trim($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY is required for real generation. Use --fake only for an explicit offline fixture.');
        }
        $agent = $this->multiAgentFactory->createLevelAgent($apiKey);
        $messageBag = new MessageBag(Message::ofUser($this->buildRealPrompt($prompt)));
        try {
            $content = $agent->call($messageBag, ['text' => ['format' => ['type' => 'json_object']], 'max_output_tokens' => 6000])->asText();
        } catch (\Throwable) {
            throw new \RuntimeException('AI generation failed. Check provider access and retry. No level was generated.');
        }
        $resultText = \is_string($content) ? $content : json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $payload = $this->extractJsonPayload($resultText);

        return [
            'fake_mode' => false,
            'selected_agent' => $agent->getName(),
            'result_text' => $resultText,
            'payload' => $payload,
            'validation' => $this->validatePayload($payload),
        ];
    }

    private function buildRealPrompt(string $prompt): string
    {
        return $prompt.' Return the complete original platformer level JSON.';
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
                new TerrainElement('stairs', 180, 9, 5, 4, ['steps' => 5]),
            ],
            objects: [
                new GameplayObject('coin', 14, 8),
                new GameplayObject('block', 16, 9, ['kind' => 'question', 'content' => 'coin']),
                new GameplayObject('enemy', 22, 12, ['kind' => 'walker']),
            ],
            metadata: [
                'generated_by' => 'deterministic_fixture',
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
        try {
            $decoded = json_decode($resultText, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \RuntimeException('AI output is invalid JSON. No level was generated.');
        }
        if (!\is_array($decoded) || array_is_list($decoded)) {
            throw new \RuntimeException('AI output must be a level object.');
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function validatePayload(array $payload): ValidationReport
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

        foreach (['tile_size', 'width', 'height'] as $dimension) {
            if (!\is_int($payload[$dimension] ?? null) || $payload[$dimension] < 1 || $payload[$dimension] > 512) {
                $errors[] = $dimension.' must be an integer between 1 and 512.';
            }
        }
        if (!\is_string($payload['name'] ?? null) || '' === trim($payload['name'])) {
            $errors[] = 'name must be nonempty text.';
        }
        if (!\is_array($payload['terrain'] ?? null) || [] === $payload['terrain']) {
            $errors[] = 'terrain must contain at least one element.';
        }
        foreach (['terrain', 'objects'] as $collection) {
            if (\is_array($payload[$collection] ?? null) && (!array_is_list($payload[$collection]) || \count($payload[$collection]) > 100)) {
                $errors[] = $collection.' must be a list of at most 100 elements.';
            }
        }
        if ([] !== $errors) {
            return new ValidationReport(false, $errors);
        }
        $positions = [$payload['player_start'], $payload['goal'], ...$payload['terrain'], ...$payload['objects']];
        foreach ($positions as $position) {
            if (!\is_array($position)) {
                $errors[] = 'Each position must be an object.';
                continue;
            }
            foreach (['x' => 'width', 'y' => 'height'] as $axis => $dimension) {
                if (!\is_int($position[$axis] ?? null) || $position[$axis] < 0 || $position[$axis] >= $payload[$dimension]) {
                    $errors[] = $axis.' must be an integer inside the level bounds.';
                }
            }
        }

        foreach ($payload['terrain'] as $piece) {
            if (!\is_array($piece)) {
                continue;
            }
            foreach (['w' => 'width', 'h' => 'height'] as $size => $dimension) {
                $axis = 'w' === $size ? 'x' : 'y';
                if (!\is_int($piece[$size] ?? null) || $piece[$size] < 1 || !\is_int($piece[$axis] ?? null) || $piece[$axis] + $piece[$size] > $payload[$dimension]) {
                    $errors[] = 'Terrain dimensions must be positive integers inside the level bounds.';
                }
            }
        }

        return new ValidationReport([] === $errors, $errors, ['Geometry checks do not establish playability or copyright clearance.']);
    }
}
