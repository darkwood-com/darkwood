<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Placeholder animation frame index from mode/direction/tick.
 */
final class Animation
{
    public static function frame(string $mode, int $direction, int $actionFrame, int $walkFrames, int $walkTotal): int
    {
        return match ($mode) {
            'WALK' => abs($walkTotal - $walkFrames) % 8,
            'ATTACK', 'RANGED', 'SPELL' => $actionFrame % 8,
            'GOT_HIT' => min(3, $actionFrame),
            'DEATH' => min(7, $actionFrame),
            default => 0,
        };
    }

    /** @return array{0:int,1:int,2:int} RGB */
    public static function colorForActor(string $kind, string $mode): array
    {
        if ($mode === 'DEATH') {
            return [80, 80, 80];
        }
        if ($mode === 'GOT_HIT') {
            return [255, 200, 200];
        }

        return match ($kind) {
            'player' => [200, 60, 50],
            'zombie' => [90, 140, 70],
            'skeleton' => [200, 200, 180],
            'fallen' => [160, 40, 40],
            'archer' => [60, 100, 160],
            default => [140, 140, 140],
        };
    }
}
