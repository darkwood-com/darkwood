<?php

declare(strict_types=1);

namespace Diablo\World;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Assets\MpqArchive;
use Diablo\Engine\Random;
use Diablo\Engine\Render\DunMicro;
use Diablo\Levels\DungeonMap;

/**
 * CLI handlers: world:cell-diff / world:validate-visible / world:dump-grid
 */
final class WorldCommands
{
    /**
     * @param list<string> $argv
     */
    public static function tryDispatch(array $argv): int
    {
        $cmd = $argv[1] ?? '';
        $supported = [
            'world:cell-diff',
            'world:validate-visible',
            'world:dump-grid',
            'world:pixel-diff',
            'world:min-fixtures',
            'world:parity-oracle-dump',
            'world:microtile-golden',
        ];
        if (!in_array($cmd, $supported, true)) {
            return -1;
        }
        $opts = self::parseOpts($argv);

        if ($cmd === 'world:pixel-diff') {
            return self::cmdPixelDiff($opts);
        }

        $data = $opts['data'] ?? (getenv('DIABLO_DATA') ?: '');
        if ($data === '' || !is_file($data)) {
            fwrite(STDERR, "world:* requires --data=/path/to/DIABDAT.MPQ\n");

            return 1;
        }
        $mpq = new MpqArchive($data);
        $assets = new AssetStore($mpq);
        $outDir = dirname(__DIR__, 2) . '/tmp/world-cells';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0777, true);
        }

        return match ($cmd) {
            'world:dump-grid' => self::cmdDumpGrid($assets, $opts, $outDir),
            'world:cell-diff' => self::cmdCellDiff($assets, $opts, $outDir),
            'world:validate-visible' => self::cmdValidateVisible($assets, $opts),
            'world:min-fixtures' => self::cmdMinFixtures($assets, $opts),
            'world:parity-oracle-dump' => self::cmdParityOracleDump($assets, $opts),
            'world:microtile-golden' => self::cmdMicrotileGolden($assets, $opts),
            default => 1,
        };
    }

    /**
     * @param list<string> $argv
     * @return array<string,string|bool>
     */
    private static function parseOpts(array $argv): array
    {
        $opts = [];
        foreach ($argv as $i => $arg) {
            if ($i < 2) {
                continue;
            }
            if (str_starts_with($arg, '--data=')) {
                $opts['data'] = substr($arg, 7);
            } elseif (str_starts_with($arg, '--x=')) {
                $opts['x'] = substr($arg, 4);
            } elseif (str_starts_with($arg, '--y=')) {
                $opts['y'] = substr($arg, 4);
            } elseif (str_starts_with($arg, '--ref-dump=') || str_starts_with($arg, '--dx-dump=')) {
                $opts['ref-dump'] = substr($arg, strpos($arg, '=') + 1);
            } elseif (str_starts_with($arg, '--out=')) {
                $opts['out'] = substr($arg, 6);
            } elseif (str_starts_with($arg, '--level=')) {
                $opts['level'] = substr($arg, 8);
            } elseif (str_starts_with($arg, '--seed=')) {
                $opts['seed'] = substr($arg, 7);
            } elseif ($arg === '--town') {
                $opts['town'] = true;
            } elseif ($arg === '--viewport') {
                $opts['viewport'] = true;
            } elseif ($arg === '--oracle') {
                $opts['oracle'] = true;
            } elseif ($arg === '--fullbright') {
                $opts['fullbright'] = true;
            } elseif ($arg === '--world-data-audit') {
                $opts['world-data-audit'] = true;
            } elseif (str_starts_with($arg, '--a=')) {
                $opts['a'] = substr($arg, 4);
            } elseif (str_starts_with($arg, '--b=')) {
                $opts['b'] = substr($arg, 4);
            } elseif (str_starts_with($arg, '--mask-ui')) {
                $opts['mask-ui'] = true;
            } elseif (str_starts_with($arg, '--count=')) {
                $opts['count'] = substr($arg, 8);
            }
        }

        return $opts;
    }

    /** @param array<string,string|bool> $opts */
    private static function cmdDumpGrid(AssetStore $assets, array $opts, string $outDir): int
    {
        $level = (int) ($opts['level'] ?? 0);
        if ($level >= 1) {
            fwrite(STDERR, "world:dump-grid L1 not yet; use --town\n");

            return 1;
        }
        $who = !empty($opts['oracle']) ? 'oracle' : 'php';
        if ($who === 'oracle') {
            $map = TownOracle::build($assets);
            $path = (string) ($opts['out'] ?? ($outDir . '/dx-town-oracle.jsonl'));
            $n = WorldCellAudit::dumpGrid($map, $path, 'town_oracle');
            fwrite(STDOUT, "wrote {$n} cells → {$path} (TownOracle)\n");

            return 0;
        }
        $map = new DungeonMap();
        $map->loadTown($assets);
        $path = (string) ($opts['out'] ?? ($outDir . '/php-town.jsonl'));
        $n = WorldCellAudit::dumpGrid($map, $path, 'php_loadTown');
        fwrite(STDOUT, "wrote {$n} cells → {$path} (DungeonMap::loadTown)\n");

        return 0;
    }

    /** @param array<string,string|bool> $opts */
    private static function cmdCellDiff(AssetStore $assets, array $opts, string $outDir): int
    {
        WorldCellAudit::$enabled = !empty($opts['world-data-audit']);
        $phpMap = new DungeonMap();
        $phpMap->loadTown($assets);

        $dxPath = (string) ($opts['ref-dump'] ?? ($opts['dx-dump'] ?? ''));
        if ($dxPath === '') {
            // Prefer prebuilt dump; else generate TownOracle twin
            $candidate = $outDir . '/dx-town.jsonl';
            if (is_file($candidate)) {
                $dxPath = $candidate;
            } else {
                $oracle = TownOracle::build($assets);
                $dxPath = $outDir . '/dx-town-oracle.jsonl';
                WorldCellAudit::dumpGrid($oracle, $dxPath, 'town_oracle');
                fwrite(STDOUT, "DX dump missing — generated oracle twin → {$dxPath}\n");
                fwrite(STDOUT, "(TownOracle builder — not DungeonMap::loadTown)\n");
            }
        }
        $dxIndex = WorldCellAudit::loadDumpIndex($dxPath);

        if (!empty($opts['viewport'])) {
            $viewX = $phpMap->viewX;
            $viewY = $phpMap->viewY;
            \Diablo\Engine\Render\Scrollrt::prepareViewport(
                \Diablo\Engine\Render\IsoCoords::SCREEN_W,
                \Diablo\Engine\Render\IsoCoords::viewportHeight()
            );
            [$ftx, $fty] = \Diablo\Engine\Render\Scrollrt::firstTile($viewX, $viewY, 0, 0);
            $vp = \Diablo\Engine\Render\Scrollrt::viewportStats();
            $rows = (int) ($vp['tileRows'] ?? 18);
            $cols = (int) ($vp['tileColumns'] ?? 14);
            $seen = [];
            $tileX = $ftx;
            $tileY = $fty;
            $columns = $cols;
            for ($i = 0; $i < $rows; $i++) {
                for ($j = 0; $j < $columns; $j++) {
                    if ($phpMap->inBounds($tileX, $tileY)) {
                        $seen[$tileX . ',' . $tileY] = true;
                    }
                    $tileX++;
                    $tileY--;
                }
                $tileX -= $columns;
                $tileY += $columns;
                if (($i & 1) !== 0) {
                    $tileX++;
                    $columns--;
                } else {
                    $tileY++;
                    $columns++;
                }
            }
            $miss = WorldCellAudit::findFirstMismatch(
                $phpMap,
                $dxIndex,
                static fn (int $x, int $y): bool => isset($seen[$x . ',' . $y])
            );
        } elseif (isset($opts['x'], $opts['y'])) {
            $x = (int) $opts['x'];
            $y = (int) $opts['y'];
            $php = WorldCellAudit::cellRecord($phpMap, $x, $y, 'php_loadTown');
            $dx = $dxIndex[$x . ',' . $y] ?? null;
            $r = WorldCellAudit::diffCell($php, $dx);
            fwrite(STDOUT, WorldCellAudit::formatDiffReport($x, $y, $r));
            $phpPath = $outDir . '/php-cell.jsonl';
            file_put_contents($phpPath, json_encode($php) . "\n");

            return $r['ok'] ? 0 : 2;
        } else {
            $miss = WorldCellAudit::findFirstMismatch($phpMap, $dxIndex);
        }

        if ($miss === null) {
            fwrite(STDOUT, "CELL DIFF: all compared cells MATCH ({$dxPath})\n");

            return 0;
        }
        fwrite(STDOUT, WorldCellAudit::formatDiffReport($miss['x'], $miss['y'], $miss['result']));
        fwrite(STDOUT, sprintf(
            "\nFIRST_MISMATCH x=%d y=%d %s\n",
            $miss['x'],
            $miss['y'],
            $miss['result']['diff'] ?? ''
        ));

        return 2;
    }

    /** @param array<string,string|bool> $opts */
    private static function cmdValidateVisible(AssetStore $assets, array $opts): int
    {
        WorldCellAudit::$enabled = true;
        WorldCellAudit::resetBlitDiag();
        $map = new DungeonMap();
        $level = (int) ($opts['level'] ?? 0);
        if ($level >= 1) {
            $seed = (int) ($opts['seed'] ?? 12345);
            $rng = new Random();
            $rng->setRndSeed($seed);
            $map->loadCathedral($assets, $rng, $seed, $level);
        } else {
            $map->loadTown($assets);
        }
        $sum = WorldCellAudit::validateVisible($map, $map->viewX, $map->viewY);
        fwrite(STDOUT, sprintf(
            "Visible %d | ok %d | missing_dPiece %d | missing_micro %d | missing_CEL %d | missing_frame %d | render_skipped %d | unexpected %d\n",
            $sum['visible'],
            $sum['ok'],
            $sum['missing_dPiece'],
            $sum['missing_micro'],
            $sum['missing_CEL'],
            $sum['missing_frame'],
            $sum['render_skipped'],
            $sum['unexpected']
        ));
        foreach (array_slice($sum['lines'], 0, 40) as $ln) {
            fwrite(STDOUT, $ln . "\n");
        }

        \Diablo\Engine\Render\Scrollrt::prepareViewport(
            \Diablo\Engine\Render\IsoCoords::SCREEN_W,
            \Diablo\Engine\Render\IsoCoords::viewportHeight()
        );
        [$ptx, $pty] = \Diablo\Engine\Render\Scrollrt::firstTile($map->viewX, $map->viewY, 0, 0);
        $phpVp = \Diablo\Engine\Render\Scrollrt::viewportStats();
        $dxVp = ViewportGeometry::compute($map->viewX, $map->viewY);
        fwrite(STDOUT, sprintf(
            "VIEWPORT_OWNERSHIP PHP first=%d,%d tilesToTop=%d tilesToLeft=%d | DX first=%d,%d tilesToTop=%d tilesToLeft=%d\n",
            $ptx,
            $pty,
            $phpVp['tilesToTop'],
            $phpVp['tilesToLeft'],
            $dxVp['firstX'],
            $dxVp['firstY'],
            $dxVp['tilesToTop'],
            $dxVp['tilesToLeft']
        ));
        if ($ptx !== $dxVp['firstX'] || $pty !== $dxVp['firstY']) {
            fwrite(STDOUT, "VIEWPORT_MISMATCH first tile PHP≠DX\n");

            return 3;
        }

        return $sum['unexpected'] > 0 ? 2 : 0;
    }

    /**
     * Compare two PNGs: total different pixels + large bounding boxes + optional diff image.
     * @param array<string,string|bool> $opts
     */
    private static function cmdPixelDiff(array $opts): int
    {
        $aPath = (string) ($opts['a'] ?? '');
        $bPath = (string) ($opts['b'] ?? '');
        $outPath = (string) ($opts['out'] ?? '');
        if ($aPath === '' || $bPath === '') {
            fwrite(STDERR, "world:pixel-diff requires --a=PATH --b=PATH [--out=diff.png] [--mask-ui]\n");

            return 1;
        }
        if (!str_starts_with($aPath, '/')) {
            $aPath = dirname(__DIR__, 2) . '/' . $aPath;
        }
        if (!str_starts_with($bPath, '/')) {
            $bPath = dirname(__DIR__, 2) . '/' . $bPath;
        }
        if ($outPath !== '' && !str_starts_with($outPath, '/')) {
            $outPath = dirname(__DIR__, 2) . '/' . $outPath;
        }
        if (!function_exists('imagecreatefrompng')) {
            fwrite(STDERR, "world:pixel-diff requires PHP GD with PNG\n");

            return 1;
        }
        $a = @imagecreatefrompng($aPath);
        $b = @imagecreatefrompng($bPath);
        if ($a === false || $b === false) {
            fwrite(STDERR, "world:pixel-diff could not load PNGs\n");

            return 1;
        }
        $w = imagesx($a);
        $h = imagesy($a);
        if ($w !== imagesx($b) || $h !== imagesy($b)) {
            fwrite(STDERR, sprintf("size mismatch %dx%d vs %dx%d\n", $w, $h, imagesx($b), imagesy($b)));

            return 2;
        }
        $maskUi = !empty($opts['mask-ui']);
        $uiTop = \Diablo\Engine\Render\IsoCoords::viewportHeight();
        $diff = 0;
        $minX = $w;
        $minY = $h;
        $maxX = -1;
        $maxY = -1;
        // coarse grid for large bboxes (32x32)
        $cell = 32;
        $hot = [];
        $diffImg = $outPath !== '' ? imagecreatetruecolor($w, $h) : null;
        if ($diffImg !== null) {
            $black = imagecolorallocate($diffImg, 0, 0, 0);
            imagefill($diffImg, 0, 0, $black);
        }
        $red = $diffImg !== null ? imagecolorallocate($diffImg, 255, 40, 40) : 0;
        for ($y = 0; $y < $h; $y++) {
            if ($maskUi && $y >= $uiTop) {
                continue;
            }
            for ($x = 0; $x < $w; $x++) {
                if (imagecolorat($a, $x, $y) === imagecolorat($b, $x, $y)) {
                    continue;
                }
                $diff++;
                if ($x < $minX) {
                    $minX = $x;
                }
                if ($y < $minY) {
                    $minY = $y;
                }
                if ($x > $maxX) {
                    $maxX = $x;
                }
                if ($y > $maxY) {
                    $maxY = $y;
                }
                $ck = intdiv($x, $cell) . ',' . intdiv($y, $cell);
                $hot[$ck] = ($hot[$ck] ?? 0) + 1;
                if ($diffImg !== null) {
                    imagesetpixel($diffImg, $x, $y, $red);
                }
            }
        }
        arsort($hot);
        $bboxes = [];
        foreach (array_slice($hot, 0, 12, true) as $ck => $n) {
            [$cx, $cy] = array_map('intval', explode(',', $ck));
            $bboxes[] = [
                'x' => $cx * $cell,
                'y' => $cy * $cell,
                'w' => $cell,
                'h' => $cell,
                'diffPixels' => $n,
            ];
        }
        fwrite(STDOUT, sprintf(
            "PIXEL_DIFF a=%s b=%s diffPixels=%d maskUi=%d overallBBox=%s\n",
            $aPath,
            $bPath,
            $diff,
            $maskUi ? 1 : 0,
            $maxX >= 0 ? sprintf('%d,%d-%d,%d', $minX, $minY, $maxX, $maxY) : 'none'
        ));
        foreach ($bboxes as $bb) {
            fwrite(STDOUT, sprintf(
                "  hot %d,%d %dx%d diffs=%d\n",
                $bb['x'],
                $bb['y'],
                $bb['w'],
                $bb['h'],
                $bb['diffPixels']
            ));
        }
        if ($diffImg !== null && $outPath !== '') {
            $dir = dirname($outPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            imagepng($diffImg, $outPath);
            fwrite(STDOUT, "diff image → {$outPath}\n");
        }
        $report = [
            'a' => $aPath,
            'b' => $bPath,
            'diffPixels' => $diff,
            'overallBBox' => $maxX >= 0 ? [$minX, $minY, $maxX, $maxY] : null,
            'hotCells' => $bboxes,
        ];
        $jsonOut = dirname(__DIR__, 2) . '/tmp/parity/pixel-diff-last.json';
        if (!is_dir(dirname($jsonOut))) {
            mkdir(dirname($jsonOut), 0775, true);
        }
        file_put_contents($jsonOut, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return $diff === 0 ? 0 : 3;
    }

    /**
     * Generate/verify ≥50 L1 MIN→live micro fixtures (type/zero-frame + post-reencode).
     * @param array<string,string|bool> $opts
     */
    private static function cmdMinFixtures(AssetStore $assets, array $opts): int
    {
        $count = max(50, (int) ($opts['count'] ?? 50));
        $seed = (int) ($opts['seed'] ?? 12345);
        $map = new DungeonMap();
        $rng = new Random();
        $rng->setRndSeed($seed);
        $map->loadCathedral($assets, $rng, $seed, 1);
        $minPath = 'levels\\l1data\\l1.min';
        $fixtures = [];
        $seen = [];
        $fail = 0;
        for ($y = 16; $y < 96 && count($fixtures) < $count; $y++) {
            for ($x = 16; $x < 96 && count($fixtures) < $count; $x++) {
                $piece = (int) ($map->dPiece[$y][$x] ?? 0);
                if ($piece <= 0 || isset($seen[$piece])) {
                    continue;
                }
                $seen[$piece] = true;
                $raw = $map->rawMinMicros($assets, $minPath, $piece);
                $diff = $map->diffPieceVsRawMin($assets, $minPath, $piece);
                $live = [];
                foreach ($map->dPieceMicros[$piece] ?? [] as $bi => $b) {
                    $live[] = [
                        'i' => (int) $bi,
                        'type' => (int) ($b['type'] ?? 0),
                        'frame' => (int) ($b['frame'] ?? 0),
                    ];
                }
                $ok = !empty($diff['ok']);
                if (!$ok) {
                    $fail++;
                }
                $fixtures[] = [
                    'piece' => $piece,
                    'sampleTile' => [$x, $y],
                    'rawMin' => $raw,
                    'live' => $live,
                    'rawMinTypesOk' => $ok ? 1 : 0,
                    'rawMinDiff' => $diff['diff'] ?? null,
                ];
            }
        }
        $out = dirname(__DIR__, 2) . '/tmp/parity/min_fixtures.json';
        if (!is_dir(dirname($out))) {
            mkdir(dirname($out), 0775, true);
        }
        $payload = [
            'seed' => $seed,
            'minPath' => $minPath,
            'count' => count($fixtures),
            'fail' => $fail,
            'note' => 'raw MIN type/zero-frame vs live post-reencode; DX PNG dump compares pixels separately',
            'fixtures' => $fixtures,
        ];
        file_put_contents($out, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        fwrite(STDOUT, sprintf(
            "MIN_FIXTURES count=%d fail=%d → %s\n",
            count($fixtures),
            $fail,
            $out
        ));

        return $fail === 0 && count($fixtures) >= 50 ? 0 : 2;
    }

    /**
     * Dump Town oracle + PHP cell grids into tmp/parity/dx as interim twin until native dump builds.
     * @param array<string,string|bool> $opts
     */
    private static function cmdParityOracleDump(AssetStore $assets, array $opts): int
    {
        $root = dirname(__DIR__, 2) . '/tmp/parity/dx';
        if (!is_dir($root)) {
            mkdir($root, 0775, true);
        }
        $oracle = TownOracle::build($assets);
        $n = WorldCellAudit::dumpGrid($oracle, $root . '/town_cells_oracle.jsonl', 'town_oracle');
        fwrite(STDOUT, "town oracle cells={$n} → {$root}/town_cells_oracle.jsonl\n");

        $php = new DungeonMap();
        $php->loadTown($assets);
        $n2 = WorldCellAudit::dumpGrid($php, $root . '/town_cells_php.jsonl', 'php_loadTown');
        fwrite(STDOUT, "town php cells={$n2} → {$root}/town_cells_php.jsonl\n");

        $seed = (int) ($opts['seed'] ?? 12345);
        $l1 = new DungeonMap();
        $rng = new Random();
        $rng->setRndSeed($seed);
        $l1->loadCathedral($assets, $rng, $seed, 1);
        // Viewport-sized dump around view for L1 (full 112² is huge)
        $path = $root . '/l1_cells_php_viewport.jsonl';
        $fh = fopen($path, 'wb');
        $n3 = 0;
        $vx = $l1->viewX;
        $vy = $l1->viewY;
        for ($dy = -20; $dy <= 20; $dy++) {
            for ($dx = -20; $dx <= 20; $dx++) {
                $x = $vx + $dx;
                $y = $vy + $dy;
                if (!$l1->inBounds($x, $y)) {
                    continue;
                }
                $rec = WorldCellAudit::cellRecord($l1, $x, $y, 'php_l1_seed_' . $seed);
                fwrite($fh, json_encode($rec, JSON_UNESCAPED_SLASHES) . "\n");
                $n3++;
            }
        }
        fclose($fh);
        fwrite(STDOUT, "l1 php viewport cells={$n3} view={$vx},{$vy} → {$path}\n");
        file_put_contents($root . '/README.md', <<<MD
# DX parity dumps

- `town_cells_oracle.jsonl` — town oracle dump
- `town_cells_php.jsonl` — DungeonMap::loadTown
- `l1_cells_php_viewport.jsonl` — L1 seed 12345 viewport cells

Optional external PNG/JSON dumps may be placed beside oracle JSONL for visual compare.
MD
        );

        return 0;
    }

    /**
     * 15 golden pieces: per-micro JSONL + MICROTILE_GOLDEN_PIECES.md.
     * Town: fail on first TownOracle micro field mismatch.
     * L1: fail on first raw-MIN type/zero-frame mismatch.
     * @param array<string,string|bool> $opts
     */
    private static function cmdMicrotileGolden(AssetStore $assets, array $opts): int
    {
        $root = dirname(__DIR__, 2);
        $outDir = $root . '/tmp/parity/golden';
        $pieceDir = $root . '/tmp/parity/pieces';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }
        if (!is_dir($pieceDir)) {
            mkdir($pieceDir, 0775, true);
        }

        $townGold = [
            ['id' => 'grass_path_black_diamond', 'x' => 75, 'y' => 74, 'dPiece' => 97],
            ['id' => 'black_diamond_bc', 'x' => 79, 'y' => 72, 'dPiece' => 180],
            ['id' => 'black_diamond_br', 'x' => 81, 'y' => 68, 'dPiece' => 282],
            ['id' => 'hut_roof', 'x' => 73, 'y' => 69, 'dPiece' => 1144],
            ['id' => 'tree_stripe', 'x' => 75, 'y' => 69, 'dPiece' => 856],
            ['id' => 'tree_base', 'x' => 76, 'y' => 68, 'dPiece' => 123],
            ['id' => 'wall_float', 'x' => 75, 'y' => 65, 'dPiece' => 198],
            ['id' => 'ground_stripe', 'x' => 74, 'y' => 72, 'dPiece' => 857],
            ['id' => 'river_edge', 'x' => 78, 'y' => 66, 'dPiece' => 277],
            ['id' => 'hut_wall', 'x' => 73, 'y' => 67, 'dPiece' => 1156],
        ];
        $l1Gold = [
            ['id' => 'l1_floor', 'dPiece' => 215],
            ['id' => 'l1_wall_junction', 'dPiece' => 350],
            ['id' => 'l1_doorway', 'dPiece' => 43],
            ['id' => 'l1_stairs_up', 'dPiece' => 141],
            ['id' => 'l1_arch_special', 'dPiece' => 11],
        ];

        $town = new DungeonMap();
        $town->loadTown($assets);
        $oracle = TownOracle::build($assets);
        $townMin = 'levels\\towndata\\town.min';

        $jsonl = $outDir . '/microtile_golden.jsonl';
        $fh = fopen($jsonl, 'wb');
        $fail = 0;
        $firstFail = null;
        $md = [];
        $md[] = '# Microtile Golden Pieces';
        $md[] = '';
        $md[] = '15 fixed pieces (10 Town + 5 L1). Data oracle = TownOracle + raw MIN.';
        $md[] = '';
        $md[] = '## Town';
        $md[] = '';

        foreach ($townGold as $g) {
            $x = (int) $g['x'];
            $y = (int) $g['y'];
            $expect = (int) $g['dPiece'];
            $atTile = (int) ($town->dPiece[$y][$x] ?? 0);
            $piece = $expect; // always table the golden dPiece id
            $rec = self::goldenPieceRecord($town, $assets, $townMin, $piece, 'town', $g['id'], $x, $y);
            $rec['expectedDPiece'] = $expect;
            $rec['dPieceAtTile'] = $atTile;
            $rec['dPieceMatch'] = $atTile === $expect ? 1 : 0;
            if ($atTile !== $expect && $firstFail === null) {
                $firstFail = sprintf('TOWN %s tile(%d,%d) dPiece live=%d expected=%d', $g['id'], $x, $y, $atTile, $expect);
                $fail++;
            }
            $oraPiece = (int) ($oracle->dPiece[$y][$x] ?? 0);
            $oraDiff = self::diffMicrosVsOracle($town, $oracle, $piece, $piece);
            if ($atTile !== $oraPiece && $firstFail === null) {
                $firstFail = sprintf('TOWN %s tile dPiece php=%d oracle=%d', $g['id'], $atTile, $oraPiece);
                $fail++;
            }
            $rec['oracle'] = $oraDiff;
            if (empty($oraDiff['ok']) && $firstFail === null) {
                $firstFail = sprintf('TOWN %s oracle micros: %s', $g['id'], $oraDiff['diff'] ?? '?');
                $fail++;
            }
            self::appendIsolatedChecksums($town, $piece, $pieceDir, $rec);
            fwrite($fh, json_encode($rec, JSON_UNESCAPED_SLASHES) . "\n");
            $md[] = self::goldenMdSection($rec, $oraDiff);
        }

        $md[] = '## L1 (seed 12345)';
        $md[] = '';
        $l1 = new DungeonMap();
        $rng = new Random();
        $seed = (int) ($opts['seed'] ?? 12345);
        $rng->setRndSeed($seed);
        $l1->loadCathedral($assets, $rng, $seed, 1);
        $l1Min = 'levels\\l1data\\l1.min';

        foreach ($l1Gold as $g) {
            $piece = (int) $g['dPiece'];
            $sample = self::findPieceTile($l1, $piece);
            $rec = self::goldenPieceRecord(
                $l1,
                $assets,
                $l1Min,
                $piece,
                'l1',
                $g['id'],
                $sample[0] ?? -1,
                $sample[1] ?? -1
            );
            $diff = $l1->diffPieceVsRawMin($assets, $l1Min, $piece);
            $rec['rawMinDiff'] = $diff;
            if (empty($diff['ok']) && $firstFail === null) {
                $firstFail = sprintf('L1 %s raw MIN: %s', $g['id'], $diff['diff'] ?? '?');
                $fail++;
            }
            self::appendIsolatedChecksums($l1, $piece, $pieceDir, $rec);
            fwrite($fh, json_encode($rec, JSON_UNESCAPED_SLASHES) . "\n");
            $md[] = self::goldenMdSection($rec, $diff);
        }
        fclose($fh);

        $md[] = '## Result';
        $md[] = '';
        $md[] = $fail === 0
            ? '**DATA tables: PASS** (no type/zero-frame/oracle field mismatch on golden set).'
            : '**DATA tables: FAIL** first=' . ($firstFail ?? '?');
        $md[] = '';
        $md[] = 'JSONL → `tmp/parity/golden/microtile_golden.jsonl`';
        $md[] = 'Isolated PNGs → `tmp/parity/pieces/piece-N-micro-I.png`';
        $md[] = '';
        file_put_contents($root . '/tmp/MICROTILE_GOLDEN_PIECES.md', implode("\n", $md) . "\n");

        fwrite(STDOUT, sprintf(
            "MICROTILE_GOLDEN fail=%d%s → %s\n",
            $fail,
            $firstFail !== null ? " first={$firstFail}" : '',
            $jsonl
        ));

        return $fail === 0 ? 0 : 2;
    }

    /**
     * @return array<string,mixed>
     */
    private static function goldenPieceRecord(
        DungeonMap $map,
        AssetStore $assets,
        string $minPath,
        int $piece,
        string $level,
        string $id,
        int $x,
        int $y,
    ): array {
        $sol = (int) ($map->sol[$piece] ?? 0);
        $raw = $map->rawMinMicros($assets, $minPath, $piece);
        $live = [];
        $mt = $map->dPieceMicros[$piece] ?? [];
        foreach ($mt as $i => $m) {
            $frame = (int) ($m['frame'] ?? 0);
            $type = (int) ($m['type'] ?? 0);
            $bytes = $frame > 0 ? $map->celFrameBytes($frame) : '';
            $relX = ($i & 1) * 32;
            $relY = -intdiv((int) $i, 2) * 32;
            $rawRow = $raw[$i] ?? null;
            $frame0BasedSuspect = $frame > 0 && $rawRow !== null
                && (int) ($rawRow['frame'] ?? 0) === $frame + 1;
            $live[] = [
                'i' => (int) $i,
                'type' => $type,
                'frame' => $frame,
                'data' => (int) ($m['data'] ?? 0),
                'celBytes' => strlen($bytes),
                'relX' => $relX,
                'relY' => $relY,
                'rawType' => $rawRow !== null ? (int) $rawRow['type'] : null,
                'rawFrame' => $rawRow !== null ? (int) $rawRow['frame'] : null,
                'rawData' => $rawRow !== null ? (int) $rawRow['data'] : null,
                'frame0BasedSuspect' => $frame0BasedSuspect ? 1 : 0,
            ];
        }

        return [
            'id' => $id,
            'level' => $level,
            'tile' => [$x, $y],
            'dPiece' => $piece,
            'sol' => $sol,
            'isFloor' => (($sol & 0x05) === 0) ? 1 : 0,
            'microTileLen' => $map->microTileLen,
            'floorFlags' => [
                'drawFloorForcesLRTriangle' => (($sol & 0x05) === 0) ? 1 : 0,
                'foliageIfTypeTS' => 1,
            ],
            'rawMin' => $raw,
            'micros' => $live,
        ];
    }

    /**
     * @return array{ok:bool,diff:?string}
     */
    private static function diffMicrosVsOracle(DungeonMap $php, DungeonMap $oracle, int $phpPiece, int $oraPiece): array
    {
        if ($phpPiece !== $oraPiece) {
            return ['ok' => false, 'diff' => "dPiece php={$phpPiece} oracle={$oraPiece}"];
        }
        if ($phpPiece <= 0) {
            return ['ok' => true, 'diff' => null];
        }
        $a = $php->dPieceMicros[$phpPiece] ?? [];
        $b = $oracle->dPieceMicros[$oraPiece] ?? [];
        $n = max(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            $ma = $a[$i] ?? null;
            $mb = $b[$i] ?? null;
            if ($ma === null || $mb === null) {
                return ['ok' => false, 'diff' => "micro[{$i}] missing"];
            }
            foreach (['type', 'frame'] as $field) {
                if ((int) ($ma[$field] ?? -1) !== (int) ($mb[$field] ?? -2)) {
                    return [
                        'ok' => false,
                        'diff' => sprintf(
                            'micro[%d].%s php=%d oracle=%d',
                            $i,
                            $field,
                            (int) ($ma[$field] ?? -1),
                            (int) ($mb[$field] ?? -2)
                        ),
                    ];
                }
            }
            $relX = ($i & 1) * 32;
            $relY = -intdiv($i, 2) * 32;
            // rel offsets are formulaic — assert both sides use same i
            if ($relX !== ($i & 1) * 32 || $relY !== -intdiv($i, 2) * 32) {
                return ['ok' => false, 'diff' => "micro[{$i}] rel offset"];
            }
        }

        return ['ok' => true, 'diff' => null];
    }

    /**
     * @param array<string,mixed> $rec
     */
    private static function appendIsolatedChecksums(DungeonMap $map, int $piece, string $pieceDir, array &$rec): void
    {
        $sol = (int) ($map->sol[$piece] ?? 0);
        $isFloor = ($sol & 0x05) === 0;
        $checks = [];
        foreach ($map->dPieceMicros[$piece] ?? [] as $i => $m) {
            $frame = (int) ($m['frame'] ?? 0);
            if ($frame === 0) {
                continue;
            }
            $type = (int) ($m['type'] ?? 0);
            $bytes = $map->celFrameBytes($frame);
            $decType = $type;
            $mode = '';
            if ($isFloor && $i < 2) {
                $decType = ($i & 1) === 0 ? DunMicro::TYPE_LEFT_TRIANGLE : DunMicro::TYPE_RIGHT_TRIANGLE;
                $mode = 'floor_triangle';
            }
            try {
                $dec = DunMicro::decode($bytes, $decType, $map->palette, true, $mode === 'foliage' ? 'foliage' : '');
            } catch (\Throwable $e) {
                $checks[] = ['i' => (int) $i, 'error' => $e->getMessage()];

                continue;
            }
            $rgba = $dec['rgba'];
            $w = (int) $dec['w'];
            $h = (int) $dec['h'];
            $indexPlane = '';
            $alphaPlane = '';
            $opaque = 0;
            for ($p = 0; $p < strlen($rgba); $p += 4) {
                $a = ord($rgba[$p + 3]);
                $alphaPlane .= chr($a);
                if ($a === 0) {
                    $indexPlane .= "\x00";
                } else {
                    $opaque++;
                    // approximate index plane via RGB presence
                    $indexPlane .= chr(1);
                }
            }
            $path = sprintf('%s/piece-%d-micro-%d.png', $pieceDir, $piece, $i);
            self::writeMagentaPng($path, $rgba, $w, $h);
            $checks[] = [
                'i' => (int) $i,
                'decType' => $decType,
                'mode' => $mode,
                'w' => $w,
                'h' => $h,
                'opaque' => $opaque,
                'md5_rgba' => md5($rgba),
                'md5_alpha' => md5($alphaPlane),
                'md5_index_proxy' => md5($indexPlane),
                'png' => $path,
                'blitAssert' => '1:1 no scale/rotate (SDL_RenderCopy only)',
            ];
        }
        $rec['isolated'] = $checks;
        file_put_contents(
            sprintf('%s/piece-%d.json', $pieceDir, $piece),
            json_encode(['piece' => $piece, 'micros' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );
    }

    private static function writeMagentaPng(string $path, string $rgba, int $w, int $h): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            return;
        }
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $mag = imagecolorallocatealpha($im, 255, 0, 255, 0);
        imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $mag);
        imagealphablending($im, true);
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $i = ($y * $w + $x) * 4;
                $a = ord($rgba[$i + 3]);
                if ($a === 0) {
                    continue;
                }
                $c = imagecolorallocatealpha($im, ord($rgba[$i]), ord($rgba[$i + 1]), ord($rgba[$i + 2]), 127 - intdiv($a, 2));
                imagesetpixel($im, $x, $y, $c);
            }
        }
        imagepng($im, $path);
    }

    /**
     * @param array<string,mixed> $rec
     * @param array{ok?:bool,diff?:?string} $diff
     */
    private static function goldenMdSection(array $rec, array $diff): string
    {
        $lines = [];
        $lines[] = sprintf(
            '### %s — dPiece=%d tile=(%s,%s) sol=0x%02X floor=%s',
            $rec['id'],
            $rec['dPiece'],
            $rec['tile'][0],
            $rec['tile'][1],
            $rec['sol'],
            !empty($rec['isFloor']) ? 'yes' : 'no'
        );
        $lines[] = '';
        $ok = !empty($diff['ok']);
        $lines[] = 'Data: ' . ($ok ? 'MATCH' : ('FAIL ' . ($diff['diff'] ?? '')));
        $lines[] = '';
        $lines[] = '| i | type | frame | rawType | rawFrame | rel | celBytes |';
        $lines[] = '|---|------|-------|---------|----------|-----|----------|';
        foreach ($rec['micros'] as $m) {
            $lines[] = sprintf(
                '| %d | %d | %d | %s | %s | (%d,%d) | %d |',
                $m['i'],
                $m['type'],
                $m['frame'],
                $m['rawType'] ?? '—',
                $m['rawFrame'] ?? '—',
                $m['relX'],
                $m['relY'],
                $m['celBytes']
            );
        }
        $lines[] = '';

        return implode("\n", $lines);
    }

    /** @return array{0:int,1:int}|null */
    private static function findPieceTile(DungeonMap $map, int $piece): ?array
    {
        for ($y = 16; $y < 96; $y++) {
            for ($x = 16; $x < 96; $x++) {
                if ((int) ($map->dPiece[$y][$x] ?? 0) === $piece) {
                    return [$x, $y];
                }
            }
        }

        return null;
    }
}
