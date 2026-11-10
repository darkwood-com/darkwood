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
 *
 * Cached payload is JSON (plain rows), not PHP serialize(), so large catalogs do not
 * hit multi‑second {@see unserialize()} on every catalog page view.
 */
final class ModelsDevApiCatalogRepository implements ModelCatalogRepositoryInterface
{
    private const CACHE_KEY = 'no_life_models.models_dev_parsed_json_v3';

    private const CACHE_FORMAT_VERSION = 3;

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

        if (\function_exists('set_time_limit')) {
            @set_time_limit(120);
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

                        return self::encodeCachedCatalog([]);
                    }

                    $raw = $response->getContent();
                } catch (\Throwable) {
                    $item->expiresAfter(60);

                    return self::encodeCachedCatalog([]);
                }

                try {
                    $data = json_decode($raw, true, 2048, \JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $item->expiresAfter(60);

                    return self::encodeCachedCatalog([]);
                }

                return self::encodeCachedCatalog(self::parseDecodedApi($data));
            });
        } catch (\Throwable) {
            return [];
        }

        return self::decodeCachedCatalogBlob($blob);
    }

    /**
     * @param list<ModelCatalogEntry> $entries
     */
    private static function encodeCachedCatalog(array $entries): string
    {
        $rows = [];
        foreach ($entries as $e) {
            if (!$e instanceof ModelCatalogEntry) {
                continue;
            }
            $rows[] = self::entryToPlainRow($e);
        }

        return json_encode(
            ['v' => self::CACHE_FORMAT_VERSION, 'rows' => $rows],
            \JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @return list<ModelCatalogEntry>
     */
    private static function decodeCachedCatalogBlob(mixed $blob): array
    {
        if (!\is_string($blob) || '' === $blob) {
            return [];
        }

        try {
            $wrap = json_decode($blob, true, 32, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (!\is_array($wrap) || (int) ($wrap['v'] ?? 0) !== self::CACHE_FORMAT_VERSION) {
            return [];
        }
        $rows = $wrap['rows'] ?? null;
        if (!\is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $r) {
            if (!\is_array($r)) {
                continue;
            }
            $e = self::entryFromPlainRow($r);
            if ($e instanceof ModelCatalogEntry) {
                $out[] = $e;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private static function entryToPlainRow(ModelCatalogEntry $e): array
    {
        return [
            'id' => $e->id->value,
            'providerId' => $e->providerId->value,
            'name' => $e->name,
            'contextLength' => $e->contextLength,
            'inputModalities' => $e->inputModalities,
            'outputModalities' => $e->outputModalities,
            'capabilities' => $e->capabilities,
            'family' => $e->family,
            'costPerMillionInput' => $e->costPerMillionInput,
            'costPerMillionOutput' => $e->costPerMillionOutput,
            'costPerMillionReasoning' => $e->costPerMillionReasoning,
            'costPerMillionOver200kInput' => $e->costPerMillionOver200kInput,
            'costPerMillionOver200kOutput' => $e->costPerMillionOver200kOutput,
            'limitMaxInput' => $e->limitMaxInput,
            'limitMaxOutput' => $e->limitMaxOutput,
            'reasoningEnabled' => $e->reasoningEnabled,
            'toolCallEnabled' => $e->toolCallEnabled,
            'structuredOutput' => $e->structuredOutput,
            'openWeights' => $e->openWeights,
            'knowledge' => $e->knowledge,
            'releaseDate' => $e->releaseDate,
        ];
    }

    /**
     * @param array<string, mixed> $r
     */
    private static function entryFromPlainRow(array $r): ?ModelCatalogEntry
    {
        try {
            $id = (string) ($r['id'] ?? '');
            $providerId = (string) ($r['providerId'] ?? '');
            if ('' === $id || '' === $providerId) {
                return null;
            }

            $in = self::stringList($r['inputModalities'] ?? ['text']);
            $outM = self::stringList($r['outputModalities'] ?? ['text']);
            if ([] === $in) {
                $in = ['text'];
            }
            if ([] === $outM) {
                $outM = ['text'];
            }

            return new ModelCatalogEntry(
                new ModelId($id),
                new ProviderId($providerId),
                (string) ($r['name'] ?? $id),
                isset($r['contextLength']) && is_numeric($r['contextLength']) ? (int) $r['contextLength'] : null,
                $in,
                $outM,
                self::stringList($r['capabilities'] ?? []),
                family: self::optionalString($r['family'] ?? null),
                costPerMillionInput: self::optionalFloat($r['costPerMillionInput'] ?? null),
                costPerMillionOutput: self::optionalFloat($r['costPerMillionOutput'] ?? null),
                costPerMillionReasoning: self::optionalFloat($r['costPerMillionReasoning'] ?? null),
                costPerMillionOver200kInput: self::optionalFloat($r['costPerMillionOver200kInput'] ?? null),
                costPerMillionOver200kOutput: self::optionalFloat($r['costPerMillionOver200kOutput'] ?? null),
                limitMaxInput: isset($r['limitMaxInput']) && is_numeric($r['limitMaxInput']) ? (int) $r['limitMaxInput'] : null,
                limitMaxOutput: isset($r['limitMaxOutput']) && is_numeric($r['limitMaxOutput']) ? (int) $r['limitMaxOutput'] : null,
                reasoningEnabled: (bool) ($r['reasoningEnabled'] ?? false),
                toolCallEnabled: (bool) ($r['toolCallEnabled'] ?? false),
                structuredOutput: \array_key_exists('structuredOutput', $r)
                    ? (null === $r['structuredOutput'] ? null : (bool) $r['structuredOutput'])
                    : null,
                openWeights: (bool) ($r['openWeights'] ?? false),
                knowledge: self::optionalString($r['knowledge'] ?? null),
                releaseDate: self::optionalString($r['releaseDate'] ?? null),
            );
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    private static function optionalString(mixed $v): ?string
    {
        if (!\is_string($v) || '' === $v) {
            return null;
        }

        return $v;
    }

    private static function optionalFloat(mixed $v): ?float
    {
        if (null === $v) {
            return null;
        }
        if (\is_int($v) || \is_float($v)) {
            return (float) $v;
        }
        if (\is_string($v) && is_numeric($v)) {
            return (float) $v;
        }

        return null;
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
