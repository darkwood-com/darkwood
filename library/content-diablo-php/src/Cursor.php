<?php

declare(strict_types=1);

namespace Diablo;

use Diablo\Engine\Assets\Cel;
use Diablo\Engine\Assets\Palette;
use Diablo\Items\ItemDat;

/**
 * Software mouse cursor — DevilutionX Source/cursor.h + cursor.cpp parity.
 *
 * Asset: data\inv\objcurs.cel
 * Frame index = pcurs - 1
 * Tool frames 0–10 (pcurs 1–11); item frames = iCurs + 11 (pcurs = iCurs + CURSOR_FIRSTITEM)
 *
 * Hover targets (pcursmonst / pcursitem / …) do NOT change the sprite.
 */
final class Cursor
{
    public const CURSOR_NONE = 0;
    public const CURSOR_HAND = 1;
    public const CURSOR_IDENTIFY = 2;
    public const CURSOR_REPAIR = 3;
    public const CURSOR_RECHARGE = 4;
    public const CURSOR_DISARM = 5;
    public const CURSOR_OIL = 6;
    public const CURSOR_TELEKINESIS = 7;
    public const CURSOR_RESURRECT = 8;
    public const CURSOR_TELEPORT = 9;
    public const CURSOR_HEALOTHER = 10;
    public const CURSOR_HOURGLASS = 11;
    public const CURSOR_FIRSTITEM = 12;

    public const ASSET_PATH = 'data\\inv\\objcurs.cel';

    /** Per-frame decode widths from DevilutionX assets/data/inv/objcurs-widths.txt (179 frames). */
    private const WIDTHS = [
        33, 32, 32, 32, 32, 32, 32, 32, 32, 32, 23,
        // frames 11–85: 28px (75 frames)
        28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28,
        28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28,
        28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28,
        28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28,
        // frames 86–178: 56px (93 frames)
        56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56,
        56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56,
        56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56,
        56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56,
        56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56, 56,
    ];

    /** Current cursor sprite id (pcurs). */
    public int $pcurs = self::CURSOR_HAND;

    /** Palette epoch for texture cache invalidation. */
    public int $paletteEpoch = 0;

    public static function frameIndex(int $pcurs): int
    {
        return max(0, $pcurs - 1);
    }

    public static function frameWidth(int $frame): int
    {
        return self::WIDTHS[$frame] ?? 28;
    }

    public static function numKnownWidths(): int
    {
        return count(self::WIDTHS);
    }

    /**
     * pcurs for a held inventory item (NewCursor(Item)).
     * @param array<string,mixed> $item
     */
    public static function pcursForItem(array $item): int
    {
        if (isset($item['objcursFrame']) && is_int($item['objcursFrame'])) {
            return (int) $item['objcursFrame'] + 1;
        }
        $gname = (string) ($item['cursorGraphic'] ?? '');
        if ($gname === '' && ($item['type'] ?? '') === 'gold') {
            $gname = 'GOLD_SMALL';
        }
        $frame = ItemDat::cursorGraphicFrame($gname);

        return $frame + 1; // frame is 0-based objcurs index (= iCurs+11); pcurs = frame+1
    }

    public function newCursor(int $cursId): void
    {
        $this->pcurs = max(self::CURSOR_NONE, $cursId);
    }

    /** @param array<string,mixed>|null $item */
    public function newCursorFromItem(?array $item): void
    {
        if ($item === null) {
            $this->newCursor(self::CURSOR_HAND);

            return;
        }
        $this->newCursor(self::pcursForItem($item));
    }

    public function initLevelCursor(): void
    {
        $this->newCursor(self::CURSOR_HAND);
    }

    /**
     * Hotspot offset from mouse to sprite top-left (DrawCursor semantics).
     * Tools: top-left. Held item: center.
     *
     * @return array{x:int,y:int}
     */
    public static function hotspotOffset(int $pcurs, int $w, int $h, bool $holdingItem): array
    {
        if ($holdingItem && $pcurs >= self::CURSOR_FIRSTITEM) {
            return ['x' => intdiv($w, 2), 'y' => intdiv($h, 2)];
        }

        return ['x' => 0, 'y' => 0];
    }

    /**
     * Texture / decode cache key — must not collide with ground it* / belt / inv keys.
     */
    public static function cacheKey(int $frame, int $width, int $paletteEpoch): string
    {
        return 'objcurs:f' . $frame . ':w' . $width . ':p' . $paletteEpoch;
    }

    /**
     * Decode one objcurs frame with the authoritative width.
     *
     * @return array{w:int,h:int,rgba:string,frame:int,width:int}
     */
    public static function decodeSprite(Cel $cel, int $pcurs, Palette $pal): array
    {
        if ($pcurs <= self::CURSOR_NONE) {
            return ['w' => 0, 'h' => 0, 'rgba' => '', 'frame' => -1, 'width' => 0];
        }
        $frame = self::frameIndex($pcurs);
        if ($frame < 0 || $frame >= $cel->numFrames) {
            throw new \RuntimeException("objcurs frame OOB: pcurs={$pcurs} frame={$frame} n={$cel->numFrames}");
        }
        $width = self::frameWidth($frame);
        $fr = $cel->decodeFrame($frame, $width, $pal);

        return [
            'w' => (int) $fr['w'],
            'h' => (int) $fr['h'],
            'rgba' => (string) $fr['rgba'],
            'frame' => $frame,
            'width' => $width,
        ];
    }

    /** @return list<int> */
    public static function allWidths(): array
    {
        return self::WIDTHS;
    }

    public static function nameFor(int $pcurs): string
    {
        return match ($pcurs) {
            self::CURSOR_NONE => 'NONE',
            self::CURSOR_HAND => 'HAND',
            self::CURSOR_IDENTIFY => 'IDENTIFY',
            self::CURSOR_REPAIR => 'REPAIR',
            self::CURSOR_RECHARGE => 'RECHARGE',
            self::CURSOR_DISARM => 'DISARM',
            self::CURSOR_OIL => 'OIL',
            self::CURSOR_TELEKINESIS => 'TELEKINESIS',
            self::CURSOR_RESURRECT => 'RESURRECT',
            self::CURSOR_TELEPORT => 'TELEPORT',
            self::CURSOR_HEALOTHER => 'HEALOTHER',
            self::CURSOR_HOURGLASS => 'HOURGLASS',
            default => $pcurs >= self::CURSOR_FIRSTITEM ? 'ITEM+' . ($pcurs - self::CURSOR_FIRSTITEM) : 'UNKNOWN',
        };
    }
}
