<?php

declare(strict_types=1);

namespace Quake3\Platform;

use FFI;
use FFI\CData;
use Quake3\Bsp\LightGrid;
use Quake3\Bsp\LightmapLump;
use Quake3\Bsp\VisData;
use Quake3\Collision\CollisionModel;
use Quake3\Game\Player;
use Quake3\Game\QuakeMath;
use Quake3\Game\WeaponState;
use Quake3\Render\DecodedImage;
use Quake3\Render\LightingConfig;
use Quake3\Render\MarkFragments;
use Quake3\Render\MaterialResolver;
use Quake3\Render\Md3Model;
use Quake3\Render\Q3Shader;
use Quake3\Render\ShaderParser;
use Quake3\Render\ShaderStage;
use Quake3\Render\WorldBatch;

/**
 * Fixed-function OpenGL: VBO batches + multitexture + blend stages + sky/flares.
 */
final class Gl
{
    public const GL_COLOR_BUFFER_BIT = 0x00004000;
    public const GL_DEPTH_BUFFER_BIT = 0x00000100;
    public const GL_TRIANGLES = 0x0004;
    public const GL_TRIANGLE_STRIP = 0x0005;
    public const GL_TRIANGLE_FAN = 0x0006;
    public const GL_UNSIGNED_INT = 0x1405;
    public const GL_FLOAT = 0x1406;
    public const GL_ARRAY_BUFFER = 0x8892;
    public const GL_ELEMENT_ARRAY_BUFFER = 0x8893;
    public const GL_STATIC_DRAW = 0x88E4;
    public const GL_STREAM_DRAW = 0x88E0;
    public const GL_DEPTH_TEST = 0x0B71;
    public const GL_CULL_FACE = 0x0B44;
    public const GL_BLEND = 0x0BE2;
    public const GL_ALPHA_TEST = 0x0BC0;
    public const GL_TEXTURE_2D = 0x0DE1;
    public const GL_VERTEX_ARRAY = 0x8074;
    public const GL_TEXTURE_COORD_ARRAY = 0x8078;
    public const GL_MODELVIEW = 0x1700;
    public const GL_PROJECTION = 0x1701;
    public const GL_TEXTURE0 = 0x84C0;
    public const GL_TEXTURE1 = 0x84C1;
    public const GL_MODULATE = 0x2100;
    public const GL_REPLACE = 0x1E01;
    public const GL_TEXTURE_ENV = 0x2300;
    public const GL_TEXTURE_ENV_MODE = 0x2200;
    public const GL_RGBA = 0x1908;
    public const GL_UNSIGNED_BYTE = 0x1401;
    public const GL_LINEAR = 0x2601;
    public const GL_LINEAR_MIPMAP_LINEAR = 0x2703;
    public const GL_TEXTURE_MIN_FILTER = 0x2801;
    public const GL_TEXTURE_MAG_FILTER = 0x2800;
    public const GL_TEXTURE_WRAP_S = 0x2802;
    public const GL_TEXTURE_WRAP_T = 0x2803;
    public const GL_REPEAT = 0x2901;
    public const GL_CLAMP = 0x2900;
    public const GL_CLAMP_TO_EDGE = 0x812F;
    public const GL_FRONT = 0x0404;
    public const GL_BACK = 0x0405;
    public const GL_SRC_ALPHA = 0x0302;
    public const GL_ONE_MINUS_SRC_ALPHA = 0x0303;
    public const GL_ONE_MINUS_SRC_COLOR = 0x0301;
    public const GL_ONE = 1;
    public const GL_ZERO = 0;
    public const GL_POLYGON_OFFSET_FILL = 0x8037;
    private const MARK_TOTAL_TIME = 10.0;
    private const MARK_FADE_TIME = 1.0;
    private const MAX_MARKS = 256;
    public const GL_DST_COLOR = 0x0306;
    public const GL_GREATER = 0x0204;
    public const GL_GEQUAL = 0x0206;
    public const GL_LESS = 0x0201;
    public const GL_EQUAL = 0x0202;
    public const GL_LEQUAL = 0x0203;
    public const GL_DEPTH_WRITEMASK = 0x0B72;
    public const GL_GENERATE_MIPMAP = 0x8191;
    public const GL_QUADS = 0x0007;
    public const GL_LINES = 0x0001;
    public const GL_FRONT_AND_BACK = 0x0408;
    public const GL_LINE = 0x1B01;
    public const GL_FILL = 0x1B02;
    public const GL_NORMAL_ARRAY = 0x8075;
    public const GL_TEXTURE_GEN_S = 0x0C60;
    public const GL_TEXTURE_GEN_T = 0x0C61;
    public const GL_TEXTURE_GEN_MODE = 0x2500;
    public const GL_SPHERE_MAP = 0x2402;
    public const GL_S = 0x2000;
    public const GL_T = 0x2001;
    public const GL_COLOR_ARRAY = 0x8076;
    public const GL_DEPTH_COMPONENT = 0x1902;
    public const GL_MODELVIEW_MATRIX = 0x0BA6;
    public const GL_PROJECTION_MATRIX = 0x0BA7;
    public const GL_CLIP_PLANE0 = 0x3000;

    private const STRIDE = 40; // 10 * float32: xyz + st + lm + normal
    /** Fixed specular light origin from RB_CalcSpecularAlpha */
    private const SPECULAR_LIGHT_ORIGIN = [-960.0, 1980.0, 96.0];

    private FFI $ffi;
    private bool $glDebug = false;

    /** @var list<array{vbo: CData, ibo: CData, count: int, shader: string, lm: int, useLm: bool, blend: bool, cluster: int, flat?: bool}> */
    private array $gpuBatches = [];
    /**
     * Inline bmodel batches keyed by BSP model index (func_door *N).
     * @var array<int, list<array{vbo: CData, ibo: CData, count: int, shader: string, lm: int, useLm: bool, blend: bool, cluster: int, flat?: bool}>>
     */
    private array $gpuMoverBatches = [];
    /** @var array<string, int> path => tex id */
    private array $textureCache = [];
    /** @var list<int> lightmap tex ids */
    private array $lightmapTex = [];
    private int $whiteTex = 0;
    private int $drawCalls = 0;
    private int $binds = 0;
    private int $culled = 0;

    private ?ShaderParser $shaderParser = null;
    private ?MaterialResolver $materials = null;
    private LightingConfig $lighting;
    /** @var array<string, true> */
    private array $missingTextureLogged = [];
    private ?string $debugMaterial = null;
    private bool $drawFlares = true;
    private bool $skyEnabled = true;
    private bool $drawEffects = true;
    private bool $debugNoCull = false;
    private bool $debugBatches = false;
    private bool $debugBatchesLogged = false;
    private bool $debugBatchFrame = false;
    private ?int $debugFace = null;
    private float $flareSize = 8.0;
    /** Current vertical FOV degrees (CG_CalcFov fov_y) for flare screen→world. */
    private float $fovY = 90.0;
    /** r_flareFade default (tr_init.c). */
    private float $flareFadeRate = 7.0;
    /**
     * Per-flare fade state (RB_TestFlare).
     * @var array<string, array{visible: bool, fadeTime: float}>
     */
    private array $flareFadeState = [];
    private int $viewportX = 0;
    private int $viewportY = 0;
    private int $viewportW = 640;
    private int $viewportH = 480;

    /** @var list<array{origin: list<float>, shader: string, normal?: list<float>, color?: list<float>}> */
    private array $flares = [];
    private ?\Quake3\Collision\CollisionModel $collision = null;
    private ?LightGrid $lightGrid = null;
    /** @var list<array{0:list<float>,1:list<float>,2:list<float>,normal:list<float>}> */
    private array $markTris = [];
    private ?string $skyShader = null;
    /** @var list<int> sky box face tex ids rt,bk,lf,ft,up,dn */
    private array $skyBoxTex = [];
    /** Cloud-layer sky (skyparms farbox '-'): stage textures from sky shader */
    private ?\Quake3\Render\Q3Shader $skyMaterial = null;
    /** @var array<int, array<int, array<int, array{0: float, 1: float}>>>|null s_cloudTexCoords */
    private ?array $cloudTexCoords = null;
    private float $cloudHeightCached = -1.0;
    /** R_MirrorViewBySurface recursion / cull flip. */
    private bool $inPortalView = false;
    private bool $isMirror = false;
    /**
     * misc_portal_surface resolved (MapGame).
     * @var list<array{origin: list<float>, origin2: list<float>, axis: list<list<float>>, mirror: bool, roll?: float}>
     */
    private array $portalSurfaces = [];

    private ?Md3Model $hudHead = null;
    /** @var array<string, int> */
    private array $hudHeadTex = [];
    /** @var array<string, array{model: Md3Model, tex: array<string, int>}> */
    private array $hudAmmo = [];
    private ?Md3Model $hudArmor = null;
    /** @var array<string, int> */
    private array $hudArmorTex = [];
    /** ringFlashModel (models/weaphits/ring02.md3) for plasmaExplosion */
    private ?Md3Model $impactRing = null;
    /** @var array<string, int> */
    private array $impactRingTex = [];
    /** bulletFlashModel (models/weaphits/bullet.md3) for bulletExplosion */
    private ?Md3Model $impactBullet = null;
    /** @var array<string, int> */
    private array $impactBulletTex = [];
    /** teleportEffectModel (models/misc/telep.md3) */
    private ?Md3Model $teleportModel = null;
    /** @var array<string, int> */
    private array $teleportModelTex = [];
    /**
     * LE_FADE_RGB teleport swirls (CG_SpawnEffect).
     * @var list<array{origin: list<float>, born: float, dieAt: float}>
     */
    private array $teleportEffects = [];
    private float $headStartYaw = 180.0;
    private float $headEndYaw = 180.0;
    private float $headStartPitch = 0.0;
    private float $headEndPitch = 0.0;
    private float $headStartTime = 0.0;
    private float $headEndTime = 0.0;

    /** @var list<array{origin: list<float>, normal: list<float>, dieAt: float, born: float, orientation: float, color: list<float>, textured: bool, kind: string}> */
    private array $impacts = [];
    /**
     * Lasting wall marks (CG_ImpactMark / CG_AddMarks — projected quads).
     * @var list<array{origin: list<float>, normal: list<float>, born: float, path: string, radius: float, orientation: float, alphaFade: bool, color: list<float>, verts: list<array{xyz: list<float>, st: list<float>}>}>
     */
    private array $marks = [];
    /**
     * Plasma trail LE_MOVE_SCALE_FADE sprites (CG_PlasmaTrail — railDisc).
     * @var list<array{origin: list<float>, velocity: list<float>, born: float, end: float}>
     */
    private array $plasmaTrail = [];
    /**
     * Smoke puffs (CG_SmokePuff / shotgunSmokePuff) — LE_MOVE_SCALE_FADE, no gravity.
     * @var list<array{origin: list<float>, velocity: list<float>, born: float, end: float, radius: float, alpha: float}>
     */
    private array $smokePuffs = [];
    /** machinegunBrassModel / shotgunBrassModel */
    private ?Md3Model $brassMg = null;
    private ?Md3Model $brassSg = null;
    /** @var array<string, int> */
    private array $brassMgTex = [];
    /** @var array<string, int> */
    private array $brassSgTex = [];
    /**
     * LE_FRAGMENT brass (CG_MachineGunEjectBrass / CG_ShotgunEjectBrass).
     * @var list<array{
     *   kind: string,
     *   origin: list<float>,
     *   trBase: list<float>,
     *   trDelta: list<float>,
     *   trTime: float,
     *   trType: string,
     *   angBase: list<float>,
     *   angDelta: list<float>,
     *   bounce: float,
     *   born: float,
     *   end: float
     * }>
     */
    private array $brassFragments = [];
    /**
     * One-frame MG tracers (CG_Tracer).
     * @var list<array{start: list<float>, finish: list<float>, forward: list<float>, width: float}>
     */
    private array $tracers = [];
    private float $time = 0.0;
    /** @var list<float>|null */
    private ?array $viewOrigin = null;
    /** CG_DamageBlendBlob state (seconds) */
    private float $damageTime = -999.0;
    private float $damageValue = 0.0;
    private float $damageX = 0.0;
    private float $damageY = 0.0;
    /** CG_DrawPickupItem / ITEM_BLOB_TIME */
    private string $itemPickupClass = '';
    private float $itemPickupTime = -999.0;
    private float $itemPickupBlendTime = -999.0;
    /** Keep last specular color / CPU texcoord buffers alive across glDrawElements */
    private mixed $specularColorBuf = null;
    private mixed $turbTexCoordBuf = null;
    private mixed $envTexCoordBuf = null;

    public function __construct(bool $glDebug = false, ?LightingConfig $lighting = null)
    {
        $this->glDebug = $glDebug;
        $this->lighting = $lighting ?? new LightingConfig();
        $library = $this->resolveLibrary();
        try {
            $this->ffi = FFI::cdef($this->cdef(), $library);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to load OpenGL via FFI (' . $library . '): ' . $e->getMessage());
        }
    }

    public function lighting(): LightingConfig
    {
        return $this->lighting;
    }

    public function setLighting(LightingConfig $lighting): void
    {
        $this->lighting = $lighting;
    }

    public function setDebugMaterial(?string $shader): void
    {
        $this->debugMaterial = $shader !== null && $shader !== '' ? $shader : null;
    }

    public function setDrawFlares(bool $on, float $size = 40.0): void
    {
        $this->drawFlares = $on;
        $this->flareSize = $size;
    }

    public function setDrawSky(bool $on): void
    {
        $this->skyEnabled = $on;
    }

    public function setDrawEffects(bool $on): void
    {
        $this->drawEffects = $on;
    }

    public function setDebugNoCull(bool $on): void
    {
        $this->debugNoCull = $on;
    }

    public function setDebugBatches(bool $on): void
    {
        $this->debugBatches = $on;
        $this->debugBatchesLogged = false;
    }

    public function setDebugFace(?int $faceIndex): void
    {
        $this->debugFace = $faceIndex;
    }

    public function setFovY(float $fovYDeg): void
    {
        $this->fovY = max(1.0, $fovYDeg);
    }

    public function setCollisionModel(?\Quake3\Collision\CollisionModel $cm): void
    {
        $this->collision = $cm;
    }

    public function setLightGrid(?LightGrid $grid): void
    {
        $this->lightGrid = $grid;
    }

    /**
     * Planar + patch triangles for R_MarkFragments projection.
     * @param list<array{0:list<float>,1:list<float>,2:list<float>,normal:list<float>}> $tris
     */
    public function setMarkTris(array $tris): void
    {
        $this->markTris = $tris;
    }

    /**
     * @param list<array{origin: list<float>, origin2: list<float>, axis: list<list<float>>, mirror: bool}> $portals
     */
    public function setPortalSurfaces(array $portals): void
    {
        $this->portalSurfaces = $portals;
    }

    /** @param array<string, int> $surfaceTex */
    public function setHudHead(?Md3Model $model, array $surfaceTex = []): void
    {
        $this->hudHead = $model;
        $this->hudHeadTex = $surfaceTex;
        $this->headStartTime = 0.0;
        $this->headEndTime = 0.0;
        $this->headStartYaw = 180.0;
        $this->headEndYaw = 180.0;
        $this->headStartPitch = 0.0;
        $this->headEndPitch = 0.0;
    }

    /**
     * CG_RegisterWeapon ammoModel for status bar.
     * @param array<string, int> $surfaceTex
     */
    public function setHudAmmo(string $weaponName, ?Md3Model $model, array $surfaceTex = []): void
    {
        if ($model === null) {
            unset($this->hudAmmo[$weaponName]);
            return;
        }
        $this->hudAmmo[$weaponName] = ['model' => $model, 'tex' => $surfaceTex];
    }

    /** @param array<string, int> $surfaceTex */
    public function setHudArmor(?Md3Model $model, array $surfaceTex = []): void
    {
        $this->hudArmor = $model;
        $this->hudArmorTex = $surfaceTex;
    }

    /**
     * CG_RegisterGraphics ringFlashModel — plasma / rail explosion mesh.
     * @param array<string, int> $surfaceTex
     */
    public function setImpactRingFlash(?Md3Model $model, array $surfaceTex = []): void
    {
        $this->impactRing = $model;
        $this->impactRingTex = $surfaceTex;
        // plasmaExplosion customShader clampMap (not the MD3 surface shader)
        $this->ensurePathTexture('models/weaphits/plasmaboom', false);
    }

    /**
     * CG_RegisterGraphics bulletFlashModel — MG/SG explosion mesh.
     * @param array<string, int> $surfaceTex
     */
    public function setImpactBulletFlash(?Md3Model $model, array $surfaceTex = []): void
    {
        $this->impactBullet = $model;
        $this->impactBulletTex = $surfaceTex;
        // bulletExplosion animMap frames
        $this->ensurePathTexture('models/weaphits/bullet1');
        $this->ensurePathTexture('models/weaphits/bullet2');
        $this->ensurePathTexture('models/weaphits/bullet3');
        $this->ensurePathTexture('gfx/colors/black');
    }

    /**
     * CG_RegisterGraphics teleportEffectModel.
     * @param array<string, int> $surfaceTex
     */
    public function setTeleportEffect(?Md3Model $model, array $surfaceTex = []): void
    {
        $this->teleportModel = $model;
        $this->teleportModelTex = $surfaceTex;
        $this->ensurePathTexture('gfx/misc/teleportEffect2');
    }

    /**
     * CG_RegisterGraphics machinegunBrassModel / shotgunBrassModel.
     * @param array<string, int> $mgTex
     * @param array<string, int> $sgTex
     */
    public function setBrassModels(
        ?Md3Model $mg,
        array $mgTex,
        ?Md3Model $sg,
        array $sgTex,
    ): void {
        $this->brassMg = $mg;
        $this->brassMgTex = $mgTex;
        $this->brassSg = $sg;
        $this->brassSgTex = $sgTex;
    }

    /** CG_DamageFeedback → CG_DamageBlendBlob inputs. */
    public function setDamageFeedback(float $damageTime, float $damageValue, float $damageX = 0.0, float $damageY = 0.0): void
    {
        $this->damageTime = $damageTime;
        $this->damageValue = $damageValue;
        $this->damageX = $damageX;
        $this->damageY = $damageY;
    }

    /** EV_ITEM_PICKUP → cg.itemPickup / itemPickupTime / itemPickupBlendTime. */
    public function notifyItemPickup(string $classname, float $now): void
    {
        $this->itemPickupClass = $classname;
        $this->itemPickupTime = $now;
        $this->itemPickupBlendTime = $now;
    }

    public function setShaderParser(?ShaderParser $parser): void
    {
        $this->shaderParser = $parser;
    }

    public function viewport(int $x, int $y, int $w, int $h): void
    {
        $this->viewportX = $x;
        $this->viewportY = $y;
        $this->viewportW = max(1, $w);
        $this->viewportH = max(1, $h);
        $this->ffi->glViewport($x, $y, $w, $h);
    }

    public function clearColor(float $r, float $g, float $b, float $a = 1.0): void
    {
        $this->ffi->glClearColor($r, $g, $b, $a);
    }

    public function clear(): void
    {
        $this->ffi->glClear(self::GL_COLOR_BUFFER_BIT | self::GL_DEPTH_BUFFER_BIT);
    }

    public function enableDepthTest(): void
    {
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        // Q3 CT_FRONT_SIDED: world tris are CW from the interior; cull GL_FRONT
        $this->ffi->glCullFace(self::GL_FRONT);
    }

    /**
     * @param list<WorldBatch> $batches
     * @param list<array{origin: list<float>, shader: string}> $flares
     */
    public function uploadWorld(
        array $batches,
        LightmapLump $lightmaps,
        MaterialResolver $materials,
        bool $loadTextures = true,
        array $flares = [],
        ?string $skyShader = null,
        array $moverBatches = [],
    ): void {
        $this->destroyWorld();
        $this->ensureWhiteTexture();
        $this->materials = $materials;
        $this->flares = $flares;
        $this->skyShader = $skyShader;

        if ($loadTextures) {
            foreach ($batches as $b) {
                $this->ensureShaderTextures($b->shaderName, $materials);
            }
            foreach ($moverBatches as $mbList) {
                foreach ($mbList as $b) {
                    $this->ensureShaderTextures($b->shaderName, $materials);
                }
            }
            if ($skyShader !== null) {
                $this->loadSkyBox($skyShader, $materials);
            }
            foreach ($flares as $f) {
                $this->ensureShaderTextures($f['shader'], $materials);
            }
        }

        for ($i = 0; $i < $lightmaps->count(); $i++) {
            $rgba = $lightmaps->pageRgba(
                $i,
                $this->lighting->mapOverBrightBits,
                $this->lighting->overbrightBits,
            );
            // Lightmaps: ColorShift at load; only_gamma when no HW gamma (R_LightScaleTexture).
            if ($this->lighting->bakeGammaIntoTextures) {
                $rgba = $this->lighting->gammaRgba($rgba);
            }
            $this->lightmapTex[$i] = $this->createTexture(
                $rgba,
                LightmapLump::PAGE_SIZE,
                LightmapLump::PAGE_SIZE,
                false,
                $this->lighting->lightmapMipmaps,
            );
        }

        foreach ($batches as $b) {
            $this->gpuBatches[] = $this->uploadOneWorldBatch($b);
        }
        foreach ($moverBatches as $mi => $mbList) {
            $this->gpuMoverBatches[(int) $mi] = [];
            foreach ($mbList as $b) {
                $this->gpuMoverBatches[(int) $mi][] = $this->uploadOneWorldBatch($b);
            }
        }
        $this->checkError('uploadWorld');
    }

    /** Upload one WorldBatch into a GPU batch descriptor. */
    private function uploadOneWorldBatch(WorldBatch $b): array
    {
        $cull = 'front';
        if ($this->shaderParser !== null) {
            $cull = $this->shaderParser->get($b->shaderName)->cull;
        }
        $keepVerts = false;
        if ($this->shaderParser !== null) {
            $shKeep = $this->shaderParser->get($b->shaderName);
            $keepVerts = $shKeep->hasLightingSpecular()
                || $shKeep->hasEnvironment()
                || $shKeep->hasWaveDeform()
                || $shKeep->hasPortalAlpha()
                || $shKeep->hasTcModTurb();
        }
        $vbo = $this->ffi->new('unsigned int');
        $ibo = $this->ffi->new('unsigned int');
        $this->ffi->glGenBuffers(1, FFI::addr($vbo));
        $this->ffi->glGenBuffers(1, FFI::addr($ibo));

        $vertexBinary = $b->vertexBinary;
        $indexBinary = $b->indexBinary;
        $posLen = strlen($vertexBinary);
        $idxLen = strlen($indexBinary);
        $posBuf = $this->ffi->new('unsigned char[' . $posLen . ']');
        FFI::memcpy($posBuf, $vertexBinary, $posLen);
        $idxBuf = $this->ffi->new('unsigned char[' . $idxLen . ']');
        FFI::memcpy($idxBuf, $indexBinary, $idxLen);

        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $vbo->cdata);
        $this->ffi->glBufferData(self::GL_ARRAY_BUFFER, $posLen, $posBuf, self::GL_STATIC_DRAW);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $ibo->cdata);
        $this->ffi->glBufferData(self::GL_ELEMENT_ARRAY_BUFFER, $idxLen, $idxBuf, self::GL_STATIC_DRAW);

        return [
            'vbo' => $vbo,
            'ibo' => $ibo,
            'count' => $b->indexCount,
            'shader' => $b->shaderName,
            'lm' => $b->lightmapNum,
            'useLm' => $b->useLightmap,
            'blend' => $b->blend,
            'cluster' => $b->cluster,
            'clusters' => $b->clusters,
            'faces' => $b->faces,
            'cull' => $cull,
            'vertexBinary' => $keepVerts ? $vertexBinary : null,
            'colorBinary' => (($this->shaderParser !== null
                && $this->shaderParser->get($b->shaderName)->hasRgbGenVertex()
                && $b->colorBinary !== '')
                || ($b->vertexLight && $b->colorBinary !== ''))
                ? $b->colorBinary : null,
            'vertexLight' => $b->vertexLight,
            'autosprites' => $b->autosprites,
            'autosprite2s' => $b->autosprite2s,
            'sort' => $b->sort,
            'polygonOffset' => $b->polygonOffset,
            'planeNormal' => $b->planeNormal,
            'planeDist' => $b->planeDist,
        ];
    }

    /**
     * CG_Mover SOLID_BMODEL — translate inline brush by origin−pos1.
     * @param list<float> $delta
     */
    public function drawInlineMover(int $modelIndex, array $delta, string $mode = 'full', float $time = 0.0): void
    {
        $list = $this->gpuMoverBatches[$modelIndex] ?? [];
        if ($list === []) {
            return;
        }
        $saved = $this->gpuBatches;
        $this->gpuBatches = $list;
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glPushMatrix();
        $this->ffi->glTranslatef($delta[0], $delta[1], $delta[2]);
        $this->drawWorldBatches($mode, null, -1, false, $time, true);
        $this->drawWorldBatches($mode, null, -1, false, $time, false);
        $this->ffi->glPopMatrix();
        $this->gpuBatches = $saved;
        $this->resetGlState();
    }

    private function ensureShaderTextures(string $shaderName, MaterialResolver $materials): void
    {
        // Never early-return on shaderName alone — another material may have cached
        // this path as a single stage map, skipping sibling maps (e.g. chrome_env).
        $clamp = false;
        if ($this->shaderParser !== null) {
            $sh = $this->shaderParser->get($shaderName);
            foreach ($sh->stages as $st) {
                if (!$st->isLightmap && $st->map !== '' && !str_starts_with($st->map, '$')) {
                    $clamp = $st->clamp;
                    $maps = $st->animFrames !== [] ? $st->animFrames : [$st->map];
                    foreach ($maps as $key) {
                        if ($key === '' || str_starts_with($key, '$') || isset($this->textureCache[$key])) {
                            continue;
                        }
                        $stageImg = $materials->resolveStageImage($key);
                        if ($stageImg !== null) {
                            $scaled = $this->lighting->lightScaleRgba($stageImg->rgba);
                            $this->textureCache[$key] = $this->createTexture(
                                $scaled,
                                $stageImg->width,
                                $stageImg->height,
                                !$clamp,
                                true,
                            );
                        } else {
                            $this->logMissingTexture($shaderName, $key);
                        }
                    }
                }
            }
        }
        if (!isset($this->textureCache[$shaderName])) {
            $img = $materials->resolveImage($shaderName);
            if ($img !== null) {
                $scaled = $this->lighting->lightScaleRgba($img->rgba);
                $this->textureCache[$shaderName] = $this->createTexture($scaled, $img->width, $img->height, !$clamp, true);
            } else {
                $this->logMissingTexture($shaderName, $shaderName);
                $this->textureCache[$shaderName] = $this->whiteTex;
            }
        }
    }

    private function logMissingTexture(string $shader, string $map): void
    {
        $k = $shader . "\0" . $map;
        if (isset($this->missingTextureLogged[$k])) {
            return;
        }
        $this->missingTextureLogged[$k] = true;
        fwrite(STDERR, "MISSING_TEXTURE shader={$shader} map={$map}\n");
    }

    private function loadSkyBox(string $skyShader, MaterialResolver $materials): void
    {
        $this->skyBoxTex = [];
        $this->skyMaterial = null;
        $this->cloudTexCoords = null;
        $this->cloudHeightCached = -1.0;
        $box = null;
        if ($this->shaderParser !== null) {
            $sh = $this->shaderParser->get($skyShader);
            $this->skyMaterial = $sh;
            $box = $sh->skyBox;
            // Ensure cloud stage maps are uploaded (q3dm0: skyparms - 512 -)
            foreach ($sh->stages as $st) {
                if ($st->isLightmap || $st->map === '' || str_starts_with($st->map, '$')) {
                    continue;
                }
                if (!isset($this->textureCache[$st->map])) {
                    $img = $materials->resolveStageImage($st->map);
                    if ($img !== null) {
                        $scaled = $this->lighting->lightScaleRgba($img->rgba);
                        $this->textureCache[$st->map] = $this->createTexture(
                            $scaled,
                            $img->width,
                            $img->height,
                            true,
                            true,
                        );
                    }
                }
            }
        }
        if ($box === null || $box === '-' || $box === '' || $box === 'full' || $box === 'half') {
            // No outerbox (or non-path token) — cloud layers only
            return;
        }
        $suf = ['rt', 'bk', 'lf', 'ft', 'up', 'dn'];
        foreach ($suf as $s) {
            $img = $materials->resolveStageImage($box . '_' . $s);
            if ($img === null) {
                $this->skyBoxTex[] = $this->whiteTex;
            } else {
                $this->skyBoxTex[] = $this->createTexture($img->rgba, $img->width, $img->height, false, true);
            }
        }
    }

    public function uploadMesh(string $positions, string $indices): void
    {
        $this->destroyWorld();
        $vbo = $this->ffi->new('unsigned int');
        $ibo = $this->ffi->new('unsigned int');
        $this->ffi->glGenBuffers(1, FFI::addr($vbo));
        $this->ffi->glGenBuffers(1, FFI::addr($ibo));
        $posLen = strlen($positions);
        $idxLen = strlen($indices);
        $posBuf = $this->ffi->new('unsigned char[' . $posLen . ']');
        FFI::memcpy($posBuf, $positions, $posLen);
        $idxBuf = $this->ffi->new('unsigned char[' . $idxLen . ']');
        FFI::memcpy($idxBuf, $indices, $idxLen);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $vbo->cdata);
        $this->ffi->glBufferData(self::GL_ARRAY_BUFFER, $posLen, $posBuf, self::GL_STATIC_DRAW);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $ibo->cdata);
        $this->ffi->glBufferData(self::GL_ELEMENT_ARRAY_BUFFER, $idxLen, $idxBuf, self::GL_STATIC_DRAW);
        $this->gpuBatches[] = [
            'vbo' => $vbo,
            'ibo' => $ibo,
            'count' => intdiv($idxLen, 4),
            'shader' => '',
            'lm' => -1,
            'useLm' => false,
            'blend' => false,
            'cluster' => -1,
            'flat' => true,
        ];
    }

    public function drawMeshFlat(float $r, float $g, float $b): void
    {
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glColor3f($r, $g, $b);
        foreach ($this->gpuBatches as $b) {
            if (!empty($b['flat'])) {
                $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
                $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
                $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
                $this->ffi->glVertexPointer(3, self::GL_FLOAT, 0, null);
                $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
                $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
                $this->drawCalls++;
            } else {
                $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
                $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
                $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
                $this->ffi->glVertexPointer(3, self::GL_FLOAT, self::STRIDE, null);
                $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
                $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
                $this->drawCalls++;
            }
        }
    }

    /**
     * @param 'full'|'no_textures'|'no_lightmaps'|'lightmap_only' $mode
     * @param list<float>|null $viewOrigin for sky + flares
     */
    public function drawWorld(
        string $mode = 'full',
        ?VisData $vis = null,
        int $viewCluster = -1,
        bool $usePvs = true,
        ?array $viewOrigin = null,
        float $time = 0.0,
    ): void {
        $this->drawCalls = 0;
        $this->binds = 0;
        $this->culled = 0;
        $this->time = $time;
        $this->viewOrigin = $viewOrigin;
        $this->debugBatchFrame = $this->debugBatches && !$this->debugBatchesLogged;

        $this->beginOpaqueWorldState();

        // tr_main.c: R_MirrorViewBySurface before SS_PORTAL stages (mirror2 underlay).
        // Nested view paints color; main view then clears depth only (RB_BeginDrawingView).
        if ($viewOrigin !== null && $mode === 'full' && !$this->inPortalView) {
            if ($this->drawMirrorUnderlays($mode, $vis, $viewCluster, $usePvs, $time, $viewOrigin)) {
                $this->ffi->glDepthMask(1);
                $this->ffi->glClear(self::GL_DEPTH_BUFFER_BIT);
            }
        }

        // Portal stages composite over underlay / clear; then sky; then rest
        $this->drawWorldBatches($mode, $vis, $viewCluster, $usePvs, $time, true);
        if ($viewOrigin !== null && $this->skyEnabled) {
            $this->drawSky($viewOrigin, $mode);
            $this->beginOpaqueWorldState();
        }
        $this->drawWorldBatches($mode, $vis, $viewCluster, $usePvs, $time, false);

        if ($viewOrigin !== null && $mode === 'full' && $this->drawFlares) {
            $this->drawFlares($viewOrigin);
            $this->resetGlState();
        }
        if ($this->drawEffects) {
            $this->drawImpacts();
            $this->resetGlState();
            $this->drawMarks();
            $this->resetGlState();
            $this->drawPlasmaTrail();
            $this->resetGlState();
            $this->drawSmokePuffs();
            $this->resetGlState();
            $this->drawBrassFragments();
            $this->resetGlState();
            $this->drawTracers();
            $this->resetGlState();
            $this->drawTeleportEffects();
            $this->resetGlState();
            if ($viewOrigin !== null && $mode === 'full') {
                $this->drawDamageBlendBlob($viewOrigin);
                $this->resetGlState();
            }
        }

        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        if ($this->debugBatchFrame) {
            $this->debugBatchesLogged = true;
        }
    }

    /**
     * @param 'full'|'no_textures'|'no_lightmaps'|'lightmap_only' $mode
     * @param bool $portalOnly when true, only sort ≤ SS_PORTAL; when false, skip those
     */
    private function drawWorldBatches(
        string $mode,
        ?VisData $vis,
        int $viewCluster,
        bool $usePvs,
        float $time,
        bool $portalOnly,
    ): void {
        foreach ($this->gpuBatches as $b) {
            if (!empty($b['flat'])) {
                continue;
            }
            $sort = (float) ($b['sort'] ?? Q3Shader::SS_OPAQUE);
            $isPortal = $sort <= Q3Shader::SS_PORTAL + 0.001;
            if ($portalOnly !== $isPortal) {
                continue;
            }
            if ($this->debugMaterial !== null && $b['shader'] !== $this->debugMaterial) {
                if ($this->batchPvsVisible($b, $vis, $viewCluster, $usePvs)) {
                    $this->drawBatchGray($b);
                } else {
                    $this->culled++;
                }
                continue;
            }
            if ($this->debugFace !== null) {
                $has = false;
                foreach ($b['faces'] ?? [] as $f) {
                    if ((int) ($f['index'] ?? -1) === $this->debugFace) {
                        $has = true;
                        break;
                    }
                }
                if (!$has) {
                    $this->culled++;
                    continue;
                }
            }
            if (!$this->batchPvsVisible($b, $vis, $viewCluster, $usePvs)) {
                $this->logBatch('pvs-cull', $b);
                $this->culled++;
                continue;
            }

            $this->applyCull($b['cull'] ?? 'front');
            $this->logBatch('draw', $b);
            $po = !empty($b['polygonOffset']);
            if ($po) {
                $this->ffi->glEnable(self::GL_POLYGON_OFFSET_FILL);
                $this->ffi->glPolygonOffset(-1.0, -2.0);
            }

            if (!empty($b['blend']) && $mode === 'full' && $this->shaderParser !== null) {
                $sh = $this->shaderParser->get($b['shader']);
                // Env / lightingSpecular: full multipass (no opaque diffuse*lm)
                if ($b['useLm'] && !$sh->needsFullMultipass()) {
                    $this->drawOpaqueBatch($b, $mode);
                    $this->resetGlState();
                }
                $this->drawBlendBatch($b, $time);
                $this->resetGlState();
                if ($po) {
                    $this->ffi->glDisable(self::GL_POLYGON_OFFSET_FILL);
                }
                continue;
            }

            // Opaque alphaFunc / rgbGen vertex / env — full multipass, not diffuse*lm combine
            if ($mode === 'full' && $this->shaderParser !== null) {
                $sh = $this->shaderParser->get($b['shader']);
                if ($sh->needsFullMultipass()) {
                    $this->drawBlendBatch($b, $time);
                    $this->resetGlState();
                    if ($po) {
                        $this->ffi->glDisable(self::GL_POLYGON_OFFSET_FILL);
                    }
                    continue;
                }
            }

            $this->drawOpaqueBatch($b, $mode);
            $this->resetGlState();
            if ($po) {
                $this->ffi->glDisable(self::GL_POLYGON_OFFSET_FILL);
            }
        }
    }

    private function applyCull(string $cull): void
    {
        if ($this->debugNoCull || $cull === 'none' || $cull === 'disable' || $cull === 'twosided') {
            $this->ffi->glDisable(self::GL_CULL_FACE);
            return;
        }
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $front = self::GL_FRONT;
        $back = self::GL_BACK;
        // Q3 isMirror flips face culling
        if ($this->isMirror) {
            $tmp = $front;
            $front = $back;
            $back = $tmp;
        }
        // tr_backend.c GL_Cull: CT_FRONT_SIDED → GL_FRONT, CT_BACK_SIDED → GL_BACK.
        // BSP indexes are CW from the interior; NDC of a front-facing floor is CW,
        // so OpenGL's CCW-front test classifies them as back-facing — Q3 therefore
        // culls GL_FRONT to keep them. Prior PHP mapping inverted this (GL_BACK
        // for default), which dropped spawn floors and showed sky (tim_hell) through.
        if ($cull === 'back') {
            $this->ffi->glCullFace($back);
        } else {
            $this->ffi->glCullFace($front);
        }
    }

    /**
     * Q3 R_AddWorldSurface: a merged batch is visible if ANY contributing cluster is.
     *
     * @param array<string, mixed> $b
     */
    private function batchPvsVisible(array $b, ?VisData $vis, int $viewCluster, bool $usePvs): bool
    {
        if (!$usePvs || $vis === null || $viewCluster < 0) {
            return true;
        }
        $clusters = $b['clusters'] ?? [];
        if (!is_array($clusters) || $clusters === []) {
            $c = (int) ($b['cluster'] ?? -1);
            return $c < 0 || $vis->visible($viewCluster, $c);
        }
        foreach ($clusters as $c) {
            $c = (int) $c;
            if ($c < 0 || $vis->visible($viewCluster, $c)) {
                return true;
            }
        }
        return false;
    }

    /** @param array<string, mixed> $b */
    private function logBatch(string $op, array $b): void
    {
        if (!$this->debugBatchFrame) {
            return;
        }
        $clusters = $b['clusters'] ?? [];
        $clist = is_array($clusters) && $clusters !== []
            ? implode(',', array_map('strval', $clusters))
            : (string) ($b['cluster'] ?? -1);
        $faces = $b['faces'] ?? [];
        $face0 = is_array($faces) && $faces !== [] ? (int) ($faces[0]['index'] ?? -1) : -1;
        fwrite(STDERR, sprintf(
            "batch %s shader=%s lm=%d tris=%d cull=%s clusters=%s faces=%d firstFace=%d\n",
            $op,
            (string) ($b['shader'] ?? ''),
            (int) ($b['lm'] ?? -1),
            intdiv((int) ($b['count'] ?? 0), 3),
            (string) ($b['cull'] ?? 'front'),
            $clist,
            is_array($faces) ? count($faces) : 0,
            $face0,
        ));
    }

    /** Untextured stand-in so --debug-material keeps other geometry visible. */
    private function drawBatchGray(array $b): void
    {
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glColor4f(0.45, 0.45, 0.45, 1.0);
        $this->applyCull($b['cull'] ?? 'front');
        $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
        $this->ffi->glVertexPointer(3, self::GL_FLOAT, 40, null);
        $this->ffi->glDrawElements(self::GL_TRIANGLES, (int) $b['count'], self::GL_UNSIGNED_INT, null);
        $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
        $this->drawCalls++;
    }

    /**
     * R_MirrorViewBySurface — one nested underlay (mirror2 or portal_sfx).
     * @param list<float> $viewOrigin
     * @return bool true if an underlay was drawn
     */
    private function drawMirrorUnderlays(
        string $mode,
        ?VisData $vis,
        int $viewCluster,
        bool $usePvs,
        float $time,
        array $viewOrigin,
    ): bool {
        foreach ($this->gpuBatches as $b) {
            $sort = (float) ($b['sort'] ?? Q3Shader::SS_OPAQUE);
            if ($sort > Q3Shader::SS_PORTAL + 0.001) {
                continue;
            }
            $n = $b['planeNormal'] ?? null;
            if (!is_array($n) || count($n) < 3) {
                continue;
            }
            $planeDist = (float) ($b['planeDist'] ?? 0.0);
            // Facing: viewer must be in front of the portal plane
            $side = $n[0] * $viewOrigin[0] + $n[1] * $viewOrigin[1] + $n[2] * $viewOrigin[2] - $planeDist;
            if ($side <= 0.1) {
                continue;
            }
            if ($usePvs && $vis !== null && $viewCluster >= 0 && !$this->batchPvsVisible($b, $vis, $viewCluster, true)) {
                continue;
            }
            $ent = $this->matchPortalEntity($n, $planeDist);
            if ($ent === null) {
                // Fallback: untargeted mirror shaders without entity match
                if (($b['shader'] ?? '') !== 'textures/common/mirror2') {
                    continue;
                }
                $ent = [
                    'origin' => [$n[0] * $planeDist, $n[1] * $planeDist, $n[2] * $planeDist],
                    'origin2' => [$n[0] * $planeDist, $n[1] * $planeDist, $n[2] * $planeDist],
                    'axis' => [[1.0, 0.0, 0.0], [0.0, 1.0, 0.0], [0.0, 0.0, 1.0]],
                    'mirror' => true,
                ];
            }
            $this->renderMirrorView($n, $planeDist, $ent, $mode, $vis, $viewCluster, $usePvs, $time, $viewOrigin);
            return true;
        }

        return false;
    }

    /**
     * Match misc_portal_surface within 64u of plane (tr_main.c).
     * @param list<float> $n
     * @return array{origin: list<float>, origin2: list<float>, axis: list<list<float>>, mirror: bool}|null
     */
    private function matchPortalEntity(array $n, float $planeDist): ?array
    {
        $best = null;
        $bestAbs = 65.0;
        foreach ($this->portalSurfaces as $e) {
            $d = $n[0] * $e['origin'][0] + $n[1] * $e['origin'][1] + $n[2] * $e['origin'][2] - $planeDist;
            $ad = abs($d);
            if ($ad < $bestAbs) {
                $bestAbs = $ad;
                $best = $e;
            }
        }

        return $bestAbs <= 64.0 ? $best : null;
    }

    /**
     * @param list<float> $planeN
     * @param array{origin: list<float>, origin2: list<float>, axis: list<list<float>>, mirror: bool} $ent
     * @param list<float> $viewOrigin
     */
    private function renderMirrorView(
        array $planeN,
        float $planeDist,
        array $ent,
        string $mode,
        ?VisData $vis,
        int $viewCluster,
        bool $usePvs,
        float $time,
        array $viewOrigin,
    ): void {
        $nlen = sqrt($planeN[0] ** 2 + $planeN[1] ** 2 + $planeN[2] ** 2) ?: 1.0;
        $n = [$planeN[0] / $nlen, $planeN[1] / $nlen, $planeN[2] / $nlen];

        $surfAxis0 = $n;
        $surfAxis1 = $this->perpendicularVector($n);
        $surfAxis2 = [
            $surfAxis0[1] * $surfAxis1[2] - $surfAxis0[2] * $surfAxis1[1],
            $surfAxis0[2] * $surfAxis1[0] - $surfAxis0[0] * $surfAxis1[2],
            $surfAxis0[0] * $surfAxis1[1] - $surfAxis0[1] * $surfAxis1[0],
        ];

        $isMirror = !empty($ent['mirror']);
        if ($isMirror) {
            $surfOrigin = [$n[0] * $planeDist, $n[1] * $planeDist, $n[2] * $planeDist];
            $camOrigin = $surfOrigin;
            $camAxis0 = [-$surfAxis0[0], -$surfAxis0[1], -$surfAxis0[2]];
            $camAxis1 = $surfAxis1;
            $camAxis2 = $surfAxis2;
            $pvsOrigin = $ent['origin'];
        } else {
            // Project portal entity onto plane (R_GetPortalOrientations remote)
            $d = $n[0] * $ent['origin'][0] + $n[1] * $ent['origin'][1] + $n[2] * $ent['origin'][2] - $planeDist;
            $surfOrigin = [
                $ent['origin'][0] - $d * $surfAxis0[0],
                $ent['origin'][1] - $d * $surfAxis0[1],
                $ent['origin'][2] - $d * $surfAxis0[2],
            ];
            $camOrigin = $ent['origin2'];
            // AxisCopy then negate axis[0] and axis[1]
            $camAxis0 = [-$ent['axis'][0][0], -$ent['axis'][0][1], -$ent['axis'][0][2]];
            $camAxis1 = [-$ent['axis'][1][0], -$ent['axis'][1][1], -$ent['axis'][1][2]];
            $camAxis2 = $ent['axis'][2];
            // R_GetPortalOrientations rotate block (oldframe=swing, frame=rotateSpeed, skinNum=roll°)
            $roll = (float) ($ent['roll'] ?? 0.0);
            $swing = !empty($ent['swing']);
            $rotateSpeed = (int) ($ent['rotateSpeed'] ?? 0);
            $deg = null;
            if ($swing && $rotateSpeed !== 0) {
                $deg = $this->time * (float) $rotateSpeed;
            } elseif ($swing) {
                // time is seconds; Q3 uses refdef.time(ms)*0.003 ≡ sec*3
                $deg = $roll + sin($this->time * 3.0) * 4.0;
            } elseif (abs($roll) > 0.01) {
                $deg = $roll;
            }
            if ($deg !== null) {
                $camAxis1 = $this->rotatePointAroundVector($camAxis0, $camAxis1, $deg);
                $camAxis2 = [
                    $camAxis0[1] * $camAxis1[2] - $camAxis0[2] * $camAxis1[1],
                    $camAxis0[2] * $camAxis1[0] - $camAxis0[0] * $camAxis1[2],
                    $camAxis0[0] * $camAxis1[1] - $camAxis0[1] * $camAxis1[0],
                ];
            }
            $pvsOrigin = $ent['origin2'];
        }

        [$right, $up] = $this->cameraRightUp();
        $forward = $this->cameraForward();
        $oldAxis = [
            $forward,
            [-$right[0], -$right[1], -$right[2]],
            $up,
        ];
        $surf = ['origin' => $surfOrigin, 'axis' => [$surfAxis0, $surfAxis1, $surfAxis2]];
        $cam = ['origin' => $camOrigin, 'axis' => [$camAxis0, $camAxis1, $camAxis2]];
        $newOrigin = $this->mirrorPoint($viewOrigin, $surf, $cam);
        $newAxis = [
            $this->mirrorVector($oldAxis[0], $surf, $cam),
            $this->mirrorVector($oldAxis[1], $surf, $cam),
            $this->mirrorVector($oldAxis[2], $surf, $cam),
        ];
        $portalN = [-$camAxis0[0], -$camAxis0[1], -$camAxis0[2]];
        $portalDist = $portalN[0] * $camOrigin[0] + $portalN[1] * $camOrigin[1] + $portalN[2] * $camOrigin[2];

        $nestedCluster = $viewCluster;
        if ($usePvs && $this->collision !== null) {
            $leaf = $this->collision->pointLeafnum($pvsOrigin);
            $nestedCluster = $this->collision->leafCluster($leaf);
        }

        $this->ffi->glClear(self::GL_DEPTH_BUFFER_BIT);
        $this->ffi->glPushMatrix();
        $this->loadMatrix(self::GL_MODELVIEW, $this->viewMatrixFromQ3Axes($newOrigin, $newAxis[0], $newAxis[1], $newAxis[2]));

        $planeEq = $this->ffi->new('double[4]');
        $planeEq[0] = $portalN[0];
        $planeEq[1] = $portalN[1];
        $planeEq[2] = $portalN[2];
        $planeEq[3] = -$portalDist;
        $this->ffi->glClipPlane(self::GL_CLIP_PLANE0, $planeEq);
        $this->ffi->glEnable(self::GL_CLIP_PLANE0);

        $savedOrigin = $this->viewOrigin;
        $this->viewOrigin = $newOrigin;
        $this->inPortalView = true;
        $this->isMirror = $isMirror;

        $this->drawWorldBatches($mode, $vis, $nestedCluster, $usePvs, $time, false);
        $this->drawSky($newOrigin, $mode);

        $this->isMirror = false;
        $this->inPortalView = false;
        $this->viewOrigin = $savedOrigin;
        $this->ffi->glDisable(self::GL_CLIP_PLANE0);
        $this->ffi->glPopMatrix();
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthMask(1);
    }

    /** @param list<float> $n @return list<float> */
    private function perpendicularVector(array $n): array
    {
        $minelem = 1.0;
        $pos = 0;
        for ($i = 0; $i < 3; $i++) {
            $a = abs($n[$i]);
            if ($a < $minelem) {
                $minelem = $a;
                $pos = $i;
            }
        }
        $temp = [0.0, 0.0, 0.0];
        $temp[$pos] = 1.0;
        // ProjectOntoPlane
        $d = $temp[0] * $n[0] + $temp[1] * $n[1] + $temp[2] * $n[2];
        $out = [$temp[0] - $d * $n[0], $temp[1] - $d * $n[1], $temp[2] - $d * $n[2]];
        $len = sqrt($out[0] ** 2 + $out[1] ** 2 + $out[2] ** 2) ?: 1.0;

        return [$out[0] / $len, $out[1] / $len, $out[2] / $len];
    }

    /**
     * RotatePointAroundVector (q_math.c) — Rodrigues form.
     * @param list<float> $dir unit axis
     * @param list<float> $point
     * @return list<float>
     */
    private function rotatePointAroundVector(array $dir, array $point, float $degrees): array
    {
        $rad = deg2rad($degrees);
        $c = cos($rad);
        $s = sin($rad);
        $t = 1.0 - $c;
        $x = $dir[0];
        $y = $dir[1];
        $z = $dir[2];
        $px = $point[0];
        $py = $point[1];
        $pz = $point[2];

        return [
            ($t * $x * $x + $c) * $px + ($t * $x * $y - $s * $z) * $py + ($t * $x * $z + $s * $y) * $pz,
            ($t * $x * $y + $s * $z) * $px + ($t * $y * $y + $c) * $py + ($t * $y * $z - $s * $x) * $pz,
            ($t * $x * $z - $s * $y) * $px + ($t * $y * $z + $s * $x) * $py + ($t * $z * $z + $c) * $pz,
        ];
    }

    /**
     * @param list<float> $in
     * @param array{origin: list<float>, axis: list<list<float>>} $surface
     * @param array{origin: list<float>, axis: list<list<float>>} $camera
     * @return list<float>
     */
    private function mirrorPoint(array $in, array $surface, array $camera): array
    {
        $local = [
            $in[0] - $surface['origin'][0],
            $in[1] - $surface['origin'][1],
            $in[2] - $surface['origin'][2],
        ];
        $transformed = [0.0, 0.0, 0.0];
        for ($i = 0; $i < 3; $i++) {
            $d = $local[0] * $surface['axis'][$i][0]
                + $local[1] * $surface['axis'][$i][1]
                + $local[2] * $surface['axis'][$i][2];
            $transformed[0] += $d * $camera['axis'][$i][0];
            $transformed[1] += $d * $camera['axis'][$i][1];
            $transformed[2] += $d * $camera['axis'][$i][2];
        }

        return [
            $transformed[0] + $camera['origin'][0],
            $transformed[1] + $camera['origin'][1],
            $transformed[2] + $camera['origin'][2],
        ];
    }

    /**
     * @param list<float> $in
     * @param array{origin: list<float>, axis: list<list<float>>} $surface
     * @param array{origin: list<float>, axis: list<list<float>>} $camera
     * @return list<float>
     */
    private function mirrorVector(array $in, array $surface, array $camera): array
    {
        $out = [0.0, 0.0, 0.0];
        for ($i = 0; $i < 3; $i++) {
            $d = $in[0] * $surface['axis'][$i][0]
                + $in[1] * $surface['axis'][$i][1]
                + $in[2] * $surface['axis'][$i][2];
            $out[0] += $d * $camera['axis'][$i][0];
            $out[1] += $d * $camera['axis'][$i][1];
            $out[2] += $d * $camera['axis'][$i][2];
        }

        return $out;
    }

    /**
     * Column-major look matrix from Q3 viewaxis (forward, left, up) + origin.
     * @param list<float> $origin
     * @param list<float> $forward
     * @param list<float> $left
     * @param list<float> $up
     * @return list<float>
     */
    private function viewMatrixFromQ3Axes(array $origin, array $forward, array $left, array $up): array
    {
        $rx = -$left[0];
        $ry = -$left[1];
        $rz = -$left[2];
        $ux = $up[0];
        $uy = $up[1];
        $uz = $up[2];
        $fx = $forward[0];
        $fy = $forward[1];
        $fz = $forward[2];
        $ex = $origin[0];
        $ey = $origin[1];
        $ez = $origin[2];

        return [
            $rx, $ux, -$fx, 0.0,
            $ry, $uy, -$fy, 0.0,
            $rz, $uz, -$fz, 0.0,
            -($rx * $ex + $ry * $ey + $rz * $ez),
            -($ux * $ex + $uy * $ey + $uz * $ez),
            -(-$fx * $ex - $fy * $ey - $fz * $ez),
            1.0,
        ];
    }

    /** @param array{vbo: CData, ibo: CData, count: int, shader: string, lm: int, useLm: bool, blend: bool, cluster: int, vertexBinary?: ?string, colorBinary?: ?string, vertexLight?: bool} $b */
    private function drawOpaqueBatch(array $b, string $mode): void
    {
        $this->beginOpaqueWorldState();
        // Prior blend/CPU-ST passes may leave client arrays / ARRAY_BUFFER=0
        $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
        $this->ffi->glDisableClientState(self::GL_NORMAL_ARRAY);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);

        $shWave = $this->shaderParser?->get($b['shader']);
        if ($shWave !== null && $shWave->hasWaveDeform() && !empty($b['vertexBinary'])) {
            $this->uploadDeformedVerts($b, $shWave, $this->time);
        }

        $diffTex = $this->diffuseTextureForBatch($b);
        $lmTex = $this->whiteTex;
        if ($b['useLm'] && $b['lm'] >= 0 && isset($this->lightmapTex[$b['lm']])) {
            $lmTex = $this->lightmapTex[$b['lm']];
        }

        if ($mode === 'no_textures' || $mode === 'lightmap_only') {
            $diffTex = $this->whiteTex;
        }
        if ($mode === 'no_lightmaps') {
            $lmTex = $this->whiteTex;
        }

        // LIGHTMAP_BY_VERTEX (MST_TRIANGLE_SOUP): CGEN_EXACT_VERTEX, no lightmap
        if (!empty($b['vertexLight']) && $mode === 'full') {
            $this->ffi->glActiveTexture(self::GL_TEXTURE1);
            $this->ffi->glDisable(self::GL_TEXTURE_2D);
            $this->ffi->glActiveTexture(self::GL_TEXTURE0);
            $this->ffi->glEnable(self::GL_TEXTURE_2D);
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $diffTex);
            $this->binds++;
            $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_MODULATE);
            $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
            $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
            $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
            $this->ffi->glVertexPointer(3, self::GL_FLOAT, self::STRIDE, null);
            $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
            $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
            $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(12));
            if (!empty($b['colorBinary'])) {
                $this->rebindBatchVbo($b);
                $this->bindVertexColors($b['colorBinary'], true);
            } else {
                $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
                $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
            }
            $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
            $this->drawCalls++;
            $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
            $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
            $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
            return;
        }

        if ($mode === 'lightmap_only') {
            $this->ffi->glActiveTexture(self::GL_TEXTURE0);
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $lmTex);
            $this->binds++;
            $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_REPLACE);
            $this->ffi->glActiveTexture(self::GL_TEXTURE1);
            $this->ffi->glDisable(self::GL_TEXTURE_2D);
            $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
            $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
            $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
            $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
            $this->ffi->glVertexPointer(3, self::GL_FLOAT, self::STRIDE, null);
            $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
            $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(20));
            $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
            $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
            $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
            $this->drawCalls++;
            return;
        }

        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $diffTex);
        $this->binds++;
        $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_REPLACE);

        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $lmTex);
        $this->binds++;
        $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_MODULATE);

        $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
        $this->ffi->glVertexPointer(3, self::GL_FLOAT, self::STRIDE, null);

        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(12));

        $this->ffi->glClientActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(20));

        $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
        $this->drawCalls++;

        $this->ffi->glClientActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
    }

    /** @param array{vbo: CData, ibo: CData, count: int, shader: string, lm: int, useLm: bool, vertexBinary?: ?string, autosprites?: list} $b */
    private function drawBlendBatch(array $b, float $time): void
    {
        $sh = $this->shaderParser?->get($b['shader']);
        $stages = $sh?->stages ?? [];
        if ($stages === []) {
            $this->drawOpaqueBatch($b, 'full');
            return;
        }

        // Cycle 4 reset opaque combine after sky; this path (lava / deferred LM /
        // fire pads / rgbGen vertex) must start from GLS_DEFAULT too — not leftover
        // blend, alpha test, depthFunc equal, or Color3f alpha from clouds.
        $this->beginOpaqueWorldState();
        $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);

        // AutospriteDeform — camera-facing quads (tr_shade_calc.c)
        if ($sh !== null && $sh->hasAutosprite() && !empty($b['autosprites'])) {
            $this->drawAutospriteBatch($b, $stages, $time);
            return;
        }
        if ($sh !== null && $sh->hasAutosprite2() && !empty($b['autosprite2s'])) {
            $this->drawAutosprite2Batch($b, $stages, $time);
            return;
        }

        // Q3: RB_DeformTessGeometry then ComputeColors/TexCoords on deformed xyz
        $cpuVerts = $b['vertexBinary'] ?? '';
        if ($sh !== null && $sh->hasWaveDeform() && $cpuVerts !== '') {
            $cpuVerts = $this->applyWaveDeforms($cpuVerts, $sh->waveDeforms(), $time);
            $this->uploadDeformedBinary($b, $cpuVerts);
        }

        $fullMultipass = $sh !== null && $sh->needsFullMultipass();

        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_MODULATE);

        $this->ffi->glEnableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glEnableClientState(self::GL_NORMAL_ARRAY);
        $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
        $this->ffi->glVertexPointer(3, self::GL_FLOAT, self::STRIDE, null);
        $this->ffi->glNormalPointer(self::GL_FLOAT, self::STRIDE, $this->offsetPtr(28));
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(12));

        foreach ($stages as $stage) {
            if ($stage->isLightmap) {
                // LIGHTMAP_BY_VERTEX: no lightmap pass (ParseTriSurf)
                if (!empty($b['vertexLight'])) {
                    continue;
                }
                if (!$fullMultipass) {
                    continue;
                }
                $this->applyStageState($stage, $time);
                if (!$stage->hasBlend) {
                    $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_REPLACE);
                } else {
                    $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_MODULATE);
                }
                $lm = ($b['lm'] >= 0 && isset($this->lightmapTex[$b['lm']]))
                    ? $this->lightmapTex[$b['lm']] : $this->whiteTex;
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $lm);
                $this->binds++;
                $this->disableTexGen();
                $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
                $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
                // ComputeTexCoords: $lightmap may still override tcGen (comp3, teslacoil, banners)
                if ($stage->tcGen === 'environment' && $cpuVerts !== '' && $this->viewOrigin !== null) {
                    $this->bindEnvironmentTexCoords($cpuVerts, $this->viewOrigin, $stage, $time);
                } else {
                    $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(20));
                }
                $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
                $this->drawCalls++;
                // Always rebind before offsetPtr — env path left ARRAY_BUFFER=0
                $this->ensureBatchArrayBuffer($b);
                $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(12));
                $this->ffi->glMatrixMode(0x1702);
                $this->ffi->glLoadIdentity();
                $this->ffi->glMatrixMode(self::GL_MODELVIEW);
                $this->ffi->glTexEnvi(self::GL_TEXTURE_ENV, self::GL_TEXTURE_ENV_MODE, self::GL_MODULATE);
                continue;
            }
            // Skip lightmap filter when opaque already combined (non-multipass)
            if (!$fullMultipass
                && $stage->hasBlend
                && $stage->blendSrc === ShaderParser::GL_DST_COLOR
                && $stage->blendDst === ShaderParser::GL_ZERO) {
                continue;
            }
            // Non-blend base already drawn as opaque — unless multipass / env / alpha punch
            if (!$stage->hasBlend && $b['useLm'] && !$fullMultipass
                && $stage->tcGen !== 'environment' && $stage->alphaFunc === '') {
                continue;
            }
            $this->applyStageState($stage, $time);
            // Per-stage texenv like the $lightmap branch: unblended opaque uses REPLACE
            // unless rgbGen needs the primary color (identityLighting / vertex / wave).
            $rgb = strtolower($stage->rgbGen);
            $needsPrimary = $stage->hasBlend
                || $rgb === 'identitylighting'
                || $rgb === 'wave'
                || $this->isRgbGenVertex($stage->rgbGen)
                || (!empty($b['vertexLight']) && ($rgb === 'identity' || $rgb === ''));
            $this->ffi->glTexEnvi(
                self::GL_TEXTURE_ENV,
                self::GL_TEXTURE_ENV_MODE,
                $needsPrimary ? self::GL_MODULATE : self::GL_REPLACE,
            );
            $tex = $this->stageTexture($stage, $b['shader']);
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            $this->disableTexGen();
            // VBO attrs first; client color arrays last with ARRAY_BUFFER=0 (macOS GL quirk)
            $this->rebindBatchVbo($b);
            if ($stage->alphaGen === 'lightingspecular' && $cpuVerts !== '' && $this->viewOrigin !== null) {
                $this->bindSpecularColors($cpuVerts, $this->viewOrigin);
            } elseif ($stage->alphaGen === 'portal' && $cpuVerts !== '' && $this->viewOrigin !== null) {
                $range = ($sh !== null && $sh->portalRange > 0.0) ? $sh->portalRange : $stage->portalRange;
                $rgb = 1.0;
                if ($stage->rgbGen === 'identitylighting' || $stage->rgbGen === 'identityLighting') {
                    $rgb = $this->lighting->identityLight;
                }
                $this->bindPortalColors($cpuVerts, $this->viewOrigin, $range > 0.0 ? $range : 256.0, $rgb);
            } elseif ($this->isRgbGenVertex($stage->rgbGen) && !empty($b['colorBinary'])) {
                $exact = strtolower($stage->rgbGen) === 'exactvertex';
                $this->bindVertexColors($b['colorBinary'], $exact);
            } elseif (!empty($b['vertexLight']) && !empty($b['colorBinary'])
                && ($stage->rgbGen === 'identity' || $stage->rgbGen === '')) {
                // Default BY_VERTEX collapse: exact vertex colors on diffuse stages
                $this->bindVertexColors($b['colorBinary'], true);
            } else {
                $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
            }
            if ($stage->tcGen === 'environment' && $cpuVerts !== '' && $this->viewOrigin !== null) {
                // ComputeTexCoords: env then tcMods (incl. turb — not texture-matrix approx)
                $this->bindEnvironmentTexCoords($cpuVerts, $this->viewOrigin, $stage, $time);
            } elseif ($this->stageHasTcModTurb($stage) && $cpuVerts !== '') {
                $this->bindTurbStageTexCoords($cpuVerts, $stage, $time);
            } else {
                if ($stage->tcGen === 'environment') {
                    // Fallback only when verts unavailable
                    $this->enableSphereMap();
                }
                $this->applyTcMod($stage, $time);
            }
            $this->ffi->glDrawElements(self::GL_TRIANGLES, $b['count'], self::GL_UNSIGNED_INT, null);
            $this->drawCalls++;
            // Restore VBO texcoords after client CPU ST / keep ARRAY_BUFFER coherent
            $this->ensureBatchArrayBuffer($b);
            $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
            $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(12));
            $this->ffi->glMatrixMode(0x1702); // GL_TEXTURE
            $this->ffi->glLoadIdentity();
            $this->ffi->glMatrixMode(self::GL_MODELVIEW);
            $this->disableTexGen();
            $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
        }

        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glDisableClientState(self::GL_NORMAL_ARRAY);
        $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
    }

    /**
     * AutospriteDeform: rebuild quads as camera-facing sprites (tr_shade_calc.c).
     * @param array{autosprites: list<array{mid: list<float>, radius: float}>, shader?: string} $b
     * @param list<ShaderStage> $stages
     */
    private function drawAutospriteBatch(array $b, array $stages, float $time): void
    {
        [$right, $up] = $this->cameraRightUp();
        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glDisableClientState(self::GL_NORMAL_ARRAY);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
        $this->disableTexGen();

        foreach ($stages as $stage) {
            if ($stage->isLightmap || $stage->map === '' || str_starts_with($stage->map, '$')) {
                continue;
            }
            $tex = $this->stageTexture($stage, $b['shader'] ?? '');
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            $this->applyStageState($stage, $time);
            $this->applyTcMod($stage, $time);
            foreach ($b['autosprites'] as $sp) {
                $mid = $sp['mid'];
                $r = $sp['radius'];
                // Q3 leftDir = viewaxis[1] = −camera right (AnglesToAxis)
                $left = [-$right[0] * $r, -$right[1] * $r, -$right[2] * $r];
                $upv = [$up[0] * $r, $up[1] * $r, $up[2] * $r];
                $this->emitQuadStamp($mid, $left, $upv);
            }
            $this->ffi->glMatrixMode(0x1702);
            $this->ffi->glLoadIdentity();
            $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        }
        $this->ffi->glEnable(self::GL_CULL_FACE);
    }

    /**
     * RB_AddQuadStamp / RB_AddQuadStampExt (s1,t1,s2,t2 = 0,0,1,1).
     * $left / $up already scaled half-extents (Q3 viewaxis[1]/cameraRight).
     * @param list<float> $origin
     * @param list<float> $left
     * @param list<float> $up
     */
    private function emitQuadStamp(array $origin, array $left, array $up): void
    {
        $this->ffi->glBegin(self::GL_QUADS);
        $this->ffi->glTexCoord2f(0, 0);
        $this->ffi->glVertex3f(
            $origin[0] + $left[0] + $up[0],
            $origin[1] + $left[1] + $up[1],
            $origin[2] + $left[2] + $up[2],
        );
        $this->ffi->glTexCoord2f(1, 0);
        $this->ffi->glVertex3f(
            $origin[0] - $left[0] + $up[0],
            $origin[1] - $left[1] + $up[1],
            $origin[2] - $left[2] + $up[2],
        );
        $this->ffi->glTexCoord2f(1, 1);
        $this->ffi->glVertex3f(
            $origin[0] - $left[0] - $up[0],
            $origin[1] - $left[1] - $up[1],
            $origin[2] - $left[2] - $up[2],
        );
        $this->ffi->glTexCoord2f(0, 1);
        $this->ffi->glVertex3f(
            $origin[0] + $left[0] - $up[0],
            $origin[1] + $left[1] - $up[1],
            $origin[2] + $left[2] - $up[2],
        );
        $this->ffi->glEnd();
        $this->drawCalls++;
    }

    /**
     * Autosprite2Deform: pivot rectangular quads on long axis (tr_shade_calc.c).
     * @param array{autosprite2s: list<array{verts: list<list<float>>, sts: list<list<float>>, shortEdges: list<array{a:int,b:int,len2:float,flip:bool}>}>, shader?: string} $b
     * @param list<ShaderStage> $stages
     */
    private function drawAutosprite2Batch(array $b, array $stages, float $time): void
    {
        $forward = $this->cameraForward();
        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glDisableClientState(self::GL_NORMAL_ARRAY);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
        $this->disableTexGen();

        foreach ($stages as $stage) {
            if ($stage->isLightmap || $stage->map === '' || str_starts_with($stage->map, '$')) {
                continue;
            }
            $tex = $this->stageTexture($stage, $b['shader'] ?? '');
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            $this->applyStageState($stage, $time);
            $this->applyTcMod($stage, $time);
            foreach ($b['autosprite2s'] as $sp) {
                $xyz = $sp['verts'];
                $sts = $sp['sts'];
                $edges = $sp['shortEdges'];
                $mid = [
                    [
                        0.5 * ($xyz[$edges[0]['a']][0] + $xyz[$edges[0]['b']][0]),
                        0.5 * ($xyz[$edges[0]['a']][1] + $xyz[$edges[0]['b']][1]),
                        0.5 * ($xyz[$edges[0]['a']][2] + $xyz[$edges[0]['b']][2]),
                    ],
                    [
                        0.5 * ($xyz[$edges[1]['a']][0] + $xyz[$edges[1]['b']][0]),
                        0.5 * ($xyz[$edges[1]['a']][1] + $xyz[$edges[1]['b']][1]),
                        0.5 * ($xyz[$edges[1]['a']][2] + $xyz[$edges[1]['b']][2]),
                    ],
                ];
                $major = [
                    $mid[1][0] - $mid[0][0],
                    $mid[1][1] - $mid[0][1],
                    $mid[1][2] - $mid[0][2],
                ];
                // minor = normalize(cross(major, forward))
                $minor = [
                    $major[1] * $forward[2] - $major[2] * $forward[1],
                    $major[2] * $forward[0] - $major[0] * $forward[2],
                    $major[0] * $forward[1] - $major[1] * $forward[0],
                ];
                $mlen = sqrt($minor[0] ** 2 + $minor[1] ** 2 + $minor[2] ** 2);
                if ($mlen < 1e-8) {
                    continue;
                }
                $minor = [$minor[0] / $mlen, $minor[1] / $mlen, $minor[2] / $mlen];

                $out = $xyz;
                for ($j = 0; $j < 2; $j++) {
                    $e = $edges[$j];
                    $half = 0.5 * sqrt($e['len2']);
                    $sign = $e['flip'] ? 1.0 : -1.0;
                    $a = $e['a'];
                    $bb = $e['b'];
                    $out[$a] = [
                        $mid[$j][0] + $sign * $half * $minor[0],
                        $mid[$j][1] + $sign * $half * $minor[1],
                        $mid[$j][2] + $sign * $half * $minor[2],
                    ];
                    $out[$bb] = [
                        $mid[$j][0] - $sign * $half * $minor[0],
                        $mid[$j][1] - $sign * $half * $minor[1],
                        $mid[$j][2] - $sign * $half * $minor[2],
                    ];
                }
                $this->ffi->glBegin(self::GL_QUADS);
                for ($vi = 0; $vi < 4; $vi++) {
                    $this->ffi->glTexCoord2f($sts[$vi][0], $sts[$vi][1]);
                    $this->ffi->glVertex3f($out[$vi][0], $out[$vi][1], $out[$vi][2]);
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
            }
            $this->ffi->glMatrixMode(0x1702);
            $this->ffi->glLoadIdentity();
            $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        }
        $this->ffi->glEnable(self::GL_CULL_FACE);
    }

    /**
     * RB_CalcDeformVertexes → stream deformed XYZ into batch VBO (keep ST/LM/N).
     * @param array{vbo: CData, vertexBinary: string} $b
     */
    private function uploadDeformedVerts(array $b, \Quake3\Render\Q3Shader $sh, float $time): void
    {
        $this->uploadDeformedBinary($b, $this->applyWaveDeforms($b['vertexBinary'], $sh->waveDeforms(), $time));
    }

    /** @param array{vbo: CData} $b */
    private function uploadDeformedBinary(array $b, string $deformed): void
    {
        $len = strlen($deformed);
        $buf = $this->ffi->new('unsigned char[' . $len . ']');
        FFI::memcpy($buf, $deformed, $len);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
        $this->ffi->glBufferData(self::GL_ARRAY_BUFFER, $len, $buf, self::GL_STREAM_DRAW);
    }

    /**
     * @param list<array{type: string, spread: float, func: string, base: float, amp: float, phase: float, freq: float}> $waves
     */
    private function applyWaveDeforms(string $vertexBinary, array $waves, float $time): string
    {
        if ($waves === []) {
            return $vertexBinary;
        }
        $vertCount = intdiv(strlen($vertexBinary), self::STRIDE);
        $out = $vertexBinary;
        foreach ($waves as $ds) {
            $spread = (float) ($ds['spread'] ?? 0.01);
            $func = (string) ($ds['func'] ?? 'sin');
            $base = (float) ($ds['base'] ?? 0.0);
            $amp = (float) ($ds['amp'] ?? 0.0);
            $phase = (float) ($ds['phase'] ?? 0.0);
            $freq = (float) ($ds['freq'] ?? 0.0);
            for ($i = 0; $i < $vertCount; $i++) {
                $o = $i * self::STRIDE;
                $xyz = unpack('g3', substr($out, $o, 12));
                $nml = unpack('g3', substr($out, $o + 28, 12));
                if ($xyz === false || $nml === false) {
                    continue;
                }
                $x = $xyz[1];
                $y = $xyz[2];
                $z = $xyz[3];
                $nx = $nml[1];
                $ny = $nml[2];
                $nz = $nml[3];
                if ($freq == 0.0) {
                    $scale = $this->evalWaveForm($func, $base, $amp, $phase, $freq, $time);
                } else {
                    $off = ($x + $y + $z) * $spread;
                    $scale = $this->evalWaveForm($func, $base, $amp, $phase + $off, $freq, $time);
                }
                $x += $nx * $scale;
                $y += $ny * $scale;
                $z += $nz * $scale;
                $out = substr_replace($out, pack('g3', $x, $y, $z), $o, 12);
            }
        }
        return $out;
    }

    private function isRgbGenVertex(string $rgbGen): bool
    {
        $g = strtolower($rgbGen);
        return $g === 'vertex' || $g === 'exactvertex';
    }

    /**
     * CGEN_VERTEX / CGEN_EXACT_VERTEX (tr_shade.c ComputeColors).
     * vertex scales RGB by identityLight; exactvertex copies as-is.
     */
    private function bindVertexColors(string $colorBinary, bool $exact): void
    {
        $len = strlen($colorBinary);
        if ($len < 4 || ($len % 4) !== 0) {
            return;
        }
        $colors = $colorBinary;
        if (!$exact && $this->lighting->identityLight < 0.999) {
            $il = $this->lighting->identityLight;
            $out = '';
            for ($i = 0; $i < $len; $i += 4) {
                $out .= chr((int) (ord($colorBinary[$i]) * $il))
                    . chr((int) (ord($colorBinary[$i + 1]) * $il))
                    . chr((int) (ord($colorBinary[$i + 2]) * $il))
                    . $colorBinary[$i + 3];
            }
            $colors = $out;
        }
        $this->specularColorBuf = $this->ffi->new('unsigned char[' . $len . ']');
        FFI::memcpy($this->specularColorBuf, $colors, $len);
        $this->ffi->glEnableClientState(self::GL_COLOR_ARRAY);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, 0);
        $this->ffi->glColorPointer(4, self::GL_UNSIGNED_BYTE, 0, $this->specularColorBuf);
    }

    /**
     * AGEN_PORTAL (tr_shade.c): alpha = clamp(dist(view, vert) / portalRange).
     * RGB from identityLighting (or white).
     * @param list<float> $viewOrigin
     */
    private function bindPortalColors(string $vertexBinary, array $viewOrigin, float $portalRange, float $rgb): void
    {
        $vertCount = intdiv(strlen($vertexBinary), self::STRIDE);
        if ($vertCount <= 0 || $portalRange <= 0.0) {
            return;
        }
        $rb = (int) max(0, min(255, (int) round($rgb * 255.0)));
        $colors = '';
        $vx = $viewOrigin[0];
        $vy = $viewOrigin[1];
        $vz = $viewOrigin[2];
        for ($i = 0; $i < $vertCount; $i++) {
            $o = $i * self::STRIDE;
            $xyz = unpack('g3', substr($vertexBinary, $o, 12));
            $dx = $xyz[1] - $vx;
            $dy = $xyz[2] - $vy;
            $dz = $xyz[3] - $vz;
            $len = sqrt($dx * $dx + $dy * $dy + $dz * $dz) / $portalRange;
            if ($len <= 0.0) {
                $a = 0;
            } elseif ($len >= 1.0) {
                $a = 255;
            } else {
                $a = (int) ($len * 255.0);
            }
            $colors .= chr($rb) . chr($rb) . chr($rb) . chr($a);
        }
        $lenBytes = strlen($colors);
        $this->specularColorBuf = $this->ffi->new('unsigned char[' . $lenBytes . ']');
        FFI::memcpy($this->specularColorBuf, $colors, $lenBytes);
        $this->ffi->glEnableClientState(self::GL_COLOR_ARRAY);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, 0);
        $this->ffi->glColorPointer(4, self::GL_UNSIGNED_BYTE, 0, $this->specularColorBuf);
    }

    /**
     * RB_CalcSpecularAlpha → per-vertex RGBA (RGB=255, A=specular).
     * @param list<float> $viewOrigin
     */
    private function bindSpecularColors(string $vertexBinary, array $viewOrigin): void
    {
        $vertCount = intdiv(strlen($vertexBinary), self::STRIDE);
        if ($vertCount <= 0) {
            return;
        }
        $colors = '';
        $lx = self::SPECULAR_LIGHT_ORIGIN[0];
        $ly = self::SPECULAR_LIGHT_ORIGIN[1];
        $lz = self::SPECULAR_LIGHT_ORIGIN[2];
        $vx = $viewOrigin[0];
        $vy = $viewOrigin[1];
        $vz = $viewOrigin[2];
        for ($i = 0; $i < $vertCount; $i++) {
            $o = $i * self::STRIDE;
            $xyz = unpack('g3', substr($vertexBinary, $o, 12));
            $nml = unpack('g3', substr($vertexBinary, $o + 28, 12));
            $px = $xyz[1];
            $py = $xyz[2];
            $pz = $xyz[3];
            $nx = $nml[1];
            $ny = $nml[2];
            $nz = $nml[3];

            $ldx = $lx - $px;
            $ldy = $ly - $py;
            $ldz = $lz - $pz;
            $llen = sqrt($ldx * $ldx + $ldy * $ldy + $ldz * $ldz) ?: 1.0;
            $ldx /= $llen;
            $ldy /= $llen;
            $ldz /= $llen;

            $d = $nx * $ldx + $ny * $ldy + $nz * $ldz;
            $rx = $nx * 2.0 * $d - $ldx;
            $ry = $ny * 2.0 * $d - $ldy;
            $rz = $nz * 2.0 * $d - $ldz;

            $vdx = $vx - $px;
            $vdy = $vy - $py;
            $vdz = $vz - $pz;
            $vlen2 = $vdx * $vdx + $vdy * $vdy + $vdz * $vdz;
            $ilength = $vlen2 > 1e-12 ? 1.0 / sqrt($vlen2) : 0.0;
            $l = ($rx * $vdx + $ry * $vdy + $rz * $vdz) * $ilength;
            if ($l < 0.0) {
                $b = 0;
            } else {
                $l = $l * $l;
                $l = $l * $l;
                $b = (int) ($l * 255.0);
                if ($b > 255) {
                    $b = 255;
                }
            }
            $colors .= "\xff\xff\xff" . chr($b);
        }
        $len = strlen($colors);
        $this->specularColorBuf = $this->ffi->new('unsigned char[' . $len . ']');
        FFI::memcpy($this->specularColorBuf, $colors, $len);
        $this->ffi->glEnableClientState(self::GL_COLOR_ARRAY);
        // Unbind ARRAY_BUFFER so color pointer uses client memory
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, 0);
        $this->ffi->glColorPointer(4, self::GL_UNSIGNED_BYTE, 0, $this->specularColorBuf);
    }

    /** Re-bind batch VBO after client-memory color pointer setup. */
    private function rebindBatchVbo(array $b): void
    {
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
        $this->ffi->glVertexPointer(3, self::GL_FLOAT, self::STRIDE, null);
        $this->ffi->glNormalPointer(self::GL_FLOAT, self::STRIDE, $this->offsetPtr(28));
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, self::STRIDE, $this->offsetPtr(12));
    }

    private function enableSphereMap(): void
    {
        // Fallback only — preferred path is bindEnvironmentTexCoords (RB_CalcEnvironmentTexCoords)
        $this->ffi->glTexGeni(self::GL_S, self::GL_TEXTURE_GEN_MODE, self::GL_SPHERE_MAP);
        $this->ffi->glTexGeni(self::GL_T, self::GL_TEXTURE_GEN_MODE, self::GL_SPHERE_MAP);
        $this->ffi->glEnable(self::GL_TEXTURE_GEN_S);
        $this->ffi->glEnable(self::GL_TEXTURE_GEN_T);
    }

    private function disableTexGen(): void
    {
        $this->ffi->glDisable(self::GL_TEXTURE_GEN_S);
        $this->ffi->glDisable(self::GL_TEXTURE_GEN_T);
    }

    /**
     * One-vertex RB_CalcEnvironmentTexCoords (+ optional tcMods).
     * @param list<float> $viewOrigin
     * @return array{0: float, 1: float}
     */
    private function environmentStForVertex(
        float $px,
        float $py,
        float $pz,
        float $nx,
        float $ny,
        float $nz,
        array $viewOrigin,
        ?ShaderStage $stage = null,
        float $time = 0.0,
    ): array {
        $vx = $viewOrigin[0];
        $vy = $viewOrigin[1];
        $vz = $viewOrigin[2];
        $viewerX = $vx - $px;
        $viewerY = $vy - $py;
        $viewerZ = $vz - $pz;
        $vlen2 = $viewerX * $viewerX + $viewerY * $viewerY + $viewerZ * $viewerZ;
        $ilength = $vlen2 > 1e-12 ? 1.0 / sqrt($vlen2) : 0.0;
        $viewerX *= $ilength;
        $viewerY *= $ilength;
        $viewerZ *= $ilength;

        $d = $nx * $viewerX + $ny * $viewerY + $nz * $viewerZ;
        $refY = $ny * 2.0 * $d - $viewerY;
        $refZ = $nz * 2.0 * $d - $viewerZ;
        $s = 0.5 + $refY * 0.5;
        $t = 0.5 - $refZ * 0.5;

        if ($stage !== null && $stage->tcMods !== []) {
            foreach ($stage->tcMods as $mod) {
                $type = $mod['type'] ?? '';
                $a = $mod['args'] ?? [];
                if ($type === 'scroll' && count($a) >= 2) {
                    $s += $this->scrollFrac($a[0] * $time);
                    $t += $this->scrollFrac($a[1] * $time);
                } elseif ($type === 'scale' && count($a) >= 2) {
                    $s *= $a[0];
                    $t *= $a[1];
                } elseif ($type === 'rotate' && count($a) >= 1) {
                    // RB_CalcRotateTexCoords: degs = -degsPerSecond * time
                    $ang = deg2rad(-$a[0] * $time);
                    $cs = cos($ang);
                    $sn = sin($ang);
                    $s2 = $s - 0.5;
                    $t2 = $t - 0.5;
                    $s = $s2 * $cs - $t2 * $sn + 0.5;
                    $t = $s2 * $sn + $t2 * $cs + 0.5;
                } elseif ($type === 'stretch' && count($a) >= 5) {
                    $func = is_string($a[0]) ? $a[0] : 'sin';
                    $wave = $this->evalWaveForm($func, (float) $a[1], (float) $a[2], (float) $a[3], (float) $a[4], $time);
                    if (abs($wave) < 1e-4) {
                        $wave = 1e-4;
                    }
                    $p = 1.0 / $wave;
                    $s = ($s - 0.5) * $p + 0.5;
                    $t = ($t - 0.5) * $p + 0.5;
                } elseif ($type === 'turb' && count($a) >= 4) {
                    $amp = (float) $a[1];
                    $now = (float) $a[2] + $time * (float) $a[3];
                    $s += sin(((($px + $pz) * (1.0 / 128.0) * 0.125) + $now) * 2.0 * M_PI) * $amp;
                    $t += sin((($py * (1.0 / 128.0) * 0.125) + $now) * 2.0 * M_PI) * $amp;
                }
            }
        }

        return [$s, $t];
    }

    /**
     * RB_CalcEnvironmentTexCoords — Q3 reflection UVs (not GL_SPHERE_MAP).
     * When $stage is set, applies tcMods in CPU order (tr_shade.c ComputeTexCoords).
     * @param list<float> $viewOrigin
     */
    private function bindEnvironmentTexCoords(
        string $vertexBinary,
        array $viewOrigin,
        ?ShaderStage $stage = null,
        float $time = 0.0,
    ): void {
        $vertCount = intdiv(strlen($vertexBinary), self::STRIDE);
        if ($vertCount <= 0) {
            return;
        }
        $sts = '';
        for ($i = 0; $i < $vertCount; $i++) {
            $o = $i * self::STRIDE;
            $xyz = unpack('g3', substr($vertexBinary, $o, 12));
            $nml = unpack('g3', substr($vertexBinary, $o + 28, 12));
            [$s, $t] = $this->environmentStForVertex(
                $xyz[1],
                $xyz[2],
                $xyz[3],
                $nml[1],
                $nml[2],
                $nml[3],
                $viewOrigin,
                $stage,
                $time,
            );
            $sts .= pack('g2', $s, $t);
        }
        $len = strlen($sts);
        $this->envTexCoordBuf = $this->ffi->new('unsigned char[' . $len . ']');
        FFI::memcpy($this->envTexCoordBuf, $sts, $len);
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, 0);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, 0, $this->envTexCoordBuf);
    }

    /**
     * First color-map stage image (RB_IterateStagesGeneric), not shader name.
     * Scripted *_trans / remap shaders would otherwise bind white or the wrong TGA.
     *
     * @param array<string, mixed> $b
     */
    private function diffuseTextureForBatch(array $b): int
    {
        $shaderName = (string) ($b['shader'] ?? '');
        if ($this->shaderParser !== null && $shaderName !== '') {
            $sh = $this->shaderParser->get($shaderName);
            foreach ($sh->stages as $st) {
                if ($st->isLightmap || $st->map === '' || str_starts_with($st->map, '$')) {
                    continue;
                }
                return $this->stageTexture($st, $shaderName);
            }
        }

        return $this->textureCache[$shaderName] ?? $this->whiteTex;
    }

    private function stageTexture(ShaderStage $stage, string $shaderName, ?float $time = null): int
    {
        $t = $time ?? $this->time;
        if ($stage->animFrames !== [] && $stage->animFrequency > 0) {
            // tr_shade.c: imageAnimationSpeed * shaderTime (not wall clock)
            $idx = ((int) floor($t * $stage->animFrequency)) % count($stage->animFrames);
            if ($idx < 0) {
                $idx += count($stage->animFrames);
            }
            $map = $stage->animFrames[$idx];
            return $this->textureCache[$map] ?? $this->textureCache[$shaderName] ?? $this->whiteTex;
        }
        if ($stage->map !== '' && isset($this->textureCache[$stage->map])) {
            return $this->textureCache[$stage->map];
        }
        return $this->textureCache[$shaderName] ?? $this->whiteTex;
    }

    private function applyStageState(ShaderStage $stage, float $time): void
    {
        if ($stage->hasBlend) {
            $this->ffi->glEnable(self::GL_BLEND);
            // GL_ZERO is 0 — never use ?: defaults (breaks filter DST_COLOR/ZERO, mirror ZERO/…)
            $this->ffi->glBlendFunc($stage->blendSrc, $stage->blendDst);
            // blendFunc clears depth mask unless depthwrite (tr_shader.c)
            $this->ffi->glDepthMask($stage->depthWrite ? 1 : 0);
        } else {
            $this->ffi->glDisable(self::GL_BLEND);
            // Non-blend defaults GLS_DEPTHMASK_TRUE (incl. alphaFunc without explicit depthwrite)
            $this->ffi->glDepthMask(1);
        }
        if ($stage->alphaFunc !== '') {
            $this->ffi->glEnable(self::GL_ALPHA_TEST);
            match (strtoupper($stage->alphaFunc)) {
                'GT0' => $this->ffi->glAlphaFunc(self::GL_GREATER, 0.0),
                'LT128' => $this->ffi->glAlphaFunc(self::GL_LESS, 0.5),
                'GE128' => $this->ffi->glAlphaFunc(self::GL_GEQUAL, 0.5),
                default => $this->ffi->glDisable(self::GL_ALPHA_TEST),
            };
        } else {
            $this->ffi->glDisable(self::GL_ALPHA_TEST);
        }
        if ($stage->depthFunc === 'equal') {
            $this->ffi->glDepthFunc(self::GL_EQUAL);
        } else {
            $this->ffi->glDepthFunc(self::GL_LEQUAL);
        }
        // Always set A — glColor3f preserves stale alpha and breaks SRC_ALPHA blends
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
        if ($stage->rgbGen === 'wave') {
            $glow = $this->evalWaveForm(
                $stage->rgbWaveFunc,
                $stage->rgbWaveBase,
                $stage->rgbWaveAmp,
                $stage->rgbWavePhase,
                $stage->rgbWaveFreq,
                $time,
            ) * $this->lighting->identityLight;
            $glow = max(0.0, min(1.0, $glow));
            $this->ffi->glColor4f($glow, $glow, $glow, 1.0);
        } elseif ($stage->rgbGen === 'identitylighting' || $stage->rgbGen === 'identityLighting') {
            $il = $this->lighting->identityLight;
            $this->ffi->glColor4f($il, $il, $il, 1.0);
        }
    }

    /**
     * After client-memory ColorPointer / TexCoordPointer, ARRAY_BUFFER is 0.
     * Re-bind VBO attrs before any further offsetPtr() pointer setup.
     */
    private function ensureBatchArrayBuffer(array $b): void
    {
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, $b['vbo']->cdata);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, $b['ibo']->cdata);
    }

    /** EvalWaveForm / WAVEVALUE subset (sin/triangle/square/sawtooth). */
    private function evalWaveForm(
        string $func,
        float $base,
        float $amp,
        float $phase,
        float $freq,
        float $time,
    ): float {
        $x = $phase + $time * $freq;
        $frac = $x - floor($x);
        $y = match ($func) {
            'sin' => sin($frac * 2.0 * M_PI),
            'square' => ($frac < 0.5) ? 1.0 : -1.0,
            'triangle' => ($frac < 0.5) ? ($frac * 4.0 - 1.0) : (3.0 - $frac * 4.0),
            'sawtooth' => $frac,
            'inversesawtooth' => 1.0 - $frac,
            default => sin($frac * 2.0 * M_PI),
        };
        return $base + $y * $amp;
    }

    /** RB_CalcScrollTexCoords wrap: x - floor(x) ∈ [0,1). PHP fmod is signed. */
    private function scrollFrac(float $x): float
    {
        return $x - floor($x);
    }

    private function stageHasTcModTurb(ShaderStage $stage): bool
    {
        foreach ($stage->tcMods as $m) {
            if (($m['type'] ?? '') === 'turb') {
                return true;
            }
        }
        return false;
    }

    /**
     * Apply tcMods in CPU order when turb is present (tr_shade.c ComputeTexCoords).
     * Turb uses RB_CalcTurbulentTexCoords (xyz-dependent sin offsets).
     */
    private function bindTurbStageTexCoords(string $vertexBinary, ShaderStage $stage, float $time): void
    {
        $vertCount = intdiv(strlen($vertexBinary), self::STRIDE);
        if ($vertCount <= 0) {
            return;
        }
        $sts = '';
        for ($i = 0; $i < $vertCount; $i++) {
            $o = $i * self::STRIDE;
            $xyz = unpack('g3', substr($vertexBinary, $o, 12));
            $st = unpack('g2', substr($vertexBinary, $o + 12, 8));
            $s = $st[1];
            $t = $st[2];
            $x = $xyz[1];
            $y = $xyz[2];
            $z = $xyz[3];
            foreach ($stage->tcMods as $mod) {
                $type = $mod['type'] ?? '';
                $a = $mod['args'] ?? [];
                if ($type === 'scroll' && count($a) >= 2) {
                    $s += $this->scrollFrac($a[0] * $time);
                    $t += $this->scrollFrac($a[1] * $time);
                } elseif ($type === 'scale' && count($a) >= 2) {
                    $s *= $a[0];
                    $t *= $a[1];
                } elseif ($type === 'rotate' && count($a) >= 1) {
                    // RB_CalcRotateTexCoords: degs = -degsPerSecond * time
                    $ang = deg2rad(-$a[0] * $time);
                    $cs = cos($ang);
                    $sn = sin($ang);
                    $s2 = $s - 0.5;
                    $t2 = $t - 0.5;
                    $s = $s2 * $cs - $t2 * $sn + 0.5;
                    $t = $s2 * $sn + $t2 * $cs + 0.5;
                } elseif ($type === 'stretch' && count($a) >= 5) {
                    $func = is_string($a[0]) ? $a[0] : 'sin';
                    $wave = $this->evalWaveForm($func, (float) $a[1], (float) $a[2], (float) $a[3], (float) $a[4], $time);
                    if (abs($wave) < 1e-4) {
                        $wave = 1e-4;
                    }
                    $p = 1.0 / $wave;
                    $s = ($s - 0.5) * $p + 0.5;
                    $t = ($t - 0.5) * $p + 0.5;
                } elseif ($type === 'turb' && count($a) >= 4) {
                    // args: base amp phase freq — base unused (id software)
                    $amp = (float) $a[1];
                    $now = (float) $a[2] + $time * (float) $a[3];
                    $s += sin(((($x + $z) * (1.0 / 128.0) * 0.125) + $now) * 2.0 * M_PI) * $amp;
                    $t += sin((($y * (1.0 / 128.0) * 0.125) + $now) * 2.0 * M_PI) * $amp;
                }
            }
            $sts .= pack('g2', $s, $t);
        }
        $len = strlen($sts);
        $this->turbTexCoordBuf = $this->ffi->new('unsigned char[' . $len . ']');
        FFI::memcpy($this->turbTexCoordBuf, $sts, $len);
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, 0);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glTexCoordPointer(2, self::GL_FLOAT, 0, $this->turbTexCoordBuf);
    }

    private function applyTcMod(ShaderStage $stage, float $time): void
    {
        if ($stage->tcMods === []) {
            return;
        }
        $this->ffi->glMatrixMode(0x1702); // GL_TEXTURE
        $this->ffi->glLoadIdentity();
        // OpenGL post-multiplies (v' = M_opN * … * M_op1 * v). Q3/webgl apply
        // tcMods in shader order on ST — reverse here so first mod runs first.
        foreach (array_reverse($stage->tcMods) as $mod) {
            $t = $mod['type'];
            $a = $mod['args'];
            if ($t === 'scroll' && count($a) >= 2) {
                $this->ffi->glTranslatef($this->scrollFrac($a[0] * $time), $this->scrollFrac($a[1] * $time), 0.0);
            } elseif ($t === 'scale' && count($a) >= 2) {
                $this->ffi->glScalef($a[0], $a[1], 1.0);
            } elseif ($t === 'rotate' && count($a) >= 1) {
                // RB_CalcRotateTexCoords: degs = -degsPerSecond * time
                $this->ffi->glTranslatef(0.5, 0.5, 0.0);
                $this->ffi->glRotatef(-$a[0] * $time, 0.0, 0.0, 1.0);
                $this->ffi->glTranslatef(-0.5, -0.5, 0.0);
            } elseif ($t === 'turb' && count($a) >= 4) {
                // Prefer bindTurbStageTexCoords (RB_CalcTurbulentTexCoords); matrix
                // translate is a last-resort approx if vertexBinary was dropped.
                $now = ($a[2] + $time * $a[3]) * 2.0 * M_PI;
                $amp = $a[1];
                $this->ffi->glTranslatef(sin($now) * $amp, cos($now * 1.37) * $amp, 0.0);
            } elseif ($t === 'stretch' && count($a) >= 5) {
                // RB_CalcStretchTexCoords: p = 1/EvalWaveForm; scale about 0.5
                $func = is_string($a[0]) ? $a[0] : 'sin';
                $wave = $this->evalWaveForm($func, (float) $a[1], (float) $a[2], (float) $a[3], (float) $a[4], $time);
                if (abs($wave) < 1e-4) {
                    $wave = 1e-4;
                }
                $p = 1.0 / $wave;
                $this->ffi->glTranslatef(0.5, 0.5, 0.0);
                $this->ffi->glScalef($p, $p, 1.0);
                $this->ffi->glTranslatef(-0.5, -0.5, 0.0);
            }
        }
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
    }

    private function resetGlState(): void
    {
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDisable(self::GL_ALPHA_TEST);
        $this->disableTexGen();
        // Full client-array teardown — leftover COLOR/NORMAL/TEXCOORD with ARRAY_BUFFER=0
        // (offsetPtr as client addr) segfaults Apple GL on the next glDrawElements.
        $this->ffi->glDisableClientState(self::GL_COLOR_ARRAY);
        $this->ffi->glDisableClientState(self::GL_NORMAL_ARRAY);
        $this->ffi->glDisableClientState(self::GL_VERTEX_ARRAY);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glClientActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glDisableClientState(self::GL_TEXTURE_COORD_ARRAY);
        $this->ffi->glBindBuffer(self::GL_ARRAY_BUFFER, 0);
        $this->ffi->glBindBuffer(self::GL_ELEMENT_ARRAY_BUFFER, 0);
        $this->ffi->glDepthFunc(self::GL_LEQUAL);
        $this->ffi->glDepthMask(1);
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glCullFace(self::GL_FRONT);
        $this->ffi->glDepthRange(0.0, 1.0);
        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
    }

    /**
     * Deterministic opaque material start (RB_BeginDrawingView / GLS_DEFAULT).
     * Sky cloud stages (tim_hell) enable blend + tcMod; do not inherit that.
     */
    private function beginOpaqueWorldState(): void
    {
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDisable(self::GL_ALPHA_TEST);
        $this->disableTexGen();
        $this->ffi->glDepthFunc(self::GL_LEQUAL);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDepthRange(0.0, 1.0);
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        // Sky / autosprite / cull-none leave face mode sticky — match Cycle 7 CT_FRONT_SIDED.
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glCullFace(self::GL_FRONT);
        $this->ffi->glActiveTexture(self::GL_TEXTURE1);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glActiveTexture(self::GL_TEXTURE0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
    }

    /** @param list<float> $origin */
    private function drawSky(array $origin, string $mode): void
    {
        // Depth-aware sky: after portals write Z, fill only uncleared pixels
        // (depthRange 1,1 + LEQUAL). Do not disable depth — that would paint
        // over portal underlays drawn earlier in drawWorld.
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthFunc(self::GL_LEQUAL);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDepthRange(1.0, 1.0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $s = 4096.0;
        $x = $origin[0];
        $y = $origin[1];
        $z = $origin[2];

        $drew = false;
        if ($this->skyBoxTex !== [] && count($this->skyBoxTex) === 6 && $mode === 'full') {
            // sky_texorder: rt,lf,bk,ft,up,dn → indices 0,2,1,3,4,5 into rt,bk,lf,ft,up,dn load order
            $order = [0, 2, 1, 3, 4, 5];
            $faces = [
                [[$x + $s, $y - $s, $z - $s], [$x + $s, $y - $s, $z + $s], [$x + $s, $y + $s, $z + $s], [$x + $s, $y + $s, $z - $s]],
                [[$x - $s, $y - $s, $z - $s], [$x + $s, $y - $s, $z - $s], [$x + $s, $y - $s, $z + $s], [$x - $s, $y - $s, $z + $s]],
                [[$x - $s, $y + $s, $z - $s], [$x - $s, $y + $s, $z + $s], [$x - $s, $y - $s, $z + $s], [$x - $s, $y - $s, $z - $s]],
                [[$x + $s, $y + $s, $z - $s], [$x + $s, $y + $s, $z + $s], [$x - $s, $y + $s, $z + $s], [$x - $s, $y + $s, $z - $s]],
                [[$x - $s, $y - $s, $z + $s], [$x + $s, $y - $s, $z + $s], [$x + $s, $y + $s, $z + $s], [$x - $s, $y + $s, $z + $s]],
                [[$x - $s, $y + $s, $z - $s], [$x + $s, $y + $s, $z - $s], [$x + $s, $y - $s, $z - $s], [$x - $s, $y - $s, $z - $s]],
            ];
            $this->ffi->glEnable(self::GL_TEXTURE_2D);
            // tr_sky.c: qglColor3f(tr.identityLight, …); use Color4f so A stays 1
            $il = $this->lighting->identityLight;
            $this->ffi->glColor4f($il, $il, $il, 1.0);
            foreach ($order as $faceIdx => $texIdx) {
                $f = $faces[$faceIdx];
                $tex = $this->skyBoxTex[$texIdx];
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
                $this->binds++;
                $this->ffi->glBegin(self::GL_QUADS);
                $uv = [[0, 0], [1, 0], [1, 1], [0, 1]];
                for ($i = 0; $i < 4; $i++) {
                    $this->ffi->glTexCoord2f($uv[$i][0], $uv[$i][1]);
                    $this->ffi->glVertex3f($f[$i][0], $f[$i][1], $f[$i][2]);
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
            }
            $drew = true;
        }

        // Cloud layers (R_BuildCloudData subset) — q3dm0 skyparms "- 512 -"
        if ($mode === 'full' && $this->skyMaterial !== null && $this->skyMaterial->stages !== []) {
            $this->drawSkyClouds($origin);
            $drew = true;
        }

        if (!$drew) {
            // last-resort void fill only when no sky resources resolve
            $this->ffi->glDisable(self::GL_TEXTURE_2D);
            $this->ffi->glBegin(self::GL_QUADS);
            $this->ffi->glColor3f(0.35, 0.45, 0.75);
            $this->ffi->glVertex3f($x - $s, $y - $s, $z + $s);
            $this->ffi->glVertex3f($x + $s, $y - $s, $z + $s);
            $this->ffi->glVertex3f($x + $s, $y + $s, $z + $s);
            $this->ffi->glVertex3f($x - $s, $y + $s, $z + $s);
            $this->ffi->glColor3f(0.15, 0.18, 0.28);
            $this->ffi->glVertex3f($x - $s, $y - $s, $z - $s);
            $this->ffi->glVertex3f($x + $s, $y - $s, $z - $s);
            $this->ffi->glVertex3f($x + $s, $y + $s, $z - $s);
            $this->ffi->glVertex3f($x - $s, $y + $s, $z - $s);
            $this->ffi->glEnd();
            $this->drawCalls++;
        }

        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDepthRange(0.0, 1.0);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthMask(1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glCullFace(self::GL_FRONT);
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
    }

    /**
     * Cloud sky via MakeSkyVec + R_InitSkyTexCoords (tr_sky.c subset).
     * Full faces, skip bottom (axis 5). SKY_SUBDIVISIONS=8.
     *
     * @param list<float> $origin
     */
    private function drawSkyClouds(array $origin): void
    {
        if ($this->skyMaterial === null) {
            return;
        }
        $heightCloud = (float) ($this->skyMaterial->cloudHeight > 0 ? $this->skyMaterial->cloudHeight : 512);
        $this->ensureCloudTexCoords($heightCloud);

        $half = 4; // HALF_SKY_SUBDIVISIONS
        $div = 8;  // SKY_SUBDIVISIONS
        $boxSize = 1024.0 / 1.75; // R_InitSkyTexCoords uses zFar=1024

        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glDepthRange(1.0, 1.0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $first = true;
        foreach ($this->skyMaterial->stages as $stage) {
            if ($stage->isLightmap || $stage->map === '') {
                continue;
            }
            $tex = $this->textureCache[$stage->map] ?? $this->whiteTex;
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            // Prefer applyStageState (Color4f / identityLighting / wave; GL_ZERO-safe blend)
            $this->applyStageState($stage, $this->time);
            if (!$stage->hasBlend && $first) {
                $this->ffi->glDepthMask(1);
            }
            $this->applyTcMod($stage, $this->time);

            for ($axis = 0; $axis < 6; $axis++) {
                if ($axis === 5) {
                    continue; // no bottom clouds
                }
                for ($ti = 0; $ti < $div; $ti++) {
                    $this->ffi->glBegin(self::GL_TRIANGLE_STRIP);
                    for ($si = 0; $si <= $div; $si++) {
                        foreach ([$ti, $ti + 1] as $tj) {
                            $s = ($si - $half) / (float) $half;
                            $t = ($tj - $half) / (float) $half;
                            $xyz = $this->makeSkyVec($s, $t, $axis, $boxSize);
                            $st = $this->cloudTexCoords[$axis][$tj][$si];
                            $this->ffi->glTexCoord2f($st[0], $st[1]);
                            $this->ffi->glVertex3f(
                                $origin[0] + $xyz[0],
                                $origin[1] + $xyz[1],
                                $origin[2] + $xyz[2],
                            );
                        }
                    }
                    $this->ffi->glEnd();
                    $this->drawCalls++;
                }
            }

            $this->ffi->glMatrixMode(0x1702);
            $this->ffi->glLoadIdentity();
            $this->ffi->glMatrixMode(self::GL_MODELVIEW);
            $first = false;
        }
        $this->ffi->glDepthRange(0.0, 1.0);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glCullFace(self::GL_FRONT);
    }

    /**
     * MakeSkyVec (tr_sky.c) — s,t in [-1,1]; returns XYZ only (ST from cloud table).
     * @return list<float>
     */
    private function makeSkyVec(float $s, float $t, int $axis, float $boxSize): array
    {
        // st_to_vec[6][3]
        static $stToVec = [
            [3, -1, 2],
            [-3, 1, 2],
            [1, 3, 2],
            [-1, -3, 2],
            [-2, -1, 3],
            [2, -1, -3],
        ];
        $b = [$s * $boxSize, $t * $boxSize, $boxSize];
        $out = [0.0, 0.0, 0.0];
        for ($j = 0; $j < 3; $j++) {
            $k = $stToVec[$axis][$j];
            if ($k < 0) {
                $out[$j] = -$b[-$k - 1];
            } else {
                $out[$j] = $b[$k - 1];
            }
        }
        return $out;
    }

    /** R_InitSkyTexCoords(heightCloud) — builds s_cloudTexCoords. */
    private function ensureCloudTexCoords(float $heightCloud): void
    {
        if ($this->cloudTexCoords !== null && abs($this->cloudHeightCached - $heightCloud) < 0.01) {
            return;
        }
        $half = 4;
        $div = 8;
        $boxSize = 1024.0 / 1.75;
        $radiusWorld = 4096.0;
        $coords = [];
        for ($axis = 0; $axis < 6; $axis++) {
            $coords[$axis] = [];
            for ($tj = 0; $tj <= $div; $tj++) {
                $coords[$axis][$tj] = [];
                for ($si = 0; $si <= $div; $si++) {
                    $s = ($si - $half) / (float) $half;
                    $t = ($tj - $half) / (float) $half;
                    $skyVec = $this->makeSkyVec($s, $t, $axis, $boxSize);
                    $sx = $skyVec[0];
                    $sy = $skyVec[1];
                    $sz = $skyVec[2];
                    $dot = $sx * $sx + $sy * $sy + $sz * $sz;
                    // parametric p intersecting cloud layer (R_InitSkyTexCoords)
                    $p = (1.0 / (2.0 * $dot)) * (
                        -2.0 * $sz * $radiusWorld
                        + 2.0 * sqrt(
                            ($sz * $sz) * ($radiusWorld * $radiusWorld)
                            + 2.0 * ($sx * $sx) * $radiusWorld * $heightCloud
                            + ($sx * $sx) * ($heightCloud * $heightCloud)
                            + 2.0 * ($sy * $sy) * $radiusWorld * $heightCloud
                            + ($sy * $sy) * ($heightCloud * $heightCloud)
                            + 2.0 * ($sz * $sz) * $radiusWorld * $heightCloud
                            + ($sz * $sz) * ($heightCloud * $heightCloud)
                        )
                    );
                    $v = [$sx * $p, $sy * $p, $sz * $p + $radiusWorld];
                    $len = sqrt($v[0] * $v[0] + $v[1] * $v[1] + $v[2] * $v[2]) ?: 1.0;
                    $v = [$v[0] / $len, $v[1] / $len, $v[2] / $len];
                    $sRad = acos(max(-1.0, min(1.0, $v[0])));
                    $tRad = acos(max(-1.0, min(1.0, $v[1])));
                    $coords[$axis][$tj][$si] = [$sRad, $tRad];
                }
            }
        }
        $this->cloudTexCoords = $coords;
        $this->cloudHeightCached = $heightCloud;
    }

    /** @param list<float> $viewOrigin */
    private function drawFlares(array $viewOrigin): void
    {
        if ($this->flares === []) {
            return;
        }
        [$right, $up] = $this->cameraRightUp();
        $fwd = $this->cameraForward();
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $il = $this->lighting->identityLight;
        $seen = [];
        foreach ($this->flares as $f) {
            $o = $f['origin'];
            $dx = $o[0] - $viewOrigin[0];
            $dy = $o[1] - $viewOrigin[1];
            $dz = $o[2] - $viewOrigin[2];
            $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            // -eyeZ in view space ≈ depth along forward (RB_AddFlare / RB_RenderFlare)
            $depth = $dx * $fwd[0] + $dy * $fwd[1] + $dz * $fwd[2];
            if ($depth < 8.0 || $dist < 8.0) {
                continue;
            }
            $key = sprintf('%.2f:%.2f:%.2f', $o[0], $o[1], $o[2]);
            $seen[$key] = true;

            // RB_AddFlare: fade by facing DotProduct(viewer - point, normal)
            $nx = $f['normal'][0] ?? 0.0;
            $ny = $f['normal'][1] ?? 0.0;
            $nz = $f['normal'][2] ?? 1.0;
            $inv = 1.0 / $dist;
            $facing = ($viewOrigin[0] - $o[0]) * $nx + ($viewOrigin[1] - $o[1]) * $ny + ($viewOrigin[2] - $o[2]) * $nz;
            $facing *= $inv;
            $facingOk = $facing > 0.01;

            // RB_TestFlare: depth-buffer occlusion (grates/glass holes), not CM trace
            $visible = false;
            if ($facingOk) {
                $proj = $this->projectWorldToWindow($o[0], $o[1], $o[2]);
                if ($proj !== null) {
                    [$winX, $winY, $eyeZ] = $proj;
                    $screenZ = $this->readFlareScreenZ($winX, $winY);
                    // visible when buffer depth is within 24u of flare eyeZ (tr_flares.c)
                    $visible = ((-$eyeZ) - (-$screenZ)) < 24.0;
                }
            }

            // RB_TestFlare — soft intensity fade (r_flareFade)
            $st = $this->flareFadeState[$key] ?? [
                'visible' => false,
                'fadeTime' => $this->time - 2.0,
            ];
            if ($visible) {
                if (!$st['visible']) {
                    $st['visible'] = true;
                    $st['fadeTime'] = $this->time;
                }
                $fade = ($this->time - $st['fadeTime']) * $this->flareFadeRate;
            } else {
                if ($st['visible']) {
                    $st['visible'] = false;
                    $st['fadeTime'] = $this->time;
                }
                $fade = 1.0 - ($this->time - $st['fadeTime']) * $this->flareFadeRate;
            }
            $this->flareFadeState[$key] = $st;
            $fade = max(0.0, min(1.0, $fade));
            if ($fade <= 0.001) {
                continue;
            }
            if (!$facingOk) {
                continue;
            }

            // RB_RenderFlare: size is screen pixels under glOrtho; we billboard in world space.
            // world_half = sizePx * depth * tan(fovY/2) / (viewportH/2)
            $sizePx = $this->viewportW * ($this->flareSize / 640.0 + 8.0 / $depth);
            $tanHalf = tan(deg2rad($this->fovY) / 2.0);
            $size = $sizePx * $depth * $tanHalf / (0.5 * (float) max(1, $this->viewportH));
            $tex = $this->textureCache[$f['shader']] ?? $this->textureCache['gfx/misc/flare'] ?? $this->whiteTex;
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            // Color already facing-scaled in AddFlare; we apply facing * drawIntensity * identityLight
            $cr = ($f['color'][0] ?? 0.85) * $facing * $fade * $il;
            $cg = ($f['color'][1] ?? 0.75) * $facing * $fade * $il;
            $cb = ($f['color'][2] ?? 0.55) * $facing * $fade * $il;
            $this->ffi->glColor4f($cr, $cg, $cb, 1.0);
            // World billboard approximating RB_RenderFlare ortho stamp
            $left = [-$right[0] * $size, -$right[1] * $size, -$right[2] * $size];
            $upv = [$up[0] * $size, $up[1] * $size, $up[2] * $size];
            $this->emitQuadStamp($o, $left, $upv);
        }
        // Drop fade state for flares not touched this frame (map reload / etc.)
        foreach (array_keys($this->flareFadeState) as $k) {
            if (!isset($seen[$k])) {
                unset($this->flareFadeState[$k]);
            }
        }
        $this->ffi->glColor4f(1.0, 1.0, 1.0, 1.0);
    }

    /**
     * R_TransformModelToClip + R_TransformClipToWindow for a world point.
     * @return array{0: int, 1: int, 2: float}|null windowX, windowY, eyeZ (or null if clipped)
     */
    private function projectWorldToWindow(float $x, float $y, float $z): ?array
    {
        $mv = $this->ffi->new('float[16]');
        $pr = $this->ffi->new('float[16]');
        $this->ffi->glGetFloatv(self::GL_MODELVIEW_MATRIX, $mv);
        $this->ffi->glGetFloatv(self::GL_PROJECTION_MATRIX, $pr);

        $eye = [0.0, 0.0, 0.0, 0.0];
        $clip = [0.0, 0.0, 0.0, 0.0];
        for ($i = 0; $i < 4; $i++) {
            $eye[$i] = $x * (float) $mv[$i]
                + $y * (float) $mv[$i + 4]
                + $z * (float) $mv[$i + 8]
                + (float) $mv[$i + 12];
        }
        for ($i = 0; $i < 4; $i++) {
            $clip[$i] = $eye[0] * (float) $pr[$i]
                + $eye[1] * (float) $pr[$i + 4]
                + $eye[2] * (float) $pr[$i + 8]
                + $eye[3] * (float) $pr[$i + 12];
        }
        $w = $clip[3];
        if ($w === 0.0) {
            return null;
        }
        for ($i = 0; $i < 3; $i++) {
            if ($clip[$i] >= $w || $clip[$i] <= -$w) {
                return null;
            }
        }
        $nx = $clip[0] / $w;
        $ny = $clip[1] / $w;
        $winX = (int) (0.5 * (1.0 + $nx) * $this->viewportW + 0.5) + $this->viewportX;
        $winY = (int) (0.5 * (1.0 + $ny) * $this->viewportH + 0.5) + $this->viewportY;
        if ($winX < $this->viewportX || $winX >= $this->viewportX + $this->viewportW
            || $winY < $this->viewportY || $winY >= $this->viewportY + $this->viewportH) {
            return null;
        }

        return [$winX, $winY, $eye[2]];
    }

    /** Reconstruct eye-space Z from depth buffer sample (RB_TestFlare). */
    private function readFlareScreenZ(int $winX, int $winY): float
    {
        $depthC = $this->ffi->new('float');
        $this->ffi->glReadPixels(
            $winX,
            $winY,
            1,
            1,
            self::GL_DEPTH_COMPONENT,
            self::GL_FLOAT,
            FFI::addr($depthC),
        );
        $depth = (float) $depthC->cdata;
        $pr = $this->ffi->new('float[16]');
        $this->ffi->glGetFloatv(self::GL_PROJECTION_MATRIX, $pr);
        $p10 = (float) $pr[10];
        $p11 = (float) $pr[11];
        $p14 = (float) $pr[14];
        $denom = (2.0 * $depth - 1.0) * $p11 - $p10;
        if (abs($denom) < 1e-12) {
            return 0.0;
        }

        return $p14 / $denom;
    }

    /**
     * CG_DamageBlendBlob — first-person blood sprite (viewBloodBlend / gfx/damage/blood_screen).
     * DAMAGE_TIME = 500ms.
     * @param list<float> $viewOrigin
     */
    private function drawDamageBlendBlob(array $viewOrigin): void
    {
        if ($this->damageValue <= 0.0) {
            return;
        }
        $maxTime = 0.5;
        $t = $this->time - $this->damageTime;
        if ($t <= 0.0 || $t >= $maxTime) {
            return;
        }
        [$right, $up] = $this->cameraRightUp();
        $fwd = $this->cameraForward();
        // Q3: vieworg + 8*fwd + (damageX*-8)*viewaxis[1] + (damageY*8)*up
        // viewaxis[1] = left = −cameraRight → + damageX*8*right
        $ox = $viewOrigin[0] + $fwd[0] * 8.0 + $right[0] * ($this->damageX * 8.0) + $up[0] * ($this->damageY * 8.0);
        $oy = $viewOrigin[1] + $fwd[1] * 8.0 + $right[1] * ($this->damageX * 8.0) + $up[1] * ($this->damageY * 8.0);
        $oz = $viewOrigin[2] + $fwd[2] * 8.0 + $right[2] * ($this->damageX * 8.0) + $up[2] * ($this->damageY * 8.0);
        $r = $this->damageValue * 3.0;
        $alpha = (200.0 / 255.0) * (1.0 - $t / $maxTime);
        $tex = $this->ensurePathTexture('gfx/damage/blood_screen');

        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_SRC_ALPHA, self::GL_ONE_MINUS_SRC_ALPHA);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_DEPTH_TEST);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        $il = $this->lighting->identityLight;
        $this->ffi->glColor4f($il, $il, $il, $alpha);
        $left = [-$right[0] * $r, -$right[1] * $r, -$right[2] * $r];
        $upv = [$up[0] * $r, $up[1] * $r, $up[2] * $r];
        $this->emitQuadStamp([$ox, $oy, $oz], $left, $upv);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthMask(1);
        $this->ffi->glColor4f(1, 1, 1, 1);
    }

    /**
     * Camera right/up in world space from current modelview (orthonormal view).
     * @return array{0: list<float>, 1: list<float>}
     */
    private function cameraRightUp(): array
    {
        $m = $this->ffi->new('float[16]');
        $this->ffi->glGetFloatv(0x0BA6, $m); // GL_MODELVIEW_MATRIX
        // columns of inverse rotation = rows of upper 3x3 for pure rotation
        $right = [(float) $m[0], (float) $m[4], (float) $m[8]];
        $up = [(float) $m[1], (float) $m[5], (float) $m[9]];
        return [$right, $up];
    }

    /** View forward (−Z) in world space from modelview. @return list<float> */
    private function cameraForward(): array
    {
        $m = $this->ffi->new('float[16]');
        $this->ffi->glGetFloatv(0x0BA6, $m);
        return [-(float) $m[2], -(float) $m[6], -(float) $m[10]];
    }

    /**
     * Draw MD3 world item with per-surface shader stages (env / blend subset).
     * @param list<float> $origin
     * @param array<string, int> $surfaceTex map shader/name => tex id
     */
    public function drawMd3(
        Md3Model $model,
        array $origin,
        float $yawDeg,
        float $bobZ = 0.0,
        array $surfaceTex = [],
        int $frame = 0,
        ?array $lightDir = null,
        float $modelScale = 1.0,
    ): void {
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDepthMask(1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $rad = deg2rad($yawDeg);
        $c = cos($rad);
        $s = sin($rad);
        $entityLight = $this->entityLightingAt($origin, $lightDir);

        foreach ($model->surfaces as $si => $surf) {
            $key = $surf['shader'] !== '' ? $surf['shader'] : $surf['name'];
            $shaderName = $this->stripShaderExt($key);
            $stages = $this->md3Stages($shaderName);
            $verts = $model->frameVerts($si, $frame);
            if ($this->shaderParser !== null) {
                $this->applyCull($this->shaderParser->get($shaderName)->cull);
            }
            $pass = 0;
            // R_SetupEntityLightingGrid sample (or no-grid fallback)
            $ambient = $entityLight['ambient'];
            $directed = $entityLight['directed'];
            $ld = $entityLight['lightDir'];
            foreach ($stages as $stage) {
                if ($stage->isLightmap || $stage->map === '' || str_starts_with($stage->map, '$')) {
                    continue;
                }
                $tex = $this->stageTexture($stage, $shaderName);
                if ($tex === $this->whiteTex) {
                    $tex = $surfaceTex[$key]
                        ?? $surfaceTex[$surf['name']]
                        ?? $this->textureCache[$key]
                        ?? $this->whiteTex;
                }
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
                $this->binds++;
                $env = $stage->tcGen === 'environment';
                $envView = ($env && $this->viewOrigin !== null) ? $this->viewOrigin : null;
                $this->applyStageState($stage, $this->time);
                $this->disableTexGen();
                if ($envView === null) {
                    $this->applyTcMod($stage, $this->time);
                }
                $useDiffuse = strtolower($stage->rgbGen) === 'lightingdiffuse';
                $this->ffi->glBegin(self::GL_TRIANGLES);
                foreach ($surf['indices'] as $idx) {
                    $v = $verts[$idx];
                    $st = $surf['st'][$idx];
                    $n = $surf['normals'][$idx] ?? [0.0, 0.0, 1.0];
                    $rx = ($v[0] * $c - $v[1] * $s) * $modelScale;
                    $ry = ($v[0] * $s + $v[1] * $c) * $modelScale;
                    $rz = $v[2] * $modelScale;
                    $nx = $n[0] * $c - $n[1] * $s;
                    $ny = $n[0] * $s + $n[1] * $c;
                    $nz = $n[2];
                    $wx = $origin[0] + $rx;
                    $wy = $origin[1] + $ry;
                    $wz = $origin[2] + $rz + $bobZ;
                    // ComputeColors ∥ ComputeTexCoords — lightingDiffuse applies with env ST too
                    if ($useDiffuse) {
                        $incoming = $nx * $ld[0] + $ny * $ld[1] + $nz * $ld[2];
                        if ($incoming <= 0.0) {
                            $this->ffi->glColor4f($ambient[0], $ambient[1], $ambient[2], 1.0);
                        } else {
                            $this->ffi->glColor4f(
                                min(1.0, $ambient[0] + $incoming * $directed[0]),
                                min(1.0, $ambient[1] + $incoming * $directed[1]),
                                min(1.0, $ambient[2] + $incoming * $directed[2]),
                                1.0,
                            );
                        }
                    }
                    if ($envView !== null) {
                        [$es, $et] = $this->environmentStForVertex($wx, $wy, $wz, $nx, $ny, $nz, $envView, $stage, $this->time);
                        $this->ffi->glTexCoord2f($es, $et);
                    } else {
                        $this->ffi->glTexCoord2f($st[0], $st[1]);
                    }
                    $this->ffi->glVertex3f($wx, $wy, $wz);
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
                $this->ffi->glMatrixMode(0x1702);
                $this->ffi->glLoadIdentity();
                $this->ffi->glMatrixMode(self::GL_MODELVIEW);
                $pass++;
            }
            if ($pass === 0) {
                // Fallback: single diffuse when no shader stages resolve
                $tex = $surfaceTex[$key] ?? $surfaceTex[$surf['name']] ?? $this->textureCache[$key] ?? $this->whiteTex;
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
                $this->binds++;
                $this->ffi->glDisable(self::GL_BLEND);
                $this->ffi->glDepthMask(1);
                $this->ffi->glColor3f(1.0, 1.0, 1.0);
                $this->ffi->glBegin(self::GL_TRIANGLES);
                foreach ($surf['indices'] as $idx) {
                    $v = $verts[$idx];
                    $st = $surf['st'][$idx];
                    $rx = ($v[0] * $c - $v[1] * $s) * $modelScale;
                    $ry = ($v[0] * $s + $v[1] * $c) * $modelScale;
                    $this->ffi->glTexCoord2f($st[0], $st[1]);
                    $this->ffi->glVertex3f($origin[0] + $rx, $origin[1] + $ry, $origin[2] + $v[2] * $modelScale + $bobZ);
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
            }
        }
        $this->disableTexGen();
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDisable(self::GL_ALPHA_TEST);
        $this->ffi->glDepthFunc(self::GL_LEQUAL);
        $this->ffi->glDepthMask(1);
        $this->ffi->glColor3f(1, 1, 1);
    }

    /**
     * R_SetupEntityLighting (grid or no-world fallback) → GL 0–1 colors + world lightDir.
     *
     * @param list<float> $origin
     * @param list<float>|null $lightDirOverride
     * @return array{ambient: list<float>, directed: list<float>, lightDir: list<float>}
     */
    private function entityLightingAt(array $origin, ?array $lightDirOverride): array
    {
        $il = $this->lighting->identityLight;
        $sample = $this->lightGrid?->sample($origin);
        if ($sample !== null) {
            $ambient = $sample['ambient'];
            $directed = $sample['directed'];
            $ld = $sample['lightDir'];
        } else {
            $ambient = [$il * 150.0, $il * 150.0, $il * 150.0];
            $directed = [$il * 150.0, $il * 150.0, $il * 150.0];
            $ld = $lightDirOverride ?? [0.45, 0.3, 0.9];
            $llen = sqrt($ld[0] ** 2 + $ld[1] ** 2 + $ld[2] ** 2) ?: 1.0;
            $ld = [$ld[0] / $llen, $ld[1] / $llen, $ld[2] / $llen];
        }
        // RF_MINLIGHT bonus (always on in id source)
        $ambient[0] += $il * 32.0;
        $ambient[1] += $il * 32.0;
        $ambient[2] += $il * 32.0;
        $byteMax = (float) $this->lighting->identityLightByte;
        for ($i = 0; $i < 3; $i++) {
            if ($ambient[$i] > $byteMax) {
                $ambient[$i] = $byteMax;
            }
        }

        return [
            'ambient' => [$ambient[0] / 255.0, $ambient[1] / 255.0, $ambient[2] / 255.0],
            'directed' => [$directed[0] / 255.0, $directed[1] / 255.0, $directed[2] / 255.0],
            'lightDir' => $ld,
        ];
    }

    /** @return list<ShaderStage> */
    private function md3Stages(string $shaderName): array
    {
        if ($this->shaderParser === null) {
            return [];
        }
        return $this->shaderParser->get($shaderName)->stages;
    }

    private function stripShaderExt(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        foreach (['.tga', '.jpg', '.jpeg', '.png'] as $ext) {
            if (str_ends_with(strtolower($name), $ext)) {
                return substr($name, 0, -strlen($ext));
            }
        }
        return $name;
    }

    /**
     * First-person weapon hierarchy (CG_AddViewWeapon): gun/barrel/flash at poses.
     * RF_DEPTHHACK: depthRange 0..0.3 while drawing, then restore.
     *
     * @param array{gun: ?array{origin: list<float>, axis: list<list<float>>}, barrel: ?array{origin: list<float>, axis: list<list<float>>}, flash: ?array{origin: list<float>, axis: list<list<float>>}} $poses
     * @param array{gun?: ?\Quake3\Render\Md3Model, barrel?: ?\Quake3\Render\Md3Model, flash?: ?\Quake3\Render\Md3Model} $models
     * @param array<string, array<string, int>> $texMaps
     */
    /**
     * @param list<float>|null $lightingOrigin world sample point (player lightingOrigin / RF_LIGHTING_ORIGIN)
     */
    public function drawViewWeaponHierarchy(
        array $poses,
        array $models,
        array $texMaps = [],
        bool $showFlash = false,
        bool $wireframe = false,
        bool $depthHack = true,
        ?array $lightingOrigin = null,
    ): void {
        if ($depthHack) {
            $this->ffi->glDepthRange(0.0, 0.3);
        }
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthMask(1);
        $this->ffi->glEnable(self::GL_CULL_FACE);

        if (isset($models['gun'], $poses['gun']) && $models['gun'] !== null && $poses['gun'] !== null) {
            $this->drawMd3Oriented(
                $models['gun'],
                $poses['gun']['origin'],
                $poses['gun']['axis'],
                $texMaps['gun'] ?? [],
                0,
                $wireframe,
                $lightingOrigin,
            );
        }
        if (isset($models['barrel'], $poses['barrel']) && $models['barrel'] !== null && $poses['barrel'] !== null) {
            $this->drawMd3Oriented(
                $models['barrel'],
                $poses['barrel']['origin'],
                $poses['barrel']['axis'],
                $texMaps['barrel'] ?? [],
                0,
                $wireframe,
                $lightingOrigin,
            );
        }
        if ($showFlash && isset($models['flash'], $poses['flash']) && $poses['flash'] !== null) {
            if ($models['flash'] !== null && $models['flash']->numSurfaces > 0) {
                $this->ffi->glEnable(self::GL_BLEND);
                $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
                $this->ffi->glDepthMask(0);
                $this->drawMd3Oriented(
                    $models['flash'],
                    $poses['flash']['origin'],
                    $poses['flash']['axis'],
                    $texMaps['flash'] ?? [],
                    0,
                    $wireframe,
                    $lightingOrigin,
                );
                $this->ffi->glDepthMask(1);
                $this->ffi->glDisable(self::GL_BLEND);
            } else {
                // tiny additive marker at tag_flash
                $o = $poses['flash']['origin'];
                $this->ffi->glEnable(self::GL_BLEND);
                $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
                $this->ffi->glDepthMask(0);
                $this->ffi->glDisable(self::GL_TEXTURE_2D);
                $this->ffi->glColor3f(1.0, 0.85, 0.35);
                $r = 2.0;
                $this->ffi->glBegin(self::GL_QUADS);
                $this->ffi->glVertex3f($o[0] - $r, $o[1] - $r, $o[2]);
                $this->ffi->glVertex3f($o[0] + $r, $o[1] - $r, $o[2]);
                $this->ffi->glVertex3f($o[0] + $r, $o[1] + $r, $o[2]);
                $this->ffi->glVertex3f($o[0] - $r, $o[1] + $r, $o[2]);
                $this->ffi->glEnd();
                $this->drawCalls++;
                $this->ffi->glDepthMask(1);
                $this->ffi->glDisable(self::GL_BLEND);
                $this->ffi->glColor3f(1, 1, 1);
            }
        }

        if ($depthHack) {
            $this->ffi->glDepthRange(0.0, 1.0);
        }
        $this->resetGlState();
    }

    /**
     * Draw MD3 with Quake origin+axis (R_RotateForEntity style).
     * Honors surface shader stages (env / blend) — e.g. plasma_glass, plasma_glo.
     * @param list<float> $origin @param list<list<float>> $axis
     * @param array<string, int> $surfaceTex
     */
    /**
     * @param list<float>|null $lightingOrigin sample lightgrid here (defaults to model origin)
     * @param string|null $customShader tr_mesh customShader (e.g. plasmaExplosion)
     * @param float|null $shaderTime refEntity.shaderTime age (defaults to wall clock)
     * @param list<float>|null $entityRgb rgbGen entity override (RGBA)
     */
    public function drawMd3Oriented(
        Md3Model $model,
        array $origin,
        array $axis,
        array $surfaceTex = [],
        int $frame = 0,
        bool $wireframe = false,
        ?array $lightingOrigin = null,
        ?string $customShader = null,
        ?float $shaderTime = null,
        ?array $entityRgb = null,
    ): void {
        if ($model->numSurfaces === 0) {
            return;
        }
        $time = $shaderTime ?? $this->time;
        $entityLight = $this->entityLightingAt($lightingOrigin ?? $origin, null);
        $ambient = $entityLight['ambient'];
        $directed = $entityLight['directed'];
        $ld = $entityLight['lightDir'];
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        if ($wireframe) {
            $this->ffi->glDisable(self::GL_TEXTURE_2D);
            $this->ffi->glPolygonMode(self::GL_FRONT_AND_BACK, self::GL_LINE);
        }
        foreach ($model->surfaces as $si => $surf) {
            $key = $surf['shader'] !== '' ? $surf['shader'] : $surf['name'];
            $verts = $model->frameVerts($si, $frame);
            if ($wireframe) {
                $this->ffi->glColor3f(1, 1, 1);
                $this->ffi->glBegin(self::GL_LINES);
                $idxCount = count($surf['indices']);
                for ($i = 0; $i + 2 < $idxCount; $i += 3) {
                    $pts = [];
                    for ($k = 0; $k < 3; $k++) {
                        $pts[] = \Quake3\Game\QuakeMath::transformPoint($verts[$surf['indices'][$i + $k]], $origin, $axis);
                    }
                    for ($e = 0; $e < 3; $e++) {
                        $a = $pts[$e];
                        $b = $pts[($e + 1) % 3];
                        $this->ffi->glVertex3f($a[0], $a[1], $a[2]);
                        $this->ffi->glVertex3f($b[0], $b[1], $b[2]);
                    }
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
                continue;
            }

            $shaderName = $customShader ?? $this->stripShaderExt($key);
            $stages = $this->md3Stages($shaderName);
            if ($this->shaderParser !== null) {
                $this->applyCull($this->shaderParser->get($shaderName)->cull);
            }
            $pass = 0;
            foreach ($stages as $stage) {
                if ($stage->isLightmap || $stage->map === '' || str_starts_with($stage->map, '$')) {
                    continue;
                }
                $tex = $this->stageTexture($stage, $shaderName, $time);
                if ($tex === $this->whiteTex) {
                    $tex = $surfaceTex[$key]
                        ?? $this->textureCache[$key]
                        ?? $this->whiteTex;
                }
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
                $this->binds++;
                $env = $stage->tcGen === 'environment';
                $envView = ($env && $this->viewOrigin !== null) ? $this->viewOrigin : null;
                $this->applyStageState($stage, $time);
                if ($entityRgb !== null && strtolower($stage->rgbGen) === 'entity') {
                    $this->ffi->glColor4f(
                        $entityRgb[0],
                        $entityRgb[1],
                        $entityRgb[2],
                        $entityRgb[3] ?? 1.0,
                    );
                }
                $this->disableTexGen();
                if ($envView === null) {
                    $this->applyTcMod($stage, $time);
                }
                $useDiffuse = strtolower($stage->rgbGen) === 'lightingdiffuse';
                $this->ffi->glBegin(self::GL_TRIANGLES);
                foreach ($surf['indices'] as $idx) {
                    $v = $verts[$idx];
                    $pt = \Quake3\Game\QuakeMath::transformPoint($v, $origin, $axis);
                    $n = $surf['normals'][$idx] ?? [0.0, 0.0, 1.0];
                    // rotate normal by entity axis (no translation)
                    $nx = $n[0] * $axis[0][0] + $n[1] * $axis[1][0] + $n[2] * $axis[2][0];
                    $ny = $n[0] * $axis[0][1] + $n[1] * $axis[1][1] + $n[2] * $axis[2][1];
                    $nz = $n[0] * $axis[0][2] + $n[1] * $axis[1][2] + $n[2] * $axis[2][2];
                    // ComputeColors ∥ ComputeTexCoords — lightingDiffuse applies with env ST too
                    if ($useDiffuse) {
                        $incoming = $nx * $ld[0] + $ny * $ld[1] + $nz * $ld[2];
                        if ($incoming <= 0.0) {
                            $this->ffi->glColor4f($ambient[0], $ambient[1], $ambient[2], 1.0);
                        } else {
                            $this->ffi->glColor4f(
                                min(1.0, $ambient[0] + $incoming * $directed[0]),
                                min(1.0, $ambient[1] + $incoming * $directed[1]),
                                min(1.0, $ambient[2] + $incoming * $directed[2]),
                                1.0,
                            );
                        }
                    }
                    if ($envView !== null) {
                        [$es, $et] = $this->environmentStForVertex(
                            $pt[0], $pt[1], $pt[2], $nx, $ny, $nz, $envView, $stage, $time,
                        );
                        $this->ffi->glTexCoord2f($es, $et);
                    } else {
                        $st = $surf['st'][$idx];
                        $this->ffi->glTexCoord2f($st[0], $st[1]);
                    }
                    $this->ffi->glVertex3f($pt[0], $pt[1], $pt[2]);
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
                $this->ffi->glMatrixMode(0x1702);
                $this->ffi->glLoadIdentity();
                $this->ffi->glMatrixMode(self::GL_MODELVIEW);
                $pass++;
            }
            if ($pass === 0) {
                $tex = $surfaceTex[$key] ?? $this->textureCache[$key] ?? $this->whiteTex;
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
                $this->binds++;
                $this->ffi->glDisable(self::GL_BLEND);
                $this->ffi->glDepthMask(1);
                // No shader stages — still apply lightgrid diffuse (RF_MINLIGHT via entityLightingAt)
                $this->ffi->glBegin(self::GL_TRIANGLES);
                foreach ($surf['indices'] as $idx) {
                    $v = $verts[$idx];
                    $pt = \Quake3\Game\QuakeMath::transformPoint($v, $origin, $axis);
                    $n = $surf['normals'][$idx] ?? [0.0, 0.0, 1.0];
                    $nx = $n[0] * $axis[0][0] + $n[1] * $axis[1][0] + $n[2] * $axis[2][0];
                    $ny = $n[0] * $axis[0][1] + $n[1] * $axis[1][1] + $n[2] * $axis[2][1];
                    $nz = $n[0] * $axis[0][2] + $n[1] * $axis[1][2] + $n[2] * $axis[2][2];
                    $incoming = $nx * $ld[0] + $ny * $ld[1] + $nz * $ld[2];
                    if ($incoming <= 0.0) {
                        $this->ffi->glColor3f($ambient[0], $ambient[1], $ambient[2]);
                    } else {
                        $this->ffi->glColor3f(
                            min(1.0, $ambient[0] + $incoming * $directed[0]),
                            min(1.0, $ambient[1] + $incoming * $directed[1]),
                            min(1.0, $ambient[2] + $incoming * $directed[2]),
                        );
                    }
                    $st = $surf['st'][$idx];
                    $this->ffi->glTexCoord2f($st[0], $st[1]);
                    $this->ffi->glVertex3f($pt[0], $pt[1], $pt[2]);
                }
                $this->ffi->glEnd();
                $this->drawCalls++;
            }
        }
        if ($wireframe) {
            $this->ffi->glPolygonMode(self::GL_FRONT_AND_BACK, self::GL_FILL);
        }
        $this->disableTexGen();
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDisable(self::GL_ALPHA_TEST);
        $this->ffi->glDepthFunc(self::GL_LEQUAL);
        $this->ffi->glDepthMask(1);
        $this->ffi->glColor3f(1, 1, 1);
    }

    /** @deprecated use drawViewWeaponHierarchy */
    public function drawViewWeapon(
        Md3Model $model,
        array $eye,
        array $forward,
        array $right,
        array $up,
        float $gunX,
        float $gunY,
        float $gunZ,
        array $bob,
        int $frame,
        bool $muzzleFlash,
        array $surfaceTex = [],
    ): void {
        $axis = [$forward, [-$right[0], -$right[1], -$right[2]], $up];
        $ox = $eye[0] + $forward[0] * ($gunX + ($bob[0] ?? 0)) + $right[0] * ($gunY + ($bob[1] ?? 0)) + $up[0] * ($gunZ + ($bob[2] ?? 0));
        $oy = $eye[1] + $forward[1] * ($gunX + ($bob[0] ?? 0)) + $right[1] * ($gunY + ($bob[1] ?? 0)) + $up[1] * ($gunZ + ($bob[2] ?? 0));
        $oz = $eye[2] + $forward[2] * ($gunX + ($bob[0] ?? 0)) + $right[2] * ($gunY + ($bob[1] ?? 0)) + $up[2] * ($gunZ + ($bob[2] ?? 0));
        $this->drawViewWeaponHierarchy(
            ['gun' => ['origin' => [$ox, $oy, $oz], 'axis' => $axis], 'barrel' => null, 'flash' => null],
            ['gun' => $model],
            ['gun' => $surfaceTex],
            false,
            false,
            true,
        );
    }

    /** @param list<float> $origin @param list<float> $normal @param list<float> $color */
    public function addImpact(
        array $origin,
        array $normal,
        float $dieAt,
        array $color = [1.0, 0.85, 0.3],
        bool $textured = false,
        string $kind = 'bullet',
    ): void {
        $this->impacts[] = [
            'origin' => $origin,
            'normal' => $normal,
            'dieAt' => $dieAt,
            'born' => $this->time,
            'orientation' => (float) mt_rand(0, 359),
            'color' => $color,
            'textured' => $textured || $kind !== '',
            'kind' => $kind,
        ];
        // CG_MissileHitWall lasting mark (gfx/damage/*_mrk)
        if ($kind === 'plasma') {
            $this->addImpactMark($origin, $normal, 'gfx/damage/plasma_mrk', 16.0, true);
        } elseif ($kind === 'bullet' || $kind === 'shotgun') {
            $this->addImpactMark(
                $origin,
                $normal,
                'gfx/damage/bullet_mrk',
                $kind === 'shotgun' ? 4.0 : 8.0,
                false,
            );
        }
    }

    /**
     * CG_ImpactMark + trap_CM_MarkFragments / R_MarkFragments (planar+patch chop).
     * @param list<float> $origin @param list<float> $normal
     * @param list<float> $color
     */
    public function addImpactMark(
        array $origin,
        array $normal,
        string $path,
        float $radius,
        bool $alphaFade,
        array $color = [1.0, 1.0, 1.0],
        ?float $orientationDeg = null,
    ): void {
        if ($radius <= 0.0) {
            return;
        }
        if (count($this->marks) >= self::MAX_MARKS) {
            array_shift($this->marks);
        }
        $orientation = $orientationDeg ?? (float) (mt_rand() / mt_getrandmax() * 360.0);
        $polys = $this->projectMarkPolys($origin, $normal, $radius, $orientation);
        $this->marks[] = [
            'origin' => $origin,
            'normal' => $normal,
            'born' => $this->time,
            'path' => $path,
            'radius' => $radius,
            'orientation' => $orientation,
            'alphaFade' => $alphaFade,
            'color' => $color,
            'polys' => $polys,
        ];
    }

    /**
     * CG_PlayerShadow — temporary markShadow under feet (not stored in cg_marks).
     * @param list<float> $lerpOrigin player feet
     */
    public function drawPlayerShadow(array $lerpOrigin, float $yawDeg): void
    {
        if ($this->collision === null) {
            return;
        }
        $end = [$lerpOrigin[0], $lerpOrigin[1], $lerpOrigin[2] - 128.0];
        $tr = $this->collision->trace(
            $lerpOrigin,
            $end,
            [-15.0, -15.0, 0.0],
            [15.0, 15.0, 2.0],
            CollisionModel::MASK_PLAYERSOLID,
        );
        if ($tr->fraction === 1.0 || $tr->startSolid || $tr->allSolid) {
            return;
        }
        $alpha = 1.0 - $tr->fraction;
        $normal = $tr->planeNormal ?? [0.0, 0.0, 1.0];
        $origin = $tr->endPos;
        $polys = $this->projectMarkPolys($origin, $normal, 24.0, $yawDeg);
        if ($polys === []) {
            return;
        }
        $tex = $this->ensurePathTexture('gfx/damage/shadow');
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ZERO, self::GL_ONE_MINUS_SRC_COLOR);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_POLYGON_OFFSET_FILL);
        $this->ffi->glPolygonOffset(-1.0, -2.0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        $this->ffi->glColor3f($alpha, $alpha, $alpha);
        foreach ($polys as $poly) {
            if (count($poly) < 3) {
                continue;
            }
            $this->ffi->glBegin(self::GL_TRIANGLE_FAN);
            foreach ($poly as $v) {
                $this->ffi->glTexCoord2f($v['st'][0], $v['st'][1]);
                $this->ffi->glVertex3f($v['xyz'][0], $v['xyz'][1], $v['xyz'][2]);
            }
            $this->ffi->glEnd();
            $this->drawCalls++;
        }
        $this->ffi->glDisable(self::GL_POLYGON_OFFSET_FILL);
        $this->ffi->glColor3f(1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /**
     * Build projected mark polygon fans (shared by lasting scars + temporary shadow).
     * @param list<float> $origin @param list<float> $normal
     * @return list<list<array{xyz: list<float>, st: list<float>}>>
     */
    private function projectMarkPolys(
        array $origin,
        array $normal,
        float $radius,
        float $orientation,
    ): array {
        [$axis1, $axis2] = $this->markAxes($normal, $orientation);
        $texScale = 0.5 / $radius;
        $corners = [
            [
                $origin[0] - $radius * $axis1[0] - $radius * $axis2[0],
                $origin[1] - $radius * $axis1[1] - $radius * $axis2[1],
                $origin[2] - $radius * $axis1[2] - $radius * $axis2[2],
            ],
            [
                $origin[0] + $radius * $axis1[0] - $radius * $axis2[0],
                $origin[1] + $radius * $axis1[1] - $radius * $axis2[1],
                $origin[2] + $radius * $axis1[2] - $radius * $axis2[2],
            ],
            [
                $origin[0] + $radius * $axis1[0] + $radius * $axis2[0],
                $origin[1] + $radius * $axis1[1] + $radius * $axis2[1],
                $origin[2] + $radius * $axis1[2] + $radius * $axis2[2],
            ],
            [
                $origin[0] - $radius * $axis1[0] + $radius * $axis2[0],
                $origin[1] - $radius * $axis1[1] + $radius * $axis2[1],
                $origin[2] - $radius * $axis1[2] + $radius * $axis2[2],
            ],
        ];
        $nlen = sqrt($normal[0] ** 2 + $normal[1] ** 2 + $normal[2] ** 2) ?: 1.0;
        $nx = $normal[0] / $nlen;
        $ny = $normal[1] / $nlen;
        $nz = $normal[2] / $nlen;
        $projection = [-$nx * 20.0, -$ny * 20.0, -$nz * 20.0];

        $polys = [];
        if ($this->markTris !== []) {
            $frags = MarkFragments::project($corners, $projection, $this->markTris);
            foreach ($frags as $frag) {
                if (count($frag) < 3) {
                    continue;
                }
                $poly = [];
                foreach ($frag as $xyz) {
                    $ox = $xyz[0] + $nx * 0.5;
                    $oy = $xyz[1] + $ny * 0.5;
                    $oz = $xyz[2] + $nz * 0.5;
                    $dx = $ox - $origin[0];
                    $dy = $oy - $origin[1];
                    $dz = $oz - $origin[2];
                    $poly[] = [
                        'xyz' => [$ox, $oy, $oz],
                        'st' => [
                            0.5 + ($dx * $axis1[0] + $dy * $axis1[1] + $dz * $axis1[2]) * $texScale,
                            0.5 + ($dx * $axis2[0] + $dy * $axis2[1] + $dz * $axis2[2]) * $texScale,
                        ],
                    ];
                }
                $polys[] = $poly;
            }
        }

        if ($polys === []) {
            $verts = [];
            foreach ($corners as $corner) {
                $xyz = $corner;
                if ($this->collision !== null) {
                    $start = [
                        $corner[0] + $nx * 2.0,
                        $corner[1] + $ny * 2.0,
                        $corner[2] + $nz * 2.0,
                    ];
                    $end = [
                        $corner[0] - $nx * 20.0,
                        $corner[1] - $ny * 20.0,
                        $corner[2] - $nz * 20.0,
                    ];
                    $tr = $this->collision->trace(
                        $start,
                        $end,
                        [0.0, 0.0, 0.0],
                        [0.0, 0.0, 0.0],
                        CollisionModel::CONTENTS_SOLID,
                    );
                    if ($tr->fraction < 1.0) {
                        $xyz = [
                            $tr->endPos[0] + $nx * 0.5,
                            $tr->endPos[1] + $ny * 0.5,
                            $tr->endPos[2] + $nz * 0.5,
                        ];
                    } else {
                        $xyz = [
                            $corner[0] + $nx * 0.5,
                            $corner[1] + $ny * 0.5,
                            $corner[2] + $nz * 0.5,
                        ];
                    }
                } else {
                    $xyz = [
                        $corner[0] + $nx * 0.5,
                        $corner[1] + $ny * 0.5,
                        $corner[2] + $nz * 0.5,
                    ];
                }
                $dx = $xyz[0] - $origin[0];
                $dy = $xyz[1] - $origin[1];
                $dz = $xyz[2] - $origin[2];
                $verts[] = [
                    'xyz' => $xyz,
                    'st' => [
                        0.5 + ($dx * $axis1[0] + $dy * $axis1[1] + $dz * $axis1[2]) * $texScale,
                        0.5 + ($dx * $axis2[0] + $dy * $axis2[1] + $dz * $axis2[2]) * $texScale,
                    ],
                ];
            }
            $polys[] = $verts;
        }

        return $polys;
    }

    private function drawImpacts(): void
    {
        if ($this->impacts === []) {
            return;
        }
        $alive = [];
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $bulletA = [
            $this->ensurePathTexture('models/weaphits/bullet1'),
            $this->ensurePathTexture('models/weaphits/bullet2'),
            $this->ensurePathTexture('models/weaphits/bullet3'),
            0, // gfx/colors/black — skip draw
        ];
        $bulletB = [
            $this->ensurePathTexture('models/weaphits/bullet2'),
            $this->ensurePathTexture('models/weaphits/bullet3'),
            0,
            0,
        ];
        foreach ($this->impacts as $imp) {
            if ($imp['dieAt'] < $this->time) {
                continue;
            }
            $alive[] = $imp;
            $o = $imp['origin'];
            $n = $imp['normal'];
            $c = $imp['color'];
            $born = $imp['born'] ?? ($imp['dieAt'] - 0.6);
            $shaderTime = max(0.0, $this->time - $born);
            $orient = $imp['orientation'] ?? 0.0;
            // LE_EXPLOSION: axis on surface (not camera billboard)
            [$ax1, $ax2] = $this->markAxes($n, $orient);
            $ox = $o[0] + $n[0] * 0.75;
            $oy = $o[1] + $n[1] * 0.75;
            $oz = $o[2] + $n[2] * 0.75;
            $origin = [$ox, $oy, $oz];
            $kind = $imp['kind'] ?? ($imp['textured'] ? 'plasma' : '');
            if ($kind === 'plasma' || ($kind === '' && !empty($imp['textured']))) {
                // CG_MakeExplosion: ring02.md3 + plasmaExplosion customShader
                if ($this->impactRing !== null) {
                    $axis = $this->rotateAroundDirection($n, $orient);
                    $this->ffi->glEnable(self::GL_BLEND);
                    $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
                    $this->ffi->glDepthMask(0);
                    $this->ffi->glDisable(self::GL_CULL_FACE);
                    $this->drawMd3Oriented(
                        $this->impactRing,
                        $o,
                        $axis,
                        $this->impactRingTex,
                        0,
                        false,
                        null,
                        'plasmaExplosion',
                        $shaderTime,
                    );
                    // drawMd3Oriented resets blend — restore for remaining impacts
                    $this->ffi->glEnable(self::GL_BLEND);
                    $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
                    $this->ffi->glDepthMask(0);
                    $this->ffi->glDisable(self::GL_CULL_FACE);
                    $this->ffi->glEnable(self::GL_TEXTURE_2D);
                } else {
                    // Fallback flat plasmaboom if ring MD3 missing
                    $boomTex = $this->ensurePathTexture('models/weaphits/plasmaboom', false);
                    $r = 24.0;
                    $left = [$ax1[0] * $r, $ax1[1] * $r, $ax1[2] * $r];
                    $upv = [$ax2[0] * $r, $ax2[1] * $r, $ax2[2] * $r];
                    $glow = $this->evalWaveForm('inversesawtooth', 0.0, 1.0, 0.0, 1.5, $shaderTime);
                    $glow = max(0.0, min(1.0, $glow));
                    $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $boomTex);
                    $this->binds++;
                    $this->ffi->glColor3f($c[0] * $glow, $c[1] * $glow, $c[2] * $glow);
                    $this->emitQuadStamp($origin, $left, $upv);
                    $this->drawCalls++;
                }
            } elseif ($kind === 'bullet' || $kind === 'shotgun') {
                // CG_MakeExplosion: bullet.md3 + bulletExplosion customShader
                if ($this->impactBullet !== null) {
                    $axis = $this->rotateAroundDirection($n, $orient);
                    $this->ffi->glEnable(self::GL_BLEND);
                    $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
                    $this->ffi->glDepthMask(0);
                    $this->ffi->glDisable(self::GL_CULL_FACE);
                    $this->drawMd3Oriented(
                        $this->impactBullet,
                        $o,
                        $axis,
                        $this->impactBulletTex,
                        0,
                        false,
                        null,
                        'bulletExplosion',
                        $shaderTime,
                    );
                    $this->ffi->glEnable(self::GL_BLEND);
                    $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
                    $this->ffi->glDepthMask(0);
                    $this->ffi->glDisable(self::GL_CULL_FACE);
                    $this->ffi->glEnable(self::GL_TEXTURE_2D);
                } else {
                    // Fallback flat dual animMap if bullet MD3 missing
                    $r = $kind === 'shotgun' ? 8.0 : 12.0;
                    $left = [$ax1[0] * $r, $ax1[1] * $r, $ax1[2] * $r];
                    $upv = [$ax2[0] * $r, $ax2[1] * $r, $ax2[2] * $r];
                    $idx = ((int) floor($shaderTime * 5.0)) % 4;
                    if ($idx < 0) {
                        $idx += 4;
                    }
                    $w1 = max(0.0, min(1.0, $this->evalWaveForm('inversesawtooth', 0.0, 1.0, 0.0, 5.0, $shaderTime)));
                    $w2 = max(0.0, min(1.0, $this->evalWaveForm('sawtooth', 0.0, 1.0, 0.0, 5.0, $shaderTime)));
                    if ($bulletA[$idx] !== 0) {
                        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $bulletA[$idx]);
                        $this->binds++;
                        $this->ffi->glColor3f($c[0] * $w1, $c[1] * $w1, $c[2] * $w1);
                        $this->emitQuadStamp($origin, $left, $upv);
                        $this->drawCalls++;
                    }
                    if ($bulletB[$idx] !== 0) {
                        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $bulletB[$idx]);
                        $this->binds++;
                        $this->ffi->glColor3f($c[0] * $w2, $c[1] * $w2, $c[2] * $w2);
                        $this->emitQuadStamp($origin, $left, $upv);
                        $this->drawCalls++;
                    }
                }
            } else {
                $r = 6.0;
                $left = [$ax1[0] * $r, $ax1[1] * $r, $ax1[2] * $r];
                $upv = [$ax2[0] * $r, $ax2[1] * $r, $ax2[2] * $r];
                $this->ffi->glDisable(self::GL_TEXTURE_2D);
                $this->ffi->glColor3f($c[0], $c[1], $c[2]);
                $this->emitQuadStamp($origin, $left, $upv);
                $this->ffi->glEnable(self::GL_TEXTURE_2D);
                $this->drawCalls++;
            }
        }
        $this->impacts = $alive;
        $this->ffi->glColor3f(1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /** CG_AddMarks — lasting wall scars (MARK_TOTAL_TIME / MARK_FADE_TIME). */
    private function drawMarks(): void
    {
        if ($this->marks === []) {
            return;
        }
        $alive = [];
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_POLYGON_OFFSET_FILL);
        $this->ffi->glPolygonOffset(-1.0, -2.0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        foreach ($this->marks as $m) {
            $age = $this->time - $m['born'];
            if ($age > self::MARK_TOTAL_TIME) {
                continue;
            }
            $alive[] = $m;
            $fade = 1.0;
            $remaining = self::MARK_TOTAL_TIME - $age;
            if ($remaining < self::MARK_FADE_TIME) {
                $fade = max(0.0, $remaining / self::MARK_FADE_TIME);
            }
            $c = $m['color'];
            $tex = $this->ensurePathTexture($m['path']);
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            if ($m['alphaFade']) {
                // energyMark: GL_SRC_ALPHA GL_ONE_MINUS_SRC_ALPHA
                $this->ffi->glBlendFunc(self::GL_SRC_ALPHA, self::GL_ONE_MINUS_SRC_ALPHA);
                // cg_marks.c: RGB *= (450 - 450*age/3) when < 255; alpha only MARK_FADE_TIME
                $rgb = 1.0;
                $energyByte = 450.0 - 450.0 * ($age / 3.0);
                if ($energyByte < 255.0) {
                    $rgb = max(0.0, $energyByte / 255.0);
                }
                $this->ffi->glColor4f($c[0] * $rgb, $c[1] * $rgb, $c[2] * $rgb, $fade);
            } else {
                // bullet/burn: GL_ZERO GL_ONE_MINUS_SRC_COLOR (multiply darkening)
                $this->ffi->glBlendFunc(self::GL_ZERO, self::GL_ONE_MINUS_SRC_COLOR);
                $this->ffi->glColor3f($c[0] * $fade, $c[1] * $fade, $c[2] * $fade);
            }
            $polys = $m['polys'] ?? (isset($m['verts']) ? [$m['verts']] : []);
            if ($polys !== []) {
                foreach ($polys as $poly) {
                    if (count($poly) < 3) {
                        continue;
                    }
                    $this->ffi->glBegin(self::GL_TRIANGLE_FAN);
                    foreach ($poly as $v) {
                        $this->ffi->glTexCoord2f($v['st'][0], $v['st'][1]);
                        $this->ffi->glVertex3f($v['xyz'][0], $v['xyz'][1], $v['xyz'][2]);
                    }
                    $this->ffi->glEnd();
                    $this->drawCalls++;
                }
            } else {
                // legacy fallback
                [$ax, $ay] = $this->markAxes($m['normal'], $m['orientation']);
                $r = $m['radius'];
                $o = $m['origin'];
                $n = $m['normal'];
                $nlen = sqrt($n[0] * $n[0] + $n[1] * $n[1] + $n[2] * $n[2]) ?: 1.0;
                $ox = $o[0] + $n[0] / $nlen * 0.5;
                $oy = $o[1] + $n[1] / $nlen * 0.5;
                $oz = $o[2] + $n[2] / $nlen * 0.5;
                $rx = $ax[0] * $r;
                $ry = $ax[1] * $r;
                $rz = $ax[2] * $r;
                $ux = $ay[0] * $r;
                $uy = $ay[1] * $r;
                $uz = $ay[2] * $r;
                $this->ffi->glBegin(self::GL_QUADS);
                $this->ffi->glTexCoord2f(0, 0);
                $this->ffi->glVertex3f($ox - $rx - $ux, $oy - $ry - $uy, $oz - $rz - $uz);
                $this->ffi->glTexCoord2f(1, 0);
                $this->ffi->glVertex3f($ox + $rx - $ux, $oy + $ry - $uy, $oz + $rz - $uz);
                $this->ffi->glTexCoord2f(1, 1);
                $this->ffi->glVertex3f($ox + $rx + $ux, $oy + $ry + $uy, $oz + $rz + $uz);
                $this->ffi->glTexCoord2f(0, 1);
                $this->ffi->glVertex3f($ox - $rx + $ux, $oy - $ry + $uy, $oz - $rz + $uz);
                $this->ffi->glEnd();
                $this->drawCalls++;
            }
        }
        $this->marks = $alive;
        $this->ffi->glDisable(self::GL_POLYGON_OFFSET_FILL);
        $this->ffi->glColor4f(1, 1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /**
     * q_math.c RotateAroundDirection — axis[0] already set to direction.
     * @param list<float> $dir
     * @return list<list<float>>
     */
    private function rotateAroundDirection(array $dir, float $yawDeg): array
    {
        $nx = $dir[0];
        $ny = $dir[1];
        $nz = $dir[2];
        $nlen = sqrt($nx * $nx + $ny * $ny + $nz * $nz) ?: 1.0;
        $axis0 = [$nx / $nlen, $ny / $nlen, $nz / $nlen];
        // PerpendicularVector: project unit axis of smallest |component|
        $minelem = abs($axis0[0]);
        $pos = 0;
        for ($i = 1; $i < 3; $i++) {
            if (abs($axis0[$i]) < $minelem) {
                $minelem = abs($axis0[$i]);
                $pos = $i;
            }
        }
        $temp = [0.0, 0.0, 0.0];
        $temp[$pos] = 1.0;
        // ProjectPointOnPlane onto plane of axis0
        $d = $temp[0] * $axis0[0] + $temp[1] * $axis0[1] + $temp[2] * $axis0[2];
        $axis1 = [
            $temp[0] - $d * $axis0[0],
            $temp[1] - $d * $axis0[1],
            $temp[2] - $d * $axis0[2],
        ];
        $alen = sqrt($axis1[0] * $axis1[0] + $axis1[1] * $axis1[1] + $axis1[2] * $axis1[2]) ?: 1.0;
        $axis1 = [$axis1[0] / $alen, $axis1[1] / $alen, $axis1[2] / $alen];
        if ($yawDeg !== 0.0) {
            $axis1 = $this->rotatePointAroundVector($axis0, $axis1, $yawDeg);
            $alen = sqrt($axis1[0] * $axis1[0] + $axis1[1] * $axis1[1] + $axis1[2] * $axis1[2]) ?: 1.0;
            $axis1 = [$axis1[0] / $alen, $axis1[1] / $alen, $axis1[2] / $alen];
        }
        $axis2 = [
            $axis0[1] * $axis1[2] - $axis0[2] * $axis1[1],
            $axis0[2] * $axis1[0] - $axis0[0] * $axis1[2],
            $axis0[0] * $axis1[1] - $axis0[1] * $axis1[0],
        ];
        return [$axis0, $axis1, $axis2];
    }

    /**
     * Build mark tangent frame (cg_marks.c axis[1]/axis[2] after orientation).
     * @param list<float> $normal
     * @return array{0: list<float>, 1: list<float>}
     */
    private function markAxes(array $normal, float $orientationDeg): array
    {
        $nx = $normal[0];
        $ny = $normal[1];
        $nz = $normal[2];
        $nlen = sqrt($nx * $nx + $ny * $ny + $nz * $nz) ?: 1.0;
        $nx /= $nlen;
        $ny /= $nlen;
        $nz /= $nlen;
        // PerpendicularVector
        if (abs($nz) > 0.7) {
            $px = 0.0;
            $py = 1.0;
            $pz = 0.0;
        } else {
            $px = 0.0;
            $py = 0.0;
            $pz = 1.0;
        }
        // axis1 = normalize(cross(n, p)) then rotate around n by orientation
        $ax = $ny * $pz - $nz * $py;
        $ay = $nz * $px - $nx * $pz;
        $az = $nx * $py - $ny * $px;
        $alen = sqrt($ax * $ax + $ay * $ay + $az * $az) ?: 1.0;
        $ax /= $alen;
        $ay /= $alen;
        $az /= $alen;
        $rad = deg2rad($orientationDeg);
        $cos = cos($rad);
        $sin = sin($rad);
        // Rodrigues: rotate axis1 around normal
        $dot = $nx * $ax + $ny * $ay + $nz * $az;
        $rx = $ax * $cos + ($ny * $az - $nz * $ay) * $sin + $nx * $dot * (1.0 - $cos);
        $ry = $ay * $cos + ($nz * $ax - $nx * $az) * $sin + $ny * $dot * (1.0 - $cos);
        $rz = $az * $cos + ($nx * $ay - $ny * $ax) * $sin + $nz * $dot * (1.0 - $cos);
        $rlen = sqrt($rx * $rx + $ry * $ry + $rz * $rz) ?: 1.0;
        $rx /= $rlen;
        $ry /= $rlen;
        $rz /= $rlen;
        // axis[2] = Rotate(perp around normal); axis[1] = Cross(normal, axis[2])
        // (cg_marks.c) — return [axis1, axis2] for st0/st1
        $bx = $ny * $rz - $nz * $ry;
        $by = $nz * $rx - $nx * $rz;
        $bz = $nx * $ry - $ny * $rx;
        return [[$bx, $by, $bz], [$rx, $ry, $rz]];
    }

    /** Plasma bolt sprite (CG_Missile RT_SPRITE radius 16, sprites/plasma1 → plasmaa). */
    public function drawPlasmaBolt(array $origin): void
    {
        $tex = $this->ensurePathTexture('sprites/plasmaa', false);
        [$right, $up] = $this->cameraRightUp();
        $r = 16.0;
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        // sprites/plasma1: tcMod rotate 931 (RB_CalcRotateTexCoords)
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glTranslatef(0.5, 0.5, 0.0);
        $this->ffi->glRotatef(-931.0 * $this->time, 0.0, 0.0, 1.0);
        $this->ffi->glTranslatef(-0.5, -0.5, 0.0);
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glColor3f(1.0, 1.0, 1.0);
        $left = [-$right[0] * $r, -$right[1] * $r, -$right[2] * $r];
        $upv = [$up[0] * $r, $up[1] * $r, $up[2] * $r];
        $this->emitQuadStamp($origin, $left, $upv);
        $this->ffi->glMatrixMode(0x1702);
        $this->ffi->glLoadIdentity();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->resetGlState();
    }

    /**
     * CG_PlasmaTrail — spawn LE_MOVE_SCALE_FADE railDisc spark at bolt.
     * Throttled to 50ms (step in id source). Sets lifeRate (id omits it; trail would be invisible).
     * @param list<float> $origin @param list<float> $fwd bolt travel direction (normalized-ish)
     */
    public function spawnPlasmaTrail(array $origin, array $fwd): void
    {
        // crandom() in [-1,1]
        $cr = static fn (): float => (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
        $velLocal = [
            60.0 - 120.0 * $cr(),
            40.0 - 80.0 * $cr(),
            100.0 - 200.0 * $cr(),
        ];
        // Build axis from travel direction (AnglesToAxis substitute)
        $fx = $fwd[0];
        $fy = $fwd[1];
        $fz = $fwd[2];
        $flen = sqrt($fx * $fx + $fy * $fy + $fz * $fz) ?: 1.0;
        $fx /= $flen;
        $fy /= $flen;
        $fz /= $flen;
        if (abs($fz) < 0.9) {
            $rx = -$fy;
            $ry = $fx;
            $rz = 0.0;
        } else {
            $rx = 0.0;
            $ry = -$fz;
            $rz = $fy;
        }
        $rlen = sqrt($rx * $rx + $ry * $ry + $rz * $rz) ?: 1.0;
        $rx /= $rlen;
        $ry /= $rlen;
        $rz /= $rlen;
        $ux = $fy * $rz - $fz * $ry;
        $uy = $fz * $rx - $fx * $rz;
        $uz = $fx * $ry - $fy * $rx;
        // offset 2,2,2 in local axes
        $ox = $origin[0] + 2.0 * $fx + 2.0 * $rx + 2.0 * $ux;
        $oy = $origin[1] + 2.0 * $fy + 2.0 * $ry + 2.0 * $uy;
        $oz = $origin[2] + 2.0 * $fz + 2.0 * $rz + 2.0 * $uz;
        $vx = $velLocal[0] * $fx + $velLocal[1] * $rx + $velLocal[2] * $ux;
        $vy = $velLocal[0] * $fy + $velLocal[1] * $ry + $velLocal[2] * $uy;
        $vz = $velLocal[0] * $fz + $velLocal[1] * $rz + $velLocal[2] * $uz;
        if (count($this->plasmaTrail) > 256) {
            array_shift($this->plasmaTrail);
        }
        $this->plasmaTrail[] = [
            'origin' => [$ox, $oy, $oz],
            'velocity' => [$vx, $vy, $vz],
            'born' => $this->time,
            'end' => $this->time + 0.6,
        ];
    }

    /** CG_AddMoveScaleFade for plasma trail sprites (railDisc). */
    private function drawPlasmaTrail(): void
    {
        if ($this->plasmaTrail === []) {
            return;
        }
        $alive = [];
        $tex = $this->ensurePathTexture('gfx/misc/raildisc_mono2', false);
        [$right, $up] = $this->cameraRightUp();
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        // flashDlightColor 0.6,0.6,1 → le->color * 0.2
        $baseR = 0.6 * 0.2;
        $baseG = 0.6 * 0.2;
        $baseB = 1.0 * 0.2;
        $g = 800.0; // DEFAULT_GRAVITY
        foreach ($this->plasmaTrail as $p) {
            if ($this->time >= $p['end']) {
                continue;
            }
            $alive[] = $p;
            $life = $p['end'] - $p['born'];
            $c = ($p['end'] - $this->time) / ($life > 0 ? $life : 1.0); // lifeRate-correct fade
            $t = $this->time - $p['born'];
            $o = $p['origin'];
            $v = $p['velocity'];
            $px = $o[0] + $v[0] * $t;
            $py = $o[1] + $v[1] * $t;
            $pz = $o[2] + $v[2] * $t - 0.5 * $g * $t * $t;
            // radius = le->radius*(1-c)+8 with le->radius≈0.25
            $r = 0.25 * (1.0 - $c) + 8.0;
            $a = $c * 0.25;
            $this->ffi->glColor3f($baseR * $a * 4.0, $baseG * $a * 4.0, $baseB * $a * 4.0);
            // RB_SurfaceSprite: left=viewaxis[1]=−cameraRight, rotate in left/up plane
            $leftDir = [-$right[0], -$right[1], -$right[2]];
            $ang = deg2rad(-30.0 * $this->time); // mild spin (shader tcMod rotate approx)
            $cosA = cos($ang);
            $sinA = sin($ang);
            $left = [
                ($leftDir[0] * $cosA - $up[0] * $sinA) * $r,
                ($leftDir[1] * $cosA - $up[1] * $sinA) * $r,
                ($leftDir[2] * $cosA - $up[2] * $sinA) * $r,
            ];
            $upv = [
                ($up[0] * $cosA + $leftDir[0] * $sinA) * $r,
                ($up[1] * $cosA + $leftDir[1] * $sinA) * $r,
                ($up[2] * $cosA + $leftDir[2] * $sinA) * $r,
            ];
            $this->emitQuadStamp([$px, $py, $pz], $left, $upv);
        }
        $this->plasmaTrail = $alive;
        $this->ffi->glColor3f(1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /**
     * CG_SmokePuff — shotgunSmokePuff (LEF_PUFF_DONT_SCALE).
     * @param list<float> $origin @param list<float> $velocity
     */
    public function spawnSmokePuff(
        array $origin,
        array $velocity,
        float $radius = 32.0,
        float $alpha = 0.33,
        float $durationSec = 0.9,
    ): void {
        if (count($this->smokePuffs) > 64) {
            array_shift($this->smokePuffs);
        }
        $this->smokePuffs[] = [
            'origin' => $origin,
            'velocity' => $velocity,
            'born' => $this->time,
            'end' => $this->time + $durationSec,
            'radius' => $radius,
            'alpha' => $alpha,
        ];
    }

    /** CG_AddMoveScaleFade for smoke (no gravity, fixed radius). */
    private function drawSmokePuffs(): void
    {
        if ($this->smokePuffs === []) {
            return;
        }
        $alive = [];
        $tex = $this->ensurePathTexture('gfx/misc/smokepuff2b', false);
        [$right, $up] = $this->cameraRightUp();
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_SRC_ALPHA, self::GL_ONE_MINUS_SRC_ALPHA);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        foreach ($this->smokePuffs as $p) {
            if ($this->time >= $p['end']) {
                continue;
            }
            $alive[] = $p;
            $life = $p['end'] - $p['born'];
            $c = ($p['end'] - $this->time) / ($life > 0 ? $life : 1.0);
            $t = $this->time - $p['born'];
            $o = $p['origin'];
            $v = $p['velocity'];
            $px = $o[0] + $v[0] * $t;
            $py = $o[1] + $v[1] * $t;
            $pz = $o[2] + $v[2] * $t;
            $r = $p['radius'];
            $a = $c * $p['alpha'];
            $this->ffi->glColor4f(1.0, 1.0, 1.0, $a);
            $left = [-$right[0] * $r, -$right[1] * $r, -$right[2] * $r];
            $upv = [$up[0] * $r, $up[1] * $r, $up[2] * $r];
            $this->emitQuadStamp([$px, $py, $pz], $left, $upv);
        }
        $this->smokePuffs = $alive;
        $this->ffi->glColor4f(1, 1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /**
     * CG_Tracer — additive streak along MG bullet path. Returns false if too short.
     * @param list<float> $source muzzle @param list<float> $dest impact
     */
    public function spawnTracer(array $source, array $dest): bool
    {
        $forward = [
            $dest[0] - $source[0],
            $dest[1] - $source[1],
            $dest[2] - $source[2],
        ];
        $len = sqrt($forward[0] ** 2 + $forward[1] ** 2 + $forward[2] ** 2);
        if ($len < 100.0) {
            return false;
        }
        $forward = [$forward[0] / $len, $forward[1] / $len, $forward[2] / $len];
        $tracerLength = 100.0; // cg_tracerlength
        $tracerWidth = 1.0; // cg_tracerwidth
        $begin = 50.0 + (mt_rand() / mt_getrandmax()) * ($len - 60.0);
        $end = $begin + $tracerLength;
        if ($end > $len) {
            $end = $len;
        }
        $start = [
            $source[0] + $forward[0] * $begin,
            $source[1] + $forward[1] * $begin,
            $source[2] + $forward[2] * $begin,
        ];
        $finish = [
            $source[0] + $forward[0] * $end,
            $source[1] + $forward[1] * $end,
            $source[2] + $forward[2] * $end,
        ];
        // Defer right-axis to draw (needs camera axes from modelview)
        if (count($this->tracers) > 32) {
            array_shift($this->tracers);
        }
        $this->tracers[] = [
            'start' => $start,
            'finish' => $finish,
            'forward' => $forward,
            'width' => $tracerWidth,
        ];
        return true;
    }

    /** Draw queued CG_Tracer polys (one frame), then clear. */
    private function drawTracers(): void
    {
        if ($this->tracers === []) {
            return;
        }
        [$viewRight, $viewUp] = $this->cameraRightUp();
        $tex = $this->ensurePathTexture('gfx/misc/tracer2', false);
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        $this->ffi->glColor3f(1, 1, 1);
        foreach ($this->tracers as $tr) {
            $fwd = $tr['forward'];
            $line0 = $fwd[0] * $viewRight[0] + $fwd[1] * $viewRight[1] + $fwd[2] * $viewRight[2];
            $line1 = $fwd[0] * $viewUp[0] + $fwd[1] * $viewUp[1] + $fwd[2] * $viewUp[2];
            $right = [
                $viewRight[0] * $line1 - $viewUp[0] * $line0,
                $viewRight[1] * $line1 - $viewUp[1] * $line0,
                $viewRight[2] * $line1 - $viewUp[2] * $line0,
            ];
            $rl = sqrt($right[0] ** 2 + $right[1] ** 2 + $right[2] ** 2);
            if ($rl < 1e-6) {
                $right = $viewRight;
            } else {
                $right = [$right[0] / $rl, $right[1] / $rl, $right[2] / $rl];
            }
            $w = $tr['width'];
            $s = $tr['start'];
            $f = $tr['finish'];
            // CG_Tracer verts ST layout
            $this->ffi->glBegin(self::GL_QUADS);
            $this->ffi->glTexCoord2f(0, 1);
            $this->ffi->glVertex3f($f[0] + $right[0] * $w, $f[1] + $right[1] * $w, $f[2] + $right[2] * $w);
            $this->ffi->glTexCoord2f(1, 0);
            $this->ffi->glVertex3f($f[0] - $right[0] * $w, $f[1] - $right[1] * $w, $f[2] - $right[2] * $w);
            $this->ffi->glTexCoord2f(1, 1);
            $this->ffi->glVertex3f($s[0] - $right[0] * $w, $s[1] - $right[1] * $w, $s[2] - $right[2] * $w);
            $this->ffi->glTexCoord2f(0, 0);
            $this->ffi->glVertex3f($s[0] + $right[0] * $w, $s[1] + $right[1] * $w, $s[2] + $right[2] * $w);
            $this->ffi->glEnd();
            $this->drawCalls++;
        }
        $this->tracers = [];
        $this->ffi->glColor3f(1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /**
     * CG_MachineGunEjectBrass / CG_ShotgunEjectBrass — LE_FRAGMENT shells.
     * @param 'mg'|'sg' $kind
     * @param list<float> $lerpOrigin player feet
     * @param list<float> $viewangles pitch,yaw,roll
     */
    public function spawnBrass(string $kind, array $lerpOrigin, array $viewangles, ?float $now = null): void
    {
        $t = $now ?? $this->time;
        $v = QuakeMath::anglesToAxis($viewangles);
        $brassTime = 2.5; // cg_brassTime 2500
        $count = $kind === 'sg' ? 2 : 1;
        for ($i = 0; $i < $count; $i++) {
            if (count($this->brassFragments) > 64) {
                array_shift($this->brassFragments);
            }
            $crand = static fn (): float => (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $rand = static fn (): float => mt_rand() / mt_getrandmax();
            if ($kind === 'sg') {
                $velLocal = [
                    60.0 + 60.0 * $crand(),
                    ($i === 0 ? 40.0 : -40.0) + 10.0 * $crand(),
                    100.0 + 50.0 * $crand(),
                ];
                $offset = [8.0, 0.0, 24.0];
                $life = $brassTime * 3.0 + $brassTime * $rand();
                $bounce = 0.3;
                $angDelta = [1.0, 0.5, 0.0];
                $trTime = $t;
            } else {
                $velLocal = [
                    0.0,
                    -50.0 + 40.0 * $crand(),
                    100.0 + 50.0 * $crand(),
                ];
                $offset = [8.0, -4.0, 24.0];
                $life = $brassTime + ($brassTime / 4.0) * $rand();
                $bounce = 0.4;
                $angDelta = [2.0, 1.0, 0.0];
                $trTime = $t - (mt_rand(0, 15) / 1000.0);
            }
            $origin = [
                $lerpOrigin[0] + $offset[0] * $v[0][0] + $offset[1] * $v[1][0] + $offset[2] * $v[2][0],
                $lerpOrigin[1] + $offset[0] * $v[0][1] + $offset[1] * $v[1][1] + $offset[2] * $v[2][1],
                $lerpOrigin[2] + $offset[0] * $v[0][2] + $offset[1] * $v[1][2] + $offset[2] * $v[2][2],
            ];
            $trDelta = [
                $velLocal[0] * $v[0][0] + $velLocal[1] * $v[1][0] + $velLocal[2] * $v[2][0],
                $velLocal[0] * $v[0][1] + $velLocal[1] * $v[1][1] + $velLocal[2] * $v[2][1],
                $velLocal[0] * $v[0][2] + $velLocal[1] * $v[1][2] + $velLocal[2] * $v[2][2],
            ];
            $this->brassFragments[] = [
                'kind' => $kind,
                'origin' => $origin,
                'trBase' => $origin,
                'trDelta' => $trDelta,
                'trTime' => $trTime,
                'trType' => 'gravity',
                'angBase' => [
                    (float) (mt_rand(0, 31)),
                    (float) (mt_rand(0, 31)),
                    (float) (mt_rand(0, 31)),
                ],
                'angDelta' => $angDelta,
                'bounce' => $bounce,
                'born' => $t,
                'end' => $t + $life,
            ];
        }
    }

    /** CG_AddFragment for LE_FRAGMENT brass. */
    private function drawBrassFragments(): void
    {
        if ($this->brassFragments === []) {
            return;
        }
        $alive = [];
        $sinkTime = 1.0; // SINK_TIME 1000ms
        $gravity = 800.0;
        $frameMs = 16.0; // ~cg.frametime for stop heuristic
        foreach ($this->brassFragments as $le) {
            if ($this->time >= $le['end']) {
                continue;
            }
            $model = $le['kind'] === 'sg' ? $this->brassSg : $this->brassMg;
            $tex = $le['kind'] === 'sg' ? $this->brassSgTex : $this->brassMgTex;
            if ($model === null) {
                continue;
            }

            if ($le['trType'] === 'stationary') {
                $origin = $le['origin'];
                $remain = $le['end'] - $this->time;
                if ($remain < $sinkTime) {
                    $origin = [
                        $origin[0],
                        $origin[1],
                        $origin[2] - 16.0 * (1.0 - $remain / $sinkTime),
                    ];
                }
                $angles = [
                    $le['angBase'][0] + $le['angDelta'][0] * ($this->time - $le['born']),
                    $le['angBase'][1] + $le['angDelta'][1] * ($this->time - $le['born']),
                    $le['angBase'][2] + $le['angDelta'][2] * ($this->time - $le['born']),
                ];
                $axis = QuakeMath::anglesToAxis($angles);
                $this->drawMd3Oriented($model, $origin, $axis, $tex, 0, false, $le['origin']);
                $alive[] = $le;
                continue;
            }

            $dt = $this->time - $le['trTime'];
            $newOrigin = [
                $le['trBase'][0] + $le['trDelta'][0] * $dt,
                $le['trBase'][1] + $le['trDelta'][1] * $dt,
                $le['trBase'][2] + $le['trDelta'][2] * $dt - 0.5 * $gravity * $dt * $dt,
            ];
            $prev = $le['origin'];
            $fraction = 1.0;
            $hitNormal = null;
            $endPos = $newOrigin;
            $allSolid = false;
            if ($this->collision !== null) {
                $tr = $this->collision->trace(
                    $prev,
                    $newOrigin,
                    [0.0, 0.0, 0.0],
                    [0.0, 0.0, 0.0],
                    CollisionModel::CONTENTS_SOLID,
                );
                $fraction = $tr->fraction;
                $endPos = $tr->endPos;
                $hitNormal = $tr->planeNormal;
                $allSolid = $tr->allSolid;
            }

            if ($fraction === 1.0) {
                $le['origin'] = $newOrigin;
                $adt = $this->time - $le['born'];
                $angles = [
                    $le['angBase'][0] + $le['angDelta'][0] * $adt,
                    $le['angBase'][1] + $le['angDelta'][1] * $adt,
                    $le['angBase'][2] + $le['angDelta'][2] * $adt,
                ];
                $axis = QuakeMath::anglesToAxis($angles);
                $this->drawMd3Oriented($model, $le['origin'], $axis, $tex);
                $alive[] = $le;
                continue;
            }

            // CG_ReflectVelocity
            $vel = [
                $le['trDelta'][0],
                $le['trDelta'][1],
                $le['trDelta'][2] - $gravity * $dt,
            ];
            $n = $hitNormal ?? [0.0, 0.0, 1.0];
            $dot = $vel[0] * $n[0] + $vel[1] * $n[1] + $vel[2] * $n[2];
            $le['trDelta'] = [
                ($vel[0] - 2.0 * $dot * $n[0]) * $le['bounce'],
                ($vel[1] - 2.0 * $dot * $n[1]) * $le['bounce'],
                ($vel[2] - 2.0 * $dot * $n[2]) * $le['bounce'],
            ];
            $le['trBase'] = $endPos;
            $le['trTime'] = $this->time;
            $le['origin'] = $endPos;
            if (
                $allSolid
                || ($n[2] > 0.0 && (
                    $le['trDelta'][2] < 40.0
                    || $le['trDelta'][2] < -$frameMs * $le['trDelta'][2]
                ))
            ) {
                $le['trType'] = 'stationary';
            }
            $angles = [
                $le['angBase'][0] + $le['angDelta'][0] * ($this->time - $le['born']),
                $le['angBase'][1] + $le['angDelta'][1] * ($this->time - $le['born']),
                $le['angBase'][2] + $le['angDelta'][2] * ($this->time - $le['born']),
            ];
            $axis = QuakeMath::anglesToAxis($angles);
            $this->drawMd3Oriented($model, $le['origin'], $axis, $tex);
            $alive[] = $le;
        }
        $this->brassFragments = $alive;
    }

    /**
     * CG_SpawnEffect — telep.md3 + teleportEffect, LE_FADE_RGB 500ms, Z−24.
     * @param list<float> $origin player feet / teleport point
     */
    public function spawnTeleportEffect(array $origin, ?float $now = null): void
    {
        $t = $now ?? $this->time;
        if (count($this->teleportEffects) > 16) {
            array_shift($this->teleportEffects);
        }
        $this->teleportEffects[] = [
            'origin' => [$origin[0], $origin[1], $origin[2] - 24.0],
            'born' => $t,
            'dieAt' => $t + 0.5,
        ];
    }

    private function drawTeleportEffects(): void
    {
        if ($this->teleportEffects === [] || $this->teleportModel === null) {
            return;
        }
        $alive = [];
        $identity = [
            [1.0, 0.0, 0.0],
            [0.0, 1.0, 0.0],
            [0.0, 0.0, 1.0],
        ];
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        foreach ($this->teleportEffects as $fx) {
            if ($this->time >= $fx['dieAt']) {
                continue;
            }
            $alive[] = $fx;
            $life = $fx['dieAt'] - $fx['born'];
            $c = ($fx['dieAt'] - $this->time) / ($life > 0 ? $life : 1.0);
            $this->drawMd3Oriented(
                $this->teleportModel,
                $fx['origin'],
                $identity,
                $this->teleportModelTex,
                0,
                false,
                null,
                'teleportEffect',
                $fx['born'],
                [$c, $c, $c, 1.0],
            );
            $this->ffi->glEnable(self::GL_BLEND);
            $this->ffi->glBlendFunc(self::GL_ONE, self::GL_ONE);
            $this->ffi->glDepthMask(0);
            $this->ffi->glDisable(self::GL_CULL_FACE);
        }
        $this->teleportEffects = $alive;
        $this->ffi->glColor3f(1, 1, 1);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
    }

    /** Lazy-load a single texture path into the cache. $repeat=false for clampMap soft sprites. */
    private function ensurePathTexture(string $path, bool $repeat = true): int
    {
        if (isset($this->textureCache[$path])) {
            $tex = $this->textureCache[$path];
            if (!$repeat) {
                // Soft sprites need CLAMP even if path was first uploaded as REPEAT
                $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
                $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_WRAP_S, self::GL_CLAMP_TO_EDGE);
                $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_WRAP_T, self::GL_CLAMP_TO_EDGE);
            }
            return $tex;
        }
        if ($this->materials !== null) {
            $img = $this->materials->resolveStageImage($path)
                ?? $this->materials->resolveImage($path);
            if ($img !== null) {
                $scaled = $this->lighting->lightScaleRgba($img->rgba);
                $this->textureCache[$path] = $this->createTexture(
                    $scaled,
                    $img->width,
                    $img->height,
                    $repeat,
                    true,
                );
                return $this->textureCache[$path];
            }
        }
        $this->ensureWhiteTexture();
        return $this->whiteTex;
    }

    /** Ortho HUD: CG_DrawStatusBar digits + CG_DrawAmmoWarning + CG_DrawWeaponSelect + CG_DrawCrosshair (640×480 virtual). */
    public function drawHud(
        int $width,
        int $height,
        int $health,
        int $armor,
        int $ammo,
        string $weaponName,
        int $ammoWarning = 0,
        ?Player $player = null,
        ?WeaponState $weaponState = null,
    ): void
    {
        $this->ffi->glMatrixMode(self::GL_PROJECTION);
        $this->ffi->glPushMatrix();
        $this->ffi->glLoadIdentity();
        // Q3 2D: y grows downward in 640×480; map to GL bottom-left
        $this->ffi->glOrtho(0, 640, 480, 0, -1, 1);
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glPushMatrix();
        $this->ffi->glLoadIdentity();
        // Scale virtual 640×480 into the real viewport
        $this->ffi->glViewport(0, 0, $width, $height);
        $this->ffi->glDisable(self::GL_DEPTH_TEST);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_SRC_ALPHA, self::GL_ONE_MINUS_SRC_ALPHA);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glDepthMask(0);

        $charW = 32.0;
        $charH = 48.0;
        $icon = 48.0;
        $y = 432.0;

        // CG_Draw2D: status / ammo warn / crosshair / weapon select only while alive
        if ($health > 0) {
            // ammo (left) — CG_DrawStatusBar 3D ammoModel or 2D icon
            // Q3: grey while WEAPON_FIRING && weaponTime > 100ms (colors[2])
            $ammoColor = [1.0, 0.69, 0.0, 1.0];
            if ($ammo < 0) {
                $ammoColor = [1.0, 0.2, 0.2, 1.0];
            } elseif (
                $weaponState !== null
                && $weaponState->state === WeaponState::FIRING
                && $weaponState->weaponTime > 0.1
            ) {
                $ammoColor = [0.5, 0.5, 0.5, 1.0];
            }
            $this->drawHudField(0.0, $y, 3, $ammo, $ammoColor, $charW, $charH);
            $ammoX = 3 * $charW + 4.0;
            if (isset($this->hudAmmo[$weaponName])) {
                $am = $this->hudAmmo[$weaponName];
                // origin 70,0,0 — yaw 90 + 20*sin(t)
                $this->drawHud3DModel(
                    $ammoX,
                    $y,
                    $icon,
                    $icon,
                    $width,
                    $height,
                    $am['model'],
                    $am['tex'],
                    [70.0, 0.0, 0.0],
                    90.0 + 20.0 * sin($this->time),
                );
            } else {
                $ammoIcon = match ($weaponName) {
                    'shotgun' => 'icons/icona_shotgun',
                    'plasma' => 'icons/icona_plasma',
                    default => 'icons/icona_machinegun',
                };
                $this->drawHudPic($ammoX, $y, $icon, $icon, $ammoIcon, [1, 1, 1, 1]);
            }

            // health (center)
            if ($health > 100) {
                $hc = [1.0, 1.0, 1.0, 1.0];
            } elseif ($health > 25) {
                $hc = [1.0, 0.69, 0.0, 1.0];
            } else {
                // (cg.time >> 8) & 1 → colors[0] orange / colors[1] red
                $hc = ((((int) floor($this->time * 1000.0)) >> 8) & 1) === 0
                    ? [1.0, 0.69, 0.0, 1.0]
                    : [1.0, 0.2, 0.2, 1.0];
            }
            $this->drawHudField(185.0, $y, 3, $health, $hc, $charW, $charH);

            // CG_DrawStatusBarHead — 3D sarge head (or health icon fallback)
            $iconSize = 48.0;
            $headX = 185.0 + 3 * $charW + 4.0;
            if ($this->hudHead !== null) {
                $this->drawStatusBarHead($headX, $width, $height, $iconSize);
            } else {
                $this->drawHudPic($headX, $y, $icon, $icon, 'icons/iconh_green', [1, 1, 1, 1]);
            }

            // armor — 3D armor_yel or 2D icon
            if ($armor > 0) {
                $this->drawHudField(370.0, $y, 3, $armor, [1.0, 0.69, 0.0, 1.0], $charW, $charH);
                $armorX = 370.0 + 3 * $charW + 4.0;
                if ($this->hudArmor !== null) {
                    // yaw = (cg.time & 2047) * 360 / 2048
                    $ms = (int) floor($this->time * 1000.0);
                    $yaw = (($ms & 2047) * 360.0) / 2048.0;
                    $this->drawHud3DModel(
                        $armorX,
                        $y,
                        $icon,
                        $icon,
                        $width,
                        $height,
                        $this->hudArmor,
                        $this->hudArmorTex,
                        [90.0, 0.0, -10.0],
                        $yaw,
                    );
                } else {
                    $this->drawHudPic($armorX, $y, $icon, $icon, 'icons/iconr_yellow', [1, 1, 1, 1]);
                }
            }

            // CG_DrawAmmoWarning — bigchars at y=64
            if ($ammoWarning > 0) {
                $s = $ammoWarning === 2 ? 'OUT OF AMMO' : 'LOW AMMO WARNING';
                $w = strlen($s) * 16.0;
                $this->drawHudBigString(320.0 - $w * 0.5, 64.0, $s, 1.0);
            }

            // CG_DrawWeaponSelect — icon strip after weapon key / pickup
            if ($player !== null && $weaponState !== null) {
                $this->drawWeaponSelect($player, $weaponState);
            }

            // CG_DrawPickupItem — lower-left icon + name (3s fade)
            if ($this->itemPickupClass !== '') {
                $alpha = $this->fadeColorAlpha($this->itemPickupTime, 3.0);
                if ($alpha !== null) {
                    $info = $this->pickupItemInfo($this->itemPickupClass);
                    if ($info !== null) {
                        $py = 480.0 - 96.0; // CG_DrawLowerLeft: 480-ICON then DrawPickupItem y-=ICON → 384
                        $this->drawHudPic(8.0, $py, 48.0, 48.0, $info['icon'], [1.0, 1.0, 1.0, $alpha]);
                        // ICON_SIZE+16, vertically centered vs BIGCHAR_HEIGHT 16
                        $this->drawHudBigString(64.0, $py + (48.0 / 2.0 - 16.0 / 2.0), $info['name'], $alpha);
                    }
                }
            }

            // CG_DrawCrosshair — cg_drawCrosshair 4 → crosshaire; cg_crosshairHealth 1
            $cw = 24.0;
            $ch = 24.0;
            // ITEM_BLOB_TIME 200ms — pulse size on pickup
            $f = $this->time - $this->itemPickupBlendTime;
            if ($f > 0.0 && $f < 0.2) {
                $scale = 1.0 + ($f / 0.2);
                $cw *= $scale;
                $ch *= $scale;
            }
            $xh = $this->colorForHealth($health, $armor);
            $this->drawHudPic(320.0 - $cw * 0.5, 240.0 - $ch * 0.5, $cw, $ch, 'gfx/2d/crosshaire', $xh);
        }

        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glEnable(self::GL_CULL_FACE);
        $this->ffi->glPopMatrix();
        $this->ffi->glMatrixMode(self::GL_PROJECTION);
        $this->ffi->glPopMatrix();
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glColor4f(1, 1, 1, 1);
    }

    /**
     * CG_Draw3DModel for status-bar ammo/armor (fov 30, RDF_NOWORLDMODEL).
     * Camera at origin looking +X Quake; entity at $entOrigin with yaw around Z.
     * @param list<float> $entOrigin
     * @param array<string, int> $surfaceTex
     */
    private function drawHud3DModel(
        float $x,
        float $y,
        float $w,
        float $h,
        int $screenW,
        int $screenH,
        Md3Model $model,
        array $surfaceTex,
        array $entOrigin,
        float $yawDeg,
    ): void {
        $sx = (int) floor($x / 640.0 * $screenW);
        $syTop = (int) floor($y / 480.0 * $screenH);
        $sw = max(1, (int) floor($w / 640.0 * $screenW));
        $sh = max(1, (int) floor($h / 480.0 * $screenH));
        $glY = $screenH - $syTop - $sh;

        $this->ffi->glViewport($sx, $glY, $sw, $sh);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glClear(self::GL_DEPTH_BUFFER_BIT);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glEnable(self::GL_CULL_FACE);

        $proj = (new \Quake3\Render\FlyCamera())->projectionMatrix(1.0, 30.0, 0.5, 200.0);
        $this->ffi->glMatrixMode(self::GL_PROJECTION);
        $this->loadMatrix(self::GL_PROJECTION, $proj);
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glLoadIdentity();

        $rad = deg2rad($yawDeg);
        $c = cos($rad);
        $s = sin($rad);
        $il = $this->lighting->identityLight;
        $this->ffi->glColor3f($il, $il, $il);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);

        foreach ($model->surfaces as $si => $surf) {
            $key = $surf['shader'] !== '' ? $surf['shader'] : $surf['name'];
            $tex = $surfaceTex[$key]
                ?? $surfaceTex[$surf['name']]
                ?? $this->textureCache[$key]
                ?? $this->whiteTex;
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            $verts = $model->frameVerts($si, 0);
            $this->ffi->glBegin(self::GL_TRIANGLES);
            foreach ($surf['indices'] as $idx) {
                $v = $verts[$idx];
                $st = $surf['st'][$idx];
                // yaw around Quake Z, then translate
                $rx = $v[0] * $c - $v[1] * $s;
                $ry = $v[0] * $s + $v[1] * $c;
                $rz = $v[2];
                $qx = $rx + $entOrigin[0];
                $qy = $ry + $entOrigin[1];
                $qz = $rz + $entOrigin[2];
                // Quake (X fwd, Y left, Z up) → GL (look −Z): (Y, Z, −X)
                $this->ffi->glTexCoord2f($st[0], $st[1]);
                $this->ffi->glVertex3f($qy, $qz, -$qx);
            }
            $this->ffi->glEnd();
            $this->drawCalls++;
        }

        // Restore HUD 2D state
        $this->ffi->glViewport(0, 0, $screenW, $screenH);
        $this->ffi->glMatrixMode(self::GL_PROJECTION);
        $this->ffi->glLoadIdentity();
        $this->ffi->glOrtho(0, 640, 480, 0, -1, 1);
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glLoadIdentity();
        $this->ffi->glDisable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_SRC_ALPHA, self::GL_ONE_MINUS_SRC_ALPHA);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        $this->ffi->glColor4f(1, 1, 1, 1);
    }

    /**
     * CG_DrawStatusBarHead + CG_DrawHead / CG_Draw3DModel (fov 30, RDF_NOWORLDMODEL).
     */
    private function drawStatusBarHead(float $x, int $screenW, int $screenH, float $iconSize): void
    {
        if ($this->hudHead === null) {
            return;
        }
        $size = $iconSize * 1.25;
        // Damage kick stretch (cg_draw.c DAMAGE_TIME 500ms)
        if ($this->damageTime > -900.0 && ($this->time - $this->damageTime) < 0.5 && $this->damageValue > 0.0) {
            $frac = ($this->time - $this->damageTime) / 0.5;
            $size = $iconSize * 1.25 * (1.5 - $frac * 0.5);
            $stretch = $size - $iconSize * 1.25;
            $x -= $stretch * 0.5 + $this->damageX * $stretch * 0.5;
            $this->headStartYaw = 180.0 + $this->damageX * 45.0;
            $cr = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $this->headEndYaw = 180.0 + 20.0 * cos($cr * M_PI);
            $cr = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $this->headEndPitch = 5.0 * cos($cr * M_PI);
            $this->headStartTime = $this->time;
            $this->headEndTime = $this->time + 0.1 + (mt_rand() / mt_getrandmax()) * 2.0;
        } elseif ($this->time >= $this->headEndTime) {
            // Idle look-around (cg_draw.c)
            $this->headStartYaw = $this->headEndYaw;
            $this->headStartPitch = $this->headEndPitch;
            $this->headStartTime = $this->headEndTime;
            $this->headEndTime = $this->time + 0.1 + (mt_rand() / mt_getrandmax()) * 2.0;
            $cr = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $this->headEndYaw = 180.0 + 20.0 * cos($cr * M_PI);
            $cr = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $this->headEndPitch = 5.0 * cos($cr * M_PI);
        }
        if ($this->headStartTime > $this->time) {
            $this->headStartTime = $this->time;
        }
        $span = max(1e-3, $this->headEndTime - $this->headStartTime);
        $frac = ($this->time - $this->headStartTime) / $span;
        $frac = max(0.0, min(1.0, $frac));
        $frac = $frac * $frac * (3.0 - 2.0 * $frac);
        $yaw = $this->headStartYaw + ($this->headEndYaw - $this->headStartYaw) * $frac;
        $pitch = $this->headStartPitch + ($this->headEndPitch - $this->headStartPitch) * $frac;

        $y = 480.0 - $size;
        // Virtual 640×480 → screen pixels (y grows down in Q3)
        $sx = (int) floor($x / 640.0 * $screenW);
        $syTop = (int) floor($y / 480.0 * $screenH);
        $sw = max(1, (int) floor($size / 640.0 * $screenW));
        $sh = max(1, (int) floor($size / 480.0 * $screenH));
        $glY = $screenH - $syTop - $sh; // GL viewport origin is bottom-left

        $b = $this->hudHead->bounds(0);
        $len = 0.7 * ($b['maxs'][2] - $b['mins'][2]);
        $dist = $len / 0.268;
        $midX = 0.5 * ($b['mins'][0] + $b['maxs'][0]);
        $midY = 0.5 * ($b['mins'][1] + $b['maxs'][1]);
        $midZ = 0.5 * ($b['mins'][2] + $b['maxs'][2]);

        $this->ffi->glViewport($sx, $glY, $sw, $sh);
        $this->ffi->glEnable(self::GL_DEPTH_TEST);
        $this->ffi->glClear(self::GL_DEPTH_BUFFER_BIT);
        $this->ffi->glDepthMask(1);
        $this->ffi->glDisable(self::GL_BLEND);
        $this->ffi->glEnable(self::GL_CULL_FACE);

        $proj = (new \Quake3\Render\FlyCamera())->projectionMatrix(1.0, 30.0, 0.5, 100.0);
        $this->ffi->glMatrixMode(self::GL_PROJECTION);
        $this->loadMatrix(self::GL_PROJECTION, $proj);
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glLoadIdentity();
        // Face camera at yaw≈180; OpenGL looks down −Z
        $this->ffi->glTranslatef(0.0, 0.0, -$dist);
        $this->ffi->glRotatef(-$pitch, 1.0, 0.0, 0.0);
        $this->ffi->glRotatef($yaw - 180.0, 0.0, 1.0, 0.0);
        $this->ffi->glTranslatef(-$midX, -$midZ, $midY);

        $this->ffi->glColor3f(1.0, 1.0, 1.0);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
        foreach ($this->hudHead->surfaces as $si => $surf) {
            $key = $surf['shader'] !== '' ? $surf['shader'] : $surf['name'];
            $tex = $this->hudHeadTex[$key]
                ?? $this->hudHeadTex[$surf['name']]
                ?? $this->textureCache[$key]
                ?? $this->whiteTex;
            $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
            $this->binds++;
            $verts = $this->hudHead->frameVerts($si, 0);
            $this->ffi->glBegin(self::GL_TRIANGLES);
            foreach ($surf['indices'] as $idx) {
                $v = $verts[$idx];
                $st = $surf['st'][$idx];
                $this->ffi->glTexCoord2f($st[0], $st[1]);
                // Quake Z-up → GL Y-up
                $this->ffi->glVertex3f($v[0], $v[2], -$v[1]);
            }
            $this->ffi->glEnd();
            $this->drawCalls++;
        }

        // Restore HUD 2D state
        $this->ffi->glViewport(0, 0, $screenW, $screenH);
        $this->ffi->glMatrixMode(self::GL_PROJECTION);
        $this->ffi->glLoadIdentity();
        $this->ffi->glOrtho(0, 640, 480, 0, -1, 1);
        $this->ffi->glMatrixMode(self::GL_MODELVIEW);
        $this->ffi->glLoadIdentity();
        $this->ffi->glDisable(self::GL_DEPTH_TEST);
        $this->ffi->glDepthMask(0);
        $this->ffi->glDisable(self::GL_CULL_FACE);
        $this->ffi->glEnable(self::GL_BLEND);
        $this->ffi->glBlendFunc(self::GL_SRC_ALPHA, self::GL_ONE_MINUS_SRC_ALPHA);
        $this->ffi->glEnable(self::GL_TEXTURE_2D);
    }

    /**
     * CG_DrawWeaponSelect — owned weapon icons + select frame + noammo + name (1.4s fade).
     */
    private function drawWeaponSelect(Player $player, WeaponState $ws): void
    {
        $alpha = $this->fadeColorAlpha($ws->selectTime, 1.4);
        if ($alpha === null) {
            return;
        }
        // CG_DrawWeaponSelect: cg.itemPickupTime = 0 while strip is up
        $this->itemPickupTime = -999.0;
        $color = [1.0, 1.0, 1.0, $alpha];

        $owned = [];
        foreach (
            [
                Player::WP_MACHINEGUN => ['icons/iconw_machinegun', 'Machinegun'],
                Player::WP_SHOTGUN => ['icons/iconw_shotgun', 'Shotgun'],
                Player::WP_PLASMA => ['icons/iconw_plasma', 'Plasma Gun'],
            ] as $wp => [$icon, $name]
        ) {
            if (($player->weapons & (1 << $wp)) === 0) {
                continue;
            }
            $owned[] = [
                'wp' => $wp,
                'icon' => $icon,
                'name' => $name,
                'ammo' => $player->ammo[$wp] ?? 0,
            ];
        }
        $count = count($owned);
        if ($count === 0) {
            return;
        }

        $x = 320.0 - $count * 20.0;
        $y = 380.0;
        foreach ($owned as $slot) {
            $this->drawHudPic($x, $y, 32.0, 32.0, $slot['icon'], $color);
            if ($slot['wp'] === $ws->selectWeapon) {
                $this->drawHudPic($x - 4.0, $y - 4.0, 40.0, 40.0, 'gfx/2d/select', $color);
            }
            if ($slot['ammo'] <= 0) {
                $this->drawHudPic($x, $y, 32.0, 32.0, 'icons/noammo', $color);
            }
            $x += 40.0;
        }

        $selName = null;
        foreach ($owned as $slot) {
            if ($slot['wp'] === $ws->selectWeapon) {
                $selName = $slot['name'];
                break;
            }
        }
        if ($selName !== null) {
            $w = strlen($selName) * 16.0;
            $this->drawHudBigString((640.0 - $w) * 0.5, $y - 22.0, $selName, $alpha);
        }
    }

    /** CG_FadeColor — returns alpha or null if expired. */
    private function fadeColorAlpha(float $startSec, float $totalSec): ?float
    {
        if ($startSec < -900.0) {
            return null;
        }
        $t = $this->time - $startSec;
        if ($t >= $totalSec) {
            return null;
        }
        $fade = 0.2;
        if ($totalSec - $t < $fade) {
            return ($totalSec - $t) / $fade;
        }

        return 1.0;
    }

    /**
     * bg_misc itemlist icon + pickup_name for q3dm0 classnames.
     * @return array{icon: string, name: string}|null
     */
    private function pickupItemInfo(string $classname): ?array
    {
        return match ($classname) {
            'item_health_small' => ['icon' => 'icons/iconh_green', 'name' => '5 Health'],
            'item_health' => ['icon' => 'icons/iconh_yellow', 'name' => '25 Health'],
            'item_health_large' => ['icon' => 'icons/iconh_red', 'name' => '50 Health'],
            'item_health_mega' => ['icon' => 'icons/iconh_mega', 'name' => 'Mega Health'],
            'item_armor_shard' => ['icon' => 'icons/iconr_shard', 'name' => 'Armor Shard'],
            'item_armor_combat' => ['icon' => 'icons/iconr_yellow', 'name' => 'Armor'],
            'item_armor_body' => ['icon' => 'icons/iconr_red', 'name' => 'Heavy Armor'],
            'weapon_shotgun' => ['icon' => 'icons/iconw_shotgun', 'name' => 'Shotgun'],
            'weapon_machinegun' => ['icon' => 'icons/iconw_machinegun', 'name' => 'Machinegun'],
            'weapon_plasmagun' => ['icon' => 'icons/iconw_plasma', 'name' => 'Plasma Gun'],
            'ammo_shells' => ['icon' => 'icons/icona_shotgun', 'name' => 'Shells'],
            'ammo_bullets' => ['icon' => 'icons/icona_machinegun', 'name' => 'Bullets'],
            'ammo_cells' => ['icon' => 'icons/icona_plasma', 'name' => 'Cells'],
            default => null,
        };
    }

    /**
     * CG_DrawBigString — gfx/2d/bigchars atlas, drop shadow, 16×16.
     */
    private function drawHudBigString(float $x, float $y, string $s, float $alpha): void
    {
        $cw = 16.0;
        $ch = 16.0;
        $shadow = [0.0, 0.0, 0.0, $alpha];
        $color = [1.0, 1.0, 1.0, $alpha];
        $xx = $x;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $this->drawHudChar($xx + 2.0, $y + 2.0, $cw, $ch, ord($s[$i]), $shadow);
            $xx += $cw;
        }
        $xx = $x;
        for ($i = 0; $i < $len; $i++) {
            $this->drawHudChar($xx, $y, $cw, $ch, ord($s[$i]), $color);
            $xx += $cw;
        }
    }

    /**
     * CG_DrawChar — 16×16 cells in gfx/2d/bigchars (row = ch>>4, col = ch&15).
     * @param list<float> $rgba
     */
    private function drawHudChar(float $x, float $y, float $w, float $h, int $ch, array $rgba): void
    {
        $ch &= 255;
        if ($ch === 32) {
            return;
        }
        $row = $ch >> 4;
        $col = $ch & 15;
        $size = 0.0625;
        $fcol = $col * $size;
        $frow = $row * $size;
        $tex = $this->ensureHudTexture('gfx/2d/bigchars');
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        $this->ffi->glColor4f($rgba[0], $rgba[1], $rgba[2], $rgba[3] ?? 1.0);
        $this->ffi->glBegin(self::GL_QUADS);
        $this->ffi->glTexCoord2f($fcol, $frow);
        $this->ffi->glVertex2f($x, $y);
        $this->ffi->glTexCoord2f($fcol + $size, $frow);
        $this->ffi->glVertex2f($x + $w, $y);
        $this->ffi->glTexCoord2f($fcol + $size, $frow + $size);
        $this->ffi->glVertex2f($x + $w, $y + $h);
        $this->ffi->glTexCoord2f($fcol, $frow + $size);
        $this->ffi->glVertex2f($x, $y + $h);
        $this->ffi->glEnd();
        $this->drawCalls++;
    }

    /**
     * CG_DrawField — large status digits from gfx/2d/numbers/*_32b.
     * @param list<float> $rgba
     */
    private function drawHudField(float $x, float $y, int $width, int $value, array $rgba, float $charW, float $charH): void
    {
        if ($width < 1) {
            return;
        }
        if ($width > 5) {
            $width = 5;
        }
        $max = match ($width) {
            1 => 9,
            2 => 99,
            3 => 999,
            4 => 9999,
            default => 99999,
        };
        $value = max(0, min($max, $value));
        $num = (string) $value;
        $l = strlen($num);
        if ($l > $width) {
            $l = $width;
        }
        $x += 2.0 + $charW * ($width - $l);
        $names = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        for ($i = 0; $i < $l; $i++) {
            $d = (int) $num[$i];
            $path = 'gfx/2d/numbers/' . $names[$d] . '_32b';
            $this->drawHudPic($x, $y, $charW, $charH, $path, $rgba);
            $x += $charW;
        }
    }

    /**
     * CG_GetColorForHealth (cg_drawtools.c) — crosshair / head tint.
     * @return list<float>
     */
    private function colorForHealth(int $health, int $armor): array
    {
        if ($health <= 0) {
            return [0.0, 0.0, 0.0, 1.0];
        }
        $count = $armor;
        $max = (int) ($health * 0.66 / (1.0 - 0.66));
        if ($max < $count) {
            $count = $max;
        }
        $h = $health + $count;
        $r = 1.0;
        $g = 1.0;
        $b = 1.0;
        if ($h >= 100) {
            $b = 1.0;
        } elseif ($h < 66) {
            $b = 0.0;
        } else {
            $b = ($h - 66) / 33.0;
        }
        if ($h > 60) {
            $g = 1.0;
        } elseif ($h < 30) {
            $g = 0.0;
        } else {
            $g = ($h - 30) / 30.0;
        }
        return [$r, $g, $b, 1.0];
    }

    /**
     * CG_DrawPic in 640×480 virtual space (y down).
     * @param list<float> $rgba
     */
    private function drawHudPic(float $x, float $y, float $w, float $h, string $path, array $rgba): void
    {
        $tex = $this->ensureHudTexture($path);
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $tex);
        $this->binds++;
        $this->ffi->glColor4f($rgba[0], $rgba[1], $rgba[2], $rgba[3] ?? 1.0);
        $this->ffi->glBegin(self::GL_QUADS);
        $this->ffi->glTexCoord2f(0, 0);
        $this->ffi->glVertex2f($x, $y);
        $this->ffi->glTexCoord2f(1, 0);
        $this->ffi->glVertex2f($x + $w, $y);
        $this->ffi->glTexCoord2f(1, 1);
        $this->ffi->glVertex2f($x + $w, $y + $h);
        $this->ffi->glTexCoord2f(0, 1);
        $this->ffi->glVertex2f($x, $y + $h);
        $this->ffi->glEnd();
        $this->drawCalls++;
    }

    /** HUD/UI textures: no world lightScale (identity RGB). */
    private function ensureHudTexture(string $path): int
    {
        $key = 'hud:' . $path;
        if (isset($this->textureCache[$key])) {
            return $this->textureCache[$key];
        }
        if ($this->materials !== null) {
            $img = $this->materials->resolveImage($path);
            if ($img !== null) {
                $this->textureCache[$key] = $this->createTexture(
                    $img->rgba,
                    $img->width,
                    $img->height,
                    false,
                    false,
                );
                return $this->textureCache[$key];
            }
        }
        $this->ensureWhiteTexture();
        return $this->whiteTex;
    }

    public function ensureMd3Textures(Md3Model $model, MaterialResolver $materials): array
    {
        $map = [];
        foreach ($model->surfaces as $surf) {
            foreach ([$surf['shader'], $surf['name']] as $key) {
                if ($key === '') {
                    continue;
                }
                $shaderName = $this->stripShaderExt($key);
                // Upload shader stage maps (envmap, overlays, animMap frames) like ensureShaderTextures
                if ($this->shaderParser !== null) {
                    foreach ($this->shaderParser->get($shaderName)->stages as $st) {
                        if ($st->isLightmap || $st->map === '' || str_starts_with($st->map, '$')) {
                            continue;
                        }
                        $maps = $st->animFrames !== [] ? $st->animFrames : [$st->map];
                        foreach ($maps as $mapKey) {
                            if ($mapKey === '' || str_starts_with($mapKey, '$') || isset($this->textureCache[$mapKey])) {
                                continue;
                            }
                            $img = $materials->resolveStageImage($mapKey);
                            if ($img !== null) {
                                $scaled = $this->lighting->lightScaleRgba($img->rgba);
                                $this->textureCache[$mapKey] = $this->createTexture(
                                    $scaled,
                                    $img->width,
                                    $img->height,
                                    !$st->clamp,
                                    true,
                                );
                            }
                        }
                    }
                }
                if (isset($this->textureCache[$key])) {
                    $map[$key] = $this->textureCache[$key];
                    continue;
                }
                $img = $materials->resolveImage($key);
                if ($img !== null) {
                    $scaled = $this->lighting->lightScaleRgba($img->rgba);
                    $this->textureCache[$key] = $this->createTexture($scaled, $img->width, $img->height, true, true);
                    $map[$key] = $this->textureCache[$key];
                } elseif ($this->shaderParser !== null) {
                    // Prefer first non-LM stage as the surface key binding
                    foreach ($this->shaderParser->get($shaderName)->stages as $st) {
                        if (!$st->isLightmap && $st->map !== '' && isset($this->textureCache[$st->map])) {
                            $map[$key] = $this->textureCache[$st->map];
                            $this->textureCache[$key] = $this->textureCache[$st->map];
                            break;
                        }
                    }
                }
            }
        }
        return $map;
    }

    public function lastDrawCalls(): int
    {
        return $this->drawCalls;
    }

    public function lastBinds(): int
    {
        return $this->binds;
    }

    public function lastCulled(): int
    {
        return $this->culled;
    }

    public function batchCount(): int
    {
        return count($this->gpuBatches);
    }

    /** @param list<float> $mins @param list<float> $maxs */
    public function drawDebugBox(array $mins, array $maxs, float $r, float $g, float $b): void
    {
        $this->ffi->glDisable(self::GL_TEXTURE_2D);
        $this->ffi->glColor3f($r, $g, $b);
        $this->ffi->glBegin(self::GL_LINES);
        $corners = [
            [$mins[0], $mins[1], $mins[2]], [$maxs[0], $mins[1], $mins[2]],
            [$maxs[0], $maxs[1], $mins[2]], [$mins[0], $maxs[1], $mins[2]],
            [$mins[0], $mins[1], $maxs[2]], [$maxs[0], $mins[1], $maxs[2]],
            [$maxs[0], $maxs[1], $maxs[2]], [$mins[0], $maxs[1], $maxs[2]],
        ];
        $edges = [[0, 1], [1, 2], [2, 3], [3, 0], [4, 5], [5, 6], [6, 7], [7, 4], [0, 4], [1, 5], [2, 6], [3, 7]];
        foreach ($edges as [$a, $b]) {
            $this->ffi->glVertex3f($corners[$a][0], $corners[$a][1], $corners[$a][2]);
            $this->ffi->glVertex3f($corners[$b][0], $corners[$b][1], $corners[$b][2]);
        }
        $this->ffi->glEnd();
        $this->ffi->glColor3f(1, 1, 1);
    }

    /** @param list<float> $m 16 floats column-major */
    public function loadMatrix(int $mode, array $m): void
    {
        $this->ffi->glMatrixMode($mode);
        $buf = $this->ffi->new('float[16]');
        for ($i = 0; $i < 16; $i++) {
            $buf[$i] = $m[$i];
        }
        $this->ffi->glLoadMatrixf($buf);
    }

    public function uploadTexture(DecodedImage $img, bool $repeat = true): int
    {
        return $this->createTexture($img->rgba, $img->width, $img->height, $repeat, true);
    }

    public function destroyMesh(): void
    {
        $this->destroyWorld();
    }

    public function destroyWorld(): void
    {
        foreach ($this->gpuBatches as $b) {
            $this->ffi->glDeleteBuffers(1, FFI::addr($b['vbo']));
            $this->ffi->glDeleteBuffers(1, FFI::addr($b['ibo']));
        }
        $this->gpuBatches = [];
        foreach ($this->gpuMoverBatches as $list) {
            foreach ($list as $b) {
                $this->ffi->glDeleteBuffers(1, FFI::addr($b['vbo']));
                $this->ffi->glDeleteBuffers(1, FFI::addr($b['ibo']));
            }
        }
        $this->gpuMoverBatches = [];
        foreach ($this->textureCache as $id) {
            if ($id !== $this->whiteTex) {
                $tid = $this->ffi->new('unsigned int');
                $tid->cdata = $id;
                $this->ffi->glDeleteTextures(1, FFI::addr($tid));
            }
        }
        $this->textureCache = [];
        foreach ($this->lightmapTex as $id) {
            $tid = $this->ffi->new('unsigned int');
            $tid->cdata = $id;
            $this->ffi->glDeleteTextures(1, FFI::addr($tid));
        }
        $this->lightmapTex = [];
        foreach ($this->skyBoxTex as $id) {
            if ($id !== $this->whiteTex) {
                $tid = $this->ffi->new('unsigned int');
                $tid->cdata = $id;
                $this->ffi->glDeleteTextures(1, FFI::addr($tid));
            }
        }
        $this->skyBoxTex = [];
        $this->skyMaterial = null;
        $this->flares = [];
        $this->flareFadeState = [];
        $this->impacts = [];
        $this->marks = [];
        $this->plasmaTrail = [];
        $this->skyShader = null;
    }

    private function ensureWhiteTexture(): void
    {
        if ($this->whiteTex !== 0) {
            return;
        }
        $rgba = str_repeat("\xFF\xFF\xFF\xFF", 4);
        $this->whiteTex = $this->createTexture($rgba, 2, 2, false, false);
    }

    private function createTexture(string $rgba, int $w, int $h, bool $repeat, bool $mipmap): int
    {
        $id = $this->ffi->new('unsigned int');
        $this->ffi->glGenTextures(1, FFI::addr($id));
        $this->ffi->glBindTexture(self::GL_TEXTURE_2D, $id->cdata);
        $wrap = $repeat ? self::GL_REPEAT : self::GL_CLAMP_TO_EDGE;
        $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_WRAP_S, $wrap);
        $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_WRAP_T, $wrap);
        $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_MAG_FILTER, self::GL_LINEAR);
        if ($mipmap) {
            $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_MIN_FILTER, self::GL_LINEAR_MIPMAP_LINEAR);
            $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_GENERATE_MIPMAP, 1);
        } else {
            $this->ffi->glTexParameteri(self::GL_TEXTURE_2D, self::GL_TEXTURE_MIN_FILTER, self::GL_LINEAR);
        }
        $buf = $this->ffi->new('unsigned char[' . strlen($rgba) . ']');
        FFI::memcpy($buf, $rgba, strlen($rgba));
        $this->ffi->glTexImage2D(
            self::GL_TEXTURE_2D,
            0,
            self::GL_RGBA,
            $w,
            $h,
            0,
            self::GL_RGBA,
            self::GL_UNSIGNED_BYTE,
            $buf,
        );
        return (int) $id->cdata;
    }

    private function checkError(string $where): void
    {
        if (!$this->glDebug) {
            return;
        }
        $err = $this->ffi->glGetError();
        if ($err !== 0) {
            fwrite(STDERR, "GL error 0x" . dechex($err) . " at {$where}\n");
        }
    }

    private function offsetPtr(int $bytes): mixed
    {
        return $this->ffi->cast('void *', $bytes);
    }

    private function resolveLibrary(): string
    {
        $candidates = [
            getenv('OPENGL_LIB') ?: '',
            '/System/Library/Frameworks/OpenGL.framework/OpenGL',
            '/usr/lib/libGL.so.1',
            'libGL.so.1',
            'opengl32.dll',
        ];
        foreach ($candidates as $path) {
            if ($path === '') {
                continue;
            }
            if (str_contains($path, '/') || str_contains($path, '\\')) {
                if (is_file($path) || is_link($path)) {
                    return $path;
                }
                if (str_contains($path, 'OpenGL.framework') && file_exists($path)) {
                    return $path;
                }
            } else {
                return $path;
            }
        }
        throw new \RuntimeException('OpenGL library not found. Set OPENGL_LIB if needed.');
    }

    private function cdef(): string
    {
        return <<<'C'
            void glViewport(int x, int y, int width, int height);
            void glClearColor(float r, float g, float b, float a);
            void glClear(unsigned int mask);
            void glEnable(unsigned int cap);
            void glDisable(unsigned int cap);
            void glColor3f(float r, float g, float b);
            void glMatrixMode(unsigned int mode);
            void glLoadMatrixf(const float *m);
            void glLoadIdentity(void);
            void glPushMatrix(void);
            void glPopMatrix(void);
            void glClipPlane(unsigned int plane, const double *equation);
            void glOrtho(double left, double right, double bottom, double top, double zNear, double zFar);
            void glCullFace(unsigned int mode);
            void glDepthMask(unsigned char flag);
            void glDepthFunc(unsigned int func);
            void glBlendFunc(unsigned int sfactor, unsigned int dfactor);
            void glPolygonOffset(float factor, float units);
            void glAlphaFunc(unsigned int func, float ref);
            void glColor4f(float r, float g, float b, float a);
            void glTranslatef(float x, float y, float z);
            void glScalef(float x, float y, float z);
            void glRotatef(float angle, float x, float y, float z);
            unsigned int glGetError(void);
            void glGetFloatv(unsigned int pname, float *params);
            void glGenBuffers(int n, unsigned int *buffers);
            void glDeleteBuffers(int n, const unsigned int *buffers);
            void glBindBuffer(unsigned int target, unsigned int buffer);
            void glBufferData(unsigned int target, long size, const void *data, unsigned int usage);
            void glEnableClientState(unsigned int array);
            void glDisableClientState(unsigned int array);
            void glVertexPointer(int size, unsigned int type, int stride, const void *pointer);
            void glNormalPointer(unsigned int type, int stride, const void *pointer);
            void glColorPointer(int size, unsigned int type, int stride, const void *pointer);
            void glTexCoordPointer(int size, unsigned int type, int stride, const void *pointer);
            void glTexGeni(unsigned int coord, unsigned int pname, int param);
            void glDrawElements(unsigned int mode, int count, unsigned int type, const void *indices);
            void glGenTextures(int n, unsigned int *textures);
            void glDeleteTextures(int n, const unsigned int *textures);
            void glBindTexture(unsigned int target, unsigned int texture);
            void glTexImage2D(unsigned int target, int level, int internalformat, int width, int height, int border, unsigned int format, unsigned int type, const void *pixels);
            void glTexParameteri(unsigned int target, unsigned int pname, int param);
            void glTexEnvi(unsigned int target, unsigned int pname, int param);
            void glActiveTexture(unsigned int texture);
            void glClientActiveTexture(unsigned int texture);
            void glDepthRange(double nearVal, double farVal);
            void glPolygonMode(unsigned int face, unsigned int mode);
            void glBegin(unsigned int mode);
            void glEnd(void);
            void glVertex3f(float x, float y, float z);
            void glVertex2f(float x, float y);
            void glNormal3f(float x, float y, float z);
            void glTexCoord2f(float s, float t);
            void glReadPixels(int x, int y, int width, int height, unsigned int format, unsigned int type, void *pixels);
        C;
    }
}
