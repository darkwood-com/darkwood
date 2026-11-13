<?php

declare(strict_types=1);

namespace Quake3\Render;

use Quake3\Filesystem\FileSystem;

/**
 * MD3 loader (md3Header_t / md3Surface_t / md3XyzNormal_t / md3Tag_t).
 * Tags: numFrames × numTags blocks of 112 bytes (frame-major).
 */
final class Md3Model
{
    public const IDENT = 'IDP3';
    public const VERSION = 15;
    public const XYZ_SCALE = 1.0 / 64.0;
    public const TAG_SIZE = 112;

    public string $name = '';
    public string $path = '';
    public int $numFrames = 0;
    public int $numTags = 0;
    public int $numSurfaces = 0;

    /**
     * @var list<array{
     *   name: string,
     *   shader: string,
     *   verts: list<list<float>>,
     *   normals: list<list<float>>,
     *   st: list<list<float>>,
     *   indices: list<int>,
     *   frames: list<list<list<float>>>
     * }>
     */
    public array $surfaces = [];

    /**
     * Frame-major tags: [frame][tagIndex] = {name, origin, axis}.
     * @var list<list<array{name: string, origin: list<float>, axis: list<list<float>>}>>
     */
    public array $frameTags = [];

    /** Frame-0 tags (convenience). @var list<array{name: string, origin: list<float>, axis: list<list<float>>}> */
    public array $tags = [];

    public static function load(FileSystem $fs, string $path): self
    {
        $m = self::parse($fs->read($path), $path);
        $m->path = $path;
        return $m;
    }

    public static function parse(string $bytes, string $path = ''): self
    {
        if (strlen($bytes) < 108) {
            throw new \RuntimeException('MD3 too short: ' . $path);
        }
        if (substr($bytes, 0, 4) !== self::IDENT) {
            throw new \RuntimeException('Bad MD3 ident: ' . $path);
        }
        $version = unpack('V', substr($bytes, 4, 4))[1];
        if ($version !== self::VERSION) {
            throw new \RuntimeException("MD3 version {$version}: {$path}");
        }
        $m = new self();
        $m->name = self::cString(substr($bytes, 8, 64));
        $m->path = $path;
        $m->numFrames = unpack('V', substr($bytes, 76, 4))[1];
        $m->numTags = unpack('V', substr($bytes, 80, 4))[1];
        $m->numSurfaces = unpack('V', substr($bytes, 84, 4))[1];
        $ofsTags = unpack('V', substr($bytes, 96, 4))[1];
        $ofsSurfaces = unpack('V', substr($bytes, 100, 4))[1];

        for ($f = 0; $f < $m->numFrames; $f++) {
            $frameTagList = [];
            for ($t = 0; $t < $m->numTags; $t++) {
                $o = $ofsTags + ($f * $m->numTags + $t) * self::TAG_SIZE;
                if ($o + self::TAG_SIZE > strlen($bytes)) {
                    break 2;
                }
                $frameTagList[] = self::readTag($bytes, $o);
            }
            $m->frameTags[] = $frameTagList;
        }
        $m->tags = $m->frameTags[0] ?? [];

        $surfOff = $ofsSurfaces;
        for ($s = 0; $s < $m->numSurfaces; $s++) {
            if ($surfOff + 108 > strlen($bytes)) {
                break;
            }
            $base = $surfOff;
            $name = self::cString(substr($bytes, $base + 4, 64));
            $numFrames = unpack('V', substr($bytes, $base + 72, 4))[1];
            $numShaders = unpack('V', substr($bytes, $base + 76, 4))[1];
            $numVerts = unpack('V', substr($bytes, $base + 80, 4))[1];
            $numTris = unpack('V', substr($bytes, $base + 84, 4))[1];
            $ofsTris = unpack('V', substr($bytes, $base + 88, 4))[1];
            $ofsShaders = unpack('V', substr($bytes, $base + 92, 4))[1];
            $ofsSt = unpack('V', substr($bytes, $base + 96, 4))[1];
            $ofsXyz = unpack('V', substr($bytes, $base + 100, 4))[1];
            $ofsEnd = unpack('V', substr($bytes, $base + 104, 4))[1];

            $shader = $name;
            if ($numShaders > 0 && $base + $ofsShaders + 64 <= strlen($bytes)) {
                $shader = self::cString(substr($bytes, $base + $ofsShaders, 64));
            }

            $st = [];
            for ($i = 0; $i < $numVerts; $i++) {
                $o = $base + $ofsSt + $i * 8;
                $uv = unpack('g2', substr($bytes, $o, 8));
                $st[] = [$uv[1], $uv[2]];
            }

            $frames = [];
            $normals0 = [];
            for ($f = 0; $f < $numFrames; $f++) {
                $verts = [];
                $normals = [];
                for ($i = 0; $i < $numVerts; $i++) {
                    $o = $base + $ofsXyz + ($f * $numVerts + $i) * 8;
                    $xyz = unpack('s3', substr($bytes, $o, 6));
                    $latlng = unpack('v', substr($bytes, $o + 6, 2))[1];
                    $verts[] = [
                        $xyz[1] * self::XYZ_SCALE,
                        $xyz[2] * self::XYZ_SCALE,
                        $xyz[3] * self::XYZ_SCALE,
                    ];
                    $normals[] = self::decodeNormal($latlng);
                }
                $frames[] = $verts;
                if ($f === 0) {
                    $normals0 = $normals;
                }
            }

            $indices = [];
            for ($t = 0; $t < $numTris; $t++) {
                $o = $base + $ofsTris + $t * 12;
                $tri = unpack('V3', substr($bytes, $o, 12));
                $indices[] = $tri[1];
                $indices[] = $tri[2];
                $indices[] = $tri[3];
            }

            $m->surfaces[] = [
                'name' => $name,
                'shader' => $shader,
                'verts' => $frames[0] ?? [],
                'normals' => $normals0,
                'st' => $st,
                'indices' => $indices,
                'frames' => $frames,
            ];
            $surfOff = $base + $ofsEnd;
        }

        return $m;
    }

    /** @return array{name: string, origin: list<float>, axis: list<list<float>>} */
    private static function readTag(string $bytes, int $o): array
    {
        $name = self::cString(substr($bytes, $o, 64));
        $origin = [
            unpack('g', substr($bytes, $o + 64, 4))[1],
            unpack('g', substr($bytes, $o + 68, 4))[1],
            unpack('g', substr($bytes, $o + 72, 4))[1],
        ];
        $axis = [];
        for ($a = 0; $a < 3; $a++) {
            $axis[] = [
                unpack('g', substr($bytes, $o + 76 + $a * 12, 4))[1],
                unpack('g', substr($bytes, $o + 80 + $a * 12, 4))[1],
                unpack('g', substr($bytes, $o + 84 + $a * 12, 4))[1],
            ];
        }
        return ['name' => $name, 'origin' => $origin, 'axis' => $axis];
    }

    private static function cString(string $raw): string
    {
        $nul = strpos($raw, "\0");
        if ($nul === false) {
            return $raw;
        }
        return substr($raw, 0, $nul);
    }

    /** @return list<float> */
    public static function decodeNormal(int $latlng): array
    {
        $lat = ($latlng >> 8) & 0xff;
        $lng = $latlng & 0xff;
        $lat *= M_PI / 128.0;
        $lng *= M_PI / 128.0;
        return [
            cos($lat) * sin($lng),
            sin($lat) * sin($lng),
            cos($lng),
        ];
    }

    /** @return list<list<float>> */
    public function frameVerts(int $surfaceIndex, int $frame): array
    {
        $surf = $this->surfaces[$surfaceIndex];
        $frames = $surf['frames'];
        if ($frames === []) {
            return $surf['verts'];
        }
        $frame = max(0, min(count($frames) - 1, $frame));
        return $frames[$frame];
    }

    public function findTag(string $name, int $frame = 0): ?array
    {
        $frame = max(0, min(count($this->frameTags) - 1, $frame));
        foreach ($this->frameTags[$frame] ?? [] as $t) {
            if ($t['name'] === $name) {
                return $t;
            }
        }
        return null;
    }

    /**
     * Axis-aligned bounds for a surface frame (or all surfaces).
     * @return array{mins: list<float>, maxs: list<float>, size: list<float>}
     */
    public function bounds(int $frame = 0): array
    {
        $mins = [INF, INF, INF];
        $maxs = [-INF, -INF, -INF];
        $any = false;
        foreach ($this->surfaces as $si => $surf) {
            $verts = $this->frameVerts($si, $frame);
            foreach ($verts as $v) {
                $any = true;
                for ($i = 0; $i < 3; $i++) {
                    $mins[$i] = min($mins[$i], $v[$i]);
                    $maxs[$i] = max($maxs[$i], $v[$i]);
                }
            }
        }
        if (!$any) {
            return ['mins' => [0.0, 0.0, 0.0], 'maxs' => [0.0, 0.0, 0.0], 'size' => [0.0, 0.0, 0.0]];
        }
        return [
            'mins' => $mins,
            'maxs' => $maxs,
            'size' => [$maxs[0] - $mins[0], $maxs[1] - $mins[1], $maxs[2] - $mins[2]],
        ];
    }

    public function triangleVertexCount(): int
    {
        $n = 0;
        foreach ($this->surfaces as $s) {
            $n += count($s['indices']);
        }
        return $n;
    }
}
