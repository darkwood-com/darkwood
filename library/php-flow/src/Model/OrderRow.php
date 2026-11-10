<?php

declare(strict_types=1);

namespace App\Model;

final readonly class OrderRow
{
    public function __construct(
        public string $id,
        public string $email,
        public float $amount,
    ) {}
}
