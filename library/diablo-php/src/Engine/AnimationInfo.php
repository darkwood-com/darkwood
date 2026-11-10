<?php

declare(strict_types=1);

namespace Diablo\Engine;

/**
 * Animation timing (distribution-off path).
 * baseValueFraction = 128. ProgressToNextGameTick supplied externally (0..128).
 */
final class AnimationInfo
{
    public const BASE_VALUE_FRACTION = 128;

    public int $ticksPerFrame = 1;
    public int $tickCounterOfCurrentFrame = 0;
    public int $numberOfFrames = 1;
    public int $currentFrame = 0;
    public bool $isPetrified = false;

    /** External: ProgressToNextGameTick analogue (0..BASE_VALUE_FRACTION). */
    public int $progressToNextGameTick = 0;

    public function setNewAnimation(int $numberOfFrames, int $ticksPerFrame = 1, int $numSkippedFrames = 0): void
    {
        if ($ticksPerFrame <= 0) {
            $ticksPerFrame = 1;
        }
        $this->numberOfFrames = max(1, $numberOfFrames);
        $this->ticksPerFrame = $ticksPerFrame;
        $this->currentFrame = max(0, min($this->numberOfFrames - 1, $numSkippedFrames));
        $this->tickCounterOfCurrentFrame = 0;
        $this->isPetrified = false;
    }

    public function isLastFrame(): bool
    {
        return $this->currentFrame >= ($this->numberOfFrames - 1);
    }

    public function getFrameToUseForRendering(): int
    {
        return max(0, $this->currentFrame);
    }

    /**
     * Distribution-off branch of AnimationInfo::getAnimationProgress.
     */
    public function getAnimationProgress(): int
    {
        if ($this->ticksPerFrame <= 0 || $this->numberOfFrames <= 0) {
            return 0;
        }
        $bf = self::BASE_VALUE_FRACTION;
        $ticksSinceSequenceStarted = (($this->currentFrame * $this->ticksPerFrame) + $this->tickCounterOfCurrentFrame) * $bf;
        $tickModifier = intdiv($bf, $this->ticksPerFrame);
        $progressToNext = $this->isPetrified ? 0 : $this->progressToNextGameTick;
        $totalTicks = $progressToNext + $ticksSinceSequenceStarted;
        $progressInAnimationFrames = $totalTicks * $tickModifier;
        $animationFraction = intdiv($progressInAnimationFrames, $this->numberOfFrames * $bf);

        return max(0, min($bf, $animationFraction));
    }

    public function processAnimation(bool $reverse = false): void
    {
        $this->tickCounterOfCurrentFrame++;
        if ($this->tickCounterOfCurrentFrame >= $this->ticksPerFrame) {
            $this->tickCounterOfCurrentFrame = 0;
            if ($reverse) {
                --$this->currentFrame;
                if ($this->currentFrame < 0) {
                    $this->currentFrame = $this->numberOfFrames - 1;
                }
            } else {
                ++$this->currentFrame;
                if ($this->currentFrame >= $this->numberOfFrames) {
                    $this->currentFrame = 0;
                }
            }
        }
    }
}
