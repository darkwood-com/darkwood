<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Video\Provider\Replicate;

use App\Infrastructure\Video\Provider\Replicate\ReplicateVideoInputMapper;
use App\Infrastructure\Video\Provider\Replicate\ReplicateVideoModelPresets;
use PHPUnit\Framework\TestCase;

final class ReplicateVideoInputMapperTest extends TestCase
{
    public function testHailuoMapsPromptDurationSeed(): void
    {
        $m = new ReplicateVideoInputMapper();
        $preset = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::HAILUO);
        $out = $m->buildInput($preset['model'], $preset['input'], 'Hello', [
            'duration' => 6,
            'seed' => 42,
        ]);

        self::assertSame('Hello', $out['prompt']);
        self::assertSame(6, $out['duration']);
        self::assertSame(42, $out['seed']);
    }

    public function testSeedanceMapsLikeHailuo(): void
    {
        $m = new ReplicateVideoInputMapper();
        $preset = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::SEEDANCE);
        $out = $m->buildInput($preset['model'], $preset['input'], 'Scene', ['duration' => 5]);

        self::assertSame('Scene', $out['prompt']);
        self::assertSame(5, $out['duration']);
        self::assertSame(24, $out['fps']);
        self::assertSame('480p', $out['resolution']);
    }

    public function testSeedance2FastMapsPromptAndResolution(): void
    {
        $m = new ReplicateVideoInputMapper();
        $preset = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::SEEDANCE_2_FAST);
        $out = $m->buildInput($preset['model'], $preset['input'], 'Clip', ['duration' => 8]);

        self::assertSame('Clip', $out['prompt']);
        self::assertSame(8, $out['duration']);
        self::assertSame('480p', $out['resolution']);
    }

    public function testPVideoCoercesDraftBoolean(): void
    {
        $m = new ReplicateVideoInputMapper();
        $preset = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::P_VIDEO_DRAFT);
        $out = $m->buildInput($preset['model'], $preset['input'], 'Draft clip', []);

        self::assertTrue($out['draft']);
        self::assertSame('Draft clip', $out['prompt']);
    }

    public function testReplicateInputMergesOverPreset(): void
    {
        $m = new ReplicateVideoInputMapper();
        $preset = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::P_VIDEO_DRAFT);
        $out = $m->buildInput($preset['model'], $preset['input'], 'X', [
            'replicate_input' => ['draft' => false],
        ]);

        self::assertFalse($out['draft']);
    }
}
