extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const OriginalOverlay := preload("res://original/port/pacific_conflict.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")
const SCENERY := "res://original/mac/data/scenery/Pacific Conflict"

## The Native overlay's typed trigonometry tables against the Original's reads of the overlay image:
## sin / cos for every angle from -0x8000 to 0x7fff and a few far beyond (indices outside the table
## read the image there, in both), acos over -0x1100..0x1100, atan over ratios from -2^20 to 2^20 and
## the extremes, and the altitude curve over -0x40000..0x9ffff (bands below 0 read the image, above 7
## clamp); every result equal.
func run() -> bool:
	var image: PackedByteArray = FileAccess.get_file_as_bytes(SCENERY)
	var m = Memory68k.new(0x40000)
	for k in mini(image.size() - 0x20, 0x3e700):
		m.write_u8(k, image[0x20 + k])
	var o = OriginalOverlay.new(m)
	var n = NativeOverlay.new(m)
	var ok := true
	var bad: Array = []
	for angle in range(-0x8000, 0x8000):
		if n.sin_fixed14(angle) != o.sin_fixed14(angle) or n.cos_fixed14(angle) != o.cos_fixed14(angle):
			bad.append(["sin/cos", angle])
	for angle in [0x10000, -0x10000, 0x123456, -0x123456]:
		if n.sin_fixed14(angle) != o.sin_fixed14(angle) or n.cos_fixed14(angle) != o.cos_fixed14(angle):
			bad.append(["sin/cos", angle])
	for x in range(-0x1100, 0x1100):
		if n.acos_fixed14(x) != o.acos_fixed14(x):
			bad.append(["acos", x])
	for ratio in range(-0x100000, 0x100000, 13) + [0x7fffffff, -0x7fffffff, 0]:
		if n.atan_fixed14(ratio) != o.atan_fixed14(ratio):
			bad.append(["atan", ratio])
	for altitude in range(-0x40000, 0xa0000, 3):
		if n.table_lerp_1b2c0(altitude) != o.table_lerp_1b2c0(altitude):
			bad.append(["altitude", altitude])
	ok = Assertions.assert_equal(bad.slice(0, 5), [], "every result equal") and ok
	return ok
