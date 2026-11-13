<?php

declare(strict_types=1);

namespace Quake3\Filesystem;

/**
 * Multi-PK3 search path — subset of fs_searchpaths / FS_FOpenFileRead.
 *
 * Packs are a singly linked priority list: head = highest priority.
 * addPak prepends (like FS_AddGameDirectory after each FS_LoadZipFile).
 * addGameDirectory sorts PK3 basenames ascending (paksort / FS_PathCmp) then
 * prepends each so later alphabetic names override earlier ones (pak1 > pak0).
 *
 * Loose directory nodes, pure-server filtering, and fs_* cvars are not implemented.
 */
final class FileSystem
{
    public const MAX_PAKFILES = 1024;

    /** @var list<Pk3Archive> head = index 0 = highest priority */
    private array $packs = [];

    /**
     * Prepend a loaded pack (highest priority).
     * Reference: FS_AddGameDirectory pack searchpath prepend.
     */
    public function addPak(string $path): Pk3Archive
    {
        $pak = new Pk3Archive($path);
        array_unshift($this->packs, $pak);

        return $pak;
    }

    /**
     * Discover *.pk3 in a directory, sort ascending, load low→high with prepend.
     * Reference: FS_AddGameDirectory (packs only — no loose directory node).
     *
     * @return list<Pk3Archive> packs loaded in this call (low→high load order)
     */
    public function addGameDirectory(string $dir): array
    {
        $real = realpath($dir);
        if ($real === false || !is_dir($real)) {
            throw new \RuntimeException(sprintf('Game directory not found: %s', $dir));
        }

        $names = [];
        foreach (scandir($real) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!str_ends_with(strtolower($entry), '.pk3')) {
                continue;
            }
            if (!is_file($real . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }
            $names[] = $entry;
        }

        // sort them so that later alphabetic matches override earlier ones
        usort($names, static fn (string $a, string $b): int => Path::compare($a, $b));

        if (count($names) > self::MAX_PAKFILES) {
            $names = array_slice($names, 0, self::MAX_PAKFILES);
        }

        $loaded = [];
        foreach ($names as $name) {
            $loaded[] = $this->addPak($real . DIRECTORY_SEPARATOR . $name);
        }

        return $loaded;
    }

    /**
     * @return list<Pk3Archive>
     */
    public function getPacks(): array
    {
        return $this->packs;
    }

    /**
     * Walk search list; first hit wins (FS_FOpenFileRead).
     */
    public function resolve(string $qpath): ?ResolvedFile
    {
        try {
            Path::normalize($qpath);
        } catch (\InvalidArgumentException) {
            return null;
        }

        foreach ($this->packs as $pack) {
            $entry = $pack->getEntry($qpath);
            if ($entry !== null) {
                return new ResolvedFile($entry, $pack);
            }
        }

        return null;
    }

    public function exists(string $qpath): bool
    {
        return $this->resolve($qpath) !== null;
    }

    public function read(string $qpath): string
    {
        $resolved = $this->resolve($qpath);
        if ($resolved === null) {
            throw new \RuntimeException(sprintf('File not found on search path: %s', $qpath));
        }

        return $resolved->archive->read($qpath);
    }

    public function readText(string $qpath): string
    {
        return $this->read($qpath);
    }
}
