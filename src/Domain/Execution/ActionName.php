<?php

declare(strict_types=1);

namespace App\Domain\Execution;

final readonly class ActionName
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new \InvalidArgumentException('Action name cannot be empty.');
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
