<?php

declare(strict_types=1);

namespace Diablo\DiabloUI;

use Diablo\Engine\Assets\UiFont;
use Diablo\Platform\Sdl;

/**
 * Menu list widget + render.
 * Coords: (64,192), item height 43, width 510, FontSize42, spacing 5.
 */
final class UiList
{
    public const LIST_X = 64;
    public const LIST_Y = 192;
    public const ITEM_H = 43;
    public const LIST_W = 510;
    public const SPACING = 5;

    /** @var list<string> */
    private array $items = [];
    private int $cursor = 0;
    private ?UiFont $fontGold = null;
    private ?UiFont $fontYellow = null;
    private DrawSelector $selector;
    /** @var array<string, array{tex:object,w:int,h:int}> */
    private array $textCache = [];

    public function __construct()
    {
        $this->selector = new DrawSelector();
    }

    public function setFonts(?UiFont $gold, ?UiFont $yellow): void
    {
        $this->fontGold = $gold;
        $this->fontYellow = $yellow;
        $this->textCache = [];
    }

    public function setFocusSheet(string $rgba, int $w, int $h): void
    {
        $this->selector->loadFromRgba($rgba, $w, $h);
    }

    /** @param list<string> $items */
    public function setItems(array $items, int $cursor = 0): void
    {
        $this->items = $items;
        $this->cursor = max(0, min(count($items) - 1, $cursor));
    }

    public function cursor(): int
    {
        return $this->cursor;
    }

    public function move(int $delta): void
    {
        $n = count($this->items);
        if ($n === 0) {
            return;
        }
        $this->cursor = ($this->cursor + $delta + $n) % $n;
    }

    public function selectedLabel(): string
    {
        return $this->items[$this->cursor] ?? '';
    }

    public function advanceFocusAnim(): void
    {
        $this->selector->advance();
    }

    public function render(Sdl $sdl, int $uiX, int $uiY): void
    {
        if ($this->fontGold === null) {
            fwrite(STDERR, "UiList ERROR: UiFont missing — cannot draw list text\n");

            return;
        }
        foreach ($this->items as $i => $label) {
            $rect = [
                'x' => $uiX + self::LIST_X,
                'y' => $uiY + self::LIST_Y + $i * self::ITEM_H,
                'w' => self::LIST_W,
                'h' => self::ITEM_H,
            ];
            $selected = $i === $this->cursor;
            if ($selected) {
                $this->selector->draw($sdl, $rect);
            }
            $font = ($selected && $this->fontYellow !== null) ? $this->fontYellow : $this->fontGold;
            $key = ($selected ? 'Y:' : 'G:') . $label;
            if (!isset($this->textCache[$key])) {
                $img = $font->render($label, self::SPACING);
                $this->textCache[$key] = [
                    'tex' => $sdl->createTextureRGBA($img['w'], $img['h'], $img['rgba']),
                    'w' => $img['w'],
                    'h' => $img['h'],
                ];
            }
            $c = $this->textCache[$key];
            $tx = $rect['x'] + intdiv($rect['w'] - (int) $c['w'], 2);
            $ty = $rect['y'] + intdiv($rect['h'] - (int) $c['h'], 2);
            $sdl->copyTexture($c['tex'], $tx, $ty, (int) $c['w'], (int) $c['h']);
        }
    }
}
