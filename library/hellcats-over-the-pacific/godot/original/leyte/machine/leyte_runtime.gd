extends RefCounted
## Leyte Gulf's routines the runtime writes (as unexported.gd does for 1.0.3), registered where Leyte's
## ports call them: CODE 5's protection helpers Ghidra did not export. Leyte's CODE 5 is 1.0.3's
## (host_ports.json "remap"), so each is 1.0.3's (unexported.gd) with Leyte's A5 operands, read from
## Leyte's bytes (sources/leyte-gulf-1-0/CODE/ID05.bin).

const C68kScript := preload("res://original/compat/c68k.gd")

var machine
var memory
var a5: int
var id05
var id02
var id06
var id04

func _init(owner) -> void:
	machine = owner
	memory = owner.memory
	a5 = owner.A5
	var segments = owner.segments
	id05 = segments.instances[5]
	id02 = segments.instances[2]
	segments.register_local(5, 0x3ba, func(a: Array) -> int: return id05_03ba(a[0], a[1], a[2], a[3]))
	# CODE 5 +0x4 (jump-table slot $5ba, called by the CODE 8 loader after FSClose): RTS, as in 1.0.3.
	segments.stand_ins[0x5ba] = func(_a: Array) -> int: return 0
	# CODE 5 +0x8b2 (slot $58a; 1.0.3's $532, arm_restore_gate): ADDQ.W #1, -$bc(A5); RTS.
	segments.stand_ins[0x58a] = func(_a: Array) -> int: return id05_08b2()
	# CODE 6 +0x858 (slot $1da; 1.0.3's $1b2, release_render_buffers).
	id06 = segments.instances[6]
	segments.stand_ins[0x1da] = func(_a: Array) -> int: return id06_0858()
	# CODE 4 +0x482 (slot $aa, run every pass of the event loop; 1.0.3's $172, CODE 4 +0x432).
	segments.stand_ins[0xaa] = func(_a: Array) -> int: return id04_0482()
	# CODE 4 +0x694 (slot $15a, the Active Duty Roster; 1.0.3's $132, CODE 4 +0x644) and +0x208, the
	# default ring item it installs (1.0.3's +0x1b8).
	id04 = segments.instances[4]
	segments.stand_ins[0x15a] = func(_a: Array) -> int: return id04_0694()
	segments.register_local(4, 0x208, func(a: Array) -> int: return id04_0208(a[0], a[1], a[2]))
	# CODE 4 +0xc52 (slot $92, Select a Mission; 1.0.3's $82, CODE 4 +0xc02) and the CODE 4 routines it
	# calls that the port does not hold.
	segments.stand_ins[0x92] = func(_a: Array) -> int: return id04_0c52()
	segments.register_local(4, 0x32c, func(a: Array) -> int: return id04_032c(a[0], a[1], a[2], a[3]))
	segments.register_local(4, 0x9c, func(a: Array) -> int: return id04_009c(a[0], a[1], a[2]))
	segments.register_local(4, 0xc4, func(a: Array) -> int: return id04_00c4(a[0], a[1]))
	segments.register_local(4, 0x566, func(_a: Array) -> int: return id04_0566())
	# The same routine through its jump-table slot $9a (quit_application asks it; 1.0.3's $13a).
	segments.stand_ins[0x9a] = func(_a: Array) -> int: return id04_0566()
	segments.register_local(4, 0x1b4c, func(_a: Array) -> int: return id04_1b4c())
	# CODE 2 +0x9e8 / +0xa88: the session handles locked / unlocked (1.0.3's 0x95a / 0x9fa).
	segments.register_local(2, 0x9e8, func(_a: Array) -> int: return id02_09e8())
	segments.register_local(2, 0xa88, func(_a: Array) -> int: return id02_0a88())
	# CODE 2 +0x434 (slot $54a, C strncmp; 1.0.3's $502 at the same offset, the same bytes).
	segments.stand_ins[0x54a] = func(a: Array) -> int: return id02_0434(a[0], a[1], a[2])

func _g(offset: int) -> int:
	return (a5 + offset) & 0xffffffff

func _run(port, method: String, args: Array = []) -> int:
	var saved: int = port.a7
	port.a7 = machine.free_stack()
	var r = port.callv(method, args)
	port.a7 = saved
	return 0 if r == null else int(r)

func _jt(offset: int, args: Array = []) -> int:
	return id02.jt_routines[offset].call(args)

## CODE 5 0x3ba (build_protection_code_strings; 1.0.3's id05_03ba): the same instructions with Leyte's
## operands: GetVol JT $2da, the C string JT $33a, strcmp JT $562, the string copy JT $572, the equal
## case's string at A5 - 0xda4 (1.0.3 - 0xd66); A5 - 0x2c8 and - 0xca are where 1.0.3 has them.
func id05_03ba(code: int, code_text: int, other: int, other_text: int) -> int:
	var frame: int = machine.free_stack()
	var a6: int = frame - 4
	for k in 0xfe:
		memory.write_u8(a6 - 0x10c + k, memory.read_u8(_g(-0x2c8) + k))
	_run(id05, "get_machine_screen_fingerprint", [a6 - 2, a6 - 4, a6 - 6, a6 - 8, a6 - 0xa])
	_run(id05, "read_TEXT128_pair", [code, other])
	if memory.read_u32(code) == memory.read_u32(other):
		_jt(0x2da, [a6 - 0xc, a6 - 0x10c])
		var name: int = _jt(0x33a, [a6 - 0x10c])
		if _jt(0x562, [name, _g(-0xca)]) & 0xffff == 0:
			_run(id05, "write_TEXT128_pair", [0, _run(id05, "random_28bit_value")])
	_run(id05, "read_TEXT128_pair", [code, other])
	if memory.read_u32(other) == memory.read_u32(code):
		_jt(0x572, [other_text, memory.read_u32(_g(-0xda4))])
	else:
		var fields: Array = []
		for off in [2, 4, 6, 8, 0xa]:
			fields.append(memory.read_u16(a6 - off))
		_jt(0x572, [other_text, _run(id05, "pack_fields_to_code_buf68", fields)])
		_jt(0x572, [other_text + 6, _run(id05, "encode_code28_to_buf54", [memory.read_u32(other)])])
	_jt(0x572, [code_text, _run(id05, "encode_long_to_buf40", [memory.read_u32(code)])])
	return 0

## CODE 5 0x8b2 (arm_restore_gate): A5 - 0xbc counts up.
func id05_08b2() -> int:
	memory.write_u16(_g(-0xbc), (memory.read_u16(_g(-0xbc)) + 1) & 0xffff)
	return 0

## CODE 6 0x858 (release_render_buffers; 1.0.3's id06_0858), as Leyte's bytes read: 0x15a frees the
## pattern tables A5 - 0x1f60 then - 0x1f64 (free_aligned, each cleared); 0x582 the edge pool A5 -
## 0x1d5c when set (cleared); free_row_table (0x512); the raster target A5 - 0x1d54 when set (cleared).
func id06_0858() -> int:
	for slot in [-0x1f60, -0x1f64]:
		_run(id06, "free_aligned", [memory.read_u32(_g(slot))])
		memory.write_u32(_g(slot), 0)
	if memory.read_u32(_g(-0x1d5c)) != 0:
		_run(id06, "free_aligned", [memory.read_u32(_g(-0x1d5c))])
	memory.write_u32(_g(-0x1d5c), 0)
	_run(id06, "free_row_table")
	if memory.read_u32(_g(-0x1d54)) != 0:
		_run(id06, "free_aligned", [memory.read_u32(_g(-0x1d54))])
	memory.write_u32(_g(-0x1d54), 0)
	return 0

## CODE 4 0x482 (1.0.3's id04_0432, report_panel_store_failure: the same instructions with Leyte's
## operands, read from Leyte's ID04.bin): a pending message (A5 - 0x151e; 1.0.3 - 0x14a0) is shown:
## JT $4ba (1.0.3 $462), then show_caution_alert_with_number (JT $4da; 1.0.3 $4ba) with the C string at
## A5 - 0xf80 (1.0.3 - 0xf42) and 0; the flag cleared.
func id04_0482() -> int:
	if memory.read_u16(_g(-0x151e)) != 0:
		_jt(0x4ba)
		_jt(0x4da, [memory.read_u32(_g(-0xf80)), 0])
		memory.write_u16(_g(-0x151e), 0)
	return 0

## A routine of CODE 4 the original calls by its offset: the port's own when one is registered there,
## else reported missing (never answered for it) with 0, as a jump-table slot with no routine is.
func _local4(offset: int, args: Array) -> int:
	if id04.code_routines.has(offset):
		return int(id04.code_routines[offset].call(args))
	machine.note_missing("local:4+0x%x" % offset, "CODE 4 +0x%x not ported, %d args" % [offset, args.size()])
	return 0

func _trap(word: int, args: Array = []) -> int:
	return int(id02.traps[word].call(args))

## CODE 4 0x208 (1.0.3's id04_01b8, install_default_ring_item; C: dialog, item, value): the item
## becomes a user item drawn by the procedure at slot $ea (1.0.3 $12a) (GetDItem, SetDItem with its
## type and box), and A5 - 0x2322 (1.0.3 - 0x225e) = value.
func id04_0208(dialog: int, item: int, value: int) -> int:
	var f: int = machine.free_stack() - 0x10
	_trap(0xa98d, [f, f + 8, f + 0xc, item, dialog])
	_trap(0xa98e, [f, _g(0xea), memory.read_u16(f + 0xc), item, dialog])
	memory.write_u16(_g(-0x2322), value & 0xffff)
	return 0

## CODE 4 0x694 (1.0.3's id04_0644, run_duty_roster_dialog: the same instructions with Leyte's
## operands, read from Leyte's ID04.bin). DLOG 130 centred (JT $4aa); the pilots ranked
## (rank_pilots_by_score 0x11ba into the frame, prune_inactive_pilots_beyond_ten 0x13d6); up to ten
## active ones (record +0x20 clear) of the roster **(A5 - 0x1426) listed: item n+5 their rank's name
## (the strings at A5 - 0x103c by record +0x22), n+15 their name, n+25 their score
## (compute_pilot_score 0x5d8, the text by JT $512); the listed pilots kept in the frame (-0x110, as
## the original: the item-4 and OK rows are read back from it as they come). The settings' pilot
## (**(A5 - 0x142a) +0x20) becomes the current one (A5 - 0x1422); its row is selected when listed
## with a name or a score. Item 2 by 0x208, item 39 by set_dialog_user_item_proc (0x13c) with slot
## $102. ModalDialog (filter $e2): Cancel (3) returns 0; item 4 shows the row's pilot
## (show_mission_summary_dialog 0x3638 with A5 - 0xe88); OK (1) writes back the edited names (30
## bytes: p2c $33a, strncmp $54a, strncpy $552, c2p $342; the roster changed and written when one
## differs), makes the selected row's pilot current and returns 1.
func id04_0694() -> int:
	var a6: int = machine.free_stack() - 4
	var dlg: int = _trap(0xa97c, [0xffffffff, 0, 0x82])
	memory.write_u32(a6 - 0xa, dlg)
	_jt(0x4aa, [dlg])
	_run(id04, "rank_pilots_by_score", [a6 - 0xd4, a6 - 0xfc])
	_run(id04, "prune_inactive_pilots_beyond_ten", [a6 - 0xfc])
	var box: int = a6 - 0x1a
	var n: int = 0
	for i in 0x14:
		var pilot: int = memory.read_s16(a6 - 0xfc + 2 * i)
		var rec: int = (memory.read_u32(memory.read_u32(_g(-0x1426))) + (pilot << 6)) & 0xffffffff
		if memory.read_u16(rec + 0x20) != 0:
			continue
		_trap(0xa98d, [box, a6 - 0x12, a6 - 0xe, n + 5, dlg])
		_trap(0xa98f, [memory.read_u32(_g(-0x103c) + 4 * memory.read_s16(rec + 0x22)), memory.read_u32(a6 - 0x12)])
		memory.write_u32(a6 - 0x84, rec)
		_trap(0xa98d, [box, a6 - 0x12, a6 - 0xe, n + 15, dlg])
		_trap(0xa98f, [memory.read_u32(a6 - 0x84), memory.read_u32(a6 - 0x12)])
		_trap(0xa98d, [box, a6 - 0x12, a6 - 0xe, n + 25, dlg])
		var score_text: int = a6 - 0x7e + 10 * n
		_jt(0x512, [_run(id04, "compute_pilot_score", [pilot]), score_text])
		_trap(0xa98f, [score_text, memory.read_u32(a6 - 0x12)])
		memory.write_u16(a6 - 0x110 + 2 * n, pilot & 0xffff)
		n += 1
		if n == 10:
			break
	memory.write_u16(a6 - 6, n)
	memory.write_u16(_g(-0x1422), memory.read_u16(memory.read_u32(memory.read_u32(_g(-0x142a))) + 0x20))
	var selected: int = 0
	for i in n:
		var pilot: int = memory.read_s16(a6 - 0x110 + 2 * i)
		if pilot != memory.read_s16(_g(-0x1422)):
			continue
		var rec: int = (memory.read_u32(memory.read_u32(_g(-0x1426))) + (pilot << 6)) & 0xffffffff
		if memory.read_u8(rec) != 0 or _run(id04, "compute_pilot_score", [pilot]) != 0:
			selected = i
	memory.write_u16(a6 - 4, selected)
	_trap(0xa98d, [box, a6 - 0x12, a6 - 0xe, selected + 15, dlg])
	_trap(0xa97e, [0x3e8, 0, selected + 15, dlg])
	id04_0208(dlg, 2, 1)
	_run(id04, "set_dialog_user_item_proc", [dlg, 0x27, _g(0x102)])
	_trap(0xa915, [dlg])
	while true:
		_trap(0xa991, [a6 - 0xc, _g(0xe2)])
		var hit: int = memory.read_u16(a6 - 0xc)
		if hit == 3:
			_trap(0xa983, [dlg])
			return 0
		if hit == 4:
			var row: int = C68kScript.s16(memory.read_u16(dlg + 0xa4) - 14)
			_local4(0x3638, [memory.read_s16(a6 - 0x110 + 2 * row), 0, memory.read_u32(_g(-0xe88))])
		if hit == 1:
			break
	var changed: bool = false
	for i in n:
		var pilot: int = memory.read_s16(a6 - 0x110 + 2 * i)
		var rec: int = (memory.read_u32(memory.read_u32(_g(-0x1426))) + (pilot << 6)) & 0xffffffff
		memory.write_u32(a6 - 0x114, rec)
		_trap(0xa98d, [box, a6 - 0x12, a6 - 0xe, i + 15, dlg])
		_trap(0xa990, [a6 - 0x214, memory.read_u32(a6 - 0x12)])
		_jt(0x33a, [a6 - 0x214])
		_jt(0x33a, [rec])
		if _jt(0x54a, [rec, a6 - 0x214, 0x1e]) & 0xffff != 0:
			changed = true
			_jt(0x552, [rec, a6 - 0x214, 0x1e])
		_jt(0x342, [rec])
	memory.write_u16(a6 - 0x80, 1 if changed else 0)
	if changed:
		_trap(0xa9aa, [memory.read_u32(_g(-0x1426))])
		_trap(0xa9b0, [memory.read_u32(_g(-0x1426))])
	var row: int = C68kScript.s16(memory.read_u16(dlg + 0xa4) - 14)
	memory.write_u16(_g(-0x1422), memory.read_u16(a6 - 0x110 + 2 * row))
	_trap(0xa983, [dlg])
	return 1

## CODE 2 0x434 (1.0.3's id02_0434, ansi_strncmp: byte for byte the same routine; C strncmp(a, b, n)):
## 0 when the first n bytes (up to a terminator) are equal, else -1 / 1 as the first difference is
## below / above (unsigned).
func id02_0434(a: int, b: int, n: int) -> int:
	n &= 0xffffffff
	var k: int = 0
	while n > 0:
		var x: int = memory.read_u8(a + k)
		var y: int = memory.read_u8(b + k)
		if x != y:
			return 1 if x > y else 0xffffffff
		if x == 0:
			return 0
		k += 1
		n -= 1
	return 0

static func _s16(v: int) -> int:
	v &= 0xffff
	return v - 0x10000 if v >= 0x8000 else v

## CODE 4 0x32c (1.0.3's id04_02dc, set_radio_group_selection: byte for byte the same; C: dialog,
## item numbers, count, selected): when `selected` is within the group (from its first to its last
## item) every radio button of the group gets value 1 if it is the selected one, else 0.
func id04_032c(dialog: int, items: int, count: int, selected: int) -> int:
	selected = _s16(selected)
	count = _s16(count)
	if selected < memory.read_s16(items) or selected > memory.read_s16(items + 2 * (count - 1)):
		return 0
	var f: int = machine.free_stack() - 0x10
	for k in count:
		var item: int = memory.read_s16(items + 2 * k)
		_trap(0xa98d, [f, f + 8, f + 0xc, item, dialog])
		_trap(0xa963, [1 if item == selected else 0, memory.read_u32(f + 8)])
	return 0

## CODE 4 0x9c (Leyte's own; C: dialog, item, value): the item's control (GetDItem) gets the value
## (SetCtlValue).
func id04_009c(dialog: int, item: int, value: int) -> int:
	var f: int = machine.free_stack() - 0x10
	_trap(0xa98d, [f, f + 8, f + 0xc, item & 0xffff, dialog])
	_trap(0xa963, [value & 0xffff, memory.read_u32(f + 8)])
	return 0

## CODE 4 0xc4 (Leyte's own; C: dialog, item): the item's control value (GetDItem, GetCtlValue).
func id04_00c4(dialog: int, item: int) -> int:
	var f: int = machine.free_stack() - 0x10
	_trap(0xa98d, [f, f + 8, f + 0xc, item & 0xffff, dialog])
	return _trap(0xa960, [memory.read_u32(f + 8)]) & 0xffff

## CODE 4 0x1b4c (1.0.3's get_mission_kill_report_state 0x18e0 without its first test: Leyte has no
## "game state 2 reports nothing"): 2 when A5 - 0x153e is clear and A5 - 0x1334 is positive; 1 when
## any of the ten kill counts at A5 - 0x1348 is set; else 0.
func id04_1b4c() -> int:
	if memory.read_u16(_g(-0x153e)) == 0 and memory.read_s16(_g(-0x1334)) > 0:
		return 2
	for i in 0xa:
		if memory.read_u16(_g(-0x1348 + 2 * i)) != 0:
			return 1
	return 0

## CODE 4 0x566 (1.0.3's confirm_end_mission 0x516; Leyte's mission is running from game state 2,
## 1.0.3's from 3): in a running mission (A5 - 0x1376 from 2, A5 - 0x1542 clear) with something to
## report (0x1b4c), caution alert 0x103's answer (positioned by JT $4c2); otherwise 2.
func id04_0566() -> int:
	if memory.read_s16(_g(-0x1376)) < 2 or memory.read_u16(_g(-0x1542)) != 0:
		return 2
	if id04_1b4c() & 0xffff == 0:
		return 2
	_jt(0x4c2, [0x103])
	return _trap(0xa988, [0, 0x103]) & 0xffff

## CODE 4 0xc52 (1.0.3's id04_0c02, run_scenario_selection_dialog, with Leyte's operands and two
## options 1.0.3 does not have). The settings' scenario (**(A5 - 0x142a) +0x1e, 1 when unset) and the
## fuel percent (A5 - 0x1350, 100 when unset, into A5 - 0x2310); DLOG 129 centred (JT $4aa); item 2
## by 0x208, items 4 / 19 / 5 by the procedures at slots $172 / $102 / $142. Selected: the scenario's
## radio button (scenario + 7, scenario 1 shown as 2) among the 8 at A5 - 0x230a, bombs item 8 among
## the 3 at A5 - 0x22fa, item 23 or 24 (A5 - 0x134a clear or set) of the 2 at A5 - 0x22f4, and the
## checkbox 25 from A5 - 0x134c. ModalDialog (filter $162): Cancel (3) returns 0; OK (1) goes on when
## confirm_end_mission (0x566) answers 2; any other item selects within the three groups, and item 25
## toggles its value (0 / 6, the box checked when 6). Then, each stored when it changed (A5 - 0x1408
## set): the bombs (selection - 6, settings +0x24), the second group (selection - 23, +0x26), the
## checkbox (+0x28 toggled when the box's 0 / 6 differs from it), the scenario (selection - 7, +0x1e),
## the fuel (+0x22) and the pilot (+0x20, A5 - 0x1422). The dialog disposed, the settings applied
## (JT $4fa), A5 - 0x1542 cleared, the game state A5 - 0x1376 = the scenario, the overlay reset (JT
## $51a); the scenario is returned.
func id04_0c52() -> int:
	var a6: int = machine.free_stack() - 4
	var settings: int = memory.read_u32(memory.read_u32(_g(-0x142a)))
	var scenario: int = memory.read_s16(settings + 0x1e)
	var second: int = memory.read_u16(_g(-0x134a))
	var box: int = memory.read_u16(_g(-0x134c))
	if scenario == 0:
		scenario = 1
	if memory.read_u16(_g(-0x1350)) == 0:
		memory.write_u16(_g(-0x1350), 100)
	memory.write_u16(_g(-0x2310), memory.read_u16(_g(-0x1350)))
	var dlg: int = _trap(0xa97c, [0xffffffff, 0, 0x81])
	memory.write_u32(a6 - 4, dlg)
	_jt(0x4aa, [dlg])
	id04_0208(dlg, 2, 1)
	_run(id04, "set_dialog_user_item_proc", [dlg, 4, _g(0x172)])
	_run(id04, "set_dialog_user_item_proc", [dlg, 0x13, _g(0x102)])
	_run(id04, "set_dialog_user_item_proc", [dlg, 5, _g(0x142)])
	if scenario == 1:
		scenario = 2
	id04_032c(dlg, _g(-0x230a), 8, scenario + 7)
	id04_032c(dlg, _g(-0x22fa), 3, 8)
	id04_032c(dlg, _g(-0x22f4), 2, 0x17 if second == 0 else 0x18)
	id04_009c(dlg, 0x19, 0 if box == 0 else 1)
	_trap(0xa915, [dlg])
	while true:
		_trap(0xa991, [a6 - 6, _g(0x162)])
		var hit: int = memory.read_u16(a6 - 6)
		if hit == 3:
			_trap(0xa983, [dlg])
			return 0
		if hit == 1:
			if id04_0566() & 0xffff == 2:
				break
			continue
		id04_032c(dlg, _g(-0x230a), 8, hit)
		id04_032c(dlg, _g(-0x22fa), 3, hit)
		id04_032c(dlg, _g(-0x22f4), 2, hit)
		if hit == 0x19:
			box = 6 if box == 0 else 0
			id04_009c(dlg, hit, 0 if box == 0 else 1)
	var bombs: int = _run(id04, "get_radio_group_selection", [dlg, _g(-0x22fa), 3])
	settings = memory.read_u32(memory.read_u32(_g(-0x142a)))
	if _s16(bombs) != -1 and (bombs - 6) & 0xffff != memory.read_u16(settings + 0x24):
		memory.write_u16(settings + 0x24, (bombs - 6) & 0xffff)
		memory.write_u16(_g(-0x1408), 1)
	var group: int = _run(id04, "get_radio_group_selection", [dlg, _g(-0x22f4), 2])
	settings = memory.read_u32(memory.read_u32(_g(-0x142a)))
	if _s16(group) != -1 and (group - 0x17) & 0xffff != memory.read_u16(settings + 0x26):
		memory.write_u16(settings + 0x26, (group - 0x17) & 0xffff)
		memory.write_u16(_g(-0x1408), 1)
	var checked: int = 0 if id04_00c4(dlg, 0x19) == 0 else 6
	settings = memory.read_u32(memory.read_u32(_g(-0x142a)))
	if checked != memory.read_u16(settings + 0x28):
		memory.write_u16(settings + 0x28, 6 if memory.read_u16(settings + 0x28) == 0 else 0)
		memory.write_u16(_g(-0x1408), 1)
	var mission: int = _run(id04, "get_radio_group_selection", [dlg, _g(-0x230a), 8])
	settings = memory.read_u32(memory.read_u32(_g(-0x142a)))
	if _s16(mission) != -1 and (mission - 7) & 0xffff != memory.read_u16(settings + 0x1e):
		memory.write_u16(settings + 0x1e, (mission - 7) & 0xffff)
		memory.write_u16(_g(-0x1408), 1)
	if memory.read_u16(settings + 0x22) != memory.read_u16(_g(-0x2310)):
		memory.write_u16(settings + 0x22, memory.read_u16(_g(-0x2310)))
		memory.write_u16(_g(-0x1408), 1)
	if memory.read_u16(settings + 0x20) != memory.read_u16(_g(-0x1422)):
		memory.write_u16(settings + 0x20, memory.read_u16(_g(-0x1422)))
		memory.write_u16(_g(-0x1408), 1)
	_trap(0xa983, [dlg])
	_jt(0x4fa)
	memory.write_u16(_g(-0x1542), 0)
	memory.write_u16(_g(-0x1376), memory.read_u16(memory.read_u32(memory.read_u32(_g(-0x142a))) + 0x1e))
	_jt(0x51a)
	return memory.read_u16(_g(-0x1376))

## The four session handles (1.0.3: A5 - 0x11f6, - 0x11dc, - 0x11d4, - 0x12b0) and where their master
## pointers are kept (1.0.3: - 0x11f2, - 0x11d8, - 0x11d0, - 0x12ac).
const SESSION_HANDLES := [[-0x1270, -0x126c], [-0x1256, -0x1252], [-0x124e, -0x124a], [-0x132a, -0x1326]]

## CODE 2 0x9e8 (1.0.3's id02_095a, lock_session_handles, with Leyte's operands): the first of nested
## calls (counter A5 - 0x1572) locks the four session handles (JT $28a HLock) and keeps their master
## pointers, 24-bit cleaned when A5 - 0x13c6 is set.
func id02_09e8() -> int:
	var depth: int = memory.read_u16(_g(-0x1572))
	memory.write_u16(_g(-0x1572), (depth + 1) & 0xffff)
	if depth != 0:
		return 0
	for pair in SESSION_HANDLES:
		_jt(0x28a, [memory.read_u32(_g(pair[0]))])
	for pair in SESSION_HANDLES:
		var p: int = memory.read_u32(memory.read_u32(_g(pair[0])))
		if memory.read_u16(_g(-0x13c6)) != 0:
			p &= 0xffffff
		memory.write_u32(_g(pair[1]), p)
	return 0

## CODE 2 0xa88 (1.0.3's id02_09fa, unlock_session_handles): the last of the nested calls unlocks them
## (JT $292 HUnlock), the fourth first, as the original.
func id02_0a88() -> int:
	var depth: int = (memory.read_u16(_g(-0x1572)) - 1) & 0xffff
	memory.write_u16(_g(-0x1572), depth)
	if depth == 0:
		for slot in [-0x132a, -0x1270, -0x1256, -0x124e]:
			_jt(0x292, [memory.read_u32(_g(slot))])
	return 0
