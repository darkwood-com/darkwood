<?php

declare(strict_types=1);

namespace App\Compiler\Parser;

use App\Compiler\Common\SourceLoc;

/** Parse-time error with source location. */
final class ParseError extends \Exception
{
    public function __construct(
        public readonly SourceLoc $loc,
        public readonly int $errorKind,
        public readonly string $detail1 = '',
        public readonly string $detail2 = '',
    ) {
        parent::__construct('', 0);
    }

    public const int KIND_UNEXPECTED = 0;
    public const int KIND_EXPECTED = 1;
    public const int KIND_UNDECLARED = 2;

    public static function unexpectedToken(string $msg, SourceLoc $loc): self
    {
        return new self($loc, self::KIND_UNEXPECTED, $msg);
    }

    public static function expected(string $exp, string $got, SourceLoc $loc): self
    {
        return new self($loc, self::KIND_EXPECTED, $exp, $got);
    }

    public static function undeclared(string $name, SourceLoc $loc): self
    {
        return new self($loc, self::KIND_UNDECLARED, $name);
    }
}
