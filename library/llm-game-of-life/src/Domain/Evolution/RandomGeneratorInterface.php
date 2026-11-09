<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

interface RandomGeneratorInterface
{
    public function seed(int $seed): void;

    public function int(int $min, int $max): int;

    public function float(): float;
}
