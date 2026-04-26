<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\Context;

final readonly class LogChunkAction extends AbstractNdjsonLogAction
{
    public function name(): ActionName
    {
        return ActionName::fromString('life_llm.log_chunk');
    }

    protected function buildRecord(Context $context): array
    {
        $payload = $context->get('payload', []);
        if (!\is_array($payload)) {
            $payload = [];
        }

        return $this->record($context, 'chunk_recorded', $this->slimPayload($payload));
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    private function slimPayload(array $payload): array
    {
        $chunk = $payload['chunk'] ?? null;
        if (!\is_array($chunk)) {
            return $payload;
        }

        $bestProgram = $chunk['best_program'] ?? null;
        $programSize = \is_array($bestProgram) && isset($bestProgram['nodes']) && \is_array($bestProgram['nodes'])
            ? \count($bestProgram['nodes'])
            : null;

        $bestFrame = $chunk['best_frame'] ?? null;
        $frameHash = \is_array($bestFrame)
            ? hash('sha256', json_encode($bestFrame, JSON_THROW_ON_ERROR))
            : null;

        return array_filter([
            'mode' => $payload['mode'] ?? null,
            'dataset' => $payload['dataset'] ?? $chunk['dataset'] ?? null,
            'seed' => $chunk['seed'] ?? null,
            'generation' => $chunk['generation'] ?? null,
            'best_fitness' => $chunk['best_fitness'] ?? null,
            'average_fitness' => $chunk['average_fitness'] ?? null,
            'program_size' => $programSize,
            'sequence_hash' => \is_array($bestProgram) ? hash('sha256', json_encode($bestProgram, JSON_THROW_ON_ERROR)) : null,
            'frame_hash' => $frameHash,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
