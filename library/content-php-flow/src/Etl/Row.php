<?php

declare(strict_types=1);

namespace App\Etl;

use App\Model\OrderRow;

/**
 * Simplified row model — inspired by {@see \Flow\ETL\Row}.
 */
final readonly class Row
{
    /**
     * @param array<string, mixed> $entries
     */
    public function __construct(
        public array $entries,
    ) {}

    public static function fromOrderRow(OrderRow $order): self
    {
        return new self([
            'id' => $order->id,
            'email' => $order->email,
            'amount' => $order->amount,
        ]);
    }

    public function get(string $key): mixed
    {
        return $this->entries[$key];
    }

    public function with(string $key, mixed $value): self
    {
        $entries = $this->entries;
        $entries[$key] = $value;

        return new self($entries);
    }
}
