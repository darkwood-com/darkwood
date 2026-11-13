<?php

declare(strict_types=1);

namespace App\Model;

final readonly class ExcerptResult
{
    public function __construct(
        public string $uri,
        public string $title,
        public string $excerpt,
    ) {}
}
