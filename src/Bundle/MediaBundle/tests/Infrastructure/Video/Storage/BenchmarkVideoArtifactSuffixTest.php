<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Video\Storage;

use App\Infrastructure\Video\Provider\Replicate\ReplicateVideoModelPresets;
use App\Infrastructure\Video\Storage\BenchmarkVideoArtifactSuffix;
use PHPUnit\Framework\TestCase;

final class BenchmarkVideoArtifactSuffixTest extends TestCase
{
    public function testEmptyOptionsReturnsNull(): void
    {
        self::assertNull(BenchmarkVideoArtifactSuffix::resolve([]));
    }

    public function testExplicitVideoArtifactKey(): void
    {
        self::assertSame(
            'my-custom-key',
            BenchmarkVideoArtifactSuffix::resolve(['video_artifact_key' => 'My Custom Key'])
        );
    }

    public function testHailuoPresetMatchesModelBasename(): void
    {
        self::assertSame(
            'hailuo-02-fast',
            BenchmarkVideoArtifactSuffix::resolve([
                'replicate_preset' => ReplicateVideoModelPresets::HAILUO,
            ])
        );
    }

    public function testSeedancePreset(): void
    {
        self::assertSame(
            'seedance-1-lite',
            BenchmarkVideoArtifactSuffix::resolve([
                'replicate_preset' => ReplicateVideoModelPresets::SEEDANCE,
            ])
        );
    }

    public function testSeedance2FastPreset(): void
    {
        self::assertSame(
            'seedance-2-0-fast',
            BenchmarkVideoArtifactSuffix::resolve([
                'replicate_preset' => ReplicateVideoModelPresets::SEEDANCE_2_FAST,
            ])
        );
    }

    public function testPVideoDraftPresetAppendsDraft(): void
    {
        self::assertSame(
            'p-video-draft',
            BenchmarkVideoArtifactSuffix::resolve([
                'replicate_preset' => ReplicateVideoModelPresets::P_VIDEO_DRAFT,
            ])
        );
    }

    public function testModelOverrideWithoutPreset(): void
    {
        self::assertSame(
            'some-model',
            BenchmarkVideoArtifactSuffix::resolve([
                'replicate_model' => 'org/some-model',
            ])
        );
    }

    public function testReplicateInputDraftSuffixWithoutPresetDraftDefaults(): void
    {
        self::assertSame(
            'p-video-draft',
            BenchmarkVideoArtifactSuffix::resolve([
                'replicate_model' => 'prunaai/p-video',
                'replicate_input' => ['draft' => true],
            ])
        );
    }
}
