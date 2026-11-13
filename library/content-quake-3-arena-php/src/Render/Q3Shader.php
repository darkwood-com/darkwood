<?php

declare(strict_types=1);

namespace Quake3\Render;

/** Parsed Q3 material (scripts/*.shader). */
final class Q3Shader
{
    public function __construct(
        public readonly string $name,
        /** @var list<ShaderStage> */
        public array $stages = [],
        public bool $sky = false,
        public bool $nonsolid = false,
        public bool $nolightmap = false,
        public string $cull = 'front', // front|back|none|disable
        public ?string $skyBox = null,
        public int $cloudHeight = 512,
        /** @var array<string, true> */
        public array $surfaceparms = [],
        public bool $opaque = true,
        /** @var list<array{type: string, spread?: float, func?: string, base?: float, amp?: float, phase?: float, freq?: float}> */
        public array $deforms = [],
        /** FinishShader sort key (shaderSort_t / explicit sort). */
        public float $sort = 3.0,
        public bool $polygonOffset = false,
        /** alphaGen portal range (default 256). */
        public float $portalRange = 256.0,
    ) {
    }

    public const SS_PORTAL = 1.0;
    public const SS_ENVIRONMENT = 2.0;
    public const SS_OPAQUE = 3.0;
    public const SS_DECAL = 4.0;
    public const SS_SEE_THROUGH = 5.0;
    public const SS_BANNER = 6.0;
    public const SS_FOG = 7.0;
    public const SS_UNDERWATER = 8.0;
    public const SS_BLEND0 = 9.0;
    public const SS_BLEND1 = 10.0;
    public const SS_NEAREST = 16.0;

    public function hasAutosprite(): bool
    {
        foreach ($this->deforms as $d) {
            if (($d['type'] ?? '') === 'autosprite') {
                return true;
            }
        }
        return false;
    }

    public function hasAutosprite2(): bool
    {
        foreach ($this->deforms as $d) {
            if (($d['type'] ?? '') === 'autosprite2') {
                return true;
            }
        }
        return false;
    }

    public function hasWaveDeform(): bool
    {
        foreach ($this->deforms as $d) {
            if (($d['type'] ?? '') === 'wave') {
                return true;
            }
        }
        return false;
    }

    /** @return list<array{type: string, spread: float, func: string, base: float, amp: float, phase: float, freq: float}> */
    public function waveDeforms(): array
    {
        $out = [];
        foreach ($this->deforms as $d) {
            if (($d['type'] ?? '') === 'wave') {
                $out[] = $d;
            }
        }
        return $out;
    }

    public function hasPortalAlpha(): bool
    {
        foreach ($this->stages as $s) {
            if ($s->alphaGen === 'portal') {
                return true;
            }
        }
        return false;
    }

    public function isSky(): bool
    {
        return $this->sky || isset($this->surfaceparms['sky']);
    }

    public function needsBlendPass(): bool
    {
        // Lightmap filter (GL_DST_COLOR, GL_ZERO) is multipass lighting, not translucency.
        foreach ($this->stages as $s) {
            if ($s->tcGen === 'environment') {
                return true; // needs multipass (env + detail/lm)
            }
            if (!$s->hasBlend) {
                continue;
            }
            if ($s->isLightmap) {
                continue;
            }
            // add / blend / alpha blends → translucent pass
            if ($s->blendSrc === ShaderParser::GL_ONE && $s->blendDst === ShaderParser::GL_ONE) {
                return true;
            }
            if ($s->blendSrc === ShaderParser::GL_SRC_ALPHA && $s->blendDst === ShaderParser::GL_ONE_MINUS_SRC_ALPHA) {
                return true;
            }
            if ($s->blendSrc === ShaderParser::GL_ZERO && $s->blendDst === ShaderParser::GL_SRC_COLOR) {
                return true; // filter as sole stage sometimes used translucent
            }
            // DST_COLOR,ZERO = lightmap modulate — opaque when paired with LM
            if ($s->blendSrc === ShaderParser::GL_DST_COLOR && $s->blendDst === ShaderParser::GL_ZERO) {
                continue;
            }
            // any other blend → treat as blend pass
            return true;
        }
        // surfaceparm nonsolid / explicit non-opaque from parser
        return !$this->opaque && !$this->isSky();
    }

    public function startsWithEnvironment(): bool
    {
        return isset($this->stages[0]) && $this->stages[0]->tcGen === 'environment';
    }

    public function hasEnvironment(): bool
    {
        foreach ($this->stages as $s) {
            if ($s->tcGen === 'environment') {
                return true;
            }
        }
        return false;
    }

    public function hasLightingSpecular(): bool
    {
        foreach ($this->stages as $s) {
            if ($s->alphaGen === 'lightingspecular') {
                return true;
            }
        }
        return false;
    }

    public function hasAlphaFunc(): bool
    {
        foreach ($this->stages as $s) {
            if ($s->alphaFunc !== '') {
                return true;
            }
        }
        return false;
    }

    public function hasTcModTurb(): bool
    {
        foreach ($this->stages as $s) {
            foreach ($s->tcMods as $m) {
                if (($m['type'] ?? '') === 'turb') {
                    return true;
                }
            }
        }
        return false;
    }

    /** Any tcMod — opaque diffuse×lm combine never applies texture-matrix / CPU ST mods. */
    public function hasTcMod(): bool
    {
        foreach ($this->stages as $s) {
            if ($s->tcMods !== []) {
                return true;
            }
        }
        return false;
    }

    public function hasRgbGenVertex(): bool
    {
        foreach ($this->stages as $s) {
            $g = strtolower($s->rgbGen);
            if ($g === 'vertex' || $g === 'exactvertex') {
                return true;
            }
        }
        return false;
    }

    public function hasDepthFuncEqual(): bool
    {
        foreach ($this->stages as $s) {
            if ($s->depthFunc === 'equal') {
                return true;
            }
        }
        return false;
    }

    /** Lightmap stage after a non-lightmap pass — cannot use opaque diffuse*lm combine. */
    public function hasDeferredLightmap(): bool
    {
        $seenColor = false;
        foreach ($this->stages as $s) {
            if ($s->isLightmap || $s->map === '$lightmap') {
                if ($seenColor) {
                    return true;
                }
                continue;
            }
            if ($s->map !== '' && !str_starts_with($s->map, '$')) {
                $seenColor = true;
            }
        }
        return false;
    }

    /** Full multipass (skip opaque diffuse*lm combine). */
    public function needsFullMultipass(): bool
    {
        // Any tcGen environment (not only stage 0) — e.g. comp3, banners
        // depthFunc equal / deferred LM need prior passes before lightmap filter
        return $this->hasEnvironment()
            || $this->hasLightingSpecular()
            || $this->hasAlphaFunc()
            || $this->hasRgbGenVertex()
            || $this->hasDepthFuncEqual()
            || $this->hasDeferredLightmap()
            || $this->hasTcMod();
    }
}
