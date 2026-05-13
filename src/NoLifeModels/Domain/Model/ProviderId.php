<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

final readonly class ProviderId
{
    public function __construct(public string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('ProviderId cannot be empty.');
        }
    }
}
