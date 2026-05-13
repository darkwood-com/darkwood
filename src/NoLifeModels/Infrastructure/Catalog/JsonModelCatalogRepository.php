<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Catalog;

use App\NoLifeModels\Domain\Catalog\ModelCatalogRepositoryInterface;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;
use App\NoLifeModels\Domain\Model\ModelId;
use App\NoLifeModels\Domain\Model\ProviderId;

/**
 * Loads catalog entries from a versioned JSON file (no runtime dependency on models.dev).
 */
final class JsonModelCatalogRepository implements ModelCatalogRepositoryInterface
{
    public function __construct(
        private readonly string $jsonPath,
    ) {
    }

    public function findAll(): array
    {
        if (!is_file($this->jsonPath) || !is_readable($this->jsonPath)) {
            return [];
        }
        $raw = file_get_contents($this->jsonPath);
        if (false === $raw) {
            return [];
        }
        try {
            $data = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }
        if (!\is_array($data)) {
            return [];
        }
        $rows = $data['models'] ?? $data;
        if (!\is_array($rows)) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $id = (string) ($row['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $provider = (string) ($row['providerId'] ?? $row['provider'] ?? '');
            if ($provider === '') {
                continue;
            }
            $out[] = new ModelCatalogEntry(
                new ModelId($id),
                new ProviderId($provider),
                (string) ($row['name'] ?? $id),
                isset($row['contextLength']) ? (int) $row['contextLength'] : null,
                self::stringList($row['inputModalities'] ?? ['text']),
                self::stringList($row['outputModalities'] ?? ['text']),
                self::stringList($row['capabilities'] ?? []),
            );
        }

        return $out;
    }

    /**
     * @param mixed $value
     *
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }
        $list = [];
        foreach ($value as $v) {
            if (\is_string($v) && $v !== '') {
                $list[] = $v;
            }
        }

        return $list;
    }
}
