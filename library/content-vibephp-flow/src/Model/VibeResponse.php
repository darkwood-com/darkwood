<?php

declare(strict_types=1);

namespace App\Model;

final readonly class VibeResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public int $status,
        public array $headers,
        public string $body,
        public int $includeCount = 0,
        public int $routeMatches = 0,
    ) {}
}
