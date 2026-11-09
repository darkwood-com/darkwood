<?php

declare(strict_types=1);

namespace App\Infrastructure\Evolution;

use App\Domain\Evolution\RandomGeneratorInterface;

final class MtRandomGenerator implements RandomGeneratorInterface
{
    public function seed(int $seed): void
    {
        mt_srand($seed);
    }

    public function int(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    public function float(): float
    {
        return mt_rand(0, PHP_INT_MAX) / PHP_INT_MAX;
    }
}
