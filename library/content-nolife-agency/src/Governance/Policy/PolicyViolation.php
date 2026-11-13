<?php

namespace App\Governance\Policy;

final readonly class PolicyViolation
{
    public function __construct(
        public string $rule,
        public string $message,
    ) {
    }
}
