<?php

namespace App\Mario\Application\Orchestration;

use App\Mario\Domain\Level\Level;
use App\Mario\Domain\Level\LevelValidationResult;

interface MarioLevelOrchestrator
{
    /**
     * @return array{level: Level, payload: array<string, mixed>, validation: LevelValidationResult}
     */
    public function generate(string $reference, string $target): array;
}
