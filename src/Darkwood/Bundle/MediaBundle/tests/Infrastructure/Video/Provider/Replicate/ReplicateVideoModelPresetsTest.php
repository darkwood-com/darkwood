<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Video\Provider\Replicate;

use App\Infrastructure\Video\Provider\Replicate\ReplicateVideoModelPresets;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ReplicateVideoModelPresetsTest extends TestCase
{
    public function testResolveHailuo()
    {
        $r = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::HAILUO);
        self::assertSame('minimax/hailuo-02-fast', $r['model']);
        self::assertSame([], $r['input']);
    }

    public function testResolveSeedance()
    {
        $r = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::SEEDANCE);
        self::assertSame('bytedance/seedance-1-lite', $r['model']);
        self::assertSame(24, $r['input']['fps'] ?? null);
        self::assertSame('480p', $r['input']['resolution'] ?? null);
    }

    public function testResolveSeedance2Fast()
    {
        $r = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::SEEDANCE_2_FAST);
        self::assertSame('bytedance/seedance-2.0-fast', $r['model']);
        self::assertSame('480p', $r['input']['resolution'] ?? null);
    }

    public function testResolveSeedance2Fast916()
    {
        $r = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::SEEDANCE_2_FAST_9_16);
        self::assertSame('bytedance/seedance-2.0-fast', $r['model']);
        self::assertSame('9:16', $r['input']['aspect_ratio'] ?? null);
    }

    public function testResolvePVideoDraftSetsDraftFlag()
    {
        $r = ReplicateVideoModelPresets::resolve(ReplicateVideoModelPresets::P_VIDEO_DRAFT);
        self::assertSame('prunaai/p-video', $r['model']);
        self::assertTrue($r['input']['draft'] ?? false);
    }

    public function testResolveUnknownThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        ReplicateVideoModelPresets::resolve('no-such-preset');
    }

    public function testCoreBenchmarkExcludesPVideo()
    {
        self::assertSame(
            [ReplicateVideoModelPresets::HAILUO, ReplicateVideoModelPresets::SEEDANCE],
            ReplicateVideoModelPresets::coreBenchmarkPresetKeys()
        );
    }

    public function testPresetKeyFromCliVideoModel()
    {
        self::assertSame(
            ReplicateVideoModelPresets::P_VIDEO_DRAFT,
            ReplicateVideoModelPresets::presetKeyFromCliVideoModel('pvideo')
        );
        self::assertSame(
            ReplicateVideoModelPresets::HAILUO,
            ReplicateVideoModelPresets::presetKeyFromCliVideoModel('HAILUO')
        );
        self::assertSame(
            ReplicateVideoModelPresets::SEEDANCE_2_FAST,
            ReplicateVideoModelPresets::presetKeyFromCliVideoModel('seedance2fast')
        );
    }

    public function testPresetKeyFromCliUnknownThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        ReplicateVideoModelPresets::presetKeyFromCliVideoModel('other');
    }
}
