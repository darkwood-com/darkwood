<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * IBSP constants from code/qcommon/qfiles.h (BSP_IDENT, BSP_VERSION, LUMP_*).
 */
final class BspIdent
{
    /** (('P'<<24)+('S'<<16)+('B'<<8)+'I') — little-endian on-disk "IBSP" */
    public const IDENT = 0x50534249;

    public const VERSION = 46;

    public const HEADER_LUMPS = 17;

    public const HEADER_SIZE = 144; // 2×int + 17×lump_t

    public const LUMP_ENTITIES = 0;
    public const LUMP_SHADERS = 1;
    public const LUMP_PLANES = 2;
    public const LUMP_NODES = 3;
    public const LUMP_LEAFS = 4;
    public const LUMP_LEAFSURFACES = 5;
    public const LUMP_LEAFBRUSHES = 6;
    public const LUMP_MODELS = 7;
    public const LUMP_BRUSHES = 8;
    public const LUMP_BRUSHSIDES = 9;
    public const LUMP_DRAWVERTS = 10;
    public const LUMP_DRAWINDEXES = 11;
    public const LUMP_FOGS = 12;
    public const LUMP_SURFACES = 13;
    public const LUMP_LIGHTMAPS = 14;
    public const LUMP_LIGHTGRID = 15;
    public const LUMP_VISIBILITY = 16;

    /** @var list<string> */
    public const LUMP_NAMES = [
        'entities',
        'shaders',
        'planes',
        'nodes',
        'leafs',
        'leafsurfaces',
        'leafbrushes',
        'models',
        'brushes',
        'brushsides',
        'drawverts',
        'drawindexes',
        'fogs',
        'surfaces',
        'lightmaps',
        'lightgrid',
        'visibility',
    ];

    public static function identToString(int $ident): string
    {
        return chr($ident & 0xFF)
            . chr(($ident >> 8) & 0xFF)
            . chr(($ident >> 16) & 0xFF)
            . chr(($ident >> 24) & 0xFF);
    }
}
