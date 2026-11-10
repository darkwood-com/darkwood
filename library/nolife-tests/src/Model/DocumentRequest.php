<?php

declare(strict_types=1);

namespace App\Model;

final readonly class DocumentRequest
{
    public function __construct(public string $uri) {}
}
