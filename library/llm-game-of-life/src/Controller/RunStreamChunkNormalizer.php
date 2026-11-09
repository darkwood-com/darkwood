<?php

declare(strict_types=1);

namespace App\Controller;

final class RunStreamChunkNormalizer
{
    /**
     * @param array<string,mixed> $chunk
     *
     * @return array<string,mixed>
     */
    public function normalize(array $chunk): array
    {
        if (isset($chunk['frame'], $chunk['cells'])) {
            return $chunk;
        }

        if (isset($chunk['best_frame']) && \is_array($chunk['best_frame'])) {
            /** @var array<string,mixed> $frame */
            $frame = $chunk['best_frame'];
            $frame['metadata'] = $this->metadataFromChunk($chunk);

            return $frame;
        }

        return [
            'frame' => $chunk['generation'] ?? 0,
            'size' => 1,
            'cells' => [[0]],
            'metadata' => $this->metadataFromChunk($chunk),
        ];
    }

    /**
     * @param array<string,mixed> $chunk
     *
     * @return array<string,mixed>
     */
    private function metadataFromChunk(array $chunk): array
    {
        $metadata = [
            'fitness' => $chunk['best_fitness'] ?? null,
            'average_fitness' => $chunk['average_fitness'] ?? null,
            'generation' => $chunk['generation'] ?? null,
            'seed' => $chunk['seed'] ?? null,
            'mode' => $chunk['mode'] ?? $this->inferMode($chunk),
            'dataset' => $chunk['dataset'] ?? null,
            'sequence_hash' => $chunk['sequence_hash'] ?? null,
            'best_program' => $chunk['best_program'] ?? null,
        ];

        return array_filter($metadata, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param array<string,mixed> $chunk
     */
    private function inferMode(array $chunk): ?string
    {
        if (isset($chunk['dataset'])) {
            return 'supervised';
        }

        if (isset($chunk['best_frame'])) {
            return 'unsupervised';
        }

        return null;
    }
}
