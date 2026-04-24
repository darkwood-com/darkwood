<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

final readonly class Individual
{
    /**
     * @param array{type:string,nodes:list<array<string,mixed>>} $programAst
     */
    public function __construct(
        public array $programAst,
        public float $fitness,
    ) {
    }
}
