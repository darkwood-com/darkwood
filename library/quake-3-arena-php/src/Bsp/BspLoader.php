<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * Load IBSP from memory — header path shared by CM_LoadMap / RE_LoadWorldMap.
 *
 * Validates ident + version (stricter than stock C, which often checks version only)
 * and lump bounds against the buffer length.
 */
final class BspLoader
{
    public function loadFromString(string $bytes, string $sourceLabel = ''): BspMap
    {
        $size = strlen($bytes);
        if ($size < BspIdent::HEADER_SIZE) {
            throw new \RuntimeException(sprintf(
                'BSP too small (%d bytes); need at least %d for dheader_t%s',
                $size,
                BspIdent::HEADER_SIZE,
                $sourceLabel !== '' ? " ({$sourceLabel})" : '',
            ));
        }

        $head = unpack('Vident/Vversion', substr($bytes, 0, 8));
        if ($head === false) {
            throw new \RuntimeException('Failed to unpack BSP header');
        }

        $ident = $head['ident'];
        $version = $head['version'];

        if ($ident !== BspIdent::IDENT) {
            throw new \RuntimeException(sprintf(
                'Bad BSP ident 0x%08X (%s); expected IBSP (0x%08X)%s',
                $ident,
                BspIdent::identToString($ident),
                BspIdent::IDENT,
                $sourceLabel !== '' ? " ({$sourceLabel})" : '',
            ));
        }

        if ($version !== BspIdent::VERSION) {
            throw new \RuntimeException(sprintf(
                'Unsupported BSP version %d; expected %d%s',
                $version,
                BspIdent::VERSION,
                $sourceLabel !== '' ? " ({$sourceLabel})" : '',
            ));
        }

        $lumps = [];
        $offset = 8;
        for ($i = 0; $i < BspIdent::HEADER_LUMPS; $i++) {
            $chunk = unpack('Vfileofs/Vfilelen', substr($bytes, $offset, 8));
            if ($chunk === false) {
                throw new \RuntimeException(sprintf('Failed to unpack lump %d', $i));
            }
            $fileofs = $chunk['fileofs'];
            $filelen = $chunk['filelen'];

            if ($fileofs < 0 || $filelen < 0) {
                throw new \RuntimeException(sprintf(
                    'Lump %s (%d) has negative ofs/len (%d, %d)',
                    BspIdent::LUMP_NAMES[$i],
                    $i,
                    $fileofs,
                    $filelen,
                ));
            }

            // unsigned V can be large; reject ranges that exceed file
            if ($filelen > 0) {
                $end = $fileofs + $filelen;
                if ($fileofs > $size || $end > $size || $end < $fileofs) {
                    throw new \RuntimeException(sprintf(
                        'Lump %s (%d) out of bounds: ofs=%d len=%d filesize=%d',
                        BspIdent::LUMP_NAMES[$i],
                        $i,
                        $fileofs,
                        $filelen,
                        $size,
                    ));
                }
            }

            $lumps[] = new BspLump(
                index: $i,
                name: BspIdent::LUMP_NAMES[$i],
                fileofs: $fileofs,
                filelen: $filelen,
            );
            $offset += 8;
        }

        return new BspMap(
            header: new BspHeader($ident, $version, $lumps),
            bytes: $bytes,
            sourceLabel: $sourceLabel,
        );
    }

    public function loadFromFile(string $path): BspMap
    {
        $real = realpath($path);
        if ($real === false || !is_file($real)) {
            throw new \RuntimeException(sprintf('BSP file not found: %s', $path));
        }
        $bytes = file_get_contents($real);
        if ($bytes === false) {
            throw new \RuntimeException(sprintf('Failed to read BSP: %s', $real));
        }

        return $this->loadFromString($bytes, $real);
    }
}
