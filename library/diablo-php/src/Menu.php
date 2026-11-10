<?php

declare(strict_types=1);

namespace Diablo;

final class Menu
{
    public const STATE_MAIN = 'main';
    public const STATE_CREATE = 'create';
    public const STATE_PLAYING = 'playing';
    public const STATE_VENDOR = 'vendor';
    public const STATE_DEAD = 'dead';

    public string $state = self::STATE_MAIN;
    public int $cursor = 0;
    /** @var list<string> */
    public array $classes = ['Warrior', 'Rogue', 'Sorcerer'];
    public string $createName = 'Hero';
    public int $classIndex = 0;
    public ?Towner $activeVendor = null;
    public int $vendorCursor = 0;

    /** @return list<string> */
    public function mainOptions(bool $hasSave): array
    {
        $opts = ['New Game', 'Quit'];
        if ($hasSave) {
            array_splice($opts, 1, 0, ['Continue']);
        }

        return $opts;
    }
}
