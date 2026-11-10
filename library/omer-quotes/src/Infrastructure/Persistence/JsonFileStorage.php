<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Port\StoragePort;

final readonly class JsonFileStorage implements StoragePort
{
    public function __construct(private string $basePath = __DIR__ . '/../../../var/app')
    {
    }

    public function write(string $collection, string $id, array $payload): void
    {
        $dir = $this->basePath . '/' . $collection;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/' . $id . '.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function read(string $collection, string $id): ?array
    {
        $file = $this->basePath . '/' . $collection . '/' . $id . '.json';
        if (!is_file($file)) {
            return null;
        }

        return json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    }

    public function readAll(string $collection): array
    {
        $dir = $this->basePath . '/' . $collection;
        if (!is_dir($dir)) {
            return [];
        }

        $out = [];
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $out[basename($file, '.json')] = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        }

        return $out;
    }
}
