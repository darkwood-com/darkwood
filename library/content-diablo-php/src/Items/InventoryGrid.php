<?php

declare(strict_types=1);

namespace Diablo\Items;

/**
 * Diablo inventory grid (10×4) + belt + cursor-held — footprint placement.
 * See NOTICE.md for attribution.
 */
final class InventoryGrid
{
    public const COLS = 10;
    public const ROWS = 4;
    public const SLOTS = 40;

    /**
     * @param list<?array<string,mixed>> $grid
     * @param array<string,mixed> $item
     * @return int|null slot index (top-left) or null
     */
    public static function findFit(array $grid, array $item): ?int
    {
        $w = max(1, (int) ($item['invW'] ?? 1));
        $h = max(1, (int) ($item['invH'] ?? 1));
        for ($y = 0; $y <= self::ROWS - $h; $y++) {
            for ($x = 0; $x <= self::COLS - $w; $x++) {
                if (self::canPlace($grid, $x, $y, $w, $h)) {
                    return $y * self::COLS + $x;
                }
            }
        }

        return null;
    }

    /**
     * @param list<?array<string,mixed>> $grid
     * @param array<string,mixed> $item
     * @return list<?array<string,mixed>>
     */
    public static function place(array $grid, array $item, int $slot): array
    {
        $w = max(1, (int) ($item['invW'] ?? 1));
        $h = max(1, (int) ($item['invH'] ?? 1));
        $ox = $slot % self::COLS;
        $oy = intdiv($slot, self::COLS);
        $item['_invAnchor'] = true;
        $item['_invW'] = $w;
        $item['_invH'] = $h;
        for ($dy = 0; $dy < $h; $dy++) {
            for ($dx = 0; $dx < $w; $dx++) {
                $idx = ($oy + $dy) * self::COLS + ($ox + $dx);
                $grid[$idx] = $dx === 0 && $dy === 0 ? $item : ['_invFill' => $slot];
            }
        }

        return $grid;
    }

    /** @param list<?array<string,mixed>> $grid */
    public static function canPlace(array $grid, int $x, int $y, int $w, int $h): bool
    {
        for ($dy = 0; $dy < $h; $dy++) {
            for ($dx = 0; $dx < $w; $dx++) {
                $idx = ($y + $dy) * self::COLS + ($x + $dx);
                if (($grid[$idx] ?? null) !== null) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Lift item at cell (resolves _invFill to anchor). Returns [newGrid, item|null].
     *
     * @param list<?array<string,mixed>> $grid
     * @return array{0:list<?array<string,mixed>>,1:?array<string,mixed>}
     */
    public static function pickAt(array $grid, int $slot): array
    {
        if ($slot < 0 || $slot >= self::SLOTS) {
            return [$grid, null];
        }
        $cell = $grid[$slot] ?? null;
        if ($cell === null) {
            return [$grid, null];
        }
        $anchor = $slot;
        if (isset($cell['_invFill'])) {
            $anchor = (int) $cell['_invFill'];
            $cell = $grid[$anchor] ?? null;
        }
        if ($cell === null || !empty($cell['_invFill'])) {
            return [$grid, null];
        }
        $w = max(1, (int) ($cell['_invW'] ?? $cell['invW'] ?? 1));
        $h = max(1, (int) ($cell['_invH'] ?? $cell['invH'] ?? 1));
        $ox = $anchor % self::COLS;
        $oy = intdiv($anchor, self::COLS);
        for ($dy = 0; $dy < $h; $dy++) {
            for ($dx = 0; $dx < $w; $dx++) {
                $idx = ($oy + $dy) * self::COLS + ($ox + $dx);
                $grid[$idx] = null;
            }
        }
        unset($cell['_invAnchor'], $cell['_invW'], $cell['_invH']);

        return [$grid, $cell];
    }
}
