<?php

declare(strict_types=1);

namespace Quake3\Game;

use Quake3\Collision\CollisionModel;
use Quake3\Collision\TraceResult;

/**
 * Subset of bg_pmove.c / bg_slidemove.c — walk, air, jump, slide, step, duck.
 * Water / spectator / items deferred.
 */
final class Pmove
{
    private const STEPSIZE = 18.0;
    private const JUMP_VELOCITY = 270.0;
    private const MIN_WALK_NORMAL = 0.7;
    private const OVERCLIP = 1.001;
    private const MAX_CLIP_PLANES = 5;
    private const ENTITYNUM_NONE = -1;

    private const PM_STOPSPEED = 100.0;
    private const PM_ACCELERATE = 10.0;
    private const PM_AIRACCELERATE = 1.0;
    private const PM_FRICTION = 6.0;
    private const PM_DUCK_SCALE = 0.25;

    public int $pmoveMsec = 8;

    private Player $ps;
    private Usercmd $cmd;
    private CollisionModel $cm;
    private int $tracemask;

    private float $frametime = 0.0;
    private int $msec = 0;
    /** @var list<float> */
    private array $forward = [0.0, 0.0, 0.0];
    /** @var list<float> */
    private array $right = [0.0, 0.0, 0.0];
    /** @var list<float> */
    private array $up = [0.0, 0.0, 0.0];
    /** @var list<float> */
    private array $previousOrigin = [0.0, 0.0, 0.0];
    /** @var list<float> */
    private array $previousVelocity = [0.0, 0.0, 0.0];
    private bool $walking = false;
    private bool $groundPlane = false;
    private TraceResult $groundTrace;
    private float $impactSpeed = 0.0;

    public function __construct(CollisionModel $cm, int $tracemask = CollisionModel::MASK_PLAYERSOLID)
    {
        $this->cm = $cm;
        $this->tracemask = $tracemask;
        $this->groundTrace = new TraceResult();
    }

    /**
     * Run Pmove for one usercmd (may split by pmove_msec like Pmove()).
     */
    public function move(Player $player, Usercmd $cmd): void
    {
        $this->ps = $player;
        $player->justJumped = false;
        $player->justLanded = false;
        $player->justFootstep = false;
        $player->justFootstepMetal = false;
        $player->justStep = 0;
        $finalTime = $cmd->serverTime;

        if ($finalTime < $player->commandTime) {
            return;
        }
        if ($finalTime > $player->commandTime + 1000) {
            $player->commandTime = $finalTime - 1000;
        }

        $cmd->angles = $player->viewangles;

        while ($player->commandTime !== $finalTime) {
            $msec = $finalTime - $player->commandTime;
            if ($msec > $this->pmoveMsec) {
                $msec = $this->pmoveMsec;
            }
            $step = clone $cmd;
            $step->serverTime = $player->commandTime + $msec;
            $this->cmd = $step;
            $this->pmoveSingle();
        }
    }

    private function pmoveSingle(): void
    {
        $this->msec = $this->cmd->serverTime - $this->ps->commandTime;
        if ($this->msec < 1) {
            $this->msec = 1;
        } elseif ($this->msec > 200) {
            $this->msec = 200;
        }
        $this->ps->commandTime = $this->cmd->serverTime;
        $this->frametime = $this->msec * 0.001;

        $this->previousOrigin = $this->ps->origin;
        $this->previousVelocity = $this->ps->velocity;
        $this->walking = false;
        $this->groundPlane = false;
        $this->impactSpeed = 0.0;

        $this->ps->viewangles = $this->cmd->angles;
        $this->angleVectors($this->ps->viewangles, $this->forward, $this->right, $this->up);

        if ($this->cmd->upmove < 10) {
            $this->ps->pmFlags &= ~Player::PMF_JUMP_HELD;
        }

        $this->checkDuck();
        if ($this->ps->alive
            && ($this->ps->pmFlags & Player::PMF_RESPAWNED) !== 0
            && ($this->cmd->buttons & Usercmd::BUTTON_ATTACK) === 0
        ) {
            $this->ps->pmFlags &= ~Player::PMF_RESPAWNED;
        }

        $this->groundTrace();
        if (!$this->ps->alive) {
            $this->deadMove();
        }
        $this->dropTimers();

        if ($this->walking) {
            $this->walkMove();
        } else {
            $this->airMove();
        }

        if ($this->ps->alive) {
            $this->footsteps();
        }
        $this->groundTrace();
        $this->ps->onGround = $this->walking;
    }

    /** PM_CheckDuck — mins/maxs/viewheight from upmove. */
    private function checkDuck(): void
    {
        $this->ps->mins = Player::MINS;
        $this->ps->maxs = [Player::MAXS[0], Player::MAXS[1], Player::MAXS[2]];

        if (!$this->ps->alive) {
            $this->ps->maxs[2] = -8.0;
            $this->ps->viewheight = -16; // DEAD_VIEWHEIGHT
            return;
        }

        if ($this->cmd->upmove < 0) {
            $this->ps->pmFlags |= Player::PMF_DUCKED;
        } elseif (($this->ps->pmFlags & Player::PMF_DUCKED) !== 0) {
            // try to stand up
            $standMaxs = [Player::MAXS[0], Player::MAXS[1], Player::MAXS[2]];
            $tr = $this->cm->trace(
                $this->ps->origin,
                $this->ps->origin,
                $this->ps->mins,
                $standMaxs,
                $this->tracemask,
            );
            if (!$tr->allSolid && !$tr->startSolid) {
                $this->ps->pmFlags &= ~Player::PMF_DUCKED;
            }
        }

        $oldHeight = $this->ps->viewheight;
        if (($this->ps->pmFlags & Player::PMF_DUCKED) !== 0) {
            $this->ps->maxs[2] = Player::CROUCH_MAXS_Z;
            $this->ps->viewheight = Player::CROUCH_VIEWHEIGHT;
        } else {
            $this->ps->maxs[2] = Player::MAXS[2];
            $this->ps->viewheight = Player::DEFAULT_VIEWHEIGHT;
        }
        if ($this->ps->viewheight !== $oldHeight) {
            $this->ps->duckChange = (float) ($this->ps->viewheight - $oldHeight);
            $this->ps->duckTime = (float) $this->ps->commandTime;
        }
    }

    /** PM_DeadMove — extra ground friction on corpse. */
    private function deadMove(): void
    {
        if (!$this->walking) {
            return;
        }
        $forward = sqrt(
            $this->ps->velocity[0] * $this->ps->velocity[0]
            + $this->ps->velocity[1] * $this->ps->velocity[1]
            + $this->ps->velocity[2] * $this->ps->velocity[2]
        );
        $forward -= 20.0;
        if ($forward <= 0.0) {
            $this->ps->velocity = [0.0, 0.0, 0.0];
            return;
        }
        $inv = 1.0 / sqrt(
            $this->ps->velocity[0] * $this->ps->velocity[0]
            + $this->ps->velocity[1] * $this->ps->velocity[1]
            + $this->ps->velocity[2] * $this->ps->velocity[2]
        );
        $this->ps->velocity = [
            $this->ps->velocity[0] * $inv * $forward,
            $this->ps->velocity[1] * $inv * $forward,
            $this->ps->velocity[2] * $inv * $forward,
        ];
    }

    /**
     * PM_Footsteps bobCycle advance + EV_FOOTSTEP on cycle boundary (bg_pmove.c).
     */
    private function footsteps(): void
    {
        $xyspeed = sqrt(
            $this->ps->velocity[0] * $this->ps->velocity[0]
            + $this->ps->velocity[1] * $this->ps->velocity[1]
        );

        if (!$this->walking) {
            return; // airborne: leave bobCycle intact
        }

        if ($this->cmd->forwardmove === 0 && $this->cmd->rightmove === 0) {
            if ($xyspeed < 5) {
                $this->ps->bobCycle = 0;
            }
            return;
        }

        if (($this->ps->pmFlags & Player::PMF_DUCKED) !== 0) {
            $bobmove = 0.5; // ducked characters bob much faster
            $doFootstep = false; // ducked characters never play footsteps
        } elseif (($this->cmd->buttons & Usercmd::BUTTON_WALKING) !== 0) {
            $bobmove = 0.3; // walking
            $doFootstep = false;
        } else {
            // run bob — Q3 footstep=qtrue, no xyspeed gate
            $bobmove = 0.4;
            $doFootstep = true;
        }
        $old = $this->ps->bobCycle;
        $this->ps->bobCycle = ((int) ($old + $bobmove * $this->msec)) & 255;

        // crossed half-cycle boundary → EV_FOOTSTEP / EV_FOOTSTEP_METAL (bg_pmove.c PM_FootstepForSurface)
        if (((($old + 64) ^ ($this->ps->bobCycle + 64)) & 128) !== 0 && $doFootstep) {
            $this->ps->justFootstep = true;
            // SURF_METALSTEPS 0x1000
            $this->ps->justFootstepMetal = ($this->groundTrace->surfaceFlags & 0x1000) !== 0;
        }
    }

    private function dropTimers(): void
    {
        if ($this->ps->pmTime > 0) {
            if ($this->msec >= $this->ps->pmTime) {
                $this->ps->pmFlags &= ~Player::PMF_ALL_TIMES;
                $this->ps->pmTime = 0;
            } else {
                $this->ps->pmTime -= $this->msec;
            }
        }
    }

    private function groundTrace(): void
    {
        $point = [
            $this->ps->origin[0],
            $this->ps->origin[1],
            $this->ps->origin[2] - 0.25,
        ];
        $trace = $this->trace($this->ps->origin, $point);
        $this->groundTrace = $trace;

        if ($trace->allSolid) {
            // soft nudge: try slight offsets (simplified CorrectAllSolid)
            $this->walking = false;
            $this->groundPlane = false;
            $this->ps->groundEntityNum = self::ENTITYNUM_NONE;
            return;
        }

        if ($trace->fraction === 1.0) {
            if ($this->ps->groundEntityNum !== self::ENTITYNUM_NONE) {
                // was on ground, now falling — optional trace down farther omitted
            }
            $this->groundPlane = false;
            $this->walking = false;
            $this->ps->groundEntityNum = self::ENTITYNUM_NONE;
            return;
        }

        $n = $trace->planeNormal ?? [0.0, 0.0, 1.0];
        if ($this->ps->velocity[2] > 0 && $this->dot($this->ps->velocity, $n) > 10) {
            $this->ps->groundEntityNum = self::ENTITYNUM_NONE;
            $this->groundPlane = false;
            $this->walking = false;
            return;
        }

        if ($n[2] < self::MIN_WALK_NORMAL) {
            $this->ps->groundEntityNum = self::ENTITYNUM_NONE;
            $this->groundPlane = true;
            $this->walking = false;
            return;
        }

        $this->groundPlane = true;
        $this->walking = true;

        if ($this->ps->groundEntityNum === self::ENTITYNUM_NONE) {
            // just hit the ground
            $this->crashLand($trace);
            // don't do landing time if we were just going down a slope
            if ($this->previousVelocity[2] < -200) {
                $this->ps->pmFlags |= Player::PMF_TIME_LAND;
                $this->ps->pmTime = 250;
            }
        }

        $this->ps->groundEntityNum = $trace->entityNum;
    }

    /**
     * PM_CrashLand — quadratic impact delta → EV_FALL_* / footstep.
     * landChange mirrors cg_event (-24/-16/-8); Application maps damage from those.
     */
    private function crashLand(TraceResult $trace): void
    {
        $dist = $this->ps->origin[2] - $this->previousOrigin[2];
        $vel = $this->previousVelocity[2];
        $acc = -$this->ps->gravity;
        $a = $acc / 2.0;
        $b = $vel;
        $c = -$dist;
        $den = $b * $b - 4.0 * $a * $c;
        if ($den < 0.0) {
            return;
        }
        $t = (-$b - sqrt($den)) / (2.0 * $a);
        $delta = $vel + $t * $acc;
        $delta = $delta * $delta * 0.0001;

        // ducking while falling doubles damage
        if (($this->ps->pmFlags & Player::PMF_DUCKED) !== 0) {
            $delta *= 2.0;
        }

        if ($delta < 1.0) {
            return;
        }

        // SURF_NODAMAGE 0x1 — bounce pads: no crunch / fall damage
        if (($trace->surfaceFlags & 0x1) !== 0) {
            return;
        }

        if ($delta > 60.0) {
            $this->ps->landChange = -24.0; // EV_FALL_FAR
            $this->ps->landTime = (float) $this->ps->commandTime;
            $this->ps->justLanded = true;
        } elseif ($delta > 40.0) {
            if ($this->ps->alive && $this->ps->health > 0) {
                $this->ps->landChange = -16.0; // EV_FALL_MEDIUM
                $this->ps->landTime = (float) $this->ps->commandTime;
                $this->ps->justLanded = true;
            }
        } elseif ($delta > 7.0) {
            $this->ps->landChange = -8.0; // EV_FALL_SHORT
            $this->ps->landTime = (float) $this->ps->commandTime;
            $this->ps->justLanded = true;
        } else {
            // soft land → footstep for surface
            $this->ps->justFootstep = true;
            $this->ps->justFootstepMetal = ($trace->surfaceFlags & 0x1000) !== 0;
        }
    }

    private function checkJump(): bool
    {
        if (($this->ps->pmFlags & Player::PMF_RESPAWNED) !== 0) {
            return false;
        }
        if ($this->cmd->upmove < 10) {
            return false;
        }
        if ($this->ps->pmFlags & Player::PMF_JUMP_HELD) {
            $this->cmd->upmove = 0;
            return false;
        }
        // PMF_TIME_LAND is set on hard land but Q3 PM_CheckJump never gates on it

        $this->groundPlane = false;
        $this->walking = false;
        $this->ps->pmFlags |= Player::PMF_JUMP_HELD;
        $this->ps->groundEntityNum = self::ENTITYNUM_NONE;
        $this->ps->velocity[2] = self::JUMP_VELOCITY;
        $this->ps->justJumped = true;
        return true;
    }

    private function walkMove(): void
    {
        if ($this->checkJump()) {
            $this->airMove();
            return;
        }

        $this->friction();

        $fmove = (float) $this->cmd->forwardmove;
        $smove = (float) $this->cmd->rightmove;
        $scale = $this->cmdScale();

        $forward = $this->forward;
        $right = $this->right;
        $forward[2] = 0.0;
        $right[2] = 0.0;
        $n = $this->groundTrace->planeNormal ?? [0.0, 0.0, 1.0];
        $forward = $this->clipVelocity($forward, $n);
        $right = $this->clipVelocity($right, $n);
        $forward = $this->normalize($forward);
        $right = $this->normalize($right);

        $wishvel = [
            $forward[0] * $fmove + $right[0] * $smove,
            $forward[1] * $fmove + $right[1] * $smove,
            $forward[2] * $fmove + $right[2] * $smove,
        ];
        [$wishdir, $wishspeed] = $this->normalizeRet($wishvel);
        $wishspeed *= $scale;

        // clamp the speed lower if ducking (pm_duckScale 0.25 → 80 ups)
        if (($this->ps->pmFlags & Player::PMF_DUCKED) !== 0) {
            $duck = $this->ps->speed * self::PM_DUCK_SCALE;
            if ($wishspeed > $duck) {
                $wishspeed = $duck;
            }
        }

        // PM_WalkMove: knockback → air accel + keep gravity on Z
        $knockback = ($this->ps->pmFlags & Player::PMF_TIME_KNOCKBACK) !== 0;
        $this->accelerate(
            $wishdir,
            $wishspeed,
            $knockback ? self::PM_AIRACCELERATE : self::PM_ACCELERATE,
        );

        if ($knockback) {
            $this->ps->velocity[2] -= $this->ps->gravity * $this->frametime;
        }

        $vel = $this->length($this->ps->velocity);
        $this->ps->velocity = $this->clipVelocity($this->ps->velocity, $n);
        $this->ps->velocity = $this->normalize($this->ps->velocity);
        $this->ps->velocity = [
            $this->ps->velocity[0] * $vel,
            $this->ps->velocity[1] * $vel,
            $this->ps->velocity[2] * $vel,
        ];

        if ($this->ps->velocity[0] == 0.0 && $this->ps->velocity[1] == 0.0) {
            return;
        }

        $this->stepSlideMove(false);
    }

    private function airMove(): void
    {
        $this->friction();

        $fmove = (float) $this->cmd->forwardmove;
        $smove = (float) $this->cmd->rightmove;
        $scale = $this->cmdScale();

        $forward = $this->forward;
        $right = $this->right;
        $forward[2] = 0.0;
        $right[2] = 0.0;
        $forward = $this->normalize($forward);
        $right = $this->normalize($right);

        $wishvel = [
            $forward[0] * $fmove + $right[0] * $smove,
            $forward[1] * $fmove + $right[1] * $smove,
            0.0,
        ];
        [$wishdir, $wishspeed] = $this->normalizeRet($wishvel);
        $wishspeed *= $scale;

        $this->accelerate($wishdir, $wishspeed, self::PM_AIRACCELERATE);

        if ($this->groundPlane) {
            $n = $this->groundTrace->planeNormal ?? [0.0, 0.0, 1.0];
            $this->ps->velocity = $this->clipVelocity($this->ps->velocity, $n);
        }

        $this->stepSlideMove(true);
    }

    private function friction(): void
    {
        $vel = $this->ps->velocity;
        $vec = $vel;
        if ($this->walking) {
            $vec[2] = 0.0;
        }
        $speed = $this->length($vec);
        if ($speed < 1) {
            $this->ps->velocity[0] = 0.0;
            $this->ps->velocity[1] = 0.0;
            return;
        }

        $drop = 0.0;
        if ($this->walking) {
            // no ground friction while PMF_TIME_KNOCKBACK (teleport spit / damage knockback)
            if (($this->ps->pmFlags & Player::PMF_TIME_KNOCKBACK) === 0) {
                $control = $speed < self::PM_STOPSPEED ? self::PM_STOPSPEED : $speed;
                $drop += $control * self::PM_FRICTION * $this->frametime;
            }
        }

        $newspeed = $speed - $drop;
        if ($newspeed < 0) {
            $newspeed = 0;
        }
        $newspeed /= $speed;
        $this->ps->velocity[0] *= $newspeed;
        $this->ps->velocity[1] *= $newspeed;
        $this->ps->velocity[2] *= $newspeed;
    }

    /** @param list<float> $wishdir */
    private function accelerate(array $wishdir, float $wishspeed, float $accel): void
    {
        $currentspeed = $this->dot($this->ps->velocity, $wishdir);
        $addspeed = $wishspeed - $currentspeed;
        if ($addspeed <= 0) {
            return;
        }
        $accelspeed = $accel * $this->frametime * $wishspeed;
        if ($accelspeed > $addspeed) {
            $accelspeed = $addspeed;
        }
        $this->ps->velocity[0] += $accelspeed * $wishdir[0];
        $this->ps->velocity[1] += $accelspeed * $wishdir[1];
        $this->ps->velocity[2] += $accelspeed * $wishdir[2];
    }

    private function cmdScale(): float
    {
        $max = abs($this->cmd->forwardmove);
        if (abs($this->cmd->rightmove) > $max) {
            $max = abs($this->cmd->rightmove);
        }
        if (abs($this->cmd->upmove) > $max) {
            $max = abs($this->cmd->upmove);
        }
        if ($max === 0) {
            return 0.0;
        }
        $total = sqrt(
            $this->cmd->forwardmove * $this->cmd->forwardmove
            + $this->cmd->rightmove * $this->cmd->rightmove
            + $this->cmd->upmove * $this->cmd->upmove
        );
        return $this->ps->speed * $max / (127.0 * $total);
    }

    private function slideMove(bool $gravity): bool
    {
        $primal = $this->ps->velocity;
        $endVelocity = $primal;
        $numbumps = 4;

        if ($gravity) {
            $endVelocity = $this->ps->velocity;
            $endVelocity[2] -= $this->ps->gravity * $this->frametime;
            $this->ps->velocity[2] = ($this->ps->velocity[2] + $endVelocity[2]) * 0.5;
            $primal[2] = $endVelocity[2];
            if ($this->groundPlane) {
                $n = $this->groundTrace->planeNormal ?? [0.0, 0.0, 1.0];
                $this->ps->velocity = $this->clipVelocity($this->ps->velocity, $n);
            }
        }

        $timeLeft = $this->frametime;
        $planes = [];
        if ($this->groundPlane) {
            $planes[] = $this->groundTrace->planeNormal ?? [0.0, 0.0, 1.0];
        }
        $planes[] = $this->normalize($this->ps->velocity);
        $numplanes = count($planes);

        $bumpcount = 0;
        for (; $bumpcount < $numbumps; $bumpcount++) {
            $end = [
                $this->ps->origin[0] + $timeLeft * $this->ps->velocity[0],
                $this->ps->origin[1] + $timeLeft * $this->ps->velocity[1],
                $this->ps->origin[2] + $timeLeft * $this->ps->velocity[2],
            ];
            $trace = $this->trace($this->ps->origin, $end);

            if ($trace->allSolid) {
                $this->ps->velocity[2] = 0.0;
                return true;
            }

            if ($trace->fraction > 0) {
                $this->ps->origin = $trace->endPos;
            }

            if ($trace->fraction === 1.0) {
                break;
            }

            $timeLeft -= $timeLeft * $trace->fraction;

            if ($numplanes >= self::MAX_CLIP_PLANES) {
                $this->ps->velocity = [0.0, 0.0, 0.0];
                return true;
            }

            $n = $trace->planeNormal ?? [0.0, 0.0, 1.0];
            $samePlane = false;
            for ($i = 0; $i < $numplanes; $i++) {
                if ($this->dot($n, $planes[$i]) > 0.99) {
                    $this->ps->velocity[0] += $n[0];
                    $this->ps->velocity[1] += $n[1];
                    $this->ps->velocity[2] += $n[2];
                    $samePlane = true;
                    break;
                }
            }
            if ($samePlane) {
                continue;
            }
            $planes[$numplanes++] = $n;

            for ($i = 0; $i < $numplanes; $i++) {
                $into = $this->dot($this->ps->velocity, $planes[$i]);
                if ($into >= 0.1) {
                    continue;
                }
                if (-$into > $this->impactSpeed) {
                    $this->impactSpeed = -$into;
                }

                $clipVelocity = $this->clipVelocity($this->ps->velocity, $planes[$i]);
                $endClipVelocity = $this->clipVelocity($endVelocity, $planes[$i]);

                for ($j = 0; $j < $numplanes; $j++) {
                    if ($j === $i) {
                        continue;
                    }
                    if ($this->dot($clipVelocity, $planes[$j]) >= 0.1) {
                        continue;
                    }
                    $clipVelocity = $this->clipVelocity($clipVelocity, $planes[$j]);
                    $endClipVelocity = $this->clipVelocity($endClipVelocity, $planes[$j]);

                    if ($this->dot($clipVelocity, $planes[$i]) >= 0) {
                        continue;
                    }

                    $dir = $this->cross($planes[$i], $planes[$j]);
                    $dir = $this->normalize($dir);
                    $d = $this->dot($dir, $this->ps->velocity);
                    $clipVelocity = [$dir[0] * $d, $dir[1] * $d, $dir[2] * $d];

                    $dir = $this->cross($planes[$i], $planes[$j]);
                    $dir = $this->normalize($dir);
                    $d = $this->dot($dir, $endVelocity);
                    $endClipVelocity = [$dir[0] * $d, $dir[1] * $d, $dir[2] * $d];

                    for ($k = 0; $k < $numplanes; $k++) {
                        if ($k === $i || $k === $j) {
                            continue;
                        }
                        if ($this->dot($clipVelocity, $planes[$k]) >= 0.1) {
                            continue;
                        }
                        $this->ps->velocity = [0.0, 0.0, 0.0];
                        return true;
                    }
                }

                $this->ps->velocity = $clipVelocity;
                $endVelocity = $endClipVelocity;
                break;
            }
        }

        if ($gravity) {
            $this->ps->velocity = $endVelocity;
        }

        return $bumpcount !== 0;
    }

    private function stepSlideMove(bool $gravity): void
    {
        $startO = $this->ps->origin;
        $startV = $this->ps->velocity;

        if (!$this->slideMove($gravity)) {
            return;
        }

        $down = [$startO[0], $startO[1], $startO[2] - self::STEPSIZE];
        $trace = $this->trace($startO, $down);
        $up = [0.0, 0.0, 1.0];
        if ($this->ps->velocity[2] > 0 && ($trace->fraction === 1.0
            || $this->dot($trace->planeNormal ?? [0, 0, 1], $up) < 0.7)) {
            return;
        }

        $upPos = [$startO[0], $startO[1], $startO[2] + self::STEPSIZE];
        $trace = $this->trace($startO, $upPos);
        if ($trace->allSolid) {
            return;
        }

        $stepSize = $trace->endPos[2] - $startO[2];
        $this->ps->origin = $trace->endPos;
        $this->ps->velocity = $startV;
        $this->slideMove($gravity);

        $down = [
            $this->ps->origin[0],
            $this->ps->origin[1],
            $this->ps->origin[2] - $stepSize,
        ];
        $trace = $this->trace($this->ps->origin, $down);
        if (!$trace->allSolid) {
            $this->ps->origin = $trace->endPos;
        }
        if ($trace->fraction < 1.0) {
            $n = $trace->planeNormal ?? [0.0, 0.0, 1.0];
            $this->ps->velocity = $this->clipVelocity($this->ps->velocity, $n);
        }

        // EV_STEP_* — smooth stair climb (bg_slidemove.c)
        $delta = $this->ps->origin[2] - $startO[2];
        if ($delta > 2.0) {
            if ($delta < 7.0) {
                $this->ps->justStep = 4;
            } elseif ($delta < 11.0) {
                $this->ps->justStep = 8;
            } elseif ($delta < 15.0) {
                $this->ps->justStep = 12;
            } else {
                $this->ps->justStep = 16;
            }
        }
    }

    /** @param list<float> $start @param list<float> $end */
    private function trace(array $start, array $end): TraceResult
    {
        return $this->cm->trace($start, $end, $this->ps->mins, $this->ps->maxs, $this->tracemask);
    }

    /**
     * @param list<float> $angles
     * @param list<float> $forward
     * @param list<float> $right
     * @param list<float> $up
     */
    private function angleVectors(array $angles, array &$forward, array &$right, array &$up): void
    {
        $yaw = $angles[1] * (M_PI * 2 / 360);
        $sy = sin($yaw);
        $cy = cos($yaw);
        $pitch = $angles[0] * (M_PI * 2 / 360);
        $sp = sin($pitch);
        $cp = cos($pitch);
        $roll = $angles[2] * (M_PI * 2 / 360);
        $sr = sin($roll);
        $cr = cos($roll);

        $forward = [$cp * $cy, $cp * $sy, -$sp];
        $right = [
            (-1 * $sr * $sp * $cy + -1 * $cr * -$sy),
            (-1 * $sr * $sp * $sy + -1 * $cr * $cy),
            -1 * $sr * $cp,
        ];
        $up = [
            ($cr * $sp * $cy + -$sr * -$sy),
            ($cr * $sp * $sy + -$sr * $cy),
            $cr * $cp,
        ];
    }

    /** @param list<float> $in @param list<float> $normal @return list<float> */
    private function clipVelocity(array $in, array $normal): array
    {
        $backoff = $this->dot($in, $normal);
        if ($backoff < 0) {
            $backoff *= self::OVERCLIP;
        } else {
            $backoff /= self::OVERCLIP;
        }
        return [
            $in[0] - $normal[0] * $backoff,
            $in[1] - $normal[1] * $backoff,
            $in[2] - $normal[2] * $backoff,
        ];
    }

    /** @param list<float> $a @param list<float> $b */
    private function dot(array $a, array $b): float
    {
        return $a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2];
    }

    /** @param list<float> $a @param list<float> $b @return list<float> */
    private function cross(array $a, array $b): array
    {
        return [
            $a[1] * $b[2] - $a[2] * $b[1],
            $a[2] * $b[0] - $a[0] * $b[2],
            $a[0] * $b[1] - $a[1] * $b[0],
        ];
    }

    /** @param list<float> $v */
    private function length(array $v): float
    {
        return sqrt($v[0] * $v[0] + $v[1] * $v[1] + $v[2] * $v[2]);
    }

    /** @param list<float> $v @return list<float> */
    private function normalize(array $v): array
    {
        $len = $this->length($v);
        if ($len === 0.0) {
            return [0.0, 0.0, 0.0];
        }
        return [$v[0] / $len, $v[1] / $len, $v[2] / $len];
    }

    /**
     * @param list<float> $v
     * @return array{0: list<float>, 1: float}
     */
    private function normalizeRet(array $v): array
    {
        $len = $this->length($v);
        if ($len === 0.0) {
            return [[0.0, 0.0, 0.0], 0.0];
        }
        return [[$v[0] / $len, $v[1] / $len, $v[2] / $len], $len];
    }
}
