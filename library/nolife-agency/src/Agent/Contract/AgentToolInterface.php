<?php

namespace App\Agent\Contract;

interface AgentToolInterface
{
    public function name(): string;

    public function description(): string;

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function invoke(array $input): array;
}
