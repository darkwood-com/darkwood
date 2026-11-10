<?php

declare(strict_types=1);

namespace App\Model;

final readonly class RawDocument
{
    public function __construct(
        public string $uri,
        public string $html,
    ) {}
}
