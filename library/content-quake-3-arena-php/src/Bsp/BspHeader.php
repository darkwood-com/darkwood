<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * dheader_t — ident, version, lumps[HEADER_LUMPS].
 */
final class BspHeader
{
    /**
     * @param list<BspLump> $lumps exactly HEADER_LUMPS entries
     */
    public function __construct(
        public readonly int $ident,
        public readonly int $version,
        public readonly array $lumps,
    ) {
        if (count($lumps) !== BspIdent::HEADER_LUMPS) {
            throw new \InvalidArgumentException('dheader_t requires 17 lumps');
        }
    }

    public function identString(): string
    {
        return BspIdent::identToString($this->ident);
    }
}
