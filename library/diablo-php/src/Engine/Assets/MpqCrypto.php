<?php

declare(strict_types=1);

namespace Diablo\Engine\Assets;

/**
 * MPQ cryptographic primitives.
 * MPQ reader adapted from mpqfs (MIT). See NOTICE.md.
 */
final class MpqCrypto
{
    public const HASH_TABLE_INDEX = 0x000;
    public const HASH_NAME_A = 0x100;
    public const HASH_NAME_B = 0x200;
    public const HASH_FILE_KEY = 0x300;
    public const HASH_KEY2_MIX = 0x400;

    /** @var list<int>|null */
    private static ?array $cryptTable = null;

    /** @var list<int> */
    private static array $asciiToUpper = [];

    public static function init(): void
    {
        if (self::$cryptTable !== null) {
            return;
        }
        self::$asciiToUpper = [];
        for ($i = 0; $i < 256; $i++) {
            self::$asciiToUpper[$i] = $i;
        }
        for ($i = 0x61; $i <= 0x7A; $i++) {
            self::$asciiToUpper[$i] = $i - 0x20;
        }
        self::$asciiToUpper[0x2F] = 0x5C; // '/' -> '\'

        $table = array_fill(0, 0x500, 0);
        $seed = 0x00100001;
        for ($index1 = 0; $index1 < 0x100; $index1++) {
            $index2 = $index1;
            for ($i = 0; $i < 5; $i++, $index2 += 0x100) {
                $seed = ($seed * 125 + 3) % 0x2AAAAB;
                $temp1 = ($seed & 0xFFFF) << 0x10;
                $seed = ($seed * 125 + 3) % 0x2AAAAB;
                $temp2 = $seed & 0xFFFF;
                $table[$index2] = ($temp1 | $temp2) & 0xFFFFFFFF;
            }
        }
        self::$cryptTable = $table;
    }

    public static function hashString(string $str, int $hashType): int
    {
        self::init();
        $seed1 = 0x7FED7FED;
        $seed2 = 0xEEEEEEEE;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $ch = self::$asciiToUpper[ord($str[$i])];
            $seed1 = (self::$cryptTable[$hashType + $ch] ^ (($seed1 + $seed2) & 0xFFFFFFFF)) & 0xFFFFFFFF;
            $seed2 = ($ch + $seed1 + $seed2 + (($seed2 << 5) & 0xFFFFFFFF) + 3) & 0xFFFFFFFF;
        }

        return $seed1;
    }

    /** @param list<int> $data uint32 words */
    public static function decryptBlock(array &$data, int $key): void
    {
        self::init();
        $seed = 0xEEEEEEEE;
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $seed = ($seed + self::$cryptTable[self::HASH_KEY2_MIX + ($key & 0xFF)]) & 0xFFFFFFFF;
            $ch = ($data[$i] ^ (($key + $seed) & 0xFFFFFFFF)) & 0xFFFFFFFF;
            $data[$i] = $ch;
            $key = (((~$key << 0x15) + 0x11111111) & 0xFFFFFFFF) | (($key >> 0x0B) & 0xFFFFFFFF);
            $key &= 0xFFFFFFFF;
            $seed = ($ch + $seed + (($seed << 5) & 0xFFFFFFFF) + 3) & 0xFFFFFFFF;
        }
    }

    public static function fileKey(string $path, int $blockOffset, int $fileSize, bool $adjust): int
    {
        $name = $path;
        $pos = strrpos(str_replace('/', '\\', $path), '\\');
        if ($pos !== false) {
            $name = substr($path, $pos + 1);
        }
        $key = self::hashString($name, self::HASH_FILE_KEY);
        if ($adjust) {
            $key = (($key + $blockOffset) ^ $fileSize) & 0xFFFFFFFF;
        }

        return $key;
    }

    /** Decrypt raw little-endian byte string of uint32s; returns binary. */
    public static function decryptBinary(string $bin, int $key): string
    {
        $n = intdiv(strlen($bin), 4);
        $words = [];
        for ($i = 0; $i < $n; $i++) {
            $words[] = unpack('V', substr($bin, $i * 4, 4))[1];
        }
        self::decryptBlock($words, $key);
        $out = '';
        foreach ($words as $w) {
            $out .= pack('V', $w);
        }
        // leftover bytes unchanged
        if (strlen($bin) % 4 !== 0) {
            $out .= substr($bin, $n * 4);
        }

        return $out;
    }
}
