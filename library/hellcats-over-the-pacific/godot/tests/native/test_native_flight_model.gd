extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const OriginalOverlay := preload("res://original/port/pacific_conflict.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")

const REC := 0x30000
const OUT := 0x31000

## The flight-model leaves of update_aircraft_entity on the aircraft type view against the Original
## port, from the same bytes: compute_template_force_from_626 for the four aircraft type records of
## the data file and 40 generated ones (words at the extremes) over angles of attack -0x400..0x400
## around every stall angle and every flaps byte -16..16; scale_template_field_38 over densities
## -0x8000..0x7fff (but the zero divisors, which stop both) and rated powers at the long extremes;
## damp_short_if_below_0x200 over heights around 0x200 and the extremes and every density step.
## Same results and memory (the stall marker byte), byte for byte.
func run() -> bool:
	var ok := true
	var bad: Array = []
	var stalled := 0
	var records: Array = []
	var image: PackedByteArray = FileAccess.get_file_as_bytes("res://original/mac/data/scenery/Pacific Conflict")
	for file_off in [0x1a7b0, 0x1a88e, 0x1a844, 0x1a7fa]:
		records.append(image.slice(file_off, file_off + 0x4a))
	var rng := RandomNumberGenerator.new()
	rng.seed = 7
	for k in 40:
		var r := PackedByteArray()
		for i in 0x4a:
			r.append(rng.randi_range(0, 255))
		if k % 4 == 0:
			for off in [0x26, 0x28, 0x2a, 0x2c]:
				var v: int = [0x7fff, 0x8000, 0xffff, 0][rng.randi_range(0, 3)]
				r[off] = v >> 8
				r[off + 1] = v & 0xff
		r[0x34] = 0
		r[0x35] = rng.randi_range(0, 0xff) if k % 3 else 0x7f
		records.append(r)
	for r in records:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		for m in [mo, mn]:
			for i in r.size():
				m.write_u8(REC + i, r[i])
			m.write_u32(0x26004, REC)
		var a = OriginalOverlay.new(mo)
		var b = NativeOverlay.new(mn)
		var stall: int = mo.read_s16(REC + 0x34)
		for aoa in range(-0x400, 0x401, 7) + [stall - 1, stall, stall + 1, stall + 0x1f, stall + 0x20, stall + 0x21, 0x7fff, -0x8000]:
			for flaps in range(-16, 17, 3) + [0, 15, -15]:
				mo.write_u8(OUT, 0)
				mn.write_u8(OUT, 0)
				var x: int = a.compute_template_force_from_626(aoa, flaps, OUT)
				var y: int = b.compute_template_force_from_626(aoa, flaps, OUT)
				stalled += 1 if mo.read_u8(OUT) == 0xc else 0
				if x != y or mo._bytes != mn._bytes:
					bad.append([records.find(r), aoa, flaps])
	ok = Assertions.assert_equal(bad.slice(0, 5), [], "compute_template_force_from_626: same lift and stall marker") and ok
	ok = Assertions.assert_true(stalled > 1000, "past-stall cases ran (%d)" % stalled) and ok
	bad.clear()
	for power in [0, 1, -1, 0x7fffffff, -0x80000000, 0x123456, 0x40000]:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		for m in [mo, mn]:
			m.write_u32(REC + 0x38, power)
			m.write_u32(0x26004, REC)
		var a = OriginalOverlay.new(mo)
		var b = NativeOverlay.new(mn)
		for density in range(-0x8000, 0x8000, 5):
			if _divisor(density) == 0:
				continue
			a.scale_template_field_38(density, OUT)
			b.scale_template_field_38(density, OUT)
			if mo.read_u32(OUT) != mn.read_u32(OUT):
				bad.append([power, density])
	ok = Assertions.assert_equal(bad.slice(0, 5), [], "scale_template_field_38: same power") and ok
	bad.clear()
	var a0 = OriginalOverlay.new(Memory68k.new(0x100))
	var b0 = NativeOverlay.new(Memory68k.new(0x100))
	for height in [-0x80000000, -0x10000, -1, 0, 1, 0x100, 0x1fe, 0x1ff, 0x200, 0x201, 0x7fffffff]:
		for density in range(-0x8000, 0x8000, 37) + [0x7fff, -0x8000, 0xffff, 0x10000]:
			if a0.damp_short_if_below_0x200(height, density) != b0.damp_short_if_below_0x200(height, density):
				bad.append([height, density])
	return Assertions.assert_equal(bad.slice(0, 5), [], "damp_short_if_below_0x200: same density") and ok

## The word divisor the leaf computes (a zero one stops both at the division).
func _divisor(density: int) -> int:
	var w: int = ((density + 0x105) >> 3) & 0xffff
	return w
