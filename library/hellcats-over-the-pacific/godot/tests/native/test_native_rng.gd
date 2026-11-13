extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const OriginalOverlay := preload("res://original/port/pacific_conflict.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")

## The Native overlay's typed RNG against the Original's in the Mac memory: from the same ring bytes,
## outputs drawn before any seeding, then seeded with several seeds, give the same numbers, and the
## ring and cursor end as the Original leaves them in memory; the Native never writes that memory.
func run() -> bool:
	var ok := true
	for seed in [0, 1, 0x12345678, 0xfffffffe]:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		for m in [mo, mn]:
			for k in 0xe4:
				m.write_u8(0x26008 + k, (k * 73 + 11) & 0xff)
			m.write_u32(0x26008, 0x2600c + 4 * 20)
		var o = OriginalOverlay.new(mo)
		var n = NativeOverlay.new(mn)
		var before: PackedByteArray = mn._bytes.slice(0x26008, 0x260ec)
		var a: Array = []
		var b: Array = []
		for i in 200:
			a.append(o.overlay_rng_next())
			b.append(n.overlay_rng_next())
		o.overlay_rng_init(seed)
		n.overlay_rng_init(seed)
		for i in 3000:
			a.append(o.overlay_rng_next())
			b.append(n.overlay_rng_next())
		ok = Assertions.assert_equal(b, a, "seed %x: the same numbers" % seed) and ok
		ok = Assertions.assert_equal(n.rng_bytes(), mo._bytes.slice(0x26008, 0x260e8), "seed %x: ring and cursor" % seed) and ok
		ok = Assertions.assert_equal(mn._bytes.slice(0x26008, 0x260ec), before, "seed %x: Mac memory untouched" % seed) and ok
	return ok
