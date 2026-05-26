<?php

declare(strict_types=1);

namespace Navi\Domain\Execution;

final class Context
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(private array $values) {}

    /**
     * @param array<string, mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function merge(array $values): self
    {
        return new self(array_replace($this->values, $values));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
