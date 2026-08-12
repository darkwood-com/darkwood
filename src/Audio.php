<?php

declare(strict_types=1);

namespace Diablo;

use Diablo\Engine\Assets\AssetStore;

/**
 * Audio hooks — cue log + optional WAV playback from MPQ (afplay on macOS).
 * SDL_mixer still deferred; see NOTICE.md.
 */
final class Audio
{
    /** @var list<string> */
    private array $log = [];

    /** @var array<string,string> cue → MPQ path */
    private const CUES = [
        'death' => 'sfx\\misc\\dead.wav',
        'player_hit' => 'sfx\\misc\\swing2.wav',
        'swing' => 'sfx\\misc\\swing.wav',
        'door' => 'sfx\\items\\invgrab.wav',
        'pickup' => 'sfx\\items\\invpot.wav',
        'missile' => 'sfx\\misc\\bfire.wav',
        'cast' => 'sfx\\misc\\cast1.wav',
        'heal' => 'sfx\\misc\\healing.wav',
        'walk' => 'sfx\\misc\\walk1.wav',
    ];

    /** Throttle concurrent afplay processes. */
    private float $lastPlayAt = 0.0;

    public function __construct(private readonly ?AssetStore $assets = null)
    {
    }

    public function play(string $cue): void
    {
        $this->log[] = $cue;
        if (count($this->log) > 64) {
            array_shift($this->log);
        }
        $path = self::CUES[$cue] ?? null;
        if ($path === null || $this->assets === null || !$this->assets->has($path)) {
            return;
        }
        $now = microtime(true);
        // Avoid stacking dozens of afplay during combat spam
        if ($now - $this->lastPlayAt < 0.05) {
            return;
        }
        $this->lastPlayAt = $now;
        try {
            $bytes = $this->assets->read($path);
        } catch (\Throwable) {
            return;
        }
        $tmp = sys_get_temp_dir() . '/diablo_php_' . md5($path) . '.wav';
        if (!is_file($tmp) || filesize($tmp) !== strlen($bytes)) {
            @file_put_contents($tmp, $bytes);
        }
        if (!is_file($tmp)) {
            return;
        }
        // Fire-and-forget; macOS afplay. No-op if missing.
        if (PHP_OS_FAMILY === 'Darwin' && is_executable('/usr/bin/afplay')) {
            $cmd = 'afplay ' . escapeshellarg($tmp) . ' >/dev/null 2>&1 &';
            @exec($cmd);
        }
    }

    /** @return list<string> */
    public function recent(): array
    {
        return $this->log;
    }
}
