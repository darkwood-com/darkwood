<?php

declare(strict_types=1);

namespace Diablo\Levels;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Assets\Palette;
use Diablo\Engine\Random;
use Diablo\Engine\Render\DunMicro;

/**
 * Diablo dungeon grids (gendung ).
 * See NOTICE.md for attribution.
 */
final class DungeonMap
{
    public const DMAXX = 40;
    public const DMAXY = 40;
    public const MAXDUNX = 112; // 16 + 40*2 + 16
    public const MAXDUNY = 112;

    public const DTYPE_TOWN = 0;
    public const DTYPE_CATHEDRAL = 1;
    public const DTYPE_CATACOMBS = 2;
    public const DTYPE_CAVES = 3;
    public const DTYPE_HELL = 4;

    public int $levelType = self::DTYPE_TOWN;
    public int $currLevel = 0;

    /** @var list<list<int>> */
    public array $dPiece = [];
    /** @var list<list<int>> */
    public array $dungeon = [];
    /** @var list<int> SOL flags per mega tile id */
    public array $sol = [];
    /** @var list<array{micro1:int,micro2:int,micro3:int,micro4:int}> */
    public array $megaTiles = [];
    /** @var list<list<array{data:int,type:int,frame:int}>> DPieceMicros[tile].mt[block] */
    public array $dPieceMicros = [];
    /** @var list<list<int>> transparency ids [y][x] */
    public array $dTransVal = [];
 /** TransList[256] — rooms currently see-through */
    /** @var list<bool> */
    public array $transList = [];
    /** @var list<list<int>> special micros [y][x] */
    public array $dSpecial = [];
    /** @var list<list<int>> light radius index [y][x] (0=bright … 15=dark) */
    public array $dLight = [];

    public string $dungeonCel = '';
    /** @var list<int> */
    public array $celOffsets = [];
    public int $celFrames = 0;

    public Palette $palette;
    public int $viewX = 75;
    public int $viewY = 68;
    /** World dPiece tiles from PlaceCathedralStairs (megaToWorld + entry offsets). */
    public int $stairUpX = 0;
    public int $stairUpY = 0;
    public int $stairDownX = 0;
    public int $stairDownY = 0;
    /** Town warp stairs (L5 / L13) world tile, 0 if absent. */
    public int $stairWarpX = 0;
    public int $stairWarpY = 0;
    public int $microTileLen = 16;
    public bool $allowMazeFallback = false;

    public function __construct()
    {
        $this->palette = new Palette();
        $this->palette->colors = array_fill(0, 256, [0, 0, 0]);
        $this->clear();
    }

    public function clear(): void
    {
        $this->dPiece = [];
        $this->dTransVal = [];
        $this->dSpecial = [];
        $this->dLight = [];
        for ($y = 0; $y < self::MAXDUNY; $y++) {
            $this->dPiece[$y] = array_fill(0, self::MAXDUNX, 0);
            $this->dTransVal[$y] = array_fill(0, self::MAXDUNX, 0);
            $this->dSpecial[$y] = array_fill(0, self::MAXDUNX, 0);
            $this->dLight[$y] = array_fill(0, self::MAXDUNX, 15);
        }
        $this->dungeon = [];
        for ($y = 0; $y < self::DMAXY; $y++) {
            $this->dungeon[$y] = array_fill(0, self::DMAXX, 0);
        }
        $this->transList = array_fill(0, 256, false);
        // Town / default: TransList[1]=true (non-cathedral PlrDoTrans)
        $this->transList[1] = true;
    }

    public function transListActive(int $transVal): bool
    {
        if ($transVal <= 0 || $transVal >= 256) {
            return false;
        }

        return !empty($this->transList[$transVal]);
    }

    /** Enable transparency for the room containing (x,y) — PlrDoTrans analogue */
    public function plrDoTrans(int $x, int $y): void
    {
        if ($this->levelType === self::DTYPE_TOWN) {
            $this->transList = array_fill(0, 256, false);
            $this->transList[1] = true;

            return;
        }
        // Reset then enable rooms in 3×3 (: InitLighting zeroes; PlrDoTrans sets)
        $this->transList = array_fill(0, 256, false);
        for ($iy = $y - 1; $iy <= $y + 1; $iy++) {
            for ($ix = $x - 1; $ix <= $x + 1; $ix++) {
                if (!$this->inBounds($ix, $iy) || $this->isSolid($ix, $iy)) {
                    continue;
                }
                $tv = (int) ($this->dTransVal[$iy][$ix] ?? 0);
                if ($tv > 0 && $tv < 256) {
                    $this->transList[$tv] = true;
                }
            }
        }
    }

    public const SOL_SOLID = 0x01;
    public const SOL_BLOCK_LIGHT = 0x02;
    public const SOL_BLOCK_MISSILE = 0x04;
    public const SOL_TRANSPARENT = 0x08;
    public const SOL_TRANSPARENT_LEFT = 0x10;
    public const SOL_TRANSPARENT_RIGHT = 0x20;

    public function pieceSol(int $piece): int
    {
        // DevilutionX zero-indexes dPiece: TIL micro 0 → piece 0 (valid MIN/SOL entry), not "empty".
        return $piece >= 0 ? (int) ($this->sol[$piece] ?? 0) : 0;
    }

    public function tileHas(int $x, int $y, int $props): bool
    {
        if (!$this->inBounds($x, $y)) {
            return false;
        }
        $piece = (int) ($this->dPiece[$y][$x] ?? 0);

        return ($this->pieceSol($piece) & $props) !== 0;
    }

    public function inBounds(int $x, int $y): bool
    {
        return $x >= 0 && $y >= 0 && $x < self::MAXDUNX && $y < self::MAXDUNY;
    }

    public function isSolid(int $x, int $y): bool
    {
        if (!$this->inBounds($x, $y)) {
            return true;
        }
        $piece = (int) ($this->dPiece[$y][$x] ?? 0);
        // TileHasAny: HasAnyOf(SOLData[dPiece[x][y]], Solid) — piece 0 is a real tile (DX zero-index).
        $sol = (int) ($this->sol[$piece] ?? 0);

        return ($sol & 0x01) !== 0; // TileProperties::Solid
    }

    /** TileHasAny(BlockMissile) */
    public function blocksMissile(int $x, int $y): bool
    {
        if (!$this->inBounds($x, $y)) {
            return true;
        }
        $piece = (int) ($this->dPiece[$y][$x] ?? 0);
        $sol = (int) ($this->sol[$piece] ?? 0);

        return ($sol & self::SOL_BLOCK_MISSILE) !== 0;
    }

    /** IsFloor: !TileHasAny(Solid|BlockMissile) */
    public function isFloorTile(int $x, int $y): bool
    {
        if (!$this->inBounds($x, $y)) {
            return false;
        }
        $piece = (int) ($this->dPiece[$y][$x] ?? 0);
        $sol = (int) ($this->sol[$piece] ?? 0);

        return ($sol & (0x01 | 0x04)) === 0; // Solid | BlockMissile
    }

    public function isWalkable(int $x, int $y): bool
    {
        return $this->inBounds($x, $y) && !$this->isSolid($x, $y);
    }

    public function loadTown(AssetStore $assets): void
    {
        $this->levelType = self::DTYPE_TOWN;
        $this->currLevel = 0;
        $this->microTileLen = 16;
        $this->clear();
        $this->palette = $assets->loadPalette('levels\\towndata\\town.pal');
        $this->loadTilMinSolCel(
            $assets,
            'levels\\towndata\\town.til',
            'levels\\towndata\\town.min',
            'levels\\towndata\\town.sol',
            'levels\\towndata\\town.cel',
            16
        );
        // DrlgTPass3: prefill every mega with piece 426 before sectors
        $this->prefillTownPieces(426);
        $this->fillSector($assets, 'levels\\towndata\\sector1s.dun', 46, 46);
        $this->fillSector($assets, 'levels\\towndata\\sector2s.dun', 46, 0);
        $this->fillSector($assets, 'levels\\towndata\\sector3s.dun', 0, 46);
        $this->fillSector($assets, 'levels\\towndata\\sector4s.dun', 0, 0);
        if ($assets->has('levels\\towndata\\automap.dun')) {
            $this->placeDunTiles($assets, 'levels\\towndata\\automap.dun', 0, 0);
        }
        // closed-warp / quest tile fills (vanilla: warps closed)
        $this->fillTile(48, 20, 320);
        $this->fillTile(16, 68, 332);
        $this->fillTile(16, 70, 331);
        // Hell warp closed strip (vanilla PickRandomlyAmong({1,2,3,4}) — deterministic stand-in)
        for ($x = 36; $x < 46; $x++) {
            $this->fillTile($x, 78, 1 + ($x % 4));
        }
        $this->fillTile(60, 70, 71); // polluted water quest inactive default
        $this->initTownPieces();
        $this->viewX = 75;
        $this->viewY = 68;
        $this->rebuildDungeonFromPieces();
        \Diablo\World\WorldCellAudit::stageDrlg($this, 'DungeonMap::loadTown');
        \Diablo\World\WorldCellAudit::stageMicros($this);
    }

    /** TIL/MIN/SOL/CEL + SetDungeonMicros only (no DrlgTPass3). */
    public function loadTownGraphics(AssetStore $assets): void
    {
        $this->loadTilMinSolCel(
            $assets,
            'levels\\towndata\\town.til',
            'levels\\towndata\\town.min',
            'levels\\towndata\\town.sol',
            'levels\\towndata\\town.cel',
            16
        );
    }

 /** FillTile — mega id t is 1-based into pMegaTiles. */
    private function fillTile(int $xx, int $yy, int $t): void
    {
        $tid = $t - 1;
        if ($tid < 0 || !isset($this->megaTiles[$tid])) {
            return;
        }
        $m = $this->megaTiles[$tid];
        $this->setMega($xx, $yy, $t, $m['micro1'], $m['micro2'], $m['micro3'], $m['micro4']);
    }

 /** DrlgTPass3 initial fill — all megas get the same piece id. */
    private function prefillTownPieces(int $piece): void
    {
        for ($yy = 0; $yy < self::MAXDUNY; $yy += 2) {
            for ($xx = 0; $xx < self::MAXDUNX; $xx += 2) {
                if ($this->inBounds($xx, $yy)) {
                    $this->dPiece[$yy][$xx] = $piece;
                }
                if ($this->inBounds($xx + 1, $yy)) {
                    $this->dPiece[$yy][$xx + 1] = $piece;
                }
                if ($this->inBounds($xx, $yy + 1)) {
                    $this->dPiece[$yy + 1][$xx] = $piece;
                }
                if ($this->inBounds($xx + 1, $yy + 1)) {
                    $this->dPiece[$yy + 1][$xx + 1] = $piece;
                }
            }
        }
    }

    /**
     * InitTownPieces () — arch/special CEL ids from dPiece.
     * PHP dPiece is [y][x]; source uses [x][y].
     */
    private function initTownPieces(): void
    {
        $map = [
            359 => 1,
            357 => 2,
            128 => 6,
            129 => 7,
            127 => 8,
            116 => 9,
            156 => 10,
            157 => 11,
            155 => 12,
            161 => 13,
            159 => 14,
            213 => 15,
            211 => 16,
            216 => 17,
            215 => 18,
        ];
        for ($y = 0; $y < self::MAXDUNY; $y++) {
            for ($x = 0; $x < self::MAXDUNX; $x++) {
                $piece = (int) ($this->dPiece[$y][$x] ?? 0);
                if (isset($map[$piece])) {
                    $this->dSpecial[$y][$x] = $map[$piece];
                }
            }
        }
    }

    /**
     * Cathedral: CreateL5Dungeon SOURCE_PORTED GenerateLevel path.
     * Maze .dun only when $allowMazeFallback (--maze-fallback).
     */
    public function loadCathedral(AssetStore $assets, Random $rng, int $seed, int $dlvl = 1, bool $allowMazeFallback = false): void
    {
        $this->levelType = self::DTYPE_CATHEDRAL;
        $this->currLevel = $dlvl;
        $this->microTileLen = 10;
        $rng->setRndSeed($seed);
        $this->clear();
        $this->palette = $assets->loadPalette('levels\\l1data\\l1_' . min(5, max(1, $dlvl)) . '.pal');
        if (!$assets->has('levels\\l1data\\l1_' . min(5, max(1, $dlvl)) . '.pal')) {
            $this->palette = $assets->loadPalette('levels\\l1data\\l1.pal');
        }
        $this->loadTilMinSolCel(
            $assets,
            'levels\\l1data\\l1.til',
            'levels\\l1data\\l1.min',
            'levels\\l1data\\l1.sol',
            'levels\\l1data\\l1.cel',
            10
        );
        $this->lastGenMeta = [];
        try {
            $drlg = new DrlgL1();
            $gen = $drlg->createL5Dungeon($rng, $seed, $dlvl, $this->megaTiles);
            $this->dungeon = $gen['dungeon'];
            $this->dPiece = $gen['dPiece'];
            $this->dTransVal = $gen['dTransVal'] ?? $this->dTransVal;
            $this->dSpecial = $gen['dSpecial'] ?? $this->dSpecial;
            $this->viewX = $gen['viewX'];
            $this->viewY = $gen['viewY'];
            // PlaceCathedralStairs: megaToWorld + {3,4} up / {3,3} down
            $this->stairUpX = 16 + 2 * (int) $gen['stairUp'][0] + 3;
            $this->stairUpY = 16 + 2 * (int) $gen['stairUp'][1] + 4;
            $this->stairDownX = 16 + 2 * (int) $gen['stairDown'][0] + 3;
            $this->stairDownY = 16 + 2 * (int) $gen['stairDown'][1] + 3;
            $flat = [];
            foreach ($this->dungeon as $row) {
                foreach ($row as $t) {
                    $flat[] = (string) $t;
                }
            }
            $tileDump = implode('.', $flat);
            $this->lastGenMeta = [
                'source' => 'CreateL5Dungeon/SOURCE_PORTED',
                'seed' => $seed,
                'dlvl' => $dlvl,
                'area' => $gen['area'],
                'attempts' => $gen['attempts'],
                'stairUp' => $gen['stairUp'],
                'stairDown' => $gen['stairDown'],
                'view' => [$this->viewX, $this->viewY],
                'dungeonCrc' => sprintf('%u', crc32($tileDump)),
                'dungeonTileCount' => count($flat),
                'dungeonTilesHead' => implode('.', array_slice($flat, 0, 80)),
            ];
        } catch (\Throwable $e) {
            if (!$allowMazeFallback && !$this->allowMazeFallback) {
                throw $e;
            }
            $path = $rng->flipCoin(2) ? 'levels\\l1data\\lv1mazea.dun' : 'levels\\l1data\\lv1mazeb.dun';
            $this->placeDunTiles($assets, $path, 0, 0);
            $this->viewX = 30;
            $this->viewY = 30;
            $this->stairUpX = 0;
            $this->stairUpY = 0;
            $this->stairDownX = 0;
            $this->stairDownY = 0;
            $this->lastGenMeta = ['source' => 'maze.dun_fallback', 'error' => $e->getMessage()];
        }
        // Ensure walkable spawn
        if (!$this->isWalkable($this->viewX, $this->viewY)) {
            $found = false;
            for ($y = 16; $y < self::MAXDUNY - 16; $y++) {
                for ($x = 16; $x < self::MAXDUNX - 16; $x++) {
                    if ($this->isWalkable($x, $y)) {
                        $this->viewX = $x;
                        $this->viewY = $y;
                        $found = true;
                        break 2;
                    }
                }
            }
            if (!$found) {
                throw new \RuntimeException('Cathedral level has no walkable spawn tile');
            }
        }
        $this->rebuildDungeonFromPieces();
    }

    public function loadCatacombs(AssetStore $assets, Random $rng, int $seed, int $dlvl = 5): void
    {
        $this->loadTypedDungeon($assets, $rng, $seed, $dlvl, self::DTYPE_CATACOMBS, 'l2', 10, new DrlgL2());
    }

    public function loadCaves(AssetStore $assets, Random $rng, int $seed, int $dlvl = 9): void
    {
        $this->loadTypedDungeon($assets, $rng, $seed, $dlvl, self::DTYPE_CAVES, 'l3', 10, new DrlgL3());
    }

    public function loadHell(AssetStore $assets, Random $rng, int $seed, int $dlvl = 13): void
    {
        $this->loadTypedDungeon($assets, $rng, $seed, $dlvl, self::DTYPE_HELL, 'l4', 10, new DrlgL4());
    }

    /** @param object{createL2Dungeon?:callable,createL3Dungeon?:callable,createL4Dungeon?:callable}|DrlgL2|DrlgL3|DrlgL4 $drlg */
    private function loadTypedDungeon(
        AssetStore $assets,
        Random $rng,
        int $seed,
        int $dlvl,
        int $dtype,
        string $prefix,
        int $microLen,
        object $drlg,
    ): void {
        $this->levelType = $dtype;
        $this->currLevel = $dlvl;
        $this->microTileLen = $microLen;
        $rng->setRndSeed($seed);
        $this->clear();
        $pal = "levels\\{$prefix}data\\{$prefix}.pal";
        if (!$assets->has($pal) && $prefix === 'l4') {
            $pal = 'levels\\l4data\\l4_1.pal';
        }
        if (!$assets->has($pal)) {
            $pal = 'levels\\l1data\\l1.pal';
        }
        $this->palette = $assets->loadPalette($pal);
        $this->loadTilMinSolCel(
            $assets,
            "levels\\{$prefix}data\\{$prefix}.til",
            "levels\\{$prefix}data\\{$prefix}.min",
            "levels\\{$prefix}data\\{$prefix}.sol",
            "levels\\{$prefix}data\\{$prefix}.cel",
            $microLen
        );
        $method = match ($dtype) {
            self::DTYPE_CATACOMBS => 'createL2Dungeon',
            self::DTYPE_CAVES => 'createL3Dungeon',
            default => 'createL4Dungeon',
        };
        $gen = $drlg->$method($rng, $seed, $dlvl, $this->megaTiles);
        $this->dungeon = $gen['dungeon'];
        $this->dPiece = $gen['dPiece'];
        $this->viewX = $gen['viewX'];
        $this->viewY = $gen['viewY'];
        // L2–L4 PlaceStairs: mega coords → world trigger tiles (same offsets as L1)
        $this->stairUpX = 16 + 2 * (int) $gen['stairUp'][0] + 3;
        $this->stairUpY = 16 + 2 * (int) $gen['stairUp'][1] + 4;
        $this->stairDownX = 16 + 2 * (int) $gen['stairDown'][0] + 3;
        $this->stairDownY = 16 + 2 * (int) $gen['stairDown'][1] + 3;
        $this->stairWarpX = 0;
        $this->stairWarpY = 0;
        if (!empty($gen['stairWarp']) && is_array($gen['stairWarp'])) {
            $this->stairWarpX = 16 + 2 * (int) $gen['stairWarp'][0] + 3;
            $this->stairWarpY = 16 + 2 * (int) $gen['stairWarp'][1] + 4;
        }
        $this->lastGenMeta = [
            'source' => $gen['source'] ?? $method,
            'seed' => $seed,
            'dlvl' => $dlvl,
            'stairUp' => $gen['stairUp'],
            'stairDown' => $gen['stairDown'],
            'stairWarp' => $gen['stairWarp'] ?? null,
            'view' => [$this->viewX, $this->viewY],
        ];
        if (!$this->isWalkable($this->viewX, $this->viewY)) {
            for ($y = 16; $y < self::MAXDUNY - 16; $y++) {
                for ($x = 16; $x < self::MAXDUNX - 16; $x++) {
                    if ($this->isWalkable($x, $y)) {
                        $this->viewX = $x;
                        $this->viewY = $y;
                        break 2;
                    }
                }
            }
        }
        $this->rebuildDungeonFromPieces();
    }

    /** @var array<string,mixed> */
    public array $lastGenMeta = [];

    /** @return array<string,mixed> */
    public function debugGenerationDump(): array
    {
        $walk = 0;
        for ($y = 0; $y < self::MAXDUNY; $y++) {
            for ($x = 0; $x < self::MAXDUNX; $x++) {
                if ($this->isWalkable($x, $y)) {
                    $walk++;
                }
            }
        }

        return $this->lastGenMeta + [
            'walkable' => $walk,
            'spawn' => [$this->viewX, $this->viewY],
            'levelType' => $this->levelType,
            'currLevel' => $this->currLevel,
        ];
    }

    private function loadTilMinSolCel(
        AssetStore $assets,
        string $til,
        string $min,
        string $sol,
        string $cel,
        int $blocks,
    ): void {
        $tilData = $assets->read($til);
        $n = intdiv(strlen($tilData), 8);
        $this->megaTiles = [];
        for ($i = 0; $i < $n; $i++) {
            $e = unpack('vmicro1/vmicro2/vmicro3/vmicro4', substr($tilData, $i * 8, 8));
            $this->megaTiles[$i] = $e;
        }

        $solData = $assets->read($sol);
        $this->sol = [];
        for ($i = 0; $i < strlen($solData); $i++) {
            $this->sol[$i] = ord($solData[$i]);
        }

        $minData = $assets->read($min);
        $wordCount = intdiv(strlen($minData), 2);
        $tileCount = intdiv($wordCount, $blocks);
        $this->dPieceMicros = [];
        for ($t = 0; $t < $tileCount; $t++) {
            $mt = [];
            for ($b = 0; $b < $blocks; $b++) {
                // Match indexing into MIN words
                $idx = $blocks * $t + ($blocks - 2 + ($b & 1) - ($b & 0xE));
                $data = unpack('v', substr($minData, $idx * 2, 2))[1];
                $mt[$b] = [
                    'data' => $data,
                    'type' => ($data & 0x7000) >> 12,
                    'frame' => $data & 0xFFF,
                ];
            }
            $this->dPieceMicros[$t] = $mt;
        }

        $this->dungeonCel = $assets->read($cel);
        $this->celFrames = unpack('V', substr($this->dungeonCel, 0, 4))[1];
        $this->celOffsets = [];
        for ($i = 0; $i <= $this->celFrames; $i++) {
            $this->celOffsets[] = unpack('V', substr($this->dungeonCel, 4 + $i * 4, 4))[1];
        }
        $this->setDungeonMicros();
    }

    /**
     * SetDungeonMicros — fill DPieceMicros, ReencodeDungeonCels, adjust frames.
     */
    public function setDungeonMicros(): void
    {
        $blocks = $this->microTileLen;
        $frameToType = [];
        foreach ($this->dPieceMicros as $levelPieceId => $mt) {
            foreach ($mt as $block => $celBlock) {
                $frame = (int) ($celBlock['frame'] ?? 0);
                if ($frame === 0) {
                    continue;
                }
                if (!isset($frameToType[$frame])) {
                    $frameToType[$frame] = [
                        'microTileIndex' => (int) $block,
                        'type' => (int) ($celBlock['type'] ?? 0),
                        'properties' => (int) ($this->sol[$levelPieceId] ?? 0),
                    ];
                }
            }
        }
        ksort($frameToType, SORT_NUMERIC);
        $frames = [];
        foreach ($frameToType as $frame => $info) {
            $frames[] = [$frame, $info];
        }
        if ($frames === []) {
            return;
        }
        $result = \Diablo\Engine\Render\ReencodeDungeonCels::reencode($this->dungeonCel, $frames);
        $this->dungeonCel = $result['cel'];
        $this->celFrames = unpack('V', substr($this->dungeonCel, 0, 4))[1];
        $this->celOffsets = [];
        for ($i = 0; $i <= $this->celFrames; $i++) {
            $this->celOffsets[] = unpack('V', substr($this->dungeonCel, 4 + $i * 4, 4))[1];
        }
        $adjustments = $result['adjustments'];
        if ($adjustments === []) {
            return;
        }
        foreach ($this->dPieceMicros as &$mt) {
            foreach ($mt as &$celBlock) {
                $frame = (int) ($celBlock['frame'] ?? 0);
                if ($frame === 0) {
                    continue;
                }
                // std::upper_bound on adjustments by threshold > frame
                $chosen = null;
                foreach ($adjustments as [$threshold, $adjustment]) {
                    if ($threshold > $frame) {
                        $chosen = $adjustment;
                        break;
                    }
                }
                if ($chosen !== null) {
                    $newFrame = $frame - $chosen;
                    $celBlock['frame'] = $newFrame;
                    $celBlock['data'] = (($celBlock['type'] & 7) << 12) | ($newFrame & 0xFFF);
                }
            }
            unset($celBlock);
        }
        unset($mt);
    }

    /**
     * Re-parse MIN words for one piece (pre-reencode) using block shuffle.
     * @return list<array{data:int,type:int,frame:int}>
     */
    public function rawMinMicros(AssetStore $assets, string $minPath, int $piece): array
    {
        if ($piece < 0 || !$assets->has($minPath)) {
            return [];
        }
        $minData = $assets->read($minPath);
        $blocks = $this->microTileLen;
        $wordCount = intdiv(strlen($minData), 2);
        $tileCount = intdiv($wordCount, $blocks);
        if ($piece >= $tileCount) {
            return [];
        }
        $mt = [];
        for ($b = 0; $b < $blocks; $b++) {
            $idx = $blocks * $piece + ($blocks - 2 + ($b & 1) - ($b & 0xE));
            $data = unpack('v', substr($minData, $idx * 2, 2))[1];
            $mt[$b] = [
                'data' => $data,
                'type' => ($data & 0x7000) >> 12,
                'frame' => $data & 0xFFF,
            ];
        }

        return $mt;
    }

    /**
     * First mismatch live (post-reencode) micros vs raw MIN type/frame (pre-reencode).
     * Frame may differ after reencode adjustments — report type mismatch first, then note frame delta.
     * @return array{ok:bool,diff:?string,raw:list<array{data:int,type:int,frame:int}>}
     */
    public function diffPieceVsRawMin(AssetStore $assets, string $minPath, int $piece): array
    {
        $raw = $this->rawMinMicros($assets, $minPath, $piece);
        $live = $this->dPieceMicros[$piece] ?? [];
        if ($raw === []) {
            return ['ok' => false, 'diff' => 'raw MIN missing/empty', 'raw' => []];
        }
        $nmax = max(count($raw), count($live));
        for ($i = 0; $i < $nmax; $i++) {
            $a = $live[$i] ?? null;
            $b = $raw[$i] ?? null;
            if ($a === null || $b === null) {
                return [
                    'ok' => false,
                    'diff' => sprintf('micro[%d] count live=%d raw=%d', $i, count($live), count($raw)),
                    'raw' => $raw,
                ];
            }
            if ((int) ($a['type'] ?? -1) !== (int) ($b['type'] ?? -2)) {
                return [
                    'ok' => false,
                    'diff' => sprintf(
                        'micro[%d].type mismatch live=%d raw=%d',
                        $i,
                        (int) $a['type'],
                        (int) $b['type']
                    ),
                    'raw' => $raw,
                ];
            }
            // Frame: live may be adjusted by reencode; raw is 1-based MIN. Type match is hard requirement.
            // If both zero or both non-zero with same type, accept; flag only if live zero vs raw nonzero (or vice versa wrongly).
            $lf = (int) ($a['frame'] ?? 0);
            $rf = (int) ($b['frame'] ?? 0);
            if (($lf === 0) !== ($rf === 0)) {
                return [
                    'ok' => false,
                    'diff' => sprintf('micro[%d].frame zero-ness live=%d raw=%d', $i, $lf, $rf),
                    'raw' => $raw,
                ];
            }
        }

        return ['ok' => true, 'diff' => null, 'raw' => $raw];
    }

    private function fillSector(AssetStore $assets, string $path, int $xi, int $yy): void
    {
        $raw = $assets->read($path);
        $width = unpack('v', substr($raw, 0, 2))[1];
        $height = unpack('v', substr($raw, 2, 2))[1];
        $tiles = substr($raw, 4);
        for ($j = 0; $j < $height; $j++) {
            $xx = $xi;
            for ($i = 0; $i < $width; $i++) {
                $tileId = unpack('v', substr($tiles, (($j * $width) + $i) * 2, 2))[1] - 1;
                $v1 = $v2 = $v3 = $v4 = 218;
                if ($tileId >= 0 && isset($this->megaTiles[$tileId])) {
                    $m = $this->megaTiles[$tileId];
                    $v1 = $m['micro1'];
                    $v2 = $m['micro2'];
                    $v3 = $m['micro3'];
                    $v4 = $m['micro4'];
                    $dx = intdiv($xx, 2);
                    $dy = intdiv($yy + $j * 2, 2);
                    // store mega id in dungeon grid when in range
                    $dungeonX = intdiv($xx - 0, 2);
                    // Map sector coords into dungeon[] roughly
                    $this->setMega($xx, $yy + $j * 2, $tileId + 1, $v1, $v2, $v3, $v4);
                } else {
                    $this->setMega($xx, $yy + $j * 2, 0, $v1, $v2, $v3, $v4);
                }
                $xx += 2;
            }
        }
    }

    private function setMega(int $xx, int $yy, int $megaId, int $v1, int $v2, int $v3, int $v4): void
    {
        if ($this->inBounds($xx, $yy)) {
            $this->dPiece[$yy][$xx] = $v1;
        }
        if ($this->inBounds($xx + 1, $yy)) {
            $this->dPiece[$yy][$xx + 1] = $v2;
        }
        if ($this->inBounds($xx, $yy + 1)) {
            $this->dPiece[$yy + 1][$xx] = $v3;
        }
        if ($this->inBounds($xx + 1, $yy + 1)) {
            $this->dPiece[$yy + 1][$xx + 1] = $v4;
        }
        $dx = intdiv($xx, 2);
        $dy = intdiv($yy, 2);
        if ($dx >= 0 && $dy >= 0 && $dx < self::DMAXX && $dy < self::DMAXY) {
            $this->dungeon[$dy][$dx] = $megaId;
        }
    }

    private function placeDunTiles(AssetStore $assets, string $path, int $ox, int $oy): void
    {
        $raw = $assets->read($path);
        $width = unpack('v', substr($raw, 0, 2))[1];
        $height = unpack('v', substr($raw, 2, 2))[1];
        $tiles = substr($raw, 4);
        for ($j = 0; $j < $height; $j++) {
            for ($i = 0; $i < $width; $i++) {
                $tileId = unpack('v', substr($tiles, (($j * $width) + $i) * 2, 2))[1];
                if ($tileId === 0) {
                    continue;
                }
                $tid = $tileId - 1;
                if (!isset($this->megaTiles[$tid])) {
                    continue;
                }
                $m = $this->megaTiles[$tid];
                $xx = 16 + ($ox + $i) * 2;
                $yy = 16 + ($oy + $j) * 2;
                $this->setMega($xx, $yy, $tileId, $m['micro1'], $m['micro2'], $m['micro3'], $m['micro4']);
            }
        }
    }

    private function rebuildDungeonFromPieces(): void
    {
        // dungeon[] already filled in setMega
    }

    public function celFrameBytes(int $frame1Based): string
    {
        if ($frame1Based <= 0 || $frame1Based > $this->celFrames) {
            return '';
        }
        $i = $frame1Based - 1;
        $start = $this->celOffsets[$i];
        $end = $this->celOffsets[$i + 1];

        return substr($this->dungeonCel, $start, $end - $start);
    }

    public function decodeMicro(int $piece): ?array
    {
        if ($piece < 0) {
            return null;
        }
        // dPiece indexes DPieceMicros (DrawCell); piece 0 is valid (DX zero-index).
        if (!isset($this->dPieceMicros[$piece])) {
            // Some TIL values are LevelCelBlock-packed; try low 12 bits as piece id
            $alt = $piece & 0xFFF;
            if ($alt === $piece || !isset($this->dPieceMicros[$alt])) {
                return null;
            }
            $piece = $alt;
        }
        $mt = $this->dPieceMicros[$piece];
        // Compose floor micro mt[0] as representative tile preview (full stack in renderer)
        $block = $mt[0] ?? null;
        if ($block === null || ($block['frame'] ?? 0) === 0) {
            $block = $mt[1] ?? null;
        }
        if ($block === null || ($block['frame'] ?? 0) === 0) {
            return null;
        }
        $type = (int) $block['type'];
        $frame = (int) $block['frame'];
        $bytes = $this->celFrameBytes($frame);
        if ($bytes === '' || strlen($bytes) < 16) {
            return null;
        }
        try {
            return DunMicro::decode($bytes, $type, $this->palette, true);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * All micro blocks for a dPiece id (for layered draw).
     *
     * @return list<array{type:int,frame:int,rgba:?string,w:int,h:int}>
     */
    public function decodePieceLayers(int $piece): array
    {
        if ($piece < 0 || !isset($this->dPieceMicros[$piece])) {
            return [];
        }
        $out = [];
        foreach ($this->dPieceMicros[$piece] as $block) {
            $frame = (int) ($block['frame'] ?? 0);
            if ($frame === 0) {
                $out[] = ['type' => 0, 'frame' => 0, 'rgba' => null, 'w' => 32, 'h' => 32];
                continue;
            }
            $type = (int) $block['type'];
            $bytes = $this->celFrameBytes($frame);
            if ($bytes === '') {
                $out[] = ['type' => $type, 'frame' => $frame, 'rgba' => null, 'w' => 32, 'h' => 32];
                continue;
            }
            try {
                $decoded = DunMicro::decode($bytes, $type, $this->palette, true);
                $out[] = ['type' => $type, 'frame' => $frame, 'rgba' => $decoded['rgba'], 'w' => $decoded['w'], 'h' => $decoded['h']];
            } catch (\Throwable) {
                $out[] = ['type' => $type, 'frame' => $frame, 'rgba' => null, 'w' => 32, 'h' => 32];
            }
        }

        return $out;
    }
}
