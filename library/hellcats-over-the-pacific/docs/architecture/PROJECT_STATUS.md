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
- Loop restarted (job 4a6284ed). Iteration 1, sound output: the native driver's samples go to
  `native/sound/native_speaker.gd` (inherits mac_speaker.gd; set in native/Game.tscn), no longer via
  SoundBase; same order as Original's buffer (k + 2). test_native_sound.gd: equal sample for sample;
  native mission 2 trace matches; Godot 88/88; QA passes.
- Native sound dependencies left: sample bytes read from the Mac memory (the game's 'snd ' handles);
  ticks from the Sound Manager's VBL service.
- Iteration 2, saves: `native/saves/resource_manager.gd` (toolbox override "resources"): the game's
  written resources in `user://native/saves.cfg`, pilots as fields, the rest as exact bytes; first
  run imports `user://resources` read only. test_native_saves.gd passes; native mission 2 trace
  matches; the full native stepped session passes (191 steps); Godot 89/89; QA passes.
- Iteration 3, dialogs: the snapshot carries the focused edit field and whether its text is
  selected; the overlay draws a blinking caret (32 ticks) or the selection inverted. Tested in
  test_native_dialogs.gd; Godot 89/89; QA passes.
- Iteration 4, menus: `native/menus/` (Original Menu Manager + snapshot published from
  draw_menu_bar / _draw_menu / _restore; overlay of Godot labels; choices stay the Original's).
  test_native_menus.gd passes (same memory both editions); native mission 2 trace matches;
  Godot 90/90; QA passes.
- Phase 6 host adapters now cover input, sound, dialogs, saves, menus. Left emulated for normal play:
  the program itself (memory_68k / c68k), window frames and the cursor (Window Manager), QuickDraw's
  drawing of the game's own graphics (cockpit, 3D view, user items), the Sound Manager's tick.
- Phase 6 done (iteration 5): full native stepped session 191 steps / 0 missing / 0 script errors;
  protected checksums, check_sync, mirror, resedit verify, Original 1.0.3 trace, 1.0.4 mission,
  Godot 90/90, QA, links all pass. Native is not independent: the program still runs on the
  emulated Macintosh (see the dependencies above).
- Phase 7, iteration 6: the overlay RNG as typed Native state (`native/state/native_overlay.gd`,
  chosen by overlay_loader.gd's `port_override`, null for Original). test_native_rng.gd passes;
  state_trace `rng` reads the port's state when it keeps one, `world` now leaves the RNG out (the
  baselines re-recorded, their other fields unchanged); native mission 2 matches on NATIVE_FIELDS;
  Godot 91/91; QA passes.
- Phase 7, iteration 7: the trigonometry tables as typed arrays in native_overlay.gd
  (test_native_trig.gd: every result equal over ~300k inputs; native mission 2 trace matches;
  Godot 92/92; QA passes). The effect log and contact records were rejected as slices: written
  inline by spawn_effect (long, recursive through debris) and linked into the entity records.
- Phase 7 measurement: of 307 named overlay globals, 90 are used by one function and 83 by two;
  the most shared (0x1b5a0) by 113. Order: read-only image tables first, then mutable state closed
  over few functions; the entity / controller records (most of the 740 functions) need a design
  decision on how Native holds them while the Original port still reads them from memory.
- Phase 7, iteration 8: the altitude curve (table_lerp_1b2c0) typed, tested over ~300k altitudes in
  test_native_trig.gd. The route tables 0x1b2e0 / 0x1b310 are not slices: their addresses go into
  the controller records. Remaining read-only candidates: alt_value_table 0x1b214 /
  alt_tolerance_table 0x1b264 (data); the per-kind dispatch tables 0x1b3f4 (AI fly), 0x1b40c /
  0x1b41c / 0x1b42c (effects), 0x1b50c / 0x1b514 (trails) (code addresses: typed Callable tables).
- Iteration 9: alt_value_table / alt_tolerance_table and the dispatch tables are read inside large
  functions tied to the entity records (update_altitude_detail_bands, spawn_effect, the AI tick):
  no further self-contained slice. Loop stopped (job 4a6284ed) with these open decisions:
  1. Phase 7: how Native holds the entity / controller records while the Original port reads them
     from memory (Native copies of the large functions over typed records, or typed record views
     over the shared memory first).
  2. Phase 8: Ghidra is not installed (QA: analyzeHeadless not found), needed for Leyte's 262 new
     overlay functions.
  3. Phase 9: which enhancements are approved.
- Phase 7, Typed Views First (approved; loop job d03f49f1), iteration 1, milestone reached:
  typed views over the shared memory, no copy, original offsets and widths, signedness from the
  Original port's reads: `native/state/world_object_view.gd` (the [PROVEN] world object fields of
  docs/reference/STRUCTS.md, raw access for the rest, overlaps documented) and
  `native/state/controller_view.gd` (common header and aircraft fields; other kinds raw). First
  Native consumers on them, in `native_overlay.gd`: update_object_camera_metric 0x3b98,
  copy_object_vertex_buffer 0x1c04, spin_object_heading 0x114b4, face_camera_heading 0x11ae6.
  test_native_views.gd: every field against the port's read, wrap / sign / big-endian stores, the
  overlapping fields, array indexing, and the four consumers against the Original over 40 memory
  set-ups (extremes, sign boundary), byte for byte. Original and Native stepped mission-2 traces
  match the fixture; Godot 93/93; Python QA, sync, links, protected checksums, diff check pass.
  Still emulated: the records themselves (Stage E not started), the camera globals 0x1d830..0x1d838
  / 0x1d80e those consumers read, and every other function that reads the records. Next slice: the
  per-frame object pass (collect_near_objects, update_object_distance_band, the far-object sort)
  on the views, then the aircraft tick's leaf helpers on ControllerView.
- Phase 7 execution loop (job 98774027), iteration 1: `native/state/scene_view.gd` types the camera
  (0x1d830..0x1d838 longs, 0x1d80c / 0x1d80e / 0x1d810 words) and the object-pass globals
  (frame_counter, band_limits, view points, visible list, far list and their counts), zero-copy.
  Native collect_near_objects 0x53f8 and update_object_distance_band 0x3da8 on the views (long +4
  stays raw); the earlier consumers now read the camera through SceneView. test_native_object_pass.gd:
  60 near lists (same bytes, same retire / deactivate calls and a7) and every band against every
  limit; stepped mission-2 traces match in both editions. A negative detail size never leaves the
  original's shift loop (game metrics are non-negative): inputs kept in that domain.
- Iteration 2: the object pass of overlay_frame_step on the views: _sort_far_objects (carried
  pairs; carrier controller word +0x54 raw), collect_far_objects 0x555a (overlap list, broad-phase
  calls left to the Original's tests, active list in the caller's frame), build_visible_object_list
  0x58ec (the Original's transform_int3_vecs_asr_by_1d744). SceneView gains the object pool, carried
  pairs, overlap list and visible_first_pass; WorldObjectView documents long +4 as bytes +4..+7
  (still unresolved as a whole) and +0x28 (far-pass running minimum, unresolved name). Parity
  tests: 80 far pools (1,027 broad-phase calls, same order and a7), 60 far sorts, 80 visible lists;
  stepped traces match in both editions. Still Original inside the pass: the camera-metric loop's
  caller, update_ground_scatter_objects, dispatch_overlap_collisions, age_contact_records,
  retire_entity / deactivate_object, the collision tests and the matrix transform.
- Iteration 3, milestone: the aircraft AI control helpers on ControllerView
  (adjust_ai_throttle_vs_speed 0x10c2c, max_ai_throttle_maybe_invert_679 0x10cb2,
  invert_ai_bias_67b_if_positive 0x10cd8, enqueue_control_inputs_avg4 0x10558; fields in
  docs/reference/STRUCTS.md). test_native_aircraft.gd: 200 controllers, every call byte for byte,
  both throttle branches, every bias byte. The Original port's comment on 0x10558 says damage word
  bit 0x100; its code and the semantic C test byte +0x685 bit 0 (word bit 0): Native follows the
  code (comment left for an Original evidence pass). Validation: Godot 95/95; stepped mission-2
  traces match the fixture in both editions; the full 191-step stepped session runs in both
  editions with identical game_globals / world / rng over all 852 trace entries (stepped_trace.json
  is test_stepped_run.gd's 600-tick set-up, not the smoke session's: not an --expect target there);
  Python QA, sync, links, protected checksums, diff check pass. Loop job 98774027 stopped.
  Still emulated: every record's storage (Stage E not started), the frame step around the pass,
  update_ground_scatter_objects, dispatch_overlap_collisions, age_contact_records, retire_entity /
  deactivate_object, the collision tests, the matrix transform, and the aircraft tick itself
  (update_aircraft_entity 0xe792, flight model, AI states). Next slice: the AI pursuit helpers
  that call these (ai_bootstrap_climb_phase, ai_script_try_fire_burst, build_relative_steer_sample),
  then update_aircraft_entity's flight-model leaves.
- Phase 7 AI / flight-model loop (job 2413bdf5), iteration 1: the AI pursuit helpers on the views:
  ai_bootstrap_climb_phase 0x10b84, ai_script_try_fire_burst 0x10e28, build_relative_steer_sample
  0x10360 (its matrix / transform / isqrt / attitude callees stay the Original's). ControllerView:
  +0x61a previous pitch error, +0x674 bomb phase, bombs / throttle setters; long +0xc and word
  +0x634 raw (unresolved). test_native_ai_pursuit.gd: 150 + 150 + 200 cases, RNG scripted alike,
  spawn_weapon_entity logged; mission-2 traces match. The Original's 0x10558 comment corrected
  (damage word bit 0, comment only); trace fixtures and their comparisons in GAME_RUNTIME.md.
- Iteration 2: the flight-model leaves of update_aircraft_entity: damp_short_if_below_0x200
  0xe468, compute_template_force_from_626 0xe4a4 (lift, stall marker), scale_template_field_38
  0xe570 (engine power), on `native/state/aircraft_type_view.gd` (the record DAT_00026004 points at;
  lift pair, flaps pair, stall angle, rated power as STRUCTS.md gives them). The tick's blocks are
  inline in update_aircraft_entity (one 440-line function): only its called leaves can move without
  rewriting it. test_native_flight_model.gd: the four data-file records and 40 generated, ~76k
  past-stall cases, every density, heights around 0x200; mission-2 traces match.
- Iteration 3, milestone: retire_entity 0x51ec, deactivate_object 0x52dc, age_contact_records
  0x1377a, dispatch_overlap_collisions 0x5832 and the camera writer copy_player_pose_to_camera
  0x739a on the views (SceneView: camera setters, camera_angle4, player controller, player shot,
  child window, scatter slots, contact table, scene root, retire count, replay state,
  designated_entity_2). test_native_object_release.gd: heap / pose / cleanup / fractal / jsr callees
  stubbed alike, same memory, calls and a7. Validation: Godot 98/98; mission-2 traces match in
  both editions; full 191-step session identical over 852 entries; Python QA, sync, mirror, links,
  protected checksums, diff check pass. Loop job 2413bdf5 stopped. Open: ControllerView's +0x626
  is named throttle after STRUCTS' aircraft row, but the type-record section, block 3a and 0xe4a4
  use it as the angle of attack (name to settle). Not migrated: update_ground_scatter_objects
  (177 lines, next), the collision tests, the matrix transforms, the heap, and the tick body
  update_aircraft_entity (inline blocks). Storage: the camera has Native writers
  (copy_player_pose_to_camera) and readers, but its other writers (camera set-ups at 0x10355..,
  0x14015.., 0x16565.., 0x17050..) and the Original's matrix builders still use the addresses: no
  storage switch yet. Next slice: update_ground_scatter_objects, then the camera set-up writers,
  after which the camera block can be the first Native-storage proof of concept.

Loop 4 (Complete Object Pass & First Native Camera Storage, job dbfe0376):

- Iteration 1: update_ground_scatter_objects 0x5e28 on the scene view (scatter points 0x1b924,
  state 0x1b8e4, surface cache 0x1b8a4, slot points 0x1b894, templates 0x1bc24, scatter_started
  0x24cec, view reset 0x1d856, view mode 0x1d858, the projection window and scales, host world
  0x1b5a0); every callee the Original's at the same a7 (point transforms on the camera matrix
  0x1d744, surface lookup, spawn, cleanup, fractal release, template chain, shading).
  test_native_ground_scatter.gd: 300 scenes over the overlay image, RNG and surfaces scripted
  alike, all eight callee branches reached; same memory, calls and a7; a one-constant mutation
  fails it. The object pass now has no Original function of its own left (its callees remain:
  collision tests, matrix transforms, heap, spawn). Next: the camera set-up sites.
- Iteration 2: the "four camera set-up sites" of loop 3 (0x10355, 0x14015, 0x16565, 0x17050) were
  line numbers of pacific_conflict.gd, not code addresses: copy_player_pose_to_camera 0x739a
  (Native), sort_group_parts_by_distance 0x1443a, process_flight_input_and_outcome 0x740a and its
  port helper _update_view_camera. Camera block audit (Ghidra C per function, the port's reads /
  writes, and the overlay image's absolute operands: position 41 / 48 / 41, angles 10 / 17 / 16,
  angle4 10):

  | Fields | Writers | Readers | Native |
  |---|---|---|---|
  | position 0x1d830..0x1d838 | start_mission_scene 0x3ac4, process_flight_input_and_outcome 0x740a (+ its helpers), sort_group_parts_by_distance 0x1443a; copy_player_pose_to_camera 0x739a, place_camera_behind_object 0x88a0, update_bailout_view 0x8972, update_tracking_camera 0x8c30, begin_replay_view 0x8ff4 | overlay_frame_step 0x6598 (y), update_altitude_detail_bands 0x5a14, transform_and_project_object 0xa75e, draw_distant_object_dots 0x162ba, spawn_camera_relative_object 0x19aa2, follow_camera_offset 0x19b30, tick_view_attached_object 0x19bbc, compute_camera_abs_metric, build_land_outline_at_detail 0x4958, update_altimeter_needles; update_object_camera_metric 0x3b98, collect_near_objects 0x53f8, collect_far_objects 0x555a, _sort_far_objects, update_ground_scatter_objects 0x5e28, turn_camera_toward_point 0x8abe | writers 5 of 8, readers 6 of 16 |
  | angles 0x1d80c / 0x1d80e / 0x1d810 | 0x740a, 0x823e (tail of 0x740a); 0x739a, 0x8abe | 0x6598 (matrices 0x1d744..), update_heading_tape 0xc1aa, update_direction_needle 0xc8d6, shape_free_plume 0x11796, face_object_to_camera 0x12738, update_radar_scope 0x17b68, 0x19bbc; face_camera_heading 0x11ae6 | writers 2 of 4, readers 1 of 8 |
  | angle4 0x1d808 | 0x740a; 0x739a, 0x8abe | 0x6598 | writers 2 of 3, reader 0 of 1 |
  | replay camera 0x1d814..0x1d81f, 0x24d18..0x24d2b | 0x8abe, 0x8c30, 0x8ff4; the host's bss memset (ID02 reset_overlay_state 0x212a) | the same three | all: Godot-backed |

  Indirect access: no pointer is taken into 0x1d808..0x1d83b in the Ghidra C (`&DAT_0001d824` /
  `&DAT_0001d83c` lie outside the fields). Watching the Mac memory found the one writer no search
  of the overlay shows: the host's reset_overlay_state memsets the overlay's bss (0x1b524..0x267c8,
  from the image header), which holds every field above, at each mission start, then calls the
  overlay entry (its only caller). Camera position and angles: no storage switch, since Original
  functions still use the addresses on Native's path (position: 3 writers, 10 readers; angles: 2
  writers, 7 readers; overlay_frame_step and process_flight_input_and_outcome among them, 185 and
  118 lines plus helpers).
  First Godot-backed block: the replay camera (camera_step, camera_heading / camera_pitch,
  target_hold_frames, camera_goal), closed over turn_camera_toward_point, update_tracking_camera
  and begin_replay_view, now Native with update_bailout_view and place_camera_behind_object on the
  views. `native/state/replay_camera_state.gd`: memory-backed (reference) or Godot-backed
  (`replay_camera_storage_godot`, the rollback switch, default Godot): typed integers, never read
  from or written to the Mac memory, zero at start and zeroed in Native's overlay_main_entry, where
  the host has just zeroed the bss (a first version loaded the block lazily from the memory and
  diverged at the second mission: the watch above found why). Traces hash the world with the
  port's native_state_ranges() in place of the memory's bytes. test_native_replay_camera.gd: 80
  replay sequences (16800 frames, every outcome), 100 bail-out, 150 turn, 150 place cases, both
  backends; for the Godot one the block's Mac bytes are overwritten with 0xa5 first and must stay
  so while the results, calls, a7, the rest of the memory and the Godot values equal the
  Original's; a hidden memory read injected into the Godot backend fails it. Full session:
  identical over 852 entries in 1.0.3 and in 1.0.4, mission-2 traces match. The smoke session put
  the 1.0.3 Native sound driver into 1.0.4 runs (797 divergences); it now keeps 1.0.4's driver,
  as native_game.gd does.
- +0x626 settled as the angle of attack (ControllerView.angle_of_attack, STRUCTS' aircraft row
  corrected; names only): written as type +0x42 at spawn / restart, stepped by block 3a toward the
  pitch stick + type +0x44, capped at +0x42 (interpolated by airspeed), cleared in one key path;
  read as the lift curve's input (rising to the stall break +0x34, falling past it), as the
  x-rotation of the body attitude matrix and the gun line ((+0x626 - +0x44) & 0x3fff), as
  camera_angle4, and by the landing check fold(+0x626) < -0x80. No reader uses it as a throttle
  (the AI throttle is byte +0x668).
- Milestone (loop job dbfe0376 stopped): Godot 100/100; mission-2 traces match in both editions;
  full 191-step session identical over 852 entries in 1.0.3 and in 1.0.4 (world hashed with the
  Godot-held block); Python QA, strict sync (740 verified), resource mirror 29/29 / 109/109, links,
  protected checksums (32 OK), diff check pass; the Original port unchanged this loop. Storage:
  PARTIAL, the replay camera block independent; camera position / angles shared. Next: the camera
  angles' readers (HUD tapes, needle, radar, plume, face_object_to_camera: small) and the 0x740a
  view helpers (_update_view_camera, _update_external_camera, _view_key_reset), then
  overlay_frame_step's camera part, after which the angles (then the position) can move.

Loop 5 (Native Camera Independence, job 27dd3817):

- The camera angle block is 0x1d800..0x1d811, not only the three words: longs camera_angle6
  0x1d800, camera_angle5 0x1d804, camera_angle4 0x1d808, words camera_angle3 0x1d80c,
  neg_camera_heading 0x1d80e, neg_camera_pitch 0x1d810 (overlay_frame_step's six-angle matrix
  reads all six). Its readers and writers, by port function (every line naming the constants;
  Ghidra C per function; one address list, in _view_key_reset):
  writers copy_player_pose_to_camera, turn_camera_toward_point, process_flight_input_and_outcome
  (0x740a with 0x823e: the view pitch mirror), its port helpers _view_key_reset, _process_flight_key,
  _update_view_camera, _update_external_camera; readers face_camera_heading,
  tick_view_attached_object 0x19bbc, face_object_to_camera 0x12738, shape_free_plume 0x11796,
  update_direction_needle 0xc8d6, update_heading_tape 0xc1aa, update_radar_scope 0x17b68,
  overlay_frame_step 0x6598 (both camera matrices, 0x1d744).
- All of them are Native. Hand-written on the views: tick_view_attached_object,
  face_object_to_camera, shape_free_plume, update_direction_needle (with the earlier three). The
  rest is generated: the generator (now `tools/native/gen_routed_port.py`) copies the eight functions verbatim from
  pacific_conflict.gd into the generated adapters (now `native/state/routed_port.gd`) with `memory.` -> `_routed.` and
  nothing else (`--check` in the Python QA: camera-adapters); `routed_memory.gd` sends an access to
  the block to the angle state, and fails on any access that is not exactly one field (no byte or
  straddling access, no fallback), everything else to the Mac memory. native_overlay.gd extends
  the generated adapters; SceneView takes the angle storage (`_scene()`).
- Godot-backed angles: `native/state/camera_angle_state.gd`, six typed fields, memory-backed
  reference or Godot-backed (`camera_angle_storage_godot`, rollback switch, default Godot), zeroed
  in overlay_main_entry where the host zeroes the overlay's bss (the block lies in it). Traces hash
  the world with native_state_ranges() (replay camera and angles) in place of the memory's bytes.
- Closure, by runtime observation: every Mac read and write overlapping 0x1d800..0x1d811 logged
  with its call stack (source files) over the full 191-step Native session and Native mission 2:
  only the host's own, ansi_memset in reset_overlay_state (ID02 0x212a) and the Memory Manager's
  new_ptr clearing the image's allocation. No overlay code, Original or Native, touches the Mac
  bytes. A first watch run logged Original functions instead (corrected in loop 6: the shell passed
  "--stepped --edition native" as one argument, so that run was the Original edition; the smoke
  session now fails on unknown arguments, and on a Native overlay that does not load).
- test_native_camera_angles.gd: the six readers (hand-written and generated) over 120 scenes each,
  both storage modes (Godot: Mac bytes of the blocks 0xa5 and left so); a changed needle offset
  fails it. tests/helpers/native_state.gd seeds / poisons / compares Native-held blocks
  (test_native_views, test_native_object_release, test_native_replay_camera use it).
- Camera angles: INDEPENDENT on Native's path (no Original reader or writer left, no Mac access but
  the host's bss clear and allocation, which the reset reproduces). Camera position
  (0x1d830..0x1d83b): SHARED, not switched: Original readers remain on Native's path
  (transform_and_project_object 0xa75e, draw_distant_object_dots 0x162ba,
  spawn_camera_relative_object 0x19aa2, follow_camera_offset 0x19b30, compute_camera_abs_metric,
  build_land_outline_at_detail 0x4958, update_altitude_detail_bands 0x5a14, update_altimeter_needles)
  and writers (start_mission_scene 0x3ac4, sort_group_parts_by_distance 0x1443a, which shifts the
  camera in place around its sort). Next: those, generated the same way, then the position block.
- Milestone (loop job 27dd3817 stopped): Godot 101/101; mission-2 traces match in both editions;
  full 191-step session identical over 852 entries in 1.0.3 and 1.0.4 with the angles and the
  replay camera hashed from Godot; Python QA (with camera-adapters), strict sync, resource mirror,
  links, protected checksums (32 OK), diff check pass; the Original port and runtime unchanged this
  loop (the watches' temporary edits of memory_68k.gd / overlay_loader.gd were restored).

Loop 6 (Autonomous Native migration, job 3371ed0f):

- Correction: the mission-2 "Native" trace checks of loops 4 and 5 ran through a zsh loop that
  passed `--edition native` as one argument, so they ran the Original edition (the full-session
  Native comparisons passed the flags literally and stand). smoke_session.gd now rejects unknown
  arguments; the mission-2 Native check is redone below.
- Camera position 0x1d830..0x1d83b and view state 0x1d856..0x1d859 (view_flag_d856, view_mode)
  are held in Godot like the angles: `native/state/native_block.gd` (one typed-block class for the
  three blocks; camera_angle_state.gd folded into it), routed_memory.gd routes to whichever block
  an access overlaps. Their readers and writers are all Native: 20 more Original functions
  generated (start_mission_scene, sort_group_parts_by_distance, transform_and_project_object,
  draw_distant_object_dots, spawn_camera_relative_object, follow_camera_offset,
  compute_camera_abs_metric, build_land_outline_at_detail, update_altitude_detail_bands,
  update_altimeter_needles; aircraft_notice_object, dispatch_collision, render_visible_objects,
  respawn_player_at_anchor, spawn_effect, test_object_collision, update_aircraft_entity,
  update_game_sounds, update_view_clip_bounds, update_view_readouts_and_overlays), 28 in all; the
  generator now ends a function at its first top-level line (it had copied constants between
  functions). SceneView routes position and view state; overlay_main_entry zeroes all blocks.
- Closure by runtime watch (every Mac read / write overlapping 0x1d800..0x1d811, 0x1d830..0x1d83b,
  0x1d856..0x1d859 with its stack), Native runs with literal arguments: full session 1.0.3 and
  1.0.4, mission 2: only the host's bss memset (reset_overlay_state) and new_ptr's clear.
- Validation: Godot 101/101; full session identical over 852 entries in 1.0.3 and 1.0.4 (blocks
  hashed from Godot); mission 2 Native matches its fixture (redone, genuinely Native); Python QA
  (camera-adapters), strict sync, mirror, links, protected checksums (32 OK), diff check pass;
  Original port and runtime unchanged (watch edits restored). Tests now seed / poison / compare
  every Godot-held block (test_native_object_pass and test_native_ground_scatter moved to the
  helper). Loop job 3371ed0f stopped.
- Stop point (approval needed): the next state groups (entity / controller records, the matrices
  and sin / cos caches 0x1d72c..0x1d7fd) are reached through pointers (heap records, matrix
  pointers passed to the matrix code), not fixed addresses: moving them out of the Mac memory needs
  a decision on how Native addresses records (handles over Godot-owned records vs routed pointer
  ranges over the heap).

Loop 7 (Autonomous Native development, job 569b9755), in progress:

- Decision (records): Godot-owned heap records, the handle being the payload address the Mac heap
  assigns (`native/state/native_records.gd`). The heap keeps assigning addresses, its headers and
  its free blocks (the heap is its own later subsystem), so pointer fields and traces stay exactly
  comparable. A record's bytes (original layout and widths) live in Godot from allocation (zeroed,
  as heap_zero_payload) to release (heap_free_coalesce: the bytes go back into the Mac block, which
  the heap owns once freed); the host's overlay reset (before every overlay entry) releases them
  all. Controllers first: the 25 constructors allocate through _alloc_record. Rollback switch
  `record_storage_godot`.
- Decision (routing): the whole overlay runs routed on Native. `tools/native/gen_routed_port.py`
  (was gen_camera_adapters.py) generates `native/state/routed_port.gd` (was camera_adapters.gd):
  every Original function not hand-written in native_overlay.gd, 492, verbatim except `memory.` ->
  `_routed.` (and _alloc_record in the constructors); QA check routed-port. The hand-written
  Native functions use the router too. One router (routed_memory.gd): Native blocks, then records,
  then the Mac memory. Cost: the Native tests run about 1.5x slower.
- Bugs found and fixed on the way: a PackedByteArray taken out of a Dictionary is a copy (record
  writes were lost: records are now objects); stale records across the host's overlay reset.
- Verification gap found: no trace field covered the overlay's heap (controllers, objects, vertex
  blocks live there, 0x110000.. here); traces now carry "heap" (the arena, host +0x348 up to
  heap_break, records patched in), compared in full-session runs (not in the older fixtures).
- Record watch (every Mac access inside a live record, with its stack) found what no search of the
  port shows: the host's reset_overlay_state memsets the block holding the heap arena (records are
  dropped at overlay entry, not written back), and two routines the runtime implements outside the
  740 on the Mac memory read controllers: 0x15134 carrier_deck_contact (reached through _jsr: the
  Native _jsr runs a routed copy) and hook 0x4b0 player_left_ground_zone (called by the host:
  temporary adapter, the Native overlay re-registers it through the loader's own
  register_address at every overlay entry; Original's runtime files untouched, an edit of
  overlay_loader.gd to let ports supply written routines was refused and not made).
- Full 191-step session 1.0.3 with controller records: identical over 852 entries in
  game_globals / world / rng and heap.
- Iteration 2, every heap block a record: the Native heap_alloc makes each block the overlay's
  heap returns a Godot record (its payload's current Mac bytes move in; heap_alloc_cleared then
  clears the record through the router), so the constructors need no special allocation. Record
  watch: QuickDraw's copy_bits read one record, present_spans_to_window's 0x2000-byte CopyBits
  buffer: blocks allocated while that function runs stay Mac blocks (shared with the emulated
  Toolbox until QuickDraw is Native; the traces do not hash the screen, the watch caught it). The
  generator emits a function Native wraps (native_overlay.gd calls routed_<name>) under that name,
  so a wrapper keeps the verbatim routed body. 1.0.3 / 1.0.4 identical incl. heap; mission 2;
  Godot 101/101.
- Iteration 3, the overlay globals (data 0x1a75c..0x1b524 and bss ..0x267c8) as one Godot record,
  created at the first overlay entry from the loaded image, its bss part zeroed at every later
  entry (the host's memset), the typed blocks overriding its fields. The globals watch found the
  hand-written overrides calling super() (the unrouted Original: heap_alloc, heap_free_coalesce,
  overlay_main_entry, _jsr), which split the heap globals: they now call their routed copies; and
  the runtime's hooks 0xd23a (_d23a reads 0x20ae4) and 0x16208 (writes 0x1da8c), taken over like
  0x4b0 (on the machine's free stack, as the loader's hook). Traces read the overlay's view where
  Native holds it (the RNG tail long, the heap arena bounds).
  The trace patches the records first, then the replay camera and the typed blocks (the globals
  record's own bytes at those addresses are not Native's state).
- Milestone, overlay state in Godot: the overlay's globals (data and bss), every heap block, the
  camera / view blocks and the replay camera are Godot-owned; the overlay's code runs routed
  (generated, drift-checked). Watches over the globals and every live record (full session 1.0.3):
  only the host's own accesses (its bss and arena memsets, an image load writing a new image's data
  for a new port). Full sessions 1.0.3 and 1.0.4 identical over 852 entries in game_globals /
  world / rng / heap; mission 2 Native matches its fixture; Godot 101/101; Python QA, strict sync,
  mirror, links, protected checksums (32 OK), diff check pass; Original port and runtime files
  unchanged this loop.
- Still in the Mac memory on Native's path: the heap allocator's headers and free blocks in the
  arena (the addresses it assigns are the record handles), the CopyBits buffer shared with the
  emulated QuickDraw, the overlay's code image (never executed: the port is GDScript), the host
  program's A5 world and every Toolbox structure. Next: the heap allocator (headers and free lists
  as Godot state, same address assignment), then the host / Toolbox side (QuickDraw first).
- Iteration 4, the heap arena as one Godot record: the per-block records are gone; the arena
  (host world +0x348, up to heap_sbrk's break) is one record grown by the Native heap_sbrk, so the
  allocator's headers and free blocks are Godot's as well as the payloads (the allocator runs
  routed). The CopyBits buffer is a hole while it lives (its bytes moved out to the Mac memory when
  allocated in present_spans_to_window, back into the record when freed). Watch over every record
  (full session 1.0.3): only the host's bss / arena memsets and image loads. 1.0.3 / 1.0.4
  identical incl. heap; mission 2; Godot 101/101; routed-port check pass.
- Native's overlay now keeps no state in the Mac memory but the Toolbox-shared hole. What remains
  emulated is the host program (the CODE segments' ports, their A5 world), the Mac runtime (memory
  manager, segment / overlay loaders, Toolbox, QuickDraw, windows, events, sound scheduling) and the
  overlay's code image (loaded, never executed).
- Iteration 5, the other screen depths: mission 2 recorded at depths 1 and 4 in both editions and
  compared (game_globals / world / rng / heap), with the record watch. Depth 4: identical. Depth
  1: the host's 1-bit renderer (CODE 6, add_polygon_edges) reads the overlay's screen polygon
  (0x24df2..) in place, and present_bw_frame hands its heap buffer to the host's 1-bit frame
  routine: Native now loads its own CODE 6 (`native/state/native_id06.gd`, the Original port with
  its memory the overlay's routed view, `host_routed_memory.gd`; temporary adapter until the host
  is Native) and present_bw_frame's buffer is a Toolbox hole like the CopyBits one. Depth 1:
  identical incl. heap.
- Validation after iteration 5: full sessions 1.0.3 / 1.0.4 identical incl. heap, Godot 101/101,
  Python QA, sync, mirror, links, protected checksums (32 OK), diff check pass.

Loop 8 (Gameplay fidelity and Native development, job b9bce5a2):

- Tester report B, "one +/- press runs the throttle to the minimum or maximum": confirmed, Mac runtime
  defect (Original layer), both editions. The game's ramp (process_flight_input_and_outcome:
  key-down '+' / '=' / '-' / '_' sets throttle_step 0x24cf6 to +-1; each frame 0x7dbe moves the
  throttle byte +0x668 one notch while |step| is 1 and doubles it, otherwise steps it toward 0: a
  notch every second frame, frames at least 4 ticks apart; the key-up clears it) is incremental.
  The runtime posted key-up events without a character (Godot's key releases carry no unicode),
  so the game's switch on the character missed the release and the ramp ran to 0 or 9. Native maps
  its own throttle keys ('=' / '-') with characters, but '+' (shifted or keypad) and other layouts
  fall back to the runtime path. Fix: the Event Manager (event_manager.gd post_key) gives a key-up
  without a character its key-down's, as a Mac key-up carries the key's character. No game or port
  code changed. tests/original/mac/test_throttle_keys.gd: the event characters (down, auto-key,
  up), a one-frame press (one notch up / down), a held key (a notch every second frame), both
  bounds, and the defect path (a character-less key-up leaves the ramp running). The smoke
  sessions post explicit characters on key-up, which is why they never showed it.
- Tester report A, "cockpit graphics sometimes stay visible over the scenery when the view
  changes": not reproduced so far; investigation recorded.
  - Smoke session `--views` (with `--mission`): the aircraft flown and the view keys cycled
    (cockpit 'v', chase 'c', tracking 't', 'e', the look arrows), a shot after each. Flight School
    (mission 2), Original 1.0.3: every view change renders as expected, the panel drawn in the
    cockpit views only. A pixel metric (fraction of the panel area matching a clean cockpit shot)
    separates the cases (cockpit 0.93..0.99, outside views 0.00..0.02). The scripted flight
    crashed after its first round (stick input too early): the second round is not evidence.
  - Original and Native screenshots of the full session are byte-identical, so a defect would be
    in the shared game / runtime, not in Native's records.
  - Frame by frame (framebuffer before and after the readouts, around cockpit / chase switches):
    no stale panel pixels. The emulated screen is drawn in place (the span filler writes rows into
    screen memory, then the readouts), while the window copies the framebuffer at 60 Hz from
    another thread: a mid-frame copy shows a partial frame. Measured on Native headless, real
    time: frame step ~261 ms, span fill ~16 ms, so a torn copy during the fill is rare (~6%); not
    shown to be the reported effect.
  - Needed from the tester: edition and version, screen depth and options (view size, haze, one
    line), the keys used, and a screenshot, to reproduce the exact case.
  - Also measured: Native runs ~4 frames per second headless (the routed port: every memory
    access goes through the router); a performance item for the Native roadmap.
- Validation (end of loop 8, job b9bce5a2 stopped): Original 1.0.3 full session identical to its
  trace before the loop; Godot 102/102 (test_throttle_keys added); Python QA, strict sync, links,
  protected checksums (32 OK), diff check pass. Next: report A with the tester's details (the
  --views scenario and the panel metric ready); Native performance (the router's per-access cost);
  then the host program / Toolbox side, QuickDraw presentation first.

Loop 9 (Native performance, Enhanced mobile, job eb51985b):

- Native performance. Benchmark: the stepped Flight School run (`--mission 2 --seconds 60
  --stepped`, the same game work in both editions), wall time on the development machine: Original
  56 s, Native 209 s before. The router's page table (native_records.gd `fast`, one entry per 32
  bytes: 0 Mac memory, record + 1 when one record covers the page with no hole, -1 the checked
  path; routed_memory.gd skips the typed-block scan on pages no block touches) took it to 100 s;
  then the routed memory a plain field set up on first use (the getter cost ~150 ns an access),
  width-specific read paths and the Mac bytes decoded directly when the memory starts at 0: 82 s.
  Per 32-bit read (micro-benchmark): Mac memory 473 ns, routed to Mac memory 469 ns, routed to a
  record 576 ns. Same values as before (the checked path unchanged); mission 2 Native matches its
  fixture, the Native tests pass.
- Enhanced, mobile landscape (approved): `enhanced/` (README), `--edition=enhanced`. The on-screen
  controller `touch_controller.gd` sends the key bound to each Native action through Godot's input,
  so the Native keyboard path takes it unchanged; Native gained the original's keyboard-flying keys
  as actions (`8` / `2` / `4` / `6` / `5`, space to fire; process_flight_input_and_outcome's
  cases). Touch: tap = one down and one up, a hold = a key held without auto-repeat, multi-touch by
  index, drag-off and focus loss release. Handheld orientation: sensor landscape (it was portrait).
  tests/enhanced/test_enhanced_touch.gd: layouts at five phone / tablet sizes, the touch semantics,
  each button's Mac key and character through the Event Manager, desktop default off. Export:
  Godot's Android and iOS templates are installed; the Android SDK and an iOS signing team are not,
  so no mobile build has been made or run on a device.
- Validation (loop 9 stopped): Native full sessions 1.0.3 / 1.0.4 identical to Original over 852
  entries incl. heap; mission 2 Native matches its fixture; Godot 103/103; Python QA, strict sync,
  resource mirror, links, protected checksums (32 OK), diff check pass; no Original file changed.
  Open: report A (needs the tester's details); Native still 1.5x Original's time (typed-block
  lookups for camera fields, per-call view objects); the host / Toolbox replacement; an Android
  SDK and an iOS signing team for device builds; Leyte Gulf (Ghidra not installed).

Loop 10 (Performance, Leyte Gulf, mobile, environment; job a65b2593):

- Environment audit: 16 GiB free of 228 GiB. Nix store 36 GB, of which 35.3 GB has no GC root
  (the dead list includes paths in use: Godot's export templates link into the store, and every
  project's nix-shell is unrooted): not collected; a safe collection needs the active environments
  rooted first (approval). Brave cache 5.1 GB (regenerable; Brave was running: not cleared).
- Leyte Gulf, Ghidra: Ghidra 12.2 at /Applications/Ghidra (Java 25, 68000 language, headless
  analyzer); macOS ARM natives built (support/gradle/gradlew buildNatives) for the decompiler. MCP:
  community servers exist (none bundled with Ghidra); not installed (third-party code with file and
  process access; the headless scripts suffice). Pipeline: tools/leyte/extract_overlay.py (the
  overlay verbatim, QA leyte-overlay), tools/leyte/ghidra_export.py with
  tools/leyte/ghidra/{SeedLeyteOverlay,ExportLeyteC}.java: sources/leyte-gulf-1-0/CODE/Leyte Gulf
  overlay.c, 523 functions, 522 decompiled. Seeds are the exact correspondences (identical / moved:
  Ghidra's analysis finds 35 / 35 and 60 / 61 of them as functions; the instruction-shape matches
  can land inside instructions, 65 / 82 confirmed). Next: the semantic .decomp.c of the 262.
- Enhanced, Android: debug export (export_presets.cfg) and the emulator run: the game starts in
  landscape, dialogs answer touches, Flight School flies with the touch throttle (screenshots).
  The controller now sits in the bars beside the picture on 2:1 phones. The launcher starts
  Enhanced on mobile devices.
- Validation (loop 10 stopped): Godot 103/103; Python QA (with leyte-overlay), strict sync, links,
  protected checksums (32 OK), diff check pass; no Original file changed. Free space 15 GiB.
  Pending approval: rooting the active Nix environments then collecting (~35 GB candidates); Brave's
  cache with Brave closed (5.1 GB). Next: Leyte's 262 new overlay functions to semantic C (from the
  Ghidra export); Native's remaining 1.5x; the host / Toolbox replacement; a physical Android device.

Loop 11 (global development; job 04fb0336):

- Environment: Brave confirmed closed; its regenerable caches cleared (profile data, bookmarks,
  passwords, cookies, extensions, sessions kept): +5.2 GiB, 24 GiB free. Nix unchanged (the safe
  plan, rooting the active environments before collecting, still needs approval).
- Native performance: width-specific direct writes in routed_memory.gd (as the reads: one page,
  Mac bytes or one record's bytes, no call) and one cached SceneView per port. Per 32-bit write:
  Mac 987 ns, routed to Mac 963 ns, routed to a record 1342 ns (1902 before). Benchmark (emulator
  off, back to back): Native 72-76 s against Original 55 s (1.0.3), 74 s against 52 s (1.0.4); the
  router's checked path is ~2% of accesses; the rest is record access through object properties
  (next: one router-owned byte array for record bytes). Mission 2 Native matches its fixture.
- Leyte Gulf: the unresolved function FUN_00019fb4 (368 bytes) is real code; Ghidra's decompiler
  process crashes on it (an analysis limitation): manual 68000 review. Names table
  tools/leyte/names_from_correspondence.py -> CODE/Leyte Gulf overlay.names.json: 523 functions,
  95 named (identical / moved Hellcats functions), 65 candidates (same instructions, to confirm),
  363 Leyte's own; QA leyte-overlay checks it.
- Google Play account (matyo91@gmail.com): official Help researched; report and support-request
  draft in the task workspace (its research notes, google-play-account); nothing submitted or paid.
  Debug APKs and the emulator need no Play account.
- Validation (loop 11): Godot 103/103; Python QA, strict sync, protected checksums (32 OK), diff
  check pass. Full stepped sessions: Native 1.0.3 / 1.0.4 identical to Original (game globals,
  world, heap, RNG over 852 entries) and to the earlier references. Finding: the Original stepped
  run is load-sensitive: four sessions run at once, Original diverged from its own reference (at
  entry 331 for 1.0.3, entry 12 for 1.0.4; RNG unchanged) while Native did not; run alone it
  matches. (Cause found in loop 18: the sessions shared one save folder, not a clock.)

Loop 12 (continuation of job 04fb0336):

- Native performance: the records' bytes now live in one byte array addressed like the Mac memory
  (native_records.gd `space`, 16 MB; a record is its handle and size), so a record access is
  `records.space[address]`, with no record lookup; holes, growth, release and the traces' memory
  image as before. Benchmark (stepped Flight School, emulator off, one at a time): Native 67-71 s
  (72-76 before), Original 55 s. Mission 2 Native matches its fixture; Godot 103/103.
- Leyte Gulf, correspondence corrected (supersedes loops 10-11's counts): 1.0.3's overlay addresses
  are file offsets (348 of 443 start on a prologue there), but leyte_correspondence.py and
  leyte_match.py sliced the header-less code with them, comparing each function from +0x20 into
  the next. Fixed for the overlay (host segments were right): identical 36, moved 73, same
  instructions 145, not found 189 (was 36 / 63 / 82 / 262); every match now starts on its 1.0.3
  function's first word (254 / 254; 147 / 181 before). The unresolved function FUN_00019fb4 was one
  of twelve seeds inside instructions (erase_dots_depth4's tail match, inside FUN_00019fac, which
  uses the 68020 muls.l): re-exported, 511 functions, 511 decompiled, no bad data. Names: 87 named,
  127 candidates, 14 ambiguous (one Leyte offset for several equal 1.0.3 functions, now listed with
  every claimant instead of the last one written), 283 Leyte's own.
- Validation (loop 12): Native full sessions 1.0.3 / 1.0.4 identical to Original (game globals,
  world, heap, RNG, 852 entries; run one at a time); Godot 103/103; Python QA (leyte-overlay with the
  names check), correspondence --check, protected checksums (32 OK), diff check pass; no Original or
  source-evidence file changed. 24 GiB free. Next: the remaining Native gap (~1.25x: profile the
  routed port's calls); Leyte semantic C, starting with the 87 named functions' Hellcats
  counterparts and the 14 ambiguous offsets.

Loop 13 (continuation of job 04fb0336):

- Native performance, measured by part (stepped Flight School, 1.0.4, one run at a time; temporary
  harnesses, removed): CODE 6, the dialog / resource / menu managers alone cost nothing (53 s, as
  Original); the Native overlay all of the gap (68 s). Inside it: with every Godot storage off, the
  router alone cost 7 s (60 s routed, 53 s with the port reading the Mac memory directly), about
  115 ns an access over ~60 million accesses; storage added the rest (records ~4 s, globals ~4 s,
  blocks ~3 s, replay camera ~1 s, not additive); the hand-written functions' own cost (views
  allocated per call) ~1.2 s inclusive.
- Fix: routed_memory.gd reads one page table of its own (`route`: MAC, RECORD or CHECKED per 32-byte
  page, composed from the typed blocks' pages and native_records' `fast`, which now reports its
  changes, pages_changed) and owns the records' bytes (`space`; native_records.gd keeps which ranges
  are records). A fast access is one table index and one array index, with no second table and no
  other object's property. The generator passes the router to the record store.
- Result: Native 55 s against Original 54 s (was 67-71 s; 82 s at loop 9's start, 209 s before the
  page table). Mission 2 Native matches its fixture; Godot 103/103.
- iOS readiness (no paid enrollment): Xcode 27.2 installed but not the active developer directory
  (xcode-select is the Command Line Tools: switching needs the administrator) and no iOS Simulator
  runtime (a download of several GB, not made); enhanced/README.md.
- Validation (loop 13): Native full sessions 1.0.3 / 1.0.4 identical to Original (game globals,
  world, heap, RNG, 852 entries), 197 / 198 s each with tracing; Godot 103/103; Python QA
  (routed-port regenerated), protected checksums (32 OK), diff check pass; no Original file changed.
  Next: Leyte semantic C; the host / Toolbox side of Native (QuickDraw presentation first); Android
  lifecycle on the emulator.

Loop 14 (continuation of job 04fb0336):

- Enhanced, application background (enhanced/README.md): enhanced/app_clock.gd makes the Mac sleep
  while the application is paused: TickCount stops and the program's thread waits at its next
  TickCount until the resume (the time away is never the program's); the touch controller releases
  its keys on the pause. Installed by enhanced/menu_manager.gd (the Native Menu Manager plus the
  clock: the last edition-built manager, installed after the Event Manager and before the program's
  thread, so no race); native_game.gd gained menu_manager() for it (Native unchanged). No Original
  file changed. tests/enhanced/test_enhanced_clock.gd; Godot 104/104.
- Emulator (debug APK, Android 37): two Home / relaunch cycles logged "application paused: the Mac
  sleeps" / "resumed: 4301 ticks slept" (6635 in all); same process; 1.2 % CPU in the background
  against 18 % in the foreground; the game thread runs again after the resume. The emulator was
  memory-starved (8 GB host; the first boot's system server stalled under swap; a cold boot with a
  1.5 GB guest and two cores installed the APK) and the program did not reach the pilot roster in
  nine minutes, so a mid-flight background is not yet seen. Not a device result.

Loop 15 (continuation of job 04fb0336):

- Leyte Gulf, semantic C first stage: tools/leyte/semantic_overlay.py writes CODE/Leyte Gulf
  overlay.decomp.c from the Ghidra C and the names table: the 87 named functions under their
  Hellcats names (`/* Original: FUN_... */` kept). Globals by binary evidence: in a named pair the
  relocated longs sit at the same offsets (asserted for all 87), so each pairs a Leyte address with a
  Hellcats one; 97 Leyte globals get the Hellcats name (each tied to one named Hellcats global; 0
  conflicts), applied in the bodies with the #defines listing the pairs. Generated, `--check` in QA
  (leyte-overlay); not reviewed C. Next: the 14 ambiguous offsets (equal Hellcats functions: which
  Leyte copy is which), the 127 same-instruction candidates.

Loop 16 (continuation of job 04fb0336):

- Leyte Gulf, call-site evidence (names_from_correspondence.py, schema 2): in a named pair the code is
  the same, so a PC-relative call at the same offset reaches the counterpart on each side; the
  Hellcats target names the Leyte one when all such calls agree. Checked first against the existing
  names: 8 overlaps, 8 agree, 0 disagree. Result (to a fixpoint): sin_fixed14 / cos_fixed14 placed
  (an ambiguous pair, equal in 1.0.3; their code matches there too), 10 "called" functions (role
  proven by the call, code different: matmul3x3_s16, build_attitude_matrix_3ang,
  transform_int3_vecs_by_matrix3, face_is_back_facing, unlock_handle_purgeable, ...). Counts: 89
  named, 10 called, 126 candidates, 12 ambiguous, 274 own (511). Semantic C regenerated: 89
  functions, 99 globals, calls to called functions named. Relocation pairing alone could not split
  the other ambiguous groups (too few paired globals), except register_smoke_template's copy at
  0x1370c (+2 / -0 against -1 for every other claimant): noted, not applied (one rule at a time).

Loop 17 and close of job 04fb0336 (loops 11-17):

- Environment: this session's own scratch files removed (~250 MB). Remaining large regenerable
  caches belong to other applications (Cursor's updater download 0.97 GB, Playwright browsers
  0.57 GB) or to the emulator's state (AVD userdata 2.4 GB): not touched. 22 GiB free.
- Final validation: Godot 104/104; Python QA; Leyte correspondence --check; strict sync; protected
  checksums (32 OK); diff check; mission 2 Native matches its fixture; no Original or source-evidence
  file changed. Native full sessions last verified identical to Original in loop 13 (no Native
  overlay or router change since).
- Open, carried forward: Nix collection (rooting the active environments first) needs approval; iOS
  Simulator needs Xcode as the active developer directory (administrator) and a Simulator runtime
  download; Android on a physical device and a mid-flight background; report A (cockpit) needs the
  tester's details; Leyte's 12
  ambiguous offsets, 126 candidates and 274 own functions; the Native host / Toolbox replacement.

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

Loop 18 (job 7f629bda: Native runtime, Leyte Gulf, Enhanced):

- Determinism of concurrent stepped sessions, cause proven: smoke_session.gd saved into one fixed
  folder (user://smoke_session_resources) for every process, emptying it at start; the game writes
  its pilots and settings there, so concurrent sessions cleared and overwrote each other's saved
  state. No host-clock read was involved (ticks() honours the stepped clock; the sound hook and
  1.0.4's double buffer run on the stepped tick). Reproduced: two concurrent 1.0.4 sessions, one
  identical to its reference, the other diverging at entry 12 (as in loop 11). Fix (test
  infrastructure only): one folder per process (smoke_session_resources/<pid>, emptied first,
  removed at the end); protection_session.gd likewise. Verified: four concurrent sessions (1.0.3
  and 1.0.4, two each) all identical to their solo references (game globals, world, heap, RNG over
  852 entries; comparison unchanged). Concurrent reference sessions are safe again. The suite's own
  fixed folders (test_stepped_run, test_native_saves) run one at a time inside one process.
- Validation: Godot 104/104; Python QA; diff check; no Original port or runtime file changed.

Loop 19 (job 7f629bda):

- Native presentation, path mapped: a colour frame is drawn in bands into a 0x2000-byte heap buffer
  (present_spans_to_window: span filler *DAT_00020ac0), each band copied to the window by host service
  +0x296 = A5 - 0x10f2 = JT $322, ID02 blit_offscreen_frame_to_window: an offscreen colour port over
  the buffer (made once, A5 - 0x103a), PenMode / BackColor, then CopyBits into the window's port,
  which the emulated QuickDraw does pixel by pixel in GDScript, clipped to the port's clip rectangle
  (quickdraw.gd copy_bits); depth 1 goes through ID06 rasterize_and_present. MacScreen presents the
  screen bytes through a palette shader. The band buffer is a Native record with a hole only because
  CopyBits reads it: replacing that copy is the first QuickDraw removal on the frame path.
- Image regression, before any migration: trace entries now hash the screen's bytes and the screen
  device's colour table ("screen", "palette": state_trace.gd RENDER_FIELDS, compared whenever both
  traces have them). Mission 2's fixture gained them from a fresh Original recording whose existing
  fields equal the fixture's exactly. Original and Native both match it; a copy with one screen hash
  altered is reported at that entry (tick 1830, field screen). Full 1.0.3 session, Original against
  Native: screen and palette identical at all 852 entries (with game globals, world, heap, RNG).
- Validation: Godot 104/104; Python QA; diff check; no Original port or runtime file changed.

Loop 20 (job 7f629bda):

- Native frame path, measured: the colour frame reaches the screen without QuickDraw. With host word
  +0x9e clear (the normal case) overlay_frame_step's span filler writes the frame straight into the
  screen memory (host +6, the direct-screen path); present_spans_to_window and its CopyBits run only
  with +0x9e set: once in a whole Flight School mission. Besides those bands the only CopyBits into
  the screen during flight is the cockpit panel (rows 297..460, twice). So the frame path's emulated
  dependency is the shared screen memory, not QuickDraw.
- Native band copy: the host blit's CopyBits made Native for the band (native_overlay.gd
  _install_band_copy / _copy_band / copy_band_pixels: same depth 4 or 8, srcCopy, unscaled, no mask,
  asserted; clipped as the runtime clips, clip_of included); the band buffer stays a Godot record (no
  hole on that path). Outside a band the runtime's CopyBits runs. The traces cannot see this copy
  (its one band is painted over: a mutated copy still matched), so tests/native/
  test_native_band_copy.gd holds copy_band_pixels to the runtime's copy_bits on the same bytes (depth
  8 and 4, clipping, source rows past its bounds, odd nibble alignments); a mutated copy fails all 5.
- Validation: Godot 105/105; Python QA (routed-port regenerated); mission 2 Native matches the
  fixture with screen hashes; full Native sessions 1.0.3 / 1.0.4 identical to Original (game globals,
  world, heap, RNG; 1.0.3 screen and palette too) over 852 entries; diff check; no Original file
  changed. Next for Native presentation: the screen memory itself (the direct-screen frame and the
  QuickDraw-drawn panel share it), not the frame copy.

Loop 21 (job 7f629bda):

- Leyte Gulf, candidates confirmed by layout (names_from_correspondence.py step 3): a same-instruction
  candidate is named when every instruction matches once addresses, PC-relative displacements, branch
  targets and register-relative displacements ((An) read as $0(An)) are masked: same instructions,
  registers, addressing modes and immediates, only structure fields and globals moved. 91 of the 126
  qualify. What moved is itself a finding: Leyte re-lays out the host world (fields +0x3c in places:
  +0x2c6 -> +0x302) and the objects (position +0x18 -> +4, controller fields shifted). The other 35
  stay candidates with their first differences listed: immediates (dispatch_entity_tick's cmpi #5 ->
  #8, create_enemy_fighter's moveq #4 -> #7), frame and allocation sizes, a loop register, code
  addresses as immediates (overlay_main_entry), 15 with a different instruction count.
  Layout-named functions are kept out of call-site and relocation pairing (their byte offsets can
  shift). Counts: 180 named (89 same code, 91 layout), 10 called, 35 candidates, 12 ambiguous, 274
  own (511). Semantic C regenerated (180 functions, 99 globals).
- Validation: Python QA (leyte-overlay: names and semantic C checks); diff check; no Godot change.

Loop 22 (job 7f629bda):

- Enhanced Android, mid-flight background on the emulator: with the host GPU (`-gpu host`, Apple M1;
  SwiftShader before) the game reaches flight in about two minutes by touch (OK taps through the
  roster and mission dialog). Home for a minute mid-flight, then relaunch: "paused" / "resumed: 3704
  ticks slept" logged, same process, the scene after the resume as before Home (no game time passed),
  the game continuing afterwards (screenshots; enhanced/README.md). First attempt: an ANR during a
  10 s long press while the host swapped (3.9 / 5 GB) and the system UI's own gesture monitor also
  timed out (app UI thread idle, engine and game threads running): an emulator stall on this 8 GB
  host, not shown to be the game's; then a delayed tap hit the dialog's Close app. Second attempt
  (Google background apps force-stopped, short taps): no ANR. Not a device result.
- Validation: no code changed this loop; emulator stopped afterwards (swap back to 2 GB).

Close of job 7f629bda (loops 18-22):

- Final validation: four full sessions run at the same time (1.0.3 / 1.0.4, Original / Native):
  Original identical to its solo reference, Native identical to Original (game globals, world, heap,
  RNG, screen and palette) over 852 entries each; Godot 105/105; Python QA; Leyte correspondence
  --check; strict sync; protected checksums (32 OK); diff check; no Original port, runtime or source
  evidence changed. 22 GiB free.
- Open, each needing a design step: Native presentation beyond the band copy (the frame is written
  straight into the shared screen memory, which QuickDraw also draws the cockpit panel and menu bar
  into); the Leyte runtime and Original port (host segments and the embedded overlay); a whole mission
  and the menu bar by touch, audio after a resume; a physical Android device; report A (cockpit) with
  the tester's details; Nix collection (approval); iOS Simulator (administrator, runtime download).


Loop 23 (job 47d42d06: Native, Leyte Gulf, mobile):

- iOS Simulator, checked rather than assumed: Xcode 27.2 active, iOS 27.2 runtime, iPhone 18 Pro
  booted (15 GiB free after the runtime). An "iOS" export preset (Xcode project only) and a project
  icon (the game's Finder icon, icl8 128: BNDL 132 -> FREF 130 -> ICN# 128; required by the iOS
  export, Android's warning gone too); the export succeeds. Blocked at the link: Godot 4.7.2's
  official template has an x86_64-only Simulator slice (no arm64 `_main` for this M1's Simulator),
  Rosetta is not installed (a system install) and no Rosetta destination is offered. Options in
  enhanced/README.md: Rosetta (administrator) or an arm64-Simulator template built from source.

Loop 24 (job 47d42d06):

- Native screen ownership, census before any storage change (temporary instrumentation, removed:
  every trap wrapped with a screen comparison, routed screen writes sampled by caller; Native mission
  2, 20 s). The screen (0x700000, 640 x 480) reaches the program through four aliases: low-memory
  ScreenBase (0x824), QuickDraw's screenBits, the main GDevice's PixMap and every port's bitmap.
  Writers: (1) the Native overlay's span writers _span8_write / _span4_write, which draw only the
  spans that changed since the last frame (the direct-screen frame; ~450 000 routed byte writes in
  20 s, with the band copy's), (2) emulated QuickDraw traps: window and dialog set-up (ShowWindow,
  DrawDialog, DisposeDialog...) and in flight LineTo (18 of 1474 calls changed it), FrameRect /
  PaintRect, polygons (likely the instrument needles and panel), (3) the Menu Manager directly
  (menu_manager.gd, save-behind). Readers: MacScreen (the presenter), the traces, the Menu Manager.
  So one storage is shared by Native overlay code, the emulated QuickDraw and the Menu Manager:
  moving it to Godot now would make two stores (no dual storage). Order: first the QuickDraw drawing
  done in flight (the needles' LineTo and the panel's rects / polygons) made Native, exact against
  the screen hashes; then the screen storage; window chrome and dialogs last.

Loop 25 (job 47d42d06):

- Native screen ownership, census by time (temporary, removed: each trap that changed the screen
  logged with its tick, Native mission 2, 60 s): every one falls in the first 6 s, the window and
  dialog set-up at 0-5 s (ShowWindow, DrawDialog, DisposeDialog, rects) and the panel layout at 6 s
  (LineTo x18, FrameRect / PaintRect, polygons: _lay_out_view_panels' host drawing); the last at tick
  361. From there to the end (54 s of flight) no Toolbox call changes the screen: the flying picture,
  needles included (draw_gauge_needle's depth routines with host word +0x9e clear), is written only by
  the Native overlay's routed code; the ~1 400 in-flight LineTo calls never reach it. So in flight
  the screen is already Native-written; QuickDraw's part is set-up (panel layout, then window /
  dialog chrome). Next: the panel layout's host drawing made Native (exact against screen hashes),
  which leaves dialogs / window chrome as the screen's only QuickDraw writers.

Loop 26 (job 47d42d06):

- Leyte Gulf, relocated immediates: in the layout rule an immediate now counts as layout when a
  relocated long lies in the instruction in both binaries (the overlay's relocation tables mark it
  as an address, not a constant). Four more candidates named, among them overlay_main_entry (22
  differences, all routine addresses stored into the host world), configure_renderer_for_display
  (the span fillers it installs: 0x3794 in 1.0.3, 0x4294 in Leyte) and create_friendly_bomber.
  Counts: 184 named (89 same code, 95 layout), 10 called, 31 candidates, 12 ambiguous, 274 own.
  Native panel layout (the 6 s set-up drawing): deferred to a design step (it needs QuickDraw's line
  and polygon rasterisation reproduced exactly, for a set-up-only path).

Close of job 47d42d06 (loops 23-26):

- Final validation: Godot 105/105; mission 2 Original and Native match the fixture (screen and
  palette included); Python QA; strict sync; protected checksums (32 OK); diff check; no Original
  port, runtime or source evidence changed; no temporary files left. 13 GiB free (the iOS runtime).
- Waiting on decisions: iOS Simulator (install Rosetta, or build Godot's iOS template with an arm64
  Simulator slice here); the Native panel layout (exact QuickDraw line / polygon rasterisation for a
  set-up-only path, before the screen storage moves to Godot). Also open: a whole mission and the
  menu bar by touch on Android, audio after a resume; Leyte's 31 candidates, 12 ambiguous groups and
  the Leyte runtime; report A (cockpit); Nix collection (approval).

Loop 27 (job 2cabd141: Native rendering, Leyte, mobile):

- Disk (13 GiB free of 228): Nix store 36 GB; iOS Simulator runtimes 15.5 GB (two iOS 27.2 images:
  24B8117 and the device-specific 24B91016s "iPhone19-4"; the device list names both "iOS 27.2",
  so which one the booted iPhone 18 Pro uses is not shown); Android SDK 4.4 GB, emulator state 3.1 GB;
  Library caches 1.7 GB (Cursor's updater download, Playwright: other applications'); Xcode
  DerivedData empty; nothing of this project's left to clean. Cleanup proposal, largest first, each
  the user's decision: (1) Nix collection after rooting the active environments (~35 GB); (2) the
  iOS 27.2 image not used by the test device (~7.7 GB) once identified; (3) Cursor's update cache
  (~1 GB). Development continues within the 13 GiB (no large builds).
- Native cockpit panel, drawing commands recovered: overlay_frame_step's _lay_out_view_panels draws
  through host services: +0x26a = A5 - 0x111e = JT $372 run_segment3_entry_and_unload (the panel and
  gauge faces are ID03's: MoveTo / LineTo / PenSize, PaintRect / FrameRect / InsetRect, FrameOval,
  regions as clips (OpenRgn / RectRgn / SetClip), text in the game's fonts (TextFont / TextSize /
  StringWidth), resources), +0x26e = JT $3aa blit_image_clipped_to_region, +0x2c2 = JT $35a
  swap_mmu_mode. A separate Native drawing layer reproducing those pixels would be QuickDraw rewritten
  (lines, ovals, regions, glyphs): not done.
- Screen ownership plan (no Original file changed, no second QuickDraw): every Toolbox manager reaches
  memory only through its own `memory` field (quickdraw, quickdraw_traps, menu, window, dialog,
  TextEdit: no raw byte access; the Menu Manager's save-behind addresses the screen through it too),
  and the host ports through theirs. So one Godot-owned screen store (indexed bytes, the screen's
  depth and row bytes) can take the screen's pages for every writer at once: the Native overlay's
  router (a SCREEN route), and a screen router given to the Toolbox managers and the host segment
  ports (screen pages to the store, the rest to the Mac memory); the presenter and the traces' screen
  hash read the store; the Mac memory's screen region is poisoned and must never be read or shown.
  All writers switch together (no dual storage); verified by the screen hashes against Original.
  Next: that switch, behind a Native storage switch, with the poison check.

Loop 28 (job 2cabd141):

- Native screen ownership: the screen's bytes live in Godot (native/display/screen_memory.gd: a store
  for 0x700000 .. + 640 x 480; every other address goes to the Mac memory, unchanged). Installed before
  the program's thread starts (native_screen.gd's attach in the Native scene, the smoke session for
  --edition native) into every holder of the machine's memory (Toolbox managers, segment loader and
  host ports, overlay loader, runtime routines; found by walking their fields), and registered so a
  port built later (the overlay's, given the Mac memory at its load) is routed too: the Native router
  has a SCREEN route. The presenter, the traces' screen hash and screenshots read the store; the Mac
  memory's screen is filled with 0xa5 and checked at the session's end. First run: the overlay port
  (built at load) still wrote the Mac screen and the check caught it (screen diverged at tick 390);
  fixed by the registration. Hot paths inlined: Native mission 2 back to 60 s (67 s before).
- Verified: mission 2 Native matches the fixture (screen and palette included); full Native sessions
  1.0.3 / 1.0.4 identical to Original over 852 entries (game globals, world, heap, RNG, screen,
  palette), the Mac memory's screen untouched throughout (menus, dialogs, missions); Python QA; diff
  check; no Original file changed. QuickDraw still draws (window / dialog set-up, the panel layout),
  now into the Godot store; the next dependencies are QuickDraw itself and the Mac memory for the
  rest of the address space.

Loop 29 (job 2cabd141):

- Leyte Gulf, runtime architecture measured (docs/topics/LEYTE_GULF.md): A5 world 0x2322 (0x225e in
  1.0.3), jump table 184 entries (175); CODE 1 / 5 / 6 / 7 the same code as 1.0.3's but not the same
  bytes (CODE 1: one A5 offset, 0x4b2 -> 0x50a; the others their headers and A5 offsets); CODE 2 / 3 /
  4 / 9 grown, CODE 8 the data-fork overlay loader; the overlay the same format (0x107) with its own
  sizes, entry and relocations. So the 1.0.3 host ports cannot run Leyte as they are (A5 offsets are
  constants in them). Plan recorded: a title profile (disk, jump table from CODE 0, resources), an A5
  offset map from the host correspondence's identical / moved pairs, host ports remapped and checked
  against Leyte's bytes, the data-fork overlay loader, then the overlay port.

Close of job 2cabd141 (loops 27-29):

- Native: the screen is Godot-owned (loop 28), verified on full sessions against Original with the
  Mac memory's screen untouched; QuickDraw still draws (window / dialog set-up, the panel layout)
  into that store; the host, the Toolbox and the rest of the Mac memory remain emulated.
- Validation (loop 28, unchanged since but documentation): Godot 105/105; Python QA; full Native
  sessions 1.0.3 / 1.0.4 identical to Original (state, screen, palette) over 852 entries; mission 2
  fixture; diff check; no Original file changed. 15 GiB free.
- Next: Leyte's A5 offset map and title profile toward a first execution path; QuickDraw's set-up
  drawing; Android full mission by touch; iOS once the Simulator path is decided.

Loop 30 (job 89a0c0a9: Leyte execution, Native QuickDraw, mobile):

- Leyte Gulf, A5 world map (tools/leyte/a5_map.py -> sources/leyte-gulf-1-0/a5_map.json, checked in
  QA leyte-overlay): from the host correspondence's identical / moved pairs (171; 4 ambiguous left
  out), A5-relative operands at the same instruction pair 1.0.3's offsets with Leyte's: 161 mapped (114
  globals, 47 jump-table slots), 1 conflict (slot 0x28a), 98 offsets the 1.0.3 host uses unreached.
  The globals moved in blocks (38 same, then -126, -134, -62...): a per-offset map. A5 world 0x225e ->
  0x2322. Next: the title profile (disk, application, CODE 0 jump table) and the first host port
  remapped through the map and checked against Leyte's bytes.

Loop 31 (job 89a0c0a9):

- Leyte Gulf, first host ports verified against Leyte's bytes (tools/leyte/gen_host_ports.py ->
  sources/leyte-gulf-1-0/host_ports.json, godot/original/leyte/port/; checked in QA): ID01 reused (its
  six routines byte-identical); ID07 generated from 1.0.3's port with its A5 constants remapped
  (string table -0xf46 -> -0xf84, four copy targets, HLock's slot 0x272 -> 0x28a; memmove's slot 0x4fa
  -> 0x542 verified to reach the same routine through CODE 0's entries) and one reviewed immediate:
  Leyte's STR# 129 has 123 strings (cmpi #$7c, 1.0.3 #$79). Cases: Leyte's own CODE 7 (with its CODE 1
  / CODE 2 helpers) run on the 68000 emulator (tools/leyte/gen_leyte_id07_cases.py); the port matches
  all 6 (tests/leyte/port/test_leyte_id07.gd).
- Found by that test, not by the comparison: capstone stopped disassembling at ID07's inline switch
  table, so the instruction check missed the bound. All Leyte tools now disassemble past inline data
  (skipdata): the names drop to 183 (animate_smoke_trail's layout match withdrawn: counts differ past
  its data; 32 candidates), the A5 map gains one pair (162).
- Validation: Godot 106/106 (with tests/leyte/port/test_leyte_id07.gd); Python QA (leyte-overlay:
  names, semantic C, A5 map, host ports); diff check; no Original file changed. 15 GiB free.

Close of job 89a0c0a9 (loops 30-31): Leyte's A5 map (162 offsets) and its first host ports (ID01
reused, ID07 generated and verified against Leyte's own bytes on the 68000 emulator). Not yet: the
title profile (disk, CODE 0 jump table, resources into the runtime), the data-fork overlay loader, an
end-to-end Leyte start; Native QuickDraw replacement; the Android full mission; iOS (the Simulator
path is the user's choice).

Cycle 1 of job 6ef3b3cb (20-minute cycles: Leyte, Native, mobile, iOS):

- Leyte Gulf, title profile first stage (resource loading): tools/leyte/extract_resources.py extracts
  Leyte's resource fork (113 resources) and CODE 0..9 verbatim from LeyteGulf.dsk into
  sources/leyte-gulf-1-0/ (manifest with SHA-256) and mirrors the resources into
  godot/assets/leyte-gulf-1.0/ under the 1.0.3 mirror's folder names; assets/ui/resource_files.gd has
  a title ("hellcats" / "leyte-gulf") whose Leyte root never falls back to Hellcats'.
  tests/leyte/test_leyte_resources.gd: every Leyte resource loads with its extracted bytes, a
  Hellcats-only resource (HELL 0) is absent, Hellcats' resources unchanged after. QA checks the
  extraction (leyte-overlay).
- iOS Simulator (authorized): the official 4.7.2 template's Simulator libgodot.a is x86_64 only
  (lipo), MoltenVK's has arm64, the camera plugin is unused. Godot's source at 4.7.2-stable (commit
  ed1daf0bf, the installed engine's) cloned outside the project (scratch, 445 MB); SCons from Nix,
  Xcode's toolchain; building template_debug for the arm64 Simulator (-j3, one variant). The
  Simulator supports neither Metal nor Vulkan: the export will need the Compatibility renderer.
- iOS Simulator runs the game (cycle 1, continued): the arm64 Simulator library built in 8 min,
  made fat with the official x86_64 slice into a copy of the official template
  (~/Library/Caches/hellcats-ios-template/4.7.2/ios.zip); tools/mobile/ios_simulator.sh exports with it
  (the preset restored after), builds unsigned and launches on the booted iPhone 18 Pro: landscape,
  the pilot roster and the menu bar shown. Open: the touch controller takes its overlay layout there
  (safe area / units to check), then touches, a mission, lifecycle and audio. godot/enhanced/README.md.

Cycle 2 of job 6ef3b3cb:

- iOS Simulator, played by touch: roster OK, Select a Mission (Flight School, OK), the flight with
  cockpit and instruments, the throttle by the controller's + (RPM, manifold, the aircraft rolling;
  released after the press), background 30 s / resume logged in the same process. Controller layout
  fixed for notched phones: measured on the Simulator (size 2622 x 1206, safe area x 186..2436, y
  0..1146, picture x 507..2115): the safe side bars (321 px) were under the side-bar minimum, so the
  overlay layout was used; now the bars run to the screen's edges when the safe ones are too narrow,
  the safe area kept vertically (touch_controller.gd; test with that geometry, safe_override).
  Open: the doubled radio labels (Android and iOS), audio, a whole mission.
- Leyte Gulf, title profile linkage data: tools/leyte/build_jump_table.py writes
  godot/original/leyte/data/jump_table.json from Leyte's CODE 0 (184 slots; A5 world 0x2322 below,
  0x5e0 above) in 1.0.3's format, naming only slots a Leyte host port proves (5: ID01's four divide
  entries, ID07's init_string_pointer_groups at 0x5d2). Checked in QA. Next on that path: a Leyte
  segment loader configuration (ports per segment, this jump table) and the data-fork overlay loader.

Cycle 3 of job 6ef3b3cb:

- Leyte Gulf host ports: ID05 (26 functions) and ID06 (24) generated from 1.0.3's ports
  (tools/leyte/gen_host_ports.py), each verified instruction by instruction against Leyte's bytes.
  The generator grew what this needed, each recorded in host_ports.json: globals the A5 map does not
  reach paired by the function's own match ("a5_local", refused if contradicting the map); jump-table
  slots identified by the routine they reach, including CODE 9 glue (same bytes to its exit, the
  shared exit's displacement aside) and same-instruction counterparts (the match kind recorded,
  "jt_counterparts"); function ranges ending at every routine entry (jump-table targets and the
  runtime's own unexported routines: ID05's code_dialog_filter range had swallowed 0x3ba's operands);
  globals Leyte keeps in place need no rewrite. Jump table rebuilt: more slots named.
- Not yet: Leyte emulator cases for ID05 / ID06 (ID07 has them): their verification is the
  instruction-level match only.

Cycle 4 of job 6ef3b3cb:

- Leyte Gulf, behavioural proof for ID05: tools/leyte/gen_leyte_host_cases.py runs 1.0.3's own case
  generators (gen_id05_protection_cases, gen_id05_dialog_cases) unchanged on Leyte's CODE 5, swapping
  below them the bytes, the routines' offsets (correspondence) and, before the code runs only, every
  A5-world input address and jump-table stand-in through the port's verified remap (a field moves
  with its global); the fixture's recorded inputs are translated the same way. The generated Leyte ID05
  port matches all 88 cases (61 protection, 27 code dialog): tests/leyte/port/test_leyte_host_ports.gd.
  First attempt translated the program's own stores too (they go through the emulator's write):
  fixed by translating only outside call(). ID06 not yet: its generators record scenes built by
  running routines, already in Leyte's layout, which a translation would move twice.
- Leyte proof status: ID01 reused (byte-identical), ID07 and ID05 emulator-verified, ID06 instruction-
  verified only.

Close of job 6ef3b3cb (cycles 1-4):

- iOS Simulator runs and is played by touch (arm64 Simulator library built from Godot 4.7.2's source,
  tools/mobile/ios_simulator.sh; controller layout fixed for notched phones). Leyte Gulf: resources
  load in the runtime (title "leyte-gulf"), jump table data, host ports ID01 / ID05 / ID06 / ID07
  (ID05 and ID07 matching Leyte's own bytes on the emulator, ID06 instruction-verified).
- Final validation: Godot 108/108; Python QA; mission 2 Original and Native match the fixture (screen
  hashes, the Mac screen untouched); strict sync; protected checksums (32 OK); diff check; no Original
  port, runtime or source evidence changed. 15 GiB free (Godot's iOS build tree, ~1 GB, kept in the
  session scratch for a rebuild; the template in ~/Library/Caches/hellcats-ios-template).
- Next: Leyte's host main program (CODE 2) and the data-fork overlay loader toward a first start;
  ID06's cases; Native QuickDraw replacement; Android and iOS whole missions, audio, the doubled
  radio labels.

Cycle 1 of job d44decba (Leyte startup, Native, mobile):

- Doubled radio labels (Select a Mission, Android and iOS): the Native dialog overlay's check boxes and
  radio buttons were bare Godot CheckBoxes (transparent, light theme text) over the Mac's own drawing
  of the item, which showed through offset from Godot's text; buttons, labels and edit fields were
  already opaque. Fix in the presentation layer only (native/dialogs/dialog_overlay.gd): opaque white,
  black text, Mac-style marks (a circle with a dot / a square with an X, under the names a CheckBox
  draws outside a ButtonGroup); items clipped to the dialog's portRect (now in the snapshot, "clip")
  as the Mac clips them (DLOG 129's right column overran the dialog once opaque). Regression test
  (tests/native/test_native_dialogs.gd): every DLOG's controls opaque, check / radio black on white
  with the marks; with the old code it fails on DLOG 129's radios. iOS Simulator: single labels,
  circles with the current choices dotted, nothing past the dialog's edge (screenshot). The game's
  logic and the Mac's drawing unchanged.
- Leyte Gulf host decompilation (tools/leyte/ghidra_host.py, ExportLeyteC.java taking an optional code
  start and title): CODE 8 -> sources/leyte-gulf-1-0/CODE/ID08.c (4 functions, all decompiled, seeded
  with its jump-table targets and exact correspondences). Its FUN_00000006 is Leyte's own overlay
  loader: opens the data fork, reads the 0x20-byte header into A5 - 0x1068, NewPtr(code + data + bss +
  4) aligned to 4 into A5 - 0x1048, reads code and data, then the relocation entries (8 bytes each,
  their flag bits deciding) and applies them. One branch carries Ghidra's "bad instruction data"
  (to review: a 68020 instruction or data). Next: its port and the File Manager's data fork for Leyte,
  then CODE 2.

Cycle 2 of job d44decba, and close:

- Leyte's CODE 8 loader (FUN_00000006), its calls resolved through Leyte's CODE 0: it opens its own
  file (pea $910: low-memory CurApName; JT $2c2), reads the overlay from the data fork (CODE 9 glue:
  open, read, NewPtr), relocates it, then runs the copy protection inline through the CODE 5 routines
  already ported and emulator-verified (poison_overlay_bytes, init_protection_table, read /
  write_DATA128_pair, encode_code28_to_buf54, record_code_index_equals, run_protection_dialog,
  restore_poisoned_overlay_bytes), ansi_strcmp, an alert (position_alert_template), ExitToShell on
  failure. Ghidra's "bad instruction data" there is the A-line trap $A9F4 (_ExitToShell), which its
  68000 language does not decode: not data. Next: an emulator harness running this loader on Leyte's
  bytes with the data fork served through the File Manager traps (the evidence a port is checked
  against), its port, then CODE 2 (38 identical, 42 moved, 30 same-instruction, 17 new functions).
- Validation at close (job d44decba): Godot 108/108; Python QA; diff check; no Original port,
  runtime or source evidence changed. 15 GiB free.

Cycle 1 of job 3d2e0c15:

- Leyte's overlay loader verified on its own bytes (tools/leyte/loader_harness.py,
  tools/leyte/loader_check.py, QA check "leyte-loader"). The harness loads Leyte's CODE 1..9, builds
  its jump table from CODE 0, sets CurApName "Leyte Gulf" and CurrentA5, and serves the real data fork
  through File Manager traps (_Open, _Read at the mark, _Close, _GetFPos, _GetEOF, _SetFPos; NewPtr /
  NewPtrClear on a deterministic heap; every call logged; unknown names and references get fnfErr /
  rfNumErr, short reads eofErr). CODE 8 FUN_00000006 then runs unchanged: header, NewPtrClear(183824)
  at 0x300000, text 128856, data 4084, 4274 relocation records (3448 applied, 826 pc-relative
  skipped), and FUN_00000350 reads the rest of the fork. The run stops at JT $51a (the CODE 2 routine
  after relocation, which needs host state the main program sets up: a nil pointer to _GetPtrSize),
  at JT $592 (protection) or at JT $4da (the loader's error alert), whichever is reached first.
- The data fork is an a.out OMAGIC (0x107) file. loader_check.py decodes it from that format alone
  and compares: image (132940 bytes) byte for byte, allocation size and alignment, header, and the
  File Manager read sequence. Seven corruptions are each detected: a changed image byte, a dropped
  text and a dropped data relocation, a misplaced relocation (decoded, and the loader run on that fork
  agreeing with its decode), an external record (the loader's error alert), a truncated fork (eofErr
  read, error alert). The protection was not run (the stop comes first); no synthetic inputs yet.
- Leyte's loader ported: godot/original/leyte/port/ID08.gd (LeyteStrictID08), written from Leyte's
  bytes: load_overlay_from_data_fork (FUN_00000006, whole function, ExitToShell falling through as the
  bytes do), alloc_and_read_resource_payload (0x350) and sysbeep_and_exit_to_shell (0x340).
  tests/leyte/port/test_leyte_loader.gd runs it on the real data fork with stand-ins serving the fork
  as the harness does and matches the emulator run (fixtures/loader_image.json,
  tools/leyte/gen_loader_fixture.py --check in QA): header, base 0x300000, heap blocks, all 4281
  File Manager calls, image (132940 bytes) and payload (148366 bytes) SHA-256; an external record
  takes the error alert (A5 - 0xe2c) and drops the image. Verified up to JT $51a only: the tail
  (FSClose, JT $5ba, the resource file, the protection, the volume check) is translated but unverified.
  Not yet in godot/original/mappings/functions.json (that registry is 1.0.3's; Leyte's has no entry
  type for hand-written host ports yet).
- Validation at the end of the cycle: QA PASS (leyte-loader, the fixture check); Godot suite below.
- Next: the protection with synthetic inputs (JT $592.. on the emulator after an image, with an
  isolated synthetic DATA 128 pair), then JT $51a (CODE 2 +0x245a) and CODE 2 toward startup.

Cycle 2 of job 3d2e0c15:

- Leyte CODE 2, first pass (tools/leyte/gen_host_ports.py mode "partial"): each of 1.0.3's 126
  ported ID02 functions checked alone as "remap" checks a segment (same instructions in Leyte's
  CODE 2, A5 operands only differing, each through the A5 map, jump-table slots reaching the same
  routine); a global the map does not reach paired differently by two functions refuses both; a
  verified function reaching a global the port does not write as a constant is refused; repeated
  until stable. Result: godot/original/leyte/port/ID02.gd (LeyteStrictID02) with 84 functions
  verified by instruction and 42 refused (body replaced by an assert naming it), each with the check
  that refused it in host_ports.json "unverified": 17 not found in Leyte (initialize_application,
  main, reset_overlay_state, do_menu_command, the display set-up, ...), 15 reaching globals written
  through computed addresses, 3 conflicting pairings, 7 others (a changed immediate in
  blit_image_clipped_to_region, muls #$160 -> #$194; jump-table slots reaching other routines in
  hlock / hunlock / hpurge / hnopurge_a5safe, poll_and_dispatch_event,
  check_device_color_state_changed). The jump table (build_jump_table.py) names 64 of 184 slots
  (21 before). ID02 has no Leyte emulator cases yet: the 84 are proven by instruction comparison
  only, not yet by replay.
- Validation: QA PASS. Godot suite pending a full rerun after the ID02 port.
- Next: Leyte emulator cases for ID02 (the 1.0.3 generators through gen_leyte_host_cases.py,
  limited to the verified functions), then the 42 refused, toward JT $51a (CODE 2 +0x245a) and main.

Cycle 3 of job 3d2e0c15:

- Leyte ID02 replayed on the emulator (tools/leyte/gen_leyte_host_cases.py ID02, tests/leyte/port/
  test_leyte_id02.gd): 1.0.3's generators trapfree, crt, glue, memory, draw, palette, toolbox and
  dialog run unchanged on Leyte's CODE 2; only verified functions' cases are built and kept (the
  generator's build() filtered; a case whose Leyte run stops is recorded in the fixture's
  "not_run", none now); each generator's own A5 is used (crt puts it at 0x9000), and a table a
  generator writes past 0x40 bytes is declared with its extent (EXTENTS: crt's 256-byte ctype table).
  1447 cases, 82 of the verified functions covered (uncovered: the sound VBL handlers,
  init_sound_system, ensure_free_memory_and_init_sound, call_overlay_session_hooks,
  show_resource_file_error_box). The replay found two real gaps, now closed in
  gen_host_ports.py's "partial" mode: (1) a verified function's port may call refused functions or
  reach globals its own instructions do not (shared helpers): each function's port call closure is
  followed, a refused callee or an A5 global (-0x200 or below) neither proof nor A5 map pairs
  refuses it; (2) the port writes globals as "a5 - 0x149e" (and through _current_a5() / world),
  which the literal rewrite read as positive numbers, leaving them at 1.0.3's offsets behind the
  "derived" fallback: normalised to "+ -0x", now rewritten, the fallback unused for ID02. ID05 / ID06
  / ID07 are byte-identical to before (none uses that form). Result: 89 verified, 37 refused (17 not
  found in Leyte, 10 reaching refused code or unpaired globals, 7 differing slots / immediate, 3
  conflicting pairings); the jump table names 69 of 184 slots.
- Validation: QA PASS; test_leyte_id02 1447/1447; full Godot suite rerun below.
- Next: the 37 refused ID02 functions (startup: initialize_application, main, reset_overlay_state =
  JT $51a's routine, the display set-up) written from Leyte's bytes with emulator cases.

Cycle 4 of job 3d2e0c15:

- Cycle 3's full Godot suite: 110/110.
- correspondence.json misplaces tiny wrappers: it pairs them by shape, not by what their
  jump-table slot reaches. Four 1.0.3 handle wrappers (dispose / hlock / hunlock / hpurge_a5safe)
  all sat at Leyte 0x1d1a, and hnopurge_a5safe was "identical" at 0x1d9a: the same bytes, but
  slot $28a is HNoPurge in 1.0.3 and HLock in Leyte. gen_host_ports.py now looks for a refused
  function among Leyte's routine entries (link a6, or the segment's jump-table targets) not taken
  by a verified function. Exactly one passing every check relocates it (host_ports.json
  "relocated", used by build_jump_table.py and gen_leyte_host_cases.py); none or several leave it
  refused, with the candidates listed. Relocated: hlock 0x1d9a, hunlock 0x1dbe, hpurge 0x1de2,
  hnopurge 0x1e06, and freemem_a5safe 0x1e2a, which the correspondence called not found.
  correspondence.json itself is left as it is: its fix belongs in leyte_correspondence.py.
- The port-closure check's A5 range is now -0x2400..-0x200: ansi_div's -0x8000 is a constant, not a
  global.
- Result: 95 verified, 31 refused; 1465 Leyte cases, all passing, covering 88 of the 95; the jump
  table names 74 of 184 slots. QA PASS.
- Next: design_y_to_screen's conflicting pairing (-0x1370 -> -0x13f0, against -0x13ee in two other
  functions: likely another misplacement); the 16 not found in Leyte (startup) written from
  Leyte's bytes.

Cycle 5 of job 3d2e0c15:

- Cycle 4's full Godot suite: 110/110.
- Another correspondence collision: design_x_to_screen and design_y_to_screen were both placed at Leyte
  0x196c. Conflicting pairings are now refused before the relocation search, so they go through it too,
  and a candidate must agree with the verified functions' own pairings. design_y_to_screen was found at
  0x1996. blit_offscreen_frame_to_window and draw_text_in_columns_a5safe were found again at their own
  offsets: their conflict came from design_y_to_screen's misplacement.
- A reviewed immediate for ID02: the image table's record size, 0x160 in 1.0.3 and 0x194 in Leyte
  (muls.w in blit_image_clipped_to_region). In "partial" mode an immediate is applied only where the
  function holding it is verified (host_ports.json lists only the applied ones). That function is still
  refused, so this one is not applied yet.
- Known over-refusal: the closure check counts a literal between -0x2400 and -0x200 as an A5 global.
  draw_view_frame_outline's -0xc80 / -0x424 are design coordinates (design_x_to_screen arguments),
  which refuses it and the two blit functions. Telling them apart from globals passed through
  parameters (_design_to_screen(x, -0x1372), a5 + slot) needs argument-level analysis; left refusing.
- Result: 98 verified, 28 refused: 16 not found in Leyte (startup and menus), 10 reaching refused code
  or the over-counted values, 2 differing slots (poll_and_dispatch_event, check_device_color_state_changed).
  1481 Leyte cases, all passing, covering 90 of the 98; the jump table names 77 of 184 slots. QA PASS.

Cycle 6 of job 3d2e0c15:

- Cycle 5's full Godot suite: 110/110.
- First CODE 2 function written from Leyte's own bytes: reset_overlay_state (0x245a, JT $51a, where
  the loader hands over). It lives in godot/original/leyte/port/ID02_reconstructed.gd
  (LeyteReconstructedID02, extending the generated partial port, which the generator never
  overwrites). It clears the overlay's bss and the block at A5 - 0x1082 (GetPtrSize, JT $252), enters
  the overlay at base + a_entry with (A5 - 0x1406, -1, TickCount) where 1.0.3 passes two arguments,
  then calls the segment's routines 0x54b8 and 0x2db2 (1.0.3 instead syncs globals and applies
  settings).
- Its cases run Leyte's own code on the emulator (tools/leyte/gen_leyte_id02_reconstructed_cases.py
  --check in QA; tests/leyte/port/test_leyte_id02_reconstructed.gd): 3 synthetic cases pass. A variant
  with 1.0.3's two-argument entry fails all 3, so the test catches that difference.
- The block at A5 - 0x1082 is what the CODE 8 harness found nil: Leyte's main allocates it before
  loading. The next startup step is main / initialize_application, both not in 1.0.3's port.
- Validation: QA PASS; full Godot suite below.
- Next: 0x54b8 and 0x2db2 (what they are: the correspondence of 1.0.3's 0x4d24 sync and
  apply_settings_to_live_options), then main and initialize_application from Leyte's bytes.

Cycle 7 of job 3d2e0c15:

- Cycle 6's full Godot suite: 111/111.
- Two more Leyte CODE 2 functions written from Leyte's bytes into ID02_reconstructed.gd: the two
  routines reset_overlay_state ends with.
  - apply_display_palettes (0x54b8, 1.0.3's unexported 0x4d24): the segment's 0x4daa
    (select_palette_for_depth) and 0x4e32 (rebuild_all_palette_index_maps), then the overlay hook at
    A5 - 0x10e4.
  - apply_settings_to_live_options (0x2db2; the correspondence says not found): 1.0.3's 0x2a44
    with the settings record at *(A5 - 0x142a) and Leyte's live-option globals. It copies two
    fields 1.0.3 does not (+0x28 -> A5 - 0x134c, +0x26 -> - 0x134a). Its hooks are at A5 - 0x10d4 /
    - 0x1090 / - 0x10d8, the image test uses A5 - 0x1048 and the depth A5 - 0x13f8. When a hook ran,
    it calls the segment's 0x5544 (1.0.3: redraw_main_window).
- The reconstructed-cases generator gained them (11 synthetic cases: unchanged, each change kind,
  depth 4 and 8, no overlay image, palettes). The routine under test gets no local stand-in at its own
  entry. All 11 pass; dropping the +0x28 copy fails 7.
- reset_overlay_state still reaches them by offset (_local 0x54b8 / 0x2db2, stand-ins in the tests).
  The runtime will link those offsets to these methods.
- Validation: QA PASS; full Godot suite below.
- Next: Leyte's main and initialize_application (the start-up that allocates A5 - 0x1082 and calls
  the CODE 8 loader), then 0x5544.

Cycle 8 of job 3d2e0c15:

- Cycle 7's full Godot suite: 111/111.
- Leyte's main found and written from its bytes (ID02_reconstructed.gd main, 0x37d2). CODE 1 is
  byte-identical, but it calls main through the slot its header long names (D1, CODE 1 +4): $4b2 in
  1.0.3, $50a in Leyte, which reaches CODE 2 +0x37d2 (Leyte's slot $4b2 is a palette lookup).
  1.0.3's main shape with Leyte's offsets: A5 - 0x142c = -1, initialize_application (0x32d8),
  UnloadSeg A5 + 0xda, MaxMem (JT $22a, grow at A6 - 6), 0x1e58, A5 - 0x1542 = 1, the overlay
  loader (JT $1fa = CODE 8 FUN_00000006, whose word result is kept at A6 - 2), UnloadSeg A5 + 0x1f2 /
  + 0x5ba, install_menus (0x3838), start_new_mission (0x3d38) when the loader returned nonzero, A5 -
  0x1070 is set and an overlay image exists (A5 - 0x1048, a test 1.0.3 does not make), then
  poll_and_dispatch_event (0x370c) for ever.
- 3 emulator cases (loader 1 with image, loader 0, no image). The emulator's endless loop is stopped at
  its second poll; the port runs with event_loop_passes 1 (port_vars). All 14 reconstructed cases
  pass. Dropping the image test fails the no-image case; the call counts confirm each branch.
- The start-up chain from Leyte's bytes is now main -> (initialize_application) -> loader (ported,
  byte-exact) -> reset_overlay_state -> apply_display_palettes / apply_settings_to_live_options.
  Still missing: initialize_application (0x32d8), 0x1e58, start_new_mission, poll_and_dispatch_event's
  Leyte form, 0x5544.
- Validation: QA PASS; full Godot suite below.
- Next: initialize_application (0x32d8: it allocates A5 - 0x1082), then link the reconstructed
  methods into the Original runtime's Leyte profile for a first start attempt.

Cycle 9 of job 3d2e0c15:

- Cycle 8's full Godot suite: 111/111.
- Leyte's initialize_application written from its bytes (ID02_reconstructed.gd, 0x32d8). Its
  differences from 1.0.3's 0x2bbc:
  - CurResFile is not saved here; InitGraf is on A5 - 0x168a.
  - A new call follows the Toolbox set-up (0x2f42).
  - The SysEnvirons results go to A5 - 0x1406 / - 0x1405.
  - The colour table header comes from NewPtrClear (JT $242) into A5 - 0x1530 / - 0x1394.
  - The settings resource is 'HEL1' 227 (1.0.3: 127), 0x12e bytes at A5 - 0x142a. When it is made,
    a new check (0x3082) decides the defaults (+0x10 = 2, +0xe = 1, +0x24 = 2, +0x2a = 1);
    +0x28 = 6 and +0x26 = 0 are always set, and the name A5 - 0xc72 goes to +0x2e.
  - The table resource is 'HEL1' 228 (0x500 bytes, A5 - 0x1426), filled by 0x31d8.
  - It ends with 0x2b48 and SetPort(A5 - 0x1548).
  The settings record main and apply_settings_to_live_options read at A5 - 0x142a is made here.
- 4 emulator cases: everything present on colour / 68020; no colour / 68040 with the settings made
  with defaults; colour / 68030 made without defaults with 228 present; no MENU 2 with a failed system
  check. The SysEnvirons record is written by its stand-in, and the generator's memory grew so the
  stand-in logs stay clear of the data. All 18 reconstructed cases pass; the call counts and the
  bytes written confirm each branch.
- Validation: QA PASS; full Godot suite below.
- Next: the remaining start-up locals (0x2f42, 0x1e58, 0x2b48 configure_display, 0x24c8
  scan_display_devices, 0x3082, 0x31d8), then link the reconstructed methods into the Original
  runtime's Leyte profile for a first start attempt.

Cycle 10 of job 3d2e0c15:

- Cycle 9's full Godot suite: 111/111.
- Leyte's 0x1e58 (main's set-up; 1.0.3's 0x1b42 install_overlay_service_table, which the 1.0.3
  runtime implements in unexported.gd) written from its bytes as install_overlay_service_table in
  ID02_reconstructed.gd. It stores 35 jump-table slot addresses into the overlay's hook globals
  (1.0.3: 34), then allocates the block A5 - 0x1082 with NewPtrClear: 1000000 bytes when FreeMem
  (JT $222, $A01C) is above 0x1c2000, else 600000 (1.0.3: 0x4e390 / 0x35cf0 against 1 MB). This is the
  block the CODE 8 harness found nil and reset_overlay_state clears.
- 4 emulator cases (FreeMem 0x400000, 0x1c2000, 0x1c2001, 0x80000) confirm the threshold exactly and
  all 35 table writes. All 22 reconstructed cases pass.
- Read, not yet written: 0x3082 opens the resource file named at A5 - 0xc94 and, when its 'HEL1' 127
  exists, copies fields from it into Leyte's settings (Leyte reads the Hellcats settings record).
  0x2f42 is a File Manager routine (PBGetCatInfo / volume calls, a 'HELL' file type) that ends in an
  error alert and ExitToShell on failure.
- Validation: QA PASS; full Godot suite below.
- Next: 0x3082, 0x31d8, 0x2f42, then scan_display_devices / configure_display (0x24c8 / 0x2b48),
  then the runtime link for a first start attempt.

Cycle 11 of job 3d2e0c15:

- Cycle 10's full Godot suite: 111/111.
- Leyte's 0x3082 written from its bytes (ID02_reconstructed.gd import_settings_from_resource_file;
  no 1.0.3 counterpart). initialize_application calls it when it makes 'HEL1' 227.
  - It opens the resource file named at A5 - 0xc94 (OpenResFile); -1 returns 0, and the caller then
    sets its defaults.
  - When that file's 'HEL1' 127 (1.0.3's settings record) exists, it loads it and copies the words at
    +2 / 4 / 6 / 8 / 0xe / 0x10 / 0x12 / 0x14 / 0x16 / 0x18 / 0x1a / 0x22 into Leyte's settings,
    and its +0x26 into +0x2a.
  - Its 'HEL1' 128 is detached into A5 - 0xd40; the file is closed and A5 - 0x1042 used again;
    it returns 1.
  So Leyte carries a Hellcats player's settings over. Its synthetic cases use a stand-in name only;
  no real player file is read.
- 4 emulator cases (no file, both resources, no 127, neither) compare the memory and the returned
  word. All 26 reconstructed cases pass.
- Validation: QA PASS; full Godot suite below.
- Next: 0x31d8 ('HEL1' 228's filler), 0x2f42 (the File Manager check), then the display scan and
  configuration (0x24c8 / 0x2b48), then the runtime link.

Cycle 12 of job 3d2e0c15:

- Cycle 11's full Godot suite: 111/111.
- Leyte's 0x31d8 written from its bytes (ID02_reconstructed.gd convert_imported_pilots; no 1.0.3
  counterpart). It fills a new 'HEL1' 228 from what 0x3082 imported.
  - Only with an imported 'HEL1' 128 at A5 - 0xd40: otherwise it does nothing.
  - Its bytes are moved into 'HEL1' 228 (GetHandleSize $A025, BlockMove $A02E).
  - Each of the 20 records of 64 bytes is converted: +0x26 = 0 (LSL.W #16 through a register), and
    the 12 words from +0x28 are rearranged into Leyte's order: +0x28 = w0, +0x2a = 0, +0x2c = w1,
    +0x2e = w2, +0x30 = w5, +0x32 = w6, +0x34 = w7, +0x36 = 0, +0x38 = w3, +0x3a = w9.
  - The imported handle is disposed ($A023).
  So Leyte carries Hellcats' pilot roster over; synthetic records only.
- 2 emulator cases (nothing imported; 20 records converted, 373 bytes changed). All 28 reconstructed
  cases pass.
- Validation: QA PASS; full Godot suite below.
- Next: 0x2f42 (the File Manager check before SysEnvirons), then the display scan and configuration
  (0x24c8 / 0x2b48), then the runtime link for a first start attempt.

Cycle 13 of job 3d2e0c15:

- Cycle 12's full Godot suite: 111/111.
- Leyte's 0x2f42 written from its bytes (ID02_reconstructed.gd open_preferences_file; no 1.0.3
  counterpart). It opens Leyte's preferences file:
  - CurResFile into A5 - 0x1044; GetVol and SysEnvirons(1) into its frame.
  - PBGetWDInfo on the System folder's volume ($A260 selector 7); failure means the alert
    (A5 - 0xda0) and ExitToShell.
  - The folder named at A5 - 0xca0 looked up (PBGetCatInfo, selector 9). Found: a working
    directory opened on it (PBOpenWD, selector 1, procID 'HELL') becomes A5 - 0x1040; otherwise the
    System folder's volume does. SetVol there.
  - The file named at A5 - 0xcb6 opened, created while it does not open (CreateResFile; a ResError
    means the alert and ExitToShell); CurResFile into A5 - 0x1042 (the file 0x3082 uses again and
    the CODE 8 loader's tail closes back to); the first volume restored.
- 3 emulator cases (folder and file present; no folder, file created; WD info failing and one failed
  creation). Its parameter blocks are in its frame: their stand-ins write the outputs (the list form
  of "out"), and the cases compare the frame addresses logged (a7). All 31 reconstructed cases pass;
  the call counts and A5 - 0x1040 / - 0x1042 / - 0x1044 confirm each branch.
- Validation: QA PASS; full Godot suite below.
- Next: scan_display_devices (0x24c8) and configure_display_from_main_device (0x2b48), then the
  runtime link (the reconstructed methods behind their offsets) for a first start attempt.

Cycle 14 of job 3d2e0c15:

- Cycle 13's full Godot suite: 111/111.
- Leyte's scan_display_devices written from its bytes (ID02_reconstructed.gd, 0x24c8; it overrides
  the generated port's refusal). It differs from 1.0.3's 0x1060 in substance:
  - Every device that matches the main device (top, bottom, width, pixel size) fills its record:
    main A5 - 0x13d6, left - 0x13c0, right - 0x13aa. 1.0.3 fills only the main device's record (its
    port keeps that quirk).
  - A new multi-screen flag, A5 - 0x1074: 1 when the main record and a side record are both filled
    (1.0.3 clears A5 - 0x1032 there).
  - The main device is kept at A5 - 0x1078, and the 0x200-byte index map comes from NewPtrClear
    (JT $242).
  - The colour flag uses the same MMU32Bit glue (JT $31a, the byte at 0xcb2) as 1.0.3's $2da.
- 5 emulator cases (one screen; main, left and right; non-matching sides; main second in the list
  with MMU32Bit set; existing index maps). All 36 reconstructed cases pass; the records and the
  flag confirm each branch.
- Validation: QA PASS; full Godot suite below.
- Next: configure_display_from_main_device (0x2b48), then the runtime link for a first start attempt.

Cycle 15 of job 3d2e0c15:

- Cycle 14's full Godot suite: 111/111.
- Leyte's configure_display_from_main_device written from its bytes (ID02_reconstructed.gd, 0x2b48,
  overriding the refusal). It is 1.0.3's 0x27e4 with Leyte's globals plus one step: an unsupported
  depth of 16 bits or more quits (ExitToShell after the alert; the bytes fall through as the other
  ExitToShells do).
  - Main device A5 - 0x1078, window - 0x1548, hidden flag - 0xd42, alert - 0xdb4, running flag
    - 0x1376, display-ok flag - 0x1070.
  - Screen globals: - 0x142c, - 0x1404, - 0x13f8, - 0x13fa, - 0x13fc, - 0x13f6, - 0x1352.
  - MMU byte - 0x103e, flag - 0x1390.
  - The device bases go down a menu bar through MULU.L (4c01 0000: the low 32 bits equal 1.0.3's
    signed product). Then - 0x106a is cleared and 0x2676 lays out the window.
  It calls scan_display_devices as a method, and its cases run Leyte's own 0x24c8 under the same
  stand-ins (REAL in the generator), so the scan is checked a second time from here.
- 4 emulator cases: 8 bits on one screen; 16 then 8 after the rescan (hide, alert, quit, show); 2
  bits staying unsupported (the height wraps below zero, as in the original); two screens with
  MMU32Bit set. All 40 reconstructed cases pass.
- Every routine on the start-up path down to the event loop now exists from Leyte's bytes or as a
  verified port, except those reached only by offset: layout_game_window_and_view (0x2676),
  install_menus (verified, 0x3838), start_new_mission, poll_and_dispatch_event, and 0x5544.
- Validation: QA PASS; full Godot suite below.
- Next: the runtime link: a Leyte profile in the Original runtime that loads Leyte's CODE through
  these ports (segment_loader's Leyte ports, the reconstructed methods behind their offsets) and runs
  main for a first start attempt, recording where it stops.

Cycle 16 of job 3d2e0c15:

- Cycle 15's full Godot suite: 111/111.
- Runtime link, measured first. A Leyte title profile in segment_loader.gd (which today wires 1.0.3's
  ports, jump table and local methods, with a 1.0.4 branch) needs:
  - host ports for every CODE segment (CODE 3 and 4 had none);
  - Leyte's jump table naming its methods (CODE 8's port and the reconstructed CODE 2 functions are
    not named yet);
  - the ID09 Toolbox glue: 35 of Leyte's 41 glue slots match 1.0.3's glue byte for byte, so the
    runtime's stand-ins can be keyed to Leyte's offsets. 6 are new: GetHandleSize $272, BlockMove
    $2b2, SetVol $2e2, PBOpenWD $2fa, PBGetWDInfo $302, PBGetCatInfo $30a. The runtime File
    Manager has no working-directory or catalog calls yet.
- CODE 3 and CODE 4 ported for Leyte (gen_host_ports.py "partial" on ID03 / ID04; ID02 / ID05 / ID06
  / ID07 byte-identical to before):
  - ID03: 38 of 45 verified (7 refused: 4 reaching refused code, 2 not found, 1 other).
  - ID04: 43 of 59 verified, 3 relocated by proof (draw_targets_destroyed_list 0x1fb8,
    refuel_dialog_filter_proc 0xfd4, scenario_selection_filter_proc 0xc2a), 16 refused.
- Replayed on Leyte's own code (gen_leyte_host_cases.py ID03 ID04; tests/leyte/port/test_leyte_id03.gd
  and test_leyte_id04.gd):
  - ID03: 133 cases covering 36 of the 38 verified functions, all passing.
  - ID04: 110 cases covering 42 of 43, all passing.
  - 11 cases whose Leyte run stopped are listed in their fixtures' "not_run".
  - ID03's generators read a directory (CODE_DIR, now Leyte's CODE); CODE 2's own abs_long / memset
    run behind two slots.
  - A stop is charged to the generator running at the time, since ID03's all call the helper's run;
    so no placeholder result is ever kept.
  - Left out: gen_id04_summary_cases (its cases carry no method); ID03 prepare / remap (none of their
    functions is verified).
- The jump table names 91 of 184 slots (77 before).
- Validation: QA PASS; full Godot suite below.
- Next: name CODE 8's port and the reconstructed CODE 2 functions in Leyte's jump table; key the 35
  glue stand-ins to Leyte's offsets and write the 6 new File Manager / Memory Manager calls; then the
  segment_loader Leyte profile (a branch like 1.0.4's, 1.0.3 untouched) for a first start attempt.

Cycle 17 of job 3d2e0c15:

- Cycle 16's full Godot suite: 113/113.
- Leyte's runtime tables, the two data files a Leyte profile needs, both checked in QA:
  - tools/leyte/reconstructed.py: the routines written from Leyte's bytes, by segment and Leyte
    offset (11 in CODE 2, 2 in CODE 8). check() requires each to be defined in its port file and to
    have emulator cases (CODE 8's payload helper is covered inside the loader's run).
  - Jump table (build_jump_table.py): names them too, and every segment-2 slot now points at
    ID02_reconstructed.gd, which extends the generated port. 95 of 184 slots are named, among them
    every slot the start-up path calls: main $50a, reset_overlay_state $51a, the CODE 8 loader $1fa,
    copy_pascal_string $4e2, the alert $4da, CODE 4's check_system_requirements $c2 and
    show_scenery_open_error_alert $17a, CODE 7's init_string_pointer_groups $5d2.
  - Segment methods (tools/leyte/build_segment_methods.py -> godot/original/leyte/data/
    segment_methods.json): the routines ports call by offset, keyed by Leyte's offsets only (250
    across 8 segments: verified functions at their Leyte offsets, and the reconstructed ones).
    1.0.3's offsets cannot share it: they collide (CODE 2 0xad0 is a different function in each), and
    the build refuses any offset claimed twice. The only `_local` call verified generated code
    reaches, run_protection_dialog's 0x3ba, is the same JSR to the same offset in Leyte, an unexported
    routine the runtime supplies itself. Refused functions are left out, so a call reaching one stays
    unresolved (the runtime's note_missing) instead of hitting a refusal.
- Validation: QA PASS; full Godot suite below.
- Next: key the 35 matching ID09 glue stand-ins to Leyte's offsets and write the 6 new calls
  (GetHandleSize, BlockMove, SetVol, PBOpenWD, PBGetWDInfo, PBGetCatInfo), then the segment_loader
  Leyte branch for a first start attempt.

Cycle 18 of job 3d2e0c15:

- Cycle 17's full Godot suite: 113/113.
- Leyte's CODE 9 glue mapped onto 1.0.3's (tools/leyte/build_glue_slots.py -> godot/original/leyte/
  data/glue_slots.json, checked in QA).
  - 35 of Leyte's 41 glue slots are byte-identical to exactly one 1.0.3 slot. The Leyte profile can
    re-key the runtime's existing stand-ins (which the Toolbox managers install by 1.0.3 offset)
    through this map, without changing them.
  - The scan window is 0x80 bytes, not gen_host_ports.py's 64: FSRead / FSWrite's shared entry runs
    72 bytes, and with the shorter window FSRead ($2d2, the CODE 8 loader's) looked new.
  - 6 new slots, each with its trap: GetHandleSize $272 ($A025), BlockMove $2b2 ($A02E), SetVol
    $2e2 ($A015), PBOpenWD $2fa / PBGetWDInfo $302 / PBGetCatInfo $30a ($A260 selectors 1 / 7 / 9).
- Two comment corrections in ID02_reconstructed.gd found by the map (code and tests unchanged):
  - JT $232 is MaxApplZone (1.0.3's $222, which 1.0.3's port names JT_MORE_MASTERS; MoreMasters
    is $2aa / $292).
  - JT $262 is NewHandleClear ($A322), not NewHandle.
- Design point before the 6 new calls. On a first start Leyte opens or creates its preferences file
  (0x2f42: the System folder's working directory, a Preferences folder, CreateResFile), and 0x3082
  reads a Hellcats file. In the runtime these must go to a sandboxed, title-specific location
  (never a real player's files or Hellcats' saves). The runtime's File Manager is read-only today and
  has no directories.
- Validation: QA PASS.
- Next: GetHandleSize, BlockMove and SetVol in the Memory and File Managers; the HFS calls on a
  sandboxed Leyte volume (no folder: PBGetCatInfo fnfErr, so the preferences file goes in the System
  folder of that volume); then the segment_loader Leyte branch.

Cycle 19 of job 3d2e0c15:

- Cycle 18's full Godot suite: 113/113.
- Leyte's glue on the runtime's own managers (godot/original/leyte/machine/leyte_glue.gd, no second
  Macintosh). rekey() gives Leyte's offsets the stand-ins the managers install by 1.0.3's offsets
  (through glue_slots.json "same"), and answers three of the new slots:
  - GetHandleSize $272 (memory_manager.handle_size);
  - BlockMove $2b2 (new memory_manager.block_move: an overlap-safe copy, as BlockMove is);
  - SetVol $2e2 (new file_manager.set_vol: the one volume or a working directory it knows, else
    nsvErr -35; 1.0.3 never calls it).
  unanswered() reports the rest (PBOpenWD, PBGetWDInfo, PBGetCatInfo), which the segment loader
  will report missing, never fake. 1.0.3's paths are unchanged: the two manager methods are new, and
  nothing of 1.0.3's calls them.
- tests/leyte/test_leyte_glue.gd: every paired slot gets exactly the 1.0.3 stand-in (25 from the Memory
  and File Managers; the others come from the other managers), NewPtrClear / GetPtrSize and
  GetHandleSize through Leyte's offsets, BlockMove overlapping up and down, SetVol both ways, and the
  three unanswered HFS slots. All pass.
- Validation: QA PASS; full Godot suite below (it checks 1.0.3 is unchanged).
- Next: the three HFS calls on a sandboxed Leyte volume, then the segment_loader Leyte branch.

Cycle 20 of job 3d2e0c15, and close (precise blocker):

- Cycle 19's full Godot suite: 114/114.
- Blocker for the Leyte start-up link: player data, a decision to make before code. The runtime's
  Resource Manager has one resource map, the application's with the player's saved resources over it
  (user://resources, resource_manager.gd saved_root), and no OpenResFile / CreateResFile /
  CloseResFile. On a first start Leyte:
  (1) opens or creates its own preferences file (0x2f42: the System folder, a Preferences folder,
      CreateResFile) and keeps its settings and pilots there;
  (2) opens a Hellcats resource file (0x3082, the name at A5 - 0xc94) and imports that player's
      settings and pilot roster (0x31d8);
  (3) in the CODE 8 loader's tail, opens one more resource file (A5 - 0x1768).
  Implementing these means deciding where Leyte's saved data lives (a title-specific root such as
  user://leyte-gulf, never Hellcats' user://resources) and whether the runtime's Leyte may read the
  player's Hellcats saves for the import (faithful), or must see no such file (the import then sets
  defaults). Both touch player saves, which the approval rules reserve for the user.
- Validation at close: Godot 114/114; Python QA PASS; protected checksums 32 OK; Leyte resources
  check PASS (238 files); no original evidence changed. 14 GiB free.

Job f19bd982 (approved Leyte player-data policy: Leyte's own storage under user://leyte-gulf/;
Hellcats' saves read-only for the faithful import; tests on synthetic data only).

Cycle 1 of job f19bd982:

- The names Leyte asks for, from DATA 0 (the A5 world's initial data, Pascal strings laid out at the
  offsets the code uses):
  - A5 - 0xcb6 "Hellcats Preferences": the preferences file 0x2f42 opens or creates.
  - - 0xca0 "Preferences": the folder it looks for in the System Folder.
  - - 0xc94 "Hellcats": the file 0x3082 imports from, the Hellcats application, whose fork is where
    1.0.3 keeps its saves.
  - - 0xc8a "Hellcats Saved Settings" and - 0xc66 "Player and Score Data": the names of the new
    'HEL1' 227 / 228.
  - - 0xc72 "Leyte Gulf": copied into the settings at +0x2e.
  Leyte's own fork ships only 'HEL1' 0 (an owner resource), so 227 / 228 are really created on a
  first start. Hellcats' shipped fork has 'HEL1' 127 / 128.
- Sandboxed volume (godot/original/leyte/machine/leyte_volume.gd), under save_root
  (user://leyte-gulf):
  - The root (dirID 2) holds "Leyte Gulf" and "Hellcats" (both read-only, served by the Resource
    Manager); "System Folder" (3) holds "Preferences" (4), as on System 7.
  - Working directories are HFS's: a (directory, procID) pair keeps one reference; the System
    Folder's is sysVRefNum.
  - PBGetWDInfo, PBGetCatInfo and PBOpenWD on Inside Macintosh's parameter blocks (by name, by index,
    the directory itself; ioResult). nsvErr / dirNFErr / fnfErr where the Toolbox gives them; no
    unconditional success.
- Multi-file Resource Manager (godot/original/leyte/machine/leyte_resources.gd, a subclass:
  resource_manager.gd and 1.0.3 unchanged):
  - The files: the application (Leyte's fork, its writes under save_root/application); "Hellcats"
    (its saved resources, Hellcats' user://resources, over 1.0.3's shipped fork, read-only); files on
    the volume (a folder per file).
  - OpenResFile (current; the existing reference when open; fnfErr), CreateResFile (never over an
    existing file: dupFNErr), GetResource down the chain from the current file, Get1Resource, Add /
    Changed / Write / Rmve / DetachResource, UpdateResFile, CloseResFile (writes, then drops the file's
    handles; a detached one stays the program's), UseResFile, HomeResFile, CurResFile.
  - The Hellcats file refuses every write: addResFailed, rmvResFailed, wrPermErr.
- tests/leyte/test_leyte_files.gd, on user://leyte-files-test and a synthetic Hellcats save only
  (statics restored, the directory removed): 38 checks, all passing.
  - The HFS calls and their errors.
  - A first start: OpenResFile fnfErr, CreateResFile, a second CreateResFile dupFNErr, the settings
    added.
  - The import: 'HEL1' 127 from the save, 128 from the shipped fork; writes refused; a detached roster
    surviving CloseResFile.
  - 227 / 228 written in Leyte's file; a restart reading them back; an update persisting.
  - No Hellcats on the volume: fnfErr.
  - The Hellcats save byte-identical afterwards, nothing new beside it.
- Validation: QA PASS; full Godot suite below.
- Next: the segment_loader Leyte branch, wiring this volume, its Resource Manager, the glue re-keying
  (leyte_glue.gd, now with the HFS calls), sysVRefNum and the Leyte tables, then a first start.

Cycle 2 of job f19bd982:

- Cycle 1's full Godot suite: 115/115. Its temporary folder (empty) is now removed by the test. The
  player's Hellcats saves were confirmed untouched (no file newer than the run).
- The Leyte title profile (godot/original/leyte/machine/leyte_profile.gd: apply() / clear()), on the one
  machine. Hooks, each empty / null for Hellcats so 1.0.3 / 1.0.4 run as before:
  - segment_loader.gd: static profile (Leyte's ports for CODE 2..8, jump table, segment methods);
  - mac_machine.gd: static profile (A5 world 0x2322 below A5; setup / link around the segments; no
    1.0.3 overlay loader or unexported routines, whose slots are 1.0.3's);
  - toolbox.gd: sys_vref (SysEnvirons' sysVRefNum: the System Folder's working directory for Leyte);
  - file_manager.gd: title_files (the application's data fork by name, read-only).
  The profile wires the sandboxed volume, LeyteResources (Toolbox.overrides), the glue re-keying with the
  volume's HFS calls, CurApName "Leyte Gulf" (0x910), and the data fork. The fork is mirrored verbatim
  to godot/assets/leyte-gulf-1.0/data_fork.bin by tools/leyte/extract_overlay.py, whose --check (QA)
  compares both copies with the disk's SHA-256.
- First start (tests/leyte/leyte_start_probe.gd: a temporary save root, wiped each run, and an empty
  synthetic Hellcats save; main for one event-loop pass):
  - main -> initialize_application -> open_preferences_file: "Hellcats Preferences" created in the
    sandbox's System Folder:Preferences.
  - The settings import from Hellcats' shipped 'HEL1' 127: the 12 words equal, +0x2a = its +0x26,
    +0x28 = 6, +0x26 = 0, "Leyte Gulf" at +0x2e. 'HEL1' 228 is 0x500 bytes; the resource names are
    the original's.
  - main -> the CODE 8 loader (JT $1fa): FSOpen of the data fork (315530 bytes), read and relocated,
    then reset_overlay_state.
  Divergences recorded, in order:
  1. configure_display_from_main_device's local 0x2676 (layout_game_window_and_view, Leyte's, not
     written yet).
  2. The overlay entry (relocated image + a_entry, 0x2079ce here) has no routine: Leyte's overlay is
     not ported. Its hooks stay 0, so apply_display_palettes' hook and the settings hooks reach code
     address 0.
  3. apply_display_palettes' 0x4e32 (rebuild_all_palette_index_maps, refused) and 0x5544.
  4. The copy-protection path is reached: CODE 5 slot $5ba (+0x4) and $58a (+0x8b2) and local 0x3ba
     (build_protection_code_strings). In 1.0.3 these are runtime-written routines (unexported.gd);
     Leyte needs its own. The run stops in the protection dialog's loop.
- Validation: QA PASS; full Godot suite below.
- Next: Leyte's three CODE 5 runtime routines (protection, synthetic inputs), 0x2676 / 0x5544 /
  0x4e32. Then the main dependency: Leyte's overlay port (the overlay entry), which the first game
  screen needs.

Cycle 3 of job f19bd982:

- Leyte's runtime-written CODE 5 routines (godot/original/leyte/machine/leyte_runtime.gd, registered by
  the profile's link):
  - CODE 5 +0x4 (slot $5ba): RTS, as in 1.0.3.
  - +0x8b2 (slot $58a, arm_restore_gate): ADDQ.W #1, -$bc(A5).
  - Local 0x3ba (build_protection_code_strings): 1.0.3's unexported.gd routine with Leyte's operands,
    read from Leyte's bytes (GetVol $2da, P2C $33a, strcmp $562, the copy $572, A5 - 0xda4 for
    1.0.3's - 0xd66). It is not yet checked on the emulator: 1.0.3's are, through
    tools/runtime/gen_runtime_cases.py, and a Leyte counterpart is still to write.
- CODE 9 ported for Leyte (gen_host_ports.py "partial"): fsopen_glue, swapmmumode_glue and
  length_prefixed_bytes_to_c_string_in_place verified; sysenvirons_glue_fallback is not in Leyte.
  The profile now includes CODE 9. The jump table names 98 slots; segment methods updated.
- First start, now with no runtime gap (machine.missing empty). The start probe traces the copy
  protection; Leyte's loader tail runs it in order:
  - poison_overlay_bytes $592 (base, text 128856); init_protection_table $59a (A5 - 0x1d4a, 0x38,
    TickCount); read_DATA128_pair $5c2;
  - encode $57a / record_code_index_equals $5a2 = 1, so arm_restore_gate $58a;
  - run_protection_dialog $5ca, which opens no dialog: the stored pair matches;
  - restore_poisoned_overlay_bytes $582; the check again (1), so write_DATA128_pair $5b2.
  The DATA 128 pair is the one Leyte's shipped application fork holds (program data, no player's
  registration); its write goes to the probe's sandbox. The dialog's own branch is covered by the ID05
  dialog cases on Leyte's bytes (synthetic responses).
- Where the start stops now, all of it Leyte's own code not written yet:
  - layout_game_window_and_view 0x2676 (from configure_display_from_main_device);
  - the overlay's entry (Leyte's overlay is not ported: the dependency for any game screen);
  - 0x4e32 (rebuild_all_palette_index_maps, refused) and 0x5544;
  - start_new_mission 0x3d38 and poll_and_dispatch_event 0x370c (main's loop).
- Validation: QA PASS; the full Godot suite for cycle 2 is still running.
- Next: a runtime run of the protection dialog with a synthetic, mismatching DATA 128 (its synthetic
  answer through the event queue); 0x2676, 0x5544, poll_and_dispatch_event 0x370c; then the overlay.

Cycle 4 of job f19bd982:

- Cycle 2's full Godot suite: 115/115, with the profile hooks in place: 1.0.3 / 1.0.4 unchanged.
- layout_game_window_and_view written from Leyte's bytes (ID02_reconstructed.gd, 0x2676). It is 1.0.3's
  0x2334 with Leyte's globals:
  - settings *(A5 - 0x142a), two-screen flag - 0x1074, screen width / height / depth - 0x13fc /
    - 0x13fa / - 0x13f8, menu-bar flag - 0x106a, device records - 0x13ec + 0x16 i, view size
    - 0x13ee / - 0x13f0;
  - window - 0x1548, palettes - 0x152c / - 0x1528, offscreen port - 0x107c, resize hook - 0x10e4
    with - 0x1070, 1-bit base - 0x137e.
  The same arithmetic, decoded from the 68020 instructions capstone leaves undecoded: MULS.L
  #$1e0 / DIVS.L #$280, MULU.L #$8d4 / DIVS.L #$12c0, MULU.L for the bases (add, then sub: D1A8 /
  91A8). One step 1.0.3 does not make: in a two-screen session the window's rect grows over the side
  screens before the window is made or moved. Its local calls reach verified functions at Leyte's
  offsets: configure_palette_entries 0x4eb6, select_palette_for_depth 0x4daa, close_and_dispose_port
  0xdb2, fatal_memory_error_exit 0x1cf8.
- 6 emulator cases: the first window at 8 bits; a visible window at 4 bits, half width, with an
  offscreen port and the resize hook; 1 bit with the buffer failing; two screens with left and right
  devices; 512 x 342 skipping the menu bar; the menu bar already skipped, shrinking to fit. All 46
  reconstructed cases pass; the call counts and the view sizes confirm each branch.
- Generator fixes:
  - a case's C arguments are pushed as words on the emulator stack ("stack"), as Leyte's caller
    pushes them; before, the original ran with a zero rect;
  - CODE 1 is loaded behind slot $3a (the dense switch helper, CODE 1 +0xbe).
- Leyte's release_render_buffers (CODE 6 +0x858, slot $1da; leyte_runtime.gd), from its bytes:
  pattern tables A5 - 0x1f60 / - 0x1f64, edge pool - 0x1d5c, free_row_table, raster - 0x1d54.
- First start now: the window laid out and made, then the loader and the protection, with no runtime
  gap. Still unwritten: the overlay entry (Leyte's overlay), 0x4e32, 0x5544, start_new_mission
  0x3d38, poll_and_dispatch_event 0x370c.
- Validation: QA PASS; full Godot suite below.
- Next: poll_and_dispatch_event (0x370c, main's loop) and redraw 0x5544, toward the first window's
  update; then the overlay port.

Cycle 5 of job f19bd982:

- A correction to cycles 3 and 4: the scratch disassembler used to compare 1.0.3 with Leyte ignored its
  CODEDIR setting (a failed edit), so every "1.0.3" listing in this job was Leyte's own.
  - Cycle 3's build_protection_code_strings (0x3ba) was then checked against Leyte's operands, read
    directly. Its remaining check is still the emulator one recorded there.
  - Cycle 4's layout_game_window_and_view rests on Leyte's listing and its emulator cases, not on a
    1.0.3 diff.
  Fixed now; the comparison below is a real one.
- poll_and_dispatch_event written from Leyte's bytes (ID02_reconstructed.gd, 0x370c). It is 1.0.3's
  0x2fd4 instruction for instruction (a real side-by-side listing). Only these differ:
  - the local call targets: update_menu_items 0x38b2, refresh_display_if_changed 0x2d60,
    handle_mouse_down 0x3656, do_menu_command 0x3dfc, redraw_main_window 0x5544 (1.0.3 0x4db0),
    handle_activate_event 0x36d8;
  - the idle slot, $aa (CODE 4 +0x482; 1.0.3's $172, report_panel_store_failure, runtime-written);
  - the globals: the window up A5 - 0x1544, the window - 0x1548.
- 7 emulator cases (no event, mouse-down, command key, key without the command bit, an update of the
  game window and of another, activate). GetNextEvent's stand-in writes each event record. All 53
  reconstructed cases pass; the call counts confirm each branch.
- First start: main's loop now runs Leyte's own poll. It reaches update_menu_items 0x38b2, the idle
  routine $aa (to write for Leyte, as 1.0.3's runtime writes $172) and refresh_display_if_changed
  0x2d60.
- Validation: QA PASS; the cycle 4 full suite is still running.
- Next: the idle routine CODE 4 +0x482, update_menu_items, refresh_display_if_changed and
  redraw_main_window 0x5544: the window's first drawing.

Cycle 6 of job f19bd982:

- Cycle 5's full Godot suite: 115/115.
- redraw_main_window written from Leyte's bytes (ID02_reconstructed.gd, 0x5544). It is 1.0.3's 0x4db0
  drawn on every screen of a two-screen session:
  - It needs the window up (A5 - 0x1544), an overlay image (- 0x1048) and a running game (- 0x1376).
    The drawn flag - 0x1368 is set once (its old value to - 0x107e).
  - HideCursor; the device colours checked (0x53ac); UnloadSeg A5 + 0x19a; the overlay's redraw
    hook (A5 - 0x10e0).
  - Two screens: for each device record, SetGDevice, the offscreen port closed, the record's view
    base / row bytes / colour flag made the screen's (A5 - 0x1400 / - 0x13f6 / - 0x1390), the overlay's
    session hooks (call_overlay_session_hooks 0x9be, verified) for that screen, 0xab4 on the first;
    then the screen globals synced (0x552a).
  - One screen: 0x552a, the session hooks for screen 1, 0xab4. Then ShowCursor.
  0x552a and 0xab4 are 1.0.3's runtime-written 0x4d96 / 0xa26 at Leyte's offsets: not written yet.
- 6 emulator cases (window not up; no image; one screen, first and later drawing; two screens with
  left, right and an offscreen port; the two-screen option without the devices). All 59 reconstructed
  cases pass; the call counts confirm each branch (three devices: SetGDevice and the hooks three
  times, the readouts once).
- The window's drawing depends on the overlay: the redraw and session hooks are the overlay's. With
  its entry unported they stay 0. Leyte's overlay port is the gate for any game screen; the host
  path down to the event loop is in place.
- Corrected identification: Leyte's 0x38b2 is update_menu_items (it starts as 1.0.3's: FrontWindow,
  then the items enabled through 0x3ce4). A garbled comparison had suggested otherwise.
- Validation: QA PASS; full Godot suite below.
- Next: the host routines the loop still reaches (the idle routine CODE 4 +0x482, update_menu_items,
  refresh_display_if_changed, 0x552a / 0xab4, start_new_mission); then the plan for Leyte's overlay
  port (the 1.0.3 overlay port's generator approach, with overlay-relative addresses).

Cycle 7 of job f19bd982:

- Cycle 6's full Godot suite: 115/115.
- Leyte's overlay, the gate for any game screen, sized against 1.0.3's overlay port
  (godot/original/port/pacific_conflict.gd: 536 functions, 17393 lines; it addresses the image at base
  0 through 307 named DAT_ constants and literals).
  - Correspondence of the port's functions in Leyte's overlay: 30 identical, 70 moved, 139
    same-instruction, 184 not found (443 decompiled overlay functions in all: 36 / 73 / 145 / 189).
  - The two images: text 0x1a75c / 0x1f758, data 0xdc8 / 0xff4, bss 0xb2a4 / 0xc6c0 (1.0.3 / Leyte):
    a different layout, so the overlay needs an address map, the counterpart of a5_map.json.
- tools/leyte/overlay_map.py -> sources/leyte-gulf-1-0/overlay_map.json (checked in QA). Both overlays
  are a.out images whose relocation records name every long holding an image address. In each
  identical / moved pair, each relocated long of 1.0.3's function sits at the same place in Leyte's,
  relocated there too: the two values are the same address in each image. From the 109 identical /
  moved pairs: 129 addresses mapped (16 text, 9 data, 104 bss), 0 conflicts. That covers 98 of the
  port's 307 DAT_ constants and 126 of its 1237 literals in the image's range (many of those are not
  addresses).
- The approach, as for CODE 2:
  1. extend the map through the 145 same-instruction pairs (operands aligned by instruction: the
     relocated long in each), with conflicts refused;
  2. a generator "partial" mode for pacific_conflict.gd: per function, the same instructions in
     Leyte's overlay and every image address through the map (and every A5 / host operand through
     a5_map.json), the others refused;
  3. emulator replay of the verified functions on Leyte's overlay bytes (the 1.0.3 overlay case
     generators, translated like gen_leyte_host_cases.py);
  4. the overlay's entry and its Leyte-only functions from Leyte's bytes.
- Validation: QA PASS.
- Next: step 1 (the same-instruction pairs), then step 2's generator.

Cycle 8 of job f19bd982:

- Overlay address map, step 1 of the overlay plan (tools/leyte/overlay_map.py, checked in QA):
  - Same-instruction pairs now count too, aligned by instruction rather than byte: both functions
    disassembled (capstone 68020, past inline data), the same count and mnemonics required, and the
    relocated longs at the same offset inside each instruction paired. All 130 eligible aligned.
  - A Leyte offset two correspondence rows claim is no evidence for either: 37 rows in 14 clusters
    left out. Examples: eight register_*_template wrappers on the overlay's 0x1370c; sin / cos_fixed14
    on 0x23c4; four create_*_controller functions on 0x1db9a. Before that rule, the clusters gave
    the map's only 5 conflicts (each register_* wrapper pairing its 1.0.3 global with the one Leyte
    routine's).
  - Result: 283 addresses (56 text, 9 data, 218 bss), 0 conflicts, from 87 identical / moved and 130
    same-instruction pairs; 191 of the port's 307 DAT_ constants mapped (98 before).
- Validation: QA PASS (no Godot code changed this cycle).
- Next: step 2, the generator for pacific_conflict.gd ("partial" per function: same instructions, image
  addresses through overlay_map.json, host operands through a5_map.json; refusals otherwise). The 37
  collided functions need relocation by proof, as CODE 2's wrappers did.

Cycle 9 of job f19bd982:

- Overlay port, step 2, its proof first: tools/leyte/gen_overlay_port.py (--report). A port function
  of pacific_conflict.gd is verified for Leyte when its correspondence routine (not a collided one)
  disassembles to the same instructions, under four conditions:
  - the same count and mnemonics; branch targets move with the code;
  - every relocated long (an image address) at the same place in both and paired through
    overlay_map.json;
  - every A5 operand paired through a5_map.json;
  - every other operand equal.
- First measurement: 85 verified; 184 not in Leyte; 37 in collided clusters; about 117 refused at a
  named instruction. Most of these are structure-field displacements that differ: an aircraft field
  +0x63c vs +0x64a (adjust_ai_throttle_vs_speed), a route field +0x20 vs +0xc
  (advance_route_waypoint), an object allocation of 0x52 vs 0x54 bytes (alloc_object_from_desc). Leyte's
  object records are laid out differently, so a record-layout map from evidence (as the address map
  is: field displacements paired at the same instruction across aligned functions, refused on
  conflict) is the next piece, or those functions are reconstructed.
- Validation: QA PASS (no Godot code changed this cycle).
- Next: the record-layout map (per base register and record kind, from the aligned pairs' displacement
  operands); then writing the port for the verified functions, and the emulator replay.

Cycle 10 of job f19bd982:

- Record-layout pairs (gen_overlay_port.py record_layout). Across every aligned function pair of the two
  overlays (same count and mnemonics; not collided), a record-field displacement d16(An) (An not A5 /
  A7, the same register) pairs 1.0.3's displacement with Leyte's at the same instruction.
  - 257 seen, 227 consistent (one Leyte value each), 30 ambiguous: small offsets meaning different
    fields in different records (0x4 stays 0x4 in 56 places, becomes 0x14 in 13).
  - Consistent changes show records that grew in the middle (0xce -> 0xd2 ... 0x122 -> 0x126: +4) and
    a negative-offset block moved by -0x78 (-0x6bc -> -0x734).
  - An instruction needing a changed displacement is accepted only through a consistent pair; an
    ambiguous one refuses the function. Each verified function records the pairs it rests on.
- PC-relative targets (tables and routines reached without relocation) added to overlay_map.json, from
  all aligned pairs: 308 addresses (81 text), still 0 conflicts. The generator checks PC-relative
  operands through the map.
- Overlay port proof now: 120 verified (24 through record-layout pairs); refused: 184 not in Leyte, 37
  collided, 46 needing an ambiguous displacement, 24 other operands, 11 immediates, 1 A5 operand.
- Validation: QA PASS (no Godot code changed this cycle).
- Next: write the port for the 120 (pacific_conflict.gd's bodies with their literals rewritten:
  image addresses through the map, the record displacements each function rests on, A5 / host
  through a5_map), then the emulator replay on Leyte's overlay bytes.

Cycle 11 of job f19bd982:

- The overlay port's call closure (as the host's: a verified function whose GDScript reaches an
  unverified function is not Leyte's):
  - 46 of the 120 instruction-verified functions are closure-clean.
  - The most reached blockers: _jsr (30; the port's dispatcher for calls by code address,
    infrastructure like _jt / _local, to allow), cos_fixed14 / sin_fixed14 (22 / 21; in a collided
    cluster, to relocate by proof), abs_int (16), matmul3x3_s16 (13), and private helpers.
  - The 112 private helpers are pieces of original functions (no correspondence of their own): their
    literals need a rule (address and field-offset forms only, through the global maps), with the
    replay as the check.
- The replay: 1.0.3's overlay cases come from about 100 generators in tools/runtime/ (routines by
  absolute 1.0.3 address, the image at base 0). Translating them for Leyte means routine addresses
  through the correspondence, image addresses through overlay_map.json, and, for records whose layout
  changed, the field offsets: a case's writes are absolute (base + offset), so they need each record's
  base. Not generic.
- First tranche, the safe one: 44 closure-clean functions use no record-layout pair; 13 of them have
  1.0.3 cases: c_strlen, clear_flag_00020ae4, copy_block_return_dst, cycle_view_distance,
  fill_pose_history_angles / flags, init_code_pointer_tables, mark_effect_log_end,
  mark_view_overlays_drawn, reset_distance_base_globals, reset_radar_tracks, set_view_distance,
  track_ground_plane_horizon.
- Validation: no code changed this cycle beyond the scratch analysis; QA PASS last cycle.
- Next: generate the Leyte overlay port (_jsr allowed; refusals elsewhere), and translate the first
  tranche's cases (routine and image addresses only) for a first overlay replay on Leyte's bytes.

Cycle 12 of job f19bd982:

- Leyte's overlay port, first generated (tools/leyte/gen_overlay_port.py --write / --check in QA) ->
  godot/original/leyte/port/pacific_conflict.gd (LeyteStrictPacificConflict) and
  sources/leyte-gulf-1-0/overlay_port.json.
  - Kept: the functions verified by instruction, using no record-layout pair (their field offsets are
    not rewritten yet), and clean through their GDScript call closure (_init and _jsr allowed as the
    port's dispatch).
  - A kept function's image-range literals (0x1000 and up) must be in overlay_map.json; an unmapped
    one refuses it.
  - The constructor's code_table keeps a line only for a kept function whose own 1.0.3 address is
    the key, re-keyed to its Leyte address (3 of 55); the others are dropped, so no call dispatches to
    a 1.0.3 address.
  - Result: 31 functions kept, among them c_strlen, the overlay heap (heap_free_coalesce /
    heap_zero_payload), overlay_rng_next, memmove_block / memzero, the polygon clippers,
    traverse_scene_tree, reset_radar_tracks, set_view_distance. 392 refused, each with its reason.
    It parses in Godot.
- Not yet replayed on Leyte's bytes: these 31 rest on the instruction proof only.
- Validation: QA PASS.
- Next: translated replay of the kept functions that have 1.0.3 cases (the overlay case generators
  run on Leyte's overlay image: routines through the correspondence, image addresses and pointer
  values through overlay_map.json; a case touching an unmapped image address is left out, recorded).

Cycle 13 of job f19bd982:

- The first replay of Leyte's overlay port on Leyte's own overlay bytes
  (tools/leyte/gen_leyte_overlay_cases.py --check in QA; tests/leyte/port/test_leyte_overlay.gd):
  1.0.3's overlay fixtures (the tools/original families; not the host id0* ones) translated case by
  case for the functions the Leyte port keeps.
  - The routine is the function's Leyte address.
  - Every written address and long value in 1.0.3's image goes through overlay_map.json (a field
    within 0x40 of a mapped address moves with it). Any other image address goes to scratch space
    past Leyte's image, page by page: safe because no kept function names an unmapped image address,
    so that memory is reached only through what the case passes.
  - Call-log stand-ins with their dereference fields; no RNG seeding on either side (Leyte has no
    overlay_rng_init).
  - Left out, recorded: cases stubbing functions by name (1.0.3's port's stubbing), and cases whose
    Leyte run enters a function the port does not keep (the emulator traces function entries) or
    stops.
  Result: 22 cases over 12 functions, all matching: c_strlen, track_ground_plane_horizon,
  copy_block_return_dst, plot_dot_bw, set_view_distance, mark_view_overlays_drawn,
  fill_pose_history_angles / flags, reset_distance_base_globals, reset_radar_tracks,
  clear_flag_00020ae4, init_code_pointer_tables. 3 left out: two drive
  invoke_tagged_attitude_compose_neg_sins6 into composers the port refuses; one stops on data.
- The replay found a gap in the port's closure check: a call through _jsr(<literal address>) is now
  part of the closure. The left-out case above is a different thing: a pointer the case writes, which
  the trace now catches.
- Validation: QA PASS; full Godot suite below.
- Next: more overlay functions into the port. Relocate the collided clusters by proof (sin / cos_fixed14
  unblock about 20), rewrite record-field offsets for the 24 layout-verified functions, and allow the
  private helpers under the address / offset rule, each step checked by this replay.

Cycle 14 of job f19bd982:

- Cycle 13's full Godot suite: 116/116.
- Overlay relocation by proof (gen_overlay_port.py; its proof is now one function, prove()):
  - A refused overlay function, not-found ones included, is looked for among Leyte's overlay
    functions (Ghidra's 511; candidates within 4 bytes of its size, not taken by a verified one).
    Exactly one passing relocates it; several leave it refused, listed.
  - A relocation proof may learn an address pair the map lacks (the collided rows are no evidence in
    overlay_map.json, so their tables are unpaired there), only injectively: the 1.0.3 address
    unpaired, the Leyte address no other's image. A unique relocation's pairs join the port's
    rewriting (overlay_port.json "learned_addresses", 11 so far: the sin / cos tables 0x1ab6c ->
    0x1fd94 and 0x1ad6c -> 0x1ff94, the controller factories the register_*_template wrappers name).
  - 14 relocated: sin_fixed14 0x23c4, cos_fixed14 0x2426, seven of the eight register_*_template
    wrappers (each at a routine of its own), lock_handle_nopurge, create_spinner_controller,
    ensure_ai_script_1b310_tick, alloc_transformed_vertex_block, reset_palette_owner_tables.
- Proof: 134 verified (25 through layout pairs, 14 relocated); the port keeps 35 (sin / cos among them).
  The replay is unchanged: 22 cases over 12 functions, all matching.
- Validation: QA PASS; full Godot suite below.
- Next: rewrite record-field offsets in the layout-verified functions' bodies (each function's own
  pairs), then allow the private helpers under the address / offset rule, each checked by the replay.

Cycle 15 of job f19bd982:

- Cycle 14's full Godot suite: 116/116.
- Record-field offsets in the generated overlay port (gen_overlay_port.py). A function verified
  through record-layout pairs is now kept, with exactly its own pairs rewritten in its own body, in
  offset form only ("+ 0xce" -> "+ 0xd2", "- 0x6bc" -> "- 0x734"; overlay_port.json "record_fields").
  Several are the host world reached through a register (0x282 -> 0x2ba, 0x272 -> 0x2aa: the +0x38
  of the A5 map's shifts). The consistent pairs are published (overlay_port.json "record_layout").
  The port keeps 53 functions (35 before).
- Case translation (gen_leyte_overlay_cases.py):
  - addresses past 1.0.3's image but inside Leyte's larger one go to scratch too;
  - the host world the case writes (the base at the overlay's host_world pointer 0x1b5a0) moves with
    its base, offsets in its first 0x600 bytes through the record-layout pairs;
  - function entries are traced against all 511 of Ghidra's Leyte overlay functions: a run reaching
    any the port does not keep is left out (a jump to address 0 had passed unseen).
  Gauge and colour-setup cases still stop in Leyte's run: their host-world data sits at offsets the
  layout pairs do not establish, and guessing record layouts is not allowed, so they are left out with
  the reason.
- Status: 53 kept; 12 replayed on Leyte's bytes (22 cases, all matching). 8 kept functions carry
  rewritten record fields and none of them is replayed yet: they rest on the instruction proof (every
  field displacement paired). 33 others are kept on the instruction proof alone (no 1.0.3 cases).
- Validation: QA PASS; full Godot suite below.
- Next: replayable host-world cases (the host world's layout from the A5 map and the host's own
  correspondence, not only the overlay's pairs), so the layout-rewritten functions get a replay;
  then the private helpers.

Cycle 16 of job f19bd982:

- Cycle 15's full Godot suite: 116/116.
- Private helpers in the overlay port (gen_overlay_port.py). The port's "_name" helpers (pieces of
  original functions, no correspondence of their own) are taken as they are when no offset-form
  literal in their body ("+ 0x.." / "- 0x..") is a displacement Leyte moves (changed or ambiguous in
  the record layout). Otherwise the helper is refused with those offsets, and its callers with it.
  Their image addresses go through the map like any function's; their own calls are checked by the
  closure.
- Result: 69 original functions kept (53 before) and 45 helpers (overlay_port.json "functions" /
  "helpers"). Replay: 24 cases over 14 functions, all matching Leyte's bytes. Left out: 37 whose
  Leyte run stops (host-world and record data the translation cannot establish), 2 reaching
  functions the port does not keep.
- Validation: QA PASS; full Godot suite below.
- Next: the host world's layout for the overlay's cases, so the gauge / colour / offscreen families
  replay. The host world is what install_overlay_service_table (Leyte 0x1e58, 35 slots) builds: its
  globals pair 1.0.3's table with Leyte's, entry by entry. Then link the port into the Leyte profile
  (the overlay entry) to see how far the start goes with it.

Cycle 17 of job f19bd982:

- Cycle 16's full Godot suite: 116/116.
- Leyte's overlay entry written from its bytes (godot/original/leyte/port/pacific_conflict_reconstructed.gd,
  leyte_overlay_main_entry, 0x175a, the a.out entry). It takes (host world, -1, TickCount), one
  argument more than 1.0.3's 0x10f2, so it is named apart from the base's refused two-argument
  overlay_main_entry: GDScript forbids an override with another signature.
  - It stores the host world at 0x207fc (1.0.3 0x1b5a0) and runs Leyte's initialiser 0x1ef0 (BSR.L;
    1.0.3's overlay_initialise has no counterpart found, so it is Leyte's own and not written yet).
  - It sets world +0x92 = 9 and writes 22 hooks (Leyte's routines) from world +0x322 (1.0.3: +0x2e6).
  2 emulator cases (tools/leyte/gen_leyte_overlay_reconstructed_cases.py, --check in QA;
  tests/leyte/port/test_leyte_overlay_reconstructed.gd) pass.
- A fault in the overlay case generators, found and fixed: m68k_emu's Emulator() with no binary loads
  1.0.3's overlay image, and the Leyte image only overwrote its own text and data. So the emulator side
  had 1.0.3's bytes in Leyte's bss, where the port side had Leyte's fork bytes; the matches held only
  because the kept functions never read there. Both sides now hold what the loader leaves: Leyte's text
  and data, everything past them zero. The overlay replay grows to 34 cases over 17 functions, all
  matching.
- Validation: QA PASS; full Godot suite below.
- Next: Leyte's initialiser (0x1ef0), then link the overlay into the Leyte profile: the image at
  0x01000000 as 1.0.3's overlay_loader.gd places it (its 24 low bits zero: the port addresses the image
  at 0), the entry and the hooks registered as code routines.

Cycle 18 of job f19bd982:

- Cycle 17's full Godot suite: 117/117.
- Leyte's overlay initialiser written from its bytes (leyte_overlay_initialise, image 0x1ef0, 128
  bytes). It is 1.0.3's overlay_initialise entry for entry with Leyte's addresses (the correction to
  cycle 17: the counterpart exists). The steps: the RNG seeded with the ticks (0x135fe, Leyte's own
  routine, not written yet); the palette usage maps (0x23d94 = 0x23e9c, 0x23d90 = 0x23d9c);
  init_code_pointer_tables; 0x4212 twice (a bare RTS, 1.0.3's overlay_init_noop, so it is left out); the
  three span tables (init_span_table_32ba kept, 0x3ef2 and 0x4094 still refused); the light vector
  (0xd983, 0x69d8, 0xc330); 0x2156c = 1 and 0x21568 = 0; the frame counter 0x21020 = 0x4c; and
  set_view_distance(0xc15c0).
  - leyte_overlay_main_entry now calls it instead of jumping to the address.
  - 4 emulator cases (2 for the initialiser, 2 for the entry) pass. The unwritten callees are logging
    stand-ins on both sides, and the kept callees run for real.
- Validation: QA PASS; full Godot suite below.
- Next: the two span-table builders (0x3ef2, 0x4094) and the RNG seeding (0x135fe), by correspondence
  with 1.0.3's init_span_table_3466, init_span_table_3608 and overlay_rng_init. Then link the overlay
  into the Leyte profile.

Cycle 19 of job f19bd982:

- Cycle 18's full Godot suite: 117/117.
- The three routines Leyte's overlay initialiser was still standing in, written from Leyte's bytes
  (pacific_conflict_reconstructed.gd). Each is 1.0.3's routine with Leyte's addresses:
  - leyte_overlay_rng_mul_mix (0x135be): byte for byte 1.0.3's overlay_rng_mul_mix. It is written
    under its own name because the base's static copy is refused for Leyte.
  - leyte_overlay_rng_init (0x135fe): 1.0.3's overlay_rng_init on Leyte's ring. The seed is at
    0x2c5b8, the cursor at 0x2c5b4 is set to 0x2c690, then 10000 calls of the kept overlay_rng_next.
    It was "not found" because the base's DAT_0002600c is unmapped.
  - leyte_init_span_table_3ef2 (table 0x3ece, writers 0x3fd6) and leyte_init_span_table_4094 (0x4070,
    0x4178): 1.0.3's init_span_table_3466 / 3608 through _init_span4_table. The operands are resolved
    from the PC-relative LEAs; the offsets are 0, 6, 0x18, 0x20, 0x24 and 0x50 four times. The
    generator refused them as ambiguous, since both bodies pass at both addresses; Leyte's initialiser
    calls them in 1.0.3's order.
  - Note: Ghidra's listing of the Leyte overlay shows text-relative values +0x20 (file addressing) but
    data absolutes as in the image. The raw bytes are the evidence used.
- The overlay entry and its whole initialiser now run with no stand-ins: 8 emulator cases match (2
  entry, 2 initialiser, 2 RNG, 2 span tables).
- Link survey for next cycle: MacMachine creates no overlay loader under a title profile. Leyte's
  after-load slot is 0x51a (CODE 2 +0x245a; 0x4c2 is position_alert in Leyte), and overlay_loader.gd's
  method map (overlay_methods.json), hook list and two-argument entry are 1.0.3's.
- Validation: QA PASS; full Godot suite below.
- Next: a Leyte overlay loader created in leyte_profile.link. overlay_loader.gd gets overridable
  pieces (method map, after-load slot, port script, entry call, hooks) with the defaults left as they
  are. Leyte supplies its code_table, 0x51a, the reconstructed port, the three-argument entry and
  LEYTE_OVERLAY_HOOKS. Then rerun the start probe.

Cycle 20 of job f19bd982:

- Cycle 19's full Godot suite: 117/117.
- Leyte's overlay is linked into the start (godot/original/leyte/machine/leyte_overlay_loader.gd,
  created in leyte_profile.link). It extends overlay_loader.gd, whose _init now takes the method map
  path and after-load slot as parameters. The defaults are Pacific Conflict's, so 1.0.3 is unchanged.
  - The slot is 0x51a, with no method map (the port's code_table holds the kept routines at Leyte
    addresses), and the port is pacific_conflict_reconstructed.gd.
  - The entry 0x175a is called with three arguments.
  - The 22 hooks are registered position for position with 1.0.3's OVERLAY_HOOKS. A hook with no Leyte
    routine is reported through note_missing (naming its 1.0.3 counterpart), never answered for it.
  - None of 1.0.3's runtime-written overlay routines are carried over, since their addresses are 1.0.3's.
- Start probe (synthetic storage user://leyte-start-probe): FSOpen "Leyte Gulf" (315530 bytes) → the
  image loaded (code 0x1f758, 4274 relocations, the low memory restored) → slot 0x51a links it →
  reset_overlay_state → apply_display_palettes. The first divergence on this path is host 0x4e32 =
  ID02 rebuild_all_palette_index_maps (same instructions as 1.0.3). It is refused because
  build_palette_index_map is not found in Leyte and A5 -0x1122 is unpaired.
  - Later: the overlay entry runs, then the host calls hooks 0 (0x8032, 1.0.3
    configure_renderer_for_display: ambiguous displacement 0x48), 4 (0x5192, run_simulation_tick, not
    found) and 21 (0x728c, spawn_cloud_layer, not found). All are reported missing.
  - Still: start_new_mission 0x3d38, update_menu_items 0x38b2, the idle slot $aa (CODE 4 +0x482) and
    refresh_display_if_changed 0x2d60.
- Validation: QA PASS; full Godot suite below.
- Next: Leyte's build_palette_index_map and rebuild_all_palette_index_maps in ID02_reconstructed.gd,
  from its bytes with emulator cases, then the three overlay hooks met (configure_renderer_for_display,
  run_simulation_tick, spawn_cloud_layer).

Cycle 21 of job f19bd982:

- Cycle 20's full Godot suite: 117/117.
- Leyte's palette index maps written from its CODE 2 bytes (ID02_reconstructed.gd):
  - rebuild_all_palette_index_maps (0x4e32): 1.0.3's 0x46b4, the same instructions with Leyte's A5. It
    uses the flag -0x106c, depth -0x13f8, the offscreen port -0x107c and the device records -0x13ec;
    its callees are close_and_dispose_port 0xdb2 and init_standard_colour_indices 0x52f6.
  - build_palette_index_map (0x502c), 1.0.3's 0x48ae ("not found": one condition differs). A colour hit
    twice at palette depth 8 below 0xc8 sets the flag only when A5 -0x1352 and A5 -0x1074 are both set,
    or for device 1; 1.0.3 sets it for every device. The service pointer is A5 -0x1168 = A5 +0x4ca with
    the flag, else +0x3d2 (1.0.3: -0x1122, +0x46a / +0x33a).
  - The frame is the original's in memory (hits, saved device, count), so at another palette depth the
    index is the clearing loop's 0x100 and the count the frame word as found, with no assert.
  - Note: the generated Leyte ID02.gd keeps 1.0.3's A5_DEVICE_RECORDS (-0x136e), which is unused by its
    kept functions. The reconstruction uses Leyte's -0x13ec.
- 11 emulator cases (7 build, 4 rebuild; the flag condition each way), 70 in all, match. segment_methods.json
  is regenerated (CODE 2: 114 methods).
- Start probe: apply_display_palettes now runs through the rebuild in the real start. The first
  divergences are the overlay hooks 0 (configure_renderer_for_display), 4 (run_simulation_tick) and
  21 (spawn_cloud_layer), then start_new_mission 0x3d38, update_menu_items 0x38b2, the idle slot $aa
  and refresh_display_if_changed 0x2d60.
- Validation: QA and the full Godot suite below.
- Next: the overlay hook 0 (configure_renderer_for_display, refused on the ambiguous displacement 0x48
  at +0x722e: settled by Leyte's record layout or written from its bytes), then hooks 4 and 21.

Cycle 22 of job f19bd982:

- Cycle 21's run: QA PASS, full Godot suite 117/117.
- tools/leyte/gen_overlay_port.py, two evidence rules:
  - A record displacement that is ambiguous across the overlays (30 of them) is settled for one function
    when every aligned instruction of that function pairs it with the same Leyte displacement
    (_local_layout). Its port's offsets are then rewritten with that pair. Verified functions rise from
    134 to 159; 25 rest on a locally settled pair, among them configure_renderer_for_display
    (0x48 -> 0x4a, hook 0's refusal).
  - A guard on the rewrite, which reaches hex offset literals only. A function resting on a moved field
    is refused when its body also writes that offset in decimal ("obj + 4") or as a stack-frame offset
    ("entry_a7 - 0x14"). It refuses 4: tick_effect, tick_trail and test_proximity_collision (+4 ->
    +0x14 written "obj + 4") and destroy_aircraft_spawner_controller (+3). Without the guard they
    would have kept 1.0.3's offset. No frame-offset case turned up.
  - The port keeps 77 functions (was 69) plus the helpers.
- Hook 0 (configure_renderer_for_display) now passes the instruction proof. Its call closure still
  reaches routines not proven for Leyte: init_mission_object_tables and overlay_patch_template_u16_pair
  (not found), and update_view_clip_bounds (through reset_span_row_lists). Hooks 4 (run_simulation_tick)
  and 21 (spawn_cloud_layer) are not found in Leyte.
- Validation: overlay fixtures, QA and the full Godot suite below.
- Next: emulator cases for the newly kept overlay functions. Then Leyte's counterparts of the hook 0
  closure (init_mission_object_tables, overlay_patch_template_u16_pair, reset_span_row_lists), placed by
  Ghidra function size and written from Leyte's bytes where they differ.

Cycle 23 of job f19bd982:

- Cycle 22's overlay fixtures: the reconstructed cases unchanged (PASS); overlay_cases.json regenerated
  for the 77 kept functions: 38 cases over 18 functions, all matching. The new one is
  face_is_back_facing, 4 cases, resting on the locally settled +0x44 -> +0x46.
- Probe unchanged: the first stops are the overlay hooks 0, 4 and 21, then the host's 0x3d38, 0x38b2,
  the idle slot $aa and 0x2d60.

Cycle 24 of job f19bd982:

- Two more host routines written from Leyte's CODE 2 bytes (ID02_reconstructed.gd):
  - refresh_display_if_changed (0x2d60; 1.0.3 0x2614). A main device other than A5 -0x1078 rescans the
    devices. A pixel size of its pixmap other than A5 -0x13f8 runs configure_display (0x2b48),
    apply_settings (0x2db2) and apply_display_palettes (0x54b8). 1.0.3 synced the overlay globals
    there instead. Then the overlay hook A5 -0x10d8 runs when A5 -0x1070, the image A5 -0x1048 and
    A5 -0x1544 are all set.
  - check_device_color_state_changed (0x53ac; 1.0.3 0x4bd0) with Leyte's A5 and its frame (link 0x40a:
    the "any changed" word at -0xa). The palette report is JT $1a2 (1.0.3 $182) with A5 -0x13d2; the
    hook is A5 -0x10d8 with an overlay image.
- 11 emulator cases (5 colour check, 6 refresh), 81 in all, match. segment_methods.json is regenerated
  (CODE 2: 116).
- Validation: probe, QA and the full Godot suite below.
- Next: update_menu_items (0x38b2) and start_new_mission (0x3d38), both "not found" by the
  correspondence. They will be placed by their callers' bytes and written from Leyte's.

Cycle 25 of job f19bd982:

- Cycle 24's probe: refresh_display_if_changed (0x2d60) no longer stops the start.
- update_menu_items (0x38b2) written from Leyte's CODE 2 bytes (ID02_reconstructed.gd). It is 1.0.3's
  0x3174 by role ("not found": Leyte's menus have other items and conditions).
  - File (A5 -0xc4a) items 6, 3, 7, 8, 4, 5. The state words are playing -0x1070, game state -0x1376,
    image -0x1048, -0x136e, -0x1542 and -0x132e.
  - Edit (A5 -0xc46): items 0 (when the desk-accessory state changes, -0xc3a), 1, 3, 4, 5, 6.
  - Options (A5 -0xc42): checks from settings +3/+5/+7/+9 and the sound byte -0x1071. Item 8 is enabled
    by -0x1074 and checked by +0x1d; 10/12/11 are enabled unless busy (-0x1074 and -0x1352) and checked
    for settings +0xe = 1/2/3.
  - View (A5 -0xc3e): 7 at depth 8, 11 unless depth 4; 1..5 for +0x10; 7, 9, 8, 11, 10 from +0x13,
    +0x15, +0x17, +0x1b, +0x19.
  - It calls set_menu_item_enabled (0x3ce4, identical), run as written on both sides, and CheckItem.
- 9 emulator cases, 90 in all, match; each trap call and its arguments is compared through the call
  logs. segment_methods.json is regenerated (CODE 2: 117).
- Validation: probe, QA and the full Godot suite below.
- Next: start_new_mission (0x3d38), then the idle slot $aa (CODE 4 +0x482).
- Probe (cycle 25): update_menu_items now runs in the real start; 0x38b2 no longer stops it. The first
  host stop is start_new_mission 0x3d38, then the idle slot $aa, with overlay hooks 0, 4 and 21
  reported missing.
- Full Godot suite NOT run to completion for cycles 24 and 25. A separate Codex session on this machine
  (the ChatGPT app, a disk-cleanup task in ~/Documents/Codex/2026-11-15/...) sends SIGTERM to every
  "Godot ... --script res://tests/run_all.gd" process ("Arrêt autorisé des tests Godot") and runs
  nix-store --gc. Two runs died with exit 143 after 61 s and 27 s. That stop was left alone and not
  worked around.
  - Verified instead: QA PASS (cycle 24 state), and the targeted replays passed (ID02 reconstructed:
    90/90 cases; overlay: 38/38; overlay reconstructed: 8/8).
  - The full suite is to be rerun once the cleanup is over. The nix-store --gc may also mean the
    nix-shell environment is fetched again on next use.

Cycle 26 of job f19bd982:

- The Codex cleanup session finished its nix-store --gc (free disk 57 GiB) and runs no command now.
  QA was rerun on cycle 25's state: PASS (the earlier traceback was that GC mid-run). The full Godot
  suite was rerun; its result is below.
- start_new_mission (0x3d38) written from Leyte's CODE 2 bytes (ID02_reconstructed.gd). It is 1.0.3's
  0x35e4 with Leyte's A5 and a loop 1.0.3 does not have:
  - While JT $15a (1.0.3 $132) and JT $92 ($82) allow a mission: the mission hook A5 -0x10dc with the
    game state -0x1376. With the window up (-0x1544): its port rect is invalidated (window -0x1548,
    port -0x168a restored).
  - -0x136e and the counters at -0x1420 / -0x1348 (12 each) are cleared, with -0x132e, -0x1330,
    -0x132c, -0x1542, -0x1540 and -0x153e.
  - The 1-bit hook -0x10d8 runs at depth 1.
  - Then Leyte's flight session (0x4fc, word result); a nonzero result starts the next mission.
- A fixture bug found and fixed in gen_leyte_id02_reconstructed_cases.py. The stand-ins' 0x400-byte log
  slots from 0x40000 passed index 120 and reached CODE 1 at 0x5e000. The new mission stand-ins logged
  over CODE 1's bytes, so their logs were lost (a "two missions" case changed only 1 byte).
  - The emulated memory is now 0x80000 with CODE 1 at 0x7e000, plus an assert that the log table ends
    below CODE 1.
  - Stand-ins up to index 119 ended at 0x5e000 exactly, so earlier cases were unaffected.
  - 7 mission cases (one and two missions, each check refusing, window up/down, depth 1/8); 97 in all
    match. segment_methods.json is regenerated (CODE 2: 118).
- Probe: no assertion stops remain in the start. main → … → start_new_mission, whose JT $15a (CODE 4
  +0x694) is reported missing and so answers 0: no mission starts. The other missing routine is the
  idle slot $aa (CODE 4 +0x482). Overlay hooks 0, 4 and 21 are reported missing.
- Next: CODE 4 +0x694 (the mission-ready check) and +0x482 (idle), from Leyte's bytes against 1.0.3's
  CODE 4, then Leyte's flight session 0x4fc.

Cycle 27 of job f19bd982:

- The full Godot suite rerun (cycle 25's state, uninterrupted): 117/117 in 753 s. QA PASS.
- CODE 4 +0x482 (slot $aa, run every event-loop pass) written in leyte_runtime.gd as id04_0482. It is
  1.0.3's id04_0432 (report_panel_store_failure, CODE 4 +0x432, slot $172): the same instructions found
  by a byte search with A5 offsets masked. Leyte's operands: flag -0x151e, JT $4ba, JT $4da with the C
  string at A5 -0xf80 and 0. The probe no longer reports $aa missing.
- Leyte's JT slots paired with 1.0.3's CODE 4 dialogs through the two jump tables:
  - $15a = CODE 4 +0x694 = 1.0.3's $132, +0x644 run_duty_roster_dialog: the Active Duty Roster, DLOG
    130 (GetNewDialog 0x82).
  - $92 = +0xc52 = 1.0.3's $82, +0xc02 run_scenario_selection_dialog ("Select a Mission").
  - In 1.0.3 both are written in unexported.gd (id04_0644, id04_0c02) and checked by
    tools/runtime/gen_runtime_cases.py.
- Probe: the only host routine missing on the start path is JT $15a, the Duty Roster: the first game
  screen. Overlay hooks 0, 4 and 21 are reported missing.
- Owed: Leyte's runtime routines (CODE 5 0x3ba and $58a, CODE 6 $1da, CODE 4 $aa) have no emulator
  cases yet. 1.0.3's are in unexported_cases.json.
- Validation: QA and the full suite below.
- Next: a Leyte runtime-case generator after tools/runtime/gen_runtime_cases.py (Leyte's CODE files and
  a profile-built machine on temporary storage) covering the four routines above. Then the Duty Roster
  (+0x694) and Select a Mission (+0xc52) from Leyte's bytes, with dialog cases driven by synthetic
  ModalDialog item sequences.

Cycle 28 of job f19bd982:

- Cycle 27's QA: PASS.
- Leyte's runtime-written routines are now checked against Leyte's original bytes (the gap from cycle 27):
  - tools/leyte/gen_leyte_runtime_cases.py reuses tools/runtime/gen_runtime_cases.py's runner with its
    CODE directory pointed at sources/leyte-gulf-1-0/CODE. It writes 1.0.3's cases with Leyte's operands
    (a5_map.json and the CODE 5 port's own remapping: SysEnvirons $202, HLock/HUnlock $2da/$2a2,
    HNoPurge/HPurge $29a, the string glue $33a, strcmp $562, the copy $572, DisposPtr $24a, the screen
    bounds -0x16fe..-0x16f8, the equal string -0xda4).
  - The fixture is godot/tests/leyte/fixtures/runtime_cases.json: 18 cases. CODE 4 0x482 (3), CODE 5
    0x8b2 (4), CODE 6 0x858 (8), and CODE 5 0x3ba build_protection_code_strings (3), its first emulator
    check.
  - tests/leyte/test_leyte_runtime_routines.gd (in run_all) replays them on a machine the Leyte profile
    builds. It extends test_unexported_routines.gd for its call logging. Its storage is a temporary
    root (user://leyte-runtime-test), removed afterwards, and the profile's roots are restored.
  - 18/18 match. --check is added to QA.
- Validation: QA and the full suite below.
- Next: the Duty Roster (CODE 4 +0x694, JT $15a) from Leyte's bytes against 1.0.3's id04_0644, with
  cases after 1.0.3's duty_roster_cases (synthetic roster and ModalDialog item sequences).

Cycle 29 of job f19bd982:

- Cycle 28's run: QA PASS; full Godot suite 118/118 in 688 s, with the new
  test_leyte_runtime_routines.gd. No test left a Leyte folder in Godot's user data, only the manual
  probe's own root.
- Leyte's Active Duty Roster written in leyte_runtime.gd (slot $15a). The first game screen the start
  reaches.
  - id04_0694: CODE 4 +0x694, 1.0.3's id04_0644 run_duty_roster_dialog with Leyte's operands: roster
    -0x1426, settings -0x142a, current pilot -0x1422, rank names -0x103c, JT $4aa/$512/$33a/$342/$54a/
    $552, filter $e2, user item proc $102, the record's message -0xe88.
  - It calls the kept rank_pilots_by_score 0x11ba, prune 0x13d6, compute_pilot_score 0x5d8 and
    set_dialog_user_item_proc 0x13c.
  - As the original, the listed pilots are kept in its frame (-0x110) and the item-4 / OK rows are
    read back from it unclamped. 1.0.3's runtime port clamps them, which the original does not.
  - Item 4's record (show_mission_summary_dialog, 0x3638, not ported for Leyte) goes through _local4:
    reported missing, never answered for it.
  - id04_0208: install_default_ring_item, 1.0.3's id04_01b8 with slot $ea and A5 -0x2322.
  - 4 roster cases (Cancel; OK unchanged; OK renamed, ten shown, the roster written; OK after other
    items) in gen_leyte_runtime_cases.py: 22 in all, matching. Each roster case compares about 150
    Toolbox and jump-table calls.
- The probe now drives each ModalDialog with a synthetic Return (at most five) and prints the item.
  The start reaches DLOG 130 (GetNewDialog), ModalDialog ends on item 1 (OK), the names are read back
  and the selected pilot made current.
- Next gaps, in the order met:
  - the roster's filter (slot $e2, CODE 4 +0x634; 1.0.3's duty_roster_filter_proc +0x5e4);
  - strncmp (JT $54a, CODE 2 +0x434; 1.0.3's runtime id02_0434 at the same offset);
  - Select a Mission (JT $92, CODE 4 +0xc52; 1.0.3's id04_0c02). It is reported missing and answers
    0, so no mission starts yet.
- Validation: QA and the full suite below.

Cycle 30 of job f19bd982:

- Cycle 29's run: full suite 118/118 in 785 s. Its QA failed one check, "stale wording" (the word
  "unported" in a leyte_runtime.gd comment). The comment is reworded and QA rerun: PASS.
- tools/leyte/gen_host_ports.py: CODE 1's switch helpers (slots $2a / $32 / $3a, the same bytes in both
  editions) end in an indexed JMP (4EF0, JMP d8(A0,D2.W)). The slot-identity check's glue comparison
  did not accept that as a terminator, so every port reaching them through a sparse switch was refused
  ("jump-table slot 0x2a does not reach the same routine"). It is accepted now, its extension word
  compared too.
  - Five Leyte CODE 4 dialog filters are now proven by instruction: duty_roster_filter_proc 0x634,
    fuel_slider_dialog_filter 0xade, scenario_selection_filter_proc 0xc2a, refuel_dialog_filter_proc
    0xfd4 and map_dialog_filter_proc 0x417e. None is newly refused.
  - host_ports.json, the ports, segment_methods.json (CODE 4: 48) and jump_table.json (184 slots, 102
    named) are regenerated.
- strncmp (slot $54a, CODE 2 +0x434), byte for byte 1.0.3's id02_0434 at the same offset, added to
  leyte_runtime.gd. 1.0.3's 12 strncmp cases run on Leyte's CODE 2: 34 runtime cases in all, matching.
- Probe (synthetic Return): the Duty Roster runs end to end. The Return goes through Leyte's
  duty_roster_filter_proc, which presses item 1 (OK), and the names are compared with strncmp. The only
  host routine missing on the start path is now JT $92 (Select a Mission, CODE 4 +0xc52). It answers 0,
  so no mission starts. Overlay hooks 0, 4 and 21 are reported missing.
- Validation: QA and the full suite below.
- Next: Select a Mission (CODE 4 +0xc52) from Leyte's bytes against 1.0.3's id04_0c02, with cases after
  1.0.3's scenario_dialog_cases. Then Leyte's flight session (CODE 2 +0x4fc).

Cycle 31 of job f19bd982:

- Cycle 30's QA: PASS (its suite below).
- Select a Mission written in leyte_runtime.gd (slot $92): id04_0c52, CODE 4 +0xc52. It is 1.0.3's
  id04_0c02 (run_scenario_selection_dialog) with Leyte's operands and two options 1.0.3 does not have:
  - Operands: settings -0x142a, fuel -0x1350 / -0x2310, pilot -0x1422, changed flag -0x1408, game
    state -0x1376, -0x1542, mission radios -0x230a, bomb radios -0x22fa, JT $4aa/$4fa/$51a, filter
    $162, user items $172/$102/$142.
  - A second radio group (items 23/24 at A5 -0x22f4, from A5 -0x134a, stored at settings +0x26).
  - A checkbox (item 25, from A5 -0x134c, toggled 0/6 on a hit, stored at +0x28 toggled when the box
    differs, as the original does).
  - Its callees not in the port: id04_032c (set_radio_group_selection, byte for byte 1.0.3's
    id04_02dc); id04_009c / id04_00c4 (Leyte's own: SetCtlValue / GetCtlValue of a dialog item).
  - id04_0566, confirm_end_mission (1.0.3 0x516). Leyte's mission counts as running from game state 2
    (1.0.3: 3); it was refused on that compare.
  - id04_1b4c, the kill report (1.0.3's get_mission_kill_report_state 0x18e0 without its "state 2
    reports nothing" test).
  - 5 cases (Cancel; OK unset; change all with the box ticked; no radio on; a running mission whose
    end alert answers 1 then 2). 39 runtime cases in all, matching.
- Probe (synthetic Return only): main → copy protection → overlay loaded → the Duty Roster (DLOG 130,
  OK) → Select a Mission (DLOG 129, OK) → the overlay's mission hook. That is hook 2, 0x46e2 (1.0.3
  0x3aa4), reported missing. Then the flight session, CODE 2 +0x4fc, which has no routine: the start
  stops there.
- Next: the flight session (Leyte CODE 2 +0x4fc; 1.0.3's run_flight_session 0x1a6c, "not found") and
  the overlay hooks a mission needs (2 and 4: run_simulation_tick). That is the gate to gameplay; the
  overlay's simulation (264 of its functions refused for Leyte) is the large remaining body of work.

Cycle 32 of job f19bd982:

- Cycle 31's QA: PASS (its suite below).
- The flight session (Leyte CODE 2 +0x4fc, the main game loop; 1.0.3's run_flight_session 0x1a6c) is
  read in full. A plain disassembly stops at its first sparse switch (jsr $32(a5)) by reading the case
  table as code.
  - tools/leyte/switch_listing.py, a read-only evidence tool, decodes CODE 1's three switch helpers'
    inline tables and resumes after them. The table formats are taken from the helpers' own bytes:
    - $2a / $32 (sparse, word / long values): a count, that many (offset.w, value) pairs, then the
      default offset word. The helper's dbeq makes count + 1 passes; the last reads the default word.
    - $3a (dense): low, high, the default offset, then one offset per case. Every target is its offset
      word's address plus the offset.
  - The routine spans 0x4fc..0x9bc. Its command keys are T/t (A5 -0x1071 bit 0), 1..5 (settings +0x10,
    then apply_settings 0x2db2), Q/q (result 5), S/s and E/e, as in 1.0.3, with Leyte's operands.
  - Its CODE 2 callees not yet written for Leyte: 0x45a / 0x4a4 (begin / end overlay session), 0x18e2,
    0x552a, 0x1c92, 0x9e8, 0xa88, 0x54c8, 0x54fe and 0x36d8. Overlay hooks follow through the A5 hook
    slots (-0x10b0 at the start).
- Assessment: past the two dialogs, the start enters gameplay: this loop, its session routines and the
  overlay's simulation. The overlay keeps 77 of its functions for Leyte, with 264 refused, and hooks 0,
  2, 4 and 21 are missing. The flight session can be written and checked by its cases as the other
  host routines were, but a mission will not run until the overlay's simulation is ported for Leyte.
  That is several cycles of work at the current rate.
- Next: the flight session's session routines (0x45a, 0x4a4) and the loop itself from the listing,
  with cases; then the overlay hooks a mission calls first (2, then 4: run_simulation_tick).

Cycle 33 of job f19bd982:

- Cycle 31's full suite: 118/118 in 702 s. Cycle 32's QA (a read-only tool and checkpoint text only):
  PASS.
- The flight session's session routines, from Leyte's CODE 2 bytes:
  - begin_overlay_session (0x45a) and end_overlay_session (0x4a4) in ID02_reconstructed.gd: 1.0.3's at
    the same offsets, the same instructions with Leyte's operands.
    - Begin: UnloadSeg A5 +0xda, 0x4c6, the prepare hook -0x10c4, load_game_sounds 0x22ac, FreeMem
      $222 / MaxMem $22a under 100 000 bytes, the start hook -0x10c0, 0x9e8, then 0x23ee.
    - End: shutdown_sound 0x4a96 with sound on (-0x136c), the finish hook -0x10b8, 0x2186, 0xa88, then
      DrawMenuBar with -0x106a set.
    - 7 emulator cases; 104 CODE 2 cases in all match.
  - lock_session_handles (0x9e8) and unlock (0xa88) in leyte_runtime.gd: 1.0.3's id02_095a / 09fa
    with Leyte's operands (counter -0x1572; handles -0x1270, -0x1256, -0x124e, -0x132a with their kept
    pointers -0x126c, -0x1252, -0x124a, -0x1326; the 24-bit flag -0x13c6; HLock $28a, HUnlock $292).
    8 cases with the HLock / HUnlock calls compared; 47 runtime cases in all match.
- segment_methods.json (CODE 2: 120) and jump_table.json are regenerated.
- A duplicate constant (LEYTE_JT_FREE_MEM, already declared with the same value) briefly broke the
  reconstructed CODE 2 file's parse. It was caught by the targeted replay and removed before the run.
- Still to write before a session can run: load_game_sounds 0x22ac (1.0.3 0x1f8e; 93 instructions
  against 88), then the flight loop 0x4fc and its remaining callees 0x18e2, 0x552a, 0x1c92, 0x54c8,
  0x54fe and 0x36d8.
- Validation: QA and the full suite below.

Cycle 34 of job f19bd982:

- Cycle 33's QA: PASS (its suite below).
- load_game_sounds (0x22ac) written from Leyte's CODE 2 bytes (ID02_reconstructed.gd). It is 1.0.3's
  0x1f8e with Leyte's operands and one more sound:
  - The slot table -0xd94 is recorded at -0x1170, then release_sound_slots 0x2186, MaxMem $22a and
    FreeMem $222.
  - The engine sound is from -0xd2e above 0x2e400 free, else from -0xd26.
  - Then slots 5, 2, 9, 6, 7, 14 (Leyte's, from -0xcf2), 1, 12, 11, 10 and 8. Slot 8 falls back to
    -0xcca, and that counts as a failure (BSET #0 of the failure word).
  - Any failure shows the sound error dialog once (0x20f2). Both callees are the verified port's,
    called by offset.
- 5 cases (all loaded with plenty / little free; one missing; slot 8's fallback loading or missing).
  109 CODE 2 cases in all match. segment_methods.json (CODE 2: 121) and jump_table.json are
  regenerated.
- A line-continuation slip in the case generator made it fail with an IndentationError. It was caught
  at once and fixed; the replay then ran the regenerated fixture.
- Validation: QA and the full suite below.
- Next: the flight loop 0x4fc (listing in hand: tools/leyte/switch_listing.py), with its callees
  0x18e2, 0x552a, 0x1c92, 0x54c8, 0x54fe and 0x36d8.

Cycle 35 of job f19bd982:

- Cycle 33's full suite: 118/118 in 725 s. Cycle 34's QA: PASS (its suite below).
- The flight loop's callees, by the correspondence and Leyte's bytes. Already kept:
  set_draw_color_index 0x18e2, run_segment3_entry_and_unload_a5safe 0x1c92 and handle_activate_event
  0x36d8. Written now (ID02_reconstructed.gd):
  - restore_screen_globals (0x552a, no correspondence row): 1.0.3's 0x4d96 (the runtime's id02_4d96)
    with Leyte's operands: SetGDevice(-0x1078); -0x1400 / -0x13f6 / -0x1390 from -0x13c4 / -0x13c8 /
    -0x13c6.
  - process_pending_update_events (0x54c8) and handle_update_event (0x54fe): 1.0.3's 0x4d34 / 0x4d6a,
    the same instructions. They differ only in the game window -0x1548, its flag -0x1544 and the redraw
    0x5544. They were refused only through callees now reconstructed, and through A5 globals the map
    does not pair.
  - 8 cases (the update loop over 0, 1, 2 events and another window's, the records written by
    GetNextEvent's stand-in into the frame; the single update for the window up / down / another).
    117 CODE 2 cases in all match. segment_methods.json (CODE 2: 124) and jump_table.json (104 named)
    are regenerated.
- Every CODE 2 callee of the flight loop now has a routine for Leyte except the loop itself.
- Validation: QA and the full suite below.
- Next: the flight loop 0x4fc from the listing (its keys' sparse switch, the event loop, the end-of-
  session switch), with cases.

Cycle 36 of job f19bd982:

- Cycle 34's full suite: 118/118 in 720 s.
- CPU (user question): the load was the Hellcats iOS build left running in the iPhone 18 Pro simulator
  (about 140% for over three hours) and the Claude app's renderer. The user stopped the app and shut
  the simulator down. Pending the user's choice, the loop runs lighter: targeted tests (every Leyte
  test) and QA each cycle at nice 15, with the full Godot suite run less often. No full suite this
  cycle.
- The flight loop written from Leyte's bytes (ID02_reconstructed.gd run_flight_session_leyte, 0x4fc,
  word result). It is named apart from the base's refused run_flight_session, which returns nothing.
  - It is 1.0.3's run_flight_session with Leyte's operands. The frame is the original's in memory:
    event -0x22, mouse -0x2a, result -0xa, half the menu bar -4, the two-device flag -0x24, the return
    value -0x26.
  - Covered: the 13-case command-key switch (T/t, 1..5, Q/q, S/s, E/e); key, update and activate
    event handling; the ask hook (-0x10c8); the frame counter -0x1582; the end-of-flight switch on
    -1..10 (conclude JT $10a, next mission JT $16a, paused JT $8a, quit 0x35e2).
  - Leyte's addition: with two devices the step hook runs once per device record, each made current
    with its screen globals.
  - 8 whole-session cases (command-Q; the hook's 2 then 5; concluded with answer 1, and with 3 flying
    again; a plain key with two devices, paused; an update mid-flight; the first show with T and 3,
    the pilot status written back; the ask hook with an activate). 125 CODE 2 cases in all match.
- A fixture gap fixed: the CODE 2 case generator wired only CODE 1's dense switch ($3a). The flight
  loop's sparse switch ($32) ran into an empty slot (the emulator's step limit). Slots $2a / $32 / $3a
  are now all wired, as the jump table has them.
- A runtime divergence found and fixed: GetNamedResource resolved names from 1.0.3's
  resource_names.json even for Leyte. Leyte's 'snd ' "Missle" was not found, so load_game_sounds failed
  and the sound error dialog (DLOG 139) appeared, which the original would not show.
  - tools/leyte/extract_resources.py now writes each resource's name into the Godot mirror (39 named).
  - LeyteResources builds its name table from there (case-insensitive, as before). 1.0.3 is unchanged.
  - The extractor's sources outputs were rewritten with identical bytes (--check PASS, 238 files);
    nothing under sources/ changed in content.
- Probe (synthetic input only: Return for each ModalDialog, a click for a Button wait, Command-Q in
  the flight loop): main → protection → overlay → Duty Roster (OK) → Select a Mission (OK) → the
  flight loop. The session begins and all sounds load. The loop runs its frames until Command-Q.
  - Then the end of flight reaches quit_application 0x35e2, which has no routine yet.
  - The redraw's 0xab4 is also met.
  - Overlay hooks met and reported missing: 0, 1, 2, 4, 8, 9, 10, 11, 13, 14 and 21; also JT $192
    (CODE 3 +0x5eb4).
- Validation: every Leyte test and QA at low priority, below.
- Next: quit_application (0x35e2) and 0xab4 from Leyte's bytes; then the overlay hooks a frame calls,
  starting with the step hook's routines.

Cycle 37 of job f19bd982:

- Cycle 36's validation (lighter mode): all 13 Leyte tests OK, QA PASS.
- Three more CODE 2 routines from Leyte's bytes (ID02_reconstructed.gd). Each is a 1.0.3 routine its
  runtime writes, with Leyte's operands:
  - save_changed_settings (0x34d8; 1.0.3 0x2da0): flag -0x1408, settings handle -0x142a.
  - quit_application (0x35e2; 1.0.3 0x2eaa): confirm JT $9a must answer 2; with changed settings, ask
    JT $132 (1 saves, 2 cancels); the window -0x1548 hidden when shown (-0x1544); ExitToShell.
  - redraw_panel_readouts_locked (0xab4; 1.0.3 0xa26): with session handles -0x1270 / -0x1256, the
    readouts hook -0x10b4 between the lock 0x9e8 and the unlock 0xa88.
  - 10 cases (save changed / not; 1.0.3's five quit cases; three readout cases). 135 CODE 2 cases in
    all match.
- A registration gap fixed: confirm_end_mission (CODE 4 +0x566) was registered by offset only, so
  quit_application's JT $9a was reported missing and answered 0, cancelling the quit. Slot $9a now
  reaches the same routine.
- Tool note: tools/leyte/switch_listing.py's END is an absolute address. Two empty listings earlier in
  this cycle came from an END below START, not from the shell.
- Probe (synthetic input only: Return for each ModalDialog and alert, a click for a Button wait,
  Command-Q in flight): a whole session runs from launch to quit.
  - main → copy protection → overlay load → Duty Roster (OK) → Select a Mission (OK) → the flight
    loop's frames → Command-Q → the end of flight → quit_application → CautionAlert 257 "Save
    Changes" (Save, written to the probe's temporary root) → ExitToShell.
  - No assertion stop remains on this path. Still reported missing on it: JT $192 (CODE 3 +0x5eb4) and
    the overlay's hooks (0, 1, 2, 4, 8, 9, 10, 11, 13, 14, 21).
  - The probe now answers alerts and ends at its timeout after ExitToShell, which parks the machine.
- Not playable: a frame does nothing yet. The overlay's simulation and rendering hooks are missing, so
  no aircraft, world or panel is drawn.
- Validation: every Leyte test and QA at low priority, below.
- Next: JT $192 (CODE 3 +0x5eb4), then the overlay hooks a frame calls, step hook first. The overlay's
  simulation is the large body of work left before gameplay.

Cycle 38 of job f19bd982:

- Cycle 37's 13 Leyte tests: OK (its QA below).
- JT $192 (CODE 3 +0x5eb4) identified. The A5 map pairs it with 1.0.3's slot $17a, which reaches
  CODE 3 +0x5cc4 prepare_instrument_panel (the cockpit panel made ready, ported in 1.0.3's ID03.gd).
  run_segment3_entry_and_unload_a5safe calls it when a flight session begins.
  - Leyte's version is that routine extended:
    - its panel cache is 'HEL3' 127 of 0x194-byte entries (0x650 bytes when made; 1.0.3: 'HEL2' 127,
      0x160-byte entries, 0x580);
    - it does not save the current resource file or UseResFile(A5 -0x1002) first, as 1.0.3 does;
    - its globals move as elsewhere (the panel scale -0x151c / -0x1518, the overlay's sine / cosine
      -0x10a8 / -0x10a4 into -0x21a2 / -0x219e, the cache handle -0x1430);
    - it copies a larger set of tables from the matching entry.
  - Its callees include compute_panel_screen_addresses 0x5a50, open_cport_on_buffer 0x5e26 and
    close_port_glue 0x5ea6 (same instructions as 1.0.3's) and the instrument drawing CODE 3 holds.
- Not written this cycle: a faithful translation needs the routine's full extent (it has several exits,
  and an RTS cut runs past it) and its callees' Leyte status. A few hundred instructions.
- Lighter mode continues. No code changed this cycle, so its validation is cycle 37's.
- Next: delimit prepare_instrument_panel's Leyte routine (0x5eb4 to its exit), list the CODE 3 callees
  it reaches and their status, then write it in a CODE 3 reconstruction (ID03_reconstructed.gd, with a
  case generator after the CODE 2 one).

Cycle 39 of job f19bd982:

- Cycle 37's run: 13 Leyte tests OK, QA PASS. No code changed in cycle 38.
- prepare_instrument_panel delimited and compared (evidence work; no code changed):
  - Boundaries from the code itself (an unlk/rts followed by a link prologue): 1.0.3 0x5cc4..0x6c1c
    (one 0xf58-byte function, as the inventory has it), Leyte 0x5eb4..0x6e5e (0xfaa bytes). Leyte's
    0x6e5e is presumably remap_cached_panel_colours (1.0.3 0x6c1c); the correspondence finds neither.
  - Instruction diff (tools/leyte/switch_listing.py; 1175 against 1198 lines). With A5 operands,
    PC targets, record fields and the record size masked, 43 lines differ. All of Leyte's changes:
    1. no CurResFile save / UseResFile(A5 -0x1002) at entry and the restore at exit;
    2. the cache made 0x650 bytes (1.0.3 0x580), its entries 0x194 (1.0.3 0x160), type 'HEL3' (1.0.3
       'HEL2'); Leyte's CODE 3 holds no 'HEL2' at all;
    3. one more table of 13 longs kept per cache entry, loaded and saved with the others;
    4. one more instrument drawn: a call with item 10, then one with (0x17c, 0x122, 7, its result,
       0x37f, -0x2a8); and an immediate 0x474 becomes 0x4e2.
  - Its callees in 1.0.3's port, in the Leyte CODE 3 port: compute_panel_screen_addresses,
    open_cport_on_buffer, close_port_glue and make_inset_face_oval_region are kept.
  - Found: the generated Leyte ID03.gd keeps 1.0.3's private helpers _entry, _load_cached_panel,
    _draw_new_panel and _lend_panel_bitmap. They carry 1.0.3's PANEL_TYPE 'HEL2', PANEL_ENTRY_SIZE
    0x160 and the cache handle A5 -0x13b2, which are wrong for Leyte (-0x1430). No kept public
    function reaches them: they are dead code, called only by the refused prepare_instrument_panel.
    Not changed: noted as a generator hygiene gap. Private helpers are kept without a reachability or
    moved-constant test, which the overlay generator does apply.
- Plan for the write: a CODE 3 reconstruction (godot/original/leyte/port/ID03_reconstructed.gd, the
  Leyte profile's port 3). It holds prepare_instrument_panel with Leyte's own helper copies, since GDScript
  constants cannot be overridden (the cache as 'HEL3' / 0x194 / -0x1430, the extra table and
  instrument, no UseResFile). It also gets a case generator after the CODE 2 one, on Leyte's ID03.bin,
  and a reachability rule for the host-port generator's private helpers.

Cycle 40 of job f19bd982:

- A CODE 3 reconstruction, godot/original/leyte/port/ID03_reconstructed.gd. The Leyte profile's port 3
  and reconstructed.py's PORT_FILES now include it.
  - prepare_instrument_panel (0x5eb4, slot $192) from Leyte's bytes. It is 1.0.3's with Leyte's
    operands and the differences found in cycle 39: no UseResFile around it; the cache 'HEL3' / 0x194
    / 0x650 at -0x1430.
  - Its cached-entry path is written with Leyte's own helpers (_leyte_entry, _leyte_load_cached_panel,
    _leyte_lend_panel_bitmap and the 9-record LEYTE_PANEL_RECORDS), not the generated port's stale
    1.0.3 helpers.
  - The A5 pairs were read positionally from the two aligned listings: 57 consistent. One apparent
    two-way pair (-0x1186) is the record that moves from +0x12a to +0x15e when Leyte's 13-long table
    takes +0x12a.
  - The records were read from Leyte's own copies. The same extraction reproduces 1.0.3's
    PANEL_RECORDS exactly.
  - Its verified callees (0x5a50, 0x5e26, 0x5ea6, 0x3832) and the routines still to write (0x5be4
    release_panel_bitmaps, 0x741e remap_panel_colours_identity) are called by offset.
  - The new-panel path (0x637a..0x6df0, with its extra instrument and the three refused drawers) is not
    written yet. Its branch asserts with that message: no 1.0.3 code runs there.
- tools/leyte/gen_leyte_id03_reconstructed_cases.py: the CODE 2 generator's runner on Leyte's ID03.bin
  with its own stand-ins. 4 cases (the current entry at depth 8; a stored entry loaded at depth 4; the
  current entry after a near miss at depth 1; the cache made), all matching
  (tests/leyte/port/test_leyte_id03_reconstructed.gd, in run_all; --check in QA).
  segment_methods.json (CODE 3: 39) and jump_table.json (106 named) are regenerated.
- Probe: the panel set-up now runs in the real start. It makes the 'HEL3' cache, finds no entry for
  this display on a first start, and stops at the new-panel path's assertion. The session then goes on
  to the Save alert and ExitToShell.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: the new-panel path, with its extra instrument, draw_panel_section_with_numbered_boxes,
  draw_percent_scale_gauge, draw_split_display_box, store_panel_bitmaps_as_resources (0x5ca6),
  release_panel_bitmaps (0x5be4) and the colour remap (0x741e / 0x6e5e).

Cycle 41 of job f19bd982:

- Cycle 40's validation: all 14 Leyte tests OK, QA PASS.
- Three more CODE 3 routines from Leyte's bytes (ID03_reconstructed.gd), each 1.0.3's with Leyte's
  operands. Their A5 pairs were read from the aligned listings, all consistent:
  - release_panel_bitmaps (0x5be4; 1.0.3 0x59f4, the runtime's id03_59f4): handles -0x1270 / -0x1256
    / -0x124e / -0x132a; the entry *(A5 -0x1430) + 0x194 * A5 -0x21a4; DisposHandle $26a.
  - store_panel_bitmaps_as_resources (0x5ca6; 1.0.3 0x5ab6, refused for Leyte on the record size):
    'HEL3', entry A5 -0x142c, names from -0xe48, file -0x1042, failure flag -0x151e.
  - remap_panel_colours_identity (0x741e; 1.0.3 0x71a2): the colours A5 -0x13d2; it calls
    remap_cached_panel_colours (0x6e5e) by offset.
  - 7 cases (release: all held / two / none, with the resource flag set or not; store: first, over
    old ones, an add failing; the identity remap). 11 CODE 3 cases in all match.
    segment_methods.json (CODE 3: 42) is regenerated.
- remap_cached_panel_colours (Leyte 0x6e5e; 1.0.3 0x6c1c): 537 lines against 517. Its dense switch
  on the depth (4 / 8) is the same. Leyte adds two colour lookups (a call with (2, 2, 2) and one with
  (0, 0, 0), each mapped through the table into an A5 global) in one depth's branch. To write next.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: remap_cached_panel_colours, then the new-panel path (0x637a..0x6df0) with its extra instrument
  and the three refused drawers.

Cycle 42 of job f19bd982:

- Cycle 41's validation: all Leyte tests OK, QA PASS.
- remap_cached_panel_colours (CODE 3 0x6e5e) written from Leyte's bytes (ID03_reconstructed.gd). It
  is 1.0.3's 0x6c1c with Leyte's operands (28 pairs, all consistent, from the aligned listings) and
  two more cached colours: A5 -0x11d0 = (0, 0, 0) and -0x11ce = (2, 2, 2), after -0x1204.
  - Its order was checked against Leyte's write sequence. The 1-bit refresh is JT $49a; the generated
    port's JT_FLUSH_PANEL_1BIT keeps 1.0.3's $482, used only by refused code.
  - 4 cases (a configuration mismatch; depth 1; depth 8 and depth 4 over a synthetic panel bitmap and
    gyro card, every cached colour refreshed). 15 CODE 3 cases in all match. segment_methods.json
    (CODE 3: 43) and jump_table.json (107 named) are regenerated.
- The panel cache's routines are now all written for Leyte: the set-up's found-entry path, load,
  release, store and both colour remaps.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: the new-panel path (0x637a..0x6df0): the least recently used entry chosen, the "creating panel"
  dialog, the frame and edges, the instruments (63 of 66 kept; draw_panel_section_with_numbered_boxes,
  draw_percent_scale_gauge and draw_split_display_box to write, plus Leyte's extra instrument), the
  records saved and the bitmaps stored.

Cycle 43 of job f19bd982:

- Cycle 42's validation: all Leyte tests OK, QA PASS.
- The three refused instrument drawers, examined:
  - draw_panel_section_with_numbered_boxes 0x4304, draw_percent_scale_gauge 0x4790 and
    draw_split_display_box 0x4b32 have instructions aligned with 1.0.3's (360 / 282 / 85 lines, no
    conflicting pair). They were refused only for A5 globals the map cannot pair: -0x11a6, -0x1182,
    -0x1198 and -0x1190.
  - Those never appear as operands. Each is the second long of an 8-byte rect the code writes through
    LEA base(A5) and two MOVE.L (A0)+ (bases -0x11aa, -0x1186, -0x119c, -0x1194, each confirmed in the
    listing). So each is its base's Leyte pair + 4: -0x1220, -0x11c8, -0x1212, -0x120a.
  - The three bodies, translated mechanically from 1.0.3's port through the per-function pairs, are in
    ID03_reconstructed.gd. Every named constant they use already holds Leyte's value in the generated
    port.
  - NOT YET CHECKED on the emulator, and not registered by offset. Their cases reach
    draw_vector_number, whose glyph walk reads stroke data from the A5 world's initialised data (CODE 3
    0x9de..0xa1e, to an end mark 'c'). The case runner does not load it: zeroed, the walk never ends
    (the emulator's step limit). They are reached only from the new-panel path, which is not written.
  - Case-runner groundwork kept: polygon trap stand-ins (OpenPoly with a synthetic handle, Close / Kill
    / Frame / Paint / Fill / Erase / Invert), JT $402, and digest (bounded) logs for MoveTo / LineTo,
    which a vector-drawn digit calls more often than a 0x400-byte log holds.
- The A5 map, measured (not changed): admitting "same_instructions" matches to a5_map.py's evidence, as
  overlay_map.py already does, raises the mapped pairs from 162 to 376 (globals 115 -> 299). It changes
  or loses none and adds no conflict (still 1: $28a). Not applied: it regenerates every host port and
  their fixtures, a change to make with a full suite.
- Validation (lighter mode): every Leyte test and QA at low priority, below. The CODE 3 fixture is
  regenerated for the added stand-ins: 15 cases match.
- Next: load the A5 world's initialised data (Leyte's DATA, as the runtime's init_a5_world does) in the
  case runner, so the drawers can be checked. Then the new-panel path. The A5 map widening is a
  candidate for a cycle with a full suite.

Cycle 44 of job f19bd982:

- Cycle 43's validation: all Leyte tests OK, QA PASS.
- The CODE 3 case runner now starts drawing cases from Leyte's initialised A5 world
  (gen_leyte_id03_reconstructed_cases.py a5_world):
  - DATA 0's words expanded from A5 -0x2322 up to A5, each zero word followed by ZERO 0's run of zero
    bytes, then DREL 0's cells relocated by A5, as mac_machine.gd init_a5_world builds it. 1473 writes,
    ending exactly at A5.
  - The writes are part of each case, so both sides start from it with no change to the replay helper.
  - With it, the vector digits' stroke walk ends, and the three drawers' cases run on Leyte's code.
- Two stand-in errors fixed on the way, both of which had made the emulator side meaningless:
  - FillPoly takes (poly, pattern) and InvertPoly one handle; their argument sizes had been swapped.
  - JT $42a (CODE 2 get_blended_fill_pattern) had no stand-in, so the emulator ran the zeros in its
    unloaded slot. It now answers a synthetic pattern address.
- The drawers are now actually checked, and they do not match Leyte's code yet:
  - in the numbered section, the original writes the box rects from A5 -0x1240 and the port does not;
  - the split box differs after its first writes.
  - The cause (a translated drawer or a kept helper it calls) is not found yet. The six cases are kept
    out of the fixture until it is, and the file says so (NOT YET MATCHING).
- 15 CODE 3 cases match (the drawers' excluded).
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: compare the drawer cases write by write (the full change lists, not the first six), find the
  diverging instruction and fix it. Then the new-panel path.

Cycle 45 of job f19bd982:

- Cycle 44's validation: all Leyte tests OK, QA PASS.
- The drawer mismatch found, write by write (the replay loop mirrored in a scratch script, the full
  change lists diffed; the scratch files removed afterwards). Two causes, neither in Leyte's evidence:
  - draw_panel_section_with_numbered_boxes: the port wrote the box rects at 1.0.3's A5 -0x11c6 / -0x11be
    / -0x11b6. The 1.0.3 body holds them as bare numbers in a list, wrapped in _g() only later, so the
    mechanical _g(literal) substitution missed them. Now Leyte's -0x1240 / -0x1238 / -0x1230, which
    are their positional pairs. A scan of the three drawers finds no other bare A5 literal.
  - draw_split_display_box: the case's arguments were wrong, not the drawer. Its third argument is an
    unused long at $c(a6) (the words at 8, $a, $10, $12), and the case had pushed five words. The masked
    instructions are identical, so no constant differs.
- 21 CODE 3 cases match, the six drawer cases included (depths 8, 4, 1 for each).
  draw_panel_section_with_numbered_boxes (0x4304) and draw_split_display_box (0x4b32) are registered
  (segment_methods.json CODE 3: 45). draw_percent_scale_gauge is written but not yet checked: it builds
  offscreen ports the cases do not stand in for yet. The file says which is which.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: draw_percent_scale_gauge's cases (stand-ins for new_offscreen_bw_port and friends), then the
  new-panel path.

Cycle 46 of job f19bd982:

- Cycle 45's validation: all Leyte tests OK, QA PASS.
- draw_percent_scale_gauge (CODE 3 0x4790) checked against Leyte's code.
  - Six word arguments at 8(a6)..$12(a6), in the port's order.
  - Its kept callees run as they are on both sides: new_offscreen_bw_port, dispose_offscreen_bw_port,
    antialias_downsample_bw_3x, draw_vector_number, nearest_palette_index_rgb.
  - Their calls are stood in for: NewPtr $242 (answering a synthetic bit buffer, then a port record),
    DisposPtr $24a, OpenPort, SetPortBits, PortSize, RectRgn, ClosePort, PenPat, SetOrigin, and Line
    (a digest log).
  - 3 cases (depths 8, 4, 1), matching. All three formerly refused instrument drawers now match
    Leyte's code. The gauge is registered (segment_methods.json CODE 3: 46); 24 CODE 3 cases in all.
- Every routine the new-panel path calls is now written for Leyte: the 63 kept instrument functions,
  the three drawers, store / release / remap.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: the new-panel path itself (CODE 3 0x637a..0x6df0), from Leyte's listing against 1.0.3's
  _draw_new_panel / _draw_panel_instruments. Leyte's 13-long record and its one extra instrument
  (the call with item 10, then (0x17c, 0x122, 7, its result, 0x37f, -0x2a8)).

Cycle 47 of job f19bd982:

- Cycle 46's validation: all Leyte tests OK, QA PASS.
- Leyte's new-panel path read against 1.0.3's _draw_new_panel / _draw_panel_instruments. Leyte's
  instrument calls differ in two places:
  - the split display box is drawn at a = 0x4e2 (1.0.3: 0x474);
  - after it, Leyte draws one more instrument: CODE 3 0x4c42 with (0x17c, 0x122, 7 << 16 | scale, 0x37f,
    -0x2a8). In 1.0.3, 0x4c42 is draw_flaps_indicator, which Leyte moves to 0x4e32. Leyte's 0x4c42
    (151 instructions) resembles neither drawer (instruction similarity 0.27 / 0.25): it is Leyte's own.
- That instrument written from Leyte's bytes as draw_six_cell_display_box (ID03_reconstructed.gd,
  named for what it draws).
  - A box placed as the split box places one, painted in colour 0, framed in colour 2, inset by 3.
  - Six cells, three each way from the middle and 4 units lower each, kept as rects at A5 -0x1200 + 8 *
    slot. That is the start of the 13-long table Leyte adds to each cache entry, which explains it.
  - A cell step of 6 or more takes a 1-unit inset and a gap of 3, else a gap of 2.
  - 6 cases (as the panel calls it, and a narrow box for the small-step branch; depths 8, 4, 1)
    match Leyte's code at the first replay. It is registered (segment_methods.json CODE 3: 47); 30 CODE
    3 cases in all.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: _leyte_draw_new_panel and _leyte_draw_panel_instruments from Leyte's listing. 1.0.3's structure
  with the pairs read in cycle 40, the split box's 0x4e2, the six-cell box, Leyte's records
  (LEYTE_PANEL_RECORDS) saved, NewHandleClear $262, and every JT constant checked against the listing.

Cycle 48 of job f19bd982:

- Cycle 47's validation: all Leyte tests OK, QA PASS.
- The new-panel path written (ID03_reconstructed.gd _leyte_draw_new_panel and
  _leyte_draw_panel_instruments, replacing the stopping stub).
  - 1.0.3's structure with the listing's pairs and these changes: the split box at a = 0x4e2; Leyte's
    six-cell box after it; Leyte's records (LEYTE_PANEL_RECORDS) saved.
  - The instrument list was checked call by call against Leyte's bytes (targets and immediates).
  - Every constant was checked against the listing. Four of the generated port's are stale 1.0.3
    numbers, used explicitly from the listing instead: JT_CREATING_PANEL_DIALOG $49a -> $4f2,
    JT_NEW_HANDLE $24a -> $25a, JT_NEW_HANDLE_CLEAR $252 -> $262, JT_VIEW_FRAME_OUTLINE $3ba -> $36a.
    The private helpers it calls (_corner_arc, _line_to, _panel_edge, _panel_colour, _h, _v,
    scale_design_480_min10) hold no unpaired A5 literal and no stale constant.
- Case-runner work for a whole new panel (gen_leyte_id03_reconstructed_cases.py new_panel_cases):
  - QuickDraw stand-ins for every trap CODE 3 uses, signatures from quickdraw_traps.gd: regions, ovals,
    clip, text, GetFontInfo, StringWidth, OffsetRect, PtInRgn, CopyBits, OpenCPort.
  - The CODE 2 colour-port slots $34a / $44a stood in for; the overlay's cosine / sine (0x3000 /
    0x3100) and point rotation (A5 -0x10a0, 0x3200) stood in for.
  - The four bitmap handles given master pointers, and an 80 000 000-step limit for this generator
    (the 3x anti-aliased labels).
  - New in the shared runner (gen_leyte_id02_reconstructed_cases.py): a case may ask for CODE 2's own
    routines behind slots (code2_slots: CODE 2 loaded at 0x10000). labs (JT $522, CODE 2 +0x1e) and
    memset ($53a, +0x2b0) run as Leyte's code, since the port computes both inline. CODE 2's own
    fixture is unchanged (--check PASS).
  - Found on the way, each of which had made a run's emulator side silently wrong: calls through
    unwired slots ran the zeros in them (JT $522), and an unset code pointer (the rotation) jumped to
    address 0.
- Result: the whole new panel now runs on Leyte's code (16650 changed bytes) and on the port. They
  agree on the first writes and differ further in. The case is kept out of the fixture and the path is
  marked NOT YET MATCHING. 30 CODE 3 cases match.
- Probe: the start now draws the new panel with no assertion stop. JT $36a (the view-frame outline,
  CODE 2 +0x19c0) is reported missing. The session runs on through the flight loop to the Save alert
  and ExitToShell.
- Validation (lighter mode): every Leyte test and QA at low priority, below.
- Next: the new-panel case write by write (the full change lists), the first divergence found and
  fixed. Then JT $36a, the view-frame outline.

Cycle 49 of job f19bd982:

- Cycle 48's Leyte tests: all OK (its QA still running at the time of writing).
- The new-panel case compared write by write (scratch fixture and dump script; nothing left in the
  project): 345 differing bytes, nearly all in the stand-ins' call logs.
  - The rounding slots locate it: JT $372 (round_v) is called 193 times by the port and 192 by Leyte's
    code, JT $3a2 (round_h) 212 against 211. Both agree call for call up to #166 / #181, then the port
    has one extra pair, and they agree again shifted by one.
  - The extra calls are inside draw_artificial_horizon (CODE 3 0x38fc, "same instructions"). The port
    rounds (0x334, 0x366) in the horizon itself (line 1171), then again in make_inset_face_oval_region
    (0x3832, "moved"). Leyte's code shows the horizon's round_v of 0x334 followed directly by 0xee84 and
    then 0x49c.
  - Leyte's 0x3832 does round both coordinates again (its listing), so the duplicate itself is the
    original's. What differs is which values reach those calls between the horizon and 0x3832: possibly
    the order of the coordinate arguments the horizon passes (the port passes (rect, size, v, h); Leyte
    pushes its $10 then $e), or a difference in 0x3832's own reading of them.
  - Not resolved this cycle. The shared kept functions are 1.0.3's port, so the finding may concern 1.0.3's
    port as well: to be checked against 1.0.3's own code before any change.
- No project code changed this cycle; cycle 48's validation stands.
- Next: compare draw_artificial_horizon and make_inset_face_oval_region instruction by instruction with
  their ports, in both editions, at the call that takes (rect, size, $e, $10). Fix whichever side is
  wrong (if it is 1.0.3's port, with 1.0.3's own cases), then restore the new-panel case.

Cycle 50 of job f19bd982:

- Cycle 48's run: all Leyte tests OK, QA PASS. Cycle 49 changed no project code.
- The new-panel divergence resolved. Three causes, one of them a fidelity error in 1.0.3's own port:
  1. Case artefact: draw_artificial_horizon calls make_inset_face_oval_region (CODE 3 0x3832) directly
     on both sides. The emulator met its stand-in (added for the set-up's _local call), the port ran it.
     A case may now list routines that run as they are ("real", gen_leyte_id02_reconstructed_cases.py
     run(); CODE 2's fixture unchanged), and the new-panel case runs 0x3832 so.
  2. Translation: _leyte_draw_new_panel called remap_panel_colours_identity as a method, where Leyte's
     code calls 0x741e like any local. It now goes through _local, as the original does.
  3. 1.0.3's port: draw_instrument (CODE 3 0x3c02, in 1.0.3 and Leyte alike) gave every instrument a
     five-word argument area. The gyro (kind 8) and horizon (kind 9) calls push six words, the card
     flag first (MOVE.W #1, ...; LEA $c(A7)), seen in both editions' bytes. So the port entered both
     instruments 2 bytes high: their frames' temporary rects sat 2 bytes off (no global or result
     changed; the case's logged rect pointers showed it).
     - Fixed in godot/original/port/ID03.gd: a7 = entry - 4 - 0xa - 0xc - 4 for kinds 8 and 9.
     - The Leyte host ports are regenerated (Leyte's ID03.gd carries the fix).
     - 1.0.3's own replays pass, id03_gauges_cases.json 59/59 included.
  - Also a stand-in sizing fix: TextFace's style argument is pushed as a word (MOVE.W #1), and the
    stand-in had read one byte, the word's high byte.
- Result: 31 CODE 3 cases match, the whole new panel included. Leyte's cockpit panel is drawn, every
  instrument, exactly as Leyte's code draws it, the six-cell box and the extra records included. The
  new-panel path is no longer marked NOT YET MATCHING.
- Validation: the Original port changed, so QA and the full Godot suite this cycle (low priority),
  below.
- Next: JT $36a (the view-frame outline, CODE 2 +0x19c0), the start's last reported host gap on the
  panel path; then the overlay's frame hooks.

Cycle 51 of job f19bd982:

- Cycle 50's QA: PASS (its full suite below).
- JT $36a (CODE 2 +0x19c0) is 1.0.3's draw_view_frame_outline (0x16aa, "same instructions"). It was
  refused for "A5 globals not paired: -0xc80, -0x424", which are the view frame's design coordinates
  (-3200 and -1060 units, passed to design_x_to_screen), not A5 offsets.
  - gen_host_ports.py treats any literal in -0x2400..-0x200 as an A5 global. A literal a
    design-coordinate converter takes directly (design_x_to_screen, design_y_to_screen, _h, _v) is now
    left out of that scan (DESIGN_COORDINATE). It is a targeted exception, not a narrowing of the rule.
  - Regenerated: no longer refused are draw_view_frame_outline and the two blitters refused only
    through it (blit_image_clipped_to_region and its a5safe variant). None is newly refused.
    segment_methods.json (CODE 2: 131) and jump_table.json (109 named) are regenerated.
- Probe: the start reports no missing host routine any more. Launch → copy protection → overlay →
  Duty Roster → Select a Mission → the complete cockpit panel → the flight loop → Command-Q → Save →
  ExitToShell. What the start still reports missing is the overlay's frame hooks.
- Validation: the Leyte tests and QA after cycle 50's full suite (below).
- Next: the overlay's frame hooks, in the order a frame calls them (the step hook first). That is
  the gate to anything drawn in the view.

Cycle 52 of job f19bd982:

- Cycle 51's run: all Leyte tests OK, QA PASS. Cycle 50's full suite: 119/119 in 599 s.
- The overlay hooks the start reports missing, ranked by what blocks them (closure reached, unproven
  in it). Nearest: hook 8 allocate_session_buffers (2 unproven); hook 11 unlock_session_buffers
  (3); hooks 1 and 9 through reset_span_row_lists. Farthest: hook 10 overlay_frame_step (121
  unproven) and hook 14 process_flight_input_and_outcome (87).
- A real defect in the generated Leyte overlay port, fixed in tools/leyte/gen_overlay_port.py:
  - Kept functions reaching the host through _host_memory_call(entry, depth, offset, ...) kept 1.0.3's
    host-world offsets. Leyte's world has them 0x3c further on (0x2c6 -> 0x302, 0x2ce -> 0x30a, 0x2de
    -> 0x31a, 0x2d2 -> 0x30e; the function's own proven pairs). The rewrite reached only "+ 0x.."
    forms, so ensure_handle_allocated and lock_handle_nopurge would have called the wrong host
    services. That offset argument is now rewritten through the function's pairs.
  - The cycle 22 guard is extended: a moved field still written as a bare hex literal anywhere else
    refuses the function. It refuses exactly one, lock_overlay_buffers (0xd8, 0x192, 0x1ac, 0x1b4
    bare, also wrong for Leyte). Kept functions: 77 -> 76, helpers unchanged.
- Hook 8 written (pacific_conflict_reconstructed.gd): allocate_session_buffers (0x150c). It is 1.0.3's
  0xe80 without the free-memory test: a fixed 0x8340-byte entry handle (1200 entries) and 30 rows,
  with Leyte's addresses (flag 0x27184, handles 0x20f30 / 0x20f34 / 0x20f44).
  - The reconstructed overlay now registers its routines in code_table (_init), so the loader links
    the hook.
  - 4 cases (first allocation; handles in place, two screens; depth 1; no entry flag), the host
    world's three services stood in for, match. They exercise the corrected ensure_handle_allocated
    against Leyte's code. 12 reconstructed overlay cases in all.
- Validation: the overlay case fixture regenerated (the kept set changed), every Leyte test and QA at
  low priority, below.
- Next: hook 11 (unlock_session_buffers, through unlock_handle_purgeable), then reset_span_row_lists
  (unblocking hooks 1 and 9), then lock_overlay_buffers by hand.

Cycle 53 of job f19bd982:

- Hook 11 written (pacific_conflict_reconstructed.gd unlock_session_buffers, 0x16dc), through
  unlock_handle_purgeable (0x149e: 1.0.3's 0xe32 with Leyte's host slots +0x312 / +0x316; the
  correspondence had placed it on 0x1476, and Ghidra lists no function at 0x149e). 4 cases.
- reset_span_row_lists found in Leyte at 0x7eac (the correspondence: "not found"). Leyte walks a pointer
  over the data pointers 0x20f5c + 4 i instead of 1.0.3's index; same semantics, Leyte's addresses (view
  rows 0x21010, width 0x21012, fill colour 0x23fd0). Written and registered; 7 cases.
- Validation: overlay reconstructed fixture 23 cases, all matching; every Leyte test OK, QA PASS.

Cycle 54 of job f19bd982:

- Hook 1 written: update_view_clip_bounds (0x7f12), 1.0.3's 0x6ed6 instruction for instruction with
  Leyte's addresses (read from the disassembly: palette_changed 0x23fd2, clip 0x20b2e / 0x27014 /
  0x27012, view mode 0x23d88, inset 0x2700e, projection tables 0x2703c / 0x27024 -> 0x27050 / 0x2704e).
  7 cases (view modes, inset flag, odd / negative rows, the mode-6 entry).
- Hook 9 written: lock_session_buffers (0x15da, 1.0.3's 0xf72), through lock_handle_nopurge (0x1476,
  host slots +0x30e / +0x31a) and the 24-bit mask (host word +0x76). 5 cases (entry flag, depth 1,
  multi-screen, masked pointers, the helper alone).
- The overlay case runner gained the host service +0x30e.
- Overlay reconstructed fixture: 35 cases, all matching Leyte's code.
- Probe with hooks 1 / 9 live: no assertion stop; the session still runs through the flight loop to the
  Save alert and ExitToShell. Hooks reported missing: 0, 2, 4, 10, 12, 13, 14, 15, 16, 17, 21 (12 and
  15..17 newly reached now that 9 runs; 1, 8, 9, 11 written).
- Next: the hooks in the order the start meets them (0, 10, 2, 4, then 12..17 and 21), each found in
  Leyte's bytes and checked against Leyte's code; lock_overlay_buffers by hand.

Cycles 55-56 of job f19bd982:

- Hooks 15 / 16 (sin_fixed14 / cos_fixed14): the generated port already holds them verified (recorded at
  the file offsets 0x23c4 / 0x2426, image 0x23a4 / 0x2406, Leyte's tables 0x1fd94 / 0x1ff94); now
  registered as the hooks. 16 cases with D0 (the case runner records D0 when a case asks).
- Hook 17 (rotate_int3_vecs_by_3ang, 0x31ca) and its tree, compared instruction by instruction with
  1.0.3's (branch targets paired, absolute addresses masked):
  - the hook, build_attitude_matrix_3ang (0x2ce2) and load_attitude_angle_sincos (0x265c): the same;
  - finalize_attitude_matrix_3ang (0x2be8): Leyte's own. 1.0.3 tagged the composer and multiplied
    three matrices through matmul3x3_s16; Leyte writes the product inline from the six sin / cos words
    (0x23d22 .. 0x23d2c). Written from the listing;
  - transform_int3_vecs_by_matrix3 (0x3418): registers reallocated, same arithmetic; with count 0 its
    D0 is the copy loop's end pointer, the frame address entry - 8 (1.0.3: matrix + 0x12).
  - Note: capstone's text drops the index scale of a brief extension word (0x8cdc is a0.l*4); the
    bytes were read to settle it.
- 15 rotation cases (the hook with 3 and 0 vectors over 4 angle sets, the build, three finalize sets
  including the extremes, the transform with 3 / 1 / 0 vectors).
- Hook 13 (begin_flight_for_rank, 0x834c): 1.0.3's 0x72f0 with Leyte's addresses (rank table 0x204dc,
  handler 0xfcf6, host words +0x37a / +0x37c) and one change: the second aim lag (0x2105e) is the
  first halved toward zero (1.0.3 stored the same word twice). Its depth-1 path calls hook 3 (0x4e68,
  refresh_object_colours_for_depth, not ported for Leyte), whose assertion stops there. 4 cases.
- Overlay reconstructed fixture: 70 cases, all matching Leyte's code.
- Validation with hooks 15 / 16 live: every Leyte test OK, QA PASS; the probe reaches ExitToShell.
- Hooks still missing: 0, 2, 4, 10, 12, 14, 21 (1, 8, 9, 11, 13, 15, 16, 17 written).
- Next: the probe with hooks 13 / 17 live; then hook 12 (0xe61e, 1.0.3 0xd23a) and hook 3 by the same
  compare-then-write route.

Cycle 57 of job f19bd982:

- Probe with hook 17 live: it is no longer reported missing; the session still reaches ExitToShell.
- Hook 12 (0xe61e) compared with 1.0.3's written _d23a: the same sequence (the readouts, then with
  0x27010 set the throttle gauge 0xdf20, the radar range buttons 0x1bc0e and the bomb lamp 0xdde6) plus
  one inserted call, 0xde86, a Leyte-only routine switching on player bytes +0x68e / +0x68f. The overlays
  are then marked drawn through 0xe644 (mark_view_overlays_drawn, already verified).
- update_bomb_indicator (0xdde6) written: 1.0.3's with Leyte's fields (object y +8, count byte +0x68d,
  caches 0x2c57a / 0x2c57c, drawn flag 0x2c596, host words +0x204 / +0x200, lamp routine +0x2d6) and a
  fourth argument (sext byte player +0x690). 4 cases; the case runner gained the host routine +0x2d6.
- Overlay reconstructed fixture: 74 cases, all matching Leyte's code.
- Next: the rest of hook 12's tree - update_radar_range_buttons (0x1bc0e, moved fields and one
  immediate), Leyte's 0xde86, update_view_readouts_and_overlays (0xe3ca) - then hook 12 itself.

Cycle 58 of job f19bd982:

- Cycle 57's validation: every Leyte test OK; QA flagged stale wording ("unported" in a case-generator
  docstring), reworded and rerun.
- update_radar_range_buttons (0x1bc0e) written: 1.0.3's 0x17a6c instruction for instruction with Leyte's
  fields (scope +0x1e8 / +0x1e4, colours +0x1de / +0x1e0, buttons +0x1c6 / +0x1ce / +0x1d6, routine
  +0x2c6, globals 0x2cc22 / 0x2cc1e / 0x2cc18). 5 cases.
- update_state_lamp (0xde86, Leyte only) written from the listing: player state byte +0x68e (cached in
  0x2c57e under the drawn flag) through a pc-relative word switch (0..6, refined by +0x68f == 1 for
  states 1 and 3) to a lamp code for host routine +0x2da. A state above 6 skips the switch and passes
  the caller's D1; the port does not hold it and stops there with an assertion. 16 cases.
- Case runner: two more host routines (+0x2c6, +0x2da); call-log slots narrowed to 0x80 bytes with an
  assertion that they stay below the stack guard (the ninth would have fallen inside it).
- Overlay reconstructed fixture: 95 cases, all matching Leyte's code.
- Next: update_view_readouts_and_overlays (0xe3ca), the last piece of hook 12's tree, then hook 12.

Cycle 59 of job f19bd982:

- Cycle 58's validation: QA PASS after the wording fix; the probe with hook 13 live reaches
  ExitToShell (hook 13 no longer reported missing).
- update_view_readouts_and_overlays (0xe3ca) written: 1.0.3's 0xcfec with Leyte's addresses (panels
  flag 0x27010, view 0x23d88, designated entity 0x21070, inset 0x2700e, readout index / caches 0x2c58c /
  0x2c58e, drawn flag 0x2c596, object y +8, climb 0x21468, panel copy offset 0x21564 from host words
  +0xe6 * +0xe4, text routine +0x2b2, c_strlen 0x1f73a entered 0x9c below). The panels path calls the
  same fourteen routines in the same order at Leyte's addresses.
- Hook 12 is NOT registered yet. The panels path reaches routines not yet written for Leyte, and
  registering it now would turn the probe's "missing" note into an assertion stop at the first panel
  frame. Those routines are:
  - sky / ground 0xc77e, heading tape 0xd4b2, frame lines 0xccf6 and the radar scope 0x1bcea (not
    found by the correspondence);
  - the eleven needles (found, but refused by the generator: they pass host-world dial offsets as
    bare literals to _simple_needle).
- 5 readout cases (the inset-4 reset, plain passes, view 7, negative values, the degree sign, an
  unchanged cache). The text routine's stand-in also logs 12 bytes read through its first argument,
  so the printed digits (in the caller's frame, inside the stack guard) are checked too. Log slots
  0xc0 bytes, still below the guard.
- Overlay reconstructed fixture: 100 cases, all matching Leyte's code.
- Next: the needles' host dial offsets (a generator rule for _simple_needle's literal host fields, or
  by hand), then the sky / heading tape / frame lines / radar scope, then register hook 12.

Cycle 60 of job f19bd982:

- Cycle 59's validation: every Leyte test OK, QA PASS; the probe reaches ExitToShell.
- The five simple panel needles written for Leyte (pacific_conflict_reconstructed.gd), each compared
  with 1.0.3's instruction by instruction, every difference listed (not just the first four):
  - airspeed 0xdb2c, direction 0xdbf6, climb 0xdc68, tachometer 0xdce2, manifold 0xdd64;
  - the only differences: host dial words 4 bytes up (+0x100 / +0x150 / +0x120 / +0x140 / +0x130 ->
    +0x104 / +0x154 / +0x124 / +0x144 / +0x134) and the player's rpm / manifold fields 0xe up (+0x66a
    -> +0x678, +0x672 -> +0x680); every immediate identical;
  - _simple_needle written (the generator refused it for the literal dial offsets its callers pass);
    the helpers and draw_gauge_needle (0xd892) were already verified.
- Case runner: the call logs moved to free memory at 0x38000 (0x100 bytes each, asserted below the
  stand-in addresses); the host line routine +0x2aa and the two depth-routine code pointers
  (0x23fc8 / 0x23fcc) stood in and logged.
- 41 needle cases: each needle through the host line routine, through the depth routines with the
  readouts not drawn, and drawn with a changed end, over its input ranges (airspeed's three pieces and
  wrap, negative climb / manifold, heading wrap).
- Overlay reconstructed fixture: 141 cases, all matching Leyte's code.
- Next: the other needles (altimeter 0xd984, flaps 0xdfd0, ammo 0xe1da, fuel 0xe282, gear 0xe088),
  then the sky / heading tape / frame lines / radar scope, then register hook 12.

Cycle 61 of job f19bd982:

- Cycle 60's validation: every Leyte test OK, QA PASS.
- The other five needles written for Leyte (altimeter 0xd984, flaps 0xdfd0, gear 0xe088, ammo 0xe1da,
  fuel 0xe282), each compared with 1.0.3's instruction by instruction, every difference listed:
  - the altimeter's dial 4 bytes up (+0xf0 -> +0xf4) and its host fields as in draw_gauge_needle;
  - the flaps / gear / ammo / fuel dials 0x38 up (e.g. +0x20e -> +0x246);
  - their player bytes moved (+0x67b -> +0x68c, +0x679 -> +0x68a, +0x682 -> +0x696, +0x67d / +0x67e ->
    +0x691 / +0x692) and caches at Leyte's addresses (0x2c572 .. 0x2c58a); every immediate identical.
- abs_int (0x38e8): the same four instructions as 1.0.3's, but the generated port holds an asserting
  stub; the reconstruction now carries the real body (found because the flaps cases stopped on it).
- 51 cases: each needle through the host line routine and the depth routines, drawn and not drawn,
  the cached-value early return, negative flaps / gear, both altimeter hands, both tanks against the
  capacity record.
- Overlay reconstructed fixture: 192 cases, all matching Leyte's code.
- All eleven needles are now written. Hook 12 still waits on four drawers: sky / ground 0xc77e, heading
  tape 0xd4b2, frame lines 0xccf6, radar scope 0x1bcea.
- Next: those four, then register hook 12 and run the probe.

Cycle 62 of job f19bd982:

- Cycle 61's validation: every Leyte test OK, QA PASS.
- Hook 12's four remaining drawing routines compared with 1.0.3's: frame lines 0xccf6 (24 more
  instructions), heading tape 0xd4b2 (restructured, two port-switch helpers 0xd426 / 0xd486 with host
  routines +0x2ba / +0x2be / +0x2c2) and radar scope 0x1bcea (its loops restructured) need reading by
  hand; sky / ground 0xc77e is the same 372 instructions with moved fields.
- draw_sky_and_ground (0xc77e) written, derived from 1.0.3's port by one substitution table checked
  against the full comparison: host geometry +0x18a..+0x19d (1.0.3 +0x186..), colours +0x194 / +0x192,
  depth-1 band routine +0x2b6, cursor hook +0x2fe, panel copy +0xe4 / +0xe6 / +0xe0, Leyte's globals.
  Leyte checks the lower band's bound before its loop instead of jumping to it: the same rows.
  reset_background_rows (0xc728, registers swapped, host +0x190) written too.
- Found on the way: five of the generated port's DAT_ constants still hold 1.0.3's addresses
  (DAT_00020ad8, DAT_00025f34, DAT_00025f3a, DAT_00025f44, DAT_00025f48); the routines that use them stay
  refused there, and the reconstruction spells every global out.
- 12 cases (the row reset with rows, none and no buffer; sky / ground on the colour path through the
  span filler for each horizon order, all sky, all ground and the panels not drawn, and on the depth-1
  path over even / odd frames with and without the cursor hook). The case runner gained the span
  filler pointer (0x26ff0) and host routines +0x2b6 / +0x2fe.
- Overlay reconstructed fixture: 204 cases, all matching Leyte's code.
- Next: frame lines, heading tape and radar scope by hand, then register hook 12 and run the probe.

Cycle 63 of job f19bd982:

- Cycle 62's validation: every Leyte test OK, QA PASS.
- draw_panel_frame_lines (0xccf6) written from the listing: 1.0.3's 0xba0a with every host field 0x38
  up (+0x238..+0x25c -> +0x270..+0x294, line routine +0x2aa). Leyte saves five registers (1.0.3 four),
  so the line-routine calls sit 0x2c / 0x40 below the entry. Its 24 extra instructions lay the screen
  loops out per depth where 1.0.3 shared them: the same writes.
- 6 cases: the line routine at depth 8 and its depth-1 return; the screen at depth 4 and 8 (an odd row
  stride, a negative stride with an odd width); depth 2 drawing nothing. A negative width was left out:
  its unsigned 32-bit count would write about 4 billion pixels, in the original as in the port.
- Overlay reconstructed fixture: 210 cases, all matching Leyte's code.
- Hook 12 now waits on two routines: the heading tape (0xd4b2, with its port helpers 0xd426 / 0xd486)
  and the radar scope (0x1bcea).
- Next: the heading tape, then the radar scope, then register hook 12 and run the probe.

Cycle 64 of job f19bd982:

- Cycle 63's validation: every Leyte test OK, QA PASS.
- update_radar_scope (0x1bcea) written from the listing: 1.0.3's 0x17b48 with Leyte's layout - the
  player position at object +4 / +0xc (1.0.3 +0x18 / +0x20), the scope centre / bounds 4 bytes up
  (+0x1ec / +0x1ee, x +0x1e4..+0x1e8, y +0x1e2..+0x1e6), colour +0xe8, line routine +0x2aa, the track
  lists 0x20744 / 0x20748 with their counts 0x2cc2a / 0x2cc26, blip routines 0x23fc0 / 0x23fc4. Its loops
  are laid out differently (a dbra on the word count where 1.0.3 jumped to its test): the same
  iterations. Stack depths identical.
- 4 cases: a colour screen at heading 0 and turned (last frame's drawn blips erased, visible tracks
  drawn, tracks off each side of the scope cleared), the line-routine screen, empty lists; the list
  swap and counts. The case runner gained the two blip routines.
- Overlay reconstructed fixture: 214 cases, all matching Leyte's code.
- Hook 12's tree now lacks only the heading tape (0xd4b2, with its port helpers 0xd426 / 0xd486).
- Next: the heading tape, then register hook 12 and run the probe.

Cycle 65 of job f19bd982:

- Cycle 64's validation: every Leyte test OK, QA PASS.
- update_heading_tape (0xd4b2) written: read against Leyte's listing step by step, it is 1.0.3's 0xc18a
  (the same frame, stack depths and column walk), so it was derived from 1.0.3's port by one
  substitution table: tape fields 4 bytes up, host routines 0x38 up, colour +0x294, panel copy +0xe0 /
  +0xe4 / +0xe6, cached position 0x2c598, the depth-8 copy at code address 0xd2ae. As in 1.0.3 the
  depth-4 strip branch is unreachable. Its port helpers (0xd426 / 0xd486) were already verified.
- copy_rect_depth8 (0xd2ae): the same 49 instructions as 1.0.3's (the correspondence missed it);
  written with _dbra_word_count (the generated port held asserting stubs for both) and registered at
  its code address.
- Hook 12 (redraw_view_readouts, 0xe61e) written and REGISTERED: the readouts (0xe3ca), then with the
  panels drawn the throttle gauge, radar range buttons, bomb lamp, Leyte's state lamp and the
  overlays-drawn mark. Its whole tree is now on Leyte's code.
- 15 cases: copy_rect_depth8 at each source misalignment and a short unaligned destination; the tape at
  depth 8 (marked-column walk, wrap at the period), depth 4 (host copy, split), unchanged position, the
  panels not drawn (offscreen port, rect fill, cursor hook); hook 12's readouts path and a whole panel
  frame through the host line routine. A stale old position outside the period was replaced with a
  real one (the walk would read past the marks table, in the original as in the port).
- Overlay reconstructed fixture: 229 cases, all matching Leyte's code.
- Probe with hook 12 live, Leyte tests and QA: running (result in the next checkpoint).
- Next: the probe's result (the first divergence, if hook 12's drawing meets one), then hooks 0, 2, 4,
  10, 14, 21.

Cycle 66 of job f19bd982 (survey while the hook-12 probe runs):

- The remaining hooks all reach the object / entity system, whose record layout Leyte changed. The
  comparisons so far (hook 4's tree, run_simulation_tick 0x5192):
  - dispatch_entity_tick (0x5152, 1.0.3 0x4484): controller at entity +0 (1.0.3 +8), flags +0x14
    (1.0.3 +4), and the controller type hidden unless host +0xb0 is 8 (1.0.3 5);
  - clear_pending_entity_slots (0x6c6a): the same, slot table 0x2144c;
  - dispatch_entity_cleanup (0x60dc): child index word +0x10 (1.0.3 byte +1), type byte +0x28 (1.0.3
    +0), flags +0x14, stamp +0x3e (1.0.3 +0x3c), frame counter 0x21020;
  - retire_entity (0x603a): child indices words +0x10 / +0x12 (1.0.3 bytes +1 / +2), scene root
    0x23c2c (+0x32 child table, 1.0.3 +0x30), child window 0x2107a / 0x21078, shot controller 0x21068,
    and a new guard: the scene root's child-table entry is cleared only when it is this object;
  - apply_entity_mission_rates (0x4fce, 114 instructions against 1.0.3's 100) still to read.
- Hooks 2 and 21 reach deeper trees in the same system (object records with the type at +0x28, the
  child index as a word).
- Next: the probe's result; then hook 4's tree from its leaves up (retire, cleanup, mission rates,
  tick, slots, then run_simulation_tick), each read by hand against Leyte's listing.
- Probe with hook 12 registered: no assertion stop; hook 12 is no longer reported missing and the
  session still runs through the flight loop to the Save alert and ExitToShell. Hooks still missing:
  0, 2, 4, 10, 14, 21 (1, 8, 9, 11, 12, 13, 15, 16, 17 written). Every Leyte test OK, QA PASS, 49 GiB free.

Cycle 67 of job f19bd982:

- Hook 4's tree started from its leaves, each read from Leyte's listing:
  - retire_entity (0x603a): 1.0.3's 0x51cc on Leyte's entity layout (controller +0, child indices words
    +0x10 / +0x12, x +4, flags +0x14, stamp +0x3e; scene root 0x23c2c with its child table at +0x32;
    window 0x2107a / 0x21078, scatter slots 0x2144c, shot controller 0x21068, retire count 0x2b22c), plus
    Leyte's guard: the child-table entry is cleared only when it holds the object;
  - dispatch_entity_cleanup (0x60dc): retired flag byte +0x17 bit 1, child index word +0x10, type byte
    +0x28, stamp +0x3e from frame counter 0x21020.
  The D0 each leaves for its caller matches 1.0.3's rules (the caller's, the child index, or 7).
- 10 cases (equal / differing indices, the guard both ways, the window and the slot scrub, an index
  word above 0x7fff, the shot controller, no controller; cleanup already retired, stamped, retired),
  D0 checked wherever the code defines it.
- Overlay reconstructed fixture: 239 cases, all matching Leyte's code.
- Next: apply_entity_mission_rates (0x4fce), then dispatch_entity_tick, clear_pending_entity_slots and
  run_simulation_tick (hook 4).

Cycle 68 of job f19bd982:

- Cycle 67's validation: every Leyte test OK, QA PASS.
- apply_entity_mission_rates (0x4fce) written from the listing: 1.0.3's 0x433a on Leyte's entity layout
  (flagged byte +0x17, scale bit byte +0x15, rate +0x18, child index word +0x10, parent template list
  +0x2a, faces +0x4a with count word +0x3c, type byte +0x28, player = controller +0 == 0x20b28), with two
  clamps 1.0.3 does not have: the rate held to 0..999999999, and with byte +0x16 bit 4 raised to at least
  +0x20 + 0xce40 (before the shape clamp). The three indexed operands' scales (x8, x4, x2) were read from
  the extension words, since capstone's text drops them.
- dispatch_entity_tick (0x5152: controller +0, flags +0x14, hidden controller type 8 where 1.0.3 had 5)
  and clear_pending_entity_slots (0x6c6a, slots 0x2144c) written.
- 20 cases: mission rates over each phase, the parked rate, halving, the scale bit, both new clamps, the
  shape clamp, the player's and others' faces, path 1; the tick's hide / show; the slots full and empty.
- Overlay reconstructed fixture: 259 cases, all matching Leyte's code.
- Hook 4's tree now lacks only run_simulation_tick itself (0x5192).
- Next: run_simulation_tick, register hook 4 and run the probe.

Cycle 69 of job f19bd982:

- Cycle 68's validation: every Leyte test OK, QA PASS.
- Hook 4 (run_simulation_tick, 0x5192) written and REGISTERED: 1.0.3's 0x44c8 with Leyte's arrays
  (path 0: 0x20b44 / count 0x20f2c, 0x20804 / 0x20b24; path 1: 0x233bc / 0x219c4, 0x22d2c / 0x219c0),
  pending slots through 0x6c6a, distance reset 0x7ff4 (verified); the same stack depths. Its whole tree
  is now on Leyte's code.
- 4 cases end to end (eight entities across the four arrays through the tick, mission rates and the
  type-8 hide; the slots cleaned or kept by host +0xac; phases 0 and 6).
- Overlay reconstructed fixture: 263 cases, all matching Leyte's code.
- Probe with hook 4 live, Leyte tests and QA: running (result in the next checkpoint).
- Next: the probe's result; then hooks 0, 2, 10, 14, 21.

Cycle 70 of job f19bd982 (survey while the hook-4 probe runs):

- Hook 21 (spawn_cloud_layer, 0x728c) maps to: overlay_rng_next (verified), fold_angle_pm_0x2000 (0xf85c,
  the same 9 instructions, refused only for its 0x2000 / 0x4000 constants), abs_int and sin / cos
  (written), spawn_camera_relative_object (0x1dc02, controller +0 for 1.0.3's +8) and
  spawn_mission_pool_object (0x5f64), which reaches heap_realloc_grow_only (0x21c0, the same) and
  instantiate_mission_pool_slot (0x5450): restructured in Leyte (134 instructions against 90, with a
  frame 1.0.3 does not have) - the object-instantiation core that hook 2's tree reaches too.
- Next: the probe's result; then instantiate_mission_pool_slot by hand (it unblocks hooks 2 and 21).
- Probe with hook 4 registered: no assertion stop; hook 4 is no longer reported missing and the session
  still runs through the flight loop to the Save alert and ExitToShell. Hooks still missing: 0, 2, 10,
  14, 21 (1, 4, 8, 9, 11, 12, 13, 15, 16, 17 written). Every Leyte test OK, QA PASS, 49 GiB free.

Cycle 71 of job f19bd982:

- instantiate_mission_pool_slot (0x5450) read from Leyte's listing. It is 1.0.3's 0x46ba on Leyte's
  layout (descriptor via root +0x2a, slot table root +0x32, type / slot as words +0x10 / +0x12, the
  same-slot counter byte +0x29, the "below root +0x2e" flag in +0x14) with one rewritten part: after
  the class factory creates the controller (stored at object +0 and cleared), Leyte transforms (0, 0,
  0x1000 << word +0x36) through apply_template_chain_xform, subtracts the object position, turns that
  vector into two angle words through 0x2554 (hook 19), and gives the controller the object position
  (+0x14..+0x1c) and those angles (+0x20 / +0x22), where 1.0.3 copied three record words.
- Its callees: update_object_camera_metric and alloc_object_from_desc (both written this cycle),
  apply_template_chain_xform (0x3524) and activate_near_range_object (0x5c98), both restructured, hook
  19 (0x2554), the class factories (one create routine per controller kind, through the factory table
  0x2074c), apply_entity_mission_rates (written).
- update_object_camera_metric (0x47b6): position +4 / +8 / +0xc, camera 0x23d68 / 0x23d64 / 0x23d60,
  metric at +0x1c (1.0.3 +0x10).
- alloc_object_from_desc (0x532e): shapes 0x54 bytes (1.0.3 0x52), type byte +0x28, flags +0x14,
  position +4..+0xc, rate +0x18, words +0x36..+0x3c / +0x52, birth frame +0x42; kind 3 0x36 bytes with
  the descriptor at +0x2a. Leyte's object record, as far as this shows it, keeps 1.0.3's field order
  with the type byte moved behind the position.
- 7 cases with D0 (the metric per dominant axis; both object kinds allocated on a fresh heap that
  builds its sentinel and grows the arena from host +0x384).
- Overlay reconstructed fixture: 270 cases, all matching Leyte's code.
- Next: hook 19 (0x2554) and apply_template_chain_xform, then activate_near_range_object; the class
  factories after that (they are the bulk of the flight code).

Cycle 72 of job f19bd982:

- Cycle 71's validation: every Leyte test OK, QA PASS.
- Hook 19 (attitude_from_velocity, 0x2554) written and REGISTERED: the same 100 instructions as 1.0.3's
  0x1f02, refused by the generator only for its callees:
  - atan_fixed14 (0x24ec) and acos_fixed14 (0x2468): registers reallocated, Leyte's tan / cos tables
    (0x20194 / 0x1ff94);
  - isqrt_floor (0xf7c0): Leyte returns the root as a sign-extended word where 1.0.3 moved the long -
    the same value (the root is at most 0x7fff).
- 28 cases with D0 / outputs: atan and acos across their ranges and clamps, isqrt including 0, 1 and a
  negative, and hook 19 over each heading branch, steep and level pitch of both signs, vz 0, speed 0.
- Overlay reconstructed fixture: 298 cases, all matching Leyte's code.
- Leyte tests, QA and a probe with hook 19 live: running.
- Next: apply_template_chain_xform (0x3524), then activate_near_range_object (0x5c98), toward
  instantiate_mission_pool_slot (hooks 2 and 21).

Cycle 73 of job f19bd982:

- Cycle 72's validation: every Leyte test OK, QA PASS (the probe with hook 19 live still running).
- apply_template_chain_xform (0x3524) read from Leyte's listing: restructured. Leyte no longer walks
  the parent chain itself: a new helper (0x36fa, about 150 instructions) composes the whole chain into
  one matrix and translation in the frame (a6 - 0x1e) and returns whether any rotation applies; the
  point path (src or dst 0) then works on the object position (+4..+0xc, from 1.0.3's +0x18..) and
  always commits it (1.0.3 committed only with both pointers 0); the mesh path rotates and translates
  the vertices once, offsets shifted by word +0x36 (1.0.3 +0x34). The composer builds each parent's
  rotation (0x2ce2), multiplies matrices (0x2326), copies (memcpy 0x1f61a) and accumulates rotated
  translations - to be read in full next.
- translate_int3_vecs (0x2274) written: 1.0.3's 0x1c3c with the count tested before the loop (the same
  iterations). 4 cases.
- Overlay reconstructed fixture: 302 cases, all matching Leyte's code.
- Next: the chain composer 0x36fa and the matrix product 0x2326, then apply_template_chain_xform.
- Probe with hook 19 registered: no assertion stop; the session still reaches the Save alert and
  ExitToShell. Hooks still missing: 0, 2, 10, 14, 21 (1, 4, 8, 9, 11, 12, 13, 15, 16, 17, 19 written).
  Every Leyte test OK, QA PASS, 49 GiB free.

Cycle 74 of job f19bd982:

- Leyte's template-chain transform written (pacific_conflict_reconstructed.gd), read from the listing:
  - leyte_matmul3x3_s16 (0x2326): 1.0.3's matmul3x3_s16 laid out with a frame (out = a x b, each element
    the add.l sum of three muls.w products asr 12);
  - compose_template_chain (0x36fa, Leyte only): identity / zero start, then for each parent (+0x24 chain,
    slot index word +0x12, slot table parent +0x32): the slot's rotation (0x2ce2) folded in when an
    angle is non-zero - copied the first time (memmove_block), then accumulated x R into the other frame
    buffer - with the translation turned by R ((t . R) asr 12) before the slot's is added; otherwise the
    slot's translation is added. Matrix to out (unless already there), translation at out + 0x12;
    returns whether any rotation was folded in. Its work buffers live in its frame (a6 - 0x12, - 0x24,
    - 0x48), at the stack depths the listing gives (0xb4 / 0xbc below the entry);
  - apply_template_chain_xform (0x3524): the chain composed once, the point path on the object position
    (+4..+0xc) always committed back, the mesh path rotated then translated by the translation << word
    +0x36.
- 11 cases: the product; the composer with no parent, a translating parent, a rotating parent and a
  three-level chain mixing both (D0 checked); the point path on each; the mesh path translated and
  rotated.
- Overlay reconstructed fixture: 313 cases, all matching Leyte's code.
- Next: activate_near_range_object (0x5c98), then instantiate_mission_pool_slot and the class factories.

Cycle 75 of job f19bd982:

- Cycle 74's validation: every Leyte test OK, QA PASS.
- activate_near_range_object (0x5c98, 221 instructions against 1.0.3's 206) mapped. Its callees on
  Leyte: heap_alloc (verified), build_object_poly_buffers (0x561c), build_land_outline_at_detail
  (0x577a), set_object_faces_bw_pattern (0x5738, verified), apply_entity_mission_rates (written),
  update_object_distance_band (0x49cc), sink_object_shape_at_waterline (0x19b32), translate_int3_vecs and
  apply_template_chain_xform (written), shade_object_faces (0x4aec), instantiate_mission_pool_slot
  (0x5450, mutually recursive with it) and update_object_camera_metric (written).
- The distance bands written: the shared search on Leyte's band_limits table (0x203b8, read from the
  image: 0, 40041, 81395, 124152, 168409, 214275, 261873, 311338, ...), the object's band word at +0x32
  (1.0.3 +0x30), the record's at +4 (0x4a3a). 12 cases with D0 (kept, up one, up several, down several,
  down one, near zero; a distance below 0 would walk off the table's front, in the original as in the
  port, and is left out).
- Overlay reconstructed fixture: 325 cases, all matching Leyte's code.
- Next: shade_object_faces (0x4aec), build_object_poly_buffers (0x561c), build_land_outline_at_detail
  and sink_object_shape_at_waterline, then activate_near_range_object and instantiate_mission_pool_slot.

Cycle 76 of job f19bd982:

- Cycle 75's validation: every Leyte test OK, QA PASS.
- A new comparison aid (scratch, not part of the repository): the two listings aligned by a sequence
  diff on normalised instructions (branch targets and absolute addresses masked), so a routine whose
  loop gained a bound check before entry still lines up and only its real differences show.
- shade_object_faces (0x4aec) written, derived from 1.0.3's port by the object-field table the aligned
  listings give (type byte +0x28, retired byte +0x17, band word +0x32, flags +0x14, vertices +0x46 after
  word +0x38, template list +0x2a, child index word +0x10, faces +0x4a with count word +0x3c, snapshot flag
  byte +0x16); every constant paired one to one (light 0x21574 / 0x21572 / 0x21570, owner table 0x23fac);
  the face records unchanged. Leyte checks the face loop's bound before entry: the same faces.
- assign_face_colour (0x1be6, the same 94 instructions) and write_face_palette_slot (0x1b90, registers
  reallocated; the three owner tables 0x23fa0..0x23fa8) written with Leyte's globals.
- 12 cases: shading at depth 8 with haze, haze masked, an owning face, depth 4, depth 1, a flat-white face,
  the retired skip and the snapshot; slot assignment re-queued, first free and all full (D0); the
  multi-screen slot write. (A case-writing slip was caught on the way: the flags long at +0x14 written
  after the retired byte +0x17 had cleared it; reordered, the retired case now changes nothing.)
- Overlay reconstructed fixture: 337 cases, all matching Leyte's code.
- Next: build_object_poly_buffers (0x561c), build_land_outline_at_detail and
  sink_object_shape_at_waterline, then activate_near_range_object and instantiate_mission_pool_slot.

Cycle 77 of job f19bd982:

- Cycle 76's validation: every Leyte test OK, QA PASS.
- build_object_poly_buffers (0x561c) written: 1.0.3's 0x47e8 (an earlier pairing had taken the wrong
  1.0.3 address, 0x4938, which is build_land_outline_at_detail) derived from its port by the aligned
  listings' table - face count word +0x3c, vertex count word +0x3a, faces +0x4a, flag byte +0x16,
  palette_changed 0x23fd2; both loops' bounds checked before entry (the same iterations); the face
  records unchanged.
- 2 cases on a fresh heap (three faces with 3 / 0 / 4 index words; with and without the vertex trailer).
- Overlay reconstructed fixture: 339 cases, all matching Leyte's code.
- The two remaining leaves under activate_near_range_object sized: build_land_outline_at_detail (156
  port lines; four polygon clippers, approx_dist3_xyz, heap_realloc_grow_only) and
  sink_object_shape_at_waterline (132 lines; clip_polygon_y_nonneg, transform_vecs12_to_int3_by_matrix3).
- Next: those two with their helpers, then activate_near_range_object and instantiate_mission_pool_slot.

Cycle 78 of job f19bd982:

- Cycle 77's validation: every Leyte test OK, QA PASS.
- build_land_outline_at_detail (0x577a) written, derived from 1.0.3's 0x4938 port by three tables read
  from the aligned listings:
  - frame (link.w a6,#-0x1cf8 against -0x1c08): the work buffers below -0x8c0 moved down - relative
    points -0x1cd0, transformed vertices -0x16d0, unique points -0xfc8 (room for 150 where 1.0.3 had 140),
    last x / z -0x1ce4 / -0x1ce8; -0x8c0 and -0x460 unchanged; the chain transform entered 0x1d38 below.
    Two traps on the way: the 4-digit frame displacements were first masked as addresses, and the
    buffers indexed off a6 take their offset from a following adda.w (capstone shows the indexed
    operands without a displacement) - both settled from the raw words;
  - object fields: template list +0x2a, child index word +0x10, faces +0x4a / count +0x3c, vertices +0x46
    after word +0x38, flags +0x14, second buffer +0x4e, position x / z +4 / +0xc, range +0x20, metric
    +0x1c, shift +0x36, steps +0x34 (a chained substitution that first turned the child index into
    +0x1c was caught and fixed);
  - globals: detail ref 0x23c54 / 0x23c4c, camera 0x23d68 / 0x23d60, clip bounds 0x2701a / 0x27018 /
    0x27016 / 0x20b2e; approx_dist3_xyz at 0x4886.
- approx_dist3_xyz (0x4886, the same 36 instructions): the generated port held an asserting stub; the
  real body written.
- Test support: a case may carry its own stack guard (test_leyte_overlay_reconstructed.gd and the
  generator), for routines whose frame keeps work buffers far below the default 0x840-byte band.
- 5 cases: approx_dist3_xyz (D0); the land outline near with a wide limit and far with clipping (the
  object, faces and globals compared; the frame ignored).
- Overlay reconstructed fixture: 344 cases, all matching Leyte's code.
- Next: sink_object_shape_at_waterline (with transform_vecs12_to_int3_by_matrix3), then
  activate_near_range_object and instantiate_mission_pool_slot.

Cycle 79 of job f19bd982 (survey):

- Cycle 78's validation: every Leyte test OK, QA PASS.
- A fuller comparison aid (scratch): one report per routine pair with the frame offsets, field /
  immediate pairs and absolute addresses paired through the aligned listings, then the real
  differences.
- sink_object_shape_at_waterline is rewritten in Leyte, not moved:
  - 0x19b32 is now a 17-instruction wrapper: compose_template_chain(obj, its frame) (0x36fa), then the
    body 0x1970a(obj, three long arguments, the composed chain). 1.0.3's 0x15742 took four words and
    walked the chain itself (apply_template_chain_xform);
  - the body (330 instructions against 333) multiplies the sink rotation (0x2ce2) into the composed
    chain with the 3x3 product (0x2326), transforms through 0x32a0 (1.0.3's
    transform_vecs12_to_int3_by_matrix3, not found by the correspondence), reads the object position
    from +4..+0xc (1.0.3 +0x18..+0x20), keeps its buffers about 0x78 lower in a larger frame (link
    #-0x33dc against -0x3358), uses clip_polygon_y_nonneg (0x195d6, verified) and ends by calling 0x16116
    where 1.0.3 called shade_object_faces;
  - so it needs a hand reading of Leyte's listing, with 0x16116 and 0x32a0 identified first, and
    activate_near_range_object's call to it checked for the new arguments.
- No code changed this cycle.
- Next: 0x16116 and 0x32a0, then the sink body and wrapper, then activate_near_range_object.

Cycle 80 of job f19bd982:

- The sink's two unknown callees identified and written:
  - 0x32a0 = transform_vecs12_to_int3_by_matrix3 (1.0.3 0x2956), the count tested before the loop;
  - 0x16116 = drop_contact_records_for_object (1.0.3 0x137ba): contact table 0x215a4, flag bytes +0x16
    (1.0.3 +6), fractal reference +0x2e (1.0.3 +0x2c). So Leyte's sink body ends by dropping the object's
    contacts where 1.0.3's re-shaded its faces.
- release_fractal_reference (0x1746c) written: 1.0.3's 0x14ad2 on Leyte's layout (scenery flags byte
  +0x15, owner flag +0x16, reference +0x2e, position +4..+0xc, template list +0x2a, child index word
  +0x10; object_pool 0x20804 with word count 0x20b26, deferred-free flag 0x219a6, the warning through
  overlay_printf 0x189c with the format at 0x17434).
- A flaw in the scratch comparison aids found and fixed: they treated every mnemonic starting with "b" as
  a branch, so btst / bset / bclr / bchg operands were masked. Re-running the routines compared with
  them: shade_object_faces' flag bytes +6 / +7 -> +0x16 / +0x17 (already handled, checked by hand
  against the listing then), build_land_outline_at_detail and build_object_poly_buffers unchanged - no
  missed difference.
- 7 cases: the vector transform (two records, none); the fractal release on the user path (last user
  and still shared), with the static-scenery reset, and on the owner path freeing unused blocks and the
  list; the contact drop (records naming the object as a or b, another kept, the release on the way).
- Overlay reconstructed fixture: 351 cases, all matching Leyte's code.
- Next: the sink body (0x1970a) and its wrapper (0x19b32), then activate_near_range_object.

Cycle 81 of job f19bd982:

- Cycle 80's validation: every Leyte test OK, QA PASS.
- Leyte's waterline sink written from its listing:
  - sink_object_shape_at_waterline (0x19b32): the wrapper - the object's chain composed into its frame
    (0x36fa), then the body with (obj, ang1, ang2, ang3, chain). Leyte reads three arguments after obj
    (1.0.3 passed four words, the fourth the drop); the port keeps the generated signature's fifth
    parameter unread;
  - leyte_sink_body (0x1970a): the sink rotation times the composed chain (0x2326) turns the template
    vectors in one pass (0x32a0) - no separate chain transform; only the y words are rebased, by the
    object's own y (+8) << shift (1.0.3 rebased all three after the chain transform, y by the drop), x / z
    restored on the clipped points from +4 / +0xc; then 1.0.3's clip / hash / rebuild on Leyte's fields
    and frame (any-face -0x33c0, clip count -0x33be, rotation -0x33aa, product -0x33bc, normals -0x3398,
    bucket ids -0x2998, id buckets -0x2d98, clip out -0x2598, vertices -0x1f08, points -0x1800); it
    re-shades only when host word +0xb2 is set and always drops the object's contacts (0x16116).
- 2 cases (a square face crossing the waterline and a triangle below it, tilted under a translating
  parent; the re-shade flag on a retired object), the per-case stack guard covering the 0x3438-deep frame.
- Overlay reconstructed fixture: 353 cases, all matching Leyte's code.
- Next: activate_near_range_object (0x5c98) - its call to the sink checked for the three arguments - then
  instantiate_mission_pool_slot and the class factories.

Cycle 82 of job f19bd982:

- Cycle 81's validation: every Leyte test OK, QA PASS.
- activate_near_range_object (0x5c98) written: 1.0.3's 0x4e4e on Leyte's layout (type byte +0x28, flag
  bytes +0x15 / +0x16 / +0x17, controller +0, metric +0x1c, counts +0x38 / +0x3c, buffers +0x46 / +0x4e,
  group child count +0x30 / table +0x32 / descriptor +0x2a; arrays 0x20b44 / 0x20804, controller_list
  0x21bd4 / 0x219b4) with two changes read from the listing: a new steps word +0x34 computed after
  publishing ((metric asr 2 + metric + range +0x20) << word +0x36, asr #1 until below 0x8000), and the
  waterline sink called for controller types 0x1b..0x22 with the controller words +0x658 / +0x65a /
  +0x65c (1.0.3: types 0x15..0x19, four words +0x34..).
- instantiate_mission_pool_slot (0x5450) written from the cycle-71 reading: 1.0.3's on Leyte's layout,
  with the controller set-up rewritten (the chain-transformed (0, 0, 0x1000 << word +0x36) minus the
  object position, heading / pitch through hook 19, the controller given the object position and the
  two angle words). Its class-factory call goes through _jsr to the definition record's create routine
  (factory table 0x2074c); none of those is written for Leyte yet, so that path stops at its assertion.
- 4 cases: activation of a childless group and of an unknown kind; instantiation end to end on a fresh
  heap - a near shape activated through the plain path (buffers, face records, chain transform, band,
  depth-8 shading) and a far one queued on entity_array_3.
- Overlay reconstructed fixture: 357 cases, all matching Leyte's code.
- The object-instantiation core under hooks 2 and 21 is now on Leyte's code except the class factories.
- Next: hook 21 (spawn_cloud_layer, 0x728c: spawn_camera_relative_object, spawn_mission_pool_object,
  fold_angle) and hook 2 (start_mission_scene, 0x46e2), then the class factories they reach.

Cycle 83 of job f19bd982:

- Cycle 82's validation: every Leyte test OK, QA PASS.
- Hook 21 (spawn_cloud_layer, 0x728c) written and REGISTERED with its tree, each paired with 1.0.3's:
  - fold_angle_pm_0x2000 (0xf85c): the same 10 instructions (the generated port held a stub);
  - spawn_mission_pool_object (0x5f64): root (0x23c2c) count / table +0x30 / +0x32, template type word
    +0x10 (1.0.3 byte +1), vertex buffer +0x46, instantiate 0x28 below;
  - spawn_camera_relative_object (0x1dc02): controller +0 (1.0.3 +8), cloud templates 0x2cd90, camera
    0x23d68 / 0x23d64 / 0x23d60;
  - spawn_cloud_layer: the cloud controller type is 0x2a (1.0.3 0x1d), entity_array_1 0x20b44 / 0x20f2c.
- 11 cases: fold_angle across its range (D0); the pool spawner with a free slot, a full table (grown by
  10) and no vertices (D0 - with the camera far away the object is queued, has no vertex buffer and the
  spawner retires it, in the original as in the port); hook 21 without clouds (type-0x2a clouds retired,
  others kept). With clouds the hook reaches the cloud classes' create routines (through the factory
  table), not yet written for Leyte - their assertion stops there.
- Overlay reconstructed fixture: 368 cases, all matching Leyte's code.
- Probe with hook 21 live, Leyte tests and QA: running (result in the next checkpoint).

Cycle 84 of job f19bd982:

- Cycle 83's Leyte tests OK and QA PASS (its probe still running at the time of writing).
- Hook 2's tree mapped (start_mission_scene 0x46e2: 1.0.3's 0x3aa4 instruction for instruction, the scene
  table at root +0x32, Leyte's globals): reset_distance_base_globals, fill_pose_history_angles and
  stop_all_sound_channels already verified; hook 21 written; find_anchor_point (0x11fa) is a new
  algorithm in Leyte (90 instructions against 22, a 0xb7c-byte frame, nearest-point searches through
  approx_dist3_xyz) - its own cycle next.
- Written with cases:
  - reset_object_to_template_pose (0x5c30): position +4..+0xc and range +0x20 from the descriptor, flag
    byte +0x17;
  - deactivate_object (0x6130): type +0x28, flags +0x14 / +0x16, buffers +0x4e / +0x46, faces +0x4a / +0x3c,
    controller +0, child indices words +0x12 / +0x10, fractal reference +0x2e; groups +0x30 / +0x32 with the
    children's +0x28 / +0x2e / +0x14 / +0x29 / +0;
  - copy_player_pose_to_camera (0x83de): position +4..+0xc, the type record's word +0x46 (1.0.3 +0x44 - a
    field the first draft kept at +0x44, caught by the pairing and fixed against the listing).
- 6 cases (the pose reset and its skip, the camera copy, a shape and a group deactivated).
- Overlay reconstructed fixture: 373 cases, all matching Leyte's code.
- Next: the hook-21 probe result; find_anchor_point; then start_mission_scene registered as hook 2.
- Probe with hook 21 registered: the FIRST DIVERGENCE of this stretch. The cloud path instantiates through
  the class-factory table 0x2074c, which lies exactly at the end of the loaded image (text + data =
  0x2074c), i.e. in the zeroed bss: Leyte fills it at run time - routines at 0x38 and ~0x280 copy the
  definition records (0x52 bytes each from 0x1f78c, 0x1c bytes from 0x1fbb0) into heap blocks and file
  them under their word +0 - and those have not run in the port. The factory call therefore read a null
  definition and jumped through garbage (dozens of assertions, no ExitToShell). Hook 21 is unregistered
  again (written, cases kept), so the start returns to its previous reach; the probe is re-run to
  confirm.
- Next: find what reaches the definition-registration routines (no direct bsr: a code pointer, likely
  the overlay initialisation or the mission set-up), port them, then register hooks 21 and 2.

Cycle 85 of job f19bd982:

- The hook-21 divergence traced to its source: in 1.0.3, overlay_init_template_pointer_tables fills the
  definition table and is called first by init_mission_object_tables, which hook 0
  (configure_renderer_for_display) calls when no mission has been built. Hook 0 is not ported for Leyte,
  so the table was never filled. Hooks 21 and 2 therefore wait on hook 0 and the mission build under it.
- Leyte's overlay_init_template_pointer_tables (0x38) written from the listing: seven 0x52-byte records
  from 0x1f78c (1.0.3: four of 0x4a) and eight 0x1c-byte records from 0x1fbb0 (1.0.3: five), each at the
  slot its first word names, then the fixed copies of two step tables (group A through table + 0x20,
  group B through table + 0x8c; sizes 0x1a / 0x20; the k-th call of a group 0xc deeper, the first at 0x24
  below the entry with five saved registers).
- 1 case: the whole initialiser on a fresh heap (43 copies, 1023 changed bytes), matching Leyte's code.
  (A first draft of the case zeroed 0x40 table slots and so ran over the host-world pointer at 0x207fc;
  the table is 0xb0 bytes up to it, and the case now zeroes those 44 slots.)
- Overlay reconstructed fixture: 374 cases, all matching Leyte's code.
- Next: the probe confirming the start's reach with hook 21 unregistered; then Leyte's
  init_mission_object_tables and hook 0 (configure_renderer_for_display, 0x8032), after which hooks 21
  and 2 can be registered.

Cycle 86 of job f19bd982:

- Cycle 85's Leyte tests OK and QA PASS; the probe confirming the start without hook 21 still running.
- Leyte's mission build located (0x1d2a, the one direct caller of the template initialiser 0x38 - an
  earlier search had missed it by matching only bare operands) and written from the listing:
  - init_mission_object_tables: 1.0.3's 0x16c2 on Leyte's layout (root 0x23c2c, descriptor +0x2a, child
    count +0x30 / table +0x32 / window +0x2e, child indices words +0x10 / +0x12), with Leyte's changes:
    child 2 is always table +0x44 (1.0.3 chose +0x5c when host word +0x90 == 2), the special children go
    to 0x2159c / 0x21598 / 0x21594 / 0x2158c (+ child 2's faces at 0x21590), the window to 0x2107a / 0x21078
    and its children to 0x21410 / 0x2141c / 0x21420; depths from the pushed arguments;
  - instantiate_object_from_desc (0x5408): the controller at the object's +0 and Leyte's definition table
    0x2074c (a Leyte class-factory helper);
  - copy_object_vertex_buffer (0x221c): counts +0x38 / +0x3c, buffers +0x46 / +0x4e.
- 3 cases (the vertex copy with vertices and none; a key-less descriptor instantiated on a fresh heap).
  The full mission build needs a scene whose children reach working class factories, not yet written.
- Overlay reconstructed fixture: 377 cases, all matching Leyte's code.
- Next: hook 0 (configure_renderer_for_display, 0x8032), which calls this mission build; then the class
  factories the scene's children name.
- Probe with hook 21 unregistered again: the start is back to its previous reach - no assertion stop, the
  Save alert and ExitToShell; hooks still missing 0, 2, 10, 14, 21 (21 written, waiting on hook 0 and the
  class factories). Every Leyte test OK, QA PASS, 48 GiB free.

Cycle 87 of job f19bd982:

- Hook 0 (configure_renderer_for_display, 0x8032) written, NOT registered: 1.0.3's 0x6fd6, the same 157
  instructions, derived from its port by the pairing's table (Leyte's globals, host +0x2ae for +0x276,
  mission child 4's faces +0x4a, the depth-1 face output 0xb482, and the three renderer-hook tables mapped
  slot by slot and address by address).
- Why not registered: the renderer hooks it installs are 21 code addresses (span fillers 0x4294 / 0x3d9a /
  0x3f46 / 0x40e8, depth routines 0xe784 / 0xece2 / 0xe978 / 0xef28, face / scatter outputs 0x19f12 /
  0x19e8c / 0x1a104 / 0x19f8c / 0x1a20c / 0x1a204, blip drawers 0x1b992 / 0x1b902 / 0x1baca / 0x1b9ea /
  0x1bbe2 / 0x1bbb6, face output 0xb3d0) with no Leyte port yet; once live, the frame code would call them
  through _jsr - the hook-21 lesson. They are the next block of work (P2).
- overlay_patch_template_u16_pair (0x0): Leyte patches three template words (0x1f7d0, + 0xa4, + 0xf6:
  definition records 0, 2 and 3) where 1.0.3 patched two.
- 5 cases: the patch; hook 0 with nothing changed, with a depth change to 8 and to 4 (colour state through
  the verified initialisers, the renderer hooks, the template words, child 4's face flags) and to depth 1.
  (The depth-change cases first had no heap; the palette owner tables then allocated over address 0. They
  now carry a fresh arena.)
- Overlay reconstructed fixture: 382 cases, all matching Leyte's code.
- Next: the 21 renderer routines, depth 8 first (the span filler 0x4294, the depth routines 0xe784 /
  0xece2, the outputs 0x19f12 / 0x19e8c / 0xb3d0, the blip drawers 0x1b992 / 0x1b902), then register hook 0.

Cycle 88 of job f19bd982:

- Cycle 87's validation: every Leyte test OK, QA PASS.
- Hook 0's depth-8 renderer routines paired with 1.0.3's: the span filler (0x4294), draw / restore line
  (0xe784 / 0xece2), the dot plotter (0x19f12) and both radar-blip routines (0x1b992 / 0x1b902) are the same
  instructions with no real difference; written from 1.0.3's port with Leyte's globals, the span writer's
  long-run labels moved with the routine (+0xb00: 0x4338 / 0x4396; the generated port already fills the
  tables 0x27224 / 0x29224 with Leyte's labels), and the blip host fields (+0xe0 / +0xe4, +0x1eb).
- 14 cases: dots of each size, a blip drawn and erased, lines drawn and restored (shallow, steep,
  reversed), and the span filler on rows whose spans differ and match, aligned and at an odd screen start.
  Three things found by them:
  - a REAL PORT BUG caught before it reached the start: the derived blip erase still stepped the saved
    panel by 1.0.3's row-bytes field (+0xe0); Leyte reads +0xe4. Fixed;
  - the span records' header is the span count (as the sky routine's _bg_row writes it), not count - 1:
    the case's records were wrong, not the port;
  - a zero-length line starts the line routine's dbra counter at -1 and runs 65536 steps, in the original
    as in the port: left out of the cases.
- Overlay reconstructed fixture: 396 cases, all matching Leyte's code.
- Next: the rest of the depth-8 hooks (output_face_colour 0xb3d0 with its edge-row helpers, erase_dots_depth8
  0x19e8c), then the depth-4 / depth-1 sets, then register hook 0.

### Cycle 89 checkpoint — depth-8 dot eraser

- erase_dots_depth8 (Leyte 0x19e8c, 1.0.3 0x15e9e) reconstructed. The pairing tool found only the moved
  globals: screen record 0x207fc, dot count 0x23fbc, dot table 0x2c83c. Leyte also checks the loop bound
  before the first record, which runs the same records. Each dot gives back its saved pixels only where the
  screen still shows its colour.
- 2 cases: three dots (sizes 1, 3 and 4, one pixel since recoloured and left alone) and an empty list.
- Overlay reconstructed fixture: 398 cases, all matching Leyte's code.
- Depth-8 renderer set for hook 0: 7 of 8 routines written; output_face_colour (0xb3d0) remains.
- Next: output_face_colour 0xb3d0 with its edge-row helpers, then the depth-4 / depth-1 sets, then register
  hook 0.

### Cycle 90 checkpoint — depth-8 face-colour output

- output_face_colour (Leyte 0xb3d0, 1.0.3 0xa14e) and its edge rasteriser add_face_edges_to_rows (Leyte 0xaddc,
  1.0.3 0x9b6c) reconstructed.
  - The output routine pairs with no real difference. Its moved callees are the four clip passes
    (0xc458 / 0xc50a / 0xc5bc / 0xc672), the horizon tracker 0xb2e0 and the rasteriser. The generated port
    already had all of them except the rasteriser.
  - The rasteriser differs only in checking its edge loop's bound before entry, which runs the same edges.
  - Every moved global matches the generated port's DAT_ constants, so both bodies are 1.0.3's port as it
    stands. The rasteriser calls assign_face_colour (0x1be6), which is already reconstructed.
- 5 cases:
  - a triangle inside the view (no palette slot);
  - a quad clipped at the right and bottom whose face still owns slot 7 (marked used);
  - a polygon wholly outside (returns 0, nothing rasterised);
  - the ground face (horizon tracking against the alternate bottom row sets the bottom-corner bits);
  - an edge equal to one already in its row's bucket, which takes the face as its second face, beside an
    edge sorted before the existing one.
- Overlay reconstructed fixture: 403 cases, all matching Leyte's code.
- Depth-8 renderer set for hook 0: all 8 routines written. Hook 0 is still unregistered: its depth-4 and
  depth-1 sets remain, and the routines are registered with it.
- Next: the depth-4 set (0x3d9a, 0x3f46, 0x40e8, 0xe978, 0xef28, 0x1a104, 0x19f8c, 0x1baca, 0x1b9ea), then
  the depth-1 set (0x1a20c, 0x1a204, 0x1bbe2, 0x1bbb6), then register hook 0 and run the start probe.

### Cycle 91 checkpoint — depth-4 and 1-bit renderer routines

- Every remaining renderer routine hook 0 installs was paired by instruction with 1.0.3's and reconstructed. The
  only differences are moved globals, host fields and code-relative tables:
  - span fillers A / B / C 0x3d9a / 0x3f46 / 0x40e8: all +0xb00, with their pc-relative run-writer tables
    0x3d22 / 0x3ece / 0x4070 and label bases 0x3e34 / 0x3fd6 / 0x4178. The table fillers 0x3ef2 / 0x4094 pair
    identically; the generated port already had the first, 0x3d46;
  - depth-4 line draw / restore 0xe978 / 0xef28: +0x13e8, labels named in comments;
  - depth-4 dot plotter / eraser 0x1a104 / 0x19f8c. The eraser checks its loop bound before entry;
  - depth-4 blip drawer / eraser 0x1baca / 0x1b9ea: colour host +0x1eb for 1.0.3's +0x1e7, the saved panel's
    row bytes +0xe4 for +0xe0;
  - 1-bit blip eraser / drawer 0x1bbb6 / 0x1bbe2: host rectangle routine +0x2ae for 1.0.3's +0x276;
  - 1-bit dot eraser 0x1a204 (clr.w 0x23fbc; 1.0.3 had no port function for its 0x16208). The 1-bit dot
    plotter 0x1a20c is the generated port's plot_dot_bw.
  - The generated port's dot_records constant (DAT_00026288) is still 1.0.3's address, so the reconstruction
    uses literal Leyte addresses (dot table 0x2c83c).
- 34 cases:
  - both table fillers;
  - dots of each size at both nibble phases, and four dots erased, one pixel recoloured since;
  - blips at both phases, drawn and erased;
  - the 1-bit blips, logging their host rectangle calls, and the 1-bit dot eraser;
  - lines drawn and restored (shallow, steep, reversed);
  - all three span fillers, A through handles.
- Test strengthened: the depth-8 line-restore cases from cycle 88 changed nothing (both the screen and the
  saved panel were zero along the lines). Both depths' restore cases now get a filled saved panel and copy
  52-125 bytes back. They still match.
- Overlay reconstructed fixture: 430 cases, all matching Leyte's code.
- Hook 0's renderer set is complete for all three depths.
- Next: register hook 0 (configure_renderer_for_display 0x8032) with code_table entries for its installed
  routines (LEYTE_RENDERER_HOOKS_8 / 4 / 1), then run the start probe and fix the first divergence.

### Cycles 92-94 checkpoint — hook 0 live, mission-build class factories

- Hook 0 (configure_renderer_for_display, 0x8032) registered in code_table, together with the 21 renderer routines
  it installs (LEYTE_RENDERER_HOOKS_8 / _4 / _1). Cycle 91's duplicate table fillers (init_span_table_3466 /
  _3608) were removed: leyte_init_span_table_3ef2 / _4094 already covered them.
- Start probe with hook 0 live: the first divergence was the mission build. init_mission_object_tables
  instantiates the scene's children, and their class factories (definition +6, reached through 0x2074c) had no
  port. The first was 0x1ebe0, followed by 0x1ca20, 0x13bb6, 0x14952 / 0x14974 / 0x14996, 0x1d56e, 0x1ed5c and
  0xf616. Without a port the run continued on Nil values. Leyte's definition records are renumbered and more
  numerous than 1.0.3's (7 + 8 for 4 + 5), so each factory was paired by its record's position.
- Template-registration factories reconstructed:
  - 0x1ebe0, ordnance: nine slots at 0x2cdd4, redirected to 0x1ebb6 only at index 8. 1.0.3 kept one template;
  - 0x1ca20, shadow: six slots at 0x2cd70, past 4 redirected to 0x1c9da;
  - 0x1d56e, parachute: 0x2cd8c, redirected to 0x1d516;
  - 0x1ed5c, cloud: two slots at 0x2ce04; any index but 0 redirects both cloud definitions to 0x1ec7e;
  - 0x14952 / 0x14974 / 0x14996: Leyte-only single templates 0x2c834 / 0x2c830 / 0x2c82c, factory 0x148fa;
  - 0x13bb6, type-0x10 controller: sets only flags bit 0x100000 and no longer publishes the controller.
- Second probe: all of those passed. The only failure left was 0xf616, the enemy-fighter factory (1.0.3
  0xe1c6). Its chain is now reconstructed:
  - 0xf616 create_enemy_fighter: template key 7 at 0x2c5ae;
  - 0xf3b8 init_aircraft_controller: a 0x6c8-byte controller. Bombs +0x68d, ammo +0x696, tanks +0x691 / +0x692,
    word +0x642, +0x63e, gear +0x68a, definition +0x44;
  - 0x1ca84 spawn_object_shadow: a new null check on the player-object pointer 0x20b28; the controller at
    object +0;
  - 0x1c9da create_shadow_controller: now also resets the distance references (0x7ff4).
  - heap_alloc_cleared (0x2184) and fill_pose_history (0xf326) pair identically.
- 30 cases:
  - every slot table empty, at its threshold and full;
  - the single-template registrations and the type-0x10 controller;
  - the aircraft controller, the shadow controller, the fighter template capture, and a fighter with its
    shadow;
  - shadows through the real pool spawn and shadow factory: live and owned by the player, live with no
    player pointer, and out of range (retired).
- Test gap found: cycle 85's "spawn pool object" cases never spawn a live object. The camera at 0x900000
  leaves every spawned object inactive, so they all retire and return 0. The aircraft cases place the camera
  beside the spawn point and do get live shadows. Strengthening the cycle-85 cases is a follow-up.
- Overlay reconstructed fixture: 451 cases, all matching Leyte's code.
- Third probe, with the fighter chain: clean, no assertion failures. Startup reaches the Save alert and
  ExitToShell with hook 0 running for real: renderer configuration, colour state, renderer routines and the
  whole mission build (scene root, children, template registrations, the enemy-fighter template and fighters
  with shadows). Missing hooks: 2, 10, 14, 21 (hook 0 is no longer missing). All Leyte tests and QA pass;
  48 GiB free.
- Next: hook 2 (start_mission_scene 0x46e2). Its blocker, find_anchor_point (0x11fa), is a Leyte rewrite:
  - a 0xb7c-byte frame;
  - a scene visitor (0x22ce) collecting two candidate lists;
  - a nearest pick through the distance routine 0x4886: only candidates farther than their per-entry limit
    from every point in the first list qualify, and the pick is skipped when its type byte is 0x18.
  Then hooks 21 (its cloud factories 0x1ec7e now registered as templates), 10 and 14. Follow-up: give the
  cycle-85 pool-spawn cases an in-range live object.

### Cycle 95 checkpoint — find_anchor_point (hook 2's blocker)

- find_anchor_point (Leyte 0x11fa, 1.0.3 0xd4a) reconstructed with its chain:
  - scene walker 0x82a: 1.0.3's 0x686 with a 0x730-byte frame, entering the traversal 0x750 below;
  - node-chain transform 0x676: 1.0.3's 0x4d8 with registers reallocated; its callees are already Leyte's;
  - the generated port's verified traverse_scene_tree, and the extended visitor 0x108e.
  - The visitor's address comes from a pc-relative pea that capstone prints as the absolute target (0x108e).
  - Visitor, mode != 0: no longer picks the nearest runway itself. It appends runway faces whose first vertex
    isn't above ground to list A (x, 0x43, z), and nodes of kind 0x19 / 0x18 to list B with their range
    (node +0xe) and kind. Mode 0 is 1.0.3's.
  - find_anchor_point, mode != 0: for each A, the nearest B within that B's range sets D5 to B's kind. A
    becomes the anchor when nearer the reference than the best, unless D5 is 0x18. D5 is not reset per
    candidate: it carries over, and before the first in-range node it is the caller's register.
  - The port takes the caller's D5 as entry_d5 and asserts when it is read while unknown, rather than invent
    one. Hook 2 calls with mode 0 and never reads it; the mode-1 caller 0x43d4 inherits it from its own caller.
- Case runner: per-case "d5_in" sets the emulator's D5 to the value the port receives as its extra argument.
  The original code doesn't read the extra stack argument.
- 6 cases on a scene of two runway leaves, two nodes and a placed, turned kind-1 leaf:
  - mode 0: the kind-1 leaf with its record's attitude;
  - N1 of kind 0x19: R1 taken;
  - N1 of kind 0x18 vetoes R1, and R2 is taken through N2;
  - with N2 out of range the veto carries over to R2: none taken;
  - no node in range, with the caller's D5 at 0 (R1 taken) and at 0x18 (none).
  A first, weaker scene put all runways on one leaf (one candidate position) and the kind-1 leaf at the
  origin, so I rebuilt it before keeping the cases.
- Overlay reconstructed fixture: 457 cases, all matching Leyte's code.
- Next: hook 2 (start_mission_scene 0x46e2: the previous pool object 0x23c30 retired through 0x6130, then the
  scene record's +0x74 object, find_anchor_point(0x23d6c, 0)...), then hooks 21, 10 and 14.

### Cycles 96-97 checkpoint — hook 2 written, mission-object factories

- Hook 2 (start_mission_scene, Leyte 0x46e2) reconstructed. It pairs with 1.0.3's 0x3aa4 with no real
  difference: only moved globals and callees, and the scene table at +0x32 (1.0.3 +0x30). All its callees exist
  for Leyte. An integration case runs it on the anchor scene: scene object set and activated, camera, distance
  references, frame counter 0x100 -> 0x14b, sound choice reset, player pose ring, and no clouds.
- Start probe with hook 2 registered: it runs, and the next divergence is the mission's own objects. Activating
  the new scene object instantiates them, and their class factories had no port. 0x1dce2 is hit first, then
  0x17ba2, 0x1862a, 0x1bea6, 0x1cc72, 0x1d21a, 0x1db7a and 0xf4a8; after that the run continues on bad values.
  So hook 2 is written but NOT registered again, like hooks 0 and 21 before it, and the start is clean.
- Factories reconstructed:
  - 0x1dce2 / 0x1db7a: 0x32- / 0x40-byte flagged controllers. They return the controller still in D0 from
    the allocation;
  - 0x1d21a turret: 0x54 bytes, +0x42 = 0xce40, flag bits depending on the object's y;
  - 0x1cc72 flak: 0xfa bytes, flag bits when word +0x3c <= 4;
  - 0x1bea6 aircraft spawner: 1.0.3's 0x17cf6 on Leyte's layout, key 0x18.
  - The normalised pairing can't tell these small factories apart (they differ only in immediates), so their
    constants were read from the disassembly.
- 0x1ec7e is not a cloud controller; I had paired its records (keys 0x27 / 0x28) with 1.0.3's cloud slot by
  position. It is a Leyte class with a 0x634-byte controller. For kind 99 with no sub-record yet it builds one
  (0x1a936: a kind-2 shape among the owner group's children, a record sized by the shape's face count), then
  sets heading word +0x22 (0x1eeee, 0x18712) and fills the pose rings at +0x32.
- 10 cases: each factory on a fresh heap, both turret and flak branches, and the spawner over all four ranges
  with both keys.
- Overlay reconstructed fixture: 468 cases, all matching Leyte's code.
- Next: the remaining mission factories, then register hook 2 and run the probe. They are carrier 0x17ba2,
  ship 0x1862a (and its siblings 0x185ba / 0x18544), aircraft_create 0xf4a8 and the kind-99 class 0x1ec7e;
  all are Leyte rewrites of 1.0.3's.

### Cycle 98 checkpoint — Leyte's ship classes

- Cycles 96-97 validation: all Leyte tests and QA passed.
- The ship-class factories hook 2 reaches, reconstructed from the disassembly. 1.0.3's controllers were
  0x56..300 bytes; Leyte's are 0x69c..0x772, with the shared fields moved: range word +0x668, target distance
  +0x66c, word +0x656 = definition word +0x1a, and the kind word at +0x32.
  - 0x17ba2 carrier: becomes the first carrier (0x2104c) only while the anchor mode word 0x23d84 is set and the
    anchor lies within the object's range (+0x20; approx_dist3_xyz 0x4886); the mode word then becomes 2.
    +0x38 = 0x18e20;
  - 0x18544 sinking: range 3, +0x38 = 0x18376, +0x696 / +0x698 = 2 / 0;
  - 0x185ba basic: range 7, +0x696 = 3, object flag bit 1;
  - 0x1862a escort (Leyte only): a nonzero kind also sets object word +0x16 |= 0x4000;
  - 0x18088 firing ship: 1.0.3's 0x152ee on the moved fields, 0x772 bytes.
  - A nonzero kind sets the object's +0x18 to 1000000000 in every class except the firing ship.
- 11 cases: the carrier with no anchor mode, the anchor in range (first carrier, mode 2), the anchor beyond
  range, and a first carrier already set; each other ship with kind 0 and 1; the firing ship.
- Overlay reconstructed fixture: 479 cases, all matching Leyte's code.
- Next: the last two factories hook 2 needs, then register hook 2 and run the probe. They are aircraft_create
  0xf4a8 (1.0.3's 0xe0ae extended: player selection and per-host tank / bomb loading) and the kind-99 class
  0x1ec7e (with 0x1a936, 0x1eeee and 0x18712).

### Cycle 99 checkpoint — aircraft_create

- Cycle 98 validation: all Leyte tests and QA passed; 49 GiB free.
- aircraft_create (Leyte 0xf4a8, 1.0.3 0xe0ae) reconstructed from the disassembly. Its second argument is the
  definition key, and the key now drives it:
  - the player is the first aircraft of key 1 while *0x20b28 is 0 (1.0.3: the first aircraft);
  - the player's stores load from three host bytes (+0xb9 bombs, +0xbb into +0x68e, +0xbd into +0x690);
  - its weight word +0x642 gets 16 per bomb plus 5 per unit of +0x68e;
  - its tanks scale by host word +0xb6 percent, the left one forced to 1 when both come out empty;
  - 1.0.3's host-mode-2 loading is gone;
  - the shadow kind follows the key: 1 -> 1, 2 -> 5, 3 -> 6 (the first key 3 claims 0x21074), others none;
  - kinds 5 and 6 add a bomb and 0x10 of weight.
- 7 cases through the real shadow spawn: the player with stores loaded, the player with tanks scaled empty,
  key 1 with a player already, keys 2 and 3 (0x21074 free and claimed), and a key without a shadow. The expected
  values were checked by hand: weight 0x13a, tanks -15, the empty-tank rule.
- Overlay reconstructed fixture: 486 cases, all matching Leyte's code.
- Next: the kind-99 class 0x1ec7e (with 0x1a936, 0x1eeee and 0x18712), then register hook 2 and run the probe.

### Cycle 100 checkpoint — the kind-99 route class; hook 2 registered

- Cycle 99 validation: all Leyte tests and QA passed.
- 0x1ec7e (the class of definitions 0x27 / 0x28) reconstructed with its three helpers, all Leyte-only. Index
  scales were decoded from the extension words, since capstone drops them.
  - The class: a 0x634-byte controller. Kind 99 builds a route; a route ship takes its next waypoint and turns
    toward it; then the pose rings at +0x32.
  - 0x1a936 leyte_build_route: the first flagged, controller-less kind-2 shape up the owner chain. Its face
    vertices (shifted by +0x36) are copied after an 8-byte header and the nearest index recorded; none -> 0,
    and the kind is cleared.
  - 0x1eeee leyte_next_waypoint: x/z distance to the current point; within 0xa9 it advances, wrapping at the
    count. Without a route, or with +0x62d bit 1 set, it uses the static point 0x2cdf8 from +0x622 / +0x626.
  - 0x18712 leyte_heading_to_point: halves |dx| + |dz| until it fits in 15 bits (andi.w leaves the upper word
    for the tst.l), then attitude_from_velocity (0x2554) for the heading alone.
- 7 cases: kind 99 with a route (nearest 1, stepped to 2, heading set), kind 99 without a route shape (kind
  cleared), kind 5; the static waypoint near and far; the heading with small deltas and past 15 bits.
- Overlay reconstructed fixture: 493 cases, all matching Leyte's code.
- Every class factory the hook-2 probe reached is now written, so hook 2 is registered again; the start probe
  is running.

### Cycle 101 checkpoint — hook 2 live; the generated port's keep rule rebuilt on evidence

- Start probe with hook 2 registered (cycle 100's routines): clean. The mission scene starts on Leyte's code,
  its objects instantiated through the class factories, and the flight loop is entered.
- Planning moved from one start probe per divergence to the whole call graph:
  `tools/leyte/overlay_status.py` (read-only) lists, for every 1.0.3 overlay port function, whether Leyte has
  it (kept by the generated port / reconstructed / missing), each of the 22 hooks' closure, and the frontier.
- The generated port's keep rule (`tools/leyte/gen_overlay_port.py`) was rebuilt. Before: a function proven
  instruction for instruction was kept only when its whole GDScript call closure was verified, and any hex
  literal inside the image that the map did not reach refused it, plain numbers such as 0x3fff included.
  76 functions were kept and 83 proven ones refused. Now a proven function is kept when its own body is
  sound; a callee that is not kept stays a refusing stub (assert), never a silent success.
- Cross-check, and what it found. The 34 hand-written routines the first version of the new rule also kept
  were removed from the reconstruction and their emulator cases run against the generated versions. 31
  passed; 3 generated functions were wrong although proven by instruction. Each cause is closed in the
  generator, not worked around:
  - a proof cut short: Ghidra lists a false entry inside some routines (FUN_00003be0 inside
    update_object_camera_metric, FUN_00001db8 inside sin_fixed14), and the proof stopped there, never
    seeing the tail (the metric's store, which Leyte moved from +0x10 to +0x1c). The proof now covers the
    routine to its natural end; an entry the code runs past, where the port has no function, does not end it;
  - an offset no instruction holds: a body reading the low byte of a word field (host +0x1e7 of the word at
    +0x1e6) kept 1.0.3's offset. Every offset a body adds must now be explained by the function's own
    operands: a displacement, an immediate, a byte or word inside a field (it moves with the field: the
    manifest's derived_fields), or the sum of two operands; otherwise the function is refused;
  - a helper kept without evidence: _heading_tape_finish held host offset 0x2c2, which no aligned instruction
    pair had ever shown. A private helper is now kept only on the evidence of every kept function it is a
    piece of.
  - Also: a literal counts as a plain number only on the function's own operands (or as a bit mask, or one of
    a few word-arithmetic constants no relocation of the overlay names); static functions can be proven;
    sin_fixed14 / cos_fixed14, which pass each other's proof, are placed by the overlay map's hook pairs.
- Result: 152 functions and 58 helpers kept (76 and 46 before), none of the earlier ones lost. 36 hand-written
  routines are superseded by generated, proven ones and removed from the reconstruction
  (docs/architecture/leyte_overlay_superseded_overrides.md keeps their text); their 493-case fixture passes on
  the generated versions.
- The base overlay test now runs the port as the game does (the reconstruction over the generated port). A
  translated 1.0.3 case is left out when Leyte's run enters a routine the port has not got, when the function
  is made of a refusing private helper, or when it calls a null code pointer. Entries are counted only when
  a call or jump reaches them. 280 translated cases over 45 functions (38 over 18 before), all matching.
- Names aligned with 1.0.3's identities so kept callers find them: matmul3x3_s16 (0x2326, with the D0 it
  leaves), _distance_band, _aircraft_common, create_cloud_controller (0x1db7a, proven by
  register_cloud_templates' redirect), create_view_attached_controller (0x1dce2). What I had called the cloud
  registration (0x1ed5c) is the route ship's: leyte_register_route_ship_templates.
- Hooks 18 (isqrt_floor) and 21 (spawn_cloud_layer) registered. Start probe: clean; only hooks 10
  (overlay_frame_step) and 14 (process_flight_input_and_outcome) are reported missing on that path.
- Validation: all Leyte tests pass, QA passes, 51 GiB free.
- Scale, from overlay_status.py: 542 port functions; 210 kept, 92 reconstructed, 240 missing. Hook 10's
  closure is 176 functions with 45 missing; hook 14's is 121 with 33 missing.

### Cycle 102 checkpoint — routine placement, the frame step's first routines (loop stopped here)

- `tools/leyte/overlay_placement.py` -> `sources/leyte-gulf-1-0/overlay_placement.json`: 400 of 428 1.0.3 overlay
  routines placed in Leyte's overlay by following the code, and 518 address pairs beyond the overlay map. Seeds: the
  map's text pairs, whole-alignment correspondence rows, and routines with hand-written cases. Then votes from call
  targets, relocated longs and PC-relative operands in the aligned instructions, and similarity for the rest. Of the
  functions verified at their correspondence or relocation place, 133 agree with the placement, 0 differ and 9 are
  unplaced. Added to QA (--check).
- gen_overlay_port.py tries a refused function at its placed routine and reads (An) as displacement 0: 161 functions
  kept (19 through the placement). 3 more hand-written routines are superseded (archived in
  leyte_overlay_superseded_overrides.md).
- New tools: `draft_overlay_port.py` (a Leyte draft of a 1.0.3 port function from the placed pair, for hand porting)
  and `overlay_case_coverage.py` (which instructions no emulator case executes). Coverage found an
  apply_controller_velocities case setting the wrong flag byte, and spawn_cloud_layer's cloud path never run; both
  fixed with cases. overlay_status.py shows each missing routine's Leyte address and match counts.
- 1.0.3's translated cases also run against hand-written routines: 478 cases over 82 functions.
- Hook 10 batch 1 written with cases: transform_int3_vecs_asr_by_1d744, transform_vecs12_by_matrix3,
  scale_int3_vecs, apply_controller_velocities, tick_flagged_object_controllers, age_contact_records,
  build_visible_object_list, emit_faces_in_order / _unnumbered. 521 hand-written cases.
- Validation: overlay tests, all Leyte tests and QA pass. Start probe clean through the flight loop; hooks 10 and 14
  missing. Free disk is down to 13 GiB.
- The autonomous loop was cancelled at the user's request. Prepared but NOT applied (session scratchpad): hook 10
  batch 2 (project_prepared_object, transform_face_vertices, the face projections, emit_faces_bsp,
  collect_near_objects) and the own-routine label rule, which in a trial build keeps scan_convert_edge_rows.
