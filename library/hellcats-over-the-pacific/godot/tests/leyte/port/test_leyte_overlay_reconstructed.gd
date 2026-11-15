extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68kScript := preload("res://original/compat/memory_68k.gd")
const LeyteOverlayPort := preload("res://tests/leyte/port/helpers/leyte_overlay_reconstructed_port.gd")

## Leyte Gulf's overlay functions written from Leyte's bytes (original/leyte/port/pacific_conflict_reconstructed.gd), against Leyte's own overlay on the 68000 emulator; the same replay as test_leyte_overlay.gd (the functions
## tools/leyte/gen_overlay_port.py keeps) against Leyte's own overlay on the 68000 emulator:
## fixtures/overlay_reconstructed_cases.json (tools/leyte/gen_leyte_overlay_cases.py, 1.0.3's overlay cases
## translated through overlay_map.json). Each case starts from Leyte's image (the data fork, runtime =
## file - 0x20; neither side seeds the overlay RNG), clears its zero ranges, applies its writes, runs the
## function and must change exactly the bytes Leyte's code changed outside the stack guard, with a7
## restored and D0 where the case records it.

const FIXTURE := "res://tests/leyte/port/fixtures/overlay_reconstructed_cases.json"
const DATA_FORK := "res://assets/leyte-gulf-1.0/data_fork.bin"

func run() -> bool:
	var ok := true
	var fixture: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(FIXTURE))
	var fork := FileAccess.get_file_as_bytes(DATA_FORK)
	var size: int = int(fixture["memory_size"])
	# The image as the loader leaves it: text and data (header +4 / +8), the bss and the rest zero.
	var text: int = (fork[4] << 24) | (fork[5] << 16) | (fork[6] << 8) | fork[7]
	var data: int = (fork[8] << 24) | (fork[9] << 16) | (fork[10] << 8) | fork[11]
	var image := fork.slice(0x20, 0x20 + text + data)
	image.resize(size)
	var lo: int = int(fixture["stack_guard"][0])
	var hi: int = int(fixture["stack_guard"][1])
	for c in fixture["cases"]:
		var mem = Memory68kScript.new(size)
		mem._bytes = image.duplicate()
		var p = LeyteOverlayPort.new(mem)
		p.install_call_logs(c.get("call_logs", []))
		var entry_a7: int = int(fixture["stack_top"]) - 4 - 4 * c["args"].size()
		p.a7 = entry_a7
		for r in c["zero"]:
			for addr in range(int(r[0]), int(r[1])):
				mem._bytes[addr] = 0
		for w in c["writes"]:
			match int(w[1]):
				1: mem.write_u8(int(w[0]), int(w[2]))
				2: mem.write_u16(int(w[0]), int(w[2]))
				_: mem.write_u32(int(w[0]), int(w[2]))
		var before: PackedByteArray = mem._bytes.duplicate()
		var result = p.callv(String(c["method"]), c["args"].map(func(v): return int(v)))
		var got := []
		for addr in range(size):
			if addr >= lo and addr < hi:
				continue
			if mem._bytes[addr] != before[addr]:
				got.append([addr, mem._bytes[addr]])
		var want: Array = c["changes"].map(func(x): return [int(x[0]), int(x[1])])
		ok = Assertions.assert_equal(got == want, true, "Leyte overlay (reconstructed): %s (%d bytes)" % [c["name"], want.size()]) and ok
		ok = Assertions.assert_equal(p.a7, entry_a7, "a7 restored: %s" % c["name"]) and ok
		if c.has("d0"):
			ok = Assertions.assert_equal(int(result) & 0xffffffff, int(c["d0"]), "D0: %s" % c["name"]) and ok
	return ok
