<?php

declare(strict_types=1);

namespace Quake3\Filesystem;

/**
 * Result of resolving a qpath across the search path (first hit wins).
 */
final class ResolvedFile
{
    public function __construct(
        public readonly Pk3Entry $entry,
        public readonly Pk3Archive $archive,
    ) {
    }
}
