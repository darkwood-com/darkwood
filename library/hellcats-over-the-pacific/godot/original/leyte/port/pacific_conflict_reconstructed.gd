
extends "res://original/leyte/port/pacific_conflict.gd"
## Leyte Gulf's overlay functions written from Leyte's own bytes (the data fork's image, runtime = file -
## 0x20), over the generated port (original/leyte/port/pacific_conflict.gd, which the generator never
## touches here). Checked against Leyte's code on the 68000 emulator (tools/leyte/gen_leyte_overlay_
## reconstructed_cases.py, tests/leyte/port/test_leyte_overlay_reconstructed.gd).

const LEYTE_HOST_WORLD: int = 0x207fc        ## the host world pointer (1.0.3's 0x1b5a0, by the map)
const LEYTE_RNG_SEED: int = 0x2c5b8          ## the ring's first long (1.0.3 0x2600c)
const LEYTE_RNG_CURSOR: int = 0x2c5b4        ## the ring cursor (1.0.3 0x26008, the base's DAT_00026008)
## The 22 hooks the entry gives the host world, from +0x322 (1.0.3: from +0x2e6), Leyte's routines.
const LEYTE_OVERLAY_HOOKS: Array = [0x8032, 0x7f12, 0x46e2, 0x4e68, 0x5192, 0x52c, 0x43d4, 0x64e, 0x150c,
	0x15da, 0x741e, 0x16dc, 0xe61e, 0x834c, 0x844e, 0x23a4, 0x2406, 0x31ca, 0xf7c0, 0x2554, 0xae2, 0x728c]

## 0x175a (the a.out entry; named apart from 1.0.3's two-argument overlay_main_entry, which the base refuses;
## reset_overlay_state calls it with (host world, -1, TickCount): one argument
## more than 1.0.3's 0x10f2): the host world stored at 0x207fc, Leyte's initialiser (0x1ef0, below) run with
## the ticks, world +0x92 = 9 and the 22 hooks written from world +0x322.
func leyte_overlay_main_entry(host: int, _unused: int, ticks: int) -> void:
	assert(memory != null, "overlay_main_entry requires original global memory")
	memory.write_u32(LEYTE_HOST_WORLD, C68kScript.u32(host))
	leyte_overlay_initialise(ticks)
	var world: int = memory.read_u32(LEYTE_HOST_WORLD)
	memory.write_u16(world + 0x92, 9)
	for k in LEYTE_OVERLAY_HOOKS.size():
		memory.write_u32(world + 0x322 + 4 * k, LEYTE_OVERLAY_HOOKS[k])

## 0x1ef0 (Leyte's overlay initialiser; 1.0.3's overlay_initialise 0x18d6, entry for entry with
## Leyte's addresses): the overlay RNG seeded with the ticks (0x135fe, below); the palette usage maps
## (0x23d94 = 0x23e9c, 0x23d90 = 0x23d9c); init_code_pointer_tables (0x4214); 0x4212 twice (an RTS:
## 1.0.3's overlay_init_noop); the three span tables (init_span_table_32ba 0x3d46, then 0x3ef2 and
## 0x4094); the light vector (0x21574 = 0xd983, 0x21572 = 0x69d8, 0x21570 = 0xc330); 0x2156c = 1,
## 0x21568 = 0; the frame counter 0x21020 = 0x4c; set_view_distance(0xc15c0) (0x1b89c).
func leyte_overlay_initialise(ticks: int) -> void:
	leyte_overlay_rng_init(ticks)
	memory.write_u32(0x23d94, 0x23e9c)
	memory.write_u32(0x23d90, 0x23d9c)
	init_code_pointer_tables()
	init_span_table_32ba()
	leyte_init_span_table_3ef2()
	leyte_init_span_table_4094()
	memory.write_u16(0x21574, 0xd983)
	memory.write_u16(0x21572, 0x69d8)
	memory.write_u16(0x21570, 0xc330)
	memory.write_u32(0x2156c, 1)
	memory.write_u32(0x21568, 0)
	memory.write_u32(0x21020, 0x4c)
	set_view_distance(0xc15c0)

## 0x135be (FUN_000135de in Ghidra's file addressing): byte for byte 1.0.3's overlay_rng_mul_mix
## (0x114aa): (a * b) mod 2^32 from 16-bit halves. The base's copy is refused for Leyte (an assert),
## so it is written here under its own name.
static func leyte_overlay_rng_mul_mix(a: int, b: int) -> int:
	a = C68kScript.u32(a)
	b = C68kScript.u32(b)
	var cross: int = C68kScript.u32((b & 0xffff) * (a >> 0x10) + (a & 0xffff) * (b >> 0x10))
	return C68kScript.s32(((cross & 0xffff) << 0x10) + (b & 0xffff) * (a & 0xffff))

## 0x135fe (FUN_0001361e): 1.0.3's overlay_rng_init (0x114ea) with Leyte's ring: the seed at
## 0x2c5b8, ring longs 1..54 = previous * 0x1df5e0d + 1, the cursor 0x2c5b4 = 0x2c690, then 10000
## discarded outputs of overlay_rng_next (0x13652, kept, already on Leyte's ring).
func leyte_overlay_rng_init(seed: int) -> void:
	assert(memory != null, "leyte_overlay_rng_init requires original global memory")
	var i: int
	memory.write_u32(LEYTE_RNG_SEED, C68kScript.u32(seed))
	i = 1
	while true:
		memory.write_u32(LEYTE_RNG_SEED + i * 4, C68kScript.u32(leyte_overlay_rng_mul_mix(0x1df5e0d, memory.read_u32(LEYTE_RNG_SEED + (i - 1) * 4)) + 1))
		i = i + 1
		if not (i < 0x37):
			break
	memory.write_u32(LEYTE_RNG_CURSOR, 0x2c690)
	i = 0
	while true:
		overlay_rng_next()
		i = i + 1
		if not (i < 10000):
			break

## 0x3ef2 (FUN_00003f12): 1.0.3's init_span_table_3466: the run-writer table at 0x3ece from the
## writers at 0x3fd6 (offsets 0, 6, 0x18, 0x20, 0x24, then 0x50 four times; PC-relative LEAs).
func leyte_init_span_table_3ef2() -> void:
	assert(memory != null, "leyte_init_span_table_3ef2 requires original global memory")
	_init_span4_table(0x3ece, 0x3fd6)

## 0x4094 (FUN_000040b4): 1.0.3's init_span_table_3608: the table at 0x4070 from 0x4178.
func leyte_init_span_table_4094() -> void:
	assert(memory != null, "leyte_init_span_table_4094 requires original global memory")
	_init_span4_table(0x4070, 0x4178)
