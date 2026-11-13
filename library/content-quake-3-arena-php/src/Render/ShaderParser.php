<?php

declare(strict_types=1);

namespace Quake3\Render;

use Quake3\Filesystem\FileSystem;

/**
 * Parse scripts/*.shader into Q3Shader (q3dm0 subset).
 */
final class ShaderParser
{
    // GL constants for blend
    public const GL_ZERO = 0;
    public const GL_ONE = 1;
    public const GL_SRC_COLOR = 0x0300;
    public const GL_ONE_MINUS_SRC_COLOR = 0x0301;
    public const GL_SRC_ALPHA = 0x0302;
    public const GL_ONE_MINUS_SRC_ALPHA = 0x0303;
    public const GL_DST_ALPHA = 0x0304;
    public const GL_ONE_MINUS_DST_ALPHA = 0x0305;
    public const GL_DST_COLOR = 0x0306;
    public const GL_ONE_MINUS_DST_COLOR = 0x0307;

    /** @var array<string, Q3Shader> */
    private array $cache = [];

    /** @var array<string, true> names defined in scripts/*.shader */
    private array $scripted = [];

    /** @var array<string, int> */
    private array $directiveHits = [];

    /** @var array<string, true> */
    private array $unknown = [];

    private bool $loaded = false;

    public function __construct(private readonly FileSystem $fs)
    {
    }

    public function get(string $name): Q3Shader
    {
        $this->ensureLoaded();
        $key = $this->stripExt($name);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        // R_FindShader two-pass lightmap (tr_shader.c): $lightmap GLS_DEFAULT, then
        // diffuse with DST_COLOR/ZERO. FinishShader sets TCGEN_LIGHTMAP + depthMask.
        $lm = new ShaderStage();
        $lm->isLightmap = true;
        $lm->map = '$lightmap';
        $lm->rgbGen = 'identity';
        $lm->tcGen = 'lightmap';
        $lm->depthWrite = true;
        $diff = new ShaderStage();
        $diff->map = $key;
        $diff->hasBlend = true;
        $diff->blendSrc = self::GL_DST_COLOR;
        $diff->blendDst = self::GL_ZERO;
        $diff->rgbGen = 'identity';
        $shader = new Q3Shader($key, [$lm, $diff], opaque: true);
        $this->cache[$key] = $shader;
        return $shader;
    }

    /** True if scripts/*.shader defined this name (not a synthesized default). */
    public function isScripted(string $name): bool
    {
        $this->ensureLoaded();
        $key = $this->stripExt($name);

        return isset($this->scripted[$key]);
    }

    /** @return array<string, Q3Shader> */
    public function all(): array
    {
        $this->ensureLoaded();
        return $this->cache;
    }

    /** @return array<string, int> */
    public function directiveHits(): array
    {
        $this->ensureLoaded();
        arsort($this->directiveHits);
        return $this->directiveHits;
    }

    /** @return list<string> */
    public function unknownDirectives(): array
    {
        $this->ensureLoaded();
        return array_keys($this->unknown);
    }

    private function ensureLoaded(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;
        foreach ($this->fs->getPacks() as $pak) {
            foreach ($pak->listEntries() as $e) {
                if (!str_starts_with($e->name, 'scripts/') || !str_ends_with($e->name, '.shader')) {
                    continue;
                }
                try {
                    $this->parseFile($pak->read($e->name));
                } catch (\Throwable) {
                    continue;
                }
            }
        }
    }

    private function parseFile(string $text): void
    {
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
            $start = $i;
            while ($i < $len && !ctype_space($text[$i]) && $text[$i] !== '{') {
                $i++;
            }
            $name = $this->stripExt(substr($text, $start, $i - $start));
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
            if (!isset($this->cache[$name])) {
                $this->cache[$name] = $this->parseBody($name, $body);
                $this->scripted[$name] = true;
            }
        }
    }

    private function parseBody(string $name, string $body): Q3Shader
    {
        $stages = [];
        $surfaceparms = [];
        $sky = false;
        $nonsolid = false;
        $nolightmap = false;
        $cull = 'front';
        $cloudHeight = 512;
        $skyBox = null;
        $opaque = true;
        $deforms = [];
        $sort = 0.0; // 0 = unset (FinishShader fills)
        $polygonOffset = false;
        $sortExplicit = false;

        // split top-level tokens and brace stages
        $tokens = $this->tokenize($body);
        $n = count($tokens);
        $t = 0;
        while ($t < $n) {
            $tok = strtolower($tokens[$t]);
            if ($tok === '{') {
                $t++;
                $stageTokens = [];
                $depth = 1;
                while ($t < $n && $depth > 0) {
                    if ($tokens[$t] === '{') {
                        $depth++;
                    } elseif ($tokens[$t] === '}') {
                        $depth--;
                        if ($depth === 0) {
                            $t++;
                            break;
                        }
                    }
                    if ($depth > 0) {
                        $stageTokens[] = $tokens[$t];
                    }
                    $t++;
                }
                $stage = $this->parseStage($stageTokens);
                $stages[] = $stage;
                if ($stage->hasBlend && $this->stageIsTranslucent($stage)) {
                    $opaque = false;
                }
                continue;
            }

            $this->hit($tok);
            if ($tok === 'surfaceparm' && $t + 1 < $n) {
                $sp = strtolower($tokens[++$t]);
                $surfaceparms[$sp] = true;
                if ($sp === 'sky') {
                    $sky = true;
                }
                if ($sp === 'nonsolid') {
                    $nonsolid = true;
                }
                if ($sp === 'nolightmap') {
                    $nolightmap = true;
                }
            } elseif ($tok === 'cull' && $t + 1 < $n) {
                $cull = strtolower($tokens[++$t]);
                if ($cull === 'disable' || $cull === 'none' || $cull === 'twosided') {
                    $cull = 'none';
                }
            } elseif ($tok === 'deformvertexes' && $t + 1 < $n) {
                $kind = strtolower($tokens[++$t]);
                if ($kind === 'autosprite' || $kind === 'autosprite2') {
                    $deforms[] = ['type' => $kind];
                } elseif ($kind === 'wave' && $t + 5 < $n) {
                    // deformVertexes wave <div> <func> <base> <amp> <phase> <freq>
                    $div = (float) $tokens[++$t];
                    $func = strtolower($tokens[++$t]);
                    $base = (float) $tokens[++$t];
                    $amp = (float) $tokens[++$t];
                    $phase = (float) $tokens[++$t];
                    $freq = (float) $tokens[++$t];
                    $deforms[] = [
                        'type' => 'wave',
                        'spread' => $div != 0.0 ? 1.0 / $div : 100.0,
                        'func' => $func,
                        'base' => $base,
                        'amp' => $amp,
                        'phase' => $phase,
                        'freq' => $freq,
                    ];
                } else {
                    while ($t + 1 < $n && $tokens[$t + 1] !== '{' && !in_array(strtolower($tokens[$t + 1]), ['surfaceparm', 'cull', 'skyparms', 'deformvertexes', 'fogparms', 'sort', 'nopicmip', 'nomipmaps', 'polygonoffset', 'portal'], true)) {
                        $t++;
                    }
                }
            } elseif ($tok === 'portal') {
                // tr_shader.c: portal → SS_PORTAL
                $sortExplicit = true;
                $sort = Q3Shader::SS_PORTAL;
            } elseif ($tok === 'sort' && $t + 1 < $n) {
                $sortExplicit = true;
                $sv = strtolower($tokens[++$t]);
                $sort = match ($sv) {
                    'portal' => Q3Shader::SS_PORTAL,
                    'sky' => Q3Shader::SS_ENVIRONMENT,
                    'opaque' => Q3Shader::SS_OPAQUE,
                    'decal' => Q3Shader::SS_DECAL,
                    'seethrough' => Q3Shader::SS_SEE_THROUGH,
                    'banner' => Q3Shader::SS_BANNER,
                    'additive' => Q3Shader::SS_BLEND1,
                    'nearest' => Q3Shader::SS_NEAREST,
                    'underwater' => Q3Shader::SS_UNDERWATER,
                    default => is_numeric($sv) ? (float) $sv : Q3Shader::SS_OPAQUE,
                };
            } elseif ($tok === 'polygonoffset') {
                $polygonOffset = true;
            } elseif ($tok === 'nopicmip' || $tok === 'nomipmaps') {
                // recognized no-op for audit
            } elseif ($tok === 'skyparms' && $t + 3 < $n) {
                $farbox = $tokens[$t + 1];
                $ch = $tokens[$t + 2];
                $t += 3; // farbox cloudheight nearbox
                if ($farbox !== '-' && $farbox !== '') {
                    $skyBox = $farbox;
                }
                if (is_numeric($ch)) {
                    $cloudHeight = (int) $ch;
                }
                $sky = true;
            } elseif (str_starts_with($tok, 'q3map_') || str_starts_with($tok, 'qer_')) {
                // skip rest of line-ish: consume until known or brace
                while ($t + 1 < $n && $tokens[$t + 1] !== '{' && !in_array(strtolower($tokens[$t + 1]), ['surfaceparm', 'cull', 'skyparms'], true)) {
                    $next = strtolower($tokens[$t + 1]);
                    if (preg_match('/^[a-z_]+$/i', $tokens[$t + 1]) && !is_numeric($tokens[$t + 1]) && !str_contains($tokens[$t + 1], '/') && !str_starts_with($tokens[$t + 1], '$')) {
                        if (in_array($next, ['surfaceparm', 'cull', 'skyparms', 'deformvertexes', 'fogparms', 'sort', 'nopicmip', 'nomipmaps', 'polygonoffset'], true)) {
                            break;
                        }
                    }
                    $t++;
                    if ($t + 1 >= $n) {
                        break;
                    }
                }
            } else {
                // unknown global
                if (!in_array($tok, ['{', '}'], true)) {
                    $this->unknown[$tok] = true;
                }
            }
            $t++;
        }

        if (!$sortExplicit) {
            $sort = $this->finishSort($sky, $polygonOffset, $stages);
        }

        $portalRange = 256.0;
        foreach ($stages as $st) {
            if ($st->alphaGen === 'portal') {
                $portalRange = $st->portalRange > 0 ? $st->portalRange : 256.0;
                break;
            }
        }

        return new Q3Shader(
            name: $name,
            stages: $stages,
            sky: $sky,
            nonsolid: $nonsolid,
            nolightmap: $nolightmap,
            cull: $cull,
            skyBox: $skyBox,
            cloudHeight: $cloudHeight,
            surfaceparms: $surfaceparms,
            opaque: $opaque && !$sky,
            deforms: $deforms,
            sort: $sort,
            polygonOffset: $polygonOffset,
            portalRange: $portalRange,
        );
    }

    /**
     * FinishShader sort defaults (tr_shader.c) — subset.
     * @param list<ShaderStage> $stages
     */
    private function finishSort(bool $sky, bool $polygonOffset, array $stages): float
    {
        if ($sky) {
            return Q3Shader::SS_ENVIRONMENT;
        }
        if ($polygonOffset) {
            return Q3Shader::SS_DECAL;
        }
        $stage0Blend = isset($stages[0]) && $stages[0]->hasBlend;
        foreach ($stages as $st) {
            if (!$st->hasBlend || !$stage0Blend) {
                continue;
            }
            // both stage0 and this stage blended → translucent sort
            if ($st->depthWrite) {
                return Q3Shader::SS_SEE_THROUGH;
            }
            return Q3Shader::SS_BLEND0;
        }
        return Q3Shader::SS_OPAQUE;
    }

    /** @param list<string> $tokens */
    private function parseStage(array $tokens): ShaderStage
    {
        $s = new ShaderStage();
        $rgbGenExplicit = false;
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $tok = strtolower($tokens[$i]);
            $this->hit($tok);
            if (($tok === 'map' || $tok === 'clampmap') && $i + 1 < $n) {
                $s->clamp = $tok === 'clampmap';
                $map = $tokens[++$i];
                if (strtolower($map) === '$lightmap') {
                    $s->isLightmap = true;
                    $s->map = '$lightmap';
                } elseif (str_starts_with($map, '$')) {
                    $s->map = $map;
                } else {
                    $s->map = $this->stripExt($map);
                }
            } elseif ($tok === 'animmap' && $i + 1 < $n) {
                $s->animFrequency = (float) $tokens[++$i];
                while ($i + 1 < $n && !in_array(strtolower($tokens[$i + 1]), ['blendfunc', 'rgbgen', 'alphagen', 'alphafunc', 'tcmod', 'tcgen', 'depthwrite', 'depthfunc', 'map', 'clampmap', 'animmap'], true)) {
                    $s->animFrames[] = $this->stripExt($tokens[++$i]);
                }
                if ($s->animFrames !== []) {
                    $s->map = $s->animFrames[0];
                }
            } elseif ($tok === 'blendfunc' && $i + 1 < $n) {
                $a = strtolower($tokens[++$i]);
                if (in_array($a, ['add', 'filter', 'blend'], true)) {
                    $s->hasBlend = true;
                    if ($a === 'add') {
                        $s->blendSrc = self::GL_ONE;
                        $s->blendDst = self::GL_ONE;
                    } elseif ($a === 'filter') {
                        $s->blendSrc = self::GL_DST_COLOR;
                        $s->blendDst = self::GL_ZERO;
                    } else {
                        $s->blendSrc = self::GL_SRC_ALPHA;
                        $s->blendDst = self::GL_ONE_MINUS_SRC_ALPHA;
                    }
                } elseif ($i + 1 < $n) {
                    $b = strtolower($tokens[++$i]);
                    $s->hasBlend = true;
                    $s->blendSrc = $this->glBlend($a);
                    $s->blendDst = $this->glBlend($b);
                }
            } elseif ($tok === 'rgbgen' && $i + 1 < $n) {
                $rgbGenExplicit = true;
                $s->rgbGen = strtolower($tokens[++$i]);
                if ($s->rgbGen === 'wave' && $i + 5 < $n) {
                    $s->rgbWaveFunc = strtolower($tokens[++$i]);
                    $s->rgbWaveBase = (float) $tokens[++$i];
                    $s->rgbWaveAmp = (float) $tokens[++$i];
                    $s->rgbWavePhase = (float) $tokens[++$i];
                    $s->rgbWaveFreq = (float) $tokens[++$i];
                }
            } elseif ($tok === 'alphagen' && $i + 1 < $n) {
                $s->alphaGen = strtolower($tokens[++$i]);
                if ($s->alphaGen === 'portal') {
                    // range follows; stored on shader in parseShader via stage flag
                    if ($i + 1 < $n && is_numeric($tokens[$i + 1])) {
                        $s->portalRange = (float) $tokens[++$i];
                    } else {
                        $s->portalRange = 256.0;
                    }
                }
            } elseif ($tok === 'alphafunc' && $i + 1 < $n) {
                $s->alphaFunc = strtoupper($tokens[++$i]);
            } elseif ($tok === 'tcmod' && $i + 1 < $n) {
                $type = strtolower($tokens[++$i]);
                $args = [];
                // tcMod stretch <func> base amp phase freq (tr_shader ParseTexMod)
                if ($type === 'stretch' && $i + 5 < $n) {
                    $args[] = strtolower($tokens[++$i]); // func name stored as string in args[0]
                    $args[] = (float) $tokens[++$i];
                    $args[] = (float) $tokens[++$i];
                    $args[] = (float) $tokens[++$i];
                    $args[] = (float) $tokens[++$i];
                } else {
                    while ($i + 1 < $n && is_numeric($tokens[$i + 1])) {
                        $args[] = (float) $tokens[++$i];
                    }
                }
                $s->tcMods[] = ['type' => $type, 'args' => $args];
            } elseif ($tok === 'depthwrite') {
                $s->depthWrite = true;
            } elseif ($tok === 'depthfunc' && $i + 1 < $n) {
                $s->depthFunc = strtolower($tokens[++$i]);
            } elseif ($tok === 'tcgen' && $i + 1 < $n) {
                $s->tcGen = strtolower($tokens[++$i]);
                if ($s->tcGen === 'vector' && $i + 6 < $n) {
                    $i += 6; // skip two vec3
                }
            } else {
                if (!in_array($tok, ['detail'], true)) {
                    // may be unknown stage directive
                }
            }
        }

        // tr_shader.c: GL_ONE GL_ZERO disables blending and forces depth write
        if ($s->hasBlend && $s->blendSrc === self::GL_ONE && $s->blendDst === self::GL_ZERO) {
            $s->hasBlend = false;
            $s->blendSrc = 0;
            $s->blendDst = 0;
            $s->depthWrite = true;
        }

        // tr_shader.c: default depthMask TRUE when not blended (alphaFunc alone still writes Z)
        if (!$s->hasBlend) {
            $s->depthWrite = true;
        }

        // tr_shader.c: implicit rgbGen when omitted
        if (!$rgbGenExplicit) {
            if (!$s->hasBlend
                || $s->blendSrc === self::GL_ONE
                || $s->blendSrc === self::GL_SRC_ALPHA) {
                $s->rgbGen = 'identitylighting';
            } else {
                $s->rgbGen = 'identity';
            }
        }

        // FinishShader: $lightmap defaults to TCGEN_LIGHTMAP (leave environment alone)
        if ($s->isLightmap && $s->tcGen === 'base') {
            $s->tcGen = 'lightmap';
        }

        return $s;
    }

    private function stageIsTranslucent(ShaderStage $s): bool
    {
        if (!$s->hasBlend) {
            return false;
        }
        if ($s->blendSrc === self::GL_DST_COLOR && $s->blendDst === self::GL_ZERO) {
            return false; // lightmap filter
        }
        if ($s->blendSrc === self::GL_ONE && $s->blendDst === self::GL_ONE) {
            return true;
        }
        if ($s->blendSrc === self::GL_SRC_ALPHA && $s->blendDst === self::GL_ONE_MINUS_SRC_ALPHA) {
            return true;
        }
        if ($s->alphaFunc !== '') {
            return true;
        }
        return true;
    }

    private function glBlend(string $name): int
    {
        return match (strtoupper($name)) {
            'GL_ZERO' => self::GL_ZERO,
            'GL_ONE' => self::GL_ONE,
            'GL_SRC_COLOR' => self::GL_SRC_COLOR,
            'GL_ONE_MINUS_SRC_COLOR' => self::GL_ONE_MINUS_SRC_COLOR,
            'GL_SRC_ALPHA' => self::GL_SRC_ALPHA,
            'GL_ONE_MINUS_SRC_ALPHA' => self::GL_ONE_MINUS_SRC_ALPHA,
            'GL_DST_ALPHA' => self::GL_DST_ALPHA,
            'GL_ONE_MINUS_DST_ALPHA' => self::GL_ONE_MINUS_DST_ALPHA,
            'GL_DST_COLOR' => self::GL_DST_COLOR,
            'GL_ONE_MINUS_DST_COLOR' => self::GL_ONE_MINUS_DST_COLOR,
            default => self::GL_ONE,
        };
    }

    /** @return list<string> */
    private function tokenize(string $body): array
    {
        $out = [];
        $len = strlen($body);
        $i = 0;
        while ($i < $len) {
            $c = $body[$i];
            if (ctype_space($c)) {
                $i++;
                continue;
            }
            if ($c === '{' || $c === '}') {
                $out[] = $c;
                $i++;
                continue;
            }
            $start = $i;
            while ($i < $len && !ctype_space($body[$i]) && $body[$i] !== '{' && $body[$i] !== '}') {
                $i++;
            }
            $out[] = substr($body, $start, $i - $start);
        }
        return $out;
    }

    private function hit(string $d): void
    {
        $this->directiveHits[$d] = ($this->directiveHits[$d] ?? 0) + 1;
    }

    private function stripExt(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        foreach (['.tga', '.jpg', '.jpeg', '.png'] as $ext) {
            if (str_ends_with(strtolower($name), $ext)) {
                return substr($name, 0, -strlen($ext));
            }
        }
        return $name;
    }
}
