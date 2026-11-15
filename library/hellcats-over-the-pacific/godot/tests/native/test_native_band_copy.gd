extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")

## The Native band copy (native_overlay.gd copy_band_pixels, the host blit's CopyBits made Native)
## against the runtime's CopyBits (quickdraw.gd copy_bits, srcCopy) on the same bytes: depth 8 and
## 4, the destination clipped, the source rect partly outside the source's bounds, 4-bit pixels moved
## between nibble alignments. The flight's colour frames rarely take this path (host word +0x9e set:
## once a Flight School mission), so the traces' screen hashes do not exercise it; this test does.

const SRC := 0x300000
const DST := 0x340000

func _fill(mem, at: int, n: int, seed_: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed_
	for i in n:
		mem.write_u8(at + i, rng.randi() & 0xff)

func _bytes(mem, at: int, n: int) -> PackedByteArray:
	var out := PackedByteArray()
	for i in n:
		out.append(mem.read_u8(at + i))
	return out

func _case(m, label: String, depth: int, src_row: int, src_bounds: Rect2i, dst_row: int, dst_bounds: Rect2i,
		sr: Rect2i, dr: Rect2i, clip: Rect2i) -> bool:
	var mem = m.memory
	var qd = m.toolbox.quickdraw
	var src := [SRC, src_row, src_bounds, depth]
	var dst := [DST, dst_row, dst_bounds, depth]
	var n_dst: int = dst_row * dst_bounds.size.y
	_fill(mem, SRC, src_row * src_bounds.size.y, 7)
	_fill(mem, DST, n_dst, 11)
	var before: PackedByteArray = _bytes(mem, DST, n_dst)
	qd.copy_bits(src, dst, sr, dr, 0, clip)
	var runtime: PackedByteArray = _bytes(mem, DST, n_dst)
	for i in n_dst:
		mem.write_u8(DST + i, before[i])
	NativeOverlay.copy_band_pixels(mem, src, dst, sr, dr, clip)
	var native: PackedByteArray = _bytes(mem, DST, n_dst)
	var changed: bool = runtime != before
	return Assertions.assert_true(changed, label + ": the runtime's copy changes the destination") \
			and Assertions.assert_equal(native, runtime, label + ": Native band copy = runtime CopyBits")

func run() -> bool:
	var m = MacMachine.new()
	var big := Rect2i(-32768, -32768, 65535, 65535)
	var ok := _case(m, "depth 8, clipped right", 8, 64, Rect2i(0, 0, 64, 40), 80, Rect2i(0, -20, 80, 60),
			Rect2i(0, 0, 64, 12), Rect2i(8, 5, 64, 12), Rect2i(0, 0, 70, 60))
	ok = _case(m, "depth 8, source rows past its bounds", 8, 64, Rect2i(0, 0, 64, 40), 80, Rect2i(0, -20, 80, 60),
			Rect2i(0, 35, 64, 12), Rect2i(0, 10, 64, 12), big) and ok
	ok = _case(m, "depth 8, as the host blit (window at -20)", 8, 640, Rect2i(0, 0, 640, 46), 640, Rect2i(0, -20, 640, 48),
			Rect2i(0, 0, 640, 12), Rect2i(0, 12, 640, 12), Rect2i(0, 0, 640, 28)) and ok
	ok = _case(m, "depth 4, odd source to even destination", 4, 32, Rect2i(0, 0, 64, 20), 40, Rect2i(0, 0, 80, 30),
			Rect2i(1, 0, 30, 10), Rect2i(6, 3, 30, 10), big) and ok
	ok = _case(m, "depth 4, odd destination, clipped", 4, 32, Rect2i(0, 0, 64, 20), 40, Rect2i(0, 0, 80, 30),
			Rect2i(0, 2, 30, 10), Rect2i(7, 3, 30, 10), Rect2i(10, 0, 20, 50)) and ok
	print(("PASS" if ok else "FAIL") + " Native band copy = runtime CopyBits")
	return ok
