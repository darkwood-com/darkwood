<?php

declare(strict_types=1);

namespace App\Demo\Model;

final readonly class FetchRequest
{
    public function __construct(
        public string $url,
        public float $timeoutSeconds,
    ) {
    }
}
