extends RefCounted

## Godot-owned records of the overlay's memory: ranges whose bytes live here, not in the Mac memory.
## Two kinds (PROJECT_STATUS, loop 7):
## - the overlay's globals (data and bss), one record created at the first overlay entry;
## - the heap arena, one record growing with heap_sbrk's break: block headers, free blocks and
##   payloads alike (the allocator runs routed, so its metadata is Godot's too). The handles the
##   program holds are still the addresses the allocator assigns, as in the Original.
## A hole is a range of a record that lives in the Mac memory instead (a block shared with the
## emulated Toolbox): opening it moves its bytes out to the Mac memory, closing it moves them back.
## Records keep the original layout (big-endian, the original widths); the router reads and writes
## them by address. An access straddling a record's edge or a hole's fails.

const PAGE_SHIFT := 5

class Record:
	var bytes: PackedByteArray

var memory
var _records := {}     ## handle -> Record (an object: a PackedByteArray taken out of a Dictionary is a copy)
var _pages := {}       ## address >> PAGE_SHIFT -> [handle, ...]
var _holes := {}       ## start -> end: Mac-memory ranges inside records

func _init(memory_) -> void:
	memory = memory_

func create(handle: int, size: int) -> void:
	handle &= 0xffffff
	assert(not _records.has(handle), "record 0x%x already exists" % handle)
	var r := Record.new()
	r.bytes.resize(size)
	_records[handle] = r
	for page in range(handle >> PAGE_SHIFT, ((handle + size - 1) >> PAGE_SHIFT) + 1):
		if not _pages.has(page):
			_pages[page] = []
		_pages[page].append(handle)

## A record taking over a block's payload with its current Mac bytes (heap_alloc returns the payload
## as the allocator leaves it; the bytes are the program's from then on, in Godot).
func create_from_memory(handle: int, size: int) -> void:
	create(handle, size)
	var r: Record = _records[handle & 0xffffff]
	for i in size:
		r.bytes[i] = memory.read_u8(handle + i)

func has(handle: int) -> bool:
	return _records.has(handle & 0xffffff)

## Release: the bytes back into the Mac block, the record gone.
func release(handle: int) -> void:
	handle &= 0xffffff
	var r: Record = _records[handle]
	for i in r.bytes.size():
		memory.write_u8(handle + i, r.bytes[i])
	for page in range(handle >> PAGE_SHIFT, ((handle + r.bytes.size() - 1) >> PAGE_SHIFT) + 1):
		_pages[page].erase(handle)
		if _pages[page].is_empty():
			_pages.erase(page)
	_records.erase(handle)

## Extend a record to new_size: the added bytes are taken from the Mac memory (the arena grows as the
## break advances over memory the program has not used yet).
func grow(handle: int, new_size: int) -> void:
	handle &= 0xffffff
	var r: Record = _records[handle]
	var old_size: int = r.bytes.size()
	if new_size <= old_size:
		return
	for i in range(old_size, new_size):
		r.bytes.append(memory.read_u8(handle + i))
	for page in range((handle + old_size) >> PAGE_SHIFT, ((handle + new_size - 1) >> PAGE_SHIFT) + 1):
		if not _pages.has(page):
			_pages[page] = []
		if not _pages[page].has(handle):
			_pages[page].append(handle)

## A hole: [start, end) of a record moves to the Mac memory (its bytes written there).
func open_hole(start: int, end: int) -> void:
	start &= 0xffffff
	end &= 0xffffff
	var h: int = find(start, 1)
	assert(h >= 0 and not _holes.has(start), "hole 0x%x: no record or already open" % start)
	var r: Record = _records[h]
	for at in range(start, end):
		memory.write_u8(at, r.bytes[at - h])
	_holes[start] = end

func is_hole(start: int) -> bool:
	return _holes.has(start & 0xffffff)

## Close a hole: its Mac bytes move back into the record.
func close_hole(start: int) -> void:
	start &= 0xffffff
	var end: int = _holes[start]
	_holes.erase(start)
	var h: int = find(start, 1)
	var r: Record = _records[h]
	for at in range(start, end):
		r.bytes[at - h] = memory.read_u8(at)

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
		var size: int = _records[h].bytes.size()
		if at < h + size and at + width > h:
			assert(at >= h and at + width <= h + size, "access 0x%x width %d straddles record 0x%x" % [address, width, h])
			return h
	return -1

func read(handle: int, address: int, width: int) -> int:
	var r: Record = _records[handle]
	var at: int = (address & 0xffffff) - handle
	var v: int = 0
	for i in width:
		v = (v << 8) | r.bytes[at + i]
	return v

func write(handle: int, address: int, width: int, v: int) -> void:
	var r: Record = _records[handle]
	var at: int = (address & 0xffffff) - handle
	for i in width:
		r.bytes[at + i] = (v >> (8 * (width - 1 - i))) & 0xff

## The host's overlay reset (reset_overlay_state, before every overlay entry) memsets the block
## that holds the heap arena (runtime watch, loop 7): the records are dropped, not written back.
func drop_all(keep: int = -1) -> void:
	_holes.clear()
	for h in _records.keys():
		if h == keep:
			continue
		var r: Record = _records[h]
		for page in range(h >> PAGE_SHIFT, ((h + r.bytes.size() - 1) >> PAGE_SHIFT) + 1):
			_pages[page].erase(h)
			if _pages[page].is_empty():
				_pages.erase(page)
		_records.erase(h)

## Zero part of a record (the host's bss memset, reproduced on the overlay globals record).
func zero(handle: int, from: int, to: int) -> void:
	var r: Record = _records[handle & 0xffffff]
	for i in range(from - handle, to - handle):
		r.bytes[i] = 0

## Every live record as the Mac memory would hold it: [[handle, bytes]] (traces, tests).
func memory_image() -> Array:
	var out: Array = []
	for h in _records:
		var bytes: PackedByteArray = _records[h].bytes
		if not _holes.is_empty():
			bytes = bytes.duplicate()
			for hs in _holes:
				if hs >= h and hs < h + bytes.size():
					for at in range(hs, _holes[hs]):
						bytes[at - h] = memory.read_u8(at)
		out.append([h, bytes])
	return out

func count() -> int:
	return _records.size()
