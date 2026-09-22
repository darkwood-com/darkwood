<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform;

use Darkwood\AI\Platform\Exception\InvalidArgumentException;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
class Model
{
    /**
     * @param non-empty-string $name
     * @param list<Capability> $capabilities
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly string $name,
        private readonly array $capabilities = [],
        private readonly array $options = [],
    ) {
        if ('' === trim($name)) {
            throw new InvalidArgumentException('Model name cannot be empty.');
        }
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return list<Capability>
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function supports(Capability $capability): bool
    {
        return \in_array($capability, $this->capabilities, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
