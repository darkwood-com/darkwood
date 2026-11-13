<?php

declare(strict_types=1);

namespace Diablo\Monsters;

/**
 * monstdat.tsv ingest.
 */
final class Monstdat
{
    /** @var list<array<string,string>>|null */
    private static ?array $rows = null;

    /** @return list<array<string,string>> */
    public static function all(): array
    {
        if (self::$rows !== null) {
            return self::$rows;
        }
        $path = dirname(__DIR__, 2) . '/assets/txtdata/monsters/monstdat.tsv';
        if (!is_file($path)) {
            self::$rows = [];

            return self::$rows;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $header = str_getcsv(array_shift($lines) ?: '', "\t", '"', '\\');
        $rows = [];
        foreach ($lines as $line) {
            $cols = str_getcsv($line, "\t", '"', '\\');
            if (count($cols) < count($header)) {
                continue;
            }
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $cols[$i] ?? '';
            }
            $rows[] = $row;
        }
        self::$rows = $rows;

        return self::$rows;
    }

    /** Monsters available on cathedral dlvl 1–4. */
    /** @return list<array<string,string>> */
    public static function forCathedralLevel(int $dlvl): array
    {
        $out = [];
        foreach (self::all() as $row) {
            $min = (int) ($row['minDunLvl'] ?? 0);
            $max = (int) ($row['maxDunLvl'] ?? 0);
            if ($min <= $dlvl && $dlvl <= $max && ($row['availability'] ?? '') === 'Always') {
                $out[] = $row;
            }
        }

        return $out;
    }

    public static function cl2Path(array $row, string $modeLetter = 'n'): string
    {
        // assetsSuffix like zombie\zombie → Monsters\Monsters\Zombie\ZombieN.CL2 style varies;
        // uses Monsters\{folder}\{base}{mode}.CL2
        $suf = str_replace('/', '\\', $row['assetsSuffix'] ?? '');
        $parts = explode('\\', $suf);
        $base = $parts[count($parts) - 1] ?? 'monster';

        return 'monsters\\' . $suf . $modeLetter . '.cl2';
    }

    /**
     * MonsterGraphic index into frames[6] / rate[6]: Stand, Walk, Attack, GetHit, Death, Special.
     */
    public static function graphicIndex(string $mode): int
    {
        return match ($mode) {
            'WALK' => 1,
            'ATTACK', 'RANGED' => 2,
            'GOT_HIT' => 3,
            'DEATH' => 4,
            default => 0, // STAND
        };
    }

    /** @return list<int> */
    public static function parseIntList(string $csv, int $want = 6): array
    {
        $parts = array_map('intval', explode(',', $csv));
        while (count($parts) < $want) {
            $parts[] = 0;
        }

        return array_slice($parts, 0, $want);
    }

    /** Mode frame count from monstdat frames[6], falling back to $fallback. */
    public static function modeFrames(array $row, string $mode, int $fallback = 1): int
    {
        $frames = self::parseIntList((string) ($row['frames[6]'] ?? ''));
        $n = (int) ($frames[self::graphicIndex($mode)] ?? 0);

        return max(1, $n > 0 ? $n : $fallback);
    }

    /** Ticks per frame from monstdat rate[6]. */
    public static function modeRate(array $row, string $mode, int $fallback = 1): int
    {
        $rates = self::parseIntList((string) ($row['rate[6]'] ?? ''));
        $r = (int) ($rates[self::graphicIndex($mode)] ?? 0);

        return max(1, $r > 0 ? $r : $fallback);
    }

    /** 1-based attack hit frame (animFrameNum); convert to 0-based index for PHP. */
    public static function attackHitFrame(array $row, int $fallback = 5): int
    {
        $n = (int) ($row['animFrameNum'] ?? 0);

        return max(0, ($n > 0 ? $n : $fallback) - 1);
    }
}
