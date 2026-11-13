extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const NativeState := preload("res://tests/helpers/native_state.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")

## Object retirement and release, contact ageing and the player camera on the views against the
## Original port, from the same bytes; the heap, template-pose, cleanup and fractal callees are
## stubbed alike, logging their arguments and a7:
## - retire_entity over 120 objects (player shot controller or not, bytes +1 / +2 equal or not,
##   +1 below / inside / above the child window, in 0..3 scatter slots, null controller, d0_in);
## - deactivate_object over 120 objects (kinds 2 / 3 / others, 0..6 faces or children, byte +6
##   bits 4 / 1, null controllers and support lists, child refcounts at 0);
## - age_contact_records over 40 tables (0..256 records, ages 0 / 1 / 0xffff, terrain and bit-5
##   objects);
## - dispatch_overlap_collisions over 60 lists (0..20 objects, overlapping and disjoint ranges,
##   controllers with and without notice handlers, replay state 3 or not; _jsr stubbed alike);
## - copy_player_pose_to_camera over 60 players (positions and attitude words at the extremes).
## Same result, memory byte for byte, calls in order at the same a7.

class OriginalRelease extends "res://original/port/pacific_conflict.gd":
	var calls: Array = []
	func _jsr(address: int, args: Array, _d0: int = 0):
		calls.append(["jsr", address, args, a7])
		return 0
	func heap_free_coalesce(p: int) -> void:
		calls.append(["free", p, a7])
	func reset_object_to_template_pose(o: int) -> void:
		calls.append(["pose", o, a7])
	func dispatch_entity_cleanup(o: int, _d0_in: int = 0) -> int:
		calls.append(["cleanup", o, a7])
		return 0
	func release_fractal_reference(o: int) -> void:
		calls.append(["fractal", o, a7])

class NativeRelease extends "res://native/state/native_overlay.gd":
	var calls: Array = []
	func _jsr(address: int, args: Array, _d0: int = 0):
		calls.append(["jsr", address, args, a7])
		return 0
	func heap_free_coalesce(p: int) -> void:
		calls.append(["free", p, a7])
	func reset_object_to_template_pose(o: int) -> void:
		calls.append(["pose", o, a7])
	func dispatch_entity_cleanup(o: int, _d0_in: int = 0) -> int:
		calls.append(["cleanup", o, a7])
		return 0
	func release_fractal_reference(o: int) -> void:
		calls.append(["fractal", o, a7])

const OBJ := 0x30000
const KIDS := 0x31000
const ROOT := 0x34000
const TABLE := 0x35000
const RECS := 0x36000

func _setup(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed
	for k in 0x100:
		m.write_u8(OBJ + k, rng.randi_range(0, 255))
	for k in 6:
		for j in 0x80:
			m.write_u8(KIDS + k * 0x80 + j, rng.randi_range(0, 255))
		m.write_u8(KIDS + k * 0x80, [2, 3, 2][rng.randi_range(0, 2)])
		m.write_u32(KIDS + k * 0x80 + 8, [0, 0x3a000 + k * 0x10][rng.randi_range(0, 1)])
		m.write_u32(KIDS + k * 0x80 + 0x2c, [0, 0x1234][rng.randi_range(0, 1)])
		m.write_u8(KIDS + k * 0x80 + 3, rng.randi_range(0, 2))
	m.write_u8(OBJ, [2, 3, 1, 0x82][rng.randi_range(0, 3)])
	var b1: int = rng.randi_range(0, 9)
	m.write_u8(OBJ + 1, b1)
	m.write_u8(OBJ + 2, b1 if rng.randi_range(0, 2) == 0 else rng.randi_range(0, 9))
	m.write_u32(OBJ + 8, [0, 0x3b000][rng.randi_range(0, 1)])
	m.write_u32(OBJ + 0x2c, [0, 0x2222][rng.randi_range(0, 1)])
	m.write_u16(OBJ + 0x2e, rng.randi_range(0, 6))
	m.write_u16(OBJ + 0x3a, rng.randi_range(0, 6))
	m.write_u32(OBJ + 0x48, 0x37000)
	m.write_u32(OBJ + 0x30, TABLE if m.read_u8(OBJ) == 3 else m.read_u32(OBJ + 0x30))
	for k in 6:
		m.write_u32(TABLE + 0x14 + k * 0x18, KIDS + k * 0x80)
		m.write_u32(0x37000 + 0x18 + k * 0x20, 0x38000 + k * 0x40)
	m.write_u32(0x1b880, m.read_u32(OBJ + 8) if rng.randi_range(0, 1) else 0x99)
	m.write_u32(0x1d6fc, ROOT)
	m.write_u32(ROOT + 0x30, 0x39000)
	m.write_u16(0x1b88e, rng.randi_range(-1, 5))
	m.write_u16(0x1b88c, rng.randi_range(3, 9))
	for k in 7:
		m.write_u32(0x1bc64 + 4 * k, OBJ if rng.randi_range(0, 3) == 0 else 0x40 * k)
	m.write_u32(0x24cf0, rng.randi_range(0, 0xffffffff))

func _contacts(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed + 300
	var n: int = [0, 1, 40, 256][seed % 4]
	for k in 0x100:
		m.write_u32(0x1bdbc + 4 * k, 0)
	for k in n:
		var slot: int = rng.randi_range(0, 255) if n < 256 else k
		var rec: int = RECS + slot * 0x10
		m.write_u32(0x1bdbc + 4 * slot, rec)
		m.write_u32(rec, KIDS + rng.randi_range(0, 5) * 0x80)
		m.write_u32(rec + 4, KIDS + rng.randi_range(0, 5) * 0x80)
		m.write_u16(rec + 8, [0, 0, 1, 0xffff, 7][rng.randi_range(0, 4)])
	for k in 6:
		m.write_u8(KIDS + k * 0x80 + 6, rng.randi_range(0, 255))

func _camera(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed + 900
	var ctl: int = 0x3c000
	m.write_u32(0x1b738, ctl)
	m.write_u32(ctl, OBJ)
	m.write_u32(ctl + 4, 0x3d000)
	for axis in 3:
		m.write_u32(OBJ + 0x18 + 4 * axis, [0, -1, 0x7fffffff, -0x80000000][rng.randi_range(0, 3)] if seed % 2 else rng.randi_range(-0x100000, 0x100000))
		m.write_u16(ctl + 0x20 + 2 * axis, [0, 1, 0x3fff, 0x4000, 0x7fff, 0x8000, 0xffff][rng.randi_range(0, 6)])
	m.write_u16(0x3d000 + 0x44, rng.randi_range(0, 0xffff))
	m.write_u16(ctl + 0x626, rng.randi_range(0, 0xffff))

func _run(name: String, n: int, prep: Callable, act: Callable) -> bool:
	var bad: Array = []
	var calls := 0
	for seed in n:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		prep.call(mo, seed)
		prep.call(mn, seed)
		var a := OriginalRelease.new(mo)
		var b := NativeRelease.new(mn)
		NativeState.seed_and_poison(b, mn)
		a.a7 = 0x3f000
		b.a7 = 0x3f000
		var ra = act.call(a, seed)
		var rb = act.call(b, seed)
		calls += a.calls.size()
		if ra != rb or NativeState.compare(b, mo, mn) != "" or a.calls != b.calls or a.a7 != b.a7:
			bad.append(seed)
	return Assertions.assert_equal(bad.slice(0, 5), [], "%s: same result, memory, calls (%d calls)" % [name, calls])

func run() -> bool:
	var ok := _run("retire_entity", 120, _setup, func(o, seed): return o.retire_entity(OBJ, seed * 3))
	ok = _run("deactivate_object", 120, _setup, func(o, _s): o.deactivate_object(OBJ); return 0) and ok
	ok = _run("age_contact_records", 40, func(m, s): _setup(m, s); _contacts(m, s), func(o, _s): o.age_contact_records(); return 0) and ok
	ok = _run("dispatch_overlap_collisions", 60, _overlaps, func(o, _s): o.dispatch_overlap_collisions(0x3e000, 0x3e800); return 0) and ok
	return _run("copy_player_pose_to_camera", 60, func(m, s): _setup(m, s); _camera(m, s), func(o, _s): o.copy_player_pose_to_camera(); return 0) and ok

func _overlaps(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed + 4000
	var n: int = rng.randi_range(0, 20)
	m.write_u16(0x1b870, [3, 0, 1][rng.randi_range(0, 2)])
	m.write_u32(0x1b888, 0x5555)
	m.write_u32(0x3e800, (rng.randi_range(0, 0xffff) << 16) | n)
	var metric: int = 0
	for i in n:
		var obj: int = OBJ + i * 0x80
		m.write_u32(0x3e000 + 4 * i, obj)
		metric += rng.randi_range(0, 0x3000)
		m.write_u32(obj + 0x10, metric)
		m.write_u32(obj + 0xc, rng.randi_range(0, 0x6000))
		m.write_u32(obj + 0x28, rng.randi_range(-0x8000, 0x20000))
		var ctl: int = [0, 0x3a000 + i * 0x20][rng.randi_range(0, 1)]
		m.write_u32(obj + 8, ctl)
		if ctl != 0:
			var def: int = 0x3b000 + i * 0x20
			m.write_u32(ctl + 4, def)
			m.write_u32(def + 0x16, [0, 0x100d4, 0xe0a4][rng.randi_range(0, 2)])
