<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Catalog;

use App\NoLifeModels\Domain\Catalog\ModelCatalogRepositoryInterface;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;
use App\NoLifeModels\Domain\Model\ModelId;
use App\NoLifeModels\Domain\Model\ProviderId;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Loads catalog rows from models.dev public API (nested providers → models map).
 */
final class ModelsDevApiCatalogRepository implements ModelCatalogRepositoryInterface
{
    /** Parsed {@see ModelCatalogEntry} list (not raw JSON) — avoids json_decode on every request. */
    private const CACHE_KEY = 'no_life_models.models_dev_parsed_entries_v2';

    /** @var list<class-string> */
    private const UNSERIALIZE_CLASSES = [
        ModelCatalogEntry::class,
        ModelId::class,
        ProviderId::class,
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $apiUrl,
        private readonly bool $enabled,
        private readonly int $cacheTtlSeconds,
    ) {
    }

    public function findAll(): array
    {
        if (!$this->enabled || $this->apiUrl === '') {
            return [];
        }

        try {
            $blob = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): string {
                $item->expiresAfter($this->cacheTtlSeconds);
                if (\function_exists('set_time_limit')) {
                    @set_time_limit(300);
                }
                try {
                    $response = $this->httpClient->request('GET', $this->apiUrl);
                    if (200 !== $response->getStatusCode()) {
                        $item->expiresAfter(60);

                        return self::serializeEntries([]);
                    }

                    $raw = $response->getContent();
                } catch (\Throwable) {
                    $item->expiresAfter(60);

                    return self::serializeEntries([]);
                }

                try {
                    $data = json_decode($raw, true, 2048, \JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $item->expiresAfter(60);

                    return self::serializeEntries([]);
                }

                return self::serializeEntries(self::parseDecodedApi($data));
            });
        } catch (\Throwable) {
            return [];
        }

        return self::unserializeEntriesBlob($blob);
    }

    /**
     * @param list<ModelCatalogEntry> $entries
     */
    private static function serializeEntries(array $entries): string
    {
        return serialize($entries);
    }

    /**
     * @return list<ModelCatalogEntry>
     */
    private static function unserializeEntriesBlob(mixed $blob): array
    {
        if (!\is_string($blob) || '' === $blob) {
            return [];
        }

        $data = @unserialize($blob, ['allowed_classes' => self::UNSERIALIZE_CLASSES]);
        if (!\is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $row) {
            if ($row instanceof ModelCatalogEntry) {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param mixed $data
     *
     * @return list<ModelCatalogEntry>
     */
    public static function parseDecodedApi(mixed $data): array
    {
        if (!\is_array($data)) {
            return [];
        }

        $out = [];
        foreach ($data as $outerKey => $provider) {
            if (!\is_array($provider)) {
                continue;
            }
            $models = $provider['models'] ?? null;
            if (!\is_array($models)) {
                continue;
            }
            $providerId = (string) ($provider['id'] ?? $outerKey);
            if ('' === $providerId) {
                continue;
            }
            foreach ($models as $modelKey => $model) {
                if (!\is_array($model)) {
                    continue;
                }
                $modelLocalId = (string) ($model['id'] ?? $modelKey);
                if ('' === $modelLocalId) {
                    continue;
                }
                $fullId = $providerId.'/'.$modelLocalId;
                $modalities = $model['modalities'] ?? [];
                $input = \is_array($modalities) ? self::stringList($modalities['input'] ?? []) : [];
                $output = \is_array($modalities) ? self::stringList($modalities['output'] ?? []) : [];
                if ([] === $input) {
                    $input = ['text'];
                }
                if ([] === $output) {
                    $output = ['text'];
                }
                $capabilities = [];
                if (!empty($model['reasoning'])) {
                    $capabilities[] = 'reasoning';
                }
                if (!empty($model['tool_call'])) {
                    $capabilities[] = 'tool_call';
                }
                if (!empty($model['attachment'])) {
                    $capabilities[] = 'attachment';
                }

                $ctx = CatalogModelPayloadMapper::resolvedContextLength($model);
                $d = CatalogModelPayloadMapper::displayFields($model);

                try {
                    $out[] = new ModelCatalogEntry(
                        new ModelId($fullId),
                        new ProviderId($providerId),
                        (string) ($model['name'] ?? $modelLocalId),
                        $ctx,
                        $input,
                        $output,
                        $capabilities,
                        family: $d['family'],
                        costPerMillionInput: $d['costPerMillionInput'],
                        costPerMillionOutput: $d['costPerMillionOutput'],
                        costPerMillionReasoning: $d['costPerMillionReasoning'],
                        costPerMillionOver200kInput: $d['costPerMillionOver200kInput'],
                        costPerMillionOver200kOutput: $d['costPerMillionOver200kOutput'],
                        limitMaxInput: $d['limitMaxInput'],
                        limitMaxOutput: $d['limitMaxOutput'],
                        reasoningEnabled: $d['reasoningEnabled'],
                        toolCallEnabled: $d['toolCallEnabled'],
                        structuredOutput: $d['structuredOutput'],
                        openWeights: $d['openWeights'],
                        knowledge: $d['knowledge'],
                        releaseDate: $d['releaseDate'],
                    );
                } catch (\InvalidArgumentException) {
                    continue;
                }
            }
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
            if (\is_string($v) && '' !== $v) {
                $list[] = $v;
            }
        }

        return $list;
    }
}
