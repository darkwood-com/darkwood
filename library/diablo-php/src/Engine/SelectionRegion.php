<?php

declare(strict_types=1);

namespace Diablo\Engine;

/**
 * SelectionRegion bitflags for cursor targeting.
 */
final class SelectionRegion
{
    public const NONE = 0;
    public const BOTTOM = 1;
    public const MIDDLE = 2;
    public const TOP = 4;

    public static function parse(string $value): int
    {
        $flags = self::NONE;
        foreach (preg_split('/[|,]/', $value) ?: [] as $part) {
            $part = trim($part);
            if ($part === 'Bottom') {
                $flags |= self::BOTTOM;
            } elseif ($part === 'Middle') {
                $flags |= self::MIDDLE;
            } elseif ($part === 'Top') {
                $flags |= self::TOP;
            }
        }

        return $flags === self::NONE ? (self::BOTTOM | self::MIDDLE) : $flags;
    }

    public static function has(int $flags, int $region): bool
    {
        return ($flags & $region) !== 0;
    }
}
