
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

## Leyte's overlay routines written here, by their image address (the loader registers the hooks among
## them through code_table).
func _init(param_memory = null) -> void:
	super(param_memory)
	code_table[0x150c] = Callable(self, "allocate_session_buffers")
	code_table[0x16dc] = Callable(self, "unlock_session_buffers")
	code_table[0x149e] = Callable(self, "unlock_handle_purgeable")
	code_table[0x7eac] = Callable(self, "reset_span_row_lists")
	code_table[0x7f12] = Callable(self, "update_view_clip_bounds")
	code_table[0x15da] = Callable(self, "lock_session_buffers")
	code_table[0x1476] = Callable(self, "lock_handle_nopurge")
	## hooks 15 / 16: the generated port's sin_fixed14 / cos_fixed14 (verified there at file 0x23c4 /
	## 0x2426; the image addresses are 0x23a4 / 0x2406), Leyte's tables 0x1fd94 / 0x1ff94.
	code_table[0x23a4] = Callable(self, "sin_fixed14")
	code_table[0x2406] = Callable(self, "cos_fixed14")
	code_table[0x31ca] = Callable(self, "rotate_int3_vecs_by_3ang")
	code_table[0x834c] = Callable(self, "begin_flight_for_rank")
	code_table[0xdde6] = Callable(self, "update_bomb_indicator")
	code_table[0x1bc0e] = Callable(self, "update_radar_range_buttons")
	code_table[0xde86] = Callable(self, "update_state_lamp")
	code_table[0xd2ae] = Callable(self, "copy_rect_depth8")
	code_table[0xe61e] = Callable(self, "redraw_view_readouts")
	code_table[0x5192] = Callable(self, "run_simulation_tick")
	code_table[0x2554] = Callable(self, "attitude_from_velocity")
	## hook 0 (configure_renderer_for_display, 0x8032) and the renderer routines it installs for each depth
	## (LEYTE_RENDERER_HOOKS_8 / _4 / _1), reached through those pointers.
	code_table[0x8032] = Callable(self, "configure_renderer_for_display")
	for entry in [[0x4294, "fill_span_rows_8bit"], [0xe784, "draw_line_depth8"], [0xece2, "restore_line_depth8"],
			[0x19f12, "plot_dot_depth8"], [0x19e8c, "erase_dots_depth8"], [0x1b992, "draw_radar_blip_depth8"],
			[0x1b902, "erase_radar_blip_depth8"], [0xb3d0, "output_face_colour"], [0x3d9a, "fill_span_rows_4bit_a"],
			[0x3f46, "fill_span_rows_4bit_b"], [0x40e8, "fill_span_rows_4bit_c"], [0xe978, "draw_line_depth4"],
			[0xef28, "restore_line_depth4"], [0x1a104, "plot_dot_depth4"], [0x19f8c, "erase_dots_depth4"],
			[0x1baca, "draw_radar_blip_depth4"], [0x1b9ea, "erase_radar_blip_depth4"], [0x1a20c, "plot_dot_bw"],
			[0x1a204, "clear_dots_bw"], [0x1bbe2, "draw_radar_blip_bw"], [0x1bbb6, "erase_radar_blip_bw"]]:
		code_table[entry[0]] = Callable(self, entry[1])
	## the template-registration class factories the mission build (hook 0) reaches through the definition
	## table 0x2074c (definition +6), called with (object, key, extra).
	code_table[0x1ebe0] = func(o: int, k: int, _c: int = 0) -> int: return leyte_register_ordnance_templates(o, k)
	code_table[0x1ca20] = func(o: int, k: int, _c: int = 0) -> int: return register_shadow_templates(o, k)
	code_table[0x1d56e] = func(o: int, k: int, _c: int = 0) -> int: return register_parachute_template(o, k)
	code_table[0x1ed5c] = func(o: int, k: int, _c: int = 0) -> int: return leyte_register_route_ship_templates(o, k)
	code_table[0x14952] = func(o: int, k: int, _c: int = 0) -> int: return leyte_register_template_2c834(o, k)
	code_table[0x14974] = func(o: int, k: int, _c: int = 0) -> int: return leyte_register_template_2c830(o, k)
	code_table[0x14996] = func(o: int, k: int, _c: int = 0) -> int: return leyte_register_template_2c82c(o, k)
	code_table[0x13bb6] = func(o: int, k: int, _c: int = 0) -> int: return leyte_create_type10_controller(o, k)
	code_table[0xf616] = func(o: int, k: int, c: int = 0) -> int: return create_enemy_fighter(o, k, c)
	code_table[0x1c9da] = func(o: int, k: int, _c: int = 0) -> int: return create_shadow_controller(o, k)
	code_table[0x108e] = Callable(self, "anchor_point_visitor")
	code_table[0x1dce2] = func(o: int, k: int, _c: int = 0) -> int: return create_view_attached_controller(o, k)
	code_table[0x1db7a] = func(o: int, k: int, _c: int = 0) -> int: return create_cloud_controller(o, k)
	code_table[0x1d21a] = func(o: int, k: int, _c: int = 0) -> int: return create_turret_controller(o, k)
	code_table[0x1cc72] = func(o: int, k: int, _c: int = 0) -> int: return create_flak_controller(o, k)
	code_table[0x1bea6] = func(o: int, k: int, _c: int = 0) -> int: return create_aircraft_spawner_controller(o, k)
	code_table[0x17ba2] = func(o: int, k: int, c: int = 0) -> int: return create_carrier_controller(o, k, c)
	code_table[0x18544] = func(o: int, k: int, c: int = 0) -> int: return create_sinking_controller(o, k, c)
	code_table[0x185ba] = func(o: int, k: int, c: int = 0) -> int: return create_basic_ship_controller(o, k, c)
	code_table[0x1862a] = func(o: int, k: int, c: int = 0) -> int: return leyte_create_escort_controller(o, k, c)
	code_table[0x18088] = func(o: int, k: int, _c: int = 0) -> int: return create_firing_ship_controller(o, k)
	code_table[0xf4a8] = func(o: int, k: int, c: int = 0) -> int: return aircraft_create(o, k, c)
	code_table[0x1ec7e] = func(o: int, k: int, c: int = 0) -> int: return leyte_create_route_ship(o, k, c)
	## hook 2 (start_mission_scene, 0x46e2): activating the new scene object instantiates the mission's objects
	## through the class factories registered below.
	code_table[0x46e2] = Callable(self, "start_mission_scene")
	## hook 21 (spawn_cloud_layer, 0x728c): its cloud path reaches the cloud class through the definition
	## table 0x2074c - register_cloud_templates (0x1dbb4, the generated port's) then create_cloud_controller
	## (0x1db7a), both there once hook 0 has built the mission tables. Hook 18 is isqrt_floor (0xf7c0).
	code_table[0x728c] = Callable(self, "spawn_cloud_layer")
	code_table[0xf7c0] = Callable(self, "isqrt_floor")

## 0x150c (hook 8; 1.0.3's allocate_session_buffers 0xe80 without its free-memory test: "not found"):
## session_buffers_rebuilt (0x27184) cleared; with host byte +0 the entry handle (0x20f30) made usable
## at 0x8340 bytes (1200 entries of 0x1c; 1.0.3: 800 or 550 by the free memory); unless host word +0xe
## is 1, the two handle groups 0x20f34 / 0x20f44, slot 1 and (with the multi-screen flag, host word
## +0xb4) slots 2..3, at 30 rows * host word +0xc * 4 bytes (1.0.3: 30 or 20 rows); any (re)allocation
## there sets the flag (ensure_handle_allocated, 0x143c).
func allocate_session_buffers() -> void:
	assert(memory != null, "allocate_session_buffers requires original global memory")
	var entry_a7: int = a7
	memory.write_u32(0x27184, 0)
	var rows: int = 0x1e
	var world: int = memory.read_u32(LEYTE_HOST_WORLD)
	if memory.read_u8(world) != 0:
		a7 = entry_a7 - 0x14
		ensure_handle_allocated(0x20f30, 0x8340)
		a7 = entry_a7
	if memory.read_s16(memory.read_u32(LEYTE_HOST_WORLD) + 0xe) == 1:
		return
	for k in range(1, 4):
		for group in [0x20f34, 0x20f44]:
			if k == 1 or memory.read_u16(memory.read_u32(LEYTE_HOST_WORLD) + 0xb4) != 0:
				var size: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(rows * memory.read_s16(memory.read_u32(LEYTE_HOST_WORLD) + 0xc)), 2))
				a7 = entry_a7 - 0x14
				var made: int = ensure_handle_allocated(group + 4 * k, size)
				a7 = entry_a7
				if made != 0:
					memory.write_u32(0x27184, 1)

## 0x149e (1.0.3's unlock_handle_purgeable 0xe32, the same instructions with Leyte's host-world slots;
## the correspondence placed it on lock_handle_nopurge's 0x1476, and Ghidra lists no function here):
## unlock the handle (host +0x312, 0xc below) and make it purgeable (+0x316, 0x10 below).
func unlock_handle_purgeable(handle: int) -> void:
	assert(memory != null, "unlock_handle_purgeable requires original global memory")
	var entry_a7: int = a7
	_host_memory_call(entry_a7, 0xc, 0x312, [handle])
	_host_memory_call(entry_a7, 0x10, 0x316, [handle])

## 0x7eac (1.0.3's reset_span_row_lists 0x6e74; the correspondence found no match, Leyte walking a
## pointer over the group's data pointers instead of 1.0.3's index): the span runs of the device slots
## 1..3 (data pointers 0x20f5c + 4 i) are reset - every slot when slot == 0, else the one whose pointer
## sits at 0x20f5c + 4 slot: for each view row (sext word 0x21010) an 8-byte run (0, 1, view width
## 0x21012, fill colour 0x23fd0).
func reset_span_row_lists(slot: int) -> void:
	assert(memory != null, "reset_span_row_lists requires original global memory")
	for i in range(1, 4):
		var run: int = memory.read_u32(0x20f5c + 4 * i)
		if run == 0 or (C68kScript.u32(slot) != 0 and C68kScript.u32(4 * slot) != 4 * i):
			continue
		var row: int = 0
		while row < memory.read_s16(0x21010):
			memory.write_u16(run, 0)
			memory.write_u16(run + 2, 1)
			memory.write_u16(run + 4, memory.read_u16(0x21012))
			memory.write_u16(run + 6, memory.read_u16(0x23fd0))
			run += 8
			row += 1

## A locked handle's master pointer, masked to 24 bits with host word +0x76.
func _leyte_locked_pointer(handle: int) -> int:
	var p: int = memory.read_u32(handle)
	if memory.read_u16(memory.read_u32(0x207fc) + 0x76) != 0:
		p = p & 0xffffff
	return p

## 0x2be8 (Leyte's own finalize_attitude_matrix_3ang; link.w a6,#-0x14 unused): 1.0.3's 0x24d8 tagged
## the composer and multiplied Rz * Rx * Ry through matmul3x3_s16; Leyte writes the product inline from
## the six sin / cos words load_attitude_angle_sincos left (0x23d22 .. 0x23d2c), every term muls.w
## (16 x 16 -> 32) or muls.l, asr.l #12, stored as a word.
func finalize_attitude_matrix_3ang(out: int) -> void:
	assert(memory != null, "finalize_attitude_matrix_3ang requires original global memory")
	var w22: int = memory.read_s16(0x23d22)
	var w24: int = memory.read_s16(0x23d24)
	var w26: int = memory.read_s16(0x23d26)
	var w28: int = memory.read_s16(0x23d28)
	var w2a: int = memory.read_s16(0x23d2a)
	var w2c: int = memory.read_s16(0x23d2c)
	var d1: int = C68kScript.asr32(C68kScript.s32(w2c * w28), 12)
	var d0: int = C68kScript.asr32(C68kScript.s32(w2c * w26), 12)
	memory.write_u16(out, C68kScript.u16(C68kScript.asr32(C68kScript.s32(w22 * w26 + C68kScript.s16(d1) * w24), 12)))
	memory.write_u16(out + 2, C68kScript.u16(C68kScript.asr32(C68kScript.s32(w24 * w2a), 12)))
	memory.write_u16(out + 4, C68kScript.u16(C68kScript.asr32(C68kScript.s32(w22 * w28 - C68kScript.s16(d0) * w24), 12)))
	var neg24: int = C68kScript.s32(-w24)
	memory.write_u16(out + 6, C68kScript.u16(C68kScript.asr32(C68kScript.s32(neg24 * w26 + C68kScript.s16(d1) * w22), 12)))
	memory.write_u16(out + 8, C68kScript.u16(C68kScript.asr32(C68kScript.s32(w22 * w2a), 12)))
	memory.write_u16(out + 0xa, C68kScript.u16(C68kScript.asr32(C68kScript.s32(neg24 * w28 - C68kScript.s16(d0) * w22), 12)))
	memory.write_u16(out + 0xc, C68kScript.u16(C68kScript.asr32(C68kScript.s32(-w2a * w28), 12)))
	memory.write_u16(out + 0xe, C68kScript.u16(w2c))
	memory.write_u16(out + 0x10, C68kScript.u16(C68kScript.asr32(C68kScript.s32(w2a * w26), 12)))

## 0x2ce2 (1.0.3's build_attitude_matrix_3ang 0x251e, the same instructions): the angles' sin / cos
## (load_attitude_angle_sincos 0x265c, fourth angle 0, 0x18 below) and the matrix (0x2be8, 0x1c below).
func build_attitude_matrix_3ang(angle1: int, angle2: int, angle3: int, out: int) -> void:
	assert(a7 != 0, "build_attitude_matrix_3ang needs a7 (the composer frame lies below it)")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x18
	load_attitude_angle_sincos(angle1, angle2, angle3, 0)
	a7 = entry_a7 - 0x1c
	finalize_attitude_matrix_3ang(out)
	a7 = entry_a7

## 0x3418 (1.0.3's transform_int3_vecs_by_matrix3 0x2aba, registers reallocated): out_j = the 64-bit
## x * m[0][j] + y * m[1][j] + z * m[2][j] asr 12 cut to 32 bits; the nine words are sign-extended
## into the frame (a6 - 0x24) first, kept in a local here since nothing outside reads it. Unsigned
## count. D0: the last stored component - or, with count 0, the copy loop's end pointer, the frame
## address a6 - 4 = entry - 8 (1.0.3 left matrix + 0x12 there).
func transform_int3_vecs_by_matrix3(count: int, src: int, dst: int, matrix: int) -> int:
	assert(memory != null, "transform_int3_vecs_by_matrix3 requires original global memory")
	assert(a7 != 0, "transform_int3_vecs_by_matrix3 needs a7 (its count-0 result is a frame address)")
	var m: Array[int] = []
	for k in range(9):
		m.append(memory.read_s16(matrix + 2 * k))
	var result: int = C68kScript.u32(a7 - 8)
	var left: int = C68kScript.u32(count)
	while left != 0:
		left -= 1
		var x: int = memory.read_s32(src)
		var y: int = memory.read_s32(src + 4)
		var z: int = memory.read_s32(src + 8)
		memory.write_u32(dst, C68kScript.u32((m[0] * x + m[3] * y + m[6] * z) >> 0xc))
		memory.write_u32(dst + 4, C68kScript.u32((m[1] * x + m[4] * y + m[7] * z) >> 0xc))
		result = C68kScript.u32((m[2] * x + m[5] * y + m[8] * z) >> 0xc)
		memory.write_u32(dst + 8, result)
		src += 0xc
		dst += 0xc
	return result

## 0x834c (hook 13; 1.0.3's begin_flight_for_rank 0x72f0 with Leyte's addresses and one change): the
## AI aim lag 0x21060 = the rank table (0x204dc) word, and 0x2105e = that word / 2 toward zero (1.0.3
## stored the same word in both); without host word +0xa4 the player's (0x20b28) handler +0x32 =
## 0xfcf6; at depth 1 the objects are refreshed (0x4e68, hook 3, not ported for Leyte: its assertion
## stops there) and 0x23fb8 = the first host pattern word (+0x88); 0x2b268 / 0x2b26a / 0x21042 are
## cleared; with 0x2b270 set, host words +0x37a / +0x37c go to 0x2b26e / 0x2b26c.
func begin_flight_for_rank(rank: int) -> void:
	assert(memory != null, "begin_flight_for_rank requires original global memory")
	var lag: int = memory.read_s16(C68kScript.u32(0x204dc + C68kScript.s32(rank) * 2))
	memory.write_u16(0x21060, C68kScript.u16(lag))
	memory.write_u16(0x2105e, C68kScript.u16((lag + 1 if lag < 0 else lag) >> 1))
	var world: int = memory.read_u32(0x207fc)
	if memory.read_u16(world + 0xa4) == 0:
		memory.write_u32(memory.read_u32(0x20b28) + 0x32, 0xfcf6)
	if memory.read_s16(memory.read_u32(0x207fc) + 0xe) == 1:
		var entry_a7: int = a7
		a7 = entry_a7 - 4
		refresh_object_colours_for_depth()
		a7 = entry_a7
		memory.write_u16(0x23fb8, memory.read_u16(memory.read_u32(memory.read_u32(0x207fc) + 0x88)))
	memory.write_u16(0x2b268, 0)
	memory.write_u16(0x2b26a, 0)
	memory.write_u16(0x21042, 0)
	if memory.read_u16(0x2b270) != 0:
		world = memory.read_u32(0x207fc)
		memory.write_u16(0x2b26e, memory.read_u16(world + 0x37a))
		memory.write_u16(0x2b26c, memory.read_u16(world + 0x37c))

## 0xdde6 (1.0.3's update_bomb_indicator 0xcaa6 with Leyte's fields and one more argument; two saved
## registers): high = (player (0x20b28) object y (+8) - player +0x2e) > 0x43; unless the readouts are
## drawn (0x2c596) with the same bomb count (sext byte player +0x68d) and flag, both are cached
## (0x2c57a / 0x2c57c) and the host lamp routine (+0x2d6)(high, count > 0 ? word +0x204 : word +0x200,
## count > 1 ? +0x204 : +0x200, sext byte player +0x690) runs (0x1c below).
func update_bomb_indicator() -> void:
	assert(memory != null, "update_bomb_indicator requires original global memory")
	var player: int = memory.read_u32(0x20b28)
	var high: int = 1 if C68kScript.s32(memory.read_u32(memory.read_u32(player) + 8) - memory.read_u32(player + 0x2e)) > 0x43 else 0
	if memory.read_u16(0x2c596) != 0 and memory.read_s8(player + 0x68d) == memory.read_s16(0x2c57a) and high == memory.read_s16(0x2c57c):
		return
	player = memory.read_u32(0x20b28)
	memory.write_u16(0x2c57a, C68kScript.u16(memory.read_s8(player + 0x68d)))
	memory.write_u16(0x2c57c, high)
	var world: int = memory.read_u32(0x207fc)
	var on: int = memory.read_s16(world + 0x204)
	var off: int = memory.read_s16(world + 0x200)
	var extra: int = memory.read_s8(player + 0x690)
	var count: int = memory.read_s16(0x2c57a)
	var entry_a7: int = a7
	a7 = entry_a7 - 0x1c
	_jsr(memory.read_u32(world + 0x2d6), [high, on if count > 0 else off, on if count > 1 else off, extra])
	a7 = entry_a7

## 0x1bc0e (1.0.3's update_radar_range_buttons 0x17a6c, the same instructions with Leyte's fields; the
## correspondence stopped at the button field +0x1c2 -> +0x1c6): when radar_changed (0x2cc22): the
## radar scale (word 0x2cc18) = radar range (0x2cc1e) divs.l (scope width (host +0x1e8 - +0x1e4) * 16
## / 2, toward zero); radar_changed = 0; the three range buttons (host +0x1c6 / +0x1ce / +0x1d6, for
## 0xce40 / 0x26ac0 / 0xc15c0) drawn through host +0x2c6 in the lit colour (+0x1de) for the current
## range, else unlit (+0x1e0) (0x14 below each).
func update_radar_range_buttons() -> void:
	assert(memory != null, "update_radar_range_buttons requires original global memory")
	if memory.read_u32(0x2cc22) == 0:
		return
	var entry_a7: int = a7
	var host: int = memory.read_u32(0x207fc)
	var width: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(memory.read_s16(host + 0x1e8) - memory.read_s16(host + 0x1e4)), 4))
	if width < 0:
		width = C68kScript.s32(width + 1)
	memory.write_u16(0x2cc18, C68kScript.u16(C68kScript.divs_trunc(memory.read_s32(0x2cc1e), C68kScript.asr32(width, 1))))
	memory.write_u32(0x2cc22, 0)
	host = memory.read_u32(0x207fc)
	var lit: int = memory.read_s16(host + 0x1de)
	var unlit: int = memory.read_s16(host + 0x1e0)
	for button in [[0x1c6, 0xce40], [0x1ce, 0x26ac0], [0x1d6, 0xc15c0]]:
		host = memory.read_u32(0x207fc)
		var colour: int = lit if memory.read_u32(0x2cc1e) == button[1] else unlit
		a7 = entry_a7 - 0x14
		_jsr(memory.read_u32(host + 0x2c6), [C68kScript.u32(host + button[0]), C68kScript.u32(colour)])
		a7 = entry_a7

const LEYTE_STATE_LAMP_CODES: Array = [0x0, -1, 0xc, -1, 0x1e, 0x3f, 0x3f]

## 0xde86 (Leyte only: hook 12 calls it between the bomb lamp and the overlays-drawn mark; two saved
## registers): unless the readouts are drawn (0x2c596) with the same state (sext byte player +0x68e,
## cached in word 0x2c57e), the state is cached and its lamp code goes to the host routine +0x2da (0x10
## below): state 0 -> 0, 1 -> 4 (8 unless sext byte +0x68f == 1), 2 -> 0xc, 3 -> 0xe (0x1c unless
## +0x68f == 1), 4 -> 0x1e, 5 / 6 -> 0x3f (a pc-relative word switch). A state above 6 (unsigned)
## skips the switch and passes the caller's D1, which the port does not hold: it stops there.
func update_state_lamp() -> void:
	assert(memory != null, "update_state_lamp requires original global memory")
	var player: int = memory.read_u32(0x20b28)
	if memory.read_u16(0x2c596) != 0 and memory.read_s8(player + 0x68e) == memory.read_s16(0x2c57e):
		return
	player = memory.read_u32(0x20b28)
	var state: int = memory.read_s8(player + 0x68e)
	memory.write_u16(0x2c57e, C68kScript.u16(state))
	var detail: int = memory.read_s8(player + 0x68f)
	assert(C68kScript.u32(state) <= 6, "update_state_lamp: state %d leaves the switch with the caller's D1" % state)
	var code: int = LEYTE_STATE_LAMP_CODES[state]
	if state == 1:
		code = 4 if detail == 1 else 8
	elif state == 3:
		code = 0xe if detail == 1 else 0x1c
	var entry_a7: int = a7
	a7 = entry_a7 - 0x10
	_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2da), [code])
	a7 = entry_a7

## 0xe3ca (1.0.3's update_view_readouts_and_overlays 0xcfec with Leyte's addresses; link.w a6,#-0x64,
## nine saved registers, the body 0x8c below the entry). Panels not drawn (0x27010 == 0): the numeric
## readouts. The subject is the designated entity (0x21070) in view 7 (0x23d88; mode7 = 1), else the
## player (0x20b28); readouts_drawn (0x2c596) = 0; then the inset (word 0x2700e) passes run through
## dbra. Inset 4 first resets: readout index (0x2c58c) = 0, the host text routine (+0x2b2)(0, 0, 0, 0,
## 1, mode7) (0xa8 below), inset = 1, the four cached values (0x2c58e) = 0x7fff, and the count
## register becomes 4. Each pass takes the readout index (above 3 the previous value is kept): 0
## altitude (object y +8 divs 10), 1 speed ((+0x62a divs 0x12) / 4 toward zero), 2 heading (0x168 -
## ((word +0x22 * 0x168) / 256 / 64 toward zero)), 3 climb (((0x21468 divs 0x78) * 0x258) / 64 toward
## zero divs 0x76); in view 7 readout 3 is skipped. A value differing from its cache caches its
## magnitude and is printed into the frame (a6 - 0x64: digits backwards from index 9, terminator at
## 10, a degree sign 0xa1 for the heading, '-' when negative), measured by c_strlen (0x1f73a, 0x9c
## below) and sent to the text routine (text, index, 0, length, 0, mode7). The index then advances
## modulo 4. Panels drawn: the panel copy offset (0x21564 = host word +0xe6 * +0xe4) unless the
## readouts are drawn; without the host line routine (word +0x9e) the sky / ground (0xc77e) and the
## heading tape (0xd4b2) take hide_cursor (0x94 / 0x98 below); then the panel frame lines (0xccf6) and
## the instruments (0xd984, 0xdb2c, 0xdbf6, 0xdc68, 0xdce2, 0xdd64, the radar scope 0x1bcea, 0xdfd0,
## 0xe1da, 0xe282, 0xe088), all 0x90 below - the 1.0.3 port's order and names.
func update_view_readouts_and_overlays(hide_cursor: int) -> void:
	assert(memory != null, "update_view_readouts_and_overlays requires original global memory")
	assert(a7 != 0, "update_view_readouts_and_overlays needs a7 (its frame holds the text)")
	var entry_a7: int = a7
	var text: int = entry_a7 - 4 - 0x64
	if memory.read_u16(0x27010) != 0:
		if memory.read_u16(0x2c596) == 0:
			var world: int = memory.read_u32(0x207fc)
			memory.write_u32(0x21564, C68kScript.u32(memory.read_s16(world + 0xe6) * memory.read_s16(world + 0xe4)))
		if memory.read_u16(memory.read_u32(0x207fc) + 0x9e) == 0:
			a7 = entry_a7 - 0x94
			draw_sky_and_ground(hide_cursor)
			a7 = entry_a7 - 0x98
			update_heading_tape(hide_cursor)
		a7 = entry_a7 - 0x90
		draw_panel_frame_lines()
		update_altimeter_needles()
		update_airspeed_needle()
		update_direction_needle()
		update_climb_needle()
		update_tachometer_needle()
		update_manifold_needle()
		update_radar_scope()
		update_flaps_needle()
		update_ammo_needle()
		update_fuel_needles()
		update_gear_needles()
		a7 = entry_a7
		return
	var subject: int
	var mode7: int
	if memory.read_s16(0x23d88) == 7:
		subject = memory.read_u32(0x21070)
		mode7 = 1
	else:
		subject = memory.read_u32(0x20b28)
		mode7 = 0
	memory.write_u16(0x2c596, 0)
	var count: int = memory.read_u16(0x2700e)
	var value: int = 0
	while true:
		count = (count - 1) & 0xffff
		if count == 0xffff:
			break
		if memory.read_s16(0x2700e) == 4:
			memory.write_u16(0x2c58c, 0)
			a7 = entry_a7 - 0xa8
			_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2b2), [0, 0, 0, 0, 1, mode7])
			a7 = entry_a7
			memory.write_u16(0x2700e, 1)
			for k in 4:
				memory.write_u16(0x2c58e + 2 * k, 0x7fff)
			count = 4
		var index: int = memory.read_s16(0x2c58c)
		if C68kScript.u32(index) <= 3:
			var d0: int
			match index:
				0:
					value = C68kScript.s16(C68kScript.divs_trunc(memory.read_s32(memory.read_u32(subject) + 8), 10))
				1:
					d0 = C68kScript.divs_trunc(memory.read_s32(subject + 0x62a), 0x12)
					if d0 < 0:
						d0 += 3
					value = C68kScript.s16(d0 >> 2)
				2:
					d0 = C68kScript.s32(memory.read_s16(subject + 0x22) * 0x168)
					if d0 < 0:
						d0 += 0xff
					d0 = d0 >> 8
					if d0 < 0:
						d0 += 0x3f
					d0 = d0 >> 6
					value = C68kScript.s16(0x168 - d0)
				3:
					d0 = C68kScript.s32(C68kScript.divs_trunc(memory.read_s32(0x21468), 0x78) * 0x258)
					if d0 < 0:
						d0 += 0x3f
					d0 = d0 >> 6
					value = C68kScript.s16(C68kScript.divs_trunc(d0, 0x76))
		var skip: bool = mode7 != 0 and memory.read_s16(0x2c58c) == 3
		var slot: int = C68kScript.u32(0x2c58e + memory.read_s16(0x2c58c) * 2)
		if not skip and memory.read_s16(slot) != value:
			var negative: bool = value < 0
			if negative:
				value = C68kScript.s16(-value)
			memory.write_u16(slot, C68kScript.u16(value))
			memory.write_u8(text + 10, 0)
			var pos: int = 9
			if memory.read_s16(0x2c58c) == 2:
				memory.write_u8(text + 9, 0xa1)
				pos = 8
			while true:
				memory.write_u8(text + pos, (C68kScript.rems_trunc(value, 10) + 0x30) & 0xff)
				pos -= 1
				value = C68kScript.s16(C68kScript.divs_trunc(value, 10))
				if value == 0:
					break
			if negative:
				memory.write_u8(text + pos, 0x2d)
				pos -= 1
			pos += 1
			var start: int = text + pos
			a7 = entry_a7 - 0x9c
			var length: int = c_strlen(start)
			a7 = entry_a7 - 0xa8
			_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2b2), [start, memory.read_s16(0x2c58c), 0, length, 0, mode7])
			a7 = entry_a7
		memory.write_u16(0x2c58c, C68kScript.u16(memory.read_u16(0x2c58c) + 1))
		if memory.read_s16(0x2c58c) == 4:
			memory.write_u16(0x2c58c, 0)

## The five simple needles (1.0.3's, instruction for instruction, with Leyte's fields): the host dial
## words moved 4 bytes up (+0x100 -> +0x104, +0x150 -> +0x154, +0x120 -> +0x124, +0x140 -> +0x144, +0x130
## -> +0x134) and the player's rpm / manifold fields 0xe up (+0x66a -> +0x678, +0x672 -> +0x680). The
## generator refused them because the dial offsets travel as bare literals; the helpers they use
## (_dial_centre, _needle_offsets, _call_draw_gauge_needle, draw_gauge_needle 0xd892) are verified.

## Centre at host word off (and off + 2), length word off + 4; cos entered cos_depth below,
## draw_gauge_needle cos_depth + 0x18 below (two pushed angles and five arguments).
func _simple_needle(entry_a7: int, angle: int, off: int, cos_depth: int, kind: int) -> void:
	var centre: Array = _dial_centre(off)
	var o: Array = _needle_offsets(angle, off + 4, entry_a7, cos_depth)
	_call_draw_gauge_needle(entry_a7, cos_depth + 0x18, kind, centre[0], centre[1], centre[0] + o[0], centre[1] + o[1])

## 0xdb2c (1.0.3's 0xc7ec): airspeed (type 1) from player +0x62a: above 0x3843 it wraps down by 0x3843;
## then three linear pieces ((v << 14) divs 0xb409 up to 0xb40, ((v - 0x5a0) << 14) divs 0x5a04 up to
## 0x10e0, else ((v - 0xb41) << 14) divs 0x2d02), + 0xf000; dial at host +0x104. Four saved registers.
func update_airspeed_needle() -> void:
	assert(memory != null, "update_airspeed_needle requires original global memory")
	var entry_a7: int = a7
	var v: int = memory.read_s32(memory.read_u32(0x20b28) + 0x62a)
	if v > 0x3843:
		v = C68kScript.s32(v - 0x3843)
	var d0: int
	if v <= 0xb40:
		d0 = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(v, 14)), 0xb409)
	elif v <= 0x10e0:
		d0 = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(v - 0x5a0, 14)), 0x5a04)
	else:
		d0 = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(v - 0xb41, 14)), 0x2d02)
	var angle: int = C68kScript.u16(d0 + 0xf000) & 0x3fff
	_simple_needle(entry_a7, angle, 0x104, 0x18, 1)

## 0xdbf6 (1.0.3's 0xc8b6): direction (type 6): angle neg_camera_heading (0x23d3e) + 0xf000 (word),
## dial at host +0x154.
func update_direction_needle() -> void:
	assert(memory != null, "update_direction_needle requires original global memory")
	_simple_needle(a7, C68kScript.u16(memory.read_u16(0x23d3e) + 0xf000) & 0x3fff, 0x154, 0x18, 6)

## 0xdc68 (1.0.3's 0xc928): climb (type 3): (climb_rate 0x21468 divs 0x78) - 0x2000, dial at host
## +0x124.
func update_climb_needle() -> void:
	assert(memory != null, "update_climb_needle requires original global memory")
	_simple_needle(a7, C68kScript.u32(C68kScript.divs_trunc(memory.read_s32(0x21468), 0x78) - 0x2000) & 0x3fff, 0x124, 0x18, 3)

## 0xdce2 (1.0.3's 0xc9a2): tachometer (type 5): ((player +0x678 << 6) divs 0x23a) + 0x1540, dial at
## host +0x144.
func update_tachometer_needle() -> void:
	assert(memory != null, "update_tachometer_needle requires original global memory")
	var rpm: int = memory.read_s32(memory.read_u32(0x20b28) + 0x678)
	_simple_needle(a7, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(rpm, 6)), 0x23a) + 0x1540) & 0x3fff, 0x144, 0x18, 5)

## 0xdd64 (1.0.3's 0xca24): manifold (type 4): ((sext word player +0x680 << 6) divs 0x5f) - 0x2500,
## dial at host +0x134.
func update_manifold_needle() -> void:
	assert(memory != null, "update_manifold_needle requires original global memory")
	var mp: int = memory.read_s16(memory.read_u32(0x20b28) + 0x680)
	_simple_needle(a7, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(mp, 6)), 0x5f) - 0x2500) & 0x3fff, 0x134, 0x18, 4)

## The other five needles (1.0.3's, instruction for instruction, every difference listed against
## Leyte's bytes): the altimeter's dial 4 bytes up (+0xf0 -> +0xf4) and its host fields as in
## draw_gauge_needle (+0xe4 -> +0xe8, +0xe0 -> +0xe4, +0xdc -> +0xe0, line routine +0x272 -> +0x2aa); the
## flaps / gear / ammo / fuel dials 0x38 up (+0x20e / +0x214 / +0x232 / +0x226 -> +0x246 / +0x24c /
## +0x26a / +0x25e); their player bytes moved (flaps +0x67b -> +0x68c, gear +0x679 -> +0x68a, ammo word
## +0x682 -> +0x696, tanks +0x67d / +0x67e -> +0x691 / +0x692); every immediate identical. abs_int (0x38e8)
## is the same 4 instructions as 1.0.3's.

## 0xd984 (1.0.3's 0xc644): altimeter. Long hand (type 0): angle ((camera y 0x23d64 rems 10000) << 14
## divs 10000) + 0xf000; when it was drawn, the short hand at 2/3 length: angle ((y rems 100000) << 14
## divs 100000) + 0xf000, through the host line routine (+0x2aa, 0x34 below) or the depth routine at
## 0x23fc8 (restore the cached end 0x2c572 / 0x2c576, when the readouts are drawn; 0x40 below) and the
## one at 0x23fcc (0x3c below); its end is cached. Seven saved registers.
func update_altimeter_needles() -> void:
	assert(memory != null, "update_altimeter_needles requires original global memory")
	var entry_a7: int = a7
	var y: int = memory.read_s32(0x23d64)
	var angle: int = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(C68kScript.rems_trunc(y, 0x2710), 14)), 0x2710) + 0xf000) & 0x3fff
	var centre: Array = _dial_centre(0xf4)
	var off: Array = _needle_offsets(angle, 0xf8, entry_a7, 0x24)
	if _call_draw_gauge_needle(entry_a7, 0x3c, 0, centre[0], centre[1], centre[0] + off[0], centre[1] + off[1]) == 0:
		return
	angle = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(C68kScript.rems_trunc(memory.read_s32(0x23d64), 0x186a0), 14)), 0x186a0) + 0xf000) & 0x3fff
	a7 = entry_a7 - 0x24
	var c: int = C68kScript.s16(cos_fixed14(angle))
	var world: int = memory.read_u32(0x207fc)
	var dx: int = C68kScript.asr32(C68kScript.s32(C68kScript.divs_trunc(memory.read_s16(world + 0xf8) * 2, 3) * c), 12)
	a7 = entry_a7 - 0x28
	var sn: int = C68kScript.s16(sin_fixed14(angle))
	a7 = entry_a7
	world = memory.read_u32(0x207fc)
	var dy: int = C68kScript.asr32(C68kScript.s32(C68kScript.divs_trunc(memory.read_s16(world + 0xf8) * 2, 3) * sn), 12)
	var cx: int = centre[0]
	var cy: int = centre[1]
	var ex: int = C68kScript.s32(cx + dx)
	var ey: int = C68kScript.s32(cy + dy)
	if memory.read_u16(world + 0x9e) != 0:
		a7 = entry_a7 - 0x34
		_jsr(memory.read_u32(world + 0x2aa), [memory.read_s16(world + 0xe8), cx, cy, ex, ey])
	else:
		if memory.read_u16(0x2c596) != 0:
			world = memory.read_u32(0x207fc)
			a7 = entry_a7 - 0x40
			_jsr(memory.read_u32(0x23fc8), [cx, cy, memory.read_s32(0x2c572), memory.read_s32(0x2c576), memory.read_s32(world + 6),
					memory.read_s16(world + 0x10), C68kScript.s32(memory.read_u32(world + 0xe0) - memory.read_u32(0x21564)), memory.read_s16(world + 0xe4)])
		world = memory.read_u32(0x207fc)
		a7 = entry_a7 - 0x3c
		_jsr(memory.read_u32(0x23fcc), [cx, cy, ex, ey, memory.read_s16(world + 0xe8), memory.read_s32(world + 6), memory.read_s16(world + 0x10)])
	a7 = entry_a7
	memory.write_u32(0x2c572, C68kScript.u32(ex))
	memory.write_u32(0x2c576, C68kScript.u32(ey))

## 0xdfd0 (1.0.3's 0xcbf2): flaps (type 0xa). Unless the readouts are drawn with the same byte player
## +0x68c (cached in 0x2c582), it is cached; angle ((-abs_int(flaps) muls.l 0xac0) divs 15) + 0x2000
## (abs_int 0x18, cos 0x1c, needle 0x38 below; four saved registers), dial at host +0x246.
func update_flaps_needle() -> void:
	assert(memory != null, "update_flaps_needle requires original global memory")
	if memory.read_u16(0x2c596) != 0 and memory.read_s8(memory.read_u32(0x20b28) + 0x68c) == memory.read_s16(0x2c582):
		return
	var flaps: int = memory.read_s8(memory.read_u32(0x20b28) + 0x68c)
	memory.write_u16(0x2c582, C68kScript.u16(flaps))
	var entry_a7: int = a7
	a7 = entry_a7 - 0x18
	var mag: int = abs_int(flaps)
	a7 = entry_a7
	var angle: int = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(C68kScript.s32(-mag) * 0xac0), 15) + 0x2000) & 0x3fff
	var centre: Array = _dial_centre(0x246)
	var o: Array = _needle_offsets(angle, 0x24a, entry_a7, 0x1c)
	_call_draw_gauge_needle(entry_a7, 0x38, 0xa, centre[0], centre[1], centre[0] + o[0], centre[1] + o[1])

## 0xe088 (1.0.3's 0xccaa): gear (types 0xb / 0xc / 0xd), nine saved registers. Unless the readouts are
## drawn with the same byte player +0x68a (cached in 0x2c584), it is cached; angle (abs_int(gear) << 12)
## divs 0x2d (abs_int 0x2c below). Needles at host +0x24c (end centre + offset; cos 0x30, needle 0x48
## below), +0x252 (x mirrored; cos 0x2c, needle 0x44) and +0x258 (cos 0x48, sin 0x2c; needle 0x40).
func update_gear_needles() -> void:
	assert(memory != null, "update_gear_needles requires original global memory")
	if memory.read_u16(0x2c596) != 0 and memory.read_s8(memory.read_u32(0x20b28) + 0x68a) == memory.read_s16(0x2c584):
		return
	var gear: int = memory.read_s8(memory.read_u32(0x20b28) + 0x68a)
	memory.write_u16(0x2c584, C68kScript.u16(gear))
	var entry_a7: int = a7
	a7 = entry_a7 - 0x2c
	var mag: int = abs_int(gear)
	a7 = entry_a7
	var angle: int = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(mag, 12)), 0x2d) & 0x3fff
	var c1: Array = _dial_centre(0x24c)
	var o1: Array = _needle_offsets(angle, 0x250, entry_a7, 0x30)
	_call_draw_gauge_needle(entry_a7, 0x48, 0xb, c1[0], c1[1], c1[0] + o1[0], c1[1] + o1[1])
	var c2: Array = _dial_centre(0x252)
	var o2: Array = _needle_offsets(angle, 0x256, entry_a7, 0x2c)
	_call_draw_gauge_needle(entry_a7, 0x44, 0xc, c2[0], c2[1], c2[0] - o2[0], c2[1] + o2[1])
	var c3: Array = _dial_centre(0x258)
	a7 = entry_a7 - 0x48
	var c: int = C68kScript.s16(cos_fixed14(angle))
	a7 = entry_a7 - 0x2c
	var sn: int = C68kScript.s16(sin_fixed14(angle))
	a7 = entry_a7
	var length: int = memory.read_s16(memory.read_u32(0x207fc) + 0x25c)
	_call_draw_gauge_needle(entry_a7, 0x40, 0xd, c3[0], c3[1], c3[0] + C68kScript.asr32(c * length, 12), c3[1] + C68kScript.asr32(sn * length, 12))

## 0xe1da (1.0.3's 0xcdfc): ammo (type 0xf). Unless the readouts are drawn with the same word player
## +0x696 (cached in 0x2c586), it is cached; angle sext word ((count muls.w 0x1555) divs 0x2a3 + 0x555),
## dial at host +0x26a, end centre - offset. Four saved registers.
func update_ammo_needle() -> void:
	assert(memory != null, "update_ammo_needle requires original global memory")
	if memory.read_u16(0x2c596) != 0 and memory.read_u16(memory.read_u32(0x20b28) + 0x696) == memory.read_u16(0x2c586):
		return
	memory.write_u16(0x2c586, memory.read_u16(memory.read_u32(0x20b28) + 0x696))
	var entry_a7: int = a7
	var angle: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(0x2c586) * 0x1555), 0x2a3) + 0x555)
	var centre: Array = _dial_centre(0x26a)
	var o: Array = _needle_offsets(angle, 0x26e, entry_a7, 0x18)
	_call_draw_gauge_needle(entry_a7, 0x30, 0xf, centre[0], centre[1], centre[0] - o[0], centre[1] - o[1])

## 0xe282 (1.0.3's 0xcea4): fuel (types 0x10 / 0x11), nine saved registers. Unless the readouts are
## drawn with the same tank bytes player +0x691 / +0x692 (cached in 0x2c588 / 0x2c58a), both are cached;
## capacity = word (player +4) +0x1c halved toward zero; angle ((tank muls.w 0x1555) divs capacity) +
## 0xf555. Left needle at host +0x25e (end x + offset, y - offset; cos 0x2c, needle 0x44 below), right
## at +0x264 (end centre - offset; cos 0x48, sin 0x2c; needle 0x40).
func update_fuel_needles() -> void:
	assert(memory != null, "update_fuel_needles requires original global memory")
	var player: int = memory.read_u32(0x20b28)
	if memory.read_u16(0x2c596) != 0 and memory.read_s8(player + 0x691) == memory.read_s16(0x2c588) and memory.read_s8(player + 0x692) == memory.read_s16(0x2c58a):
		return
	memory.write_u16(0x2c588, C68kScript.u16(memory.read_s8(player + 0x691)))
	memory.write_u16(0x2c58a, C68kScript.u16(memory.read_s8(player + 0x692)))
	var cap: int = memory.read_s16(memory.read_u32(player + 4) + 0x1c)
	if cap < 0:
		cap = C68kScript.s16(cap + 1)
	cap = C68kScript.s16(cap >> 1)
	var entry_a7: int = a7
	var angle: int = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(0x2c588) * 0x1555), cap) + 0xf555) & 0x3fff
	var c1: Array = _dial_centre(0x25e)
	var o1: Array = _needle_offsets(angle, 0x262, entry_a7, 0x2c)
	_call_draw_gauge_needle(entry_a7, 0x44, 0x10, c1[0], c1[1], c1[0] + o1[0], c1[1] - o1[1])
	angle = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(0x2c58a) * 0x1555), cap) + 0xf555) & 0x3fff
	var c2: Array = _dial_centre(0x264)
	a7 = entry_a7 - 0x48
	var c: int = C68kScript.s16(cos_fixed14(angle))
	a7 = entry_a7 - 0x2c
	var sn: int = C68kScript.s16(sin_fixed14(angle))
	a7 = entry_a7
	var length: int = memory.read_s16(memory.read_u32(0x207fc) + 0x268)
	_call_draw_gauge_needle(entry_a7, 0x40, 0x11, c2[0], c2[1], c2[0] - C68kScript.asr32(c * length, 12), c2[1] - C68kScript.asr32(sn * length, 12))

## 0xc728 (1.0.3's reset_background_rows 0xb440, registers swapped): when background_rows (0x27004)
## is set, each of the sext word host +0x190 view rows gets the span (0, 1, view width 0x21012, fill
## colour 0x23fd0) as words.
func reset_background_rows() -> void:
	assert(memory != null, "reset_background_rows requires original global memory")
	var rows: int = memory.read_u32(0x27004)
	if rows == 0:
		return
	var row: int = 0
	while row < memory.read_s16(memory.read_u32(0x207fc) + 0x190):
		memory.write_u16(rows, 0)
		memory.write_u16(rows + 2, 1)
		memory.write_u16(rows + 4, memory.read_u16(0x21012))
		memory.write_u16(rows + 6, memory.read_u16(0x23fd0))
		rows += 8
		row += 1

## 0xc77e (1.0.3's draw_sky_and_ground 0xb496, derived from its port by one substitution table checked
## against the instruction-by-instruction comparison): the host view geometry +0x18a..+0x19d (1.0.3
## +0x186..), sky / ground colours +0x194 / +0x192, the depth-1 band routine +0x2b6 and cursor hook
## +0x2fe (1.0.3 +0x27e / +0x2c2), the panel copy +0xe4 / +0xe6 / +0xe0 (1.0.3 +0xe0 / +0xe2 / +0xdc), and
## Leyte's globals (cache 0x2c4cc .. 0x2c4e0, row buffers 0x27008 / 0x27004, horizon 0x21578 .. 0x21588,
## span filler 0x26ff0). Leyte tests the lower band's bound before its loop instead of after a jump:
## the same rows. Otherwise as 1.0.3: link.w a6,#-0x14, ten saved registers, body 0x40 below the entry.
func draw_sky_and_ground(hide_cursor: int) -> void:
	assert(memory != null, "draw_sky_and_ground requires original global memory")
	assert(a7 != 0, "draw_sky_and_ground needs a7 (its frame holds the colours and buffer slots)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var upper_colour: int = a6 - 0xe
	var left_colour: int = a6 - 0x10
	var lower_colour: int = a6 - 0x12
	var x_bottom_slot: int = a6 - 0xc
	var world: int = memory.read_u32(0x207fc)
	var same: bool = memory.read_u32(0x27008) != 0
	if same:
		for off in [0, 2, 4, 6]:
			if memory.read_u16(world + 0x18a + off) != memory.read_u16(0x2c4cc + off):
				same = false
		if memory.read_u32(0x2c4cc + 0x10) != memory.read_u32(world + 0x19a):
			same = false
	if not same:
		for k in 5:
			memory.write_u32(0x2c4cc + 4 * k, memory.read_u32(memory.read_u32(0x207fc) + 0x18a + 4 * k))
		if memory.read_u32(0x27008) != 0:
			a7 = entry_a7 - 0x48
			heap_free_coalesce(memory.read_u32(0x27008))
			a7 = entry_a7
			memory.write_u32(0x27008, 0)
		if memory.read_u32(0x27004) != 0:
			a7 = entry_a7 - 0x48
			heap_free_coalesce(memory.read_u32(0x27004))
			a7 = entry_a7
			memory.write_u32(0x27004, 0)
		if memory.read_s16(0x2100e) != 1:
			a7 = entry_a7 - 0x48
			memory.write_u32(0x27008, C68kScript.u32(heap_alloc(C68kScript.s32(memory.read_s16(0x2c4d2) * 20))))
			a7 = entry_a7 - 0x4c
			memory.write_u32(0x27004, C68kScript.u32(heap_alloc(C68kScript.s32(memory.read_s16(0x2c4d2) * 20))))
		a7 = entry_a7 - 0x44
		reset_background_rows()
		a7 = entry_a7
	var half_w: int = memory.read_s16(0x27052)
	var half_h: int = memory.read_s16(0x27054)
	var view_rows: int = memory.read_s16(0x21010)
	var left: int = memory.read_s16(0x2c4cc + 2)
	var scale := func(v: int) -> int:
		return C68kScript.divs_trunc(C68kScript.s32(v * memory.read_s16(0x2c4d2)), view_rows)
	memory.write_u32(0x21588, C68kScript.u32(scale.call(C68kScript.s32(half_w + memory.read_s32(0x21588))) + left))
	memory.write_u32(0x21584, C68kScript.u32(scale.call(C68kScript.s32(half_h + memory.read_s32(0x21584)))))
	memory.write_u32(0x21580, C68kScript.u32(scale.call(C68kScript.s32(half_w + memory.read_s32(0x21580))) + left))
	memory.write_u32(0x2157c, C68kScript.u32(scale.call(C68kScript.s32(half_h + memory.read_s32(0x2157c)))))
	memory.write_u32(0x21588, C68kScript.u32(C68kScript.lsl32(memory.read_u32(0x21588), 16) + 0x8000))
	memory.write_u32(0x21580, C68kScript.u32(C68kScript.lsl32(memory.read_u32(0x21580), 16) + 0x8000))
	var top: int
	var bottom: int
	var x_top: int = 0
	var slope: int = 0
	var side: int = 0
	var y0: int = memory.read_s32(0x21584)
	var y1: int = memory.read_s32(0x2157c)
	if y0 == y1:
		bottom = memory.read_s16(0x21584 + 2)
		top = bottom
	elif y0 > y1:
		bottom = memory.read_s16(0x21584 + 2)
		top = memory.read_s16(0x2157c + 2)
		x_top = memory.read_s32(0x21580)
		memory.write_u32(x_bottom_slot, memory.read_u32(0x21588))
		slope = C68kScript.divs_trunc(C68kScript.s32(memory.read_s32(0x21588) - x_top), C68kScript.s32(y0 - y1))
		side = 1
	else:
		bottom = memory.read_s16(0x2157c + 2)
		top = memory.read_s16(0x21584 + 2)
		x_top = memory.read_s32(0x21588)
		memory.write_u32(x_bottom_slot, memory.read_u32(0x21580))
		slope = C68kScript.divs_trunc(C68kScript.s32(memory.read_s32(0x21580) - x_top), C68kScript.s32(y1 - y0))
		side = 0
	world = memory.read_u32(0x207fc)
	var corners: int = memory.read_u32(0x21578)
	if corners == 0xf:
		top = memory.read_s16(0x2c4d2)
		memory.write_u16(upper_colour, memory.read_u16(world + 0x194))
	elif corners == 0:
		top = memory.read_s16(0x2c4d2)
		memory.write_u16(upper_colour, memory.read_u16(world + 0x192))
	else:
		memory.write_u16(upper_colour, memory.read_u16(world + (0x194 if (corners & 3) != 0 else 0x192)))
		memory.write_u16(lower_colour, memory.read_u16(world + (0x194 if (corners & 0xc) != 0 else 0x192)))
	var rows: int = memory.read_s16(0x2c4d2)
	if top > rows:
		top = rows
	if bottom > rows:
		bottom = rows
	var right_colour: int
	if side != 0:
		memory.write_u16(left_colour, memory.read_u16(world + 0x192))
		right_colour = memory.read_s16(world + 0x194)
	else:
		memory.write_u16(left_colour, memory.read_u16(world + 0x194))
		right_colour = memory.read_s16(world + 0x192)
	if memory.read_s16(0x2100e) == 1:
		if memory.read_u16(0x27010) != 0:
			var parity: int = memory.read_u16(0x2c4e0) & 1
			memory.write_u16(0x2c4e0, C68kScript.u16(memory.read_u16(0x2c4e0) + 1))
			if parity == 0:
				if hide_cursor != 0:
					a7 = entry_a7 - 0x44
					_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
				a7 = entry_a7 - 0x68
				_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2b6), [top, bottom, memory.read_s16(0x2c4d2), memory.read_s16(upper_colour),
						memory.read_s16(lower_colour), C68kScript.asr32(x_top, 16), C68kScript.asr32(memory.read_s32(x_bottom_slot), 16),
						memory.read_s16(left_colour), right_colour])
				if hide_cursor != 0:
					a7 = entry_a7 - 0x44
					_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
				a7 = entry_a7
	else:
		var extents: int = memory.read_u32(0x2c4dc)
		var a1: int = memory.read_u32(0x27008)
		var fill: int = memory.read_u16(0x23fd0)
		var row: int = 0
		while row < top:
			a1 = _bg_row(a1, [[memory.read_u16(extents + 4 * row), fill], [memory.read_u16(extents + 4 * row + 2), memory.read_u16(upper_colour)],
					[memory.read_u16(0x21012), memory.read_u16(0x23fd0)]])
			row += 1
		while row < bottom:
			var x0: int = memory.read_s16(extents + 4 * row)
			var x1: int = memory.read_s16(extents + 4 * row + 2)
			var spans: Array = [[x0, memory.read_u16(0x23fd0)]]
			if C68kScript.s32(C68kScript.lsl32(x0, 16)) >= x_top:
				spans.append([x1, right_colour])
			elif C68kScript.s32(C68kScript.lsl32(x1, 16)) <= x_top:
				spans.append([x1, memory.read_u16(left_colour)])
			else:
				spans.append([C68kScript.asr32(x_top, 16), memory.read_u16(left_colour)])
				spans.append([x1, right_colour])
			spans.append([memory.read_u16(0x21012), memory.read_u16(0x23fd0)])
			a1 = _bg_row(a1, spans)
			x_top = C68kScript.s32(x_top + slope)
			row += 1
		while row < memory.read_s16(0x2c4d2):
			a1 = _bg_row(a1, [[memory.read_u16(extents + 4 * row), memory.read_u16(0x23fd0)], [memory.read_u16(extents + 4 * row + 2), memory.read_u16(lower_colour)],
					[memory.read_u16(0x21012), memory.read_u16(0x23fd0)]])
			row += 1
		world = memory.read_u32(0x207fc)
		var base: int
		var skip: int
		if memory.read_u16(0x27010) == 0:
			skip = 0
			base = C68kScript.u32(C68kScript.s32(memory.read_s16(world + 0xe4) * C68kScript.s32(memory.read_s16(0x2c4cc) - memory.read_s16(world + 0xe6))) + memory.read_u32(world + 0xe0))
		else:
			var per_byte: int = C68kScript.divs_trunc(8, memory.read_s16(0x2100e))
			var width_bytes: int = C68kScript.s16(C68kScript.divs_trunc(memory.read_s16(0x21012), C68kScript.s16(per_byte)))
			skip = C68kScript.s16(memory.read_u16(world + 0x10) - width_bytes)
			base = C68kScript.u32(C68kScript.s32(memory.read_s16(world + 0x10) * memory.read_s16(0x2c4cc)) + memory.read_u32(world + 6))
		memory.write_u32(a6 - 4, memory.read_u32(0x27008))
		memory.write_u32(a6 - 8, memory.read_u32(0x27004))
		a7 = entry_a7 - 0x58
		_jsr(memory.read_u32(0x26ff0), [a6 - 4, a6 - 8, memory.read_s16(0x2c4d2), base, skip])
		a7 = entry_a7
		var tmp: int = memory.read_u32(0x27008)
		memory.write_u32(0x27008, memory.read_u32(0x27004))
		memory.write_u32(0x27004, tmp)
	memory.write_u32(0x2157c, 0)
	memory.write_u32(0x21584, 0)
	memory.write_u32(0x21578, 0)

## 32-bit dbra count (subq.l / cmp #-1 / dbra, then clr.w / subq.l / bcc): the long count as an
## unsigned iteration count.
func _leyte_dbra32_count(n: int) -> int:
	return C68kScript.u32(n)

## 0xccf6 (1.0.3's draw_panel_frame_lines 0xba0a, read from the listing): the four panel frame lines
## (host words +0x270..+0x282, colour +0x294; 1.0.3 +0x238..+0x24a, +0x25c), five saved registers (1.0.3
## four). With the host line routine (word +0x9e), unless the view depth (0x2100e) is 1: two vertical
## lines (colour, x +0x278, top +0x270 / +0x274, x +0x278, bottom +0x272 / +0x276 - 2) and two horizontal
## ones (colour, left +0x27a / +0x27e, y +0x282, right +0x27c / +0x280 - 2, y +0x282) through host +0x2aa
## (0x2c / 0x40 below: each pair's arguments popped together). Otherwise drawn into the screen: depth 4
## with the byte (c << 4) | c of colour byte +0x295, depth 8 with the word (c << 8) | c of word +0x294:
## columns from the addresses at host +0x284 / +0x288 stepping one row (word +0x10, halved toward zero at
## depth 8 and stepped in words) for (bottom - top) rows, and double rows (the address and the one a row
## below) from +0x28c / +0x290 for (right - left) / 2 pixels. Leyte lays the loops out per depth where
## 1.0.3 shared them (24 more instructions); the same writes.
func draw_panel_frame_lines() -> void:
	assert(memory != null, "draw_panel_frame_lines requires original global memory")
	var world: int = memory.read_u32(0x207fc)
	var depth: int = memory.read_s16(0x2100e)
	var entry_a7: int = a7
	if memory.read_u16(world + 0x9e) != 0:
		if depth == 1:
			return
		a7 = entry_a7 - 0x2c
		_jsr(memory.read_u32(world + 0x2aa), [memory.read_s16(world + 0x294), memory.read_s16(world + 0x278), memory.read_s16(world + 0x270),
				memory.read_s16(world + 0x278), memory.read_s16(world + 0x272) - 2])
		world = memory.read_u32(0x207fc)
		a7 = entry_a7 - 0x40
		_jsr(memory.read_u32(world + 0x2aa), [memory.read_s16(world + 0x294), memory.read_s16(world + 0x278), memory.read_s16(world + 0x274),
				memory.read_s16(world + 0x278), memory.read_s16(world + 0x276) - 2])
		world = memory.read_u32(0x207fc)
		a7 = entry_a7 - 0x2c
		_jsr(memory.read_u32(world + 0x2aa), [memory.read_s16(world + 0x294), memory.read_s16(world + 0x27a), memory.read_s16(world + 0x282),
				memory.read_s16(world + 0x27c) - 2, memory.read_s16(world + 0x282)])
		world = memory.read_u32(0x207fc)
		a7 = entry_a7 - 0x40
		_jsr(memory.read_u32(world + 0x2aa), [memory.read_s16(world + 0x294), memory.read_s16(world + 0x27e), memory.read_s16(world + 0x282),
				memory.read_s16(world + 0x280) - 2, memory.read_s16(world + 0x282)])
		a7 = entry_a7
		return
	if depth != 4 and depth != 8:
		return
	var step: int
	var unit: int
	var colour: int
	if depth == 4:
		step = memory.read_s16(world + 0x10)
		unit = 1
		var c: int = memory.read_u8(world + 0x295)
		colour = ((c << 4) | c) & 0xff
	else:
		var row: int = memory.read_s16(world + 0x10)
		if row < 0:
			row += 1
		step = C68kScript.s16(row >> 1)
		unit = 2
		var w: int = memory.read_u16(world + 0x294)
		colour = ((w << 8) | w) & 0xffff
	var put := func(addr: int) -> void:
		if unit == 1:
			memory.write_u8(addr, colour)
		else:
			memory.write_u16(addr, colour)
	for col in [[0x270, 0x272, 0x284], [0x274, 0x276, 0x288]]:
		world = memory.read_u32(0x207fc)
		var n: int = _leyte_dbra32_count(C68kScript.s32(memory.read_s16(world + col[1]) - memory.read_s16(world + col[0])))
		var addr: int = memory.read_u32(world + col[2])
		for i in n:
			put.call(addr)
			addr = C68kScript.u32(addr + step * unit)
	for rw in [[0x27a, 0x27c, 0x28c], [0x27e, 0x280, 0x290]]:
		world = memory.read_u32(0x207fc)
		var width: int = C68kScript.s32(memory.read_s16(world + rw[1]) - memory.read_s16(world + rw[0]))
		if width < 0:
			width += 1
		var n: int = _leyte_dbra32_count(width >> 1)
		var a0: int = memory.read_u32(memory.read_u32(0x207fc) + rw[2])
		var a1: int = C68kScript.u32(a0 + step * unit)
		for i in n:
			put.call(a0)
			put.call(a1)
			a0 = C68kScript.u32(a0 + unit)
			a1 = C68kScript.u32(a1 + unit)

## 0x1bcea (1.0.3's update_radar_scope 0x17b48, read from the listing): the radar scope. Link a6 -8 and
## ten registers (body 0x34 below); sin / cos of neg_camera_heading (0x23d3e; 8 / 0xc below). On a
## colour screen (host +0x9e == 0) last frame's blips (list 0x20748, word count 0x2cc28) that were drawn
## (+0x10) are erased through *0x23fc0(x, y) (0xc below). Each current track (list 0x20744, word count
## 0x2cc2c): dx / dz = (track - player object +4 / +0xc) divs.l radar scale (0x2cc18), low words (1.0.3
## read +0x18 / +0x20); screen x = centre x (+0x1ec) + word((dx * cos - dz * sin) asr 16), y = centre y
## (+0x1ee) - word((dx * sin + dz * cos) asr 16) replace the track position; inside the scope (+0x1e4 <= x
## < +0x1e8, +0x1e2 <= y < +0x1e6) it is drawn - *0x23fc4(x, y) (0xc below) on colour, else two host +0x2aa
## lines in colour +0xe8 at the scaled offsets (colour, dx, dz - 1, dx, dz + 1) (0x18 below) and (colour,
## dx - 1, dz, dx + 1, dz) (0x2c below) - and +0x10 = 1, else +0x10 = 0. Then the drawn count (0x2cc26)
## takes the track count, the two lists swap and the track count (0x2cc2a) is cleared.
func update_radar_scope() -> void:
	assert(memory != null, "update_radar_scope requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x34
	var player: int = memory.read_u32(memory.read_u32(0x20b28))
	var host: int = memory.read_u32(0x207fc)
	var centre_x: int = memory.read_s16(host + 0x1ec)
	var centre_y: int = memory.read_s16(host + 0x1ee)
	a7 = body - 8
	var sin_h: int = C68kScript.s16(sin_fixed14(memory.read_s16(0x23d3e)))
	a7 = body - 0xc
	var cos_h: int = C68kScript.s16(cos_fixed14(memory.read_s16(0x23d3e)))
	a7 = entry_a7
	host = memory.read_u32(0x207fc)
	if memory.read_u16(host + 0x9e) == 0:
		var blip: int = memory.read_u32(0x20748)
		var left: int = memory.read_u16(0x2cc26 + 2)
		while left != 0:
			left -= 1
			if memory.read_u32(blip + 0x10) != 0:
				a7 = body - 0xc
				_jsr(memory.read_u32(0x23fc0), [memory.read_u32(blip), memory.read_u32(blip + 4)])
				a7 = entry_a7
			blip = C68kScript.u32(blip + 0x14)
	var track: int = memory.read_u32(0x20744)
	var left_tracks: int = memory.read_u16(0x2cc2a + 2)
	while left_tracks != 0:
		left_tracks -= 1
		var scale: int = memory.read_s16(0x2cc18)
		assert(scale != 0, "update_radar_scope: divs.l by zero traps")
		var dx: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(track) - memory.read_u32(player + 4)), scale))
		var dz: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(track + 4) - memory.read_u32(player + 0xc)), scale))
		var sx: int = C68kScript.s16(C68kScript.s16(C68kScript.asr32(C68kScript.s32(dx * cos_h - dz * sin_h), 16)) + centre_x)
		memory.write_u32(track, C68kScript.u32(sx))
		var sy: int = C68kScript.s16(centre_y - C68kScript.s16(C68kScript.asr32(C68kScript.s32(dx * sin_h + dz * cos_h), 16)))
		memory.write_u32(track + 4, C68kScript.u32(sy))
		host = memory.read_u32(0x207fc)
		if sx < memory.read_s16(host + 0x1e4) or sx >= memory.read_s16(host + 0x1e8) or sy < memory.read_s16(host + 0x1e2) or sy >= memory.read_s16(host + 0x1e6):
			memory.write_u32(track + 0x10, 0)
		else:
			if memory.read_u16(host + 0x9e) != 0:
				var colour: int = memory.read_s16(host + 0xe8)
				a7 = body - 0x18
				_jsr(memory.read_u32(host + 0x2aa), [C68kScript.u32(colour), C68kScript.u32(dx), C68kScript.u32(dz - 1), C68kScript.u32(dx), C68kScript.u32(dz + 1)])
				host = memory.read_u32(0x207fc)
				colour = memory.read_s16(host + 0xe8)
				a7 = body - 0x2c
				_jsr(memory.read_u32(host + 0x2aa), [C68kScript.u32(colour), C68kScript.u32(dx - 1), C68kScript.u32(dz), C68kScript.u32(dx + 1), C68kScript.u32(dz)])
				a7 = entry_a7
			else:
				a7 = body - 0xc
				_jsr(memory.read_u32(0x23fc4), [C68kScript.u32(sx), C68kScript.u32(sy)])
				a7 = entry_a7
			memory.write_u32(track + 0x10, 1)
		track = C68kScript.u32(track + 0x14)
	var drawn: int = memory.read_s16(0x2cc2a + 2)
	memory.write_u32(0x2cc2a, memory.read_u32(0x2cc26))
	memory.write_u32(0x2cc26, C68kScript.u32(drawn))
	var tracks: int = memory.read_u32(0x20744)
	memory.write_u32(0x20744, memory.read_u32(0x20748))
	memory.write_u32(0x20748, tracks)
	memory.write_u32(0x2cc2a, 0)

## 0xd2ae (1.0.3's copy_rect_depth8 0xbf90: the same 49 instructions, its jump table moved with it;
## the correspondence did not find it): copy a columns-by-height block of bytes from src (row stride
## src_row) to dst (row stride dst_row). Leading columns go one at a time until dst is long aligned (a
## zero count stops them); then (count word & ~3) / 4 longs per row, src aligned down and its
## misalignment (0..3, jump table) merged from consecutive source longs; then the remaining columns
## (count & 3, or the whole count when no long fits) one at a time. The height loops are dbra words.
func copy_rect_depth8(dst: int, src: int, count: int, height: int, src_row: int, dst_row: int) -> void:
	assert(memory != null, "copy_rect_depth8 requires original global memory")
	var rows: int = _dbra_word_count(C68kScript.s16(height))
	var left: int = C68kScript.u32(count)
	var d5: int = C68kScript.u32(dst)
	var a2: int = C68kScript.u32(src)
	var column := func(to: int, from: int) -> void:
		for r in rows:
			memory.write_u8(to, memory.read_u8(from))
			to = C68kScript.u32(to + dst_row)
			from = C68kScript.u32(from + src_row)
	var stopped: bool = false
	while (d5 & 3) != 0:
		if left == 0:
			stopped = true
			break
		column.call(d5, a2)
		d5 = C68kScript.u32(d5 + 1)
		a2 = C68kScript.u32(a2 + 1)
		left = C68kScript.u32(left - 1)
	var whole: int = C68kScript.s16(left & 0xfffc)
	var tail_dst: int = C68kScript.u32(d5 + whole)
	var tail_src: int = C68kScript.u32(a2 + whole)
	if whole != 0 and not stopped:
		var longs: int = _dbra_word_count(C68kScript.s16((whole + (3 if whole < 0 else 0)) >> 2))
		var mis: int = a2 & 3
		a2 = C68kScript.u32(a2 - mis)
		for r in rows:
			var to: int = d5
			if mis == 0:
				var from: int = a2
				for k in longs:
					memory.write_u32(to, memory.read_u32(from))
					to += 4
					from += 4
			else:
				var shift: int = 8 * mis
				var acc: int = C68kScript.u32(memory.read_u32(a2) << shift)
				var from2: int = C68kScript.u32(a2 + 4)
				for k in longs:
					var nxt: int = memory.read_u32(from2)
					from2 += 4
					memory.write_u32(to, C68kScript.u32(acc | (nxt >> (32 - shift))))
					to += 4
					acc = C68kScript.u32(nxt << shift)
			d5 = C68kScript.u32(d5 + dst_row)
			a2 = C68kScript.u32(a2 + src_row)
		left = left & 3
	while left != 0:
		column.call(tail_dst, tail_src)
		tail_dst = C68kScript.u32(tail_dst + 1)
		tail_src = C68kScript.u32(tail_src + 1)
		left = C68kScript.u32(left - 1)

## 0xd4b2 (1.0.3's update_heading_tape 0xc18a, derived from its port by one substitution table checked
## against Leyte's listing, step by step: the same frame, stack depths and column walk): the host tape
## fields 4 bytes up (strip +0x1b4, marks +0x1bc, period +0x1a8, window +0x19e / +0x1a0 / +0x1a2 / +0x1a4,
## position bias +0x1ac, strip rows +0x1aa / +0x1ae, copy source +0x1c0), the host routines 0x38 up (copy
## +0x2d2, rect fill +0x2c6, cursor hook +0x2fe), colour +0x294, panel copy +0xe0 / +0xe4 / +0xe6, the
## cached position 0x2c598, and the depth-8 copy at code address 0xd2ae. The port helpers are 0xd426 /
## 0xd486 (begin / end_offscreen_drawing, verified). As in 1.0.3 the depth-4 strip branch (0xd740, copy
## 0xcfa6) is unreachable: it is only entered at depth 8.
func update_heading_tape(hide_cursor: int) -> void:
	assert(memory != null, "update_heading_tape requires original global memory")
	assert(a7 != 0, "update_heading_tape needs a7 (its frame holds the port and rects)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var port: int = a6 - 0x6c
	var frame_rect: int = a6 - 0x74
	var src_rect: int = a6 - 0x7c
	var dst_rect: int = a6 - 0x84
	var world: int = memory.read_u32(0x207fc)
	var strip: int = memory.read_u32(world + 0x1b4)
	var marks: int = memory.read_u32(world + 0x1bc)
	var period: int = memory.read_s16(world + 0x1a8)
	var cols_left: int = memory.read_s16(world + 0x1a2)
	var height: int = memory.read_s16(world + 0x1a4)
	var strip_row: int = memory.read_u16(world + 0x1ae) & 0x1fff
	var base: int
	var rel_row: int
	var row_bytes: int
	var saved: int = 0
	var panels: bool = memory.read_u16(0x27010) != 0
	if panels:
		base = memory.read_u32(world + 6)
		rel_row = memory.read_s16(world + 0x19e)
		row_bytes = memory.read_s16(world + 0x10)
	else:
		base = memory.read_u32(world + 0xe0)
		rel_row = memory.read_s16(world + 0x19e) - memory.read_s16(world + 0xe6)
		row_bytes = memory.read_s16(world + 0xe4)
		if hide_cursor != 0:
			a7 = entry_a7 - 0xd0
			_jsr(memory.read_u32(world + 0x2fe), [])
		a7 = entry_a7 - 0xe0
		saved = C68kScript.u32(begin_offscreen_drawing(port, row_bytes, base, memory.read_s16(memory.read_u32(0x207fc) + 0xe6)))
		world = memory.read_u32(0x207fc)
		memory.write_u16(frame_rect + 2, memory.read_u16(world + 0x1a0))
		memory.write_u16(frame_rect, memory.read_u16(world + 0x19e))
		memory.write_u16(frame_rect + 6, C68kScript.u16(memory.read_u16(world + 0x1a0) + cols_left))
		memory.write_u16(frame_rect + 4, C68kScript.u16(memory.read_u16(world + 0x19e) + height))
		a7 = entry_a7 - 0xe8
		_jsr(memory.read_u32(world + 0x2c6), [frame_rect, memory.read_s16(world + 0x294)])
		if memory.read_s16(0x2100e) == 8:
			a7 = entry_a7 - 0xd8
			end_offscreen_drawing(port, saved)
		if hide_cursor != 0:
			a7 = entry_a7 - 0xd0
			_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
		a7 = entry_a7
	var turn: int = C68kScript.s32(C68kScript.s32(-memory.read_s16(0x23d3e)) * period)
	if turn < 0:
		turn = C68kScript.s32(turn + 0x3fff)
	turn = turn >> 14
	var half: int = cols_left
	if half < 0:
		half += 1
	half = half >> 1
	var pos: int = C68kScript.s32(C68kScript.s32(turn - half) + memory.read_s16(memory.read_u32(0x207fc) + 0x1ac))
	while pos < 0:
		pos = C68kScript.s32(pos + period)
	var depth: int = memory.read_s16(0x2100e)
	if memory.read_s16(0x2c598) == pos:
		if panels or depth == 8:
			return
		if hide_cursor != 0:
			a7 = entry_a7 - 0xd0
			_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
		_heading_tape_finish(entry_a7, port, saved, hide_cursor)
		return
	var old_pos: int = memory.read_s16(0x2c598)
	memory.write_u16(0x2c598, C68kScript.u16(pos))
	if depth != 8:
		if hide_cursor != 0:
			a7 = entry_a7 - 0xd0
			_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
			a7 = entry_a7
		var target: int = 0 if panels else port
		world = memory.read_u32(0x207fc)
		var left: int = memory.read_u16(world + 0x1a0)
		var top: int = memory.read_u16(world + 0x19e)
		memory.write_u16(dst_rect + 2, left)
		memory.write_u16(dst_rect, top)
		var right: int = C68kScript.u16(left + memory.read_u16(world + 0x1a2))
		memory.write_u16(dst_rect + 6, right)
		memory.write_u16(dst_rect + 4, C68kScript.u16(top + memory.read_u16(world + 0x1a4)))
		memory.write_u16(src_rect + 2, C68kScript.u16(pos))
		memory.write_u16(src_rect, 0)
		memory.write_u16(src_rect + 4, memory.read_u16(world + 0x1aa))
		var src_right: int = C68kScript.u16(pos + cols_left)
		memory.write_u16(src_rect + 6, src_right)
		if period < C68kScript.s16(src_right):
			var over: int = C68kScript.s32(C68kScript.s16(src_right) - period)
			memory.write_u16(src_rect + 6, C68kScript.u16(src_right - over))
			right = C68kScript.u16(right - over)
			memory.write_u16(dst_rect + 6, right)
			a7 = entry_a7 - 0xe4
			_jsr(memory.read_u32(world + 0x2d2), [memory.read_u32(world + 0x1c0), memory.read_u32(world + 0x1b4), src_rect, target, dst_rect])
			a7 = entry_a7
			memory.write_u16(src_rect + 2, 0)
			memory.write_u16(src_rect + 6, C68kScript.u16(over))
			memory.write_u16(dst_rect + 2, memory.read_u16(dst_rect + 6))
			memory.write_u16(dst_rect + 6, C68kScript.u16(memory.read_u16(dst_rect + 6) + over))
		world = memory.read_u32(0x207fc)
		a7 = entry_a7 - 0xe4
		_jsr(memory.read_u32(world + 0x2d2), [memory.read_u32(world + 0x1c0), memory.read_u32(world + 0x1b4), src_rect, target, dst_rect])
		a7 = entry_a7
		if not panels:
			_heading_tape_finish(entry_a7, port, saved, hide_cursor)
		elif hide_cursor != 0:
			a7 = entry_a7 - 0xd0
			_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
			a7 = entry_a7
		return
	# Depth 8: copy the runs of columns marked at the old or new position.
	var mark_new: int = C68kScript.u32(marks + pos)
	var mark_old: int = C68kScript.u32(marks + old_pos)
	var dst: int = C68kScript.u32(C68kScript.u32(base + C68kScript.s32(C68kScript.s16(rel_row) * row_bytes)) + memory.read_s16(memory.read_u32(0x207fc) + 0x1a0))
	var src: int = C68kScript.u32(strip + pos)
	var col: int = pos
	var skip: int = 0
	var run: int = 0
	var old_col: int = old_pos
	var left_cols: int = C68kScript.u32(cols_left)
	while left_cols != 0:
		left_cols -= 1
		col = C68kScript.s32(col + 1)
		var marked: int = memory.read_u8(mark_old) | memory.read_u8(mark_new)
		mark_old = C68kScript.u32(mark_old + 1)
		mark_new = C68kScript.u32(mark_new + 1)
		if marked != 0:
			run += 1
			if col == period:
				dst = C68kScript.u32(dst + skip)
				_heading_tape_copy(entry_a7, dst, C68kScript.u32(src + skip), run, height, strip_row, row_bytes)
				dst = C68kScript.u32(dst + run)
				run = 0
				skip = 0
				src = strip
				col = 0
				mark_new = marks
		else:
			if run != 0:
				dst = C68kScript.u32(dst + skip)
				src = C68kScript.u32(src + skip)
				_heading_tape_copy(entry_a7, dst, src, run, height, strip_row, row_bytes)
				dst = C68kScript.u32(dst + run)
				src = C68kScript.u32(src + run)
				run = 0
				skip = 0
			skip += 1
			if col == period:
				src = strip
				dst = C68kScript.u32(dst + skip)
				skip = 0
				col = 0
				mark_new = marks
		old_col += 1
		if old_col == period:
			old_col = 0
			mark_old = marks
	if run != 0:
		_heading_tape_copy(entry_a7, C68kScript.u32(dst + skip), C68kScript.u32(src + skip), run, height, strip_row, row_bytes)

## copy_rect_depth8 through its code address (0xd2ae; 1.0.3 0xbf90), 0xe8 below the tape's entry.
func _heading_tape_copy(entry_a7: int, dst: int, src: int, run: int, height: int, strip_row: int, row_bytes: int) -> void:
	a7 = entry_a7 - 0xe8
	_jsr(0xd2ae, [dst, src, run, height, strip_row, row_bytes])
	a7 = entry_a7

## end_offscreen_drawing (0xd8 below), then the cursor hook (0xd0 below) when hide_cursor.
func _heading_tape_finish(entry_a7: int, port: int, saved: int, hide_cursor: int) -> void:
	a7 = entry_a7 - 0xd8
	end_offscreen_drawing(port, saved)
	if hide_cursor != 0:
		a7 = entry_a7 - 0xd0
		_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fe), [])
	a7 = entry_a7

## 0xe61e (hook 12; 1.0.3's 0xd23a, written there as the loader's _d23a, plus one call): the readouts
## and overlays (0xe3ca, argument 0, 8 below); with the panels drawn (0x27010) the throttle gauge
## (0xdf20), the radar range buttons (0x1bc0e), the bomb lamp (0xdde6), Leyte's state lamp (0xde86, the
## added call) and the overlays marked drawn (0xe644), each 4 below.
func redraw_view_readouts() -> void:
	assert(memory != null, "redraw_view_readouts requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 8
	update_view_readouts_and_overlays(0)
	a7 = entry_a7
	if memory.read_u16(0x27010) != 0:
		a7 = entry_a7 - 4
		update_throttle_gauge()
		update_radar_range_buttons()
		update_bomb_indicator()
		update_state_lamp()
		mark_view_overlays_drawn()
		a7 = entry_a7

## 0x603a (1.0.3's retire_entity 0x51cc, read from the listing on Leyte's entity layout): clears the
## shot controller (0x21068) when it is obj's controller (+0); when the child indices differ (words
## +0x12 / +0x10; 1.0.3 bytes +2 / +1), the scene root's (0x23c2c) child-table entry (+0x32, 0x18 bytes
## each, +0x14) for index +0x12 is cleared - only when it holds obj (a guard 1.0.3 does not have) - and,
## if index +0x10 lies in [child_window_lo 0x2107a, child_window_hi 0x21078] (sext words, signed), obj
## leaves the seven scatter slots (0x2144c); then x (+4; 1.0.3 +0x18) = 1000000000, flags +0x14 |= 2,
## +0x3e = 0, *controller = 0, retire_count (0x2b22c) += 1. Returns D0 as the bytes leave it: the
## caller's D0 when the indices are equal, else index +0x10 zero-extended, or 7 after the slot scrub.
func retire_entity(obj: int, d0_in: int = 0) -> int:
	assert(memory != null, "retire_entity requires original global memory")
	var d0: int = d0_in
	if memory.read_u32(obj) == memory.read_u32(0x21068):
		memory.write_u32(0x21068, 0)
	if memory.read_u16(obj + 0x12) != memory.read_u16(obj + 0x10):
		var table: int = memory.read_u32(memory.read_u32(0x23c2c) + 0x32)
		var entry: int = C68kScript.u32(table + 0x14 + memory.read_u16(obj + 0x12) * 0x18)
		if memory.read_u32(entry) == C68kScript.u32(obj):
			memory.write_u32(entry, 0)
		d0 = memory.read_u16(obj + 0x10)
		if memory.read_s16(0x2107a) <= d0 and d0 <= memory.read_s16(0x21078):
			for k in 7:
				if memory.read_u32(0x2144c + 4 * k) == C68kScript.u32(obj):
					memory.write_u32(0x2144c + 4 * k, 0)
			d0 = 7
	memory.write_u32(obj + 4, 1000000000)
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 2)
	memory.write_u32(obj + 0x3e, 0)
	if memory.read_u32(obj) != 0:
		memory.write_u32(memory.read_u32(obj), 0)
	memory.write_u32(0x2b22c, C68kScript.u32(memory.read_u32(0x2b22c) + 1))
	return d0

## 0x60dc (1.0.3's dispatch_entity_cleanup 0x526a on Leyte's entity layout): no-op when byte +0x17 bit 1
## (long +0x14 bit 1, set by retire_entity) is set. Retires the entity when it belongs to the scene
## root's published child window (+0x24 == scene root 0x23c2c, sext words child_window_lo <= word +0x10
## <= child_window_hi) or is not a shape (type byte +0x28 != 2); a shape outside the window is stamped
## instead: +0x14 |= 0x42, +0x3e = frame_counter (0x21020). Returns D0.
func dispatch_entity_cleanup(obj: int, d0_in: int = 0) -> int:
	assert(memory != null, "dispatch_entity_cleanup requires original global memory")
	var d0: int = d0_in
	var in_window: bool = false
	if (memory.read_u8(obj + 0x17) & 2) != 0:
		return d0
	if memory.read_u32(obj + 0x24) == memory.read_u32(0x23c2c):
		d0 = memory.read_u16(obj + 0x10)
		in_window = memory.read_s16(0x2107a) <= d0 and d0 <= memory.read_s16(0x21078)
	if in_window or memory.read_u8(obj + 0x28) != 2:
		var entry_a7: int = a7
		a7 = entry_a7 - 8
		d0 = retire_entity(obj, d0)
		a7 = entry_a7
		return d0
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x42)
	memory.write_u32(obj + 0x3e, memory.read_u32(0x21020))
	return d0

## 0x4fce (1.0.3's apply_entity_mission_rates 0x433a, read from the listing on Leyte's entity layout,
## with two clamps 1.0.3 does not have): mission rates for an entity not flagged (byte +0x17 bit 1) in
## phases 1..5 (word host +0xa8): the template record = host +0x8c + the parent's (+0x24) template list
## (+0x2a) entry +0x2e + 0x18 * child index (word +0x10); scale = 1 for byte +0x15 bit 1, else phase - 1.
## Unless the rate (+0x18; 1.0.3 +0xc) is parked (1000000000): record +0xe * scale (muls.l) or, at scale 0,
## record / 2 (toward zero); then held to 0..999999999 (Leyte); with byte +0x16 bit 4 raised to at least
## +0x20 + 0xce40 (Leyte); a shape (type byte +0x28 == 2) above 0x42b209 without byte +0x16 bit 4 is
## clamped to it. For shapes on path 0 every face +0x1c (faces from +0x4a, count word +0x3c) gets the
## record's packed face long +0xa * 5 for the player's object (controller +0 == 0x20b28), else * scale or
## / 2; face records start at record + 0x22 + 0xc * (words +0x1a + +0x1e) and step 0xe + 2 * byte +8.
func apply_entity_mission_rates(entity: int, path: int) -> void:
	assert(memory != null, "apply_entity_mission_rates requires original global memory")
	if (memory.read_u8(entity + 0x17) & 2) != 0:
		return
	var world: int = memory.read_u32(0x207fc)
	var phase: int = memory.read_s16(world + 0xa8)
	if phase <= 0 or phase > 5:
		return
	var list: int = C68kScript.u32(memory.read_u32(memory.read_u32(entity + 0x24) + 0x2a) + 0x1a)
	var rec: int = C68kScript.u32(memory.read_u32(list + 0x14 + memory.read_u16(entity + 0x10) * 0x18) + memory.read_u32(world + 0x8c))
	var scale: int
	if (memory.read_u8(entity + 0x15) & 2) != 0:
		scale = 1
	else:
		scale = C68kScript.s32(memory.read_s16(memory.read_u32(0x207fc) + 0xa8) - 1)
	if memory.read_s32(entity + 0x18) != 1000000000:
		if scale == 0:
			memory.write_u32(entity + 0x18, C68kScript.u32(C68kScript.divs_trunc(memory.read_s32(rec + 0xe), 2)))
		else:
			memory.write_u32(entity + 0x18, C68kScript.u32(scale * memory.read_s32(rec + 0xe)))
		if memory.read_s32(entity + 0x18) < 0 or memory.read_s32(entity + 0x18) > 0x3b9ac9ff:
			memory.write_u32(entity + 0x18, 0x3b9ac9ff)
		if (memory.read_u8(entity + 0x16) & 0x10) != 0:
			var floor_value: int = C68kScript.s32(memory.read_s32(entity + 0x20) + 0xce40)
			if floor_value > memory.read_s32(entity + 0x18):
				memory.write_u32(entity + 0x18, C68kScript.u32(floor_value))
		if memory.read_u8(entity + 0x28) == 2 and memory.read_s32(entity + 0x18) > 0x42b209 and (memory.read_u8(entity + 0x16) & 0x10) == 0:
			memory.write_u32(entity + 0x18, 0x42b209)
	if memory.read_u8(entity + 0x28) != 2 or C68kScript.u32(path) != 0:
		return
	var face_rec: int = C68kScript.u32(rec + 0x22 + memory.read_s16(rec + 0x1a) * 0xc + memory.read_s16(rec + 0x1e) * 0xc)
	var is_player: bool = memory.read_u32(entity) != 0 and memory.read_u32(0x20b28) == memory.read_u32(entity)
	var face_value: int = C68kScript.u32(memory.read_u32(entity + 0x4a) + 0x1c)
	var i: int = 0
	while i < memory.read_s16(entity + 0x3c):
		var v: int = memory.read_s32(face_rec + 0xa)
		if is_player:
			memory.write_u32(face_value, C68kScript.u32((v << 2) + v))
		elif scale == 0:
			memory.write_u32(face_value, C68kScript.u32(C68kScript.divs_trunc(v, 2)))
		else:
			memory.write_u32(face_value, C68kScript.u32(scale * v))
		face_rec = C68kScript.u32(face_rec + 0xe + memory.read_u8(face_rec + 8) * 2)
		face_value = C68kScript.u32(face_value + 0x20)
		i += 1

## 0x5152 (1.0.3's dispatch_entity_tick 0x4484 on Leyte's layout): apply_entity_mission_rates (0x4fce,
## 0x10 below); an entity whose controller (+0; 1.0.3 +8) is of type 8 (word controller +4 -> +0; 1.0.3
## type 5) is hidden (flags +0x14 |= 0x40) unless host word +0xb0 is set (then shown).
func dispatch_entity_tick(entity: int, path: int) -> void:
	assert(memory != null, "dispatch_entity_tick requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x10
	apply_entity_mission_rates(entity, path)
	a7 = entry_a7
	var ctl: int = memory.read_u32(entity)
	if ctl == 0 or memory.read_s16(memory.read_u32(ctl + 4)) != 8:
		return
	if memory.read_u16(memory.read_u32(0x207fc) + 0xb0) != 0:
		memory.write_u32(entity + 0x14, memory.read_u32(entity + 0x14) & 0xffffffbf)
	else:
		memory.write_u32(entity + 0x14, memory.read_u32(entity + 0x14) | 0x40)

## 0x5192 (hook 4; 1.0.3's run_simulation_tick 0x44c8 with Leyte's arrays): one simulation tick. Without
## host word +0xac the pending scatter slots are cleaned (0x6c6a, 0xc below). In mission phases 1..5 (host
## word +0xa8) every object of the arrays 0x20b44 (count 0x20f2c) and 0x20804 (count 0x20b24) on path 0,
## then 0x233bc (count 0x219c4) and 0x22d2c (count 0x219c0) on path 1, goes through dispatch_entity_tick
## (0x5152, 0x14 below; counts re-read each step), then the distance references reset (0x7ff4, 0xc below).
func run_simulation_tick() -> void:
	assert(memory != null, "run_simulation_tick requires original global memory")
	var entry_a7: int = a7
	if memory.read_u16(memory.read_u32(0x207fc) + 0xac) == 0:
		a7 = entry_a7 - 0xc
		clear_pending_entity_slots()
		a7 = entry_a7
	var phase: int = memory.read_s16(memory.read_u32(0x207fc) + 0xa8)
	if phase <= 0 or phase > 5:
		return
	for pass_spec in [[0x20b44, 0x20f2c, 0], [0x20804, 0x20b24, 0], [0x233bc, 0x219c4, 1], [0x22d2c, 0x219c0, 1]]:
		var i: int = 0
		while i < memory.read_s32(pass_spec[1]):
			a7 = entry_a7 - 0x14
			dispatch_entity_tick(memory.read_u32(pass_spec[0] + 4 * i), pass_spec[2])
			a7 = entry_a7
			i += 1
	a7 = entry_a7 - 0xc
	reset_distance_base_globals()
	a7 = entry_a7

## 0x532e (1.0.3's alloc_object_from_desc 0x459a on Leyte's object layout): a new object from a scene
## descriptor. Kind 2: a cleared 0x54-byte object (1.0.3 0x52; heap_alloc_cleared 0x2184), type byte +0x28
## (1.0.3 +0); flags long +0x14 = zero-extended word desc +0x16 (byte +0x16 bit 5 also sets byte +0x15 bit
## 4); position +4 / +8 / +0xc from desc +2 / +6 / +0xa, +0x18 from +0xe, +0x20 from +0x12; words +0x36 /
## +0x38 / +0x3a (0 -> 1) / +0x3c / +0x52 from +0x18 / +0x1a / +0x1c / +0x1e / +0x20; +0x42 = frame
## (0x21020). Kind 3: a cleared 0x36-byte object (1.0.3 0x34): type +0x28, position, +0x18, +0x20, word
## +0x2e from +0x16, +0x2a = desc, word +0x30 from +0x18. Any other tag returns the caller's D1 (d1_in).
func alloc_object_from_desc(desc: int, d1_in: int = 0) -> int:
	assert(memory != null, "alloc_object_from_desc requires original global memory")
	var obj: int
	var tag: int = memory.read_u8(desc)
	if tag == 2:
		obj = heap_alloc_cleared(0x54)
		memory.write_u8(obj + 0x28, tag)
		memory.write_u32(obj + 0x14, memory.read_u16(desc + 0x16))
		if (memory.read_u8(obj + 0x16) & 0x20) != 0:
			memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x10)
		memory.write_u32(obj + 4, memory.read_u32(desc + 2))
		memory.write_u32(obj + 8, memory.read_u32(desc + 6))
		memory.write_u32(obj + 0xc, memory.read_u32(desc + 0xa))
		memory.write_u32(obj + 0x18, memory.read_u32(desc + 0xe))
		memory.write_u32(obj + 0x20, memory.read_u32(desc + 0x12))
		memory.write_u16(obj + 0x36, memory.read_u16(desc + 0x18))
		memory.write_u16(obj + 0x38, memory.read_u16(desc + 0x1a))
		memory.write_u16(obj + 0x3a, memory.read_u16(desc + 0x1c))
		if memory.read_u16(desc + 0x1c) == 0:
			memory.write_u16(obj + 0x3a, 1)
		memory.write_u16(obj + 0x3c, memory.read_u16(desc + 0x1e))
		memory.write_u16(obj + 0x52, memory.read_u16(desc + 0x20))
		memory.write_u32(obj + 0x42, memory.read_u32(0x21020))
		return obj
	if tag == 3:
		obj = heap_alloc_cleared(0x36)
		memory.write_u8(obj + 0x28, tag)
		memory.write_u32(obj + 4, memory.read_u32(desc + 2))
		memory.write_u32(obj + 8, memory.read_u32(desc + 6))
		memory.write_u32(obj + 0xc, memory.read_u32(desc + 0xa))
		memory.write_u32(obj + 0x18, memory.read_u32(desc + 0xe))
		memory.write_u32(obj + 0x20, memory.read_u32(desc + 0x12))
		memory.write_u16(obj + 0x2e, memory.read_u16(desc + 0x16))
		memory.write_u32(obj + 0x2a, desc)
		memory.write_u16(obj + 0x30, memory.read_u16(desc + 0x18))
		return obj
	return d1_in

## 0x24ec (1.0.3's atan_fixed14 0x1e9c, registers reallocated, Leyte's tan_table 0x20194): the largest
## index lo in 0..0x7f with tan_table[lo] <= ratio (signed longs), then ((lo - 0x3f) asl 6 with its low
## word increased by word((t0 - ratio) asl 6 divs.l (t0 - t1))) & 0x3fff.
func atan_fixed14(ratio: int) -> int:
	assert(memory != null, "atan_fixed14 requires the original tan_table in memory")
	ratio = C68kScript.s32(ratio)
	var lo: int = 0
	var hi: int = 0x7f
	while true:
		var sum: int = hi + lo
		if sum < 0:
			sum += 1
		var mid: int = C68kScript.s16(sum >> 1)
		if ratio < memory.read_s32(0x20194 + mid * 4):
			hi = mid
		else:
			lo = mid
		if not (hi - 1 > lo):
			break
	var t0: int = memory.read_s32(0x20194 + lo * 4)
	var t1: int = memory.read_s32(0x20194 + lo * 4 + 4)
	var q: int = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(C68kScript.s32(t0 - ratio), 6)), C68kScript.s32(t0 - t1))
	return C68kScript.u16(((lo - 0x3f) << 6) + q) & 0x3fff

## 0x2468 (1.0.3's acos_fixed14 0x1e1a, registers reallocated, Leyte's cos_table 0x1ff94): x clamped to
## +-0x1000; the largest index lo in 0..0x80 with x <= cos_table[lo] (signed words, a decreasing table),
## then lo asl 6 + word((-(x - c0) asl 6) divs.l (c0 - c1)), the word sum sign-extended.
func acos_fixed14(x: int) -> int:
	assert(memory != null, "acos_fixed14 requires the original cos_table in memory")
	x = C68kScript.s32(x)
	if x > 0x1000:
		x = 0x1000
	if x < -0x1000:
		x = -0x1000
	var lo: int = 0
	var hi: int = 0x80
	while true:
		var sum: int = hi + lo
		if sum < 0:
			sum += 1
		var mid: int = C68kScript.s16(sum >> 1)
		if x > memory.read_s16(0x1ff94 + mid * 2):
			hi = mid
		else:
			lo = mid
		if not (hi - 1 > lo):
			break
	var c0: int = memory.read_s16(0x1ff94 + lo * 2)
	var c1: int = memory.read_s16(0x1ff94 + lo * 2 + 2)
	var q: int = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(C68kScript.s32(-(x - c0)), 6)), c0 - c1)
	return C68kScript.s16((lo << 6) + q)

## 0xf7c0 (1.0.3's isqrt_floor 0xe352): the largest lo in 0..0x7fff with muls.w(lo, lo) <= n (signed).
## Leyte returns it as a sign-extended word (move.w / ext.l) where 1.0.3 moved the long: the same value.
func isqrt_floor(n: int) -> int:
	n = C68kScript.s32(n)
	var lo: int = 0
	var hi: int = 0x7fff
	while true:
		var sum: int = hi + lo
		if sum < 0:
			sum += 1
		var mid: int = C68kScript.s16(sum >> 1)
		if n < mid * mid:
			hi = mid
		else:
			lo = mid
		if not (hi - 1 > lo):
			break
	return lo

## 0x2274 (1.0.3's translate_int3_vecs 0x1c3c; Leyte tests the count before the loop where 1.0.3 jumped to
## its dbra: the same iterations): adds (dx, dy, dz) to count int3 vectors (add.l, stride 12; src and dst
## may alias). 32-bit count: dbra, then clr.w / subq.l / bcc. No frame (movem only).
func translate_int3_vecs(count: int, src: int, dst: int, dx: int, dy: int, dz: int) -> void:
	assert(memory != null, "translate_int3_vecs requires original global memory")
	var left: int = C68kScript.u32(count)
	while left != 0:
		left -= 1
		memory.write_u32(dst, C68kScript.u32(memory.read_s32(src) + dx))
		memory.write_u32(dst + 4, C68kScript.u32(memory.read_s32(src + 4) + dy))
		memory.write_u32(dst + 8, C68kScript.u32(memory.read_s32(src + 8) + dz))
		dst = C68kScript.u32(dst + 0xc)
		src = C68kScript.u32(src + 0xc)

## 0x2326 (1.0.3's matmul3x3_s16 0x1ce2, laid out with a frame): out[i][j] = (a[i][0] * b[0][j] + a[i][1] *
## b[1][j] + a[i][2] * b[2][j]) asr.l 12 as words (muls.w, the sum a 32-bit add.l). out must not alias a
## or b. Link a6 -8 and ten registers: no callee. It carries 1.0.3's name: the generated port's verified
## callers (compose_attitude_matrix_x, ...) reach it by that name. D0 at the rts is the last element's sum asr
## 12 as a long (1.0.3 left b + 6 in A0); no caller reads either.
func matmul3x3_s16(a: int, b: int, out: int) -> int:
	assert(memory != null, "matmul3x3_s16 requires original global memory")
	var last: int = 0
	for i in 3:
		for j in 3:
			var sum: int = C68kScript.s32(memory.read_s16(a + 6 * i) * memory.read_s16(b + 2 * j)
					+ memory.read_s16(a + 6 * i + 2) * memory.read_s16(b + 6 + 2 * j))
			sum = C68kScript.s32(sum + memory.read_s16(a + 6 * i + 4) * memory.read_s16(b + 0xc + 2 * j))
			last = C68kScript.asr32(sum, 12)
			memory.write_u16(out + 6 * i + 2 * j, C68kScript.u16(last))
	return last

## 0x36fa (Leyte only; 1.0.3 walked the chain inside apply_template_chain_xform): composes obj's parent
## chain into one matrix (out, 9 words) and translation (out + 0x12, three longs). Link a6 -0x7c and ten
## registers (body 0xa8 below the entry). The translation (a6-relative buffer pair a6 - 0x24 .. / out +
## 0x12 and a6 - 0x48) starts at 0 and the matrix at identity (words 0 / 8 / 0x10 = 0x1000; memzero 0xb4
## / 0xbc below). For each parent (+0x24 chain, the child's slot index word +0x12): its slot (parent +0x32,
## 0x18 bytes each) gives the translation (+8 / +0xc / +0x10); when an angle word (+2 / +4 / +6) is non-zero
## the slot's rotation R (build_attitude_matrix_3ang 0x2ce2, into a6 - 0x12, 0xbc below) is folded in - a
## copy the first time (memmove_block 0xb4 below), then the accumulated matrix x R (0x2326, 0xb4 below,
## into the other buffer, the two swapped) - and the translation becomes (t . R) asr 12 per component
## (muls.l of each sext row word by the component, the three rows summed) + the slot's; otherwise the
## slot's translation is added. The matrix is copied to out unless already there, the translation
## stored at out + 0x12. Returns 1 when any rotation was folded in, else 0.
func compose_template_chain(obj: int, out: int) -> int:
	assert(memory != null, "compose_template_chain requires original global memory")
	assert(a7 != 0, "compose_template_chain needs a7 (its frame holds the work buffers)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var rotated: int = 0
	var acc: int = out
	var other: int = a6 - 0x24
	var t: int = out + 0x12
	var t_other: int = a6 - 0x48
	var rot: int = a6 - 0x12
	a7 = entry_a7 - 0xb4
	memzero(t, 0xc)
	a7 = entry_a7 - 0xbc
	memzero(acc, 0x12)
	a7 = entry_a7
	memory.write_u16(acc + 0x10, 0x1000)
	memory.write_u16(acc + 8, 0x1000)
	memory.write_u16(acc, 0x1000)
	var parent: int = memory.read_u32(obj + 0x24)
	var index: int = memory.read_u16(obj + 0x12)
	while parent != 0:
		var slot: int = C68kScript.u32(memory.read_u32(parent + 0x32) + index * 0x18)
		var tx: int = memory.read_s32(slot + 8)
		var ty: int = memory.read_s32(slot + 0xc)
		var tz: int = memory.read_s32(slot + 0x10)
		if memory.read_s16(slot + 2) != 0 or memory.read_s16(slot + 4) != 0 or memory.read_s16(slot + 6) != 0:
			a7 = entry_a7 - 0xbc
			build_attitude_matrix_3ang(memory.read_s16(slot + 2), memory.read_s16(slot + 4), memory.read_s16(slot + 6), rot)
			a7 = entry_a7
			if rotated != 0:
				a7 = entry_a7 - 0xb4
				matmul3x3_s16(acc, rot, other)
				a7 = entry_a7
				var swap: int = acc
				acc = other
				other = swap
			else:
				a7 = entry_a7 - 0xb4
				memmove_block(rot, acc, 0x12)
				a7 = entry_a7
			var sums: Array[int] = [0, 0, 0]
			for k in 3:
				var comp: int = memory.read_s32(t + 4 * k)
				if comp != 0:
					for c in 3:
						sums[c] = C68kScript.s32(sums[c] + C68kScript.s32(memory.read_s16(rot + 6 * k + 2 * c) * comp))
			memory.write_u32(t_other, C68kScript.u32(C68kScript.asr32(sums[0], 12) + tx))
			memory.write_u32(t_other + 4, C68kScript.u32(C68kScript.asr32(sums[1], 12) + ty))
			memory.write_u32(t_other + 8, C68kScript.u32(C68kScript.asr32(sums[2], 12) + tz))
			var tswap: int = t
			t = t_other
			t_other = tswap
			rotated = 1
		else:
			memory.write_u32(t, C68kScript.u32(memory.read_s32(t) + tx))
			memory.write_u32(t + 4, C68kScript.u32(memory.read_s32(t + 4) + ty))
			memory.write_u32(t + 8, C68kScript.u32(memory.read_s32(t + 8) + tz))
		index = memory.read_u16(parent + 0x12)
		parent = memory.read_u32(parent + 0x24)
	if acc != out:
		a7 = entry_a7 - 0xb4
		memmove_block(acc, out, 0x12)
		a7 = entry_a7
	var x: int = memory.read_u32(t)
	var y: int = memory.read_u32(t + 4)
	var z: int = memory.read_u32(t + 8)
	memory.write_u32(out + 0x12, x)
	memory.write_u32(out + 0x16, y)
	memory.write_u32(out + 0x1a, z)
	return rotated

## 0x3524 (1.0.3's apply_template_chain_xform 0x2bb8, restructured around 0x36fa; link a6 -0x2c, five saved
## registers): the chain composed into the frame (matrix a6 - 0x1e, translation a6 - 0xc; 0x36fa entered
## 0x50 below). Point path (src or dst 0): the object position (+4..+0xc) copied to a6 - 0x2a, rotated
## when the chain rotates (0x3418, one vector) and translated (0x2274), then ALWAYS committed back (1.0.3
## only when src and dst were both 0). Mesh path: vert_count vertices rotated src -> dst when the chain
## rotates (then src = dst) and translated src -> dst by the translation << word +0x36 (1.0.3 +0x34).
func apply_template_chain_xform(obj: int, vert_count: int, src: int, dst: int) -> void:
	assert(memory != null, "apply_template_chain_xform requires original global memory")
	assert(a7 != 0, "apply_template_chain_xform needs a7 (its frame holds the composed chain)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var chain: int = a6 - 0x1e
	var pos: int = a6 - 0x2a
	var body: int = entry_a7 - 4 - 0x2c - 0x14
	if src == 0 or dst == 0:
		a7 = body - 0xc
		var rotates: int = compose_template_chain(obj, chain)
		a7 = entry_a7
		memory.write_u32(pos, memory.read_u32(obj + 4))
		memory.write_u32(pos + 4, memory.read_u32(obj + 8))
		memory.write_u32(pos + 8, memory.read_u32(obj + 0xc))
		if rotates != 0:
			a7 = body - 0x14
			transform_int3_vecs_by_matrix3(1, pos, pos, chain)
			a7 = entry_a7
		a7 = body - 0x1c
		translate_int3_vecs(1, pos, pos, memory.read_s32(a6 - 0xc), memory.read_s32(a6 - 8), memory.read_s32(a6 - 4))
		a7 = entry_a7
		memory.write_u32(obj + 4, memory.read_u32(pos))
		memory.write_u32(obj + 8, memory.read_u32(pos + 4))
		memory.write_u32(obj + 0xc, memory.read_u32(pos + 8))
		return
	a7 = body - 0xc
	var rot: int = compose_template_chain(obj, chain)
	a7 = entry_a7
	if rot != 0:
		a7 = body - 0x14
		transform_int3_vecs_by_matrix3(vert_count, src, dst, chain)
		a7 = entry_a7
		src = dst
	var shift: int = memory.read_s16(obj + 0x36) & 0x3f
	a7 = body - 0x1c
	translate_int3_vecs(vert_count, src, dst, C68kScript.s32(C68kScript.lsl32(memory.read_u32(a6 - 0xc), shift)),
			C68kScript.s32(C68kScript.lsl32(memory.read_u32(a6 - 8), shift)), C68kScript.s32(C68kScript.lsl32(memory.read_u32(a6 - 4), shift)))
	a7 = entry_a7

## Shared band search of 0x49cc / 0x4a3a (1.0.3's _distance_band on Leyte's band_limits table 0x203b8):
## with b = the band word, d >= T[b] steps b up while T[b] <= d; d < T[b - 1] steps down from b - 2 while
## d < T[b] (word index), then b + 1; otherwise the band is kept (returns 0).
func _distance_band(band_word: int, dist: int) -> int:
	var band: int = memory.read_s16(band_word)
	dist = C68kScript.s32(dist)
	if dist < memory.read_s32(0x203b8 + band * 4):
		if memory.read_s32(0x203b8 - 4 + band * 4) <= dist:
			return 0
		band = C68kScript.s16(band - 2)
		while dist < memory.read_s32(0x203b8 + band * 4):
			band = C68kScript.s16(band - 1)
		band = C68kScript.s16(band + 1)
	else:
		band = C68kScript.s16(band + 1)
		while memory.read_s32(0x203b8 + band * 4) <= dist:
			band = C68kScript.s16(band + 1)
	memory.write_u16(band_word, C68kScript.u16(band))
	return 1

## 0x49cc (1.0.3's update_object_distance_band 0x3d88): band word +0x32 of an object (1.0.3 +0x30).
func update_object_distance_band(obj: int, dist: int) -> int:
	assert(memory != null, "update_object_distance_band requires original global memory")
	return _distance_band(obj + 0x32, dist)

## 0x4a3a (1.0.3's update_record_distance_band 0x3de2): band word +4 of a record (unchanged).
func update_record_distance_band(rec: int, dist: int) -> int:
	assert(memory != null, "update_record_distance_band requires original global memory")
	return _distance_band(rec + 4, dist)

## 0x4aec (1.0.3's shade_object_faces 0x3e80, derived from its port by the object-field table the aligned
## listings give; Leyte checks the face loop's bound before entry, the same faces): flat shading of a
## kind-2 object (type byte +0x28; not byte +0x17 bit 1). Per face (unless face byte +0x13 bit 2: I =
## 0xffff): I = (dx * light_x + dy * light_y + dz * light_z) asr 13 (light 0x21574 / 0x21572 / 0x21570), d =
## normal point i (the +0x46 entries after the word +0x38 vertices) - vertex 0; factor, base colours from
## the template's packed faces (parent +0x24 template list +0x2a, child index word +0x10), depth 1 / 4 /
## else and the haze (host +0xaa, band word +0x32, unless flags +0x14 & 0x1280) as in 1.0.3; faces from
## +0x4a, count word +0x3c, owner table 0x23fac. Afterwards snapshot_face_colours (0x4aa8) with the haze
## option on and byte +0x16 bit 1.
func shade_object_faces(obj: int) -> void:
	assert(memory != null, "shade_object_faces requires original global memory")
	if (memory.read_u8(obj + 0x17) & 2) != 0 or memory.read_u8(obj + 0x28) != 2:
		return
	var world: int = memory.read_u32(0x207fc)
	var haze_option: int = memory.read_s16(world + 0xaa)
	var haze: int = haze_option
	var band: int = 0
	var haze_rgb: Array = [0, 0, 0]
	if haze_option != 0:
		band = memory.read_s16(obj + 0x32)
		haze_rgb = [C68kScript.s32(band * 0x7d), C68kScript.s32(band * 0x84), C68kScript.s32(band * 0xa8)]
		if (memory.read_u32(obj + 0x14) & 0x1280) != 0:
			haze = 0
	var depth: int = memory.read_s16(world + 0xe)
	var v0: int = memory.read_u32(obj + 0x46)
	var normals: int = v0 + memory.read_s16(obj + 0x38) * 0xc
	var desc: int = C68kScript.u32(memory.read_u32(world + 0x8c) + memory.read_u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18))
	var base_rgb: int = desc + 0x22 + memory.read_s16(desc + 0x1a) * 0xc + memory.read_s16(desc + 0x1e) * 0xc
	var face: int = memory.read_u32(obj + 0x4a)
	var inv_band: int = 0x20 - band
	var i: int = 0
	while i < memory.read_s16(obj + 0x3c):
		var intensity: int
		if (memory.read_u8(face + 0x13) & 4) != 0:
			intensity = 0xffff
		else:
			var n: int = normals + i * 0xc
			var dx: int = C68kScript.s16(memory.read_u16(n + 2) - memory.read_u16(v0 + 2))
			var dy: int = C68kScript.s16(memory.read_u16(n + 6) - memory.read_u16(v0 + 6))
			var dz: int = C68kScript.s16(memory.read_u16(n + 10) - memory.read_u16(v0 + 10))
			intensity = C68kScript.asr32(dx * memory.read_s16(0x21574) + dy * memory.read_s16(0x21572) + dz * memory.read_s16(0x21570), 13)
		var factor: int
		if intensity < 0:
			factor = C68kScript.u16(C68kScript.divs_trunc(intensity + 0x10000, 4) + 0x4000)
		else:
			factor = C68kScript.u16(C68kScript.divs_trunc(intensity, 2) + 0x8000)
		var rgb: Array = []
		for k in range(3):
			var product: int = memory.read_u8(base_rgb + k) * factor
			if depth == 4:
				rgb.append(C68kScript.u8(C68kScript.asr32(C68kScript.divs_trunc(product, 64) + 0x2000, 16)))
			else:
				rgb.append(C68kScript.u8(C68kScript.asr32(product, 16)))
		if depth == 1 or depth == 4:
			for k in range(3):
				memory.write_u8(face + 0xc + k, rgb[k])
			assign_face_colour(face)
		else:
			if haze != 0:
				for k in range(3):
					rgb[k] = C68kScript.u8(C68kScript.u32(rgb[k] * inv_band + haze_rgb[k]) >> 5)
			for k in range(3):
				memory.write_u8(face + 0xc + k, rgb[k])
			if face == memory.read_u32(memory.read_u32(0x23fac) + memory.read_u8(face + 0xf) * 4):
				write_face_palette_slot(face)
			else:
				memory.write_u8(face + 0xf, 0)
		base_rgb = base_rgb + 0xe + memory.read_u8(base_rgb + 8) * 2
		face = face + 0x20
		i += 1
	if haze_option != 0 and (memory.read_u8(obj + 0x16) & 2) != 0:
		snapshot_face_colours(obj)

## 0x1b90 (1.0.3's write_face_palette_slot 0x1528, registers reallocated): queue_face_palette_entry
## (0x1ac2); with the multi-screen flag (host word +0xb4), the slot of every other non-null owner table
## (0x23fa0 / 0x23fa4 / 0x23fa8) gets -face.
func write_face_palette_slot(face: int) -> void:
	assert(memory != null, "write_face_palette_slot requires original global memory")
	queue_face_palette_entry(face)
	if memory.read_s16(memory.read_u32(0x207fc) + 0xb4) == 0:
		return
	for n in range(3):
		var table: int = memory.read_u32(0x23fa0 + 4 * n)
		if table != 0 and memory.read_u32(0x23fac) != table:
			memory.write_u32(table + memory.read_u8(face + 0xf) * 4, C68kScript.u32(-face))


## 0x561c (1.0.3's build_object_poly_buffers 0x47e8, derived from its port by the aligned listings' table:
## face count word +0x3c, vertex count word +0x3a, faces +0x4a, flag byte +0x16 bit 1, palette_changed
## 0x23fd2; Leyte checks both loops' bounds before entry, the same iterations; the face records unchanged).
## Face records from the packed stream: heap_alloc(0x20 * faces (+ 6 * faces + 2 * vertex count with the
## flag)) at +0x4a; per face +8 = 0, +0xc long, bytes +0x10..+0x13, word +0x14 = count byte +8, +0x16 /
## +0x17 = high / low nibble of byte +9, +0x1c = long +0xa, +0x18 = heap_alloc(count * 2) holding the
## count index words; the stream advances 0xe + 2 * count. With the flag, words 0 .. vertex count - 1
## follow the face records. Sets palette_changed.
func build_object_poly_buffers(obj: int, src: int) -> void:
	assert(memory != null, "build_object_poly_buffers requires original global memory")
	var size: int = memory.read_s16(obj + 0x3c) * 0x20
	if (memory.read_u8(obj + 0x16) & 2) != 0:
		size = memory.read_s16(obj + 0x3c) * 6 + memory.read_s16(obj + 0x3a) * 2 + size
	var face: int = heap_alloc(C68kScript.s32(size))
	memory.write_u32(obj + 0x4a, face)
	var i: int = 0
	while i < memory.read_s16(obj + 0x3c):
		memory.write_u32(face + 8, 0)
		memory.write_u32(face + 0xc, memory.read_u32(src))
		for k in range(4):
			memory.write_u8(face + 0x10 + k, memory.read_u8(src + 4 + k))
		memory.write_u16(face + 0x14, memory.read_u8(src + 8))
		memory.write_u8(face + 0x16, memory.read_u8(src + 9) & 0xf0)
		memory.write_u8(face + 0x17, memory.read_u8(src + 9) & 0xf)
		memory.write_u32(face + 0x1c, memory.read_u32(src + 0xa))
		src = src + 0xe
		var indices: int = heap_alloc(memory.read_s16(face + 0x14) << 1)
		memory.write_u32(face + 0x18, indices)
		var k2: int = 0
		while k2 < memory.read_s16(face + 0x14):
			memory.write_u16(indices + k2 * 2, memory.read_u16(src + k2 * 2))
			k2 += 1
		src = src + memory.read_s16(face + 0x14) * 2
		face = face + 0x20
		i += 1
	if (memory.read_u8(obj + 0x16) & 2) != 0:
		var trailer: int = memory.read_u32(obj + 0x4a) + memory.read_s16(obj + 0x3c) * 0x20
		i = 0
		while i < memory.read_s16(obj + 0x3a):
			memory.write_u16(trailer + i * 2, C68kScript.u16(i))
			i += 1
	memory.write_u16(0x23fd2, 1)

## 0x577a (1.0.3's build_land_outline_at_detail 0x4938, derived from its port by the aligned listings'
## tables). Frame link.w a6,#-0x1cf8 (1.0.3 -0x1c08): the work buffers below -0x8c0 moved down - relative
## points -0x1cd0, transformed vertices -0x16d0, unique points -0xfc8 (room for 150 where 1.0.3 had 140),
## last x / z -0x1ce4 / -0x1ce8; clip buffer -0x8c0 and outline -0x460 unchanged; the chain transform
## (0x3524) entered 0x1d38 below. Object fields: template list +0x2a, child index word +0x10, faces +0x4a /
## count word +0x3c, vertices +0x46 after word +0x38, flags +0x14, second vertex buffer +0x4e, position
## x / z +4 / +0xc, range +0x20, metric +0x1c, shift word +0x36, steps word +0x34. Globals: detail ref x / z
## 0x23c54 / 0x23c4c, camera x / z 0x23d68 / 0x23d60, clip bounds 0x2701a / 0x27018 / 0x27016 / 0x20b2e.
## Otherwise as 1.0.3 (the face records unchanged; Leyte checks the unique-point search before entry).
func build_land_outline_at_detail(obj: int, range_value: int, shift: int, limit: int) -> void:
	assert(memory != null, "build_land_outline_at_detail requires original global memory")
	assert(a7 != 0, "build_land_outline_at_detail needs a7 (its frame holds the work buffers)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var last_x: int = a6 - 0x1ce4
	var last_z: int = a6 - 0x1ce8
	var rel_pts: int = a6 - 0x1cd0
	var xf_verts: int = a6 - 0x16d0
	var unique_pts: int = a6 - 0xfc8
	var clip_buf: int = a6 - 0x8c0
	var outline: int = a6 - 0x460
	var world: int = memory.read_u32(0x207fc)
	var rec: int = C68kScript.u32(memory.read_u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18) + memory.read_u32(world + 0x8c))
	var i: int = 0
	while i < memory.read_s16(obj + 0x3c):
		var vert: int = memory.read_u32(obj + 0x46) + (memory.read_s16(obj + 0x38) + i) * 0xc
		for k in range(3):
			memory.write_u32(rel_pts + i * 0xc + 4 * k, C68kScript.u32(memory.read_u32(vert + 4 * k) - memory.read_u32(memory.read_u32(obj + 0x46) + 4 * k)))
		i = C68kScript.s16(i + 1)
	var face_src: int = C68kScript.u32(rec + 0x22 + memory.read_s16(rec + 0x1a) * 0xc + memory.read_s16(rec + 0x1e) * 0xc)
	a7 = entry_a7 - 0x1d38
	apply_template_chain_xform(obj, memory.read_s16(rec + 0x1a), C68kScript.u32(rec + 0x22), xf_verts)
	a7 = entry_a7
	var unique: int = 0
	var divisor: int = C68kScript.s16(memory.read_u16(world + 0xa8) + 3)
	var neg_limit: int = C68kScript.u16(-limit)
	var face_i: int = 0
	while face_i < memory.read_s16(obj + 0x3c):
		var idx: int = face_src + 0xe
		var cursor: int = outline
		var skipped: int = 0
		var count: int = 0
		while count < memory.read_u8(face_src + 8):
			var v: int = memory.read_s16(idx + count * 2)
			var vx: int = memory.read_s32(xf_verts + v * 0xc)
			var vz: int = memory.read_s32(xf_verts + v * 0xc + 8)
			var keep: bool = count == 0
			if not keep:
				skipped = C68kScript.s32(skipped + approx_dist3_xyz(memory.read_s32(last_x), 0, memory.read_s32(last_z), vx, 0, vz))
				var cam: int = approx_dist3_xyz(memory.read_s32(0x23d68), 0, memory.read_s32(0x23d60), vx, 0, vz)
				keep = C68kScript.divs_trunc(cam, divisor) <= skipped
			if keep:
				memory.write_u32(last_x, C68kScript.u32(vx))
				memory.write_u32(last_z, C68kScript.u32(vz))
				skipped = 0
				memory.write_u32(cursor, C68kScript.u32(C68kScript.asr32(C68kScript.s32(vx - memory.read_s32(0x23c54)), shift)))
				memory.write_u32(cursor + 4, C68kScript.u32(C68kScript.asr32(C68kScript.s32(vz - memory.read_s32(0x23c4c)), shift)))
				cursor += 8
			count = C68kScript.s16(count + 1)
		count = C68kScript.s16(C68kScript.asr32(cursor - outline, 3))
		if count <= 2:
			count = 0
		else:
			var saved: int = memory.read_u16(0x2701a)
			memory.write_u16(0x2701a, neg_limit)
			count = C68kScript.s16(clip_polygon_x_min(outline, count, clip_buf))
			memory.write_u16(0x2701a, saved)
			if count != 0:
				saved = memory.read_u16(0x27018)
				memory.write_u16(0x27018, C68kScript.u16(limit))
				count = C68kScript.s16(clip_polygon_x_max(clip_buf, count, outline))
				memory.write_u16(0x27018, saved)
			if count != 0:
				saved = memory.read_u16(0x27016)
				memory.write_u16(0x27016, neg_limit)
				count = C68kScript.s16(clip_polygon_y_min(outline, count, clip_buf))
				memory.write_u16(0x27016, saved)
			if count != 0:
				saved = memory.read_u16(0x20b2e)
				memory.write_u16(0x20b2e, C68kScript.u16(limit))
				count = C68kScript.s16(clip_polygon_y_max(clip_buf, count, outline))
				memory.write_u16(0x20b2e, saved)
		var face: int = C68kScript.u32(memory.read_u32(obj + 0x4a) + face_i * 0x20)
		if count == 0:
			memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) | 2)
		else:
			memory.write_u16(face + 0x14, C68kScript.u16(count))
			memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) & 0xfd)
			memory.write_u32(face + 0x18, heap_realloc_grow_only(memory.read_u32(face + 0x18), count << 1))
			var k2: int = 0
			while k2 < count:
				var px: int = C68kScript.s32(C68kScript.lsl32(memory.read_u32(outline + k2 * 8), shift) + memory.read_u32(0x23c54))
				var pz: int = C68kScript.s32(C68kScript.lsl32(memory.read_u32(outline + k2 * 8 + 4), shift) + memory.read_u32(0x23c4c))
				var pt: int = unique_pts
				var left: int = unique
				var found: bool = false
				while true:
					left = C68kScript.s16(left - 1)
					if left == -1:
						break
					if memory.read_s32(pt) == px and memory.read_s32(pt + 8) == pz:
						memory.write_u16(memory.read_u32(face + 0x18) + k2 * 2, C68kScript.u16(unique - left - 1))
						found = true
						break
					pt += 0xc
				if not found:
					var u: int = unique_pts + unique * 0xc
					memory.write_u32(u, C68kScript.u32(px))
					memory.write_u32(u + 4, 0)
					memory.write_u32(u + 8, C68kScript.u32(pz))
					memory.write_u16(memory.read_u32(face + 0x18) + k2 * 2, unique)
					unique = C68kScript.s16(unique + 1)
				k2 = C68kScript.s16(k2 + 1)
		face_src = C68kScript.u32(idx + memory.read_u8(face_src + 8) * 2)
		face_i = C68kScript.s16(face_i + 1)
	if unique == 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x40)
	else:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbf)
		memory.write_u16(obj + 0x38, unique)
		var size: int = C68kScript.s16(unique + memory.read_u16(obj + 0x3c)) * 0xc
		memory.write_u32(obj + 0x4e, heap_realloc_grow_only(memory.read_u32(obj + 0x4e), size))
		memory.write_u32(obj + 0x46, heap_realloc_grow_only(memory.read_u32(obj + 0x46), size))
		i = 0
		while i < unique:
			for k in range(3):
				memory.write_u32(memory.read_u32(obj + 0x46) + i * 0xc + 4 * k, memory.read_u32(unique_pts + i * 0xc + 4 * k))
			i = C68kScript.s16(i + 1)
		i = 0
		while i < memory.read_s16(obj + 0x3c):
			var nrm: int = rel_pts + i * 0xc
			for k in range(3):
				memory.write_u32(nrm + 4 * k, C68kScript.u32(memory.read_u32(memory.read_u32(obj + 0x46) + 4 * k) + memory.read_u32(nrm + 4 * k)))
			var dst: int = memory.read_u32(obj + 0x46) + (memory.read_s16(obj + 0x38) + i) * 0xc
			for k in range(3):
				memory.write_u32(dst + 4 * k, memory.read_u32(nrm + 4 * k))
			i = C68kScript.s16(i + 1)
	memory.write_u32(obj + 4, memory.read_u32(0x23c54))
	memory.write_u32(obj + 0xc, memory.read_u32(0x23c4c))
	memory.write_u32(obj + 0x20, C68kScript.u32(range_value))
	update_object_camera_metric(obj)
	var d0: int = C68kScript.s32(C68kScript.asr32(memory.read_u32(obj + 0x1c), 2) + memory.read_s32(obj + 0x1c) + memory.read_s32(obj + 0x20))
	var d2: int = C68kScript.u32(C68kScript.lsl32(d0, memory.read_s16(obj + 0x36)))
	var steps: int = 0
	while (d2 & 0xffff8000) != 0:
		steps = C68kScript.s16(steps + 1)
		d2 = C68kScript.u32(C68kScript.asr32(d2, 1))
	memory.write_u16(obj + 0x34, C68kScript.u16(steps))

## 0x32a0 (1.0.3's transform_vecs12_to_int3_by_matrix3 0x2956; Leyte tests the 32-bit count before the
## loop, the same iterations): the rows of count 12-byte records (words +2 / +6 / +10) through the 3x3 word
## matrix, each component (z * m[2][j] + y * m[1][j] + x * m[0][j] + 0x800) asr 12 stored as a long, dst
## += 12 per record.
func transform_vecs12_to_int3_by_matrix3(count: int, src: int, dst: int, matrix: int) -> void:
	assert(memory != null, "transform_vecs12_to_int3_by_matrix3 requires original global memory")
	var left: int = C68kScript.u32(count)
	while left != 0:
		left -= 1
		var x: int = memory.read_s16(src + 2)
		var y: int = memory.read_s16(src + 6)
		var z: int = memory.read_s16(src + 10)
		memory.write_u32(dst + 0, C68kScript.u32(C68kScript.asr32(z * memory.read_s16(matrix + 12) + y * memory.read_s16(matrix + 6) + x * memory.read_s16(matrix + 0) + 0x800, 0xc)))
		memory.write_u32(dst + 4, C68kScript.u32(C68kScript.asr32(z * memory.read_s16(matrix + 14) + y * memory.read_s16(matrix + 8) + x * memory.read_s16(matrix + 2) + 0x800, 0xc)))
		memory.write_u32(dst + 8, C68kScript.u32(C68kScript.asr32(z * memory.read_s16(matrix + 16) + y * memory.read_s16(matrix + 10) + x * memory.read_s16(matrix + 4) + 0x800, 0xc)))
		src = C68kScript.u32(src + 0xc)
		dst = C68kScript.u32(dst + 0xc)

## 0x1746c (1.0.3's release_fractal_reference 0x14ad2 on Leyte's object layout): drops an object's fractal
## reference (+0x2e). A static scenery piece (byte +0x15 bit 3 without bit 4) first goes back to its
## template position (descriptor +2 / +6 / +0xa into +4 / +8 / +0xc; template list +0x2a, child index word
## +0x10) through apply_template_chain_xform(obj, 1, 0, 0) (0x34 below). A user (byte +0x16 bit 1 clear)
## counts the shared block's users down (word +0), setting deferred_free_flag (0x219a6) at 0. An owner walks
## its block list: a block still in use releases every object_pool (0x20804, word count 0x20b26) object
## pointing at it (recursively, 0x28 below), still-used blocks print a warning (overlay_printf 0x189c with
## the format at 0x17434), and each block and then the list are freed (0x1fe8). +0x2e = 0.
func release_fractal_reference(obj: int) -> void:
	assert(memory != null, "release_fractal_reference requires original global memory")
	var entry_a7: int = a7
	if (memory.read_u8(obj + 0x15) & 8) != 0 and (memory.read_u8(obj + 0x15) & 0x10) == 0:
		var desc: int = C68kScript.u32(memory.read_u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18) + memory.read_u32(memory.read_u32(0x207fc) + 0x8c))
		for k in range(3):
			memory.write_u32(obj + 4 + 4 * k, memory.read_u32(desc + 2 + 4 * k))
		a7 = entry_a7 - 0x34
		apply_template_chain_xform(obj, 1, 0, 0)
		a7 = entry_a7
	if (memory.read_u8(obj + 0x16) & 2) == 0:
		var block: int = memory.read_u32(obj + 0x2e)
		memory.write_u16(block, C68kScript.u16(memory.read_u16(block) - 1))
		if memory.read_u16(block) == 0:
			memory.write_u16(0x219a6, 1)
	else:
		var list: int = memory.read_u32(obj + 0x2e)
		if list != 0:
			var left: int = memory.read_u16(list)
			var cursor: int = list + 4
			while true:
				left = C68kScript.u16(left - 1)
				if left == 0xffff:
					break
				var block2: int = memory.read_u32(cursor)
				cursor += 4
				if memory.read_u16(block2) != 0:
					var n: int = memory.read_u16(0x20b26)
					var slot: int = 0x20804
					while true:
						n = C68kScript.u16(n - 1)
						if n == 0xffff:
							break
						var other: int = memory.read_u32(slot)
						slot += 4
						if memory.read_u32(other + 0x2e) == block2:
							a7 = entry_a7 - 0x28
							release_fractal_reference(other)
							a7 = entry_a7
				if memory.read_u16(block2) != 0:
					a7 = entry_a7 - 0x28
					_bsr_file(0x189c + 0x20, [0x17434])
					a7 = entry_a7
				heap_free_coalesce(block2)
			heap_free_coalesce(memory.read_u32(obj + 0x2e))
	memory.write_u32(obj + 0x2e, 0)

## 0x16116 (1.0.3's drop_contact_records_for_object 0x137ba on Leyte's layout; Leyte's sink body ends with
## it where 1.0.3's called shade_object_faces): drops every contact record (contact_table 0x215a4, 256
## slots) that names obj as object a or b; when b is a fractal owner (byte +0x16 bit 1) and a holds a
## fractal reference (byte +0x16 bit 5 and +0x2e), a releases it first (0x1746c, 0x18 below); the record is
## freed (0x1fe8) and its slot cleared.
func drop_contact_records_for_object(obj: int) -> void:
	assert(memory != null, "drop_contact_records_for_object requires original global memory")
	var entry_a7: int = a7
	for i in range(0x100):
		var slot: int = 0x215a4 + 4 * i
		var rec: int = memory.read_u32(slot)
		if rec == 0:
			continue
		if memory.read_u32(rec) != C68kScript.u32(obj) and memory.read_u32(rec + 4) != C68kScript.u32(obj):
			continue
		var obj_a: int = memory.read_u32(rec)
		if (memory.read_u8(memory.read_u32(rec + 4) + 0x16) & 2) != 0 and (memory.read_u8(obj_a + 0x16) & 0x20) != 0 and memory.read_u32(obj_a + 0x2e) != 0:
			a7 = entry_a7 - 0x18
			release_fractal_reference(obj_a)
			a7 = entry_a7
		heap_free_coalesce(rec)
		memory.write_u32(slot, 0)

## 0x1970a (Leyte's sink body; 1.0.3's sink_object_shape_at_waterline 0x15742 reworked around the composed
## chain): link.w a6,#-0x33dc and ten registers (body 0x3408 below the entry). The sink rotation
## (build_attitude_matrix_3ang(ang1..3) into a6 - 0x33aa, 0x341c below) times the composed chain matrix
## (0x2326 into a6 - 0x33bc, 0x3428 below) turns the template vectors (vertices + normal points, words +2 /
## +6 / +10) into a6 - 0x1f08 (0x32a0, 0x3438 below) - no separate chain transform. The normal points are
## saved relative to vertex 0 (a6 - 0x3398); each vertex word +6 gets + (object y +8 << shift) (shift word
## +0x36; x / z stay object-relative and are restored on the clipped points as + (+4 / +0xc << shift)).
## Each packed face is clipped to y >= 0 (0x195d6, 0x341c below; none left -> face byte +0x13 |= 2; else
## count +0x14, the hidden bit cleared unless packed byte +7 bit 1, the index list regrown 0x3414 below),
## the clipped points de-duplicated through 256 hash buckets (((y asr 1) + x + z) asr 2 & 0xff, linear
## probing; ids a6 - 0x2998, bucket of id a6 - 0x2d98, points a6 - 0x1800). No point -> flags +0x14 |= 0x40;
## else &= ~0x40, word +0x38 = points, +0x4e / +0x46 regrown to (points + faces) * 12 (face count +0x3c), the
## points copied and the normal points stored after them. Then shade_object_faces (0x4aec) when host word
## +0xb2 is set, drop_contact_records_for_object (0x16116) always; returns 1 when a face is left.
func leyte_sink_body(obj: int, ang1: int, ang2: int, ang3: int, chain: int) -> int:
	assert(memory != null, "leyte_sink_body requires original global memory")
	assert(a7 != 0, "leyte_sink_body needs a7 (its frame holds the work buffers)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var any_face: int = a6 - 0x33c0
	var clip_count: int = a6 - 0x33be
	var att_matrix: int = a6 - 0x33aa
	var product: int = a6 - 0x33bc
	var normals: int = a6 - 0x3398
	var id_bucket: int = a6 - 0x2d98
	var bucket_ids: int = a6 - 0x2998
	var clip_out: int = a6 - 0x2598
	var verts: int = a6 - 0x1f08
	var bucket_pts: int = a6 - 0x1800
	memory.write_u16(any_face, 0)
	var world: int = memory.read_u32(0x207fc)
	var tmpl: int = C68kScript.u32(memory.read_u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18) + memory.read_u32(world + 0x8c))
	var n: int = C68kScript.s16(memory.read_u16(tmpl + 0x1a) + memory.read_u16(tmpl + 0x1e))
	var packed: int = tmpl + 0x22 + n * 0xc
	a7 = entry_a7 - 0x341c
	build_attitude_matrix_3ang(C68kScript.s16(ang1), C68kScript.s16(ang2), C68kScript.s16(ang3), att_matrix)
	a7 = entry_a7 - 0x3428
	matmul3x3_s16(att_matrix, chain, product)
	a7 = entry_a7 - 0x3438
	transform_vecs12_to_int3_by_matrix3(n, tmpl + 0x22, verts, product)
	a7 = entry_a7
	var i: int = 0
	while i < memory.read_s16(tmpl + 0x1e):
		var src: int = verts + (memory.read_s16(tmpl + 0x1a) + i) * 0xc
		for k in range(3):
			memory.write_u32(normals + i * 0xc + 4 * k, C68kScript.u32(memory.read_u32(src + 4 * k) - memory.read_u32(verts + 4 * k)))
		i = C68kScript.s16(i + 1)
	var shift: int = memory.read_s16(obj + 0x36) & 0x3f
	var neg_off_x: int = C68kScript.s32(-C68kScript.lsl32(memory.read_u32(obj + 4), shift))
	var up_y: int = C68kScript.s32(C68kScript.lsl32(memory.read_u32(obj + 8), shift))
	var neg_off_z: int = C68kScript.s32(-C68kScript.lsl32(memory.read_u32(obj + 0xc), shift))
	memory.write_u32(a6 - 0x33cc, C68kScript.u32(neg_off_x))
	memory.write_u32(a6 - 0x33d0, C68kScript.u32(neg_off_z))
	var left: int = memory.read_s16(tmpl + 0x1a)
	var v: int = verts
	while true:
		left = C68kScript.s16(left - 1)
		if left == -1:
			break
		memory.write_u16(v + 6, C68kScript.u16(memory.read_u16(v + 6) + up_y))
		v += 0xc
	for k in range(0x100):
		memory.write_u32(bucket_ids + 4 * k, 0xffffffff)
	var uniq: int = 0
	i = 0
	while i < memory.read_s16(tmpl + 0x1e):
		memory.write_u16(clip_count, memory.read_u8(packed + 8))
		a7 = entry_a7 - 0x341c
		clip_polygon_y_nonneg(clip_count, verts, packed + 0xe, clip_out)
		a7 = entry_a7
		var face: int = memory.read_u32(obj + 0x4a) + i * 0x20
		var count: int = memory.read_s16(clip_count)
		if count == 0:
			memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) | 2)
		else:
			memory.write_u16(any_face, 1)
			memory.write_u16(face + 0x14, C68kScript.u16(count))
			if (memory.read_u8(packed + 7) & 2) == 0:
				memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) & 0xfd)
			a7 = entry_a7 - 0x3414
			memory.write_u32(face + 0x18, heap_realloc_grow_only(memory.read_u32(face + 0x18), count << 1))
			a7 = entry_a7
			var k2: int = 0
			while k2 < count:
				var e: int = clip_out + k2 * 0xc
				var px: int = C68kScript.s32(memory.read_s16(e + 2) - neg_off_x)
				var py: int = memory.read_s16(e + 6)
				var pz: int = C68kScript.s32(memory.read_s16(e + 10) - neg_off_z)
				var bucket: int = C68kScript.u16(C68kScript.asr32(C68kScript.s32((py >> 1) + px + pz), 2)) & 0xff
				var pt: int = bucket_pts + bucket * 0xc
				var reused: bool = false
				while memory.read_s16(bucket_ids + C68kScript.s16(bucket) * 2) >= 0:
					if memory.read_s32(pt) == px and memory.read_s32(pt + 4) == py and memory.read_s32(pt + 8) == pz:
						memory.write_u16(memory.read_u32(face + 0x18) + k2 * 2, memory.read_u16(bucket_ids + C68kScript.s16(bucket) * 2))
						reused = true
						break
					pt += 0xc
					bucket = C68kScript.u16(bucket + 1)
				if not reused:
					var slot: int = C68kScript.s16(bucket)
					memory.write_u32(bucket_pts + slot * 0xc, C68kScript.u32(px))
					memory.write_u32(bucket_pts + slot * 0xc + 4, C68kScript.u32(py))
					memory.write_u32(bucket_pts + slot * 0xc + 8, C68kScript.u32(pz))
					memory.write_u16(bucket_ids + slot * 2, uniq)
					memory.write_u16(id_bucket + uniq * 2, bucket)
					memory.write_u16(memory.read_u32(face + 0x18) + k2 * 2, uniq)
					uniq = C68kScript.s16(uniq + 1)
				k2 += 1
		packed = packed + 0xe + memory.read_u8(packed + 8) * 2
		i = C68kScript.s16(i + 1)
	if uniq == 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x40)
	else:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbf)
		memory.write_u16(obj + 0x38, uniq)
		var size: int = C68kScript.s16(uniq + memory.read_u16(obj + 0x3c)) * 0xc
		a7 = entry_a7 - 0x3414
		memory.write_u32(obj + 0x4e, heap_realloc_grow_only(memory.read_u32(obj + 0x4e), size))
		memory.write_u32(obj + 0x46, heap_realloc_grow_only(memory.read_u32(obj + 0x46), size))
		a7 = entry_a7
		i = 0
		while i < uniq:
			var b: int = memory.read_s16(id_bucket + i * 2)
			for k in range(3):
				memory.write_u32(memory.read_u32(obj + 0x46) + i * 0xc + 4 * k, memory.read_u32(bucket_pts + b * 0xc + 4 * k))
			i += 1
		i = 0
		while i < memory.read_s16(obj + 0x3c):
			var nrm: int = normals + i * 0xc
			for k in range(3):
				memory.write_u32(nrm + 4 * k, C68kScript.u32(memory.read_u32(memory.read_u32(obj + 0x46) + 4 * k) + memory.read_u32(nrm + 4 * k)))
			var dst: int = memory.read_u32(obj + 0x46) + (i + memory.read_s16(obj + 0x38)) * 0xc
			for k in range(3):
				memory.write_u32(dst + 4 * k, memory.read_u32(nrm + 4 * k))
			i += 1
	a7 = entry_a7 - 0x3410
	if memory.read_u16(memory.read_u32(0x207fc) + 0xb2) != 0:
		shade_object_faces(obj)
	drop_contact_records_for_object(obj)
	a7 = entry_a7
	return memory.read_s16(any_face)

## 0x19b32 (Leyte's sink_object_shape_at_waterline; link.w a6,#-0x20, two saved registers): the object's
## chain composed into the frame (a6 - 0x1e; 0x36fa, 0x38 below), then the body 0x1970a(obj, ang1, ang2,
## ang3, chain) (0x54 below: the composer's arguments stay pushed). Returns the body's D0. Leyte's wrapper
## reads three arguments after obj; the fifth parameter only keeps the generated port's signature (1.0.3
## passed a fourth word, the drop) and is not read.
func sink_object_shape_at_waterline(obj: int, ang1: int, ang2: int, ang3: int, _unused: int = 0) -> int:
	assert(memory != null, "sink_object_shape_at_waterline requires original global memory")
	assert(a7 != 0, "sink_object_shape_at_waterline needs a7 (its frame holds the composed chain)")
	var entry_a7: int = a7
	var chain: int = entry_a7 - 4 - 0x1e
	a7 = entry_a7 - 0x38
	compose_template_chain(obj, chain)
	a7 = entry_a7 - 0x54
	var d0: int = leyte_sink_body(obj, ang1, ang2, ang3, chain)
	a7 = entry_a7
	return d0

## 0x5c98 (1.0.3's activate_near_range_object 0x4e4e on Leyte's layout; seven saved registers, no frame).
## Kind 2 (type byte +0x28): published on entity_array_1 (0x20b44, count 0x20f2c; byte +0x17 bit 0) or the
## object pool (0x20804, count 0x20b24); then (Leyte, new) the steps word +0x34 = the number of asr #1
## steps that bring ((metric +0x1c asr 2) + metric + range +0x20) << word +0x36 below 0x8000; n = word +0x38
## + word +0x3c; +0x4e and +0x46 = heap_alloc(sext n * 12); the face records from the descriptor stream
## after its n template vectors (0x561c); a controller (+0) gets *ctl = obj and joins controller_list
## (0x21bd4, count 0x219b4). Then by flags: byte +0x16 bit 4 -> the land outline (0x577a; 0xb4e1, 0, 0x7fe4)
## and, at host word +0xe == 1, set_object_faces_bw_pattern(obj, 0x80), else shade; byte +0x17 bit 4 ->
## mission rates, band, the waterline sink (0x19b32) with the controller words +0x658 / +0x65a / +0x65c when
## its type word is 0x1b..0x22 (1.0.3: four words +0x34.. for types 0x15..0x19), else zeros; byte +0x15 bit
## 4 -> the template vectors translated into +0x46 unchanged, rates, band, shade; otherwise the chain
## transform (0x3524) of the template vectors into +0x46, rates, band, shade. Unless byte +0x16 bit 1, word
## +0x16 |= 0x800. Kind 3 (group): published on entity_array_1, its child table (count word +0x30, at
## +0x32) copied from the descriptor (+0x2a, +0x1a on; +0x14 relocated by host +0x8c) and every child
## instantiated (0x5450). Every path ends with update_object_camera_metric.
func activate_near_range_object(obj: int) -> void:
	assert(memory != null, "activate_near_range_object requires original global memory")
	var entry_a7: int = a7
	var kind: int = memory.read_s8(obj + 0x28)
	var world: int = memory.read_u32(0x207fc)
	if kind == 2:
		if (memory.read_u8(obj + 0x17) & 1) != 0:
			var count1: int = memory.read_u32(0x20f2c)
			memory.write_u32(0x20b44 + count1 * 4, obj)
			memory.write_u32(0x20f2c, C68kScript.u32(count1 + 1))
		else:
			var count2: int = memory.read_u32(0x20b24)
			memory.write_u32(0x20804 + count2 * 4, obj)
			memory.write_u32(0x20b24, C68kScript.u32(count2 + 1))
		var d1: int = C68kScript.s32(C68kScript.asr32(memory.read_s32(obj + 0x1c), 2) + memory.read_s32(obj + 0x1c) + memory.read_s32(obj + 0x20))
		d1 = C68kScript.u32(C68kScript.lsl32(d1, memory.read_s16(obj + 0x36) & 0x3f))
		var steps: int = 0
		while (d1 & 0xffff8000) != 0:
			steps += 1
			d1 = C68kScript.u32(C68kScript.asr32(d1, 1))
		memory.write_u16(obj + 0x34, C68kScript.u16(steps))
		var n: int = C68kScript.s16(memory.read_u16(obj + 0x38) + memory.read_u16(obj + 0x3c))
		var size: int = C68kScript.s32(n * 0xc)
		a7 = entry_a7 - 0x24
		memory.write_u32(obj + 0x4e, heap_alloc(size))
		a7 = entry_a7 - 0x28
		memory.write_u32(obj + 0x46, heap_alloc(size))
		a7 = entry_a7
		var work: int = C68kScript.u32(memory.read_u32(world + 0x8c) + memory.read_u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18) + 0x22)
		a7 = entry_a7 - 0x2c
		build_object_poly_buffers(obj, C68kScript.u32(work + size))
		a7 = entry_a7
		if memory.read_u32(obj) != 0:
			memory.write_u32(memory.read_u32(obj), obj)
			var count3: int = memory.read_u32(0x219b4)
			memory.write_u32(0x21bd4 + count3 * 4, memory.read_u32(obj))
			memory.write_u32(0x219b4, C68kScript.u32(count3 + 1))
		if (memory.read_u8(obj + 0x16) & 0x10) != 0:
			a7 = entry_a7 - 0x30
			build_land_outline_at_detail(obj, 0xb4e1, 0, 0x7fe4)
			a7 = entry_a7
			if memory.read_s16(memory.read_u32(0x207fc) + 0xe) == 1:
				a7 = entry_a7 - 0x28
				set_object_faces_bw_pattern(obj, 0x80)
				a7 = entry_a7
			else:
				a7 = entry_a7 - 0x24
				shade_object_faces(obj)
				a7 = entry_a7
		elif (memory.read_u8(obj + 0x17) & 0x10) != 0:
			a7 = entry_a7 - 0x28
			apply_entity_mission_rates(obj, 0)
			a7 = entry_a7 - 0x30
			update_object_distance_band(obj, memory.read_s32(obj + 0x1c))
			a7 = entry_a7
			var ctl: int = memory.read_u32(obj)
			var sink: Array = [0, 0, 0]
			if ctl != 0:
				var type_word: int = memory.read_s16(memory.read_u32(ctl + 4))
				if type_word > 0x1a and type_word <= 0x22:
					sink = [memory.read_s16(ctl + 0x658), memory.read_s16(ctl + 0x65a), memory.read_s16(ctl + 0x65c)]
			a7 = entry_a7 - 0x30
			sink_object_shape_at_waterline(obj, sink[0], sink[1], sink[2])
			a7 = entry_a7
		elif (memory.read_u8(obj + 0x15) & 0x10) != 0:
			a7 = entry_a7 - 0x38
			translate_int3_vecs(n, work, memory.read_u32(obj + 0x46), 0, 0, 0)
			a7 = entry_a7 - 0x40
			apply_entity_mission_rates(obj, 0)
			a7 = entry_a7 - 0x28
			update_object_distance_band(obj, memory.read_s32(obj + 0x1c))
			a7 = entry_a7 - 0x2c
			shade_object_faces(obj)
			a7 = entry_a7
		else:
			a7 = entry_a7 - 0x30
			apply_template_chain_xform(obj, n, work, memory.read_u32(obj + 0x46))
			a7 = entry_a7 - 0x38
			apply_entity_mission_rates(obj, 0)
			a7 = entry_a7 - 0x40
			update_object_distance_band(obj, memory.read_s32(obj + 0x1c))
			a7 = entry_a7 - 0x24
			shade_object_faces(obj)
			a7 = entry_a7
		if (memory.read_u8(obj + 0x16) & 2) == 0:
			memory.write_u16(obj + 0x16, memory.read_u16(obj + 0x16) | 0x800)
	elif kind == 3:
		var count4: int = memory.read_u32(0x20f2c)
		memory.write_u32(0x20b44 + count4 * 4, obj)
		memory.write_u32(0x20f2c, C68kScript.u32(count4 + 1))
		var src: int = memory.read_u32(obj + 0x2a) + 0x1a
		a7 = entry_a7 - 0x24
		memory.write_u32(obj + 0x32, heap_alloc(C68kScript.s32(memory.read_s16(obj + 0x30) * 0x18)))
		a7 = entry_a7
		var off: int = 0
		var i: int = 0
		while i < memory.read_s16(obj + 0x30):
			var dst: int = memory.read_u32(obj + 0x32) + off
			for k in range(6):
				memory.write_u32(dst + 4 * k, memory.read_u32(src + 4 * k))
			src = src + 0x18
			var name_field: int = memory.read_u32(obj + 0x32) + 0x14 + off
			memory.write_u32(name_field, C68kScript.u32(memory.read_u32(name_field) + memory.read_u32(world + 0x8c)))
			off = off + 0x18
			i += 1
		i = 0
		while i < memory.read_s16(obj + 0x30):
			a7 = entry_a7 - 0x2c
			instantiate_mission_pool_slot(obj, i, i)
			a7 = entry_a7
			i += 1
	a7 = entry_a7 - 0x24
	update_object_camera_metric(obj)
	a7 = entry_a7

## 0x5450 (1.0.3's instantiate_mission_pool_slot 0x46ba on Leyte's layout, with the controller set-up
## rewritten; link.w a6,#-0x14 and seven registers, body 0x34 below the entry): instantiates pool slot
## `slot` of root from template record `type`: descriptor = host +0x8c + long (root +0x2a list + 0x1a + 0x14 +
## 0x18 * type); object (0x532e, 0x3c below) into the slot (root +0x32, +0x14 + 0x18 * slot), +0x24 = root, word
## +0x10 = type, word +0x12 = slot, byte +0x29 += 1 when slot == type, flags +0x14 |= 1 when type < sext word
## root +0x2e; the chain transform (0x3524, point path, 0x48 below). With a class key (sext byte desc +1):
## the definition record (factory table 0x2074c [key]) create routine (+6) (obj, key, sext word slot record
## +0) (0x44 below) gives the controller (+0), cleared at its +0; then (Leyte) (0, 0, 0x1000 << word +0x36)
## goes through the chain (mesh path, 0x48 below), each component asr word +0x36 minus the object position,
## heading / pitch from that (0x2554, 0x54 below; speed 0x1000, squared 0x1000000), and the controller gets
## the object position (+0x14..+0x1c) and the pitch / heading words (+0x20 / +0x22). Then mission rates (obj,
## 1) (0x40 below); camera metric (0x44 below) - rate +0x18 <= 0x19c7f -> activate_near_range_object (0x3c
## below), else appended to entity_array_3 (0x233bc, count 0x219c4).
func instantiate_mission_pool_slot(root: int, type: int, slot: int) -> void:
	assert(memory != null, "instantiate_mission_pool_slot requires original global memory")
	assert(a7 != 0, "instantiate_mission_pool_slot needs a7 (its frame holds the vector and angles)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var world: int = memory.read_u32(0x207fc)
	var desc: int = C68kScript.u32(memory.read_u32(memory.read_u32(root + 0x2a) + 0x1a + 0x14 + type * 0x18) + memory.read_u32(world + 0x8c))
	a7 = entry_a7 - 0x3c
	var obj: int = alloc_object_from_desc(desc)
	a7 = entry_a7
	memory.write_u32(memory.read_u32(root + 0x32) + 0x14 + slot * 0x18, obj)
	memory.write_u32(obj + 0x24, root)
	memory.write_u16(obj + 0x10, C68kScript.u16(type))
	memory.write_u16(obj + 0x12, C68kScript.u16(slot))
	if C68kScript.u32(slot) == C68kScript.u32(type):
		memory.write_u8(obj + 0x29, (memory.read_u8(obj + 0x29) + 1) & 0xff)
	if memory.read_s16(root + 0x2e) > C68kScript.s32(type):
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 1)
	a7 = entry_a7 - 0x48
	apply_template_chain_xform(obj, 1, 0, 0)
	a7 = entry_a7
	if memory.read_u8(desc + 1) != 0:
		var record: int = memory.read_u32(root + 0x32) + slot * 0x18
		var key: int = memory.read_s8(desc + 1)
		var definition: int = memory.read_u32(0x2074c + key * 4)
		a7 = entry_a7 - 0x44
		var ctl: int = C68kScript.u32(int(_jsr(memory.read_u32(definition + 6), [obj, key, memory.read_s16(record)])))
		a7 = entry_a7
		memory.write_u32(obj, ctl)
		if ctl != 0:
			memory.write_u32(ctl, 0)
			var vec: int = a6 - 0xc
			memory.write_u32(vec, 0)
			memory.write_u32(vec + 4, 0)
			var shift: int = memory.read_s16(obj + 0x36) & 0x3f
			memory.write_u32(vec + 8, C68kScript.u32(C68kScript.lsl32(0x1000, shift)))
			a7 = entry_a7 - 0x48
			apply_template_chain_xform(obj, 1, vec, vec)
			a7 = entry_a7
			shift = memory.read_s16(obj + 0x36) & 0x3f
			var vx: int = C68kScript.s32(C68kScript.asr32(memory.read_s32(vec), shift) - memory.read_s32(obj + 4))
			var vy: int = C68kScript.s32(C68kScript.asr32(memory.read_s32(vec + 4), shift) - memory.read_s32(obj + 8))
			var vz: int = C68kScript.s32(C68kScript.asr32(memory.read_s32(vec + 8), shift) - memory.read_s32(obj + 0xc))
			memory.write_u32(vec, C68kScript.u32(vx))
			memory.write_u32(vec + 4, C68kScript.u32(vy))
			memory.write_u32(vec + 8, C68kScript.u32(vz))
			memory.write_u16(a6 - 0x10, 0)
			a7 = entry_a7 - 0x54
			attitude_from_velocity(vx, vy, vz, 0x1000, 0x1000000, a6 - 0xe, a6 - 0x10)
			a7 = entry_a7
			ctl = memory.read_u32(obj)
			memory.write_u32(ctl + 0x14, memory.read_u32(obj + 4))
			memory.write_u32(ctl + 0x18, memory.read_u32(obj + 8))
			memory.write_u32(ctl + 0x1c, memory.read_u32(obj + 0xc))
			memory.write_u16(ctl + 0x20, memory.read_u16(a6 - 0xe))
			memory.write_u16(ctl + 0x22, memory.read_u16(a6 - 0x10))
	a7 = entry_a7 - 0x40
	apply_entity_mission_rates(obj, 1)
	a7 = entry_a7 - 0x44
	var metric: int = update_object_camera_metric(obj)
	a7 = entry_a7
	if C68kScript.s32(metric - memory.read_s32(obj + 0x18)) <= 0x19c7f:
		a7 = entry_a7 - 0x3c
		activate_near_range_object(obj)
		a7 = entry_a7
	else:
		var count: int = memory.read_u32(0x219c4)
		memory.write_u32(0x233bc + count * 4, obj)
		memory.write_u32(0x219c4, C68kScript.u32(count + 1))

## 0x5f64 (1.0.3's spawn_mission_pool_object 0x50f6 on Leyte's layout): the first free slot (+0x14 == 0) of the
## scene root's (0x23c2c) table (+0x32, sext word count +0x30, 0x18-byte records), the table grown by 10 when
## none is free (heap_realloc_grow_only 0x21c0); the slot gets word +0 = low word of factory_arg, offsets +8 /
## +0xc / +0x10 and the low words of the angles at +2 / +4 / +6; then instantiate_mission_pool_slot(root,
## template word +0x10, slot) (0x5450, 0x28 below). The slot's object is returned, or retired (0x603a) and 0
## when its vertex buffer (+0x46) is null.
func spawn_mission_pool_object(tmpl_obj: int, off_x: int, off_y: int, off_z: int, angle1: int, angle2: int, angle3: int, factory_arg: int) -> int:
	assert(memory != null, "spawn_mission_pool_object requires original global memory")
	var entry_a7: int = a7
	var root: int = memory.read_u32(0x23c2c)
	var table: int = memory.read_u32(root + 0x32)
	var count: int = memory.read_s16(root + 0x30)
	var slot: int = 0
	var obj: int

	while slot < count:
		if memory.read_u32(table + slot * 0x18 + 0x14) == 0:
			break
		slot = slot + 1
	if count == slot:
		table = heap_realloc_grow_only(table, C68kScript.s32((count + 10) * 0x18))
		root = memory.read_u32(0x23c2c)
		memory.write_u32(root + 0x32, table)
		memory.write_u16(root + 0x30, C68kScript.u16(memory.read_u16(root + 0x30) + 10))
	var record: int = table + slot * 0x18
	memory.write_u16(record, C68kScript.u16(factory_arg))
	memory.write_u32(record + 8, C68kScript.u32(off_x))
	memory.write_u32(record + 0xc, C68kScript.u32(off_y))
	memory.write_u32(record + 0x10, C68kScript.u32(off_z))
	memory.write_u16(record + 2, C68kScript.u16(angle1))
	memory.write_u16(record + 4, C68kScript.u16(angle2))
	memory.write_u16(record + 6, C68kScript.u16(angle3))
	a7 = entry_a7 - 0x28
	instantiate_mission_pool_slot(memory.read_u32(0x23c2c), memory.read_u16(tmpl_obj + 0x10), slot)
	a7 = entry_a7
	obj = memory.read_u32(memory.read_u32(memory.read_u32(0x23c2c) + 0x32) + slot * 0x18 + 0x14)
	if memory.read_u32(obj + 0x46) == 0:
		retire_entity(obj)
		return 0
	return obj

## 0x1dc02 (1.0.3's spawn_camera_relative_object 0x19a82; the controller at the object's +0, 1.0.3 +8): spawn
## cloud_templates[cls] (0x2cd90) at the camera (0x23d68 / 0x23d64 / 0x23d60) + (dx, dy, dz) through
## spawn_mission_pool_object (0x5f64); its controller keeps the three angle words (+0x20 / +0x22 / +0x24), the
## offset (+0x32..+0x3a) and the class (+0x3e). Returns the controller, 0 when nothing was spawned.
func spawn_camera_relative_object(cls: int, dx: int, dy: int, dz: int, ang1: int, ang2: int, ang3: int) -> int:
	assert(memory != null, "spawn_camera_relative_object requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x14 - 0x24
	var obj: int = spawn_mission_pool_object(memory.read_u32(C68kScript.u32(0x2cd90 + C68kScript.s32(cls) * 4)), C68kScript.u32(dx + memory.read_u32(0x23d68)), C68kScript.u32(dy + memory.read_u32(0x23d64)), C68kScript.u32(dz + memory.read_u32(0x23d60)), 0, 0, 0, 0)
	a7 = entry_a7
	if obj == 0:
		return 0
	var ctl: int = memory.read_u32(obj)
	memory.write_u16(ctl + 0x20, C68kScript.u16(ang1))
	memory.write_u16(ctl + 0x22, C68kScript.u16(ang2))
	memory.write_u16(ctl + 0x24, C68kScript.u16(ang3))
	memory.write_u32(ctl + 0x32, C68kScript.u32(dx))
	memory.write_u32(ctl + 0x36, C68kScript.u32(dy))
	memory.write_u32(ctl + 0x3a, C68kScript.u32(dz))
	memory.write_u16(ctl + 0x3e, C68kScript.u16(cls))
	return ctl

## 0x728c (hook 21; 1.0.3's spawn_cloud_layer 0x63ec with Leyte's globals and cloud controller type 0x2a,
## 1.0.3 0x1d): with clouds (host word +0xae) 10 clouds are placed as in 1.0.3 (class rng % 5, bearings at
## least 0x280 apart, class 4 high, offsets from sin / cos, spawn_camera_relative_object 0x1dc02); without
## clouds every entity_array_1 (0x20b44, count 0x20f2c) object whose controller (+0) is of type 0x2a
## retires (0x603a). Leyte checks the retire loop's bound before entry, the same objects.
func spawn_cloud_layer() -> void:
	assert(memory != null, "spawn_cloud_layer requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x38
	var bearings: int = entry_a7 - 4 - 0x14
	if memory.read_u16(memory.read_u32(0x207fc) + 0xae) == 0:
		var n: int = 0
		while n < memory.read_s32(0x20f2c):
			var obj: int = memory.read_u32(0x20b44 + n * 4)
			var ctl: int = memory.read_u32(obj)
			if ctl != 0 and memory.read_s16(memory.read_u32(ctl + 4)) == 0x2a:
				a7 = body - 8
				retire_entity(obj)
				a7 = entry_a7
			n = C68kScript.s16(n + 1)
		return
	for n in 10:
		a7 = body - 4
		var cls: int = (overlay_rng_next() & 0xffff) % 5
		var bearing: int
		while true:
			bearing = C68kScript.s16(((overlay_rng_next() & 0xff) << 6))
			var j: int = 0
			while j < n:
				a7 = body - 8
				var turn: int = fold_angle_pm_0x2000(C68kScript.s32(bearing - memory.read_s16(bearings + 2 * j)))
				a7 = body - 0xc
				var far_apart: bool = abs_int(turn) > 0x27f
				a7 = body - 4
				if not far_apart:
					break
				j += 1
			if j == n:
				break
		memory.write_u16(bearings + 2 * n, C68kScript.u16(bearing))
		var factor: int
		var height: int
		if cls == 4:
			factor = (overlay_rng_next() & 0xffff) % 10 + 0xf
			height = (overlay_rng_next() & 0xfff) * 10 + 300000
		else:
			factor = (overlay_rng_next() & 0xffff) % 10 + 0x23
			height = (overlay_rng_next() & 0xffff) % 1000 + 0xcc4c
		a7 = body - 8
		var dx: int = C68kScript.asr32(C68kScript.s32(C68kScript.s16(sin_fixed14(bearing)) * 0x339 * factor), 6)
		a7 = body - 0xc
		var dz: int = C68kScript.asr32(C68kScript.s32(C68kScript.s16(cos_fixed14(bearing)) * 0x339 * factor), 6)
		if cls == 4:
			bearing = 0
		a7 = body - 0x20
		spawn_camera_relative_object(cls, dx, height, dz, 0, (0x4000 - bearing) & 0x3fff, 0)
		a7 = entry_a7

## 0x5c30 (1.0.3's reset_object_to_template_pose 0x4de6 on Leyte's layout): unless byte +0x17 bit 1, the
## position (+4 / +8 / +0xc) and the range (+0x20) back to the template descriptor's (template list +0x2a,
## child index word +0x10), then apply_template_chain_xform(obj, 1, 0, 0) (0x20 below).
func reset_object_to_template_pose(obj: int) -> void:
	assert(memory != null, "reset_object_to_template_pose requires original global memory")
	if (memory.read_u8(obj + 0x17) & 2) != 0:
		return
	var desc: int = C68kScript.u32(memory.read_u32(memory.read_u32(0x207fc) + 0x8c) + memory.read_u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18))
	memory.write_u32(obj + 4, memory.read_u32(desc + 2))
	memory.write_u32(obj + 8, memory.read_u32(desc + 6))
	memory.write_u32(obj + 0xc, memory.read_u32(desc + 0xa))
	memory.write_u32(obj + 0x20, memory.read_u32(desc + 0x12))
	var entry_a7: int = a7
	a7 = entry_a7 - 0x20
	apply_template_chain_xform(obj, 1, 0, 0)
	a7 = entry_a7

## 0x6130 (1.0.3's deactivate_object 0x52bc on Leyte's layout; Leyte checks the face loop's bound before
## entry): a shape (type byte +0x28 == 2): back to its template pose when byte +0x16 bit 4 (0x5c30); its
## vertex buffers (+0x4e then +0x46) freed and +0x46 cleared, then every face's index list (+0x4a + 0x18 + 32 i,
## word +0x3c faces) and the face table; its controller's (+0) object link cleared; an object whose child
## indices (words +0x12 / +0x10) differ goes through dispatch_entity_cleanup (0x60dc); a fractal reference
## (+0x2e) is released (0x1746c); flags +0x14 lose 0x80000. A group (kind 3): each child (+0x32 + 0x14 + 24 i,
## word +0x30) releases its fractal reference (shapes), gets +0x14 |= 2, loses a reference (byte +0x29) and
## its controller link; the child table is freed and cleared, and the group's controller link cleared.
func deactivate_object(obj: int) -> void:
	assert(memory != null, "deactivate_object requires original global memory")
	var entry_a7: int = a7
	var kind: int = memory.read_s8(obj + 0x28)
	if kind == 2:
		a7 = entry_a7 - 0x18
		if (memory.read_u8(obj + 0x16) & 0x10) != 0:
			reset_object_to_template_pose(obj)
		heap_free_coalesce(memory.read_u32(obj + 0x4e))
		a7 = entry_a7 - 0x1c
		heap_free_coalesce(memory.read_u32(obj + 0x46))
		memory.write_u32(obj + 0x46, 0)
		a7 = entry_a7 - 0x18
		var i: int = 0
		while memory.read_s16(obj + 0x3c) > i:
			heap_free_coalesce(memory.read_u32(memory.read_u32(obj + 0x4a) + 0x18 + i * 0x20))
			i += 1
		heap_free_coalesce(memory.read_u32(obj + 0x4a))
		if memory.read_u32(obj) != 0:
			memory.write_u32(memory.read_u32(obj), 0)
		if memory.read_u16(obj + 0x12) != memory.read_u16(obj + 0x10):
			dispatch_entity_cleanup(obj)
		if memory.read_u32(obj + 0x2e) != 0:
			release_fractal_reference(obj)
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xfff7ffff)
		a7 = entry_a7
	elif kind == 3:
		a7 = entry_a7 - 0x18
		var i: int = 0
		while memory.read_s16(obj + 0x30) > i:
			var child: int = memory.read_u32(memory.read_u32(obj + 0x32) + 0x14 + i * 0x18)
			if memory.read_u8(child + 0x28) == 2 and memory.read_u32(child + 0x2e) != 0:
				release_fractal_reference(child)
			memory.write_u32(child + 0x14, memory.read_u32(child + 0x14) | 2)
			memory.write_u8(child + 0x29, C68kScript.u8(memory.read_u8(child + 0x29) - 1))
			if memory.read_u32(child) != 0:
				memory.write_u32(memory.read_u32(child), 0)
			i += 1
		heap_free_coalesce(memory.read_u32(obj + 0x32))
		memory.write_u32(obj + 0x32, 0)
		if memory.read_u32(obj) != 0:
			memory.write_u32(memory.read_u32(obj), 0)
		a7 = entry_a7

## The fixed steps of Leyte's template-table initialiser after its first loop, read from the listing:
## (source, size, slot offset into the definition table 0x2074c), in groups whose arguments stay pushed (the
## k-th call of a group is 0xc * k deeper). Group A stores through table + 0x20, group B through table + 0x8c.
const LEYTE_TEMPLATE_STEPS_A := [[[0x1f9ca, 0x1a, 0x20], [0x1f9e4, 0x1a, 0x24], [0x1f9fe, 0x1a, 0x28]],
		[[0x1fa18, 0x1a, 0x34], [0x1fa32, 0x1a, 0x38], [0x1fa4c, 0x1a, 0x2c]],
		[[0x1fa66, 0x1a, 0x30], [0x1fa80, 0x1a, 0x5c], [0x1fd7a, 0x1a, 0x3c]],
		[[0x1fa9a, 0x20, 0x40], [0x1faba, 0x1a, 0x4c], [0x1fad4, 0x20, 0x48]],
		[[0x1faf4, 0x20, 0x44], [0x1fb14, 0x1a, 0x50], [0x1fb2e, 0x1a, 0x54]],
		[[0x1fb48, 0x1a, 0x58], [0x1fb62, 0x1a, 0x60], [0x1fb7c, 0x1a, 0x64]],
		[[0x1fb96, 0x1a, 0x68]]]
const LEYTE_TEMPLATE_STEPS_B := [[[0x1fc90, 0x1a, 0x8c], [0x1fcaa, 0x1a, 0x90], [0x1fcde, 0x1a, 0x98]],
		[[0x1fcf8, 0x1a, 0x9c], [0x1fd12, 0x1a, 0xa0], [0x1fd2c, 0x1a, 0xa4]],
		[[0x1fcc4, 0x1a, 0x94], [0x1fd46, 0x1a, 0xa8], [0x1fd60, 0x1a, 0xac]]]

## One template copy: heap_alloc(size) (0x2080) then copy_block_return_dst(block, source, size) (0x16), both
## entered depth below (five saved registers, three pushed arguments: 0x24, plus the group's earlier calls).
func _leyte_copy_template(entry_a7: int, depth: int, source: int, size: int) -> int:
	a7 = entry_a7 - depth
	var block: int = C68kScript.u32(heap_alloc(size))
	var copied: int = C68kScript.u32(copy_block_return_dst(block, source, size))
	a7 = entry_a7
	return copied

## 0x38 (1.0.3's overlay_init_template_pointer_tables at 0x34, the same scheme with Leyte's records): heap
## copies of the built-in definition templates into the definition table 0x2074c (in the bss, past the
## loaded image): seven 0x52-byte records from 0x1f78c (1.0.3: four of 0x4a) and eight 0x1c-byte records from
## 0x1fbb0 (1.0.3: five) at the slot their first word names, and the fixed 0x1a / 0x20-byte records of
## LEYTE_TEMPLATE_STEPS_A / _B. The class factories (definition +6) and controller ticks read these copies.
func overlay_init_template_pointer_tables() -> void:
	assert(memory != null, "overlay_init_template_pointer_tables requires original global memory")
	var entry_a7: int = a7
	for k in 7:
		var src: int = 0x1f78c + 0x52 * k
		memory.write_u32(C68kScript.u32(0x2074c + memory.read_s16(src) * 4), _leyte_copy_template(entry_a7, 0x24, src, 0x52))
	for group in LEYTE_TEMPLATE_STEPS_A:
		for k in group.size():
			memory.write_u32(0x2074c + group[k][2], _leyte_copy_template(entry_a7, 0x24 + 0xc * k, group[k][0], group[k][1]))
	for k in 8:
		var src2: int = 0x1fbb0 + 0x1c * k
		memory.write_u32(C68kScript.u32(0x2074c + memory.read_s16(src2) * 4), _leyte_copy_template(entry_a7, 0x24, src2, 0x1c))
	for group in LEYTE_TEMPLATE_STEPS_B:
		for k in group.size():
			memory.write_u32(0x2074c + group[k][2], _leyte_copy_template(entry_a7, 0x24 + 0xc * k, group[k][0], group[k][1]))

## 0x221c (1.0.3's copy_object_vertex_buffer 0x1be4 on Leyte's layout): copy the object's live vertices
## (+0x46) to its second buffer (+0x4e), 12 bytes each, for word +0x38 + word +0x3c vertices (none when the sum
## is not positive). Both pointers are read again for every long, as the bytes do.
func copy_object_vertex_buffer(obj: int) -> void:
	assert(memory != null, "copy_object_vertex_buffer requires original global memory")
	var count: int = memory.read_s16(obj + 0x38) + memory.read_s16(obj + 0x3c)
	if count <= 0:
		return
	var off: int = 0
	while true:
		for k in [0, 4, 8]:
			memory.write_u32(memory.read_u32(obj + 0x4e) + off + k, memory.read_u32(memory.read_u32(obj + 0x46) + off + k))
		off += 0xc
		if not (count * 12 > off):
			break

## The class factory of definition key k (sext byte desc +1, when non-zero) on Leyte's layout: code address =
## long at (long 0x2074c[k]) + 6, called with (obj, k, extra); its result goes to the object's +0 (1.0.3 +8)
## and, when non-null, its first long is cleared. Entered `depth` below the caller's a7.
func _leyte_call_class_factory(obj: int, desc: int, extra: int, depth: int) -> int:
	var key: int = memory.read_s8(desc + 1)
	var entry: int = memory.read_u32(0x2074c + key * 4)
	var entry_a7: int = a7
	a7 = entry_a7 - depth
	var ctl: int = C68kScript.u32(_jsr(memory.read_u32(entry + 6), [obj, key, extra]))
	a7 = entry_a7
	memory.write_u32(obj, ctl)
	if ctl != 0:
		memory.write_u32(ctl, 0)
	return ctl

## 0x1d2a (1.0.3's init_mission_object_tables 0x16c2 on Leyte's layout; six saved registers): the template
## tables first (0x38, 0x1c below); the scene root (0x23c2c) = instantiate_object_from_desc(host +0x8c) (0x20
## below); its 24-byte child records (sext word +0x30 of them, from descriptor +0x2a + 0x1a) copied into a heap
## table (+0x32, 0x24 below) with +0x14 relocated by host +0x8c, then each child instantiated (0x20 below) with
## +0x24 = root and words +0x10 / +0x12 = its index. Children 0 / 1 / 2 / 4 become 0x2159c / 0x21598 / 0x21594 /
## 0x2158c (child 2 always table +0x44: Leyte drops 1.0.3's host-word +0x90 choice of +0x5c) and are activated
## (0x5c98); children 0 and 4 get their vertex copies (0x221c; the second copy after child 1 again names
## child 0, as in 1.0.3), and 0x21590 = child 2's faces (+0x4a). The pool count (0x20b24) is cleared; the child
## window (word root +0x2e - 1 .. + 1) is published in 0x2107a / 0x21078 and its three children in 0x21410 /
## 0x2141c / 0x21420. The depths follow the pushed arguments (one adda.w #$1c at the end).
func init_mission_object_tables() -> void:
	assert(memory != null, "init_mission_object_tables requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x1c
	overlay_init_template_pointer_tables()
	a7 = entry_a7 - 0x20
	var root: int = C68kScript.u32(instantiate_object_from_desc(memory.read_u32(memory.read_u32(0x207fc) + 0x8c)))
	a7 = entry_a7
	memory.write_u32(0x23c2c, root)
	var src: int = C68kScript.u32(memory.read_u32(root + 0x2a) + 0x1a)
	var count: int = memory.read_s16(root + 0x30)
	a7 = entry_a7 - 0x24
	memory.write_u32(root + 0x32, C68kScript.u32(heap_alloc(C68kScript.s32(count * 24))))
	a7 = entry_a7
	for k in range(maxi(count, 0)):
		var rec: int = C68kScript.u32(memory.read_u32(root + 0x32) + 24 * k)
		for w in 6:
			memory.write_u32(rec + 4 * w, memory.read_u32(src + 4 * w))
		src = C68kScript.u32(src + 0x18)
		memory.write_u32(rec + 0x14, C68kScript.u32(memory.read_u32(rec + 0x14) + memory.read_u32(memory.read_u32(0x207fc) + 0x8c)))
	for k in range(maxi(count, 0)):
		var rec2: int = C68kScript.u32(memory.read_u32(root + 0x32) + 24 * k)
		a7 = entry_a7 - 0x20
		var child: int = C68kScript.u32(instantiate_object_from_desc(memory.read_u32(rec2 + 0x14)))
		a7 = entry_a7
		memory.write_u32(C68kScript.u32(memory.read_u32(root + 0x32) + 24 * k + 0x14), child)
		memory.write_u32(child + 0x24, root)
		memory.write_u16(child + 0x10, k & 0xffff)
		memory.write_u16(child + 0x12, k & 0xffff)
	var table: int = memory.read_u32(memory.read_u32(0x23c2c) + 0x32)
	memory.write_u32(0x2159c, memory.read_u32(table + 0x14))
	a7 = entry_a7 - 0x20
	activate_near_range_object(memory.read_u32(0x2159c))
	a7 = entry_a7 - 0x24
	copy_object_vertex_buffer(memory.read_u32(0x2159c))
	table = memory.read_u32(memory.read_u32(0x23c2c) + 0x32)
	memory.write_u32(0x21598, memory.read_u32(table + 0x2c))
	a7 = entry_a7 - 0x28
	activate_near_range_object(memory.read_u32(0x21598))
	a7 = entry_a7 - 0x2c
	copy_object_vertex_buffer(memory.read_u32(0x2159c))
	table = memory.read_u32(memory.read_u32(0x23c2c) + 0x32)
	memory.write_u32(0x21594, memory.read_u32(table + 0x44))
	a7 = entry_a7 - 0x30
	activate_near_range_object(memory.read_u32(0x21594))
	a7 = entry_a7
	memory.write_u32(0x21590, memory.read_u32(memory.read_u32(0x21594) + 0x4a))
	table = memory.read_u32(memory.read_u32(0x23c2c) + 0x32)
	memory.write_u32(0x2158c, memory.read_u32(table + 0x74))
	a7 = entry_a7 - 0x34
	activate_near_range_object(memory.read_u32(0x2158c))
	a7 = entry_a7 - 0x38
	copy_object_vertex_buffer(memory.read_u32(0x2158c))
	a7 = entry_a7
	memory.write_u32(0x20b24, 0)
	var root2: int = memory.read_u32(0x23c2c)
	var lo: int = C68kScript.s16(memory.read_s16(root2 + 0x2e) - 1)
	memory.write_u16(0x2107a, C68kScript.u16(lo))
	var children: int = memory.read_u32(root2 + 0x32)
	memory.write_u32(0x21410, memory.read_u32(C68kScript.u32(children + 24 * lo + 0x14)))
	memory.write_u32(0x2141c, memory.read_u32(C68kScript.u32(children + 24 * (lo + 1) + 0x14)))
	memory.write_u32(0x21420, memory.read_u32(C68kScript.u32(children + 24 * (lo + 2) + 0x14)))
	memory.write_u16(0x21078, C68kScript.u16(lo + 2))

## 0x0 (1.0.3's overlay_patch_template_u16_pair at 0x0, extended): the word argument into three template
## record words of the 0x52-byte definition records (0x1f7d0 = record 0 +0x44, + 0xa4, + 0xf6: records 0, 2
## and 3; 1.0.3 patched two, 0x1a7d2 and + 0x4a).
func overlay_patch_template_u16_pair(value: int) -> void:
	assert(memory != null, "overlay_patch_template_u16_pair requires original global memory")
	memory.write_u16(0x1f7d0, C68kScript.u16(value))
	memory.write_u16(0x1f7d0 + 0xa4, C68kScript.u16(value))
	memory.write_u16(0x1f7d0 + 0xf6, C68kScript.u16(value))

## The renderer hooks hook 0 installs per depth (1.0.3's RENDERER_HOOKS_*, each slot and code address mapped
## through the instruction pairing): span fillers (0x26ff0 / 0x26fec / 0x26fe8), the depth routines (0x23fcc /
## 0x23fc8), the face / scatter outputs (0x23fb0 / 0x23fb4), the blip drawers (0x23fc4 / 0x23fc0) and the face
## output (0x26fe4). Each is reconstructed here (the depth-1 face output 0xb482 and dot plotter 0x1a20c are the
## generated port's) and registered in code_table with hook 0.
const LEYTE_RENDERER_HOOKS_8 := [[0x26ff0, 0x4294], [0x26fec, 0x4294], [0x26fe8, 0x4294], [0x23fcc, 0xe784], [0x23fc8, 0xece2],
		[0x23fb0, 0x19f12], [0x23fb4, 0x19e8c], [0x23fc4, 0x1b992], [0x23fc0, 0x1b902], [0x26fe4, 0xb3d0]]
const LEYTE_RENDERER_HOOKS_4 := [[0x26ff0, 0x3d9a], [0x26fec, 0x3f46], [0x26fe8, 0x40e8], [0x23fcc, 0xe978], [0x23fc8, 0xef28],
		[0x23fb0, 0x1a104], [0x23fb4, 0x19f8c], [0x23fc4, 0x1baca], [0x23fc0, 0x1b9ea], [0x26fe4, 0xb3d0]]
const LEYTE_RENDERER_HOOKS_1 := [[0x23fb0, 0x1a20c], [0x23fb4, 0x1a204], [0x23fc4, 0x1bbe2], [0x23fc0, 0x1bbb6]]

## The dot pixel: screen base host +6 + word(sy + view_half_rows) * row bytes (muls.w) + offset.
func _dot_row_base(sy: int) -> int:
	var host: int = memory.read_u32(0x207fc)
	return C68kScript.u32(memory.read_u32(host + 6) + C68kScript.s16(sy + memory.read_s16(0x27054)) * memory.read_s16(host + 0x10))

## Frame and saved-panel addresses of a radar blip row y - 1 at byte column col: frame = host +6
## + row bytes (+0x10) * (y - 1) + col, panel = host +0xe0 (1.0.3 +0xdc) - panel_back_offset (0x21564) + panel
## row bytes (+0xe4, 1.0.3 +0xe0) * (y - 1) + col (muls.l).
func _blip_addresses(col: int, y: int) -> Array:
	var host: int = memory.read_u32(0x207fc)
	var y1: int = C68kScript.s32(C68kScript.s16(y) - 1)
	var frame: int = C68kScript.u32(C68kScript.s32(memory.read_s16(host + 0x10) * y1) + memory.read_u32(host + 6) + col)
	var panel: int = C68kScript.u32(C68kScript.s32(memory.read_u32(host + 0xe0) - memory.read_u32(0x21564)) + C68kScript.s32(memory.read_s16(host + 0xe4) * y1) + col)
	return [frame, panel]

## 0x17810 (depth-8 *0x1da94; a second entry inside erase_radar_blip_depth8's extent): draw the
## blip's 12 bytes in the colour byte host +0x1eb.
func draw_radar_blip_depth8(x: int, y: int) -> void:
	assert(memory != null, "draw_radar_blip_depth8 requires original global memory")
	var host: int = memory.read_u32(0x207fc)
	var frame: int = _blip_addresses(C68kScript.s16(x), y)[0]
	var colour: int = memory.read_u8(host + 0x1eb)
	for cell in _blip_cross8(x):
		memory.write_u8(frame + cell[0] * memory.read_s16(host + 0x10) + cell[1], colour)

## 0x19e8c (1.0.3's erase_dots_depth8 0x15e9e; Leyte checks the record loop's bound before entry, the same
## records): erase last frame's dots on an 8-bit screen. For each dot record (table 0x2c83c, 0xe bytes each)
## from the last (count word 0x23fbc) the pixels still showing its colour (+8) get their saved bytes back (+9
## top-left, +0xa top-right when size +6 > 2, +0xb bottom-left when > 1, +0xc bottom-right when > 3; rows host
## +0x10 apart). The count is cleared.
func erase_dots_depth8() -> void:
	assert(memory != null, "erase_dots_depth8 requires original global memory")
	var row: int = memory.read_s16(memory.read_u32(0x207fc) + 0x10)
	var i: int = memory.read_u16(0x23fbc)
	while i != 0:
		i -= 1
		var rec: int = 0x2c83c + C68kScript.s16(i) * 0xe
		var pix: int = memory.read_u32(rec)
		var colour: int = memory.read_u8(rec + 8)
		var size: int = memory.read_s16(rec + 6)
		if memory.read_u8(pix) == colour:
			memory.write_u8(pix, memory.read_u8(rec + 9))
		if size > 2 and memory.read_u8(pix + 1) == colour:
			memory.write_u8(pix + 1, memory.read_u8(rec + 0xa))
		pix = C68kScript.u32(pix + row)
		if size > 1 and memory.read_u8(pix) == colour:
			memory.write_u8(pix, memory.read_u8(rec + 0xb))
		if size > 3 and memory.read_u8(pix + 1) == colour:
			memory.write_u8(pix + 1, memory.read_u8(rec + 0xc))
	memory.write_u16(0x23fbc, 0)

## 0xaddc (1.0.3's add_face_edges_to_rows 0x9b6c; Leyte tests the edge loop's bound before entry, the same
## edges): rasterise the clipped polygon's edges for face into the per-row edge buckets. Instruction pairing
## found only moved globals, all carried by the generated port's DAT_ constants (session_entry_ptr 0x20f54,
## slot_usage_cur 0x23d94, palette_owner_current 0x23fac, row_has_edges 0x23fd4, row_edge_buckets 0x24fdc,
## edge_pool_count 0x26fe0, view_centre_fixed 0x2701c, view_half_width 0x27052, view_half_rows 0x27054,
## screen_poly_x 0x2b338, screen_poly_count 0x2c0b0) and assign_face_colour at 0x1be6. Word +0 = 0; a face
## with a palette slot it still owns marks it used, else gets one (failure -> return); x becomes 16.16 + 0x8000;
## each non-horizontal edge takes the next 0x1c-byte pool record and is inserted sorted by x then slope in its
## top row's bucket (an equal edge of the same row with no second face takes this face in +4 instead).
func add_face_edges_to_rows(face: int) -> void:
	assert(memory != null, "add_face_edges_to_rows requires original global memory")
	var entry_a7: int = a7
	var n: int = memory.read_u16(DAT_00025b1a)
	memory.write_u16(face, 0)
	var slot: int = memory.read_u8(face + 0xf)
	if slot != 0xff:
		if memory.read_u32(C68kScript.u32(memory.read_u32(DAT_0001da7c) + slot * 4)) == C68kScript.u32(face):
			memory.write_u8(C68kScript.u32(memory.read_u32(DAT_0001d864) + slot), 1)
		else:
			a7 = entry_a7 - 0x20
			var failed: int = assign_face_colour(face)
			a7 = entry_a7
			if failed != 0:
				return
			memory.write_u8(C68kScript.u32(memory.read_u32(DAT_0001d864) + memory.read_u8(face + 0xf)), 1)
	var k: int = n
	var p: int = DAT_00024df2
	while k != 0:
		memory.write_u32(p, C68kScript.u32(C68kScript.lsl32(memory.read_u32(p), 16) + 0x8000))
		p += 8
		k = (k - 1) & 0xffff
	var half_rows: int = memory.read_s16(DAT_00020b2c)
	var a0: int = DAT_00024df2
	var d3: int = n
	while true:
		d3 = (d3 - 1) & 0xffff
		if d3 == 0xffff:
			break
		var a3: int = C68kScript.u32(DAT_00024df2 + 8 * C68kScript.s16(d3))
		if memory.read_s32(a0 + 4) != memory.read_s32(a3 + 4):
			var rec: int = C68kScript.u32(memory.read_u32(DAT_0001b76c) + C68kScript.s32(0x1c * memory.read_s32(DAT_00020ab0)))
			memory.write_u32(rec, C68kScript.u32(face))
			memory.write_u32(rec + 4, 0)
			var top_x: int
			var top_y: int
			var dxv: int
			var dyv: int
			if memory.read_s32(a0 + 4) > memory.read_s32(a3 + 4):
				memory.write_u16(rec + 0x16, C68kScript.u16(memory.read_u16(a0 + 6) + half_rows))
				top_x = memory.read_s32(a3)
				top_y = memory.read_s32(a3 + 4)
				dxv = C68kScript.s32(memory.read_s32(a0) - top_x)
				dyv = memory.read_s32(a0 + 4)
			else:
				memory.write_u16(rec + 0x16, C68kScript.u16(memory.read_u16(a3 + 6) + half_rows))
				top_x = memory.read_s32(a0)
				top_y = memory.read_s32(a0 + 4)
				dxv = C68kScript.s32(memory.read_s32(a3) - top_x)
				dyv = memory.read_s32(a3 + 4)
			memory.write_u32(rec + 0x10, C68kScript.u32(C68kScript.divs_trunc(dxv, C68kScript.s32(dyv - top_y))))
			var row: int = C68kScript.s32(top_y + half_rows)
			memory.write_u32(rec + 0xc, C68kScript.u32(top_x + C68kScript.lsl32(memory.read_s16(DAT_00020b2a), 16)))
			memory.write_u32(DAT_00020ab0, C68kScript.u32(memory.read_u32(DAT_00020ab0) + 1))
			if not (memory.read_u32(rec + 0xc) == memory.read_u32(DAT_00020af0) and memory.read_u32(rec + 0x10) == 0):
				var link: int = C68kScript.u32(DAT_0001eaac + row * 4)
				var node: int = memory.read_u32(link)
				var merged: bool = false
				while node != 0:
					var rc: int = memory.read_s32(rec + 0xc)
					var nc: int = memory.read_s32(node + 0xc)
					if rc < nc:
						break
					if rc == nc:
						var rs: int = memory.read_s32(rec + 0x10)
						var ns: int = memory.read_s32(node + 0x10)
						if rs < ns:
							break
						if rs == ns and memory.read_u16(node + 0x16) == memory.read_u16(rec + 0x16) and memory.read_u32(node + 4) == 0:
							memory.write_u32(node + 4, C68kScript.u32(face))
							merged = true
							break
					link = C68kScript.u32(node + 0x18)
					node = memory.read_u32(link)
				if not merged:
					memory.write_u32(rec + 0x18, memory.read_u32(link))
					memory.write_u32(link, rec)
					memory.write_u16(C68kScript.u32(DAT_0001daa4 + memory.read_s16(rec + 0x16) * 2), 1)
		a0 = a3

## Leyte's depth-4 renderer routines (hook 0's renderer hooks for a 4-bit screen) and its 1-bit ones, each paired
## by instruction with 1.0.3's: no real difference but moved globals, host fields and code-relative tables.
## - span fillers A / B / C 0x3d9a / 0x3f46 / 0x40e8 (1.0.3 0x329a / 0x3446 / 0x35e8, all +0xb00 with their
##   pc-relative run-writer tables 0x3d22 / 0x3ece / 0x4070 and label bases 0x3e34 / 0x3fd6 / 0x4178; the table
##   fillers 0x3d46 / 0x3ef2 / 0x4094 pair identically: the generated port has the first, leyte_init_span_table_3ef2 / _4094 above the others);
## - draw / restore line 0xe978 / 0xef28 (1.0.3 0xd590 / 0xdb40, +0x13e8 with their labels, named in comments);
## - dot plotter / eraser 0x1a104 / 0x19f8c (globals 0x207fc, 0x23fb8, 0x27052, 0x23fbc, dot table 0x2c83c; the
##   eraser tests its loop bound before entry, the same records);
## - blip drawer / eraser 0x1baca / 0x1b9ea (colour host +0x1eb for 1.0.3's +0x1e7, the saved panel's row bytes
##   +0xe4 for +0xe0);
## - 1-bit: blip eraser / drawer 0x1bbb6 / 0x1bbe2 (host rectangle routine +0x2ae for 1.0.3's +0x276), dot eraser
##   0x1a204 (clr.w 0x23fbc). The 1-bit dot plotter 0x1a20c is the generated port's plot_dot_bw.

## 0x19f8c (1.0.3 0x15f98): erase last frame's dots on a 4-bit screen; the count is cleared.
func erase_dots_depth4() -> void:
	assert(memory != null, "erase_dots_depth4 requires original global memory")
	var row: int = memory.read_s16(memory.read_u32(0x207fc) + 0x10)
	var i: int = memory.read_u16(0x23fbc)
	while i != 0:
		i -= 1
		var rec: int = 0x2c83c + C68kScript.s16(i) * 0xe
		var pix: int = memory.read_u32(rec)
		var colour: int = memory.read_u8(rec + 8)
		var size: int = memory.read_s16(rec + 6)
		if memory.read_u16(rec + 4) == 0:
			_erase_nibble(pix, colour, memory.read_u8(rec + 9), 0xf0)
			if size > 2:
				_erase_nibble(pix, colour, memory.read_u8(rec + 9), 0x0f)
			pix = C68kScript.u32(pix + row)
			if size > 1:
				_erase_nibble(pix, colour, memory.read_u8(rec + 0xb), 0xf0)
			if size > 3:
				_erase_nibble(pix, colour, memory.read_u8(rec + 0xb), 0x0f)
		else:
			_erase_nibble(pix, colour, memory.read_u8(rec + 9), 0x0f)
			if size > 2:
				_erase_nibble(pix + 1, colour, memory.read_u8(rec + 0xa), 0xf0)
			pix = C68kScript.u32(pix + row)
			if size > 1:
				_erase_nibble(pix, colour, memory.read_u8(rec + 0xb), 0x0f)
			if size > 3:
				_erase_nibble(pix + 1, colour, memory.read_u8(rec + 0xc), 0xf0)
	memory.write_u16(0x23fbc, 0)

func _blip_rect_bw(x: int, y: int) -> void:
	var entry_a7: int = a7
	x = C68kScript.s16(x)
	y = C68kScript.s16(y)
	a7 = entry_a7 - 0x18
	_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2ae), [C68kScript.u32(x), C68kScript.u32(y), C68kScript.u32(x + 1), C68kScript.u32(y + 1)])
	a7 = entry_a7

## 0x1a204 (1.0.3 0x16208): the 1-bit dot eraser - the dots are host polygons, so only the count is cleared.
func clear_dots_bw() -> void:
	assert(memory != null, "clear_dots_bw requires original global memory")
	memory.write_u16(0x23fbc, 0)


## Leyte's template-registration class factories (definition +6 of the records the mission build instantiates;
## each returns 0 and leaves no controller). Helpers are the generated port's: _register_template stores the
## single template and redirects +6, _store_in_first_free_slot fills the first empty long (unsigned index).

## 0x1ebe0 (1.0.3's register_ordnance_template 0x127fe kept one template at 0x26280 and redirected +6 to its
## factory at once): Leyte keeps up to nine, the first empty long of 0x2cdd4[0..8] (signed bound, ble #8), and
## redirects +6 to the ordnance factory 0x1ebb6 only when the template landed in the last slot (index 8; a
## tenth finds them all full, index 9, and changes nothing).
func leyte_register_ordnance_templates(tmpl: int, def_key: int) -> int:
	assert(memory != null, "leyte_register_ordnance_templates requires original global memory")
	if _store_in_first_free_slot(0x2cdd4, 9, tmpl) == 8:
		memory.write_u32(_definition(def_key) + 6, 0x1ebb6)
	return 0

## 0x1ca20 (1.0.3 0x188d4, the same instructions with six slots for four): shadow_templates[1..6] from 0x2cd70
## (kind 0 stays empty); an index past 4 redirects +6 to the shadow factory 0x1c9da.
func register_shadow_templates(tmpl: int, def_key: int) -> int:
	assert(memory != null, "register_shadow_templates requires original global memory")
	if _store_in_first_free_slot(0x2cd70, 6, tmpl) > 4:
		memory.write_u32(_definition(def_key) + 6, 0x1c9da)
	return 0

## 0x1ed5c (1.0.3's register_cloud_templates 0x19a34: five slots from 0x26784, past 3 redirecting its own
## definition): two slots from 0x2ce04; any index but 0 (the second, or none free: 2) redirects +6 of BOTH
## cloud definitions (0x207e8 / 0x207ec, keys 0x27 / 0x28; the key argument is not read) to 0x1ec7e.
func leyte_register_route_ship_templates(tmpl: int, _def_key: int) -> int:
	assert(memory != null, "leyte_register_route_ship_templates requires original global memory")
	if _store_in_first_free_slot(0x2ce04, 2, tmpl) != 0:
		memory.write_u32(memory.read_u32(0x207e8) + 6, 0x1ec7e)
		memory.write_u32(memory.read_u32(0x207ec) + 6, 0x1ec7e)
	return 0

## 0x14952 / 0x14974 / 0x14996 (Leyte only; the shape of 1.0.3's single-template registrations): the template
## at 0x2c834 / 0x2c830 / 0x2c82c, +6 redirected to the shared factory 0x148fa.
func leyte_register_template_2c834(tmpl: int, def_key: int) -> int:
	assert(memory != null, "leyte_register_template_2c834 requires original global memory")
	return _register_template(0x2c834, tmpl, def_key, 0x148fa)

func leyte_register_template_2c830(tmpl: int, def_key: int) -> int:
	assert(memory != null, "leyte_register_template_2c830 requires original global memory")
	return _register_template(0x2c830, tmpl, def_key, 0x148fa)

func leyte_register_template_2c82c(tmpl: int, def_key: int) -> int:
	assert(memory != null, "leyte_register_template_2c82c requires original global memory")
	return _register_template(0x2c82c, tmpl, def_key, 0x148fa)

## 0x13bb6 (1.0.3's create_singleton_type10_controller 0x12f72): a cleared 0x32-byte controller (heap_alloc_cleared
## 0x2184) for the object, +4 its definition; Leyte sets only bit 4 of the object's flags byte +0x15 (1.0.3: long
## +4 |= 0x112100) and no longer publishes the controller (1.0.3's 0x20b40). Returns the controller.
func leyte_create_type10_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "leyte_create_type10_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x10
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x32))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x10)
	memory.write_u32(ctl + 4, _definition(def_key))
	return ctl


## 0xf3b8 (1.0.3's init_aircraft_controller 0xdfd0, the same 46 instructions on Leyte's layouts): a cleared
## 0x6c8-byte aircraft controller (1.0.3: 0x6b0; heap_alloc_cleared 0x2184, 0x14 below). Bombs byte +0x68d = 2,
## ammo word +0x696 = 0x2a3, word +0x614 = kind, +0 = object, +4 = its definition; word +0x642 = definition
## word +0x1a + (extb bombs asl 4) (word add); both tanks +0x691 / +0x692 = definition word +0x1c / 2 (toward
## zero); word +0x626 = definition word +0x44 (1.0.3 +0x42); word +0x63e = -0x5b; byte +0x68a = 0x2d (gear
## down); the pose ring at +0x36 filled with the object position +4 / +8 / +0xc (fill_pose_history 0xf326,
## identical to 1.0.3's; 0x24 below: the allocation's size is still pushed); object flags byte +0x15 bit 1.
## (1.0.3's +0x67c / +0x682 / +0x634 / +0x67d / +0x67e / +0x630 / +0x679, object +0x18.. and +5.)
func init_aircraft_controller(obj: int, def_key: int, kind: int) -> int:
	assert(memory != null, "init_aircraft_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x14
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x6c8))
	a7 = entry_a7
	memory.write_u8(ctl + 0x68d, 2)
	memory.write_u16(ctl + 0x696, 0x2a3)
	memory.write_u16(ctl + 0x614, C68kScript.u16(kind))
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(ctl + 4, _definition(def_key))
	var definition: int = memory.read_u32(ctl + 4)
	memory.write_u16(ctl + 0x642, C68kScript.u16((memory.read_s8(ctl + 0x68d) << 4) + memory.read_u16(definition + 0x1a)))
	for tank in [0x691, 0x692]:
		var fuel: int = memory.read_s16(memory.read_u32(ctl + 4) + 0x1c)
		if fuel < 0:
			fuel = C68kScript.s16(fuel + 1)
		memory.write_u8(ctl + tank, C68kScript.u8(fuel >> 1))
	memory.write_u16(ctl + 0x626, memory.read_u16(memory.read_u32(ctl + 4) + 0x44))
	memory.write_u16(ctl + 0x63e, 0xffa5)
	memory.write_u8(ctl + 0x68a, 0x2d)
	a7 = entry_a7 - 0x24
	fill_pose_history(ctl + 0x36, memory.read_u32(obj + 4), memory.read_u32(obj + 8), memory.read_u32(obj + 0xc))
	a7 = entry_a7
	memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 2)
	return ctl

## Shared opening of the aircraft creators (1.0.3's _aircraft_common on Leyte's fields): byte +0x64c = 1 (1.0.3
## +0x63e) and long +0x2e = object long +8 (+0x1c) - sext definition word +0x42 (+0x40; suba.w).
func _aircraft_common(obj: int, ctl: int) -> void:
	memory.write_u8(ctl + 0x64c, 1)
	memory.write_u32(ctl + 0x2e, C68kScript.u32(memory.read_u32(obj + 8) - memory.read_s16(memory.read_u32(ctl + 4) + 0x42)))

## 0xf616 (1.0.3's create_enemy_fighter 0xe1c6, the same 34 instructions): the first key-7 object (1.0.3: key 4)
## while enemy_fighter_template (0x2c5ae; 1.0.3 0x26000) is 0 becomes that template (no controller, returns 0).
## Otherwise init_aircraft_controller (0xf3b8, 0x1c below), byte +0x64c = 1, long +0x2e = object long +8 - sext
## definition word +0x42 (suba.w), +0x32 = 0x13038, object flags long +0x14 |= 0x102100 and spawn_object_shadow(x,
## 0, z, 2, obj) (0x1ca84; 0x30 below, the init arguments still pushed). Returns the controller.
func create_enemy_fighter(obj: int, def_key: int, kind: int) -> int:
	assert(memory != null, "create_enemy_fighter requires original global memory")
	if memory.read_u32(0x2c5ae) == 0 and C68kScript.s32(def_key) == 7:
		memory.write_u32(0x2c5ae, C68kScript.u32(obj))
		return 0
	var entry_a7: int = a7
	a7 = entry_a7 - 0x1c
	var ctl: int = C68kScript.u32(init_aircraft_controller(obj, def_key, kind))
	a7 = entry_a7
	_aircraft_common(obj, ctl)
	memory.write_u32(ctl + 0x32, 0x13038)
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x102100)
	a7 = entry_a7 - 0x30
	spawn_object_shadow(memory.read_u32(obj + 4), 0, memory.read_u32(obj + 0xc), 2, obj)
	a7 = entry_a7
	return ctl

## 0x1ca84 (1.0.3's spawn_object_shadow 0x18936): spawn_mission_pool_object(shadow_templates[kind] (0x2cd6c),
## x, y, z, 0, 0, 0, 0) (0x5f64, 0x2c below); null -> 0. Leyte tests the player-object pointer (0x20b28; 1.0.3
## 0x1b738) before comparing: the player's shadow gets +0x18 = 1000000000 (1.0.3 +0xc). With host word +0xb0 == 0
## the shadow is hidden (flags long +0x14 |= 0x40). Its controller (object +0; 1.0.3 +8) +0x32 = owner, owner
## byte +0x29 += 1 (1.0.3 +3); returns the controller.
func spawn_object_shadow(x: int, y: int, z: int, kind: int, owner: int) -> int:
	assert(memory != null, "spawn_object_shadow requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x2c
	var shadow: int = C68kScript.u32(spawn_mission_pool_object(memory.read_u32(C68kScript.u32(0x2cd6c + C68kScript.s32(kind) * 4)), x, y, z, 0, 0, 0, 0))
	a7 = entry_a7
	if shadow == 0:
		return 0
	if memory.read_u32(0x20b28) != 0 and C68kScript.u32(owner) == memory.read_u32(memory.read_u32(0x20b28)):
		memory.write_u32(shadow + 0x18, 1000000000)
	if memory.read_u16(memory.read_u32(0x207fc) + 0xb0) == 0:
		memory.write_u32(shadow + 0x14, memory.read_u32(shadow + 0x14) | 0x40)
	var ctl: int = memory.read_u32(shadow)
	memory.write_u32(ctl + 0x32, C68kScript.u32(owner))
	memory.write_u8(owner + 0x29, C68kScript.u8(memory.read_u8(owner + 0x29) + 1))
	return ctl

## 0x1c9da (1.0.3's create_shadow_controller 0x1889a): a cleared 0x36-byte controller (0x2184, 0x14 below), +0 the
## object, flags long +0x14 |= 0x112000 (1.0.3 +4), +4 the definition; Leyte then resets the distance references
## (reset_distance_base_globals 0x7ff4, 0x14 below: the size is still pushed). Returns the controller.
func create_shadow_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_shadow_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x14
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x36))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x112000)
	memory.write_u32(ctl + 4, _definition(def_key))
	a7 = entry_a7 - 0x14
	reset_distance_base_globals()
	a7 = entry_a7
	return ctl


## 0x82a (1.0.3's for_each_mission_scene_object 0x686, the same instructions with a 0x730-byte frame for 0x6b8):
## traverse_scene_tree(visitor, ctx, path stack, 0, root) (0x77e, verified in the generated port; 0x750 below),
## root = long (host_world +0x8c) + long ((host_world +0x8c) + 24 * sext word host_world +0x90 + 0x8e); the ten-slot
## path stack at a6 - 0x28 (a7 - 0x2c).
func for_each_mission_scene_object(visitor: int, ctx: int) -> void:
	assert(memory != null, "for_each_mission_scene_object requires original global memory")
	assert(a7 != 0, "for_each_mission_scene_object needs a7 (its frame holds the path stack)")
	var entry_a7: int = a7
	var path_stack: int = entry_a7 - 0x2c
	var world: int = memory.read_u32(0x207fc)
	var base: int = memory.read_u32(world + 0x8c)
	a7 = entry_a7 - 0x750
	traverse_scene_tree(visitor, ctx, path_stack, 0,
			C68kScript.u32(base + memory.read_u32(C68kScript.u32(base + memory.read_s16(world + 0x90) * 0x18 + 0x8e))))
	a7 = entry_a7

## 0x676 (1.0.3's transform_through_node_chain 0x4d8: the same frame, fields and calls, registers reallocated
## and the node loop entered at its test): see 1.0.3's port. Its callees are Leyte's build_attitude_matrix_3ang
## 0x2ce2 (0x60 below), transform_int3_vecs_by_matrix3 0x3418 and translate_int3_vecs 0x2274.
func transform_through_node_chain(path_top: int, node_count: int, object: int, out_x: int, out_y: int, out_z: int, vertex_out: int) -> void:
	assert(memory != null, "transform_through_node_chain requires original global memory")
	assert(a7 != 0, "transform_through_node_chain needs a7 (its frame holds the matrix and position)")
	var node: int
	var shift: int
	var src: int
	var path: int
	var matrix: int = a7 - 0x22
	var pos_x: int = a7 - 0x10
	var entry_a7: int = a7

	path = path_top
	memory.write_u32(pos_x, memory.read_u32(object + 2))
	memory.write_u32(pos_x + 4, memory.read_u32(object + 6))
	memory.write_u32(pos_x + 8, memory.read_u32(object + 10))
	src = object + 0x22
	node_count = C68kScript.s16(node_count)
	while true:
		node_count = C68kScript.s16(node_count - 1)
		if node_count == -1:
			break
		path = path - 4
		node = memory.read_u32(path)
		a7 = entry_a7 - 0x60
		build_attitude_matrix_3ang(memory.read_s16(node + 2), memory.read_s16(node + 4), memory.read_s16(node + 6), matrix)
		a7 = entry_a7
		transform_int3_vecs_by_matrix3(1, pos_x, pos_x, matrix)
		translate_int3_vecs(1, pos_x, pos_x, memory.read_s32(node + 8), memory.read_s32(node + 0xc), memory.read_s32(node + 0x10))
		if vertex_out != 0:
			transform_int3_vecs_by_matrix3(memory.read_s16(object + 0x1a), src, vertex_out, matrix)
			shift = memory.read_s16(object + 0x18)
			translate_int3_vecs(memory.read_s16(object + 0x1a), vertex_out, vertex_out,
					C68kScript.s32(C68kScript.lsl32(memory.read_u32(node + 8), shift & 0x3f)),
					C68kScript.s32(C68kScript.lsl32(memory.read_u32(node + 0xc), shift & 0x3f)),
					C68kScript.s32(C68kScript.lsl32(memory.read_u32(node + 0x10), shift & 0x3f)))
			src = vertex_out
	memory.write_u32(out_x, memory.read_u32(pos_x))
	memory.write_u32(out_y, memory.read_u32(pos_x + 4))
	memory.write_u32(out_z, memory.read_u32(pos_x + 8))
	return

## 0x108e (1.0.3's anchor_point_visitor 0xc2c, extended; link.w a6,#-0x720, five saved registers): the
## for_each_mission_scene_object visitor of find_anchor_point. ctx: +0 the anchor record, +4 / +8 / +0xc the
## reference point, word +0x1e the mode, +0x20 the best value; Leyte adds two candidate lists. Mode != 0: a face of
## type nibble 0x70 is transformed (transform_through_node_chain 0x676 into the frame vertex buffer a6 - 0x708,
## outputs a6 - 0x718 / - 0x71c / - 0x720; 0x758 below) and, unless the vertex of its word +0xe lies above (y > 0),
## appended to list A (count word ctx +0xb78, 12-byte entries from ctx +0x24) as (x, 0x43, z) - the nearest is no
## longer chosen here; any other face of a node of kind 0x19 or 0x18 (byte +1) appends the node to list B (count
## word ctx +0xb7a): its transformed position (no vertex buffer) at ctx +0x4d4, its range long +0xe at ctx +0x9e8
## and its kind byte at ctx +0x984 (by index). Neither sets the anchor's word +0x18. Mode 0 is 1.0.3's: the
## shallowest node of kind 1 becomes the anchor (position, and the parent record's attitude words +2 / +4 / +6
## sign-extended), word +0x18 = 1.
func anchor_point_visitor(ctx: int, node: int, face: int, path: int, depth: int) -> void:
	assert(memory != null, "anchor_point_visitor requires original global memory")
	assert(a7 != 0, "anchor_point_visitor needs a7 (its frame holds the vertex buffer)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var px: int = a6 - 0x718
	var py: int = a6 - 0x71c
	var pz: int = a6 - 0x720
	var verts: int = a6 - 0x708
	var level: int = C68kScript.s16(depth)
	if memory.read_u16(ctx + 0x1e) != 0:
		if (memory.read_u8(face + 9) & 0xf0) == 0x70:
			a7 = entry_a7 - 0x758
			transform_through_node_chain(path, level, node, px, py, pz, verts)
			a7 = entry_a7
			if memory.read_s32(C68kScript.u32(verts + 12 * memory.read_s16(face + 0xe) + 4)) > 0:
				return
			var n: int = memory.read_s16(ctx + 0xb78)
			var entry: int = C68kScript.u32(ctx + 0x24 + 12 * n)
			memory.write_u16(ctx + 0xb78, C68kScript.u16(n + 1))
			memory.write_u32(entry, memory.read_u32(px))
			memory.write_u32(entry + 4, 0x43)
			memory.write_u32(entry + 8, memory.read_u32(pz))
		elif memory.read_u8(node + 1) == 0x19 or memory.read_u8(node + 1) == 0x18:
			a7 = entry_a7 - 0x758
			transform_through_node_chain(path, level, node, px, py, pz, 0)
			a7 = entry_a7
			var k: int = memory.read_s16(ctx + 0xb7a)
			memory.write_u32(C68kScript.u32(ctx + 0x9e8 + 4 * k), memory.read_u32(node + 0xe))
			memory.write_u8(C68kScript.u32(ctx + 0x984 + k), memory.read_u8(node + 1))
			var point: int = C68kScript.u32(ctx + 0x4d4 + 12 * k)
			memory.write_u16(ctx + 0xb7a, C68kScript.u16(k + 1))
			memory.write_u32(point, memory.read_u32(px))
			memory.write_u32(point + 4, memory.read_u32(py))
			memory.write_u32(point + 8, memory.read_u32(pz))
		return
	if memory.read_u8(node + 1) != 1 or not (level < memory.read_s32(ctx + 0x20)):
		return
	memory.write_u32(ctx + 0x20, C68kScript.u32(level))
	a7 = entry_a7 - 0x758
	transform_through_node_chain(path, level, node, px, py, pz, 0)
	a7 = entry_a7
	var anchor: int = memory.read_u32(ctx)
	memory.write_u32(anchor, memory.read_u32(px))
	memory.write_u32(anchor + 4, memory.read_u32(py))
	memory.write_u32(anchor + 8, memory.read_u32(pz))
	var parent: int = memory.read_u32(C68kScript.u32(path - 4))
	memory.write_u32(anchor + 0xc, C68kScript.u32(memory.read_s16(parent + 2)))
	memory.write_u32(anchor + 0x10, C68kScript.u32(memory.read_s16(parent + 4)))
	memory.write_u32(anchor + 0x14, C68kScript.u32(memory.read_s16(parent + 6)))
	memory.write_u16(anchor + 0x18, 1)

## 0x11fa (1.0.3's find_anchor_point 0xd4a, rewritten; link.w a6,#-0xb7c, eight saved registers): the visitor
## context in the frame (ctx = a6 - 0xb7c: the anchor record, its first 26 bytes copied, the mode word, best
## 1000000000, both list counts 0), then for_each_mission_scene_object with anchor_point_visitor (0x108e; 0xbac
## below). Mode 0 ends there. Otherwise, for each list-A candidate: the nearest list-B node within its own range
## (approx_dist3_xyz(candidate, node) < node range and < the nearest so far, starting at 1000000000; 0xbbc below)
## gives D5 = its kind; then a candidate nearer the reference point (ctx +4 / +8 / +0xc) than the best becomes the
## anchor (x, y, z, word +0x18 = 1) unless D5 is 0x18. D5 is not reset per candidate: with no node in range it
## keeps the previous candidate's kind, and before any it is the caller's D5 - entry_d5 here, -1 when unknown, in
## which case reading it asserts rather than invent a value (hook 2 calls with mode 0 and never reads it).
func find_anchor_point(anchor: int, mode: int, entry_d5: int = -1) -> void:
	assert(memory != null, "find_anchor_point requires original global memory")
	assert(a7 != 0, "find_anchor_point needs a7 (its frame holds the visitor context)")
	var entry_a7: int = a7
	var ctx: int = entry_a7 - 4 - 0xb7c
	memory.write_u16(ctx + 0x1e, C68kScript.u16(mode))
	memory.write_u32(ctx + 0x20, 1000000000)
	memory.write_u32(ctx, C68kScript.u32(anchor))
	for k in 6:
		memory.write_u32(ctx + 4 + 4 * k, memory.read_u32(C68kScript.u32(anchor + 4 * k)))
	memory.write_u16(ctx + 0x1c, memory.read_u16(C68kScript.u32(anchor + 0x18)))
	memory.write_u16(ctx + 0xb78, 0)
	memory.write_u16(ctx + 0xb7a, 0)
	a7 = entry_a7 - 0xbac
	for_each_mission_scene_object(0x108e, ctx)
	a7 = entry_a7
	if C68kScript.u32(mode) == 0:
		return
	var d5: int = entry_d5
	var a_index: int = 0
	while a_index < memory.read_s16(ctx + 0xb78):
		var cand: int = C68kScript.u32(ctx + 0x24 + 12 * a_index)
		var nearest: int = 1000000000
		var b_index: int = 0
		while b_index < memory.read_s16(ctx + 0xb7a):
			var point: int = C68kScript.u32(ctx + 0x4d4 + 12 * b_index)
			a7 = entry_a7 - 0xbbc
			var d: int = approx_dist3_xyz(memory.read_s32(cand), memory.read_s32(cand + 4), memory.read_s32(cand + 8),
					memory.read_s32(point), memory.read_s32(point + 4), memory.read_s32(point + 8))
			a7 = entry_a7
			if d < memory.read_s32(C68kScript.u32(ctx + 0x9e8 + 4 * b_index)) and d < nearest:
				d5 = memory.read_s8(C68kScript.u32(ctx + 0x984 + b_index))
				nearest = d
			b_index += 1
		a7 = entry_a7 - 0xbbc
		var d0: int = approx_dist3_xyz(memory.read_s32(cand), memory.read_s32(cand + 4), memory.read_s32(cand + 8),
				memory.read_s32(ctx + 4), memory.read_s32(ctx + 8), memory.read_s32(ctx + 0xc))
		a7 = entry_a7
		if d0 < memory.read_s32(ctx + 0x20):
			assert(d5 != -1, "find_anchor_point: D5 is the caller's (no list-B node in range yet) and was not given")
			if C68kScript.s16(d5) != 0x18:
				memory.write_u32(ctx + 0x20, C68kScript.u32(d0))
				var a: int = memory.read_u32(ctx)
				memory.write_u32(a, memory.read_u32(cand))
				memory.write_u32(a + 4, memory.read_u32(cand + 4))
				memory.write_u32(a + 8, memory.read_u32(cand + 8))
				memory.write_u16(a + 0x18, 1)
		a_index += 1


## 0x46e2 (hook 2; 1.0.3's start_mission_scene 0x3aa4, the same 47 instructions on Leyte's globals): start mission
## scene n. The previous scene object (0x23c30) is deactivated (deactivate_object 0x6130, 0xc below); the new one is
## +0x74 of scene_root (0x23c2c) record n (+0x32 table, 0x18 bytes each; 1.0.3 +0x30). The anchor
## (find_anchor_point(0x23d6c, 0) 0x11fa, 0x10 below; mode 0 never reads D5) becomes the camera position (0x23d68 /
## 0x23d64 / 0x23d60); view mode 0x23d88, view flag 0x23d86 and 0x20b2c = 1, 0x23d54 = 1000000000;
## reset_distance_base_globals (0x7ff4, 0x10 below), activate_near_range_object (0x5c98, 0x14 below), the frame
## counter 0x21020 += 0x4b; then fill_pose_history_angles(player +0x36, its angle words +0x20 / +0x22 / +0x24)
## (0xf360), copy_player_pose_to_camera (0x83de), spawn_cloud_layer (0x728c) and stop_all_sound_channels (0xa6a4),
## all 0x24 below. The player is *0x20b28.
func start_mission_scene(scene: int) -> void:
	assert(memory != null, "start_mission_scene requires original global memory")
	var entry_a7: int = a7
	if memory.read_u32(0x23c30) != 0:
		a7 = entry_a7 - 0xc
		deactivate_object(memory.read_u32(0x23c30))
		a7 = entry_a7
	var record: int = C68kScript.u32(C68kScript.s32(C68kScript.s32(scene) * 24) + memory.read_u32(memory.read_u32(0x23c2c) + 0x32))
	memory.write_u32(0x23c30, memory.read_u32(record + 0x74))
	a7 = entry_a7 - 0x10
	find_anchor_point(0x23d6c, 0)
	a7 = entry_a7
	memory.write_u32(0x23d68, memory.read_u32(0x23d6c))
	memory.write_u32(0x23d64, memory.read_u32(0x23d70))
	memory.write_u32(0x23d60, memory.read_u32(0x23d74))
	memory.write_u16(0x23d88, 1)
	memory.write_u16(0x23d86, 1)
	memory.write_u16(0x20b2c, 1)
	memory.write_u32(0x23d54, 1000000000)
	a7 = entry_a7 - 0x10
	reset_distance_base_globals()
	a7 = entry_a7 - 0x14
	activate_near_range_object(memory.read_u32(0x23c30))
	a7 = entry_a7
	memory.write_u32(0x21020, C68kScript.u32(memory.read_u32(0x21020) + 0x4b))
	var player: int = memory.read_u32(0x20b28)
	a7 = entry_a7 - 0x24
	fill_pose_history_angles(C68kScript.u32(player + 0x36), memory.read_s16(player + 0x20), memory.read_s16(player + 0x22), memory.read_s16(player + 0x24))
	copy_player_pose_to_camera()
	spawn_cloud_layer()
	stop_all_sound_channels()
	a7 = entry_a7


## Class factories of the mission's objects (definition +6, reached when hook 2 activates the scene object).
## Each allocates a cleared controller (heap_alloc_cleared 0x2184, 0x14 below: link, one saved register, the size
## pushed, bsr), +0 the object, +4 its definition (0x2074c[key]); the object's flags long is +0x14 (1.0.3 +4).

## 0x1dce2 (1.0.3's create_view_attached_controller 0x19b62 on Leyte's object layout): 0x32 bytes; flags |= 0x132000. Returns
## the controller (still in D0 from the allocation: no move to D0).
func create_view_attached_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_view_attached_controller requires original global memory")
	return _leyte_flagged_controller(obj, def_key, 0x32, 0x132000)

## 0x1db7a (1.0.3's create_cloud_controller 0x199fa on Leyte's object layout; register_cloud_templates 0x1dbb4 redirects the cloud definitions here): 0x40 bytes; flags |= 0x132000. Returns the
## controller (still in D0).
func create_cloud_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_cloud_controller requires original global memory")
	return _leyte_flagged_controller(obj, def_key, 0x40, 0x132000)

func _leyte_flagged_controller(obj: int, def_key: int, size: int, flags: int) -> int:
	var entry_a7: int = a7
	a7 = entry_a7 - 0x10
	var ctl: int = C68kScript.u32(heap_alloc_cleared(size))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | flags)
	memory.write_u32(ctl + 4, _definition(def_key))
	return ctl

## 0x1d21a (1.0.3's create_turret_controller 0x190a0 reshaped): 0x54 bytes; flags byte +0x15 bit 1; then bit 4 when
## the object's long +8 (its y) is 0, else bit 0, and bit 4 again either way; long +0x42 = 0xce40. Returns the
## controller.
func create_turret_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_turret_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x10
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x54))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 2)
	if memory.read_u32(obj + 8) == 0:
		memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x10)
	else:
		memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 1)
	memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x10)
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u32(ctl + 0x42, 0xce40)
	return ctl

## 0x1cc72 (1.0.3's create_flak_controller 0x18b32 extended): 0xfa bytes; +4 the definition, word +0x46 = 0x3c, long
## +0x42 = 0x26ac0; flags byte +0x15 bit 1, and bits 0 and 4 when the object's word +0x3c is at most 4 (signed).
## Returns the controller.
func create_flak_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_flak_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x10
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0xfa))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u16(ctl + 0x46, 0x3c)
	memory.write_u32(ctl + 0x42, 0x26ac0)
	memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 2)
	if memory.read_s16(obj + 0x3c) <= 4:
		memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 1)
		memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x10)
	return ctl

## 0x1bea6 (1.0.3's create_aircraft_spawner_controller 0x17cf6, the same 51 instructions on Leyte's layout): a
## cleared 0x48-byte spawner (0x2184); +0 the object, flags long +0x14 |= 0x30040 (key 0x18, 1.0.3 0x12, also sets
## byte +0x15 bit 4), +4 the definition; aircraft to launch word +0x32 = rng & 0xf (overlay_rng_next 0x13652),
## reduced by the object's range long +0x18 (1.0.3 +0xc): 0x80e80 -> (rng & 3) + 1, 0xc15c0 -> (rng & 3) + 2,
## 0x40740 -> (rng & 0xf) % 3, any other keeps rng & 0xf. Returns the controller.
func create_aircraft_spawner_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_aircraft_spawner_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x18
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x48))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x30040)
	if C68kScript.s32(def_key) == 0x18:
		memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x10)
	memory.write_u32(ctl + 4, _definition(def_key))
	var left: int = overlay_rng_next() & 0xf
	var rng: int = memory.read_s32(obj + 0x18)
	if rng == 0x80e80:
		left = (left & 3) + 1
	elif rng == 0xc15c0:
		left = (left & 3) + 2
	elif rng == 0x40740:
		left = left % 3
	memory.write_u16(ctl + 0x32, left)
	return ctl


## Leyte's ship classes (definition +6 factories; the third argument is the kind word, stored at +0x32; 1.0.3's took
## none or a word). Each allocates a cleared controller (0x2184) - 0x69c..0x772 bytes for 1.0.3's 0x56..300 - with
## the shared fields moved: range word +0x668 (1.0.3 +0x44), the target distance long +0x66c = 1000000000 (+0x48)
## and word +0x656 = definition word +0x1a (+0x32). A nonzero kind sets the object's long +0x18 to 1000000000.

## 0x17ba2 (1.0.3's create_carrier_controller 0x150a8, rewritten): 0x69c bytes (0x1c below); the object's word +0x16
## |= 0x4000; words +0x698 / +0x69a = -1, range 7. The first carrier (0x2104c still 0) becomes it only while the
## mode word 0x23d84 is set and the anchor (0x23d6c / 0x23d70 / 0x23d74) lies within the object's long +0x20 of its
## position (approx_dist3_xyz 0x4886, 0x30 below; not beyond): the mode word becomes 2. Then +0x38 = 0x18e20.
func create_carrier_controller(obj: int, def_key: int, kind: int = 0) -> int:
	assert(memory != null, "create_carrier_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x1c
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x69c))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u16(ctl + 0x32, C68kScript.u16(kind))
	memory.write_u16(obj + 0x16, memory.read_u16(obj + 0x16) | 0x4000)
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u16(ctl + 0x698, 0xffff)
	memory.write_u16(ctl + 0x69a, 0xffff)
	memory.write_u32(ctl + 0x66c, 1000000000)
	memory.write_u16(ctl + 0x656, memory.read_u16(memory.read_u32(ctl + 4) + 0x1a))
	memory.write_u16(ctl + 0x668, 7)
	if memory.read_u32(0x2104c) == 0 and memory.read_u16(0x23d84) != 0:
		a7 = entry_a7 - 0x30
		var d: int = approx_dist3_xyz(memory.read_s32(0x23d6c), memory.read_s32(0x23d70), memory.read_s32(0x23d74),
				memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc))
		a7 = entry_a7
		if not (d > memory.read_s32(obj + 0x20)):
			memory.write_u16(0x23d84, 2)
			memory.write_u32(0x2104c, ctl)
	memory.write_u32(ctl + 0x38, 0x18e20)
	if kind != 0:
		memory.write_u32(obj + 0x18, 1000000000)
	return ctl

## 0x18544 (1.0.3's create_sinking_controller 0x15560, rewritten): 0x6a0 bytes (0x14 below); range 3, +0x38 =
## 0x18376, word +0x696 = 2, word +0x698 = 0.
func create_sinking_controller(obj: int, def_key: int, kind: int = 0) -> int:
	assert(memory != null, "create_sinking_controller requires original global memory")
	var ctl: int = _leyte_ship_controller(obj, def_key, kind, 0x6a0, 3)
	memory.write_u32(ctl + 0x38, 0x18376)
	memory.write_u16(ctl + 0x696, 2)
	memory.write_u16(ctl + 0x698, 0)
	if kind != 0:
		memory.write_u32(obj + 0x18, 1000000000)
	return ctl

## 0x185ba (1.0.3's create_basic_ship_controller 0x155cc, rewritten): 0x6a8 bytes; range 7, word +0x696 = 3; the
## object's flags byte +0x15 bit 1.
func create_basic_ship_controller(obj: int, def_key: int, kind: int = 0) -> int:
	assert(memory != null, "create_basic_ship_controller requires original global memory")
	var ctl: int = _leyte_ship_controller(obj, def_key, kind, 0x6a8, 7)
	memory.write_u16(ctl + 0x696, 3)
	memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 2)
	if kind != 0:
		memory.write_u32(obj + 0x18, 1000000000)
	return ctl

## 0x1862a (Leyte; the shape of 1.0.3's create_basic_ship_controller): 0x696 bytes; a nonzero kind also sets the
## object's word +0x16 |= 0x4000 (before the shared fields); +0x38 = 0x18e20, range 7.
func leyte_create_escort_controller(obj: int, def_key: int, kind: int = 0) -> int:
	assert(memory != null, "leyte_create_escort_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x14
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x696))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u16(ctl + 0x32, C68kScript.u16(kind))
	if kind != 0:
		memory.write_u32(obj + 0x18, 1000000000)
		memory.write_u16(obj + 0x16, memory.read_u16(obj + 0x16) | 0x4000)
	memory.write_u32(ctl + 0x38, 0x18e20)
	memory.write_u32(ctl + 0x66c, 1000000000)
	memory.write_u16(ctl + 0x668, 7)
	memory.write_u16(ctl + 0x656, memory.read_u16(memory.read_u32(ctl + 4) + 0x1a))
	return ctl

## 0x18088 (1.0.3's create_firing_ship_controller 0x152ee, the same 16 instructions on the moved fields): 0x772
## bytes (300; 0xc below: no saved register); word +0x696 = 0x14 (1.0.3 +0x50), range 7. Returns the controller
## (still in D0 from the allocation).
func create_firing_ship_controller(obj: int, def_key: int) -> int:
	assert(memory != null, "create_firing_ship_controller requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0xc
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x772))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u16(ctl + 0x696, 0x14)
	memory.write_u32(ctl + 0x66c, 1000000000)
	memory.write_u16(ctl + 0x668, 7)
	memory.write_u16(ctl + 0x656, memory.read_u16(memory.read_u32(ctl + 4) + 0x1a))
	return ctl

## The shared opening of 0x18544 / 0x185ba: a cleared controller of size (0x14 below: link, two saved registers,
## the size pushed, bsr), +0 the object, kind word +0x32 and the definition (0x18544 stores the kind first, 0x185ba
## the definition first: the same result), the target distance 1000000000, range word +0x668 and word +0x656 =
## definition word +0x1a.
func _leyte_ship_controller(obj: int, def_key: int, kind: int, size: int, range_word: int) -> int:
	var entry_a7: int = a7
	a7 = entry_a7 - 0x14
	var ctl: int = C68kScript.u32(heap_alloc_cleared(size))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u16(ctl + 0x32, C68kScript.u16(kind))
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u32(ctl + 0x66c, 1000000000)
	memory.write_u16(ctl + 0x668, range_word)
	memory.write_u16(ctl + 0x656, memory.read_u16(memory.read_u32(ctl + 4) + 0x1a))
	return ctl


## 0xf4a8 (1.0.3's aircraft_create 0xe0ae, rewritten; five saved registers, no frame): init_aircraft_controller(obj,
## key, kind) (0xf3b8, 0x24 below), byte +0x64c = 1, long +0x2e = object long +8 - sext definition word +0x42.
## The player is now the first aircraft of key 1 (1.0.3: the first aircraft) while *0x20b28 is 0: it becomes
## *0x20b28, +0x32 = 0xfcf6, word +0x698 |= 0x4000; the bombs' weight leaves word +0x642 (extb +0x68d << 4), the
## stores are loaded from the host - bombs +0x68d = byte +0xb9, +0x68e = byte +0xbb, +0x690 = byte +0xbd - and word
## +0x642 takes (extb +0x68d << 4) + 5 * extb +0x68e (word add); both tanks +0x691 / +0x692 = extb tank * host word
## +0xb6 / 100 (muls.l / divs.l), the left one 1 when both come out empty (the right one tested as the quotient's
## low byte); flags long +0x14 |= 0x102140 and long +0x18 = 1000000000. (1.0.3's host mode 2 loading is gone.)
## Others: +0x32 = 0x13038, flags |= 0x102100. Then the shadow by key (spawn_object_shadow 0x1ca84 (x, 0, z, kind,
## obj), 0x2c below): key 1 kind 1; key 2 kind 5; key 3 kind 6, the first of them also claiming 0x21074; after kinds
## 5 / 6 the bombs byte +0x68d += 1 and word +0x642 += 0x10. Other keys spawn no shadow. Returns the controller.
func aircraft_create(obj: int, def_key: int, kind: int) -> int:
	assert(memory != null, "aircraft_create requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x24
	var ctl: int = C68kScript.u32(init_aircraft_controller(obj, def_key, kind))
	a7 = entry_a7
	_aircraft_common(obj, ctl)
	var key: int = C68kScript.s32(def_key)
	if memory.read_u32(0x20b28) == 0 and key == 1:
		memory.write_u32(0x20b28, ctl)
		memory.write_u32(ctl + 0x32, 0xfcf6)
		memory.write_u16(ctl + 0x698, memory.read_u16(ctl + 0x698) | 0x4000)
		memory.write_u16(ctl + 0x642, C68kScript.u16(memory.read_u16(ctl + 0x642) - (memory.read_s8(ctl + 0x68d) << 4)))
		var host: int = memory.read_u32(0x207fc)
		memory.write_u8(ctl + 0x68d, memory.read_u8(host + 0xb9))
		memory.write_u8(ctl + 0x68e, memory.read_u8(host + 0xbb))
		memory.write_u8(ctl + 0x690, memory.read_u8(host + 0xbd))
		var load: int = (memory.read_s8(ctl + 0x68d) << 4) + 5 * memory.read_s8(ctl + 0x68e)
		memory.write_u16(ctl + 0x642, C68kScript.u16(memory.read_u16(ctl + 0x642) + load))
		var pct: int = memory.read_s16(host + 0xb6)
		var left: int = C68kScript.divs_trunc(C68kScript.s32(memory.read_s8(ctl + 0x691) * pct), 100)
		memory.write_u8(ctl + 0x691, C68kScript.u8(left))
		var right: int = C68kScript.divs_trunc(C68kScript.s32(memory.read_s8(ctl + 0x692) * pct), 100)
		memory.write_u8(ctl + 0x692, C68kScript.u8(right))
		if memory.read_u8(ctl + 0x691) == 0 and C68kScript.u8(right) == 0:
			memory.write_u8(ctl + 0x691, 1)
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x102140)
		memory.write_u32(obj + 0x18, 1000000000)
	else:
		memory.write_u32(ctl + 0x32, 0x13038)
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x102100)
	var shadow_kind: int = 0
	if key == 1:
		shadow_kind = 1
	elif key == 2:
		shadow_kind = 5
	elif key == 3:
		if memory.read_u32(0x21074) == 0:
			memory.write_u32(0x21074, ctl)
		shadow_kind = 6
	if shadow_kind != 0:
		a7 = entry_a7 - 0x2c
		spawn_object_shadow(memory.read_u32(obj + 4), 0, memory.read_u32(obj + 0xc), shadow_kind, obj)
		a7 = entry_a7
		if shadow_kind != 1:
			memory.write_u8(ctl + 0x68d, C68kScript.u8(memory.read_u8(ctl + 0x68d) + 1))
			memory.write_u16(ctl + 0x642, C68kScript.u16(memory.read_u16(ctl + 0x642) + 0x10))
	return ctl


## 0x1ec7e (Leyte's class of definitions 0x27 / 0x28, which the template registration 0x1ed5c redirects here; link.w
## a6,#-0xc, three saved registers): a cleared 0x634-byte controller (0x2184, 0x24 below), +0 the object, kind word
## +0x610 = the third argument, the object's flags long +0x14 |= 0x102000, +4 the definition, word +0x62a = -1,
## long +0x616 = 0. Kind 99 without a route (+0x612, always 0 when new) gets one: leyte_build_route(object, 8, frame
## word a6 - 2) (0x1a936, 0x2c below) - none clears the kind; otherwise the route's index word +4 = the nearest
## point, the next waypoint is taken (leyte_next_waypoint(ctl, a6 - 6, a6 - 0xa), 0x1eeee, 0x2c below) and heading
## word +0x22 = leyte_heading_to_point(object, the waypoint) (0x18712, 0x3c below: the step's arguments still pushed).
## Then the pose ring at +0x32 is filled with the object's position (fill_pose_history 0xf326, 0x30 below) and its
## angles 0, heading, 0 (fill_pose_history_angles 0xf360, 0x40 below). Returns the controller.
func leyte_create_route_ship(obj: int, def_key: int, kind: int) -> int:
	assert(memory != null, "leyte_create_route_ship requires original global memory")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	a7 = entry_a7 - 0x24
	var ctl: int = C68kScript.u32(heap_alloc_cleared(0x634))
	a7 = entry_a7
	memory.write_u32(ctl, C68kScript.u32(obj))
	memory.write_u16(ctl + 0x610, C68kScript.u16(kind))
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x102000)
	memory.write_u32(ctl + 4, _definition(def_key))
	memory.write_u16(ctl + 0x62a, 0xffff)
	memory.write_u32(ctl + 0x616, 0)
	if C68kScript.s32(kind) == 99 and memory.read_u32(ctl + 0x612) == 0:
		a7 = entry_a7 - 0x2c
		var route: int = C68kScript.u32(leyte_build_route(memory.read_u32(ctl), 8, a6 - 2))
		a7 = entry_a7
		memory.write_u32(ctl + 0x612, route)
		if route == 0:
			memory.write_u16(ctl + 0x610, 0)
		else:
			memory.write_u16(route + 4, memory.read_u16(a6 - 2))
			a7 = entry_a7 - 0x2c
			leyte_next_waypoint(ctl, a6 - 6, a6 - 0xa)
			a7 = entry_a7
			var point: int = memory.read_u32(a6 - 6)
			a7 = entry_a7 - 0x3c
			var heading: int = leyte_heading_to_point(obj, memory.read_u32(point), memory.read_u32(point + 4), memory.read_u32(point + 8))
			a7 = entry_a7
			memory.write_u16(ctl + 0x22, C68kScript.u16(heading))
	a7 = entry_a7 - 0x30
	fill_pose_history(C68kScript.u32(ctl + 0x32), memory.read_u32(obj + 4), memory.read_u32(obj + 8), memory.read_u32(obj + 0xc))
	a7 = entry_a7 - 0x40
	fill_pose_history_angles(C68kScript.u32(ctl + 0x32), 0, memory.read_s16(ctl + 0x22), 0)
	a7 = entry_a7
	return ctl

## 0x1a936 (Leyte; link.w a6,#-0xc, ten saved registers): a route for obj from the shape it sails along. Up its
## owner chain (+0x24, then each group's +0x24) the first group child (+0x32 table, 0x18-byte records, +0x14; sext
## word +0x30 of them) that is a shape (byte +0x28 == 2) with word +0x3c == 1, no controller (+0) and a face record
## (+0x4a) whose byte +0x13 has bit 1: its face's vertices (count word +0x14, word indices from +0x18 into the vertex
## buffer +0x46, 12 bytes each, every component asr.l by the shape's word +0x36) are copied after a header of
## header_size bytes into a cleared block (0x2184, 0x40 below), the count stored as the word before them; the index
## of the vertex nearest obj's position (approx_dist3_xyz 0x4886, 0x54 below; strictly nearer, from 0x7fffffff) is
## stored at nearest_out (a word). Returns the block, or 0 when the chain has no such shape.
func leyte_build_route(obj: int, header_size: int, nearest_out: int) -> int:
	assert(memory != null, "leyte_build_route requires original global memory")
	var entry_a7: int = a7
	var group: int = memory.read_u32(obj + 0x24)
	while group != 0:
		var i: int = 0
		while i < memory.read_s16(group + 0x30):
			var child: int = memory.read_u32(C68kScript.u32(memory.read_u32(group + 0x32) + 0x18 * i + 0x14))
			var faces: int = memory.read_u32(child + 0x4a) if child != 0 else 0
			if child != 0 and memory.read_u8(child + 0x28) == 2 and memory.read_u16(child + 0x3c) == 1 \
					and memory.read_u32(child) == 0 and faces != 0 and (memory.read_u8(faces + 0x13) & 2) != 0:
				var count: int = memory.read_s16(faces + 0x14)
				var shift: int = memory.read_s16(child + 0x36) & 0x3f
				a7 = entry_a7 - 0x40
				var block: int = C68kScript.u32(heap_alloc_cleared(C68kScript.s32(header_size + count * 12)))
				a7 = entry_a7
				memory.write_u16(C68kScript.u32(block + header_size - 2), C68kScript.u16(count))
				var points: int = C68kScript.u32(block + header_size)
				var best: int = 0x7fffffff
				var k: int = 0
				while k < count:
					var v: int = C68kScript.u32(memory.read_u32(child + 0x46) + 12 * memory.read_s16(C68kScript.u32(memory.read_u32(faces + 0x18) + 2 * k)))
					var x: int = C68kScript.asr32(memory.read_s32(v), shift)
					var y: int = C68kScript.asr32(memory.read_s32(v + 4), shift)
					var z: int = C68kScript.asr32(memory.read_s32(v + 8), shift)
					a7 = entry_a7 - 0x54
					var d: int = approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc), x, y, z)
					a7 = entry_a7
					if d < best:
						best = d
						memory.write_u16(nearest_out, C68kScript.u16(k))
					memory.write_u32(C68kScript.u32(points + 12 * k), C68kScript.u32(x))
					memory.write_u32(C68kScript.u32(points + 12 * k + 4), C68kScript.u32(y))
					memory.write_u32(C68kScript.u32(points + 12 * k + 8), C68kScript.u32(z))
					k += 1
				return block
			i += 1
		group = memory.read_u32(group + 0x24)
	return 0

## 0x1eeee (Leyte; link.w a6,#-4, four saved registers): ctl's next waypoint. With a route (+0x612) and byte +0x62d
## bit 1 clear: the route's current point (index word route +4, points from route +8) is measured from the object in
## x / z (approx_dist3_xyz(x, 0, z, px, 0, pz), 0x34 below) into *dist_out; within 0xa9 the index advances (back to 0
## at the count word route +6) and the new point goes to *point_out, returning 1; else the current point, 0.
## Otherwise the static point 0x2cdf8 = (+0x622, 0, +0x626) goes to *point_out and is measured the same way: 1
## within 0xa9, else 0.
func leyte_next_waypoint(ctl: int, point_out: int, dist_out: int) -> int:
	assert(memory != null, "leyte_next_waypoint requires original global memory")
	var entry_a7: int = a7
	var obj: int = memory.read_u32(ctl)
	var route: int = memory.read_u32(ctl + 0x612)
	if (memory.read_u8(ctl + 0x62d) & 2) == 0 and route != 0:
		var idx_at: int = C68kScript.u32(route + 4)
		var point: int = C68kScript.u32(idx_at + 4 + 12 * memory.read_s16(idx_at))
		a7 = entry_a7 - 0x34
		var d: int = approx_dist3_xyz(memory.read_s32(obj + 4), 0, memory.read_s32(obj + 0xc), memory.read_s32(point), 0, memory.read_s32(point + 8))
		a7 = entry_a7
		memory.write_u32(dist_out, C68kScript.u32(d))
		if d > 0xa9:
			memory.write_u32(point_out, point)
			return 0
		memory.write_u16(idx_at, C68kScript.u16(memory.read_u16(idx_at) + 1))
		if not (memory.read_s16(idx_at + 2) > memory.read_s16(idx_at)):
			memory.write_u16(idx_at, 0)
		memory.write_u32(point_out, C68kScript.u32(idx_at + 4 + 12 * memory.read_s16(idx_at)))
		return 1
	memory.write_u32(0x2cdf8, memory.read_u32(ctl + 0x622))
	memory.write_u32(0x2cdfc, 0)
	memory.write_u32(0x2ce00, memory.read_u32(ctl + 0x626))
	memory.write_u32(point_out, 0x2cdf8)
	a7 = entry_a7 - 0x34
	var d2: int = approx_dist3_xyz(memory.read_s32(obj + 4), 0, memory.read_s32(obj + 0xc), memory.read_s32(ctl + 0x622), 0, memory.read_s32(ctl + 0x626))
	a7 = entry_a7
	memory.write_u32(dist_out, C68kScript.u32(d2))
	return 1 if d2 <= 0xa9 else 0

## 0x18712 (Leyte; link.w a6,#-4, five saved registers): the heading from obj to (x, y, z). dx = x - object +4, dz = z
## - object +0xc; s counts the halvings (asr.l) of |dx| + |dz| (abs_int 0x38e8) until it fits in 15 bits (andi.w
## #$8000 keeps the upper word, so the tst.l sees it too); then
## attitude_from_velocity(dx asr s, 0, dz asr s, 0, 0, no pitch, the frame word a6 - 2) (0x2554, 0x3c below; speed 0
## leaves the pitch alone). Returns that heading word, sign-extended.
func leyte_heading_to_point(obj: int, x: int, _y: int, z: int) -> int:
	assert(memory != null, "leyte_heading_to_point requires original global memory")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var dx: int = C68kScript.s32(C68kScript.s32(x) - memory.read_s32(obj + 4))
	var dz: int = C68kScript.s32(C68kScript.s32(z) - memory.read_s32(obj + 0xc))
	var total: int = C68kScript.s32(abs_int(dx) + abs_int(dz))
	var s: int = 0
	while (C68kScript.u32(total) & 0xffff8000) != 0:
		s += 1
		total = C68kScript.asr32(total, 1)
	a7 = entry_a7 - 0x3c
	attitude_from_velocity(C68kScript.asr32(dx, s), 0, C68kScript.asr32(dz, s), 0, 0, 0, a6 - 2)
	a7 = entry_a7
	return memory.read_s16(a6 - 2)

## Leyte's routines of the frame step's closure (hook 10), each drafted from 1.0.3's port through the placed routine
## pair (sources/leyte-gulf-1-0/overlay_placement.json: addresses and record fields from the aligned instructions)
## and settled against Leyte's listing. Where Leyte's code differs only in testing a loop's count before entering
## it (1.0.3 jumped to the test at the loop's bottom: the same iterations), the body is 1.0.3's.

## 0x311e (1.0.3's transform_int3_vecs_asr_by_1d744 0x27e2; the count tested before the loop): dst = camera matrix
## (0x23c74 words, rows of 3) * (src >> shift as words), + 0x800 asr 12, for an unsigned 32-bit count of int3
## vectors.
func transform_int3_vecs_asr_by_1d744(count: int, src: int, dst: int, shift: int) -> void:
	assert(memory != null, "transform_int3_vecs_asr_by_1d744 requires original global memory")
	var m: int = 0x23c74
	for i in C68kScript.u32(count):
		var x: int = C68kScript.s16(C68kScript.asr32(memory.read_u32(src), shift))
		var y: int = C68kScript.s16(C68kScript.asr32(memory.read_u32(src + 4), shift))
		var z: int = C68kScript.s16(C68kScript.asr32(memory.read_u32(src + 8), shift))
		for j in 3:
			var sum: int = C68kScript.s32(x * memory.read_s16(m + 2 * j) + y * memory.read_s16(m + 6 + 2 * j) + z * memory.read_s16(m + 0xc + 2 * j) + 0x800)
			memory.write_u32(dst + 4 * j, C68kScript.u32(C68kScript.asr32(sum, 12)))
		src = C68kScript.u32(src + 12)
		dst = C68kScript.u32(dst + 12)

## 0x3200 (1.0.3's transform_vecs12_by_matrix3 0x28bc; the count tested before the loop): out_i = (x * m[i] + y *
## m[3 + i] + z * m[6 + i] + 0x800) asr 12 per 12-byte record (words +2 / +6 / +0xa), stored as words. 32-bit count.
func transform_vecs12_by_matrix3(count: int, src: int, dst: int, matrix: int) -> void:
	assert(memory != null, "transform_vecs12_by_matrix3 requires original global memory")
	var y: int
	var z: int
	var count_hi: int
	var x: int

	count = C68kScript.u32(count)
	while true:
		count_hi = C68kScript.u16(count >> 0x10)
		x = C68kScript.s16(count - 1)
		count = C68kScript.u32((count_hi << 0x10) | C68kScript.u16(x))
		if x == -1:
			count = C68kScript.u32(count_hi * 0x10000 - 1)
			if count_hi == 0:
				break
		x = memory.read_s16(src + 2)
		y = memory.read_s16(src + 6)
		z = memory.read_s16(src + 10)
		memory.write_u16(dst + 2, C68kScript.u16(C68kScript.asr32(z * memory.read_s16(matrix + 12) + y * memory.read_s16(matrix + 6) + x * memory.read_s16(matrix + 0) + 0x800, 0xc)))
		memory.write_u16(dst + 6, C68kScript.u16(C68kScript.asr32(z * memory.read_s16(matrix + 14) + y * memory.read_s16(matrix + 8) + x * memory.read_s16(matrix + 2) + 0x800, 0xc)))
		memory.write_u16(dst + 10, C68kScript.u16(C68kScript.asr32(z * memory.read_s16(matrix + 16) + y * memory.read_s16(matrix + 10) + x * memory.read_s16(matrix + 4) + 0x800, 0xc)))
		src = src + 0xc
		dst = dst + 0xc
	return

## 0x22ca (1.0.3's scale_int3_vecs 0x1c8c; the count tested before the loop): multiplies count int3 vectors
## component-wise (muls.l, low 32 bits, stride 12; src and dst may alias). 32-bit count.
func scale_int3_vecs(count: int, src: int, dst: int, sx: int, sy: int, sz: int) -> void:
	assert(memory != null, "scale_int3_vecs requires original global memory")
	var count_hi: int
	var count_lo: int

	count = C68kScript.u32(count)
	while true:
		count_hi = C68kScript.u16(count >> 0x10)
		count_lo = C68kScript.s16(count - 1)
		count = C68kScript.u32((count_hi << 0x10) | C68kScript.u16(count_lo))
		if count_lo == -1:
			count = C68kScript.u32(count_hi * 0x10000 - 1)
			if count_hi == 0:
				break
		memory.write_u32(dst, C68kScript.u32(memory.read_s32(src) * C68kScript.s32(sx)))
		memory.write_u32(dst + 4, C68kScript.u32(memory.read_s32(src + 4) * C68kScript.s32(sy)))
		memory.write_u32(dst + 8, C68kScript.u32(memory.read_s32(src + 8) * C68kScript.s32(sz)))
		dst = dst + 0xc
		src = src + 0xc
	return

## 0xa5be (1.0.3's apply_controller_velocities 0x945c on Leyte's object layout; the count tested before the loop):
## for the live controllers (list 0x21bd4, count word 0x219b2) whose object has flags byte +0x16 bit 5 (1.0.3 +6):
## the old position +4 / +8 / +0xc (+0x18..) goes to controller +0x14..+0x1c, +0x2e is cleared and the object moves by
## the velocity (controller +8 / +0xc / +0x10).
func apply_controller_velocities() -> void:
	assert(memory != null, "apply_controller_velocities requires original global memory")
	var slot: int = 0x21bd4
	for i in memory.read_u16(0x219b2):
		var ctl: int = memory.read_u32(slot)
		slot += 4
		var obj: int = memory.read_u32(ctl)
		if obj == 0 or (memory.read_u8(obj + 0x16) & 0x20) == 0:
			continue
		for k in 3:
			memory.write_u32(ctl + 0x14 + 4 * k, memory.read_u32(obj + 4 + 4 * k))
		memory.write_u32(ctl + 0x2e, 0)
		for k in 3:
			memory.write_u32(obj + 4 + 4 * k, C68kScript.u32(memory.read_u32(obj + 4 + 4 * k) + memory.read_u32(ctl + 8 + 4 * k)))

## 0xa61a (1.0.3's tick_flagged_object_controllers 0x94b2; both counts tested before their loops): compact the
## controller list (0x21bd4, count word 0x219b6 of the long 0x219b4) to those still holding an object and run the
## tick (definition +2, 0x18 below) of those whose object has flags byte +0x15 bit 2 (1.0.3 +5). When entries were
## dropped, controllers the ticks appended (the count long grew) are moved down after the kept ones and the count
## becomes the new length.
func tick_flagged_object_controllers() -> void:
	assert(memory != null, "tick_flagged_object_controllers requires original global memory")
	var entry_a7: int = a7
	var dst: int = 0x21bd4
	var src: int = 0x21bd4
	var old: int = memory.read_s16(0x219b4 + 2)
	for i in memory.read_u16(0x219b4 + 2):
		var ctl: int = memory.read_u32(src)
		src += 4
		var obj: int = memory.read_u32(ctl)
		if obj == 0:
			continue
		memory.write_u32(dst, ctl)
		dst += 4
		if (memory.read_u8(obj + 0x15) & 4) != 0 and memory.read_u32(memory.read_u32(ctl + 4) + 2) != 0:
			a7 = entry_a7 - 0x18
			_jsr(memory.read_u32(memory.read_u32(ctl + 4) + 2), [ctl])
			a7 = entry_a7
	var kept: int = C68kScript.s16(C68kScript.asr32(dst - 0x21bd4, 2))
	if old > kept:
		if old < memory.read_s32(0x219b4):
			for i in (memory.read_u16(0x219b4 + 2) - old) & 0xffff:
				memory.write_u32(dst, memory.read_u32(src))
				dst += 4
				src += 4
		memory.write_u32(0x219b4, C68kScript.u32(C68kScript.asr32(dst - 0x21bd4, 2)))

## 0x160b0 (1.0.3's age_contact_records 0x1375a on Leyte's object layout; the count tested before the loop): age the
## 256 contact records (0x215a4): a record not touched this frame (word +8 == 0) is freed (heap_free_coalesce 0x1fe8)
## and its slot cleared - first releasing object a's fractal reference (0x1746c) when b is terrain (flags byte +0x16
## bit 1; 1.0.3 +6) and a has +0x16 bit 5 and a reference (+0x2e; 1.0.3 +0x2c), both 0x14 below; a touched one counts
## its +8 down.
func age_contact_records() -> void:
	assert(memory != null, "age_contact_records requires original global memory")
	var entry_a7: int = a7
	for k in 0x100:
		var slot: int = 0x215a4 + 4 * k
		var rec: int = memory.read_u32(slot)
		if rec == 0:
			continue
		if memory.read_u16(rec + 8) != 0:
			memory.write_u16(rec + 8, C68kScript.u16(memory.read_u16(rec + 8) - 1))
			continue
		a7 = entry_a7 - 0x14
		var obj_a: int = memory.read_u32(rec)
		if (memory.read_u8(memory.read_u32(rec + 4) + 0x16) & 2) != 0 and (memory.read_u8(obj_a + 0x16) & 0x20) != 0 and memory.read_u32(obj_a + 0x2e) != 0:
			release_fractal_reference(obj_a)
		heap_free_coalesce(rec)
		a7 = entry_a7
		memory.write_u32(slot, 0)

## 0x6754 (1.0.3's build_visible_object_list 0x58cc on Leyte's object layout; the count tested before the loop): cull
## the visible list (0x22994). The view points (0x21f44, count 0x219b8) are turned into camera space
## (transform_int3_vecs_asr_by_1d744, 0x44 below); then two passes (the near objects up to visible_first_pass, word
## 0x219aa, then the far ones, after which it becomes the kept count) keep: objects with flags byte +0x17 bit 2 (no
## view point: the point cursor stays), objects the camera is inside (metric +0x1c - metric / 4 < radius +0x20; flags
## long +0x14 |= 8), and objects in front (z > 0) whose max(|x|, |y|) - r (r = radius asr word +0x34, words) times 1.5
## is under z + r (abs_int 0x38e8). visible_count (0x219bc) = kept, view_point_count = 0. (1.0.3: +7, +0x10, +0x14,
## +4, +0x32.)
func build_visible_object_list() -> void:
	assert(memory != null, "build_visible_object_list requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x30
	a7 = body - 0x14
	transform_int3_vecs_asr_by_1d744(memory.read_u32(0x219b8), 0x21f44, 0x21f44, 0)
	a7 = entry_a7
	var keep: int = 0x22994
	var read: int = 0x22994
	var point: int = 0x21f44
	for pass_index in 2:
		var left: int
		if pass_index == 0:
			left = memory.read_u16(0x219aa)
		else:
			left = (memory.read_u16(0x219bc + 2) - memory.read_u16(0x219aa)) & 0xffff
			memory.write_u16(0x219aa, C68kScript.u16(C68kScript.asr32(keep - 0x22994, 2)))
		while left != 0:
			left -= 1
			var obj: int = memory.read_u32(read)
			read += 4
			if (memory.read_u8(obj + 0x17) & 4) != 0:
				memory.write_u32(keep, obj)
				keep += 4
				continue
			var inner: int = C68kScript.s32(-C68kScript.s32(C68kScript.asr32(memory.read_u32(obj + 0x1c), 2) - memory.read_u32(obj + 0x1c)))
			if inner < memory.read_s32(obj + 0x20):
				memory.write_u32(keep, obj)
				keep += 4
				memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 8)
			elif memory.read_s32(point + 8) > 0:
				a7 = body - 8
				var ax: int = C68kScript.s16(abs_int(memory.read_s32(point)))
				var ay: int = C68kScript.s16(abs_int(memory.read_s32(point + 4)))
				a7 = entry_a7
				var r: int = C68kScript.s16(C68kScript.asr32(memory.read_u32(obj + 0x20), memory.read_u16(obj + 0x34)))
				var edge: int = C68kScript.s16((ax if ay < ax else ay) - r)
				var reach: int = (C68kScript.s16(edge) >> 1) + edge
				if C68kScript.s32(r + memory.read_s32(point + 8)) > reach:
					memory.write_u32(keep, obj)
					keep += 4
			point += 12
	memory.write_u32(0x219bc, C68kScript.u32(C68kScript.asr32(keep - 0x22994, 2)))
	memory.write_u32(0x219b8, 0)

## Near mode (1.0.3's _face_near_mode with Leyte's back-face queue cursor 0x2c0be; the face flags byte +0x13 as in
## 1.0.3: ori.b #$10 / andi.b #$ef at 0xb1ca / 0xb1d2): a back-facing face is kept in the back-face queue when
## one-sided, else flagged (+0x13 |= 0x10) and processed; a front-facing one loses the flag. Returns whether the
## root walk stops.
func _face_near_mode(face: int, back: int, one_sided: bool) -> bool:
	if back != 0:
		if one_sided:
			var q: int = memory.read_u32(0x2c0be)
			memory.write_u32(q, face)
			memory.write_u32(0x2c0be, C68kScript.u32(q + 4))
			return true
		memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) | 0x10)
	else:
		memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) & 0xef)
	return false

## The root walks of emit_faces_in_order (0xb14e) and emit_faces_unnumbered (0xb220) (1.0.3's _emit_root_chains; the
## root count tested before the loop; the prepared object's metric at +0x1c for 1.0.3's +0x10): for each of
## root_face_count roots (word 0x2c0ae), its +0x12 chain until a face is far (+0x1c < the object distance) or +0x13
## bit 1 (next root). Near mode (word 0x2c0ac): back-facing one-sided faces go to the back-face queue and end the
## chain, the others are flagged; outside it a back-facing one-sided face ends the chain (face_is_back_facing 0xad30,
## 0x28 below). Faces reached are queued (numbered unless unnumbered).
func _emit_root_chains(numbered: bool) -> void:
	var entry_a7: int = a7
	var distance: int = memory.read_s32(memory.read_u32(0x2c0ba) + 0x1c)
	var roots: int = memory.read_u16(0x2c0ae)
	var root: int = 0
	while true:
		roots = (roots - 1) & 0xffff
		if roots == 0xffff:
			break
		var i: int = C68kScript.s16(root)
		while true:
			var face: int = _face_record(i)
			if distance > memory.read_s32(face + 0x1c) or (memory.read_u8(face + 0x13) & 2) != 0:
				break
			var one_sided: bool = (memory.read_u8(face + 0x13) & 1) == 0
			if memory.read_u16(0x2c0ac) != 0:
				if _face_near_mode(face, _call_back_facing(entry_a7, 0x28, i, face), one_sided):
					break
			elif one_sided and _call_back_facing(entry_a7, 0x28, i, face) != 0:
				break
			_emit_face(face, numbered)
			i = memory.read_s8(face + 0x12)
			if i < 0:
				break
		root = C68kScript.s16(root + 1)

## 0xb14e (1.0.3's emit_faces_in_order 0x9ed8): root chains, faces numbered.
func emit_faces_in_order() -> void:
	assert(memory != null, "emit_faces_in_order requires original global memory")
	_emit_root_chains(true)

## 0xb220 (1.0.3's emit_faces_unnumbered 0x9fa4): root chains, faces queued without numbers.
func emit_faces_unnumbered() -> void:
	assert(memory != null, "emit_faces_unnumbered requires original global memory")
	_emit_root_chains(false)
