<?php

namespace App\Integration\Navi;

final readonly class TraceEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $name,
        public string $timestamp,
        public array $payload = [],
    ) {
    }
}
