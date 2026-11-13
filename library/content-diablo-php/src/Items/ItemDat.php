<?php

declare(strict_types=1);

namespace Diablo\Items;

use Diablo\Engine\Random;

/**
 * itemdat.tsv (+ optional prefix/suffix) ingest and SetupAllItems-style early generation.
 */
final class ItemDat
{
    /** @var list<array<string,string>>|null */
    private static ?array $items = null;
    /** @var list<array<string,string>>|null */
    private static ?array $prefixes = null;
    /** @var list<array<string,string>>|null */
    private static ?array $suffixes = null;

    /** @return list<array<string,string>> */
    public static function allItems(): array
    {
        if (self::$items === null) {
            self::$items = self::loadTsv(dirname(__DIR__, 2) . '/assets/txtdata/items/itemdat.tsv');
        }

        return self::$items;
    }

    /** @return list<array<string,string>> */
    private static function loadTsv(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $header = str_getcsv(array_shift($lines) ?: '', "\t", '"', '\\');
        $rows = [];
        foreach ($lines as $line) {
            $cols = str_getcsv($line, "\t", '"', '\\');
            if ($cols === [] || $cols[0] === null) {
                continue;
            }
            $row = [];
            foreach ($header as $i => $key) {
                $row[trim($key)] = $cols[$i] ?? '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /** @return list<array<string,string>> */
    public static function prefixes(): array
    {
        return self::$prefixes ??= self::loadTsv(dirname(__DIR__, 2) . '/assets/txtdata/items/item_prefixes.tsv');
    }

    /** @return list<array<string,string>> */
    public static function suffixes(): array
    {
        return self::$suffixes ??= self::loadTsv(dirname(__DIR__, 2) . '/assets/txtdata/items/item_suffixes.tsv');
    }

    /** @var list<array<string,string>>|null */
    private static ?array $uniques = null;

    /** @return list<array<string,string>> */
    public static function uniques(): array
    {
        return self::$uniques ??= self::loadTsv(dirname(__DIR__, 2) . '/assets/txtdata/items/unique_itemdat.tsv');
    }

    /**
     * Roll a drop from itemdat for dungeon level (dropRate > 0, minMonsterLevel).
     * @return array<string,mixed>
     */
    public static function setupAllItems(Random $rng, int $dlvl): array
    {
        // Rare unique (~2% + dlvl/4)
        if ($rng->generateRnd(100) < 2 + intdiv($dlvl, 4)) {
            $uniq = self::rollUnique($rng, $dlvl);
            if ($uniq !== null) {
                return $uniq;
            }
        }
        $candidates = [];
        foreach (self::allItems() as $row) {
            $rate = (int) ($row['dropRate'] ?? 0);
            if ($rate <= 0) {
                continue;
            }
            $minLvl = (int) ($row['minMonsterLevel'] ?? 1);
            if ($minLvl > $dlvl + 2) {
                continue;
            }
            $candidates[] = $row;
        }
        if ($candidates === []) {
            return ItemData::rollDrop($rng, $dlvl);
        }
        if ($rng->generateRnd(100) < 40) {
            return [
                'type' => 'gold',
                'name' => 'Gold',
                'value' => $rng->between(5, 20 + $dlvl * 8),
                'invW' => 1,
                'invH' => 1,
            ];
        }
        $row = $candidates[$rng->generateRnd(count($candidates))];

        return self::itemFromRow($rng, $row, $dlvl);
    }

    /**
     * GetItemAttrs unique path — base by uniqueBaseItem + unique_itemdat powers.
     * @return array<string,mixed>|null
     */
    public static function rollUnique(Random $rng, int $dlvl): ?array
    {
        $pool = [];
        foreach (self::uniques() as $u) {
            $min = (int) ($u['minLevel'] ?? 1);
            if ($min > $dlvl + 2) {
                continue;
            }
            $pool[] = $u;
        }
        if ($pool === []) {
            return null;
        }
        $u = $pool[$rng->generateRnd(count($pool))];
        $baseKey = strtoupper((string) ($u['uniqueBaseItem'] ?? ''));
        $row = null;
        foreach (self::allItems() as $r) {
            if (strtoupper((string) ($r['uniqueBaseItem'] ?? '')) === $baseKey && $baseKey !== '') {
                $row = $r;
                break;
            }
        }
        if ($row === null) {
            foreach (self::allItems() as $r) {
                if (strtoupper((string) ($r['id'] ?? '')) === 'IDI_' . $baseKey) {
                    $row = $r;
                    break;
                }
            }
        }
        if ($row === null) {
            return null;
        }
        $item = self::itemFromRow($rng, $row, $dlvl, false);
        $item['quality'] = 'unique';
        $item['identified'] = false;
        // Keep base type name for unidentified PrintItem; unique name after ID
        $item['baseName'] = (string) ($item['baseName'] ?? $item['name'] ?? 'Item');
        $item['uniqueName'] = (string) ($u['name'] ?? $item['baseName']);
        $item['name'] = $item['uniqueName'];
        $item['value'] = (int) ($u['value'] ?? $item['value'] ?? 0);
        $cg = trim((string) ($u['cursorGraphic'] ?? ''));
        if ($cg !== '') {
            $item['cursorGraphic'] = $cg;
        }
        for ($i = 0; $i < 6; $i++) {
            $power = strtoupper(trim((string) ($u['power' . $i] ?? '')));
            if ($power === '') {
                continue;
            }
            self::applyAffixPower($item, [
                'power' => $power,
                'power.value1' => (string) ($u['power' . $i . '.value1'] ?? '0'),
                'power.value2' => (string) ($u['power' . $i . '.value2'] ?? '0'),
            ], $rng);
        }

        return $item;
    }

    /**
     * CreateTypeItem stand-in — filter by itemType (LightArmor, Sword, …) or class (Armor/Weapon).
     * @param string|list<string> $typeOrClass ItemType name(s) or class name
     * @return array<string,mixed>
     */
    public static function setupClassItems(Random $rng, int $dlvl, string|array $typeOrClass): array
    {
        $wanted = is_array($typeOrClass) ? $typeOrClass : [$typeOrClass];
        $wantedLower = array_map(static fn (string $s): string => strtolower($s), $wanted);
        $candidates = [];
        foreach (self::allItems() as $row) {
            $rate = (int) ($row['dropRate'] ?? 0);
            if ($rate <= 0) {
                continue;
            }
            $itemType = strtolower((string) ($row['itemType'] ?? ''));
            $class = strtolower((string) ($row['class'] ?? ''));
            $match = false;
            foreach ($wantedLower as $w) {
                if ($itemType === $w || $class === $w) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                continue;
            }
            $minLvl = (int) ($row['minMonsterLevel'] ?? 1);
            if ($minLvl > $dlvl + 3) {
                continue;
            }
            $candidates[] = $row;
        }
        if ($candidates === []) {
            return self::setupAllItems($rng, $dlvl);
        }
        $row = $candidates[$rng->generateRnd(count($candidates))];

        return self::itemFromRow($rng, $row, $dlvl);
    }

    /** OperateArmorStand level bands → Light / Medium / Heavy body armor. */
    public static function setupArmorStandItem(Random $rng, int $dlvl): array
    {
        $itype = match (true) {
            $dlvl <= 5 => 'LightArmor',
            $dlvl <= 9 => 'MediumArmor',
            default => 'HeavyArmor',
        };

        return self::setupClassItems($rng, $dlvl, $itype);
    }

    /** OperateWeaponRack — Sword / Axe / Bow / Mace. */
    public static function setupWeaponRackItem(Random $rng, int $dlvl): array
    {
        $pick = ['Sword', 'Axe', 'Bow', 'Mace'][$rng->generateRnd(4)];

        return self::setupClassItems($rng, $dlvl, $pick);
    }

    /** CreateTypeItem Misc with IMISC_BOOK / IMISC_SCROLL. */
    public static function setupMiscIdItem(Random $rng, int $dlvl, string $miscId): array
    {
        $want = strtoupper($miscId);
        $candidates = [];
        foreach (self::allItems() as $row) {
            $rate = (int) ($row['dropRate'] ?? 0);
            if ($rate <= 0) {
                continue;
            }
            if (strtoupper((string) ($row['miscId'] ?? '')) !== $want) {
                continue;
            }
            $minLvl = (int) ($row['minMonsterLevel'] ?? 1);
            if ($minLvl > $dlvl + 3) {
                continue;
            }
            $candidates[] = $row;
        }
        if ($candidates === []) {
            return self::setupAllItems($rng, $dlvl);
        }
        $row = $candidates[$rng->generateRnd(count($candidates))];
        $item = self::itemFromRow($rng, $row, $dlvl);
        if ($want === 'BOOK' && str_ends_with(rtrim((string) ($item['name'] ?? '')), 'of')) {
            $item['name'] = rtrim((string) $item['name']) . ' Knowledge';
        }

        return $item;
    }

    /**
     * @param array<string,string> $row
     * @return array<string,mixed>
     */
    private static function itemFromRow(Random $rng, array $row, int $dlvl, bool $allowMagic = true): array
    {
        $class = strtolower($row['class'] ?? 'misc');
        $type = match ($class) {
            'gold' => 'gold',
            'weapon' => 'weapon',
            'armor' => 'armor',
            'misc' => self::miscItemType($row),
            default => 'misc',
        };
        $item = [
            'type' => $type,
            'name' => $row['name'] ?? 'Item',
            'id' => $row['id'] ?? '',
            'cursorGraphic' => (string) ($row['cursorGraphic'] ?? ''),
            'itemType' => (string) ($row['itemType'] ?? ''),
            'miscId' => strtoupper((string) ($row['miscId'] ?? '')),
            'spell' => (string) ($row['spell'] ?? 'Null'),
            'minDamage' => (int) ($row['minDamage'] ?? 0),
            'maxDamage' => (int) ($row['maxDamage'] ?? 0),
            'armor' => (int) ($row['minArmor'] ?? 0),
            'value' => (int) ($row['value'] ?? 0),
            'equipType' => $row['equipType'] ?? 'Unequippable',
            'quality' => 'normal',
            'identified' => true,
            'invW' => self::footprintW($row),
            'invH' => self::footprintH($row),
            // DUR_INDESTRUCTIBLE = 255
            'durability' => (int) ($row['durability'] ?? 0),
            'maxDurability' => (int) ($row['durability'] ?? 0),
        ];
        $item['baseName'] = (string) $item['name'];
        // Staff charges (miscId STAFF / itemType Staff)
        $itemType = strtolower((string) ($row['itemType'] ?? ''));
        $misc = (string) $item['miscId'];
        $spell = (string) $item['spell'];
        if ($itemType === 'staff' || $misc === 'STAFF') {
            // GetStaffSpell — Null base staffs roll a castable spell + charge band
            if ($spell === '' || $spell === 'Null') {
                $spell = 'Firebolt';
            }
            $item['spell'] = $spell;
            $maxCh = 10 + $rng->generateRnd(31); // DX-ish staff charge roll band
            $item['maxCharges'] = $maxCh;
            $item['charges'] = $maxCh;
            if (!str_contains(strtolower((string) $item['name']), ' of ')) {
                $item['name'] = $item['baseName'] . ' of ' . $spell;
            }
        }
        if ($type === 'weapon' || $type === 'armor') {
            $item['equipSlot'] = match (true) {
                str_contains(strtolower($row['itemType'] ?? ''), 'helm') => 'head',
                str_contains(strtolower($row['itemType'] ?? ''), 'shield') => 'offhand',
                str_contains(strtolower($row['class'] ?? ''), 'armor') && !str_contains(strtolower($row['itemType'] ?? ''), 'shield') => 'body',
                default => 'hand',
            };
        }
        if ($allowMagic && $type !== 'gold' && $rng->generateRnd(100) < 15 + $dlvl * 3) {
            $item['quality'] = 'magic';
            $item['identified'] = false;
            $pref = self::prefixes();
            $suf = self::suffixes();
            if ($pref !== [] && $rng->flipCoin()) {
                $p = $pref[$rng->generateRnd(count($pref))];
                $item['name'] = trim(($p['name'] ?? '') . ' ' . $item['baseName']);
                self::applyAffixPower($item, $p, $rng);
            } elseif ($suf !== []) {
                $s = $suf[$rng->generateRnd(count($suf))];
                $item['name'] = $item['baseName'] . ' ' . ($s['name'] ?? '');
                self::applyAffixPower($item, $s, $rng);
            }
        }

        return $item;
    }

    /**
     * Roll power.value1..value2 onto PL fields (CalcPlrItemVals / SaveItemPower subset).
     *
     * @param array<string,mixed> $item
     * @param array<string,string> $affix
     */
    private static function applyAffixPower(array &$item, array $affix, Random $rng): void
    {
        $power = strtoupper((string) ($affix['power'] ?? ''));
        if ($power === '') {
            return;
        }
        $item['effects'] = array_values(array_unique(array_merge($item['effects'] ?? [], [$power])));
        $v1 = (int) ($affix['power.value1'] ?? 0);
        $v2 = (int) ($affix['power.value2'] ?? $v1);
        $roll = $v1;
        if ($v2 > $v1) {
            $roll = $v1 + $rng->generateRnd($v2 - $v1 + 1);
        } elseif ($v2 < $v1) {
            // curse ranges still store low..high as positive magnitudes
            $roll = $v2 + $rng->generateRnd($v1 - $v2 + 1);
        }
        $add = static function (array &$it, string $key, int $n): void {
            $it[$key] = (int) ($it[$key] ?? 0) + $n;
        };
        match ($power) {
            'FIRERES' => $add($item, 'plFireRes', $roll),
            'LIGHTRES' => $add($item, 'plLightRes', $roll),
            'MAGICRES' => $add($item, 'plMagicRes', $roll),
            'ALLRES' => (static function () use (&$item, $roll, $add): void {
                $add($item, 'plFireRes', $roll);
                $add($item, 'plLightRes', $roll);
                $add($item, 'plMagicRes', $roll);
            })(),
            'FIREDAM' => (static function () use (&$item, $v1, $v2, $add): void {
                $add($item, 'plMinDam', max(1, $v1));
                $add($item, 'plMaxDam', max(max(1, $v1), $v2));
            })(),
            'LIGHTDAM' => (static function () use (&$item, $v1, $v2, $add): void {
                $add($item, 'plMinDam', max(1, $v1));
                $add($item, 'plMaxDam', max(max(1, $v1), $v2));
            })(),
            'TOHIT', 'TOHIT_DAMP' => (static function () use (&$item, $power, $roll, $v1, $add): void {
                if ($power === 'TOHIT_DAMP') {
                    $add($item, 'plDamPct', $roll);
                    // CalculateToHitBonus(param1) ≈ param1/2-ish; use roll/2 as approx for to-hit half
                    $add($item, 'plToHit', max(1, intdiv($v1, 2)));
                } else {
                    $add($item, 'plToHit', $roll);
                }
            })(),
            'TOHIT_CURSE' => $add($item, 'plToHit', -$roll),
            'TOHIT_DAMP_CURSE' => (static function () use (&$item, $roll, $v1, $add): void {
                $add($item, 'plDamPct', -$roll);
                $add($item, 'plToHit', -max(1, intdiv($v1, 2)));
            })(),
            'ACP' => $add($item, 'plAC', $roll),
            'ACP_CURSE', 'AC_CURSE' => $add($item, 'plAC', -$roll),
            'DAMP' => $add($item, 'plDamPct', $roll),
            'DAMP_CURSE' => $add($item, 'plDamPct', -$roll),
            'DAMMOD' => (static function () use (&$item, $v1, $v2, $add): void {
                $add($item, 'plMinDam', max(1, $v1));
                $add($item, 'plMaxDam', max(max(1, $v1), $v2));
            })(),
            'STR' => $add($item, 'plStr', $roll),
            'STR_CURSE' => $add($item, 'plStr', -$roll),
            'DEX' => $add($item, 'plDex', $roll),
            'DEX_CURSE' => $add($item, 'plDex', -$roll),
            'MAG' => $add($item, 'plMag', $roll),
            'MAG_CURSE' => $add($item, 'plMag', -$roll),
            'VIT' => $add($item, 'plVit', $roll),
            'VIT_CURSE' => $add($item, 'plVit', -$roll),
            'LIFE' => $add($item, 'plHP', $roll),
            'LIFE_CURSE' => $add($item, 'plHP', -$roll),
            'MANA' => $add($item, 'plMana', $roll),
            'MANA_CURSE' => $add($item, 'plMana', -$roll),
            'STEALLIFE' => $add($item, 'plStealLife', max(1, $v1)), // 3 or 5 (%)
            'STEALMANA' => $add($item, 'plStealMana', max(1, $v1)),
            'FIRE_ARROWS' => (static function () use (&$item, $v1, $v2, $add): void {
                $add($item, 'plFMinDam', max(1, $v1));
                $add($item, 'plFMaxDam', max(max(1, $v1), $v2));
            })(),
            'LIGHT_ARROWS' => (static function () use (&$item, $v1, $v2, $add): void {
                $add($item, 'plLMinDam', max(1, $v1));
                $add($item, 'plLMaxDam', max(max(1, $v1), $v2));
            })(),
            'GETHIT' => $add($item, 'plGetHit', $roll),
            'GETHIT_CURSE' => $add($item, 'plGetHit', -$roll),
            'LIGHT' => $add($item, 'plLight', max(1, $v1)),
            'LIGHT_CURSE' => $add($item, 'plLight', -max(1, $v1)),
            'FASTATTACK' => $item['plFastAttack'] = max((int) ($item['plFastAttack'] ?? 0), max(1, $v1)),
            'FASTRECOVER' => $item['plFastRecover'] = max((int) ($item['plFastRecover'] ?? 0), max(1, $v1)),
            'THORNS' => $add($item, 'plThorns', max(1, $roll)),
            'KNOCKBACK' => null, // flag via effects[] only
            'ATTRIBS' => (static function () use (&$item, $roll, $add): void {
                $add($item, 'plStr', $roll);
                $add($item, 'plMag', $roll);
                $add($item, 'plDex', $roll);
                $add($item, 'plVit', $roll);
            })(),
            'ATTRIBS_CURSE' => (static function () use (&$item, $roll, $add): void {
                $add($item, 'plStr', -$roll);
                $add($item, 'plMag', -$roll);
                $add($item, 'plDex', -$roll);
                $add($item, 'plVit', -$roll);
            })(),
            'SPLLVLADD' => $add($item, 'plSplLvlAdd', max(1, $v1)),
            'INDESTRUCTIBLE' => (static function () use (&$item): void {
                $item['durability'] = 255;
                $item['maxDurability'] = 255;
            })(),
            'DUR' => (static function () use (&$item, $roll): void {
                $item['maxDurability'] = max(1, (int) ($item['maxDurability'] ?? 0) + $roll);
                $item['durability'] = min(
                    (int) $item['maxDurability'],
                    (int) ($item['durability'] ?? 0) + $roll
                );
            })(),
            'DUR_CURSE' => (static function () use (&$item, $roll): void {
                $max = max(1, (int) ($item['maxDurability'] ?? 1) - $roll);
                $item['maxDurability'] = $max;
                $item['durability'] = min((int) ($item['durability'] ?? $max), $max);
            })(),
            'SETDAM' => (static function () use (&$item, $v1, $v2): void {
                $item['minDamage'] = max(1, $v1);
                $item['maxDamage'] = max(max(1, $v1), $v2);
            })(),
            'SETAC' => (static function () use (&$item, $roll): void {
                $item['armor'] = max(0, $roll);
            })(),
            'SETDUR' => (static function () use (&$item, $v1): void {
                $d = max(1, $v1);
                $item['durability'] = $d;
                $item['maxDurability'] = $d;
            })(),
            'RNDSTEALLIFE' => $add($item, 'plStealLife', 3),
            'SPELL' => (static function () use (&$item, $v1, $v2): void {
                // IPL_SPELL: param1 = SpellID, param2 = charges
                $item['spell'] = self::spellIdToName($v1);
                $ch = max(1, $v2 > 0 ? $v2 : $v1);
                $item['charges'] = $ch;
                $item['maxCharges'] = $ch;
            })(),
            'NOMINSTR' => null, // ignore str req — not enforced yet
            'NOMANA' => null, // flag via effects[] — CalcPlrItemVals zeroes mana
            'TARGAC' => $add($item, 'plEnAc', max(1, $roll)),
            'FASTBLOCK', 'ABSHALFTRAP',
            'CHARGES' => null,
            default => (static function () use (&$item, $add): void {
                // Unknown power — keep a tiny placeholder so magic gear isn't blank
                if (!isset($item['plMinDam']) && !isset($item['plAC']) && !isset($item['plFireRes'])) {
                    $add($item, 'plMinDam', 1);
                    $add($item, 'plMaxDam', 2);
                }
            })(),
        };
    }

    /** Map DevilutionX SpellID ordinal → staff spell name used by GameState. */
    private static function spellIdToName(int $id): string
    {
        // SpellID enum order (spelldat.h) — subset we can cast
        return match ($id) {
            1 => 'Firebolt',
            2 => 'Healing',
            3 => 'Lightning',
            4 => 'Flash',
            8 => 'StoneCurse',
            9 => 'Infravision',
            10 => 'Phasing',
            11 => 'ManaShield',
            12 => 'Fireball',
            18 => 'Nova',
            20 => 'Inferno',
            23 => 'Teleport',
            24 => 'Apocalypse',
            30 => 'ChargedBolt',
            46 => 'Search',
            default => 'Firebolt',
        };
    }

    /**
     * Classify usable misc rows (potions / scrolls / books) — not all usable ⇒ heal potion.
     * @param array<string,string> $row
     */
    private static function miscItemType(array $row): string
    {
        if (($row['usable'] ?? '') !== 'true') {
            return 'misc';
        }
        $misc = strtoupper((string) ($row['miscId'] ?? ''));
        $cg = strtoupper((string) ($row['cursorGraphic'] ?? ''));
        $name = strtolower((string) ($row['name'] ?? ''));

        return match (true) {
            in_array($misc, ['HEAL', 'FULLHEAL'], true) || str_contains($cg, 'HEALING') => 'potion_heal',
            in_array($misc, ['MANA', 'FULLMANA'], true) || str_contains($cg, 'MANA') => 'potion_mana',
            in_array($misc, ['REJUV', 'FULLREJUV'], true) || str_contains($name, 'rejuven') => 'potion_rejuv',
            in_array($misc, ['SCROLL', 'SCROLLT'], true) || str_starts_with($cg, 'SCROLL') => 'scroll',
            $misc === 'BOOK' || str_contains($name, 'book of') => 'book',
            str_starts_with($misc, 'OIL') || $cg === 'OIL' || str_contains($name, 'oil') => 'oil',
            default => 'misc',
        };
    }

    /**
     * itemdat.h item_cursor_graphic → objcurs frame = value + 11.
     * Early common graphics only (ground drop art).
     */
    public static function cursorGraphicFrame(string $name): int
    {
        static $map = [
            'POTION_OF_FULL_MANA' => 0,
            'SCROLL_OF' => 1,
            'GOLD_SMALL' => 4,
            'GOLD_MEDIUM' => 5,
            'GOLD_LARGE' => 6,
            'RING' => 12,
            'POTION_OF_HEALING' => 32,
            'POTION_OF_MANA' => 39,
            'SHORT_SWORD' => 51,
            'SWORD' => 52,
            'DAGGER' => 53,
            'MACE' => 54,
            'CLUB' => 55,
            'AXE' => 56,
            'BOW' => 60,
            'LONG_BOW' => 61,
            'HUNTERS_BOW' => 62,
            'STAFF' => 70,
            'SMALL_SHIELD' => 75,
            'BUCKLER' => 76,
            'CAP' => 80,
            'SKULL_CAP' => 81,
            'CROWN' => 85,
            'LEATHER_ARMOR' => 90,
            'HARD_LEATHER_ARMOR' => 91,
            'CHAIN_MAIL' => 95,
            'BREAST_PLATE' => 100,
            'PLATE_MAIL' => 105,
        ];
        $key = strtoupper(str_replace([' ', "'"], ['_', ''], $name));
        if ($key === '' || $key === 'NONE') {
            return 4; // gold small fallback
        }
        if (isset($map[$key])) {
            return $map[$key] + 11;
        }
        // Heuristic by substring
        foreach ([['GOLD', 4], ['POTION', 32], ['BOW', 60], ['SWORD', 52], ['SHIELD', 75], ['HELM', 80], ['ARMOR', 90], ['RING', 12], ['STAFF', 70], ['AXE', 56]] as [$needle, $id]) {
            if (str_contains($key, $needle)) {
                return $id + 11;
            }
        }

        return 4 + 11;
    }

    /** @param array<string,string> $row */
    private static function footprintW(array $row): int
    {
        $t = strtolower($row['itemType'] ?? '');
        if (str_contains($t, 'bow') || str_contains($t, 'staff') || str_contains($t, 'axe')) {
            return 2;
        }

        return 1;
    }

    /** @param array<string,string> $row */
    private static function footprintH(array $row): int
    {
        $t = strtolower($row['itemType'] ?? '');
        $c = strtolower($row['class'] ?? '');
        if (str_contains($t, 'armor') || $c === 'armor' && !str_contains($t, 'shield') && !str_contains($t, 'helm')) {
            return 3;
        }
        if (str_contains($t, 'sword') || str_contains($t, 'mace') || str_contains($t, 'axe') || str_contains($t, 'staff') || str_contains($t, 'bow')) {
            return 3;
        }
        if (str_contains($t, 'helm') || str_contains($t, 'shield')) {
            return 2;
        }

        return 1;
    }
}
