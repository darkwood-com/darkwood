# Game runtime: running the Original edition in Godot

`godot/original/Game.tscn` runs the original program (`StrictID02.main`, CODE 2 0x309a) from
the Original port on a Macintosh built in GDScript under `godot/original/mac/`. `godot/original/` is
not edited: the runtime provides what the port calls through its tables.

## Architecture

| Part | File | Role |
|---|---|---|
| Machine | `original/mac/machine/mac_machine.gd` | One flat 68000 address space (the Original port's `Memory68k`), its memory map, low-memory globals, missing-feature log |
| Segments | `original/mac/machine/segment_loader.gd` | The CODE 2..9 ports on that memory and A5, linked through the CODE 0 jump table (`original/mac/data/jump_table.json`); unported slots go to runtime stand-ins |
| Toolbox | `original/mac/toolbox/*.gd` | The trap table: every A-line word starts as a logging stand-in; the managers install what they implement |
| Thread | `original/mac/machine/game_thread.gd` | `main()` never returns and polls for events, so it runs on its own thread |
| Screen | `original/mac/machine/mac_screen.gd`, `.gdshader` | The 640 x 480 screen, 8 bits a pixel by default, 4 or 1 (`MacMachine.screen_depth`; the device, ports and screenBits follow it), on a 32-bit Mac or a 24-bit one with a 32-bit screen (`MacMachine.addressing_24`), shown as palette indices through the device's colour table |

`tools/runtime/build_runtime_data.py` writes `original/mac/data/` from the original files: the jump
table (175 entries, 117 targeting ported routines), the resource names, and the Pacific Conflict
scenery file (`--check` verifies them).

Memory map (24-bit, `original/mac/machine/mac_memory.gd`: the high address byte is ignored, as by the
Memory Manager of the game's era): the overlay at its base 0 (the overlay port addresses its
data by file offset - 0x20), low-memory globals sharing that range, A5 at 0x0c0000, the stack
below 0x0ff000, the heap from 0x100000, the sound buffer at 0x6f0000 and the screen at 0x700000.

Start-up as the original's C runtime (CODE 1 0x34) does it: the A5 world from DATA 0 (words, each
zero word followed by a ZERO 0 count of zero bytes) up to A5, then DREL 0's cells relocated by A5.

Overlay loading (`original/mac/machine/overlay_loader.gd`): ID08 reads the scenery file, allocates the
image and relocates it by its address. The runtime gives the image the address 0x01000000 (low 24
bits 0), takes that base off the relocated longs again and puts back the low-memory globals the
image covered, with the values they held when the file was opened (on the Mac the image never
touches them; a reload from File > Open… must not undo what the program set since, such as the
SoundHook), so the bytes sit where the overlay port reads them; then it links the overlay's
entry and hooks (`original/mac/data/overlay_methods.json`: ported routines by runtime address) and
gives the overlay the host's jump-table slot addresses (the service table ID02 0x1b42 fills). Calls
through code addresses follow C: a caller may pass more arguments than the routine declares (the
definition pre-render hooks get the controller and a phase), and the extra ones are dropped, as
Godot would otherwise skip the call without a word.

Calling conventions: a port passes a call's arguments last-pushed first. That is the parameter
order of MPW C routines; the CODE 9 glue is Pascal, so the slot reverses them for it.

Text: the resource fork holds no FONT / NFNT and Godot ships no Chicago or Geneva, so
`original/mac/toolbox/glyph_cache.gd` rasterises the nearest installed face without antialiasing
(on the main thread, before the game thread starts) into one-bit glyphs QuickDraw draws.

Arguments a port passes by value where the original passed an address (rects and strings its
test stubs logged by value) are read either way; a rect changed in place through such a value
(InsetRect, OffsetRect) keeps its change for the next use of the same value.

QuickDraw polygons record the pen's moves between OpenPoly and ClosePoly and fill by scanlines;
regions are their bounding rects (OpenRgn / CloseRgn record the extent drawn). Every ported
overlay routine is reachable by its runtime address (the overlay jumps through the addresses it
stores for controller ticks and collision hooks); the ones outside the 740 are logged stand-ins
until written.

The stack: every routine the runtime calls from outside the ports (a hook, a procedure pointer, an
adapter) gets a stack pointer below all the ports' frames (`MacMachine.free_stack`) and has its
own restored afterwards; without the restore the frames sank a little each frame until they
overwrote the overlay's data (the span run-writer tables at 0x20cec).

Ported routines the ports call by offset (`_local`) or by stored code address are all registered
(`original/mac/data/segment_methods.json`, and the overlay's routines by runtime address, including
the second entries the port documents inside another routine's extent); a call to a routine
without a result answers 0.

Colour: a colour port keeps the pixel values it draws with in fgColor / bkColor (+0x50 / +0x54),
as Color QuickDraw does; the program both sets RGB colours and stores device indices there itself.
The screen device's 256 colours (`original/mac/toolbox/palette_manager.gd`) start as the default 8-bit
table and follow the program: SetEntries, its palettes (SetEntryColor / SetEntryUsage, SetPalette /
ActivatePalette, AnimateEntry / AnimatePalette); palette entry i owns device index i + 1 (0 and 255,
white and black, stay the system's), PmForeColor leaves that index in the port, and the Godot
screen redraws through the table when it changes. CopyBits follows a colour port's portBits (the
PixMap handle, marked by the rowBytes field's two high bits).

Sound: the program mixes its channels itself into the Mac's sound buffer (SoundBase, 370 samples
every tick) from the VBL handler it puts in the SoundHook; the runtime runs that hook once per
elapsed tick at the program's trap calls, as the interrupt would arrive between them
(`original/mac/toolbox/sound_manager.gd`), and `original/mac/machine/mac_speaker.gd` plays the samples at
22254.5 Hz. Saving: the resources the program writes back (the pilot roster, the settings) are
kept under Godot's `user://resources/<type>/<id>.bin` and read before the originals; delete that
folder to start afresh.

Regions are kept as their bounding rects, except a region framed from one oval alone (OpenRgn /
FrameOval / CloseRgn): it also keeps the oval, so the instrument faces drawn through such a clip and
the artificial horizon's row spans (PtInRgn) come out round. The instrument panel is built once and
cached by the program as 'HEL2' resources (saved under `user://resources`): a change to the panel
drawing needs that folder cleared to be seen.

Between flights (Escape interrupts one) the menus and their ⌘ keys work: Mission Status, Mission
Map, Instant Replay, Resume, the Options and Scenery toggles.

Input during a flight is the original's: the mouse is the stick (its button the guns) and only
keys are read: run_flight_session (ID02 0x4fc) sets the event mask to 0xfff9 (SetEventMask), so
mouse-down and mouse-up are not even queued (event_manager.gd). Its own ⌘-key switch (table 0x650,
on key-up) knows only ⌘T, ⌘1..5, ⌘Q, ⌘S and ⌘E; any other ⌘ key, such as ⌘M (Mission Map) or ⌘X,
does nothing until the flight is interrupted. HideCursor / ShowCursor / InitCursor keep the Mac
cursor's level, which hides Godot's pointer during the flight.

The Original port passes the rects that traps change in place (InsetRect, OffsetRect) by their
frame address, as the original pushes them, so the change lands where the program reads it back;
read-only rects may still come by value (two longs). FSRead's count is the caller's long too: the
bytes read are written back into it. Pictures (DrawPicture, dialog picture items) are decoded from
the mirror's PNG on the game's thread without making textures.

Routines outside the 740 (Ghidra never defined them) are written from their disassembly in
`original/mac/machine/unexported.gd` and registered where the port calls them.

`tools/qa/test_regression.py` (`check_runtime_coverage`) keeps the runtime complete: every
jump-table slot and segment-local offset the Original port calls, every slot the CODE segments hand
out as a procedure, every code pointer in the overlay's relocation table and every trap word the
ports use must have a routine behind it, so a gap fails the check before anyone plays into it.

Those written routines are also checked against their original bytes where they stand alone:
`tools/runtime/gen_runtime_cases.py` runs them on the 68000 interpreter (`tools/original/
m68k_emu.py`) and `godot/tests/original/mac/test_unexported_routines.gd` replays the cases on the
runtime, comparing the D0 and the memory each leaves (ansi_strlen, ansi_strncmp, lock / unlock_
session_handles, save_changed_settings, restore_screen_globals, apply_display_palettes,
install_overlay_service_table, release_panel_bitmaps, report_panel_store_failure,
redraw_panel_readouts_locked, end_overlay_session, install_default_ring_item,
set_radio_group_selection, track_summary_text_click, quit_application, draw_rank_insignia,
draw_mission_awards, run_map_dialog, track_map_bearing_and_distance, run_refuel_dialog,
run_scenario_selection_dialog, run_duty_roster_dialog, run_high_scores_dialog,
mission_summary_filter_proc, advance_mission_summary_text (every stage) and release_render_buffers
(with the calls they make, overlay hooks and Dialog, Control Manager and TextEdit traps, DisposPtr
and the overlay's heading and square-root services included), arm_restore_gate,
build_protection_code_strings, the grey-map accessor, carrier_deck_contact on its own image,
player_left_ground_zone, the 1-bit erase hook and redraw_view_readouts (its four drawing callees
stubbed): every written routine but the IUCompString glue, whose comparison is the System's _Pack6
and not in the game's bytes; Toolbox traps and Pascal glue stubbed on the emulator side).

Stepped time makes a run reproducible: `MacMachine.use_stepped_clock(steps, stop)` sets Ticks
to 0 and moves it only at the program's own waits (`idle`: a null event, a dialog or menu waiting
for the mouse) and every 64 TickCount reads of a busy loop, never with the host clock. The steps,
`[tick, Callable(machine)]`, post input on the program's thread when their tick comes; `on_tick`
records state; at `stop` the program parks. `tests/helpers/state_trace.gd` captures a traced tick
(MD5s of the A5 world and of the overlay's image, the overlay's random-number table exactly, and the
same two without what an edition may keep elsewhere: the sound driver's A5 block, low memory; not
the screen, whose text depends on the host's fonts) and names the first tick and field where two
traces differ. `tests/original/mac/test_stepped_run.gd` runs the game twice that way (Return at the
copy protection, 600 ticks, traced every 30) and requires both runs and the recorded Original trace
(`fixtures/stepped_trace.json`, rewritten with `HELLCATS_RECORD_TRACE=1`) to agree. Those 600
ticks stay in the opening dialogs. The smoke session's `--stepped` runs any of its scripts the same
way (steps at tick seconds * 60) and `--trace` / `--expect` write or check a trace: the recorded
flight is `--mission 2 --seconds 60 --stepped --expect res://tests/original/mac/fixtures/stepped_mission2_trace.json`
(120 entries; the A5 world, the overlay's objects and the random-number table all move, about
a minute of host time).

A trace compares only with one from the same scenario: the same input script, initial state,
version, edition fields and trace schema. The three references:

| Scenario | Reference | Check |
|---|---|---|
| Deterministic stepped test (600 ticks, Return at the copy protection) | `fixtures/stepped_trace.json` | `tests/original/mac/test_stepped_run.gd` (in `run_all.gd`); never a smoke-session `--expect` target: the smoke session's input script differs, so its trace parts from it at the first scripted step |
| Mission 2, 60 s | `fixtures/stepped_mission2_trace.json` | `smoke_session.gd -- --mission 2 --seconds 60 --stepped [--edition native] --expect res://tests/original/mac/fixtures/stepped_mission2_trace.json` (Native compares `NATIVE_FIELDS`) |
| Full smoke session (191 steps) | none recorded: Original and Native against each other | `smoke_session.gd -- --stepped --trace <a>` and `-- --stepped --edition native --trace <b>`, then every entry's `game_globals` / `world` / `rng` equal (852 entries) |
| Full smoke session, Hellcats 1.0.4 | none recorded: Original and Native against each other | the same with `--version 1.0.4` on both runs (852 entries; Native keeps 1.0.4's own sound driver, as `native_game.gd` does) |

State an edition keeps outside the Mac memory is traced from where it lives: `rng` from the port's
`rng_bytes()`, and in `world` the blocks the port lists in `native_state_ranges()` (Native's
Godot-backed replay camera, 0x1d814 and 0x24d18, camera angles 0x1d800..0x1d811, camera position 0x1d830..0x1d83b and view state 0x1d856..0x1d859) take the place of the memory's bytes, so the hash
covers the values Native actually holds; the memory's bytes there are not Native's state.

An end-to-end session is `tests/original/mac/smoke_session.gd`, kept out of `run_all.gd` because it runs
for minutes of real time: `Godot --headless --path godot --script res://tests/original/mac/smoke_session.gd`.
Its header lists the phases (the missions played, the menus by key and by mouse, the dialogs) and
the options, among them the runs the evidence merges. It fails when the program's thread dies, the runtime notes anything missing or a script error happens, and it saves
into `user://smoke_session_resources`, never the player's `user://resources`. Its `--coverage` runs
are the evidence behind the mappings' `runtime_verified`, with `tests/original/mac/protection_session.gd`'s for
the copy-protection challenge the smoke session never meets (`tests/original/mac/fixtures/smoke_coverage.json`,
read by `tools/original/check_sync.py`); what did not run, and why (unreachable
in the original, or what it needs: a failure, another machine or scenery, longer or flown play),
is [`RUNTIME_COVERAGE.md`](../generated/RUNTIME_COVERAGE.md) (generated by `tools/original/coverage_gaps.py`).

### Traps the game never issues

The Toolbox installs 294 trap words. Scanning the original bytes at instruction boundaries (the host
segments ID01..ID08 and the *Pacific Conflict* code; ID09's glue is answered by stand-ins instead) finds
174 issued words (auto-pop variants folded). Of the other 120, three are still reached another way
(_Open $A000 and SwapMMUMode $A05D by the Original port of ID09's glue, FlushEvents $A032 by a
stand-in).
The remaining 117 have no user besides their installation, in the game's bytes, the Original port, the
runtime or the tests, in 1.0.3; Hellcats 1.0.4's sound driver (`port/ID02_104.gd`) uses three of them:
_SoundDispatch $A800 (SndPlayDoubleBuffer), NewPtrClear $A31E and DisposePtr $A01F. They are cleanup candidates, kept for now because another scenery file opened
with File > Open... could issue them (counts and list checked against the bytes by
`tools/qa/test_regression.py`, optional-unused-traps):

$A001 $A002 $A01C $A01F $A021 $A023 $A027 $A029 $A02A $A036 $A049 $A04A $A063 $A069 $A06A $A11D
$A11E $A122 $A31E $A322 $A800 $A802 $A803 $A804 $A806 $A81F $A820 $A827 $A828 $A851 $A856 $A860 $A86D
$A87C $A886 $A889 $A896 $A897 $A89E $A8A4 $A8AA $A8AB $A8B1 $A8B2 $A8B3 $A8B9 $A8C8 $A8C9 $A8D2
$A8D4 $A8D5 $A8DC $A8DD $A8DE $A8E0 $A8E4 $A8E5 $A8E7 $A8F5 $A904 $A910 $A914 $A917 $A918 $A919
$A91A $A925 $A927 $A929 $A92A $A92D $A936 $A93B $A944 $A949 $A94C $A950 $A956 $A95E $A961 $A962
$A964 $A969 $A96D $A971 $A976 $A977 $A978 $A97F $A984 $A987 $A992 $A9A4 $A9A5 $A9CB $A9CE $A9CF
$A9D1 $A9D8 $A9D9 $A9DA $A9DB $A9DC $AA15 $AA19 $AA1A $AA33 $AA34 $AA92 $AA93 $AA94 $AA96 $AA98
$AA99 $AA9A $AA9B $AA9F

## Routines outside the 740

Ghidra never defined these 35 routines, so they have no `.c` export and stay outside the registry;
the ones marked *decomp* are described in their segment's `.decomp.c` (an "Unexported function"
block, or the comment of the routine around them), with the semantic name used here. Each is written from the disassembly of its bytes; its start was checked to be a
routine boundary (a `LINK`, or the instruction after an `RTS` / `JMP (A0)`). Segment offsets are in
the CODE resource (`sources/resedit-1-0-3/CODE/<ID>.bin`); overlay addresses are runtime
addresses, the scenery file's offset less `0x20`. "Reached by" is how the port calls it: a
jump-table slot (`$offset`), its segment offset (local), or a code address.

| Segment | Offset | Reached by | Runtime | What it does |
|---|---|---|---|---|
| ID02 | 0x1b42 | local | `unexported.gd` `id02_1b42` | `install_overlay_service_table`: main's set-up (*decomp*) |
| ID02 | 0x4d24 | local | `id02_4d24` | `apply_display_palettes`: palette for the depth, index maps, the renderer (*decomp*) |
| ID02 | 0x4d96 | local | `id02_4d96` | `restore_screen_globals`: the screen device and globals (*decomp*) |
| ID02 | 0x04a4 | local | `id02_04a4` | `end_overlay_session`: end of an overlay session (sound shut down) (*decomp*) |
| ID02 | 0x095a | local | `id02_095a` | `lock_session_handles`: the first of nested calls (*decomp*) |
| ID02 | 0x09fa | local | `id02_09fa` | `unlock_session_handles`: the last of nested calls (*decomp*) |
| ID02 | 0x0a26 | local | `id02_0a26` | `redraw_panel_readouts_locked`: the overlay's readouts hook (*decomp*) |
| ID02 | 0x2da0 | local | `id02_2da0` | `save_changed_settings`: changed settings written (*decomp*) |
| ID02 | 0x2eaa | local | `id02_2eaa` | `quit_application`: asking to save changed settings (*decomp*) |
| ID02 | 0x0434 | `$502` | `id02_0434` | `ansi_strncmp` (*decomp*) |
| ID02 | 0x03e8 | `$522` | `id02_03e8` | `ansi_strlen` (*decomp*) |
| ID03 | 0x59f4 | local | `id03_59f4` | `release_panel_bitmaps`: the panel session handles (*decomp*) |
| ID04 | 0x0432 | `$172` | `id04_0432` | `report_panel_store_failure`: the event loop's pending message (*decomp*) |
| ID04 | 0x01b8 | local | `id04_01b8` | `install_default_ring_item`: the default button's ring user item (*decomp*) |
| ID04 | 0x02dc | local | `id04_02dc` | `set_radio_group_selection`: a radio group's selection (*decomp*) |
| ID04 | 0x0644 | `$132` | `id04_0644` | `run_duty_roster_dialog`: the Active Duty Roster dialog (*decomp*) |
| ID04 | 0x0c02 | `$82` | `id04_0c02` | `run_scenario_selection_dialog`: the Select a Mission dialog (*decomp*) |
| ID04 | 0x0e70 | `$9a` | `id04_0e70` | `run_refuel_dialog`: Refuel and Reload after a landing (*decomp*) |
| ID04 | 0x137a | `$102` | `id04_137a` | `run_high_scores_dialog`: the High Scores dialog (*decomp*) |
| ID04 | 0x1752 | local | `id04_1752` | `track_summary_text_click`: a click on the summary's scroll bar (*decomp*) |
| ID04 | 0x1b3e | `$15a` | `id04_1b3e` | `draw_rank_insignia`: the rank insignia user item (*decomp*) |
| ID04 | 0x1dde | `$152` | `id04_1dde` | `draw_mission_awards`: the medals user item (*decomp*) |
| ID04 | 0x2104 | `$ca` | `id04_2104` | `mission_summary_filter_proc`: the mission summary's dialog filter (*decomp*) |
| ID04 | 0x21d6 | local | `id04_21d6` | `advance_mission_summary_text`: the mission summary's text, one step a call (*decomp*) |
| ID04 | 0x3614 | local | `id04_3614` | `track_map_bearing_and_distance`: a click on the map, its heading and range (*decomp*) |
| ID04 | 0x3976 | `$10a` | `id04_3976` | `run_map_dialog`: the Mission Map dialog (*decomp*) |
| ID05 | 0x03ba | local | `id05_03ba` | `build_protection_code_strings`: the copy protection dialog's strings (*decomp*) |
| ID05 | 0x08b2 | `$532` | `id05_08b2` | `arm_restore_gate`: the protection counter (*decomp*) |
| ID06 | 0x00ee | `$1d2` | `id06_00ee` | the grey map pointer, the 1-bit view buffer base |
| ID06 | 0x0858 | `$1b2` | `id06_0858` | `release_render_buffers`, with `free_pattern_tables` (0x15a) and `free_edge_pool` (0x582) inlined (*decomp*) |
| ID09 | 0x03d6 | `$2ea` | `toolbox.gd` `iu_comp_string` | `iucompstring_glue`, IUCompString through `_Pack6` (*decomp*) |
| Pacific Conflict | 0x04b0 | hook | `overlay_loader.gd` `_04b0` | `player_left_ground_zone`: more than 4999 above its ground (*decomp*) |
| Pacific Conflict | 0xd23a | hook | `_d23a` | `redraw_view_readouts`: the readouts, gauges and overlays (*decomp*) |
| Pacific Conflict | 0x16208 | code address | `_16208` | the 1-bit renderer's erase hook: `clr.w $1da8c` |
| Pacific Conflict | 0x15134 | code address | `_15134` | `carrier_deck_contact`: the deck as ground (*decomp*) |

## Progress

| Milestone | State |
|---|---|
| 1. Machine, jump-table linking, trap table, game thread, screen scene | done |
| 2. Memory Manager, Resource Manager (from the resource mirror), Color QuickDraw device, SysEnvirons | done |
| 3. `initialize_application` runs to its end; InitWindows draws the desktop and menu bar | done |
| 4. Overlay load: File Manager, the A5 world from DATA / ZERO / DREL, the image at runtime base 0, overlay entry, hooks and host services | done |
| 5. The routines outside the 740, written from their bytes ([index](#routines-outside-the-740)) | done |
| 6. QuickDraw (ports, patterns and modes, lines, rects, round rects, ovals, regions as rects plus single-oval regions for clipping and PtInRgn, text from a glyph cache, CopyBits, pictures, colour icons), Window / Dialog / Control (scroll bars: NewControl, FindControl, TrackControl) / Menu managers, TextEdit, Standard File's Get File (File > Open…, over the scenery volume; the overlay reloads), palettes | done |
| 7. Event Manager from Godot input (mouse, Mac key codes and GetKeys, ticks), update and activate events | done |
| 8. Pilot roster, mission selection, the 3D flight view from the carrier's deck, the cockpit view (V) with its instrument panel, keyboard and mouse input, ejecting, and the mission summary (its text, scroll bar, medals and rank); sound (the VBL sound hook run every tick into SoundBase and played through `original/Game.tscn`'s Speaker; SndPlay of 'snd ' resources); saving (WriteResource / UpdateResFile kept under `user://resources`) | done |
| 9. `launcher/Main.tscn` runs the game (Godot quits cleanly with the program's thread parked); the MVP prototype (its scenes, `hellcats/` scripts, data, tests and fixtures) removed, the QA checks moved to the game runtime | done |
