extends RefCounted

## The replay camera's own state: the first block the Native edition keeps in Godot instead of the
## Mac memory. Its fields, at the original's addresses and widths:
##   0x1d814  3 longs  camera_step: per-frame camera motion, +0 z, +4 y, +8 x (goal - camera) / frames
##   0x24d18  word     camera_heading (turn_camera_toward_point's own angle; its negation is 0x1d80e)
##   0x24d1a  word     camera_pitch (likewise; its negation is 0x1d810)
##   0x24d1c  long     target_hold_frames
##   0x24d20  3 longs  camera_goal: +0 z, +4 y, +8 x
## Closed block (evidence, PROJECT_STATUS loop 4): in the Ghidra C only turn_camera_toward_point
## 0x8abe, update_tracking_camera 0x8c30 and begin_replay_view 0x8ff4 name these addresses, no
## pointer is taken into them, and the overlay image's absolute operands for each (3 / 3 / 3, 6, 6,
## 6, 3 / 5 / 3) are exactly those three functions' references. All three are Native.
##
## Both ranges lie in the overlay's bss (image header: code 0x1a75c + data 0xdc8, bss 0xb2a4 ->
## 0x1b524..0x267c8). That is the block's one other writer, found by watching the Mac memory: the
## host's reset_overlay_state (ID02 0x212a) memsets the whole bss at every mission start and then
## calls the overlay entry (overlay_main_entry 0x1112, its only caller). So the block is zero
## whenever the overlay starts, and changes only through the three functions above.
##
## Two backends with one interface: memory-backed (the reference: the Original's bytes) and
## Godot-backed (typed integers, starting at zero and zeroed by reset() where the host zeroes the
## bss; the Mac memory is neither read nor written). Values are kept wrapped to their width; signed
## reads sign them as the original's reads do.

const C68kScript := preload("res://original/compat/c68k.gd")

const STEP := 0x1d814
const HEADING := 0x24d18
const PITCH := 0x24d1a
const HOLD := 0x24d1c
const GOAL := 0x24d20
## The Mac memory ranges the block covers: [address, length].
const RANGES := [[0x1d814, 12], [0x24d18, 0x14]]

var memory
var godot_backed: bool
var _step := [0, 0, 0]       ## u32 each, by offset / 4
var _heading: int = 0        ## u16
var _pitch: int = 0          ## u16
var _hold: int = 0           ## u32
var _goal := [0, 0, 0]       ## u32 each, by offset / 4

func _init(memory_, godot_backed_: bool) -> void:
	memory = memory_
	godot_backed = godot_backed_

## The host's bss clear before the overlay entry (Godot backend; the memory-backed block is
## cleared by the host's memset itself).
func reset() -> void:
	if not godot_backed:
		return
	_step = [0, 0, 0]
	_goal = [0, 0, 0]
	_heading = 0
	_pitch = 0
	_hold = 0

## For differential tests only: start the Godot-held block from the bytes a test set in memory.
func seed_from_memory_for_test() -> void:
	for k in 3:
		_step[k] = memory.read_u32(STEP + 4 * k)
		_goal[k] = memory.read_u32(GOAL + 4 * k)
	_heading = memory.read_u16(HEADING)
	_pitch = memory.read_u16(PITCH)
	_hold = memory.read_u32(HOLD)

## k: 0 = +0 (z), 1 = +4 (y), 2 = +8 (x).
func step_u32(k: int) -> int:
	if not godot_backed:
		return memory.read_u32(STEP + 4 * k)
	return _step[k]
func set_step(k: int, v: int) -> void:
	if not godot_backed:
		memory.write_u32(STEP + 4 * k, v)
		return
	_step[k] = C68kScript.u32(v)

func heading_u16() -> int:
	if not godot_backed:
		return memory.read_u16(HEADING)
	return _heading
func set_heading(v: int) -> void:
	if not godot_backed:
		memory.write_u16(HEADING, v)
		return
	_heading = C68kScript.u16(v)

func pitch_u16() -> int:
	if not godot_backed:
		return memory.read_u16(PITCH)
	return _pitch
func set_pitch(v: int) -> void:
	if not godot_backed:
		memory.write_u16(PITCH, v)
		return
	_pitch = C68kScript.u16(v)

func hold_u32() -> int:
	if not godot_backed:
		return memory.read_u32(HOLD)
	return _hold
func set_hold(v: int) -> void:
	if not godot_backed:
		memory.write_u32(HOLD, v)
		return
	_hold = C68kScript.u32(v)

func goal_u32(k: int) -> int:
	if not godot_backed:
		return memory.read_u32(GOAL + 4 * k)
	return _goal[k]
func set_goal(k: int, v: int) -> void:
	if not godot_backed:
		memory.write_u32(GOAL + 4 * k, v)
		return
	_goal[k] = C68kScript.u32(v)

## The block as the Mac memory would hold it, per range ([address, bytes]): the Godot values for
## the Godot backend (for traces and tests: what Native holds, not the memory's stale bytes).
func memory_image() -> Array:
	if not godot_backed:
		return [[STEP, memory._bytes.slice(STEP, STEP + 12)], [HEADING, memory._bytes.slice(HEADING, HEADING + 0x14)]]
	var a := PackedByteArray()
	a.resize(12)
	for k in 3:
		a.encode_u32(4 * k, _step[k])
	var b := PackedByteArray()
	b.resize(0x14)
	b.encode_u16(0, _heading)
	b.encode_u16(2, _pitch)
	b.encode_u32(4, _hold)
	for k in 3:
		b.encode_u32(8 + 4 * k, _goal[k])
	# PackedByteArray encodes little-endian: the Mac's bytes are big-endian.
	return [[STEP, _be(a, [4, 4, 4])], [HEADING, _be(b, [2, 2, 4, 4, 4, 4])]]

static func _be(bytes: PackedByteArray, widths: Array) -> PackedByteArray:
	var out := PackedByteArray()
	var at := 0
	for w in widths:
		var part := bytes.slice(at, at + w)
		part.reverse()
		out.append_array(part)
		at += w
	return out
