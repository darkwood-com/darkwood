<?php

declare(strict_types=1);

namespace Quake3\Filesystem;

/**
 * Single PK3 (ZIP) archive — FS_LoadZipFile analogue.
 *
 * Uses PHP ZipArchive instead of id's unzip.c. Lookup semantics match Quake III:
 * names lowercased at index time; duplicate names: last central-directory entry wins.
 */
final class Pk3Archive
{
    private readonly \ZipArchive $zip;

    /** @var array<string, Pk3Entry> normalized name => entry */
    private array $index = [];

    private readonly string $path;
    private readonly string $basename;
    private readonly int $numFiles;

    public function __construct(string $zipfile)
    {
        $real = realpath($zipfile);
        if ($real === false || !is_file($real)) {
            throw new \RuntimeException(sprintf('PK3 not found: %s', $zipfile));
        }

        $zip = new \ZipArchive();
        $err = $zip->open($real, \ZipArchive::RDONLY);
        if ($err !== true) {
            throw new \RuntimeException(sprintf('Failed to open PK3 %s (ZipArchive error %s)', $real, (string) $err));
        }

        $this->zip = $zip;
        $this->path = $real;

        $base = basename($real);
        // strip .pk3 if needed (FS_LoadZipFile)
        if (strlen($base) > 4 && strcasecmp(substr($base, -4), '.pk3') === 0) {
            $base = substr($base, 0, -4);
        } elseif (strlen($base) > 4 && strcasecmp(substr($base, -4), '.zip') === 0) {
            $base = substr($base, 0, -4);
        }
        $this->basename = $base;

        $this->buildIndex();
        $this->numFiles = count($this->index);
    }

    /**
     * Walk ZIP central directory like FS_LoadZipFile.
     * Later duplicate names overwrite earlier (hash-chain prepend → first match = last loaded).
     */
    private function buildIndex(): void
    {
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $stat = $this->zip->statIndex($i);
            if ($stat === false) {
                continue;
            }

            $rawName = $stat['name'];
            // Directory markers (trailing slash) — C still indexes them; skip empty dir-only nodes for PHP readability
            if (str_ends_with($rawName, '/') || str_ends_with($rawName, '\\')) {
                continue;
            }

            try {
                $name = Path::normalize($rawName);
            } catch (\InvalidArgumentException) {
                // Skip illegal ZIP member names rather than aborting the whole pack
                continue;
            }

            $this->index[$name] = new Pk3Entry(
                name: $name,
                zipIndex: $i,
                compressedSize: (int) ($stat['comp_size'] ?? 0),
                uncompressedSize: (int) ($stat['size'] ?? 0),
                compressionMethod: (int) ($stat['comp_method'] ?? 0),
                archivePath: $this->path,
                archiveBasename: $this->basename,
            );
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBasename(): string
    {
        return $this->basename;
    }

    /** Number of indexed file entries (directories excluded). */
    public function getNumFiles(): int
    {
        return $this->numFiles;
    }

    /** Raw ZIP central-directory count including directories. */
    public function getZipEntryCount(): int
    {
        return $this->zip->numFiles;
    }

    /**
     * @return list<Pk3Entry>
     */
    public function listEntries(): array
    {
        return array_values($this->index);
    }

    public function has(string $qpath): bool
    {
        return $this->getEntry($qpath) !== null;
    }

    public function getEntry(string $qpath): ?Pk3Entry
    {
        try {
            $key = Path::normalize($qpath);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $this->index[$key] ?? null;
    }

    /**
     * Read uncompressed bytes (FS_Read / unzReadCurrentFile).
     *
     * @throws \RuntimeException if missing or unreadable
     */
    public function read(string $qpath): string
    {
        $entry = $this->getEntry($qpath);
        if ($entry === null) {
            throw new \RuntimeException(sprintf('File not found in %s: %s', $this->basename, $qpath));
        }

        $data = $this->zip->getFromIndex($entry->zipIndex);
        if ($data === false) {
            throw new \RuntimeException(sprintf('Failed to read %s from %s', $entry->name, $this->path));
        }

        return $data;
    }

    /** Same as read(); PHP strings are binary-safe (no newline translation). */
    public function readText(string $qpath): string
    {
        return $this->read($qpath);
    }

    public function __destruct()
    {
        $this->zip->close();
    }
}
