<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Result;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class Metadata
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private array $values = [],
    ) {
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }
}
