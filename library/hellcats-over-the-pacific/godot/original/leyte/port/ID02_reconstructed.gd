class_name LeyteReconstructedID02
extends "res://original/leyte/port/ID02.gd"
## Leyte Gulf's CODE 2 functions that 1.0.3's port cannot give (sources/leyte-gulf-1-0/host_ports.json
## "unverified": not found in Leyte, or differing), written from Leyte's own bytes
## (sources/leyte-gulf-1-0/CODE/ID02.bin, address = file offset) over the generated partial port
## (original/leyte/port/ID02.gd, which refuses them). Each is checked against Leyte's code on the
## 68000 emulator (tools/leyte/gen_leyte_id02_reconstructed_cases.py,
## tests/leyte/port/test_leyte_id02_reconstructed.gd). Arguments to stand-ins are listed from the top
## of the stack (the last pushed first), as in the 1.0.3 ports.

const LEYTE_JT_GET_PTR_SIZE: int = 0x252     ## GetPtrSize (CODE 9 +0x206: _GetPtrSize $A021 glue), Pascal
const LEYTE_LOCAL_AFTER_ENTRY: int = 0x54b8  ## apply_display_palettes (below; 1.0.3's 0x4d24, unexported)
const LEYTE_LOCAL_FINISH: int = 0x2db2       ## apply_settings_to_live_options (below)
const LEYTE_LOCAL_SELECT_PALETTE: int = 0x4daa   ## select_palette_for_depth (correspondence: 1.0.3 0x462c)
const LEYTE_LOCAL_REBUILD_MAPS: int = 0x4e32     ## rebuild_all_palette_index_maps (1.0.3 0x46b4)
const LEYTE_LOCAL_REDRAW: int = 0x5544           ## called when a setting changed (1.0.3: redraw_main_window)

## 0x245a (JT $51a, called by the CODE 8 loader once the overlay is read and relocated): the overlay's
## bss (after its text and data: A5 - 0x1048 + - 0x1064 + - 0x1060, A5 - 0x105c bytes) and the block
## A5 - 0x1082 (its GetPtrSize) cleared with memset; the overlay's entry (A5 - 0x1048 + the a.out
## a_entry, A5 - 0x1054) called with (A5 - 0x1406, -1, TickCount): one argument more than 1.0.3's
## (A5 - 0x1388, TickCount); then the segment's routines 0x54b8 and 0x2db2.
func reset_overlay_state() -> void:
	var g: Callable = func(off: int) -> int: return memory.read_u32(C68kScript.u32(a5 + off))
	ansi_memset(C68kScript.u32(g.call(-0x1048) + g.call(-0x1064) + g.call(-0x1060)), 0, g.call(-0x105c))
	ansi_memset(g.call(-0x1082), 0, C68kScript.u32(_jt(LEYTE_JT_GET_PTR_SIZE, [g.call(-0x1082)])))
	var target: int = C68kScript.u32(g.call(-0x1048) + g.call(-0x1054))
	var ticks: int = C68kScript.u32(_trap(TRAP_TICK_COUNT, []))
	_call_address(target, [C68kScript.u32(a5 - 0x1406), 0xffffffff, ticks])
	_local(LEYTE_LOCAL_AFTER_ENTRY, [])
	_local(LEYTE_LOCAL_FINISH, [])

## 0x54b8 (1.0.3's 0x4d24, apply_display_palettes): the segment's routines 0x4daa
## (select_palette_for_depth) and 0x4e32 (rebuild_all_palette_index_maps), then the overlay's hook
## A5 - 0x10e4.
func apply_display_palettes() -> void:
	_local(LEYTE_LOCAL_SELECT_PALETTE, [])
	_local(LEYTE_LOCAL_REBUILD_MAPS, [])
	_call_address(memory.read_u32(C68kScript.u32(a5 - 0x10e4)), [])

## 0x2db2: the settings record (*(A5 - 0x142a), words) copied into the live options, as 1.0.3's
## 0x2a44 with Leyte's globals: +2 / +4 -> A5 - 0x136c / - 0x136a, +0xc -> - 0x1368 and - 0x107e;
## +0x10 / +0x18 / +8 / +0x14 -> - 0x135e / - 0x1356 / - 0x1364 / - 0x135a (a change with an overlay
## image, A5 - 0x1048, runs the hook A5 - 0x10d4); +0x16 -> - 0x1358 (hook - 0x1090); at depth 8
## (A5 - 0x13f8) +0x12 -> - 0x135c (hook - 0x10d8) and +0x1a -> - 0x1354, else both cleared; +0x22 /
## +0x24 -> - 0x1350 / - 0x134e; two fields 1.0.3 does not copy, +0x28 / +0x26 -> - 0x134c / - 0x134a;
## +6 / +0x1c / +0xa -> - 0x1366 / - 0x1352 / - 0x1362. Any hook run: the segment's 0x5544.
func apply_settings_to_live_options() -> void:
	var changed: bool = false
	var s: Callable = func(off: int) -> int:
		return memory.read_u16(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a))) + off)
	var g: Callable = func(off: int) -> int:
		return memory.read_u16(C68kScript.u32(a5 + off))
	var put: Callable = func(off: int, v: int) -> void:
		memory.write_u16(C68kScript.u32(a5 + off), v)
	var image: Callable = func() -> bool:
		return memory.read_u32(C68kScript.u32(a5 - 0x1048)) != 0
	put.call(-0x136c, s.call(2))
	put.call(-0x136a, s.call(4))
	var drawn: int = s.call(0xc)
	put.call(-0x1368, drawn)
	put.call(-0x107e, drawn)
	var old: Array = [g.call(-0x135e), g.call(-0x1356), g.call(-0x1364), g.call(-0x135a)]
	put.call(-0x135e, s.call(0x10))
	put.call(-0x1356, s.call(0x18))
	put.call(-0x1364, s.call(8))
	put.call(-0x135a, s.call(0x14))
	if (g.call(-0x135e) != old[0] or g.call(-0x1356) != old[1] or g.call(-0x1364) != old[2] or g.call(-0x135a) != old[3]) and image.call():
		_call_address(memory.read_u32(C68kScript.u32(a5 - 0x10d4)), [])
		changed = true
	var prev: int = g.call(-0x1358)
	put.call(-0x1358, s.call(0x16))
	if prev != g.call(-0x1358) and image.call():
		_call_address(memory.read_u32(C68kScript.u32(a5 - 0x1090)), [])
		changed = true
	if g.call(-0x13f8) == 8:
		prev = g.call(-0x135c)
		put.call(-0x135c, s.call(0x12))
		if g.call(-0x135c) != prev and image.call():
			_call_address(memory.read_u32(C68kScript.u32(a5 - 0x10d8)), [])
			changed = true
		put.call(-0x1354, s.call(0x1a))
	else:
		put.call(-0x135c, 0)
		put.call(-0x1354, 0)
	put.call(-0x1350, s.call(0x22))
	put.call(-0x134e, s.call(0x24))
	put.call(-0x134c, s.call(0x28))
	put.call(-0x134a, s.call(0x26))
	put.call(-0x1366, s.call(6))
	put.call(-0x1352, s.call(0x1c))
	put.call(-0x1362, s.call(0xa))
	if changed:
		_local(LEYTE_LOCAL_REDRAW, [])

const LEYTE_JT_MAX_MEM: int = 0x22a          ## MaxMem(VAR grow): Pascal, long result (CODE 9 +0x1a0)
const LEYTE_JT_LOAD_OVERLAY: int = 0x1fa     ## CODE 8 load_overlay_from_data_fork (C, word result in D0)
const LEYTE_LOCAL_INITIALIZE: int = 0x32d8   ## initialize_application (1.0.3 0x2bbc)
const LEYTE_LOCAL_MAIN_SETUP: int = 0x1e58   ## 1.0.3's 0x1b42 (install_overlay_service_table)
const LEYTE_LOCAL_INSTALL_MENUS: int = 0x3838    ## install_menus (identical to 1.0.3's 0x30fa)
const LEYTE_LOCAL_START_MISSION: int = 0x3d38    ## start_new_mission (1.0.3 0x35e4)
const LEYTE_LOCAL_POLL_EVENT: int = 0x370c       ## poll_and_dispatch_event (1.0.3 0x2fd4)

## 0x37d2 (main: CODE 1 calls it through the slot its header names, $50a; 1.0.3's is $4b2). As 1.0.3's
## 0x309a with Leyte's offsets: A5 - 0x142c = -1; initialize_application (0x32d8); its segment
## unloaded (A5 + 0xda); MaxMem (grow at A6 - 6); 0x1e58; A5 - 0x1542 = 1; the overlay loaded from the
## data fork (JT $1fa, CODE 8: the word it returns kept at A6 - 2); the segments A5 + 0x1f2 and + 0x5ba
## unloaded; install_menus (0x3838); a new mission at once (0x3d38) when the loader returned nonzero,
## A5 - 0x1070 is set and there is an overlay image (A5 - 0x1048: a test 1.0.3 does not make); then
## poll_and_dispatch_event (0x370c) for ever (event_loop_passes: how many passes a harness lets run).
func main() -> void:
	assert(a7 != 0, "main needs a7 (MaxMem's grow is in its frame)")
	var entry: int = a7
	var a6: int = entry - 4
	memory.write_u32(a6 - 6, 0)
	memory.write_u16(C68kScript.u32(a5 - 0x142c), 0xffff)
	_local(LEYTE_LOCAL_INITIALIZE, [])
	_trap(TRAP_UNLOAD_SEG, [C68kScript.u32(a5 + 0xda)])
	_jt(LEYTE_JT_MAX_MEM, [C68kScript.u32(a6 - 6)])
	_local(LEYTE_LOCAL_MAIN_SETUP, [])
	memory.write_u16(C68kScript.u32(a5 - 0x1542), 1)
	var loaded: int = _jt(LEYTE_JT_LOAD_OVERLAY, []) & 0xffff
	memory.write_u16(a6 - 2, loaded)
	_trap(TRAP_UNLOAD_SEG, [C68kScript.u32(a5 + 0x1f2)])
	_trap(TRAP_UNLOAD_SEG, [C68kScript.u32(a5 + 0x5ba)])
	_local(LEYTE_LOCAL_INSTALL_MENUS, [])
	if loaded != 0 and memory.read_u16(C68kScript.u32(a5 - 0x1070)) != 0 and memory.read_u32(C68kScript.u32(a5 - 0x1048)) != 0:
		_local(LEYTE_LOCAL_START_MISSION, [])
	while event_loop_passes != 0:
		if event_loop_passes > 0:
			event_loop_passes -= 1
		_local(LEYTE_LOCAL_POLL_EVENT, [])

const LEYTE_JT_MORE_MASTERS: int = 0x232     ## MaxApplZone glue, CODE 9 +0x1b0 (1.0.3's $222; the name is 1.0.3's port's)
const LEYTE_JT_INIT_STEP: int = 0x2aa        ## MoreMasters glue, CODE 9 +0x290, twice (1.0.3: $292)
const LEYTE_JT_FLUSH_EVENTS: int = 0x212     ## FlushEvents(mask, stop): Pascal (CODE 9 +0x176)
const LEYTE_JT_EVENT_GLUE: int = 0x20a       ## CODE 9 +0x16e, (-1)
const LEYTE_JT_INIT_TOOLS: int = 0x5d2       ## CODE 7 +0x190 (1.0.3: $592)
const LEYTE_JT_CHECK_SYSTEM: int = 0xc2      ## CODE 4 +0x45b4: C, word result (0: ExitToShell)
const LEYTE_JT_SYS_ENVIRONS: int = 0x202     ## SysEnvirons(version, VAR rec): Pascal, word result
const LEYTE_JT_NEW_PTR_CLEAR: int = 0x242    ## NewPtrClear(size): Pascal, long result
const LEYTE_JT_NEW_HANDLE: int = 0x262       ## NewHandleClear(size) ($A322 glue): Pascal, long result
const LEYTE_LOCAL_AFTER_TOOLS: int = 0x2f42  ## no 1.0.3 counterpart at this point
const LEYTE_LOCAL_SCAN_DEVICES: int = 0x24c8 ## scan_display_devices (1.0.3)
const LEYTE_LOCAL_RESOURCE_ERROR: int = 0x356e   ## show_resource_file_error_box (1.0.3)
const LEYTE_LOCAL_QUIT: int = 0x35e2         ## 1.0.3's 0x2eaa (quit_application)
const LEYTE_LOCAL_SETTINGS_CHECK: int = 0x3082   ## C, word result: 0 sets the settings' defaults
const LEYTE_LOCAL_INIT_HEL1_228: int = 0x31d8    ## fills a new 'HEL1' 228
const LEYTE_LOCAL_CONFIGURE_DISPLAY: int = 0x2b48    ## configure_display_from_main_device (1.0.3)
const LEYTE_LOCAL_COPY_PSTRING: int = 0x361c ## copy_pascal_string (C: source, destination; JT $4e2)
const HEL1: int = 0x48454c31
const MENU: int = 0x4d454e55

## 0x32d8 (initialize_application): as 1.0.3's 0x2bbc, with Leyte's calls and records. MaxApplZone
## (JT $232) and twice MoreMasters (JT $2aa; the counter at A6 - 2); InitGraf on A5 - 0x168a (Leyte does not save
## CurResFile here), InitFonts, FlushEvents(all, 0), InitWindows, InitMenus, TEInit, InitDialogs(0),
## InitCursor, JT $20a(-1), JT $5d2, UnloadSeg A5 + 0x5da, JT $c2 (0: ExitToShell, falling through);
## then 0x2f42 (new in Leyte). SysEnvirons(1) at A6 - 0x1e: Color QuickDraw (+9) into A5 - 0x1406,
## a 68020 / 68030 (+6 = 1 or 2) into A5 - 0x1405; with colour 0x24c8 and a colour table header
## (NewPtrClear 0x6d8 into A5 - 0x1530: GetCTSeed, flags 0x8000; also A5 - 0x1394). Without MENU 2:
## SysBeep(20), 0x356e and 0x35e2. The settings 'HEL1' 227 (A5 - 0x142a; 1.0.3 'HEL1' 127): when
## missing, a new cleared handle (NewHandleClear) of 0x12e bytes added (name A5 - 0xc8a); 0x3082 returning 0 sets +0x10 = 2,
## +0xe = 1, +0x24 = 2, +0x2a = 1; +0x28 = 6 and +0x26 = 0 always; the name A5 - 0xc72 copied to
## +0x2e (0x361c); ChangedResource, WriteResource. Present: LoadResource. Then 'HEL1' 228 (A5 -
## 0x1426): missing, 0x500 bytes added (name A5 - 0xc66), filled by 0x31d8, changed and written;
## present, loaded. Last 0x2b48 and SetPort(A5 - 0x1548).
func initialize_application() -> void:
	assert(a7 != 0, "initialize_application needs a7 (SysEnvirons' record is in its frame)")
	var entry: int = a7
	var a6: int = entry - 4
	_jt(LEYTE_JT_MORE_MASTERS, [])
	for i in 2:
		memory.write_u16(a6 - 2, i)
		_jt(LEYTE_JT_INIT_STEP, [])
	memory.write_u16(a6 - 2, 2)
	_trap(TRAP_INIT_GRAF, [C68kScript.u32(a5 - 0x168a)])
	_trap(TRAP_INIT_FONTS, [])
	_jt(LEYTE_JT_FLUSH_EVENTS, [0, 0xffff])
	_trap(TRAP_INIT_WINDOWS, [])
	_trap(TRAP_INIT_MENUS, [])
	_trap(TRAP_TE_INIT, [])
	_trap(TRAP_INIT_DIALOGS, [0])
	_trap(TRAP_INIT_CURSOR, [])
	_jt(LEYTE_JT_EVENT_GLUE, [0xffff])
	_jt(LEYTE_JT_INIT_TOOLS, [])
	_trap(TRAP_UNLOAD_SEG, [C68kScript.u32(a5 + 0x5da)])
	if (_jt(LEYTE_JT_CHECK_SYSTEM, []) & 0xffff) == 0:
		_trap(TRAP_EXIT_TO_SHELL, [])
	_local(LEYTE_LOCAL_AFTER_TOOLS, [])
	_jt(LEYTE_JT_SYS_ENVIRONS, [a6 - 0x1e, 1])
	memory.write_u8(C68kScript.u32(a5 - 0x1406), memory.read_u8(a6 - 0x15))
	var cpu: int = memory.read_u16(a6 - 0x18)
	memory.write_u8(C68kScript.u32(a5 - 0x1405), 1 if cpu == 1 or cpu == 2 else 0)
	if memory.read_u8(C68kScript.u32(a5 - 0x1406)) != 0:
		_local(LEYTE_LOCAL_SCAN_DEVICES, [])
		memory.write_u32(C68kScript.u32(a5 - 0x1530), C68kScript.u32(_jt(LEYTE_JT_NEW_PTR_CLEAR, [0x6d8])))
		var seed: int = C68kScript.u32(_trap(TRAP_GET_CTSEED, []))
		memory.write_u32(memory.read_u32(C68kScript.u32(a5 - 0x1530)), seed)
		memory.write_u16(memory.read_u32(C68kScript.u32(a5 - 0x1530)) + 4, 0x8000)
		memory.write_u32(C68kScript.u32(a5 - 0x1394), memory.read_u32(C68kScript.u32(a5 - 0x1530)))
	if C68kScript.u32(_trap(TRAP_GET_RESOURCE, [2, MENU])) == 0:
		_trap(TRAP_SYS_BEEP, [0x14])
		_local(LEYTE_LOCAL_RESOURCE_ERROR, [])
		_local(LEYTE_LOCAL_QUIT, [])
	var settings: int = C68kScript.u32(_trap(TRAP_GET_RESOURCE, [0xe3, HEL1]))
	memory.write_u32(C68kScript.u32(a5 - 0x142a), settings)
	if settings == 0:
		memory.write_u32(C68kScript.u32(a5 - 0x142a), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE, [0x12e])))
		var h: int = memory.read_u32(C68kScript.u32(a5 - 0x142a))
		_trap(TRAP_ADD_RESOURCE, [C68kScript.u32(a5 - 0xc8a), 0xe3, HEL1, h])
		var put: Callable = func(off: int, v: int) -> void:
			memory.write_u16(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a))) + off, v)
		if (_local(LEYTE_LOCAL_SETTINGS_CHECK, []) & 0xffff) == 0:
			put.call(0x10, 2)
			put.call(0xe, 1)
			put.call(0x24, 2)
			put.call(0x2a, 1)
		put.call(0x28, 6)
		put.call(0x26, 0)
		_local(LEYTE_LOCAL_COPY_PSTRING, [C68kScript.u32(a5 - 0xc72), C68kScript.u32(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a))) + 0x2e)])
		_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x142a))])
		_trap(TRAP_WRITE_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x142a))])
	else:
		_trap(TRAP_LOAD_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x142a))])
	var table: int = C68kScript.u32(_trap(TRAP_GET_RESOURCE, [0xe4, HEL1]))
	memory.write_u32(C68kScript.u32(a5 - 0x1426), table)
	if table == 0:
		memory.write_u32(C68kScript.u32(a5 - 0x1426), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE, [0x500])))
		_trap(TRAP_ADD_RESOURCE, [C68kScript.u32(a5 - 0xc66), 0xe4, HEL1, memory.read_u32(C68kScript.u32(a5 - 0x1426))])
		_local(LEYTE_LOCAL_INIT_HEL1_228, [])
		_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x1426))])
		_trap(TRAP_WRITE_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x1426))])
	else:
		_trap(TRAP_LOAD_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x1426))])
	_local(LEYTE_LOCAL_CONFIGURE_DISPLAY, [])
	_trap(TRAP_SET_PORT, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])

const LEYTE_JT_FREE_MEM: int = 0x222         ## FreeMem: Pascal, long result (CODE 9 +0x196, $A01C glue)
## 0x1e58's service table: [A5 global, the jump-table slot (A5 offset) whose address it receives], in
## the code's order (35 entries; 1.0.3's 0x1b42 has 34).
const LEYTE_SERVICE_TABLE: Array = [[-0x1168, 0x3d2], [-0x1160, 0x442], [-0x1164, 0x422], [-0x115c, 0x38a],
	[-0x1158, 0x3c2], [-0x1138, 0x3ba], [-0x1134, 0x3f2], [-0x1130, 0x3e2], [-0x112c, 0x432], [-0x114c, 0x382],
	[-0x1144, 0x3aa], [-0x1148, 0x412], [-0x1140, 0x43a], [-0x113c, 0x37a], [-0x1154, 0x3ea], [-0x1150, 0x392],
	[-0x1120, 0x482], [-0x111c, 0x46a], [-0x1118, 0x492], [-0x1114, 0x47a], [-0x1110, 0x48a], [-0x110c, 0x472],
	[-0x1108, 0x3fa], [-0x138e, 0x1b2], [-0x138a, 0x1ba], [-0x1386, 0x1c2], [-0x1382, 0x1ca], [-0x1104, 0x3da],
	[-0x1100, 0x40a], [-0x10fc, 0x3ca], [-0x10f8, 0x41a], [-0x10f4, 0x352], [-0x10f0, 0x39a], [-0x10ec, 0x362],
	[-0x10e8, 0x35a]]

## 0x1e58 (main's set-up; 1.0.3's 0x1b42, install_overlay_service_table): the host service table the
## overlay calls through (each global gets its jump-table slot's address, A5 + offset), then the block
## A5 - 0x1082 (which reset_overlay_state clears) = NewPtrClear(1000000) when FreeMem is above 0x1c2000,
## else NewPtrClear(600000) (1.0.3: 0x4e390 / 0x35cf0 against 1 MB).
func install_overlay_service_table() -> void:
	for entry in LEYTE_SERVICE_TABLE:
		memory.write_u32(C68kScript.u32(a5 + entry[0]), C68kScript.u32(a5 + entry[1]))
	var free: int = C68kScript.s32(_jt(LEYTE_JT_FREE_MEM, []))
	var size: int = 0xf4240 if free > 0x1c2000 else 0x927c0
	memory.write_u32(C68kScript.u32(a5 - 0x1082), C68kScript.u32(_jt(LEYTE_JT_NEW_PTR_CLEAR, [size])))

const LEYTE_TRAP_OPEN_RES_FILE: int = 0xa997
const LEYTE_TRAP_DETACH_RESOURCE: int = 0xa992
const LEYTE_TRAP_CLOSE_RES_FILE: int = 0xa99a
const LEYTE_TRAP_USE_RES_FILE: int = 0xa998
## The settings words 0x3082 copies from the other file's 'HEL1' 127 into Leyte's 'HEL1' 227, at the
## same offsets; then its +0x26 goes to Leyte's +0x2a.
const LEYTE_IMPORTED_SETTINGS: Array = [2, 4, 6, 8, 0xe, 0x10, 0x12, 0x14, 0x16, 0x18, 0x1a, 0x22]

## 0x3082 (initialize_application's check when it makes 'HEL1' 227; no 1.0.3 counterpart): the resource
## file named at A5 - 0xc94 opened (OpenResFile; -1: returns 0, the caller then sets its defaults). Its
## 'HEL1' 127 (1.0.3's settings record), when present, loaded and the words at LEYTE_IMPORTED_SETTINGS
## copied into the new settings (*(A5 - 0x142a)), its +0x26 into +0x2a; its 'HEL1' 128 detached into
## A5 - 0xd40 (DetachResource); the file closed, A5 - 0x1042 used again; returns 1.
func import_settings_from_resource_file() -> int:
	var ref: int = _trap(LEYTE_TRAP_OPEN_RES_FILE, [C68kScript.u32(a5 - 0xc94)]) & 0xffff
	if ref == 0xffff:
		return 0
	var source: int = C68kScript.u32(_trap(TRAP_GET_RESOURCE, [0x7f, HEL1]))
	if source != 0:
		_trap(TRAP_LOAD_RESOURCE, [source])
		var record: int = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
		for off in LEYTE_IMPORTED_SETTINGS:
			memory.write_u16(C68kScript.u32(record + off), memory.read_u16(C68kScript.u32(memory.read_u32(source) + off)))
		memory.write_u16(C68kScript.u32(record + 0x2a), memory.read_u16(C68kScript.u32(memory.read_u32(source) + 0x26)))
	memory.write_u32(C68kScript.u32(a5 - 0xd40), C68kScript.u32(_trap(TRAP_GET_RESOURCE, [0x80, HEL1])))
	_trap(LEYTE_TRAP_DETACH_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0xd40))])
	_trap(LEYTE_TRAP_CLOSE_RES_FILE, [ref])
	_trap(LEYTE_TRAP_USE_RES_FILE, [memory.read_u16(C68kScript.u32(a5 - 0x1042))])
	return 1

const LEYTE_JT_GET_HANDLE_SIZE: int = 0x272  ## GetHandleSize(h): Pascal, long result ($A025 glue)
const LEYTE_JT_BLOCK_MOVE: int = 0x2b2       ## BlockMove(src, dst, count): Pascal ($A02E glue)
const LEYTE_JT_DISPOSE_HANDLE: int = 0x26a   ## DisposHandle(h): Pascal ($A023 glue)
## 0x31d8's new layout of each record's words from +0x28: [destination offset, source word index (the
## 12 words copied from +0x28), or -1 for zero].
const LEYTE_PILOT_LAYOUT: Array = [[0x28, 0], [0x2a, -1], [0x2c, 1], [0x2e, 2], [0x30, 5], [0x32, 6], [0x34, 7],
	[0x36, -1], [0x38, 3], [0x3a, 9]]

## 0x31d8 (initialize_application's filler of a new 'HEL1' 228; no 1.0.3 counterpart): with a record
## imported from the other file (A5 - 0xd40, 0x3082's 'HEL1' 128), its bytes moved into 'HEL1' 228
## (A5 - 0x1426: BlockMove of 228's GetHandleSize), then each of the 20 records of 64 bytes converted:
## its word +0x26 shifted left 16 (LSL.W: 0), its 12 words from +0x28 rearranged by
## LEYTE_PILOT_LAYOUT; the imported handle disposed. Without one, nothing.
func convert_imported_pilots() -> void:
	if memory.read_u32(C68kScript.u32(a5 - 0xd40)) == 0:
		return
	var size: int = C68kScript.u32(_jt(LEYTE_JT_GET_HANDLE_SIZE, [memory.read_u32(C68kScript.u32(a5 - 0x1426))]))
	_jt(LEYTE_JT_BLOCK_MOVE, [size, memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1426))),
			memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0xd40)))])
	for i in 0x14:
		var rec: int = C68kScript.u32(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1426))) + (i << 6))
		memory.write_u16(rec + 0x26, (memory.read_u16(rec + 0x26) << 16) & 0xffff)
		var words: Array = []
		for k in 0xc:
			words.append(memory.read_u16(rec + 0x28 + 2 * k))
		for slot in LEYTE_PILOT_LAYOUT:
			memory.write_u16(rec + slot[0], 0 if slot[1] < 0 else words[slot[1]])
	_jt(LEYTE_JT_DISPOSE_HANDLE, [memory.read_u32(C68kScript.u32(a5 - 0xd40))])

const LEYTE_JT_GET_VOL: int = 0x2da          ## GetVol(VAR name, VAR vRefNum): Pascal, word result
const LEYTE_JT_GET_WD_INFO: int = 0x302      ## PBGetWDInfo(pb, async): $A260 selector 7, word result
const LEYTE_JT_GET_CAT_INFO: int = 0x30a     ## PBGetCatInfo(pb, async): $A260 selector 9, word result
const LEYTE_JT_OPEN_WD: int = 0x2fa          ## PBOpenWD(pb, async): $A260 selector 1, word result
const LEYTE_JT_SET_VOL: int = 0x2e2          ## SetVol(name, vRefNum): Pascal, word result
const LEYTE_LOCAL_SHOW_ERROR: int = 0x2066   ## the error alert (C: message, number; JT $4da)
const LEYTE_TRAP_CUR_RES_FILE: int = 0xa994
const LEYTE_TRAP_CREATE_RES_FILE: int = 0xa9b1
const LEYTE_TRAP_RES_ERROR: int = 0xa9af

## 0x2f42 (initialize_application, after the Toolbox; no 1.0.3 counterpart): Leyte's preferences file
## opened. CurResFile into A5 - 0x1044; GetVol (name A6 - 0x100, vRefNum A6 - 0x106) and SysEnvirons(1)
## at A6 - 0x116 (sysVRefNum at - 0x108). The working-directory block at A6 - 0x14a (ioNamePtr - 0x138 =
## nil, ioVRefNum - 0x134 = sysVRefNum, ioWDIndex - 0x130 = 0, ioWDProcID - 0x12e = 0, ioWDVRefNum - 0x12a
## = 0): PBGetWDInfo, failing: the alert (A5 - 0xda0) and ExitToShell (falling through). The folder name
## A5 - 0xca0 copied to A6 - 0x2b6 and looked up (PBGetCatInfo, block at A6 - 0x1b6: that name, the
## volume - 0x12a, index 0, the directory - 0x11a): found, a working directory opened on it (procID
## 'HELL', its directory) and A5 - 0x1040 = its reference; else A5 - 0x1040 = sysVRefNum; SetVol there.
## Then the file named A5 - 0xcb6 opened (OpenResFile), created while it does not open (CreateResFile;
## ResError: the alert and ExitToShell); CurResFile into A5 - 0x1042; the first volume set again.
func open_preferences_file() -> void:
	assert(a7 != 0, "open_preferences_file needs a7 (its parameter blocks are in its frame)")
	var entry: int = a7
	var a6: int = entry - 4
	memory.write_u32(a6 - 0x2ba, C68kScript.u32(a5 - 0xcb6))
	memory.write_u16(C68kScript.u32(a5 - 0x1044), _trap(LEYTE_TRAP_CUR_RES_FILE, []) & 0xffff)
	_jt(LEYTE_JT_GET_VOL, [a6 - 0x106, a6 - 0x100])
	_jt(LEYTE_JT_SYS_ENVIRONS, [a6 - 0x116, 1])
	memory.write_u16(a6 - 0x134, memory.read_u16(a6 - 0x108))
	memory.write_u16(a6 - 0x130, 0)
	memory.write_u32(a6 - 0x12e, 0)
	memory.write_u16(a6 - 0x12a, 0)
	memory.write_u32(a6 - 0x138, 0)
	if (_jt(LEYTE_JT_GET_WD_INFO, [0, a6 - 0x14a]) & 0xffff) != 0:
		_local(LEYTE_LOCAL_SHOW_ERROR, [memory.read_u32(C68kScript.u32(a5 - 0xda0)), 0])
		_trap(TRAP_EXIT_TO_SHELL, [])
	_local(LEYTE_LOCAL_COPY_PSTRING, [C68kScript.u32(a5 - 0xca0), a6 - 0x2b6])
	memory.write_u32(a6 - 0x1a4, a6 - 0x2b6)
	memory.write_u16(a6 - 0x1a0, memory.read_u16(a6 - 0x12a))
	memory.write_u16(a6 - 0x19a, 0)
	memory.write_u32(a6 - 0x186, memory.read_u32(a6 - 0x11a))
	if (_jt(LEYTE_JT_GET_CAT_INFO, [0, a6 - 0x1b6]) & 0xffff) == 0:
		memory.write_u32(a6 - 0x138, 0)
		memory.write_u32(a6 - 0x12e, 0x48454c4c)
		memory.write_u32(a6 - 0x11a, memory.read_u32(a6 - 0x186))
		memory.write_u16(a6 - 0x134, memory.read_u16(a6 - 0x12a))
		_jt(LEYTE_JT_OPEN_WD, [0, a6 - 0x14a])
		memory.write_u16(C68kScript.u32(a5 - 0x1040), memory.read_u16(a6 - 0x134))
	else:
		memory.write_u16(C68kScript.u32(a5 - 0x1040), memory.read_u16(a6 - 0x108))
	_jt(LEYTE_JT_SET_VOL, [memory.read_u16(C68kScript.u32(a5 - 0x1040)), 0])
	while (_trap(LEYTE_TRAP_OPEN_RES_FILE, [memory.read_u32(a6 - 0x2ba)]) & 0xffff) == 0xffff:
		_trap(LEYTE_TRAP_CREATE_RES_FILE, [memory.read_u32(a6 - 0x2ba)])
		if (_trap(LEYTE_TRAP_RES_ERROR, []) & 0xffff) != 0:
			_local(LEYTE_LOCAL_SHOW_ERROR, [memory.read_u32(C68kScript.u32(a5 - 0xda0)), 0])
			_trap(TRAP_EXIT_TO_SHELL, [])
	memory.write_u16(C68kScript.u32(a5 - 0x1042), _trap(LEYTE_TRAP_CUR_RES_FILE, []) & 0xffff)
	_jt(LEYTE_JT_SET_VOL, [memory.read_u16(a6 - 0x106), 0])

const LEYTE_JT_GET_MMU_MODE: int = 0x31a     ## the byte at 0xcb2 (MMU32Bit), Pascal byte result (1.0.3: $2da)

## 0x24c8 (scan_display_devices; 1.0.3's 0x1060 differs): the main device (GetMainDevice) into A5 -
## 0x1078, its rectangle kept; the three device records (A5 - 0x13d6 main, - 0x13c0 left, - 0x13aa
## right) lose their device. Each device of GetDeviceList .. GetNextDevice with the main device's top,
## bottom, width and pixel size picks the record for its side (itself, its right edge on the main
## left, its left on the main right) and, unlike 1.0.3 (which fills only the main device's), every one
## picked is filled: a 0x200-byte index map (NewPtrClear) when missing, the device, row bytes & 0x1fff,
## the colour flag (0 when the MMU32Bit byte is set, else the device's flags bit 0), "is not type 0"
## and the base address. A5 - 0x1074 = 1 when the main record and a side record are both filled.
func scan_display_devices() -> void:
	var main: int = C68kScript.u32(_trap(TRAP_GET_MAIN_DEVICE, []))
	memory.write_u32(C68kScript.u32(a5 - 0x1078), main)
	var main_rect: int = memory.read_u32(memory.read_u32(memory.read_u32(main) + 0x16)) + 6
	var mtop: int = memory.read_u16(main_rect)
	var mleft: int = memory.read_u16(main_rect + 2)
	var mbottom: int = memory.read_u16(main_rect + 4)
	var mright: int = memory.read_u16(main_rect + 6)
	for off in [-0x13aa, -0x13c0, -0x13d6]:
		memory.write_u32(C68kScript.u32(a5 + off), 0)
	var dev: int = C68kScript.u32(_trap(TRAP_GET_DEVICE_LIST, []))
	while dev != 0:
		var pm: int = memory.read_u32(memory.read_u32(memory.read_u32(dev) + 0x16))
		var top: int = memory.read_u16(pm + 6)
		var left: int = memory.read_u16(pm + 8)
		var bottom: int = memory.read_u16(pm + 10)
		var right: int = memory.read_u16(pm + 12)
		var main_pm: int = memory.read_u32(memory.read_u32(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1078))) + 0x16))
		if top == mtop and bottom == mbottom and C68kScript.u16(right - left) == C68kScript.u16(mright - mleft) \
				and memory.read_u16(main_pm + 0x20) == memory.read_u16(pm + 0x20):
			var rec: int = 0
			if dev == memory.read_u32(C68kScript.u32(a5 - 0x1078)):
				rec = C68kScript.u32(a5 - 0x13d6)
			elif right == mleft:
				rec = C68kScript.u32(a5 - 0x13c0)
			elif left == mright:
				rec = C68kScript.u32(a5 - 0x13aa)
			if rec != 0:
				if memory.read_u32(rec + 4) == 0:
					memory.write_u32(rec + 4, C68kScript.u32(_jt(LEYTE_JT_NEW_PTR_CLEAR, [0x200])))
				memory.write_u32(rec, dev)
				memory.write_u16(rec + 0xe, memory.read_u16(memory.read_u32(memory.read_u32(memory.read_u32(dev) + 0x16)) + 4) & 0x1fff)
				var flag: int = 0
				if (_jt(LEYTE_JT_GET_MMU_MODE, []) >> 8) & 0xff == 0 and (memory.read_u8(memory.read_u32(dev) + 0x14) & 1) != 0:
					flag = 1
				memory.write_u16(rec + 0x10, flag)
				memory.write_u16(rec + 8, 1 if memory.read_u16(memory.read_u32(dev) + 4) != 0 else 0)
				memory.write_u32(rec + 0xa, memory.read_u32(memory.read_u32(memory.read_u32(memory.read_u32(dev) + 0x16))))
		dev = C68kScript.u32(_trap(TRAP_GET_NEXT_DEVICE, [dev]))
	var both: bool = memory.read_u32(C68kScript.u32(a5 - 0x13d6)) != 0 \
			and (memory.read_u32(C68kScript.u32(a5 - 0x13c0)) != 0 or memory.read_u32(C68kScript.u32(a5 - 0x13aa)) != 0)
	memory.write_u16(C68kScript.u32(a5 - 0x1074), 1 if both else 0)

const LEYTE_LOCAL_LAYOUT_WINDOW: int = 0x2676    ## layout_game_window_and_view (C, the rect's four words)
const LEYTE_MBAR_HEIGHT: int = 0xbaa             ## low memory MBarHeight

func _leyte_main_pixmap() -> int:
	return memory.read_u32(memory.read_u32(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1078))) + 0x16))

## 0x2b48 (configure_display_from_main_device): as 1.0.3's 0x27e4 with Leyte's globals, and one more
## step: an unsupported depth (not 1 / 4 / 8) of 16 or more quits (ExitToShell after the alert). The
## display scanned (scan_display_devices, this file's); an unsupported depth hides a visible game
## window (A5 - 0x1548, A5 - 0xd42 set), shows the alert (message A5 - 0xdb4), quits at 16 bits or
## more, rescans and keeps the old depth in A5 - 0x13f8; still unsupported clears A5 - 0x1070.
## Otherwise a window hidden that way is shown again in a running game (A5 - 0x1376), and A5 - 0x1070
## is set. With it set the screen globals come from the pixmap (A5 - 0x142c = -1, base - 0x1404,
## depth - 0x13f8, height - 0x13fa, width - 0x13fc, row bytes - 0x13f6, the settings' +0x1c into
## - 0x1352). The MMU32Bit byte into A5 - 0x103e and the device's flag bit 0 give A5 - 0x1390, then
## A5 - 0x103e = 1; the three device records' bases (A5 - 0x13d6 / - 0x13c0 / - 0x13aa) go down a menu
## bar (row bytes x MBarHeight, MULU.L), the height loses it, A5 - 0x106a is cleared and the window is
## laid out (0x2676) over (MBarHeight, 0, MBarHeight + height, width).
func configure_display_from_main_device() -> void:
	scan_display_devices()
	var depth: int = memory.read_u16(_leyte_main_pixmap() + 0x20)
	var ok: bool = depth == 1 or depth == 4 or depth == 8
	if not ok:
		var window: int = memory.read_u32(C68kScript.u32(a5 - 0x1548))
		if window != 0 and memory.read_u8(window + 0x6e) != 0:
			memory.write_u16(C68kScript.u32(a5 - 0xd42), 1)
			_trap(TRAP_HIDE_WINDOW, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
		_local(LEYTE_LOCAL_SHOW_ERROR, [memory.read_u32(C68kScript.u32(a5 - 0xdb4)), 0])
		if C68kScript.s16(depth) >= 0x10:
			_trap(TRAP_EXIT_TO_SHELL, [])
		scan_display_devices()
		memory.write_u16(C68kScript.u32(a5 - 0x13f8), depth)
		depth = memory.read_u16(_leyte_main_pixmap() + 0x20)
		ok = depth == 1 or depth == 4 or depth == 8
		if not ok:
			memory.write_u16(C68kScript.u32(a5 - 0x1070), 0)
	if ok:
		if memory.read_u16(C68kScript.u32(a5 - 0xd42)) != 0 and memory.read_u32(C68kScript.u32(a5 - 0x1548)) != 0 \
				and memory.read_u16(C68kScript.u32(a5 - 0x1376)) != 0:
			_trap(TRAP_SHOW_WINDOW, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
		memory.write_u16(C68kScript.u32(a5 - 0xd42), 0)
		memory.write_u16(C68kScript.u32(a5 - 0x1070), 1)
	if memory.read_u16(C68kScript.u32(a5 - 0x1070)) != 0:
		memory.write_u16(C68kScript.u32(a5 - 0x142c), 0xffff)
		var pm: int = _leyte_main_pixmap()
		memory.write_u32(C68kScript.u32(a5 - 0x1404), memory.read_u32(pm))
		memory.write_u16(C68kScript.u32(a5 - 0x13f8), memory.read_u16(pm + 0x20))
		memory.write_u16(C68kScript.u32(a5 - 0x13fa), C68kScript.u16(memory.read_u16(pm + 0xa) - memory.read_u16(pm + 6)))
		memory.write_u16(C68kScript.u32(a5 - 0x13fc), C68kScript.u16(memory.read_u16(pm + 0xc) - memory.read_u16(pm + 8)))
		memory.write_u16(C68kScript.u32(a5 - 0x13f6), memory.read_u16(pm + 4) & 0x1fff)
		memory.write_u16(C68kScript.u32(a5 - 0x1352), memory.read_u16(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a))) + 0x1c))
	var mmu: int = (_jt(LEYTE_JT_GET_MMU_MODE, []) >> 8) & 0xff
	memory.write_u8(C68kScript.u32(a5 - 0x103e), mmu)
	var flag: bool = mmu == 0 and (memory.read_u8(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1078))) + 0x14) & 1) != 0
	memory.write_u16(C68kScript.u32(a5 - 0x1390), 1 if flag else 0)
	memory.write_u8(C68kScript.u32(a5 - 0x103e), 1)
	var mbar: int = memory.read_u16(LEYTE_MBAR_HEIGHT)
	for i in range(1, 4):
		var rec: int = C68kScript.u32(a5 - 0x13ec + 0x16 * i)
		if memory.read_u32(rec) != 0:
			var delta: int = C68kScript.u32(memory.read_s16(rec + 0xe) * C68kScript.s16(mbar))
			memory.write_u32(rec + 0xa, C68kScript.u32(memory.read_u32(rec + 0xa) + delta))
	memory.write_u16(C68kScript.u32(a5 - 0x13fa), C68kScript.u16(memory.read_u16(C68kScript.u32(a5 - 0x13fa)) - mbar))
	memory.write_u16(C68kScript.u32(a5 - 0x106a), 0)
	_local(LEYTE_LOCAL_LAYOUT_WINDOW, [mbar, 0, C68kScript.u16(mbar + memory.read_u16(C68kScript.u32(a5 - 0x13fa))),
			memory.read_u16(C68kScript.u32(a5 - 0x13fc))])

const LEYTE_LOCAL_CONFIGURE_PALETTE: int = 0x4eb6    ## configure_palette_entries (verified at this offset)
const LEYTE_LOCAL_CLOSE_PORT: int = 0xdb2            ## close_and_dispose_port (verified)
const LEYTE_LOCAL_FATAL_MEMORY: int = 0x1cf8         ## fatal_memory_error_exit (verified)
const LEYTE_JT_VIEW_BUFFER_COLOUR: int = 0x1da       ## CODE 6 +0x858 (1.0.3: $1b2)
const LEYTE_JT_VIEW_BUFFER_1BIT: int = 0x1d2         ## CODE 6 +0x7b0
const LEYTE_JT_VIEW_BUFFER_BASE: int = 0x1ea         ## CODE 6 +0xee

## Leyte's device records: i = 1..3 at A5 - 0x13ec + 0x16 i (main, left, right).
func _leyte_shift_device_bases(mbar: int, sign: int) -> void:
	for i in range(1, 4):
		var rec: int = C68kScript.u32(a5 - 0x13ec + 0x16 * i)
		if memory.read_u32(rec) != 0:
			var delta: int = C68kScript.u32(memory.read_s16(rec + 0xe) * C68kScript.s16(mbar))
			memory.write_u32(rec + 0xa, C68kScript.u32(memory.read_u32(rec + 0xa) + sign * delta))

## 0x2676 (layout_game_window_and_view; C, the screen rect by value: top, left, bottom, right): as 1.0.3's
## 0x2334 with Leyte's globals (settings *(A5 - 0x142a), the two-screen flag A5 - 0x1074, screen width
## - 0x13fc, height - 0x13fa, depth - 0x13f8, the menu-bar flag - 0x106a, the device records - 0x13ec +
## 0x16 i, the view size - 0x13ee / - 0x13f0, the window - 0x1548, palettes - 0x152c / - 0x1528, the
## offscreen port - 0x107c, the resize hook - 0x10e4 with - 0x1070, the 1-bit view base - 0x137e), the
## same arithmetic (MULS.L #$1e0 / DIVS.L #$280, MULU.L #$8d4 / DIVS.L #$12c0, MULU.L for the bases),
## and one step 1.0.3 does not make: in a two-screen session the window's rect grows over the side
## screens (left by the screen width with a left device, right with a right one) before the window is
## made or moved. The view buffers: JT $1da, or at depth 1 JT $1d2 then JT $1ea into A5 - 0x137e (the
## out-of-memory exit 0x1cf8 when $1d2 fails).
func layout_game_window_and_view(top: int, left: int, bottom: int, right: int) -> void:
	assert(a7 != 0, "layout_game_window_and_view needs a7 (NewCWindow's bounds are in its frame)")
	var a6: int = a7 - 4
	var settings: int = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
	var two_screens: bool = memory.read_u16(settings + 0x1c) != 0 and memory.read_u16(C68kScript.u32(a5 - 0x1074)) != 0
	var option: int = 1 if two_screens else memory.read_u16(settings + 0xe)
	var screen_w: int = memory.read_s16(C68kScript.u32(a5 - 0x13fc))
	var w: int
	match C68kScript.s16(option):
		1: w = C68kScript.u16(screen_w)
		2: w = C68kScript.u16(screen_w / 2)
		3: w = C68kScript.u16(C68kScript.s16(screen_w * 3) / 4)
		4: w = C68kScript.u16(screen_w / 4)
		_: w = memory.read_u16(a6 - 0xe)
	var mbar: int = memory.read_u16(LEYTE_MBAR_HEIGHT)
	if memory.read_u16(C68kScript.u32(a5 - 0x106a)) != 0:
		_leyte_shift_device_bases(mbar, 1)
		memory.write_u16(C68kScript.u32(a5 - 0x13fa), C68kScript.u16(memory.read_u16(C68kScript.u32(a5 - 0x13fa)) - mbar))
	var h: int = memory.read_u16(C68kScript.u32(a5 - 0x13fa))
	var depth: int = memory.read_s16(C68kScript.u32(a5 - 0x13f8))
	if C68kScript.s16(option) == 1 and w == 0x200 and C68kScript.u16(0x156 - mbar) == h:
		if memory.read_u16(C68kScript.u32(a5 - 0x106a)) == 0:
			_leyte_shift_device_bases(mbar, -1)
			memory.write_u16(C68kScript.u32(a5 - 0x13fa), C68kScript.u16(memory.read_u16(C68kScript.u32(a5 - 0x13fa)) + mbar))
			memory.write_u16(C68kScript.u32(a5 - 0x106a), 1)
			top = C68kScript.u16(top - mbar)
		h = memory.read_u16(C68kScript.u32(a5 - 0x13fa))
	else:
		var step: int = 8 / depth
		w = C68kScript.u16((C68kScript.s16(C68kScript.s16(w) / step) & 0xfffc) * step)
		while true:
			h = C68kScript.u16(C68kScript.s32(C68kScript.u32(C68kScript.s16(w) * 0x1e0)) / 0x280)
			var avail: int = C68kScript.u16(bottom - top)
			if C68kScript.s16(avail) >= C68kScript.s16(h):
				break
			var c: int = C68kScript.u16(C68kScript.s16(C68kScript.u16(C68kScript.s16(w) / 2 + w)) / 2)
			var scaled: int = C68kScript.s32(C68kScript.u32(C68kScript.s16(c) * 0x8d4)) / 0x12c0
			c = C68kScript.u16(C68kScript.s16(avail) / 2 + scaled)
			if C68kScript.s16(c) <= C68kScript.s16(avail):
				h = avail
				break
			w = C68kScript.u16(w - ((8 / depth) << 2))
	if h & 1:
		h = C68kScript.u16(h - 1)
	var vtop: int = C68kScript.u16(C68kScript.s16(top + bottom) / 2 - C68kScript.s16(h) / 2)
	var vbottom: int = C68kScript.u16(vtop + h)
	var vleft: int = C68kScript.u16(C68kScript.s16(left + right) / 2 - C68kScript.s16(w) / 2)
	var step2: int = 8 / depth
	vleft = C68kScript.u16((C68kScript.s16(C68kScript.s16(vleft) / step2) & 0xfffc) * step2)
	var vright: int = C68kScript.u16(vleft + w)
	for i in range(1, 4):
		var rec: int = C68kScript.u32(a5 - 0x13ec + 0x16 * i)
		if memory.read_u32(rec) != 0:
			var rows: int = C68kScript.u32(memory.read_s16(rec + 0xe) * C68kScript.s16(vtop - top))
			var bytes: int = C68kScript.s16(C68kScript.s16(vleft - left) / step2)
			memory.write_u32(rec + 0x12, C68kScript.u32(rows + memory.read_u32(rec + 0xa) + bytes))
	memory.write_u16(C68kScript.u32(a5 - 0x13ee), h)
	memory.write_u16(C68kScript.u32(a5 - 0x13f0), w)
	if two_screens:
		if memory.read_u32(C68kScript.u32(a5 - 0x13c0)) != 0:
			vleft = C68kScript.u16(vleft - memory.read_u16(C68kScript.u32(a5 - 0x13fc)))
		if memory.read_u32(C68kScript.u32(a5 - 0x13aa)) != 0:
			vright = C68kScript.u16(vright + memory.read_u16(C68kScript.u32(a5 - 0x13fc)))
	memory.write_u16(a6 - 8, vtop)
	memory.write_u16(a6 - 6, vleft)
	memory.write_u16(a6 - 4, vbottom)
	memory.write_u16(a6 - 2, vright)
	var window: int = memory.read_u32(C68kScript.u32(a5 - 0x1548))
	if window == 0:
		window = C68kScript.u32(_trap(TRAP_NEW_CWINDOW, [0, 0, 0xffffffff, 2, 0, C68kScript.u32(a5 - 0xcb8),
				[memory.read_u32(a6 - 8), memory.read_u32(a6 - 4)], 0]))
		memory.write_u32(C68kScript.u32(a5 - 0x1548), window)
		memory.write_u32(C68kScript.u32(a5 - 0x152c), C68kScript.u32(_trap(TRAP_NEW_PALETTE, [0, 0, 0, 0xd])))
		_local(LEYTE_LOCAL_CONFIGURE_PALETTE, [4])
		memory.write_u32(C68kScript.u32(a5 - 0x1528), C68kScript.u32(_trap(TRAP_NEW_PALETTE, [0, 0, 0, 0xd9])))
		_local(LEYTE_LOCAL_CONFIGURE_PALETTE, [8])
		_local(LEYTE_LOCAL_SELECT_PALETTE, [])
		_trap(TRAP_SET_PORT, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
	else:
		var visible: int = C68kScript.s8(memory.read_u8(window + 0x6e))
		if visible != 0:
			_trap(TRAP_HIDE_WINDOW, [window])
		_trap(TRAP_MOVE_WINDOW, [0, vtop, vleft, memory.read_u32(C68kScript.u32(a5 - 0x1548))])
		_trap(TRAP_SIZE_WINDOW, [1, h, C68kScript.u16(vright - vleft), memory.read_u32(C68kScript.u32(a5 - 0x1548))])
		_local(LEYTE_LOCAL_SELECT_PALETTE, [])
		if visible != 0:
			_trap(TRAP_SHOW_WINDOW, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
	if two_screens and memory.read_u32(C68kScript.u32(a5 - 0x13c0)) != 0:
		_trap(TRAP_SET_ORIGIN, [0, C68kScript.u16(-memory.read_u16(C68kScript.u32(a5 - 0x13fc)))])
	else:
		_trap(TRAP_SET_ORIGIN, [0, 0])
	var offscreen: int = memory.read_u32(C68kScript.u32(a5 - 0x107c))
	if offscreen != 0:
		_local(LEYTE_LOCAL_CLOSE_PORT, [offscreen])
		memory.write_u32(C68kScript.u32(a5 - 0x107c), 0)
	if memory.read_u16(C68kScript.u32(a5 - 0x13f8)) != 1:
		_jt(LEYTE_JT_VIEW_BUFFER_COLOUR, [])
	if memory.read_u32(C68kScript.u32(a5 - 0x10e4)) != 0 and memory.read_u16(C68kScript.u32(a5 - 0x1070)) != 0:
		_call_address(memory.read_u32(C68kScript.u32(a5 - 0x10e4)), [])
	if memory.read_u16(C68kScript.u32(a5 - 0x13f8)) == 1:
		var ok: int = _jt(LEYTE_JT_VIEW_BUFFER_1BIT, [memory.read_u32(C68kScript.u32(a5 - 0x13c4)), memory.read_u16(C68kScript.u32(a5 - 0x13c8)),
				0x7f, w, h])
		if (ok & 0xffff) == 0:
			_local(LEYTE_LOCAL_FATAL_MEMORY, [])
		memory.write_u32(C68kScript.u32(a5 - 0x137e), C68kScript.u32(_jt(LEYTE_JT_VIEW_BUFFER_BASE, [])))
	else:
		_jt(LEYTE_JT_VIEW_BUFFER_COLOUR, [])

const LEYTE_JT_IDLE: int = 0xaa                      ## CODE 4 +0x482 (1.0.3's $172, report_panel_store_failure)
const LEYTE_LOCAL_UPDATE_MENUS: int = 0x38b2         ## update_menu_items (1.0.3 0x3174)
const LEYTE_LOCAL_REFRESH_DISPLAY: int = 0x2d60      ## refresh_display_if_changed (1.0.3 0x29f2)
const LEYTE_LOCAL_MOUSE_DOWN: int = 0x3656           ## handle_mouse_down (1.0.3 0x2f1e)
const LEYTE_LOCAL_MENU_COMMAND: int = 0x3dfc         ## do_menu_command (1.0.3 0x36a2)
const LEYTE_LOCAL_ACTIVATE: int = 0x36d8             ## handle_activate_event (1.0.3 0x2fa0)

## 0x370c (poll_and_dispatch_event): 1.0.3's 0x2fd4 instruction for instruction, with Leyte's local
## calls (above, and redraw_main_window 0x5544 for 1.0.3's 0x4db0), its idle slot $aa (1.0.3 $172) and
## its globals (the window up A5 - 0x1544, the window A5 - 0x1548). HiliteMenu(0), SystemTask, the menus
## updated, the idle routine, GetNextEvent(every event) into A6 - 0x12 (its byte result sign-extended),
## the display refreshed; then by kind (ID01's dense switch on 1..8): mouse-down to 0x3656 with the
## event, a key (3) or auto-key (5) with the command bit (modifiers 0x100) through MenuKey(the
## character, EXTB) to 0x3dfc, an update of the game window (after another refresh, while it is up)
## redrawn between BeginUpdate and EndUpdate, an activate to 0x36d8 with the 16-byte event by value.
func poll_and_dispatch_event() -> void:
	assert(a7 != 0, "poll_and_dispatch_event needs a7 (the event record is in its frame)")
	var a6: int = a7 - 4
	_trap(TRAP_HILITE_MENU, [0])
	_trap(TRAP_SYSTEM_TASK, [])
	_local(LEYTE_LOCAL_UPDATE_MENUS, [])
	_jt(LEYTE_JT_IDLE, [])
	var got: int = C68kScript.s8((_trap(TRAP_GET_NEXT_EVENT, [a6 - 0x12, 0xffff]) >> 8) & 0xff)
	memory.write_u16(a6 - 2, C68kScript.u16(got))
	_local(LEYTE_LOCAL_REFRESH_DISPLAY, [])
	if got == 0:
		return
	match C68kScript.s16(memory.read_u16(a6 - 0x12)):
		1:
			_local(LEYTE_LOCAL_MOUSE_DOWN, [a6 - 0x12])
		3, 5:
			if (memory.read_u16(a6 - 4) & 0x100) != 0:
				var ch: int = C68kScript.u16(C68kScript.s8(memory.read_u32(a6 - 0x10) & 0xff))
				var choice: int = C68kScript.u32(_trap(TRAP_MENU_KEY, [ch]))
				_local(LEYTE_LOCAL_MENU_COMMAND, [choice])
		6:
			_local(LEYTE_LOCAL_REFRESH_DISPLAY, [])
			if memory.read_u16(C68kScript.u32(a5 - 0x1544)) != 0 \
					and memory.read_u32(a6 - 0x10) == memory.read_u32(C68kScript.u32(a5 - 0x1548)):
				_trap(TRAP_BEGIN_UPDATE, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
				_local(LEYTE_LOCAL_REDRAW, [])
				_trap(TRAP_END_UPDATE, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
		8:
			_local(LEYTE_LOCAL_ACTIVATE, [memory.read_u32(a6 - 0x12), memory.read_u32(a6 - 0xe),
					memory.read_u32(a6 - 0xa), memory.read_u32(a6 - 6)])

const LEYTE_LOCAL_DEVICE_COLOURS: int = 0x53ac   ## check_device_color_state_changed (1.0.3's, refused for Leyte)
const LEYTE_LOCAL_SESSION_HOOKS: int = 0x9be     ## call_overlay_session_hooks (verified at this offset)
const LEYTE_LOCAL_SYNC_SCREEN: int = 0x552a      ## the screen globals synced (1.0.3's 0x4d96, unexported)
const LEYTE_LOCAL_AFTER_REDRAW: int = 0xab4      ## 1.0.3's 0xa26 (redraw_panel_readouts_locked, unexported)

## 0x5544 (redraw_main_window): 1.0.3's 0x4db0 drawn on every screen of a two-screen session. A5 - 0x1544
## (the window up), an overlay image (A5 - 0x1048) and a running game (A5 - 0x1376) needed. The drawn
## flag A5 - 0x1368 set once (its old value to A5 - 0x107e); HideCursor, the device colours checked
## (0x53ac), UnloadSeg A5 + 0x19a, the overlay's redraw hook A5 - 0x10e0. Two screens (the settings'
## A5 - 0x1352 with A5 - 0x1074): for each device record i = 1..3 (A5 - 0x13ec + 0x16 i) with a device,
## SetGDevice to it, the offscreen port A5 - 0x107c closed, the record's view base / row bytes / colour
## flag (+0x12 / +0xe / +0x10) made the screen's (A5 - 0x1400 / - 0x13f6 / - 0x1390), the overlay's
## session hooks (0x9be) for screen i, and on the first screen 0xab4; then the screen globals (0x552a).
## One screen: 0x552a, the session hooks for screen 1, 0xab4. ShowCursor.
func redraw_main_window() -> void:
	var a6: int = a7 - 4 if a7 != 0 else 0
	var two: bool = memory.read_u16(C68kScript.u32(a5 - 0x1352)) != 0 and memory.read_u16(C68kScript.u32(a5 - 0x1074)) != 0
	if a6 != 0:
		memory.write_u16(a6 - 4, 1 if two else 0)
	if memory.read_u16(C68kScript.u32(a5 - 0x1544)) == 0 or memory.read_u32(C68kScript.u32(a5 - 0x1048)) == 0 \
			or memory.read_u16(C68kScript.u32(a5 - 0x1376)) == 0:
		return
	if memory.read_u16(C68kScript.u32(a5 - 0x1368)) == 0:
		memory.write_u16(C68kScript.u32(a5 - 0x107e), memory.read_u16(C68kScript.u32(a5 - 0x1368)))
		memory.write_u16(C68kScript.u32(a5 - 0x1368), 1)
	_trap(TRAP_HIDE_CURSOR, [])
	_local(LEYTE_LOCAL_DEVICE_COLOURS, [])
	_trap(TRAP_UNLOAD_SEG, [C68kScript.u32(a5 + 0x19a)])
	_call_address(memory.read_u32(C68kScript.u32(a5 - 0x10e0)), [])
	if two:
		for i in range(1, 4):
			var rec: int = C68kScript.u32(a5 - 0x13ec + 0x16 * i)
			if a6 != 0:
				memory.write_u16(a6 - 2, i)
			if memory.read_u32(rec) == 0:
				continue
			_trap(TRAP_SET_GDEVICE, [memory.read_u32(rec)])
			var offscreen: int = memory.read_u32(C68kScript.u32(a5 - 0x107c))
			if offscreen != 0:
				_local(LEYTE_LOCAL_CLOSE_PORT, [offscreen])
				memory.write_u32(C68kScript.u32(a5 - 0x107c), 0)
			memory.write_u32(C68kScript.u32(a5 - 0x1400), memory.read_u32(rec + 0x12))
			memory.write_u16(C68kScript.u32(a5 - 0x13f6), memory.read_u16(rec + 0xe))
			memory.write_u16(C68kScript.u32(a5 - 0x1390), memory.read_u16(rec + 0x10))
			_local(LEYTE_LOCAL_SESSION_HOOKS, [0, i])
			if i == 1:
				_local(LEYTE_LOCAL_AFTER_REDRAW, [])
		if a6 != 0:
			memory.write_u16(a6 - 2, 4)
		_local(LEYTE_LOCAL_SYNC_SCREEN, [])
	else:
		_local(LEYTE_LOCAL_SYNC_SCREEN, [])
		_local(LEYTE_LOCAL_SESSION_HOOKS, [0, 1])
		_local(LEYTE_LOCAL_AFTER_REDRAW, [])
	_trap(TRAP_SHOW_CURSOR, [])

const LEYTE_DEVICE_RECORDS: int = -0x13ec           ## the device records of 0x16 (1.0.3: A5 - 0x136e)
const LEYTE_LOCAL_STANDARD_COLOURS: int = 0x52f6     ## init_standard_colour_indices (verified)

## 0x4e32 (1.0.3 0x46b4, the same instructions with Leyte's A5): the duplicate flag A5 - 0x106c cleared;
## at depth 1 (A5 - 0x13f8) the standard colour indices only; with a palette depth (A5 - 0x2cc) the
## offscreen port A5 - 0x107c closed and cleared, then build_palette_index_map for each of the three
## device records (1..3) with a device: its count word and 256-word reverse map in this frame (link
## 0x204: the index at -2, the count at -4, the reverse map at -0x204), its index map at record +4.
func rebuild_all_palette_index_maps() -> void:
	memory.write_u16(C68kScript.u32(a5 - 0x106c), 0)
	if memory.read_s16(C68kScript.u32(a5 - 0x13f8)) == 1:
		_local(LEYTE_LOCAL_STANDARD_COLOURS, [])
		return
	if memory.read_u16(C68kScript.u32(a5 - 0x2cc)) == 0:
		return
	var port: int = memory.read_u32(C68kScript.u32(a5 - 0x107c))
	if port != 0:
		_local(LEYTE_LOCAL_CLOSE_PORT, [port])
		memory.write_u32(C68kScript.u32(a5 - 0x107c), 0)
	assert(a7 != 0, "rebuild_all_palette_index_maps needs a7 (its frame holds the reverse map)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var i: int = 1
	memory.write_u16(a6 - 2, i)
	while memory.read_s16(a6 - 2) < 4:
		i = memory.read_s16(a6 - 2)
		var rec: int = C68kScript.u32(a5 + LEYTE_DEVICE_RECORDS + C68kScript.s32(i * 0x16))
		if memory.read_u32(rec) != 0:
			a7 = entry_a7 - 0x21a   # link 0x204, 0xe bytes of arguments, the return address
			build_palette_index_map(i, a6 - 4, memory.read_u32(rec + 4), a6 - 0x204)
			a7 = entry_a7
		memory.write_u16(a6 - 2, i + 1)

## 0x502c (1.0.3's build_palette_index_map 0x48ae, "not found" for Leyte: one condition differs).
## With the device of record `device` (A5 - 0x13ec + 0x16 * device) current (GetGDevice saved,
## SetGDevice), every index i below the count (0xd at palette depth 4, 0xd9 at 8) is made the
## foreground colour (PMForeColor) and the port's colour index (A5 - 0x168a +0x52) recorded both ways:
## map[i] = colour, reverse[colour] = i (the 256 words at `reverse_ptr` cleared first, with the hit
## counts in this frame). A colour hit twice at depth 8 below 0xc8 sets A5 - 0x106c only when
## A5 - 0x1352 and A5 - 0x1074 are both set, or for device 1 (1.0.3 sets it for every device). The
## frame is the original's (link 0x20c: colour -2, index -4, count -6, the FrontWindow test -8, hits
## from -0x208, the saved device -0x20c), so at another palette depth the index is the 0x100 the
## clearing loop leaves and the count the frame word as found, as in the original. *count_ptr = the
## start index; then A5 - 0x1168 = A5 + 0x4ca with the flag, else A5 + 0x3d2. Returns the count.
func build_palette_index_map(device: int, count_ptr: int, map_ptr: int, reverse_ptr: int) -> int:
	assert(a7 != 0, "build_palette_index_map needs a7 (its frame holds the hit counts)")
	var a6: int = a7 - 4
	var rec: int = C68kScript.u32(a5 + LEYTE_DEVICE_RECORDS + C68kScript.s32(C68kScript.s16(device) * 0x16))
	if memory.read_u32(rec) == 0:
		return 0
	var front: int = C68kScript.u32(_trap(TRAP_FRONT_WINDOW, []))
	memory.write_u16(a6 - 8, 1 if front == memory.read_u32(C68kScript.u32(a5 - 0x1548)) else 0)
	memory.write_u32(a6 - 0x20c, C68kScript.u32(_trap(TRAP_GET_GDEVICE, [])))
	_trap(TRAP_SET_GDEVICE, [memory.read_u32(rec)])
	var k: int = 0
	while k < 0x100:
		memory.write_u16(C68kScript.u32(a6 + 2 * k - 0x208), 0)
		memory.write_u16(C68kScript.u32(reverse_ptr + 2 * k), 0)
		k += 1
	memory.write_u16(a6 - 4, k)
	var depth: int = memory.read_s16(C68kScript.u32(a5 - 0x2cc))
	if depth == 4:
		memory.write_u16(a6 - 6, 0xd)
		memory.write_u16(a6 - 4, 0)
	elif depth == 8:
		memory.write_u16(a6 - 6, 0xd9)
		memory.write_u16(a6 - 4, 0)
	memory.write_u16(count_ptr, memory.read_u16(a6 - 4))
	while memory.read_s16(a6 - 4) < memory.read_s16(a6 - 6):
		var i: int = memory.read_s16(a6 - 4)
		_trap(TRAP_PM_FORE_COLOR, [i])
		var colour: int = memory.read_s16(memory.read_u32(C68kScript.u32(a5 - 0x168a)) + 0x52)
		memory.write_u16(a6 - 2, colour & 0xffff)
		memory.write_u16(C68kScript.u32(map_ptr + 2 * i), colour & 0xffff)
		memory.write_u16(C68kScript.u32(reverse_ptr + 2 * colour), i & 0xffff)
		var hit: int = C68kScript.u32(a6 + 2 * colour - 0x208)
		if memory.read_u16(hit) != 0 and memory.read_s16(C68kScript.u32(a5 - 0x2cc)) == 8 and i < 0xc8:
			var both: bool = memory.read_u16(C68kScript.u32(a5 - 0x1352)) != 0 and memory.read_u16(C68kScript.u32(a5 - 0x1074)) != 0
			if both or C68kScript.s16(device) == 1:
				memory.write_u16(C68kScript.u32(a5 - 0x106c), 1)
		memory.write_u16(hit, (memory.read_u16(hit) + 1) & 0xffff)
		memory.write_u16(a6 - 4, (i + 1) & 0xffff)
	_trap(TRAP_SET_GDEVICE, [memory.read_u32(a6 - 0x20c)])
	var service: int = 0x4ca if memory.read_u16(C68kScript.u32(a5 - 0x106c)) != 0 else 0x3d2
	memory.write_u32(C68kScript.u32(a5 - 0x1168), C68kScript.u32(a5 + service))
	return memory.read_s16(a6 - 6)

const LEYTE_JT_PALETTE_CHANGED: int = 0x1a2      ## C routine (device handle, new index map) (1.0.3: $182)

## 0x53ac (1.0.3's check_device_color_state_changed 0x4bd0 with Leyte's A5, and its "any changed"
## kept in the frame): the duplicate flag A5 - 0x106c cleared; at depth 1 (A5 - 0x13f8) the standard
## colour indices only. Otherwise, for each device record 1..3 with a device, a fresh map is built
## into this frame (build_palette_index_map: count word -4, map -0x20a, reverse -0x40a; link 0x40a) and
## compared with the record's map (+4) from the start index to the count; on the first difference the
## frame's "changed" (-8) and "any" (-0xa) words are set. A changed device 1 reports the fresh map
## (JT $1a2 with A5 - 0x13d2); a changed device's own map is then rebuilt. Any change with an overlay
## image (A5 - 0x1048) calls the overlay hook A5 - 0x10d8.
func check_device_color_state_changed() -> void:
	assert(a7 != 0, "check_device_color_state_changed needs a7 (its frame holds the new maps)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	memory.write_u16(a6 - 0xa, 0)
	memory.write_u16(C68kScript.u32(a5 - 0x106c), 0)
	if memory.read_s16(C68kScript.u32(a5 - 0x13f8)) == 1:
		_local(LEYTE_LOCAL_STANDARD_COLOURS, [])
		return
	memory.write_u16(a6 - 2, 1)
	while memory.read_s16(a6 - 2) < 4:
		var d: int = memory.read_s16(a6 - 2)
		var rec: int = C68kScript.u32(a5 + LEYTE_DEVICE_RECORDS + C68kScript.s32(d * 0x16))
		if memory.read_u32(rec) != 0:
			a7 = entry_a7 - 0x420   # link 0x40a, 0xe bytes of arguments, the return address
			var count: int = build_palette_index_map(d, a6 - 4, a6 - 0x20a, a6 - 0x40a)
			a7 = entry_a7
			memory.write_u16(a6 - 6, count & 0xffff)
			memory.write_u16(a6 - 8, 0)
			while memory.read_s16(a6 - 4) < memory.read_s16(a6 - 6):
				var i: int = memory.read_s16(a6 - 4)
				if memory.read_u16(C68kScript.u32(a6 + 2 * i - 0x20a)) != memory.read_u16(C68kScript.u32(memory.read_u32(rec + 4) + 2 * i)):
					memory.write_u16(a6 - 0xa, 1)
					memory.write_u16(a6 - 8, 1)
					break
				memory.write_u16(a6 - 4, (i + 1) & 0xffff)
			if memory.read_u16(a6 - 8) != 0:
				if d == 1:
					_jt(LEYTE_JT_PALETTE_CHANGED, [memory.read_u32(C68kScript.u32(a5 - 0x13d2)), a6 - 0x20a])
				a7 = entry_a7 - 0x420
				build_palette_index_map(d, a6 - 4, memory.read_u32(rec + 4), a6 - 0x40a)
				a7 = entry_a7
		memory.write_u16(a6 - 2, d + 1)
	if memory.read_u16(a6 - 0xa) != 0 and memory.read_u32(C68kScript.u32(a5 - 0x1048)) != 0:
		_hook(-0x10d8)

## 0x2d60 (1.0.3's refresh_display_if_changed 0x2614; Leyte's runs apply_display_palettes where
## 1.0.3 synced the overlay globals): GetMainDevice other than A5 - 0x1078 rescans the devices; a pixel
## size of the main device's pixmap (**(**(A5 - 0x1078) +0x16) +0x20) other than A5 - 0x13f8
## configures the display, applies the settings and the palettes, then calls the overlay hook
## A5 - 0x10d8 when A5 - 0x1070, the overlay image A5 - 0x1048 and A5 - 0x1544 are all set.
func refresh_display_if_changed() -> void:
	if C68kScript.u32(_trap(TRAP_GET_MAIN_DEVICE, [])) != memory.read_u32(C68kScript.u32(a5 - 0x1078)):
		_local(LEYTE_LOCAL_SCAN_DEVICES, [])
	var pm: int = memory.read_u32(memory.read_u32(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1078))) + 0x16))
	if memory.read_u16(C68kScript.u32(a5 - 0x13f8)) == memory.read_u16(pm + 0x20):
		return
	_local(LEYTE_LOCAL_CONFIGURE_DISPLAY, [])
	_local(LEYTE_LOCAL_FINISH, [])
	_local(LEYTE_LOCAL_AFTER_ENTRY, [])
	if memory.read_u16(C68kScript.u32(a5 - 0x1070)) != 0 and memory.read_u32(C68kScript.u32(a5 - 0x1048)) != 0 \
			and memory.read_u16(C68kScript.u32(a5 - 0x1544)) != 0:
		_hook(-0x10d8)

## 0x38b2 (1.0.3's update_menu_items 0x3174, "not found": Leyte's menus have other items and
## conditions). The menus' enabled / checked state from the game state, through set_menu_item_enabled
## (0x3ce4, identical) and CheckItem. The state words: playing A5 - 0x1070, the game state A5 - 0x1376,
## the overlay image A5 - 0x1048, A5 - 0x136e, - 0x1542, - 0x132e; the busy pair A5 - 0x1074 and
## - 0x1352; the settings record **(A5 - 0x142a); the sound flag the byte A5 - 0x1071.
## - File (A5 - 0xc4a): 6 when playing in a state, with A5 - 0x136e set or A5 - 0x1542 not 1; 3 with
##   an image while playing, in state 0 or with A5 - 0x132e clear; 7 when playing in a state with
##   A5 - 0x136e clear; 8 in a state with A5 - 0x1542 not 1; 4 and 5 when playing from state 2.
## - Edit (A5 - 0xc46): enabled for a desk accessory in front (window kind < 0), the whole menu (item 0)
##   only when that changes (A5 - 0xc3a); items 1, 3, 4, 5, 6.
## - Options (A5 - 0xc42): checks 1, 2, 5, 6 from settings +3, +5, +7, +9 and 4 from the sound flag;
##   8 enabled by A5 - 0x1074 and checked by +0x1d; 10, 12, 11 enabled unless busy and checked for
##   settings +0xe = 1, 2, 3.
## - View (A5 - 0xc3e): 7 enabled at depth 8 (A5 - 0x13f8), 11 unless at depth 4; 1..5 checked for
##   settings +0x10; 7, 9, 8, 11, 10 checked from +0x13, +0x15, +0x17, +0x1b, +0x19.
func update_menu_items() -> void:
	var front: int = C68kScript.u32(_trap(TRAP_FRONT_WINDOW, []))
	var kind: int = memory.read_s16(front + 0x6c) if front != 0 else 0
	var da: int = 1 if kind < 0 else 0
	var busy: bool = memory.read_u16(C68kScript.u32(a5 - 0x1074)) != 0 and memory.read_u16(C68kScript.u32(a5 - 0x1352)) != 0
	var playing: bool = memory.read_u16(C68kScript.u32(a5 - 0x1070)) != 0
	var state: int = memory.read_s16(C68kScript.u32(a5 - 0x1376))
	var w136e: bool = memory.read_u16(C68kScript.u32(a5 - 0x136e)) != 0
	var mode1: bool = memory.read_s16(C68kScript.u32(a5 - 0x1542)) == 1
	var file_menu: int = memory.read_u32(C68kScript.u32(a5 - 0xc4a))
	set_menu_item_enabled(file_menu, 6, 1 if playing and state != 0 and (w136e or not mode1) else 0)
	var item3: int = 0
	if memory.read_u32(C68kScript.u32(a5 - 0x1048)) != 0 and playing:
		item3 = 1 if state == 0 or memory.read_u16(C68kScript.u32(a5 - 0x132e)) == 0 else 0
	set_menu_item_enabled(file_menu, 3, item3)
	set_menu_item_enabled(file_menu, 7, 1 if playing and state != 0 and not w136e else 0)
	set_menu_item_enabled(file_menu, 8, 1 if state != 0 and not mode1 else 0)
	set_menu_item_enabled(file_menu, 4, 1 if playing and state >= 2 else 0)
	set_menu_item_enabled(file_menu, 5, 1 if playing and state >= 2 else 0)
	var edit_menu: int = memory.read_u32(C68kScript.u32(a5 - 0xc46))
	if da != memory.read_s16(C68kScript.u32(a5 - 0xc3a)):
		set_menu_item_enabled(edit_menu, 0, da)
		memory.write_u16(C68kScript.u32(a5 - 0xc3a), da)
	for item in [1, 3, 4, 5, 6]:
		set_menu_item_enabled(edit_menu, item, da)
	var options: int = memory.read_u32(C68kScript.u32(a5 - 0xc42))
	var settings: int = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
	_trap(TRAP_CHECK_ITEM, [memory.read_u8(settings + 3), 1, options])
	settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
	_trap(TRAP_CHECK_ITEM, [memory.read_u8(settings + 5), 2, options])
	_trap(TRAP_CHECK_ITEM, [memory.read_u8(C68kScript.u32(a5 - 0x1071)), 4, options])
	settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
	_trap(TRAP_CHECK_ITEM, [memory.read_u8(settings + 7), 5, options])
	settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
	_trap(TRAP_CHECK_ITEM, [memory.read_u8(settings + 9), 6, options])
	set_menu_item_enabled(options, 8, memory.read_u16(C68kScript.u32(a5 - 0x1074)))
	settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
	_trap(TRAP_CHECK_ITEM, [memory.read_u8(settings + 0x1d), 8, options])
	for item in [0xa, 0xc, 0xb]:
		set_menu_item_enabled(options, item, 0 if busy else 1)
	for pair in [[0xa, 1], [0xc, 2], [0xb, 3]]:
		settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
		_trap(TRAP_CHECK_ITEM, [1 if memory.read_s16(settings + 0xe) == pair[1] else 0, pair[0], options])
	var view_menu: int = memory.read_u32(C68kScript.u32(a5 - 0xc3e))
	set_menu_item_enabled(view_menu, 7, 1 if memory.read_s16(C68kScript.u32(a5 - 0x13f8)) == 8 else 0)
	set_menu_item_enabled(view_menu, 0xb, 1 if memory.read_s16(C68kScript.u32(a5 - 0x13f8)) != 4 else 0)
	for k in range(1, 6):
		settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
		_trap(TRAP_CHECK_ITEM, [1 if memory.read_s16(settings + 0x10) == k else 0, k, view_menu])
	for pair in [[7, 0x13], [9, 0x15], [8, 0x17], [0xb, 0x1b], [0xa, 0x19]]:
		settings = memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a)))
		_trap(TRAP_CHECK_ITEM, [memory.read_u8(settings + pair[1]), pair[0], view_menu])
