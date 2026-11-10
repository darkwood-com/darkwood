<?php

declare(strict_types=1);

namespace Quake3\Render;

use Quake3\Filesystem\FileSystem;

/**
 * BSP shader name → base image path (R_FindShader default + .shader map/clampMap).
 */
final class MaterialResolver
{
    /** @var array<string, string>|null shaderName => first map image */
    private ?array $shaderMaps = null;

    /** @var array<string, true> */
    private array $unknownDirectives = [];

    private ?ShaderParser $parser = null;

    public function __construct(
        private readonly FileSystem $fs,
        private readonly ImageLoader $images = new ImageLoader(),
    ) {
    }

    /**
     * Resolve to a DecodedImage or null if missing.
     */
    public function resolveImage(string $shaderName): ?DecodedImage
    {
        $path = $this->resolvePath($shaderName);
        if ($path === null) {
            return null;
        }
        try {
            $bytes = $this->fs->read($path);
            return $this->images->loadFromBytes($bytes, $path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function resolvePath(string $shaderName): ?string
    {
        $name = $this->stripExtension($shaderName);
        // Prefer ShaderParser first map when available
        if ($this->parser !== null) {
            $sh = $this->parser->get($name);
            foreach ($sh->stages as $st) {
                if ($st->isLightmap || $st->map === '' || str_starts_with($st->map, '$')) {
                    continue;
                }
                $found = $this->findImageFile($st->map);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        $this->ensureShaderIndex();

        if (isset($this->shaderMaps[$name])) {
            $img = $this->shaderMaps[$name];
            $found = $this->findImageFile($img);
            if ($found !== null) {
                return $found;
            }
        }

        return $this->findImageFile($name);
    }

    /**
     * Classify a stage map for shader:missing audit.
     * @return array{status: string, resolved: ?string}
     */
    public function classifyMap(string $map): array
    {
        $map = trim($map);
        if ($map === '') {
            return ['status' => 'MISSING', 'resolved' => null];
        }
        $lower = strtolower($map);
        if ($lower === '$lightmap') {
            return ['status' => 'BUILTIN_LIGHTMAP', 'resolved' => '$lightmap'];
        }
        if ($lower === '$whiteimage') {
            return ['status' => 'BUILTIN_WHITEIMAGE', 'resolved' => '$whiteimage'];
        }
        if (str_starts_with($lower, '$')) {
            return ['status' => 'BUILTIN_LIGHTMAP', 'resolved' => $map];
        }
        $found = $this->findImageFile($map);
        if ($found === null) {
            return ['status' => 'MISSING', 'resolved' => null];
        }
        $ext = strtolower(pathinfo($found, PATHINFO_EXTENSION));
        $status = match ($ext) {
            'tga' => 'FOUND_TGA',
            'jpg', 'jpeg' => 'FOUND_JPG',
            default => 'FOUND_SHADER',
        };

        return ['status' => $status, 'resolved' => $found];
    }

    /**
     * R_FindImageFile — stage map/clampMap/animMap path only (never shader first-map).
     * Critical when map path equals a shader name (e.g. glass_frame → must load .tga, not tinfx2).
     */
    public function resolveStageImage(string $map): ?DecodedImage
    {
        if ($map === '' || str_starts_with($map, '$')) {
            return null;
        }
        $path = $this->findImageFile($map);
        if ($path === null) {
            return null;
        }
        try {
            $bytes = $this->fs->read($path);

            return $this->images->loadFromBytes($bytes, $path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setParser(ShaderParser $parser): void
    {
        $this->parser = $parser;
    }

    /** Public image path probe (tga/jpg/jpeg). */
    public function findImageFilePublic(string $base): ?string
    {
        return $this->findImageFile($base);
    }

    /** @return list<string> */
    public function unknownDirectiveNames(): array
    {
        return array_keys($this->unknownDirectives);
    }

    private function stripExtension(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        foreach (['.tga', '.jpg', '.jpeg', '.png'] as $ext) {
            if (str_ends_with(strtolower($name), $ext)) {
                return substr($name, 0, -strlen($ext));
            }
        }
        return $name;
    }

    private function findImageFile(string $base): ?string
    {
        $base = $this->stripExtension($base);
        foreach ([$base . '.tga', $base . '.jpg', $base . '.jpeg'] as $cand) {
            if ($this->fs->exists($cand)) {
                return $cand;
            }
        }
        return null;
    }

    private function ensureShaderIndex(): void
    {
        if ($this->shaderMaps !== null) {
            return;
        }
        $this->shaderMaps = [];
        foreach ($this->fs->getPacks() as $pak) {
            foreach ($pak->listEntries() as $e) {
                $n = $e->name;
                if (!str_starts_with($n, 'scripts/') || !str_ends_with($n, '.shader')) {
                    continue;
                }
                try {
                    $text = $pak->read($n);
                } catch (\Throwable) {
                    continue;
                }
                $this->parseShaderFile($text);
            }
        }
    }

    private function parseShaderFile(string $text): void
    {
        // Strip // comments
        $text = preg_replace('/\/\/[^\n]*/', '', $text) ?? $text;
        $len = strlen($text);
        $i = 0;
        while ($i < $len) {
            while ($i < $len && ctype_space($text[$i])) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }
            // shader name
            $start = $i;
            while ($i < $len && !ctype_space($text[$i]) && $text[$i] !== '{') {
                $i++;
            }
            $shaderName = $this->stripExtension(substr($text, $start, $i - $start));
            while ($i < $len && ctype_space($text[$i])) {
                $i++;
            }
            if ($i >= $len || $text[$i] !== '{') {
                continue;
            }
            $i++;
            $depth = 1;
            $bodyStart = $i;
            while ($i < $len && $depth > 0) {
                if ($text[$i] === '{') {
                    $depth++;
                } elseif ($text[$i] === '}') {
                    $depth--;
                }
                $i++;
            }
            $body = substr($text, $bodyStart, $i - $bodyStart - 1);
            if (!isset($this->shaderMaps[$shaderName])) {
                $map = $this->extractFirstMap($body);
                if ($map !== null) {
                    $this->shaderMaps[$shaderName] = $map;
                }
            }
        }
    }

    private function extractFirstMap(string $body): ?string
    {
        if (!preg_match_all('/\b(map|clampmap|animMap)\b/i', $body, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        foreach ($m[0] as $idx => $match) {
            $pos = $match[1] + strlen($match[0]);
            $rest = substr($body, $pos);
            if (!preg_match('/^\s+(\S+)/', $rest, $mm)) {
                continue;
            }
            $path = $mm[1];
            $lower = strtolower($path);
            if ($lower === '$lightmap' || $lower === '$whiteimage' || str_starts_with($lower, '$')) {
                continue;
            }
            return $this->stripExtension($path);
        }

        // log unknown interesting directives once (subset awareness)
        if (preg_match_all('/^\s*([a-zA-Z]+)/m', $body, $dirs)) {
            $known = ['map', 'clampmap', 'animmap', 'blendfunc', 'rgbgen', 'alphagen',
                'tcgen', 'tcmod', 'cull', 'surfaceparm', 'deformvertexes', 'fogparms',
                'skyparms', 'nopicmip', 'nomipmaps', 'polygonoffset', 'sort', 'qer_editorimage',
                'q3map_', 'tesssize', 'qer_trans', 'light', 'cull'];
            foreach ($dirs[1] as $d) {
                $dl = strtolower($d);
                $ok = false;
                foreach ($known as $k) {
                    if ($dl === $k || str_starts_with($dl, 'q3map_') || str_starts_with($dl, 'qer_')) {
                        $ok = true;
                        break;
                    }
                }
                if (!$ok) {
                    $this->unknownDirectives[$dl] = true;
                }
            }
        }

        return null;
    }
}
