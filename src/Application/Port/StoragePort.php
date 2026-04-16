<?php

declare(strict_types=1);

namespace App\Application\Port;

interface StoragePort
{
    /**
     * @param array<string, mixed> $payload
     */
    public function write(string $collection, string $id, array $payload): void;

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $collection, string $id): ?array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function readAll(string $collection): array;
}
