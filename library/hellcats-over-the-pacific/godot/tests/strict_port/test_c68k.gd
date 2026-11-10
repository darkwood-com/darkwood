extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const C68kScript := preload("res://strict_port/compat/c68k.gd")

func run() -> bool:
	var ok := true
	ok = Assertions.assert_equal(C68kScript.u8(-1), 255, "u8 wraps negative") and ok
	ok = Assertions.assert_equal(C68kScript.s8(0xff), -1, "s8 sign extends") and ok
	ok = Assertions.assert_equal(C68kScript.u16(-1), 65535, "u16 wraps negative") and ok
	ok = Assertions.assert_equal(C68kScript.s16(0x8000), -32768, "s16 sign extends") and ok
	ok = Assertions.assert_equal(C68kScript.u32(-1), 0xffffffff, "u32 wraps negative") and ok
	ok = Assertions.assert_equal(C68kScript.s32(0xffffffff), -1, "s32 sign extends") and ok
	ok = Assertions.assert_equal(C68kScript.lsl32(0x80000001, 1), 2, "lsl32 wraps") and ok
	ok = Assertions.assert_equal(C68kScript.lsr32(-1, 1), 0x7fffffff, "lsr32 zero fills") and ok
	ok = Assertions.assert_equal(C68kScript.asr32(0x80000000, 1), -1073741824, "asr32 sign fills") and ok
	ok = Assertions.assert_equal(C68kScript.divs_trunc(-7, 3), -2, "signed division truncates toward zero") and ok
	ok = Assertions.assert_equal(C68kScript.rems_trunc(-7, 3), -1, "signed remainder follows numerator") and ok
	return ok
