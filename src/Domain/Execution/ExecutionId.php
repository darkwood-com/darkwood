<?php

declare(strict_types=1);

namespace App\Domain\Execution;

final readonly class ExecutionId
{
    private function __construct(private string $value)
    {
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(8)));
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new \InvalidArgumentException('Execution id cannot be empty.');
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
