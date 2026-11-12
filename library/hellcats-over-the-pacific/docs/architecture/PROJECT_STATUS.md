# Hellcats Over the Pacific — current status

## Current position

The original game runs in Godot. `godot/scenes/main/Main.tscn` starts the
original program, which runs as the strict port of all 740 decompiled functions
on an emulated Macintosh:

- `godot/strict_port/` is the source-faithful track: each function translated
  one to one from its semantic `.decomp.c`, checked against the original machine
  code on a 68000 emulator, with the resource fork mirrored beside it.
- `godot/runtime/` maps around it without changing it: the 24-bit memory, the
  segment and overlay loaders, and the Toolbox the program calls (QuickDraw,
  the Window, Dialog, Menu, Event, Resource, File and Sound Managers, TextEdit,
  Standard File). [`GAME_RUNTIME.md`](GAME_RUNTIME.md) describes it, with the
  index of the 35 routines Ghidra never defined.

Run `python3 tools/progress_stats.py` for current reconstruction, translation,
and resource counts, and `python3 tools/strict_port/check_sync.py --json` for
strict source-hash synchronization.

## How fixes travel

A gap found by playing is fixed at the stage its evidence points at, and goes
up the chain from there: the semantic decompilation, then the strict port and
its emulator-backed tests, then the runtime. The runtime does not work around
the port: where the port strayed from what the original pushes (rects and
colours by value, FSRead's count), the port was corrected and the workaround
removed.

## Next milestones

The project's next steps are in the README's
[roadmap](../../README.md#next-steps).

## Checkpoint

- Baselines: Python QA passes in `shell.nix` (Pillow, Capstone); Godot suite 81/81 (its exit leak
  warnings are the segments' self-bound `code_table` Callables, README Validation); the full
  `smoke_session.gd` passes.
- Runtime coverage 661 / 740: `smoke_session.gd --approach` (saved setting +0x0a = host +0xa4,
  STRUCTS.md; 'g' twice in phase 5's flight under the target lock) ran
  try_begin_landing_approach and its three callees.
- Step 1 cleanup done: nothing unreferenced in godot/runtime, resources, scenes or tools/;
  quoted code paths are checked (`check_doc_paths.py`); the semantic C's header comments name
  recovered functions as `name (FUN_*)` except false entries, runtime aliases and Ghidra labels.
- Blocked: the refuel path (outcome 9, 5 functions) needs a touchdown and stop on a friendly
  runway; the --approach glide, flown with the target lock still held, ends "augered in" before
  phase 5's release (234.8 s), so no scripted flight lands yet.
- GAME_RUNTIME.md's Architecture table already inventories the runtime for step 2; its milestone 5
  now links the routine index instead of repeating it.
- Next: README step 2 (removing the virtual machine) changes the architecture the strict port
  runs on; it waits on the maintainer's choice of a first subsystem.

## Evidence and retrieval

Start at `README.md`. Use `tools/re_query.py` for a single function, bounded
excerpt, caller/callee set, globals, status, symbol record, strict mapping, or
test case. Use `tools/resedit.py inspect`, `item`, `references`, and bounded
`raw` for one resource. Do not load project-wide histories or entire source
corpora for a focused task.

Detailed current references:

- `DECOMPILATION_PROGRESS.md` — semantic reconstruction target and workflow
- `DECOMPILATION_SYMBOLS.md` — canonical semantic symbol and field registry
- `STRICT_PORT_FOUNDATION.md` — strict translation contract: layout, rules, conventions, verification
- `GAME_RUNTIME.md` — the emulated Macintosh around the strict port
- `RESOURCE_ANALYSIS.md` — resource corpus and decoding status
- `SUBSYSTEM_MAP.md` — original-system to Godot navigation

## Validation boundary

Automated suites cover the strict port's functions against the original
bytes, synchronization, fixtures, the runtime data and the scene layout. The
game itself is checked by playing it against the reference screenshots and
video in `sources/`. Passing the suites does not by itself establish visual
parity, original runtime parity, or end-to-end mission fidelity; those are
reported separately.
