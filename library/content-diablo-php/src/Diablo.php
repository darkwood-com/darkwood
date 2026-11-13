<?php

declare(strict_types=1);

namespace Diablo;

use Diablo\DiabloUI\TitleDialog;
use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Assets\Cel;
use Diablo\Engine\Assets\Cl2;
use Diablo\Engine\Assets\MpqArchive;
use Diablo\Engine\Assets\Palette;
use Diablo\Engine\Assets\Pcx;
use Diablo\Engine\Render\IsoCoords;
use Diablo\Engine\Render\Scrollrt;
use Diablo\Engine\Direction;
use Diablo\Platform\Sdl;
use Diablo\Cursor;

/**
 * Application entry (single-player main loop).
 * See NOTICE.md for attribution.
 */
final class Diablo
{
    private const WINDOW_W = IsoCoords::SCREEN_W;
    private const WINDOW_H = IsoCoords::SCREEN_H;

    private MpqArchive $mpq;
    private AssetStore $assets;
    private Sdl $sdl;
    private GameState $state;
    private bool $running = true;

    /**
     * @param array<string,mixed> $opts
     */
    public function __construct(
        private readonly string $dataPath,
        private readonly array $opts = [],
    ) {
        $this->mpq = new MpqArchive($dataPath);
        $this->assets = new AssetStore($this->mpq);
        $this->sdl = new Sdl();
        $this->state = new GameState($this->assets, new Audio($this->assets));
    }

    public function run(): void
    {
        $smoke = (bool) ($this->opts['smoke'] ?? false);
        $town = (bool) ($this->opts['town'] ?? false);
        $level = (int) ($this->opts['level'] ?? 0);
        $seed = (int) ($this->opts['seed'] ?? 0);
        $debug = (bool) ($this->opts['debug'] ?? false);
        $playtest = (bool) ($this->opts['playtest'] ?? false);
        $debugGeneration = (bool) ($this->opts['debugGeneration'] ?? false);
        $mazeFallback = (bool) ($this->opts['mazeFallback'] ?? false);
        $debugAsset = (string) ($this->opts['debugAsset'] ?? '');
        $debugFrame = (int) ($this->opts['debugFrame'] ?? 0);
        $debugStaticFrame = (bool) ($this->opts['debugStaticFrame'] ?? false);
        $dumpFrame = (string) ($this->opts['dumpFrame'] ?? '');
        $debugGenAscii = (bool) ($this->opts['debugGenAscii'] ?? false);
        $debugPiece = (int) ($this->opts['debugPiece'] ?? -1);
        $dumpPalette = (bool) ($this->opts['dumpPalette'] ?? false);
        $debugAlpha = (bool) ($this->opts['debugAlpha'] ?? false);
        $debugFont = (bool) ($this->opts['debugFont'] ?? false);
        $debugDrawLog = (bool) ($this->opts['debugDrawLog'] ?? false);
        $debugTitleAssets = (bool) ($this->opts['debugTitleAssets'] ?? false);
        $debugTitleFrame = (int) ($this->opts['debugTitleFrame'] ?? -1);
        $debugTitleAnimation = (bool) ($this->opts['debugTitleAnimation'] ?? false);
        $debugTitleCapture = (bool) ($this->opts['debugTitleCapture'] ?? false);
        $debugTileCoverage = (bool) ($this->opts['debugTileCoverage'] ?? false);
        $debugTileCoverageOverlay = (bool) ($this->opts['debugTileCoverageOverlay'] ?? false);
        $coverageSuite = (bool) ($this->opts['coverageSuite'] ?? false);
        $movementSuite = (bool) ($this->opts['movementSuite'] ?? false);
        $profileFrame = (bool) ($this->opts['profileFrame'] ?? false);
        $debugOneStep = (bool) ($this->opts['debugOneStep'] ?? false);
        $scenario = strtoupper((string) ($this->opts['scenario'] ?? ''));
        $gameplayTrace = $this->opts['gameplayTrace'] ?? false;
        $debugGameplayOverlay = (bool) ($this->opts['debugGameplayOverlay'] ?? false);
        $debugCursorAssets = (bool) ($this->opts['debugCursorAssets'] ?? false);
        $debugPlayerAttackFrame = $this->opts['debugPlayerAttackFrame'] ?? null;
        $debugPlayerAttackAssets = (bool) ($this->opts['debugPlayerAttackAssets'] ?? false);
        $debugDirection = strtoupper((string) ($this->opts['direction'] ?? 'E'));

        $this->state->mazeFallback = $mazeFallback;
        $this->state->renderLayer = (string) ($this->opts['renderLayer'] ?? 'all');
        $this->state->debugPlaceholders = (bool) ($this->opts['debugPlaceholders'] ?? false);
        $this->state->fullbright = (bool) ($this->opts['fullbright'] ?? false);
        $this->state->cameraFixed = (bool) ($this->opts['cameraFixed'] ?? false);
        $this->state->debugWalk = (bool) ($this->opts['debugWalk'] ?? false);
        $this->state->debugDrawLog = $debugDrawLog;
        $this->state->debugTileCoverage = $debugTileCoverage;
        $this->state->debugTileCoverageOverlay = $debugTileCoverageOverlay;
        $this->state->debugWorldPick = (bool) ($this->opts['debugWorldPick'] ?? false);
        $this->state->forceFullRedraw = (bool) ($this->opts['forceFullRedraw'] ?? false);
        $this->state->debugClearMagenta = (bool) ($this->opts['debugClearMagenta'] ?? false);
        $this->state->debugDisableMasks = (bool) ($this->opts['debugDisableMasks'] ?? false);
        $this->state->disableWorldClipping = (bool) ($this->opts['disableWorldClipping'] ?? false);
        $this->state->debugRenderMargin = (int) ($this->opts['debugRenderMargin'] ?? 0);
        $this->state->debugWalkCapture = (bool) ($this->opts['debugWalkCapture'] ?? false);
        $this->state->debugWalkStep = (bool) ($this->opts['debugWalkStep'] ?? false);
        $this->state->debugCamera = (bool) ($this->opts['debugCamera'] ?? false);
        $this->state->debugClickOverlay = (bool) ($this->opts['debugClickOverlay'] ?? false);
        $this->state->debugCursor = (bool) ($this->opts['debugCursor'] ?? false);
        $this->state->debugCursorTarget = (bool) ($this->opts['debugCursorTarget'] ?? false);
        $this->state->debugPlayerState = (bool) ($this->opts['debugPlayerState'] ?? false);
        $this->state->debugGameplayOverlay = $debugGameplayOverlay;
        $this->state->freezeFirstFrame = (bool) ($this->opts['freezeFirstFrame'] ?? false);
        $this->state->tracePlayerDraw = (bool) ($this->opts['tracePlayerDraw'] ?? false);
        $this->state->traceActorDraw = (bool) ($this->opts['traceActorDraw'] ?? false);
        $this->state->debugRenderState = (bool) ($this->opts['debugRenderState'] ?? false);
        $this->state->hideActors = (bool) ($this->opts['hideActors'] ?? false);
        $this->state->hideObjects = (bool) ($this->opts['hideObjects'] ?? false);
        $this->state->hideItems = (bool) ($this->opts['hideItems'] ?? false);
        $this->state->hideHud = (bool) ($this->opts['hideHud'] ?? false);
        $this->state->renderTrace = (bool) ($this->opts['renderTrace'] ?? false);
        $this->state->softwareCompose = (bool) ($this->opts['softwareCompose'] ?? false);
        $dp = (string) ($this->opts['debugPixel'] ?? '');
        if ($dp !== '' && preg_match('/^(-?\d+)\s*,\s*(-?\d+)$/', $dp, $dm)) {
            $this->state->debugPixel = [(int) $dm[1], (int) $dm[2]];
            $this->state->renderTrace = true;
            fwrite(STDOUT, sprintf("debug-pixel → %d,%d\n", (int) $dm[1], (int) $dm[2]));
        }
        if ($this->state->softwareCompose) {
            $this->state->renderTrace = true;
        }
        if ($this->state->freezeFirstFrame) {
            $debugStaticFrame = true;
        }

        Scrollrt::$debugRenderPasses = (bool) ($this->opts['debugRenderPasses'] ?? false);
        Scrollrt::$debugRenderState = $this->state->debugRenderState;
        $traceTile = (string) ($this->opts['traceTile'] ?? '');
        if ($traceTile !== '' && preg_match('/^(-?\d+)\s*,\s*(-?\d+)$/', $traceTile, $tm)) {
            Scrollrt::$traceTile = [(int) $tm[1], (int) $tm[2]];
            fwrite(STDOUT, sprintf("trace-tile → %d,%d\n", (int) $tm[1], (int) $tm[2]));
        }

        if ($gameplayTrace !== false && $gameplayTrace !== null && $gameplayTrace !== '') {
            $root = dirname(__DIR__);
            if ($gameplayTrace === true || $gameplayTrace === '1' || $gameplayTrace === 'true') {
                $tracePath = $root . '/tmp/gameplay-trace.jsonl';
            } else {
                $tracePath = (string) $gameplayTrace;
                if (!str_starts_with($tracePath, '/')) {
                    $tracePath = $root . '/' . $tracePath;
                }
            }
            $this->state->enableGameplayTrace($tracePath);
            fwrite(STDOUT, "gameplay-trace → {$tracePath}\n");
        }

        $this->sdl->init('Diablo PHP', self::WINDOW_W, self::WINDOW_H);
        try {
            if ($debugAsset !== '') {
                $this->runDebugAsset($debugAsset, $debugFrame);

                return;
            }
            if ($debugFont) {
                $this->runDebugFont();

                return;
            }
            if ($debugTitleAssets) {
                $this->runDebugTitleAssets();

                return;
            }
            if ($debugCursorAssets) {
                $this->runDebugCursorAssets();

                return;
            }
            if ($debugPlayerAttackAssets || $debugPlayerAttackFrame !== null) {
                $this->runDebugPlayerAttack(
                    $debugPlayerAttackFrame !== null ? (int) $debugPlayerAttackFrame : null,
                    $debugDirection,
                    $debugPlayerAttackAssets
                );

                return;
            }

            $this->state->bootstrap();
            $this->state->debugTitleFrame = $debugTitleFrame;
            $this->state->debugTitleAnimation = $debugTitleAnimation;
            $this->state->debugTitleCapture = $debugTitleCapture;
            if ($debugTitleFrame >= 0) {
                fwrite(STDOUT, "debug-title-frame={$debugTitleFrame} (frozen; Esc to quit)\n");
            }
            if ($debugTitleAnimation) {
                fwrite(STDOUT, "debug-title-animation on\n");
            }
            if ($debugTitleCapture) {
                fwrite(STDOUT, "debug-title-capture → tmp/title-capture/\n");
            }
            if ($debugGeneration) {
                $this->state->newGame();
                if ($seed > 0) {
                    $this->state->setDungeonSeed($seed);
                }
                $this->state->enterCathedral(max(1, $level));
                $dump = $this->state->generationDump();
                fwrite(STDOUT, "debug-generation:\n" . json_encode($dump, JSON_PRETTY_PRINT) . "\n");
                if ($debugGenAscii) {
                    fwrite(STDOUT, $this->state->dungeonAscii() . "\n");
                }

                return;
            }
            if ($smoke || $playtest || $scenario !== '') {
                if ($seed > 0) {
                    $this->state->setDungeonSeed($seed);
                } elseif ($scenario !== '') {
                    $this->state->setDungeonSeed(12345);
                }
                $this->state->newGame();
                // newGame() reseeds from time — re-apply fixed seed for determinism
                if ($seed > 0) {
                    $this->state->setDungeonSeed($seed);
                } elseif ($scenario !== '') {
                    $this->state->setDungeonSeed(12345);
                }
                if ($scenario !== '') {
                    $ids = $scenario === 'ALL' ? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] : [$scenario];
                    $allPass = true;
                    foreach ($ids as $sid) {
                        $this->state->newGame();
                        $this->state->setDungeonSeed(12345);
                        $result = $this->state->runGameplayScenario($sid);
                        foreach ($result['lines'] as $line) {
                            fwrite(STDOUT, $line . "\n");
                        }
                        if (!$result['pass']) {
                            $allPass = false;
                        }
                        if ($sid === 'L1-FIDELITY' || (bool) ($this->opts['combatWorldStability'] ?? false)) {
                            $stab = $this->state->runCombatWorldStabilityCheck($this->sdl, self::WINDOW_W, self::WINDOW_H);
                            foreach ($stab['lines'] as $line) {
                                fwrite(STDOUT, $line . "\n");
                            }
                            fwrite(STDOUT, ($stab['ok'] ? 'PASS' : 'FAIL') . " COMBAT_WORLD_STABILITY\n");
                            if (!$stab['ok']) {
                                $allPass = false;
                            }
                        }
                    }
                    fwrite(STDOUT, $allPass ? "SCENARIOS_ALL_PASS\n" : "SCENARIOS_FAIL\n");
                    $this->state->gtrace->close();

                    return;
                }
                if ($playtest) {
                    $report = $this->state->runPlaytest();
                    foreach ($report as $line) {
                        fwrite(STDOUT, $line . "\n");
                    }
                } else {
                    $this->state->enterCathedral($level >= 1 ? $level : 1);
                    $this->state->saveGame();
                    fwrite(STDOUT, "Smoke: town+cathedral+save exercised\n");
                }
                $frames = 0;
            } elseif ($debugStaticFrame || $town || $level >= 1 || $debugPiece >= 0 || $dumpPalette || $debugAlpha || $debugTileCoverage || $debugOneStep || $coverageSuite || $movementSuite
                || (string) ($this->opts['dumpCell'] ?? '') !== '' || Scrollrt::$debugRenderPasses || Scrollrt::$traceTile !== null
                || (bool) ($this->opts['townPieceAudit'] ?? false)
                || (bool) ($this->opts['l1PieceAudit'] ?? false)
                || (string) ($this->opts['debugCombatCapture'] ?? '') !== ''
                || (bool) ($this->opts['combatWorldStability'] ?? false)) {
                $this->state->newGame();
                if ($seed > 0) {
                    $this->state->setDungeonSeed($seed);
                } elseif ($debugStaticFrame || $debugPiece >= 0 || $debugTileCoverage || $debugOneStep || $coverageSuite || $movementSuite
                    || (string) ($this->opts['dumpCell'] ?? '') !== '' || Scrollrt::$debugRenderPasses || Scrollrt::$traceTile !== null
                    || (bool) ($this->opts['townPieceAudit'] ?? false)
                    || (bool) ($this->opts['l1PieceAudit'] ?? false)
                    || (string) ($this->opts['debugCombatCapture'] ?? '') !== ''
                    || (bool) ($this->opts['combatWorldStability'] ?? false)) {
                    $this->state->setDungeonSeed(12345);
                }
                if ($movementSuite) {
                    $outDir = dirname(__DIR__) . '/tmp/movement';
                    $result = $this->state->runMovementCoherenceSuite($outDir);
                    foreach ($result['lines'] as $line) {
                        fwrite(STDOUT, $line . "\n");
                    }

                    return;
                }
                if ($coverageSuite) {
                    $outDir = dirname(__DIR__) . '/tmp/coverage';
                    $result = $this->state->runTownCoverageSuite($outDir);
                    foreach ($result['lines'] as $line) {
                        fwrite(STDOUT, $line . "\n");
                    }
                    fwrite(STDOUT, $result['pass'] ? "STRUCTURAL_WORLD_TEST PASS\n" : "STRUCTURAL_WORLD_TEST FAIL\n");

                    return;
                }
                if ($level >= 1) {
                    $this->state->enterCathedral($level);
                }
                $forcePos = (string) ($this->opts['forcePos'] ?? '');
                if ($forcePos !== '' && preg_match('/^(-?\d+)\s*,\s*(-?\d+)$/', $forcePos, $m)) {
                    $this->state->forcePlayerPos((int) $m[1], (int) $m[2]);
                    fwrite(STDOUT, sprintf("force-pos → %d,%d\n", (int) $m[1], (int) $m[2]));
                }
                $debugWalkCapture = (bool) ($this->opts['debugWalkCapture'] ?? false);
                if ($debugWalkCapture) {
                    $this->state->beginDebugWalkCapture();
                }
                if ($debugOneStep) {
                    $onFrame = null;
                    if ($this->state->tracePlayerDraw) {
                        $onFrame = function (string $phase) use ($debugStaticFrame): void {
                            $this->state->tickAlpha = $debugStaticFrame ? 0.0 : max(0.0, min(1.0, 0.5));
                            $this->state->render($this->sdl, self::WINDOW_W, self::WINDOW_H);
                            $this->sdl->present();
                        };
                    }
                    fwrite(STDOUT, $this->state->debugOneStepAllDirs($onFrame));
                    if ($this->state->tracePlayerDraw) {
                        fwrite(STDOUT, $this->state->debugMultiStepWalkTrace(5, $onFrame));
                    }

                    return;
                }
                $townPieceAudit = (bool) ($this->opts['townPieceAudit'] ?? false);
                if ($townPieceAudit) {
                    $audit = $this->state->auditTownPieceAlignment(
                        dirname(__DIR__) . '/tmp/town-piece-alignment.jsonl'
                    );
                    foreach ($audit['lines'] as $line) {
                        fwrite(STDOUT, $line . "\n");
                    }

                    return;
                }
                $l1PieceAudit = (bool) ($this->opts['l1PieceAudit'] ?? false);
                if ($l1PieceAudit) {
                    if ($level < 1) {
                        $level = 1;
                    }
                    $this->state->enterCathedral($level);
                    $audit = $this->state->auditL1PieceAlignment(
                        dirname(__DIR__) . '/tmp/l1-piece-audit.jsonl'
                    );
                    foreach ($audit['lines'] as $line) {
                        fwrite(STDOUT, $line . "\n");
                    }

                    return;
                }
                if ((bool) ($this->opts['combatWorldStability'] ?? false)) {
                    if ($level < 1) {
                        $level = 1;
                    }
                    $this->state->enterCathedral($level);
                    $stab = $this->state->runCombatWorldStabilityCheck($this->sdl, self::WINDOW_W, self::WINDOW_H);
                    foreach ($stab['lines'] as $line) {
                        fwrite(STDOUT, $line . "\n");
                    }
                    fwrite(STDOUT, ($stab['ok'] ? 'PASS' : 'FAIL') . " COMBAT_WORLD_STABILITY\n");

                    return;
                }
                $combatCap = (string) ($this->opts['debugCombatCapture'] ?? '');
                if ($combatCap !== '') {
                    if (!str_starts_with($combatCap, '/')) {
                        $combatCap = dirname(__DIR__) . '/' . $combatCap;
                    }
                    $this->state->prepareL1FidelityLiveCapture();
                    $this->state->beginDebugCombatCapture($combatCap);
                }
                if ($dumpPalette) {
                    fwrite(STDOUT, $this->state->dumpPaletteEntries());

                    return;
                }
                if ($debugPiece >= 0) {
                    fwrite(STDOUT, $this->state->dumpPieceDiagnostics($debugPiece));
                    $this->runDebugPiece($debugPiece);

                    return;
                }
                if ($debugAlpha) {
                    $this->runDebugAlpha();

                    return;
                }
                if ($debugTileCoverage) {
                    $report = $this->state->dumpTileCoverageReport(12, 'START_TOWN');
                    fwrite(STDOUT, $report);
                    $this->state->writeTileCoverageReport(
                        dirname(__DIR__) . '/tmp/coverage-START_TOWN.txt',
                        12,
                        'START_TOWN'
                    );
                }
                $dumpCell = (string) ($this->opts['dumpCell'] ?? '');
                if ($dumpCell !== '' && preg_match('/^(-?\d+)\s*,\s*(-?\d+)$/', $dumpCell, $dcm)) {
                    $this->state->dumpCell((int) $dcm[1], (int) $dcm[2]);
                    if ($debugStaticFrame && $dumpFrame === '' && !Scrollrt::$debugRenderPasses && Scrollrt::$traceTile === null) {
                        // dump-cell alone with static frame: exit after dump (no need to idle)
                        return;
                    }
                }
                if ($debug) {
                    fwrite(STDOUT, sprintf(
                        "Boot: mode=play pos=%d,%d levelType=%d currLevel=%d fullbright=%d\n",
                        $this->state->playerX(),
                        $this->state->playerY(),
                        $this->state->levelType(),
                        $this->state->currLevel(),
                        $this->state->fullbright ? 1 : 0
                    ));
                }
                $frames = 0;
            } else {
                if ($seed > 0) {
                    $this->state->setDungeonSeed($seed);
                }
                $frames = 0;
            }
            $previous = hrtime(true);
            $acc = 0.0;
            $tick = 1.0 / 20.0;
            $maxFrames = $smoke ? 15 : ($playtest ? 5 : 0);
            $staticPresented = false;
            $frameDumped = false;
            $walkCaptureFrame = 0;
            $walkCaptureDir = '';
            $walkCaptureJsonl = null;
            $debugWalkCapture = (bool) ($this->opts['debugWalkCapture'] ?? false);
            if ($debugWalkCapture) {
                $walkCaptureDir = dirname(__DIR__) . '/tmp/walk-capture';
                if (!is_dir($walkCaptureDir)) {
                    mkdir($walkCaptureDir, 0775, true);
                }
                $walkCaptureJsonl = fopen($walkCaptureDir . '/state.jsonl', 'wb');
            }
            $profileFrames = 0;
            $profileWindowStart = hrtime(true);
            $sumRenderMs = 0.0;
            $maxRenderMs = 0.0;
            $lateTicks = 0;
            $sumDecodes = 0;
            $sumUploads = 0;
            $sumDraws = 0;
            $frameSamples = [];
            while ($this->running) {
                $ev = $this->sdl->pollEvents();
                // Esc on title dismisses to main menu (UiTitleDialog), does not quit —
                // except frozen --debug-title-frame (Esc quits like other debug viewers).
                $escQuits = ($ev['escape'] ?? false)
                    && (!$this->state->isTitleScreen() || $this->state->debugTitleFrame >= 0);
                if ($ev['quit'] || $escQuits) {
                    $this->running = false;
                }
                if ($this->state->wantsQuit()) {
                    $this->running = false;
                }
                $simMs = 0.0;
                if (!$debugStaticFrame) {
                    $this->state->handleInput($ev);
                    $now = hrtime(true);
                    $dt = min(0.25, ($now - $previous) / 1e9);
                    $previous = $now;
                    $acc += $dt;
                    $simStart = hrtime(true);
                    $ticksThisFrame = 0;
                    while ($acc >= $tick) {
                        $this->state->tick();
                        $acc -= $tick;
                        $ticksThisFrame++;
                    }
                    if ($ticksThisFrame > 1) {
                        $lateTicks += $ticksThisFrame - 1;
                    }
                    $simMs = (hrtime(true) - $simStart) / 1e6;
                    // Presentation progression between sim ticks
                    $this->state->tickAlpha = max(0.0, min(1.0, $acc / $tick));
                } else {
                    $this->state->tickAlpha = 0.0;
                    $previous = hrtime(true);
                }

                $r0 = hrtime(true);
                $this->state->render($this->sdl, self::WINDOW_W, self::WINDOW_H);
                $this->state->emitGameplayTraceFrame();
                $renderMs = (hrtime(true) - $r0) / 1e6;
                if ($dumpFrame !== '' && !$frameDumped) {
                    $frameDumped = true;
                    $outPath = $dumpFrame;
                    if (!str_starts_with($outPath, '/')) {
                        $outPath = dirname(__DIR__) . '/' . $outPath;
                    }
                    // Read before present — backbuffer is undefined after swap on many drivers
                    $this->sdl->dumpFramePNG($outPath, self::WINDOW_W, self::WINDOW_H);
                    fwrite(STDOUT, "dump-frame wrote {$outPath}\n");
                    if ($this->state->renderTrace || $this->state->softwareCompose || $this->state->debugPixel !== null) {
                        $this->state->finalizeRenderComposition(dirname(__DIR__));
                    }
                    $ps = (string) ($this->opts['parityScenario'] ?? '');
                    if ($ps !== '') {
                        $jsonPath = preg_replace('/\.png$/i', '.json', $outPath) ?: ($outPath . '.json');
                        $this->state->dumpParityState($jsonPath, $ps);
                        fwrite(STDOUT, "parity-state → {$jsonPath}\n");
                    }
                }
                if ($debugWalkCapture && $walkCaptureDir !== '') {
                    $png = sprintf('%s/frame-%03d.png', $walkCaptureDir, $walkCaptureFrame);
                    $this->sdl->dumpFramePNG($png, self::WINDOW_W, self::WINDOW_H);
                    $st = $this->state->walkCaptureState($walkCaptureFrame);
                    if (is_resource($walkCaptureJsonl)) {
                        fwrite($walkCaptureJsonl, json_encode($st, JSON_UNESCAPED_SLASHES) . "\n");
                    }
                    $walkCaptureFrame++;
                    if ($this->state->walkCaptureFinished() && $walkCaptureFrame > 2) {
                        [$fx, $fy] = $this->state->playerTile();
                        $finalPng = $walkCaptureDir . '/STATIC_AFTER_MOVE.png';
                        $this->sdl->dumpFramePNG($finalPng, self::WINDOW_W, self::WINDOW_H);
                        file_put_contents(
                            $walkCaptureDir . '/final_pos.txt',
                            "{$fx},{$fy}\n"
                        );
                        fwrite(STDOUT, "debug-walk-capture done frames={$walkCaptureFrame} pos={$fx},{$fy}\n");
                        if (is_resource($walkCaptureJsonl)) {
                            fclose($walkCaptureJsonl);
                            $walkCaptureJsonl = null;
                        }
                        $this->running = false;
                    }
                }
                if ($this->state->debugCombatCaptureDir !== '') {
                    if ($this->state->captureCombatFrame($this->sdl, self::WINDOW_W, self::WINDOW_H)) {
                        fwrite(STDOUT, "debug-combat-capture done\n");
                        $this->running = false;
                    }
                }
                $p0 = hrtime(true);
                $this->sdl->present();
                $presentMs = (hrtime(true) - $p0) / 1e6;
                if ($dumpFrame !== '' && $frameDumped && $this->running) {
                    // Exit after dump so shot scripts complete (static-frame alone stays idle).
                    $this->running = false;
                }
                if ($debugStaticFrame && !$staticPresented) {
                    $staticPresented = true;
                    fwrite(STDOUT, "debug-static-frame presented (Esc to quit)\n");
                    if ($this->state->freezeFirstFrame) {
                        $this->state->dumpFirstFrameState();
                        fwrite(STDOUT, "freeze-first-frame: ViewPosition frozen; no walk/AI (Esc to quit)\n");
                    }
                    if ($debugTileCoverage) {
                        fwrite(STDOUT, $this->state->dumpTileCoverageReport(10));
                    }
                }

                if ($profileFrame) {
                    $profileFrames++;
                    $sumRenderMs += $renderMs;
                    $maxRenderMs = max($maxRenderMs, $renderMs);
                    $frameSamples[] = $renderMs;
                    $sumDecodes += $this->state->profileDecodes;
                    $sumUploads += $this->state->profileUploads;
                    $sumDraws += $this->state->profileDraws;
                    $elapsed = (hrtime(true) - $profileWindowStart) / 1e9;
                    if ($elapsed >= 1.0) {
                        $fps = $profileFrames / max(1e-6, $elapsed);
                        sort($frameSamples);
                        $n = count($frameSamples);
                        $p95 = $n > 0 ? $frameSamples[(int) min($n - 1, (int) floor($n * 0.95))] : 0.0;
                        $p99 = $n > 0 ? $frameSamples[(int) min($n - 1, (int) floor($n * 0.99))] : 0.0;
                        fwrite(STDOUT, sprintf(
                            "profile fps=%.1f frames=%d sim_ms=%.2f render_avg=%.2f render_max=%.2f p95=%.2f p99=%.2f present_ms=%.2f late_ticks=%d decodes=%d uploads=%d draws/frame=%.1f tick_hz=20\n",
                            $fps,
                            $profileFrames,
                            $simMs,
                            $sumRenderMs / max(1, $profileFrames),
                            $maxRenderMs,
                            $p95,
                            $p99,
                            $presentMs,
                            $lateTicks,
                            $sumDecodes,
                            $sumUploads,
                            $sumDraws / max(1, $profileFrames)
                        ));
                        $profileFrames = 0;
                        $profileWindowStart = hrtime(true);
                        $sumRenderMs = 0.0;
                        $maxRenderMs = 0.0;
                        $lateTicks = 0;
                        $sumDecodes = 0;
                        $sumUploads = 0;
                        $sumDraws = 0;
                        $frameSamples = [];
                    }
                }

                if ($maxFrames > 0) {
                    $frames++;
                    if ($frames >= $maxFrames) {
                        $this->running = false;
                    }
                }

                // No PRESENTVSYNC — delay(1) only (measured: CreateRenderer flags=0)
                $this->sdl->delay(1);
            }
            if ($smoke) {
                fwrite(STDOUT, "Smoke run OK\n");
            }
        } finally {
            $this->sdl->shutdown();
        }
    }

    /**
     * --debug-cursor-assets: grid every objcurs frame on magenta with id/size/hotspot.
     * Left/Right page; Esc quit. Does not enter the full game.
     */
    private function runDebugCursorAssets(): void
    {
        if (!$this->assets->has(Cursor::ASSET_PATH)) {
            throw new \RuntimeException('Missing ' . Cursor::ASSET_PATH);
        }
        $pal = $this->assets->has('levels\\towndata\\town.pal')
            ? $this->assets->loadPalette('levels\\towndata\\town.pal')
            : $this->assets->loadPalette('levels\\l1data\\l1.pal');
        $cel = $this->assets->loadCel(Cursor::ASSET_PATH);
        $n = min($cel->numFrames, Cursor::numKnownWidths());
        fwrite(STDOUT, sprintf(
            "debug-cursor-assets: %s frames=%d (showing %d) widths=%d\n",
            Cursor::ASSET_PATH,
            $cel->numFrames,
            $n,
            Cursor::numKnownWidths()
        ));

        /** @var list<array{pcurs:int,frame:int,w:int,h:int,rgba:string,hotX:int,hotY:int,name:string,tex:?object}> */
        $sprites = [];
        for ($frame = 0; $frame < $n; $frame++) {
            $pcurs = $frame + 1;
            $width = Cursor::frameWidth($frame);
            $fr = $cel->decodeFrame($frame, $width, $pal);
            $holding = $pcurs >= Cursor::CURSOR_FIRSTITEM;
            $hot = Cursor::hotspotOffset($pcurs, (int) $fr['w'], (int) $fr['h'], $holding);
            $sprites[] = [
                'pcurs' => $pcurs,
                'frame' => $frame,
                'w' => (int) $fr['w'],
                'h' => (int) $fr['h'],
                'rgba' => (string) $fr['rgba'],
                'hotX' => $hot['x'],
                'hotY' => $hot['y'],
                'name' => Cursor::nameFor($pcurs),
                'tex' => null,
            ];
            fwrite(STDOUT, sprintf(
                "  pcurs=%3d %-12s f=%3d %2dx%-2d hot=%d,%d\n",
                $pcurs,
                Cursor::nameFor($pcurs),
                $frame,
                (int) $fr['w'],
                (int) $fr['h'],
                $hot['x'],
                $hot['y']
            ));
        }

        $cellW = 72;
        $cellH = 80;
        $cols = intdiv(self::WINDOW_W, $cellW);
        $rows = intdiv(self::WINDOW_H - 24, $cellH);
        $perPage = max(1, $cols * $rows);
        $page = 0;
        $pages = (int) max(1, (int) ceil(count($sprites) / $perPage));

        $redraw = function () use (&$sprites, &$page, $pages, $cols, $rows, $cellW, $cellH, $perPage): void {
            $this->sdl->setDrawColor(255, 0, 255);
            $this->sdl->clear();
            $start = $page * $perPage;
            $end = min(count($sprites), $start + $perPage);
            for ($i = $start; $i < $end; $i++) {
                $s = &$sprites[$i];
                $local = $i - $start;
                $col = $local % $cols;
                $row = intdiv($local, $cols);
                $ox = $col * $cellW + 4;
                $oy = 20 + $row * $cellH + 4;
                if ($s['tex'] === null) {
                    $s['tex'] = $this->sdl->createTextureRGBA($s['w'], $s['h'], $s['rgba']);
                }
                $this->sdl->copyTexture($s['tex'], $ox, $oy, $s['w'], $s['h']);
                // Hotspot crosshair (cyan)
                $hx = $ox + $s['hotX'];
                $hy = $oy + $s['hotY'];
                $this->sdl->setDrawColor(0, 255, 255);
                $this->sdl->fillRect($hx - 2, $hy, 5, 1);
                $this->sdl->fillRect($hx, $hy - 2, 1, 5);
                // Tiny id bar: yellow for tools, white for items
                if ($s['pcurs'] < Cursor::CURSOR_FIRSTITEM) {
                    $this->sdl->setDrawColor(255, 220, 0);
                } else {
                    $this->sdl->setDrawColor(255, 255, 255);
                }
                $bar = min($s['pcurs'], $cellW - 8);
                $this->sdl->fillRect($ox, $oy + $cellH - 14, max(2, $bar), 2);
                // Frame index ticks (one pixel per 10)
                $this->sdl->setDrawColor(0, 0, 0);
                $this->sdl->fillRect($ox, $oy + $cellH - 10, 1 + intdiv($s['frame'], 10), 2);
            }
            // Page indicator strip
            $this->sdl->setDrawColor(0, 0, 0);
            $this->sdl->fillRect(0, 0, self::WINDOW_W, 18);
            $this->sdl->setDrawColor(255, 255, 255);
            $this->sdl->fillRect(8, 6, 4 + $page * 8, 6);
            $this->sdl->setDrawColor(80, 80, 80);
            $this->sdl->fillRect(8, 6, 4 + ($pages - 1) * 8, 2);
            $this->sdl->present();
            fwrite(STDOUT, sprintf(
                "debug-cursor-assets page %d/%d (Left/Right page, Esc quit) cells %d..%d\n",
                $page + 1,
                $pages,
                $start,
                $end - 1
            ));
        };

        $redraw();
        while ($this->running) {
            $ev = $this->sdl->pollEvents();
            if ($ev['quit'] || ($ev['escape'] ?? false)) {
                break;
            }
            foreach ($ev['keys'] as $k) {
                if ($k === 'left' || $k === 'a') {
                    $page = ($page - 1 + $pages) % $pages;
                    $redraw();
                } elseif ($k === 'right' || $k === 'd' || $k === 'space') {
                    $page = ($page + 1) % $pages;
                    $redraw();
                }
            }
            $this->sdl->delay(16);
        }
        foreach ($sprites as $s) {
            if ($s['tex'] !== null) {
                $this->sdl->destroyTexture($s['tex']);
            }
        }
    }

    /**
     * --debug-player-attack-frame=N --direction=E and/or --debug-player-attack-assets
     */
    private function runDebugPlayerAttack(?int $frame, string $direction, bool $dumpAssets): void
    {
        $dirMap = [
            'S' => Direction::S,
            'SW' => Direction::SW,
            'W' => Direction::W,
            'NW' => Direction::NW,
            'N' => Direction::N,
            'NE' => Direction::NE,
            'E' => Direction::E,
            'SE' => Direction::SE,
        ];
        $dir = $dirMap[$direction] ?? Direction::E;
        $path = 'plrgfx\\warrior\\wld\\wldat.cl2';
        if (!$this->assets->has($path)) {
            throw new \RuntimeException("Missing {$path}");
        }
        $pal = $this->assets->has('levels\\l1data\\l1.pal')
            ? $this->assets->loadPalette('levels\\l1data\\l1.pal')
            : $this->assets->loadPalette('levels\\towndata\\town.pal');
        $cl2 = Cl2::parse($this->assets->read($path));
        fwrite(STDOUT, sprintf("debug-player-attack: %s groups=%d dir=%s(%d)\n", $path, $cl2->numGroups, $direction, $dir));

        if ($dumpAssets) {
            $names = ['S', 'SW', 'W', 'NW', 'N', 'NE', 'E', 'SE'];
            for ($d = 0; $d < min(8, $cl2->numGroups); $d++) {
                $cl2->selectGroup($d);
                $n = $cl2->numFrames;
                $parts = [];
                foreach ([0, 8, max(0, $n - 1)] as $fi) {
                    if ($fi >= $n) {
                        continue;
                    }
                    $fr = $cl2->decodeFrame($fi, 128, $pal, $d);
                    $parts[] = sprintf('f%d=%dx%d#%08x', $fi, $fr['w'], $fr['h'], crc32($fr['rgba']));
                }
                fwrite(STDOUT, sprintf("  dir=%d(%s) group=%d frames=%d %s\n", $d, $names[$d], $d, $n, implode(' ', $parts)));
            }
            if ($frame === null) {
                return;
            }
        }

        $frame = max(0, $frame ?? 0);
        $cl2->selectGroup($dir);
        if ($frame >= $cl2->numFrames) {
            throw new \RuntimeException("frame {$frame} OOB (group frames={$cl2->numFrames})");
        }
        $fr = $cl2->decodeFrame($frame, 128, $pal, $dir);
        $w = (int) $fr['w'];
        $h = (int) $fr['h'];
        // Magenta background
        $this->sdl->setDrawColor(255, 0, 255);
        $this->sdl->clear();
        $tex = $this->sdl->createTextureRGBA($w, $h, (string) $fr['rgba']);
        $dx = intdiv(self::WINDOW_W - $w, 2);
        $dy = intdiv(self::WINDOW_H - $h, 2);
        $this->sdl->copyTexture($tex, $dx, $dy, $w, $h);
        // Feet/baseline marker (bottom center of sprite)
        $this->sdl->setDrawColor(0, 255, 255);
        $footX = $dx + intdiv($w, 2);
        $footY = $dy + $h - 1;
        $this->sdl->fillRect($footX - 3, $footY, 7, 1);
        $this->sdl->fillRect($footX, $footY - 3, 1, 7);
        $this->sdl->present();
        fwrite(STDOUT, sprintf(
            "debug-player-attack-frame=%d dir=%s group=%d %dx%d hotspot_center_x=%d (Esc quit)\n",
            $frame,
            $direction,
            $dir,
            $w,
            $h,
            IsoCoords::spriteTileCenterX($w)
        ));
        while ($this->running) {
            $ev = $this->sdl->pollEvents();
            if ($ev['quit'] || ($ev['escape'] ?? false)) {
                break;
            }
            $this->sdl->delay(16);
        }
        $this->sdl->destroyTexture($tex);
    }

    private function runDebugTitleAssets(): void
    {
        fwrite(STDOUT, "TITLE_ASSET_DISCOVERY:\n");
        $rows = TitleDialog::probeAssets($this->assets);
        $ok = true;
        foreach ($rows as $row) {
            $found = $row['found'] ? 'FOUND' : 'MISSING';
            if (!$row['found'] && ($row['path'] === 'ui_art\\title.pcx' || $row['path'] === 'ui_art\\logo.pcx')) {
                $ok = false;
            }
            $line = sprintf(
                "  %s  %s  format=%s  size=%d",
                $row['path'],
                $found,
                (string) $row['format'],
                (int) $row['size']
            );
            if ($row['frame_count'] !== null) {
                $line .= '  frames=' . $row['frame_count'];
            }
            if ($row['dimensions'] !== null) {
                $line .= '  dims=' . $row['dimensions'];
            }
            fwrite(STDOUT, $line . "\n");
        }
        fwrite(STDOUT, $ok ? "TITLE_ASSET_DISCOVERY: PASS\n" : "TITLE_ASSET_DISCOVERY: BROKEN\n");
        if (!$ok) {
            throw new \RuntimeException('title assets missing from MPQ');
        }
    }

    private function runDebugFont(): void
    {
        $this->state->bootstrap();
        if (!$this->state->hasUiFont()) {
            throw new \RuntimeException('debug-font: UiFont failed to load (see UiFont FAIL on stderr)');
        }
        $gold = \Diablo\Engine\Assets\UiFont::load42($this->assets, true);
        $yellow = \Diablo\Engine\Assets\UiFont::load42($this->assets, false);
        $pipe = $gold->debugGlyphPipeline('A');
        fwrite(STDOUT, "debug-font-pipeline glyph=A chroma={$pipe['chroma']} opaque={$pipe['after_chroma_opaque']} green_opaque={$pipe['green_opaque']}\n");
        fwrite(STDOUT, '  hist top: ');
        arsort($pipe['hist']);
        $i = 0;
        foreach ($pipe['hist'] as $idx => $cnt) {
            fwrite(STDOUT, "{$idx}={$cnt} ");
            if (++$i >= 8) {
                break;
            }
        }
        fwrite(STDOUT, "\n");
        if ($pipe['green_opaque'] > 0) {
            fwrite(STDERR, "FONT PIPELINE FAIL: green_opaque={$pipe['green_opaque']} (chroma key wrong)\n");
        }
        $lines = [
            ['ABC DEF GHI JKL MNO PQR STU VWX YZ', true],
            ['abc def ghi jkl mno pqr stu vwx yz', true],
            ['Single Player', true],
            ['Continue', false],
            ['Exit Diablo', true],
            ['Warrior Rogue Sorcerer', false],
        ];
        $this->sdl->setDrawColor(20, 20, 32);
        $this->sdl->clear();
        $y = 40;
        foreach ($lines as [$text, $useGold]) {
            $font = $useGold ? $gold : $yellow;
            $img = $font->render($text, \Diablo\DiabloUI\UiList::SPACING);
            $tex = $this->sdl->createTextureRGBA($img['w'], $img['h'], $img['rgba']);
            $this->sdl->copyTexture($tex, 32, $y, $img['w'], $img['h']);
            $opaque = 0;
            $green = 0;
            for ($p = 0; $p < strlen($img['rgba']); $p += 4) {
                if (ord($img['rgba'][$p + 3]) === 0) {
                    continue;
                }
                $opaque++;
                if (ord($img['rgba'][$p]) === 0 && ord($img['rgba'][$p + 1]) === 255 && ord($img['rgba'][$p + 2]) === 0) {
                    $green++;
                }
            }
            fwrite(STDOUT, sprintf("debug-font '%s' %dx%d gold=%d opaque=%d green=%d\n", $text, $img['w'], $img['h'], $useGold ? 1 : 0, $opaque, $green));
            $y += $img['h'] + 8;
        }
        $this->sdl->present();
        fwrite(STDOUT, "debug-font presented (Esc to quit)\n");
        while ($this->running) {
            $ev = $this->sdl->pollEvents();
            if ($ev['quit'] || ($ev['escape'] ?? false)) {
                $this->running = false;
            }
            $this->sdl->delay(16);
        }
    }

    private function runDebugAsset(string $path, int $frame): void
    {
        if (!$this->assets->has($path)) {
            throw new \RuntimeException("Asset not found: {$path}");
        }
        $bytes = $this->assets->read($path);
        $lower = strtolower($path);
        $rgba = '';
        $w = 0;
        $h = 0;
        $palDump = '';
        if (str_ends_with($lower, '.pcx')) {
            $dec = Pcx::decode($bytes);
            $rgba = $dec['rgba'];
            $w = $dec['w'];
            $h = $dec['h'];
            $palDump = $this->dumpPaletteSample($dec['palette']);
        } elseif (str_ends_with($lower, '.cl2')) {
            $pal = $this->assets->has('levels\\l1data\\l1.pal')
                ? $this->assets->loadPalette('levels\\l1data\\l1.pal')
                : $this->assets->loadPalette('levels\\towndata\\town.pal');
            $cl2 = Cl2::parse($bytes);
            $width = 96;
            if (str_contains($lower, 'monster')) {
                $width = 128;
            }
            $group = 0;
            $cl2->selectGroup($group);
            $dec = $cl2->decodeFrame($frame, $width, $pal, $group);
            $rgba = $dec['rgba'];
            $w = $dec['w'];
            $h = $dec['h'];
            $palDump = $this->dumpPaletteSample($pal);
            fwrite(STDOUT, "CL2 groups={$cl2->numGroups} frames/group={$cl2->numFrames} decoded group={$group} frame={$frame} {$w}x{$h}\n");
        } else {
            $pal = $this->assets->has('levels\\l1data\\l1.pal')
                ? $this->assets->loadPalette('levels\\l1data\\l1.pal')
                : $this->assets->loadPalette('levels\\towndata\\town.pal');
            $cel = Cel::parse($bytes);
            $width = str_contains($lower, 'panel') ? 640 : 32;
            $dec = $cel->decodeFrame($frame, $width, $pal);
            $rgba = $dec['rgba'];
            $w = $dec['w'];
            $h = $dec['h'];
            $palDump = $this->dumpPaletteSample($pal);
            fwrite(STDOUT, "CEL frames={$cel->numFrames} decoded frame={$frame} {$w}x{$h}\n");
        }
        fwrite(STDOUT, $palDump);
        $this->sdl->setDrawColor(0, 0, 0);
        $this->sdl->clear();
        $tex = $this->sdl->createTextureRGBA($w, $h, $rgba);
        $dx = intdiv(self::WINDOW_W - $w, 2);
        $dy = intdiv(self::WINDOW_H - $h, 2);
        $this->sdl->copyTexture($tex, $dx, $dy, $w, $h);
        $this->sdl->present();
        fwrite(STDOUT, "debug-asset: {$path} shown at native {$w}x{$h} (Esc to quit)\n");
        while ($this->running) {
            $ev = $this->sdl->pollEvents();
            if ($ev['quit'] || ($ev['escape'] ?? false)) {
                break;
            }
            $this->sdl->delay(16);
        }
        $this->sdl->destroyTexture($tex);
    }

    private function dumpPaletteSample(Palette $pal): string
    {
        $lines = ["palette sample (idx → RGB):\n"];
        foreach ([0, 1, 15, 31, 128, 192, 255] as $i) {
            [$r, $g, $b] = $pal->rgb($i);
            $lines[] = sprintf("  %3d → %3d,%3d,%3d\n", $i, $r, $g, $b);
        }

        return implode('', $lines);
    }

    /** Magenta checker background + one micro/frame to verify alpha. */
    private function runDebugAlpha(): void
    {
        $piece = $this->state->playerPieceId();
        $dump = $this->state->decodeMicroForDebug($piece, 0, 'floor_triangle');
        if ($dump === null) {
            fwrite(STDERR, "debug-alpha: no micro for piece {$piece}\n");

            return;
        }
        $w = (int) $dump['w'];
        $h = (int) $dump['h'];
        $rgba = (string) $dump['rgba'];
        $checker = '';
        for ($y = 0; $y < self::WINDOW_H; $y++) {
            for ($x = 0; $x < self::WINDOW_W; $x++) {
                $c = ((($x >> 3) ^ ($y >> 3)) & 1) !== 0;
                $checker .= $c ? "\xFF\x00\xFF\xFF" : "\x40\x00\x40\xFF";
            }
        }
        $bg = $this->sdl->createTextureRGBA(self::WINDOW_W, self::WINDOW_H, $checker);
        $tex = $this->sdl->createTextureRGBA($w, $h, $rgba);
        $this->sdl->copyTexture($bg, 0, 0, self::WINDOW_W, self::WINDOW_H);
        $this->sdl->copyTexture($tex, intdiv(self::WINDOW_W - $w, 2), intdiv(self::WINDOW_H - $h, 2), $w, $h);
        $this->sdl->present();
        fwrite(STDOUT, "debug-alpha: piece={$piece} block0 floor_triangle {$w}x{$h} on magenta checker (Esc quit)\n");
        while ($this->running) {
            $ev = $this->sdl->pollEvents();
            if ($ev['quit'] || ($ev['escape'] ?? false)) {
                break;
            }
            $this->sdl->delay(16);
        }
        $this->sdl->destroyTexture($tex);
        $this->sdl->destroyTexture($bg);
    }

    /** Render one dungeon piece (all micros) alone on magenta; dump PNG checksums. */
    private function runDebugPiece(int $piece): void
    {
        // piece < 0 → use player tile; piece 0 is a valid MIN entry (DX zero-index).
        if ($piece < 0) {
            $piece = $this->state->playerPieceId();
        }
        $dumpDir = dirname(__DIR__) . '/tmp/parity/pieces';
        if (!is_dir($dumpDir)) {
            mkdir($dumpDir, 0775, true);
        }
        $layers = $this->state->composePieceRgba($piece);
        $meta = $this->state->dumpIsolatedPieceParity($piece, $dumpDir);
        fwrite(STDOUT, $this->state->dumpPieceDiagnostics($piece));
        fwrite(STDOUT, json_encode($meta, JSON_UNESCAPED_SLASHES) . "\n");

        $checker = '';
        for ($y = 0; $y < self::WINDOW_H; $y++) {
            for ($x = 0; $x < self::WINDOW_W; $x++) {
                $c = ((($x >> 3) ^ ($y >> 3)) & 1) !== 0;
                $checker .= $c ? "\xFF\x00\xFF\xFF" : "\x20\x00\x40\xFF";
            }
        }
        $bg = $this->sdl->createTextureRGBA(self::WINDOW_W, self::WINDOW_H, $checker);
        $this->sdl->copyTexture($bg, 0, 0, self::WINDOW_W, self::WINDOW_H);
        $cx = intdiv(self::WINDOW_W, 2) - 32;
        $cy = intdiv(self::WINDOW_H, 2) + 64;
        foreach ($layers as $layer) {
            $tex = $this->sdl->createTextureRGBA($layer['w'], $layer['h'], $layer['rgba']);
            // Assert 1:1 blit — dst size equals decoded size (no scale/rotate).
            $this->sdl->copyTexture($tex, $cx + $layer['ox'], $cy + $layer['oy'] - $layer['h'], $layer['w'], $layer['h']);
            fwrite(STDOUT, sprintf(
                "SDL blit micro ox=%d oy=%d tex=%dx%d dst=%dx%d scale=1 rotate=0\n",
                $layer['ox'],
                $layer['oy'],
                $layer['w'],
                $layer['h'],
                $layer['w'],
                $layer['h']
            ));
            $this->sdl->destroyTexture($tex);
        }
        $this->sdl->present();
        $stackPath = sprintf('%s/piece-%d-stack.png', $dumpDir, $piece);
        $this->sdl->dumpFramePNG($stackPath, self::WINDOW_W, self::WINDOW_H);
        fwrite(STDOUT, "debug-piece: {$piece} layered on magenta; dumps → {$dumpDir} (Esc quit)\n");
        while ($this->running) {
            $ev = $this->sdl->pollEvents();
            if ($ev['quit'] || ($ev['escape'] ?? false)) {
                break;
            }
            $this->sdl->delay(16);
        }
        $this->sdl->destroyTexture($bg);
    }

    public function mpq(): MpqArchive
    {
        return $this->mpq;
    }

    public function assets(): AssetStore
    {
        return $this->assets;
    }
}
