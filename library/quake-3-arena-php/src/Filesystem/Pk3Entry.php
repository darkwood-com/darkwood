<?php

declare(strict_types=1);

namespace Quake3\Filesystem;

/**
 * Indexed member of a PK3 archive (fileInPack_t analogue + Zip metadata).
 */
final class Pk3Entry
{
    public function __construct(
        public readonly string $name,
        public readonly int $zipIndex,
        public readonly int $compressedSize,
        public readonly int $uncompressedSize,
        public readonly int $compressionMethod,
        public readonly string $archivePath,
        public readonly string $archiveBasename,
    ) {
    }
}
