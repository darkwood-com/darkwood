<?php

declare(strict_types=1);

namespace App\Bridge;

use FFI;
use FFI\Exception as FfiException;
use RuntimeException;

final class NativeDepthBridge
{
    private ?FFI $ffi = null;
    private mixed $ctx = null;
    private ?string $loadedModelPath = null;

    /** @var resource|null */
    private $lockHandle = null;

    public function __construct(
        private readonly string $libraryPath,
        private readonly int $threads,
    ) {
    }

    public function __destruct()
    {
        if ($this->ffi !== null && $this->ctx !== null) {
            $this->ffi->da_capi_free($this->ctx);
            $this->ctx = null;
        }
    }

    /**
     * @return array{
     *     width: int,
     *     height: int,
     *     depth: list<float>,
     *     confidence: list<float>|null,
     *     extrinsics: list<float>,
     *     intrinsics: list<float>,
     *     isMetric: bool,
     *     loadMs: float
     * }
     */
    public function inferDense(string $modelPath, string $imagePath): array
    {
        $this->assertFfiEnabled();
        $this->acquireLock();

        try {
            $loadStart = hrtime(true);
            $this->ensureContext($modelPath);
            $loadMs = (hrtime(true) - $loadStart) / 1_000_000;

            $ffi = $this->ffi;
            $outH = $ffi->new('int');
            $outW = $ffi->new('int');
            $outDepthPtr = $ffi->new('float*');
            $outConfPtr = $ffi->new('float*');
            $outSkyPtr = $ffi->new('float*');
            $ext = $ffi->new('float[12]');
            $intr = $ffi->new('float[9]');
            $isMetric = $ffi->new('int');

            $rc = $ffi->da_capi_depth_dense(
                $this->ctx,
                $imagePath,
                FFI::addr($outH),
                FFI::addr($outW),
                FFI::addr($outDepthPtr),
                FFI::addr($outConfPtr),
                FFI::addr($outSkyPtr),
                $ext,
                $intr,
                FFI::addr($isMetric),
            );

            if ($rc !== 0) {
                throw new RuntimeException($this->lastError());
            }

            $h = (int) $outH->cdata;
            $w = (int) $outW->cdata;
            $count = $h * $w;
            if ($count <= 0) {
                throw new RuntimeException('Native inference returned empty depth map.');
            }

            $depthPtr = $this->ffi->cast('float*', $outDepthPtr);
            if ($depthPtr === null) {
                throw new RuntimeException('Native inference returned null depth buffer.');
            }
            $depth = $this->copyFloatsFromPtr($depthPtr, $count);

            $confidence = null;
            $confPtr = $this->ffi->cast('float*', $outConfPtr);
            if ($confPtr !== null) {
                $confidence = $this->copyFloatsFromPtr($confPtr, $count);
            }

            $skyPtr = $this->ffi->cast('float*', $outSkyPtr);
            if ($skyPtr !== null) {
                $ffi->da_capi_free_floats($skyPtr);
            }

            return [
                'width' => $w,
                'height' => $h,
                'depth' => $depth,
                'confidence' => $confidence,
                'extrinsics' => $this->copyFixedArray($ext, 12),
                'intrinsics' => $this->copyFixedArray($intr, 9),
                'isMetric' => (bool) $isMetric->cdata,
                'loadMs' => $loadMs,
            ];
        } finally {
            $this->releaseLock();
        }
    }

    public function exportGlbCached(string $outputPath): void
    {
        $this->assertFfiEnabled();
        $this->acquireLock();

        try {
            if ($this->ctx === null) {
                throw new RuntimeException('No inference context; run inferDense before exportGlbCached.');
            }

            $rc = $this->ffi->da_capi_export_glb_cached($this->ctx, $outputPath);
            if ($rc !== 0) {
                throw new RuntimeException($this->lastError());
            }
        } finally {
            $this->releaseLock();
        }
    }

    private function ensureContext(string $modelPath): void
    {
        if (!is_file($modelPath)) {
            throw new RuntimeException(sprintf('Model file not found: %s', $modelPath));
        }

        if (!is_file($this->libraryPath)) {
            throw new RuntimeException(sprintf('Native library not found: %s', $this->libraryPath));
        }

        if ($this->ctx !== null && $this->loadedModelPath === $modelPath) {
            return;
        }

        if ($this->ctx !== null) {
            $this->ffi->da_capi_free($this->ctx);
            $this->ctx = null;
        }

        $this->ffi ??= $this->createFfi();
        $ctx = $this->ffi->da_capi_load($modelPath, $this->threads);
        if ($ctx === null) {
            throw new RuntimeException($this->lastError() ?: 'da_capi_load failed');
        }

        $this->ctx = $ctx;
        $this->loadedModelPath = $modelPath;
    }

    private function createFfi(): FFI
    {
        $header = file_get_contents(dirname(__DIR__, 2).'/config/da_capi.h');
        if ($header === false) {
            throw new RuntimeException('Unable to read da_capi.h for FFI binding.');
        }

        try {
            return FFI::cdef($header, $this->libraryPath);
        } catch (FfiException $e) {
            throw new RuntimeException('Failed to load native library via FFI: '.$e->getMessage(), 0, $e);
        }
    }

    /** @return list<float> */
    private function copyFloatsFromPtr(mixed $ptr, int $count): array
    {
        $out = [];
        for ($i = 0; $i < $count; ++$i) {
            $out[] = (float) $ptr[$i];
        }
        $this->ffi->da_capi_free_floats($ptr);

        return $out;
    }

    /** @return list<float> */
    private function copyFixedArray(mixed $array, int $count): array
    {
        $out = [];
        for ($i = 0; $i < $count; ++$i) {
            $out[] = (float) $array[$i];
        }

        return $out;
    }

    private function lastError(): string
    {
        if ($this->ffi === null || $this->ctx === null) {
            return 'Native context not initialized.';
        }

        return (string) $this->ffi->da_capi_last_error($this->ctx);
    }

    private function assertFfiEnabled(): void
    {
        if (!ini_get('ffi.enable')) {
            throw new RuntimeException('PHP FFI is disabled. Run with ffi.enable=true (see README).');
        }
    }

    private function acquireLock(): void
    {
        $path = sys_get_temp_dir().'/nolife-local-da3.lock';
        $handle = fopen($path, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Unable to open inference lock file.');
        }
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new RuntimeException('Unable to acquire inference lock.');
        }
        $this->lockHandle = $handle;
    }

    private function releaseLock(): void
    {
        if (!is_resource($this->lockHandle)) {
            return;
        }
        flock($this->lockHandle, LOCK_UN);
        fclose($this->lockHandle);
        $this->lockHandle = null;
    }
}
