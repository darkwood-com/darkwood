<?php

declare(strict_types=1);

namespace Diablo\DiabloUI;

use Diablo\Platform\Sdl;

/**
 * Focus frames left/right of list item rect.
 * ArtFocus FOCUS_BIG = focus42, 8 frames, transparentColor=250.
 */
final class DrawSelector
{
    private string $sheetRgba = '';
    private int $sheetW = 0;
    private int $sheetH = 0;
    private int $frameW = 42;
    private int $frameH = 42;
    private int $numFrames = 8;
    private int $tick = 0;
    /** @var array<int, object> */
    private array $frameTex = [];

    public function loadFromRgba(string $rgba, int $w, int $h): void
    {
        $this->sheetRgba = $rgba;
        $this->sheetW = $w;
        $this->sheetH = $h;
        // focus42.pcx: 42×336 → 8 frames of 42×42 stacked vertically
        $this->frameW = $w;
        $this->frameH = $h >= 8 * 40 ? intdiv($h, 8) : min(42, $h);
        $this->numFrames = max(1, intdiv($h, max(1, $this->frameH)));
        $this->frameTex = [];
    }

    public function advance(): void
    {
        $this->tick++;
    }

    public function currentFrame(): int
    {
        return $this->numFrames > 0 ? intdiv($this->tick, 2) % $this->numFrames : 0;
    }

    /**
     * @param array{x:int,y:int,w:int,h:int} $rect list item rect (UiList item)
     */
    public function draw(Sdl $sdl, array $rect): void
    {
        if ($this->sheetRgba === '') {
            return;
        }
        $fi = $this->currentFrame();
        if (!isset($this->frameTex[$fi])) {
            $fh = $this->frameH;
            $fw = $this->frameW;
            $sy = $fi * $fh;
            $frame = '';
            for ($y = 0; $y < $fh; $y++) {
                $srcY = $sy + $y;
                if ($srcY >= $this->sheetH) {
                    $frame .= str_repeat("\0\0\0\0", $fw);
                    continue;
                }
                $off = ($srcY * $this->sheetW) * 4;
                $frame .= substr($this->sheetRgba, $off, $fw * 4);
                if (strlen($frame) % ($fw * 4) !== 0) {
                    // pad if row short
                }
            }
            while (strlen($frame) < $fw * $fh * 4) {
                $frame .= "\0\0\0\0";
            }
            $this->frameTex[$fi] = $sdl->createTextureRGBA($fw, $fh, substr($frame, 0, $fw * $fh * 4));
        }
        $tex = $this->frameTex[$fi];
        $y = $rect['y'] + intdiv($rect['h'] - $this->frameH, 2);
        // Left + right selectors (menu UI DrawSelector)
        $sdl->copyTexture($tex, $rect['x'], $y, $this->frameW, $this->frameH);
        $sdl->copyTexture($tex, $rect['x'] + $rect['w'] - $this->frameW, $y, $this->frameW, $this->frameH);
    }
}
