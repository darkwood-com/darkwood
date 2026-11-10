<?php

declare(strict_types=1);

namespace App\Application\Service;

final class ClusteringService
{
    /**
     * @param array<string, array<string, float>> $vectors
     * @return array<string, int>
     */
    public function assign(array $vectors, int $k = 3): array
    {
        $repoIds = array_keys($vectors);
        if ($repoIds === []) {
            return [];
        }

        $assignments = [];
        foreach ($repoIds as $index => $repoId) {
            $assignments[$repoId] = $index % max($k, 1);
        }

        return $assignments;
    }
}
