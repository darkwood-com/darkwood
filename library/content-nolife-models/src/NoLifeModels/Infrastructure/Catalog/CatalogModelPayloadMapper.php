<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Catalog;

/**
 * Maps models.dev-shaped model objects (API or JSON snippets) into display metadata.
 */
final class CatalogModelPayloadMapper
{
    /**
     * @param array<string, mixed> $m
     *
     * @return array{
     *     family: ?string,
     *     costPerMillionInput: ?float,
     *     costPerMillionOutput: ?float,
     *     costPerMillionReasoning: ?float,
     *     costPerMillionOver200kInput: ?float,
     *     costPerMillionOver200kOutput: ?float,
     *     limitMaxInput: ?int,
     *     limitMaxOutput: ?int,
     *     reasoningEnabled: bool,
     *     toolCallEnabled: bool,
     *     structuredOutput: ?bool,
     *     openWeights: bool,
     *     knowledge: ?string,
     *     releaseDate: ?string,
     * }
     */
    public static function displayFields(array $m): array
    {
        $family = self::optionalString($m['family'] ?? null);
        $cost = $m['cost'] ?? null;
        $cIn = $cOut = $cReason = null;
        $o200kIn = $o200kOut = null;
        if (\is_array($cost)) {
            $cIn = self::optionalFloat($cost['input'] ?? null);
            $cOut = self::optionalFloat($cost['output'] ?? null);
            $cReason = self::optionalFloat($cost['reasoning'] ?? null);
            $over = $cost['context_over_200k'] ?? null;
            if (\is_array($over)) {
                $o200kIn = self::optionalFloat($over['input'] ?? null);
                $o200kOut = self::optionalFloat($over['output'] ?? null);
            }
        }
        $limit = $m['limit'] ?? null;
        $limIn = $limOut = null;
        if (\is_array($limit)) {
            $limIn = isset($limit['input']) ? (int) $limit['input'] : null;
            $limOut = isset($limit['output']) ? (int) $limit['output'] : null;
        }

        $caps = self::stringList($m['capabilities'] ?? []);
        $reasoning = \array_key_exists('reasoning', $m)
            ? (bool) $m['reasoning']
            : \in_array('reasoning', $caps, true);
        $toolCall = \array_key_exists('tool_call', $m)
            ? (bool) $m['tool_call']
            : \in_array('tool_call', $caps, true);
        $structured = null;
        if (\array_key_exists('structured_output', $m)) {
            $structured = (bool) $m['structured_output'];
        }
        $openWeights = (bool) ($m['open_weights'] ?? false);
        $knowledge = self::optionalString($m['knowledge'] ?? null);
        $release = self::optionalString($m['release_date'] ?? $m['releaseDate'] ?? null);

        return [
            'family' => $family,
            'costPerMillionInput' => $cIn,
            'costPerMillionOutput' => $cOut,
            'costPerMillionReasoning' => $cReason,
            'costPerMillionOver200kInput' => $o200kIn,
            'costPerMillionOver200kOutput' => $o200kOut,
            'limitMaxInput' => $limIn,
            'limitMaxOutput' => $limOut,
            'reasoningEnabled' => $reasoning,
            'toolCallEnabled' => $toolCall,
            'structuredOutput' => $structured,
            'openWeights' => $openWeights,
            'knowledge' => $knowledge,
            'releaseDate' => $release,
        ];
    }

    /**
     * @param array<string, mixed> $m
     */
    public static function resolvedContextLength(array $m): ?int
    {
        if (isset($m['contextLength']) && is_numeric($m['contextLength'])) {
            return (int) $m['contextLength'];
        }
        $limit = $m['limit'] ?? null;
        if (\is_array($limit) && isset($limit['context']) && is_numeric($limit['context'])) {
            return (int) $limit['context'];
        }

        return null;
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
