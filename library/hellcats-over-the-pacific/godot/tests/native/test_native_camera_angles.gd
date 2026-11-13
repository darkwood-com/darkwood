extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const NativeState := preload("res://tests/helpers/native_state.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const SCENERY := "res://original/mac/data/scenery/Pacific Conflict"

## The camera-angle readers on Native's angle storage against the Original port, over the overlay
## image (trig tables): hand-written on the views - tick_view_attached_object, face_object_to_camera,
## shape_free_plume (facing or not, a pending width, once per frame, young and old plumes; RNG
## scripted alike), update_direction_needle - and generated (routed_port.gd) - update_heading_tape
## (panels drawn or not, depth 4 / 8, the position unchanged or moved, the strip wrapping, cursor
## hook or not) and update_radar_scope (blips to erase, tracks inside and outside the scope, both
## drawing modes). 120 scenes each with the angles at the extremes, both storage modes: memory-backed
## (memory byte for byte) and Godot-backed (the blocks seeded, their Mac bytes overwritten with 0xa5
## and left so, the rest of the memory and the Godot-held values as the Original's). _jsr, the
## gauge-needle draw, begin / end_offscreen_drawing and the RNG are stubbed alike, logged with a7.

class OriginalAngles extends "res://original/port/pacific_conflict.gd":
	var calls: Array = []
	var rng_script: Array = []
	func _jsr(address: int, args: Array, _d0: int = 0):
		calls.append(["jsr", address, args, a7])
		return 0
	func overlay_rng_next() -> int:
		calls.append(["rng", a7])
		return rng_script.pop_front()
	func _call_draw_gauge_needle(entry_a7: int, depth: int, kind: int, cx: int, cy: int, ex: int, ey: int) -> int:
		calls.append(["needle", entry_a7, depth, kind, cx, cy, ex, ey, a7])
		return 0
	func begin_offscreen_drawing(port: int, row_bytes: int, base: int, rows: int) -> int:
		calls.append(["begin", port, row_bytes, base, rows, a7])
		return 0x3a000
	func end_offscreen_drawing(port: int, saved: int) -> void:
		calls.append(["end", port, saved, a7])

class NativeAngles extends "res://native/state/native_overlay.gd":
	var calls: Array = []
	var rng_script: Array = []
	func _jsr(address: int, args: Array, _d0: int = 0):
		calls.append(["jsr", address, args, a7])
		return 0
	func overlay_rng_next() -> int:
		calls.append(["rng", a7])
		return rng_script.pop_front()
	func _call_draw_gauge_needle(entry_a7: int, depth: int, kind: int, cx: int, cy: int, ex: int, ey: int) -> int:
		calls.append(["needle", entry_a7, depth, kind, cx, cy, ex, ey, a7])
		return 0
	func begin_offscreen_drawing(port: int, row_bytes: int, base: int, rows: int) -> int:
		calls.append(["begin", port, row_bytes, base, rows, a7])
		return 0x3a000
	func end_offscreen_drawing(port: int, saved: int) -> void:
		calls.append(["end", port, saved, a7])

const HOST := 0x30000
const CTL := 0x31000
const OBJ := 0x31800
const VERTS := 0x31a00
const PLAYER_CTL := 0x32000
const PLAYER := 0x32800
const STRIP := 0x33000
const MARKS := 0x34000
const TRACKS := 0x35000
const BLIPS := 0x35800
var _image := PackedByteArray()
var _template := PackedByteArray()

func _memory():
	var m = Memory68k.new(0x40000)
	if _template.is_empty():
		for k in mini(_image.size() - 0x20, 0x3e700):
			m.write_u8(k, _image[0x20 + k])
		_template = m._bytes.duplicate()
	else:
		m._bytes = _template.duplicate()
	return m

func _pick(rng: RandomNumberGenerator, values: Array):
	return values[rng.randi_range(0, values.size() - 1)]

func _setup(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed
	for a in [0x1d80c, 0x1d80e, 0x1d810]:
		m.write_u16(a, _pick(rng, [0, 1, 0x3fff, 0x4000, 0x8000, 0xffff, 0x2000]) if seed % 3 == 0 else rng.randi_range(0, 0xffff))
	for a in [0x1d800, 0x1d804, 0x1d808]:
		m.write_u32(a, rng.randi_range(0, 0x3fff))
	for k in 3:
		m.write_u32(0x1d830 + 4 * k, rng.randi_range(-0x100000, 0x100000))
	m.write_u32(0x1b838, 1000)
	m.write_u32(0x1b5a0, HOST)
	for k in 0x400:
		m.write_u8(HOST + k, rng.randi_range(0, 255))
	# Controller / object / plume.
	m.write_u32(CTL, OBJ)
	for k in 3:
		m.write_u32(OBJ + 0x18 + 4 * k, rng.randi_range(-0x100000, 0x100000))
	m.write_u32(OBJ + 0x44, VERTS)
	m.write_u32(OBJ + 0x40, 1000 - _pick(rng, [0, 1, 0x2c, 0x2d, 0x200]))
	m.write_u16(CTL + 0x32, _pick(rng, [0, 1]))
	m.write_u16(CTL + 0x36, _pick(rng, [0, 0, 7, -9, 0x40]))
	m.write_u16(CTL + 0x34, _pick(rng, [0, 0x10, -0x20, 0x300]))
	m.write_u32(CTL + 0x38, _pick(rng, [1000, 999]))
	for k in 0x60:
		m.write_u8(VERTS + k, rng.randi_range(0, 255))
	# Heading tape.
	var period: int = _pick(rng, [360, 512, 720])
	var cols: int = _pick(rng, [40, 64, 100])
	m.write_u16(HOST + 0x1a4, period)
	m.write_u16(HOST + 0x19e, cols)
	m.write_u16(HOST + 0x1a0, rng.randi_range(4, 20))
	m.write_u16(HOST + 0x1a8, rng.randi_range(0, 200))
	m.write_u32(HOST + 0x1b0, STRIP)
	m.write_u32(HOST + 0x1b8, MARKS)
	for k in 0x400:
		m.write_u8(MARKS + k, _pick(rng, [0, 0, 1]))
	m.write_u16(0x20ae4, _pick(rng, [0, 1]))
	m.write_u16(0x1b826, _pick(rng, [4, 8, 8]))
	m.write_u16(0x25ffe, rng.randi_range(0, period - 1) if seed % 5 else 0)
	# Radar.
	m.write_u32(0x1b738, PLAYER_CTL)
	m.write_u32(PLAYER_CTL, PLAYER)
	for k in 3:
		m.write_u32(PLAYER + 0x18 + 4 * k, rng.randi_range(-0x100000, 0x100000))
	m.write_u16(HOST + 0x9e, _pick(rng, [0, 1]))
	m.write_u16(HOST + 0x1e0, 0)
	m.write_u16(HOST + 0x1e4, 120)
	m.write_u16(HOST + 0x1de, 0)
	m.write_u16(HOST + 0x1e2, 120)
	m.write_u16(HOST + 0x1e8, 60)
	m.write_u16(HOST + 0x1ea, 60)
	m.write_u16(0x26664, _pick(rng, [0x40, 0x100, 0x800]))
	m.write_u32(0x1b520, BLIPS)
	m.write_u32(0x26672, rng.randi_range(0, 6))
	for k in 8:
		m.write_u32(BLIPS + 0x14 * k + 0x10, _pick(rng, [0, 1]))
	m.write_u32(0x1b51c, TRACKS)
	m.write_u32(0x26676, rng.randi_range(0, 10))
	for k in 12:
		m.write_u32(TRACKS + 0x14 * k, m.read_u32(PLAYER + 0x18) + rng.randi_range(-0x8000, 0x8000))
		m.write_u32(TRACKS + 0x14 * k + 4, m.read_u32(PLAYER + 0x20) + rng.randi_range(-0x8000, 0x8000))

func _case(seed: int, kind: String, godot_backed: bool) -> String:
	var mo = _memory()
	var mn = _memory()
	_setup(mo, seed)
	_setup(mn, seed)
	var a := OriginalAngles.new(mo)
	var b := NativeAngles.new(mn)
	b.camera_angle_storage_godot = godot_backed
	b.camera_position_storage_godot = godot_backed
	b.view_state_storage_godot = godot_backed
	b.replay_camera_storage_godot = godot_backed
	NativeState.seed_and_poison(b, mn)
	a.a7 = 0x3f000
	b.a7 = 0x3f000
	for k in 8:
		a.rng_script.append((seed * 7919 + k * 104729) & 0xffff)
		b.rng_script.append((seed * 7919 + k * 104729) & 0xffff)
	var ra: Array = []
	var rb: Array = []
	match kind:
		"attached":
			a.tick_view_attached_object(CTL)
			b.tick_view_attached_object(CTL)
		"face":
			ra.append(a.face_object_to_camera(CTL))
			rb.append(b.face_object_to_camera(CTL))
		"plume":
			ra.append(a.shape_free_plume(CTL))
			rb.append(b.shape_free_plume(CTL))
		"needle":
			a.update_direction_needle()
			b.update_direction_needle()
		"tape":
			var hide: int = seed % 2
			a.update_heading_tape(hide)
			b.update_heading_tape(hide)
		"radar":
			a.update_radar_scope()
			b.update_radar_scope()
	if ra != rb:
		return "results"
	if a.calls != b.calls:
		return "calls"
	if a.a7 != b.a7:
		return "a7"
	if not godot_backed:
		return "" if mo._bytes == mn._bytes else "memory"
	return NativeState.compare(b, mo, mn)

func run() -> bool:
	_image = FileAccess.get_file_as_bytes(SCENERY)
	var ok := true
	for godot_backed in [false, true]:
		var bad: Array = []
		for kind in ["attached", "face", "plume", "needle", "tape", "radar"]:
			for seed in 120:
				var why := _case(seed, kind, godot_backed)
				if why != "":
					bad.append([kind, seed, why])
		ok = Assertions.assert_equal(bad.slice(0, 6), [], "camera-angle readers (%s angles): same results, calls, a7, memory" % ["Godot-backed" if godot_backed else "memory-backed"]) and ok
	return ok
