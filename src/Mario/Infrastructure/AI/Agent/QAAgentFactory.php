<?php

namespace App\Mario\Infrastructure\AI\Agent;

use App\Mario\Domain\Level\Level;
use App\Mario\Domain\Level\LevelValidationResult;
use App\Mario\Infrastructure\Validation\MarioLevelValidator;

final class QAAgentFactory
{
    public function __construct(private readonly MarioLevelValidator $validator)
    {
    }

    public function createDeterministic(): QAAgent
    {
        return new DeterministicQAAgent($this->validator);
    }
}

interface QAAgent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(Level $level, array $payload): LevelValidationResult;
}

final class DeterministicQAAgent implements QAAgent
{
    public function __construct(private readonly MarioLevelValidator $validator)
    {
    }

    public function validate(Level $level, array $payload): LevelValidationResult
    {
        return $this->validator->validate($level, $payload);
    }
}
