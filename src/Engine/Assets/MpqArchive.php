<?php

declare(strict_types=1);

namespace Diablo\Engine\Assets;

/**
 * MPQ v1 archive reader.
 * MPQ reader adapted from mpqfs (MIT). See NOTICE.md.
 * MPQ reader usage patterns (see NOTICE.md).
 */
final class MpqArchive
{
    public const SIGNATURE = 0x1A51504D;
    public const HASH_EMPTY = 0xFFFFFFFF;
    public const HASH_DELETED = 0xFFFFFFFE;

    public const FILE_IMPLODE = 0x00000100;
    public const FILE_COMPRESS = 0x00000200;
    public const FILE_ENCRYPTED = 0x00010000;
    public const FILE_FIX_KEY = 0x00020000;
    public const FILE_SINGLE_UNIT = 0x01000000;
    public const FILE_EXISTS = 0x80000000;

    public const COMP_PKWARE = 0x08;
    public const COMP_ZLIB = 0x02;

    /** @var resource */
    private $fp;
    private int $archiveOffset = 0;
    private int $sectorSize = 4096;
    private int $hashCount = 0;
    private int $blockCount = 0;
    private int $archiveSize = 0;
    private int $formatVersion = 0;

    /** @var list<array{hashA:int,hashB:int,locale:int,platform:int,flags:int,blockIndex:int}> */
    private array $hashTable = [];

    /** @var list<array{offset:int,compressedSize:int,fileSize:int,flags:int}> */
    private array $blockTable = [];

    public function __construct(
        private readonly string $path,
    ) {
        MpqCrypto::init();
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            throw new \RuntimeException('Cannot open MPQ: ' . $path);
        }
        $this->fp = $fp;
        $this->archiveOffset = $this->findHeader();
        $this->readHeader();
        $this->loadTables();
    }

    public function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    public function path(): string
    {
        return $this->path;
    }

    public function archiveSize(): int
    {
        return $this->archiveSize;
    }

    public function hashTableCount(): int
    {
        return $this->hashCount;
    }

    public function blockTableCount(): int
    {
        return $this->blockCount;
    }

    public function sectorSize(): int
    {
        return $this->sectorSize;
    }

    public function formatVersion(): int
    {
        return $this->formatVersion;
    }

    public function hasFile(string $filename): bool
    {
        return $this->lookupBlockIndex($filename) !== null;
    }

    public function getFileSize(string $filename): int
    {
        $bi = $this->lookupBlockIndex($filename);
        if ($bi === null) {
            throw new \RuntimeException('File not found in MPQ: ' . $filename);
        }

        return $this->blockTable[$bi]['fileSize'];
    }

    public function readFile(string $filename): string
    {
        $bi = $this->lookupBlockIndex($filename);
        if ($bi === null) {
            throw new \RuntimeException('File not found in MPQ: ' . $filename);
        }

        return $this->readBlock($bi, $filename);
    }

    /** @return list<string> sample of filenames is not available from hash table alone */
    public function info(): array
    {
        return [
            'path' => $this->path,
            'archiveOffset' => $this->archiveOffset,
            'archiveSize' => $this->archiveSize,
            'formatVersion' => $this->formatVersion,
            'sectorSize' => $this->sectorSize,
            'hashTableCount' => $this->hashCount,
            'blockTableCount' => $this->blockCount,
        ];
    }

    private function findHeader(): int
    {
        $max = 128 * 1024 * 1024;
        for ($off = 0; $off < $max; $off += 512) {
            fseek($this->fp, $off);
            $raw = fread($this->fp, 4);
            if ($raw === false || strlen($raw) < 4) {
                break;
            }
            $sig = unpack('V', $raw)[1];
            if ($sig === self::SIGNATURE) {
                return $off;
            }
        }
        throw new \RuntimeException('MPQ signature not found');
    }

    private function readHeader(): void
    {
        fseek($this->fp, $this->archiveOffset);
        $raw = fread($this->fp, 32);
        if ($raw === false || strlen($raw) < 32) {
            throw new \RuntimeException('Truncated MPQ header');
        }
        $h = unpack('Vsignature/VheaderSize/VarchiveSize/vformatVersion/vsectorSizeShift/VhashTableOffset/VblockTableOffset/VhashTableCount/VblockTableCount', $raw);
        if ($h['signature'] !== self::SIGNATURE) {
            throw new \RuntimeException('Bad MPQ signature');
        }
        $this->archiveSize = $h['archiveSize'];
        $this->formatVersion = $h['formatVersion'];
        $this->sectorSize = 512 << $h['sectorSizeShift'];
        $this->hashCount = $h['hashTableCount'];
        $this->blockCount = $h['blockTableCount'];
        $this->hashTableOffset = $h['hashTableOffset'];
        $this->blockTableOffset = $h['blockTableOffset'];
    }

    private int $hashTableOffset = 0;
    private int $blockTableOffset = 0;

    private function loadTables(): void
    {
        $hashBytes = $this->hashCount * 16;
        fseek($this->fp, $this->archiveOffset + $this->hashTableOffset);
        $raw = fread($this->fp, $hashBytes);
        if ($raw === false || strlen($raw) !== $hashBytes) {
            throw new \RuntimeException('Failed to read hash table');
        }
        $key = MpqCrypto::hashString('(hash table)', MpqCrypto::HASH_FILE_KEY);
        $raw = MpqCrypto::decryptBinary($raw, $key);
        $this->hashTable = [];
        for ($i = 0; $i < $this->hashCount; $i++) {
            $e = unpack('VhashA/VhashB/vlocale/Cplatform/Cflags/VblockIndex', substr($raw, $i * 16, 16));
            $this->hashTable[] = [
                'hashA' => $e['hashA'],
                'hashB' => $e['hashB'],
                'locale' => $e['locale'],
                'platform' => $e['platform'],
                'flags' => $e['flags'],
                'blockIndex' => $e['blockIndex'],
            ];
        }

        $blockBytes = $this->blockCount * 16;
        fseek($this->fp, $this->archiveOffset + $this->blockTableOffset);
        $raw = fread($this->fp, $blockBytes);
        if ($raw === false || strlen($raw) !== $blockBytes) {
            throw new \RuntimeException('Failed to read block table');
        }
        $key = MpqCrypto::hashString('(block table)', MpqCrypto::HASH_FILE_KEY);
        $raw = MpqCrypto::decryptBinary($raw, $key);
        $this->blockTable = [];
        for ($i = 0; $i < $this->blockCount; $i++) {
            $e = unpack('Voffset/VcompressedSize/VfileSize/Vflags', substr($raw, $i * 16, 16));
            $this->blockTable[] = [
                'offset' => $e['offset'],
                'compressedSize' => $e['compressedSize'],
                'fileSize' => $e['fileSize'],
                'flags' => $e['flags'],
            ];
        }
    }

    private function lookupBlockIndex(string $filename): ?int
    {
        if ($this->hashCount === 0) {
            return null;
        }
        $index = MpqCrypto::hashString($filename, MpqCrypto::HASH_TABLE_INDEX) % $this->hashCount;
        $nameA = MpqCrypto::hashString($filename, MpqCrypto::HASH_NAME_A);
        $nameB = MpqCrypto::hashString($filename, MpqCrypto::HASH_NAME_B);
        $start = $index;
        for (;;) {
            $entry = $this->hashTable[$index];
            if ($entry['blockIndex'] === self::HASH_EMPTY) {
                return null;
            }
            if ($entry['blockIndex'] !== self::HASH_DELETED
                && $entry['hashA'] === $nameA
                && $entry['hashB'] === $nameB
            ) {
                if ($entry['blockIndex'] < $this->blockCount) {
                    return $entry['blockIndex'];
                }

                return null;
            }
            $index = ($index + 1) % $this->hashCount;
            if ($index === $start) {
                return null;
            }
        }
    }

    private function readBlock(int $blockIndex, string $filename): string
    {
        $block = $this->blockTable[$blockIndex];
        if (($block['flags'] & self::FILE_EXISTS) === 0) {
            throw new \RuntimeException('Block does not exist');
        }
        $fileSize = $block['fileSize'];
        if ($fileSize === 0) {
            return '';
        }

        $fileKey = 0;
        if ($block['flags'] & self::FILE_ENCRYPTED) {
            $fileKey = MpqCrypto::fileKey(
                $filename,
                $block['offset'],
                $block['fileSize'],
                ($block['flags'] & self::FILE_FIX_KEY) !== 0
            );
        }

        $sectorSize = $this->sectorSize;
        $flags = $block['flags'];
        $fileAbs = $this->archiveOffset + $block['offset'];

        if (($flags & self::FILE_SINGLE_UNIT) !== 0) {
            return $this->readSingleUnit($fileAbs, $block, $fileKey, $filename);
        }

        $sectorCount = intdiv($fileSize + $sectorSize - 1, $sectorSize);
        $sectorOffsets = null;
        if ($flags & (self::FILE_IMPLODE | self::FILE_COMPRESS)) {
            $tableEntries = $sectorCount + 1;
            $tableBytes = $tableEntries * 4;
            fseek($this->fp, $fileAbs);
            $rawTable = fread($this->fp, $tableBytes);
            if ($rawTable === false || strlen($rawTable) !== $tableBytes) {
                throw new \RuntimeException('Failed to read sector offset table');
            }
            if ($fileKey !== 0) {
                $rawTable = MpqCrypto::decryptBinary($rawTable, ($fileKey - 1) & 0xFFFFFFFF);
            }
            $sectorOffsets = [];
            for ($i = 0; $i < $tableEntries; $i++) {
                $sectorOffsets[] = unpack('V', substr($rawTable, $i * 4, 4))[1];
            }
            if ($sectorOffsets[0] !== $tableBytes) {
                throw new \RuntimeException('Corrupt sector offset table');
            }
        }

        $out = '';
        for ($s = 0; $s < $sectorCount; $s++) {
            $remaining = $fileSize - ($s * $sectorSize);
            $expect = min($sectorSize, $remaining);
            $out .= $this->loadSector($fileAbs, $s, $expect, $flags, $fileKey, $sectorOffsets);
        }

        return substr($out, 0, $fileSize);
    }

    /**
     * @param list<int>|null $sectorOffsets
     */
    private function loadSector(
        int $fileAbs,
        int $sectorIdx,
        int $expect,
        int $flags,
        int $fileKey,
        ?array $sectorOffsets,
    ): string {
        if ($flags & (self::FILE_IMPLODE | self::FILE_COMPRESS)) {
            if ($sectorOffsets === null) {
                throw new \RuntimeException('Missing sector offsets');
            }
            $sectorStart = $sectorOffsets[$sectorIdx];
            $sectorEnd = $sectorOffsets[$sectorIdx + 1];
            if ($sectorEnd < $sectorStart) {
                throw new \RuntimeException('Bad sector range');
            }
            $compSize = $sectorEnd - $sectorStart;
            if ($compSize === 0) {
                return str_repeat("\0", $expect);
            }
            fseek($this->fp, $fileAbs + $sectorStart);
            $comp = fread($this->fp, $compSize);
            if ($comp === false || strlen($comp) !== $compSize) {
                throw new \RuntimeException('Failed to read sector');
            }
            if ($fileKey !== 0) {
                $comp = MpqCrypto::decryptBinary($comp, ($fileKey + $sectorIdx) & 0xFFFFFFFF);
            }
            if ($compSize === $expect) {
                return $comp;
            }
            if ($flags & self::FILE_IMPLODE) {
                return MpqExplode::explodeSector($comp, $expect);
            }
            // Multi-compress
            $method = ord($comp[0]);
            $src = substr($comp, 1);
            if ($method & self::COMP_PKWARE) {
                return MpqExplode::explodeSector($src, $expect);
            }
            if ($method & self::COMP_ZLIB) {
                $decoded = zlib_decode($src);
                if ($decoded === false) {
                    throw new \RuntimeException('zlib decompress failed');
                }
                if (strlen($decoded) < $expect) {
                    $decoded .= str_repeat("\0", $expect - strlen($decoded));
                }

                return substr($decoded, 0, $expect);
            }
            throw new \RuntimeException(sprintf('Unsupported compression method 0x%02X', $method));
        }

        // Uncompressed
        fseek($this->fp, $fileAbs + ($sectorIdx * $this->sectorSize));
        $data = fread($this->fp, $expect);
        if ($data === false || strlen($data) !== $expect) {
            throw new \RuntimeException('Failed to read uncompressed sector');
        }
        if ($fileKey !== 0) {
            $data = MpqCrypto::decryptBinary($data, ($fileKey + $sectorIdx) & 0xFFFFFFFF);
        }

        return $data;
    }

    private function readSingleUnit(int $fileAbs, array $block, int $fileKey, string $filename): string
    {
        $compSize = $block['compressedSize'];
        $fileSize = $block['fileSize'];
        fseek($this->fp, $fileAbs);
        $data = fread($this->fp, $compSize);
        if ($data === false || strlen($data) !== $compSize) {
            throw new \RuntimeException('Failed to read single-unit file');
        }
        if ($fileKey !== 0) {
            $data = MpqCrypto::decryptBinary($data, $fileKey);
        }
        $flags = $block['flags'];
        if ($flags & self::FILE_IMPLODE) {
            return substr(MpqExplode::explode($data, $fileSize), 0, $fileSize);
        }
        if ($flags & self::FILE_COMPRESS) {
            $method = ord($data[0]);
            $src = substr($data, 1);
            if ($method & self::COMP_PKWARE) {
                return substr(MpqExplode::explode($src, $fileSize), 0, $fileSize);
            }
            if ($method & self::COMP_ZLIB) {
                $decoded = zlib_decode($src);
                if ($decoded === false) {
                    throw new \RuntimeException('zlib decompress failed');
                }

                return substr($decoded, 0, $fileSize);
            }
            throw new \RuntimeException('Unsupported single-unit compression');
        }

        return substr($data, 0, $fileSize);
    }
}
