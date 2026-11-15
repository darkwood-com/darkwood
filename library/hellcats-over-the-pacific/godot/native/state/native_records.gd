extends RefCounted

## Godot-owned records of the overlay's memory: ranges whose bytes live here, not in the Mac memory.
## Two kinds (PROJECT_STATUS, loop 7):
## - the overlay's globals (data and bss), one record created at the first overlay entry;
## - the heap arena, one record growing with heap_sbrk's break: block headers, free blocks and
##   payloads alike (the allocator runs routed, so its metadata is Godot's too). The handles the
##   program holds are still the addresses the allocator assigns, as in the Original.
## A hole is a range of a record that lives in the Mac memory instead (a block shared with the
## emulated Toolbox): opening it moves its bytes out to the Mac memory, closing it moves them back.
## Records keep the original layout (big-endian, the original widths) in one byte array addressed
## like the Mac memory, the router's (routed_memory.gd `space`: a record's byte at address a is
## space[a]), so the router reads and writes them by address with no lookup; this file keeps which
## ranges are records and tells the router which pages changed. An access straddling a record's edge
## or a hole's fails. Inside a hole, and outside every record, `space` holds nothing meaningful.

const PAGE_SHIFT := 5

var memory
var _store             ## the router (routed_memory.gd): owns `space`, the records' bytes
var _records := {}     ## handle -> size in bytes
var _pages := {}       ## address >> PAGE_SHIFT -> [handle, ...]
var _holes := {}       ## start -> end: Mac-memory ranges inside records
## The router's fast path, one entry per page of the 24-bit address space: 0 no record on the page,
## handle + 1 when one record covers the whole page and no hole touches it, -1 otherwise (the checked
## path: a record's edge, two records, a hole).
var fast := PackedInt32Array()

func _init(memory_, store) -> void:
	memory = memory_
	_store = store
	fast.resize(1 << (24 - PAGE_SHIFT))
	_store.ensure_space()

func _refresh(first_page: int, last_page: int) -> void:
	for page in range(first_page, last_page + 1):
		var list = _pages.get(page)
		if list == null or list.is_empty():
			fast[page] = 0
			continue
		if list.size() != 1:
			fast[page] = -1
			continue
		var h: int = list[0]
		var lo: int = page << PAGE_SHIFT
		var hi: int = lo + (1 << PAGE_SHIFT)
		var covered: bool = h <= lo and hi <= h + _records[h]
		if covered:
			for hs in _holes:
				if hs < hi and _holes[hs] > lo:
					covered = false
					break
		fast[page] = h + 1 if covered else -1
	_store.pages_changed(first_page, last_page)

func create(handle: int, size: int) -> void:
	handle &= 0xffffff
	assert(not _records.has(handle), "record 0x%x already exists" % handle)
	_records[handle] = size
	for i in range(handle, handle + size):
		_store.space[i] = 0
	for page in range(handle >> PAGE_SHIFT, ((handle + size - 1) >> PAGE_SHIFT) + 1):
		if not _pages.has(page):
			_pages[page] = []
		_pages[page].append(handle)
	_refresh(handle >> PAGE_SHIFT, (handle + size - 1) >> PAGE_SHIFT)

## A record taking over a block's payload with its current Mac bytes (heap_alloc returns the payload
## as the allocator leaves it; the bytes are the program's from then on, in Godot).
func create_from_memory(handle: int, size: int) -> void:
	create(handle, size)
	handle &= 0xffffff
	for i in size:
		_store.space[handle + i] = memory.read_u8(handle + i)

func has(handle: int) -> bool:
	return _records.has(handle & 0xffffff)

## Release: the bytes back into the Mac block, the record gone.
func release(handle: int) -> void:
	handle &= 0xffffff
	var size: int = _records[handle]
	for i in size:
		memory.write_u8(handle + i, _store.space[handle + i])
	for page in range(handle >> PAGE_SHIFT, ((handle + size - 1) >> PAGE_SHIFT) + 1):
		_pages[page].erase(handle)
		if _pages[page].is_empty():
			_pages.erase(page)
	_records.erase(handle)
	_refresh(handle >> PAGE_SHIFT, (handle + size - 1) >> PAGE_SHIFT)

## Extend a record to new_size: the added bytes are taken from the Mac memory (the arena grows as the
## break advances over memory the program has not used yet).
func grow(handle: int, new_size: int) -> void:
	handle &= 0xffffff
	var old_size: int = _records[handle]
	if new_size <= old_size:
		return
	for i in range(old_size, new_size):
		_store.space[handle + i] = memory.read_u8(handle + i)
	_records[handle] = new_size
	for page in range((handle + old_size) >> PAGE_SHIFT, ((handle + new_size - 1) >> PAGE_SHIFT) + 1):
		if not _pages.has(page):
			_pages[page] = []
		if not _pages[page].has(handle):
			_pages[page].append(handle)
	_refresh((handle + old_size - 1) >> PAGE_SHIFT, (handle + new_size - 1) >> PAGE_SHIFT)

## A hole: [start, end) of a record moves to the Mac memory (its bytes written there).
func open_hole(start: int, end: int) -> void:
	start &= 0xffffff
	end &= 0xffffff
	var h: int = find(start, 1)
	assert(h >= 0 and not _holes.has(start), "hole 0x%x: no record or already open" % start)
	for at in range(start, end):
		memory.write_u8(at, _store.space[at])
	_holes[start] = end
	_refresh(start >> PAGE_SHIFT, (end - 1) >> PAGE_SHIFT)

func is_hole(start: int) -> bool:
	return _holes.has(start & 0xffffff)

## Close a hole: its Mac bytes move back into the record.
func close_hole(start: int) -> void:
	start &= 0xffffff
	var end: int = _holes[start]
	_holes.erase(start)
	for at in range(start, end):
		_store.space[at] = memory.read_u8(at)
	_refresh(start >> PAGE_SHIFT, (end - 1) >> PAGE_SHIFT)

func _in_hole(at: int, width: int) -> bool:
	for hs in _holes:
		if at < _holes[hs] and at + width > hs:
			assert(at >= hs and at + width <= _holes[hs], "access 0x%x width %d straddles hole 0x%x" % [at, width, hs])
			return true
	return false

## The record holding [address, address + width), or -1; one straddling an edge fails.
func find(address: int, width: int) -> int:
	var at: int = address & 0xffffff
	if not _holes.is_empty() and _in_hole(at, width):
		return -1
	var list = _pages.get(at >> PAGE_SHIFT)
	if list == null:
		list = _pages.get((at + width - 1) >> PAGE_SHIFT)
		if list == null:
			return -1
	for h in list:
		var size: int = _records[h]
		if at < h + size and at + width > h:
			assert(at >= h and at + width <= h + size, "access 0x%x width %d straddles record 0x%x" % [address, width, h])
			return h
	return -1

## A record's bytes by address (the handle from find(): the access lies inside that record).
func read(_handle: int, address: int, width: int) -> int:
	var at: int = address & 0xffffff
	var v: int = 0
	for i in width:
		v = (v << 8) | _store.space[at + i]
	return v

func write(_handle: int, address: int, width: int, v: int) -> void:
	var at: int = address & 0xffffff
	for i in width:
		_store.space[at + i] = (v >> (8 * (width - 1 - i))) & 0xff

## The host's overlay reset (reset_overlay_state, before every overlay entry) memsets the block
## that holds the heap arena (runtime watch, loop 7): the records are dropped, not written back.
func drop_all(keep: int = -1) -> void:
	_holes.clear()
	for h in _records.keys():
		if h == keep:
			continue
		for page in range(h >> PAGE_SHIFT, ((h + _records[h] - 1) >> PAGE_SHIFT) + 1):
			_pages[page].erase(h)
			if _pages[page].is_empty():
				_pages.erase(page)
		_records.erase(h)
	fast.fill(0)
	_store.pages_changed(0, fast.size() - 1)
	for h in _records:
		_refresh(h >> PAGE_SHIFT, (h + _records[h] - 1) >> PAGE_SHIFT)

## Zero part of a record (the host's bss memset, reproduced on the overlay globals record).
func zero(handle: int, from: int, to: int) -> void:
	for i in range(from & 0xffffff, to & 0xffffff):
		_store.space[i] = 0

## Every live record as the Mac memory would hold it: [[handle, bytes]] (traces, tests).
func memory_image() -> Array:
	var out: Array = []
	for h in _records:
		var bytes: PackedByteArray = _store.space.slice(h, h + _records[h])
		if not _holes.is_empty():
			for hs in _holes:
				if hs >= h and hs < h + bytes.size():
					for at in range(hs, _holes[hs]):
						bytes[at - h] = memory.read_u8(at)
		out.append([h, bytes])
	return out

func count() -> int:
	return _records.size()
