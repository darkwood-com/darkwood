<?php

declare(strict_types=1);

namespace App\Infrastructure\Video\Provider\Replicate;

use InvalidArgumentException;

use function sprintf;

/**
 * Named Replicate video targets for Scene 1 benchmarking.
 *
 * Override or extend inputs per model via the video provider `replicate_input` option
 * (merged after preset defaults).
 */
final class ReplicateVideoModelPresets
{
    public const HAILUO = 'hailuo';
    public const SEEDANCE = 'seedance';
    /** Same model as {@see self::SEEDANCE} with vertical aspect ratio for short-form / reels. */
    public const SEEDANCE_9_16 = 'seedance_9_16';
    /** ByteDance Seedance 2.0 Fast on Replicate (see preset input for resolution defaults). */
    public const SEEDANCE_2_FAST = 'seedance_2_fast';
    /** Same model as {@see self::SEEDANCE_2_FAST} with vertical aspect ratio for short-form / reels. */
    public const SEEDANCE_2_FAST_9_16 = 'seedance_2_fast_9_16';
    public const P_VIDEO_DRAFT = 'p_video_draft';

    /** CLI alias for {@see self::P_VIDEO_DRAFT} (`prunaai/p-video`). */
    public const CLI_VIDEO_PVIDEO = 'pvideo';

    /** CLI alias for {@see self::SEEDANCE_2_FAST}. */
    public const CLI_VIDEO_SEEDANCE_2_FAST = 'seedance2fast';

    /** Defaults for {@see self::SEEDANCE} and {@see self::SEEDANCE_9_16} (`bytedance/seedance-1-lite`). */
    private const SEEDANCE_DEFAULT_INPUT = [
        'fps' => 24,
        'resolution' => '480p',
    ];

    /** Defaults for {@see self::SEEDANCE_2_FAST} and {@see self::SEEDANCE_2_FAST_9_16} (`bytedance/seedance-2.0-fast`). */
    private const SEEDANCE_2_FAST_DEFAULT_INPUT = [
        'resolution' => '480p',
    ];

    /** @var array<string, array{model: string, input: array<string, mixed>}> */
    private const PRESETS = [
        self::HAILUO => [
            'model' => 'minimax/hailuo-02-fast',
            'input' => [],
        ],
        self::SEEDANCE => [
            'model' => 'bytedance/seedance-1-lite',
            'input' => self::SEEDANCE_DEFAULT_INPUT,
        ],
        self::SEEDANCE_9_16 => [
            'model' => 'bytedance/seedance-1-lite',
            'input' => self::SEEDANCE_DEFAULT_INPUT + [
                'aspect_ratio' => '9:16',
            ],
        ],
        self::SEEDANCE_2_FAST => [
            'model' => 'bytedance/seedance-2.0-fast',
            'input' => self::SEEDANCE_2_FAST_DEFAULT_INPUT,
        ],
        self::SEEDANCE_2_FAST_9_16 => [
            'model' => 'bytedance/seedance-2.0-fast',
            'input' => self::SEEDANCE_2_FAST_DEFAULT_INPUT + [
                'aspect_ratio' => '9:16',
            ],
        ],
        self::P_VIDEO_DRAFT => [
            'model' => 'prunaai/p-video',
            'input' => ['draft' => true],
        ],
    ];

    /**
     * @return list<string>
     */
    public static function presetKeys(): array
    {
        return array_keys(self::PRESETS);
    }

    /**
     * Default Scene 1 presets for CLI `--benchmark-video` (excludes p-video).
     * Add {@see self::P_VIDEO_DRAFT} with `--include-pvideo`.
     *
     * @return list<string>
     */
    public static function coreBenchmarkPresetKeys(): array
    {
        return [self::HAILUO, self::SEEDANCE];
    }

    /**
     * Allowed values for `--video-model`.
     *
     * @return list<string>
     */
    public static function cliVideoModelChoices(): array
    {
        return [self::HAILUO, self::SEEDANCE, self::CLI_VIDEO_SEEDANCE_2_FAST, self::CLI_VIDEO_PVIDEO];
    }

    /**
     * Map CLI `--video-model` value to an internal preset key.
     */
    public static function presetKeyFromCliVideoModel(string $cli): string
    {
        $normalized = strtolower(trim($cli));
        if ($normalized === self::CLI_VIDEO_PVIDEO) {
            return self::P_VIDEO_DRAFT;
        }

        if ($normalized === self::CLI_VIDEO_SEEDANCE_2_FAST) {
            return self::SEEDANCE_2_FAST;
        }

        if ($normalized === self::HAILUO || $normalized === self::SEEDANCE) {
            return $normalized;
        }

        throw new InvalidArgumentException(sprintf(
            'Unknown --video-model "%s". Use one of: %s',
            $cli,
            implode(', ', self::cliVideoModelChoices())
        ));
    }

    /**
     * @return array{model: string, input: array<string, mixed>}
     */
    public static function resolve(string $presetKey): array
    {
        if (!isset(self::PRESETS[$presetKey])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown Replicate video preset "%s". Known: %s',
                $presetKey,
                implode(', ', self::presetKeys())
            ));
        }

        return self::PRESETS[$presetKey];
    }
}
