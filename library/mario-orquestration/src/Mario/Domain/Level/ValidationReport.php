<?php

namespace App\Mario\Domain\Level;

final readonly class ValidationReport
{
    /**
     * @param list<string> $errors
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private array $errors = [],
        private array $warnings = [],
    ) {
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
