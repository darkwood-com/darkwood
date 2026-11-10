<?php

declare(strict_types=1);

namespace App\Compiler\Common;

/**
 * Represents a C type. Recursive types (pointers, arrays, functions) use object references.
 */
abstract class CType
{
    public function isInteger(): bool
    {
        return match (true) {
            $this instanceof BoolCType,
            $this instanceof CharCType,
            $this instanceof SCharCType,
            $this instanceof UCharCType,
            $this instanceof ShortCType,
            $this instanceof UShortCType,
            $this instanceof IntCType,
            $this instanceof UIntCType,
            $this instanceof LongCType,
            $this instanceof ULongCType,
            $this instanceof LongLongCType,
            $this instanceof ULongLongCType,
            $this instanceof EnumCType => true,
            $this instanceof QualifiedCType => $this->base->isInteger(),
            $this instanceof TypedefCType => $this->base->isInteger(),
            default => false,
        };
    }

    public function isFloating(): bool
    {
        return match (true) {
            $this instanceof FloatCType,
            $this instanceof DoubleCType,
            $this instanceof LongDoubleCType => true,
            $this instanceof QualifiedCType => $this->base->isFloating(),
            $this instanceof TypedefCType => $this->base->isFloating(),
            default => false,
        };
    }

    public function isArithmetic(): bool
    {
        return $this->isInteger() || $this->isFloating();
    }

    public function isSigned32Bit(): bool
    {
        return match (true) {
            $this instanceof IntCType,
            $this instanceof ShortCType,
            $this instanceof SCharCType,
            $this instanceof CharCType,
            $this instanceof EnumCType => true,
            $this instanceof QualifiedCType => $this->base->isSigned32Bit(),
            $this instanceof TypedefCType => $this->base->isSigned32Bit(),
            default => false,
        };
    }

    public function isChar(): bool
    {
        return match (true) {
            $this instanceof CharCType,
            $this instanceof SCharCType,
            $this instanceof UCharCType => true,
            $this instanceof QualifiedCType => $this->base->isChar(),
            $this instanceof TypedefCType => $this->base->isChar(),
            default => false,
        };
    }

    public function isScalar(): bool
    {
        return $this->isArithmetic() || $this->isPointer();
    }

    public function isPointer(): bool
    {
        return match (true) {
            $this instanceof PointerCType => true,
            $this instanceof QualifiedCType => $this->base->isPointer(),
            $this instanceof TypedefCType => $this->base->isPointer(),
            default => false,
        };
    }

    public function isVoid(): bool
    {
        return match (true) {
            $this instanceof VoidCType => true,
            $this instanceof QualifiedCType => $this->base->isVoid(),
            $this instanceof TypedefCType => $this->base->isVoid(),
            default => false,
        };
    }

    public function isFunction(): bool
    {
        return match (true) {
            $this instanceof FunctionCType => true,
            $this instanceof QualifiedCType => $this->base->isFunction(),
            $this instanceof TypedefCType => $this->base->isFunction(),
            default => false,
        };
    }

    public function isArray(): bool
    {
        return match (true) {
            $this instanceof ArrayCType,
            $this instanceof IncompleteArrayCType => true,
            $this instanceof QualifiedCType => $this->base->isArray(),
            $this instanceof TypedefCType => $this->base->isArray(),
            default => false,
        };
    }

    public function isStruct(): bool
    {
        return match (true) {
            $this instanceof StructCType => true,
            $this instanceof QualifiedCType => $this->base->isStruct(),
            $this instanceof TypedefCType => $this->base->isStruct(),
            default => false,
        };
    }

    public function isUnion(): bool
    {
        return match (true) {
            $this instanceof UnionCType => true,
            $this instanceof QualifiedCType => $this->base->isUnion(),
            $this instanceof TypedefCType => $this->base->isUnion(),
            default => false,
        };
    }

    public function isEnum(): bool
    {
        return match (true) {
            $this instanceof EnumCType => true,
            $this instanceof QualifiedCType => $this->base->isEnum(),
            $this instanceof TypedefCType => $this->base->isEnum(),
            default => false,
        };
    }

    public function unqualified(): CType
    {
        return match (true) {
            $this instanceof QualifiedCType => $this->base->unqualified(),
            $this instanceof TypedefCType => $this->base->unqualified(),
            default => $this,
        };
    }

    public function stripQualifiers(): CType
    {
        return match (true) {
            $this instanceof QualifiedCType => $this->base->stripQualifiers(),
            default => $this,
        };
    }

    public function sizeInBytes(): ?int
    {
        return match (true) {
            $this instanceof VoidCType => 1,
            $this instanceof BoolCType,
            $this instanceof CharCType,
            $this instanceof SCharCType,
            $this instanceof UCharCType => 1,
            $this instanceof ShortCType,
            $this instanceof UShortCType => 2,
            $this instanceof IntCType,
            $this instanceof UIntCType,
            $this instanceof FloatCType => 4,
            $this instanceof LongCType,
            $this instanceof ULongCType,
            $this instanceof LongLongCType,
            $this instanceof ULongLongCType,
            $this instanceof DoubleCType,
            $this instanceof LongDoubleCType => 8,
            $this instanceof PointerCType,
            $this instanceof FunctionCType => 8,
            $this instanceof ArrayCType => ($elemSize = $this->of->sizeInBytes()) !== null
                ? $elemSize * $this->count
                : null,
            $this instanceof IncompleteArrayCType => null,
            $this instanceof StructCType,
            $this instanceof UnionCType => $this->record->size,
            $this instanceof EnumCType => 4,
            $this instanceof QualifiedCType => $this->base->sizeInBytes(),
            $this instanceof TypedefCType => $this->base->sizeInBytes(),
            default => null,
        };
    }

    public function alignOf(): ?int
    {
        return match (true) {
            $this instanceof VoidCType => 1,
            $this instanceof BoolCType,
            $this instanceof CharCType,
            $this instanceof SCharCType,
            $this instanceof UCharCType => 1,
            $this instanceof ShortCType,
            $this instanceof UShortCType => 2,
            $this instanceof IntCType,
            $this instanceof UIntCType,
            $this instanceof FloatCType => 4,
            $this instanceof LongCType,
            $this instanceof ULongCType,
            $this instanceof LongLongCType,
            $this instanceof ULongLongCType,
            $this instanceof DoubleCType,
            $this instanceof LongDoubleCType => 8,
            $this instanceof PointerCType,
            $this instanceof FunctionCType => 8,
            $this instanceof ArrayCType => $this->of->alignOf(),
            $this instanceof IncompleteArrayCType => $this->of->alignOf(),
            $this instanceof StructCType => $this->record->alignment,
            $this instanceof UnionCType => $this->record->alignment,
            $this instanceof EnumCType => 4,
            $this instanceof QualifiedCType => $this->base->alignOf(),
            $this instanceof TypedefCType => $this->base->alignOf(),
            default => null,
        };
    }

    public function isSigned(): bool
    {
        return match (true) {
            $this instanceof CharCType,
            $this instanceof SCharCType,
            $this instanceof ShortCType,
            $this instanceof IntCType,
            $this instanceof LongCType,
            $this instanceof LongLongCType => true,
            $this instanceof BoolCType,
            $this instanceof UCharCType,
            $this instanceof UShortCType,
            $this instanceof UIntCType,
            $this instanceof ULongCType,
            $this instanceof ULongLongCType => false,
            $this instanceof QualifiedCType => $this->base->isSigned(),
            $this instanceof TypedefCType => $this->base->isSigned(),
            default => false,
        };
    }

    public function isConst(): bool
    {
        return $this instanceof QualifiedCType && $this->const;
    }
}

final class VoidCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class BoolCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class CharCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class SCharCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class UCharCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class ShortCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class UShortCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class IntCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class UIntCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class LongCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class ULongCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class LongLongCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class ULongLongCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class FloatCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class DoubleCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class LongDoubleCType extends CType
{
    public static self $instance;

    public function __construct()
    {
    }
}

final class PointerCType extends CType
{
    public function __construct(
        public CType $to,
    ) {
    }
}

final class ArrayCType extends CType
{
    public function __construct(
        public CType $of,
        public int $count,
    ) {
    }
}

final class IncompleteArrayCType extends CType
{
    public function __construct(
        public CType $of,
    ) {
    }
}

final class FunctionCType extends CType
{
    /**
     * @param list<CType> $params
     */
    public function __construct(
        public array $params,
        public CType $returnType,
        public bool $variadic,
    ) {
    }
}

final class StructCType extends CType
{
    public function __construct(
        public RecordType $record,
    ) {
    }
}

final class UnionCType extends CType
{
    public function __construct(
        public RecordType $record,
    ) {
    }
}

final class EnumCType extends CType
{
    public function __construct(
        public EnumType $enumType,
    ) {
    }
}

final class TypedefCType extends CType
{
    public function __construct(
        public string $name,
        public CType $base,
    ) {
    }
}

final class QualifiedCType extends CType
{
    public function __construct(
        public CType $base,
        public bool $const,
        public bool $volatile,
        public bool $restrict,
    ) {
    }
}

final class RecordType
{
    /**
     * @param list<RecordField> $fields
     */
    public function __construct(
        public string $name,
        public array $fields = [],
        public ?int $size = null,
        public ?int $alignment = null,
    ) {
    }
}

final readonly class RecordField
{
    public function __construct(
        public ?string $name,
        public CType $type,
        public ?int $bitWidth = null,
        public int $offset = 0,
        public int $bitOffset = 0,
    ) {
    }
}

final class EnumType
{
    public CType $underlyingType;

    /**
     * @param list<EnumCase> $cases
     */
    public function __construct(
        public string $name,
        public array $cases = [],
        ?CType $underlyingType = null,
    ) {
        $this->underlyingType = $underlyingType ?? IntCType::$instance;
    }
}

final readonly class EnumCase
{
    public function __construct(
        public string $name,
        public int $value,
    ) {
    }
}

VoidCType::$instance = new VoidCType();
BoolCType::$instance = new BoolCType();
CharCType::$instance = new CharCType();
SCharCType::$instance = new SCharCType();
UCharCType::$instance = new UCharCType();
ShortCType::$instance = new ShortCType();
UShortCType::$instance = new UShortCType();
IntCType::$instance = new IntCType();
UIntCType::$instance = new UIntCType();
LongCType::$instance = new LongCType();
ULongCType::$instance = new ULongCType();
LongLongCType::$instance = new LongLongCType();
ULongLongCType::$instance = new ULongLongCType();
FloatCType::$instance = new FloatCType();
DoubleCType::$instance = new DoubleCType();
LongDoubleCType::$instance = new LongDoubleCType();
