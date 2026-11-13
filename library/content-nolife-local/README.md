# Nolife Local — Depth Anything 3 demo

Symfony 8.1 application running **local** monocular depth inference via PHP FFI and `depth-anything.cpp`.

No cloud API. No LLM. Native ggml + Metal on Apple Silicon.

## Prerequisites

- macOS arm64 (verified on Apple M4)
- PHP ≥ 8.4 with `ext-ffi`, `ext-gd`
- Built `libdepthanything.dylib` from [depth-anything.cpp](https://github.com/mudler/depth-anything.cpp)
- GGUF models in `var/models/` (not committed)

## Setup

### 1. Build native library

```bash
cd /path/to/depth-anything.cpp
git submodule update --init --recursive
cmake -B build -DDA_BUILD_CLI=ON -DDA_SHARED=ON -DDA_GGML_METAL=ON -DCMAKE_BUILD_TYPE=Release
cmake --build build -j
```

### 2. Download models

```bash
mkdir -p var/models
curl -fL -o var/models/depth-anything-base-q4_k.gguf \
  https://huggingface.co/mudler/depth-anything.cpp-gguf/resolve/main/depth-anything-base-q4_k.gguf
curl -fL -o var/models/depth-anything-base-f32.gguf \
  https://huggingface.co/mudler/depth-anything.cpp-gguf/resolve/main/depth-anything-base-f32.gguf
```

Verify checksums against `depth-anything.cpp/models/SHA256SUMS`.

### 3. Install PHP dependencies

```bash
composer install
composer check-platform-reqs
php bin/console app:depth-check
```

Expect `READY` before running inference.

### 4. Configure environment

Copy `.env` and adjust paths for your machine:

```dotenv
DA3_LIBRARY_PATH=/path/to/depth-anything.cpp/build/libdepthanything.dylib
DA3_MODEL_PATH=/path/to/var/models/depth-anything-base-q4_k.gguf
DA3_MODEL_F32_PATH=/path/to/var/models/depth-anything-base-f32.gguf
```

### 5. Run the demo

```bash
php -c php.ini -S 127.0.0.1:8765 -t public
# Open http://127.0.0.1:8765
```

`php.ini` in this project sets `ffi.enable=1` (required).

### CLI inference (no web UI)

```bash
php -d ffi.enable=1 bin/console app:depth-infer -i photo.jpg -m q4_k
```

Input files are **copied** into `var/inference/` — the source path is not moved or deleted.

Optional warmup before a demo (loads model + one inference):

```bash
php -d ffi.enable=1 bin/console app:depth-warmup -m q4_k
```

Repeated in-process stress test (native cache lifetime within one PHP process):

```bash
php -d ffi.enable=1 bin/console app:depth-stress -i public/samples/mountains.jpg -m q4_k -r 10
```

Each inference run creates a directory under `var/inference/`. Remove old runs explicitly:

```bash
php bin/console app:depth-cleanup --keep=20
```

Upload limits (web and CLI): JPEG/PNG/WebP, max 20 MB, max 4096×4096 pixels. These are application safety bounds; native code resizes to ~504 px longest side.

### Offline demo

After install, inference and the result page do not need the network:

- Native library + GGUF models on disk
- GLB viewer: `public/vendor/model-viewer.min.js` (vendored)

Optional: `php -d ffi.enable=1 bin/console app:depth-bench -i public/samples/mountains.jpg -w 1 -r 5 --json`

### Validation commands

```bash
php bin/console app:depth-check
php -d ffi.enable=1 bin/console app:depth-infer -i public/samples/mountains.jpg -m q4_k
php -d ffi.enable=1 bin/console app:depth-bench -i public/samples/mountains.jpg -m q4_k -w 1 -r 5
php -d ffi.enable=1 bin/console app:depth-stress -i public/samples/mountains.jpg -r 5
php bin/console app:depth-cleanup --keep=20
```

CLI `infer=` is the Flow **infer** step only, not the full pipeline total. Processed resolution depends on source aspect ratio (demo samples → 504×336).

Sample images (`public/samples/`) are from the MIT-licensed [depth-anything.cpp](https://github.com/mudler/depth-anything.cpp) repository.

## Architecture

```
Browser → DepthController → DepthAnythingService → DepthInferenceFlow (darkwood/flow)
              ├── Validate → InferDepth → Colormap → ExportScene → BuildResult
              ├── NativeDepthBridge (PHP FFI)
              ├── DepthColormapRenderer (PHP GD)
              └── libdepthanything.dylib → ggml → Metal
```

GLB export uses `da_capi_export_glb_cached()` — reuses the dense inference result (one native pass per request).

## What runs where

| Component | Runtime |
|-----------|---------|
| HTTP, upload, Twig UX | PHP |
| Depth colormap PNG | PHP (GD) |
| Model load, ViT, DPT, pose, GLB | Native (FFI) |
| 3D viewer | Browser (`<model-viewer>`) |

## License

Application code: proprietary (Darkwood). Native library: MIT. Model weights: Apache-2.0 (see model card).
