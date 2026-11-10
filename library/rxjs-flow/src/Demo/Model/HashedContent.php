<?php

declare(strict_types=1);

namespace App\Demo\Model;

final readonly class HashedContent
{
    public function __construct(
        public string $url,
        public ?string $body,
        public ?string $checksum,
        public ?string $error,
    ) {
    }
}
