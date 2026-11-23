class_name LeyteReconstructedID04
extends "res://original/leyte/port/ID04.gd"
## Leyte Gulf's CODE 4 functions that 1.0.3's port cannot give (sources/leyte-gulf-1-0/host_ports.json
## "unverified": not found in Leyte, or differing), written from Leyte's own bytes
## (sources/leyte-gulf-1-0/CODE/ID04.bin, address = file offset) over the generated partial port
## (original/leyte/port/ID04.gd, which refuses them). Every A5 and jump-table operand is Leyte's, read from the
## bytes; the generated port's constants keep 1.0.3's where nothing verified reaches them, so this file uses its own.
## The mission's end: conclude_mission (JT $10a) and its summary dialog, the path a flight result 1 or 6..10 takes
## out of run_flight_session_leyte.

const LEYTE_JT_CENTRE_DIALOG: int = 0x4aa      ## (C, dialog): placed before it is shown (1.0.3: $472)
const LEYTE_JT_BLOCK_MOVE: int = 0x532         ## (C: dest, src, count)
const LEYTE_LOCAL_PLAY_SOUND: int = 0x39a8     ## play_named_sound_async (verified; 1.0.3 0x3124)
const LEYTE_LOCAL_STOP_SOUND: int = 0x39fc     ## stop_named_sound (verified; 1.0.3 0x3178)
const LEYTE_LOCAL_SUMMARY: int = 0x3638        ## show_mission_summary_dialog (below; 1.0.3 0x2de6)
const LEYTE_LOCAL_DEFAULT_RING: int = 0x208    ## install_default_ring_item (the runtime's id04_0208; 1.0.3 0x1b8)
const LEYTE_LOCAL_CONFIRM_END: int = 0x566     ## confirm_end_mission (the runtime's id04_0566; 1.0.3 0x516)
const LEYTE_LOCAL_ATTACH_SCROLL: int = 0x27e2  ## attach_list_scroll_bar (below; Leyte only)
const LEYTE_LOCAL_DISPOSE_SCROLL: int = 0x28cc ## dispose_list_scroll_bar (below; Leyte only)
const LEYTE_LOCAL_ADVANCE_SUMMARY: int = 0x29e8 ## advance_mission_summary_text (C: dialog, item, phase): word (1.0.3 0x21d6)
const LEYTE_LOCAL_CREATE_TEXT: int = 0x1a52    ## create_scrolling_text_item (verified)
const LEYTE_LOCAL_DISPOSE_TEXT: int = 0x1b08   ## dispose_scrolling_text_item (verified; Leyte passes dialog, item)
const LEYTE_LOCAL_SUMMARY_BUTTONS: int = 0x1b8e ## update_summary_dialog_buttons (verified)
const LEYTE_LOCAL_SCORE_TEXT: int = 0x35d8     ## set_score_item_text (verified)
const LEYTE_LOCAL_DISABLE: int = 0x6           ## disable_dialog_control (verified)
const LEYTE_LOCAL_HIDE: int = 0x54             ## hide_dialog_control (verified)
const LEYTE_LOCAL_SHOW: int = 0x78             ## show_dialog_control (verified)
const LEYTE_LOCAL_SET_TITLE: int = 0x114       ## set_dialog_control_title (verified)
const LEYTE_LOCAL_USER_ITEM: int = 0x13c       ## set_dialog_user_item_proc (verified)
const LEYTE_LIST_SCROLLS: int = -0x22d6        ## two 0x1a-byte records for items 6 and 7: control, proc, two rects

## 0x3a2e (link a6 -6; JT $10a; 1.0.3's conclude_mission 0x31aa with Leyte's A5): the end of a mission (`which`: the
## flight's result, `arg`: the message). The words A5 - 0x1542 / - 0x1540 are kept in the frame (never read back).
## A first death (A5 - 0x132e = 1, `which` not 3, A5 - 0x1540 clear) sets A5 - 0x1540 and, with sound on (A5 - 0x136c),
## plays the named sound (0x39a8); a status outside `which` 3 sets A5 - 0x1542. Leyte has no game-state test (1.0.3
## skipped state 2). The summary (0x3638: -1, which, arg) runs, the sound stopped after it (0x39fc); a changed pilot
## status (roster record +0x20: the handle A5 - 0x1426, pilot A5 - 0x1422, 64 bytes each) is stored and the roster
## written (ChangedResource, UpdateResFile A5 - 0x1042). Returns the summary's answer (a word).
func conclude_mission(which: int, arg: int) -> int:
	assert(a7 != 0, "conclude_mission needs a7 (its frame keeps two words and the answer)")
	var entry: int = a7
	var a6: int = entry - 4
	memory.write_u16(a6 - 2, memory.read_u16(C68kScript.u32(a5 - 0x1542)))
	memory.write_u16(a6 - 4, memory.read_u16(C68kScript.u32(a5 - 0x1540)))
	var w: int = C68kScript.s16(which)
	if memory.read_s16(C68kScript.u32(a5 - 0x132e)) == 1 and w != 3 and memory.read_u16(C68kScript.u32(a5 - 0x1540)) == 0:
		memory.write_u16(C68kScript.u32(a5 - 0x1540), 1)
		if memory.read_u16(C68kScript.u32(a5 - 0x136c)) != 0:
			a7 = a6 - 6 - 4
			_local(LEYTE_LOCAL_PLAY_SOUND, [])
			a7 = entry
	if w != 3 and memory.read_u16(C68kScript.u32(a5 - 0x132e)) != 0:
		memory.write_u16(C68kScript.u32(a5 - 0x1542), 1)
	a7 = a6 - 6 - 8 - 4
	var answer: int = _local(LEYTE_LOCAL_SUMMARY, [0xffff, which & 0xffff, C68kScript.u32(arg)]) & 0xffff
	a7 = entry
	memory.write_u16(a6 - 6, answer)
	if memory.read_u16(C68kScript.u32(a5 - 0x136c)) != 0:
		a7 = a6 - 6 - 4
		_local(LEYTE_LOCAL_STOP_SOUND, [])
		a7 = entry
	var rec: int = _leyte_roster_record()
	if memory.read_u16(rec + 0x20) != memory.read_u16(C68kScript.u32(a5 - 0x132e)):
		memory.write_u16(_leyte_roster_record() + 0x20, memory.read_u16(C68kScript.u32(a5 - 0x132e)))
		_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x1426))])
		_trap(TRAP_UPDATE_RES_FILE, [memory.read_u16(C68kScript.u32(a5 - 0x1042))])
	return memory.read_u16(a6 - 6)

## The current pilot's 64-byte roster record: the handle A5 - 0x1426, the pilot A5 - 0x1422 (1.0.3: - 0x13a8 / - 0x13a4).
func _leyte_roster_record() -> int:
	return C68kScript.u32(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x1426))) + (memory.read_s16(C68kScript.u32(a5 - 0x1422)) << 6))

## 0x3638 (link a6 -0x4a; 1.0.3's show_mission_summary_dialog 0x2de6 rewritten): the mission summary, dialog 0x85, for
## `pilot` (-1: the mission's), `phase` (the flight result; high byte flags) and the title `title`.
## The frame: the hit item -0xc (1 at the start), the dialog -0xa, a counter -2, the kept pilot -0x30 (A5 - 0x1422) and
## default item -0x32 (A5 - 0x2322), the twelve tallies at -0x4a (their address in A5 - 0x22da).
## Leyte against 1.0.3: no state-2 early return; the user items 5 (A5 + 0x12a), 6 (+0xb2), 7 (+0xfa), 9 (+0xf2) and 15
## (+0xca) in that order; items 6 and 7 get list scroll bars (attach_list_scroll_bar with A5 + 0x72 / + 0x7a) after the
## text item 8; the early-death flag is tested on each item 1 inside the loop; the scroll bars are disposed with the
## text; the filter is A5 + 0x18a.
## For a pilot: A5 - 0x22dc = pilot = A5 - 0x1422, A5 - 0x2318 = 1, his tallies (record +0x28) copied, items 1 and 4
## hidden; for the mission: A5 - 0x1422 = the scenario record's word +0x20 (A5 - 0x142a), A5 - 0x2318 = 0x2d, the
## mission's tallies (A5 - 0x1420) copied, items 1 and 4 shown. Phase 3 adds the twelve kill counts A5 - 0x1348 and
## rings item 2 with 3 (item 4 hidden); otherwise item 2 rings 1 when A5 - 0x1542 is set for the mission, else 3. The
## score text (item 14), the window shown, A5 - 0x2314 = TickCount, A5 - 0x22a2 = 0, A5 - 0x22a0 = phase; a first
## text step (0x29e8: dialog, 8, phase) that ends updates the buttons (0x1b8e). ModalDialog until item 3, or item 1
## for a pilot, or item 1 for the mission once confirm_end_mission (0x566) answers 2 (item 4 with A5 - 0x2318 set
## makes it 1). After: the texts and scroll bars disposed, DisposeDialog; the early-death flag (A5 - 0x153e) for the
## mission's phase 1 when A5 - 0x132e is not 1; item 1 for the mission ends the game (A5 - 0x1376 = 0, HideWindow
## A5 - 0x1548, A5 - 0x1544 = 0, A5 - 0x1542 = 1). The kept pilot and default item are restored; the hit item returned.
func show_mission_summary_dialog(pilot: int, phase: int, title: int) -> int:
	assert(a7 != 0, "show_mission_summary_dialog needs a7 (its frame holds the tallies)")
	var entry: int = a7
	var a6: int = entry - 4
	var body: int = a6 - 0x4a
	var hit: int = a6 - 0xc
	memory.write_u16(hit, 1)
	memory.write_u16(a6 - 0x30, memory.read_u16(C68kScript.u32(a5 - 0x1422)))
	memory.write_u16(a6 - 0x32, memory.read_u16(C68kScript.u32(a5 - 0x2322)))
	var tallies: int = a6 - 0x4a
	memory.write_u32(C68kScript.u32(a5 - 0x22da), C68kScript.u32(tallies))
	var dialog: int = C68kScript.u32(_trap(TRAP_GET_NEW_DIALOG, [0xffffffff, 0, 0x85]))
	memory.write_u32(a6 - 0xa, dialog)
	a7 = body - 8
	_jt(LEYTE_JT_CENTRE_DIALOG, [dialog])
	a7 = body - 0xa - 4
	for pair in [[5, 0x12a], [6, 0xb2], [7, 0xfa], [9, 0xf2], [0xf, 0xca]]:
		_local(LEYTE_LOCAL_USER_ITEM, [dialog, pair[0], C68kScript.u32(a5 + pair[1])])
	a7 = body - 6 - 4
	_local(LEYTE_LOCAL_DISABLE, [dialog, 3])
	_local(LEYTE_LOCAL_DISABLE, [dialog, 1])
	_local(LEYTE_LOCAL_CREATE_TEXT, [dialog, 8])
	a7 = body - 0xa - 4
	_local(LEYTE_LOCAL_ATTACH_SCROLL, [dialog, 6, C68kScript.u32(a5 + 0x72)])
	_local(LEYTE_LOCAL_ATTACH_SCROLL, [dialog, 7, C68kScript.u32(a5 + 0x7a)])
	_local(LEYTE_LOCAL_SET_TITLE, [dialog, 3, C68kScript.u32(title)])
	a7 = entry
	memory.write_u16(C68kScript.u32(a5 - 0x22dc), pilot & 0xffff)
	if memory.read_u16(C68kScript.u32(a5 - 0x22dc)) != 0xffff:
		memory.write_u16(C68kScript.u32(a5 - 0x1422), memory.read_u16(C68kScript.u32(a5 - 0x22dc)))
		memory.write_u32(C68kScript.u32(a5 - 0x2318), 1)
		a7 = body - 0xc - 4
		_jt(LEYTE_JT_BLOCK_MOVE, [tallies, C68kScript.u32(_leyte_roster_record() + 0x28), 0x18])
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_HIDE, [dialog, 1])
		_local(LEYTE_LOCAL_HIDE, [dialog, 4])
	else:
		memory.write_u16(C68kScript.u32(a5 - 0x1422), memory.read_u16(memory.read_u32(memory.read_u32(C68kScript.u32(a5 - 0x142a))) + 0x20))
		memory.write_u32(C68kScript.u32(a5 - 0x2318), 0x2d)
		a7 = body - 0xc - 4
		_jt(LEYTE_JT_BLOCK_MOVE, [tallies, C68kScript.u32(a5 - 0x1420), 0x18])
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_SHOW, [dialog, 1])
		_local(LEYTE_LOCAL_SHOW, [dialog, 4])
	a7 = entry
	if C68kScript.s16(phase) == 3:
		memory.write_u16(a6 - 2, 0)
		while memory.read_s16(a6 - 2) < 0xc:
			var i: int = memory.read_s16(a6 - 2)
			memory.write_u16(tallies + 2 * i, C68kScript.u16(memory.read_u16(tallies + 2 * i) + memory.read_u16(C68kScript.u32(a5 - 0x1348 + 2 * i))))
			memory.write_u16(a6 - 2, C68kScript.u16(i + 1))
		a7 = body - 8 - 4
		_local(LEYTE_LOCAL_DEFAULT_RING, [dialog, 2, 3])
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_HIDE, [dialog, 4])
	elif memory.read_u16(C68kScript.u32(a5 - 0x1542)) != 0 and memory.read_u16(C68kScript.u32(a5 - 0x22dc)) == 0xffff:
		a7 = body - 8 - 4
		_local(LEYTE_LOCAL_DEFAULT_RING, [dialog, 2, 1])
	else:
		a7 = body - 8 - 4
		_local(LEYTE_LOCAL_DEFAULT_RING, [dialog, 2, 3])
	a7 = body - 6 - 4
	_local(LEYTE_LOCAL_SCORE_TEXT, [dialog, 0xe])
	a7 = entry
	_trap(TRAP_SHOW_WINDOW, [dialog])
	memory.write_u32(C68kScript.u32(a5 - 0x2314), C68kScript.u32(_trap(TRAP_TICK_COUNT, [])))
	memory.write_u16(C68kScript.u32(a5 - 0x22a2), 0)
	memory.write_u16(C68kScript.u32(a5 - 0x22a0), phase & 0xffff)
	a7 = body - 8 - 4
	if (_local(LEYTE_LOCAL_ADVANCE_SUMMARY, [dialog, 8, phase & 0xffff]) & 0xffff) != 0:
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_SUMMARY_BUTTONS, [dialog, phase & 0xffff])
	a7 = entry
	while true:
		_trap(TRAP_MODAL_DIALOG, [hit, C68kScript.u32(a5 + 0x18a)])
		var item: int = memory.read_u16(hit)
		if item == 3:
			break
		if item == 4 and memory.read_u32(C68kScript.u32(a5 - 0x2318)) != 0:
			memory.write_u32(C68kScript.u32(a5 - 0x2318), 1)
		if item != 1:
			continue
		if memory.read_u16(C68kScript.u32(a5 - 0x22dc)) != 0xffff:
			break
		if (phase & 0xff) == 1 and memory.read_s16(C68kScript.u32(a5 - 0x132e)) != 1:
			memory.write_u16(C68kScript.u32(a5 - 0x153e), 1)
		a7 = body - 4
		var confirm: int = _local(LEYTE_LOCAL_CONFIRM_END, []) & 0xffff
		a7 = entry
		if confirm == 2:
			break
	a7 = body - 6 - 4
	_local(LEYTE_LOCAL_DISPOSE_TEXT, [dialog, 8])
	_local(LEYTE_LOCAL_DISPOSE_SCROLL, [dialog, 6])
	_local(LEYTE_LOCAL_DISPOSE_SCROLL, [dialog, 7])
	a7 = entry
	_trap(TRAP_DISPOSE_DIALOG, [dialog])
	var mission: bool = memory.read_u16(C68kScript.u32(a5 - 0x22dc)) == 0xffff
	if mission and (phase & 0xff) == 1 and memory.read_s16(C68kScript.u32(a5 - 0x132e)) != 1:
		memory.write_u16(C68kScript.u32(a5 - 0x153e), 1)
	if memory.read_u16(hit) == 1 and memory.read_u16(C68kScript.u32(a5 - 0x22dc)) == 0xffff:
		memory.write_u16(C68kScript.u32(a5 - 0x1376), 0)
		_trap(TRAP_HIDE_WINDOW, [memory.read_u32(C68kScript.u32(a5 - 0x1548))])
		memory.write_u16(C68kScript.u32(a5 - 0x1544), 0)
		memory.write_u16(C68kScript.u32(a5 - 0x1542), 1)
	memory.write_u16(C68kScript.u32(a5 - 0x2322), memory.read_u16(a6 - 0x32))
	memory.write_u16(C68kScript.u32(a5 - 0x1422), memory.read_u16(a6 - 0x30))
	return memory.read_u16(hit)

## 0x27e2 (link a6 -0x24; Leyte only): a list item's scroll bar. Item 6 or 7 (record (item - 6) of the two 0x1a-byte
## records at A5 - 0x22d6) keeps `proc` (+4); its box (GetDItem) less 16 pixels on the right goes to +0x10 and +8, and a
## scroll bar (NewControl procID 16, title A5 - 0x21bc, visible, all values 0, refCon (item - 6) | 0x80000000) in its
## right 16 pixels, 2 pixels out at top and bottom (InsetRect 0, -2) and moved 2 right, goes to +0.
func attach_list_scroll_bar(dialog: int, item: int, proc: int) -> void:
	assert(a7 != 0, "attach_list_scroll_bar needs a7 (its frame holds the rects)")
	var a6: int = a7 - 4
	var index: int = C68kScript.s16(item - 6)
	memory.write_u16(a6 - 2, C68kScript.u16(index))
	var rec: int = C68kScript.u32(a5 + LEYTE_LIST_SCROLLS + C68kScript.s32(index * 0x1a))
	memory.write_u32(rec + 4, C68kScript.u32(proc))
	_get_ditem(dialog, item, a6 - 4, a6 - 0xc, a6 - 0x14)
	memory.write_u32(a6 - 0x1c, memory.read_u32(a6 - 0x14))
	memory.write_u32(a6 - 0x18, memory.read_u32(a6 - 0x10))
	memory.write_u16(a6 - 0x1a, C68kScript.u16(memory.read_u16(a6 - 0xe) - 0x10))
	_trap(TRAP_INSET_RECT, [0xfffe, 0, C68kScript.u32(a6 - 0x1c)])
	_trap(TRAP_OFFSET_RECT, [0, 2, C68kScript.u32(a6 - 0x1c)])
	memory.write_u16(a6 - 0xe, C68kScript.u16(memory.read_u16(a6 - 0xe) - 0x10))
	for at in [0x10, 8]:
		memory.write_u32(rec + at, memory.read_u32(a6 - 0x14))
		memory.write_u32(rec + at + 4, memory.read_u32(a6 - 0x10))
	var ref_con: int = C68kScript.u32(index) | 0x80000000
	memory.write_u32(a6 - 0x24, rec)
	memory.write_u32(rec, C68kScript.u32(_trap(TRAP_NEW_CONTROL, [ref_con, 0x10, 0, 0, 0, 1,
			C68kScript.u32(a5 - 0x21bc), _box(a6 - 0x1c), C68kScript.u32(dialog)])))

## 0x28cc (link a6 -2; Leyte only): DisposeControl of item 6's or 7's list scroll bar (record (item - 6) at A5 - 0x22d6).
func dispose_list_scroll_bar(_dialog: int, item: int) -> void:
	assert(a7 != 0, "dispose_list_scroll_bar needs a7")
	var a6: int = a7 - 4
	var index: int = C68kScript.s16(item - 6)
	memory.write_u16(a6 - 2, C68kScript.u16(index))
	_trap(TRAP_DISPOSE_CONTROL, [memory.read_u32(C68kScript.u32(a5 + LEYTE_LIST_SCROLLS + C68kScript.s32(index * 0x1a)))])

const LEYTE_LOCAL_APPEND_LINE: int = 0x1b1c    ## append_line_to_scrolling_text (verified; C: dialog, item, line)
const LEYTE_LOCAL_KILL_REPORT: int = 0x1b4c    ## get_mission_kill_report_state (the runtime's id04_1b4c)
const LEYTE_LOCAL_PILOT_SCORE: int = 0x5d8     ## compute_pilot_score (verified; C: pilot word): long
const LEYTE_LOCAL_TARGET_ROW: int = 0x1e28     ## a targets-destroyed row redrawn (C: dialog, item, kind; Leyte)
const LEYTE_LOCAL_LIST_REDRAW: int = 0x23b2    ## a list item redrawn (C: dialog, item; Leyte)
const LEYTE_LOCAL_LIST_SCROLL_SYNC: int = 0x262e ## a list's scroll bar synced (C: item; Leyte)
const LEYTE_JT_NUM_TO_STRING: int = 0x512      ## (C: long, buffer): the buffer (a Pascal string)
const LEYTE_JT_P2C: int = 0x33a                ## (C, string): in place
const LEYTE_JT_C2P: int = 0x342
const LEYTE_JT_STRCPY: int = 0x572             ## (C: dest, src)
const LEYTE_JT_STRCAT: int = 0x55a             ## (C: dest, src)
const LEYTE_TRAP_INVAL_RECT: int = 0xa928
const LEYTE_SUMMARY_STAGE: int = -0x22a2       ## the text's stage (1.0.3: - 0x2222)
const LEYTE_SUMMARY_LIST: int = -0x2246        ## the message list (1.0.3: - 0x21c6), its next slot at - 0x224a
const LEYTE_SUMMARY_LEFT: int = -0x21f6        ## messages left / the kill kind (1.0.3: - 0x2176)

## 0x29e8 (link a6 -0x5e; 1.0.3's advance_mission_summary_text 0x21d6, the runtime's id04_21d6, with Leyte's operands):
## one step of the summary's text (item `item`, 8) per call, by stage (A5 - 0x22a2); 1 when the text is complete.
## Leyte's messages are 1.0.3's moved (A5 - 0xeXX by -0x3e, A5 - 0x22XX by -0x80); the status is A5 - 0x132e, the
## replay flag A5 - 0x132c, the promotion counter A5 - 0x1330, the kill report word A5 - 0x1334, the early-death flag
## A5 - 0x153e, the game state A5 - 0x1376. Leyte's differences: phase 3's closing line by game state 2 / 8 (- 0xef4),
## 5 (- 0xefc), 6 (- 0xd9c), 7 (- 0xef8); stage 2's medal bit is 1 << (game state + 6) and redraws list item 6
## (0x23b2, 0x262e) instead of item 5, and it scans the ten kill counts itself (none: the kill report's line and, with
## something to report, the rank stage 4 at once); the kill stage 3 is inline (one kind per call: "n name[s]" by
## JT $512 / $55a, the record +0x28, the mission's - 0x1420 and the shown tallies (A5 - 0x22da) raised, the score
## and the kind's row redrawn (0x35d8, 0x1e28), item 7 redrawn for kinds 0..2), as is the rank stage 4 (the score
## against A5 - 0x1014 by rank + 1; the new rank the last at or under the score) and its stage 5 (the rank's line,
## items 15 and 9 redrawn). Stages above 5 return 0.
func advance_mission_summary_text(dialog: int, item: int, phase: int) -> int:
	assert(a7 != 0, "advance_mission_summary_text needs a7 (its frame holds the line and rects)")
	var entry: int = a7
	var a6: int = entry - 4
	var body: int = a6 - 0x5e
	phase = phase & 0xffff
	var low: int = phase & 0xff
	var high: int = (phase >> 8) & 0xff
	memory.write_u16(a6 - 0x46, 0)
	memory.write_u16(a6 - 0x48, 0)
	var stage: int = memory.read_s16(C68kScript.u32(a5 + LEYTE_SUMMARY_STAGE))
	var r: int = 0
	if stage == 0:
		r = _summary_collect(dialog, item, low, high, body)
	elif stage == 1:
		r = _summary_messages(dialog, item, body)
	elif stage == 2:
		r = _summary_medal_and_kills(dialog, item, low, a6, body)
	elif stage == 3:
		r = _summary_next_kill(dialog, item, a6, body)
	elif stage == 4:
		r = _summary_rank(a6, body)
	elif stage == 5:
		r = _summary_rank_line(dialog, item, a6, body)
	a7 = entry
	return r

func _summary_status() -> int:
	return memory.read_s16(C68kScript.u32(a5 - 0x132e))

func _summary_set_stage(v: int) -> void:
	memory.write_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_STAGE), v & 0xffff)

## A message: the long at A5 + `off` (a string pointer) into the list's next slot.
func _summary_push(off: int) -> void:
	_summary_push_pointer(memory.read_u32(C68kScript.u32(a5 + off)))

func _summary_push_pointer(p: int) -> void:
	var at: int = memory.read_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4))
	memory.write_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4), C68kScript.u32(at + 4))
	memory.write_u32(at, C68kScript.u32(p))

func _summary_pop() -> int:
	var at: int = memory.read_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4))
	memory.write_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4), C68kScript.u32(at + 4))
	return memory.read_u32(at)

## The list's size counted into A5 - 0x21f6 and its slot reset to the start.
func _summary_count_and_rewind() -> void:
	var base: int = C68kScript.u32(a5 + LEYTE_SUMMARY_LIST)
	var n: int = C68kScript.asr32(C68kScript.s32(memory.read_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4)) - base), 2)
	memory.write_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT), C68kScript.u16(n))
	memory.write_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4), base)

func _summary_append(dialog: int, item: int, line: int, body: int) -> void:
	var saved: int = a7
	a7 = body - 0xa - 4
	_local(LEYTE_LOCAL_APPEND_LINE, [dialog, item & 0xffff, C68kScript.u32(line)])
	a7 = saved

func _summary_roster_changed() -> void:
	_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(C68kScript.u32(a5 - 0x1426))])
	_trap(TRAP_UPDATE_RES_FILE, [memory.read_u16(C68kScript.u32(a5 - 0x1042))])

## Stage 0 (0x2a0e): the outcome's messages collected by the phase's low byte.
func _summary_collect(dialog: int, item: int, low: int, high: int, body: int) -> int:
	memory.write_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4), C68kScript.u32(a5 + LEYTE_SUMMARY_LIST))
	var skip: bool = false
	var kills: bool = false
	var st: int = _summary_status()
	match low:
		1:
			if st == 1:
				if memory.read_u16(C68kScript.u32(a5 - 0x132c)) != 0:
					_summary_push(-0xf28)
				else:
					_summary_push(-0xf24)
					var pick: int = C68kScript.asr32(C68kScript.s32(_trap(TRAP_TICK_COUNT, [])), 2) & 3
					_summary_push([-0xf20, -0xf1c, -0xf18, -0xf14][pick])
			else:
				_summary_push(-0xf10)
		6, 7:
			kills = true
			_summary_push(-0x229e if low == 6 else -0x2252)
			if st == 4:
				_summary_push(-0x228a if low == 6 else -0x224e)
				_summary_push(-0x2286)
			elif (high & 2) != 0:
				_summary_push(-0x2276)
				if st == 0:
					_summary_push(-0x226e)
				elif st == 1:
					_summary_push(-0x226a if low == 6 else -0x225e)
			else:
				_summary_push(-0x227e if (high & 1) != 0 else -0x227a)
				if st == 0:
					_summary_push(-0x2272)
				elif st == 1:
					_summary_push(-0x226a if low == 6 else -0x225e)
				elif st == 3:
					_summary_push(-0x2266)
					_summary_push(-0x2262)
		10:
			kills = true
			for off in [-0x2296, -0x2292, -0x2266, -0x2262]:
				_summary_push(off)
		8:
			kills = true
			_summary_push(-0x229a)
			if (high & 2) != 0:
				if st == 0:
					_summary_push(-0x226e)
				elif st == 5:
					_summary_push(-0x225e)
			else:
				_summary_push(-0x227e if (high & 1) != 0 else -0x227a)
				if st == 0:
					_summary_push(-0x2272)
				elif st == 5:
					_summary_push(-0x225e)
				elif st == 3:
					_summary_push(-0x2266)
					_summary_push(-0x2262)
		9:
			_summary_push(-0x228e)
			if (high & 1) != 0:
				_summary_push(-0x227e)
				if st == 3:
					kills = true
					_summary_push(-0x2266)
					_summary_push(-0x2262)
				else:
					_summary_push(-0x225a)
					_summary_push(-0x2256)
		3:
			_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xf0c)), body)
			var saved: int = a7
			a7 = body - 4
			var report: int = C68kScript.s16(_local(LEYTE_LOCAL_KILL_REPORT, []))
			a7 = saved
			if st == 1:
				_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xf08)), body)
			elif report == 1:
				_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xf04)), body)
			elif report == 2:
				_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xf00)), body)
			if memory.read_u16(C68kScript.u32(a5 - 0x153e)) == 0:
				var report_word: int = memory.read_s16(C68kScript.u32(a5 - 0x1334))
				if report_word == -1:
					var line_off: int = 0
					match memory.read_s16(C68kScript.u32(a5 - 0x1376)):
						2, 8:
							line_off = -0xef4
						5:
							line_off = -0xefc
						6:
							line_off = -0xd9c
						7:
							line_off = -0xef8
					if line_off != 0:
						_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 + line_off)), body)
				elif report_word == -2:
					_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xef0)), body)
			_summary_set_stage(100)
			return 1
		_:
			skip = true
	if not skip and _summary_status() == 0 and memory.read_u16(C68kScript.u32(a5 - 0x1330)) != 0:
		_summary_push(-0x2282)
		if memory.read_s16(C68kScript.u32(a5 - 0x1330)) >= 5:
			_summary_push(-0xeec)
			_summary_push(-0xee8)
			memory.write_u16(C68kScript.u32(a5 - 0x132e), 1)
			memory.write_u16(C68kScript.u32(a5 - 0x1542), 1)
		else:
			var rec: int = _leyte_roster_record()
			memory.write_u16(rec + 0x24, C68kScript.u16(memory.read_u16(rec + 0x24) + 1))
			_summary_roster_changed()
			match memory.read_s16(_leyte_roster_record() + 0x24):
				1:
					_summary_push(-0xee4)
					_summary_push(-0xee0)
				2:
					_summary_push(-0xedc)
					_summary_push(-0xed8)
				3:
					_summary_push(-0xed4)
					_summary_push(-0x2286)
					memory.write_u16(C68kScript.u32(a5 - 0x132e), 4)
					memory.write_u16(C68kScript.u32(a5 - 0x1542), 1)
		memory.write_u16(C68kScript.u32(a5 - 0x1330), 0)
	if kills and memory.read_u16(C68kScript.u32(a5 - 0x153e)) == 0 and _summary_status() == 0:
		var report_word2: int = memory.read_s16(C68kScript.u32(a5 - 0x1334))
		if report_word2 > 0:
			_summary_push_pointer(C68kScript.u32(a5 - 0x21ba))
			_summary_push(-0xed0)
			_summary_push(-0xecc)
		elif report_word2 == 0:
			_summary_push_pointer(C68kScript.u32(a5 - 0x21b8))
			_summary_push(-0xec8)
			_summary_push(-0xec4)
		memory.write_u16(C68kScript.u32(a5 - 0x1334), 0xfffe)
	_summary_count_and_rewind()
	_summary_set_stage(1)
	return 0

## Stage 1 (0x30f0): one message per call; then stage 2 for the mission, or the closing line for a pilot.
func _summary_messages(dialog: int, item: int, body: int) -> int:
	var left: int = memory.read_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT))
	memory.write_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT), C68kScript.u16(left - 1))
	if left != 0:
		_summary_append(dialog, item, _summary_pop(), body)
		return 0
	if memory.read_u16(C68kScript.u32(a5 - 0x22dc)) == 0xffff:
		_summary_set_stage(2)
		return 0
	_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xec0)), body)
	_summary_set_stage(100)
	return 1

## Stage 2 (0x314c): the separator line; a successful flight's medal (phase 1, status not 1); the kills, or the rank.
func _summary_medal_and_kills(dialog: int, item: int, low: int, a6: int, body: int) -> int:
	_summary_append(dialog, item, C68kScript.u32(a5 - 0x21b6), body)
	if low == 1 and _summary_status() != 1:
		memory.write_u32(a6 - 0x5a, memory.read_u32(C68kScript.u32(a5 - 0x168a)))
		var rec: int = _leyte_roster_record()
		var shift: int = C68kScript.u16(memory.read_u16(C68kScript.u32(a5 - 0x1376)) + 6) & 63
		var bit: int = (1 << shift) & 0xffff if shift < 16 else 0
		memory.write_u16(rec + 0x26, memory.read_u16(rec + 0x26) | bit)
		_summary_roster_changed()
		_trap(TRAP_SET_PORT, [C68kScript.u32(dialog)])
		var saved: int = a7
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_LIST_REDRAW, [dialog, 6])
		a7 = body - 2 - 4
		_local(LEYTE_LOCAL_LIST_SCROLL_SYNC, [6])
		a7 = saved
		_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x5a)])
	var i: int = 0
	while i < 0xa and memory.read_u16(C68kScript.u32(a5 - 0x1348 + 2 * i)) == 0:
		i += 1
	memory.write_u16(a6 - 2, i)
	if i == 0xa:
		_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xebc)), body)
		var saved2: int = a7
		a7 = body - 4
		var report: int = _local(LEYTE_LOCAL_KILL_REPORT, []) & 0xffff
		a7 = saved2
		memory.write_u16(a6 - 2, report)
		if report == 0:
			_summary_set_stage(100)
			return 1
		_summary_set_stage(4)
		return _summary_rank(a6, body)
	_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xeb8)), body)
	_summary_append(dialog, item, memory.read_u32(C68kScript.u32(a5 - 0xeb4)), body)
	_summary_set_stage(3)
	memory.write_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT), 0)
	return 0

## Stage 3 (0x3268): the next kind with kills (A5 - 0x21f6 its index): "n name[s]" appended and tallied; after the
## tenth the roster is written and the rank stage 4 runs at once.
func _summary_next_kill(dialog: int, item: int, a6: int, body: int) -> int:
	var left_at: int = C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT)
	while memory.read_s16(left_at) < 0xa:
		var k: int = memory.read_s16(left_at)
		memory.write_u16(a6 - 4, C68kScript.u16(k))
		var n: int = memory.read_u16(C68kScript.u32(a5 - 0x1348 + 2 * k))
		memory.write_u16(a6 - 2, n)
		if n != 0:
			break
		memory.write_u16(left_at, C68kScript.u16(k + 1))
	var at: int = memory.read_s16(left_at)
	memory.write_u16(left_at, C68kScript.u16(at + 1))
	if at >= 0xa:
		_summary_roster_changed()
		_summary_set_stage(4)
		memory.write_u32(C68kScript.u32(a5 + LEYTE_SUMMARY_LIST - 4), C68kScript.u32(a5 + LEYTE_SUMMARY_LIST))
		return _summary_rank(a6, body)
	var kind: int = memory.read_s16(a6 - 4)
	var count: int = memory.read_s16(a6 - 2)
	var line: int = a6 - 0x44
	var saved: int = a7
	a7 = body - 8 - 4
	var s: int = C68kScript.u32(_jt(LEYTE_JT_NUM_TO_STRING, [C68kScript.u32(count), line]))
	a7 = body - 4 - 4
	_jt(LEYTE_JT_P2C, [s])
	a7 = body - 8 - 4
	_jt(LEYTE_JT_STRCAT, [line, C68kScript.u32(a5 - 0x21b4)])
	_jt(LEYTE_JT_STRCAT, [line, memory.read_u32(C68kScript.u32(a5 - 0xfc4 + kind * 4))])
	a7 = saved
	var rec: int = C68kScript.u32(_leyte_roster_record() + kind * 2)
	memory.write_u16(rec + 0x28, C68kScript.u16(memory.read_u16(rec + 0x28) + count))
	memory.write_u16(C68kScript.u32(a5 - 0x1348 + 2 * kind), 0)
	var mission_tally: int = C68kScript.u32(a5 - 0x1420 + 2 * kind)
	memory.write_u16(mission_tally, C68kScript.u16(memory.read_u16(mission_tally) + count))
	var shown: int = C68kScript.u32(memory.read_u32(C68kScript.u32(a5 - 0x22da)) + kind * 2)
	memory.write_u16(shown, C68kScript.u16(memory.read_u16(shown) + count))
	if count > 1:
		a7 = body - 8 - 4
		_jt(LEYTE_JT_STRCAT, [line, C68kScript.u32(a5 - 0x21b2)])
		a7 = saved
	_summary_append(dialog, item, line, body)
	a7 = body - 6 - 4
	_local(LEYTE_LOCAL_SCORE_TEXT, [dialog, 0xe])
	a7 = body - 8 - 4
	_local(LEYTE_LOCAL_TARGET_ROW, [dialog, 5, C68kScript.u16(kind)])
	if kind == 0 or kind == 1 or kind == 2:
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_LIST_REDRAW, [dialog, 7])
	a7 = saved
	return 0

## Stage 4 (0x33c6): the pilot's score (0x5d8) against the next rank's threshold (A5 - 0x1014, by rank + 1): reached,
## the promotion messages and the new rank (the last threshold at or under the score) stored, stage 5; else the end.
func _summary_rank(a6: int, body: int) -> int:
	var saved: int = a7
	a7 = body - 2 - 4
	var score: int = C68kScript.s32(_local(LEYTE_LOCAL_PILOT_SCORE, [memory.read_u16(C68kScript.u32(a5 - 0x1422))]))
	a7 = saved
	memory.write_u32(a6 - 8, C68kScript.u32(score))
	var rank: int = memory.read_s16(_leyte_roster_record() + 0x22)
	var threshold: int = memory.read_s32(C68kScript.u32(a5 - 0x1014 + C68kScript.s16(rank + 1) * 4))
	if score < threshold:
		_summary_set_stage(100)
		return 1
	if _summary_status() == 1:
		_summary_push(-0xeb0)
	else:
		_summary_push(-0xeac)
		_summary_push(-0xea8)
	var i: int = 0
	while memory.read_s32(C68kScript.u32(a5 - 0x1014 + i * 4)) <= score:
		i += 1
	i -= 1
	memory.write_u16(a6 - 4, C68kScript.u16(i))
	memory.write_u16(_leyte_roster_record() + 0x22, C68kScript.u16(i))
	_summary_roster_changed()
	_summary_count_and_rewind()
	_summary_set_stage(5)
	return 0

## Stage 5 (0x34a0): the promotion messages, one per call; then the new rank's line ("rank" + name + A5 - 0x21b0, the
## prefix A5 - 0xea4 when the status is 1, else - 0xea0) and items 15 and 9 redrawn (GetDItem, InsetRect -0x24 / -4
## and -4 / -4, EraseRect, InvalRect in the dialog's port); the end.
func _summary_rank_line(dialog: int, item: int, a6: int, body: int) -> int:
	var left: int = memory.read_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT))
	memory.write_u16(C68kScript.u32(a5 + LEYTE_SUMMARY_LEFT), C68kScript.u16(left - 1))
	if left != 0:
		_summary_append(dialog, item, _summary_pop(), body)
		return 0
	memory.write_u32(a6 - 0x5a, memory.read_u32(C68kScript.u32(a5 - 0x168a)))
	var name: int = memory.read_u32(C68kScript.u32(a5 - 0x103c + memory.read_s16(_leyte_roster_record() + 0x22) * 4))
	memory.write_u32(a6 - 0x5e, name)
	var line: int = a6 - 0x44
	var saved: int = a7
	a7 = body - 4 - 4
	_jt(LEYTE_JT_P2C, [name])
	a7 = body - 8 - 4
	_jt(LEYTE_JT_STRCPY, [line, memory.read_u32(C68kScript.u32(a5 - 0xea4 if _summary_status() == 1 else a5 - 0xea0))])
	_jt(LEYTE_JT_STRCAT, [line, name])
	_jt(LEYTE_JT_STRCAT, [line, C68kScript.u32(a5 - 0x21b0)])
	a7 = body - 4 - 4
	_jt(LEYTE_JT_C2P, [name])
	a7 = saved
	_summary_append(dialog, item, line, body)
	_trap(TRAP_SET_PORT, [C68kScript.u32(dialog)])
	for spec in [[0xf, 0xffdc, 0xfffc], [9, 0xfffc, 0xfffc]]:
		_get_ditem(dialog, spec[0], a6 - 0x4a, a6 - 0x4e, a6 - 0x56)
		_trap(TRAP_INSET_RECT, [spec[2], spec[1], C68kScript.u32(a6 - 0x56)])
		_trap(TRAP_ERASE_RECT, [C68kScript.u32(a6 - 0x56)])
		_trap(LEYTE_TRAP_INVAL_RECT, [C68kScript.u32(a6 - 0x56)])
	_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x5a)])
	_summary_set_stage(100)
	return 1

const LEYTE_LOCAL_TEXT_CLICK: int = 0x19be     ## a click on the summary text's scroll bar tracked (C: dialog, event): word (1.0.3 0x1752)
const LEYTE_LOCAL_LIST_CLICK: int = 0x2732     ## a click on a list's scroll bar (C: dialog, event): the item, or 0 (Leyte)
const LEYTE_JT_UPDATE_EVENT: int = 0x4a2       ## (C, event): a dialog's update handled (1.0.3: $492)
const LEYTE_JT_TICK_SPAN: int = 0x522          ## (C, long ticks): a word (1.0.3: $4da)

## 0x28f0 (link a6 -2; slot $18a, the summary's ModalDialog filter, Pascal (dialog, event, VAR item): a Boolean;
## 1.0.3's mission_summary_filter_proc 0x2104 with Leyte's operands): Return or Enter (the message's low byte 13 / 3)
## give the default item (A5 - 0x2322) once the text is done (A5 - 0x2318 clear); a click tracked on the text's scroll
## bar (0x19be) gives item 8, else one on a list's scroll bar (0x2732, Leyte) gives its answer; an update goes to
## JT $4a2. Otherwise (and after a key or click that did not end it), once JT $522 of TickCount - A5 - 0x2314 (a word)
## reaches A5 - 0x2318, the text advances a step (0x29e8 with the phase A5 - 0x22a0), the buttons are updated (0x1b8e
## with 0) when it ends, and the time is taken again (A5 - 0x2314). 1 when an item is given, else 0.
func summary_dialog_filter_proc(dialog: int, event: int, item_hit: int) -> int:
	assert(a7 != 0, "summary_dialog_filter_proc needs a7")
	var entry: int = a7
	var a6: int = entry - 4
	var body: int = a6 - 2
	match memory.read_u16(event):
		3:
			var c: int = memory.read_u32(C68kScript.u32(event + 2)) & 0xff
			if (c == 0xd or c == 3) and memory.read_u32(C68kScript.u32(a5 - 0x2318)) == 0:
				memory.write_u16(item_hit, memory.read_u16(C68kScript.u32(a5 - 0x2322)))
				return 1
		1:
			a7 = body - 8 - 4
			var tracked: int = _local(LEYTE_LOCAL_TEXT_CLICK, [C68kScript.u32(dialog), C68kScript.u32(event)]) & 0xffff
			a7 = entry
			if tracked != 0:
				memory.write_u16(item_hit, 8)
				return 1
			a7 = body - 8 - 4
			var list_item: int = _local(LEYTE_LOCAL_LIST_CLICK, [C68kScript.u32(dialog), C68kScript.u32(event)]) & 0xffff
			a7 = entry
			memory.write_u16(a6 - 2, list_item)
			if list_item != 0:
				memory.write_u16(item_hit, list_item)
				return 1
		6:
			a7 = body - 4 - 4
			_jt(LEYTE_JT_UPDATE_EVENT, [C68kScript.u32(event)])
			a7 = entry
	a7 = body - 4 - 4
	var span: int = C68kScript.s16(_jt(LEYTE_JT_TICK_SPAN, [C68kScript.u32(_trap(TRAP_TICK_COUNT, []) - memory.read_u32(C68kScript.u32(a5 - 0x2314)))]))
	a7 = entry
	if span < memory.read_s32(C68kScript.u32(a5 - 0x2318)):
		return 0
	a7 = body - 8 - 4
	if (_local(LEYTE_LOCAL_ADVANCE_SUMMARY, [C68kScript.u32(dialog), 8, memory.read_u16(C68kScript.u32(a5 - 0x22a0))]) & 0xffff) != 0:
		a7 = body - 6 - 4
		_local(LEYTE_LOCAL_SUMMARY_BUTTONS, [C68kScript.u32(dialog), 0])
	a7 = entry
	memory.write_u32(C68kScript.u32(a5 - 0x2314), C68kScript.u32(_trap(TRAP_TICK_COUNT, [])))
	return 0

## The summary's item drawers and their two icon lists (Leyte only; CODE 4 0x1d9e..0x2685).

const LEYTE_TRAP_CLIP_RECT: int = 0xa87b
const LEYTE_TRAP_GET_CICON: int = 0xaa1e      ## GetCIcon(id): handle (Pascal function)
const LEYTE_TRAP_PLOT_CICON: int = 0xaa1f     ## PlotCIcon(rect, handle)
const LEYTE_TRAP_DISPOSE_CICON: int = 0xaa25
const LEYTE_TRAP_EQUAL_RECT: int = 0xa8a6     ## EqualRect(a, b): Boolean
const LEYTE_TRAP_SECT_RECT: int = 0xa8aa      ## SectRect(a, b, VAR dst): Boolean
const LEYTE_TRAP_SCROLL_COPY: int = 0xa8ec    ## CopyBits(src bits, dst bits, src rect, dst rect, mode, mask)
const LEYTE_TRAP_SET_CTL_MAX: int = 0xa965
const LEYTE_TRAP_GET_CTL_VALUE: int = 0xa960
const LEYTE_TRAP_GET_CTL_MAX: int = 0xa962
const LEYTE_TRAP_SET_CTL_VALUE: int = 0xa963
const LEYTE_LOCAL_FRAME_ITEM: int = 0x170      ## frame_dialog_item_box (verified)
const LEYTE_LOCAL_LIST_SCROLL_MAX: int = 0x2436 ## leyte_list_set_scroll_max (below; C: index)
const LEYTE_LOCAL_LIST_SCROLL: int = 0x24b8     ## leyte_list_scroll (below; C: index)

## The list record of item 6 or 7: A5 - 0x22d6 + 0x1a (item - 6): +0 control, +4 fill procedure, +8 the view rect,
## +0x10 the content rect.
func _leyte_list(index: int) -> int:
	return C68kScript.u32(a5 + LEYTE_LIST_SCROLLS + C68kScript.s32(C68kScript.s16(index) * 0x1a))

func _copy_rect(dst: int, src: int) -> void:
	memory.write_u32(dst, memory.read_u32(src))
	memory.write_u32(dst + 4, memory.read_u32(src + 4))

## 0x2436 (link a6 -8, d7 saved; Leyte only): list `index`'s scroll bar maximum = its content height (+0x10 rect's
## bottom - top) less its view height (+8 rect's), 0 when not positive (SetCtlMax).
func leyte_list_set_scroll_max(index: int) -> void:
	var rec: int = _leyte_list(index)
	var d7: int = C68kScript.s16((memory.read_u16(rec + 0x14) - memory.read_u16(rec + 0x10)) - (memory.read_u16(rec + 0xc) - memory.read_u16(rec + 8)))
	_trap(LEYTE_TRAP_SET_CTL_MAX, [C68kScript.u16(d7) if d7 > 0 else 0, memory.read_u32(rec)])

## 0x24b8 (link a6 -0x36; Leyte only): list `index` scrolled to its bar's value. The shift = (view top - content top) -
## the bar's value; none: nothing. Otherwise the view rect (+8) inset one pixel top and bottom is the clip; the part that
## stays (the view less the shift on the leaving side) is copied (CopyBits within the port's bits, SectRect against the
## clip for both rects), the uncovered band computed, the content rect (+0x10) moved by the shift (OffsetRect), and the
## list's fill procedure (+4) called with the port and the band.
func leyte_list_scroll(index: int) -> void:
	assert(a7 != 0, "leyte_list_scroll needs a7 (its frame holds the rects)")
	var entry: int = a7
	var a6: int = entry - 4
	var rec: int = _leyte_list(index)
	memory.write_u16(a6 - 2, C68kScript.u16(memory.read_u16(rec + 8) - memory.read_u16(rec + 0x10)))
	memory.write_u16(a6 - 4, C68kScript.u16(_trap(LEYTE_TRAP_GET_CTL_VALUE, [memory.read_u32(rec)])))
	var shift: int = C68kScript.s16(memory.read_u16(a6 - 2) - memory.read_u16(a6 - 4))
	memory.write_u16(a6 - 6, C68kScript.u16(shift))
	if shift == 0:
		return
	_copy_rect(a6 - 0x36, rec + 8)
	_trap(TRAP_INSET_RECT, [1, 0, C68kScript.u32(a6 - 0x36)])
	_copy_rect(a6 - 0xe, a6 - 0x36)
	if shift > 0:
		memory.write_u16(a6 - 0xa, C68kScript.u16(memory.read_u16(a6 - 0xa) - shift))
	else:
		memory.write_u16(a6 - 0xe, C68kScript.u16(memory.read_u16(a6 - 0xe) - shift))
	_copy_rect(a6 - 0x16, a6 - 0xe)
	_trap(TRAP_OFFSET_RECT, [C68kScript.u16(shift), 0, C68kScript.u32(a6 - 0x16)])
	_trap(LEYTE_TRAP_SECT_RECT, [C68kScript.u32(a6 - 0x1e), C68kScript.u32(a6 - 0xe), C68kScript.u32(a6 - 0x36)])
	_trap(LEYTE_TRAP_SECT_RECT, [C68kScript.u32(a6 - 0x26), C68kScript.u32(a6 - 0x16), C68kScript.u32(a6 - 0x36)])
	var bits: int = C68kScript.u32(memory.read_u32(C68kScript.u32(a5 - 0x168a)) + 2)
	_trap(LEYTE_TRAP_SCROLL_COPY, [0, 0, C68kScript.u32(a6 - 0x26), C68kScript.u32(a6 - 0x1e), bits, bits])
	_copy_rect(a6 - 0x2e, a6 - 0xe)
	if shift > 0:
		memory.write_u16(a6 - 0x2a, C68kScript.u16(memory.read_u16(a6 - 0x2e) + shift))
	else:
		memory.write_u16(a6 - 0x2e, C68kScript.u16(memory.read_u16(a6 - 0x2a) + shift))
	_trap(LEYTE_TRAP_SECT_RECT, [C68kScript.u32(a6 - 0x2e), C68kScript.u32(a6 - 0x2e), C68kScript.u32(a6 - 0x36)])
	_trap(TRAP_OFFSET_RECT, [C68kScript.u16(shift), 0, C68kScript.u32(rec + 0x10)])
	a7 = entry - 4 - 0x36 - 8 - 4
	_call_address(memory.read_u32(rec + 4), [memory.read_u32(C68kScript.u32(a5 - 0x168a)), C68kScript.u32(a6 - 0x2e)])
	a7 = entry

## 0x262e (link a6 -8; Leyte only; C: item): list (item - 6)'s scroll bar moved to its maximum when not there
## (GetCtlValue, GetCtlMax, SetCtlValue with GetCtlMax again), then 0x24b8 called with no argument of its own: it reads
## as its list index the word this frame keeps at -8, the bar's old value (as the original does).
func leyte_list_scroll_to_end(item: int) -> void:
	assert(a7 != 0, "leyte_list_scroll_to_end needs a7")
	var entry: int = a7
	var a6: int = entry - 4
	var index: int = C68kScript.s16(item - 6)
	memory.write_u16(a6 - 2, C68kScript.u16(index))
	var ctl: int = memory.read_u32(_leyte_list(index))
	memory.write_u32(a6 - 6, ctl)
	var value: int = _trap(LEYTE_TRAP_GET_CTL_VALUE, [ctl]) & 0xffff
	memory.write_u16(a6 - 8, value)
	if (_trap(LEYTE_TRAP_GET_CTL_MAX, [ctl]) & 0xffff) == value:
		return
	_trap(LEYTE_TRAP_SET_CTL_VALUE, [_trap(LEYTE_TRAP_GET_CTL_MAX, [ctl]) & 0xffff, ctl])
	a7 = a6 - 8 - 4
	_local(LEYTE_LOCAL_LIST_SCROLL, [memory.read_u16(a6 - 8)])
	a7 = entry

## 0x23b2 (link a6 -0x1c; Leyte only; C: dialog, item): list (item - 6) refilled (its fill procedure, +4, with the
## dialog and its view rect +8) and the item's box framed (0x170).
func leyte_list_redraw(dialog: int, item: int) -> void:
	assert(a7 != 0, "leyte_list_redraw needs a7")
	var entry: int = a7
	var a6: int = entry - 4
	var index: int = C68kScript.s16(item - 6)
	memory.write_u16(a6 - 2, C68kScript.u16(index))
	var rec: int = _leyte_list(index)
	a7 = a6 - 0x1c - 8 - 4
	_call_address(memory.read_u32(rec + 4), [C68kScript.u32(dialog), C68kScript.u32(rec + 8)])
	a7 = a6 - 0x1c - 6 - 4
	_local(LEYTE_LOCAL_FRAME_ITEM, [C68kScript.u32(dialog), item & 0xffff])
	a7 = entry

## A colour icon plotted in `rect`'s top right at the icon's own size (its handle's bounds +6): rect's left = right -
## the icon's width, rect's bottom = top + its height; PlotCIcon. (0x2124, 0x1dd6.)
func _leyte_plot_icon_at_right(rect: int, icon: int, scratch: int) -> void:
	_copy_rect(scratch, C68kScript.u32(memory.read_u32(icon) + 6))
	memory.write_u16(rect + 2, C68kScript.u16(memory.read_u16(rect + 6) - (memory.read_u16(scratch + 6) - memory.read_u16(scratch + 2))))
	memory.write_u16(rect + 4, C68kScript.u16((memory.read_u16(scratch + 4) - memory.read_u16(scratch)) + memory.read_u16(rect)))
	_trap(LEYTE_TRAP_PLOT_CICON, [icon, C68kScript.u32(rect)])

## 0x1ff8 (link a6 -0x48; slot $72, the medals list's fill procedure; C: port, rect): in `port` (the current port kept)
## `rect` erased and clipped; the list's content and view rects (A5 - 0x22c6 / - 0x22ce) copied. For each of the
## sixteen medal bits of the pilot's record word +0x26 (bit 0 first, the word shifted right in the frame), a set bit's
## icon (cicn 0x264 + i, from bit 8 0x1f8 + i) goes to cell (column, row) of three columns: the cell rect (x - 0x1e .. +
## 0x3c, y - 9 .. + 0x12 around (width * column / 3, height * row / 3)) moved by the content's (left + 0x20, top + 0xb),
## the content's bottom 3 below it, the icon plotted at its own size at the cell's top right and disposed. The clip back
## to the port's rect; a content rect that changed (EqualRect against A5 - 0x22c2) is stored and the scroll bar's
## maximum set (0x2436 with 0). The port restored.
func leyte_fill_medals_list(port: int, rect: int) -> void:
	assert(a7 != 0, "leyte_fill_medals_list needs a7 (its frame holds the rects)")
	var entry: int = a7
	var a6: int = entry - 4
	memory.write_u16(a6 - 0x14, 6)
	memory.write_u32(a6 - 0x44, memory.read_u32(C68kScript.u32(a5 - 0x168a)))
	_trap(TRAP_SET_PORT, [C68kScript.u32(port)])
	_trap(TRAP_ERASE_RECT, [C68kScript.u32(rect)])
	_trap(LEYTE_TRAP_CLIP_RECT, [C68kScript.u32(rect)])
	_copy_rect(a6 - 0x30, C68kScript.u32(a5 - 0x22c6))
	_copy_rect(a6 - 0x38, C68kScript.u32(a5 - 0x22ce))
	memory.write_u16(a6 - 8, C68kScript.u16(memory.read_u16(a6 - 0x2a) - memory.read_u16(a6 - 0x2e)))
	memory.write_u16(a6 - 0xa, C68kScript.u16(memory.read_u16(a6 - 0x34) - memory.read_u16(a6 - 0x38)))
	memory.write_u16(a6 - 4, 0)
	memory.write_u16(a6 - 6, 0)
	memory.write_u16(a6 - 0x10, memory.read_u16(_leyte_roster_record() + 0x26))
	for i in range(0x10):
		memory.write_u16(a6 - 2, i)
		if (memory.read_u8(a6 - 0xf) & 1) != 0:
			var cx: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s16(memory.read_s16(a6 - 8) * memory.read_s16(a6 - 4)), 3))
			var cy: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s16(memory.read_s16(a6 - 0xa) * memory.read_s16(a6 - 6)), 3))
			memory.write_u16(a6 - 0xc, C68kScript.u16(cx))
			memory.write_u16(a6 - 0xe, C68kScript.u16(cy))
			memory.write_u16(a6 - 0x26, C68kScript.u16(cx - 0x1e))
			memory.write_u16(a6 - 0x22, C68kScript.u16(cx - 0x1e + 0x3c))
			memory.write_u16(a6 - 0x28, C68kScript.u16(cy - 9))
			memory.write_u16(a6 - 0x24, C68kScript.u16(cy - 9 + 0x12))
			_trap(TRAP_OFFSET_RECT, [C68kScript.u16(memory.read_u16(a6 - 0x30) + 0xb), C68kScript.u16(memory.read_u16(a6 - 0x2e) + 0x20), C68kScript.u32(a6 - 0x28)])
			memory.write_u16(a6 - 0x2c, C68kScript.u16(memory.read_u16(a6 - 0x24) + 3))
			var icon: int = C68kScript.u32(_trap(LEYTE_TRAP_GET_CICON, [(0x1f8 + i) if i >= 8 else (0x264 + i)]))
			memory.write_u32(a6 - 0x48, icon)
			_leyte_plot_icon_at_right(a6 - 0x28, icon, a6 - 0x40)
			_trap(LEYTE_TRAP_DISPOSE_CICON, [icon])
			memory.write_u16(a6 - 4, C68kScript.u16(memory.read_u16(a6 - 4) + 1))
			if memory.read_s16(a6 - 4) == 3:
				memory.write_u16(a6 - 4, 0)
				memory.write_u16(a6 - 6, C68kScript.u16(memory.read_u16(a6 - 6) + 1))
		memory.write_u16(a6 - 0x10, C68kScript.u16(memory.read_s16(a6 - 0x10) >> 1))
	memory.write_u16(a6 - 2, 0x10)
	_trap(LEYTE_TRAP_CLIP_RECT, [C68kScript.u32(memory.read_u32(C68kScript.u32(a5 - 0x168a)) + 0x10)])
	if (_trap(LEYTE_TRAP_EQUAL_RECT, [C68kScript.u32(a5 - 0x22c2), C68kScript.u32(a6 - 0x2c)]) & 0xff) == 0:
		_copy_rect(C68kScript.u32(a5 - 0x22c6), a6 - 0x30)
		a7 = a6 - 0x48 - 2 - 4
		_local(LEYTE_LOCAL_LIST_SCROLL_MAX, [0])
		a7 = entry
	_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x44)])

## 0x21c6 (link a6 -0x4a; slot $7a, the aircraft-kill markers' fill procedure; C: port, rect): in `port` `rect` erased
## and clipped; list 1's content and view rects (A5 - 0x22ac / - 0x22b4) copied. The pilot's Zero, Frank and Bomber
## tallies (record +0x28 / +0x2a / +0x2c) give that many markers, in that order (cicn 0x3e8 / 0x3e9 / 0x3ea, each got
## once), six to a row: the cell rect (x - 0xd .. + 0x1a, y - 8 .. + 0x10 around (width * column / 6, height * row /
## 4)) moved by the content's (left + 0xf, top + 9), the content's bottom 1 below it, the marker plotted there. The icons
## disposed, the clip back to the port's rect; a content rect that changed (A5 - 0x22a8) is stored and the scroll bar's
## maximum set (0x2436 with 1). The port restored.
func leyte_fill_kill_markers_list(port: int, rect: int) -> void:
	assert(a7 != 0, "leyte_fill_kill_markers_list needs a7 (its frame holds the rects)")
	var entry: int = a7
	var a6: int = entry - 4
	memory.write_u32(a6 - 0x3e, memory.read_u32(C68kScript.u32(a5 - 0x168a)))
	_trap(TRAP_SET_PORT, [C68kScript.u32(port)])
	_trap(TRAP_ERASE_RECT, [C68kScript.u32(rect)])
	_trap(LEYTE_TRAP_CLIP_RECT, [C68kScript.u32(rect)])
	_copy_rect(a6 - 0x32, C68kScript.u32(a5 - 0x22ac))
	_copy_rect(a6 - 0x3a, C68kScript.u32(a5 - 0x22b4))
	memory.write_u16(a6 - 0xc, C68kScript.u16(memory.read_u16(a6 - 0x2c) - memory.read_u16(a6 - 0x30)))
	memory.write_u16(a6 - 0xe, C68kScript.u16(memory.read_u16(a6 - 0x36) - memory.read_u16(a6 - 0x3a)))
	memory.write_u16(a6 - 8, 0)
	memory.write_u16(a6 - 0xa, 0)
	var tallies: int = C68kScript.u32(_leyte_roster_record() + 0x28)
	memory.write_u32(a6 - 0x16, tallies)
	memory.write_u16(a6 - 0x1a, memory.read_u16(tallies))
	memory.write_u16(a6 - 0x1c, memory.read_u16(tallies + 2))
	memory.write_u16(a6 - 0x1e, memory.read_u16(tallies + 4))
	var total: int = C68kScript.u16(memory.read_u16(a6 - 0x1a) + memory.read_u16(a6 - 0x1c) + memory.read_u16(a6 - 0x1e))
	memory.write_u16(a6 - 4, total)
	memory.write_u16(a6 - 6, total)
	var icons: Array = []
	for k in range(3):
		var h: int = C68kScript.u32(_trap(LEYTE_TRAP_GET_CICON, [0x3e8 + k]))
		memory.write_u32(a6 - 0x42 - 4 * k, h)
		icons.append(h)
	memory.write_u16(a6 - 2, 0)
	while memory.read_s16(a6 - 2) < memory.read_s16(a6 - 4):
		var cx: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s16(memory.read_s16(a6 - 0xc) * memory.read_s16(a6 - 8)), 6))
		var cy: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s16(memory.read_s16(a6 - 0xe) * memory.read_s16(a6 - 0xa)), 4))
		memory.write_u16(a6 - 0x10, C68kScript.u16(cx))
		memory.write_u16(a6 - 0x12, C68kScript.u16(cy))
		memory.write_u16(a6 - 0x28, C68kScript.u16(cx - 0xd))
		memory.write_u16(a6 - 0x24, C68kScript.u16(cx - 0xd + 0x1a))
		memory.write_u16(a6 - 0x2a, C68kScript.u16(cy - 8))
		memory.write_u16(a6 - 0x26, C68kScript.u16(cy - 8 + 0x10))
		_trap(TRAP_OFFSET_RECT, [C68kScript.u16(memory.read_u16(a6 - 0x32) + 9), C68kScript.u16(memory.read_u16(a6 - 0x30) + 0xf), C68kScript.u32(a6 - 0x2a)])
		memory.write_u16(a6 - 0x2e, C68kScript.u16(memory.read_u16(a6 - 0x26) + 1))
		var which: int = 2
		if memory.read_u16(a6 - 0x1a) != 0:
			which = 0
			memory.write_u16(a6 - 0x1a, C68kScript.u16(memory.read_u16(a6 - 0x1a) - 1))
		elif memory.read_u16(a6 - 0x1c) != 0:
			which = 1
			memory.write_u16(a6 - 0x1c, C68kScript.u16(memory.read_u16(a6 - 0x1c) - 1))
		_trap(LEYTE_TRAP_PLOT_CICON, [icons[which], C68kScript.u32(a6 - 0x2a)])
		memory.write_u16(a6 - 8, C68kScript.u16(memory.read_u16(a6 - 8) + 1))
		if memory.read_s16(a6 - 8) == 6:
			memory.write_u16(a6 - 8, 0)
			memory.write_u16(a6 - 0xa, C68kScript.u16(memory.read_u16(a6 - 0xa) + 1))
		memory.write_u16(a6 - 2, C68kScript.u16(memory.read_u16(a6 - 2) + 1))
	for h in icons:
		_trap(LEYTE_TRAP_DISPOSE_CICON, [h])
	_trap(LEYTE_TRAP_CLIP_RECT, [C68kScript.u32(memory.read_u32(C68kScript.u32(a5 - 0x168a)) + 0x10)])
	if (_trap(LEYTE_TRAP_EQUAL_RECT, [C68kScript.u32(a5 - 0x22a8), C68kScript.u32(a6 - 0x2e)]) & 0xff) == 0:
		_copy_rect(C68kScript.u32(a5 - 0x22ac), a6 - 0x32)
		a7 = a6 - 0x4a - 2 - 4
		_local(LEYTE_LOCAL_LIST_SCROLL_MAX, [1])
		a7 = entry
	_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x3e)])

## 0x1e28 (link a6 -0x98; Leyte only; C: dialog, item, kind): one row of the targets-destroyed table. In the dialog
## (the port and its text font +0x44 kept), clipped to its rect: the item's box inset 4 horizontally; the row 16 pixels
## per kind (its top at box top + 16 kind + 1, 16 high) erased; at its baseline (+ the font's ascent) the kind's name
## (A5 - 0xfc4 + 4 kind) in the application font (TextFont 1), followed by the separator at A5 - 0x21be and the
## shown tally (word `kind` of the tallies A5 - 0x22da points to) when there is one; then right-aligned the points, tally * the kind's value (A5 - 0xf9c + 2 kind, mulu.l of the
## sign-extended words). The text font and port restored.
func leyte_draw_target_row(dialog: int, item: int, kind: int) -> void:
	assert(a7 != 0, "leyte_draw_target_row needs a7 (its frame holds the line and box)")
	var entry: int = a7
	var a6: int = entry - 4
	kind = C68kScript.s16(kind)
	memory.write_u16(a6 - 0x94, memory.read_u16(memory.read_u32(C68kScript.u32(a5 - 0x168a)) + 0x44))
	memory.write_u32(a6 - 0x98, memory.read_u32(C68kScript.u32(a5 - 0x168a)))
	memory.write_u16(a6 - 6, C68kScript.u16(kind))
	_trap(TRAP_SET_PORT, [C68kScript.u32(dialog)])
	_trap(LEYTE_TRAP_CLIP_RECT, [C68kScript.u32(memory.read_u32(C68kScript.u32(a5 - 0x168a)) + 0x10)])
	_get_ditem(dialog, item, a6 - 8, a6 - 0xc, a6 - 0x14)
	_trap(TRAP_INSET_RECT, [0, 4, C68kScript.u32(a6 - 0x14)])
	_trap(TRAP_GET_FONT_INFO, [C68kScript.u32(a6 - 0x92)])
	var y: int = C68kScript.u16(C68kScript.u16(kind << 4) + memory.read_u16(a6 - 0x92) + memory.read_u16(a6 - 0x14) + 1)
	memory.write_u16(a6 - 2, y)
	memory.write_u16(a6 - 0x14, C68kScript.u16(C68kScript.u16(kind << 4) + memory.read_u16(a6 - 0x14) + 1))
	memory.write_u16(a6 - 0x10, C68kScript.u16(memory.read_u16(a6 - 0x14) + 0x10))
	_trap(TRAP_ERASE_RECT, [C68kScript.u32(a6 - 0x14)])
	_trap(TRAP_MOVE_TO, [y, memory.read_u16(a6 - 0x12)])
	var line: int = a6 - 0x80
	var name: int = memory.read_u32(C68kScript.u32(a5 - 0xfc4 + kind * 4))
	var tally_at: int = C68kScript.u32(memory.read_u32(C68kScript.u32(a5 - 0x22da)) + kind * 2)
	if memory.read_u16(tally_at) != 0:
		a7 = a6 - 0x98 - 8 - 4
		_jt(LEYTE_JT_STRCPY, [line, name])
		_jt(LEYTE_JT_STRCAT, [line, C68kScript.u32(a5 - 0x21be)])
		var s: int = C68kScript.u32(_jt(LEYTE_JT_NUM_TO_STRING, [C68kScript.u32(memory.read_s16(tally_at)), C68kScript.u32(a6 - 0x8a)]))
		a7 = a6 - 0x98 - 4 - 4
		s = C68kScript.u32(_jt(LEYTE_JT_P2C, [s]))
		a7 = a6 - 0x98 - 8 - 4
		_jt(LEYTE_JT_STRCAT, [line, s])
	else:
		a7 = a6 - 0x98 - 8 - 4
		_jt(LEYTE_JT_STRCPY, [line, name])
	a7 = a6 - 0x98 - 4 - 4
	_jt(LEYTE_JT_C2P, [line])
	a7 = entry
	_trap(TRAP_TEXT_FONT, [1])
	_trap(TRAP_DRAW_STRING, [line])
	var points: int = C68kScript.u32(memory.read_s16(tally_at) * memory.read_s16(C68kScript.u32(a5 - 0xf9c + kind * 2)))
	a7 = a6 - 0x98 - 8 - 4
	_jt(LEYTE_JT_NUM_TO_STRING, [points, line])
	a7 = entry
	var width: int = _trap(TRAP_STRING_WIDTH, [line]) & 0xffff
	_trap(TRAP_MOVE_TO, [y, C68kScript.u16(memory.read_u16(a6 - 0xe) - width)])
	_trap(TRAP_DRAW_STRING, [line])
	_trap(TRAP_TEXT_FONT, [memory.read_u16(a6 - 0x94)])
	_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x98)])

## 0x1fb8 (slot $12a, a user item procedure, Pascal (dialog, item)): the targets-destroyed table: the item's box framed
## (0x170) and its ten rows drawn (0x1e28).
func leyte_target_table_item_proc(dialog: int, item: int) -> void:
	assert(a7 != 0, "leyte_target_table_item_proc needs a7")
	var entry: int = a7
	var a6: int = entry - 4
	a7 = a6 - 2 - 6 - 4
	_local(LEYTE_LOCAL_FRAME_ITEM, [C68kScript.u32(dialog), item & 0xffff])
	for k in range(0xa):
		memory.write_u16(a6 - 2, k)
		a7 = a6 - 2 - 8 - 4
		_local(LEYTE_LOCAL_TARGET_ROW, [C68kScript.u32(dialog), item & 0xffff, k])
	a7 = entry

## 0x23fe / 0x241a (slots $b2 / $fa, user item procedures, Pascal (dialog, item)): the medals list (item 6) or the
## kill markers (item 7) redrawn (0x23b2).
func leyte_medals_item_proc(dialog: int, item: int) -> void:
	assert(a7 != 0, "leyte_medals_item_proc needs a7")
	var entry: int = a7
	a7 = entry - 4 - 6 - 4
	_local(LEYTE_LOCAL_LIST_REDRAW, [C68kScript.u32(dialog), item & 0xffff])
	a7 = entry

func leyte_kill_markers_item_proc(dialog: int, item: int) -> void:
	assert(a7 != 0, "leyte_kill_markers_item_proc needs a7")
	var entry: int = a7
	a7 = entry - 4 - 6 - 4
	_local(LEYTE_LOCAL_LIST_REDRAW, [C68kScript.u32(dialog), item & 0xffff])
	a7 = entry

## 0x1d9e (link a6 -0x1a; slot $ca, a user item procedure, Pascal (dialog, item); 1.0.3's draw_rank_insignia 0x1b3e on
## Leyte's roster): the pilot's rank insignia (cicn 256 + record +0x22) at the item box's top right, at the icon's own
## size (PlotCIcon), then disposed.
func leyte_rank_insignia_item_proc(dialog: int, item: int) -> void:
	assert(a7 != 0, "leyte_rank_insignia_item_proc needs a7 (its frame holds the box)")
	var entry: int = a7
	var a6: int = entry - 4
	_get_ditem(dialog, item, a6 - 2, a6 - 6, a6 - 0xe)
	var icon: int = C68kScript.u32(_trap(LEYTE_TRAP_GET_CICON, [C68kScript.u16(memory.read_u16(_leyte_roster_record() + 0x22) + 0x100)]))
	memory.write_u32(a6 - 0x1a, icon)
	_leyte_plot_icon_at_right(a6 - 0xe, icon, a6 - 0x16)
	_trap(LEYTE_TRAP_DISPOSE_CICON, [icon])
