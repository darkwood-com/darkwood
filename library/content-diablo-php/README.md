# Diablo PHP

PHP runtime that loads a **user-supplied** original Diablo 1 data file (`DIABDAT.MPQ`) and runs gameplay in PHP with SDL2 via FFI.

## Videos

[![Gameplay demo 1](https://img.youtube.com/vi/uzhWDD5zbi8/hqdefault.jpg)](https://www.youtube.com/watch?v=uzhWDD5zbi8)

[![Gameplay demo 2](https://img.youtube.com/vi/D-32H0R7tP4/hqdefault.jpg)](https://www.youtube.com/watch?v=D-32H0R7tP4)

## Requirements

- PHP 8.5+ with `ext-ffi`
- SDL2 library
- Legally obtained `DIABDAT.MPQ` (not distributed here)

## Install

```bash
composer install
```

## Run

```bash
php -d ffi.enable=true bin/diablo.php --data="/path/to/DIABDAT.MPQ"
DIABLO_DATA=/path/to/DIABDAT.MPQ php -d ffi.enable=true bin/diablo.php --smoke
php -d ffi.enable=true bin/diablo.php --data="/path/to/DIABDAT.MPQ" --town
php -d ffi.enable=true bin/diablo.php --data="/path/to/DIABDAT.MPQ" --level=1 --seed=12345
```

Use `--help` for the full CLI option list (debug dumps, freeze-frame, render layers, etc.).

## Controls

| Input | Action |
|-------|--------|
| ↑/↓ Enter | Menu |
| Click | Walk / attack / operate / talk |
| Right-click | Firebolt |
| 1–4 | Belt potion |
| I / C | Inventory / character |
| E | Equip |
| H / F | Heal / Firebolt |
| S | Save |
| Esc | Save + menu |

## Legal

Blizzard game assets are **not** included. MPQ reader portions are adapted from mpqfs (MIT).
