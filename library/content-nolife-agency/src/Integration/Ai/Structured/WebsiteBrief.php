<?php

namespace App\Integration\Ai\Structured;

final readonly class WebsiteBrief
{
    public function __construct(
        public string $title,
        public string $audience,
        public string $goal,
    ) {
    }
}
