extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const NativeState := preload("res://tests/helpers/native_state.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")

## The per-frame object pass on the typed views against the Original port, from the same bytes:
## - collect_near_objects over 60 generated near lists (in / out of range by the 0x19c7f margin,
##   hidden, kinds 2 / 3, long +4 bits 0x40 / 4, recent and stale +0x3c, positions and camera at
##   the 32-bit extremes, scale shifts 0..4; metrics and extents kept non-negative, as the game's
##   are: a negative detail size never leaves the original's shift loop, in both), existing view points / visible / far entries): same
##   memory byte for byte and the same retire / deactivate calls in the same order at the same a7
##   (both stubbed alike, logging);
## - _sort_far_objects over 60 generated object pools (0..30 objects, equal metrics for stability,
##   carried pairs 0..4 with carriers above / below the camera and riders on both sides): same
##   memory byte for byte (order, restored metrics, +0x28 saves, pair count cleared) and same a7;
## - collect_far_objects over 80 generated pools (the near cases plus long +4 bits 0x8000 / 0x2800 /
##   0x800 / 0x100000, byte +7 bit 0x40, overlapping metric - extent intervals): same memory (lists,
##   +0x28 minima, word +6, the active list in the caller's frame) and the same retire / deactivate /
##   broad-phase calls (stubbed alike) in the same order at the same a7;
## - build_visible_object_list over 80 generated visible lists (view points in front, behind and
##   at the edges, the camera inside, byte +7 bit 2, projection shifts 0..20, the near / far split
##   across the whole list, a random camera matrix): same memory, same a7;
## - update_object_distance_band for every band 1..8 against distances around every limit and the
##   extremes: same result and band word.

class OriginalPass extends "res://original/port/pacific_conflict.gd":
	var calls: Array = []
	func _far_collision(proximity: bool, player: bool, a: int, b: int) -> void:
		calls.append(["collide", proximity, player, a, b, a7])
	func retire_entity(obj: int, _d0_in: int = 0) -> int:
		calls.append(["retire", obj, a7])
		return 0
	func deactivate_object(obj: int) -> void:
		calls.append(["deactivate", obj, a7])
		memory.write_u32(obj + 4, memory.read_u32(obj + 4) | 0x1000)   # a side effect to compare

class NativePass extends "res://native/state/native_overlay.gd":
	var calls: Array = []
	func _far_collision(proximity: bool, player: bool, a: int, b: int) -> void:
		calls.append(["collide", proximity, player, a, b, a7])
	func retire_entity(obj: int, _d0_in: int = 0) -> int:
		calls.append(["retire", obj, a7])
		return 0
	func deactivate_object(obj: int) -> void:
		calls.append(["deactivate", obj, a7])
		memory.write_u32(obj + 4, memory.read_u32(obj + 4) | 0x1000)

const LIST := 0x30000
const COUNT := 0x30400
const OBJECTS := 0x31000
const EXTREMES := [0, 1, -1, 0x7fffffff, -0x80000000, 0x12345, -0x54321, 0x3fffffff]

func _setup_near(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed
	var n: int = rng.randi_range(0, 24)
	for axis in 3:
		m.write_u32(0x1d830 + 4 * axis, EXTREMES[rng.randi_range(0, 7)] if seed % 3 == 0 else rng.randi_range(-0x100000, 0x100000))
	m.write_u32(0x1b838, rng.randi_range(0, 0x200))                        # frame counter
	m.write_u32(0x1c1d0, rng.randi_range(0, 3))                            # view points already
	m.write_u32(0x1c1d4, rng.randi_range(0, 3))                            # visible already
	m.write_u32(0x1c1dc, rng.randi_range(0, 3))                            # far already
	m.write_u32(COUNT, (rng.randi_range(0, 0xffff) << 16) | n)             # high word ignored
	for i in n:
		var obj: int = OBJECTS + i * 0x80
		m.write_u32(LIST + 4 * i, obj)
		m.write_u8(obj, [2, 2, 3, 1][rng.randi_range(0, 3)])
		m.write_u32(obj + 4, [0, 0x40, 4, 0x44, 0x80000][rng.randi_range(0, 4)])
		m.write_u8(obj + 7, [0, 0, 1, 2, 3][rng.randi_range(0, 4)])
		var range_: int = rng.randi_range(0, 0x80000)
		var metric: int = maxi(0, range_ + [-5, 0, 1, 0x19c7f, 0x19c80, 0x50000, -0x40000][rng.randi_range(0, 6)])
		if seed % 5 == 0:   # a metric is a sum of magnitudes: never negative in the game
			metric = [0, 1, 0x7fffffff, 0x3fffffff, 0x12345][rng.randi_range(0, 4)]
		m.write_u32(obj + 0xc, range_)
		m.write_u32(obj + 0x10, metric)
		m.write_u32(obj + 0x14, rng.randi_range(0, 0x40000) if seed % 7 else [0, 1, 0x12345, 0x7ffff][rng.randi_range(0, 3)])
		for axis in 3:
			m.write_u32(obj + 0x18 + 4 * axis, EXTREMES[rng.randi_range(0, 7)] if seed % 4 == 0 else rng.randi_range(-0x200000, 0x200000))
		m.write_u16(obj + 0x32, 0x5555)
		m.write_u16(obj + 0x34, rng.randi_range(0, 4))
		m.write_u32(obj + 0x3c, [0, m.read_u32(0x1b838), m.read_u32(0x1b838) - 0x4b, m.read_u32(0x1b838) - 0x4c, 0x7fffffff][rng.randi_range(0, 4)])

func run() -> bool:
	var ok := true
	var bad: Array = []
	var calls := 0
	for seed in 60:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		_setup_near(mo, seed)
		_setup_near(mn, seed)
		var a := OriginalPass.new(mo)
		var b := NativePass.new(mn)
		NativeState.seed_and_poison(b, mn)
		a.a7 = 0x3f000
		b.a7 = 0x3f000
		a.collect_near_objects(LIST, COUNT)
		b.collect_near_objects(LIST, COUNT)
		calls += a.calls.size()
		if NativeState.compare(b, mo, mn) != "" or a.calls != b.calls or a.a7 != b.a7:
			bad.append(seed)
	ok = Assertions.assert_equal(bad, [], "collect_near_objects: same memory, same calls, same a7") and ok
	ok = Assertions.assert_true(calls > 20, "the lists exercised retire / deactivate (%d calls)" % calls) and ok
	return _visible() and _far_pass() and _far_sort() and _bands() and ok

func _setup_visible(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed + 2000
	var n: int = rng.randi_range(0, 40)
	var points: int = 0
	for k in 9:
		m.write_u16(0x1d744 + 2 * k, rng.randi_range(-0x1000, 0x1000))
	m.write_u32(0x1c1d4, (rng.randi_range(0, 0xffff) << 16) | n)          # the low word is counted
	m.write_u16(0x1c1c0, rng.randi_range(0, n))
	for i in n:
		var obj: int = OBJECTS + i * 0x80
		m.write_u32(0x1c9dc + 4 * i, obj)
		var bits: int = 4 if rng.randi_range(0, 5) == 0 else rng.randi_range(0, 3)
		m.write_u32(obj + 4, rng.randi_range(0, 0xff) << 8 | bits)
		m.write_u32(obj + 0x10, rng.randi_range(0, 0x100000))
		m.write_u32(obj + 0x14, rng.randi_range(0, 0x80000) if rng.randi_range(0, 3) else 0x7fffffff)
		m.write_u16(obj + 0x32, rng.randi_range(0, 20))
		if (bits & 4) == 0:
			for axis in 3:
				m.write_u32(0x1c43c + 12 * points + 4 * axis, rng.randi_range(-0x3000, 0x3000) if axis < 2 else rng.randi_range(-0x1000, 0x6000))
			points += 1
	m.write_u32(0x1c1d0, points)

func _visible() -> bool:
	var bad: Array = []
	for seed in 80:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		_setup_visible(mo, seed)
		_setup_visible(mn, seed)
		var a := OriginalPass.new(mo)
		var b := NativePass.new(mn)
		NativeState.seed_and_poison(b, mn)
		a.a7 = 0x3f000
		b.a7 = 0x3f000
		a.build_visible_object_list()
		b.build_visible_object_list()
		if NativeState.compare(b, mo, mn) != "" or a.a7 != b.a7:
			bad.append(seed)
	return Assertions.assert_equal(bad, [], "build_visible_object_list: same kept list, flags, counts and points")

func _far_pass() -> bool:
	var bad: Array = []
	var collisions := 0
	for seed in 80:
		var ms: Array = []
		for e in 2:
			var m = Memory68k.new(0x40000)
			_setup_near(m, seed + 500)
			var rng := RandomNumberGenerator.new()
			rng.seed = seed
			m.write_u32(0x1c1c4, rng.randi_range(0, 2))
			for i in m.read_u16(COUNT + 2):
				var obj: int = OBJECTS + i * 0x80
				var extra: int = [0, 0x8000, 0x2000, 0x800, 0x2800, 0x100000, 0x108800][rng.randi_range(0, 6)]
				m.write_u32(obj + 4, (m.read_u32(obj + 4) & 0x46) | extra | (0x40 if rng.randi_range(0, 4) == 0 else 0))
				m.write_u32(obj + 0x14, rng.randi_range(0, 0x60000))
				m.write_u32(obj + 0x28, rng.randi_range(-0x10000, 0x80000))
			ms.append(m)
		var a := OriginalPass.new(ms[0])
		var b := NativePass.new(ms[1])
		NativeState.seed_and_poison(b, ms[1])
		a.a7 = 0x3f000
		b.a7 = 0x3f000
		a.collect_far_objects(LIST, COUNT)
		b.collect_far_objects(LIST, COUNT)
		for c in a.calls:
			collisions += 1 if c[0] == "collide" else 0
		if NativeState.compare(b, ms[0], ms[1]) != "" or a.calls != b.calls or a.a7 != b.a7:
			bad.append(seed)
	var ok := Assertions.assert_equal(bad, [], "collect_far_objects: same memory, same calls in order, same a7")
	return Assertions.assert_true(collisions > 50, "the pools exercised the broad phase (%d tests)" % collisions) and ok

func _setup_pool(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed + 1000
	var n: int = rng.randi_range(0, 30)
	m.write_u32(0x1b734, n)
	for axis in 3:
		m.write_u32(0x1d830 + 4 * axis, rng.randi_range(-0x100000, 0x100000))
	for i in n:
		var obj: int = OBJECTS + i * 0x80
		m.write_u32(0x1b5a4 + 4 * i, obj)
		var spot: int = rng.randi_range(-0x40000, 0x40000) if seed % 3 else (rng.randi_range(0, 3) * 0x10000)
		for axis in 3:
			m.write_u32(obj + 0x18 + 4 * axis, spot + axis * rng.randi_range(0, 2) * 0x100)
		m.write_u32(obj + 0x14, rng.randi_range(0, 0x20000))
		m.write_u32(obj + 8, 0x38000 + i * 0x80)
		m.write_u16(0x38000 + i * 0x80 + 0x54, rng.randi_range(-0x8000, 0x7fff) if seed % 2 else rng.randi_range(-0x100, 0x100))
	var pairs: int = 0 if n < 2 or seed % 4 == 0 else rng.randi_range(1, 4)
	m.write_u16(0x1b85c, pairs)
	m.write_u32(0x1b860, 0x3c000)
	for k in pairs:
		m.write_u32(0x3c000 + 8 * k, OBJECTS + rng.randi_range(0, n - 1) * 0x80)
		m.write_u32(0x3c000 + 8 * k + 4, OBJECTS + rng.randi_range(0, n - 1) * 0x80)

func _far_sort() -> bool:
	var bad: Array = []
	var paired := 0
	for seed in 60:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		_setup_pool(mo, seed)
		_setup_pool(mn, seed)
		paired += 1 if mo.read_u16(0x1b85c) != 0 else 0
		var a := OriginalPass.new(mo)
		var b := NativePass.new(mn)
		NativeState.seed_and_poison(b, mn)
		a.a7 = 0x3f000
		b.a7 = 0x3f000
		a._sort_far_objects(0x3e000)
		b._sort_far_objects(0x3e000)
		if NativeState.compare(b, mo, mn) != "" or a.a7 != b.a7:
			bad.append(seed)
	return Assertions.assert_equal(bad, [], "_sort_far_objects: same order and memory (%d pools with carried pairs)" % paired)

func _bands() -> bool:
	var limits := [-0x80000000, 0, 0x1000, 0x4000, 0x10000, 0x40000, 0x100000, 0x400000, 0x1000000, 0x7fffffff]
	var dists: Array = [-0x80000000, -1, 0x7ffffffe]
	for v in limits:
		dists += [v - 1, v, v + 1]
	var bad: Array = []
	for band in range(1, 9):
		for dist in dists:
			if dist >= 0x7fffffff or dist < -0x80000000:
				continue
			var mo = Memory68k.new(0x40000)
			var mn = Memory68k.new(0x40000)
			for m in [mo, mn]:
				for k in limits.size():
					m.write_u32(0x1b190 - 4 + 4 * k, limits[k])
				m.write_u16(OBJECTS + 0x30, band)
			var a = load("res://original/port/pacific_conflict.gd").new(mo)
			var b = load("res://native/state/native_overlay.gd").new(mn)
			NativeState.seed_and_poison(b, mn)
			if a.update_object_distance_band(OBJECTS, dist) != b.update_object_distance_band(OBJECTS, dist) or NativeState.compare(b, mo, mn) != "":
				bad.append([band, dist])
	return Assertions.assert_equal(bad, [], "update_object_distance_band: same result and band word")
