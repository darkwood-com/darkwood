<?php

declare(strict_types=1);

namespace App\Tests\Domain\Evolution;

use App\Domain\Evolution\RandomGeneratorInterface;

final class FakeRandomGenerator implements RandomGeneratorInterface
{
    /** @var list<int> */
    private array $ints;

    /** @var list<float> */
    private array $floats;

    /**
     * @param list<int> $ints
     * @param list<float> $floats
     */
    public function __construct(array $ints = [], array $floats = [])
    {
        $this->ints = $ints;
        $this->floats = $floats;
    }

    public function seed(int $seed): void
    {
    }

    public function int(int $min, int $max): int
    {
        if ($this->ints === []) {
            return $min;
        }

        return array_shift($this->ints);
    }

    public function float(): float
    {
        if ($this->floats === []) {
            return 0.0;
        }

        return array_shift($this->floats);
    }
}
