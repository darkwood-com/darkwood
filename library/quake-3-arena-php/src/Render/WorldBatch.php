<?php

declare(strict_types=1);

namespace Quake3\Render;

    /**
     * One draw batch: same diffuse + lightmap page.
     */
    final class WorldBatch
    {
        /**
         * @param string $vertexBinary interleaved float32 xyz+st+lm+normal (10 floats)
         * @param string $indexBinary uint32 indices
         * @param string $colorBinary RGBA8 per vertex (drawVert colors)
         * @param list<array{mid: list<float>, radius: float}> $autosprites AutospriteDeform quads
         * @param list<array{verts: list<list<float>>, sts: list<list<float>>, shortEdges: list<array{a:int,b:int,len2:float,flip:bool}>}> $autosprite2s
         */
        public function __construct(
            public readonly string $shaderName,
            public readonly int $lightmapNum,
            public readonly string $vertexBinary,
            public readonly string $indexBinary,
            public readonly int $indexCount,
            public readonly int $triangleCount,
            public readonly bool $useLightmap,
            public readonly bool $blend = false,
            public readonly bool $sky = false,
            public readonly int $cluster = -1,
            /** @var list<float> */
            public readonly array $mins = [0.0, 0.0, 0.0],
            /** @var list<float> */
            public readonly array $maxs = [0.0, 0.0, 0.0],
            /** @var list<array{mid: list<float>, radius: float}> */
            public readonly array $autosprites = [],
            public readonly float $sort = 3.0,
            public readonly bool $polygonOffset = false,
            public readonly string $colorBinary = '',
            /** @var list<array{verts: list<list<float>>, sts: list<list<float>>, shortEdges: list<array{a:int,b:int,len2:float,flip:bool}>}> */
            public readonly array $autosprite2s = [],
            /** LIGHTMAP_BY_VERTEX — MST_TRIANGLE_SOUP (tr_bsp ParseTriSurf). */
            public readonly bool $vertexLight = false,
            /** Portal/mirror face plane (R_PlaneForSurface); null if not a portal planar. */
            public readonly ?array $planeNormal = null,
            public readonly float $planeDist = 0.0,
            /**
             * Unique PVS clusters of contributing faces (R_AddWorldSurface: visible
             * if ANY cluster is visible). Empty = always draw.
             * @var list<int>
             */
            public readonly array $clusters = [],
            /**
             * BSP faces packed into this batch (debug pick / --debug-face).
             * @var list<array{
             *   index: int,
             *   type: int,
             *   typeName: string,
             *   shaderNum: int,
             *   shader: string,
             *   fog: int,
             *   lm: int,
             *   firstVert: int,
             *   numVerts: int,
             *   firstIndex: int,
             *   numIndexes: int,
             *   normal: list<float>,
             *   localIndexStart: int,
             *   localIndexCount: int,
             *   triCount: int
             * }>
             */
            public readonly array $faces = [],
        ) {
        }
    }
