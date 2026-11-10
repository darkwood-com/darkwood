<?php

declare(strict_types=1);

namespace Quake3\Render;

/** One Q3 shader stage (tr_shader.c subset). */
final class ShaderStage
{
    public string $map = '';
    public bool $isLightmap = false;
    public bool $clamp = false;
    /** @var list<string> anim frames */
    public array $animFrames = [];
    public float $animFrequency = 0.0;

    public int $blendSrc = 0;
    public int $blendDst = 0;
    public bool $hasBlend = false;

    public string $rgbGen = 'identity';
    public string $alphaGen = 'identity';
    public string $alphaFunc = '';
    /** base | environment | lightmap | vector (tr_shader TCGEN_*) */
    public string $tcGen = 'base';
    /** '' | lequal | equal (tr_shader depthFunc) */
    public string $depthFunc = '';
    /** rgbGen wave: func base amp phase freq (RB_CalcWaveColor) */
    public string $rgbWaveFunc = '';
    public float $rgbWaveBase = 0.0;
    public float $rgbWaveAmp = 0.0;
    public float $rgbWavePhase = 0.0;
    public float $rgbWaveFreq = 0.0;
    /** alphaGen portal range (copied to shader portalRange). */
    public float $portalRange = 256.0;

    /** @var list<array{type: string, args: list<float>}> */
    public array $tcMods = [];

    public bool $depthWrite = false;
}
