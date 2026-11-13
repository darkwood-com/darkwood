extends RefCounted

## The memory as the Native overlay sees it (the routed port, routed_port.gd, and the hand-written
## Native functions): the Mac memory, except the Native blocks (native_block.gd: camera angles,
## camera position, view state) and the Godot-owned heap records (native_records.gd), whose
## accesses go to Godot. A block access must be exactly one field, a record access must lie inside
## the record (else it fails); every other access is the Mac memory's, unchanged.

const C68kScript := preload("res://original/compat/c68k.gd")

var memory
var blocks: Array
var records       ## native_records.gd, or null

func _init(memory_, blocks_: Array, records_ = null) -> void:
	memory = memory_
	blocks = blocks_
	records = records_

func _block(address: int, width: int):
	for b in blocks:
		if b.overlaps(address, width):
			return b
	return null

func _read(address: int, width: int) -> int:
	var b = _block(address, width)
	if b != null:
		assert(width != 1, "byte access into a Native block")
		return b.read_unsigned(address, width)
	if records != null:
		var h: int = records.find(address, width)
		if h >= 0:
			return records.read(h, address, width)
	match width:
		1: return memory.read_u8(address)
		2: return memory.read_u16(address)
	return memory.read_u32(address)

func _write(address: int, width: int, v: int) -> void:
	var b = _block(address, width)
	if b != null:
		assert(width != 1, "byte access into a Native block")
		b.write(address, width, v)
		return
	if records != null:
		var h: int = records.find(address, width)
		if h >= 0:
			records.write(h, address, width, v)
			return
	match width:
		1: memory.write_u8(address, v)
		2: memory.write_u16(address, v)
		_: memory.write_u32(address, v)

func read_u8(address: int) -> int: return _read(address, 1)
func read_s8(address: int) -> int: return C68kScript.s8(_read(address, 1))
func read_u16(address: int) -> int: return _read(address, 2)
func read_s16(address: int) -> int: return C68kScript.s16(_read(address, 2))
func read_u32(address: int) -> int: return _read(address, 4)
func read_s32(address: int) -> int: return C68kScript.s32(_read(address, 4))
func write_u8(address: int, v: int) -> void: _write(address, 1, C68kScript.u8(v))
func write_u16(address: int, v: int) -> void: _write(address, 2, C68kScript.u16(v))
func write_u32(address: int, v: int) -> void: _write(address, 4, C68kScript.u32(v))
