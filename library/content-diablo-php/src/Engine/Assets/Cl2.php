<?php

declare(strict_types=1);

namespace Diablo\Engine\Assets;

/**
 * CL2 frame decode to RGBA.
 * Supports multi-group files (8 direction groups for player/monster graphics).
 */
final class Cl2
{
    public string $data = '';
    /** Number of direction groups (1 if ungrouped). */
    public int $numGroups = 1;
    /** @var list<int> absolute byte offset of each group's frame table */
    public array $groupBases = [];
    /** Frames in the currently selected / default group (group 0 after parse). */
    public int $numFrames = 0;
    /** @var list<int> */
    public array $frameOffsets = [];
    /** @var list<int> */
    public array $frameEnds = [];

    public static function parse(string $data): self
    {
        $c = new self();
        $c->data = $data;
        $size = strlen($data);
        if ($size < 8) {
            return $c;
        }
        // if treating dword0 as frame count fails
        // (last offset != file size), then dword0 is group-table byte size and
        // numGroups = dword0 / 4; group offsets live at data[g*4].
        $maybeNumFrames = unpack('V', substr($data, 0, 4))[1];
        $grouped = false;
        if ($maybeNumFrames > 0 && ($maybeNumFrames * 4 + 8) <= $size) {
            $lastOff = unpack('V', substr($data, $maybeNumFrames * 4 + 4, 4))[1];
            if ($lastOff !== $size) {
                $grouped = true;
                $c->numGroups = max(1, intdiv($maybeNumFrames, 4));
                if ($c->numGroups > 16) {
                    $c->numGroups = 8;
                }
                $c->groupBases = [];
                for ($g = 0; $g < $c->numGroups; $g++) {
                    $off = unpack('V', substr($data, $g * 4, 4))[1];
                    if ($off < 0 || $off >= $size) {
                        $off = 0;
                    }
                    $c->groupBases[] = $off;
                }
            }
        }
        if (!$grouped) {
            $c->numGroups = 1;
            $c->groupBases = [0];
        }
        $c->selectGroup(0);

        return $c;
    }

    /** Switch active group (direction). */
    public function selectGroup(int $group): void
    {
        $group = max(0, min($this->numGroups - 1, $group));
        $base = $this->groupBases[$group] ?? 0;
        $size = strlen($this->data);
        if ($base + 8 > $size) {
            $this->numFrames = 0;
            $this->frameOffsets = [];
            $this->frameEnds = [];

            return;
        }
        $numFrames = unpack('V', substr($this->data, $base, 4))[1];
        $this->numFrames = max(0, $numFrames);
        $this->frameOffsets = [];
        $this->frameEnds = [];
        for ($i = 0; $i < $this->numFrames; $i++) {
            $off = unpack('V', substr($this->data, $base + 4 + $i * 4, 4))[1];
            $next = unpack('V', substr($this->data, $base + 4 + ($i + 1) * 4, 4))[1];
            $this->frameOffsets[] = $base + $off;
            $this->frameEnds[] = $base + $next;
        }
    }

    public function framesInGroup(int $group): int
    {
        $this->selectGroup($group);

        return $this->numFrames;
    }

    /**
     * @return array{w:int,h:int,rgba:string}
     */
    /**
     * @param list<int>|null $lightTable LightTables row (palette index remap); null = fullbright
     * @return array{w:int,h:int,rgba:string}
     */
    public function decodeFrame(int $frameIndex, int $width, Palette $pal, ?int $group = null, ?array $lightTable = null): array
    {
        if ($group !== null) {
            $this->selectGroup($group);
        }
        if ($frameIndex < 0 || $frameIndex >= $this->numFrames) {
            throw new \RuntimeException('CL2 frame OOB group frames=' . $this->numFrames . ' idx=' . $frameIndex);
        }
        $start = $this->frameOffsets[$frameIndex];
        $end = $this->frameEnds[$frameIndex];
        $frame = substr($this->data, $start, $end - $start);
        if (strlen($frame) < 2) {
            return ['w' => $width, 'h' => 1, 'rgba' => str_repeat("\0\0\0\0", $width)];
        }
        $headerSize = unpack('v', substr($frame, 0, 2))[1];
        $pos = min($headerSize, strlen($frame));
        $len = strlen($frame);

        $pixels = array_fill(0, $width * 512, null);
        $maxH = 512;
        $x = 0;
        $y = 0;
        $written = 0;

        while ($pos < $len) {
            $control = ord($frame[$pos++]);
            if ($control < 0x80) {
                $n = $control;
                for ($i = 0; $i < $n; $i++) {
                    $this->plot($pixels, $width, $maxH, $x, $y, null);
                    $x++;
                    if ($x >= $width) {
                        $x = 0;
                        $y++;
                    }
                }
            } elseif ($control <= 0xBE) {
                $n = 0xBF - $control;
                if ($pos >= $len) {
                    break;
                }
                $color = ord($frame[$pos++]);
                for ($i = 0; $i < $n; $i++) {
                    $this->plot($pixels, $width, $maxH, $x, $y, $color);
                    $x++;
                    if ($x >= $width) {
                        $x = 0;
                        $y++;
                    }
                }
            } else {
                $n = (256 - $control) & 0xFF;
                if ($n === 0) {
                    $n = 1;
                }
                for ($i = 0; $i < $n; $i++) {
                    if ($pos >= $len) {
                        break 2;
                    }
                    $color = ord($frame[$pos++]);
                    $this->plot($pixels, $width, $maxH, $x, $y, $color);
                    $x++;
                    if ($x >= $width) {
                        $x = 0;
                        $y++;
                    }
                }
            }
            $written = max($written, $y + 1);
            if ($y >= $maxH - 1) {
                break;
            }
        }
        $h = max(1, $written);
        $rgba = '';
        for ($row = $h - 1; $row >= 0; $row--) {
            for ($col = 0; $col < $width; $col++) {
                $idx = $pixels[$row * $width + $col] ?? null;
                if ($idx === null || $idx === 0) {
                    $rgba .= "\x00\x00\x00\x00";
                } else {
                    $mapped = $idx;
                    if ($lightTable !== null) {
                        $mapped = $lightTable[$idx] ?? $idx;
                    }
                    [$r, $g, $b] = $pal->rgb($mapped);
                    $rgba .= chr($r) . chr($g) . chr($b) . "\xFF";
                }
            }
        }

        return ['w' => $width, 'h' => $h, 'rgba' => $rgba];
    }

    /** @param list<?int> $pixels */
    private function plot(array &$pixels, int $width, int $maxH, int $x, int $y, ?int $color): void
    {
        if ($y < 0 || $y >= $maxH || $x < 0 || $x >= $width) {
            return;
        }
        $pixels[$y * $width + $x] = $color;
    }
}
