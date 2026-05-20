<?php

declare(strict_types=1);

namespace App\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Ensures video.real_for_first_scene_only is wired identically for video and voice scene-aware providers.
 */
final class VideoFirstSceneRoutingParameterTest extends KernelTestCase
{
    public function testVideoAndVoiceFirstSceneFlagsShareTheSameResolvedParameter(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $video = $container->getParameter('video.video.real_for_first_scene_only');
        $voice = $container->getParameter('video.voice.real_for_first_scene_only');
        $shared = $container->getParameter('video.real_for_first_scene_only');

        self::assertIsBool($video);
        self::assertIsBool($voice);
        self::assertIsBool($shared);
        self::assertSame($shared, $video);
        self::assertSame($shared, $voice);
    }
}
