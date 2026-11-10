# Quake III Arena — PHP port

Progressive PHP port of [id Software Quake III Arena](https://github.com/id-Software/Quake-III-Arena) (engine logic in PHP; SDL2 + OpenGL via FFI for the window).

```text
STATUS:   Q3DM0_PLAYABLE_COHERENT (solo deathmatch slice)
PLATFORM: PHP 8.5+ · ext-ffi · ext-zip · SDL2 · OpenGL
PRIMARY:  native window via `play` (not the browser viewer)
```

## Videos

[![Gameplay demo](https://img.youtube.com/vi/9BKSPLpgNcU/hqdefault.jpg)](https://www.youtube.com/watch?v=9BKSPLpgNcU)

## Requirements

- PHP 8.5+ with `ext-zip` and `ext-ffi`
- SDL2 (e.g. `brew install sdl2`); optional `SDL2_LIB` / `OPENGL_LIB` if libraries are non-standard
- A local Quake III `baseq3` (or install root containing it) with PK3 data you are allowed to use

Game data is **not** shipped in this repository. Never commit PK3 files (`runtime-data/` is gitignored).

## Setup

```bash
composer install
```

## Play (canonical)

```bash
php -d ffi.enable=true bin/quake3.php play \
  --basepath=/path/to/quake3-or-baseq3 \
  --map=maps/q3dm0.bsp
```

`--basepath` may be the game install root (with a `baseq3/` child), the `baseq3` directory itself, or a directory of `*.pk3` files.

### Controls

| Input | Action |
|-------|--------|
| WASD | Move |
| Arrow keys | Turn (or strafe while +strafe); up/down walk |
| Mouse | Look |
| RMB or Alt | +strafe |
| Middle mouse | +zoom |
| Space | Jump |
| C | Crouch |
| Shift | Walk |
| Click / Ctrl / F | Fire |
| 2 / 3 / 8 | Machinegun / shotgun / plasma |
| Mouse wheel, `[` `]` | Previous / next weapon |
| PgDn / Delete | Look up / down |
| End | Center pitch |
| R | Respawn (after death delay) |
| Esc | Quit |

Useful flags: `--freecam`, `--debug-lighting`, `--debug-surface-pick`, `--spawn=N`.

## Other commands

```bash
# SDL clear-color smoke test
php -d ffi.enable=true bin/quake3.php window

# Pmove regression gate
php bin/quake3.php regress:pmove --basepath=/path/to/quake3-or-baseq3 maps/q3dm0.bsp

# PK3 / BSP diagnostics
php bin/quake3.php data:inspect --basepath=/path/to/quake3-or-baseq3
php bin/quake3.php bsp:info --basepath=/path/to/quake3-or-baseq3 maps/q3dm0.bsp
php bin/quake3.php fs:which --basepath=/path/to/quake3-or-baseq3 maps/q3dm0.bsp

# Browser orbit mesh viewer (diagnostic only — not the game)
php bin/quake3.php serve --basepath=/path/to/quake3-or-baseq3 --map=maps/q3dm0.bsp
```

## Current limitations

- Focused on a coherent solo `q3dm0` experience, not full Quake III
- Some world materials still wrong or black (shader fidelity incomplete)
- No bots, networking, QVM, or menus
- Weapon set on this map: machinegun, shotgun, plasma (plus gauntlet slot unused)
- Audio is a thin platform bridge (`afplay` / `paplay`), not a full sound system

## License

Engine port: GPL-2.0-or-later (see `composer.json`), consistent with id Software’s Quake III Arena source release. Assets remain subject to their own EULA / ownership.
