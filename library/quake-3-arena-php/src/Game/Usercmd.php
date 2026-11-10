<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * Subset of usercmd_t — movement intent for one Pmove step.
 */
final class Usercmd
{
    public const BUTTON_ATTACK = 1;
    public const BUTTON_WALKING = 16; // Q3 BUTTON_WALKING
    public const BUTTON_JUMP = 0; // Q3 jump is upmove, not a button bit for CheckJump

    public function __construct(
        public int $serverTime = 0,
        public int $forwardmove = 0, // -127..127
        public int $rightmove = 0,
        public int $upmove = 0,
        public int $buttons = 0,
        /** @var list<float> pitch/yaw/roll degrees (we set absolute for SDL mouse) */
        public array $angles = [0.0, 0.0, 0.0],
    ) {
    }
}
