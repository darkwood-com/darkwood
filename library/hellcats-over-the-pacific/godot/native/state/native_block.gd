extends RefCounted

## A block of the overlay's globals that the Native edition keeps in Godot instead of the Mac memory:
## typed fields at the original's addresses and widths (FIELDS: address -> width in bytes, every one
## inside the overlay's bss, which the host's reset_overlay_state zeroes before every overlay entry).
## The blocks and their evidence (PROJECT_STATUS, loops 5 and 6):
##   camera angles   0x1d800..0x1d811: longs camera_angle6 / 5 / 4, words camera_angle3,
##                   neg_camera_heading, neg_camera_pitch;
##   camera position 0x1d830..0x1d83b: longs camera z / y / x;
##   view state      0x1d856..0x1d859: words view_flag_d856 (reset the ground scatter), view_mode.
## Every reader and writer of a block is Native (hand-written on the views or generated verbatim with
## routed accesses, routed_port.gd), and a runtime watch over the Mac bytes saw only the host's
## bss clear and allocation.
##
## Two backends with one interface: memory-backed (the reference: the Original's bytes) and
## Godot-backed (integers wrapped to their width; the Mac memory is neither read nor written).
## Access is by the original's address and width, exactly one field at a time: any other access
## overlapping the block (a byte of a word, a long over two fields) fails, rather than falling back
## to the memory.

const C68kScript := preload("res://original/compat/c68k.gd")

const CAMERA_ANGLES := {0x1d800: 4, 0x1d804: 4, 0x1d808: 4, 0x1d80c: 2, 0x1d80e: 2, 0x1d810: 2}
const CAMERA_POSITION := {0x1d830: 4, 0x1d834: 4, 0x1d838: 4}
const VIEW_STATE := {0x1d856: 2, 0x1d858: 2}

var memory
var fields: Dictionary
var godot_backed: bool
var start: int
var end: int
var _values := {}

func _init(memory_, fields_: Dictionary, godot_backed_: bool) -> void:
	memory = memory_
	fields = fields_
	godot_backed = godot_backed_
	start = fields.keys().min()
	end = 0
	for k in fields:
		end = maxi(end, k + fields[k])
		_values[k] = 0

func overlaps(address: int, width: int) -> bool:
	var at: int = address & 0xffffff
	return at < end and at + width > start

## The host's bss clear before the overlay entry (the memory-backed block is cleared by the memset).
func reset() -> void:
	if not godot_backed:
		return
	for k in _values:
		_values[k] = 0

func _field(address: int, width: int) -> int:
	var at: int = address & 0xffffff
	assert(fields.get(at, 0) == width, "Native block 0x%x: access 0x%x width %d is not one field" % [start, address, width])
	return at

## Unsigned value of the field at the address (width 2 or 4).
func read_unsigned(address: int, width: int) -> int:
	if not godot_backed:
		return memory.read_u16(address) if width == 2 else memory.read_u32(address)
	return _values[_field(address, width)]

func write(address: int, width: int, v: int) -> void:
	if not godot_backed:
		if width == 2:
			memory.write_u16(address, v)
		else:
			memory.write_u32(address, v)
		return
	_values[_field(address, width)] = C68kScript.u16(v) if width == 2 else C68kScript.u32(v)

## For differential tests only: start the Godot-held block from the bytes a test set in memory.
func seed_from_memory_for_test() -> void:
	for k in fields:
		_values[k] = memory.read_u16(k) if fields[k] == 2 else memory.read_u32(k)

## The block as the Mac memory would hold it: [[start, bytes]] (Godot values for the Godot backend).
func memory_image() -> Array:
	var b := PackedByteArray()
	b.resize(end - start)
	for k in fields:
		var v: int = read_unsigned(k, fields[k])
		for i in fields[k]:
			b[k - start + i] = (v >> (8 * (fields[k] - 1 - i))) & 0xff
	return [[start, b]]
