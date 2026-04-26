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
        return [
            'fitness' => $chunk['best_fitness'] ?? null,
            'generation' => $chunk['generation'] ?? null,
            'seed' => $chunk['seed'] ?? null,
        ];
    }
}
