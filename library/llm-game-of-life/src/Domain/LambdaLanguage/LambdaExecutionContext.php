<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

final readonly class LambdaExecutionContext
{
    public function __construct(
        public LambdaGridState $state,
    ) {
    }
}
