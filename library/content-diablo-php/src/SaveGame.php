<?php

declare(strict_types=1);

namespace Diablo;

final class SaveGame
{
    public const VERSION = 1;

    public function __construct(private string $dir)
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0775, true);
        }
    }

    public function path(string $slot = 'slot1'): string
    {
        return $this->dir . '/' . $slot . '.json';
    }

    public function exists(string $slot = 'slot1'): bool
    {
        return is_file($this->path($slot));
    }

    /** @param array<string, mixed> $state */
    public function save(array $state, string $slot = 'slot1'): void
    {
        $state['version'] = self::VERSION;
        $state['savedAt'] = date('c');
        file_put_contents($this->path($slot), json_encode($state, JSON_PRETTY_PRINT));
    }

    /** @return array<string, mixed>|null */
    public function load(string $slot = 'slot1'): ?array
    {
        $p = $this->path($slot);
        if (!is_file($p)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($p), true);
        if (!is_array($data) || (int) ($data['version'] ?? 0) !== self::VERSION) {
            return null;
        }

        return $data;
    }
}
