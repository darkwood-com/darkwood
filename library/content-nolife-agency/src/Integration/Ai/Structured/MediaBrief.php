<?php

namespace App\Integration\Ai\Structured;

final readonly class MediaBrief
{
    public function __construct(
        public string $format,
        public string $prompt,
    ) {
    }
}
