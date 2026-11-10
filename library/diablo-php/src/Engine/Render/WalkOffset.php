<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

use Diablo\Engine\AnimationInfo;
use Diablo\Engine\Direction;

/**
 * Walking sprite offset from animation progress.
 */
final class WalkOffset
{
    private const MOVING_OFFSET = [
        Direction::S => [0, 32],
        Direction::SW => [-32, 16],
        Direction::W => [-64, 0],
        Direction::NW => [-32, -16],
        Direction::N => [0, -32],
        Direction::NE => [32, -16],
        Direction::E => [64, 0],
        Direction::SE => [32, 16],
    ];

    public const BASE_VALUE_FRACTION = AnimationInfo::BASE_VALUE_FRACTION;
    public const WALK_FRAMES = 8;

    /**
     * @return array{0:int,1:int}
     */
    public static function fromAnimInfo(AnimationInfo $anim, int $dir, bool $cameraMode = false): array
    {
        $progress = $anim->getAnimationProgress();
        [$ox, $oy] = self::MOVING_OFFSET[$dir] ?? [0, 0];
        $x = (int) intdiv($ox * $progress, self::BASE_VALUE_FRACTION);
        $y = (int) intdiv($oy * $progress, self::BASE_VALUE_FRACTION);
        if ($cameraMode) {
            return [-$x, -$y];
        }

        return [$x, $y];
    }

    /**
     * Legacy helper (tests / debug) — prefer fromAnimInfo.
     * @return array{0:int,1:int}
     */
    public static function get(
        int $dir,
        int $currentFrame,
        int $numberOfFrames = self::WALK_FRAMES,
        bool $cameraMode = false,
        float $tickAlpha = 0.0,
    ): array {
        $anim = new AnimationInfo();
        $anim->setNewAnimation($numberOfFrames, 1, max(0, $currentFrame));
        $anim->progressToNextGameTick = (int) round(max(0.0, min(1.0, $tickAlpha)) * self::BASE_VALUE_FRACTION);

        return self::fromAnimInfo($anim, $dir, $cameraMode);
    }

    /** DX WalkSettings mode for direction. */
    public static function walkModeForDir(int $dir): string
    {
        return match ($dir) {
            Direction::S, Direction::SW, Direction::SE => 'WALK_SOUTHWARDS',
            Direction::W, Direction::E => 'WALK_SIDEWAYS',
            default => 'WALK_NORTHWARDS',
        };
    }

    /**
     * Displacement::screenToLight — pixel walk offset → 1/8-tile light cone offset.
     * @return array{0:int,1:int}
     */
    public static function screenToLight(int $screenX, int $screenY): array
    {
        return [
            intdiv(2 * $screenY + $screenX, 8),
            intdiv(2 * $screenY - $screenX, 8),
        ];
    }
}
