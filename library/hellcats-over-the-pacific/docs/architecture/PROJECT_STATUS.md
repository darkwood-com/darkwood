# Hellcats Over the Pacific — current status

## Current position

The original game runs in Godot. `godot/launcher/Main.tscn` starts the
original program: the Original edition, all 740 decompiled functions ported
on an emulated Macintosh:

- `godot/original/port/` is the Original port: each function translated
  one to one from its semantic `.decomp.c`, checked against the original machine
  code on a 68000 emulator; `godot/assets/` mirrors the resource fork.
- `godot/original/mac/` maps around it without changing it: the 24-bit memory, the
  segment and overlay loaders, and the Toolbox the program calls (QuickDraw,
  the Window, Dialog, Menu, Event, Resource, File and Sound Managers, TextEdit,
  Standard File). [`GAME_RUNTIME.md`](GAME_RUNTIME.md) describes it, with the
  index of the 35 routines Ghidra never defined.

Run `python3 tools/progress_stats.py` for current reconstruction, translation,
and resource counts, and `python3 tools/original/check_sync.py --json` for
source-hash synchronization.

## How fixes travel

A gap found by playing is fixed at the stage its evidence points at, and goes
up the chain from there: the semantic decompilation, then the Original port and
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
- Step 1 cleanup done: nothing unreferenced in godot/original/mac, resources, scenes or tools/;
  quoted code paths are checked (`check_doc_paths.py`); the semantic C's header comments name
  recovered functions as `name (FUN_*)` except false entries, runtime aliases and Ghidra labels.
- Blocked: the refuel path (outcome 9, 5 functions) needs a touchdown and stop on a friendly
  runway; the --approach glide, flown with the target lock still held, ends "augered in" before
  phase 5's release (234.8 s), so no scripted flight lands yet.
- GAME_RUNTIME.md's Architecture table already inventories the runtime for the native phases; its milestone 5
  now links the routine index instead of repeating it.
- Phase 0 (README roadmap) done: `tools/resedit.py` takes every payload from the 1.0.3 floppy
  (`tools/mac_resources/hfs.py`); the eight sounds the mirror had from 1.0.4 are now 1.0.3's and
  DITL 135 is complete (544 bytes); the 1.0.4 differences are recorded per resource
  (`version_1_0_4`). Redundant media removed (`sources/mac-os-9/README.md`); the StuffIt
  archive's undecompressed third application copy is kept.
- Phase 1 done (iteration 1): user-facing docs and AGENTS.md say Original / Native / Enhanced
  edition and "Original port". Python QA passes.
- Phase 2 done (iteration 2): `git mv` of strict_port/{code,compat,mappings} to
  `original/{port,compat,mappings}`, runtime to `original/mac`, resources to `assets`,
  scenes to `launcher/Main.tscn` and `original/Game.tscn`, tests to `tests/original/{port,mac}`,
  tools/strict_port to `tools/original`; 4,146 path strings rewritten (docs/history untouched,
  Ghidra .c untouched, .decomp.c header comments only; function hashes unchanged). Kept: class
  names (`StrictID*`), the `"strict_port"` data keys, the STRICT_PORT_FOUNDATION.md file name.
  QA, check_sync (740 / 661), Godot 81/81 pass.
- Phase 3 done (iteration 3): `godot/launcher/launcher.gd` picks the edition from
  `-- --edition=original|native|enhanced` (Original by default); native / enhanced / unknown
  print a warning and show it instead of starting; `godot/native/`, `godot/enhanced/` hold a
  README each. Godot 82/82 (new `tests/launcher/test_launcher.gd`).
- Phase 2 smoke after the move: 191 steps, 0 missing, 0 script errors.
- Phase 4 started (iteration 4): stepped clock in `mac_machine.gd` (`use_stepped_clock`,
  `step_tick`, `read_ticks` for TickCount, `on_tick`, `stop_at`; GAME_RUNTIME.md). Godot 83/83
  with `tests/original/mac/test_stepped_run.gd` (two runs, identical traces, about 8 s).
- Phase 4, iteration 5: `tests/helpers/state_trace.gd` (capture + `first_divergence`) and the
  recorded Original trace `tests/original/mac/fixtures/stepped_trace.json`, checked by
  test_stepped_run.gd. Limitation: its 600 ticks only cover the opening dialogs (overlay and RNG
  constant).
- Phase 4, iteration 6: smoke_session.gd `--stepped [--trace f] [--expect f]`; mission 2 for 60 s
  stepped is reproducible through flight (110 distinct A5 states, 27 RNG states, two runs equal)
  and recorded as `fixtures/stepped_mission2_trace.json`.
- Phase 4 done (iteration 7): the full smoke session `--stepped` passes (191 steps, 0 missing,
  0 script errors, tick 25560); phase-boundary checks pass (protected checksums, resedit verify in
  nix-shell, check_sync, mirror, links, Python QA, Godot 83/83).
- Phase 5, iteration 8: `tools/mac_resources/update_1_0_4.py --check` (in QA) rebuilds 1.0.4 from
  the 1.0.3 disk + the updater's script and payloads: 13 resources change (CODE 2, vers 1, 8 snd,
  HEL1 127, SIZE 0/1), 119 identical to the AppleDouble copy, the rest written by play
  (RESOURCE_ANALYSIS.md "Hellcats 1.0.4"). CHK~ and the updater's date not decoded.
- Phase 5, iteration 9: 1.0.4 CODE 2 = 1.0.3's + 4,838 bytes appended at 0x4e1a; 119 of 127 ID02
  functions unchanged at their offsets; the 8 sound-driver entries patched to `JMP` into the
  appended code (update_1_0_4.py `code2`, checked in QA).
- Phase 5, iteration 10: the appended 1.0.4 code is a Sound Manager double-buffer driver
  (10 routines at 0x4e1a..0x5634, its variables at 0x5636..0x6100, 68020 instructions;
  RESOURCE_ANALYSIS.md table).
- Phase 5, iteration 11: `sources/hellcats-1-0-4/` (the 1.0.4 delta: 13 payloads, manifest with
  hashes, CODE 2 patch, new routines), written by update_1_0_4.py --write, checked in QA.
- Phase 5, iteration 12: `godot/assets/hellcats-1.0.4/` (the delta's 12 non-CODE payloads,
  in the mirror manifest under "versions") and `resource_files.gd` `version` ("1.0.3" default;
  "1.0.4" reads the changed resources first). Godot 84/84.
- Phase 5, iteration 13: `sources/hellcats-1-0-4/CODE/ID02.delta.decomp.c` reconstructs 4 of the
  11 new routines (set_sound_playback_rate / release_channel_loop / silence_sound_channel /
  is_channel_playing 1.0.4) and the driver's variable layout; UNVERIFIED.
- Phase 5, iteration 14: those 4 VERIFIED on the interpreter (`tools/original/check_1_0_4_sound.py`,
  in QA); m68k_emu.py gained the 68020 full-format base displacement and memory-indirect modes.
- Phase 5, iteration 15: alloc_sound_buffer_node 1.0.4 (0x4fa6) reconstructed and VERIFIED; record
  +0x04 is the free-list head (as 1.0.3). 5 / 11 routines verified.
- Closed: every 1.0.3 emulator fixture regenerated after the m68k_emu.py addressing change (all
  `tools/original/gen_*_cases.py` and `tools/runtime/gen_runtime_cases.py`, 26 min): byte-identical;
  the only fixture differences from the index are phase 0's (the 8 sounds, DITL 135).
- Phase 5, iteration 16: play / enqueue 1.0.4 VERIFIED (real node helper, every pool field
  compared); 7 / 11. Channels other than 0 drop the last 0x73a bytes: the 1,850 the eight replaced
  sounds gained, so 1.0.4 plays them at 1.0.3's length.
- Phase 5, iteration 17: shutdown_sound 1.0.4 VERIFIED (traps stubbed and logged); the driver's
  Sound Manager variables mapped (channel +0x12ca, SndDoubleBufferHeader +0x12ce); 0x5634 is
  unreferenced. 8 / 11 verified (+ the empty 0x5634).
- Phase 5, iteration 18: init_sound_system 1.0.4 VERIFIED (every exit; traps and the double-back
  entry stubbed): 4 channels of 24 nodes in a ring, NewPtrClear (trap $A31E, not a system-heap
  allocation), header 8-bit mono 22254.545 Hz. 9 / 11 verified.
- Phase 5, iteration 19: the double-back mixer VERIFIED (`RTD` added to m68k_emu.py): all 10
  routines of the 1.0.4 driver reconstructed and verified (0x5634 is an unreferenced RTS).
- Phase 5, iteration 20: `godot/original/port/ID02_104.gd` (StrictID02v104): the 10 driver routines
  in GDScript (`*_104`, a separate class: ID02.gd's signatures differ, e.g. void vs D0); compiles and
  runs init / play / mix / shutdown with stub traps. Not verified against the bytes yet, not wired.
- Phase 5, iteration 21: `tools/original/gen_id02_104_sound_cases.py` (98 cases from the 1.0.4 bytes,
  `--check` in QA) and `tests/original/port/test_id02_104_sound.gd`: ID02_104.gd matches on all 98.
  The 1.0.4 driver chain is complete: bytes -> delta.decomp.c (VERIFIED) -> ID02_104.gd (replayed).
- Phase 5, iteration 22: 1.0.4 runs. `port/ID02_v1_0_4.gd` (CODE 2 with the 8 sound entries on the
  1.0.4 driver), chosen by segment_loader.gd when resource_files.gd's version is 1.0.4; Sound Manager
  `_SoundDispatch` / SndPlayDoubleBuffer (buffers played per tick, double-back called by address).
  `smoke_session.gd --mission 2 --seconds 60 --stepped --version 1.0.4` passes (0 missing, the double
  buffer playing); the 1.0.3 stepped trace still matches its fixture; Godot 85/85; QA passes.
- Phase 5 done (iteration 23): launcher `--version=1.0.4`; the full smoke session `--stepped
  --version 1.0.4` passes (191 steps, 0 missing, 0 script errors, tick 25560); phase-boundary
  checks pass (protected checksums, resedit verify, check_sync / callees, mirror, Python QA,
  Godot 85/85, links). Open, non-blocking: CHK~ and the updater's 0x7c / 0x80 fields; the
  StuffIt archive's application.
- Phase 6, iteration 24: `--edition=native` runs `godot/native/Game.tscn` (inherits Original's,
  script `native/native_game.gd`): keyboard through InputMap actions (`native/input/controls.gd`,
  defaults = the original keys, posting the same Mac keys). Godot 86/86 (test_native_controls.gd).
- Phase 8 (independent of Native), iteration 25: Leyte Gulf first estimate
  (`tools/mac_resources/leyte_match.py`, `docs/topics/LEYTE_GULF.md`): host 175 / 297 of 1.0.3's
  functions found (77 identical), overlay 67 / 443; masks are opcode lists, so a lower bound.
- Phase 6 needs a decision: the remaining adapters (sound, dialogs, saves) replace emulated
  subsystems whose behaviour is defined by the Original machine's state, i.e. phase 7's typed
  state; which subsystem goes first is the maintainer's choice.
- Phase 8, iteration 26: overlay matched with its relocation tables and by instruction sequence:
  36 identical, 63 moved only, 82 same instructions with other operands, 259 not found (of 443);
  Leyte is its own title (LEYTE_GULF.md).
- Phase 8, iteration 27: host by instruction sequence: 77 identical, 98 moved, 89 same
  instructions, 33 not found (of 297): the host is 1.0.3's, the overlay mostly new.
- Phase 8, iteration 28: `sources/leyte-gulf-1-0/correspondence.json` (per function: match kind and
  Leyte offset), from tools/mac_resources/leyte_correspondence.py. Investigation complete; a port
  needs a decompilation of Leyte's new overlay code (262 functions, no Ghidra export).
- Loop stopped after iteration 28; the maintainer chose sound as Native's first subsystem.
- Phase 6/7, sound: `godot/native/sound/sound_driver.gd` (1.0.3's mixer on typed state) and
  `native_id02.gd` (CODE 2's sound entries on it), picked by `segment_loader.gd`'s `overrides`
  (empty for Original). `test_native_sound.gd`: Native = Original sample for sample over 240
  ticks of plays, loops, rates, channel 1, enqueue, release, shutdown. Native stepped mission 2
  matches Original's trace on NATIVE_FIELDS (trace fields game_globals / world added, the old
  fields of both fixtures unchanged). Godot 87/87, QA passes.
- Phase 6, dialogs: `godot/native/dialogs/` (Original Dialog Manager + a thread-safe snapshot of
  the front dialog's standard items; overlay of Godot controls drawn over them; clicks reach the
  game as Original's mouse events). `toolbox.gd`'s `overrides` (empty for Original) selects it.
  `test_native_dialogs.gd`: all 12 DLOGs leave identical Mac memory on both editions; native
  stepped mission 2 still matches Original's trace. Godot 88/88, QA passes. Open: edit-field caret.
- Next (phase 6): saves (settings and pilots) as the last adapter; sound still reads sample bytes /
  writes SoundBase in Mac memory, dialogs still keep their records there (phase 7).

## Evidence and retrieval

Start at `README.md`. Use `tools/re_query.py` for a single function, bounded
excerpt, caller/callee set, globals, status, symbol record, port mapping, or
test case. Use `tools/resedit.py inspect`, `item`, `references`, and bounded
`raw` for one resource. Do not load project-wide histories or entire source
corpora for a focused task.

Detailed current references:

- `DECOMPILATION_PROGRESS.md` — semantic reconstruction target and workflow
- `DECOMPILATION_SYMBOLS.md` — canonical semantic symbol and field registry
- `STRICT_PORT_FOUNDATION.md` — the Original port contract: layout, rules, conventions, verification
- `GAME_RUNTIME.md` — the emulated Macintosh around the Original port
- `RESOURCE_ANALYSIS.md` — resource corpus and decoding status
- `SUBSYSTEM_MAP.md` — original-system to Godot navigation

## Validation boundary

Automated suites cover the Original port's functions against the original
bytes, synchronization, fixtures, the runtime data and the scene layout. The
game itself is checked by playing it against the reference screenshots and
video in `sources/`. Passing the suites does not by itself establish visual
parity, original runtime parity, or end-to-end mission fidelity; those are
reported separately.
