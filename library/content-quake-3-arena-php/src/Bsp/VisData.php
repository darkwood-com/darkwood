<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * Visdata lump — cluster bitvectors (cm_load / tr_bsp subset).
 */
final class VisData
{
    public function __construct(
        public readonly int $numClusters,
        public readonly int $clusterBytes,
        public readonly string $bits,
    ) {
    }

    public static function load(BspMap $map): ?self
    {
        $bytes = $map->readLumpBytes(BspIdent::LUMP_VISIBILITY);
        if (strlen($bytes) < 8) {
            return null;
        }
        $numClusters = BspBinary::int32($bytes, 0);
        $clusterBytes = BspBinary::int32($bytes, 4);
        $bits = substr($bytes, 8);
        return new self($numClusters, $clusterBytes, $bits);
    }

    public function visible(int $fromCluster, int $testCluster): bool
    {
        if ($fromCluster < 0 || $testCluster < 0) {
            return true;
        }
        $offset = $fromCluster * $this->clusterBytes + ($testCluster >> 3);
        if ($offset < 0 || $offset >= strlen($this->bits)) {
            return true;
        }
        return (ord($this->bits[$offset]) & (1 << ($testCluster & 7))) !== 0;
    }
}
