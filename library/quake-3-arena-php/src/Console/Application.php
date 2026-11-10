<?php

declare(strict_types=1);

namespace Quake3\Console;

use Quake3\Bsp\BspLoader;
use Quake3\Bsp\BspMap;
use Quake3\Bsp\BspMeshExtractor;
use Quake3\Bsp\BspSurface;
use Quake3\Bsp\LightGrid;
use Quake3\Bsp\LightmapLump;
use Quake3\Bsp\VisData;
use Quake3\Collision\CollisionModel;
use Quake3\Filesystem\FileSystem;
use Quake3\Filesystem\Pk3Archive;
use Quake3\Game\EntityParser;
use Quake3\Game\MapGame;
use Quake3\Game\Player;
use Quake3\Game\Pmove;
use Quake3\Game\SpawnPoint;
use Quake3\Game\QuakeMath;
use Quake3\Game\Usercmd;
use Quake3\Game\ViewOffset;
use Quake3\Game\ViewWeapon;
use Quake3\Http\DevServer;
use Quake3\Platform\Gl;
use Quake3\Platform\SdlGl;
use Quake3\Platform\ThinSound;
use Quake3\Render\FlyCamera;
use Quake3\Render\ImageLoader;
use Quake3\Render\LightingConfig;
use Quake3\Render\MaterialResolver;
use Quake3\Render\MarkFragments;
use Quake3\Render\Md3Model;
use Quake3\Render\ShaderParser;
use Quake3\Render\WorldBatchBuilder;

/**
 * Minimal argv router for PK3 / BSP diagnostics.
 */
final class Application
{
    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        $cmd = $argv[1] ?? null;
        if ($cmd === null || $cmd === 'help' || $cmd === '--help' || $cmd === '-h') {
            $this->printUsage();
            return $cmd === null ? 1 : 0;
        }

        try {
            return match ($cmd) {
                'pk3:list' => $this->cmdPk3List($argv),
                'pk3:cat' => $this->cmdPk3Cat($argv),
                'pk3:info' => $this->cmdPk3Info($argv),
                'fs:which' => $this->cmdFsWhich($argv),
                'bsp:info' => $this->cmdBspInfo($argv),
                'bsp:mesh' => $this->cmdBspMesh($argv),
                'bsp:spawns' => $this->cmdBspSpawns($argv),
                'bsp:surfaces' => $this->cmdBspSurfaces($argv),
                'bsp:lightmaps' => $this->cmdBspLightmaps($argv),
                'bsp:entities' => $this->cmdBspEntities($argv),
                'trace:test' => $this->cmdTraceTest($argv),
                'regress:pmove' => $this->cmdRegressPmove($argv),
                'q3:texture:inspect' => $this->cmdTextureInspect($argv),
                'shader:audit' => $this->cmdShaderAudit($argv),
                'shader:missing' => $this->cmdShaderMissing($argv),
                'render:audit' => $this->cmdRenderAudit($argv),
                'bsp:surface-audit' => $this->cmdBspSurfaceAudit($argv),
                'md3:inspect' => $this->cmdMd3Inspect($argv),
                'lightmap:dump' => $this->cmdLightmapDump($argv),
                'serve' => $this->cmdServe($argv),
                'window' => $this->cmdWindow($argv),
                'play' => $this->cmdPlay($argv),
                'data:inspect' => $this->cmdDataInspect($argv),
                default => $this->unknown($cmd),
            };
        } catch (\Throwable $e) {
            fwrite(STDERR, 'error: ' . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function printUsage(): void
    {
        $out = <<<'USAGE'
Quake III Arena PHP — Q3DM0_PLAYABLE_COHERENT → REFERENCE_CLOSE

Native:
  php -d ffi.enable=true bin/quake3.php play --basepath=/path --map=maps/q3dm0.bsp
    [--freecam] [--no-textures] [--no-lightmaps] [--lightmap-only] [--no-pvs] [--gl-debug]
    [--debug-lighting] [--overbright=N] [--gamma=F] [--intensity=F]
    [--debug-material=shader] [--debug-surface-pick] [--debug-batches] [--debug-face=N]
    [--debug-no-cull] [--no-sky] [--no-flares] [--no-effects] [--spawn=N]
    [--no-view-weapon] [--weapon-only] [--no-muzzle-flash] [--weapon-wireframe]
    [--weapon-debug] [--fixed-view=yaw,pitch] [--frames=N]
    [--profile-frame] [--show-entities] [--no-sound]

Diagnostics:
  php bin/quake3.php regress:pmove --basepath=/path maps/q3dm0.bsp
  php bin/quake3.php shader:audit --basepath=/path maps/q3dm0.bsp
  php bin/quake3.php shader:missing --basepath=/path maps/q3dm0.bsp
  php bin/quake3.php render:audit --basepath=/path maps/q3dm0.bsp
  php bin/quake3.php bsp:surface-audit --basepath=/path maps/q3dm1.bsp
  php bin/quake3.php lightmap:dump --basepath=/path maps/q3dm0.bsp [--page=0] [--out=debug/lm0.png]
  php bin/quake3.php md3:inspect --basepath=/path models/powerups/health/medium_cross.md3

USAGE;
        fwrite(STDOUT, $out);
    }

    private function unknown(string $cmd): int
    {
        fwrite(STDERR, "unknown command: {$cmd}\n\n");
        $this->printUsage();
        return 1;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdPk3Info(array $argv): int
    {
        $path = $argv[2] ?? null;
        if ($path === null) {
            fwrite(STDERR, "usage: pk3:info <archive.pk3>\n");
            return 1;
        }

        $pak = new Pk3Archive($path);
        fwrite(STDOUT, sprintf("archive:   %s\n", $pak->getPath()));
        fwrite(STDOUT, sprintf("basename:  %s\n", $pak->getBasename()));
        fwrite(STDOUT, sprintf("entries:   %d (indexed files)\n", $pak->getNumFiles()));
        fwrite(STDOUT, sprintf("zip nodes: %d (central directory)\n", $pak->getZipEntryCount()));

        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdPk3List(array $argv): int
    {
        $path = $argv[2] ?? null;
        if ($path === null) {
            fwrite(STDERR, "usage: pk3:list <archive.pk3>\n");
            return 1;
        }

        $pak = new Pk3Archive($path);
        fwrite(STDOUT, sprintf("# %s (%d files)\n", $pak->getBasename(), $pak->getNumFiles()));
        fwrite(STDOUT, sprintf("# %-10s %-10s %-6s %s\n", 'comp', 'uncomp', 'method', 'name'));

        $entries = $pak->listEntries();
        usort($entries, static fn ($a, $b) => strcmp($a->name, $b->name));

        foreach ($entries as $e) {
            fwrite(STDOUT, sprintf(
                "%-10d %-10d %-6d %s\n",
                $e->compressedSize,
                $e->uncompressedSize,
                $e->compressionMethod,
                $e->name,
            ));
        }

        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdPk3Cat(array $argv): int
    {
        $path = $argv[2] ?? null;
        $qpath = $argv[3] ?? null;
        if ($path === null || $qpath === null) {
            fwrite(STDERR, "usage: pk3:cat <archive.pk3> <qpath>\n");
            return 1;
        }

        $pak = new Pk3Archive($path);
        $entry = $pak->getEntry($qpath);
        if ($entry === null) {
            fwrite(STDERR, sprintf("not found: %s in %s\n", $qpath, $pak->getPath()));
            return 1;
        }

        fwrite(STDERR, sprintf(
            "lookup: %s\n  archive: %s\n  basename: %s\n  normalized: %s\n  compressed: %d\n  uncompressed: %d\n  method: %d\n",
            $qpath,
            $entry->archivePath,
            $entry->archiveBasename,
            $entry->name,
            $entry->compressedSize,
            $entry->uncompressedSize,
            $entry->compressionMethod,
        ));

        fwrite(STDOUT, $pak->read($qpath));

        return 0;
    }

    /**
     * Last argument is qpath; preceding args are game dirs and/or .pk3 paths.
     * Directories use addGameDirectory (sorted override); explicit PK3s are prepended in order given
     * (later args = higher priority, matching successive prepend).
     *
     * @param list<string> $argv
     */
    private function cmdFsWhich(array $argv): int
    {
        if (count($argv) < 4) {
            fwrite(STDERR, "usage: fs:which <gameDir|pak.pk3>... <qpath>\n");
            return 1;
        }

        $qpath = $argv[array_key_last($argv)];
        $sources = array_slice($argv, 2, -1);

        $fs = new FileSystem();
        foreach ($sources as $src) {
            if (is_dir($src)) {
                $loaded = $fs->addGameDirectory($src);
                fwrite(STDERR, sprintf("loaded %d pk3 from %s\n", count($loaded), realpath($src) ?: $src));
                foreach ($loaded as $pak) {
                    fwrite(STDERR, sprintf("  + %s (%d files)\n", $pak->getBasename(), $pak->getNumFiles()));
                }
            } elseif (is_file($src)) {
                $pak = $fs->addPak($src);
                fwrite(STDERR, sprintf("prepended %s (%d files)\n", $pak->getBasename(), $pak->getNumFiles()));
            } else {
                throw new \RuntimeException(sprintf('Not a file or directory: %s', $src));
            }
        }

        fwrite(STDERR, "search order (highest first):\n");
        foreach ($fs->getPacks() as $i => $pak) {
            fwrite(STDERR, sprintf("  [%d] %s — %s\n", $i, $pak->getBasename(), $pak->getPath()));
        }

        $resolved = $fs->resolve($qpath);
        if ($resolved === null) {
            fwrite(STDERR, sprintf("not found: %s\n", $qpath));
            return 1;
        }

        $e = $resolved->entry;
        fwrite(STDOUT, sprintf(
            "qpath: %s\nnormalized: %s\nsource_pk3: %s\nsource_path: %s\ncompressed: %d\nuncompressed: %d\nmethod: %d\n",
            $qpath,
            $e->name,
            $e->archiveBasename,
            $e->archivePath,
            $e->compressedSize,
            $e->uncompressedSize,
            $e->compressionMethod,
        ));

        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdBspInfo(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if ($arg === '--basepath') {
                throw new \RuntimeException('use --basepath=/path (equals form)');
            }
            $positional[] = $arg;
        }

        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: bsp:info [--basepath=/path/to/Quake3] <file.bsp|maps/q3dm1.bsp>\n");
            return 1;
        }

        $target = $positional[0];
        $map = $this->loadBspMap($target, $basepath);
        $this->printBspInfo($map);

        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdBspMesh(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $outPath = null;
        $positional = [];

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (str_starts_with($arg, '--out=')) {
                $outPath = substr($arg, strlen('--out='));
                continue;
            }
            if ($arg === '--basepath') {
                throw new \RuntimeException('use --basepath=/path (equals form)');
            }
            $positional[] = $arg;
        }

        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: bsp:mesh [--out=tmp/map_mesh.json] [--basepath=/path] <file.bsp>\n");
            return 1;
        }

        $map = $this->loadBspMap($positional[0], $basepath);
        $mesh = (new BspMeshExtractor())->extract($map);

        fwrite(STDOUT, sprintf("source: %s\n", $mesh->sourceLabel));
        fwrite(STDOUT, sprintf("surfaces: %d\n", $mesh->surfaceCount));
        fwrite(STDOUT, sprintf(
            "surface types: planar=%d triangle_soup=%d patch=%d flare=%d bad=%d\n",
            $mesh->surfaceTypeCounts['planar'] ?? 0,
            $mesh->surfaceTypeCounts['triangle_soup'] ?? 0,
            $mesh->surfaceTypeCounts['patch'] ?? 0,
            $mesh->surfaceTypeCounts['flare'] ?? 0,
            $mesh->surfaceTypeCounts['bad'] ?? 0,
        ));
        fwrite(STDOUT, sprintf("vertices: %d\n", count($mesh->positions)));
        fwrite(STDOUT, sprintf("triangles: %d\n", $mesh->triangleCount));
        fwrite(STDOUT, sprintf(
            "bounds: mins=(%.1f, %.1f, %.1f) maxs=(%.1f, %.1f, %.1f)\n",
            $mesh->mins[0], $mesh->mins[1], $mesh->mins[2],
            $mesh->maxs[0], $mesh->maxs[1], $mesh->maxs[2],
        ));
        fwrite(STDOUT, "patches: EXPECTED_NOT_IMPLEMENTED (not tessellated)\n");

        if ($outPath !== null) {
            $json = json_encode($mesh->toJsonArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            if (!is_dir(dirname($outPath))) {
                mkdir(dirname($outPath), 0777, true);
            }
            file_put_contents($outPath, $json);
            fwrite(STDOUT, sprintf("wrote: %s\n", $outPath));
        }

        return 0;
    }

    /**
     * List info_player_deathmatch spawns from entity lump.
     *
     * @param list<string> $argv
     */
    private function cmdBspSpawns(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            $positional[] = $arg;
        }
        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: bsp:spawns [--basepath=/path] <file.bsp>\n");
            return 1;
        }

        $map = $this->loadBspMap($positional[0], $basepath);
        $spawns = (new EntityParser())->collectDeathmatchSpawns($map);
        fwrite(STDOUT, sprintf("deathmatch spawns: %d\n", count($spawns)));
        foreach ($spawns as $s) {
            $po = $s->playerOrigin();
            fwrite(STDOUT, sprintf(
                "  #%d %s origin=(%.1f %.1f %.1f) angles=(%.1f %.1f %.1f) spawnflags=%d playerOrigin_z+9=(%.1f %.1f %.1f)\n",
                $s->index,
                $s->classname,
                $s->origin[0], $s->origin[1], $s->origin[2],
                $s->angles[0], $s->angles[1], $s->angles[2],
                $s->spawnflags,
                $po[0], $po[1], $po[2],
            ));
        }

        return 0;
    }

    /**
     * Smoke-test CM_BoxTrace: drop player AABB from spawn onto floor.
     *
     * @param list<string> $argv
     */
    private function cmdTraceTest(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            $positional[] = $arg;
        }
        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: trace:test [--basepath=/path] <file.bsp>\n");
            return 1;
        }

        $map = $this->loadBspMap($positional[0], $basepath);
        $spawns = (new EntityParser())->collectDeathmatchSpawns($map);
        if ($spawns === []) {
            fwrite(STDERR, "no deathmatch spawns\n");
            return 1;
        }
        $spawn = $spawns[0];
        $start = $spawn->playerOrigin();
        $end = [$start[0], $start[1], $start[2] - 128.0];

        $cm = CollisionModel::load($map);
        $tr = $cm->trace($start, $end, Player::MINS, Player::MAXS, CollisionModel::MASK_PLAYERSOLID);
        fwrite(STDOUT, sprintf(
            "drop from spawn #0 (%.1f %.1f %.1f)\n",
            $start[0], $start[1], $start[2],
        ));
        fwrite(STDOUT, sprintf(
            "fraction=%.4f end=(%.2f %.2f %.2f) startsolid=%d allsolid=%d\n",
            $tr->fraction,
            $tr->endPos[0], $tr->endPos[1], $tr->endPos[2],
            $tr->startSolid ? 1 : 0,
            $tr->allSolid ? 1 : 0,
        ));
        if ($tr->planeNormal !== null) {
            fwrite(STDOUT, sprintf(
                "plane n=(%.3f %.3f %.3f) dist=%.2f contents=0x%x\n",
                $tr->planeNormal[0], $tr->planeNormal[1], $tr->planeNormal[2],
                $tr->planeDist,
                $tr->contents,
            ));
        }

        return $tr->fraction < 1.0 ? 0 : 1;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdRegressPmove(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            $positional[] = $arg;
        }
        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: regress:pmove --basepath=/path maps/q3dm0.bsp\n");
            return 1;
        }
        $map = $this->loadBspMap($positional[0], $basepath);
        $built = (new WorldBatchBuilder())->build($map);
        $cm = CollisionModel::load($map);
        $cm->setPatchCollisionTris($built['patchCollisionTris']);
        $spawns = (new EntityParser())->collectDeathmatchSpawns($map);
        if ($spawns === []) {
            fwrite(STDERR, "FAIL: no spawns\n");
            return 1;
        }
        $fail = 0;
        $spawn = $spawns[0];
        $p = new Player(origin: $spawn->playerOrigin(), viewangles: [0.0, $spawn->angles[1], 0.0]);
        $drop = $cm->trace($p->origin, [$p->origin[0], $p->origin[1], $p->origin[2] - 64], Player::MINS, Player::MAXS);
        if ($drop->fraction >= 1.0) {
            fwrite(STDOUT, "FAIL: floor drop\n");
            $fail++;
        } else {
            $p->origin = $drop->endPos;
            fwrite(STDOUT, "OK floor drop z=" . $p->origin[2] . "\n");
        }
        $z0 = $p->origin[2];
        $pm = new Pmove($cm);
        for ($i = 0; $i < 30; $i++) {
            $pm->move($p, new Usercmd(serverTime: $p->commandTime + 16, forwardmove: 127, angles: $p->viewangles));
        }
        $dist = sqrt(($p->origin[0] - $spawn->playerOrigin()[0]) ** 2 + ($p->origin[1] - $spawn->playerOrigin()[1]) ** 2);
        if ($dist < 10) {
            fwrite(STDOUT, "FAIL: walk distance $dist\n");
            $fail++;
        } else {
            fwrite(STDOUT, "OK walk dist=$dist\n");
        }
        if (abs($p->origin[2] - $z0) > 40) {
            fwrite(STDOUT, "FAIL: fell through? z={$p->origin[2]}\n");
            $fail++;
        }
        $pm->move($p, new Usercmd(serverTime: $p->commandTime + 16, upmove: 127, angles: $p->viewangles));
        if ($p->velocity[2] < 200) {
            fwrite(STDOUT, "FAIL: jump vel={$p->velocity[2]}\n");
            $fail++;
        } else {
            fwrite(STDOUT, "OK jump velz={$p->velocity[2]}\n");
        }
        // wall: walk into +X from spawn with yaw 0
        $p2 = new Player(origin: $spawn->playerOrigin(), viewangles: [0.0, 0.0, 0.0]);
        $d2 = $cm->trace($p2->origin, [$p2->origin[0], $p2->origin[1], $p2->origin[2] - 64], Player::MINS, Player::MAXS);
        $p2->origin = $d2->endPos;
        $x0 = $p2->origin[0];
        $pm2 = new Pmove($cm);
        for ($i = 0; $i < 200; $i++) {
            $pm2->move($p2, new Usercmd(serverTime: $p2->commandTime + 16, forwardmove: 127, angles: $p2->viewangles));
        }
        $dx = $p2->origin[0] - $x0;
        fwrite(STDOUT, "OK wall-walk dx=$dx (finite)\n");
        if (!is_finite($dx) || abs($p2->origin[2] - $d2->endPos[2]) > 80) {
            fwrite(STDOUT, "FAIL: wall walk unstable\n");
            $fail++;
        }
        fwrite(STDOUT, $fail === 0 ? "regress:pmove PASS\n" : "regress:pmove FAIL ($fail)\n");
        return $fail === 0 ? 0 : 1;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdBspSurfaces(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];
        $limit = 20;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (str_starts_with($arg, '--limit=')) {
                $limit = (int) substr($arg, 8);
                continue;
            }
            $positional[] = $arg;
        }
        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: bsp:surfaces [--basepath=/path] [--limit=20] <file.bsp>\n");
            return 1;
        }
        $map = $this->loadBspMap($positional[0], $basepath);
        $ex = new BspMeshExtractor();
        $shaders = $ex->loadShaders($map);
        $surfaces = $ex->loadSurfaces($map);
        $byType = [];
        $byShader = [];
        $byLm = [];
        foreach ($surfaces as $s) {
            $tn = $s->typeName();
            $byType[$tn] = ($byType[$tn] ?? 0) + 1;
            $sn = $shaders[$s->shaderNum]->shader ?? '?';
            $byShader[$sn] = ($byShader[$sn] ?? 0) + 1;
            $byLm[$s->lightmapNum] = ($byLm[$s->lightmapNum] ?? 0) + 1;
        }
        fwrite(STDOUT, "surfaces: " . count($surfaces) . "\nby type:\n");
        foreach ($byType as $k => $v) {
            fwrite(STDOUT, "  $k: $v\n");
        }
        fwrite(STDOUT, "by lightmap (top):\n");
        arsort($byLm);
        $i = 0;
        foreach ($byLm as $k => $v) {
            fwrite(STDOUT, "  lm $k: $v\n");
            if (++$i >= 10) {
                break;
            }
        }
        fwrite(STDOUT, "by shader (top $limit):\n");
        arsort($byShader);
        $i = 0;
        foreach ($byShader as $k => $v) {
            fwrite(STDOUT, "  $v\t$k\n");
            if (++$i >= $limit) {
                break;
            }
        }
        fwrite(STDOUT, "sample faces:\n");
        foreach (array_slice($surfaces, 0, min(10, count($surfaces))) as $s) {
            fwrite(STDOUT, sprintf(
                "  #%d type=%s shader=%s lm=%d verts=%d..+%d idxs=%d..+%d\n",
                $s->index,
                $s->typeName(),
                $shaders[$s->shaderNum]->shader ?? '?',
                $s->lightmapNum,
                $s->firstVert,
                $s->numVerts,
                $s->firstIndex,
                $s->numIndexes,
            ));
        }
        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdBspLightmaps(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            $positional[] = $arg;
        }
        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: bsp:lightmaps [--basepath=/path] <file.bsp>\n");
            return 1;
        }
        $map = $this->loadBspMap($positional[0], $basepath);
        $lm = LightmapLump::load($map);
        fwrite(STDOUT, sprintf(
            "lightmaps: %d pages of %dx%d RGB (%d bytes/page)\n",
            $lm->count(),
            LightmapLump::PAGE_SIZE,
            LightmapLump::PAGE_SIZE,
            LightmapLump::BYTES_PER_PAGE,
        ));
        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdBspEntities(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $positional = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            $positional[] = $arg;
        }
        if (count($positional) !== 1) {
            fwrite(STDERR, "usage: bsp:entities [--basepath=/path] <file.bsp>\n");
            return 1;
        }
        $map = $this->loadBspMap($positional[0], $basepath);
        $ents = (new EntityParser())->parseEntities($map->readLumpBytes(0));
        $counts = [];
        foreach ($ents as $e) {
            $c = $e['classname'] ?? '?';
            $counts[$c] = ($counts[$c] ?? 0) + 1;
        }
        ksort($counts);
        fwrite(STDOUT, "entities: " . count($ents) . "\n");
        foreach ($counts as $k => $v) {
            fwrite(STDOUT, "  $v\t$k\n");
        }
        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdTextureInspect(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $name = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $name = $arg;
            }
        }
        if ($basepath === null || $name === null) {
            fwrite(STDERR, "usage: q3:texture:inspect --basepath=/path <shaderOrPath>\n");
            return 1;
        }
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $mat = new MaterialResolver($fs);
        $path = $mat->resolvePath($name);
        if ($path === null) {
            fwrite(STDOUT, "resolved: (not found)\n");
            return 1;
        }
        $img = $mat->resolveImage($name);
        fwrite(STDOUT, "resolved: $path\n");
        if ($img === null) {
            fwrite(STDOUT, "decode: failed\n");
            return 1;
        }
        fwrite(STDOUT, sprintf(
            "format: %s\nwidth: %d\nheight: %d\nalpha: %s\nchecksum: %s\n",
            $img->format,
            $img->width,
            $img->height,
            $img->hasAlpha ? 'yes' : 'no',
            $img->checksum(),
        ));
        return 0;
    }

    /**
     * FIRST_TRIANGLES — load BSP mesh in PHP, serve static WebGL viewer.
     *
     * @param list<string> $argv
     */
    private function cmdServe(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $map = null;
        $host = '127.0.0.1';
        $port = 8080;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (str_starts_with($arg, '--map=')) {
                $map = substr($arg, strlen('--map='));
                continue;
            }
            if (str_starts_with($arg, '--host=')) {
                $host = substr($arg, strlen('--host='));
                continue;
            }
            if (str_starts_with($arg, '--port=')) {
                $port = (int) substr($arg, strlen('--port='));
                continue;
            }
            throw new \RuntimeException('unknown serve option: ' . $arg);
        }

        if ($basepath === null || $map === null) {
            fwrite(STDERR, "usage: serve --basepath=/path/to/Quake3 --map=maps/q3dm0.bsp [--port=8080] [--host=127.0.0.1]\n");
            return 1;
        }

        if ($port < 1 || $port > 65535) {
            throw new \RuntimeException('invalid port');
        }

        $bspMap = $this->loadBspMap($map, $basepath);
        $worldMesh = (new BspMeshExtractor())->extract($bspMap);
        $meshJson = json_encode($worldMesh->toJsonArray(), JSON_THROW_ON_ERROR);

        fwrite(STDERR, sprintf(
            "mesh: %d triangles, %d vertices (patches not tessellated)\n",
            $worldMesh->triangleCount,
            count($worldMesh->positions),
        ));

        $publicDir = dirname(__DIR__, 2) . '/public';
        if (!is_dir($publicDir)) {
            throw new \RuntimeException('public/ directory missing');
        }

        (new DevServer($host, $port, $publicDir, $meshJson, $worldMesh->sourceLabel))->run();
    }

    /**
     * P0 — SDL2 OpenGL clear + input smoke test.
     *
     * @param list<string> $argv
     */
    private function cmdWindow(array $argv): int
    {
        $sdl = new SdlGl();
        $sdl->init('Quake3 PHP — window (Esc quit)', 1024, 768);
        $gl = new Gl();
        $gl->viewport(0, 0, $sdl->getWidth(), $sdl->getHeight());
        $gl->enableDepthTest();

        fwrite(STDERR, "window: clear-color loop — Esc or close to quit\n");
        $t0 = $sdl->ticks();
        $running = true;
        while ($running) {
            $ev = $sdl->pollEvents();
            if ($ev['quit'] || $ev['escape']) {
                $running = false;
            }

            $t = ($sdl->ticks() - $t0) / 1000.0;
            $gl->clearColor(
                0.05 + 0.05 * sin($t),
                0.08 + 0.04 * cos($t * 0.7),
                0.15,
                1.0,
            );
            $gl->clear();
            $sdl->swap();
            $sdl->delay(16);
        }

        $sdl->shutdown();
        fwrite(STDERR, "window: shutdown ok\n");

        return 0;
    }

    /**
     * Textured+lightmapped world + pmove + coherent q3dm0 fidelity slice.
     *
     * @param list<string> $argv
     */
    private function cmdPlay(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $map = null;
        $freecam = false;
        $drawMode = 'full';
        $spawnIndex = null;
        $profile = false;
        $showEntities = false;
        $usePvs = true;
        $glDebug = false;
        $soundOn = true;
        $debugLighting = false;
        $debugMaterial = null;
        $debugSurfacePick = false;
        $debugBatches = false;
        $debugFace = null;
        $debugNoCull = false;
        $drawSky = true;
        $drawEffects = true;
        $drawFlares = true;
        $maxFrames = 0;
        $noViewWeapon = false;
        $weaponOnly = false;
        $noMuzzleFlash = false;
        $weaponWireframe = false;
        $weaponDebug = false;
        $fixedView = null; // [yaw, pitch] degrees
        $overbrightBits = 0; // windowed Q3 default
        $gamma = 1.0;
        $intensity = 1.0;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (str_starts_with($arg, '--map=')) {
                $map = substr($arg, strlen('--map='));
                continue;
            }
            if ($arg === '--freecam' || $arg === '--noclip') {
                $freecam = true;
                continue;
            }
            if ($arg === '--no-textures') {
                $drawMode = 'no_textures';
                continue;
            }
            if ($arg === '--no-lightmaps') {
                $drawMode = 'no_lightmaps';
                continue;
            }
            if ($arg === '--lightmap-only') {
                $drawMode = 'lightmap_only';
                continue;
            }
            if ($arg === '--no-pvs') {
                $usePvs = false;
                continue;
            }
            if ($arg === '--gl-debug') {
                $glDebug = true;
                continue;
            }
            if ($arg === '--no-sound') {
                $soundOn = false;
                continue;
            }
            if ($arg === '--debug-lighting') {
                $debugLighting = true;
                continue;
            }
            if (str_starts_with($arg, '--overbright=')) {
                $overbrightBits = (int) substr($arg, strlen('--overbright='));
                continue;
            }
            if (str_starts_with($arg, '--gamma=')) {
                $gamma = (float) substr($arg, strlen('--gamma='));
                continue;
            }
            if (str_starts_with($arg, '--intensity=')) {
                $intensity = (float) substr($arg, strlen('--intensity='));
                continue;
            }
            if (str_starts_with($arg, '--debug-material=')) {
                $debugMaterial = substr($arg, strlen('--debug-material='));
                continue;
            }
            if ($arg === '--debug-surface-pick') {
                $debugSurfacePick = true;
                continue;
            }
            if ($arg === '--debug-batches') {
                $debugBatches = true;
                continue;
            }
            if (str_starts_with($arg, '--debug-face=')) {
                $debugFace = (int) substr($arg, strlen('--debug-face='));
                continue;
            }
            if ($arg === '--debug-no-cull') {
                $debugNoCull = true;
                continue;
            }
            if ($arg === '--no-sky') {
                $drawSky = false;
                continue;
            }
            if ($arg === '--no-effects') {
                $drawEffects = false;
                continue;
            }
            if ($arg === '--no-flares') {
                $drawFlares = false;
                continue;
            }
            if (str_starts_with($arg, '--frames=')) {
                $maxFrames = max(0, (int) substr($arg, strlen('--frames=')));
                continue;
            }
            if ($arg === '--no-view-weapon') {
                $noViewWeapon = true;
                continue;
            }
            if ($arg === '--weapon-only') {
                $weaponOnly = true;
                continue;
            }
            if ($arg === '--no-muzzle-flash') {
                $noMuzzleFlash = true;
                continue;
            }
            if ($arg === '--weapon-wireframe') {
                $weaponWireframe = true;
                continue;
            }
            if ($arg === '--weapon-debug') {
                $weaponDebug = true;
                continue;
            }
            if (str_starts_with($arg, '--fixed-view=')) {
                $parts = explode(',', substr($arg, strlen('--fixed-view=')));
                $fixedView = [(float) ($parts[0] ?? 0), (float) ($parts[1] ?? 0)];
                continue;
            }
            if (str_starts_with($arg, '--spawn=')) {
                $spawnIndex = (int) substr($arg, 8);
                continue;
            }
            if ($arg === '--profile-frame') {
                $profile = true;
                continue;
            }
            if ($arg === '--show-entities') {
                $showEntities = true;
                continue;
            }
            throw new \RuntimeException('unknown play option: ' . $arg);
        }

        if ($basepath === null || $map === null) {
            fwrite(STDERR, "usage: play --basepath=/path --map=maps/q3dm0.bsp [flags]\n");
            return 1;
        }

        $bspMap = $this->loadBspMap($map, $basepath);
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $shaderParser = new ShaderParser($fs);
        $materials = new MaterialResolver($fs);
        $materials->setParser($shaderParser);
        $lighting = new LightingConfig(
            overbrightBits: $overbrightBits,
            gamma: $gamma,
            intensity: $intensity,
        );

        $cm = CollisionModel::load($bspMap);
        $built = (new WorldBatchBuilder())->build($bspMap, $shaderParser, $cm, $lighting, $debugFace);
        $cm->setPatchCollisionTris($built['patchCollisionTris']);
        $vis = VisData::load($bspMap);

        if ($debugLighting) {
            foreach ($lighting->debugInfo() as $k => $v) {
                fwrite(STDERR, sprintf("lighting: %s=%s\n", $k, is_bool($v) ? ($v ? 'true' : 'false') : (string) $v));
            }
            if ($built['lightmaps']->count() > 0) {
                $mean = $built['lightmaps']->pageMeanRgb(0, $lighting->mapOverBrightBits, $lighting->overbrightBits);
                fwrite(STDERR, sprintf("lighting: lm0_mean_rgb=(%.1f %.1f %.1f)\n", $mean[0], $mean[1], $mean[2]));
                $rgba = $built['lightmaps']->pageRgba(0, $lighting->mapOverBrightBits, $lighting->overbrightBits);
                $outPath = getcwd() . '/debug/lightmap0.png';
                LightingConfig::writePngRgba($outPath, $rgba, 128, 128);
                fwrite(STDERR, "lighting: wrote {$outPath}\n");
            }
            $sample = $built['batches'][0]->shaderName ?? '';
            if ($sample !== '') {
                $sh = $shaderParser->get($sample);
                fwrite(STDERR, sprintf(
                    "lighting: sample_shader=%s stages=%d blend=%s rgbGen0=%s\n",
                    $sample,
                    count($sh->stages),
                    $sh->needsBlendPass() ? 'yes' : 'no',
                    $sh->stages[0]->rgbGen ?? 'n/a',
                ));
            }
        }
        if ($debugMaterial !== null) {
            $sh = $shaderParser->get($debugMaterial);
            fwrite(STDERR, "debug-material: {$debugMaterial} sky=" . ($sh->isSky() ? '1' : '0')
                . " cull={$sh->cull} stages=" . count($sh->stages) . "\n");
            foreach ($sh->stages as $i => $st) {
                fwrite(STDERR, sprintf(
                    "  stage[%d] map=%s lm=%d blend=%d src=%d dst=%d rgbGen=%s alphaFunc=%s tcMods=%d\n",
                    $i,
                    $st->map,
                    $st->isLightmap ? 1 : 0,
                    $st->hasBlend ? 1 : 0,
                    $st->blendSrc,
                    $st->blendDst,
                    $st->rgbGen,
                    $st->alphaFunc !== '' ? $st->alphaFunc : '-',
                    count($st->tcMods),
                ));
            }
        }

        $blendCount = 0;
        foreach ($built['batches'] as $b) {
            if ($b->blend) {
                $blendCount++;
            }
        }
        fwrite(STDERR, sprintf(
            "world: %d tris, %d batches (%d blend), %d lightmaps, patches=%d flares=%d sky=%s pvs=%s\n",
            $built['triangleCount'],
            count($built['batches']),
            $blendCount,
            $built['lightmaps']->count(),
            $built['surfaceTypeCounts']['patch'] ?? 0,
            count($built['flares'] ?? []),
            $built['skyShader'] ?? '(gradient)',
            $vis !== null ? "clusters={$vis->numClusters}" : 'none',
        ));
        fwrite(STDERR, sprintf("patch collision tris: %d\n", count($built['patchCollisionTris'])));
        $skips = $built['skipReasons'] ?? [];
        if ($skips !== []) {
            $parts = [];
            foreach ($skips as $k => $v) {
                if ((int) $v > 0) {
                    $parts[] = $k . '=' . $v;
                }
            }
            fwrite(STDERR, 'surface skips: ' . ($parts !== [] ? implode(' ', $parts) : 'none') . "\n");
        }
        $multi = 0;
        $maxCl = 0;
        $faceN = 0;
        foreach ($built['batches'] as $b) {
            $n = count($b->clusters);
            if ($n > 1) {
                $multi++;
            }
            $maxCl = max($maxCl, $n);
            $faceN += count($b->faces);
        }
        fwrite(STDERR, sprintf(
            "pvs batches: multi_cluster=%d max_clusters=%d faces_packed=%d\n",
            $multi,
            $maxCl,
            $faceN,
        ));

        $spawns = (new EntityParser())->collectDeathmatchSpawns($bspMap);
        if ($spawns === []) {
            throw new \RuntimeException('no info_player_deathmatch spawns in map');
        }
        if ($spawnIndex !== null) {
            if ($spawnIndex < 0 || $spawnIndex >= count($spawns)) {
                throw new \RuntimeException('invalid --spawn index');
            }
            $spawn = $spawns[$spawnIndex];
        } else {
            $spawn = SpawnPoint::selectInitial($spawns);
        }
        $origin = $spawn->playerOrigin();
        fwrite(STDERR, sprintf(
            "spawn #%d origin=(%.1f %.1f %.1f) yaw=%.1f%s\n",
            $spawn->index,
            $origin[0], $origin[1], $origin[2],
            $spawn->angles[1],
            ($spawn->spawnflags & 1) !== 0 ? ' initial' : '',
        ));

        $game = MapGame::load($bspMap, $fs);
        $md3Loaded = 0;
        foreach ($game->items as $it) {
            if ($it['md3'] !== null) {
                $md3Loaded++;
            }
        }
        fwrite(STDERR, sprintf(
            "entities: items=%d (md3=%d) teleports=%d dummies=%d movers=%d\n",
            count($game->items),
            $md3Loaded,
            count($game->teleports),
            count($game->dummies),
            count($game->movers->movers),
        ));

        $player = new Player(
            origin: $origin,
            viewangles: [$spawn->angles[0], $spawn->angles[1], $spawn->angles[2]],
            pmFlags: Player::PMF_RESPAWNED | Player::PMF_TIME_KNOCKBACK,
            pmTime: 100,
        );
        $pmove = new Pmove($cm);
        $drop = $cm->trace(
            $player->origin,
            [$player->origin[0], $player->origin[1], $player->origin[2] - 64],
            Player::MINS,
            Player::MAXS,
        );
        if ($drop->fraction < 1.0) {
            $player->origin = $drop->endPos;
        }

        $sound = new ThinSound($soundOn, $fs);
        register_shutdown_function(static fn () => $sound->stopAllLoops());
        $prevAmmoWarning = 0;
        if ($freecam) {
            fwrite(STDERR, "mode: freecam | controls: WASD Space/Shift mouse\n");
        } else {
            fwrite(STDERR, "mode: pmove | WASD walk, Shift walk, Space jump, C crouch, mouse look, click/F fire, 2/3/8 or wheel/[ ] weapon, R respawn (after 1.7s dead), K hurt, Esc quit\n");
        }
        if ($debugSurfacePick) {
            fwrite(STDERR, "debug-surface-pick: press P (or middle-click if mapped) to dump aimed world material\n");
        }
        if ($debugNoCull) {
            fwrite(STDERR, "debug-no-cull: face culling disabled (diagnostic only)\n");
        }
        if (!$drawSky) {
            fwrite(STDERR, "sky disabled (--no-sky)\n");
        }
        if (!$drawEffects) {
            fwrite(STDERR, "effects disabled (--no-effects)\n");
        }
        if ($debugFace !== null) {
            fwrite(STDERR, "debug-face: only BSP face #{$debugFace}\n");
        }
        if ($maxFrames > 0) {
            fwrite(STDERR, "frames: exit after {$maxFrames}\n");
        }
        if (!$usePvs) {
            fwrite(STDERR, "PVS cull disabled (--no-pvs)\n");
        }

        $eye = $player->eyeOrigin();
        $cam = new FlyCamera($eye[0], $eye[1], $eye[2]);
        $cam->yaw = deg2rad($player->viewangles[1]);
        $cam->pitch = -deg2rad($player->viewangles[0]);

        $sdl = new SdlGl();
        $sdl->init('Quake3 PHP — ' . $map, 1280, 720);
        $gl = new Gl($glDebug, $lighting);
        $gl->setShaderParser($shaderParser);
        $gl->setDebugMaterial($debugMaterial);
        $gl->setDrawFlares($drawFlares, 40.0);
        $gl->setDrawSky($drawSky);
        $gl->setDrawEffects($drawEffects);
        $gl->setDebugNoCull($debugNoCull);
        $gl->setDebugBatches($debugBatches);
        $gl->setDebugFace($debugFace);
        $gl->setCollisionModel($cm);
        $gl->setMarkTris($built['markTris'] ?? []);
        $gl->setPortalSurfaces($game->portalSurfaces);
        if (($built['markTris'] ?? []) !== []) {
            fwrite(STDERR, sprintf("mark tris: %d (R_MarkFragments)\n", count($built['markTris'])));
        }
        if ($game->portalSurfaces !== []) {
            fwrite(STDERR, sprintf("portal surfaces: %d (mirror/remote underlay)\n", count($game->portalSurfaces)));
        }
        $lightGrid = LightGrid::load($bspMap, $lighting->mapOverBrightBits, $lighting->overbrightBits);
        $gl->setLightGrid($lightGrid->valid ? $lightGrid : null);
        if ($lightGrid->valid) {
            fwrite(STDERR, sprintf(
                "lightgrid: %dx%dx%d samples (size %.0f/%.0f/%.0f)\n",
                $lightGrid->bounds[0],
                $lightGrid->bounds[1],
                $lightGrid->bounds[2],
                $lightGrid->size[0],
                $lightGrid->size[1],
                $lightGrid->size[2],
            ));
        }
        $gl->viewport(0, 0, $sdl->getWidth(), $sdl->getHeight());
        $gl->enableDepthTest();
        $gl->uploadWorld(
            $built['batches'],
            $built['lightmaps'],
            $materials,
            $drawMode !== 'no_textures',
            $built['flares'] ?? [],
            $built['skyShader'] ?? null,
            $built['moverBatches'] ?? [],
        );

        $viewWeapons = ViewWeapon::loadKnown($fs);
        /** @var array<int, array{gun: array<string,int>, barrel: array<string,int>, flash: array<string,int>}> */
        $viewTexByWeapon = [];
        foreach ($viewWeapons as $wp => $vw) {
            $tex = ['gun' => [], 'barrel' => [], 'flash' => []];
            if ($vw->gun !== null) {
                $tex['gun'] = $gl->ensureMd3Textures($vw->gun, $materials);
                $gb = $vw->gun->bounds(0);
                fwrite(STDERR, sprintf(
                    "view weapon[%d]: gun frames=%d size=(%.1f %.1f %.1f) hand frames=%d tags=%d\n",
                    $wp,
                    $vw->gun->numFrames,
                    $gb['size'][0],
                    $gb['size'][1],
                    $gb['size'][2],
                    $vw->hand?->numFrames ?? 0,
                    $vw->hand?->numTags ?? 0,
                ));
            }
            if ($vw->barrel !== null) {
                $tex['barrel'] = $gl->ensureMd3Textures($vw->barrel, $materials);
            }
            if ($vw->flash !== null) {
                $tex['flash'] = $gl->ensureMd3Textures($vw->flash, $materials);
            }
            $viewTexByWeapon[$wp] = $tex;
        }
        if ($noViewWeapon) {
            fwrite(STDERR, "view weapon: disabled (--no-view-weapon)\n");
        }
        if ($weaponOnly) {
            fwrite(STDERR, "view weapon: weapon-only mode\n");
        }

        try {
            $ring = Md3Model::load($fs, 'models/weaphits/ring02.md3');
            $gl->setImpactRingFlash($ring, $gl->ensureMd3Textures($ring, $materials));
        } catch (\Throwable $e) {
            fwrite(STDERR, 'impact ring02.md3: ' . $e->getMessage() . "\n");
        }
        try {
            $bulletFlash = Md3Model::load($fs, 'models/weaphits/bullet.md3');
            $gl->setImpactBulletFlash($bulletFlash, $gl->ensureMd3Textures($bulletFlash, $materials));
        } catch (\Throwable $e) {
            fwrite(STDERR, 'impact bullet.md3: ' . $e->getMessage() . "\n");
        }
        try {
            $telep = Md3Model::load($fs, 'models/misc/telep.md3');
            $gl->setTeleportEffect($telep, $gl->ensureMd3Textures($telep, $materials));
        } catch (\Throwable $e) {
            fwrite(STDERR, 'telep.md3: ' . $e->getMessage() . "\n");
        }
        try {
            $mShell = Md3Model::load($fs, 'models/weapons2/shells/m_shell.md3');
            $sShell = Md3Model::load($fs, 'models/weapons2/shells/s_shell.md3');
            $gl->setBrassModels(
                $mShell,
                $gl->ensureMd3Textures($mShell, $materials),
                $sShell,
                $gl->ensureMd3Textures($sShell, $materials),
            );
        } catch (\Throwable $e) {
            fwrite(STDERR, 'brass shells: ' . $e->getMessage() . "\n");
        }

        /** @var array<string, array<string, int>> */
        $itemTexCache = [];
        /** @var array<string, array<string, int>> */
        $itemSphereTexCache = [];
        foreach ($game->items as $it) {
            if ($it['md3'] !== null) {
                $itemTexCache[$it['classname']] = $gl->ensureMd3Textures($it['md3'], $materials);
            }
            if (($it['md3Sphere'] ?? null) !== null) {
                $itemSphereTexCache[$it['classname']] = $gl->ensureMd3Textures($it['md3Sphere'], $materials);
            }
        }

        /** @var array<string, Md3Model> */
        $moverModel2 = [];
        /** @var array<string, array<string, int>> */
        $moverModel2Tex = [];
        foreach ($game->movers->movers as $m) {
            $path = $m['model2'] ?? null;
            if ($path === null || isset($moverModel2[$path])) {
                continue;
            }
            try {
                $md3 = Md3Model::load($fs, $path);
                $moverModel2[$path] = $md3;
                $moverModel2Tex[$path] = $gl->ensureMd3Textures($md3, $materials);
            } catch (\Throwable $e) {
                fwrite(STDERR, 'mover model2 ' . $path . ': ' . $e->getMessage() . "\n");
            }
        }

        // CG_DrawStatusBarHead — default sarge head + skin
        try {
            $hudHead = Md3Model::load($fs, 'models/players/sarge/head.md3');
            $skinMap = $this->parseMd3Skin($fs->readText('models/players/sarge/head_default.skin'));
            foreach ($hudHead->surfaces as $i => $surf) {
                $key = $surf['name'];
                if (!isset($skinMap[$key]) || $skinMap[$key] === '') {
                    continue;
                }
                $path = $skinMap[$key];
                foreach (['.tga', '.jpg', '.jpeg', '.png'] as $ext) {
                    if (str_ends_with(strtolower($path), $ext)) {
                        $path = substr($path, 0, -strlen($ext));
                        break;
                    }
                }
                $hudHead->surfaces[$i]['shader'] = $path;
            }
            $headTex = $gl->ensureMd3Textures($hudHead, $materials);
            $gl->setHudHead($hudHead, $headTex);
            fwrite(STDERR, "hud head: models/players/sarge/head.md3\n");
        } catch (\Throwable $e) {
            fwrite(STDERR, 'hud head: ' . $e->getMessage() . "\n");
        }

        // CG_DrawStatusBar 3D ammo + armor icons
        $ammoPaths = [
            'machinegun' => 'models/powerups/ammo/machinegunam.md3',
            'shotgun' => 'models/powerups/ammo/shotgunam.md3',
            'plasma' => 'models/powerups/ammo/plasmaam.md3',
        ];
        foreach ($ammoPaths as $wpn => $path) {
            try {
                if (!$fs->exists($path)) {
                    continue;
                }
                $m = Md3Model::load($fs, $path);
                $gl->setHudAmmo($wpn, $m, $gl->ensureMd3Textures($m, $materials));
                fwrite(STDERR, "hud ammo[$wpn]: $path\n");
            } catch (\Throwable $e) {
                fwrite(STDERR, "hud ammo[$wpn]: " . $e->getMessage() . "\n");
            }
        }
        try {
            $armorPath = 'models/powerups/armor/armor_yel.md3';
            if ($fs->exists($armorPath)) {
                $am = Md3Model::load($fs, $armorPath);
                $gl->setHudArmor($am, $gl->ensureMd3Textures($am, $materials));
                fwrite(STDERR, "hud armor: $armorPath\n");
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'hud armor: ' . $e->getMessage() . "\n");
        }

        $last = $sdl->ticks();
        $running = true;
        $mouseSens = 0.022 * 5.0; // m_yaw * sensitivity (defaults 0.022 × 5)
        $deathTimer = -1.0;
        $frame = 0;
        $gameTime = 0.0;
        $zoomed = false;
        $zoomTime = -999.0;

        while ($running) {
            $now = $sdl->ticks();
            $dt = max(0.001, min(0.05, ($now - $last) / 1000.0));
            $last = $now;
            $frame++;
            $gameTime += $dt;

            $ev = $sdl->pollEvents();
            if ($ev['quit'] || $ev['escape']) {
                $running = false;
            }
            $zoomHeld = !empty($ev['zoom']);
            if ($zoomHeld && !$zoomed) {
                $zoomed = true;
                $zoomTime = $gameTime;
            } elseif (!$zoomHeld && $zoomed) {
                $zoomed = false;
                $zoomTime = $gameTime;
            }
            $aspectNow = $sdl->getWidth() / max(1, $sdl->getHeight());
            $fovX = 90.0;
            $zoomFov = 22.5; // cg_zoomfov
            $zf = ($gameTime - $zoomTime) / 0.15; // ZOOM_TIME 150ms
            if ($zoomed) {
                $fovX = $zf >= 1.0 ? $zoomFov : $fovX + $zf * ($zoomFov - $fovX);
            } elseif ($zf < 1.0) {
                $fovX = $zoomFov + $zf * (90.0 - $zoomFov);
            }
            $fovYLook = FlyCamera::verticalFovFromHorizontal($fovX, $aspectNow);
            $mx = (float) $ev['mouseDx'];
            $my = (float) $ev['mouseDy'];
            if ($zoomed) {
                $zs = $fovYLook / 75.0; // cg.zoomSensitivity
                $mx *= $zs;
                $my *= $zs;
            }

            if ($freecam) {
                // Match play: m_yaw * sensitivity (0.022×5 deg → rad)
                $cam->look($mx, $my, deg2rad($mouseSens));
                $cam->move($ev['keysDown'], $dt);
                if ($debugSurfacePick && in_array('p', $ev['keysPressed'] ?? [], true)) {
                    $this->dumpSurfacePick(
                        [$cam->x, $cam->y, $cam->z],
                        [-rad2deg($cam->pitch), rad2deg($cam->yaw), 0.0],
                        $cm,
                        $built['batches'],
                        $shaderParser,
                        $materials,
                        $vis,
                    );
                }
            } else {
                $suppressPainSound = false;
                if ($player->alive) {
                    $keys = $ev['keysDown'];
                    $pressed = $ev['keysPressed'] ?? [];
                    $strafe = !empty($ev['strafe']);
                    if ($fixedView !== null) {
                        $player->viewangles[1] = $fixedView[0];
                        $player->viewangles[0] = $fixedView[1];
                    } elseif (!$strafe) {
                        $player->viewangles[1] -= $mx * $mouseSens;
                        $player->viewangles[0] += $my * $mouseSens;
                        $player->viewangles[0] = max(-89.0, min(89.0, $player->viewangles[0]));
                        // cl_yawspeed 140; +speed (Shift) × cl_anglespeedkey 1.5
                        $yawRate = 140.0 * $dt * (!empty($keys['shift']) ? 1.5 : 1.0);
                        if (!empty($keys['left'])) {
                            $player->viewangles[1] += $yawRate;
                        }
                        if (!empty($keys['right'])) {
                            $player->viewangles[1] -= $yawRate;
                        }
                    }
                    if ($fixedView === null) {
                        // cl_pitchspeed 140; +lookup/+lookdown even while +strafe
                        $pitchRate = 140.0 * $dt * (!empty($keys['shift']) ? 1.5 : 1.0);
                        if (!empty($keys['pgdn'])) {
                            $player->viewangles[0] -= $pitchRate; // +lookup
                        }
                        if (!empty($keys['del'])) {
                            $player->viewangles[0] += $pitchRate; // +lookdown
                        }
                        if (in_array('end', $pressed, true)) {
                            $player->viewangles[0] = 0.0; // centerview
                        }
                        $player->viewangles[0] = max(-89.0, min(89.0, $player->viewangles[0]));
                    }

                    // cl_run 1: Shift → BUTTON_WALKING / movespeed 64; else run 127
                    $moveSpeed = !empty($keys['shift']) ? 64 : 127;
                    $buttons = !empty($keys['shift']) ? Usercmd::BUTTON_WALKING : 0;
                    if (!empty($ev['fire'])) {
                        $buttons |= Usercmd::BUTTON_ATTACK;
                    }
                    $forward = 0;
                    $right = 0;
                    $up = 0;
                    if (!empty($keys['w']) || !empty($keys['up'])) {
                        $forward += $moveSpeed;
                    }
                    if (!empty($keys['s']) || !empty($keys['down'])) {
                        $forward -= $moveSpeed;
                    }
                    if (!empty($keys['d'])) {
                        $right += $moveSpeed;
                    }
                    if (!empty($keys['a'])) {
                        $right -= $moveSpeed;
                    }
                    if ($strafe) {
                        // in_strafe: arrows → sidemove; mouse → m_side/m_forward × sensitivity
                        if (!empty($keys['left'])) {
                            $right -= $moveSpeed;
                        }
                        if (!empty($keys['right'])) {
                            $right += $moveSpeed;
                        }
                        $sens = 5.0;
                        $right += (int) round(0.25 * $sens * $mx);
                        $forward -= (int) round(0.25 * $sens * $my);
                        $right = max(-127, min(127, $right));
                        $forward = max(-127, min(127, $forward));
                    }
                    if (!empty($keys['space'])) {
                        $up = 127;
                    }
                    if (!empty($keys['c'])) {
                        $up = -127; // +movedown / crouch
                    }
                    $weaponKeys = [
                        '1' => Player::WP_GAUNTLET,
                        '2' => Player::WP_MACHINEGUN,
                        '3' => Player::WP_SHOTGUN,
                        '8' => Player::WP_PLASMA,
                    ];
                    foreach ($weaponKeys as $key => $wp) {
                        if (!in_array($key, $pressed, true)) {
                            continue;
                        }
                        // CG_Weapon_f — ignore if STAT_WEAPONS bit unset (no gauntlet here)
                        if (($player->weapons & (1 << $wp)) === 0) {
                            continue;
                        }
                        $game->weaponState->showSelect($wp, $gameTime);
                        $game->weaponState->wantWeapon($player, $wp);
                    }
                    if (!empty($ev['weapnext']) || in_array('rbracket', $pressed, true)) {
                        $game->weaponState->cycleWeapon($player, 1, $gameTime);
                    }
                    if (!empty($ev['weapprev']) || in_array('lbracket', $pressed, true)) {
                        $game->weaponState->cycleWeapon($player, -1, $gameTime);
                    }
                    if ($game->weaponState->justChangedWeapon) {
                        $sound->play('change');
                        $game->weaponState->selectTime = $gameTime;
                        $game->weaponState->justChangedWeapon = false;
                    }
                    if (!empty($keys['k'])) {
                        $player->applyDamage(40);
                        fwrite(STDERR, "hurt hp={$player->health}\n");
                    }
                    if ($debugSurfacePick && in_array('p', $pressed, true)) {
                        $this->dumpSurfacePick(
                            $player->eyeOrigin(),
                            $player->viewangles,
                            $cm,
                            $built['batches'],
                            $shaderParser,
                            $materials,
                            $vis,
                        );
                    }

                    $msec = (int) max(1, min(50, (int) round($dt * 1000)));
                    $cm->setEntityBoxes($game->movers->solids);
                    $pmove->move($player, new Usercmd(
                        serverTime: $player->commandTime + $msec,
                        forwardmove: $forward,
                        rightmove: $right,
                        upmove: $up,
                        buttons: $buttons,
                        angles: $player->viewangles,
                    ));
                    if ($player->justJumped) {
                        $sound->play('jump');
                        $player->justJumped = false;
                    }
                    if ($player->justLanded) {
                        // EV_FALL_SHORT → land; EV_FALL_MEDIUM → *pain100_1 + 5; EV_FALL_FAR → *fall1 + 10
                        // g_active.c ClientEvents — pain_debounce so G_Damage doesn't add another shout
                        if ($player->landChange <= -24.0) {
                            $sound->play('fall');
                            $player->applyDamage(10);
                            $player->painDebounceTime = $gameTime + 0.2;
                            $suppressPainSound = true;
                        } elseif ($player->landChange <= -16.0) {
                            $sound->play('pain100');
                            $player->applyDamage(5);
                            $player->painDebounceTime = $gameTime + 0.2;
                            $suppressPainSound = true;
                        } else {
                            $sound->play('land');
                        }
                        $player->justLanded = false;
                    }
                    if ($player->justFootstep) {
                        $sound->play($player->justFootstepMetal ? 'footstep_metal' : 'footstep');
                        $player->justFootstep = false;
                        $player->justFootstepMetal = false;
                    }
                    if ($player->justStep > 0) {
                        // EV_STEP_* → cg.stepChange / stepTime (cg_event.c)
                        $nowMs = (float) $player->commandTime;
                        $delta = $nowMs - $player->stepTime;
                        $oldStep = 0.0;
                        if ($delta < ViewOffset::STEP_TIME) {
                            $oldStep = $player->stepChange
                                * (ViewOffset::STEP_TIME - $delta) / ViewOffset::STEP_TIME;
                        }
                        $player->stepChange = min(
                            ViewOffset::MAX_STEP_CHANGE,
                            $oldStep + (float) $player->justStep,
                        );
                        $player->stepTime = $nowMs;
                        $player->justStep = 0;
                    }

                    foreach ($game->update($player, $dt, $cm) as $evMsg) {
                        fwrite(STDERR, $evMsg . "\n");
                        if (str_starts_with($evMsg, 'pickup')) {
                            $cn = trim(substr($evMsg, strlen('pickup')));
                            $sound->playPickup($cn);
                            if ($cn !== '') {
                                $gl->notifyItemPickup($cn, $gameTime);
                            }
                        }
                        if (str_starts_with($evMsg, 'respawn ')) {
                            $sound->play('item_respawn');
                        }
                        if (str_starts_with($evMsg, 'speaker ')) {
                            $sound->playPath(trim(substr($evMsg, strlen('speaker '))));
                        }
                        if ($evMsg === 'door_start') {
                            $sound->play('door_start');
                        }
                        if ($evMsg === 'door_end') {
                            $sound->play('door_end');
                        }
                        if (str_starts_with($evMsg, 'teleport_out ')
                            || str_starts_with($evMsg, 'teleport_in ')
                            || str_starts_with($evMsg, 'teleport ')) {
                            if (str_starts_with($evMsg, 'teleport_out ')) {
                                $sound->play('teleport_out');
                            } else {
                                $sound->play('teleport_in');
                            }
                            if (preg_match('/^teleport_(?:out|in) ([\d.\-]+) ([\d.\-]+) ([\d.\-]+)/', $evMsg, $m)) {
                                $gl->spawnTeleportEffect([(float) $m[1], (float) $m[2], (float) $m[3]], $gameTime);
                            }
                        }
                    }
                    $cm->setEntityBoxes($game->movers->solids);
                    if ($game->weaponState->justChangedWeapon) {
                        $sound->play('change');
                        $game->weaponState->selectTime = $gameTime;
                        $game->weaponState->justChangedWeapon = false;
                    }
                    foreach ($game->pendingImpacts as $imp) {
                        $sound->play('plasma_hit');
                        $gl->addImpact(
                            $imp['origin'],
                            $imp['normal'],
                            $gameTime + $imp['life'],
                            $imp['color'],
                            true,
                            'plasma',
                        );
                    }
                    $game->pendingImpacts = [];

                    $viewWeapon = $viewWeapons[$player->weapon] ?? null;
                    if ($viewWeapon !== null) {
                        $viewWeapon->update($player, $game->weaponState, $dt, $player->onGround);
                    }

                    if (!empty($ev['fire'])) {
                        $hit = $game->fireWeapon($player, $cm);
                        if ($hit !== null) {
                            if (($hit['kind'] ?? '') === 'noammo') {
                                // EV_NOAMMO — no click (cg_event.c StartSound commented out); CG_OutOfAmmoChange
                                $game->weaponState->outOfAmmoChange($player, $gameTime);
                            } else {
                            $sfx = match ($hit['kind'] ?? '') {
                                'shotgun' => 'fire_shotgun',
                                'plasma' => 'fire_plasma',
                                default => 'fire',
                            };
                            $sound->play($sfx);
                            $viewWeapon?->onFire($gameTime);
                            $hitKind = $hit['kind'] ?? '';
                            if ($hitKind === 'shotgun') {
                                $gl->spawnBrass('sg', $player->origin, $player->viewangles, $gameTime);
                                // CG_ShotgunFire: puff at CalcMuzzlePoint(+14) + 32 forward
                                $eye = $player->eyeOrigin();
                                [$fwd] = QuakeMath::angleVectors($player->viewangles);
                                $gl->spawnSmokePuff(
                                    [
                                        $eye[0] + $fwd[0] * 46.0,
                                        $eye[1] + $fwd[1] * 46.0,
                                        $eye[2] + $fwd[2] * 46.0,
                                    ],
                                    [0.0, 0.0, 8.0],
                                    32.0,
                                    0.33,
                                    0.9,
                                );
                                // WP_SHOTGUN MissileHitWall: sfx=0 (visual only)
                                if (!empty($hit['impacts'])) {
                                    foreach ($hit['impacts'] as $pellet) {
                                        $gl->addImpact(
                                            $pellet['origin'],
                                            $pellet['normal'],
                                            $gameTime + 0.6,
                                            [1.0, 0.9, 0.55],
                                            true,
                                            'shotgun',
                                        );
                                    }
                                }
                            } elseif ($hitKind === 'plasma') {
                                // explosion when bolt hits — pendingImpacts path
                            } elseif (!empty($hit['end'])) {
                                $gl->spawnBrass('mg', $player->origin, $player->viewangles, $gameTime);
                                // CG_Bullet: ~40% cg_tracerchance streak from muzzle
                                if ((mt_rand() / mt_getrandmax()) < 0.4) {
                                    [$fwd] = QuakeMath::angleVectors($player->viewangles);
                                    $eye = $player->eyeOrigin();
                                    $muzzle = [
                                        $eye[0] + $fwd[0] * 14.0,
                                        $eye[1] + $fwd[1] * 14.0,
                                        $eye[2] + $fwd[2] * 14.0,
                                    ];
                                    if ($gl->spawnTracer($muzzle, $hit['end'])) {
                                        $sound->play('tracer');
                                    }
                                }
                                // Bullet_Fire: SURF_NOIMPACT (sky) / full miss → no ricochet/impact
                                $noImpact = (($hit['fraction'] ?? 0.0) >= 1.0)
                                    || (((int) ($hit['surfaceFlags'] ?? 0) & MarkFragments::SURF_NOIMPACT) !== 0);
                                if (!$noImpact) {
                                    $sound->play('ricochet');
                                    $n = $hit['normal'] ?? [0.0, 0.0, 1.0];
                                    if ($n === null) {
                                        $n = [0.0, 0.0, 1.0];
                                    }
                                    $gl->addImpact(
                                        $hit['end'],
                                        $n,
                                        $gameTime + 0.6,
                                        [1.0, 0.9, 0.55],
                                        true,
                                        'bullet',
                                    );
                                }
                            }
                            if (!empty($hit['dummyHit'])) {
                                fwrite(STDERR, "hit dummy\n");
                            }
                            }
                        }
                    }
                } else {
                    // Dead: keep mouse look + corpse pmove (knockback arcs); no WASD/jump/fire
                    if ($fixedView !== null) {
                        $player->viewangles[1] = $fixedView[0];
                        $player->viewangles[0] = $fixedView[1];
                    } elseif (empty($ev['strafe'])) {
                        $player->viewangles[1] -= $mx * $mouseSens;
                        $player->viewangles[0] += $my * $mouseSens;
                        $player->viewangles[0] = max(-89.0, min(89.0, $player->viewangles[0]));
                    }
                    $deadKeys = $ev['keysDown'];
                    $deadPressed = $ev['keysPressed'] ?? [];
                    if (empty($ev['strafe'])) {
                        $yawRate = 140.0 * $dt * (!empty($deadKeys['shift']) ? 1.5 : 1.0);
                        if (!empty($deadKeys['left'])) {
                            $player->viewangles[1] += $yawRate;
                        }
                        if (!empty($deadKeys['right'])) {
                            $player->viewangles[1] -= $yawRate;
                        }
                    }
                    if ($fixedView === null) {
                        $pitchRate = 140.0 * $dt * (!empty($deadKeys['shift']) ? 1.5 : 1.0);
                        if (!empty($deadKeys['pgdn'])) {
                            $player->viewangles[0] -= $pitchRate;
                        }
                        if (!empty($deadKeys['del'])) {
                            $player->viewangles[0] += $pitchRate;
                        }
                        if (in_array('end', $deadPressed, true)) {
                            $player->viewangles[0] = 0.0;
                        }
                        $player->viewangles[0] = max(-89.0, min(89.0, $player->viewangles[0]));
                    }
                    $msec = (int) max(1, min(50, (int) round($dt * 1000)));
                    $cm->setEntityBoxes($game->movers->solids);
                    $pmove->move($player, new Usercmd(
                        serverTime: $player->commandTime + $msec,
                        forwardmove: 0,
                        rightmove: 0,
                        upmove: 0,
                        angles: $player->viewangles,
                    ));
                    if ($deathTimer < 0) {
                        // player_die: respawnTime = level.time + 1700
                        $deathTimer = 0.0;
                        $game->tossClientItems($player, $cm);
                        $sound->playDeath();
                        fwrite(STDERR, "YOU DIED — fire/R after 1.7s to respawn\n");
                    }
                    $deathTimer += $dt;
                    $pressed = $ev['keysPressed'] ?? [];
                    $wantRespawn = !empty($ev['fire'])
                        || !empty($ev['keysDown']['r'])
                        || in_array('r', $pressed, true);
                    // g_forcerespawn default 20s after respawnTime (= 21.7s dead)
                    $forceRespawn = $deathTimer >= 21.7;
                    if ($deathTimer >= 1.7 && ($wantRespawn || $forceRespawn)) {
                        $sp = SpawnPoint::selectRandomFurthest($spawns, $player->origin);
                        $o = $sp->playerOrigin();
                        $d = $cm->trace($o, [$o[0], $o[1], $o[2] - 64], Player::MINS, Player::MAXS);
                        $player->respawn($d->fraction < 1 ? $d->endPos : $o, $sp->angles);
                        $game->weaponState->resetSpawn();
                        $gl->spawnTeleportEffect($player->origin, $gameTime);
                        $sound->play('teleport_in');
                        $deathTimer = -1;
                        fwrite(STDERR, "respawn spawn #{$sp->index}\n");
                    }
                }

                $eye = $player->eyeOrigin();
                // Stamp damage time before view offset so kick starts this frame
                if ($player->damagePending) {
                    $player->damageTime = $gameTime;
                    // P_DamageFeedback: EV_PAIN at most every 700ms (client playPain also 500ms)
                    if ($player->alive && !$suppressPainSound && $gameTime >= $player->painDebounceTime) {
                        $player->painDebounceTime = $gameTime + 0.7;
                        $sound->playPain($player->health, $gameTime);
                    }
                    $player->damagePending = false;
                }
                $viewOff = ViewOffset::offsetFirstPerson(
                    $player,
                    $eye,
                    $player->viewangles,
                    $gameTime,
                    (float) $player->commandTime,
                );
                $cam->x = $viewOff['origin'][0];
                $cam->y = $viewOff['origin'][1];
                $cam->z = $viewOff['origin'][2];
                $cam->yaw = deg2rad($viewOff['angles'][1]);
                $cam->pitch = -deg2rad($viewOff['angles'][0]);
            }

            $viewOrigin = [$cam->x, $cam->y, $cam->z];
            $plasmaActive = false;
            foreach ($game->projectiles as $proj) {
                if ($proj->alive) {
                    $plasmaActive = true;
                    break;
                }
            }
            $sound->updateLoops($game->loopSpeakers, $viewOrigin, $gameTime, $plasmaActive);
            $viewCluster = -1;
            if ($usePvs && $vis !== null) {
                $leaf = $cm->pointLeafnum($viewOrigin);
                $viewCluster = $cm->leafCluster($leaf);
            }

            $aspect = $sdl->getWidth() / max(1, $sdl->getHeight());
            $gl->viewport(0, 0, $sdl->getWidth(), $sdl->getHeight());
            if ($weaponOnly) {
                $gl->clearColor(0.0, 0.0, 0.0, 1.0);
            } else {
                $gl->clearColor(0.0, 0.0, 0.0, 1.0);
            }
            $gl->clear();
            // cg_fov 90 (or cg_zoomfov 22.5 while +zoom); OpenGL wants vertical (CG_CalcFov)
            $fovY = FlyCamera::verticalFovFromHorizontal($fovX, $aspect);
            $gl->setFovY($fovY);
            $gl->loadMatrix(Gl::GL_PROJECTION, $cam->projectionMatrix($aspect, $fovY));
            $gl->loadMatrix(Gl::GL_MODELVIEW, $cam->viewMatrix());
            $gl->setDamageFeedback(
                $player->damageTime,
                $player->damageValue,
                $player->damageX,
                $player->damageY,
            );
            if (!$weaponOnly) {
                $gl->drawWorld($drawMode, $vis, $viewCluster, $usePvs, $viewOrigin, $gameTime);
                // CG_Mover SOLID_BMODEL — doors translate with collision origin
                foreach ($game->movers->movers as $m) {
                    if (($m['type'] ?? '') !== 'door' || ($m['modelIndex'] ?? 0) <= 0) {
                        continue;
                    }
                    $gl->drawInlineMover(
                        (int) $m['modelIndex'],
                        [
                            $m['origin'][0] - $m['pos1'][0],
                            $m['origin'][1] - $m['pos1'][1],
                            $m['origin'][2] - $m['pos1'][2],
                        ],
                        $drawMode,
                        $gameTime,
                    );
                }
                if ($player->alive) {
                    // CG_PlayerShadow — temporary markShadow under feet
                    $gl->drawPlayerShadow($player->origin, $player->viewangles[1]);
                }

                // MD3 pickups bob + autorotate (cg_ents.c CG_Item / autoAngles)
                foreach ($game->items as $entityNum => $it) {
                    if (!$it['active'] || $it['md3'] === null) {
                        continue;
                    }
                    $cgTimeMs = (int) ($gameTime * 1000.0);
                    $scale = 0.005 + $entityNum * 0.00001;
                    $bob = 4.0 + cos(($cgTimeMs + 1000) * $scale) * 4.0;
                    // IT_HEALTH = autoAnglesFast; else autoAngles (no spawn yaw)
                    $yaw = str_starts_with($it['classname'], 'item_health')
                        ? ($cgTimeMs & 1023) * 360.0 / 1024.0
                        : ($cgTimeMs & 2047) * 360.0 / 2048.0;
                    $tex = $itemTexCache[$it['classname']] ?? [];
                    $drawOrigin = $it['origin'];
                    $drawBob = $bob;
                    $drawScale = 1.0;
                    if (str_starts_with($it['classname'], 'weapon_')) {
                        // CG_Item IT_WEAPON: midpoint·axis offset, +8 Z, then 1.5× axis scale
                        $b = $it['md3']->bounds(0);
                        $mid = [
                            $b['mins'][0] + 0.5 * ($b['maxs'][0] - $b['mins'][0]),
                            $b['mins'][1] + 0.5 * ($b['maxs'][1] - $b['mins'][1]),
                            $b['mins'][2] + 0.5 * ($b['maxs'][2] - $b['mins'][2]),
                        ];
                        $axis = QuakeMath::anglesToAxis([0.0, $yaw, 0.0]);
                        $drawOrigin = [
                            $it['origin'][0],
                            $it['origin'][1],
                            $it['origin'][2] + $bob,
                        ];
                        $drawOrigin[0] -= $mid[0] * $axis[0][0] + $mid[1] * $axis[1][0] + $mid[2] * $axis[2][0];
                        $drawOrigin[1] -= $mid[0] * $axis[0][1] + $mid[1] * $axis[1][1] + $mid[2] * $axis[2][1];
                        $drawOrigin[2] -= $mid[0] * $axis[0][2] + $mid[1] * $axis[1][2] + $mid[2] * $axis[2][2];
                        $drawOrigin[2] += 8.0;
                        $drawBob = 0.0;
                        $drawScale = 1.5;
                    }
                    // ITEM_SCALEUP_TIME 1000ms (cg_ents.c) — grow from 0 after spawn/respawn
                    $frac = 1.0;
                    $msec = ($gameTime - (float) ($it['miscTime'] ?? 0.0)) * 1000.0;
                    if ($msec >= 0.0 && $msec < 1000.0) {
                        $frac = $msec / 1000.0;
                    }
                    $drawScale *= $frac;
                    $gl->drawMd3($it['md3'], $drawOrigin, $yaw, $drawBob, $tex, 0, null, $drawScale);
                    // IT_HEALTH models[1] companion sphere — identity axis (cg_ents.c)
                    if (($it['md3Sphere'] ?? null) !== null) {
                        $sphereTex = $itemSphereTexCache[$it['classname']] ?? [];
                        $gl->drawMd3($it['md3Sphere'], $it['origin'], 0.0, $bob, $sphereTex, 0, null, $frac);
                    }
                }
                // CG_Mover modelindex2 — spinning mapobjects (fembotbig / xray on q3dm0)
                foreach ($game->movers->movers as $m) {
                    $path = $m['model2'] ?? null;
                    if ($path === null || !isset($moverModel2[$path])) {
                        continue;
                    }
                    $gl->drawMd3Oriented(
                        $moverModel2[$path],
                        $m['origin'],
                        QuakeMath::anglesToAxis($m['angles']),
                        $moverModel2Tex[$path] ?? [],
                    );
                }
                foreach ($game->projectiles as $bolt) {
                    if ($bolt->alive) {
                        $gl->drawPlasmaBolt($bolt->origin);
                        // cg_oldPlasma 1 (default): no CG_PlasmaTrail
                    }
                }
            }

            // First-person weapon (CG_AddViewWeapon hierarchy) — MG / SG / plasma
            $viewWeapon = $viewWeapons[$player->weapon] ?? null;
            if (!$freecam && !$noViewWeapon && $player->alive && $viewWeapon !== null && $viewWeapon->gun !== null) {
                // Use offset view so gun matches camera bob/land/damage kick
                $eye = isset($viewOff) ? $viewOff['origin'] : $player->eyeOrigin();
                $angles = isset($viewOff) ? $viewOff['angles'] : $player->viewangles;
                $poses = $viewWeapon->buildPoses(
                    $eye,
                    $angles,
                    $player,
                    $player->commandTime / 1000.0,
                    $game->weaponState,
                );
                if ($weaponDebug && ($frame % 60) === 1) {
                    $d = $poses['debug'];
                    fwrite(STDERR, sprintf(
                        "weapon-debug wp=%d handFrame=%d tag_weapon=%s gunOrigin=%s gunSize=%s\n",
                        $player->weapon,
                        $d['handFrame'],
                        json_encode($d['tag_weapon']),
                        json_encode($d['gunOrigin']),
                        json_encode($d['gunBounds']['size'] ?? null),
                    ));
                }
                // lightingOrigin = player origin (CG_AddPlayerWeapon copies parent->lightingOrigin)
                $gl->drawViewWeaponHierarchy(
                    $poses,
                    [
                        'gun' => $viewWeapon->gun,
                        'barrel' => $viewWeapon->barrel,
                        'flash' => $viewWeapon->flash,
                    ],
                    $viewTexByWeapon[$player->weapon] ?? ['gun' => [], 'barrel' => [], 'flash' => []],
                    !$noMuzzleFlash && $viewWeapon->flashing($gameTime),
                    $weaponWireframe,
                    true,
                    [$player->origin[0], $player->origin[1], $player->origin[2]],
                );
            }

            if ($showEntities && !$weaponOnly) {
                foreach ($game->items as $it) {
                    if (!$it['active']) {
                        continue;
                    }
                    $gl->drawDebugBox($it['mins'], $it['maxs'], 1.0, 1.0, 0.2);
                }
                foreach ($game->dummies as $d) {
                    if ($d['alive']) {
                        $gl->drawDebugBox($d['mins'], $d['maxs'], 1.0, 0.2, 0.2);
                    }
                }
                foreach ($game->movers->movers as $m) {
                    $gl->drawDebugBox($m['mins'], $m['maxs'], 0.2, 1.0, 0.4);
                }
            }

            if (!$freecam) {
                $ammoWarning = $player->lowAmmoWarning();
                if ($ammoWarning !== $prevAmmoWarning) {
                    $sound->play('noammo');
                    $prevAmmoWarning = $ammoWarning;
                }
                $gl->drawHud(
                    $sdl->getWidth(),
                    $sdl->getHeight(),
                    $player->health,
                    $player->armor,
                    $player->currentAmmo(),
                    $player->weaponName(),
                    $ammoWarning,
                    $player,
                    $game->weaponState,
                );
            }

            $sdl->swap();

            if ($profile && ($frame % 60) === 0) {
                fwrite(STDERR, sprintf(
                    "profile: draws=%d binds=%d culled=%d hp=%d armor=%d ammo=%d weapon=%s\n",
                    $gl->lastDrawCalls(),
                    $gl->lastBinds(),
                    $gl->lastCulled(),
                    $player->health,
                    $player->armor,
                    $player->currentAmmo(),
                    $player->weaponName(),
                ));
            }
            if ($maxFrames > 0 && $frame >= $maxFrames) {
                $running = false;
            }
        }

        $sound->stopAllLoops();
        $gl->destroyWorld();
        $sdl->shutdown();

        return 0;
    }

    /**
     * Rank shaders used by a map vs unimplemented directives.
     *
     * @param list<string> $argv
     */
    /**
     * P1 — audit every stage map referenced by q3dm0 shaders.
     * @param list<string> $argv
     */
    private function cmdShaderMissing(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $mapPath = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $mapPath = $arg;
                continue;
            }
            throw new \RuntimeException('unknown shader:missing option: ' . $arg);
        }
        if ($basepath === null || $mapPath === null) {
            fwrite(STDERR, "usage: shader:missing --basepath=/path maps/q3dm0.bsp\n");
            return 1;
        }

        $bspMap = $this->loadBspMap($mapPath, $basepath);
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $parser = new ShaderParser($fs);
        $mat = new MaterialResolver($fs);
        $mat->setParser($parser);
        $ex = new BspMeshExtractor();
        $shaders = $ex->loadShaders($bspMap);
        $surfaces = $ex->loadSurfaces($bspMap);

        /** @var array<string, int> $counts */
        $counts = [];
        foreach ($surfaces as $surf) {
            $name = $shaders[$surf->shaderNum]->shader ?? 'unknown';
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }
        arsort($counts);

        fwrite(STDOUT, sprintf(
            "%-48s %-6s %-40s %-40s %s\n",
            'SHADER',
            'STAGE',
            'REQUESTED',
            'RESOLVED',
            'STATUS',
        ));
        $missing = 0;
        $rows = 0;
        foreach ($counts as $name => $n) {
            $scripted = $parser->isScripted($name);
            $sh = $parser->get($name);
            if ($sh->stages === []) {
                $info = $mat->classifyMap($name);
                $status = $info['status'];
                if (!$scripted && $status === 'MISSING') {
                    $status = 'MISSING';
                } elseif ($scripted) {
                    $status = 'FOUND_SHADER';
                }
                fwrite(STDOUT, sprintf(
                    "%-48s %-6s %-40s %-40s %s\n",
                    $name,
                    '-',
                    $name,
                    $info['resolved'] ?? '-',
                    $status,
                ));
                $rows++;
                if ($status === 'MISSING') {
                    $missing++;
                }
                continue;
            }
            foreach ($sh->stages as $si => $st) {
                $maps = $st->animFrames !== [] ? $st->animFrames : [$st->map];
                if ($st->isLightmap) {
                    $maps = ['$lightmap'];
                }
                foreach ($maps as $mi => $map) {
                    if ($map === '') {
                        continue;
                    }
                    $info = $mat->classifyMap($map);
                    $status = $info['status'];
                    if ($status === 'MISSING' && $scripted && $mi === 0 && $si === 0) {
                        // keep MISSING
                    }
                    fwrite(STDOUT, sprintf(
                        "%-48s %-6s %-40s %-40s %s\n",
                        $name,
                        (string) $si,
                        $map,
                        $info['resolved'] ?? '-',
                        $status,
                    ));
                    $rows++;
                    if ($status === 'MISSING') {
                        $missing++;
                    }
                }
            }
        }
        fwrite(STDOUT, sprintf(
            "\nshader:missing — %d shaders, %d stage maps, %d MISSING (surf refs by count desc)\n",
            count($counts),
            $rows,
            $missing,
        ));

        return $missing > 0 ? 1 : 0;
    }

    /**
     * P0 — aim ray → nearest world batch + shader dump.
     * @param list<float> $eye
     * @param list<float> $angles pitch/yaw/roll deg
     * @param list<\Quake3\Render\WorldBatch> $batches
     */
    private function dumpSurfacePick(
        array $eye,
        array $angles,
        CollisionModel $cm,
        array $batches,
        ShaderParser $parser,
        MaterialResolver $materials,
        ?VisData $vis = null,
    ): void {
        [$fwd] = QuakeMath::angleVectors($angles);
        $end = [
            $eye[0] + $fwd[0] * 8192.0,
            $eye[1] + $fwd[1] * 8192.0,
            $eye[2] + $fwd[2] * 8192.0,
        ];
        $tr = $cm->trace($eye, $end, [0.0, 0.0, 0.0], [0.0, 0.0, 0.0], CollisionModel::MASK_PLAYERSOLID);
        $hit = $tr->endPos;
        $n = $tr->planeNormal ?? [0.0, 0.0, 0.0];
        fwrite(STDERR, "=== SURFACE PICK ===\n");
        fwrite(STDERR, sprintf(
            "screen hit world=(%.1f %.1f %.1f) fraction=%.4f normal=(%.2f %.2f %.2f) contents=0x%x surfaceFlags=0x%x\n",
            $hit[0],
            $hit[1],
            $hit[2],
            $tr->fraction,
            $n[0],
            $n[1],
            $n[2],
            $tr->contents,
            $tr->surfaceFlags,
        ));
        if ($tr->fraction >= 1.0) {
            fwrite(STDERR, "no hit\n");
            return;
        }

        $eyeLeaf = $cm->pointLeafnum($eye);
        $viewCluster = $cm->leafCluster($eyeLeaf);
        $leaf = $cm->pointLeafnum($hit);
        $pvsCluster = $cm->leafCluster($leaf);

        $bestT = PHP_FLOAT_MAX;
        $best = null;
        foreach ($batches as $bi => $b) {
            $hitInfo = $this->rayHitBatch($eye, $fwd, $b);
            if ($hitInfo === null) {
                continue;
            }
            if ($hitInfo['t'] < $bestT) {
                $bestT = $hitInfo['t'];
                $best = [$bi, $b, $hitInfo];
            }
        }
        if ($best === null) {
            fwrite(STDERR, "no triangle under aim (AABB fallback)\n");
            $best = $this->nearestBatchAabb($hit, $batches);
            if ($best === null) {
                fwrite(STDERR, "no batch near hit\n");
                return;
            }
            [$bi, $b] = $best;
            $face = $b->faces[0] ?? null;
            $winding = '?';
        } else {
            [$bi, $b, $hitInfo] = $best;
            $face = $hitInfo['face'];
            $winding = $hitInfo['winding'];
            fwrite(STDERR, sprintf(
                "tri hit t=%.2f winding=%s localTri=%d\n",
                $bestT,
                $winding,
                $hitInfo['localTri'],
            ));
        }

        $shader = $face['shader'] ?? $b->shaderName;
        $sh = $parser->get($shader);
        $pvsOk = true;
        if ($vis !== null && $viewCluster >= 0) {
            $pvsOk = false;
            $cls = $b->clusters !== [] ? $b->clusters : [$b->cluster];
            if ($cls === [] || $cls === [-1]) {
                $pvsOk = true;
            } else {
                foreach ($cls as $c) {
                    if ($c < 0 || $vis->visible($viewCluster, (int) $c)) {
                        $pvsOk = true;
                        break;
                    }
                }
            }
        }
        $expected = !$sh->isSky() && $pvsOk;

        fwrite(STDERR, sprintf(
            "batch#%d shader=%s lm=%d tris=%d blend=%d sort=%.1f cull=%s clusters=%s\n",
            $bi,
            $shader,
            $b->lightmapNum,
            $b->triangleCount,
            $b->blend ? 1 : 0,
            $b->sort,
            $sh->cull,
            $b->clusters !== [] ? implode(',', $b->clusters) : (string) $b->cluster,
        ));
        if ($face !== null) {
            fwrite(STDERR, sprintf(
                "face#%d type=%s(%d) shaderIndex=%d fog=%d lm=%d firstVert=%d numVerts=%d firstMeshVert=%d numMeshVerts=%d tris=%d\n",
                $face['index'],
                $face['typeName'],
                $face['type'],
                $face['shaderNum'],
                $face['fog'],
                $face['lm'],
                $face['firstVert'],
                $face['numVerts'],
                $face['firstIndex'],
                $face['numIndexes'],
                $face['triCount'],
            ));
            $fn = $face['normal'];
            fwrite(STDERR, sprintf(
                "surface normal=(%.3f %.3f %.3f) winding=%s\n",
                $fn[0],
                $fn[1],
                $fn[2],
                $winding,
            ));
        }
        fwrite(STDERR, sprintf(
            "EXPECTED TO DRAW: %s (sky=%d pvs=%s viewCluster=%d hitCluster=%d leaf=%d)\n",
            $expected ? 'YES' : 'NO',
            $sh->isSky() ? 1 : 0,
            $pvsOk ? 'visible' : 'culled',
            $viewCluster,
            $pvsCluster,
            $leaf,
        ));
        $scripted = $parser->isScripted($shader);
        fwrite(STDERR, sprintf(
            "shader scripted=%d stages=%d cull=%s opaque=%d sky=%d sort=%.1f\n",
            $scripted ? 1 : 0,
            count($sh->stages),
            $sh->cull,
            $sh->opaque ? 1 : 0,
            $sh->isSky() ? 1 : 0,
            $sh->sort,
        ));
        $baseTex = '';
        foreach ($sh->stages as $i => $st) {
            $maps = $st->animFrames !== [] ? $st->animFrames : [$st->map];
            $resolved = [];
            foreach ($maps as $m) {
                if ($m === '' || str_starts_with($m, '$')) {
                    $resolved[] = $m . '=' . ($m === '$lightmap' ? 'BUILTIN_LIGHTMAP' : ($m === '$whiteimage' ? 'BUILTIN_WHITEIMAGE' : 'BUILTIN'));
                    continue;
                }
                $info = $materials->classifyMap($m);
                $resolved[] = $m . '→' . ($info['resolved'] ?? 'MISSING') . '[' . $info['status'] . ']';
                if ($baseTex === '' && !$st->isLightmap) {
                    $baseTex = $m;
                }
            }
            fwrite(STDERR, sprintf(
                "  stage[%d] map=%s lm=%d blend=%d src=0x%x dst=0x%x depthWrite=%d rgbGen=%s alphaGen=%s alphaFunc=%s tcGen=%s tcMods=%d\n    %s\n",
                $i,
                $st->map,
                $st->isLightmap ? 1 : 0,
                $st->hasBlend ? 1 : 0,
                $st->blendSrc,
                $st->blendDst,
                $st->depthWrite ? 1 : 0,
                $st->rgbGen,
                $st->alphaGen !== '' ? $st->alphaGen : '-',
                $st->alphaFunc !== '' ? $st->alphaFunc : '-',
                $st->tcGen !== '' ? $st->tcGen : 'base',
                count($st->tcMods),
                implode('; ', $resolved),
            ));
        }
        fwrite(STDERR, 'base texture: ' . ($baseTex !== '' ? $baseTex : '(none)') . "\n");
    }

    /**
     * @param list<float> $orig
     * @param list<float> $dir
     * @return array{t: float, face: ?array, winding: string, localTri: int}|null
     */
    private function rayHitBatch(array $orig, array $dir, \Quake3\Render\WorldBatch $b): ?array
    {
        if ($b->vertexBinary === '' || $b->indexBinary === '') {
            return null;
        }
        $vf = unpack('g*', $b->vertexBinary);
        $idx = unpack('V*', $b->indexBinary);
        if ($vf === false || $idx === false) {
            return null;
        }
        $bestT = PHP_FLOAT_MAX;
        $bestTri = -1;
        $bestVerts = null;
        $nIdx = count($idx);
        for ($i = 1; $i + 2 <= $nIdx; $i += 3) {
            $ia = (int) $idx[$i];
            $ib = (int) $idx[$i + 1];
            $ic = (int) $idx[$i + 2];
            $a = $this->batchVertXyz($vf, $ia);
            $bv = $this->batchVertXyz($vf, $ib);
            $c = $this->batchVertXyz($vf, $ic);
            if ($a === null || $bv === null || $c === null) {
                continue;
            }
            $t = $this->rayTriangle($orig, $dir, $a, $bv, $c);
            if ($t !== null && $t < $bestT) {
                $bestT = $t;
                $bestTri = intdiv($i - 1, 3);
                $bestVerts = [$a, $bv, $c];
            }
        }
        if ($bestVerts === null) {
            return null;
        }
        $face = null;
        $localIndex = $bestTri * 3;
        foreach ($b->faces as $f) {
            $start = (int) $f['localIndexStart'];
            $count = (int) $f['localIndexCount'];
            if ($localIndex >= $start && $localIndex < $start + $count) {
                $face = $f;
                break;
            }
        }
        $e1 = [
            $bestVerts[1][0] - $bestVerts[0][0],
            $bestVerts[1][1] - $bestVerts[0][1],
            $bestVerts[1][2] - $bestVerts[0][2],
        ];
        $e2 = [
            $bestVerts[2][0] - $bestVerts[0][0],
            $bestVerts[2][1] - $bestVerts[0][1],
            $bestVerts[2][2] - $bestVerts[0][2],
        ];
        $tn = [
            $e1[1] * $e2[2] - $e1[2] * $e2[1],
            $e1[2] * $e2[0] - $e1[0] * $e2[2],
            $e1[0] * $e2[1] - $e1[1] * $e2[0],
        ];
        $fn = $face['normal'] ?? [0.0, 0.0, 1.0];
        $dot = $tn[0] * $fn[0] + $tn[1] * $fn[1] + $tn[2] * $fn[2];
        $winding = $dot >= 0.0 ? 'FRONT' : 'REVERSED';

        return ['t' => $bestT, 'face' => $face, 'winding' => $winding, 'localTri' => $bestTri];
    }

    /**
     * @param array<int, float> $vf
     * @return list<float>|null
     */
    private function batchVertXyz(array $vf, int $i): ?array
    {
        $base = $i * 10 + 1;
        if (!isset($vf[$base], $vf[$base + 1], $vf[$base + 2])) {
            return null;
        }
        return [$vf[$base], $vf[$base + 1], $vf[$base + 2]];
    }

    /**
     * @param list<float> $orig
     * @param list<float> $dir
     * @param list<float> $v0
     * @param list<float> $v1
     * @param list<float> $v2
     */
    private function rayTriangle(array $orig, array $dir, array $v0, array $v1, array $v2): ?float
    {
        $eps = 1e-6;
        $e1 = [$v1[0] - $v0[0], $v1[1] - $v0[1], $v1[2] - $v0[2]];
        $e2 = [$v2[0] - $v0[0], $v2[1] - $v0[1], $v2[2] - $v0[2]];
        $px = $dir[1] * $e2[2] - $dir[2] * $e2[1];
        $py = $dir[2] * $e2[0] - $dir[0] * $e2[2];
        $pz = $dir[0] * $e2[1] - $dir[1] * $e2[0];
        $det = $e1[0] * $px + $e1[1] * $py + $e1[2] * $pz;
        if (abs($det) < $eps) {
            return null;
        }
        $inv = 1.0 / $det;
        $tx = $orig[0] - $v0[0];
        $ty = $orig[1] - $v0[1];
        $tz = $orig[2] - $v0[2];
        $u = ($tx * $px + $ty * $py + $tz * $pz) * $inv;
        if ($u < 0.0 || $u > 1.0) {
            return null;
        }
        $qx = $ty * $e1[2] - $tz * $e1[1];
        $qy = $tz * $e1[0] - $tx * $e1[2];
        $qz = $tx * $e1[1] - $ty * $e1[0];
        $v = ($dir[0] * $qx + $dir[1] * $qy + $dir[2] * $qz) * $inv;
        if ($v < 0.0 || $u + $v > 1.0) {
            return null;
        }
        $t = ($e2[0] * $qx + $e2[1] * $qy + $e2[2] * $qz) * $inv;
        return $t > $eps ? $t : null;
    }

    /**
     * @param list<float> $hit
     * @param list<\Quake3\Render\WorldBatch> $batches
     * @return array{0: int, 1: \Quake3\Render\WorldBatch}|null
     */
    private function nearestBatchAabb(array $hit, array $batches): ?array
    {
        $best = null;
        $bestDist = PHP_FLOAT_MAX;
        foreach ($batches as $bi => $b) {
            $mins = $b->mins;
            $maxs = $b->maxs;
            $dx = 0.0;
            if ($hit[0] < $mins[0]) {
                $dx = $mins[0] - $hit[0];
            } elseif ($hit[0] > $maxs[0]) {
                $dx = $hit[0] - $maxs[0];
            }
            $dy = 0.0;
            if ($hit[1] < $mins[1]) {
                $dy = $mins[1] - $hit[1];
            } elseif ($hit[1] > $maxs[1]) {
                $dy = $hit[1] - $maxs[1];
            }
            $dz = 0.0;
            if ($hit[2] < $mins[2]) {
                $dz = $mins[2] - $hit[2];
            } elseif ($hit[2] > $maxs[2]) {
                $dz = $hit[2] - $maxs[2];
            }
            $d = $dx * $dx + $dy * $dy + $dz * $dz;
            if ($d < $bestDist) {
                $bestDist = $d;
                $best = [$bi, $b];
            }
        }
        return $best;
    }

    /**
     * Face/index/winding/PVS-span audit — cheap enough to run every loop cycle.
     *
     * @param list<string> $argv
     */
    private function cmdBspSurfaceAudit(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $mapPath = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $mapPath = $arg;
                continue;
            }
            throw new \RuntimeException('unknown bsp:surface-audit option: ' . $arg);
        }
        if ($basepath === null || $mapPath === null) {
            fwrite(STDERR, "usage: bsp:surface-audit --basepath=/path maps/q3dm1.bsp\n");
            return 1;
        }

        $bspMap = $this->loadBspMap($mapPath, $basepath);
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $parser = new ShaderParser($fs);
        $materials = new MaterialResolver($fs);
        $materials->setParser($parser);
        $cm = CollisionModel::load($bspMap);
        $ex = new BspMeshExtractor();
        $drawVerts = $ex->loadDrawVerts($bspMap);
        $drawIndexes = $ex->loadDrawIndexes($bspMap);
        $surfaces = $ex->loadSurfaces($bspMap);
        $built = (new WorldBatchBuilder())->build($bspMap, $parser, $cm, new LightingConfig());

        $parsed = ['planar' => 0, 'patch' => 0, 'triangle_soup' => 0, 'flare' => 0, 'bad' => 0];
        foreach ($surfaces as $s) {
            $parsed[$s->typeName()] = ($parsed[$s->typeName()] ?? 0) + 1;
        }
        $emitted = 0;
        $multi = 0;
        $maxCl = 0;
        $missingTex = 0;
        foreach ($built['batches'] as $b) {
            $emitted += count($b->faces);
            $n = count($b->clusters);
            if ($n > 1) {
                $multi++;
            }
            $maxCl = max($maxCl, $n);
            $sh = $parser->get($b->shaderName);
            foreach ($sh->stages as $st) {
                if ($st->isLightmap || $st->map === '' || str_starts_with($st->map, '$')) {
                    continue;
                }
                $maps = $st->animFrames !== [] ? $st->animFrames : [$st->map];
                foreach ($maps as $m) {
                    if ($materials->resolveStageImage($m) === null) {
                        $missingTex++;
                    }
                }
            }
        }

        $front = 0;
        $reversed = 0;
        $degenerate = 0;
        $oversize = 0;
        $badIndex = 0;
        foreach ($surfaces as $surf) {
            if ($surf->surfaceType !== BspSurface::MST_PLANAR && $surf->surfaceType !== BspSurface::MST_TRIANGLE_SOUP) {
                continue;
            }
            if ($surf->numIndexes < 3 || $surf->numVerts <= 0) {
                continue;
            }
            $mins = [INF, INF, INF];
            $maxs = [-INF, -INF, -INF];
            for ($v = 0; $v < $surf->numVerts; $v++) {
                $p = $drawVerts[$surf->firstVert + $v]->xyz;
                for ($a = 0; $a < 3; $a++) {
                    $mins[$a] = min($mins[$a], $p[$a]);
                    $maxs[$a] = max($maxs[$a], $p[$a]);
                }
            }
            $diag = sqrt(
                ($maxs[0] - $mins[0]) ** 2
                + ($maxs[1] - $mins[1]) ** 2
                + ($maxs[2] - $mins[2]) ** 2
            ) + 1.0;
            $pn = $surf->lightmapVecs[2] ?? [0.0, 0.0, 1.0];
            for ($i = 0; $i + 2 < $surf->numIndexes; $i += 3) {
                $ia = $drawIndexes[$surf->firstIndex + $i];
                $ib = $drawIndexes[$surf->firstIndex + $i + 1];
                $ic = $drawIndexes[$surf->firstIndex + $i + 2];
                if ($ia < 0 || $ib < 0 || $ic < 0
                    || $ia >= $surf->numVerts || $ib >= $surf->numVerts || $ic >= $surf->numVerts) {
                    $badIndex++;
                    continue;
                }
                $a = $drawVerts[$surf->firstVert + $ia]->xyz;
                $b = $drawVerts[$surf->firstVert + $ib]->xyz;
                $c = $drawVerts[$surf->firstVert + $ic]->xyz;
                $e1 = [$b[0] - $a[0], $b[1] - $a[1], $b[2] - $a[2]];
                $e2 = [$c[0] - $a[0], $c[1] - $a[1], $c[2] - $a[2]];
                $tn = [
                    $e1[1] * $e2[2] - $e1[2] * $e2[1],
                    $e1[2] * $e2[0] - $e1[0] * $e2[2],
                    $e1[0] * $e2[1] - $e1[1] * $e2[0],
                ];
                $area2 = sqrt($tn[0] ** 2 + $tn[1] ** 2 + $tn[2] ** 2);
                if ($area2 < 1e-4) {
                    $degenerate++;
                    continue;
                }
                $dot = $tn[0] * $pn[0] + $tn[1] * $pn[1] + $tn[2] * $pn[2];
                if ($surf->surfaceType === BspSurface::MST_PLANAR) {
                    if ($dot >= 0.0) {
                        $front++;
                    } else {
                        $reversed++;
                    }
                }
                $ab = sqrt($e1[0] ** 2 + $e1[1] ** 2 + $e1[2] ** 2);
                $ac = sqrt($e2[0] ** 2 + $e2[1] ** 2 + $e2[2] ** 2);
                $bc = sqrt(($c[0] - $b[0]) ** 2 + ($c[1] - $b[1]) ** 2 + ($c[2] - $b[2]) ** 2);
                if (max($ab, $ac, $bc) > $diag * 4.0) {
                    $oversize++;
                }
            }
        }

        $skips = $built['skipReasons'] ?? [];
        fwrite(STDOUT, "bsp:surface-audit — {$mapPath}\n");
        fwrite(STDOUT, sprintf(
            "  parsed: planar=%d patch=%d soup=%d flare=%d bad=%d total=%d\n",
            $parsed['planar'],
            $parsed['patch'],
            $parsed['triangle_soup'],
            $parsed['flare'],
            $parsed['bad'],
            count($surfaces),
        ));
        fwrite(STDOUT, sprintf(
            "  emitted faces=%d batches=%d tris=%d sky=%s\n",
            $emitted,
            count($built['batches']),
            $built['triangleCount'],
            $built['skyShader'] ?? '(none)',
        ));
        $skipParts = [];
        foreach ($skips as $k => $v) {
            $skipParts[] = $k . '=' . $v;
        }
        fwrite(STDOUT, '  skips: ' . implode(' ', $skipParts) . "\n");
        fwrite(STDOUT, sprintf(
            "  pvs: multi_cluster_batches=%d max_clusters=%d\n",
            $multi,
            $maxCl,
        ));
        fwrite(STDOUT, sprintf(
            "  planar winding vs BSP normal: same=%d opposite=%d (Q3 indexes are CW; opposite is expected) degenerate=%d oversize=%d bad_index=%d\n",
            $front,
            $reversed,
            $degenerate,
            $oversize,
            $badIndex,
        ));
        fwrite(STDOUT, sprintf("  missing_stage_maps=%d\n", $missingTex));

        fwrite(STDOUT, "  top multi-cluster batches:\n");
        $ranked = $built['batches'];
        usort($ranked, static fn ($a, $b) => count($b->clusters) <=> count($a->clusters));
        foreach (array_slice($ranked, 0, 8) as $b) {
            fwrite(STDOUT, sprintf(
                "    clusters=%d tris=%d faces=%d lm=%d %s\n",
                count($b->clusters),
                $b->triangleCount,
                count($b->faces),
                $b->lightmapNum,
                $b->shaderName,
            ));
        }

        return 0;
    }

    private function cmdRenderAudit(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $mapPath = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $mapPath = $arg;
                continue;
            }
            throw new \RuntimeException('unknown render:audit option: ' . $arg);
        }
        if ($basepath === null || $mapPath === null) {
            fwrite(STDERR, "usage: render:audit --basepath=/path maps/q3dm0.bsp\n");
            return 1;
        }

        $bspMap = $this->loadBspMap($mapPath, $basepath);
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $parser = new ShaderParser($fs);
        $materials = new MaterialResolver($fs);
        $materials->setParser($parser);
        $built = (new WorldBatchBuilder())->build($bspMap, $parser, null, new LightingConfig());

        $scripted = 0;
        $implicit = 0;
        $missingMaps = 0;
        $mapRefs = 0;
        $lightmapped = 0;
        $patches = $built['surfaceTypeCounts']['patch'] ?? 0;
        $stageCounts = ['1' => 0, '2' => 0, '3+' => 0];
        $blendStages = 0;
        $alphaStages = 0;
        $animStages = 0;
        $envStages = 0;
        $specStages = 0;
        $mpShaders = 0;

        /** @var array<string, int> $byShader */
        $byShader = [];
        foreach ($built['batches'] as $b) {
            $byShader[$b->shaderName] = ($byShader[$b->shaderName] ?? 0) + $b->triangleCount;
            if ($b->useLightmap) {
                $lightmapped += $b->triangleCount;
            }
        }

        foreach (array_keys($byShader) as $name) {
            $sh = $parser->get($name);
            if ($parser->isScripted($name)) {
                $scripted++;
            } else {
                $implicit++;
            }
            $n = count($sh->stages);
            if ($n <= 1) {
                $stageCounts['1']++;
            } elseif ($n === 2) {
                $stageCounts['2']++;
            } else {
                $stageCounts['3+']++;
            }
            if ($sh->needsFullMultipass()) {
                $mpShaders++;
            }
            foreach ($sh->stages as $st) {
                if ($st->hasBlend) {
                    $blendStages++;
                }
                if ($st->alphaFunc !== '' || strtolower((string) $st->alphaGen) === 'lightingspecular') {
                    $alphaStages++;
                }
                if ($st->animFrames !== []) {
                    $animStages++;
                }
                if ($st->tcGen === 'environment') {
                    $envStages++;
                }
                if (strtolower((string) $st->alphaGen) === 'lightingspecular') {
                    $specStages++;
                }
                if ($st->isLightmap || $st->map === '' || str_starts_with($st->map, '$')) {
                    continue;
                }
                $maps = $st->animFrames !== [] ? $st->animFrames : [$st->map];
                foreach ($maps as $m) {
                    $mapRefs++;
                    if ($materials->resolveStageImage($m) === null) {
                        $missingMaps++;
                    }
                }
            }
        }

        $types = $built['surfaceTypeCounts'];
        fwrite(STDOUT, "render:audit — {$mapPath}\n");
        fwrite(STDOUT, sprintf(
            "  surfaces: planar=%d patch=%d soup=%d flare=%d bad=%d\n",
            $types['planar'] ?? 0,
            $types['patch'] ?? 0,
            $types['triangle_soup'] ?? 0,
            $types['flare'] ?? 0,
            $types['bad'] ?? 0,
        ));
        fwrite(STDOUT, sprintf(
            "  batches=%d triangles=%d lightmapped_tris=%d patches=%d sky=%s lm_pages=%d\n",
            count($built['batches']),
            $built['triangleCount'],
            $lightmapped,
            $patches,
            $built['skyShader'] ?? '(none)',
            $built['lightmaps']->count(),
        ));
        fwrite(STDOUT, sprintf(
            "  shaders: total=%d scripted=%d implicit=%d multipass=%d\n",
            count($byShader),
            $scripted,
            $implicit,
            $mpShaders,
        ));
        fwrite(STDOUT, sprintf(
            "  stage_counts: 1=%d 2=%d 3+=%d | blend=%d alpha/spec=%d anim=%d env=%d specular=%d\n",
            $stageCounts['1'],
            $stageCounts['2'],
            $stageCounts['3+'],
            $blendStages,
            $alphaStages,
            $animStages,
            $envStages,
            $specStages,
        ));
        fwrite(STDOUT, sprintf(
            "  maps referenced=%d missing=%d\n",
            $mapRefs,
            $missingMaps,
        ));
        fwrite(STDOUT, "  top shaders by tris:\n");
        arsort($byShader);
        $i = 0;
        foreach ($byShader as $name => $tris) {
            if ($i++ >= 15) {
                break;
            }
            $sh = $parser->get($name);
            $tag = $parser->isScripted($name) ? 'S' : 'D';
            $mp = $sh->needsFullMultipass() ? 'mp' : 'op';
            fwrite(STDOUT, sprintf("    %5d %s %s %s\n", $tris, $tag, $mp, $name));
        }

        return 0;
    }

    private function cmdShaderAudit(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $mapPath = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $mapPath = $arg;
                continue;
            }
            throw new \RuntimeException('unknown shader:audit option: ' . $arg);
        }
        if ($basepath === null || $mapPath === null) {
            fwrite(STDERR, "usage: shader:audit --basepath=/path maps/q3dm0.bsp\n");
            return 1;
        }

        $bspMap = $this->loadBspMap($mapPath, $basepath);
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $parser = new ShaderParser($fs);
        $ex = new BspMeshExtractor();
        $shaders = $ex->loadShaders($bspMap);
        $surfaces = $ex->loadSurfaces($bspMap);

        /** @var array<string, int> $counts */
        $counts = [];
        foreach ($surfaces as $surf) {
            $name = $shaders[$surf->shaderNum]->shader ?? 'unknown';
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }
        arsort($counts);

        fwrite(STDOUT, "shader:audit — " . count($counts) . " shaders used\n");
        fwrite(STDOUT, str_pad('count', 8) . str_pad('blend', 8) . str_pad('sky', 6) . "name\n");
        $impl = ['map', 'clampmap', 'animmap', 'blendfunc', 'rgbgen', 'alphagen', 'alphafunc',
            'tcmod', 'cull', 'surfaceparm', 'skyparms', 'depthwrite', 'deformvertexes',
            'sort', 'polygonoffset', 'nopicmip', 'nomipmaps', 'portal'];
        foreach ($counts as $name => $n) {
            $sh = $parser->get($name);
            fwrite(STDOUT, sprintf(
                "%-8d%-8s%-6s%s\n",
                $n,
                $sh->needsBlendPass() ? 'yes' : '',
                $sh->isSky() ? 'yes' : '',
                $name,
            ));
        }

        fwrite(STDOUT, "\nDirective hits (parser):\n");
        foreach ($parser->directiveHits() as $d => $c) {
            $mark = in_array($d, $impl, true) ? 'OK' : '??';
            fwrite(STDOUT, sprintf("  [%s] %-20s %d\n", $mark, $d, $c));
        }
        $unk = $parser->unknownDirectives();
        if ($unk !== []) {
            fwrite(STDOUT, "\nUnknown/skipped globals (sample): " . implode(', ', array_slice($unk, 0, 40)) . "\n");
        }
        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdMd3Inspect(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $path = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $path = $arg;
                continue;
            }
            throw new \RuntimeException('unknown md3:inspect option: ' . $arg);
        }
        if ($basepath === null || $path === null) {
            fwrite(STDERR, "usage: md3:inspect --basepath=/path models/.../file.md3\n");
            return 1;
        }
        $fs = new FileSystem();
        $fs->addGameDirectory($this->resolveBaseq3Dir($basepath));
        $m = Md3Model::load($fs, $path);
        $b = $m->bounds(0);
        fwrite(STDOUT, sprintf(
            "MD3 %s frames=%d tags=%d surfaces=%d trisVerts=%d\n",
            $m->name !== '' ? $m->name : $path,
            $m->numFrames,
            $m->numTags,
            $m->numSurfaces,
            $m->triangleVertexCount(),
        ));
        $radius = 0.5 * sqrt(
            $b['size'][0] ** 2 + $b['size'][1] ** 2 + $b['size'][2] ** 2
        );
        fwrite(STDOUT, sprintf(
            "  bounds mins=(%.3f %.3f %.3f) maxs=(%.3f %.3f %.3f) size=(%.3f %.3f %.3f) radius=%.3f\n",
            $b['mins'][0], $b['mins'][1], $b['mins'][2],
            $b['maxs'][0], $b['maxs'][1], $b['maxs'][2],
            $b['size'][0], $b['size'][1], $b['size'][2],
            $radius,
        ));
        foreach ($m->tags as $t) {
            fwrite(STDOUT, sprintf(
                "  tag %s origin=(%.3f %.3f %.3f)\n",
                $t['name'],
                $t['origin'][0],
                $t['origin'][1],
                $t['origin'][2],
            ));
        }
        foreach ($m->surfaces as $i => $s) {
            fwrite(STDOUT, sprintf(
                "  surf[%d] %s shader=%s verts=%d tris=%d\n",
                $i,
                $s['name'],
                $s['shader'],
                count($s['verts']),
                intdiv(count($s['indices']), 3),
            ));
        }
        return 0;
    }

    /**
     * Dump a lightmap page to PNG for lighting diagnostics.
     *
     * @param list<string> $argv
     */
    private function cmdLightmapDump(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        $mapPath = null;
        $page = 0;
        $out = 'debug/lightmap0.png';
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            if (str_starts_with($arg, '--page=')) {
                $page = (int) substr($arg, 7);
                continue;
            }
            if (str_starts_with($arg, '--out=')) {
                $out = substr($arg, 6);
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                $mapPath = $arg;
                continue;
            }
            throw new \RuntimeException('unknown lightmap:dump option: ' . $arg);
        }
        if ($basepath === null || $mapPath === null) {
            fwrite(STDERR, "usage: lightmap:dump --basepath=/path maps/q3dm0.bsp [--page=0] [--out=debug/lm0.png]\n");
            return 1;
        }
        $bspMap = $this->loadBspMap($mapPath, $basepath);
        $lms = LightmapLump::load($bspMap);
        if ($page < 0 || $page >= $lms->count()) {
            throw new \RuntimeException("lightmap page {$page} out of range 0.." . ($lms->count() - 1));
        }
        $lighting = new LightingConfig();
        $rgba = $lms->pageRgba($page, $lighting->mapOverBrightBits, $lighting->overbrightBits);
        if (!str_starts_with($out, '/')) {
            $out = getcwd() . '/' . $out;
        }
        LightingConfig::writePngRgba($out, $rgba, LightmapLump::PAGE_SIZE, LightmapLump::PAGE_SIZE);
        $mean = $lms->pageMeanRgb($page, $lighting->mapOverBrightBits, $lighting->overbrightBits);
        fwrite(STDOUT, sprintf(
            "wrote %s page=%d shift=%d mean_rgb=(%.1f %.1f %.1f)\n",
            $out,
            $page,
            $lighting->shift,
            $mean[0],
            $mean[1],
            $mean[2],
        ));
        return 0;
    }

    /**
     * @param list<string> $argv
     */
    private function cmdDataInspect(array $argv): int
    {
        $args = array_slice($argv, 2);
        $basepath = null;
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--basepath=')) {
                $basepath = substr($arg, strlen('--basepath='));
                continue;
            }
            throw new \RuntimeException('unknown data:inspect option: ' . $arg);
        }
        if ($basepath === null) {
            fwrite(STDERR, "usage: data:inspect --basepath=/path/to/Quake3\n");
            return 1;
        }

        $gameDir = $this->resolveBaseq3Dir($basepath);
        fwrite(STDOUT, sprintf("baseq3: %s\n", $gameDir));

        $paks = [];
        foreach (scandir($gameDir) ?: [] as $entry) {
            if (str_ends_with(strtolower($entry), '.pk3') && is_file($gameDir . '/' . $entry)) {
                $paks[] = $entry;
            }
        }
        usort($paks, 'strcasecmp');
        fwrite(STDOUT, sprintf("pk3 count: %d\n", count($paks)));

        $fs = new FileSystem();
        $fs->addGameDirectory($gameDir);

        $mapCount = 0;
        $shaderCount = 0;
        $maps = [];
        foreach ($fs->getPacks() as $pak) {
            $size = filesize($pak->getPath()) ?: 0;
            fwrite(STDOUT, sprintf(
                "  %-12s  %8.1f MB  %5d files  %s\n",
                $pak->getBasename() . '.pk3',
                $size / (1024 * 1024),
                $pak->getNumFiles(),
                $pak->getPath(),
            ));
            foreach ($pak->listEntries() as $e) {
                if (str_starts_with($e->name, 'maps/') && str_ends_with($e->name, '.bsp')) {
                    $mapCount++;
                    $maps[] = $e->name;
                }
                if (str_starts_with($e->name, 'scripts/') && str_ends_with($e->name, '.shader')) {
                    $shaderCount++;
                }
            }
        }

        sort($maps);
        fwrite(STDOUT, sprintf("maps/*.bsp: %d\n", $mapCount));
        fwrite(STDOUT, sprintf("scripts/*.shader: %d\n", $shaderCount));
        foreach (array_slice($maps, 0, 20) as $m) {
            fwrite(STDOUT, "  " . $m . "\n");
        }
        if (count($maps) > 20) {
            fwrite(STDOUT, sprintf("  … %d more\n", count($maps) - 20));
        }

        return 0;
    }

    /**
     * Parse MD3 .skin lines: "surface,texture/path".
     * @return array<string, string>
     */
    private function parseMd3Skin(string $text): array
    {
        $map = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, ',')) {
                continue;
            }
            [$surf, $path] = array_map('trim', explode(',', $line, 2));
            if ($surf === '' || str_starts_with($surf, 'tag_')) {
                continue;
            }
            $map[$surf] = $path;
        }
        return $map;
    }

    private function loadBspMap(string $target, ?string $basepath): BspMap
    {
        $loader = new BspLoader();

        if ($basepath !== null) {
            $fs = new FileSystem();
            $gameDir = $this->resolveBaseq3Dir($basepath);
            $fs->addGameDirectory($gameDir);
            fwrite(STDERR, sprintf("basepath game dir: %s (%d paks)\n", $gameDir, count($fs->getPacks())));
            $bytes = $fs->read($target);
            $resolved = $fs->resolve($target);
            $label = $resolved !== null
                ? sprintf('%s [%s]', $target, $resolved->archive->getPath())
                : $target;

            return $loader->loadFromString($bytes, $label);
        }

        if (is_file($target)) {
            return $loader->loadFromFile($target);
        }

        throw new \RuntimeException(sprintf(
            'BSP path not found: %s (use --basepath=… for PK3 qpaths)',
            $target,
        ));
    }

    private function printBspInfo(BspMap $map): void
    {
        $h = $map->header;
        fwrite(STDOUT, sprintf("source: %s\n", $map->sourceLabel !== '' ? $map->sourceLabel : '(memory)'));
        fwrite(STDOUT, sprintf("ident: %s\n", $h->identString()));
        fwrite(STDOUT, sprintf("version: %d\n", $h->version));
        fwrite(STDOUT, sprintf("filesize: %d\n", $map->getFileSize()));
        fwrite(STDOUT, sprintf("lumps: %d\n", count($h->lumps)));
        fwrite(STDOUT, "\n");

        $nameWidth = 0;
        foreach ($h->lumps as $lump) {
            $nameWidth = max($nameWidth, strlen($lump->name) + 1); // include trailing ':'
        }

        foreach ($h->lumps as $lump) {
            fwrite(STDOUT, sprintf(
                "%-{$nameWidth}s  offset: %-10d  length: %d\n",
                $lump->name . ':',
                $lump->fileofs,
                $lump->filelen,
            ));
        }
    }

    /**
     * Accept Quake3 install root (contains baseq3/) or the baseq3 directory itself.
     */
    private function resolveBaseq3Dir(string $basepath): string
    {
        $real = realpath($basepath);
        if ($real === false || !is_dir($real)) {
            throw new \RuntimeException(sprintf('basepath not found: %s', $basepath));
        }

        if (strcasecmp(basename($real), 'baseq3') === 0) {
            return $real;
        }

        $nested = $real . DIRECTORY_SEPARATOR . 'baseq3';
        if (is_dir($nested)) {
            return $nested;
        }

        // Allow pointing directly at a dir that already holds *.pk3
        foreach (scandir($real) ?: [] as $entry) {
            if (str_ends_with(strtolower($entry), '.pk3')) {
                return $real;
            }
        }

        throw new \RuntimeException(sprintf(
            'No baseq3/ or *.pk3 under basepath: %s',
            $real,
        ));
    }
}
