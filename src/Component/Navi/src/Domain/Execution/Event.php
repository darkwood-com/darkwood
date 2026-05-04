<?php

declare(strict_types=1);

namespace Navi\Domain\Execution;

use InvalidArgumentException;

final readonly class Event
{
    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(
        private string $name,
        private array $payload
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromName(string $name, array $payload = []): self
    {
        $normalized = trim($name);

        if ($normalized === '') {
            throw new InvalidArgumentException('Event name cannot be empty.');
        }

        return new self($normalized, $payload);
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
