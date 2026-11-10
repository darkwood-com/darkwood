extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68kScript := preload("res://strict_port/compat/memory_68k.gd")

func run() -> bool:
	var ok := true
	var memory = Memory68kScript.new(16, 0x1000)
	memory.write_u32(0x1000, 0x89abcdef)
	ok = Assertions.assert_equal(memory.read_u8(0x1000), 0x89, "u32 stores big-endian byte 0") and ok
	ok = Assertions.assert_equal(memory.read_u8(0x1003), 0xef, "u32 stores big-endian byte 3") and ok
	ok = Assertions.assert_equal(memory.read_u32(0x1000), 0x89abcdef, "u32 big-endian round trip") and ok
	ok = Assertions.assert_equal(memory.read_s32(0x1000), -1985229329, "s32 big-endian sign") and ok
	memory.write_u16(0x1004, -2)
	ok = Assertions.assert_equal(memory.read_u16(0x1004), 0xfffe, "u16 wraps on store") and ok
	ok = Assertions.assert_equal(memory.read_s16(0x1004), -2, "s16 sign on load") and ok
	memory.write_u8(0x1006, -128)
	ok = Assertions.assert_equal(memory.read_s8(0x1006), -128, "s8 sign on load") and ok
	ok = Assertions.assert_true(memory.contains(0x100f, 1), "last byte is in bounds") and ok
	ok = Assertions.assert_true(not memory.contains(0x100f, 2), "multi-byte access checks bounds") and ok
	return ok
