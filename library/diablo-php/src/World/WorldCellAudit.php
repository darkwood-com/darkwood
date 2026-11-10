<?php

declare(strict_types=1);

namespace Diablo\World;

use Diablo\Engine\Render\IsoCoords;
use Diablo\Engine\Render\Scrollrt;
use Diablo\Levels\DungeonMap;

/**
 * World-data cell schema + PHP↔/oracle diff + validate-visible.
 * Does not change Scrollrt draw order.
 */
final class WorldCellAudit
{
    public static bool $enabled = false;
    /** @var array<string,int> */
    public static array $blitDiag = [];

    public static function resetBlitDiag(): void
    {
        self::$blitDiag = [
            'VISIBLE_ZERO_PIECE' => 0,
            'MISSING_MICROS_TABLE' => 0,
            'FRAME_ZERO' => 0,
            'CEL_BYTES_EMPTY' => 0,
            'DECODE_FAIL' => 0,
            'BLIT_OK' => 0,
        ];
    }

    public static function noteBlit(string $code, string $detail = ''): void
    {
        if (!isset(self::$blitDiag[$code])) {
            self::$blitDiag[$code] = 0;
        }
        self::$blitDiag[$code]++;
        if (self::$enabled && $detail !== '') {
            fwrite(STDERR, "WORLD_DATA {$code} {$detail}\n");
        }
    }

    public static function stageLog(string $stage, array $payload): void
    {
        $row = ['stage' => $stage] + $payload;
        $line = json_encode($row) . "\n";
        fwrite(STDERR, "STAGE_{$stage} " . json_encode($payload) . "\n");
        $path = dirname(__DIR__, 2) . '/tmp/world-data-audit.jsonl';
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $line, FILE_APPEND);
    }

    /** Counts of special dPiece values after DRLG. */
    public static function stageDrlg(DungeonMap $map, string $producer): void
    {
        $c0 = $c218 = $c426 = $other = 0;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $p = (int) ($map->dPiece[$y][$x] ?? 0);
                if ($p === 0) {
                    $c0++;
                } elseif ($p === 218) {
                    $c218++;
                } elseif ($p === 426) {
                    $c426++;
                } else {
                    $other++;
                }
            }
        }
        self::stageLog('DRLG', [
            'producer' => $producer,
            'levelType' => $map->levelType,
            'dPiece_0' => $c0,
            'dPiece_218' => $c218,
            'dPiece_426' => $c426,
            'dPiece_other' => $other,
        ]);
    }

    public static function stageMicros(DungeonMap $map): void
    {
        $pieces = count($map->dPieceMicros);
        $emptyAll = 0;
        foreach ($map->dPieceMicros as $mt) {
            $any = false;
            foreach ($mt as $b) {
                if (((int) ($b['frame'] ?? 0)) > 0) {
                    $any = true;
                    break;
                }
            }
            if (!$any) {
                $emptyAll++;
            }
        }
        self::stageLog('MICROS', [
            'pieceTables' => $pieces,
            'emptyAllFrames' => $emptyAll,
            'microTileLen' => $map->microTileLen,
            'celFrames' => $map->celFrames,
        ]);
    }

    /**
     * Canonical cell record (world x,y).
     * @return array<string,mixed>
     */
    public static function cellRecord(DungeonMap $map, int $x, int $y, string $producerHint = 'php'): array
    {
        $inBounds = $map->inBounds($x, $y);
        $piece = $inBounds ? (int) ($map->dPiece[$y][$x] ?? 0) : 0;
        $sol = $piece >= 0 ? (int) ($map->sol[$piece] ?? 0) : 0;
        $trans = $inBounds ? (int) ($map->dTransVal[$y][$x] ?? 0) : 0;
        $micros = [];
        $celPresent = [];
        $hasMicros = isset($map->dPieceMicros[$piece]) && $map->dPieceMicros[$piece] !== [];
        if ($hasMicros) {
            foreach ($map->dPieceMicros[$piece] as $bi => $b) {
                $frame = (int) ($b['frame'] ?? 0);
                $type = (int) ($b['type'] ?? 0);
                $data = (int) ($b['data'] ?? (($type & 7) << 12) | ($frame & 0xFFF));
                $micros[] = [
                    'block' => (int) $bi,
                    'type' => $type,
                    'frame' => $frame,
                    'data' => $data,
                ];
                $bytes = $frame > 0 ? $map->celFrameBytes($frame) : '';
                $celPresent[] = [
                    'block' => (int) $bi,
                    'frame' => $frame,
                    'byteLen' => strlen($bytes),
                ];
            }
        }
        $category = 'OUTSIDE_WORLD';
        if ($inBounds) {
            // Piece 0 is a valid MIN/SOL index (DX zero-index); only missing micros is empty.
            if (!$hasMicros) {
                $category = $piece === 0 ? 'ZERO_PIECE' : 'MICRO_TILE_MISSING';
            } else {
                $anyFrame = false;
                $anyCel = false;
                $missingCel = false;
                foreach ($micros as $i => $m) {
                    if ($m['frame'] > 0) {
                        $anyFrame = true;
                        if (($celPresent[$i]['byteLen'] ?? 0) > 0) {
                            $anyCel = true;
                        } else {
                            $missingCel = true;
                        }
                    }
                }
                if (!$anyFrame) {
                    $category = 'EMPTY_MICROS_LEGIT';
                } elseif ($missingCel && !$anyCel) {
                    $category = 'MISSING_ASSET';
                } elseif ($missingCel) {
                    $category = 'PARTIAL_CEL';
                } else {
                    $category = 'DRAWN';
                }
            }
        }

        return [
            'x' => $x,
            'y' => $y,
            'dPiece' => $piece,
            'micros' => $micros,
            'sol' => $sol,
            'solid' => $inBounds && $map->isSolid($x, $y) ? 1 : 0,
            'transparent' => ($sol & DungeonMap::SOL_TRANSPARENT) !== 0 ? 1 : 0,
            'dTransVal' => $trans,
            'transList' => $map->transListActive($trans) ? 1 : 0,
            'dLight' => $inBounds ? (int) ($map->dLight[$y][$x] ?? 0) : 0,
            'dSpecial' => $inBounds ? (int) ($map->dSpecial[$y][$x] ?? 0) : 0,
            'isFloor' => $inBounds && $map->isFloorTile($x, $y) ? 1 : 0,
            'celPresent' => $celPresent,
            'category' => $category,
            'producerHint' => $producerHint,
            'levelType' => $map->levelType,
            'inBounds' => $inBounds ? 1 : 0,
        ];
    }

    /** @param resource|null $fh */
    public static function dumpGrid(DungeonMap $map, string $outPath, string $producerHint = 'php'): int
    {
        $dir = dirname($outPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $fh = fopen($outPath, 'wb');
        if ($fh === false) {
            throw new \RuntimeException("Cannot write {$outPath}");
        }
        $n = 0;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                fwrite($fh, json_encode(self::cellRecord($map, $x, $y, $producerHint)) . "\n");
                $n++;
            }
        }
        fclose($fh);

        return $n;
    }

    /**
     * @return array<string, array<string,mixed>> keyed "x,y"
     */
    public static function loadDumpIndex(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException("DX/oracle dump not found: {$path}");
        }
        $idx = [];
        $fh = fopen($path, 'rb');
        if ($fh === false) {
            throw new \RuntimeException("Cannot read {$path}");
        }
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $row = json_decode($line, true);
            if (!is_array($row)) {
                continue;
            }
            $x = (int) ($row['x'] ?? -1);
            $y = (int) ($row['y'] ?? -1);
            $idx[$x . ',' . $y] = $row;
        }
        fclose($fh);

        return $idx;
    }

    /**
     * First differing field only (dPiece → micros → sol → light).
     * @return array{ok:bool,diff:?string,php:array,dx:?array}
     */
    public static function diffCell(array $php, ?array $dx): array
    {
        if ($dx === null) {
            return ['ok' => false, 'diff' => 'DX row missing', 'php' => $php, 'dx' => null];
        }
        if ((int) $php['dPiece'] !== (int) $dx['dPiece']) {
            return [
                'ok' => false,
                'diff' => sprintf('dPiece mismatch php=%d dx=%d', $php['dPiece'], $dx['dPiece']),
                'php' => $php,
                'dx' => $dx,
            ];
        }
        $pm = $php['micros'] ?? [];
        $dm = $dx['micros'] ?? [];
        $nmax = max(count($pm), count($dm));
        for ($i = 0; $i < $nmax; $i++) {
            $a = $pm[$i] ?? null;
            $b = $dm[$i] ?? null;
            if ($a === null || $b === null) {
                return [
                    'ok' => false,
                    'diff' => sprintf('micro[%d] count mismatch php=%d dx=%d', $i, count($pm), count($dm)),
                    'php' => $php,
                    'dx' => $dx,
                ];
            }
            foreach (['data', 'type', 'frame'] as $k) {
                if ((int) ($a[$k] ?? -1) !== (int) ($b[$k] ?? -2)) {
                    return [
                        'ok' => false,
                        'diff' => sprintf(
                            'micro[%d].%s mismatch php=%s dx=%s',
                            $i,
                            $k,
                            (string) ($a[$k] ?? ''),
                            (string) ($b[$k] ?? '')
                        ),
                        'php' => $php,
                        'dx' => $dx,
                    ];
                }
            }
        }
        if ((int) ($php['sol'] ?? 0) !== (int) ($dx['sol'] ?? 0)) {
            return [
                'ok' => false,
                'diff' => sprintf('sol mismatch php=0x%02X dx=0x%02X', $php['sol'], $dx['sol']),
                'php' => $php,
                'dx' => $dx,
            ];
        }
        if ((int) ($php['dLight'] ?? 0) !== (int) ($dx['dLight'] ?? 0)) {
            return [
                'ok' => false,
                'diff' => sprintf('dLight mismatch php=%d dx=%d', $php['dLight'], $dx['dLight']),
                'php' => $php,
                'dx' => $dx,
            ];
        }

        return ['ok' => true, 'diff' => null, 'php' => $php, 'dx' => $dx];
    }

    public static function formatDiffReport(int $x, int $y, array $result): string
    {
        $out = "CELL {$x},{$y}\n\n";
        $dx = $result['dx'];
        $php = $result['php'];
        $out .= "DX\n";
        if ($dx === null) {
            $out .= "  (missing)\n";
        } else {
            $out .= sprintf("  dPiece = %d\n", $dx['dPiece']);
            $out .= "  micros\n";
            foreach ($dx['micros'] ?? [] as $m) {
                $out .= sprintf("   %d type=%d frame=%d data=0x%04X\n", $m['block'], $m['type'], $m['frame'], $m['data']);
            }
        }
        $out .= "\nPHP\n";
        $out .= sprintf("  dPiece = %d\n", $php['dPiece']);
        $out .= "  micros\n";
        foreach ($php['micros'] ?? [] as $m) {
            $out .= sprintf("   %d type=%d frame=%d data=0x%04X\n", $m['block'], $m['type'], $m['frame'], $m['data']);
        }
        $out .= "\nDIFF\n";
        $out .= '  ' . ($result['ok'] ? 'match' : ($result['diff'] ?? 'mismatch')) . "\n";

        return $out;
    }

    /**
     * @return array{visible:int,ok:int,missing_dPiece:int,missing_micro:int,missing_CEL:int,missing_frame:int,render_skipped:int,unexpected:int,lines:list<string>}
     */
    public static function validateVisible(DungeonMap $map, int $viewX, int $viewY, int $scrollX = 0, int $scrollY = 0): array
    {
        Scrollrt::prepareViewport(IsoCoords::SCREEN_W, IsoCoords::viewportHeight());
        [$ftx, $fty] = Scrollrt::firstTile($viewX, $viewY, $scrollX, $scrollY);
        $vp = Scrollrt::viewportStats();
        $rows = (int) ($vp['tileRows'] ?? 18);
        $cols = (int) ($vp['tileColumns'] ?? 14);
        $sum = [
            'visible' => 0,
            'ok' => 0,
            'missing_dPiece' => 0,
            'missing_micro' => 0,
            'missing_CEL' => 0,
            'missing_frame' => 0,
            'render_skipped' => 0,
            'unexpected' => 0,
            'lines' => [],
        ];
        $tileX = $ftx;
        $tileY = $fty;
        $columns = $cols;
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $columns; $j++) {
                if ($map->inBounds($tileX, $tileY)) {
                    $sum['visible']++;
                    $rec = self::cellRecord($map, $tileX, $tileY);
                    $piece = (int) $rec['dPiece'];
                    $hasPiece = $piece >= 0 && isset($map->dPieceMicros[$piece]);
                    $hasMicros = ($rec['micros'] ?? []) !== [];
                    $hasFrame = false;
                    $hasCel = false;
                    $missingCel = false;
                    foreach ($rec['micros'] ?? [] as $mi => $m) {
                        if ((int) $m['frame'] > 0) {
                            $hasFrame = true;
                            $blen = (int) ($rec['celPresent'][$mi]['byteLen'] ?? 0);
                            if ($blen > 0) {
                                $hasCel = true;
                            } else {
                                $missingCel = true;
                            }
                        }
                    }
                    $wouldBlit = $hasPiece && $hasMicros && $hasFrame && $hasCel;
                    $missing = '';
                    if (!$hasPiece) {
                        $sum['missing_dPiece']++;
                        $missing = 'dPiece';
                        $sum['render_skipped']++;
                    } elseif (!$hasMicros) {
                        $sum['missing_micro']++;
                        $missing = 'micro';
                        $sum['render_skipped']++;
                        $sum['unexpected']++;
                        $sum['lines'][] = sprintf('UNEXPECTED missing_micro @%d,%d piece=%d', $tileX, $tileY, $piece);
                    } elseif (!$hasFrame) {
                        $sum['missing_frame']++;
                        $missing = 'frame';
                        $sum['render_skipped']++;
                        // EMPTY_MICROS_LEGIT — not unexpected alone
                    } elseif ($missingCel) {
                        $sum['missing_CEL']++;
                        $missing = 'CEL';
                        $sum['render_skipped']++;
                        $sum['unexpected']++;
                        $sum['lines'][] = sprintf('UNEXPECTED missing_CEL @%d,%d piece=%d', $tileX, $tileY, $piece);
                    } else {
                        $sum['ok']++;
                    }
                    if (self::$enabled) {
                        $sum['lines'][] = sprintf(
                            'cell %d,%d piece=%d micros=%d frame=%d cel=%d blit=%d miss=%s cat=%s',
                            $tileX,
                            $tileY,
                            $piece,
                            $hasMicros ? 1 : 0,
                            $hasFrame ? 1 : 0,
                            $hasCel ? 1 : 0,
                            $wouldBlit ? 1 : 0,
                            $missing !== '' ? $missing : '-',
                            $rec['category']
                        );
                    }
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
        self::stageLog('VISIBLE', [
            'view' => [$viewX, $viewY],
            'visible' => $sum['visible'],
            'ok' => $sum['ok'],
            'missing_dPiece' => $sum['missing_dPiece'],
            'missing_micro' => $sum['missing_micro'],
            'missing_CEL' => $sum['missing_CEL'],
            'missing_frame' => $sum['missing_frame'],
            'render_skipped' => $sum['render_skipped'],
            'unexpected' => $sum['unexpected'],
        ]);

        return $sum;
    }

    /**
     * Scan all cells vs index; return first mismatch.
     * @param array<string,array<string,mixed>> $dxIndex
     * @return array{ok:bool,x:int,y:int,result:array}|null null if all match
     */
    public static function findFirstMismatch(DungeonMap $map, array $dxIndex, ?callable $tileFilter = null): ?array
    {
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                if ($tileFilter !== null && !$tileFilter($x, $y)) {
                    continue;
                }
                $php = self::cellRecord($map, $x, $y);
                $dx = $dxIndex[$x . ',' . $y] ?? null;
                $r = self::diffCell($php, $dx);
                if (!$r['ok']) {
                    return ['ok' => false, 'x' => $x, 'y' => $y, 'result' => $r];
                }
            }
        }

        return null;
    }
}
