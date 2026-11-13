<?php

namespace App\Mario\Infrastructure\AI\Agent;

use App\Mario\Domain\Level\TerrainElement;

final class LayoutAgentFactory
{
    public function createDeterministic(): LayoutAgent
    {
        return new DeterministicLayoutAgent();
    }
}

interface LayoutAgent
{
    /**
     * @return list<TerrainElement>
     */
    public function generate(string $reference): array;
}

final class DeterministicLayoutAgent implements LayoutAgent
{
    public function generate(string $reference): array
    {
        return [
            new TerrainElement('ground', 0, 13, 18, 2),
            new TerrainElement('pipe', 22, 11, null, 2),
            new TerrainElement('ground', 18, 13, 18, 2),
            new TerrainElement('gap', 36, 13, 2, 2),
            new TerrainElement('ground', 38, 13, 20, 2),
            new TerrainElement('platform', 46, 10, 4, 1),
            new TerrainElement('pipe', 56, 10, null, 3),
            new TerrainElement('ground', 58, 13, 40, 2),
            new TerrainElement('gap', 98, 13, 3, 2),
            new TerrainElement('ground', 101, 13, 44, 2),
            new TerrainElement('stairs', 138, 12, 6, 2),
            new TerrainElement('ground', 145, 13, 79, 2),
            new TerrainElement('stairs', 210, 9, 10, 5),
            new TerrainElement('end_structure', 220, 10, 4, 4),
        ];
    }
}
