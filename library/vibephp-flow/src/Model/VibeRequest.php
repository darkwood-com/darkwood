<?php

declare(strict_types=1);

namespace App\Model;

final readonly class VibeRequest
{
    public function __construct(
        public string $method,
        public string $path,
        public ?string $script = null,
        public ?string $source = null,
        public ?string $prompt = null,
        public ?VibeResponse $response = null,
        public float $startedAt = 0.0,
    ) {}
}
