<?php

declare(strict_types=1);

namespace App\Compiler\Sema;

use App\Compiler\Common\CType;
use App\Compiler\Common\Param;

enum SymbolKind
{
    case Variable;
    case Function;
    case Typedef;
    case EnumConstant;
}

/**
 * A symbol in the symbol table.
 */
final class Symbol
{
    /**
     * @param list<Param>|null $params
     */
    private function __construct(
        public readonly string $name,
        public readonly SymbolKind $kind,
        public readonly CType $type,
        public readonly bool $isGlobal = false,
        public readonly ?array $params = null,
        public readonly bool $variadic = false,
        public readonly bool $defined = false,
        public readonly ?int $enumValue = null,
    ) {
    }

    public static function variable(string $name, CType $type, bool $isGlobal): self
    {
        return new self(
            name: $name,
            kind: SymbolKind::Variable,
            type: $type,
            isGlobal: $isGlobal,
        );
    }

    /**
     * @param list<Param> $params
     */
    public static function function(
        string $name,
        CType $type,
        array $params,
        bool $variadic,
        bool $defined,
    ): self {
        return new self(
            name: $name,
            kind: SymbolKind::Function,
            type: $type,
            params: $params,
            variadic: $variadic,
            defined: $defined,
        );
    }

    public static function typedef(string $name, CType $type): self
    {
        return new self(
            name: $name,
            kind: SymbolKind::Typedef,
            type: $type,
        );
    }

    public static function enumConstant(string $name, int $value, CType $type): self
    {
        return new self(
            name: $name,
            kind: SymbolKind::EnumConstant,
            type: $type,
            enumValue: $value,
        );
    }
}
