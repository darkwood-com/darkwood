<?php

declare(strict_types=1);

namespace App\Model;

final readonly class OrderBatch
{
    /**
     * @param list<OrderRow> $rows
     */
    public function __construct(
        public string $id,
        public array $rows,
    ) {}
}
