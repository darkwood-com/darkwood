<?php

declare(strict_types=1);

namespace App\Token\Optimizer;

use App\Token\OptimizeResult;

/**
 * Drop empty/null values; replace noise keys with #ref placeholders (structural).
 * Groups padding_* / noise_* under one coarse omission when possible.
 */
final class JsonOptimizer
{
    private const NOISE_KEYS = [
        'traceId',
        'trace_id',
        'requestId',
        'request_id',
        'correlationId',
        'correlation_id',
        'spanId',
        'span_id',
        'sessionId',
        'session_id',
        '__meta',
        '_meta',
        'debug',
        'debugInfo',
        'telemetry',
        'headers',
        'cookies',
        'userAgent',
        'user_agent',
        'timing',
        'timings',
        'metrics',
        'emptyFields',
        'padding',
        'noise',
    ];

    public function optimize(string $content): OptimizeResult
    {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return new OptimizeResult($content);
        }

        /** @var list<array{reason: string, content: string, marker_placeholder: string}> $omissions */
        $omissions = [];
        $paddingBucket = [];
        $cleaned = $this->clean($decoded, $omissions, $paddingBucket);

        if ($paddingBucket !== []) {
            $placeholder = '{{REF_' . count($omissions) . '}}';
            $cleaned['_omitted_padding'] = $placeholder;
            $omissions[] = [
                'reason' => 'grouped padding/noise blobs',
                'content' => json_encode($paddingBucket, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '',
                'marker_placeholder' => $placeholder,
            ];
        }

        $visible = json_encode($cleaned, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: $content;

        return new OptimizeResult($visible, $omissions);
    }

    /**
     * @param list<array{reason: string, content: string, marker_placeholder: string}> $omissions
     * @param array<string, mixed>                                                     $paddingBucket
     */
    private function clean(mixed $value, array &$omissions, array &$paddingBucket): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $isList = array_is_list($value);
        $result = [];

        foreach ($value as $key => $item) {
            if (!$isList && is_string($key)) {
                if ($this->isPaddingKey($key)) {
                    $paddingBucket[$key] = $item;
                    continue;
                }

                if ($this->isNoiseKey($key)) {
                    if ($item === null || $item === '' || $item === []) {
                        continue;
                    }
                    $placeholder = '{{REF_' . count($omissions) . '}}';
                    $encoded = json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '';
                    $omissions[] = [
                        'reason' => 'omitted json field: ' . $key,
                        'content' => $encoded,
                        'marker_placeholder' => $placeholder,
                    ];
                    $result[$key] = $placeholder;
                    continue;
                }
            }

            if ($item === null || $item === '' || $item === []) {
                continue;
            }

            $cleaned = $this->clean($item, $omissions, $paddingBucket);
            if ($cleaned === null || $cleaned === '' || $cleaned === []) {
                continue;
            }

            if ($isList) {
                $result[] = $cleaned;
            } else {
                $result[$key] = $cleaned;
            }
        }

        return $result;
    }

    private function isNoiseKey(string $key): bool
    {
        return in_array($key, self::NOISE_KEYS, true);
    }

    private function isPaddingKey(string $key): bool
    {
        return str_starts_with($key, 'padding_') || str_starts_with($key, 'noise_');
    }
}
