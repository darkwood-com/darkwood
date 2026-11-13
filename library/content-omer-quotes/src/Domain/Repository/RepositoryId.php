<?php

declare(strict_types=1);

namespace App\Domain\Repository;

final readonly class RepositoryId
{
    public function __construct(private string $value)
    {
    }

    public static function fromFullName(string $fullName): self
    {
        $normalized = strtolower(trim($fullName));
        $normalized = str_replace('/', '__', $normalized);

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
