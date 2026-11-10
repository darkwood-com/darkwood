<?php
declare(strict_types=1);
namespace Diablo\Engine;
/**
 * Diablo LCG RNG. See NOTICE.md for attribution.
 */
final class Random
{
    private int $seed = 0;
    public function setRndSeed(int $seed): void { $this->seed = $seed & 0xFFFFFFFF; }
    public function getLcgEngineState(): int { return $this->seed; }
    public function generateRandomNumber(): int
    {
        $this->seed = (int)(($this->seed * 0x015A4E35 + 1) & 0xFFFFFFFF);
        return $this->seed;
    }
    public function advanceRndSeed(): int
    {
        $seed = $this->generateRandomNumber();
        if ($seed === 0x80000000 || $seed === -2147483648) return -2147483648;
        if ($seed >= 0x80000000) $seed -= 0x100000000;
        return abs((int)$seed);
    }
    public function generateRnd(int $v): int
    {
        if ($v <= 0) return 0;
        if ($v <= 0x7FFF) return ($this->advanceRndSeed() >> 16) % $v;
        return $this->advanceRndSeed() % $v;
    }
    public function flipCoin(int $frequency = 2): bool { return $this->generateRnd($frequency) === 0; }
    public function between(int $min, int $max): int
    {
        if ($max < $min) return $min;
        return $min + $this->generateRnd($max - $min + 1);
    }
 /** DiscardRandomValues — advances LCG without GenerateRnd mod. */
    public function discardRandomValues(int $count): void
    {
        while ($count > 0) {
            $this->generateRandomNumber();
            $count--;
        }
    }
 /** RandomIntLessThan — non-negative GenerateRnd. */
    public function randomIntLessThan(int $v): int
    {
        return max($this->generateRnd($v), 0);
    }
}
