extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const NativeState := preload("res://tests/helpers/native_state.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const ReplayCameraState := preload("res://native/state/replay_camera_state.gd")
const SCENERY := "res://original/mac/data/scenery/Pacific Conflict"

## The replay camera path on the views against the Original port, over the overlay image (trig
## tables): begin_replay_view followed by update_tracking_camera every frame until the replay ends
## (80 sequences: player shots, designated / followed aircraft near and far, heading within or past
## 0x1000, holds, replay requests 1 / other, crashed or not, parachute or not, host +0x98 = 2),
## plus single calls of update_bailout_view (100), turn_camera_toward_point (150) and
## place_camera_behind_object (150). drop_contact_records_for_object, update_view_clip_bounds,
## stop_all_sound_channels and resolve_bailout_outcome are stubbed alike (logged with a7).
##
## Both Native backends of the replay camera block (replay_camera_state.gd) and the camera angle
## block (camera_angle_state.gd), switched together:
## - memory-backed: the Mac memory byte for byte as the Original's, same calls, same a7;
## - Godot-backed: the blocks seeded from the scenario, then their Mac bytes are overwritten with 0xa5 in the
##   Native memory. Native must still give the Original's results (so no read falls back to the
##   memory), leave those bytes 0xa5 (no write), match the Original's memory everywhere else, and
##   hold in Godot exactly the Original's bytes for the block (captured from the state object).

class OriginalCam extends "res://original/port/pacific_conflict.gd":
	var calls: Array = []
	var outcomes: Array = []
	func place_camera_behind_object(o: int, x: int, y: int, z: int, shift: int) -> void:
		calls.append(["place", o, x, y, z, shift, a7])
		super(o, x, y, z, shift)
	func turn_camera_toward_point(x: int, y: int, z: int) -> void:
		calls.append(["turn", x, y, z, a7])
		super(x, y, z)
	func drop_contact_records_for_object(o: int) -> void:
		calls.append(["drop", o, a7])
	func update_view_clip_bounds() -> void:
		calls.append(["clip", a7])
	func stop_all_sound_channels() -> void:
		calls.append(["sound", a7])
	func resolve_bailout_outcome() -> int:
		calls.append(["outcome", a7])
		return outcomes.pop_front()

class NativeCam extends "res://native/state/native_overlay.gd":
	var calls: Array = []
	var outcomes: Array = []
	func place_camera_behind_object(o: int, x: int, y: int, z: int, shift: int) -> void:
		calls.append(["place", o, x, y, z, shift, a7])
		super(o, x, y, z, shift)
	func turn_camera_toward_point(x: int, y: int, z: int) -> void:
		calls.append(["turn", x, y, z, a7])
		super(x, y, z)
	func drop_contact_records_for_object(o: int) -> void:
		calls.append(["drop", o, a7])
	func update_view_clip_bounds() -> void:
		calls.append(["clip", a7])
	func stop_all_sound_channels() -> void:
		calls.append(["sound", a7])
	func resolve_bailout_outcome() -> int:
		calls.append(["outcome", a7])
		return outcomes.pop_front()

const PLAYER_CTL := 0x30000
const PLAYER_OBJ := 0x31000
const FOLLOW_CTL := 0x32000
const TARGET_OBJ := 0x33000
const SHOT_CTL := 0x34000
const SHOT_OBJ := 0x34800
const CHUTE_CTL := 0x35000
const CHUTE_OBJ := 0x36000
const HOST := 0x37000
var _image := PackedByteArray()
var _template := PackedByteArray()
var seen := {}

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

func _place(m, rng: RandomNumberGenerator, obj: int, base: Array, spread: int) -> void:
	for k in 3:
		m.write_u32(obj + 0x18 + 4 * k, base[k] + rng.randi_range(-spread, spread))

func _setup(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed
	var base := [rng.randi_range(-0x400000, 0x400000), rng.randi_range(0x400, 0x20000), rng.randi_range(-0x400000, 0x400000)]
	m.write_u32(0x1b738, PLAYER_CTL)
	m.write_u32(PLAYER_CTL, PLAYER_OBJ)
	for off in [0x20, 0x22, 0x24]:
		m.write_u16(PLAYER_CTL + off, rng.randi_range(0, 0x3fff))
	m.write_u32(PLAYER_CTL + 0x2e, base[1] - rng.randi_range(0, 0x2000))
	m.write_u16(PLAYER_CTL + 0x612, rng.randi_range(0, 0x4a))
	for i in 0x4b:
		var e: int = PLAYER_CTL + 0x36 + i * 0x14
		for k in 3:
			m.write_u32(e + 4 * k, base[k] + rng.randi_range(-0x3000, 0x3000))
		m.write_u16(e + 0xe, rng.randi_range(0, 0x3fff))
	m.write_u8(PLAYER_CTL + 0x684, _pick(rng, [0, 4, 0xff]))
	_place(m, rng, PLAYER_OBJ, base, 0x2000)
	m.write_u8(PLAYER_OBJ + 7, _pick(rng, [0, 0x40, 0x42]))
	m.write_u32(PLAYER_OBJ + 4, rng.randi_range(0, 0x7fffffff))
	m.write_u32(FOLLOW_CTL, _pick(rng, [TARGET_OBJ, TARGET_OBJ, TARGET_OBJ, 0]))
	m.write_u16(FOLLOW_CTL + 0x22, (m.read_u16(PLAYER_CTL + 0x22) + _pick(rng, [0, 0x800, 0x1000, 0x1001, 0x2000, -0x1000])) & 0x3fff)
	for off in [0x20, 0x24]:
		m.write_u16(FOLLOW_CTL + off, rng.randi_range(0, 0x3fff))
	_place(m, rng, TARGET_OBJ, base, _pick(rng, [0x3000, 0x8000, 0x30000]))
	m.write_u32(0x1b888, _pick(rng, [0, FOLLOW_CTL, FOLLOW_CTL]))
	m.write_u32(0x1b884, _pick(rng, [0, 0, FOLLOW_CTL]))
	m.write_u32(0x24d1c, _pick(rng, [0, 0, 5, 1]))
	m.write_u32(0x1b880, _pick(rng, [0, 0, 0, SHOT_CTL]))
	m.write_u32(SHOT_CTL, SHOT_OBJ)
	_place(m, rng, SHOT_OBJ, base, 0x4000)
	m.write_u32(0x1b838, 1000)
	m.write_u32(SHOT_OBJ + 0x40, 1000 + _pick(rng, [-5, 0, 5]))
	m.write_u32(0x1b87c, _pick(rng, [0, CHUTE_CTL, CHUTE_CTL]))
	m.write_u32(CHUTE_CTL, CHUTE_OBJ)
	m.write_u32(CHUTE_CTL + 0x2e, base[1] - 0x1000)
	m.write_u16(CHUTE_CTL + 0x60e, rng.randi_range(0, 0x4a))
	m.write_u16(CHUTE_CTL + 0x61e, _pick(rng, [0, 1]))
	m.write_u16(CHUTE_CTL + 0x614, _pick(rng, [0, 0x5a0, 0x5a1]))
	for i in 0x4b:
		for k in 3:
			m.write_u32(CHUTE_CTL + 0x32 + i * 0x14 + 4 * k, base[k] + rng.randi_range(-0x3000, 0x3000))
	_place(m, rng, CHUTE_OBJ, base, _pick(rng, [0x2000, 0x8000, 0x30000]))
	m.write_u8(CHUTE_OBJ + 7, _pick(rng, [0, 0, 2]))
	m.write_u32(CHUTE_OBJ + 0x40, 1000 + _pick(rng, [-0x60, -0x4b, 0]))
	m.write_u32(0x1b5a0, HOST)
	m.write_u16(HOST + 0x98, _pick(rng, [0, 1, 2]))
	m.write_u16(0x1b86c, _pick(rng, [0, 1, 2]))
	m.write_u16(0x1b86e, _pick(rng, [0x69, 0x40, 0x1f, 1]))
	m.write_u16(0x1b870, _pick(rng, [1, 3]))
	m.write_u16(0x1d858, rng.randi_range(0, 8))
	m.write_u32(0x1d838, base[0] + rng.randi_range(-0x3000, 0x3000))
	m.write_u32(0x1d834, base[1] + rng.randi_range(-0x1000, 0x3000))
	m.write_u32(0x1d830, base[2] + rng.randi_range(-0x3000, 0x3000))
	for a in [0x1d80c, 0x1d80e, 0x1d810, 0x24d18, 0x24d1a]:
		m.write_u16(a, rng.randi_range(0, 0xffff))
	for k in 3:
		m.write_u32(0x1d814 + 4 * k, rng.randi_range(-0x200, 0x200))
		m.write_u32(0x24d20 + 4 * k, base[2 - k] + rng.randi_range(-0x3000, 0x3000))

## Runs one scenario in both editions; returns "" or what differed.
func _case(seed: int, kind: String, godot_backed: bool) -> String:
	var mo = _memory()
	var mn = _memory()
	_setup(mo, seed)
	_setup(mn, seed)
	var a := OriginalCam.new(mo)
	var b := NativeCam.new(mn)
	b.replay_camera_storage_godot = godot_backed
	b.camera_angle_storage_godot = godot_backed
	b.camera_position_storage_godot = godot_backed
	b.view_state_storage_godot = godot_backed
	a.a7 = 0x3f000
	b.a7 = 0x3f000
	for k in 4:
		a.outcomes.append([2, 3, -1, 0][(seed + k) % 4])
		b.outcomes.append([2, 3, -1, 0][(seed + k) % 4])
	NativeState.seed_and_poison(b, mn)   # no-op for the memory-backed blocks
	var r := RandomNumberGenerator.new()
	r.seed = seed * 3 + 1
	var ra: Array = []
	var rb: Array = []
	match kind:
		"replay":
			a.begin_replay_view()
			b.begin_replay_view()
			for f in 0x6a:
				if mo.read_u16(0x1b86e) == 0x1e and mo.read_s16(0x1b870) != 3 and mo.read_u32(0x1b884) != 0:
					break   # the original's divs.l by zero (frames_left - 0x1e): not a game state
				ra.append(a.update_tracking_camera())
				rb.append(b.update_tracking_camera())
				for obj in [PLAYER_OBJ, TARGET_OBJ]:   # the world moves alike in both
					var dx: int = r.randi_range(-0x80, 0x80)
					for m in [mo, mn]:
						m.write_u32(obj + 0x18, m.read_u32(obj + 0x18) + dx)
				if f == 10 and seed % 4 == 1:
					mo.write_u32(0x1b888, FOLLOW_CTL)
					mn.write_u32(0x1b888, FOLLOW_CTL)
				if mo.read_u16(0x1b86e) == 0:
					break
		"bailout":
			ra.append(a.update_bailout_view())
			rb.append(b.update_bailout_view())
		"turn":
			var p := [mo.read_s32(0x1d838) + r.randi_range(-0x40000, 0x40000), mo.read_s32(0x1d834) + r.randi_range(-0x8000, 0x8000), mo.read_s32(0x1d830) + r.randi_range(-0x40000, 0x40000)]
			for rep in 3:
				a.turn_camera_toward_point(p[0], p[1], p[2])
				b.turn_camera_toward_point(p[0], p[1], p[2])
		"place":
			var o := TARGET_OBJ
			var p := [mo.read_s32(o + 0x18) + r.randi_range(-0x4000, 0x4000), mo.read_s32(o + 0x1c) + r.randi_range(-0x4000, 0x4000), mo.read_s32(o + 0x20) + r.randi_range(-0x4000, 0x4000)]
			if seed % 7 == 0:
				p = [mo.read_s32(o + 0x18), mo.read_s32(o + 0x1c), mo.read_s32(o + 0x20)]
			var shift: int = r.randi_range(0, 2)
			a.place_camera_behind_object(o, p[0], p[1], p[2], shift)
			b.place_camera_behind_object(o, p[0], p[1], p[2], shift)
	for c in a.calls:
		seen[c[0]] = seen.get(c[0], 0) + 1
	if kind == "replay":
		seen["frames"] = seen.get("frames", 0) + ra.size()
		for v in ra:
			seen["result %d" % v] = seen.get("result %d" % v, 0) + 1
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
		for spec in [["replay", 80], ["bailout", 100], ["turn", 150], ["place", 150]]:
			for seed in spec[1]:
				var why := _case(seed, spec[0], godot_backed)
				if why != "":
					bad.append([spec[0], seed, why])
		ok = Assertions.assert_equal(bad.slice(0, 6), [], "replay camera path (%s): same results, calls, a7, memory%s" % ["Godot-backed block" if godot_backed else "memory-backed block", ", Godot values" if godot_backed else ""]) and ok
	ok = Assertions.assert_true(seen.get("frames", 0) > 1000 and seen.get("place", 0) > 50 and seen.get("outcome", 0) > 0
			and seen.get("result -1", 0) > 0 and seen.get("result 1", 0) > 0 and seen.get("drop", 0) > 0,
			"branches reached: %s" % str(seen)) and ok
	return ok
