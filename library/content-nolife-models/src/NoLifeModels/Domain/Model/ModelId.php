<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

final readonly class ModelId
{
    public function __construct(public string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('ModelId cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
