<?php

namespace App\Agent\Runtime;

final readonly class AgentRunId
{
    public function __construct(
        public string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(8)));
    }
}
