<?php

declare(strict_types=1);

namespace App\Compiler\Sema;

/**
 * A symbol table scope.
 */
final class Scope
{
    /** @var array<string, Symbol> */
    public array $symbols = [];

    public function __construct(
        public readonly ?Scope $parent = null,
        public readonly bool $isFunctionScope = false,
    ) {
    }

    public function lookup(string $name): ?Symbol
    {
        if (isset($this->symbols[$name])) {
            return $this->symbols[$name];
        }

        return $this->parent?->lookup($name);
    }

    public function lookupLocal(string $name): ?Symbol
    {
        return $this->symbols[$name] ?? null;
    }

    public function insert(Symbol $symbol): void
    {
        $this->symbols[$symbol->name] = $symbol;
    }
}
