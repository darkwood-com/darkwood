class_name LeyteReconstructedID03
extends "res://original/leyte/port/ID03.gd"
## Leyte Gulf's CODE 3 functions that 1.0.3's port cannot give (sources/leyte-gulf-1-0/host_ports.json
## "unverified": not found in Leyte, or differing), written from Leyte's own bytes
## (sources/leyte-gulf-1-0/CODE/ID03.bin, address = file offset) over the generated partial port
## (original/leyte/port/ID03.gd, which refuses them). Every A5 operand is Leyte's, read from the bytes:
## the generated port's private panel helpers (_entry, _load_cached_panel, _draw_new_panel,
## _lend_panel_bitmap) and some of its constants (PANEL_TYPE 'HEL2', PANEL_ENTRY_SIZE 0x160, the cache
## handle A5 - 0x13b2, LOCAL_RELEASE_BITMAPS 0x59f4, JT_NEW_HANDLE_CLEAR $252) are 1.0.3's, kept only
## because nothing verified reaches them, so this file uses its own.

## The panel cache: 'HEL3' 127 of four 0x194-byte entries (1.0.3: 'HEL2', 0x160), its handle at
## A5 - 0x1430 (1.0.3: - 0x13b2); 0x650 bytes when made (1.0.3: 0x580).
const LEYTE_PANEL_TYPE: int = 0x48454c33       ## 'HEL3'
const LEYTE_PANEL_ENTRY_SIZE: int = 0x194
const LEYTE_PANEL_CACHE: int = -0x1430
const LEYTE_PANEL_CACHE_SIZE: int = 0x650
const LEYTE_PANEL_PORT: int = -0x1514          ## (1.0.3: - 0x1496)
const LEYTE_COLOUR_MODE: int = -0x13f8         ## (1.0.3: - 0x137a)
const LEYTE_CURRENT_ENTRY: int = -0x142c       ## the current cache entry (1.0.3: - 0x13ae)
const LEYTE_PANEL_BITMAP: int = -0x132a        ## the panel's bitmap handle (1.0.3: - 0x12b0)
const LEYTE_JT_NEW_HANDLE_CLEAR: int = 0x262   ## NewHandleClear(size): Pascal, long result (1.0.3: $252)
const LEYTE_JT_HLOCK: int = 0x28a
const LEYTE_JT_HUNLOCK: int = 0x292
const LEYTE_LOCAL_RELEASE_BITMAPS: int = 0x5be4   ## release_panel_bitmaps (below; 1.0.3's 0x59f4, the runtime's id03_59f4)
const LEYTE_LOCAL_REMAP_IDENTITY: int = 0x741e    ## remap_panel_colours_identity (below; 1.0.3 0x71a2)
const LEYTE_LOCAL_REMAP_CACHED: int = 0x6e5e      ## remap_cached_panel_colours (1.0.3 0x6c1c; not written for Leyte yet)
const LEYTE_LOCAL_SCREEN_ADDRESSES: int = 0x5a50  ## compute_panel_screen_addresses (verified)
const LEYTE_LOCAL_OPEN_CPORT: int = 0x5e26        ## open_cport_on_buffer (verified; C: buffer, word, long, rect by value): long
const LEYTE_LOCAL_CLOSE_PORT: int = 0x5ea6        ## close_port_glue (verified; C: port)
const LEYTE_LOCAL_FACE_REGION: int = 0x3832       ## make_inset_face_oval_region (verified; C: rect, three words): long
## The records an entry keeps, as [A5 offset, entry offset, bytes], read from Leyte's copies: 1.0.3's
## with one more, the 13 longs at +0x12a into A5 - 0x1200 (1.0.3's next records each 0x34 further on).
const LEYTE_PANEL_RECORDS: Array = [[-0x1242, 0xe8, 0x2c], [-0x1216, 0x114, 0x16], [-0x1200, 0x12a, 0x34],
		[-0x11cc, 0x15e, 0xc], [-0x11c0, 0x16a, 0x18], [-0x11a8, 0x182, 0xc], [-0x119c, 0x18e, 6]]

## Cache entry n.
func _leyte_entry(n: int) -> int:
	return C68kScript.u32(C68kScript.s16(n) * LEYTE_PANEL_ENTRY_SIZE + memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_CACHE))))

## CODE 3 0x5eb4 (slot $192: 1.0.3's prepare_instrument_panel 0x5cc4, the same instructions with Leyte's
## operands but for these, from Leyte's bytes): no CurResFile save or UseResFile(A5 - 0x1002) around it;
## the cache 'HEL3' / 0x194 / 0x650; one more record per entry; one more instrument drawn on a new panel.
## The panel scale (3/4 of the half view width A5 - 0x13f0) into A5 - 0x151c / - 0x1518, the overlay's
## cosine / sine (A5 - 0x10a8 / - 0x10a4) into A5 - 0x21a2 / - 0x219e; the cache got or made (NewHandleClear
## JT $262, AddResource named by A5 - 0xe38); the screen layout (0x5a50); the old gyro port (A5 - 0x1246),
## face region (A5 - 0x1434) and panel port disposed, the panel port opened on its buffer (A5 - 0x1510)
## over the rect (0, 0) .. (A5 - 0x13ee, _v(0x29e)) .. (A5 - 0x13f0). A cache entry of this display
## configuration (depth A5 - 0x13f8, colour bits - 0x13fc / - 0x13fa, view size - 0x13f0 / - 0x13ee) that
## is current (A5 - 0x142c) or stored (+0xc) is used: a stored one loaded first; the bitmap lent to the
## panel port, the gyro window (A5 - 0x1268 .. - 0x1262) painted in colour 0x21. Otherwise a new panel is
## drawn. Then the gyro tape port (depths 1 / 4: on A5 - 0x14a4, A5 - 0x1258, the size A5 - 0x125e /
## - 0x125c) and the face region (depth 1: 0x3832 with 0x2d0, 0x49c, 0x4ce).
func prepare_instrument_panel() -> void:
	assert(a7 != 0, "prepare_instrument_panel needs a7 (its frame holds the rects)")
	var entry: int = a7
	var a6: int = entry - 4
	var r: int = a6 - 0x1e
	memory.write_u16(a6 - 0xa, 0)
	var w: int = memory.read_s16(_g(-0x13f0))
	var scale: int = C68kScript.s16(C68kScript.u16(C68kScript.divs_trunc(C68kScript.s16(C68kScript.u16(C68kScript.divs_trunc(w, 2) + w)), 2)))
	memory.write_u32(_g(-0x151c), C68kScript.u32(scale))
	memory.write_u32(_g(-0x1518), memory.read_u32(_g(-0x151c)))
	memory.write_u32(_g(-0x21a2), memory.read_u32(_g(-0x10a8)))
	memory.write_u32(_g(-0x219e), memory.read_u32(_g(-0x10a4)))
	memory.write_u32(_g(LEYTE_PANEL_CACHE), C68kScript.u32(_trap(TRAP_GET_RESOURCE, [0x7f, LEYTE_PANEL_TYPE])))
	if memory.read_u32(_g(LEYTE_PANEL_CACHE)) == 0:
		memory.write_u32(_g(LEYTE_PANEL_CACHE), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE_CLEAR, [LEYTE_PANEL_CACHE_SIZE])))
		_trap(TRAP_ADD_RESOURCE, [memory.read_u32(_g(-0xe38)), 0x7f, LEYTE_PANEL_TYPE, memory.read_u32(_g(LEYTE_PANEL_CACHE))])
	_local(LEYTE_LOCAL_SCREEN_ADDRESSES, [])
	memory.write_u16(r + 2, 0)
	memory.write_u16(r + 6, memory.read_u16(_g(-0x13f0)))
	memory.write_u16(r, _v(0x29e))
	memory.write_u16(r + 4, memory.read_u16(_g(-0x13ee)))
	if memory.read_u32(_g(-0x1246)) != 0:
		_local(LEYTE_LOCAL_CLOSE_PORT, [memory.read_u32(_g(-0x1246))])
		memory.write_u32(_g(-0x1246), 0)
	if memory.read_u32(_g(-0x1434)) != 0:
		_trap(TRAP_DISPOSE_RGN, [memory.read_u32(_g(-0x1434))])
	if memory.read_u32(_g(LEYTE_PANEL_PORT)) != 0:
		_local(LEYTE_LOCAL_CLOSE_PORT, [memory.read_u32(_g(LEYTE_PANEL_PORT))])
	a7 = entry - 4 - 0x30 - 0x12 - 4
	memory.write_u32(_g(LEYTE_PANEL_PORT), C68kScript.u32(_local(LEYTE_LOCAL_OPEN_CPORT, [_g(-0x1510), 0, 0, memory.read_u32(r), memory.read_u32(r + 4)])))
	a7 = entry
	var found: bool = false
	for i in 4:
		var e: int = _leyte_entry(i)
		var same: bool = true
		for check in [[0, LEYTE_COLOUR_MODE], [2, -0x13fc], [4, -0x13fa], [6, -0x13f0], [8, -0x13ee]]:
			if memory.read_u16(e + check[0]) != memory.read_u16(_g(check[1])):
				same = false
				break
		if not same:
			continue
		var current: bool = memory.read_s16(_g(LEYTE_CURRENT_ENTRY)) == i
		if not current and memory.read_u16(e + 0xc) == 0:
			continue
		if not current:
			_leyte_load_cached_panel(entry, i)
		_leyte_lend_panel_bitmap(a6)
		memory.write_u16(r + 2, memory.read_u16(_g(-0x1266)))
		memory.write_u16(r, memory.read_u16(_g(-0x1268)))
		memory.write_u16(r + 6, C68kScript.u16(memory.read_u16(r + 2) + memory.read_u16(_g(-0x1264))))
		memory.write_u16(r + 4, C68kScript.u16(memory.read_u16(r) + memory.read_u16(_g(-0x1262))))
		_trap(TRAP_FORE_COLOR, [0x21])
		_trap(TRAP_PAINT_RECT, [r])
		_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x22)])
		_jt(LEYTE_JT_HUNLOCK, [memory.read_u32(_g(LEYTE_PANEL_BITMAP))])
		found = true
		break
	if not found:
		_leyte_draw_new_panel(entry, a6, r)
	var mode: int = memory.read_u16(_g(LEYTE_COLOUR_MODE))
	if mode == 1 or mode == 4:
		memory.write_u16(r + 2, 0)
		memory.write_u16(r, 0)
		memory.write_u16(r + 6, memory.read_u16(_g(-0x125e)))
		memory.write_u16(r + 4, memory.read_u16(_g(-0x125c)))
		a7 = entry - 4 - 0x30 - 0x12 - 4
		memory.write_u32(_g(-0x1246), C68kScript.u32(_local(LEYTE_LOCAL_OPEN_CPORT, [_g(-0x14a4), memory.read_u16(_g(-0x1258)), 0, memory.read_u32(r), memory.read_u32(r + 4)])))
		a7 = entry
	if memory.read_u16(_g(LEYTE_COLOUR_MODE)) == 1:
		memory.write_u32(_g(-0x1434), C68kScript.u32(_local(LEYTE_LOCAL_FACE_REGION, [a6 - 0x30, 0x2d0, 0x49c, 0x4ce])))

## A stored cache entry loaded (Leyte's copies, 0x60d6 .. 0x62e2): the ten 0x10-byte instrument records
## (+0x12 into A5 - 0x131c), the bitmaps released (0x5be4); the gyro record (+0xb2 into A5 - 0x127c),
## 'HEL3' 300 + i into A5 - 0x1270, the panel layout (+0xc6, 0x22 bytes, into A5 - 0x1268), 'HEL3' 400 /
## 500 + i into A5 - 0x1256 / - 0x124e; the entry made the most recent (+0xe above every other's,
## ChangedResource of the cache); the further records (LEYTE_PANEL_RECORDS); 'HEL3' 200 + i into the
## panel bitmap A5 - 0x132a, its row bytes (+0xa & 0x1fff) into A5 - 0x1322, _v(0x29e) into A5 - 0x1320;
## A5 - 0x21a4 = A5 - 0x142c = i; UpdateResFile(A5 - 0x1042); the colours remapped (0x741e).
func _leyte_load_cached_panel(entry: int, i: int) -> void:
	for k in 0xa:
		_copy(_g(-0x131c + 0x10 * k), _leyte_entry(i) + 0x10 * k + 0x12, 0x10)
	_local(LEYTE_LOCAL_RELEASE_BITMAPS, [])
	_copy(_g(-0x127c), _leyte_entry(i) + 0xb2, 0x14)
	memory.write_u32(_g(-0x1270), C68kScript.u32(_trap(TRAP_GET_RESOURCE, [C68kScript.u16(i + 0x12c), LEYTE_PANEL_TYPE])))
	_copy(_g(-0x1268), _leyte_entry(i) + 0xc6, 0x22)
	memory.write_u32(_g(-0x1256), C68kScript.u32(_trap(TRAP_GET_RESOURCE, [C68kScript.u16(i + 0x190), LEYTE_PANEL_TYPE])))
	memory.write_u32(_g(-0x124e), C68kScript.u32(_trap(TRAP_GET_RESOURCE, [C68kScript.u16(i + 0x1f4), LEYTE_PANEL_TYPE])))
	for k in 4:
		if memory.read_s32(_leyte_entry(i) + 0xe) < memory.read_s32(_leyte_entry(k) + 0xe):
			memory.write_u32(_leyte_entry(i) + 0xe, C68kScript.u32(memory.read_u32(_leyte_entry(k) + 0xe) + 1))
			_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(_g(LEYTE_PANEL_CACHE))])
	for rec in LEYTE_PANEL_RECORDS:
		_copy(_g(rec[0]), _leyte_entry(i) + rec[1], rec[2])
	memory.write_u32(_g(LEYTE_PANEL_BITMAP), C68kScript.u32(_trap(TRAP_GET_RESOURCE, [C68kScript.u16(i + 0xc8), LEYTE_PANEL_TYPE])))
	memory.write_u16(_g(-0x1322), memory.read_u16(_leyte_entry(i) + 0xa) & 0x1fff)
	memory.write_u16(_g(-0x1320), _v(0x29e))
	memory.write_u16(_g(-0x21a4), i)
	memory.write_u16(_g(LEYTE_CURRENT_ENTRY), i)
	_trap(TRAP_UPDATE_RES_FILE, [memory.read_u16(_g(-0x1042))])
	var e2: int = a7
	a7 = entry - 4 - 0x30 - 4
	_local(LEYTE_LOCAL_REMAP_IDENTITY, [])
	a7 = e2

## The panel bitmap (A5 - 0x132a) locked and lent to the panel port: its pixel map's base, and the
## current entry's row bytes (+0xa); the port made current (the old one kept at -0x22 of the frame).
func _leyte_lend_panel_bitmap(a6: int) -> void:
	_jt(LEYTE_JT_HLOCK, [memory.read_u32(_g(LEYTE_PANEL_BITMAP))])
	memory.write_u32(a6 - 0x22, memory.read_u32(_g(-0x168a)))
	var pm: int = memory.read_u32(memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_PORT)) + 2))
	memory.write_u32(pm, memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_BITMAP))))
	pm = memory.read_u32(memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_PORT)) + 2))
	memory.write_u16(pm + 4, memory.read_u16(_leyte_entry(memory.read_u16(_g(LEYTE_CURRENT_ENTRY))) + 0xa))
	_trap(TRAP_SET_PORT, [memory.read_u32(_g(LEYTE_PANEL_PORT))])

## The slots the new-panel path calls whose generated-port constants keep 1.0.3's numbers (read from
## Leyte's listing instead).
const LEYTE_JT_CREATING_PANEL_DIALOG: int = 0x4f2   ## (1.0.3: $49a; the generated port keeps $49a)
const LEYTE_JT_NEW_HANDLE: int = 0x25a              ## NewHandle (Pascal; 1.0.3: $24a)
const LEYTE_JT_VIEW_FRAME_OUTLINE: int = 0x36a      ## (1.0.3: $3ba; the generated port keeps $3ba)
const LEYTE_JT_SET_HANDLE_SIZE: int = 0x282

## A new panel drawn into the least recently used cache entry (Leyte's 0x637a .. 0x6df0: 1.0.3's with
## Leyte's operands, its four slots above, its records and its instruments): the "creating panel" dialog
## (JT $4f2); the entry with the smallest use stamp (+0xe) chosen, the bitmaps released (0x5be4), made the
## current one (A5 - 0x21a4 = A5 - 0x142c), its four bitmap handles made (NewHandle $25a for the panel
## A5 - 0x132a, NewHandleClear $262 for A5 - 0x1270 / - 0x1256 / - 0x124e), stamped above every other
## entry (the cache marked changed), keyed by this display (+0..+8) and its row bytes (+0xa: the view
## width x depth, rounded to words, with bit 15); the panel bitmap sized to the rows; the bitmap lent to
## the panel port and painted in colour 0; the frame region (JT $36a's outline) less the edge region
## filled with the panel pattern, the edge painted; the outline again at pen 3 and the bezel lines and
## corner arcs; the instruments drawn; the entry's records saved from the globals (the gyro record +0xb2,
## the layout +0xc6, the ten instrument records +0x12, LEYTE_PANEL_RECORDS); the port restored, the
## bitmap unlocked, the dialog disposed, the bitmaps stored and the colours remapped.
func _leyte_draw_new_panel(entry: int, a6: int, r: int) -> void:
	var dialog: int = C68kScript.u32(_jt(LEYTE_JT_CREATING_PANEL_DIALOG, []))
	memory.write_u32(a6 - 0x26, dialog)
	var best: int = 0x7fffffff
	var k: int = 0
	for i in 4:
		var use: int = memory.read_s32(_leyte_entry(i) + 0xe)
		if use < best:
			best = use
			k = i
	_local(LEYTE_LOCAL_RELEASE_BITMAPS, [])
	memory.write_u16(_g(-0x21a4), k)
	memory.write_u16(_g(LEYTE_CURRENT_ENTRY), k)
	memory.write_u32(_g(LEYTE_PANEL_BITMAP), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE, [1])))
	memory.write_u32(_g(-0x1270), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE_CLEAR, [1])))
	memory.write_u32(_g(-0x1256), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE_CLEAR, [1])))
	memory.write_u32(_g(-0x124e), C68kScript.u32(_jt(LEYTE_JT_NEW_HANDLE_CLEAR, [1])))
	best = -0x80000000
	for i in 4:
		best = maxi(best, memory.read_s32(_leyte_entry(i) + 0xe))
	var cur: int = memory.read_u16(_g(LEYTE_CURRENT_ENTRY))
	memory.write_u32(_leyte_entry(cur) + 0xe, C68kScript.u32(best + 1))
	_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(_g(LEYTE_PANEL_CACHE))])
	memory.write_u16(_leyte_entry(cur), memory.read_u16(_g(LEYTE_COLOUR_MODE)))
	memory.write_u16(_leyte_entry(cur) + 2, memory.read_u16(_g(-0x13fc)))
	memory.write_u16(_leyte_entry(cur) + 4, memory.read_u16(_g(-0x13fa)))
	memory.write_u16(_leyte_entry(cur) + 6, memory.read_u16(_g(-0x13f0)))
	memory.write_u16(_leyte_entry(cur) + 8, memory.read_u16(_g(-0x13ee)))
	var bits: int = C68kScript.u16(memory.read_s16(_g(-0x13f0)) * memory.read_s16(_g(LEYTE_COLOUR_MODE)) + 0xf)
	memory.write_u16(_leyte_entry(cur) + 0xa, C68kScript.u16((C68kScript.s16(bits) >> 4) * 2) | 0x8000)
	var rows: int = C68kScript.u32(C68kScript.s16(C68kScript.u16(memory.read_u16(r + 4) - memory.read_u16(r))))
	_jt(LEYTE_JT_SET_HANDLE_SIZE, [C68kScript.u32(rows * (memory.read_u16(_leyte_entry(cur) + 0xa) & 0x1fff)), memory.read_u32(_g(LEYTE_PANEL_BITMAP))])
	memory.write_u16(_g(-0x1322), memory.read_u16(_leyte_entry(cur) + 0xa) & 0x1fff)
	memory.write_u16(_g(-0x1320), _v(0x29e))
	_leyte_lend_panel_bitmap(a6)
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(0)])
	_trap(TRAP_PAINT_RECT, [memory.read_u32(_g(LEYTE_PANEL_PORT)) + 0x10])
	_trap(TRAP_PEN_SIZE, [1, 1])
	var frame: int = C68kScript.u32(_trap(TRAP_NEW_RGN, []))
	memory.write_u32(a6 - 0x12, frame)
	_trap(TRAP_OPEN_RGN, [])
	_jt(LEYTE_JT_VIEW_FRAME_OUTLINE, [])
	_trap(TRAP_CLOSE_RGN, [frame])
	var edge: int = C68kScript.u32(_trap(TRAP_NEW_RGN, []))
	memory.write_u32(a6 - 0x16, edge)
	_trap(TRAP_OPEN_RGN, [])
	_panel_edge(entry, a6)
	_trap(TRAP_CLOSE_RGN, [edge])
	_trap(TRAP_DIFF_RGN, [frame, edge, frame])
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(1)])
	_jt(JT_BACK_PANEL_COLOUR, [_panel_colour(0)])
	_trap(TRAP_FILL_RGN, [C68kScript.u32(_jt(JT_PANEL_PATTERN, [])), frame])
	_trap(TRAP_PAINT_RGN, [edge])
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(0)])
	_trap(TRAP_DISPOSE_RGN, [edge])
	_trap(TRAP_DISPOSE_RGN, [frame])
	_trap(TRAP_PEN_SIZE, [3, 3])
	_jt(LEYTE_JT_VIEW_FRAME_OUTLINE, [])
	var h: int = C68kScript.u16(_h(-0xc80) - 1)
	_trap(TRAP_MOVE_TO, [_v(0x4b0), h])
	_line_to(-0xc80, 0, 0x4b0, 0)
	_line_to(-0x424, 0, 0x29e, 0)
	_line_to(0x424, -4, 0x29e, 0)
	_line_to(0xc80, -4, 0x4b0, 0)
	_line_to(0xfa0, 0, 0x4b0, 0)
	a7 = entry - 4 - 0x30 - 0x10 - 4
	_corner_arc(a6, -0xa69, -0x857, -0xe, -0x5a)
	_line_to(-0x95a, 0, 0x960, 1)
	_corner_arc(a6, 0x857, 0xa69, 0xe, 0x5a)
	_line_to(0x95a, -3, 0x960, 1)
	_leyte_draw_panel_instruments(entry)
	var cur2: int = memory.read_u16(_g(LEYTE_CURRENT_ENTRY))
	_copy(_leyte_entry(cur2) + 0xb2, _g(-0x127c), 0x14)
	_copy(_leyte_entry(cur2) + 0xc6, _g(-0x1268), 0x22)
	for n in 0xa:
		_copy(_leyte_entry(cur2) + 0x10 * n + 0x12, _g(-0x131c + 0x10 * n), 0x10)
	for rec in LEYTE_PANEL_RECORDS:
		_copy(_leyte_entry(cur2) + rec[1], _g(rec[0]), rec[2])
	_trap(TRAP_SET_PORT, [memory.read_u32(a6 - 0x22)])
	_jt(LEYTE_JT_HUNLOCK, [memory.read_u32(_g(LEYTE_PANEL_BITMAP))])
	_trap(TRAP_DISPOSE_DIALOG, [memory.read_u32(a6 - 0x26)])
	store_panel_bitmaps_as_resources()
	a7 = entry - 4 - 0x30 - 4
	_local(LEYTE_LOCAL_REMAP_IDENTITY, [])
	a7 = entry

## The panel's instruments (Leyte's list: 1.0.3's with the split box at a = 0x4e2, 1.0.3's 0x474, and
## Leyte's six-cell box after it): the label size A5 - 0x219a (1.0.3 - 0x2114) at 40, 50 for instrument 9.
func _leyte_draw_panel_instruments(entry: int) -> void:
	memory.write_u16(_g(-0x219a), 0x28)
	a7 = entry - 4 - 0x30 - 4
	draw_panel_section_with_numbered_boxes()
	a7 = entry - 4 - 0x30 - 0xc - 4
	draw_percent_scale_gauge(0x14a, 0x258, 7, scale_design_480_min10(0xa), 0x794, 0x898)
	draw_split_display_box(0x17c, 0x17c, (7 << 16) | (scale_design_480_min10(0xa) & 0xffff), 0x4e2, -0x2a8)
	draw_six_cell_display_box(0x17c, 0x122, (7 << 16) | (scale_design_480_min10(0xa) & 0xffff), 0x37f, -0x2a8)
	draw_instrument(0, 0x30c, 0xa, scale_design_480_min10(0xc), 0x744, -0x67c)
	draw_instrument(1, 0x30c, 9, scale_design_480_min10(0xa), 0x744, -0x320)
	draw_instrument(2, 0x280, 7, scale_design_480_min10(0xa), 0x794, 0)
	draw_instrument(3, 0x2a8, 8, scale_design_480_min10(0xa), 0x780, 0x320)
	draw_instrument(4, 0x2a8, 7, scale_design_480_min10(0xa), 0x780, 0x67c)
	draw_instrument(5, 0x230, 7, scale_design_480_min10(0xa), 0x544, 0x898)
	draw_instrument(6, 0x21c, 7, scale_design_480_min10(0xa), 0x550, -0x898)
	a7 = entry - 4 - 0x30 - 0xa - 4
	draw_landing_gear_indicator(0x258, 7, scale_design_480_min10(0xa), 0x7a8, -0xb2c)
	draw_flaps_indicator(0x190, 7, scale_design_480_min10(0xa), 0x582, -0xb54)
	draw_fuel_gauge(0x258, 9, scale_design_480_min10(0xa), 0x7a8, 0xb2c)
	draw_ammo_counter(0x190, 7, scale_design_480_min10(0xa), 0x582, 0xb54)
	memory.write_u16(_g(-0x219a), 0x32)
	a7 = entry - 4 - 0x30 - 0xc - 4
	draw_instrument(9, 0x2d0, 0xa, scale_design_480_min10(0xa), 0x49c, 0x4ce)
	memory.write_u16(_g(-0x219a), 0x28)
	draw_instrument(8, 0x258, 9, scale_design_480_min10(0xa), 0x474, -0x4ce)
	a7 = entry

## The four session bitmaps' handles, in the order release_panel_bitmaps takes them (1.0.3: - 0x11f6,
## - 0x11dc, - 0x11d4, - 0x12b0).
const LEYTE_SESSION_BITMAPS: Array = [-0x1270, -0x1256, -0x124e, -0x132a]
const LEYTE_JT_DISPOSE_HANDLE: int = 0x26a     ## DisposHandle (Pascal; 1.0.3: $25a)
const TRAP_RELEASE_RESOURCE: int = 0xa9a3

## CODE 3 0x5be4 (1.0.3's release_panel_bitmaps 0x59f4, the runtime's id03_59f4 there; the same
## instructions with Leyte's operands): each of the four session handles still held is released: a
## resource (the current cache entry's word +0xc set: *(A5 - 0x1430) + 0x194 * A5 - 0x21a4) by
## ReleaseResource, otherwise by DisposHandle (JT $26a); each cleared.
func release_panel_bitmaps() -> void:
	for slot in LEYTE_SESSION_BITMAPS:
		var h: int = memory.read_u32(_g(slot))
		if h == 0:
			continue
		var record: int = C68kScript.u32(memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_CACHE)))
				+ memory.read_s16(_g(-0x21a4)) * LEYTE_PANEL_ENTRY_SIZE)
		if memory.read_u16(record + 0xc) != 0:
			_trap(TRAP_RELEASE_RESOURCE, [h])
		else:
			_jt(LEYTE_JT_DISPOSE_HANDLE, [h])
		memory.write_u32(_g(slot), 0)

## CODE 3 0x5ca6 (1.0.3's store_panel_bitmaps_as_resources 0x5ab6, the same instructions with Leyte's
## operands): the four panel bitmaps (handles A5 - 0x132a / - 0x1270 / - 0x1256 / - 0x124e, names
## A5 - 0xe48 ..) stored as 'HEL3' resources (entry A5 - 0x142c + 200 / 300 / 400 / 500): the old ones
## removed (resources not loaded meanwhile), the file updated (A5 - 0x1042), each added (ResError
## summed); the cache entry (*(A5 - 0x1430) + 0x194 * entry) +0xc set to 1 on success, else cleared
## and A5 - 0x151e set; the cache marked changed and the file updated.
func store_panel_bitmaps_as_resources() -> void:
	var current: int = memory.read_u16(_g(LEYTE_CURRENT_ENTRY))
	var errors: int = 0
	_trap(TRAP_SET_RES_LOAD, [0])
	for k in 4:
		var h: int = C68kScript.u32(_trap(TRAP_GET_RESOURCE, [C68kScript.u16(current + 0xc8 + 0x64 * k), LEYTE_PANEL_TYPE]))
		if h != 0:
			_trap(TRAP_RMVE_RESOURCE, [h])
	_trap(TRAP_SET_RES_LOAD, [1])
	_trap(TRAP_UPDATE_RES_FILE, [memory.read_u16(_g(-0x1042))])
	var handles: Array = [LEYTE_PANEL_BITMAP, -0x1270, -0x1256, -0x124e]
	for k in 4:
		_trap(TRAP_ADD_RESOURCE, [memory.read_u32(_g(-0xe48 + 4 * k)), C68kScript.u16(current + 0xc8 + 0x64 * k), LEYTE_PANEL_TYPE,
				memory.read_u32(_g(handles[k]))])
		errors = C68kScript.u16(errors + _trap(TRAP_RES_ERROR, []))
	var rec: int = C68kScript.u32(C68kScript.s16(current) * LEYTE_PANEL_ENTRY_SIZE + memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_CACHE))))
	if errors != 0:
		memory.write_u16(rec + 0xc, 0)
		memory.write_u16(_g(-0x151e), 1)
	else:
		memory.write_u16(rec + 0xc, 1)
	_trap(TRAP_CHANGED_RESOURCE, [memory.read_u32(_g(LEYTE_PANEL_CACHE))])
	_trap(TRAP_UPDATE_RES_FILE, [memory.read_u16(_g(-0x1042))])

## CODE 3 0x741e (1.0.3's remap_panel_colours_identity 0x71a2, with Leyte's operand): the cached panel
## recoloured (0x6e5e) through the identity table (256 words in the frame) and A5 - 0x13d2.
func remap_panel_colours_identity() -> void:
	assert(a7 != 0, "remap_panel_colours_identity needs a7 (its frame holds the table)")
	var table: int = a7 - 4 - 0x200
	for k in 0x100:
		memory.write_u16(table + 2 * k, k)
	var entry: int = a7
	a7 = entry - 4 - 0x200 - 4 - 8 - 4
	_local(LEYTE_LOCAL_REMAP_CACHED, [table, memory.read_u32(_g(-0x13d2))])
	a7 = entry

const LEYTE_JT_FLUSH_PANEL_1BIT: int = 0x49a   ## (C): the 1-bit panel's refresh (1.0.3: $482; the generated port keeps $482)

## CODE 3 0x6e5e (1.0.3's remap_cached_panel_colours 0x6c1c with Leyte's operands and two more colours):
## the current cache entry (*(A5 - 0x1430) + 0x194 * A5 - 0x142c) recoloured for the current palette
## when it was drawn at this depth and screen geometry (+0..+8 against A5 - 0x13f8, - 0x13fc, - 0x13fa,
## - 0x13f0, - 0x13ee): 1-bit panels are just refreshed (JT $49a); otherwise the panel slots' colours
## (`map` 0..12 at depth 4, 200..216 at depth 8; ID01's sparse switch) give an inverse map, every pixel
## of the panel bitmap (*(A5 - 0x132a), rowBytes from +0xa) and the gyro card (*(A5 - 0x1256), its size
## A5 - 0x125e / - 0x125c, rowBytes A5 - 0x1258) is put through it and `table`, and the cached fill
## patterns and colours are refreshed (A5 - 0x1274 / - 0x1272, then - 0x1228 .. - 0x1172 and - 0x131e).
## Leyte's two more: A5 - 0x11d0 (0, 0, 0) and A5 - 0x11ce (2, 2, 2), after A5 - 0x1204.
func remap_cached_panel_colours(map: int, table: int) -> void:
	assert(a7 != 0, "remap_cached_panel_colours needs a7 (its frame holds the inverse map)")
	var inv: int = a7 - 4 - 0x200
	var current: int = memory.read_s16(_g(LEYTE_CURRENT_ENTRY))
	if current < 0 or memory.read_u32(_g(LEYTE_PANEL_CACHE)) == 0:
		return
	var rec: int = C68kScript.u32(C68kScript.s16(current) * LEYTE_PANEL_ENTRY_SIZE + memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_CACHE))))
	for check in [[0, LEYTE_COLOUR_MODE], [2, -0x13fc], [4, -0x13fa], [6, -0x13f0], [8, -0x13ee]]:
		if memory.read_u16(rec + check[0]) != memory.read_u16(_g(check[1])):
			return
	var mode: int = memory.read_u16(_g(LEYTE_COLOUR_MODE))
	if mode == 1:
		_jt(LEYTE_JT_FLUSH_PANEL_1BIT, [])
		return
	for k in 0x100:
		memory.write_u16(inv + 2 * k, 0)
	var row_bytes: int = memory.read_u16(rec + 0xa) & 0x1fff
	if mode == 4 or mode == 8:
		var first: int = 0 if mode == 4 else 0xc8
		var last: int = 0xd if mode == 4 else 0xd9
		for k in range(first, last):
			var slot: int = memory.read_s16(C68kScript.u32(map + 2 * k))
			if slot >= 0 and slot <= 0xff:
				memory.write_u16(inv + 2 * slot, k)
		var nibbles: bool = mode == 4
		var panel: int = memory.read_u32(_g(LEYTE_PANEL_PORT))
		var rows: int = C68kScript.u16(memory.read_u16(panel + 0x14) - memory.read_u16(panel + 0x10))
		var width: int = C68kScript.u16(memory.read_u16(panel + 0x16) - memory.read_u16(panel + 0x12))
		_remap_bitmap(inv, table, memory.read_u32(memory.read_u32(_g(LEYTE_PANEL_BITMAP))), rows,
				C68kScript.s16(width) >> 1 if nibbles else width, row_bytes, nibbles)
		var gyro_width: int = memory.read_u16(_g(-0x125e))
		_remap_bitmap(inv, table, memory.read_u32(memory.read_u32(_g(-0x1256))), memory.read_u16(_g(-0x125c)),
				C68kScript.s16(gyro_width) >> 1 if nibbles else gyro_width, memory.read_u16(_g(-0x1258)) & 0x1fff, nibbles)
		for pair in [[-0x1274, 4, 0, 0], [-0x1272, 1, 1, 1]]:
			var c: int = _colour_via(table, pair[3], pair[2], pair[1])
			if nibbles:
				memory.write_u16(_g(pair[0]), C68kScript.u16((c << 12) | (c << 8) | (c << 4) | c))
			else:
				memory.write_u16(_g(pair[0]), C68kScript.u16((c << 8) | c))
	memory.write_u16(_g(-0x1228), _colour_via(table, 0, 2, 0))
	memory.write_u16(_g(-0x1226), _colour_via(table, 0, 0, 0))
	memory.write_u16(_g(-0x121c), _colour_via(table, 3, 3, 3))
	if mode == 4:
		memory.write_u16(_g(-0x121c), C68kScript.u16((memory.read_u16(_g(-0x121c)) << 4) | memory.read_u16(_g(-0x121c))))
	memory.write_u16(_g(-0x1206), _colour_via(table, 0, 0, 0))
	memory.write_u16(_g(-0x1202), _colour_via(table, 2, 2, 2))
	memory.write_u16(_g(-0x1204), _colour_via(table, 2, 2, 2))
	memory.write_u16(_g(-0x11d0), _colour_via(table, 0, 0, 0))
	memory.write_u16(_g(-0x11ce), _colour_via(table, 2, 2, 2))
	memory.write_u16(_g(-0x11c2), _colour_via(table, 2, 2, 2))
	memory.write_u16(_g(-0x11c4), _colour_via(table, 0, 0, 0))
	memory.write_u16(_g(-0x1172), _colour_via(table, 0, 0, 0))
	memory.write_u16(_g(-0x131e), _colour_via(table, 3, 3, 3))
	if mode == 4:
		memory.write_u16(_g(-0x131e), C68kScript.u16((memory.read_u16(_g(-0x131e)) << 4) | memory.read_u16(_g(-0x131e))))

## The three CODE 3 instrument drawers the generated port refuses only for globals its A5 map cannot
## pair: each is the second long of an 8-byte rect the code writes through LEA base(A5) and two
## MOVE.L (A0)+, so it is the base's Leyte pair + 4. The bodies are 1.0.3's port with every A5 offset
## through the pairs read from the two aligned listings (none conflicting); their named constants
## already hold Leyte's values in the generated port (and the numbered section's box list holds its rects'
## Leyte addresses, -0x1240 / -0x1238 / -0x1230). All three match Leyte's code on the emulator, with the
## A5 world's initialised data loaded and the offscreen port's calls stood in for
## (tools/leyte/gen_leyte_id03_reconstructed_cases.py drawer_cases).

## 0x4304 (1.0.3's at the same offset, the same instructions with Leyte's operands; A5 - 0x1224 the
## section rect, its second long - 0x1220 (1.0.3 - 0x11aa / - 0x11a6), - 0x121a / - 0x1218, - 0x1242,
## the boxes - 0x1240 / - 0x1238 / - 0x1230).
func draw_panel_section_with_numbered_boxes() -> void:
	assert(a7 != 0, "draw_panel_section_with_numbered_boxes needs a7 (its frame holds the rect)")
	var entry: int = a7
	var a6: int = entry - 4
	var rect: int = a6 - 0xa
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(0)])
	memory.write_u16(a6 - 8, _h(-0x190))
	memory.write_u16(rect, _v(0x2ee))
	memory.write_u16(a6 - 4, _h(0x190))
	memory.write_u16(a6 - 6, _v(0x618))
	_trap(TRAP_PAINT_RECT, [rect])
	var mode: int = memory.read_u16(_g(A5_COLOUR_MODE))
	if mode == 1 or mode == 4:
		_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(0)])
	else:
		_jt(JT_FORE_PANEL_COLOUR, [_jt(JT_PANEL_COLOUR, [0, 1, 0]) & 0xffff])
	_trap(TRAP_INSET_RECT, [2, 2, C68kScript.u32(rect)])
	_trap(TRAP_PAINT_RECT, [rect])
	memory.write_u32(_g(-0x1224), memory.read_u32(rect))
	memory.write_u32(_g(-0x1220), memory.read_u32(rect + 4))
	memory.write_u16(_g(-0x121a), _h(-0xa))
	memory.write_u16(_g(-0x1218), _v(0x47e))
	memory.write_u16(_g(-0x1242), memory.read_u16(_g(A5_COLOUR_MODE)))
	if memory.read_u16(_g(A5_COLOUR_MODE)) == 1:
		_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(2)])
	else:
		_jt(JT_FORE_PANEL_COLOUR, [_jt(JT_PANEL_COLOUR, [0, 2, 0]) & 0xffff])
	_trap(TRAP_PEN_SIZE, [1, 1])
	for lines in [[-0x1b, -1, 0], [-1, 0x19, 1]]:
		var i: int = lines[0]
		while i <= lines[1]:
			var x: int = _h(i * 0xa)
			_trap(TRAP_MOVE_TO, [C68kScript.u16(_v(0x2ee) + 2), C68kScript.u16(x + lines[2])])
			x = _h(i * 0xa)
			_trap(TRAP_LINE_TO, [C68kScript.u16(_v(0x618) - 3), C68kScript.u16(x + lines[2])])
			i += 0xd
	for lines in [[-0x1a, 0, 0], [0, 0x1a, 1]]:
		var i: int = lines[0]
		while i <= lines[1]:
			var x: int = _h(-0x190)
			_trap(TRAP_MOVE_TO, [C68kScript.u16(_v(i * 0xa + 0x47e) + lines[2]), C68kScript.u16(x + 2)])
			x = _h(0x190)
			_trap(TRAP_LINE_TO, [C68kScript.u16(_v(i * 0xa + 0x47e) + lines[2]), C68kScript.u16(x - 3)])
			i += 0xd
	var frame: Array = [_h(-0x1ae), _v(0x2d0), _h(-0x190), _v(0x2ee), _h(0x190), _v(0x618), _h(0x1ae), _v(0x636)]
	draw_bevelled_frame(frame[0], frame[1], frame[2], frame[3], frame[4], frame[5], frame[6], frame[7])
	for box in [[0x2d0, 0x348, 1, -0x1240], [0x384, 0x3fc, 3, -0x1238], [0x438, 0x4b0, 0xf, -0x1230]]:
		var left: int = _h(0x1ea)
		var top: int = _v(box[0])
		var right: int = _h(0x262)
		var bottom: int = _v(box[1])
		_trap(TRAP_SET_RECT, [bottom, right, top, left, rect])
		a7 = entry - 4 - 0x18 - 6 - 4
		draw_numbered_bevel_box(rect, box[2])
		a7 = entry
		memory.write_u32(_g(box[3]), memory.read_u32(rect))
		memory.write_u32(_g(box[3] + 4), memory.read_u32(rect + 4))

## 0x4790 (1.0.3's at the same offset, the same instructions with Leyte's operands; its rect kept
## at A5 - 0x11cc, the second long - 0x11c8 (1.0.3 - 0x1186 / - 0x1182)).
func draw_percent_scale_gauge(w: int, h: int, digits: int, pen: int, v: int, h0: int) -> void:
	assert(a7 != 0, "draw_percent_scale_gauge needs a7 (its frame is read beyond its arguments)")
	var entry: int = a7
	var a6: int = entry - 4
	var wide: int = C68kScript.u16(C68kScript.u16(C68kScript.s16(digits) * 3) * 6)
	var bar_span: int = C68kScript.u16(wide + 0xaa)
	var p: int = C68kScript.divs_trunc(C68kScript.s16(pen), 0xa) & 0xffff
	_trap(TRAP_PEN_SIZE, [p, p])
	var left: int = _jt(JT_ROUND_H, [C68kScript.u16(h0 - C68kScript.divs_trunc(C68kScript.s16(w), 2))]) & 0xffff
	var top: int = _jt(JT_ROUND_V, [C68kScript.u16(v - C68kScript.divs_trunc(C68kScript.s16(h), 2))]) & 0xffff
	var right: int = C68kScript.u16(scale_design_4800(w) + left)
	var bottom: int = C68kScript.u16(scale_design_4800(h) + top)
	memory.write_u16(a6 - 0xa, top)
	memory.write_u16(a6 - 8, left)
	memory.write_u16(a6 - 6, bottom)
	memory.write_u16(a6 - 4, right)
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(0)])
	_trap(TRAP_PAINT_RECT, [a6 - 0xa])
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(3 if memory.read_u16(_g(A5_COLOUR_MODE)) == 1 else 2)])
	_trap(TRAP_FRAME_RECT, [a6 - 0xa])
	var hc: int = C68kScript.s16(C68kScript.u16(h0 - 0x14))
	var half_bar: int = C68kScript.divs_trunc(C68kScript.s16(bar_span), 2)
	var bar_left: int = _jt(JT_ROUND_H, [C68kScript.u16(half_bar + hc - 0x3c)]) & 0xffff
	memory.write_u16(a6 - 0x10, bar_left)
	memory.write_u16(a6 - 0xc, C68kScript.u16(scale_design_4800(0x3c) + bar_left))
	var half_h: int = C68kScript.divs_trunc(C68kScript.s16(h), 2)
	memory.write_u16(a6 - 0x12, _jt(JT_ROUND_V, [C68kScript.u16(v - half_h + 0x1e)]) & 0xffff)
	memory.write_u16(a6 - 0xe, _jt(JT_ROUND_V, [C68kScript.u16(half_h + v - 0x1e)]) & 0xffff)
	_trap(TRAP_FRAME_RECT, [a6 - 0x12])
	var mark_left: int = C68kScript.u16(half_bar + hc - 0x8c)
	var mark_right: int = C68kScript.u16(mark_left + 0x32)
	var base: int = C68kScript.u16(half_h + v - 0x1e)
	var span: int = C68kScript.u16(memory.read_u16(a6 - 0xe) - memory.read_u16(a6 - 0x12) - 1)
	_trap(TRAP_PEN_SIZE, [p, p])
	var row: int = 0
	for i in 0xa:
		var start: int = mark_left if i == 0 or i == 9 else C68kScript.u16(mark_left + 0x1e)
		row = C68kScript.u16(_jt(JT_ROUND_V, [base]) - C68kScript.divs_trunc(C68kScript.s16(C68kScript.u16(C68kScript.s16(span) * i)), 9))
		_trap(TRAP_MOVE_TO, [C68kScript.u16(row - 1), _jt(JT_ROUND_H, [start]) & 0xffff])
		_trap(TRAP_LINE_TO, [C68kScript.u16(row - 1), _jt(JT_ROUND_H, [mark_right]) & 0xffff])
	var w3: int = C68kScript.u16(scale_design_4800(w) * 3)
	var h3: int = C68kScript.u16(scale_design_4800(h) * 3 + 4)
	a7 = entry - 4 - 0x2a - 4 - 4
	var port: int = new_offscreen_bw_port(C68kScript.u16(w3 + 4), h3)
	a7 = entry
	_trap(TRAP_SET_PORT, [port])
	_trap(TRAP_SET_ORIGIN, [0xfffe, 0xfffe])
	var size: int = C68kScript.u16(C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(C68kScript.u32(3 * memory.read_u32(_g(A5_DESIGN_SCALE)))), 0x1e0)) * C68kScript.s16(digits))
	var pen3: int = C68kScript.u16(C68kScript.s16(pen) * 3)
	var q: int = C68kScript.divs_trunc(C68kScript.s16(C68kScript.u16(pen3 * 2)), 0x1e) & 0xffff
	_trap(TRAP_PEN_SIZE, [q, q])
	var x: int = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s16(C68kScript.u16(C68kScript.s16(w) * 3)), 2)
			- C68kScript.divs_trunc(C68kScript.s16(C68kScript.u16(C68kScript.s16(bar_span) * 3)), 2) + 0x10e)
	draw_vector_number(0x64, size, x, 0xd2)
	draw_vector_number(0, size, x, C68kScript.u16(C68kScript.s16(h) * 3 - 0xd2))
	_trap(TRAP_SET_PORT, [memory.read_u32(_g(A5_PANEL_PORT))])
	a7 = entry - 4 - 0x2a - 0x10 - 4
	antialias_downsample_bw_3x(port, left, top, C68kScript.u16(bottom - top), C68kScript.u16(right - left), 0)
	a7 = entry
	dispose_offscreen_bw_port(port)
	_trap(TRAP_INSET_RECT, [1, 1, C68kScript.u32(a6 - 0x12)])
	memory.write_u32(_g(-0x11cc), memory.read_u32(a6 - 0x12))
	memory.write_u32(_g(-0x11c8), memory.read_u32(a6 - 0xe))

## 0x4b32 (1.0.3's at the same offset, the same instructions with Leyte's operands; its two rects
## at A5 - 0x1216 / - 0x1212 and - 0x120e / - 0x120a (1.0.3 - 0x119c / - 0x1198, - 0x1194 / - 0x1190)).
func draw_split_display_box(width: int, height: int, _unused: int, a: int, b: int) -> void:
	assert(a7 != 0, "draw_split_display_box needs a7 (its frame holds the rect)")
	var a6: int = a7 - 4
	var rect: int = a6 - 0xa
	_trap(TRAP_PEN_SIZE, [1, 1])
	memory.write_u16(a6 - 8, _jt(JT_ROUND_H, [C68kScript.u16(b - C68kScript.divs_trunc(C68kScript.s16(width), 2))]) & 0xffff)
	memory.write_u16(rect, _jt(JT_ROUND_V, [C68kScript.u16(a - C68kScript.divs_trunc(C68kScript.s16(height), 2))]) & 0xffff)
	memory.write_u16(a6 - 4, C68kScript.u16(scale_design_4800(width) + memory.read_u16(a6 - 8)))
	memory.write_u16(a6 - 6, C68kScript.u16(scale_design_4800(height) + memory.read_u16(rect)))
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(0)])
	_trap(TRAP_PAINT_RECT, [rect])
	_jt(JT_FORE_PANEL_COLOUR, [_panel_colour(2)])
	_trap(TRAP_FRAME_RECT, [rect])
	_trap(TRAP_INSET_RECT, [3, 3, C68kScript.u32(rect)])
	var span: int = C68kScript.u16(memory.read_u16(a6 - 4) - memory.read_u16(a6 - 8))
	memory.write_u16(a6 - 0x20, span)
	memory.write_u16(a6 - 4, C68kScript.u16(C68kScript.divs_trunc(C68kScript.s16(span), 2) + memory.read_u16(a6 - 8) - 1))
	memory.write_u32(_g(-0x1216), memory.read_u32(rect))
	memory.write_u32(_g(-0x1212), memory.read_u32(rect + 4))
	memory.write_u16(a6 - 4, C68kScript.u16(memory.read_u16(a6 - 8) + span))
	memory.write_u16(a6 - 8, C68kScript.u16(memory.read_u16(a6 - 4) - C68kScript.divs_trunc(C68kScript.s16(span), 2) + 1))
	memory.write_u32(_g(-0x120e), memory.read_u32(rect))
	memory.write_u32(_g(-0x120a), memory.read_u32(rect + 4))

## CODE 3 0x4c42 (Leyte's own: 1.0.3 has no such instrument; named here for what it draws). A display
## box of design size w x h centred at (a, b) (C: w, h, an unused long, a, b), as draw_split_display_box
## places one: its rect (frame -0x10: top, left, bottom, right) from round_h(b - w / 2) and
## round_v(a - h / 2) (JT $3a2 / $372) and scale_design_4800 (0x8a) of w and h; painted in colour 0,
## framed in colour 2 (JT $4b2 then $3b2), inset by 3. Its inner width (-0x2e) gives a cell step (half
## of it / 3, at -4) and a cell height (its height - 8, at -6); a step of 6 or more insets the rect by 1
## horizontally, leaves a gap of 3 between cells (-8) and narrows the width by one, else the gap is 2.
## Six cells are laid out, 4 units lower each: three leftward from the middle (into A5 - 0x1200 + 8 *
## (2 - i)) and three rightward (into A5 - 0x1200 + 8 * (3 + i)), each kept as top, left, bottom, right.
func draw_six_cell_display_box(w: int, h: int, _unused: int, a: int, b: int) -> void:
	assert(a7 != 0, "draw_six_cell_display_box needs a7 (its frame holds the rects)")
	var a6: int = a7 - 4
	_trap(TRAP_PEN_SIZE, [1, 1])
	memory.write_u16(a6 - 0xe, C68kScript.u16(_jt(JT_ROUND_H, [C68kScript.u16(b - C68kScript.divs_trunc(C68kScript.s16(w), 2))])))
	memory.write_u16(a6 - 0x10, C68kScript.u16(_jt(JT_ROUND_V, [C68kScript.u16(a - C68kScript.divs_trunc(C68kScript.s16(h), 2))])))
	memory.write_u16(a6 - 0xa, C68kScript.u16(scale_design_4800(C68kScript.s16(w)) + memory.read_u16(a6 - 0xe)))
	memory.write_u16(a6 - 0xc, C68kScript.u16(scale_design_4800(C68kScript.s16(h)) + memory.read_u16(a6 - 0x10)))
	_jt(JT_FORE_PANEL_COLOUR, [_jt(JT_PANEL_COLOUR, [0, 0, 0]) & 0xffff])
	_trap(TRAP_PAINT_RECT, [a6 - 0x10])
	_jt(JT_FORE_PANEL_COLOUR, [_jt(JT_PANEL_COLOUR, [2, 2, 2]) & 0xffff])
	_trap(TRAP_FRAME_RECT, [a6 - 0x10])
	_trap(TRAP_INSET_RECT, [3, 3, a6 - 0x10])
	memory.write_u16(a6 - 0x2e, C68kScript.u16(memory.read_u16(a6 - 0xa) - memory.read_u16(a6 - 0xe)))
	memory.write_u16(a6 - 4, C68kScript.u16(C68kScript.divs_trunc(C68kScript.s16(C68kScript.u16(C68kScript.divs_trunc(memory.read_s16(a6 - 0x2e), 2))), 3)))
	memory.write_u16(a6 - 6, C68kScript.u16(memory.read_u16(a6 - 0xc) - memory.read_u16(a6 - 0x10) - 8))
	if memory.read_s16(a6 - 4) >= 6:
		_trap(TRAP_INSET_RECT, [0, 1, a6 - 0x10])
		memory.write_u16(a6 - 8, 3)
		memory.write_u16(a6 - 0x2e, C68kScript.u16(memory.read_u16(a6 - 0x2e) - 1))
	else:
		memory.write_u16(a6 - 8, 2)
	var half: int = C68kScript.divs_trunc(memory.read_s16(a6 - 0x2e), 2)
	for i in 3:
		var left: int = C68kScript.u16(half + memory.read_u16(a6 - 0xe) - (i + 1) * memory.read_s16(a6 - 4))
		_six_cell(a6, i, left, 2 - i)
	for i in 3:
		var left: int = C68kScript.u16(half + memory.read_u16(a6 - 0xe) + memory.read_s16(a6 - 4) * i + memory.read_u16(a6 - 8))
		_six_cell(a6, i, left, 3 + i)

## One cell of draw_six_cell_display_box: left as given, right = left + step - gap, top = 4 i below the
## rect's top, bottom = top + the cell height; kept (8 bytes, from the frame's -0x20) at A5 - 0x1200 + 8 slot.
func _six_cell(a6: int, i: int, left: int, slot: int) -> void:
	memory.write_u16(a6 - 2, i)
	memory.write_u16(a6 - 0x1e, left)
	memory.write_u16(a6 - 0x1a, C68kScript.u16(left + memory.read_u16(a6 - 4) - memory.read_u16(a6 - 8)))
	memory.write_u16(a6 - 0x20, C68kScript.u16((i << 2) + memory.read_u16(a6 - 0x10)))
	memory.write_u16(a6 - 0x1c, C68kScript.u16(memory.read_u16(a6 - 0x20) + memory.read_u16(a6 - 6)))
	_copy(_g(-0x1200 + 8 * slot), a6 - 0x20, 8)
