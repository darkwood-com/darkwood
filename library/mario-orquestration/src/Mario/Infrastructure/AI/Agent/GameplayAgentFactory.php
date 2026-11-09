<?php

namespace App\Mario\Infrastructure\AI\Agent;

use App\Mario\Domain\Level\GameplayObject;

final class GameplayAgentFactory
{
    public function createDeterministic(): GameplayAgent
    {
        return new DeterministicGameplayAgent();
    }
}

interface GameplayAgent
{
    /**
     * @return list<GameplayObject>
     */
    public function generate(string $reference): array;
}

final class DeterministicGameplayAgent implements GameplayAgent
{
    public function generate(string $reference): array
    {
        return [
            new GameplayObject('enemy', 12, 12, ['kind' => 'goomba']),
            new GameplayObject('question_block', 16, 9, ['content' => 'coin']),
            new GameplayObject('coin', 17, 8),
            new GameplayObject('question_block', 48, 8, ['content' => 'mushroom']),
            new GameplayObject('enemy', 61, 12, ['kind' => 'goomba']),
            new GameplayObject('hidden_block', 70, 8, ['reward' => 'coin']),
            new GameplayObject('enemy', 106, 12, ['kind' => 'koopa']),
            new GameplayObject('coin', 139, 9),
            new GameplayObject('enemy', 176, 12, ['kind' => 'goomba']),
            new GameplayObject('question_block', 194, 9, ['content' => 'coin']),
        ];
    }
}
