extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const NativeState := preload("res://tests/helpers/native_state.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const SCENERY := "res://original/mac/data/scenery/Pacific Conflict"

## update_ground_scatter_objects (0x5e28) on the views against the Original port, over the overlay
## image (its trigonometry and band tables) and 300 generated scenes: scatter off, the camera above
## / at / below 0x26ac (scatter started or not: the slot clear), the reset flag, cockpit and outside
## views, points near, at and beyond 0x4d58 of the camera, every state byte form (slot names with
## and without objects), empty and occupied slots (busy, hidden, own point kept, same-class with and
## without vertices and fractal references, camera-facing or chained vertices, other-class),
## surfaces with and without templates, cached and fresh. The random numbers come from one scripted
## sequence; surface_type_at_point, spawn_mission_pool_object, release_fractal_reference,
## dispatch_entity_cleanup, clear_pending_entity_slots, apply_template_chain_xform and
## _call_shade_object_faces are stubbed alike, logging their arguments and a7. The point transforms,
## metric and band are the editions' own. Same memory byte for byte, same calls in order, same a7.

class OriginalScatter extends "res://original/port/pacific_conflict.gd":
	var calls: Array = []
	var rng_script: Array = []
	var surfaces: Array = []
	var spawned: int = 0x37000
	func overlay_rng_next() -> int:
		calls.append(["rng", a7])
		return rng_script.pop_front()
	func surface_type_at_point(x: int, y: int, z: int) -> int:
		calls.append(["surface", x, y, z, a7])
		return surfaces.pop_front()
	func spawn_mission_pool_object(t: int, x: int, y: int, z: int, a1: int, a2: int, a3: int, f: int) -> int:
		calls.append(["spawn", t, x, y, z, a1, a2, a3, f, a7])
		spawned += 0x80
		return spawned
	func release_fractal_reference(o: int) -> void:
		calls.append(["fractal", o, a7])
	func dispatch_entity_cleanup(o: int, _d0_in: int = 0) -> int:
		calls.append(["cleanup", o, a7])
		return 0
	func clear_pending_entity_slots() -> void:
		calls.append(["clear", a7])
	func apply_template_chain_xform(o: int, n: int, src: int, dst: int) -> void:
		calls.append(["chain", o, n, src, dst, a7])
	func _call_shade_object_faces(o: int, depth: int) -> void:
		calls.append(["shade", o, depth, a7])

class NativeScatter extends "res://native/state/native_overlay.gd":
	var calls: Array = []
	var rng_script: Array = []
	var surfaces: Array = []
	var spawned: int = 0x37000
	func overlay_rng_next() -> int:
		calls.append(["rng", a7])
		return rng_script.pop_front()
	func surface_type_at_point(x: int, y: int, z: int) -> int:
		calls.append(["surface", x, y, z, a7])
		return surfaces.pop_front()
	func spawn_mission_pool_object(t: int, x: int, y: int, z: int, a1: int, a2: int, a3: int, f: int) -> int:
		calls.append(["spawn", t, x, y, z, a1, a2, a3, f, a7])
		spawned += 0x80
		return spawned
	func release_fractal_reference(o: int) -> void:
		calls.append(["fractal", o, a7])
	func dispatch_entity_cleanup(o: int, _d0_in: int = 0) -> int:
		calls.append(["cleanup", o, a7])
		return 0
	func clear_pending_entity_slots() -> void:
		calls.append(["clear", a7])
	func apply_template_chain_xform(o: int, n: int, src: int, dst: int) -> void:
		calls.append(["chain", o, n, src, dst, a7])
	func _call_shade_object_faces(o: int, depth: int) -> void:
		calls.append(["shade", o, depth, a7])

const HOST := 0x30000
const PLAYER := 0x30100
const TEMPLATES := 0x31000
const SLOT_OBJS := 0x32000
const ROOT := 0x33000
const TABLE := 0x34000
const DESCS := 0x35000
const VERTS := 0x36000
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
	m.write_u32(0x1b5a0, HOST)
	m.write_u16(HOST + 0xac, 0 if seed % 23 == 5 else 1)
	m.write_u32(HOST + 0x8c, 0x10)
	m.write_u32(0x1b738, PLAYER)
	var ground: int = rng.randi_range(-0x800, 0x800)
	m.write_u32(PLAYER + 0x2e, ground)
	m.write_u16(PLAYER + 0x22, rng.randi_range(0, 0xffff))
	var cx: int = rng.randi_range(-0x100000, 0x100000)
	var cz: int = rng.randi_range(-0x100000, 0x100000)
	m.write_u32(0x1d838, cx)
	m.write_u32(0x1d830, cz)
	m.write_u16(0x1d858, rng.randi_range(0, 9))
	m.write_u32(0x1d834, ground + _pick(rng, [0, 0x100, 0x1000, 0x26ac, 0x26ac, 0x26ad, 0x5000, -0x200]))
	m.write_u32(0x24cec, _pick(rng, [0, 1, 1, 1]))
	m.write_u16(0x1d856, _pick(rng, [0, 0, 0, 1]))
	for k in 9:
		m.write_u16(0x1d744 + 2 * k, (0x1000 if k % 4 == 0 else 0) if seed % 3 == 0 else rng.randi_range(-0x1000, 0x1000))
	m.write_u16(0x20b28, _pick(rng, [0x100, 0x200, 0x80]))
	m.write_u16(0x20b26, _pick(rng, [0x100, 0x200, 0x80]))
	m.write_u16(0x20aee, -200)
	m.write_u16(0x20aec, 200)
	m.write_u16(0x20aea, -150)
	m.write_u16(0x1b73e, 150)
	for i in 0x40:
		var spread: int = _pick(rng, [0x1000, 0x4d58, 0x4d59, 0x6000])
		m.write_u32(0x1b924 + 12 * i, cx + rng.randi_range(-spread, spread))
		m.write_u32(0x1b924 + 12 * i + 4, rng.randi_range(-0x100, 0x100))
		m.write_u32(0x1b924 + 12 * i + 8, cz + rng.randi_range(-spread, spread))
		var state: int = rng.randi_range(0, 255)
		if rng.randi_range(0, 3) == 0:
			state = 0xc0 | rng.randi_range(0, 6)
		m.write_u8(0x1b8e4 + i, state)
		m.write_u8(0x1b8a4 + i, _pick(rng, [0x00, 0x10, 0x25, 0x37, 0xf0]))
	for j in 16:
		var t: int = TEMPLATES + j * 0x40
		m.write_u8(t + 1, rng.randi_range(0, 3))
		m.write_u32(0x1bc24 + 4 * j, 0 if rng.randi_range(0, 3) == 0 else t)
	m.write_u32(ROOT + 0x28, ROOT + 0x100)
	m.write_u32(ROOT + 0x30, TABLE)
	for idx in 4:
		m.write_u32(ROOT + 0x100 + 0x1a + 0x14 + idx * 0x18, DESCS + idx * 0x100 - 0x10)
		var d: int = DESCS + idx * 0x100
		for off in [2, 6, 0xa]:
			m.write_u32(d + off, rng.randi_range(-0x2000, 0x2000))
		for w in 60:
			m.write_u16(d + 0x22 + 2 * w, rng.randi_range(-0x400, 0x400))
	m.write_u32(0x1d6fc, ROOT)
	for k in 7:
		m.write_u16(0x1b894 + 2 * k, rng.randi_range(0, 63))
		if rng.randi_range(0, 4) == 0:
			m.write_u32(0x1bc64 + 4 * k, 0)
			continue
		var o: int = SLOT_OBJS + k * 0x80
		m.write_u32(0x1bc64 + 4 * k, o)
		m.write_u8(o, 2)
		m.write_u8(o + 1, rng.randi_range(0, 3))
		m.write_u8(o + 2, rng.randi_range(0, 7))
		m.write_u8(o + 5, _pick(rng, [0, 0x10]))
		m.write_u16(o + 6, rng.randi_range(0, 0xffff))
		m.write_u8(o + 7, _pick(rng, [0, 0x40, 0x42, 0x44, 0x04]))
		m.write_u32(o + 0x2c, _pick(rng, [0, 0, 0x3a000]))
		m.write_u16(o + 0x30, rng.randi_range(1, 5))
		m.write_u16(o + 0x36, rng.randi_range(0, 3))
		m.write_u16(o + 0x3a, rng.randi_range(0, 2))
		m.write_u32(o + 0x44, 0 if rng.randi_range(0, 4) == 0 else VERTS + k * 0x100)
		m.write_u32(o + 0x18, rng.randi_range(-0x10000, 0x10000))
		if rng.randi_range(0, 2) == 0:
			m.write_u8(0x1b8e4 + m.read_u16(0x1b894 + 2 * k), 0xc0 | k)

func run() -> bool:
	_image = FileAccess.get_file_as_bytes(SCENERY)
	var bad: Array = []
	var seen := {}
	for seed in 300:
		var mo = _memory()
		var mn = _memory()
		_setup(mo, seed)
		_setup(mn, seed)
		var a := OriginalScatter.new(mo)
		var b := NativeScatter.new(mn)
		NativeState.seed_and_poison(b, mn)
		a.a7 = 0x3f000
		b.a7 = 0x3f000
		var r := RandomNumberGenerator.new()
		r.seed = seed + 7
		for k in 160:
			var v: int = r.randi_range(0, 0xffff)
			a.rng_script.append(v)
			b.rng_script.append(v)
		for k in 64:
			var s: int = _pick(r, [0x10, 0x20, 0x00, 0x35, 0xf0, 0x7f])
			a.surfaces.append(s)
			b.surfaces.append(s)
		a.update_ground_scatter_objects()
		b.update_ground_scatter_objects()
		for c in a.calls:
			seen[c[0]] = seen.get(c[0], 0) + 1
		if NativeState.compare(b, mo, mn) != "" or a.calls != b.calls or a.a7 != b.a7:
			bad.append(seed)
	var ok := Assertions.assert_equal(bad.slice(0, 8), [], "update_ground_scatter_objects: same memory, calls, a7")
	for kind in ["rng", "surface", "spawn", "fractal", "cleanup", "clear", "chain", "shade"]:
		ok = Assertions.assert_true(seen.get(kind, 0) > 0, "branch reached: %s (%d)" % [kind, seen.get(kind, 0)]) and ok
	return ok
