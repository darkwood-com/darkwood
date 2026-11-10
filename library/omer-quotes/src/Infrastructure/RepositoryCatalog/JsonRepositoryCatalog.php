<?php

declare(strict_types=1);

namespace App\Infrastructure\RepositoryCatalog;

use App\Application\Port\RepositoryCatalogPort;
use App\Domain\Repository\RepositoryId;
use App\Domain\Repository\RepositoryMetadata;

final readonly class JsonRepositoryCatalog implements RepositoryCatalogPort
{
    public function __construct(private string $path = __DIR__ . '/../../../data/repos.json')
    {
    }

    public function all(): array
    {
        $decoded = json_decode((string) file_get_contents($this->path), true, 512, JSON_THROW_ON_ERROR);
        $items = $decoded['data'] ?? [];
        $out = [];
        foreach ($items as $item) {
            $fullName = (string) ($item['full_name'] ?? '');
            $out[] = new RepositoryMetadata(
                RepositoryId::fromFullName($fullName),
                (string) ($item['name'] ?? ''),
                $fullName,
                (string) ($item['owner']['login'] ?? ''),
                (string) ($item['description'] ?? ''),
                (string) ($item['clone_url'] ?? ''),
                (string) ($item['default_branch'] ?? 'master'),
                (array) ($item['topics'] ?? []),
            );
        }

        return $out;
    }
}
