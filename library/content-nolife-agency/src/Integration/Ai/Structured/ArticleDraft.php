<?php

namespace App\Integration\Ai\Structured;

final readonly class ArticleDraft
{
    public function __construct(
        public string $title,
        public string $body,
    ) {
    }
}
