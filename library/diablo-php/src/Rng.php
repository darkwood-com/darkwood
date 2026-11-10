<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Diablo-compatible LCG RNG.
 * multiplier 0x015A4E35, increment 1.
 */
final class Rng
{
    private int $seed = 0;

    public function setSeed(int $seed): void
    {
        $this->seed = $seed & 0xFFFFFFFF;
    }

    public function getSeed(): int
    {
        return $this->seed;
    }

    public function generateRandomNumber(): int
    {
        // Borland LCG: seed = seed * 0x015A4E35 + 1
        $this->seed = (int) (($this->seed * 0x015A4E35 + 1) & 0xFFFFFFFF);

        return $this->seed;
    }

    public function advanceRndSeed(): int
    {
        $seed = $this->generateRandomNumber();
        // abs with INT_MIN quirk
        if ($seed === -2147483648 || $seed === 0x80000000) {
            return -2147483648;
        }
        // interpret as signed
        if ($seed >= 0x80000000) {
            $seed -= 0x100000000;
        }

        return abs($seed);
    }

    public function generateRnd(int $v): int
    {
        if ($v <= 0) {
            return 0;
        }
        if ($v <= 0x7FFF) {
            return ($this->advanceRndSeed() >> 16) % $v;
        }

        return $this->advanceRndSeed() % $v;
    }

    public function flipCoin(int $frequency = 2): bool
    {
        return $this->generateRnd($frequency) === 0;
    }

    public function between(int $min, int $max): int
    {
        if ($max < $min) {
            return $min;
        }

        return $min + $this->generateRnd($max - $min + 1);
    }

    /** Fisher–Yates shuffle using this LCG. */
    public function shuffle(array &$arr): void
    {
        $n = count($arr);
        for ($i = $n - 1; $i > 0; $i--) {
            $j = $this->generateRnd($i + 1);
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }
}
