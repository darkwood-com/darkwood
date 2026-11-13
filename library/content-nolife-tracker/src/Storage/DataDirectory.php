<?php

namespace App\Storage;

final class DataDirectory
{
    public function path(): string
    {
        $override = getenv('NOLIFE_DATA_DIR');
        if (false !== $override && '' !== trim($override)) {
            $override = trim($override);
            if (!$this->isAbsolute($override)) {
                throw new \RuntimeException('NOLIFE_DATA_DIR must be an absolute path.');
            }

            return $this->ensure($override);
        }

        $path = match (PHP_OS_FAMILY) {
            'Darwin' => $this->home().'/Library/Application Support/Nolife Tracker',
            'Windows' => $this->windowsPath(),
            default => $this->linuxPath(),
        };

        return $this->ensure($path);
    }

    public function databasePath(): string
    {
        return $this->path().DIRECTORY_SEPARATOR.'nolife.sqlite';
    }

    private function linuxPath(): string
    {
        $xdgDataHome = getenv('XDG_DATA_HOME');

        return false !== $xdgDataHome && '' !== trim($xdgDataHome)
            ? rtrim(trim($xdgDataHome), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'nolife-tracker'
            : $this->home().'/.local/share/nolife-tracker';
    }

    private function windowsPath(): string
    {
        $localAppData = getenv('LOCALAPPDATA');
        if (false === $localAppData || '' === trim($localAppData)) {
            throw new \RuntimeException('LOCALAPPDATA is not available; set NOLIFE_DATA_DIR explicitly.');
        }

        return rtrim(trim($localAppData), '\\/').'\\Nolife Tracker';
    }

    private function home(): string
    {
        $home = getenv('HOME');
        if (false === $home || '' === trim($home)) {
            throw new \RuntimeException('HOME is not available; set NOLIFE_DATA_DIR explicitly.');
        }

        return rtrim(trim($home), DIRECTORY_SEPARATOR);
    }

    private function ensure(string $path): string
    {
        if (!is_dir($path) && !mkdir($path, 0700, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Unable to create data directory "%s".', $path));
        }
        if (!is_writable($path)) {
            throw new \RuntimeException(sprintf('Data directory "%s" is not writable.', $path));
        }
        if ('Windows' !== PHP_OS_FAMILY) {
            @chmod($path, 0700);
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    private function isAbsolute(string $path): bool
    {
        if ('Windows' === PHP_OS_FAMILY) {
            return 1 === preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\)/', $path);
        }

        return str_starts_with($path, DIRECTORY_SEPARATOR);
    }
}
