<?php

declare(strict_types=1);

namespace Diablo\Engine;

/**
 * Compact JSONL gameplay trace for runtime diagnostics (not architecture reports).
 */
final class GameplayTrace
{
    private ?string $path = null;
    /** @var resource|null */
    private $fh = null;
    public int $makePlrPathCalls = 0;
    public int $repathFlags = 0;

    /** Transition flags cleared each tick */
    public bool $cnp = false;
    public bool $repath = false;
    public bool $consume = false;
    public bool $modechg = false;
    public bool $tplayer = false;
    public bool $atk = false;
    public bool $rej = false;
    public bool $tgtchg = false;
    public int $tplayerN = 0;
    public string $modeBefore = '';

    public function enable(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->path = $path;
        $this->fh = fopen($path, 'wb');
    }

    public function enabled(): bool
    {
        return $this->fh !== null;
    }

    public function resetTickFlags(): void
    {
        $this->cnp = false;
        $this->repath = false;
        $this->consume = false;
        $this->modechg = false;
        $this->tplayer = false;
        $this->atk = false;
        $this->rej = false;
        $this->tgtchg = false;
        $this->tplayerN = 0;
        $this->makePlrPathCalls = 0;
    }

    /** @param array<string,mixed> $row */
    public function emit(array $row): void
    {
        if ($this->fh === null) {
            return;
        }
        fwrite($this->fh, json_encode($row, JSON_UNESCAPED_SLASHES) . "\n");
    }

    public function close(): void
    {
        if ($this->fh !== null) {
            fclose($this->fh);
            $this->fh = null;
        }
    }

    public function path(): ?string
    {
        return $this->path;
    }
}
