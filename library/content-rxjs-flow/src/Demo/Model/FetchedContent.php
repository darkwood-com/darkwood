<?php

declare(strict_types=1);

namespace App\Demo\Model;

final readonly class FetchedContent
{
    public function __construct(
        public string $url,
        public ?string $body,
        public ?string $error,
    ) {
    }
}
