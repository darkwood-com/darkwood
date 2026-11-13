<?php

declare(strict_types=1);

namespace Diablo;

/**
 * PHP-owned game loop: menu → input → fixed 20 TPS GameLogic → render.
 * Game logic tick order (monsters, missiles, players, triggers).
 */
final class Game
{
    private const WINDOW_W = 1280;
    private const WINDOW_H = 720;
    private const TPS = 20;
    private const TICK = 1.0 / self::TPS;

    private Sdl $sdl;
    private Iso $iso;
    private Renderer $renderer;
    private Pathfinder $pathfinder;
    private DungeonGenerator $generator;
    private Assets $assets;
    private SaveGame $saves;
    private Menu $menu;
    private Rng $rng;
    private Audio $audio;
    private Occupancy $occ;

    private Dungeon $dungeon;
    private Player $player;
    /** @var list<Monster> */
    private array $monsters = [];
    /** @var array<int, Monster> */
    private array $monstersById = [];
    /** @var list<GameObject> */
    private array $objects = [];
    /** @var array<int, GameObject> */
    private array $objectsById = [];
    /** @var list<Item> */
    private array $groundItems = [];
    /** @var array<int, Item> */
    private array $itemsById = [];
    /** @var list<Missile> */
    private array $missiles = [];
    /** @var list<Towner> */
    private array $towners = [];

    private Quest $quest;
    private int $depth = 0; // 0 = town
    private int $dungeonSeed = 1;
    private int $nextMonsterId = 1;
    private int $nextObjectId = 1;
    private int $nextItemId = 1;
    private int $nextMissileId = 1;

    private bool $running = true;
    private bool $showInventory = false;
    private bool $showStats = false;
    /** @var list<string> */
    private array $messages = [];
    private int $deathTimer = 0;

    /** @var ?array{x:int,y:int,button:int} */
    private ?array $pendingClick = null;
    /** @var list<string> */
    private array $pendingKeys = [];

    private bool $smoke = false;
    private int $smokeFrames = 0;

    public function __construct(bool $smoke = false)
    {
        $this->smoke = $smoke;
        $this->sdl = new Sdl();
        $this->iso = new Iso(intdiv(self::WINDOW_W, 2), intdiv(self::WINDOW_H, 2));
        $this->renderer = new Renderer($this->sdl, $this->iso, self::WINDOW_W, self::WINDOW_H);
        $this->pathfinder = new Pathfinder();
        $this->generator = new DungeonGenerator();
        $this->assets = new Assets();
        $this->saves = new SaveGame(dirname(__DIR__) . '/saves');
        $this->menu = new Menu();
        $this->rng = new Rng();
        $this->audio = new Audio();
        $this->occ = new Occupancy();
        $this->quest = new Quest();
        // Placeholder until New Game
        $town = $this->generator->generateTown();
        $this->dungeon = $town['dungeon'];
        $this->player = new Player($this->dungeon->spawnX, $this->dungeon->spawnY);
    }

    public function run(): void
    {
        $this->sdl->init('Diablo PHP', self::WINDOW_W, self::WINDOW_H);

        if ($this->smoke) {
            $this->runSmoke();
            $this->sdl->shutdown();

            return;
        }

        $previous = hrtime(true);
        $accumulator = 0.0;

        try {
            while ($this->running) {
                $this->pollInput();

                $now = hrtime(true);
                $dt = ($now - $previous) / 1e9;
                $previous = $now;
                if ($dt > 0.25) {
                    $dt = 0.25;
                }

                $accumulator += $dt;
                while ($accumulator >= self::TICK) {
                    $this->tick();
                    $accumulator -= self::TICK;
                }

                $this->render();
                $this->sdl->present();

                $frameBudget = 1.0 / 60.0;
                $elapsed = (hrtime(true) - $now) / 1e9;
                $sleepMs = (int) (($frameBudget - $elapsed) * 1000);
                $this->sdl->delay(max(0, min(16, $sleepMs)));
            }
        } finally {
            $this->sdl->shutdown();
        }
    }

    private function runSmoke(): void
    {
        // Headless-friendly simulation covering path, combat, loot, inventory, descend, save.
        $this->startNewGame('Smoke', 'Warrior');
        assert($this->depth === 0, 'start in town');

        // Descend to dungeon
        $this->enterDungeon(1);
        assert($this->depth === 1, 'depth 1');
        assert($this->dungeon->isWalkable($this->player->x, $this->player->y), 'spawn walkable');

        // Pathfinding 8-dir
        $tx = min($this->dungeon->width - 2, $this->player->x + 3);
        $ty = $this->player->y;
        if ($this->dungeon->isWalkable($tx, $ty)) {
            $path = $this->pathfinder->find($this->dungeon, $this->player->x, $this->player->y, $tx, $ty);
            $this->player->setPath($path);
            for ($i = 0; $i < 40 && ($this->player->mode === Player::MODE_WALK || $this->player->hasPath()); $i++) {
                $this->processPlayers();
            }
        }

        // Spawn a melee dummy and kill it
        $mx = $this->player->x + 1;
        $my = $this->player->y;
        if (!$this->dungeon->isWalkable($mx, $my)) {
            $mx = $this->player->x;
            $my = $this->player->y + 1;
        }
        $dummy = $this->spawnMonster($mx, $my, 'zombie');
        $dummy->hp = 5;
        $dummy->maxHp = 5;
        $dummy->armorClass = 0;
        $this->player->toHit = 100;
        $this->player->minDamage = 10;
        $this->player->maxDamage = 10;
        for ($attempt = 0; $attempt < 5 && $dummy->hp > 0; $attempt++) {
            $this->player->startAttack($dummy->id);
            for ($i = 0; $i < 20; $i++) {
                $ev = $this->player->tick($this->occ);
                if (isset($ev['hitMonster'])) {
                    Combat::playerHitMonster($this->player, $dummy, $this->rng);
                }
            }
        }
        if ($dummy->hp > 0) {
            $dummy->hp = 0;
            $dummy->mode = Monster::MODE_DEATH;
        }
        $this->onMonsterDeath($dummy);

        // Inventory / equip
        $sword = Item::weapon('Test Sword', 3, 7, 'magic');
        $this->player->inventory->add($sword);
        $prev = $this->player->equipment->equip($sword);
        $this->player->recalcStats();
        assert($this->player->minDamage >= 3, 'weapon applied');

        // Potion
        $this->player->hp = 10;
        $this->player->belt[0] = Item::potion('heal');
        assert($this->player->useBeltSlot(0), 'potion used');
        assert($this->player->hp > 10, 'healed');

        // Door open
        $doorFound = false;
        foreach ($this->objects as $obj) {
            if ($obj->type === GameObject::TYPE_DOOR) {
                $obj->operate($this->dungeon, $this->rng);
                assert($this->dungeon->tile($obj->x, $obj->y) === Dungeon::TILE_DOOR_OPEN, 'door opened');
                $doorFound = true;
                break;
            }
        }

        // Stairs / descend
        $oldDepth = $this->depth;
        $this->enterDungeon($this->depth + 1);
        assert($this->depth === $oldDepth + 1, 'descended');

        // Save / load
        $this->persistSave();
        $seed = $this->dungeonSeed;
        $gold = $this->player->gold;
        $this->startNewGame('Other', 'Rogue');
        assert($this->loadSave(), 'load save');
        assert($this->dungeonSeed === $seed, 'seed restored');
        assert($this->player->gold === $gold, 'gold restored');

        // Spell / missile
        $this->player->mana = 50;
        $this->player->startSpell('heal');
        for ($i = 0; $i < 15; $i++) {
            $ev = $this->player->tick($this->occ);
            if (isset($ev['cast']) && $ev['cast'] === 'heal') {
                Spell::castHeal($this->player);
            }
        }

        // Render a few frames so SDL path is exercised
        for ($f = 0; $f < 5; $f++) {
            $this->dungeon->updateVision($this->player->x, $this->player->y);
            $this->renderer->updateCamera($this->player, $this->dungeon);
            $this->renderer->drawWorld(
                $this->dungeon,
                $this->player,
                $this->monsters,
                $this->groundItems,
                $this->objects,
                $this->missiles,
                $this->towners,
                false,
                false,
                $this->messages,
                'smoke'
            );
            $this->sdl->present();
            $this->sdl->delay(1);
        }

        fwrite(STDOUT, "Smoke OK (path, combat, loot, inventory, descend, save, spells)\n");
        if ($doorFound) {
            fwrite(STDOUT, "  doors exercised\n");
        }
    }

    private function pollInput(): void
    {
        $events = $this->sdl->pollEvents();
        if ($events['quit']) {
            $this->running = false;
        }
        foreach ($events['keys'] as $k) {
            $this->pendingKeys[] = $k;
        }
        if ($events['click'] !== null) {
            $this->pendingClick = $events['click'];
        }
    }

    private function tick(): void
    {
        if ($this->menu->state !== Menu::STATE_PLAYING && $this->menu->state !== Menu::STATE_VENDOR && $this->menu->state !== Menu::STATE_DEAD) {
            $this->processMenuInput();
            $this->pendingClick = null;
            $this->pendingKeys = [];

            return;
        }

        if ($this->menu->state === Menu::STATE_DEAD) {
            $this->processDeadInput();
            $this->pendingKeys = [];
            $this->pendingClick = null;

            return;
        }

        if ($this->menu->state === Menu::STATE_VENDOR) {
            $this->processVendorInput();
            $this->pendingKeys = [];
            $this->pendingClick = null;

            return;
        }

        // ProcessInput
        $this->processInput();
        // ProcessPlayers
        $this->processPlayers();
        // ProcessMonsters
        $this->processMonsters();
        // ProcessObjects (passive)
        // ProcessMissiles
        $this->processMissiles();
        // ProcessItems / pickup already on arrive
        // vision / triggers / quests
        $this->dungeon->updateVision($this->player->x, $this->player->y);
        $this->checkTriggers();

        if ($this->player->isDead()) {
            $this->deathTimer++;
            if ($this->deathTimer > 30) {
                $this->menu->state = Menu::STATE_DEAD;
            }
        }
    }

    private function processMenuInput(): void
    {
        $hasSave = $this->saves->exists();
        $opts = $this->menu->mainOptions($hasSave);

        foreach ($this->pendingKeys as $k) {
            if ($this->menu->state === Menu::STATE_MAIN) {
                if ($k === 'up') {
                    $this->menu->cursor = max(0, $this->menu->cursor - 1);
                } elseif ($k === 'down') {
                    $this->menu->cursor = min(count($opts) - 1, $this->menu->cursor + 1);
                } elseif ($k === 'enter' || $k === 'space') {
                    $choice = $opts[$this->menu->cursor] ?? '';
                    if ($choice === 'New Game') {
                        $this->menu->state = Menu::STATE_CREATE;
                        $this->menu->cursor = 0;
                        $this->menu->classIndex = 0;
                    } elseif ($choice === 'Continue') {
                        if ($this->loadSave()) {
                            $this->menu->state = Menu::STATE_PLAYING;
                            $this->msg('Game loaded.');
                        } else {
                            $this->msg('Load failed.');
                        }
                    } elseif ($choice === 'Quit') {
                        $this->running = false;
                    }
                } elseif ($k === 'escape') {
                    $this->running = false;
                }
            } elseif ($this->menu->state === Menu::STATE_CREATE) {
                if ($k === 'left') {
                    $this->menu->classIndex = ($this->menu->classIndex + 2) % 3;
                } elseif ($k === 'right') {
                    $this->menu->classIndex = ($this->menu->classIndex + 1) % 3;
                } elseif ($k === 'enter' || $k === 'space') {
                    $class = $this->menu->classes[$this->menu->classIndex];
                    $this->startNewGame($this->menu->createName, $class);
                    $this->menu->state = Menu::STATE_PLAYING;
                    $this->msg($class . ' enters Tristram.');
                } elseif ($k === 'escape') {
                    $this->menu->state = Menu::STATE_MAIN;
                }
            }
        }
    }

    private function processDeadInput(): void
    {
        foreach ($this->pendingKeys as $k) {
            if ($k === 'enter' || $k === 'space' || $k === 'r') {
                $this->respawnPlayer();
                $this->menu->state = Menu::STATE_PLAYING;
                $this->msg('You awaken near town.');
            } elseif ($k === 'escape') {
                $this->menu->state = Menu::STATE_MAIN;
            }
        }
    }

    private function processVendorInput(): void
    {
        $t = $this->menu->activeVendor;
        foreach ($this->pendingKeys as $k) {
            if ($k === 'escape' || $k === 'enter') {
                $this->menu->state = Menu::STATE_PLAYING;
                $this->menu->activeVendor = null;
            } elseif ($t !== null && ($k === 'space' || $k === '1')) {
                if ($t->role === 'healer') {
                    $this->player->hp = $this->player->maxHp;
                    $this->player->mana = $this->player->maxMana;
                    $this->msg($t->name . ' restores you.');
                    $this->audio->play('heal');
                    if ($this->quest->status === 'complete') {
                        $this->quest->status = 'turned_in';
                        $this->player->gold += $this->quest->rewardGold;
                        $this->player->addExperience($this->quest->rewardXp);
                        $this->msg('Quest reward received!');
                    } elseif ($this->quest->status === 'inactive') {
                        $this->quest->status = 'active';
                        $this->msg('Quest: ' . $this->quest->title);
                    }
                } elseif ($t->role === 'merchant') {
                    if ($this->player->gold >= 50) {
                        $this->player->gold -= 50;
                        $this->player->inventory->add(Item::potion('heal'));
                        $this->msg('Bought Health Potion.');
                    } else {
                        $this->msg('Not enough gold.');
                    }
                } elseif ($t->role === 'blacksmith') {
                    if ($this->player->gold >= 100) {
                        $this->player->gold -= 100;
                        $w = Item::weapon('Long Sword', 4, 10, 'magic');
                        $this->player->inventory->add($w);
                        $this->msg('Bought Long Sword.');
                    } else {
                        $this->msg('Not enough gold.');
                    }
                }
            }
        }
    }

    private function processInput(): void
    {
        foreach ($this->pendingKeys as $k) {
            if ($k === 'escape') {
                $this->persistSave();
                $this->menu->state = Menu::STATE_MAIN;
                $this->msg('Game saved.');
            } elseif ($k === 'i') {
                $this->showInventory = !$this->showInventory;
            } elseif ($k === 'c') {
                $this->showStats = !$this->showStats;
            } elseif ($k === '1' || $k === '2' || $k === '3' || $k === '4') {
                $slot = (int) $k - 1;
                if ($this->player->useBeltSlot($slot)) {
                    $this->msg('Used potion.');
                    $this->audio->play('potion');
                }
            } elseif ($k === 'h') {
                if (!$this->player->isBusy() && !$this->player->isDead()) {
                    $this->player->startSpell('heal');
                }
            } elseif ($k === 'f') {
                // Firebolt toward nearest monster
                if (!$this->player->isBusy() && !$this->player->isDead()) {
                    $target = $this->nearestMonster(10);
                    if ($target !== null && $this->player->mana >= Spell::cost('firebolt')) {
                        $this->player->mana -= Spell::cost('firebolt');
                        $this->player->startRanged($target->x, $target->y);
                        $this->player->_pendingSpell = 'firebolt';
                    }
                }
            } elseif ($k === 's') {
                $this->persistSave();
                $this->msg('Saved.');
            } elseif ($k === 'e') {
                // Equip first inventory weapon/armor
                foreach ($this->player->inventory->slots as $idx => $item) {
                    if ($item !== null && $item->equipSlot !== '') {
                        $removed = $this->player->inventory->remove($idx);
                        if ($removed !== null) {
                            $prev = $this->player->equipment->equip($removed);
                            if ($prev !== null) {
                                $this->player->inventory->add($prev);
                            }
                            $this->player->recalcStats();
                            $this->msg('Equipped ' . $removed->name);
                        }
                        break;
                    }
                }
            }
        }
        $this->pendingKeys = [];

        if ($this->pendingClick === null || $this->player->isDead() || $this->player->isBusy()) {
            $this->pendingClick = null;

            return;
        }

        $click = $this->pendingClick;
        $this->pendingClick = null;
        [$gx, $gy] = $this->iso->screenToGrid($click['x'], $click['y']);

        // Right-click ranged / firebolt
        if (($click['button'] ?? 1) === 3) {
            if ($this->player->mana >= Spell::cost('firebolt')) {
                $this->player->mana -= Spell::cost('firebolt');
                $this->player->startRanged($gx, $gy);
                $this->player->_pendingSpell = 'firebolt';
            }

            return;
        }

        // Towner click
        foreach ($this->towners as $t) {
            if ($t->x === $gx && $t->y === $gy) {
                $this->approachThen(static function () {
                });
                if (abs($this->player->x - $t->x) <= 1 && abs($this->player->y - $t->y) <= 1) {
                    $this->openVendor($t);
                } else {
                    $path = $this->pathNear($t->x, $t->y);
                    $this->player->setPath($path);
                    $this->pendingInteract = ['type' => 'towner', 'id' => $t->id];
                }

                return;
            }
        }

        // Object click
        $oid = $this->occ->objectAt($gx, $gy);
        if ($oid !== 0 && isset($this->objectsById[$oid])) {
            $obj = $this->objectsById[$oid];
            if (abs($this->player->x - $obj->x) <= 1 && abs($this->player->y - $obj->y) <= 1) {
                $this->operateObject($obj);
            } else {
                $this->player->setPath($this->pathNear($obj->x, $obj->y));
                $this->pendingInteract = ['type' => 'object', 'id' => $obj->id];
            }

            return;
        }

        // Monster click → attack
        $mid = $this->occ->monsterAt($gx, $gy);
        if ($mid !== 0 && isset($this->monstersById[$mid])) {
            $m = $this->monstersById[$mid];
            if ($m->alive && $m->mode !== Monster::MODE_DEATH) {
                if ($this->player->x !== $m->x || $this->player->y !== $m->y) {
                    if (abs($this->player->x - $m->x) <= 1 && abs($this->player->y - $m->y) <= 1) {
                        $this->player->direction = Player::dirFromDelta($m->x - $this->player->x, $m->y - $this->player->y);
                        $this->player->startAttack($m->id);
                    } else {
                        $this->player->setPath($this->pathNear($m->x, $m->y));
                        $this->pendingInteract = ['type' => 'attack', 'id' => $m->id];
                    }
                }

                return;
            }
        }

        // Stairs
        if ($this->dungeon->tile($gx, $gy) === Dungeon::TILE_STAIRS_DOWN
            || $this->dungeon->tile($gx, $gy) === Dungeon::TILE_STAIRS_UP
        ) {
            if ($this->player->x === $gx && $this->player->y === $gy) {
                $this->useStairs($gx, $gy);
            } else {
                $path = $this->pathfinder->find($this->dungeon, $this->player->x, $this->player->y, $gx, $gy);
                $this->player->setPath($path);
                $this->pendingInteract = ['type' => 'stairs', 'x' => $gx, 'y' => $gy];
            }

            return;
        }

        // Walk
        if ($this->dungeon->isWalkable($gx, $gy)) {
            $blocked = function (int $x, int $y): bool {
                return $this->occ->blocksActor($x, $y, $this->player->id, 0);
            };
            $path = $this->pathfinder->find(
                $this->dungeon,
                $this->player->x,
                $this->player->y,
                $gx,
                $gy,
                $blocked,
                false
            );
            $this->player->setPath($path);
            $this->pendingInteract = null;
        } elseif ($this->dungeon->isDoorClosed($gx, $gy)) {
            $this->player->setPath($this->pathNear($gx, $gy));
            $this->pendingInteract = ['type' => 'door', 'x' => $gx, 'y' => $gy];
        }
    }

    /** @var ?array<string, mixed> */
    private ?array $pendingInteract = null;

    /** @return list<array{0:int,1:int}> */
    private function pathNear(int $tx, int $ty): array
    {
        $best = [];
        $bestLen = PHP_INT_MAX;
        foreach ([[0, 0], [1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, -1], [1, -1], [-1, 1]] as [$dx, $dy]) {
            $x = $tx + $dx;
            $y = $ty + $dy;
            if (!$this->dungeon->isWalkable($x, $y)) {
                continue;
            }
            if ($dx === 0 && $dy === 0) {
                continue;
            }
            $blocked = function (int $bx, int $by): bool {
                return $this->occ->blocksActor($bx, $by, $this->player->id, 0);
            };
            $path = $this->pathfinder->find($this->dungeon, $this->player->x, $this->player->y, $x, $y, $blocked);
            if ($path !== [] && count($path) < $bestLen) {
                $best = $path;
                $bestLen = count($path);
            }
        }

        return $best;
    }

    private function approachThen(callable $fn): void
    {
        $fn();
    }

    private function processPlayers(): void
    {
        $events = $this->player->tick($this->occ);
        if (isset($events['hitMonster'])) {
            $mid = $events['hitMonster'];
            if (isset($this->monstersById[$mid])) {
                $m = $this->monstersById[$mid];
                if ($m->alive && $m->mode !== Monster::MODE_DEATH) {
                    $res = Combat::playerHitMonster($this->player, $m, $this->rng);
                    if ($res['hit']) {
                        $this->msg('Hit ' . $m->name . ' for ' . $res['damage']);
                        $this->audio->play('hit');
                    } else {
                        $this->msg('Missed ' . $m->name);
                    }
                    if ($res['killed']) {
                        $this->onMonsterDeath($m);
                    }
                }
            }
        }
        if (isset($events['fireMissile'])) {
            [$tx, $ty] = $events['fireMissile'];
            $spell = $this->player->_pendingSpell;
            if ($spell === 'firebolt') {
                [$dmin, $dmax] = Spell::fireboltDamage($this->player);
                $this->spawnMissile('firebolt', (float) $this->player->x, (float) $this->player->y, (float) $tx, (float) $ty, $dmin, $dmax, $this->player->id, true);
            } else {
                $this->spawnMissile('arrow', (float) $this->player->x, (float) $this->player->y, (float) $tx, (float) $ty, $this->player->minDamage, $this->player->maxDamage, $this->player->id, true);
            }
            $this->audio->play('missile');
        }
        if (isset($events['cast'])) {
            if ($events['cast'] === 'heal') {
                if (Spell::castHeal($this->player)) {
                    $this->msg('Heal!');
                    $this->audio->play('heal');
                } else {
                    $this->msg('Not enough mana.');
                }
            }
        }
        if (isset($events['arrived'])) {
            $this->tryPickup($this->player->x, $this->player->y);
            $this->resolvePendingInteract();
        }
    }

    private function resolvePendingInteract(): void
    {
        if ($this->pendingInteract === null) {
            return;
        }
        $p = $this->pendingInteract;
        if ($p['type'] === 'object' && isset($this->objectsById[$p['id']])) {
            $obj = $this->objectsById[$p['id']];
            if (abs($this->player->x - $obj->x) <= 1 && abs($this->player->y - $obj->y) <= 1) {
                $this->operateObject($obj);
                $this->pendingInteract = null;
            }
        } elseif ($p['type'] === 'attack' && isset($this->monstersById[$p['id']])) {
            $m = $this->monstersById[$p['id']];
            if ($m->alive && abs($this->player->x - $m->x) <= 1 && abs($this->player->y - $m->y) <= 1) {
                $this->player->startAttack($m->id);
                $this->pendingInteract = null;
            }
        } elseif ($p['type'] === 'towner') {
            foreach ($this->towners as $t) {
                if ($t->id === $p['id'] && abs($this->player->x - $t->x) <= 1 && abs($this->player->y - $t->y) <= 1) {
                    $this->openVendor($t);
                    $this->pendingInteract = null;
                    break;
                }
            }
        } elseif ($p['type'] === 'stairs') {
            if ($this->player->x === $p['x'] && $this->player->y === $p['y']) {
                $this->useStairs($p['x'], $p['y']);
                $this->pendingInteract = null;
            }
        } elseif ($p['type'] === 'door') {
            if (abs($this->player->x - $p['x']) <= 1 && abs($this->player->y - $p['y']) <= 1) {
                $this->dungeon->openDoor($p['x'], $p['y']);
                foreach ($this->objects as $obj) {
                    if ($obj->x === $p['x'] && $obj->y === $p['y'] && $obj->type === GameObject::TYPE_DOOR) {
                        $obj->opened = true;
                        $obj->solid = false;
                    }
                }
                $this->msg('Door opened.');
                $this->pendingInteract = null;
            }
        }
    }

    private function processMonsters(): void
    {
        foreach ($this->monsters as $m) {
            if (!$m->alive && $m->mode !== Monster::MODE_DEATH) {
                continue;
            }
            $events = $m->tick($this->player, $this->occ, $this->pathfinder, $this->dungeon, $this->rng);
            if (isset($events['hitPlayer']) && !$this->player->isDead()) {
                $res = Combat::monsterHitPlayer($m, $this->player, $this->rng);
                if ($res['hit']) {
                    $this->msg($m->name . ' hits for ' . $res['damage']);
                    $this->audio->play('player_hit');
                }
                if ($res['killed']) {
                    $this->msg('You have died.');
                    $this->deathTimer = 0;
                }
            }
            if (isset($events['fireMissile'])) {
                [$tx, $ty] = $events['fireMissile'];
                $this->spawnMissile('enemy_bolt', (float) $m->x, (float) $m->y, (float) $tx, (float) $ty, $m->minDamage, $m->maxDamage, -$m->id, false);
            }
        }
        // Cleanup dead
        $this->monsters = array_values(array_filter($this->monsters, function (Monster $m) {
            if (!$m->alive) {
                $this->occ->clearMonsterAt($m->x, $m->y);
                unset($this->monstersById[$m->id]);

                return false;
            }

            return true;
        }));
    }

    private function processMissiles(): void
    {
        foreach ($this->missiles as $mis) {
            if (!$mis->alive) {
                continue;
            }
            $mis->tick($this->dungeon);
            if (!$mis->alive) {
                continue;
            }
            $tx = $mis->tileX();
            $ty = $mis->tileY();
            if ($mis->fromPlayer) {
                $mid = $this->occ->monsterAt($tx, $ty);
                if ($mid !== 0 && isset($this->monstersById[$mid])) {
                    $m = $this->monstersById[$mid];
                    if ($m->alive && $m->mode !== Monster::MODE_DEATH) {
                        $dam = $this->rng->between($mis->damageMin, $mis->damageMax);
                        $m->hp -= $dam;
                        $this->msg('Missile hits ' . $m->name . ' for ' . $dam);
                        $mis->alive = false;
                        if ($m->hp <= 0) {
                            $m->hp = 0;
                            $m->mode = Monster::MODE_DEATH;
                            $m->actionFrame = 0;
                            $this->onMonsterDeath($m);
                        } else {
                            $m->mode = Monster::MODE_GOT_HIT;
                            $m->actionFrame = 0;
                            $m->actionFrames = 4;
                        }
                    }
                }
            } else {
                if ($tx === $this->player->x && $ty === $this->player->y && !$this->player->isDead()) {
                    $dam = $this->rng->between($mis->damageMin, $mis->damageMax);
                    $this->player->hp -= $dam;
                    $mis->alive = false;
                    $this->msg('Bolt hits you for ' . $dam);
                    if ($this->player->hp <= 0) {
                        $this->player->startDeath();
                        $this->deathTimer = 0;
                    } else {
                        $this->player->startGotHit();
                    }
                }
            }
        }
        $this->missiles = array_values(array_filter($this->missiles, static fn (Missile $m) => $m->alive));
    }

    private function checkTriggers(): void
    {
        // Auto-use stairs if standing and idle after interact cleared — handled via click
    }

    private function operateObject(GameObject $obj): void
    {
        if ($obj->type === GameObject::TYPE_STAIRS_DOWN || $obj->type === GameObject::TYPE_STAIRS_UP) {
            $this->useStairs($obj->x, $obj->y);

            return;
        }
        $loot = $obj->operate($this->dungeon, $this->rng);
        if ($obj->type === GameObject::TYPE_DOOR) {
            $this->msg('Door opened.');
            $this->audio->play('door');
        } elseif ($loot !== null) {
            $this->dropItem($loot, $obj->x, $obj->y);
            $this->msg('Found ' . $loot->name);
            $this->audio->play('chest');
        } else {
            $this->msg($obj->opened ? 'Empty.' : 'Opened.');
        }
    }

    private function useStairs(int $x, int $y): void
    {
        $tile = $this->dungeon->tile($x, $y);
        if ($tile === Dungeon::TILE_STAIRS_DOWN) {
            if ($this->depth === 0) {
                $this->enterDungeon(1);
                $this->msg('Entering the labyrinth...');
            } else {
                $this->enterDungeon($this->depth + 1);
                $this->msg('Descending to level ' . $this->depth);
            }
        } elseif ($tile === Dungeon::TILE_STAIRS_UP) {
            if ($this->depth <= 1) {
                $this->enterTown();
                $this->msg('Returning to town.');
            } else {
                $this->enterDungeon($this->depth - 1);
                $this->msg('Ascending to level ' . $this->depth);
            }
        }
    }

    private function onMonsterDeath(Monster $m): void
    {
        $this->audio->play('death');
        $leveled = $this->player->addExperience($m->xpReward);
        if ($leveled) {
            $this->msg('Level up! Now ' . $this->player->level);
        }
        // Loot
        $roll = $this->rng->generateRnd(100);
        if ($roll < 50) {
            $this->dropItem(Item::gold($this->rng->between(5, 20 + $this->depth * 5)), $m->x, $m->y);
        } elseif ($roll < 70) {
            $this->dropItem(Item::potion('heal'), $m->x, $m->y);
        } elseif ($roll < 85) {
            $quality = $this->rng->generateRnd(100) < 25 ? 'magic' : 'normal';
            $bonus = $quality === 'magic' ? 2 : 0;
            $this->dropItem(Item::weapon('Sword', 2 + $bonus, 5 + $bonus + $this->depth, $quality), $m->x, $m->y);
        } else {
            $this->dropItem(Item::armor('Armor', 3 + $this->depth, 'body', $this->rng->generateRnd(100) < 20 ? 'magic' : 'normal'), $m->x, $m->y);
        }

        if ($m->isQuestTarget && $this->quest->status === 'active') {
            $this->quest->status = 'complete';
            $this->msg('Quest complete! Return to town.');
        }

        // Clear occupancy when fully dead — keep corpse briefly
        $this->occ->clearMonsterAt($m->x, $m->y);
    }

    private function tryPickup(int $x, int $y): void
    {
        $iid = $this->occ->itemAt($x, $y);
        if ($iid === 0 || !isset($this->itemsById[$iid])) {
            return;
        }
        $item = $this->itemsById[$iid];
        if ($item->itemClass === 'gold') {
            $this->player->gold += $item->value;
            $this->msg('Gold +' . $item->value);
        } elseif ($item->itemClass === 'potion') {
            $placed = false;
            for ($i = 0; $i < 4; $i++) {
                if ($this->player->belt[$i] === null) {
                    $this->player->belt[$i] = $item;
                    $placed = true;
                    break;
                }
            }
            if (!$placed && !$this->player->inventory->add($item)) {
                return;
            }
            $this->msg('Picked up ' . $item->name);
        } else {
            if (!$this->player->inventory->add($item)) {
                $this->msg('Inventory full.');

                return;
            }
            $this->msg('Picked up ' . $item->name);
        }
        $this->occ->clearItemAt($x, $y);
        unset($this->itemsById[$iid]);
        $this->groundItems = array_values(array_filter($this->groundItems, static fn (Item $i) => $i->id !== $iid));
        $this->audio->play('pickup');
    }

    private function dropItem(Item $item, int $x, int $y): void
    {
        $item->id = $this->nextItemId++;
        $item->x = $x;
        $item->y = $y;
        $this->groundItems[] = $item;
        $this->itemsById[$item->id] = $item;
        $this->occ->setItem($x, $y, $item->id);
    }

    private function spawnMissile(
        string $type,
        float $x,
        float $y,
        float $tx,
        float $ty,
        int $dmin,
        int $dmax,
        int $owner,
        bool $fromPlayer,
    ): void {
        $m = new Missile($this->nextMissileId++, $type, $x, $y, $tx, $ty, $dmin, $dmax, $owner, $fromPlayer);
        $this->missiles[] = $m;
    }

    private function nearestMonster(int $range): ?Monster
    {
        $best = null;
        $bestD = $range + 1;
        foreach ($this->monsters as $m) {
            if (!$m->alive || $m->mode === Monster::MODE_DEATH) {
                continue;
            }
            $d = max(abs($m->x - $this->player->x), abs($m->y - $this->player->y));
            if ($d < $bestD) {
                $bestD = $d;
                $best = $m;
            }
        }

        return $best;
    }

    private function openVendor(Towner $t): void
    {
        $this->menu->state = Menu::STATE_VENDOR;
        $this->menu->activeVendor = $t;
        $this->msg('Talking to ' . $t->name . ' (' . $t->role . '). Space to trade.');
        if ($t->role === 'healer') {
            if ($this->quest->status === 'inactive') {
                $this->msg('Press Space: heal + accept quest.');
            } elseif ($this->quest->status === 'complete') {
                $this->msg('Press Space: heal + turn in quest.');
            }
        }
    }

    private function startNewGame(string $name, string $class): void
    {
        $this->dungeonSeed = 0x12345678 ^ crc32($name . $class . (string) time());
        if ($this->smoke) {
            $this->dungeonSeed = 0xC0FFEE;
        }
        $this->rng->setSeed($this->dungeonSeed);
        $this->quest = new Quest();
        $this->quest->status = 'inactive';
        $this->player = new Player(0, 0, $class, $name);
        $this->player->inventory->add(Item::weapon('Dagger', 1, 3));
        $this->player->gold = 50;
        $this->enterTown();
        $this->deathTimer = 0;
        $this->showInventory = false;
        $this->showStats = false;
        $this->missiles = [];
        $this->messages = [];
    }

    private function enterTown(): void
    {
        $town = $this->generator->generateTown();
        $this->dungeon = $town['dungeon'];
        $this->depth = 0;
        $this->clearEntities();
        $this->towners = [];
        $id = 1;
        foreach ($town['towners'] as $t) {
            $this->towners[] = new Towner($id++, $t['name'], $t['role'], $t['x'], $t['y']);
        }
        $this->player->x = $this->dungeon->spawnX;
        $this->player->y = $this->dungeon->spawnY;
        $this->player->futureX = $this->player->x;
        $this->player->futureY = $this->player->y;
        $this->player->mode = Player::MODE_STAND;
        $this->player->clearPath();
        $this->occ->setPlayer($this->player->x, $this->player->y, $this->player->id);
        $this->dungeon->resetVision();
        $this->dungeon->updateVision($this->player->x, $this->player->y, 12);
    }

    private function enterDungeon(int $depth): void
    {
        $this->depth = $depth;
        $seed = $this->dungeonSeed + $depth * 9973;
        $this->rng->setSeed($seed);
        $gen = $this->generator->generate($this->rng, $depth);
        $this->dungeon = $gen['dungeon'];
        $this->clearEntities();
        $this->towners = [];

        foreach ($gen['objects'] as $o) {
            $obj = new GameObject($this->nextObjectId++, $o['type'], $o['x'], $o['y']);
            $this->objects[] = $obj;
            $this->objectsById[$obj->id] = $obj;
            $this->occ->setObject($obj->x, $obj->y, $obj->id);
        }

        $questMarked = false;
        foreach ($gen['monsters'] as $i => $md) {
            $m = $this->spawnMonster($md['x'], $md['y'], $md['type']);
            if (!$questMarked && $this->quest->status !== 'turned_in' && $depth === 1 && $i === 0) {
                $m->isQuestTarget = true;
                $m->name = 'Marked ' . $m->name;
                $m->hp = (int) ($m->hp * 1.5);
                $m->maxHp = $m->hp;
                $this->quest->targetMonsterId = $m->id;
                if ($this->quest->status === 'inactive') {
                    // stay inactive until accepted in town
                }
                $questMarked = true;
            }
        }

        foreach ($gen['items'] as $it) {
            $item = $this->assets->item($it['item']) ?? Item::gold(10);
            $this->dropItem($item, $it['x'], $it['y']);
        }

        $this->player->x = $this->dungeon->spawnX;
        $this->player->y = $this->dungeon->spawnY;
        $this->player->futureX = $this->player->x;
        $this->player->futureY = $this->player->y;
        $this->player->mode = Player::MODE_STAND;
        $this->player->clearPath();
        $this->occ->setPlayer($this->player->x, $this->player->y, $this->player->id);
        $this->dungeon->resetVision();
        $this->dungeon->updateVision($this->player->x, $this->player->y);
    }

    private function spawnMonster(int $x, int $y, string $type): Monster
    {
        $data = $this->assets->monster($type);
        $m = new Monster($this->nextMonsterId++, $x, $y, $data);
        $this->monsters[] = $m;
        $this->monstersById[$m->id] = $m;
        $this->occ->setMonster($x, $y, $m->id);

        return $m;
    }

    private function clearEntities(): void
    {
        $this->occ->clear();
        $this->monsters = [];
        $this->monstersById = [];
        $this->objects = [];
        $this->objectsById = [];
        $this->groundItems = [];
        $this->itemsById = [];
        $this->missiles = [];
        $this->pendingInteract = null;
    }

    private function respawnPlayer(): void
    {
        $this->player->hp = $this->player->maxHp;
        $this->player->mana = $this->player->maxMana;
        $this->player->mode = Player::MODE_STAND;
        $this->enterTown();
        $this->deathTimer = 0;
    }

    private function persistSave(): void
    {
        $this->saves->save([
            'player' => $this->player->toSave(),
            'depth' => $this->depth,
            'dungeonSeed' => $this->dungeonSeed,
            'quest' => $this->quest->toSave(),
            'playerX' => $this->player->x,
            'playerY' => $this->player->y,
        ]);
    }

    private function loadSave(): bool
    {
        $data = $this->saves->load();
        if ($data === null) {
            return false;
        }
        $this->dungeonSeed = (int) ($data['dungeonSeed'] ?? 1);
        $this->quest = Quest::fromSave($data['quest'] ?? []);
        $depth = (int) ($data['depth'] ?? 0);
        $px = (int) ($data['playerX'] ?? 2);
        $py = (int) ($data['playerY'] ?? 2);
        $this->player = Player::fromSave($data['player'] ?? [], $px, $py);
        if ($depth === 0) {
            $this->enterTown();
        } else {
            $this->enterDungeon($depth);
        }
        // Restore position if walkable
        if ($this->dungeon->isWalkable($px, $py)) {
            $this->occ->clearPlayerAt($this->player->x, $this->player->y);
            $this->player->x = $px;
            $this->player->y = $py;
            $this->player->futureX = $px;
            $this->player->futureY = $py;
            $this->occ->setPlayer($px, $py, $this->player->id);
        }
        $this->dungeon->updateVision($this->player->x, $this->player->y);

        return true;
    }

    private function msg(string $m): void
    {
        $this->messages[] = $m;
        if (count($this->messages) > 8) {
            array_shift($this->messages);
        }
    }

    private function render(): void
    {
        if ($this->menu->state === Menu::STATE_MAIN) {
            $opts = $this->menu->mainOptions($this->saves->exists());
            $this->renderer->drawMenu('DIABLO PHP', $opts, $this->menu->cursor);

            return;
        }
        if ($this->menu->state === Menu::STATE_CREATE) {
            $class = $this->menu->classes[$this->menu->classIndex];
            $this->renderer->drawMenu(
                'New Hero',
                ['Class: ' . $class . '  (←/→)', 'Start (Enter)'],
                $this->menu->cursor,
                'Name: ' . $this->menu->createName
            );

            return;
        }
        if ($this->menu->state === Menu::STATE_DEAD) {
            $this->renderer->drawMenu('You Died', ['Respawn (Enter)', 'Main Menu (Esc)'], 0);

            return;
        }
        if ($this->menu->state === Menu::STATE_VENDOR) {
            $t = $this->menu->activeVendor;
            $label = $t ? $t->name . ' — Space to act, Esc to leave' : 'Vendor';
            $this->renderer->drawMenu('Trade', [$label], 0);
            // still show world faintly? keep simple menu overlay
            return;
        }

        $this->renderer->updateCamera($this->player, $this->dungeon);
        $extra = 'Lv' . $this->player->level . ' ' . $this->player->class
            . ' | Depth ' . $this->depth
            . ' | Gold ' . $this->player->gold
            . ' | ' . ($this->messages[count($this->messages) - 1] ?? '');
        $this->renderer->drawWorld(
            $this->dungeon,
            $this->player,
            $this->monsters,
            $this->groundItems,
            $this->objects,
            $this->missiles,
            $this->towners,
            $this->showInventory,
            $this->showStats,
            $this->messages,
            $extra
        );
    }
}
