<?php

declare(strict_types=1);

namespace Quake3\Game;

use Quake3\Bsp\BspIdent;
use Quake3\Bsp\BspMap;

/**
 * Parse LUMP_ENTITIES like G_ParseSpawnVars / COM_Parse (brace key/value entities).
 */
final class EntityParser
{
    /**
     * @return list<array<string, string>>
     */
    public function parseEntities(string $entityString): array
    {
        $entities = [];
        $pos = 0;
        $len = strlen($entityString);

        while (true) {
            $tok = $this->parseToken($entityString, $pos, $len);
            if ($tok === null) {
                break;
            }
            if ($tok !== '{') {
                throw new \RuntimeException('EntityParser: expected {, got ' . $tok);
            }

            $kv = [];
            while (true) {
                $key = $this->parseToken($entityString, $pos, $len);
                if ($key === null) {
                    throw new \RuntimeException('EntityParser: EOF without closing brace');
                }
                if ($key === '}') {
                    break;
                }
                $value = $this->parseToken($entityString, $pos, $len);
                if ($value === null || $value === '}') {
                    throw new \RuntimeException('EntityParser: closing brace without data');
                }
                $kv[$key] = $value;
            }
            $entities[] = $kv;
        }

        return $entities;
    }

    /**
     * @return list<SpawnPoint>
     */
    public function collectDeathmatchSpawns(BspMap $map): array
    {
        $entities = $this->parseEntities($map->readLumpBytes(BspIdent::LUMP_ENTITIES));
        $spawns = [];
        $i = 0;
        foreach ($entities as $ent) {
            $classname = $ent['classname'] ?? '';
            // SP_info_player_start aliases to deathmatch (g_client.c).
            if ($classname === 'info_player_start') {
                $classname = 'info_player_deathmatch';
            }
            if ($classname !== 'info_player_deathmatch') {
                continue;
            }
            $origin = $this->parseVec3($ent['origin'] ?? '0 0 0');
            $angles = [0.0, 0.0, 0.0];
            if (isset($ent['angles'])) {
                $angles = $this->parseVec3($ent['angles']);
            } elseif (isset($ent['angle'])) {
                // F_ANGLEHACK → yaw only
                $angles[1] = (float) $ent['angle'];
            }
            $spawns[] = new SpawnPoint(
                $classname,
                $origin,
                $angles,
                $i,
                (int) ($ent['spawnflags'] ?? 0),
            );
            $i++;
        }

        return $spawns;
    }

    /**
     * @return list<float>
     */
    private function parseVec3(string $s): array
    {
        $parts = preg_split('/\s+/', trim($s)) ?: [];
        return [
            (float) ($parts[0] ?? 0),
            (float) ($parts[1] ?? 0),
            (float) ($parts[2] ?? 0),
        ];
    }

    private function parseToken(string $data, int &$pos, int $len): ?string
    {
        while ($pos < $len) {
            $c = $data[$pos];

            // C entity string is often NUL-terminated (cm_load memcpy of filelen)
            if ($c === "\0") {
                return null;
            }

            // skip whitespace
            if ($c === ' ' || $c === "\t" || $c === "\r" || $c === "\n") {
                $pos++;
                continue;
            }

            // // comments
            if ($c === '/' && $pos + 1 < $len && $data[$pos + 1] === '/') {
                $pos += 2;
                while ($pos < $len && $data[$pos] !== "\n") {
                    $pos++;
                }
                continue;
            }

            // quoted string
            if ($c === '"') {
                $pos++;
                $start = $pos;
                while ($pos < $len && $data[$pos] !== '"') {
                    $pos++;
                }
                $token = substr($data, $start, $pos - $start);
                if ($pos < $len) {
                    $pos++; // closing quote
                }
                return $token;
            }

            // single-char braces
            if ($c === '{' || $c === '}') {
                $pos++;
                return $c;
            }

            // regular token
            $start = $pos;
            while ($pos < $len) {
                $c = $data[$pos];
                if ($c === ' ' || $c === "\t" || $c === "\r" || $c === "\n" || $c === '"') {
                    break;
                }
                $pos++;
            }
            return substr($data, $start, $pos - $start);
        }

        return null;
    }
}
