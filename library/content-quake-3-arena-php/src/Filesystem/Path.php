<?php

declare(strict_types=1);

namespace Quake3\Filesystem;

/**
 * Qpath helpers mirroring Quake III filesystem naming rules.
 *
 * References:
 * - FS_FilenameCompare / FS_PathCmp (code/qcommon/files.c)
 * - FS_FOpenFileRead leading-slash strip and ".." / "::" rejection
 * - MAX_ZPATH = 256
 */
final class Path
{
    public const MAX_ZPATH = 256;

    /**
     * Normalize a qpath for indexing/lookup: strip leading separators,
     * fold case, map '\' and ':' to '/', reject path escape sequences.
     *
     * @throws \InvalidArgumentException on empty, overlong, or illegal paths
     */
    public static function normalize(string $qpath): string
    {
        // FS_FOpenFileRead: qpaths are not supposed to have a leading slash
        while ($qpath !== '' && ($qpath[0] === '/' || $qpath[0] === '\\')) {
            $qpath = substr($qpath, 1);
        }

        if ($qpath === '') {
            throw new \InvalidArgumentException('Empty qpath');
        }

        // make absolutely sure that it can't back up the path
        if (str_contains($qpath, '..') || str_contains($qpath, '::')) {
            throw new \InvalidArgumentException(sprintf('Illegal qpath: %s', $qpath));
        }

        $out = '';
        $len = strlen($qpath);
        for ($i = 0; $i < $len; $i++) {
            $c = $qpath[$i];
            if ($c === '\\' || $c === ':') {
                $c = '/';
            }
            $out .= strtolower($c);
        }

        // MAX_ZPATH includes terminating zero in C; usable chars are 255
        if (strlen($out) >= self::MAX_ZPATH) {
            throw new \InvalidArgumentException(sprintf('qpath exceeds MAX_ZPATH (%d)', self::MAX_ZPATH));
        }

        return $out;
    }

    /**
     * FS_FilenameCompare — ignore case and separator distinctions.
     * Returns true when paths are equal under Quake III rules.
     */
    public static function equals(string $a, string $b): bool
    {
        return self::compare($a, $b) === 0;
    }

    /**
     * FS_PathCmp-style ordering: -1 / 0 / 1 with case and separator folding.
     * Used by paksort so later alphabetic matches override earlier ones.
     */
    public static function compare(string $s1, string $s2): int
    {
        $i = 0;
        $len1 = strlen($s1);
        $len2 = strlen($s2);

        while (true) {
            $c1 = $i < $len1 ? ord($s1[$i]) : 0;
            $c2 = $i < $len2 ? ord($s2[$i]) : 0;

            if ($c1 >= ord('a') && $c1 <= ord('z')) {
                $c1 -= (ord('a') - ord('A'));
            }
            if ($c2 >= ord('a') && $c2 <= ord('z')) {
                $c2 -= (ord('a') - ord('A'));
            }

            if ($c1 === ord('\\') || $c1 === ord(':')) {
                $c1 = ord('/');
            }
            if ($c2 === ord('\\') || $c2 === ord(':')) {
                $c2 = ord('/');
            }

            if ($c1 < $c2) {
                return -1;
            }
            if ($c1 > $c2) {
                return 1;
            }
            if ($c1 === 0) {
                return 0;
            }
            $i++;
        }
    }
}
