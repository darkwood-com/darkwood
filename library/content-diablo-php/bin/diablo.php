#!/usr/bin/env php
<?php

declare(strict_types=1);

use Diablo\Diablo;
use Diablo\Engine\Assets\MpqArchive;
use Diablo\World\WorldCommands;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

$worldExit = WorldCommands::tryDispatch($argv);
if ($worldExit >= 0) {
    exit($worldExit);
}

$data = getenv('DIABLO_DATA') ?: '';
$inspect = false;
$smoke = false;
$town = false;
$playtest = false;
$debug = false;
$debugGeneration = false;
$mazeFallback = false;
$debugAsset = '';
$debugFrame = 0;
$renderLayer = 'all';
$debugStaticFrame = false;
$dumpFrame = '';
$debugGenAscii = false;
$debugPlaceholders = false;
$fullbright = false;
$cameraFixed = false;
$debugAlpha = false;
$dumpPalette = false;
$debugPiece = -1;
$debugWalk = false;
$debugFont = false;
$debugTitleAssets = false;
$debugTitleFrame = -1;
$debugTitleAnimation = false;
$debugTitleCapture = false;
$debugDrawLog = false;
$debugTileCoverage = false;
$debugTileCoverageOverlay = false;
$debugWorldPick = false;
$forceFullRedraw = false;
$debugClearMagenta = false;
$debugDisableMasks = false;
$disableWorldClipping = false;
$debugRenderMargin = 0;
$debugWalkCapture = false;
$forcePos = '';
$coverageSuite = false;
$movementSuite = false;
$debugWalkStep = false;
$debugCamera = false;
$debugClickOverlay = false;
$profileFrame = false;
$debugCursor = false;
$debugCursorTarget = false;
$debugCursorAssets = false;
$debugPlayerAttackFrame = null;
$debugPlayerAttackAssets = false;
$direction = 'E';
$debugPlayerState = false;
$debugOneStep = false;
$scenario = '';
$gameplayTrace = false;
$debugGameplayOverlay = false;
$dumpCell = '';
$traceTile = '';
$debugRenderPasses = false;
$freezeFirstFrame = false;
$tracePlayerDraw = false;
$traceActorDraw = false;
$townPieceAudit = false;
$l1PieceAudit = false;
$debugRenderState = false;
$debugCombatCapture = '';
$combatWorldStability = false;
$hideActors = false;
$hideObjects = false;
$hideItems = false;
$hideHud = false;
$parityScenario = '';
$renderTrace = false;
$softwareCompose = false;
$debugPixel = '';
$level = 0;
$seed = 0;
$showHelp = false;

foreach ($argv as $i => $arg) {
    if ($i === 0) {
        continue;
    }
    if (str_starts_with($arg, '--data=')) {
        $data = substr($arg, 7);
    } elseif (str_starts_with($arg, '--level=')) {
        $level = (int) substr($arg, 8);
    } elseif (str_starts_with($arg, '--seed=')) {
        $seed = (int) substr($arg, 7);
    } elseif ($arg === '--inspect') {
        $inspect = true;
    } elseif ($arg === '--smoke') {
        $smoke = true;
    } elseif ($arg === '--town') {
        $town = true;
    } elseif ($arg === '--playtest') {
        $playtest = true;
    } elseif ($arg === '--debug') {
        $debug = true;
    } elseif ($arg === '--debug-generation') {
        $debugGeneration = true;
    } elseif ($arg === '--maze-fallback') {
        $mazeFallback = true;
    } elseif (str_starts_with($arg, '--debug-asset=')) {
        $debugAsset = str_replace('/', '\\', substr($arg, 14));
    } elseif (str_starts_with($arg, '--frame=')) {
        $debugFrame = (int) substr($arg, 8);
    } elseif (str_starts_with($arg, '--render-layer=')) {
        $renderLayer = substr($arg, 15);
    } elseif ($arg === '--debug-static-frame') {
        $debugStaticFrame = true;
    } elseif (str_starts_with($arg, '--dump-frame=')) {
        $dumpFrame = substr($arg, 13);
    } elseif ($arg === '--debug-gen-ascii') {
        $debugGenAscii = true;
    } elseif ($arg === '--debug-placeholders') {
        $debugPlaceholders = true;
    } elseif ($arg === '--fullbright') {
        $fullbright = true;
    } elseif ($arg === '--camera-fixed') {
        $cameraFixed = true;
    } elseif ($arg === '--debug-alpha') {
        $debugAlpha = true;
    } elseif ($arg === '--dump-palette') {
        $dumpPalette = true;
    } elseif (str_starts_with($arg, '--debug-piece=')) {
        $debugPiece = (int) substr($arg, 14);
    } elseif ($arg === '--debug-walk') {
        $debugWalk = true;
    } elseif ($arg === '--debug-font' || $arg === '--debug-font-pipeline') {
        $debugFont = true;
    } elseif ($arg === '--debug-draw-log') {
        $debugDrawLog = true;
    } elseif ($arg === '--debug-tile-coverage') {
        $debugTileCoverage = true;
    } elseif ($arg === '--debug-tile-coverage-overlay') {
        $debugTileCoverageOverlay = true;
    } elseif ($arg === '--debug-world-pick') {
        $debugWorldPick = true;
        $debug = true;
    } elseif ($arg === '--force-full-redraw') {
        $forceFullRedraw = true;
    } elseif ($arg === '--debug-clear-magenta') {
        $debugClearMagenta = true;
    } elseif ($arg === '--debug-disable-masks') {
        $debugDisableMasks = true;
    } elseif ($arg === '--disable-world-clipping') {
        $disableWorldClipping = true;
    } elseif (str_starts_with($arg, '--debug-render-margin=')) {
        $debugRenderMargin = max(0, (int) substr($arg, strlen('--debug-render-margin=')));
    } elseif ($arg === '--debug-walk-capture') {
        $debugWalkCapture = true;
        $town = true;
        $fullbright = true;
    } elseif (str_starts_with($arg, '--force-pos=')) {
        $forcePos = substr($arg, strlen('--force-pos='));
    } elseif ($arg === '--coverage-suite') {
        $coverageSuite = true;
        $town = true;
        $debugStaticFrame = true;
    } elseif ($arg === '--movement-suite') {
        $movementSuite = true;
        $town = true;
        $debugStaticFrame = true;
    } elseif ($arg === '--debug-walk-step') {
        $debugWalkStep = true;
    } elseif ($arg === '--debug-camera') {
        $debugCamera = true;
    } elseif ($arg === '--debug-click-overlay') {
        $debugClickOverlay = true;
    } elseif ($arg === '--profile-frame') {
        $profileFrame = true;
    } elseif ($arg === '--debug-cursor') {
        $debugCursor = true;
    } elseif ($arg === '--debug-cursor-target') {
        $debugCursorTarget = true;
    } elseif ($arg === '--debug-cursor-assets') {
        $debugCursorAssets = true;
    } elseif (str_starts_with($arg, '--debug-player-attack-frame=')) {
        $debugPlayerAttackFrame = (int) substr($arg, strlen('--debug-player-attack-frame='));
    } elseif ($arg === '--debug-player-attack-assets') {
        $debugPlayerAttackAssets = true;
    } elseif (str_starts_with($arg, '--direction=')) {
        $direction = substr($arg, strlen('--direction='));
    } elseif ($arg === '--debug-player-state') {
        $debugPlayerState = true;
    } elseif ($arg === '--debug-one-step') {
        $debugOneStep = true;
    } elseif (str_starts_with($arg, '--scenario=')) {
        $scenario = substr($arg, 11);
    } elseif ($arg === '--scenario') {
        $scenario = 'ALL';
    } elseif ($arg === '--debug-gameplay-trace') {
        $gameplayTrace = true;
    } elseif (str_starts_with($arg, '--debug-gameplay-trace=')) {
        $gameplayTrace = substr($arg, strlen('--debug-gameplay-trace='));
    } elseif ($arg === '--debug-gameplay-overlay') {
        $debugGameplayOverlay = true;
    } elseif (str_starts_with($arg, '--dump-cell=')) {
        $dumpCell = substr($arg, strlen('--dump-cell='));
    } elseif (str_starts_with($arg, '--trace-tile=')) {
        $traceTile = substr($arg, strlen('--trace-tile='));
    } elseif ($arg === '--debug-render-passes') {
        $debugRenderPasses = true;
    } elseif ($arg === '--freeze-first-frame') {
        $freezeFirstFrame = true;
        $town = true;
        $debugStaticFrame = true;
        $fullbright = true;
    } elseif ($arg === '--debug-first-frame-magenta') {
        $debugClearMagenta = true;
        $freezeFirstFrame = true;
        $town = true;
        $debugStaticFrame = true;
        $fullbright = true;
    } elseif ($arg === '--trace-player-draw') {
        $tracePlayerDraw = true;
    } elseif ($arg === '--trace-actor-draw') {
        $traceActorDraw = true;
    } elseif ($arg === '--town-piece-audit') {
        $townPieceAudit = true;
        $town = true;
        $fullbright = true;
    } elseif ($arg === '--l1-piece-audit') {
        $l1PieceAudit = true;
        $level = max(1, $level);
        $fullbright = true;
        if ($seed === 0) {
            $seed = 12345;
        }
    } elseif ($arg === '--debug-render-state') {
        $debugRenderState = true;
    } elseif (str_starts_with($arg, '--debug-combat-capture=')) {
        $debugCombatCapture = substr($arg, strlen('--debug-combat-capture='));
        $level = max(1, $level);
        $fullbright = true;
        if ($seed === 0) {
            $seed = 12345;
        }
    } elseif ($arg === '--combat-world-stability') {
        $combatWorldStability = true;
        $level = max(1, $level);
        $fullbright = true;
        if ($seed === 0) {
            $seed = 12345;
        }
    } elseif ($arg === '--hide-actors') {
        $hideActors = true;
    } elseif ($arg === '--hide-objects') {
        $hideObjects = true;
    } elseif ($arg === '--hide-items') {
        $hideItems = true;
    } elseif ($arg === '--hide-hud') {
        $hideHud = true;
    } elseif ($arg === '--render-trace') {
        $renderTrace = true;
    } elseif ($arg === '--software-compose') {
        $softwareCompose = true;
        $renderTrace = true;
    } elseif (str_starts_with($arg, '--debug-pixel=')) {
        $debugPixel = substr($arg, strlen('--debug-pixel='));
        $renderTrace = true;
        $debugStaticFrame = true;
        $freezeFirstFrame = true;
        $fullbright = true;
    } elseif (str_starts_with($arg, '--parity-scenario=')) {
        $parityScenario = substr($arg, strlen('--parity-scenario='));
        $fullbright = true;
        $hideActors = true;
        $hideObjects = true;
        $hideItems = true;
        $hideHud = true;
        $debugStaticFrame = true;
        $freezeFirstFrame = true;
        $renderTrace = true;
        $id = strtoupper($parityScenario);
        if ($id === 'A' || $id === 'B' || $id === 'TOWN' || $id === 'TOWN-BUILDING') {
            $town = true;
            if ($id === 'B' || $id === 'TOWN-BUILDING') {
                // Near known town hut region from prior audits
                if ($forcePos === '') {
                    $forcePos = '60,68';
                }
            }
        } elseif ($id === 'C' || $id === 'L1' || $id === 'D' || $id === 'L1-STAIRS' || $id === 'E' || $id === 'L1-COMBAT') {
            $level = max(1, $level);
            if ($seed === 0) {
                $seed = 12345;
            }
            if ($id === 'E' || $id === 'L1-COMBAT') {
                $hideActors = false; // combat scene keeps actors
            }
            if ($id === 'D' || $id === 'L1-STAIRS') {
                if ($forcePos === '') {
                    $forcePos = '56,68'; // near stairs_up spawn
                }
            }
        }
    } elseif ($arg === '--debug-title-assets') {
        $debugTitleAssets = true;
    } elseif (str_starts_with($arg, '--debug-title-frame=')) {
        $debugTitleFrame = (int) substr($arg, strlen('--debug-title-frame='));
    } elseif ($arg === '--debug-title-animation') {
        $debugTitleAnimation = true;
    } elseif ($arg === '--debug-title-capture') {
        $debugTitleCapture = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        $showHelp = true;
    }
}

if ($showHelp) {
    fwrite(STDOUT, <<<TXT
Diablo PHP — source port runtime

Usage:
  php -d ffi.enable=true bin/diablo.php --data=/path/to/DIABDAT.MPQ
  php -d ffi.enable=true bin/diablo.php --data=/path/to/DIABDAT.MPQ --inspect
  DIABLO_DATA=/path/to/DIABDAT.MPQ php -d ffi.enable=true bin/diablo.php

Options:
  --data=PATH   Path to DIABDAT.MPQ (or diabdat.mpq)
  --inspect     Open MPQ, validate, read known assets, exit
  --smoke       Short automated validation run
  --playtest    Headless-ish playability sequence (town→cath→combat→loot→stairs→save)
  --town        Skip menu; start New Game in town
  --level=N     Skip menu; start in Cathedral level N (implies new game)
  --seed=N      Fixed dungeon seed
  --debug       Extra boot diagnostics
  --debug-generation  Dump L1 generation meta (implies --level=1 if unset) and exit after load
  --debug-gen-ascii   With --debug-generation, also print dungeon tile ASCII map
  --debug-asset=PATH  Decode one MPQ asset (cel/cl2/pcx) to window and exit
  --frame=N           Frame index for --debug-asset (default 0)
  --render-layer=L    floor|walls|micros|specials|actors|objects|ui|entities|all
  --debug-static-frame  Fixed seed/level one-shot present then idle until quit
  --dump-frame=PATH     After first present, SDL_RenderReadPixels → PNG (exits)
  --force-full-redraw   Explicit double-clear (baseline already clears each frame)
  --debug-clear-magenta Magenta-fill viewport before world draw (undrawn = magenta)
  --debug-walk-capture  Town E,E,SE walk; dump tmp/walk-capture/frame-NNN.png + JSONL
  --debug-render-margin=N  Extra tile rows/cols (diagnostic underscan)
  --debug-disable-masks Force MaskType::SOLID (stripe isolation)
  --disable-world-clipping  Confirm no SDL clip (no-op log)
  --force-pos=X,Y       After town/level load, teleport player to tile
  --debug-placeholders  Allow diamond/rect fallbacks (OFF in normal runtime)
  --fullbright          Skip LightTables (geometry validation)
  --camera-fixed        Freeze ViewPosition (isolate actor walk)
  --debug-world-pick    LMB dumps screen→dPiece diagnostics (tmp/world-pick.jsonl)
  --debug-tile-coverage Dump tile coverage taxonomy around camera
  --debug-tile-coverage-overlay  Tint tiles: OUTSIDE_WORLD vs EXPECTED_NOT_DRAWN
  --debug-alpha         Blit one micro/frame over magenta checker then idle
  --dump-palette        Print 256 palette RGB entries and exit after load
  --debug-piece=N       Dump piece N micros; with SDL idle also blit piece alone
  --debug-walk          Log walk sim + render rows (tick/alpha/offsets)
  --debug-font          Isolated artfont fixture + pipeline chroma log
  --debug-title-assets  Probe title/logo PCX from MPQ and exit
  --debug-title-frame=N Freeze title logo on frame N (idle until quit)
  --debug-title-animation  Log title timeline (elapsed/frame/prompt)
  --debug-title-capture Capture title frames → tmp/title-capture/
  --debug-font-pipeline Alias of --debug-font (glyph index histogram)
  --debug-draw-log      Log loading/menu blit rects (asset,x,y,w,h)
  --debug-tile-coverage Classify tiles (ZERO_PIECE/DRAWN/…); dump report
  --debug-tile-coverage-overlay Per-tile coverage tint overlay
  --coverage-suite      Town START+MOVE_5_* coverage dumps to tmp/coverage/
  --movement-suite      Town walk coherence A–H → CORE_MOVEMENT_COHERENT / NOT_PLAYABLE
  --debug-walk-step     Per-tick walk lifecycle JSONL
  --debug-camera        Concise camera/view/scroll log
  --debug-click-overlay Cursor/path/current/future/cam markers
  --profile-frame       Periodic FPS / sim / render / decode / upload stats (+p95/p99)
  --debug-cursor        Log CheckCursMove / destAction / walkpath[0] + pcurs/frame/hotspot each play frame
  --debug-cursor-target Log mouse/tile/player/future/cam/scroll/entity hover
  --debug-cursor-assets Magenta grid of every objcurs frame (id/size/hotspot); Left/Right page
  --debug-player-attack-frame=N  Blit warrior wldat.cl2 frame N alone (use --direction=E)
  --debug-player-attack-assets Dump 8-dir attack group/frame sizes+CRC
  --direction=S|SW|W|NW|N|NE|E|SE  Direction for attack frame debug (default E)
  --debug-player-state  Log mode transitions / destAction / tplayer reprocess
  --debug-one-step      Force 8-dir adjacent StartWalk commits and exit
  --scenario=A..K|ALL|combat-visual|l1-fidelity|player-attack-visual   Deterministic gameplay scenarios (PASS/FAIL)
  --debug-gameplay-trace[=FILE]  JSONL tick/frame trace (default tmp/gameplay-trace.jsonl)
  --debug-gameplay-overlay  Draw current/future/cam/curs/path markers
  --dump-cell=X,Y     Dump dPiece/micros/SOL/special/entities at tile (tmp/dump-cell.jsonl)
  --trace-tile=X,Y    Log every DrawFloor/DrawCell/entity/special touch of tile
  --debug-render-passes  Per-frame PASS_BEGIN/END + tile/draw/entity counters
  --debug-render-state  Assert clip unused + draw color restored after Scrollrt passes
  --debug-combat-capture=DIR  L1 combat frames → DIR/frame-NNN.png + state.jsonl
  --combat-world-stability  Micros-only world pixel compare before/after attack (hard gate)
  --hide-actors         Omit player/towners/monsters/missiles from entity draw
  --hide-objects        Omit objects from entity draw
  --hide-items          Omit ground items from entity draw
  --hide-hud            Skip HUD overlay (geometry dumps)
  --parity-scenario=ID  Geometry dump helper: fullbright+hide*+freeze; writes state JSON
  --render-trace        Record every micro/special blit → tmp/render-trace.json
  --debug-pixel=X,Y     Trace all draws touching pixel; print winner (implies --render-trace)
  --software-compose    CPU framebuffer compose then one SDL upload (A/B vs SDL)
  --freeze-first-frame  Town golden frame: one render, no walk/AI/anim; dump tmp/first-frame-state.json
  --debug-first-frame-magenta  Freeze + magenta clear before world
  --trace-player-draw   Log every player/towner blit during DrawDungeon
  --trace-actor-draw    Log every actor blit + assert ≤1 per id/frame
  --town-piece-audit    Sample house micros → tmp/town-piece-alignment.jsonl + oracle compare
  --l1-piece-audit      Sample L1 micros → tmp/l1-piece-audit.jsonl + L1_PIECE_PARITY.md
  world:cell-diff --x=N --y=N [--ref-dump=PATH] [--viewport] [--oracle]
  world:validate-visible [--town|--level=1 --seed=N]
  world:dump-grid [--oracle] [--out=PATH]
  world:pixel-diff --a=png --b=png [--out=diff.png] [--mask-ui]
  world:min-fixtures [--seed=12345] [--count=50]
  world:parity-oracle-dump [--seed=12345]
  world:microtile-golden [--data=MPQ] [--seed=12345]
  --maze-fallback     Allow maze .dun if CreateL5Dungeon throws (off by default)
  --help        This help

STATUS: NOT_PLAYABLE — grades BROKEN|PARTIAL|READY_FOR_HUMAN_REVIEW only.
HUMAN_CONFIRMATION_REQUIRED: YES

Legal: original Blizzard assets are NOT distributed with this project.

TXT);
    exit(0);
}

if ($data === '') {
    fwrite(STDERR, "Diablo PHP error: provide --data=/path/to/DIABDAT.MPQ or DIABLO_DATA\n");
    exit(1);
}

if (!is_file($data)) {
    fwrite(STDERR, "Diablo PHP error: data file not found: {$data}\n");
    exit(1);
}

try {
    if ($inspect || $smoke || $playtest) {
        $mpq = new MpqArchive($data);
        $info = $mpq->info();
        fwrite(STDOUT, "MPQ OK\n");
        foreach ($info as $k => $v) {
            fwrite(STDOUT, "  {$k}: {$v}\n");
        }

        $probes = [
            'levels\\towndata\\town.pal',
            'Levels\\TownData\\Town.PAL',
            'ui_art\\title.pal',
            'ctrlpan\\panel8.cel',
            'towners\\butch\\deadguy.cel',
            'plrgfx\\warrior\\wld\\wldas.cl2',
        ];
        $found = 0;
        foreach ($probes as $name) {
            if ($mpq->hasFile($name)) {
                $bytes = $mpq->readFile($name);
                fwrite(STDOUT, sprintf("  READ %s (%d bytes)\n", $name, strlen($bytes)));
                $found++;
            } else {
                fwrite(STDOUT, "  MISS {$name}\n");
            }
        }
        if ($found === 0) {
            fwrite(STDERR, "Diablo PHP error: opened MPQ but no probe files found/readable\n");
            exit(1);
        }
        fwrite(STDOUT, "Inspect OK ({$found} files read)\n");
        if ($inspect && !$smoke && !$playtest) {
            exit(0);
        }
    }

    if (!extension_loaded('ffi')) {
        fwrite(STDERR, "Diablo PHP error: ext-ffi is required for the game window.\n");
        exit(1);
    }
    $ffiEnable = (string) ini_get('ffi.enable');
    if ($ffiEnable !== '1' && strtolower($ffiEnable) !== 'true') {
        fwrite(STDERR, "Diablo PHP error: enable FFI: php -d ffi.enable=true bin/diablo.php ...\n");
        exit(1);
    }

    (new Diablo($data, [
        'smoke' => $smoke,
        'town' => $town,
        'level' => $level,
        'seed' => $seed,
        'debug' => $debug,
        'playtest' => $playtest,
        'debugGeneration' => $debugGeneration,
        'mazeFallback' => $mazeFallback,
        'debugAsset' => $debugAsset,
        'debugFrame' => $debugFrame,
        'renderLayer' => $renderLayer,
        'debugStaticFrame' => $debugStaticFrame,
        'dumpFrame' => $dumpFrame,
        'debugGenAscii' => $debugGenAscii,
        'debugPlaceholders' => $debugPlaceholders,
        'fullbright' => $fullbright,
        'cameraFixed' => $cameraFixed,
        'debugAlpha' => $debugAlpha,
        'dumpPalette' => $dumpPalette,
        'debugPiece' => $debugPiece,
        'debugWalk' => $debugWalk,
        'debugFont' => $debugFont,
        'debugTitleAssets' => $debugTitleAssets,
        'debugTitleFrame' => $debugTitleFrame,
        'debugTitleAnimation' => $debugTitleAnimation,
        'debugTitleCapture' => $debugTitleCapture,
        'debugDrawLog' => $debugDrawLog,
        'debugTileCoverage' => $debugTileCoverage,
        'debugTileCoverageOverlay' => $debugTileCoverageOverlay,
        'debugWorldPick' => $debugWorldPick,
        'forceFullRedraw' => $forceFullRedraw,
        'debugClearMagenta' => $debugClearMagenta,
        'debugDisableMasks' => $debugDisableMasks,
        'disableWorldClipping' => $disableWorldClipping,
        'debugRenderMargin' => $debugRenderMargin,
        'debugWalkCapture' => $debugWalkCapture,
        'forcePos' => $forcePos,
        'coverageSuite' => $coverageSuite,
        'movementSuite' => $movementSuite,
        'debugWalkStep' => $debugWalkStep,
        'debugCamera' => $debugCamera,
        'debugClickOverlay' => $debugClickOverlay,
        'profileFrame' => $profileFrame,
        'debugCursor' => $debugCursor,
        'debugCursorTarget' => $debugCursorTarget,
        'debugCursorAssets' => $debugCursorAssets,
        'debugPlayerAttackFrame' => $debugPlayerAttackFrame,
        'debugPlayerAttackAssets' => $debugPlayerAttackAssets,
        'direction' => $direction,
        'debugPlayerState' => $debugPlayerState,
        'debugOneStep' => $debugOneStep,
        'scenario' => $scenario,
        'gameplayTrace' => $gameplayTrace,
        'debugGameplayOverlay' => $debugGameplayOverlay,
        'dumpCell' => $dumpCell,
        'traceTile' => $traceTile,
        'debugRenderPasses' => $debugRenderPasses,
        'freezeFirstFrame' => $freezeFirstFrame,
        'tracePlayerDraw' => $tracePlayerDraw,
        'traceActorDraw' => $traceActorDraw,
        'townPieceAudit' => $townPieceAudit,
        'l1PieceAudit' => $l1PieceAudit,
        'debugRenderState' => $debugRenderState,
        'debugCombatCapture' => $debugCombatCapture,
        'combatWorldStability' => $combatWorldStability,
        'hideActors' => $hideActors,
        'hideObjects' => $hideObjects,
        'hideItems' => $hideItems,
        'hideHud' => $hideHud,
        'parityScenario' => $parityScenario,
        'renderTrace' => $renderTrace,
        'softwareCompose' => $softwareCompose,
        'debugPixel' => $debugPixel,
    ]))->run();
} catch (Throwable $e) {
    fwrite(STDERR, 'Diablo PHP error: ' . $e->getMessage() . PHP_EOL);
    if (getenv('DIABLO_DEBUG') || $debug) {
        fwrite(STDERR, $e->getTraceAsString() . PHP_EOL);
    }
    exit(1);
}
