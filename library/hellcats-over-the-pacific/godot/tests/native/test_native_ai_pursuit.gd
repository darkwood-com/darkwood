extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const SCENERY := "res://original/mac/data/scenery/Pacific Conflict"

## The aircraft AI pursuit helpers on the views against the Original port, over the overlay image
## (its trigonometry tables), from the same bytes:
## - ai_bootstrap_climb_phase over 150 controllers (heights around 200 / 2000, gear and flaps
##   bytes across the sign boundary, pitch words at the extremes, previous errors);
## - ai_script_try_fire_burst over 150 (before / at / after the release frame, no bombs, busy
##   bomb phase, gate values); the random numbers come from one scripted sequence in both, and
##   spawn_weapon_entity is stubbed alike, logging its arguments and a7;
## - build_relative_steer_sample over 200 targets (near and far, above and below, low over the
##   ground, long +0xc up to +-0x10000, every attitude quadrant), the callees the Original's. Spans
##   past 0x7fffffff never leave the original's shift loop (an asr keeps the sign bit), in both: the
##   inputs stay in the game's range.
## Same results, same memory byte for byte, same calls in order at the same a7.

class OriginalAI extends "res://original/port/pacific_conflict.gd":
	var calls: Array = []
	var rng_script: Array = []
	func overlay_rng_next() -> int:
		calls.append(["rng", a7])
		return rng_script.pop_front()
	func spawn_weapon_entity(speed: int, kind: int, x: int, y: int, z: int, pitch: int, heading: int, bank: int) -> int:
		calls.append(["spawn", speed, kind, x, y, z, pitch, heading, bank, a7])
		return 0

class NativeAI extends "res://native/state/native_overlay.gd":
	var calls: Array = []
	var rng_script: Array = []
	func overlay_rng_next() -> int:
		calls.append(["rng", a7])
		return rng_script.pop_front()
	func spawn_weapon_entity(speed: int, kind: int, x: int, y: int, z: int, pitch: int, heading: int, bank: int) -> int:
		calls.append(["spawn", speed, kind, x, y, z, pitch, heading, bank, a7])
		return 0

const CTL := 0x38000
const OBJ := 0x38800
const VEC := 0x39000
const OUT := 0x39100
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

func _setup(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed
	for k in 0x700:
		m.write_u8(CTL + k, rng.randi_range(0, 255))
	m.write_u32(CTL, OBJ)
	m.write_u32(OBJ + 0x18, rng.randi_range(-0x200000, 0x200000))
	m.write_u32(CTL + 0x2e, rng.randi_range(-0x1000, 0x1000))
	var above: int = [0, 199, 200, 201, 1999, 2000, 2001, 999, 1000, 4000, 0x20000][rng.randi_range(0, 10)]
	m.write_u32(OBJ + 0x1c, m.read_s32(CTL + 0x2e) + above)
	m.write_u32(OBJ + 0x20, rng.randi_range(-0x200000, 0x200000))
	m.write_u32(CTL + 0xc, [0, 1, -1, 100, -100, 0x10000, -0x10000][rng.randi_range(0, 6)] if seed % 3 == 0 else rng.randi_range(-50, 50))
	m.write_u16(CTL + 0x20, [0, 0x2c0, 0x7fff, 0x8000, 0x3fff, 0x2000][rng.randi_range(0, 5)] if seed % 2 else rng.randi_range(0, 0x3fff))
	m.write_u16(CTL + 0x22, rng.randi_range(0, 0x3fff))
	m.write_u16(CTL + 0x24, rng.randi_range(0, 0x3fff))
	m.write_u8(CTL + 0x67b, [0, 1, -1, 127, -128][rng.randi_range(0, 4)])
	m.write_u8(CTL + 0x679, [0, 1, -1, 127, -128][rng.randi_range(0, 4)])
	m.write_u16(CTL + 0x642, rng.randi_range(0, 3))
	m.write_u8(CTL + 0x685, rng.randi_range(0, 255) & 0xfe)
	m.write_u32(0x1b838, 1000)
	m.write_u32(CTL + 0x6ac, 1000 + rng.randi_range(-2, 2))
	m.write_u8(CTL + 0x67c, [0, 1, 4][rng.randi_range(0, 2)])
	m.write_u8(CTL + 0x674, [0, 0, 2][rng.randi_range(0, 2)])

func _pair(seed: int) -> Array:
	var mo = _memory()
	var mn = _memory()
	_setup(mo, seed)
	_setup(mn, seed)
	var a := OriginalAI.new(mo)
	var b := NativeAI.new(mn)
	a.a7 = 0x3f000
	b.a7 = 0x3f000
	var r := RandomNumberGenerator.new()
	r.seed = seed + 99
	for k in 3:
		var v: int = r.randi_range(0, 0xffff)
		a.rng_script.append(v)
		b.rng_script.append(v)
	return [a, b, mo, mn]

func run() -> bool:
	_image = FileAccess.get_file_as_bytes(SCENERY)
	var ok := true
	var bad: Array = []
	var done := 0
	for seed in 150:
		var p := _pair(seed)
		var ra: int = p[0].ai_bootstrap_climb_phase(CTL)
		var rb: int = p[1].ai_bootstrap_climb_phase(CTL)
		done += ra
		if ra != rb or p[2]._bytes != p[3]._bytes or p[0].a7 != p[1].a7:
			bad.append(seed)
	ok = Assertions.assert_equal(bad, [], "ai_bootstrap_climb_phase: same result and memory (%d done)" % done) and ok
	bad.clear()
	var spawns := 0
	for seed in 150:
		var p := _pair(seed + 1000)
		var ra: int = p[0].ai_script_try_fire_burst(CTL)
		var rb: int = p[1].ai_script_try_fire_burst(CTL)
		for c in p[0].calls:
			spawns += 1 if c[0] == "spawn" else 0
		if ra != rb or p[2]._bytes != p[3]._bytes or p[0].calls != p[1].calls or p[0].a7 != p[1].a7:
			bad.append(seed)
	ok = Assertions.assert_equal(bad, [], "ai_script_try_fire_burst: same result, memory, calls") and ok
	ok = Assertions.assert_true(spawns > 5, "bursts fired (%d)" % spawns) and ok
	bad.clear()
	var low := 0
	for seed in 200:
		var p := _pair(seed + 2000)
		var r := RandomNumberGenerator.new()
		r.seed = seed
		var far: int = [0x100, 0x7000, 0x9000, 0xffff, 0x20000, 0x150000][r.randi_range(0, 5)]
		var tx: int = p[2].read_s32(OBJ + 0x18) + r.randi_range(-far, far)
		var ty: int = p[2].read_s32(OBJ + 0x1c) + r.randi_range(-0x3000, 0x3000)
		var tz: int = p[2].read_s32(OBJ + 0x20) + r.randi_range(-far, far)
		var ra: int = p[0].build_relative_steer_sample(CTL, tx, ty, tz, OUT, VEC, OUT + 8, OUT + 10)
		var rb: int = p[1].build_relative_steer_sample(CTL, tx, ty, tz, OUT, VEC, OUT + 8, OUT + 10)
		low += ra
		if ra != rb or p[2]._bytes != p[3]._bytes or p[0].calls != p[1].calls or p[0].a7 != p[1].a7:
			bad.append(seed)
	ok = Assertions.assert_equal(bad, [], "build_relative_steer_sample: same result, vector, angles, frame") and ok
	return Assertions.assert_true(low > 10 and low < 190, "both height branches ran (%d low)" % low) and ok
