<?php

declare(strict_types=1);

namespace App\Flow\IO;

/**
 * Seconds-based duration helper. Prefer Time\Duration on PHP 8.6+.
 */
final readonly class Duration
{
    public function __construct(
        public float $seconds,
    ) {}

    public static function fromSeconds(float $seconds): self
    {
        return new self($seconds);
    }

    public static function fromMilliseconds(int $ms): self
    {
        return new self($ms / 1000);
    }

    public function multiply(float $factor): self
    {
        return new self($this->seconds * $factor);
    }

    public function compare(self $other): int
    {
        return $this->seconds <=> $other->seconds;
    }

    public function toSeconds(): float
    {
        return $this->seconds;
    }

    public function toNative(): mixed
    {
        if (class_exists(\Time\Duration::class)) {
            return \Time\Duration::fromTotalMicroseconds((int) ($this->seconds * 1_000_000));
        }

        return $this->seconds;
    }
}
