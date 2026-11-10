# SQLite Compiler (PHP)

Standalone experimental C compiler written in PHP, targeting **macOS Apple Silicon (ARM64)**.

Success criterion: compile the SQLite 3.46.0 amalgamation with our frontend, assemble/link with Apple toolchain, run a smoke harness, and print `darkwood`.

## Prerequisites

- macOS arm64
- PHP ≥ 8.5
- Composer (`darkwood/flow` ^8.1 for pipeline orchestration)
- Xcode command-line tools (`clang`, `as`, `ld`)
- SQLite 3.46.0 amalgamation in `var/compiler/sqlite/` (see below)

## Setup

```bash
composer install
./scripts/download-sqlite.sh
php bin/console app:compiler-check
```

## Verify

```bash
# Baseline fixture (clang oracle for probe command)
php bin/console app:compiler-probe fixtures/compiler/001-return-42.c

# Our compiler end-to-end
php bin/console app:compiler-build fixtures/compiler/001-return-42.c
php bin/console app:compiler-run fixtures/compiler/001-return-42.c   # exit 42

# Regression fixture suite
php bin/console app:compiler-fixtures

# SQLite acceptance test
php -d memory_limit=4G bin/console app:compiler-sqlite
```

Expected smoke-test stdout:

```text
darkwood
```

## Debug / introspection

```bash
php bin/console app:compiler-tokens fixtures/compiler/001-return-42.c
php bin/console app:compiler-ast fixtures/compiler/001-return-42.c --sema
php bin/console app:compiler-compare-clang fixtures/compiler/001-return-42.c
```

## Architecture

```text
src/Compiler/     plain PHP C frontend + ARM64 codegen (no Flow dependency)
src/Flow/         coarse pipeline orchestration via darkwood/flow Composer package
src/Command/      Symfony CLI wrappers
include/          minimal Darwin libc declarations
fixtures/compiler/ regression C sources + sibling .json expectations
```

External tools on the acceptance path:

```text
PHP compiler → .s → Apple as → .o → clang/ld → executable
```

Clang is **not** used to compile C on the final path (linking/oracles only).

The compiler core (`src/Compiler/`) is plain PHP. Flow jobs in `src/Flow/Step/` orchestrate compile → assemble → link → run at the Symfony command layer.

