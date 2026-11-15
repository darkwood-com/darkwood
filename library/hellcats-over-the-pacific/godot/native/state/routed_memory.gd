extends RefCounted

## The memory as the Native overlay sees it (the routed port, routed_port.gd, and the hand-written
## Native functions): the Mac memory, except the Native blocks (native_block.gd: camera angles,
## camera position, view state) and the Godot-owned heap records (native_records.gd), whose
## accesses go to Godot. A block access must be exactly one field, a record access must lie inside
## the record (else it fails); every other access is the Mac memory's, unchanged.

const C68kScript := preload("res://original/compat/c68k.gd")
const ScreenMemory := preload("res://native/display/screen_memory.gd")

## The route of each 32-byte page (native_records' PAGE_SHIFT) of the 24-bit address space, the one
## table the fast path reads: MAC the Mac memory's bytes (decoded here: the memory starts at 0),
## RECORD wholly one record's bytes (`space`), SCREEN the Godot-owned screen's (native/display/
## screen_memory.gd, when installed), CHECKED otherwise (a typed block, a record's edge or hole, two
## records, a Mac memory not at 0): the checked path, _read / _write.
const MAC := 0
const RECORD := 1
const SCREEN := 2
const CHECKED := -1

var memory
var blocks: Array
var records       ## native_records.gd, or null
## The records' bytes at their addresses (native_records.gd keeps which ranges are records); sized on
## the first record store.
var space := PackedByteArray()
var route := PackedInt32Array()
## Pages a typed block touches: always CHECKED.
var _block_pages := PackedByteArray()
var _owner = null                 ## the Native port, set up from on first use (its storage switches)
var _mac_direct := false          ## the Mac memory starts at 0: its bytes decoded here (same values)
var _any                          ## the memory the port was given (the screen memory when installed)
var _screen = null                ## the screen memory (screen_memory.gd), or null
var _screen_lo: int = 0

## Built by the port's constructor; set up on the first access, once the port's storage switches
## (record / block storage on or off) are final.
func _init(owner_) -> void:
	_owner = owner_

func _setup() -> void:
	var owner_ = _owner
	_owner = null
	memory = owner_.memory
	if memory.get("store") == null and ScreenMemory.over(memory) != null:
		memory = ScreenMemory.over(memory)   # a port built after the install (the overlay's) gets the Mac memory
	_any = memory
	if memory.get("store") != null:          # the screen memory: the Mac memory behind it, its store
		_screen = memory
		_screen_lo = _screen.lo
		memory = _screen.mac
	blocks = [owner_.camera_angles(), owner_.camera_position(), owner_.view_state()]
	_mac_direct = memory.get("_base_address") == 0
	_block_pages.resize(1 << 19)
	for b in blocks:
		for page in range(b.start >> 5, ((b.end - 1) >> 5) + 1):
			_block_pages[page] = 1
	route.resize(1 << 19)
	records = owner_.records()
	pages_changed(0, route.size() - 1)

## native_records.gd: the record store, on its creation.
func ensure_space() -> void:
	if space.is_empty():
		space.resize(1 << 24)

## native_records.gd: the pages first..last changed (a record created, grown, released, a hole);
## their routes recomposed. Before the set-up, _setup composes them all.
func pages_changed(first: int, last: int) -> void:
	if route.is_empty():
		return
	var fast: PackedInt32Array = records.fast if records != null else PackedInt32Array()
	for page in range(first, last + 1):
		if _block_pages[page] != 0:
			route[page] = CHECKED
			continue
		if _screen != null and (page << 5) >= _screen.lo and (page << 5) < _screen.hi:
			route[page] = SCREEN
			continue
		var f: int = fast[page] if not fast.is_empty() else 0
		if f > 0:
			route[page] = RECORD
		elif f == 0 and _mac_direct:
			route[page] = MAC
		else:
			route[page] = CHECKED

func _block(address: int, width: int):
	for b in blocks:
		if b.overlaps(address, width):
			return b
	return null

# The checked path: a typed block (one field), a record (wholly inside it), else the Mac memory.

func _read(address: int, width: int) -> int:
	var b = _block(address, width)
	if b != null:
		assert(width != 1, "byte access into a Native block")
		return b.read_unsigned(address, width)
	if records != null and records.find(address, width) >= 0:
		var at: int = address & 0xffffff
		var v: int = 0
		for i in width:
			v = (v << 8) | space[at + i]
		return v
	match width:
		1: return _any.read_u8(address)
		2: return _any.read_u16(address)
	return _any.read_u32(address)

func _write(address: int, width: int, v: int) -> void:
	var b = _block(address, width)
	if b != null:
		assert(width != 1, "byte access into a Native block")
		b.write(address, width, v)
		return
	if records != null and records.find(address, width) >= 0:
		var at: int = address & 0xffffff
		for i in width:
			space[at + i] = (v >> (8 * (width - 1 - i))) & 0xff
		return
	match width:
		1: _any.write_u8(address, v)
		2: _any.write_u16(address, v)
		_: _any.write_u32(address, v)

# Entry points: the fast path inline (an access within one page whose route is MAC or RECORD), the
# checked path otherwise. Same bytes either way.

func read_u8(address: int) -> int:
	if _owner != null: _setup()
	var at: int = address & 0xffffff
	var r: int = route[at >> 5]
	if r == MAC: return memory._bytes[at]
	if r == RECORD: return space[at]
	if r == SCREEN: return _screen.store[at - _screen_lo]
	return _read(address, 1)

func read_s8(address: int) -> int:
	return C68kScript.s8(read_u8(address))

func read_u16(address: int) -> int:
	if _owner != null: _setup()
	var at: int = address & 0xffffff
	var page: int = at >> 5
	if page == ((at + 1) >> 5):
		var r: int = route[page]
		if r == MAC:
			var mb: PackedByteArray = memory._bytes
			return (mb[at] << 8) | mb[at + 1]
		if r == RECORD:
			return (space[at] << 8) | space[at + 1]
		if r == SCREEN:
			var o: int = at - _screen_lo
			return (_screen.store[o] << 8) | _screen.store[o + 1]
	return _read(address, 2)

func read_s16(address: int) -> int:
	return C68kScript.s16(read_u16(address))

func read_u32(address: int) -> int:
	if _owner != null: _setup()
	var at: int = address & 0xffffff
	var page: int = at >> 5
	if page == ((at + 3) >> 5):
		var r: int = route[page]
		if r == MAC:
			var mb: PackedByteArray = memory._bytes
			return (mb[at] << 24) | (mb[at + 1] << 16) | (mb[at + 2] << 8) | mb[at + 3]
		if r == RECORD:
			return (space[at] << 24) | (space[at + 1] << 16) | (space[at + 2] << 8) | space[at + 3]
		if r == SCREEN:
			var o: int = at - _screen_lo
			return (_screen.store[o] << 24) | (_screen.store[o + 1] << 16) | (_screen.store[o + 2] << 8) | _screen.store[o + 3]
	return _read(address, 4)

func read_s32(address: int) -> int:
	return C68kScript.s32(read_u32(address))

func write_u8(address: int, v: int) -> void:
	if _owner != null: _setup()
	var at: int = address & 0xffffff
	var r: int = route[at >> 5]
	if r == MAC:
		memory._bytes[at] = v & 0xff
		return
	if r == RECORD:
		space[at] = v & 0xff
		return
	if r == SCREEN:
		_screen.store[at - _screen_lo] = v & 0xff
		return
	_write(address, 1, C68kScript.u8(v))

func write_u16(address: int, v: int) -> void:
	if _owner != null: _setup()
	var at: int = address & 0xffffff
	var page: int = at >> 5
	if page == ((at + 1) >> 5):
		var r: int = route[page]
		if r == MAC:
			memory._bytes[at] = (v >> 8) & 0xff
			memory._bytes[at + 1] = v & 0xff
			return
		if r == RECORD:
			space[at] = (v >> 8) & 0xff
			space[at + 1] = v & 0xff
			return
		if r == SCREEN:
			var o: int = at - _screen_lo
			_screen.store[o] = (v >> 8) & 0xff
			_screen.store[o + 1] = v & 0xff
			return
	_write(address, 2, C68kScript.u16(v))

func write_u32(address: int, v: int) -> void:
	if _owner != null: _setup()
	var at: int = address & 0xffffff
	var page: int = at >> 5
	if page == ((at + 3) >> 5):
		var r: int = route[page]
		if r == MAC:
			memory._bytes[at] = (v >> 24) & 0xff
			memory._bytes[at + 1] = (v >> 16) & 0xff
			memory._bytes[at + 2] = (v >> 8) & 0xff
			memory._bytes[at + 3] = v & 0xff
			return
		if r == RECORD:
			space[at] = (v >> 24) & 0xff
			space[at + 1] = (v >> 16) & 0xff
			space[at + 2] = (v >> 8) & 0xff
			space[at + 3] = v & 0xff
			return
		if r == SCREEN:
			var o: int = at - _screen_lo
			_screen.store[o] = (v >> 24) & 0xff
			_screen.store[o + 1] = (v >> 16) & 0xff
			_screen.store[o + 2] = (v >> 8) & 0xff
			_screen.store[o + 3] = v & 0xff
			return
	_write(address, 4, C68kScript.u32(v))
