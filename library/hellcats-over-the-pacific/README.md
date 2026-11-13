# Hellcats Over the Pacific — evidence-backed Godot port

This repository ports *Hellcats Over the Pacific* (Graphic Simulations / Parsoft,
1991, Macintosh) to Godot 4.7 from the original program itself. It preserves
the Macintosh evidence, a readable semantic reconstruction of the decompiled
code, a faithful GDScript translation of every function, and a runtime that runs
that translation as the original game from Godot's main scene: the **Original
edition**. Two more editions are planned: **Native** (the same game rules on
Godot facilities, without the emulated Macintosh) and **Enhanced** (optional,
switchable improvements on Native).
[How the port was made](#how-hellcats-was-ported-to-godot) explains the
pipeline.

## Start here

Read only the material needed for the task:

| Need | Canonical entry point |
|---|---|
| Current state and next milestone | [`docs/architecture/PROJECT_STATUS.md`](docs/architecture/PROJECT_STATUS.md) |
| Reconstruction checkpoint | [`docs/port/DECOMPILATION_PROGRESS.md`](docs/port/DECOMPILATION_PROGRESS.md) |
| Semantic names, globals, fields | [`docs/reference/DECOMPILATION_SYMBOLS.md`](docs/reference/DECOMPILATION_SYMBOLS.md) via `tools/re_query.py symbol` |
| Original port: translation rules and coverage | [`docs/port/STRICT_PORT_FOUNDATION.md`](docs/port/STRICT_PORT_FOUNDATION.md) |
| Resource corpus | [`docs/reference/RESOURCE_ANALYSIS.md`](docs/reference/RESOURCE_ANALYSIS.md) via `tools/resedit.py` |
| System navigation | [`docs/architecture/SUBSYSTEM_MAP.md`](docs/architecture/SUBSYSTEM_MAP.md) |
| Documentation index | [`docs/README.md`](docs/README.md) |

Do not begin a focused task by loading all documentation or a whole CODE file.
Use deterministic narrow retrieval:

```sh
python3 tools/re_query.py function --segment "Pacific Conflict" FUN_0000e792
python3 tools/re_query.py excerpt --segment "Pacific Conflict" FUN_0000e792 --address 0xf47c
python3 tools/re_query.py callers --segment "Pacific Conflict" FUN_0000e792
python3 tools/decomp/xrefs.py "Pacific Conflict" 0x155d8   # callers in the bytes
python3 tools/re_query.py callees --segment "Pacific Conflict" FUN_0000e792
python3 tools/re_query.py globals --segment "Pacific Conflict" FUN_0000e792 --access reads
python3 tools/re_query.py tests test_effect_init --case run
python3 tools/re_query.py symbol DAT_00026004
python3 tools/re_query.py source-map FUN_0001358c

python3 tools/resedit.py inspect DITL 137
python3 tools/resedit.py item DITL 137 5
python3 tools/resedit.py references ALRT 259
python3 tools/resedit.py raw DITL 137 --offset 0 --length 32
python3 tools/resedit.py search "Mission Navigation"
python3 tools/progress_stats.py
```

## Evidence and code layout

```text
sources/mac-os-9/                         original disks, archives, manuals (its README)
sources/resedit-1-0-3/CODE/*.c            immutable Ghidra exports
sources/resedit-1-0-3/CODE/*.decomp.c     semantic reconstructions
sources/resedit-1-0-3/<TYPE>/<ID>.*       resource payload, JSON, local identity
sources/hellcats-1-0-4/                   the 1.0.4 delta (generated from the floppies)
sources/screenshots/, sources/videos/     reference captures of the original
godot/launcher/Main.tscn                  the main scene: runs the Original edition
godot/original/port/                      faithful C-to-GDScript translations
godot/original/compat/                    68k arithmetic and memory helpers
godot/original/mac/machine/, toolbox/     the emulated Macintosh around the port
godot/original/Game.tscn                  the screen and speaker the program runs in
godot/assets/<type>/                      the resource fork, mirrored, with its decoders
godot/tests/original/{port,mac}/          deterministic tests and end-to-end sessions
docs/                                     live references and contracts
tools/                                    retrieval, verification, extraction, QA
```

The critical correspondence is:

```text
immutable Ghidra .c -> semantic .decomp.c -> faithful Original port .gd
```

Original function identities and source hashes live in
`godot/original/mappings/functions.json`. Source-local provenance comments
remain valuable review evidence.

## Running the game

Open `godot/project.godot` in Godot 4.7 and run `res://launcher/Main.tscn`:
it runs the Original edition: the original program, ported, on an emulated Macintosh
(Toolbox, screen, sound, input), as [`docs/architecture/GAME_RUNTIME.md`](docs/architecture/GAME_RUNTIME.md)
describes. The mouse is the joystick; the keys are the original's (V cockpit,
C chase, T tower, F flaps, G gear, J eject, P pause, `=` / `-` throttle, ⌘Q quit).
Pilots and settings the game saves go to Godot's `user://resources` (the Native edition keeps its
own in `user://native/saves.cfg`, imported once from those).
The edition is chosen on the command line, Original by default:
`godot --path godot -- --edition=original`. `native` runs its first slices around Original
([godot/native/README.md](godot/native/README.md)); `enhanced` is not implemented yet and says so. `--version=1.0.4` runs Hellcats 1.0.4, rebuilt from its original
updater, instead of 1.0.3.

## How Hellcats was ported to Godot

The port moves the original program through four stages, each kept beside the
one before so any line of GDScript can be traced back to the bytes it came from:

```text
resource fork -> Ghidra .c -> semantic .decomp.c -> Original port .gd -> runtime + scenes
 (ResEdit)        (immutable)   (readable)           (exact mapping)   (mapped around)
```

### 1. The resources, extracted

Every resource of the application's resource fork was extracted with ResEdit
into `sources/resedit-1-0-3/<TYPE>/<ID>.*` (dialogs, alerts, menus, pictures,
icons, sounds, strings, the settings and pilot records), including the `CODE`
resources. Each of the eleven code segments (`ID00`..`ID09` and the *Pacific
Conflict* scenery overlay) was decompiled by Ghidra into
`sources/resedit-1-0-3/CODE/*.c`. Those exports are evidence: they are never
edited, and their checksums are protected.
Every payload is checked against the 1.0.3 application on the original floppy
(`sources/mac-os-9/`, whose README lists the original media), from which
`tools/resedit.py` takes it.

### 2. A readable decompilation

Ghidra's output is correct but unreadable (`FUN_0000e792`, `DAT_0001b738`,
`local_14`). With Claude Code, each `.c` file was rewritten into its
`.decomp.c` twin in two passes:

- **Functions:** every function's purpose was reconstructed from its body, its
  callers and the resources it touches, and it was given a semantic name
  (`FUN_0000e792` becomes `update_aircraft_entity`), with the original identity
  kept beside it.
- **Variables:** the same pass over every parameter, local, global and record
  field, so a routine reads as what it does.

Function boundaries, data widths, fixed-point arithmetic and addresses are
preserved, and anything not yet understood stays annotated as such. The
registry (`tools/decomp/inventory_functions.py`, `docs/reference/DECOMPILATION_SYMBOLS.md`)
holds all 740 functions with their status and evidence.

### 3. The Original port

Each `.decomp.c` function was then translated one to one into GDScript under
`godot/original/port/`: same function, same order of operations, same
widths, signedness, overflow and fixed-point behaviour. The 68000's arithmetic
is reproduced by `compat/c68k.gd` (16- and 32-bit wraps, `DIVS` / `MULS`,
shifts, rotates) and its flat memory by `compat/memory_68k.gd`, so the
translated code reads and writes the original data structures at their
original offsets. The resource fork is mirrored byte for byte into
`godot/assets/<type>/`, with Godot scenes to view it.

Every function's chain (`.c` -> `.decomp.c` -> `.gd`) is recorded in
`godot/original/mappings/functions.json`. Its behaviour is checked against
the original machine code: `tools/original/m68k_emu.py` runs the original bytes on a
68000 emulator, `tools/original/gen_*_cases.py` record what they do (memory,
results, the calls they make out of their segment), and
`godot/tests/original/port/` replays the same cases on the port.

### 4. The runtime, mapped around the port

The Original port is not touched to make it run. Instead it is *mapped around*:
`godot/original/mac/` builds the Macintosh the program expects, so the original
`main()` runs as it did in 1991.

- **`original/mac/`:** one 24-bit address space laid out as the original expects
  (low memory, the A5 world from `DATA` / `ZERO` / `DREL`, the stack, the
  heap, the sound buffer, an 8-bit screen); the segment loader that links the
  jump table to the ported routines; the overlay loader that reads and
  relocates *Pacific Conflict* as the original loader does; the program's
  thread.
- **`toolbox/`:** the parts of the Mac Toolbox the program calls through its
  traps, written for Godot: the Memory, Resource, File, Event, Window, Dialog,
  Control and Menu Managers, Color QuickDraw (ports, patterns, regions, text,
  CopyBits, pictures, colour icons), the Palette Manager, TextEdit, Standard
  File and the Sound Manager.
- **Scenes:** `launcher/Main.tscn` runs `original/Game.tscn`, whose
  screen shows the Mac's frame buffer through its colour table, whose speaker
  plays the sound buffer the program mixes every tick, and which hands Godot's
  mouse and keys to the program as Mac events.

Ghidra never defined 35 small routines (a few dialog procedures, the mission
summary's text and map, the carrier deck's collision, C library and Toolbox
glue). Those are written from their disassembly, mostly in
`original/mac/machine/unexported.gd`, each citing its address; the
[index in docs/architecture/GAME_RUNTIME.md](docs/architecture/GAME_RUNTIME.md#routines-outside-the-740)
lists them with where the port reaches them.

The result is the original game, flight model, renderer, missions, dialogs and
sound, running in Godot from a faithful GDScript version of all 740 decompiled
functions. When playing turns up a gap, the fix goes back up the chain to the
stage the evidence points at (the decompilation, the Original port and its tests,
or the runtime), so each stage stays the source for the next.

### Next steps

The project grows into three editions of one Godot project: **Original** (the
faithful Macintosh reconstruction above), **Native** (the same game rules on
Godot facilities, without the emulated Macintosh) and **Enhanced** (optional,
switchable improvements on Native). The roadmap, one validated phase at a time
(class names such as `StrictID02` and the `"strict_port"` data keys stay as they are):

0. ~~Correct the original evidence~~ (done: payloads from the 1.0.3 disk,
   redundant media removed).
1. ~~Standardize the edition terminology~~ (done in the documentation).
2. ~~Move `godot/` to `original/`, `launcher/`, `assets/`~~ (done, paths only).
3. ~~An edition launcher and the native and enhanced scaffolds~~ (done: `--edition`).
4. ~~Deterministic stepping and state traces to compare editions~~ (done: GAME_RUNTIME.md).
5. ~~Hellcats 1.0.4: rebuild it from 1.0.3 and the updater, port the changed CODE 2~~ (done:
   runs with `--version=1.0.4`; [RESOURCE_ANALYSIS.md](docs/reference/RESOURCE_ANALYSIS.md#hellcats-104)).
6. ~~Native adapters around the original port~~ (done, godot/native/README.md): input (InputMap controls), sound (the 1.0.3 driver on
   typed state, sample-exact), dialogs (Godot controls over the standard items), saves (a Godot
   ConfigFile, importing Original's saves without changing them), menus (Godot labels over the bar).
7. A typed native state model, replacing the emulated memory subsystem by subsystem (started: the
   overlay RNG).
8. Leyte Gulf: match its functions against 1.0.3, then port it (started: [LEYTE_GULF.md](docs/topics/LEYTE_GULF.md)).
9. The enhanced edition.

## Validation

From the repository root. QA runs every Python check (retrieval, inventory, progress, resource
metadata, sync, callees, generated cases, documentation links, comment and header rules) in
`shell.nix`'s Python (Pillow for the resources' pixel comparisons, Capstone for the m68k checks):

```sh
nix-shell --run 'python3 tools/qa/test_regression.py'
/Applications/Godot.app/Contents/MacOS/Godot --headless --path godot \
  --log-file /tmp/hellcats-godot.log --script res://tests/run_all.gd
git diff --check
```

The Godot run ends with "ObjectDB instances were leaked" and "resources still in use": each
Original port segment's `code_table` holds Callables bound to the segment itself, so its instances
and their memory live until exit. That is expected, not a failure; the result line decides.

After scripts move (a pull that renames folders under `godot/`), rebuild Godot's class cache
once, or the tests stop on "hides a global script class":
`/Applications/Godot.app/Contents/MacOS/Godot --headless --path godot --import`.

An end-to-end headless session of the game (about seven minutes; it never touches
the player's saved pilots) is run separately; its header lists its options and the
runs whose coverage is the evidence for `runtime_verified`, and
[`docs/generated/RUNTIME_COVERAGE.md`](docs/generated/RUNTIME_COVERAGE.md) says why each function it
has not run has not:

```sh
/Applications/Godot.app/Contents/MacOS/Godot --headless --path godot \
  --script res://tests/original/mac/smoke_session.gd
```

Verify protected Ghidra exports from their directory because the checksum
manifest uses local paths:

```sh
cd sources/resedit-1-0-3/CODE
shasum -a 256 -c ../../../docs/DECOMPILATION_PROTECTED_CHECKSUMS.sha256
```

Passing automated tests establishes only their documented scope. It does not by
itself prove visual parity, full flight parity, or agreement with an observed
original Macintosh runtime.
