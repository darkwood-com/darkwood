<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * Plasma bolt (g_missile.c fire_plasma).
 */
final class Projectile
{
    public const SPEED = 2000.0;
    public const DAMAGE = 20;
    public const SPLASH_DAMAGE = 15;
    public const SPLASH_RADIUS = 20.0;
    public const LIFETIME = 10.0;

    /** @param list<float> $origin @param list<float> $velocity */
    public function __construct(
        public array $origin,
        public array $velocity,
        public float $dieAt,
        public bool $alive = true,
        public int $owner = 0,
        /** Last trail spawn time (CG_PlasmaTrail / cent->trailTime). */
        public float $trailTime = 0.0,
    ) {
    }
}
