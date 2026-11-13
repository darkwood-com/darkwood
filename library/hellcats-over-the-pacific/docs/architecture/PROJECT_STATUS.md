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
