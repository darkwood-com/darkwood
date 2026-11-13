extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const OriginalOverlay := preload("res://original/port/pacific_conflict.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")

const CTL := 0x30000
const OBJ := 0x31000

## The aircraft AI control helpers on the controller view against the Original port, from the same
## bytes over 200 generated controllers: adjust_ai_throttle_vs_speed (target speeds and airspeeds
## at the word / long extremes, remainders, previous errors and throttle bytes across the sign
## boundary, so both the gear-down and the full-throttle branches run),
## max_ai_throttle_maybe_invert_679 (gear byte -128..127, heights around 100 and at the extremes),
## invert_ai_bias_67b_if_positive (every byte value) and 12 calls in a row of
## enqueue_control_inputs_avg4 (inputs beyond the clamps, the ring wrapping, damage bit 0x100):
## same memory byte for byte after each call.
func run() -> bool:
	var bad: Array = []
	var branches := {"gear down": 0, "full throttle": 0}
	for seed in 200:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		for m in [mo, mn]:
			_setup(m, seed)
		var a = OriginalOverlay.new(mo)
		var b = NativeOverlay.new(mn)
		var rng := RandomNumberGenerator.new()
		rng.seed = seed * 7 + 1
		var target: int = [0, 1, -1, 0x7fff, -0x8000, 0x12345, 300, -300][rng.randi_range(0, 7)] if seed % 2 else rng.randi_range(-0x800, 0x800)
		var gear_before: int = mo.read_s8(CTL + 0x679)
		a.adjust_ai_throttle_vs_speed(CTL, target)
		b.adjust_ai_throttle_vs_speed(CTL, target)
		if mo.read_u8(CTL + 0x668) == 9 and mo.read_u8(CTL + 0x63c) != 0xff:
			branches["full throttle"] += 1
		if gear_before == 0 and mo.read_s8(CTL + 0x679) == 1:
			branches["gear down"] += 1
		var steps := ["throttle"]
		a.max_ai_throttle_maybe_invert_679(CTL)
		b.max_ai_throttle_maybe_invert_679(CTL)
		steps.append("max")
		a.invert_ai_bias_67b_if_positive(CTL)
		b.invert_ai_bias_67b_if_positive(CTL)
		steps.append("bias")
		if mo._bytes != mn._bytes:
			bad.append([seed, steps])
			continue
		for k in 12:
			var args := []
			for limit in [0x40, 0x200, 0x140]:
				args.append(rng.randi_range(-limit * 2, limit * 2) if k % 4 else [0x7fff, -0x8000, 0x10000 + 5, -1][rng.randi_range(0, 3)])
			a.enqueue_control_inputs_avg4(CTL, args[0], args[1], args[2])
			b.enqueue_control_inputs_avg4(CTL, args[0], args[1], args[2])
			if mo._bytes != mn._bytes:
				bad.append([seed, "enqueue", k])
				break
	var ok := Assertions.assert_equal(bad.slice(0, 5), [], "AI control helpers: same memory after every call")
	ok = Assertions.assert_true(branches["gear down"] > 0 and branches["full throttle"] > 0,
			"both throttle branches ran (%s)" % str(branches)) and ok
	for v in range(-128, 128):
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		mo.write_u8(CTL + 0x67b, v)
		mn.write_u8(CTL + 0x67b, v)
		OriginalOverlay.new(mo).invert_ai_bias_67b_if_positive(CTL)
		NativeOverlay.new(mn).invert_ai_bias_67b_if_positive(CTL)
		if mo.read_u8(CTL + 0x67b) != mn.read_u8(CTL + 0x67b):
			bad.append(["bias", v])
	return Assertions.assert_equal(bad.slice(0, 5), [], "bias byte: every value") and ok

func _setup(m, seed: int) -> void:
	var rng := RandomNumberGenerator.new()
	rng.seed = seed
	for k in 0x700:
		m.write_u8(CTL + k, rng.randi_range(0, 255))
	m.write_u32(CTL, OBJ)
	var heights := [0, 100, 101, 99, -1, 0x7fffffff, -0x80000000, 5000]
	m.write_u32(OBJ + 0x1c, heights[rng.randi_range(0, 7)])
	m.write_u32(CTL + 0x2e, heights[rng.randi_range(0, 7)] if seed % 3 else 0)
	m.write_u32(CTL + 0x62a, [0, 0x7fffffff, -0x80000000, 0x1000, 0x4000, 0x400][rng.randi_range(0, 5)] if seed % 4 == 0 else rng.randi_range(0, 0x2000))
	m.write_u8(CTL + 0x668, rng.randi_range(-3, 12))
	m.write_u8(CTL + 0x679, [0, 0, 1, -1, 127, -128, 5][rng.randi_range(0, 6)])
	m.write_u16(CTL + 0x642, rng.randi_range(0, 3))
	m.write_u8(CTL + 0x685, rng.randi_range(0, 255) & (1 if seed % 5 == 0 else 0xfe))
