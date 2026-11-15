extends RefCounted

## The Native edition's screen in Godot: the screen's bytes (0x700000, the screen's row bytes x 480,
## indexed as the Mac's at the current depth) live here, not in the Mac memory. This object stands in
## for the machine's memory wherever the program and its Toolbox reach memory through a `memory`
## field (every Toolbox manager, the segment loader and the host ports, the overlay loader, the
## runtime's written routines): an access inside the screen is served by `store`, any other by the
## Mac memory (`mac`), unchanged. The Native overlay's router (routed_memory.gd) takes the store
## directly (its SCREEN route). The presenter (native/display/native_screen.gd), the traces and
## screenshots read `screen_bytes()`.
##
## Installed before the program's thread starts (install()). The Mac memory's screen region is filled
## with POISON then and must stay so (poisoned()): a writer that still reaches the Mac memory's screen
## shows there, a reader would see the poison and diverge from Original in the screen hashes.

const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const C68kScript := preload("res://original/compat/c68k.gd")
const POISON := 0xa5

## Rollback switch: false keeps the screen in the Mac memory (the reference).
static var enabled := true
## The screen memory installed over each Mac memory (ports built later, as the overlay's at its load,
## are given the Mac memory itself: the Native router finds the screen through this).
static var _installed := {}

## The screen memory installed over a Mac memory, or null.
static func over(mac_memory):
	return _installed.get(mac_memory)

var mac                           ## the machine's MacMemory
var store := PackedByteArray()    ## the screen's bytes
var lo: int = MacMachine.SCREEN_BASE
var hi: int

func _init(mac_memory) -> void:
	mac = mac_memory
	hi = lo + MacMachine.SCREEN_WIDTH * MacMachine.SCREEN_HEIGHT   # the 8-bit size: any depth fits
	store = mac._bytes.slice(lo, hi)

## Sets the screen up for a machine (built, its program not started): the store takes the screen's
## bytes, every holder of the machine's memory gets this object, the Mac memory's screen is poisoned.
## Returns the screen memory (also the machine's meta "native_screen"), or null when disabled.
static func install(machine):
	if not enabled:
		return null
	var screen = new(machine.memory)
	for holder in _holders(machine):
		holder.set("memory", screen)
	for at in range(screen.lo, screen.hi):
		machine.memory._bytes[at] = POISON
	machine.set_meta("native_screen", screen)
	_installed[machine.memory] = screen
	return screen

## Every object that holds the machine's memory in a `memory` field: the Toolbox and its managers (and
## theirs, one level down), the segment loader and its ports, the overlay loader, the runtime's routines.
static func _holders(machine) -> Array:
	var out: Array = []
	var seen := {}
	var queue: Array = [machine.toolbox, machine.segments, machine.overlay_loader, machine.unexported]
	queue.append_array(machine.segments.instances.values())
	var depth := {}
	for o in queue:
		depth[o] = 0
	while not queue.is_empty():
		var o = queue.pop_front()
		if o == null or not (o is Object) or seen.has(o):
			continue
		seen[o] = true
		if o.get("memory") == machine.memory:
			out.append(o)
		if depth[o] >= 2:
			continue
		for p in o.get_property_list():
			if (p["usage"] & PROPERTY_USAGE_SCRIPT_VARIABLE) == 0:
				continue
			var v = o.get(p["name"])
			if v is RefCounted and not seen.has(v) and v != machine and v != machine.memory:
				depth[v] = depth[o] + 1
				queue.append(v)
	return out

## Whether the Mac memory's screen region still holds only the poison (no writer reached it).
func poisoned() -> bool:
	for at in range(lo, hi):
		if mac._bytes[at] != POISON:
			return false
	return true

# MacMemory's interface (24-bit addresses, as mac_memory.gd: the Mac memory's bytes decoded here,
# the same values its read / write give).

func contains(address: int, width: int = 1) -> bool:
	return mac.contains(address, width)

func _offset(address: int, width: int) -> int:
	return mac._offset(address, width)

func read_u8(address: int) -> int:
	var at: int = address & 0xffffff
	if at >= lo and at < hi:
		return store[at - lo]
	return mac._bytes[at]

func read_s8(address: int) -> int:
	return C68kScript.s8(read_u8(address))

func read_u16(address: int) -> int:
	var at: int = address & 0xffffff
	if at + 2 > lo and at < hi:
		assert(at >= lo and at + 2 <= hi, "access 0x%x straddles the screen's edge" % at)
		return (store[at - lo] << 8) | store[at - lo + 1]
	assert(at + 2 <= mac.SIZE, "68000 memory access out of bounds")
	return (mac._bytes[at] << 8) | mac._bytes[at + 1]

func read_s16(address: int) -> int:
	return C68kScript.s16(read_u16(address))

func read_u32(address: int) -> int:
	var at: int = address & 0xffffff
	if at + 4 > lo and at < hi:
		assert(at >= lo and at + 4 <= hi, "access 0x%x straddles the screen's edge" % at)
		var o: int = at - lo
		return (store[o] << 24) | (store[o + 1] << 16) | (store[o + 2] << 8) | store[o + 3]
	assert(at + 4 <= mac.SIZE, "68000 memory access out of bounds")
	return (mac._bytes[at] << 24) | (mac._bytes[at + 1] << 16) | (mac._bytes[at + 2] << 8) | mac._bytes[at + 3]

func read_s32(address: int) -> int:
	return C68kScript.s32(read_u32(address))

func write_u8(address: int, value: int) -> void:
	var at: int = address & 0xffffff
	if at >= lo and at < hi:
		store[at - lo] = value & 0xff
	else:
		mac._bytes[at] = value & 0xff

func write_u16(address: int, value: int) -> void:
	var at: int = address & 0xffffff
	if at + 2 > lo and at < hi:
		assert(at >= lo and at + 2 <= hi, "access 0x%x straddles the screen's edge" % at)
		store[at - lo] = (value >> 8) & 0xff
		store[at - lo + 1] = value & 0xff
	else:
		assert(at + 2 <= mac.SIZE, "68000 memory access out of bounds")
		mac._bytes[at] = (value >> 8) & 0xff
		mac._bytes[at + 1] = value & 0xff

func write_u32(address: int, value: int) -> void:
	var at: int = address & 0xffffff
	if at + 4 > lo and at < hi:
		assert(at >= lo and at + 4 <= hi, "access 0x%x straddles the screen's edge" % at)
		var o: int = at - lo
		store[o] = (value >> 24) & 0xff
		store[o + 1] = (value >> 16) & 0xff
		store[o + 2] = (value >> 8) & 0xff
		store[o + 3] = value & 0xff
	else:
		assert(at + 4 <= mac.SIZE, "68000 memory access out of bounds")
		mac._bytes[at] = (value >> 24) & 0xff
		mac._bytes[at + 1] = (value >> 16) & 0xff
		mac._bytes[at + 2] = (value >> 8) & 0xff
		mac._bytes[at + 3] = value & 0xff

## The screen as one palette index a pixel, as MacMachine.screen_bytes() gives the Mac memory's.
func screen_bytes() -> PackedByteArray:
	var depth: int = MacMachine.screen_depth
	if depth == 8:
		return store.duplicate()
	var out := PackedByteArray()
	out.resize(MacMachine.SCREEN_WIDTH * MacMachine.SCREEN_HEIGHT)
	for i in MacMachine.screen_row_bytes() * MacMachine.SCREEN_HEIGHT:
		var b: int = store[i]
		if depth == 4:
			out[i * 2] = b >> 4
			out[i * 2 + 1] = b & 0xf
		else:
			for k in 8:
				out[i * 8 + k] = 255 if (b & (0x80 >> k)) != 0 else 0
	return out
