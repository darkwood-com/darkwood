<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Draw order: floor → walls → objects → items → actors → UI.
 * Camera follows player with edge clamp.
 */
final class Renderer
{
    public function __construct(
        private Sdl $sdl,
        private Iso $iso,
        private int $windowW,
        private int $windowH,
    ) {
    }

    public function updateCamera(Player $player, Dungeon $dungeon): void
    {
        [$sx, $sy] = [
            (int) (($player->x - $player->y) * (Iso::TILE_W / 2) + $player->renderOffsetX),
            (int) (($player->x + $player->y) * (Iso::TILE_H / 2) + $player->renderOffsetY),
        ];
        $this->iso->originX = intdiv($this->windowW, 2) - $sx;
        $this->iso->originY = intdiv($this->windowH, 2) - $sy;
        // Soft clamp so map doesn't leave huge empty borders on small maps
        $pad = 80;
        $this->iso->originX = max(-$dungeon->width * Iso::TILE_W, min($this->windowW + $pad, $this->iso->originX));
        $this->iso->originY = max(-$dungeon->height * Iso::TILE_H, min($this->windowH + $pad, $this->iso->originY));
    }

    /**
     * @param list<Monster> $monsters
     * @param list<Item> $groundItems
     * @param list<GameObject> $objects
     * @param list<Missile> $missiles
     * @param list<Towner> $towners
     * @param list<string> $messages
     */
    public function drawWorld(
        Dungeon $dungeon,
        Player $player,
        array $monsters,
        array $groundItems,
        array $objects,
        array $missiles,
        array $towners,
        bool $showInventory,
        bool $showStats,
        array $messages,
        string $hudExtra = '',
    ): void {
        $this->sdl->setDrawColor(12, 10, 16);
        $this->sdl->clear();

        $halfW = intdiv(Iso::TILE_W, 2);
        $halfH = intdiv(Iso::TILE_H, 2);
        $maxSum = $dungeon->width + $dungeon->height - 2;

        // Floor + walls with fog
        for ($sum = 0; $sum <= $maxSum; $sum++) {
            for ($x = 0; $x < $dungeon->width; $x++) {
                $y = $sum - $x;
                if ($y < 0 || $y >= $dungeon->height) {
                    continue;
                }
                if (!$dungeon->isExplored($x, $y)) {
                    continue;
                }
                $vis = $dungeon->isVisible($x, $y);
                [$sx, $sy] = $this->iso->gridToScreen($x, $y);
                $tile = $dungeon->tile($x, $y);

                if ($tile === Dungeon::TILE_WALL) {
                    $this->sdl->setDrawColor($vis ? 55 : 28, $vis ? 48 : 24, $vis ? 42 : 22);
                    $this->sdl->fillDiamond($sx, $sy, $halfW - 1, $halfH - 1);
                    if ($vis) {
                        $this->sdl->setDrawColor(78, 68, 58);
                        $this->sdl->fillDiamond($sx, $sy - 8, $halfW - 4, $halfH - 4);
                    }
                } elseif ($tile === Dungeon::TILE_DOOR_CLOSED) {
                    $this->sdl->setDrawColor($vis ? 100 : 40, $vis ? 70 : 30, $vis ? 40 : 20);
                    $this->sdl->fillDiamond($sx, $sy, $halfW - 1, $halfH - 1);
                } elseif ($tile === Dungeon::TILE_DOOR_OPEN) {
                    $this->sdl->setDrawColor($vis ? 90 : 40, $vis ? 80 : 35, $vis ? 50 : 25);
                    $this->sdl->fillDiamond($sx, $sy, $halfW - 1, $halfH - 1);
                } elseif ($tile === Dungeon::TILE_STAIRS_DOWN || $tile === Dungeon::TILE_STAIRS_UP) {
                    $this->sdl->setDrawColor($vis ? 120 : 50, $vis ? 100 : 40, $vis ? 40 : 20);
                    $this->sdl->fillDiamond($sx, $sy, $halfW - 1, $halfH - 1);
                    $this->sdl->setDrawColor(200, 180, 60);
                    $this->sdl->fillRect($sx - 4, $sy - 4, 8, 8);
                } else {
                    $this->sdl->setDrawColor($vis ? 72 : 30, $vis ? 78 : 32, $vis ? 68 : 28);
                    $this->sdl->fillDiamond($sx, $sy, $halfW - 1, $halfH - 1);
                    if ($vis) {
                        $this->sdl->setDrawColor(90, 98, 84);
                        $this->sdl->fillDiamond($sx, $sy, $halfW - 6, $halfH - 4);
                    }
                }
            }
        }

        // Objects (chests/barrels)
        foreach ($objects as $obj) {
            if ($obj->type === GameObject::TYPE_DOOR) {
                continue;
            }
            if (!$dungeon->isVisible($obj->x, $obj->y) && !$dungeon->isExplored($obj->x, $obj->y)) {
                continue;
            }
            [$sx, $sy] = $this->iso->gridToScreen($obj->x, $obj->y);
            if ($obj->type === GameObject::TYPE_CHEST) {
                $this->sdl->setDrawColor($obj->opened ? 80 : 160, $obj->opened ? 70 : 120, 40);
                $this->sdl->fillRect($sx - 8, $sy - 10, 16, 12);
            } elseif ($obj->type === GameObject::TYPE_BARREL) {
                $this->sdl->setDrawColor($obj->opened ? 60 : 140, 90, 50);
                $this->sdl->fillDiamond($sx, $sy - 4, 8, 10);
            }
        }

        // Ground items
        foreach ($groundItems as $item) {
            if (!$dungeon->isVisible($item->x, $item->y)) {
                continue;
            }
            [$sx, $sy] = $this->iso->gridToScreen($item->x, $item->y);
            if ($item->itemClass === 'gold') {
                $this->sdl->setDrawColor(220, 180, 40);
            } elseif ($item->itemClass === 'potion') {
                $this->sdl->setDrawColor($item->type === 'potion_mana' ? 60 : 200, 60, $item->type === 'potion_mana' ? 200 : 60);
            } else {
                $this->sdl->setDrawColor(180, 180, 200);
            }
            $this->sdl->fillRect($sx - 4, $sy - 4, 8, 8);
        }

        // Towners
        foreach ($towners as $t) {
            [$sx, $sy] = $this->iso->gridToScreen($t->x, $t->y);
            $this->sdl->setDrawColor(80, 160, 220);
            $this->sdl->fillDiamond($sx, $sy - 6, 10, 12);
        }

        // Monsters
        foreach ($monsters as $m) {
            if (!$m->alive && $m->mode !== Monster::MODE_DEATH) {
                continue;
            }
            if (!$dungeon->isVisible($m->x, $m->y) && $m->mode !== Monster::MODE_DEATH) {
                continue;
            }
            [$sx, $sy] = $this->iso->gridToScreen($m->x, $m->y);
            $sx += (int) $m->renderOffsetX;
            $sy += (int) $m->renderOffsetY;
            [$r, $g, $b] = Animation::colorForActor($m->type, $m->mode);
            if ($m->isQuestTarget) {
                $r = 255;
                $g = 80;
                $b = 255;
            }
            $this->sdl->setDrawColor($r, $g, $b);
            $bob = Animation::frame($m->mode, $m->direction, $m->actionFrame, $m->walkFrames, $m->walkFramesTotal);
            $this->sdl->fillDiamond($sx, $sy - 6 - ($bob % 3), 11, 11);
        }

        // Missiles
        foreach ($missiles as $mis) {
            if (!$mis->alive) {
                continue;
            }
            [$sx, $sy] = $this->iso->gridToScreen((int) round($mis->x), (int) round($mis->y));
            // sub-tile lerp
            $fx = $mis->x - floor($mis->x);
            $fy = $mis->y - floor($mis->y);
            $sx += (int) (($fx - $fy) * (Iso::TILE_W / 2));
            $sy += (int) (($fx + $fy) * (Iso::TILE_H / 2));
            if ($mis->type === 'firebolt') {
                $this->sdl->setDrawColor(255, 120, 40);
            } else {
                $this->sdl->setDrawColor(220, 220, 80);
            }
            $this->sdl->fillRect($sx - 3, $sy - 3, 6, 6);
        }

        // Player
        [$px, $py] = $this->iso->gridToScreen($player->x, $player->y);
        $px += (int) $player->renderOffsetX;
        $py += (int) $player->renderOffsetY;
        [$r, $g, $b] = Animation::colorForActor('player', $player->mode);
        $this->sdl->setDrawColor($r, $g, $b);
        $bob = Animation::frame($player->mode, $player->direction, $player->actionFrame, $player->walkFrames, $player->walkFramesTotal);
        $this->sdl->fillDiamond($px, $py - 6 - ($bob % 2), 12, 10);
        $this->sdl->setDrawColor(240, 200, 80);
        $this->sdl->fillRect($px - 3, $py - 18, 6, 6);

        $this->drawHud($player, $messages, $hudExtra);
        if ($showStats) {
            $this->drawStats($player);
        }
        if ($showInventory) {
            $this->drawInventory($player);
        }
    }

    /** @param list<string> $messages */
    private function drawHud(Player $player, array $messages, string $extra): void
    {
        // HP bar
        $this->sdl->setDrawColor(40, 20, 20);
        $this->sdl->fillRect(16, 16, 160, 14);
        $hpW = (int) (158 * max(0, $player->hp) / max(1, $player->maxHp));
        $this->sdl->setDrawColor(180, 40, 40);
        $this->sdl->fillRect(17, 17, $hpW, 12);

        // Mana bar
        $this->sdl->setDrawColor(20, 20, 40);
        $this->sdl->fillRect(16, 34, 160, 14);
        $mpW = (int) (158 * max(0, $player->mana) / max(1, $player->maxMana));
        $this->sdl->setDrawColor(40, 80, 200);
        $this->sdl->fillRect(17, 35, $mpW, 12);

        // Belt
        for ($i = 0; $i < 4; $i++) {
            $this->sdl->setDrawColor(50, 50, 50);
            $this->sdl->fillRect(16 + $i * 36, $this->windowH - 48, 32, 32);
            if (isset($player->belt[$i]) && $player->belt[$i] !== null) {
                $it = $player->belt[$i];
                $this->sdl->setDrawColor($it->type === 'potion_mana' ? 60 : 200, 60, $it->type === 'potion_mana' ? 200 : 60);
                $this->sdl->fillRect(20 + $i * 36, $this->windowH - 44, 24, 24);
            }
        }

        // Message log (as colored bars — no TTF)
        $y = $this->windowH - 80;
        foreach (array_slice($messages, -4) as $i => $msg) {
            $this->sdl->setDrawColor(30, 30, 30);
            $this->sdl->fillRect(200, $y - $i * 14, min(400, 20 + strlen($msg) * 4), 12);
        }

        if ($extra !== '') {
            $this->sdl->setDrawColor(60, 60, 40);
            $this->sdl->fillRect(200, 16, min(500, 20 + strlen($extra) * 4), 14);
        }
    }

    private function drawStats(Player $player): void
    {
        $x = $this->windowW - 220;
        $this->sdl->setDrawColor(20, 20, 28);
        $this->sdl->fillRect($x, 40, 200, 220);
        $this->sdl->setDrawColor(180, 160, 100);
        $this->sdl->fillRect($x, 40, 200, 4);
        // Stat bars as visual proxies
        $stats = [
            $player->level,
            $player->strength,
            $player->magic,
            $player->dexterity,
            $player->vitality,
            $player->armorClass,
            $player->minDamage,
            $player->maxDamage,
            $player->gold,
        ];
        foreach ($stats as $i => $v) {
            $this->sdl->setDrawColor(80, 80, 100);
            $this->sdl->fillRect($x + 12, 56 + $i * 20, min(176, 8 + $v * 2), 10);
        }
    }

    private function drawInventory(Player $player): void
    {
        $x = 40;
        $y = 80;
        $this->sdl->setDrawColor(24, 24, 32);
        $this->sdl->fillRect($x, $y, 320, 280);
        for ($i = 0; $i < Inventory::SLOTS; $i++) {
            $cx = $x + 12 + ($i % 8) * 36;
            $cy = $y + 12 + intdiv($i, 8) * 36;
            $this->sdl->setDrawColor(45, 45, 55);
            $this->sdl->fillRect($cx, $cy, 32, 32);
            $item = $player->inventory->slots[$i];
            if ($item !== null) {
                $this->sdl->setDrawColor(
                    $item->quality === 'magic' ? 80 : 160,
                    $item->quality === 'magic' ? 140 : 160,
                    $item->quality === 'magic' ? 200 : 100
                );
                $this->sdl->fillRect($cx + 4, $cy + 4, 24, 24);
            }
        }
        // Equipment column
        $ex = $x + 310;
        $this->sdl->setDrawColor(24, 24, 32);
        $this->sdl->fillRect($ex, $y, 80, 200);
        foreach (['head', 'body', 'hand', 'ring', 'amulet'] as $i => $slot) {
            $this->sdl->setDrawColor(55, 55, 70);
            $this->sdl->fillRect($ex + 20, $y + 16 + $i * 36, 40, 32);
            $it = $player->equipment->$slot;
            if ($it !== null) {
                $this->sdl->setDrawColor(180, 160, 80);
                $this->sdl->fillRect($ex + 26, $y + 22 + $i * 36, 28, 20);
            }
        }
    }

    /** @param list<string> $options */
    public function drawMenu(string $title, array $options, int $cursor, string $subtitle = ''): void
    {
        $this->sdl->setDrawColor(8, 6, 12);
        $this->sdl->clear();
        // Brand banner
        $this->sdl->setDrawColor(120, 30, 20);
        $this->sdl->fillRect(0, 0, $this->windowW, 120);
        $this->sdl->setDrawColor(200, 160, 60);
        $this->sdl->fillRect(intdiv($this->windowW, 2) - 120, 40, 240, 40);
        $this->sdl->setDrawColor(40, 20, 10);
        $this->sdl->fillRect(intdiv($this->windowW, 2) - 100, 50, 200, 20);

        foreach ($options as $i => $opt) {
            $y = 200 + $i * 48;
            if ($i === $cursor) {
                $this->sdl->setDrawColor(180, 140, 50);
                $this->sdl->fillRect(intdiv($this->windowW, 2) - 140, $y, 280, 36);
            } else {
                $this->sdl->setDrawColor(40, 36, 48);
                $this->sdl->fillRect(intdiv($this->windowW, 2) - 140, $y, 280, 36);
            }
        }
        if ($subtitle !== '') {
            $this->sdl->setDrawColor(60, 60, 70);
            $this->sdl->fillRect(intdiv($this->windowW, 2) - 100, 160, min(400, strlen($subtitle) * 6), 16);
        }
    }
}
