<?php

declare(strict_types=1);

namespace Diablo\Engine;

/**
 * Player destAction / walkpath constants.
 */
final class DestAction
{
    public const NONE = 0;
    public const WALK = 1;
    public const ATTACK_MON = 2;
    public const OPERATE = 3;
    public const TALK = 4;
    public const PICKUP = 5;
    public const SPELL = 6;
    /** player.h ACTION_RATTACKMON — standing ranged shot at monster */
    public const RATTACK_MON = 7;

    /** walkpath terminator — player.h WALK_NONE */
    public const WALK_NONE = -1;
}
