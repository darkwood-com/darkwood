<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

final class Program
{
    /**
     * @param array{type:string,nodes:list<array<string,mixed>>} $ast
     */
    private function __construct(private readonly array $ast)
    {
    }

    /**
     * @param array<string,mixed> $ast
     */
    public static function fromArray(array $ast, ProgramValidator $validator): self
    {
        $validator->validate($ast);

        /** @var array{type:string,nodes:list<array<string,mixed>>} $ast */
        return new self($ast);
    }

    /**
     * @return array{type:string,nodes:list<array<string,mixed>>}
     */
    public function toArray(): array
    {
        return $this->ast;
    }
}
