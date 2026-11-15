class_name LeyteStrictID08
extends RefCounted
## Strict translations owned by Leyte Gulf's CODE segment 08 (sources/leyte-gulf-1-0/CODE/ID08.bin;
## address = file offset): the overlay loader, which reads the overlay from the application's own
## data fork. Written from Leyte's bytes (sources/leyte-gulf-1-0/CODE/ID08.c and its disassembly):
## 1.0.3's load_overlay_from_resource has no counterpart (correspondence.json: not_found), the
## payload read (0x350) is 1.0.3's alloc_and_read_resource_payload instruction for instruction with
## Leyte's offsets, 0x340 1.0.3's sysbeep_and_exit_to_shell.
##
## MPW C passes int as a 16-bit word; pointers and longs are longs. Globals live below A5 (`a5`).
## Calls leaving the segment go through `_jt` / `_trap`, their arguments listed from the top of the
## stack (the last pushed first), as in original/port/ID08.gd. Verified against Leyte's own loader on
## the 68000 emulator up to JT $51a (tests/leyte/port/test_leyte_loader.gd); the rest is unverified.

const C68kScript := preload("res://original/compat/c68k.gd")

const CUR_AP_NAME: int = 0x910          ## low memory: the application's name (a Pascal string)
const A5_IMAGE: int = -0x1048           ## long: the overlay image
const A5_HEADER: int = -0x1068          ## 32 bytes (a.out): +4 text, +8 data, +0xc bss, +0x18 / +0x1c relocation bytes
const A5_TEXT: int = -0x1064
const A5_DATA: int = -0x1060
const A5_BSS: int = -0x105c
const A5_TEXT_RELOC: int = -0x1050
const A5_DATA_RELOC: int = -0x104c
const A5_PAYLOAD: int = -0x137a         ## long: the rest of the fork (0x350)
const JT_FS_OPEN: int = 0x2c2           ## FSOpen(name, vRefNum, VAR refNum): OSErr
const JT_FS_READ: int = 0x2d2           ## FSRead(refNum, VAR count, buffer): OSErr
const JT_FS_CLOSE: int = 0x2ca          ## FSClose(refNum): OSErr
const JT_GET_FPOS: int = 0x2f2          ## GetFPos(refNum, VAR pos): OSErr
const JT_GET_EOF: int = 0x2ea           ## GetEOF(refNum, VAR eof): OSErr
const JT_NEW_PTR: int = 0x23a           ## NewPtr(size): Ptr
const JT_NEW_PTR_CLEAR: int = 0x242     ## NewPtrClear(size): Ptr (the image)
const JT_DISPOSE_PTR: int = 0x24a
const JT_GET_VOL: int = 0x2da           ## GetVol(VAR name, VAR vRefNum): OSErr
const JT_P2C: int = 0x33a
const JT_MAX_MEM: int = 0x22a           ## MaxMem(VAR grow): Size
const JT_COPY_PSTRING: int = 0x4e2      ## CODE 2 +0x361c (C: source, destination)
const JT_REPORT_OPEN_FAILURE: int = 0x17a  ## CODE 4 +0x49e (C: the name)
const JT_SHOW_ERROR: int = 0x4da        ## CODE 2 +0x2066 (C: message, number)
const JT_AFTER_LOAD: int = 0x51a        ## CODE 2 +0x245a
const JT_AFTER_CLOSE: int = 0x5ba       ## CODE 5 +0x4
const JT_STRCMP: int = 0x562
const JT_POSITION_ALERT: int = 0x4c2
const TRAP_SYS_BEEP: int = 0xa9c8
const TRAP_EXIT_TO_SHELL: int = 0xa9f4
const TRAP_OPEN_RES_FILE: int = 0xa997
const TRAP_RES_ERROR: int = 0xa9af
const TRAP_CLOSE_RES_FILE: int = 0xa99a
const TRAP_USE_RES_FILE: int = 0xa998
const TRAP_TICK_COUNT: int = 0xa975
const TRAP_STOP_ALERT: int = 0xa986

var memory
var a5: int = 0
var a7: int = 0
var jt_routines: Dictionary = {}
var traps: Dictionary = {}
var code_routines: Dictionary = {}     ## the segment loader's table (this port calls no routine by offset)

func _init(memory_68k = null, a5_value: int = 0) -> void:
	memory = memory_68k
	a5 = a5_value

func _jt(offset: int, args: Array) -> int:
	assert(jt_routines.has(offset), "jump-table slot $%x has no routine" % offset)
	return jt_routines[offset].call(args)

func _trap(word: int, args: Array) -> int:
	assert(traps.has(word), "A-line trap %04x has no routine" % word)
	return traps[word].call(args)

func _g(offset: int) -> int:
	return C68kScript.u32(a5 + offset)

## 0x340: SysBeep(100), then ExitToShell.
func sysbeep_and_exit_to_shell() -> void:
	_trap(TRAP_SYS_BEEP, [0x64])
	_trap(TRAP_EXIT_TO_SHELL, [])

## 0x350: the rest of the open file `ref_num` read into a new block (A5 - 0x137a): GetFPos and GetEOF
## into this routine's frame, size = eof - position, NewPtr, FSRead; returns FSRead's result word.
func alloc_and_read_resource_payload(ref_num: int) -> int:
	assert(a7 != 0, "alloc_and_read_resource_payload needs a7 (its frame holds the position and size)")
	var a6: int = a7 - 4
	_jt(JT_GET_FPOS, [a6 - 8, ref_num & 0xffff])
	_jt(JT_GET_EOF, [a6 - 4, ref_num & 0xffff])
	memory.write_u32(a6 - 4, C68kScript.u32(memory.read_u32(a6 - 4) - memory.read_u32(a6 - 8)))
	var block: int = C68kScript.u32(_jt(JT_NEW_PTR, [memory.read_u32(a6 - 4)]))
	memory.write_u32(_g(A5_PAYLOAD), block)
	return _jt(JT_FS_READ, [memory.read_u32(_g(A5_PAYLOAD)), C68kScript.u32(a6 - 4), ref_num & 0xffff]) & 0xffff

## 0x6 (FUN_00000006): the overlay read from the application's own data fork. FSOpen(CurApName, 0);
## failing, the name in *(A5 - 0x142a) +0x2e is reported (JT $17a) and ExitToShell. Any previous
## image disposed; the 32-byte header read into A5 - 0x1068; NewPtrClear(text + data + bss + 4)
## aligned to 4 (A5 - 0x1048); text and data read; the 8-byte relocation records (text then data)
## read one by one: bit 7 skipped, bit 4 or a length (bits 6-5) other than 2 an error, else the
## image base added to the long at their offset (past the text for data records); the rest of the
## file read (0x350) and JT $51a run. On an error (a message from A5 - 0xe34 / -0xe30 / -0xe2c /
## -0xe28, JT $4da) the image is dropped. Then FSClose, JT $5ba, the resource file A5 - 0x1768
## opened (ResError: message A5 - 0xd98 and ExitToShell); with an image, the copy protection (JT
## $592 / $59a seeded with TickCount / $5c2 / $57a / $5a2 / $58a / $5ca / $582 / $5b2); the resource
## file closed, A5 - 0x1042 used; the current volume (A5 - 0x1aaa copied into the frame, GetVol)
## compared with A5 - 0x175e: equal means stop alert 0x105 and ExitToShell. MaxMem; returns 1.
## ExitToShell does not return on the Mac; the bytes after each fall through to what follows.
func load_overlay_from_data_fork() -> int:
	assert(a7 != 0, "load_overlay_from_data_fork needs a7 (its frame holds the reference number and buffers)")
	var entry: int = a7
	var a6: int = entry - 4
	memory.write_u32(a6 - 8, 0)
	if _jt(JT_FS_OPEN, [a6 - 0xa, 0, CUR_AP_NAME]) & 0xffff != 0:
		var rec: int = memory.read_u32(memory.read_u32(_g(-0x142a)))
		_jt(JT_COPY_PSTRING, [C68kScript.u32(rec + 0x2e), a6 - 0x120])
		_jt(JT_REPORT_OPEN_FAILURE, [a6 - 0x120])
		_trap(TRAP_EXIT_TO_SHELL, [])
	if memory.read_u32(_g(A5_IMAGE)) != 0:
		_jt(JT_DISPOSE_PTR, [memory.read_u32(_g(A5_IMAGE))])
	if _load_image(entry, a6):
		a7 = entry - 4 - 0x22c - 2 - 4
		alloc_and_read_resource_payload(memory.read_u16(a6 - 0xa))
		a7 = entry
		_jt(JT_AFTER_LOAD, [])
	else:
		memory.write_u32(_g(A5_IMAGE), 0)
	_jt(JT_FS_CLOSE, [memory.read_u16(a6 - 0xa)])
	_jt(JT_AFTER_CLOSE, [])
	memory.write_u16(a6 - 0x12a, _trap(TRAP_OPEN_RES_FILE, [_g(-0x1768)]) & 0xffff)
	if _trap(TRAP_RES_ERROR, []) & 0xffff != 0:
		_jt(JT_SHOW_ERROR, [memory.read_u32(_g(-0xd98)), 0])
		_trap(TRAP_EXIT_TO_SHELL, [])
	if memory.read_u32(_g(A5_IMAGE)) != 0:
		_protection(a6)
	_trap(TRAP_CLOSE_RES_FILE, [memory.read_u16(a6 - 0x12a)])
	_trap(TRAP_USE_RES_FILE, [memory.read_u16(_g(-0x1042))])
	for k in 0x3f:
		memory.write_u32(a6 - 0x22a + 4 * k, memory.read_u32(_g(-0x1aaa + 4 * k)))
	memory.write_u16(a6 - 0x22a + 0xfc, memory.read_u16(_g(-0x1aaa + 0xfc)))
	_jt(JT_GET_VOL, [a6 - 0x22c, a6 - 0x22a])
	var name: int = C68kScript.u32(_jt(JT_P2C, [a6 - 0x22a]))
	if _jt(JT_STRCMP, [name, _g(-0x175e)]) & 0xffff == 0:
		_jt(JT_POSITION_ALERT, [0x105])
		_trap(TRAP_STOP_ALERT, [0, 0x105])
		_trap(TRAP_EXIT_TO_SHELL, [])
	_jt(JT_MAX_MEM, [a6 - 8])
	return 1

func _fail(message: int, number: int) -> bool:
	_jt(JT_SHOW_ERROR, [memory.read_u32(_g(message)), number & 0xffff])
	return false

## 0x52..0x1e4: the header, the image and its relocations; false on an error (the message shown).
func _load_image(entry: int, a6: int) -> bool:
	var ref_num: int = memory.read_u16(a6 - 0xa)
	memory.write_u32(a6 - 0x10, 0x20)
	var err: int = _jt(JT_FS_READ, [_g(A5_HEADER), C68kScript.u32(a6 - 0x10), ref_num]) & 0xffff
	memory.write_u16(a6 - 0xc, err)
	if err != 0:
		return _fail(-0xe34, memory.read_u16(a6 - 0xc))
	var total: int = C68kScript.u32(memory.read_u32(_g(A5_TEXT)) + memory.read_u32(_g(A5_DATA)) + memory.read_u32(_g(A5_BSS)))
	memory.write_u32(a6 - 0x14, total)
	var image: int = C68kScript.u32(_jt(JT_NEW_PTR_CLEAR, [C68kScript.u32(total + 4)]))
	memory.write_u32(_g(A5_IMAGE), image)
	if image == 0:
		return _fail(-0xe30, 0)
	memory.write_u32(_g(A5_IMAGE), C68kScript.u32(image + ((4 - (image & 3)) & 3)))
	memory.write_u32(a6 - 0x10, memory.read_u32(_g(A5_TEXT)))
	_jt(JT_FS_READ, [memory.read_u32(_g(A5_IMAGE)), C68kScript.u32(a6 - 0x10), ref_num])
	if memory.read_u32(_g(A5_DATA)) != 0:
		memory.write_u32(a6 - 0x10, memory.read_u32(_g(A5_DATA)))
		_jt(JT_FS_READ, [C68kScript.u32(memory.read_u32(_g(A5_IMAGE)) + memory.read_u32(_g(A5_TEXT))), C68kScript.u32(a6 - 0x10), ref_num])
	var first: int = C68kScript.divs_trunc(memory.read_s32(_g(A5_TEXT_RELOC)), 8)
	var second: int = C68kScript.divs_trunc(memory.read_s32(_g(A5_DATA_RELOC)), 8)
	memory.write_u32(a6 - 0x18, C68kScript.u32(first))
	memory.write_u32(a6 - 0x1c, C68kScript.u32(second))
	var i: int = 0
	memory.write_u32(a6 - 4, 0)
	while C68kScript.s32(first + second) > i:
		memory.write_u32(a6 - 0x10, 8)
		_jt(JT_FS_READ, [a6 - 0x128, C68kScript.u32(a6 - 0x10), ref_num])
		var flags: int = memory.read_s32(a6 - 0x124)
		if (flags >> 7) & 1 == 0:
			if (flags >> 4) & 1 != 0:
				return _fail(-0xe2c, 0)
			if (flags >> 5) & 3 != 2:
				return _fail(-0xe28, 0)
			var base: int = memory.read_u32(_g(A5_IMAGE))
			var at: int = C68kScript.u32(base + memory.read_u32(a6 - 0x128))
			if i >= first:
				at = C68kScript.u32(base + memory.read_u32(_g(A5_TEXT)) + memory.read_u32(a6 - 0x128))
			memory.write_u32(at, C68kScript.u32(memory.read_u32(at) + base))
		i += 1
		memory.write_u32(a6 - 4, C68kScript.u32(i))
	return true

## 0x23a..0x2c4: the copy protection over the image's text, through CODE 5's routines (the frame's
## two values at -0x12e / -0x132).
func _protection(a6: int) -> void:
	_jt(0x592, [memory.read_u32(_g(A5_IMAGE)), memory.read_u32(_g(A5_TEXT))])
	_jt(0x59a, [_g(-0x1d4a), 0x38, C68kScript.u32(_trap(TRAP_TICK_COUNT, []))])
	_jt(0x5c2, [a6 - 0x12e, a6 - 0x132])
	var d: int = C68kScript.u32(_jt(0x57a, [memory.read_u32(a6 - 0x132)]))
	if _jt(0x5a2, [memory.read_u32(a6 - 0x12e), d]) & 0xffff != 0:
		_jt(0x58a, [])
	_jt(0x5ca, [a6 - 0x12e, a6 - 0x132])
	_jt(0x582, [])
	d = C68kScript.u32(_jt(0x57a, [memory.read_u32(a6 - 0x132)]))
	if _jt(0x5a2, [memory.read_u32(a6 - 0x12e), d]) & 0xffff != 0:
		_jt(0x5b2, [memory.read_u32(a6 - 0x12e), memory.read_u32(a6 - 0x132)])
