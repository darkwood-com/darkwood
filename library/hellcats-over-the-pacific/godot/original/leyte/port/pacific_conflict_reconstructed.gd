
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
	## hook 10: the frame step (0x741e)
	code_table[0x741e] = Callable(self, "overlay_frame_step")
	## routines reached through pointers from hook 10's frame: the broad-phase tests (collect_far_objects) and the
	## turret's pre-render hook (a controller definition's +0xe)
	code_table[0x16784] = Callable(self, "test_proximity_collision")
	code_table[0x167e4] = Callable(self, "test_player_vs_type0e_proximity")
	code_table[0x1d4f6] = Callable(self, "turret_pre_render")
	code_table[0x16be2] = Callable(self, "handle_player_contact")
	code_table[0x16812] = Callable(self, "test_object_collision")
	## a sinking ship's pre-render hook (its controller definition's +0xe)
	code_table[0x17dfa] = Callable(self, "leyte_pending_pose_pre_render")
	## a ship's and a carrier's collision hooks (controller definition +0x12, called (ship, other, record))
	code_table[0x17e0e] = Callable(self, "leyte_ship_collision_hook")
	code_table[0x17e54] = Callable(self, "leyte_carrier_deck_hook")
	## the scene visitor point_near_camera_or_type80_object hands to for_each_mission_scene_object
	code_table[0xce0] = Callable(self, "leyte_type80_centroid_visitor")
	code_table[0x844e] = Callable(self, "process_flight_input_and_outcome")
	code_table[0x1cb04] = Callable(self, "tick_shadow")
	code_table[0x1dd1c] = Callable(self, "tick_view_attached_object")
	code_table[0x1d2e2] = Callable(self, "tick_turret")
	code_table[0x1bf64] = Callable(self, "tick_aircraft_spawner")
	code_table[0x1cb66] = Callable(self, "shadow_pre_render")
	code_table[0x1cdce] = Callable(self, "tick_flak_gun")
	code_table[0x18f94] = Callable(self, "leyte_ship_tick")
	code_table[0x18e20] = Callable(self, "leyte_ship_mover")
	code_table[0x18376] = Callable(self, "leyte_landing_ship_mover")
	code_table[0x18d1c] = Callable(self, "leyte_ship_kind1")
	# overlay_printf is reached by bsr with one to six longs (the format first)
	code_table[0x189c] = func(fmt: int, b: int = 0, c: int = 0, d: int = 0, e: int = 0, f: int = 0) -> int: return overlay_printf(fmt, b, c, d, e, f)
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

## Batch 2 of the frame step's closure (hook 10): drafted from 1.0.3's port through the placed routine pairs
## (tools/leyte/draft_overlay_port.py) and settled against Leyte's listing. Each routine tests its loop counts before
## entering (1.0.3 jumped to the test at the bottom: the same iterations); what else differs is said with it.

## The view-vertex projection (1.0.3's _project_view_vertex), which Leyte inlines in project_prepared_object (0xb902)
## and transform_face_vertices (0xb796), with Leyte's projection scales (x word 0x27050, y word 0x2704e): z (word view
## +0xa) > 4: x = (word +2 muls.w scale_x) divs.l z, y = (-(word +6) muls.l scale_y) divs.l z; otherwise x =
## 0x80000000 (behind the near plane; y left as it was).
func _project_view_vertex(view: int, out: int) -> void:
	var z: int = memory.read_s16(view + 0xa)
	if z > 4:
		memory.write_u32(out, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(view + 2) * memory.read_s16(0x27050)), z)))
		memory.write_u32(out + 4, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(-memory.read_s16(view + 6) * memory.read_s16(0x2704e)), z)))
	else:
		memory.write_u32(out, 0x80000000)

## 0xb902 (1.0.3's project_prepared_object 0xa656; the object's fields each 2 further on, the face table read once
## before the face loop): set the renderer up for prepared_object (0x2c0ba): view_vertices (0x2c0b6) = +0x4e,
## face_table (0x2c4c6) = +0x4a, root_face_count (0x2c0ae) = word +0x3a, vertex_shift (0x2b296) = word +0x34,
## face_normals (0x2b286) = +0x46 + 12 * sext word +0x38, vertex0 (0x2b28a) = the first model vertex; every view vertex
## (word +0x38 of them) is projected into 0x2bbf8 (_project_view_vertex) and every face (word +0x3c) gets +0x13 |= 0x60.
func project_prepared_object() -> void:
	assert(memory != null, "project_prepared_object requires original global memory")
	var obj: int = memory.read_u32(0x2c0ba)
	memory.write_u32(0x2c0b6, memory.read_u32(obj + 0x4e))
	memory.write_u32(0x2c4c6, memory.read_u32(obj + 0x4a))
	memory.write_u16(0x2c0ae, memory.read_u16(obj + 0x3a))
	memory.write_u16(0x2b296, memory.read_u16(obj + 0x34))
	memory.write_u32(0x2b286, C68kScript.u32(C68kScript.s32(memory.read_s16(obj + 0x38) * 12) + memory.read_u32(obj + 0x46)))
	var verts: int = memory.read_u32(obj + 0x46)
	for k in 3:
		memory.write_u32(0x2b28a + 4 * k, memory.read_u32(verts + 4 * k))
	var view: int = memory.read_u32(0x2c0b6)
	var out: int = 0x2bbf8
	var left: int = memory.read_u16(memory.read_u32(0x2c0ba) + 0x38)
	while true:
		left = (left - 1) & 0xffff
		if left == 0xffff:
			break
		_project_view_vertex(view, out)
		view += 12
		out += 8
	var faces_left: int = memory.read_u16(memory.read_u32(0x2c0ba) + 0x3c)
	var flag: int = C68kScript.u32(memory.read_u32(0x2c4c6) + 0x13)
	while true:
		faces_left = (faces_left - 1) & 0xffff
		if faces_left == 0xffff:
			break
		memory.write_u8(flag, memory.read_u8(flag) | 0x60)
		flag += 0x20

## 0xb796 (1.0.3's transform_face_vertices 0xa4f2; registers renamed, the model's vertex buffer at +0x46): bring the
## face's vertices into camera space once per frame (vertex_done bytes 0x2b2a2): (vertex asr vertex_shift) - eye
## (words 0x2b29c / 0x2b29a / 0x2b298), as words, through object_matrix (0x2b29e: (sum of muls.w + 0x800) asr 12, words
## +2 / +6 / +0xa of view_vertices), then projected into 0x2bbf8 (_project_view_vertex).
func transform_face_vertices(face: int) -> void:
	assert(memory != null, "transform_face_vertices requires original global memory")
	var eye_x: int = memory.read_u16(0x2b29c)
	var eye_y: int = memory.read_u16(0x2b29a)
	var eye_z: int = memory.read_u16(0x2b298)
	var m: int = memory.read_u32(0x2b29e)
	var cursor: int = memory.read_u32(face + 0x18)
	var verts: int = memory.read_u32(memory.read_u32(0x2c0ba) + 0x46)
	var view: int = memory.read_u32(0x2c0b6)
	var shift: int = memory.read_s16(0x2b296)
	var left: int = memory.read_u16(face + 0x14)
	while true:
		left = (left - 1) & 0xffff
		if left == 0xffff:
			return
		var i: int = memory.read_s16(cursor)
		if memory.read_u8(C68kScript.u32(0x2b2a2 + i)) == 0:
			var out: int = C68kScript.u32(view + 12 * i)
			var v: int = C68kScript.u32(verts + 12 * i)
			memory.write_u8(C68kScript.u32(0x2b2a2 + i), 1)
			var d0: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v), shift) - eye_x)
			var d1: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v + 4), shift) - eye_y)
			var d2: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v + 8), shift) - eye_z)
			for k in 3:
				var acc: int = C68kScript.s32(d0 * memory.read_s16(m + 2 * k) + d1 * memory.read_s16(m + 6 + 2 * k) + d2 * memory.read_s16(m + 12 + 2 * k) + 0x800)
				memory.write_u16(out + 2 + 4 * k, C68kScript.u16(C68kScript.asr32(acc, 12)))
			_project_view_vertex(out, C68kScript.u32(0x2bbf8 + 8 * i))
		cursor = C68kScript.u32(cursor + 2)

## 1.0.3's _project_face_vertices with the model's vertex buffer at +0x46 (Leyte's project_face_vertices_xy 0x15a06 and
## _xz 0x15a6c): for each vertex index of the face (count word +0x14, list +0x18), the vertex (12-byte longs) asr.l by the
## word shift (mod 64) minus the word origins goes to the same index of out as words +2 and dst_b.
func _project_face_vertices(model: int, shift: int, face: int, origin_a: int, origin_b: int, out: int, src_b: int, dst_b: int) -> void:
	assert(memory != null, "_project_face_vertices requires original global memory")
	var model_verts: int = memory.read_u32(model + 0x46)
	var left: int = memory.read_s16(face + 0x14)
	var vidx: int = memory.read_u32(face + 0x18)
	shift = C68kScript.s16(shift) & 0x3f
	while true:
		left = C68kScript.s16(left - 1)
		if left == -1:
			return
		var mv: int = model_verts + memory.read_s16(vidx) * 0xc
		var ov: int = out + memory.read_s16(vidx) * 0xc
		memory.write_u16(ov + 2, C68kScript.u16(C68kScript.asr32(memory.read_u32(mv), shift) - origin_a))
		memory.write_u16(ov + dst_b, C68kScript.u16(C68kScript.asr32(memory.read_u32(mv + src_b), shift) - origin_b))
		vidx = vidx + 2

## 0x15a06 (1.0.3's project_face_vertices_xy 0x1318c): x / y to output words +2 / +6.
func project_face_vertices_xy(model: int, shift: int, face: int, origin_x: int, origin_y: int, out: int) -> void:
	_project_face_vertices(model, shift, face, origin_x, origin_y, out, 4, 6)

## 0x15a6c (1.0.3's project_face_vertices_xz 0x131ea): x / z to output words +2 / +0xa.
func project_face_vertices_xz(model: int, shift: int, face: int, origin_x: int, origin_z: int, out: int) -> void:
	_project_face_vertices(model, shift, face, origin_x, origin_z, out, 8, 10)

## 0x15ad2 (1.0.3's project_face_vertices_yz 0x13248; the vertex buffer at +0x46): vertex +4 / +8 asr.l shift minus the
## origins go to output words +6 / +0xa.
func project_face_vertices_yz(model: int, shift: int, face: int, origin_y: int, origin_z: int, out: int) -> void:
	assert(memory != null, "project_face_vertices_yz requires original global memory")
	var model_verts: int = memory.read_u32(model + 0x46)
	var left: int = memory.read_s16(face + 0x14)
	var vidx: int = memory.read_u32(face + 0x18)
	shift = C68kScript.s16(shift) & 0x3f
	while true:
		left = C68kScript.s16(left - 1)
		if left == -1:
			return
		var mv: int = model_verts + memory.read_s16(vidx) * 0xc
		var ov: int = out + memory.read_s16(vidx) * 0xc
		memory.write_u16(ov + 6, C68kScript.u16(C68kScript.asr32(memory.read_u32(mv + 4), shift) - origin_y))
		memory.write_u16(ov + 10, C68kScript.u16(C68kScript.asr32(memory.read_u32(mv + 8), shift) - origin_z))
		vidx = vidx + 2

## 0xaffc (1.0.3's emit_faces_bsp 0x9d86, the same 108 instructions; the prepared object's distance at +0x1c for 1.0.3's
## +0x10 - the face records' +0x10 / +0x11 subtrees and +0x1c distance are unchanged): BSP draw order from node index.
## Back-facing (face_is_back_facing 0xad30, 0x24 below) and near mode as in the root walks; a far face (+0x1c < the
## object distance) or one with +0x13 bit 1 is not emitted. Otherwise it is numbered and queued. Then the far side first:
## back-facing -> front subtree (+0x10), else back subtree (+0x11) (0x20 below); the face is renumbered; when it was
## emitted its +0x12 chain follows (emit_face_chain 0xaf8a, 0x24 below); then the other subtree.
func emit_faces_bsp(node: int) -> void:
	assert(memory != null, "emit_faces_bsp requires original global memory")
	var entry_a7: int = a7
	var idx: int = C68kScript.s16(node)
	var distance: int = memory.read_s32(memory.read_u32(0x2c0ba) + 0x1c)
	var face: int = _face_record(idx)
	var skip: bool = false
	var one_sided: bool = (memory.read_u8(face + 0x13) & 1) == 0
	var back: int = _call_back_facing(entry_a7, 0x24, idx, face)
	var emit_check: bool = true
	if memory.read_u16(0x2c0ac) != 0:
		if back != 0:
			skip = true
		if _face_near_mode(face, back, one_sided):
			emit_check = false
	elif back != 0:
		skip = true
		if one_sided:
			emit_check = false
	if emit_check:
		if distance > memory.read_s32(face + 0x1c) or (memory.read_u8(face + 0x13) & 2) != 0:
			skip = true
		else:
			_emit_face(face, true)
	var first: int = 0x10 if back != 0 else 0x11
	var second: int = 0x11 if back != 0 else 0x10
	if memory.read_s8(face + first) >= 0:
		a7 = entry_a7 - 0x20
		emit_faces_bsp(memory.read_s8(face + first))
		a7 = entry_a7
	memory.write_u16(face + 2, memory.read_u16(0x219a8))
	memory.write_u16(0x219a8, C68kScript.u16(memory.read_u16(0x219a8) + 1))
	if not skip and memory.read_s8(face + 0x12) >= 0:
		a7 = entry_a7 - 0x24
		emit_face_chain(memory.read_s8(face + 0x12), distance)
		a7 = entry_a7
	if memory.read_s8(face + second) >= 0:
		a7 = entry_a7 - 0x20
		emit_faces_bsp(memory.read_s8(face + second))
		a7 = entry_a7

## 0x6250 (1.0.3's collect_near_objects 0x53d8 on Leyte's object layout: the type byte at +0x28 for +0, the flags long
## +0x14 for +4, hidden +0x17 bit 1 for +7, metric +0x1c, range +0x18, radius +0x20, position +4 / +8 / +0xc, detail
## shift +0x34 and its scale word +0x36 for +0x32 / +0x34, last seen +0x3e): sort the near list (count long *count, low
## word) in place. An object inside its range and not hidden is kept; a visible shape (type 2, flags bit 6 clear) also
## gets its detail shift (asr steps bringing ((metric asr 2) + metric + radius) asl the scale word under 0x8000), a
## camera-relative view point (camera 0x23d68 / 0x23d64 / 0x23d60; asr by the shift; skipped for flags bit 2) at
## view_points (0x21f44, count 0x219b8), and a place in visible_list (0x22994, count 0x219bc). An object beyond its
## range is still kept while within 0x19c7f of it and not hidden, and a shape seen in the last 0x4b frames (0x21020 -
## +0x3e) stays; otherwise it retires (a shape with +0x3e set; retire_entity 0x603a, 0x30 below), is deactivated
## (0x6130, 0x30 below) and moves to entity_array_3 (0x233bc, count 0x219c4). Then view_point_count and *count.
func collect_near_objects(list: int, count_ptr: int) -> void:
	assert(memory != null, "collect_near_objects requires original global memory")
	var entry_a7: int = a7
	var point: int = C68kScript.u32(0x21f44 + memory.read_s32(0x219b8) * 12)
	var keep: int = list
	var read: int = list
	var left: int = memory.read_u16(count_ptr + 2)
	while left != 0:
		left -= 1
		var obj: int = memory.read_u32(read)
		read += 4
		var hidden: bool = (memory.read_u8(obj + 0x17) & 2) != 0
		var kept: bool = true
		if memory.read_s32(obj + 0x1c) > memory.read_s32(obj + 0x18) or hidden:
			if C68kScript.s32(memory.read_u32(obj + 0x1c) - memory.read_u32(obj + 0x18)) > 0x19c7f or hidden:
				if memory.read_u8(obj + 0x28) == 2 and C68kScript.s32(memory.read_u32(0x21020) - memory.read_u32(obj + 0x3e)) <= 0x4b:
					pass
				else:
					a7 = entry_a7 - 0x30
					if memory.read_u8(obj + 0x28) == 2 and memory.read_u32(obj + 0x3e) != 0:
						retire_entity(obj)
					deactivate_object(obj)
					a7 = entry_a7
					memory.write_u32(0x233bc + memory.read_s32(0x219c4) * 4, obj)
					memory.write_u32(0x219c4, C68kScript.u32(memory.read_u32(0x219c4) + 1))
					kept = false
		elif memory.read_u8(obj + 0x28) == 2 and (memory.read_u32(obj + 0x14) & 0x40) == 0:
			var size: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(C68kScript.asr32(memory.read_u32(obj + 0x1c), 2) + memory.read_u32(obj + 0x1c) + memory.read_u32(obj + 0x20)), memory.read_s16(obj + 0x36)))
			var detail: int = 0
			while (C68kScript.u32(size) & 0xffff8000) != 0:
				detail = C68kScript.s16(detail + 1)
				size = C68kScript.asr32(size, 1)
			memory.write_u16(obj + 0x34, C68kScript.u16(detail))
			if (memory.read_u32(obj + 0x14) & 4) == 0:
				memory.write_u32(point, C68kScript.u32(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0x4) - memory.read_u32(0x23d68)), detail)))
				memory.write_u32(point + 4, C68kScript.u32(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0x8) - memory.read_u32(0x23d64)), detail)))
				memory.write_u32(point + 8, C68kScript.u32(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0xc) - memory.read_u32(0x23d60)), detail)))
				point += 12
			memory.write_u32(0x22994 + memory.read_s32(0x219bc) * 4, obj)
			memory.write_u32(0x219bc, C68kScript.u32(memory.read_u32(0x219bc) + 1))
		if kept:
			memory.write_u32(keep, obj)
			keep += 4
	memory.write_u32(0x219b8, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(point - 0x21f44), 12)))
	memory.write_u32(count_ptr, C68kScript.u32(C68kScript.asr32(C68kScript.s32(keep - list), 2)))

## Batch 3 of the frame step's closure (hook 10), drafted (tools/leyte/draft_overlay_port.py) and settled against
## Leyte's listing.

## 0x14c6 (1.0.3's lock_overlay_buffers 0xe3a, the same 18 instructions on the host world's moved fields): lock the host
## handles at +0x196, +0x1b0, +0x1b8 and +0xdc (1.0.3 +0x192 / +0x1ac / +0x1b4 / +0xd8) through the host's lock routine
## +0x30e (1.0.3 +0x2d2; 8, 0xc, 0x10, 0x14 below as the arguments pile up).
func lock_overlay_buffers() -> void:
	assert(memory != null, "lock_overlay_buffers requires original global memory")
	var entry_a7: int = a7
	var depth: int = 8
	for off in [0x196, 0x1b0, 0x1b8, 0xdc]:
		_host_memory_call(entry_a7, depth, 0x30e, [memory.read_u32(memory.read_u32(0x207fc) + off)])
		depth += 4

## 0x16c8a (1.0.3's dispatch_collision 0x14328 on Leyte's object layout: the controller at the object's +0 for +8, its
## flag bytes +0x15 / +0x16 for +5 / +6; the pair table (0x205ec, cursor 0x2c838) holds 17 entries for 10; the count
## tested before the loop): call the collision handler(s) at definition (controller +4) +0x12 of objects a and b, unless
## view_mode (0x23d88) == 8 or either flags byte +0x15 has bit 0. One handler: (owner, other, 0). Two: the pair table by
## definition type words in either order, else a's handler when a's byte +0x16 has bit 5, else b's. Returns D0 (the
## handler's, or the caller's d0_in when nothing is called).
func dispatch_collision(obj_a: int, obj_b: int, d0_in: int = 0) -> int:
	assert(memory != null, "dispatch_collision requires original global memory")
	var def_a: int = 0
	var def_b: int = 0
	var handler_a: int = 0
	var handler_b: int = 0
	var left: int
	var cursor: int

	if memory.read_s16(0x23d88) == 8:
		return d0_in
	if (memory.read_u8(obj_a + 0x15) & 1) != 0:
		return d0_in
	if (memory.read_u8(obj_b + 0x15) & 1) != 0:
		return d0_in
	if memory.read_u32(obj_a) != 0:
		def_a = memory.read_u32(memory.read_u32(obj_a) + 4)
		handler_a = memory.read_u32(def_a + 0x12)
	if memory.read_u32(obj_b) != 0:
		def_b = memory.read_u32(memory.read_u32(obj_b) + 4)
		handler_b = memory.read_u32(def_b + 0x12)
	if handler_a == 0 or handler_b == 0:
		if handler_a != 0:
			return _jsr(handler_a, [obj_a, obj_b, 0], d0_in)
		if handler_b == 0:
			return d0_in
		return _jsr(handler_b, [obj_b, obj_a, 0], d0_in)
	memory.write_u32(0x2c838, 0x205ec)
	left = 0x11
	while true:
		left = C68kScript.s16(left - 1)
		if left == -1:
			break
		cursor = memory.read_u32(0x2c838)
		if memory.read_s16(cursor) == memory.read_s16(def_a) and memory.read_s16(cursor + 2) == memory.read_s16(def_b):
			return _jsr(memory.read_u32(cursor + 4), [obj_a, obj_b, 0], d0_in)
		if memory.read_s16(cursor) == memory.read_s16(def_b) and memory.read_s16(cursor + 2) == memory.read_s16(def_a):
			return _jsr(memory.read_u32(cursor + 4), [obj_b, obj_a, 0], d0_in)
		memory.write_u32(0x2c838, C68kScript.u32(cursor + 8))
	if (memory.read_u8(obj_a + 0x16) & 0x20) != 0:
		return _jsr(handler_a, [obj_a, obj_b, 0], d0_in)
	return _jsr(handler_b, [obj_b, obj_a, 0], d0_in)

## 0x66a6 (1.0.3's dispatch_overlap_collisions 0x5812 on Leyte's object layout: metric +0x1c, radius +0x18, running
## minimum +0x2a, the controller at +0; Leyte no longer clears designated_entity_2 first; both counts tested before
## their loops): one-dimensional sweep over a list sorted by metric. Each object whose reach (metric + radius) exceeds
## the smallest start (metric - radius) seen so far scans back over the earlier entries: an earlier object whose start
## lies below the reach is a pair - the definition +0x16 notice handler of each (with a controller and a handler) is
## jsr'd as (self, other), this object's first (0x30 below); an earlier object that does not reach ends the scan once
## its running minimum is above the reach. Every object then stores the running minimum start in +0x2a.
func dispatch_overlap_collisions(list: int, header: int) -> void:
	assert(memory != null, "dispatch_overlap_collisions requires original global memory")
	var entry_a7: int = a7
	var left: int = memory.read_u16(header + 2)
	var min_start: int = 0x7fffffff
	var cursor: int = list
	while true:
		left = C68kScript.u16(left - 1)
		if left == 0xffff:
			break
		var obj: int = memory.read_u32(cursor)
		cursor += 4
		var reach: int = C68kScript.s32(memory.read_u32(obj + 0x1c) + memory.read_u32(obj + 0x18))
		if min_start < reach:
			var back: int = cursor - 4
			var back_left: int = C68kScript.u16(C68kScript.asr32(back - list, 2))
			while true:
				back_left = C68kScript.u16(back_left - 1)
				if back_left == 0xffff:
					break
				back -= 4
				var other: int = memory.read_u32(back)
				var start: int = C68kScript.s32(memory.read_u32(other + 0x1c) - memory.read_u32(other + 0x18))
				if reach <= start:
					if reach < memory.read_s32(other + 0x2a):
						break
					continue
				for pair in [[obj, other], [other, obj]]:
					var ctl: int = memory.read_u32(pair[0])
					if ctl == 0:
						continue
					var notice: int = memory.read_u32(memory.read_u32(ctl + 4) + 0x16)
					if notice == 0:
						continue
					a7 = entry_a7 - 0x30
					_jsr(notice, [pair[0], pair[1]])
					a7 = entry_a7
		var own_start: int = C68kScript.s32(memory.read_u32(obj + 0x1c) - memory.read_u32(obj + 0x18))
		if own_start < min_start:
			min_start = own_start
		memory.write_u32(obj + 0x2a, C68kScript.u32(min_start))

## Leyte's point-in-polygon tests: point_in_polygon_xy 0x1581a, _xz 0x158be, _yz 0x15962 (1.0.3 0x12fb2 / 0x13050 /
## 0x130ee). Leyte orders the comparisons differently and tests the vertex count before the loop, but every path
## computes what 1.0.3's does: an edge straddles when (pb < prev_b and cur_b <= pb) or (prev_b <= pb and pb < cur_b);
## both ends beyond pa count a crossing, one end beyond counts one when prev_a + ((pb - prev_b) muls.w (cur_a - prev_a))
## divs.l (cur_b - prev_b) > 0 (compared with 0, not pa, in both); the result is the crossings' parity. The three share
## one shape (their listings equal with offsets masked), so they are 1.0.3's entry points over the generated port's
## _point_in_polygon.
func point_in_polygon_xy(px: int, py: int, verts: int, count: int, vidx: int) -> int:
	return _point_in_polygon(px, py, verts, count, vidx, 2, 6)

func point_in_polygon_xz(px: int, pz: int, verts: int, count: int, vidx: int) -> int:
	return _point_in_polygon(px, pz, verts, count, vidx, 2)

func point_in_polygon_yz(py: int, pz: int, verts: int, count: int, vidx: int) -> int:
	return _point_in_polygon(py, pz, verts, count, vidx, 6)

## 0x15c2c (1.0.3's surface_type_at_point 0x13326). Surface code at (x, z): walks entity_array_1 from the end (count word
## 0x20f2e), skipping an object whose |metric +0x1c - the point's camera metric| or approximate distance to its position
## (+4 / +8 / +0xc) exceeds its range +0x20, whose flags long +0x14 has any of 0x2042, or whose type byte +0x28 is not 2 (a
## shape). Its faces (count word +0x3c, records +0x4a, from the last) with a surface byte +0x16 are projected xz at shift
## word +0x34 + 1 from the point scaled by (scale word +0x36 - 1 - detail word +0x34), left when that word is not negative
## and right by its magnitude otherwise; the first face whose polygon holds (0, 0) gives its code, else the default face's
## (0x21590), sign-extended. Frame (link.w a6,#-0x708): projection buffer at a6 - 0x708.
func surface_type_at_point(x: int, y_unused: int, z: int) -> int:
	assert(memory != null, "surface_type_at_point requires original global memory")
	assert(a7 != 0, "surface_type_at_point needs a7 (its frame holds the projection buffer)")
	var projected: int = a7 - 4 - 0x708
	var left: int = memory.read_s16(0x20f2e)
	var slot: int = 0x20b44 + left * 4
	var metric: int = compute_camera_abs_metric(x, y_unused, z)
	while true:
		left = C68kScript.s16(left - 1)
		if left == -1:
			return memory.read_s8(memory.read_u32(0x21590) + 0x16)
		slot = slot - 4
		var obj: int = memory.read_u32(slot)
		if abs_int(C68kScript.s32(memory.read_s32(obj + 0x1c) - metric)) > memory.read_s32(obj + 0x20):
			continue
		if (memory.read_u32(obj + 0x14) & 0x2042) != 0:
			continue
		if memory.read_u8(obj + 0x28) != 2:
			continue
		if approx_dist3_xyz(x, y_unused, z, memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc)) > memory.read_s32(obj + 0x20):
			continue
		var faces_left: int = memory.read_s16(obj + 0x3c)
		var face: int = C68kScript.u32(memory.read_u32(obj + 0x4a) + faces_left * 0x20 - 0x20)
		while true:
			faces_left = C68kScript.s16(faces_left - 1)
			if faces_left == -1:
				break
			if memory.read_u8(face + 0x16) != 0:
				var scale: int = C68kScript.s16(memory.read_s16(obj + 0x36) - 1 - memory.read_s16(obj + 0x34))
				var px: int
				var pz: int
				if scale < 0:
					px = C68kScript.asr32(x, -scale)
					pz = C68kScript.asr32(z, -scale)
				else:
					px = C68kScript.lsl32(x, scale)
					pz = C68kScript.lsl32(z, scale)
				project_face_vertices_xz(obj, memory.read_s16(obj + 0x34) + 1, face, px, pz, projected)
				if point_in_polygon_xz(0, 0, projected, memory.read_s16(face + 0x14), memory.read_u32(face + 0x18)) != 0:
					return memory.read_s8(face + 0x16)
			face = C68kScript.u32(face - 0x20)
	return 0 ## not reached: the loop exits through its returns

## 0x30b8, at 1.0.3's invoke_tagged_attitude_compose_neg_sins4 0x2782 (transform_and_project_object's call for the inverse
## attitude). Leyte no longer negates the sines and recomposes: it transposes the 3x3 word matrix at out in place (words
## +2 / +6, +4 / +0xc, +0xa / +0xe swapped), the inverse of a rotation.
func invoke_tagged_attitude_compose_neg_sins4(out: int) -> void:
	assert(memory != null, "invoke_tagged_attitude_compose_neg_sins4 requires original global memory")
	for pair in [[6, 2], [0xc, 4], [0xe, 0xa]]:
		var w: int = memory.read_u16(out + pair[0])
		memory.write_u16(out + pair[0], memory.read_u16(out + pair[1]))
		memory.write_u16(out + pair[1], w)

## Near-mode penetration test of a face (Leyte inlines it twice in submit_object_faces): point_in_polygon_xy(0, 0,
## view_vertices 0x2c0b6, sext word +0x14, +0x18) (0x38 below) -> dispatch_collision(player object, prepared_object
## 0x2c0ba) (0x2c below).
func _face_penetration(entry_a7: int, face: int) -> void:
	assert(memory != null, "_face_penetration requires original global memory")
	a7 = entry_a7 - 0x38
	var hit: int = point_in_polygon_xy(0, 0, memory.read_u32(0x2c0b6), memory.read_s16(face + 0x14), memory.read_u32(face + 0x18))
	if hit != 0:
		a7 = entry_a7 - 0x2c
		dispatch_collision(memory.read_u32(memory.read_u32(0x20b28)), memory.read_u32(0x2c0ba), hit)
	a7 = entry_a7

## 0xb554 (1.0.3's submit_object_faces 0xa2d2, eight saved registers). As 1.0.3 on Leyte's addresses, with the object
## flags long at +0x14 (for +4), and a face whose flags byte +0x13 has bit 1 kept out of near mode's work: neither its
## penetration test nor the inside flag (bit 3) update in the queue pass, nor the back-face pass's test (it still becomes
## inside there). The loops test their counts first.
func submit_object_faces() -> void:
	assert(memory != null, "submit_object_faces requires original global memory")
	var entry_a7: int = a7
	var drawn: bool = false
	var queue: int = 0x2c2c6
	var left: int = C68kScript.asr32(C68kScript.s32(memory.read_u32(0x2c2c2) - 0x2c2c6), 2) & 0xffff
	while true:
		left = (left - 1) & 0xffff
		if left == 0xffff:
			break
		var face: int = memory.read_u32(queue)
		queue += 4
		if (memory.read_u8(face + 0x13) & 0x60) != 0x60:
			memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) | 0x20)
			a7 = entry_a7 - 0x28
			transform_face_vertices(face)
			a7 = entry_a7
		memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) & 0xbf)
		memory.write_u16(0x2c0b0, memory.read_u16(face + 0x14))
		var need_clip: bool = memory.read_u16(0x2c0aa) != 0
		if not need_clip:
			var out: int = 0x2b338
			var idx: int = memory.read_u32(face + 0x18)
			var n: int = memory.read_u16(0x2c0b0)
			while true:
				n = (n - 1) & 0xffff
				if n == 0xffff:
					break
				var entry: int = C68kScript.u32(0x2bbf8 + 8 * memory.read_s16(idx))
				idx += 2
				memory.write_u32(out + 4, memory.read_u32(entry + 4))
				memory.write_u32(out, memory.read_u32(entry))
				if memory.read_u32(entry) == 0x80000000:
					need_clip = true
					break
				out += 8
		if need_clip:
			a7 = entry_a7 - 0x38
			var hit: int = clip_face_near_plane(0x2c0b0, memory.read_u32(0x2c0b6), memory.read_u32(face + 0x18), 0x2bbf8, 0x2b338)
			a7 = entry_a7
			if hit != 0:
				memory.write_u16(0x2c0a8, 1)
		if memory.read_u16(0x2c0ac) != 0 and (memory.read_u8(face + 0x13) & 2) == 0:
			var flipped: int = 1 if (memory.read_u8(face + 0x13) & 0x10) != 0 else 0
			if memory.read_s16(0x2c0ac) != 2:
				var inside: int = 1 if (memory.read_u8(face + 0x13) & 8) != 0 else 0
				if (flipped ^ inside) != 0:
					_face_penetration(entry_a7, face)
			if flipped != 0:
				memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) | 8)
			else:
				memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) & 0xf7)
		if memory.read_u16(0x2c0b0) != 0:
			a7 = entry_a7 - 0x28
			var r = _jsr(memory.read_u32(0x26fe4), [face])
			a7 = entry_a7
			if int(r) != 0:
				drawn = true
	if memory.read_u16(0x2c0ac) != 0:
		var back: int = 0x2c0c2
		var count: int = C68kScript.asr32(C68kScript.s32(memory.read_u32(0x2c0be) - 0x2c0c2), 2) & 0xffff
		while true:
			count = (count - 1) & 0xffff
			if count == 0xffff:
				break
			var face2: int = memory.read_u32(back)
			back += 4
			if memory.read_s16(0x2c0ac) != 2 and (memory.read_u8(face2 + 0x13) & 0xa) == 0:
				if (memory.read_u8(face2 + 0x13) & 0x60) != 0x60:
					a7 = entry_a7 - 0x28
					transform_face_vertices(face2)
					a7 = entry_a7
				_face_penetration(entry_a7, face2)
			memory.write_u8(face2 + 0x13, memory.read_u8(face2 + 0x13) | 8)
	var obj: int = memory.read_u32(0x2c0ba)
	if drawn:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 4)
	else:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xfffffffb)
	if memory.read_u16(0x2c0a8) == 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xfffffff7)

## update_object_distance_band (0x6c below) on the prepared object's metric +0x1c (1.0.3's +0x10) when haze, and, when it
## changed the band (or always), shade_object_faces (0x68 below).
func _band_and_shade(entry_a7: int, always_shade: bool, haze: int) -> void:
	assert(memory != null, "_band_and_shade requires original global memory")
	var obj: int = memory.read_u32(0x2c0ba)
	var changed: int = 0
	if haze != 0:
		a7 = entry_a7 - 0x6c
		changed = update_object_distance_band(obj, memory.read_s32(obj + 0x1c))
	if always_shade or changed != 0:
		a7 = entry_a7 - 0x68
		shade_object_faces(memory.read_u32(0x2c0ba))
	a7 = entry_a7

## 0xb9f8 (1.0.3's transform_and_project_object 0xa73e; link.w a6,#-0x34, ten saved registers: body 0x60 below the entry),
## on Leyte's object layout: the controller at +0 (for +8), the position +4 / +8 / +0xc (for +0x18 / +8 / +0x20 - 1.0.3's
## eye used its own position fields), flag bytes +0x15 / +0x16 (for +5 / +6), the band metric +0x1c, the face count +0x3c;
## the shift word +0x34 and scale +0x36 as before. The inverse attitude is Leyte's transpose (0x30b8). The loops test their
## counts first. Returns 4 when the controller is unlinked or its pre-render hook says so, else 0.
func transform_and_project_object() -> int:
	assert(memory != null, "transform_and_project_object requires original global memory")
	assert(a7 != 0, "transform_and_project_object needs a7 (its frame holds the eye vectors)")
	var entry_a7: int = a7
	var rel: int = entry_a7 - 4 - 0x24
	memory.write_u32(0x2b29e, 0x23c74)
	var world: int = memory.read_u32(0x207fc)
	var haze: int = memory.read_s16(world + 0xaa)
	var lit: int = memory.read_s16(world + 0xb2)
	var obj: int = memory.read_u32(0x2c0ba)
	memory.write_u16(0x2b296, memory.read_u16(obj + 0x34))
	var shift: int = C68kScript.s16(memory.read_u16(obj + 0x36) - memory.read_u16(0x2b296))
	memory.write_u16(0x2b29c, C68kScript.u16(_shift_signed(memory.read_s32(0x23d68), shift)))
	memory.write_u16(0x2b29a, C68kScript.u16(_shift_signed(memory.read_s32(0x23d64), shift)))
	memory.write_u16(0x2b298, C68kScript.u16(_shift_signed(memory.read_s32(0x23d60), shift)))
	var ctl: int = memory.read_u32(memory.read_u32(0x2c0ba))
	if ctl == 0:
		if (memory.read_u8(memory.read_u32(0x2c0ba) + 0x16) & 2) == 0:
			_band_and_shade(entry_a7, false, haze)
	else:
		if memory.read_u32(ctl) == 0:
			return 4
		var hook: int = memory.read_u32(memory.read_u32(ctl + 4) + 0xe)
		var res: int = 0
		if hook != 0:
			a7 = entry_a7 - 0x6c
			res = int(_jsr(hook, [ctl, 1]))
			a7 = entry_a7
			if (res & 4) != 0:
				return 4
		obj = memory.read_u32(0x2c0ba)
		if (memory.read_u8(obj + 0x15) & 0x10) == 0:
			_band_and_shade(entry_a7, false, haze)
		else:
			if (res & 1) == 0:
				a7 = entry_a7 - 0x74
				build_attitude_matrix_3ang(memory.read_s16(ctl + 0x20), memory.read_s16(ctl + 0x22), memory.read_s16(ctl + 0x24), 0x23c5c)
			a7 = entry_a7 - 0x70
			matmul3x3_s16(0x23c5c, 0x23c74, 0x2b274)
			memory.write_u32(0x2b29e, 0x2b274)
			a7 = entry_a7 - 0x74
			invoke_tagged_attitude_compose_neg_sins4(0x23c5c)
			a7 = entry_a7
			obj = memory.read_u32(0x2c0ba)
			memory.write_u16(rel + 2, C68kScript.u16(_shift_signed(C68kScript.s32(memory.read_u32(0x23d68) - memory.read_u32(obj + 4)), shift)))
			memory.write_u16(rel + 6, C68kScript.u16(_shift_signed(C68kScript.s32(memory.read_u32(0x23d64) - memory.read_u32(obj + 8)), shift)))
			memory.write_u16(rel + 0xa, C68kScript.u16(_shift_signed(C68kScript.s32(memory.read_u32(0x23d60) - memory.read_u32(obj + 0xc)), shift)))
			if (memory.read_u8(memory.read_u32(0x2c0ba) + 0x16) & 1) != 0 and lit != 0:
				var lx: int = memory.read_u16(0x21574)
				var ly: int = memory.read_u16(0x21572)
				var lz: int = memory.read_u16(0x21570)
				memory.write_u16(rel + 0xe, lx)
				memory.write_u16(rel + 0x12, ly)
				memory.write_u16(rel + 0x16, lz)
				a7 = entry_a7 - 0x74
				transform_vecs12_by_matrix3(2, rel, rel, 0x23c5c)
				a7 = entry_a7
				memory.write_u16(0x21574, memory.read_u16(rel + 0xe))
				memory.write_u16(0x21572, memory.read_u16(rel + 0x12))
				memory.write_u16(0x21570, memory.read_u16(rel + 0x16))
				_band_and_shade(entry_a7, true, haze)
				memory.write_u16(0x21574, lx)
				memory.write_u16(0x21572, ly)
				memory.write_u16(0x21570, lz)
			else:
				a7 = entry_a7 - 0x74
				transform_vecs12_by_matrix3(1, rel, rel, 0x23c5c)
				a7 = entry_a7
				_band_and_shade(entry_a7, false, haze)
			memory.write_u16(0x2b29c, memory.read_u16(rel + 2))
			memory.write_u16(0x2b29a, memory.read_u16(rel + 6))
			memory.write_u16(0x2b298, memory.read_u16(rel + 0xa))
			if (res & 2) != 0:
				a7 = entry_a7 - 0x6c
				_jsr(memory.read_u32(memory.read_u32(ctl + 4) + 0xe), [ctl, 2])
				a7 = entry_a7
	obj = memory.read_u32(0x2c0ba)
	memory.write_u32(0x2c0b6, memory.read_u32(obj + 0x4e))
	memory.write_u32(0x2c4c6, memory.read_u32(obj + 0x4a))
	memory.write_u16(0x2c0ae, memory.read_u16(obj + 0x3a))
	memory.write_u32(0x2b286, C68kScript.u32(memory.read_s16(obj + 0x38) * 12 + memory.read_u32(obj + 0x46)))
	var verts: int = memory.read_u32(obj + 0x46)
	for k in 3:
		memory.write_u32(0x2b28a + 4 * k, memory.read_u32(verts + 4 * k))
	var count: int = memory.read_s16(memory.read_u32(0x2c0ba) + 0x38)
	var q: int = count
	if q < 0:
		q += 3
	q = C68kScript.s16(q) >> 2
	for k in q + 1:
		memory.write_u32(0x2b2a2 + 4 * k, 0)
	var face: int = memory.read_u32(0x2c4c6)
	var faces_left: int = memory.read_s16(memory.read_u32(0x2c0ba) + 0x3c)
	while true:
		faces_left -= 1
		if faces_left == -1:
			break
		var flags: int = memory.read_u8(face + 0x13)
		if (flags & 0x60) == 0x20:
			memory.write_u8(face + 0x13, flags | 0x40)
			var idx: int = memory.read_u32(face + 0x18)
			for k in maxi(memory.read_s16(face + 0x14), 0):
				memory.write_u8(C68kScript.u32(0x2b2a2 + memory.read_s16(idx)), 1)
				idx += 2
		else:
			memory.write_u8(face + 0x13, flags & 0xdf)
		face += 0x20
	var m: int = memory.read_u32(0x2b29e)
	var eye_x: int = memory.read_u16(0x2b29c)
	var eye_y: int = memory.read_u16(0x2b29a)
	var eye_z: int = memory.read_u16(0x2b298)
	var vshift: int = memory.read_s16(0x2b296)
	var view: int = memory.read_u32(0x2c0b6)
	var out: int = 0x2bbf8
	var left: int = count & 0xffff
	var k2: int = 0
	while true:
		left = (left - 1) & 0xffff
		if left == 0xffff:
			break
		if memory.read_u8(0x2b2a2 + k2) != 0:
			var v: int = C68kScript.u32(verts + 12 * k2)
			var d0: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v), vshift) - eye_x)
			var d1: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v + 4), vshift) - eye_y)
			var d2: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v + 8), vshift) - eye_z)
			var w: int = C68kScript.u32(view + 12 * k2)
			for c in 3:
				var acc: int = C68kScript.s32(d0 * memory.read_s16(m + 2 * c) + d1 * memory.read_s16(m + 6 + 2 * c) + d2 * memory.read_s16(m + 12 + 2 * c) + 0x800)
				memory.write_u16(w + 2 + 4 * c, C68kScript.u16(C68kScript.asr32(acc, 12)))
			var z: int = memory.read_s16(w + 0xa)
			if z > 4:
				memory.write_u32(out, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(w + 2) * memory.read_s16(0x27050)), z)))
				memory.write_u32(out + 4, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(C68kScript.s16(-memory.read_s16(w + 6)) * memory.read_s16(0x2704e)), z)))
			else:
				memory.write_u32(out, 0x80000000)
		out += 8
		k2 += 1
	return 0

## 0x16d7a (1.0.3's sort_group_parts_by_distance 0x1441a; link.w a6,#-0x424, ten saved registers: body 0x450 below the
## entry), 1.0.3's code on Leyte's layout: the scale word +0x36 (for +0x34), the part count +0x3a (kept in the frame at
## a6 - 0x420; for +0x38), the vertex count +0x38, vertices +0x46, the face table +0x4a with +0x3c faces, the group list
## +0x2e (for +0x2c), draw_order_next 0x219a8, deferred_free_flag 0x219a6; the loops test their counts first (the
## same iterations). Draw order for a group object's parts: the camera, scaled by the scale word (asl) with y = 0, is
## restored at the end; each part's horizontal camera distance goes into the frame array (a6 - 0x400); with host word
## +0xaa each part's colour record gets its band and, when it changed, the part face's colour bytes and palette slot;
## the parts are insertion-sorted far to near; group entries (freed when word 0 and deferred_free_flag) are sorted by
## their metric +0xc and merged with the parts, each numbered before the first part nearer than it (count << 7
## numbers), the parts one number each.
func sort_group_parts_by_distance(obj: int) -> void:
	assert(memory != null, "sort_group_parts_by_distance requires original global memory")
	assert(a7 != 0, "sort_group_parts_by_distance needs a7 (its frame holds the distances)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var dist: int = a6 - 0x400
	var cam_x: int = memory.read_u32(0x23d68)
	var cam_y: int = memory.read_u32(0x23d64)
	var cam_z: int = memory.read_u32(0x23d60)
	var scale: int = memory.read_s16(obj + 0x36)
	memory.write_u32(0x23d68, C68kScript.u32(C68kScript.lsl32(cam_x, scale & 0x3f)))
	memory.write_u32(0x23d64, 0)
	memory.write_u32(0x23d60, C68kScript.u32(C68kScript.lsl32(memory.read_u32(0x23d60), scale & 0x3f)))
	var parts: int = memory.read_s16(obj + 0x3a)
	var centres: int = C68kScript.u32(C68kScript.s32(C68kScript.s32(memory.read_s16(obj + 0x38) - parts) * 12) + memory.read_u32(obj + 0x46))
	var faces: int = memory.read_u32(obj + 0x4a)
	var list: int = C68kScript.u32(faces + C68kScript.s32(memory.read_s16(obj + 0x3c) * 32))
	var colours: int = C68kScript.u32(list + 2 * parts)
	var lit: bool = memory.read_u16(memory.read_u32(0x207fc) + 0xaa) != 0
	for k in range(maxi(parts, 0)):
		var part: int = memory.read_s16(list + 2 * k)
		var c: int = C68kScript.u32(centres + 12 * part)
		a7 = entry_a7 - 0x460
		var d: int = compute_camera_abs_metric(memory.read_s32(c), 0, memory.read_s32(c + 8))
		a7 = entry_a7
		memory.write_u32(dist + 4 * k, C68kScript.u32(d))
		if lit:
			var rec: int = C68kScript.u32(colours + 6 * part)
			a7 = entry_a7 - 0x468
			var changed: int = update_record_distance_band(rec, d)
			a7 = entry_a7
			if changed != 0:
				var face: int = C68kScript.u32(faces + 32 * part)
				var band: int = memory.read_s16(rec + 4)
				var keep: int = C68kScript.s32(0x20 - band)
				var tints: Array = [0x7d, 0x84, 0xa8]
				for ch in 3:
					var v: int = C68kScript.u32(C68kScript.s32(memory.read_u8(rec + ch) * keep) + C68kScript.s32(band * tints[ch]))
					memory.write_u8(face + 0xc + ch, (v >> 5) & 0xff)
				var slot: int = memory.read_u8(face + 0xf)
				if memory.read_u32(C68kScript.u32(memory.read_u32(0x23fac) + slot * 4)) == face:
					a7 = entry_a7 - 0x458
					write_face_palette_slot(face)
					a7 = entry_a7
				else:
					memory.write_u8(face + 0xf, 0)
	# Insertion sort of the part list, far to near.
	for k in range(maxi(parts, 0)):
		var idx: int = memory.read_u16(list + 2 * k)
		var dk: int = memory.read_s32(dist + 4 * k)
		var j: int = k - 1
		while j >= 0 and dk > memory.read_s32(dist + 4 * j):
			memory.write_u16(list + 2 * (j + 1), memory.read_u16(list + 2 * j))
			memory.write_u32(dist + 4 * (j + 1), memory.read_u32(dist + 4 * j))
			j -= 1
		memory.write_u16(list + 2 * (j + 1), idx)
		memory.write_u32(dist + 4 * (j + 1), C68kScript.u32(dk))
	var groups: int = memory.read_u32(obj + 0x2e)
	var count: int = 0
	var entries: int = C68kScript.u32(groups + 4)
	if groups != 0:
		count = memory.read_u16(groups)
		if memory.read_u16(0x219a6) != 0:
			var kept: int = entries
			var left: int = count
			var src: int = entries
			while true:
				left = (left - 1) & 0xffff
				if left == 0xffff:
					break
				var e: int = memory.read_u32(src)
				src += 4
				if memory.read_u16(e) != 0:
					memory.write_u32(kept, e)
					kept += 4
				else:
					a7 = entry_a7 - 0x458
					heap_free_coalesce(e)
					a7 = entry_a7
			count = C68kScript.asr32(C68kScript.s32(kept - entries), 2) & 0xffff
			memory.write_u16(groups, count)
	if groups == 0 or C68kScript.s16(count) == 0:
		for k in range(maxi(parts, 0)):
			_number_face(C68kScript.u32(faces + 32 * memory.read_s16(list + 2 * k)))
	else:
		var n: int = C68kScript.s16(count)
		for k in range(maxi(n, 0)):
			var e2: int = memory.read_u32(entries + 4 * k)
			a7 = entry_a7 - 0x460
			var m: int = compute_camera_abs_metric(memory.read_s32(e2 + 4), 0, memory.read_s32(e2 + 8))
			a7 = entry_a7
			memory.write_u32(e2 + 0xc, C68kScript.u32(m))
			var j2: int = k - 1
			while j2 >= 0:
				var other: int = memory.read_u32(entries + 4 * j2)
				if m <= memory.read_s32(other + 0xc):
					break
				memory.write_u32(entries + 4 * (j2 + 1), other)
				j2 -= 1
			memory.write_u32(entries + 4 * (j2 + 1), e2)
		# Merge: groups farther than a part are numbered before it.
		var gi: int = 0
		var group: int = memory.read_u32(entries)
		gi = 1
		var gdist: int = memory.read_s32(group + 0xc)
		var groups_left: int = (n - 1) & 0xffff
		var k3: int = 0
		var finished: bool = false
		while k3 < (parts & 0xffff) and not finished:
			var face2: int = C68kScript.u32(faces + 32 * memory.read_s16(list + 2 * k3))
			var pd: int = memory.read_s32(dist + 4 * k3)
			k3 += 1
			while gdist > pd:
				_number_group(group)
				groups_left = (groups_left - 1) & 0xffff
				if groups_left == 0xffff:
					_number_face(face2)
					while k3 < (parts & 0xffff):
						_number_face(C68kScript.u32(faces + 32 * memory.read_s16(list + 2 * k3)))
						k3 += 1
					finished = true
					break
				group = memory.read_u32(entries + 4 * gi)
				gi += 1
				gdist = memory.read_s32(group + 0xc)
			if not finished:
				_number_face(face2)
		if not finished:
			while true:
				_number_group(group)
				groups_left = (groups_left - 1) & 0xffff
				if groups_left == 0xffff:
					break
				group = memory.read_u32(entries + 4 * gi)
				gi += 1
	memory.write_u32(0x23d68, cam_x)
	memory.write_u32(0x23d64, cam_y)
	memory.write_u32(0x23d60, cam_z)

## A prepared object's face pass (Leyte inlines it at each call site of render_visible_objects): force_near_clip = word
## +0x16 & 8 (byte +0x17 bit 3), face_was_clipped cleared, both face queues reset, the faces emitted (unnumbered, or
## emit_faces_bsp(0) when byte +0x17 bit 5, else in order), then submit_object_faces. depth: the caller's stack depth
## below entry_a7 at the bsr.
func _render_object_faces(entry_a7: int, depth: int, unnumbered: bool) -> void:
	assert(memory != null, "_render_object_faces requires original global memory")
	var obj: int = memory.read_u32(0x2c0ba)
	memory.write_u16(0x2c0aa, memory.read_u16(obj + 0x16) & 8)
	memory.write_u16(0x2c0a8, 0)
	memory.write_u32(0x2c2c2, 0x2c2c6)
	memory.write_u32(0x2c0be, 0x2c0c2)
	if unnumbered:
		a7 = entry_a7 - depth
		emit_faces_unnumbered()
	elif (memory.read_u8(obj + 0x17) & 0x20) != 0:
		a7 = entry_a7 - depth - 4
		emit_faces_bsp(0)
	else:
		a7 = entry_a7 - depth
		emit_faces_in_order()
	a7 = entry_a7 - depth
	submit_object_faces()
	a7 = entry_a7

## Near mode for the prepared object (inlined twice in render_visible_objects): allowed and its metric +0x1c within its
## range +0x20 -> 1, and on first entry (byte +0x15 bit 5 clear) its +0x3c faces lose +0x13 bit 3, the bit is set and near
## mode is 2; otherwise near mode 0 and the bit (flags long +0x14 bit 21) cleared.
func _render_near_mode(allowed: bool) -> void:
	assert(memory != null, "_render_near_mode requires original global memory")
	var obj: int = memory.read_u32(0x2c0ba)
	if allowed:
		var near: int = 1 if memory.read_s32(obj + 0x1c) <= memory.read_s32(obj + 0x20) else 0
		memory.write_u16(0x2c0ac, near)
		if near != 0:
			if (memory.read_u8(obj + 0x15) & 0x20) == 0:
				var faces: int = memory.read_u32(0x2c4c6)
				for k in maxi(memory.read_s16(obj + 0x3c), 0):
					memory.write_u8(faces + 32 * k + 0x13, memory.read_u8(faces + 32 * k + 0x13) & 0xf7)
				memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 0x20)
				memory.write_u16(0x2c0ac, 2)
			return
	else:
		memory.write_u16(0x2c0ac, 0)
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffdfffff)

## 0xbf1e (1.0.3's render_visible_objects 0xac52; link.w a6,#-0x370, five saved registers: body 0x388 below the entry),
## on Leyte's layout: the group-sort flag byte +0x16 bit 1, the parent +0x2e, the last object's flags long +0x14; near
## mode and the face pass as above. The loops test their (long) counts first. Near mode is allowed in view 1 unless
## view_flag 0x23d86. Edge pool count 0x26fe0, draw order (1) and near mode reset. Pass 1: the visible_list_split
## (0x215a0) pre-transformed objects of 0x22994 are projected and their faces run. visible_first_pass (0x219aa) = the
## visible count's low word - it. Pass 2: the others: one with byte +0x16 bit 1 has its parts sorted (its faces then run
## unnumbered); one with a parent is deferred into the frame list (a6 - 0x370); the rest transform_and_project_object
## (skipped on 4), near mode decided only while the remaining count is below visible_first_pass. Pass 3: deferred objects
## draw with their parent's draw order (parent word +2, written back). The draw order is restored; in view 1 the object
## at 0x2158c (+0x14 |= 4) is projected and drawn last.
func render_visible_objects() -> void:
	assert(memory != null, "render_visible_objects requires original global memory")
	assert(a7 != 0, "render_visible_objects needs a7 (its frame holds the deferred list)")
	var entry_a7: int = a7
	var deferred: int = entry_a7 - 4 - 0x370
	var deferred_end: int = deferred
	var body: int = 0x388
	var allowed: bool = memory.read_u16(0x23d86) == 0 and memory.read_s16(0x23d88) == 1
	memory.write_u32(0x26fe0, 0)
	memory.write_u16(0x219a8, 1)
	memory.write_u16(0x2c0ac, 0)
	var sort_parts: bool = false
	var slot: int = 0x22994
	var left: int = C68kScript.s32(memory.read_u32(0x215a0))
	while true:
		left = C68kScript.s32(left - 1)
		if left == -1:
			break
		memory.write_u32(0x2c0ba, memory.read_u32(slot))
		slot += 4
		a7 = entry_a7 - body - 4
		project_prepared_object()
		a7 = entry_a7
		_render_object_faces(entry_a7, body + 4, false)
	memory.write_u16(0x219aa, C68kScript.u16(memory.read_u16(0x219be) - memory.read_u16(0x219aa)))
	slot = C68kScript.u32(0x22994 + 4 * memory.read_u32(0x215a0))
	var d2: int = C68kScript.s32(memory.read_u32(0x219bc) - memory.read_u32(0x215a0))
	while true:
		d2 = C68kScript.s32(d2 - 1)
		if d2 == -1:
			break
		var obj: int = memory.read_u32(slot)
		slot += 4
		memory.write_u32(0x2c0ba, obj)
		if (memory.read_u8(obj + 0x16) & 2) != 0:
			sort_parts = true
		elif memory.read_u32(obj + 0x2e) != 0:
			memory.write_u32(deferred_end, obj)
			deferred_end += 4
			continue
		a7 = entry_a7 - body - 4
		var r: int = transform_and_project_object()
		a7 = entry_a7
		if r == 4:
			continue
		if memory.read_s16(0x219aa) > d2:
			_render_near_mode(allowed)
		else:
			memory.write_u16(0x2c0ac, 0)
		if sort_parts:
			a7 = entry_a7 - body - 8
			sort_group_parts_by_distance(memory.read_u32(0x2c0ba))
			a7 = entry_a7
			sort_parts = false
			_render_object_faces(entry_a7, body + 8, true)
		else:
			_render_object_faces(entry_a7, body + 4, false)
	var order: int = memory.read_u16(0x219a8)
	var p: int = deferred
	while p < deferred_end:
		var obj2: int = memory.read_u32(p)
		p += 4
		memory.write_u32(0x2c0ba, obj2)
		var parent: int = memory.read_u32(obj2 + 0x2e)
		memory.write_u16(0x219a8, memory.read_u16(parent + 2))
		a7 = entry_a7 - body - 4
		var r2: int = transform_and_project_object()
		a7 = entry_a7
		if r2 != 4:
			_render_near_mode(allowed)
			_render_object_faces(entry_a7, body + 4, false)
		memory.write_u16(parent + 2, memory.read_u16(0x219a8))
	memory.write_u16(0x219a8, order)
	if memory.read_s16(0x23d88) == 1:
		var last: int = memory.read_u32(0x2158c)
		memory.write_u32(last + 0x14, memory.read_u32(last + 0x14) | 4)
		memory.write_u32(0x2c0ba, last)
		a7 = entry_a7 - body - 4
		project_prepared_object()
		a7 = entry_a7
		_render_object_faces(entry_a7, body + 4, false)

## One of the four broad-phase tests collect_far_objects calls (bsr in Leyte: 0x16812 test_object_collision, 0x16be2
## handle_player_contact, 0x16784 test_proximity_collision, 0x167e4 test_player_vs_type0e_proximity), entered 0xc below
## the caller's body. Reached through code_table at their Leyte addresses, as 1.0.3's port does, so a case can stand them in.
func _far_collision(proximity: bool, player: bool, a: int, b: int) -> void:
	_jsr((0x167e4 if player else 0x16784) if proximity else (0x16be2 if player else 0x16812), [a, b])

## 0x63ba (1.0.3's collect_far_objects 0x553a; link a6 -0x37c and ten registers, body 0x3a8 below the entry), 1.0.3's
## code on Leyte's layout: flags long +0x14 (for +4; the active object's word +0x16 loses 0x800, other objects' bytes
## +0x15 bit 4 and +0x17 bits 1 / 6), metric +0x1c (for +0x10), range +0x18, radius +0x20 (for +0x14), running minimum
## +0x2a (for +0x28), position +4 / +8 / +0xc, scale word +0x36 and detail word +0x34; the view points 0x21f44 (count
## 0x219b8), visible list 0x22994 (0x219bc), overlap list 0x219cc (0x219ac), entity_array_3 0x233bc (0x219c4), frame
## counter 0x21020. The loops test their counts first. Sorts the far list in place, as collect_near_objects does, while
## running the broad-phase collisions (_far_collision); out-of-range objects are kept within 0x19c7f (unhidden) - those
## seen in the last 0x4b frames skip overlap and collisions - else retired / deactivated into entity_array_3.
func collect_far_objects(list: int, count_ptr: int) -> void:
	assert(memory != null, "collect_far_objects requires original global memory")
	assert(a7 != 0, "collect_far_objects needs a7 (its frame holds the active list)")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x3a8
	var point: int = C68kScript.u32(0x21f44 + memory.read_s32(0x219b8) * 12)
	var keep: int = list
	var read: int = list
	var left: int = memory.read_u16(count_ptr + 2)
	var min_start: int = 0x7fffffff
	var active: int = entry_a7 - 4 - 0x370
	var active_count: int = 0
	while left != 0:
		left -= 1
		var obj: int = memory.read_u32(read)
		read += 4
		var flags: int = memory.read_u32(obj + 0x14)
		var normal: bool = true
		if memory.read_s32(obj + 0x1c) > memory.read_s32(obj + 0x18) or (flags & 2) != 0:
			if C68kScript.s32(memory.read_u32(obj + 0x1c) - memory.read_u32(obj + 0x18)) > 0x19c7f or (flags & 2) != 0:
				if C68kScript.s32(memory.read_u32(0x21020) - memory.read_u32(obj + 0x3e)) <= 0x4b:
					normal = false
				else:
					a7 = body - 8
					if memory.read_u32(obj + 0x3e) != 0:
						retire_entity(obj)
					deactivate_object(obj)
					a7 = entry_a7
					memory.write_u32(0x233bc + memory.read_s32(0x219c4) * 4, obj)
					memory.write_u32(0x219c4, C68kScript.u32(memory.read_u32(0x219c4) + 1))
					continue
		elif (flags & 0x40) == 0:
			var size: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(C68kScript.asr32(memory.read_u32(obj + 0x1c), 2) + memory.read_u32(obj + 0x1c) + memory.read_u32(obj + 0x20)), memory.read_s16(obj + 0x36)))
			var detail: int = 0
			while (C68kScript.u32(size) & 0xffff8000) != 0:
				detail = C68kScript.s16(detail + 1)
				size = C68kScript.asr32(size, 1)
			memory.write_u16(obj + 0x34, C68kScript.u16(detail))
			if (flags & 4) == 0:
				memory.write_u32(point, C68kScript.u32(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0x4) - memory.read_u32(0x23d68)), detail)))
				memory.write_u32(point + 4, C68kScript.u32(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0x8) - memory.read_u32(0x23d64)), detail)))
				memory.write_u32(point + 8, C68kScript.u32(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0xc) - memory.read_u32(0x23d60)), detail)))
				point += 12
			memory.write_u32(0x22994 + memory.read_s32(0x219bc) * 4, obj)
			memory.write_u32(0x219bc, C68kScript.u32(memory.read_u32(0x219bc) + 1))
		if normal:
			if (flags & 0x8000) != 0:
				memory.write_u32(0x219cc + memory.read_s32(0x219ac) * 4, obj)
				memory.write_u32(0x219ac, C68kScript.u32(memory.read_u32(0x219ac) + 1))
			var reach: int = C68kScript.s32(memory.read_u32(obj + 0x1c) + memory.read_u32(obj + 0x20))
			if (flags & 0x2800) != 0:
				memory.write_u16(obj + 0x16, memory.read_u16(obj + 0x16) & 0xf7ff)
				if min_start < reach:
					var scan: int = keep
					var n: int = C68kScript.asr32(keep - list, 2) & 0xffff
					while n != 0:
						n -= 1
						scan -= 4
						var other: int = memory.read_u32(scan)
						if (memory.read_u8(other + 0x17) & 2) != 0:
							continue
						if reach > C68kScript.s32(memory.read_u32(other + 0x1c) - memory.read_u32(other + 0x20)):
							a7 = body - 0xc
							_far_collision((memory.read_u8(other + 0x15) & 0x10) != 0, (flags & 0x40) != 0, obj, other)
							a7 = entry_a7
						elif reach < memory.read_s32(other + 0x2a):
							break
				memory.write_u32(active + active_count * 4, obj)
				active_count += 1
			elif min_start < reach:
				var n: int = active_count & 0xffff
				var scan: int = active + active_count * 4
				while n != 0:
					n -= 1
					scan -= 4
					var other: int = memory.read_u32(scan)
					if reach > C68kScript.s32(memory.read_u32(other + 0x1c) - memory.read_u32(other + 0x20)):
						a7 = body - 0xc
						_far_collision((flags & 0x100000) != 0, (memory.read_u8(other + 0x17) & 0x40) != 0, other, obj)
						a7 = entry_a7
					elif reach < memory.read_s32(other + 0x2a):
						break
			var start: int = C68kScript.s32(memory.read_u32(obj + 0x1c) - memory.read_u32(obj + 0x20))
			if min_start > start:
				min_start = start
			memory.write_u32(obj + 0x2a, C68kScript.u32(min_start))
		memory.write_u32(keep, obj)
		keep += 4
	memory.write_u32(0x219b8, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(point - 0x21f44), 12)))
	memory.write_u32(count_ptr, C68kScript.u32(C68kScript.asr32(C68kScript.s32(keep - list), 2)))

## 0x525c (Leyte only; update_altitude_detail_bands calls it after a reference move): for each controller of
## controller_list (0x21bd4, long count 0x219b4, word index) whose definition (+4) type word is 8 and whose target
## (+0x32) is set, the first later entry equal to the target's first long (*(+0x32)) is swapped with it, so that entry
## comes first.
func leyte_order_tracked_controllers() -> void:
	assert(memory != null, "leyte_order_tracked_controllers requires original global memory")
	var i: int = 0
	while i < memory.read_s32(0x219b4):
		var slot: int = 0x21bd4 + 4 * i
		var ctl: int = memory.read_u32(slot)
		if memory.read_u16(memory.read_u32(ctl + 4)) == 8 and memory.read_u32(ctl + 0x32) != 0:
			var j: int = C68kScript.s16(i + 1)
			while j < memory.read_s32(0x219b4):
				var other: int = memory.read_u32(0x21bd4 + 4 * j)
				if other == memory.read_u32(memory.read_u32(ctl + 0x32)):
					memory.write_u32(slot, other)
					memory.write_u32(0x21bd4 + 4 * j, ctl)
					break
				j = C68kScript.s16(j + 1)
		i = C68kScript.s16(i + 1)

## 0x52b6 (Leyte only; link.w a6,#-0x190, called by update_altitude_detail_bands at its end after a reference move):
## when entity_array_1 (0x20b44, long count 0x20f2c) is not empty, its shapes (type byte +0x28 == 2) whose controller
## (+0) definition type word is 0xf move to the end of the list, both parts in their order (through a frame buffer at
## a6 - 0x190); the loops run the count's low word.
func leyte_move_type0f_shapes_last() -> void:
	assert(memory != null, "leyte_move_type0f_shapes_last requires original global memory")
	if memory.read_s32(0x20f2c) <= 0:
		return
	var moved: Array = []
	var write: int = 0x20b44
	var read: int = 0x20b44
	var left: int = memory.read_u16(0x20f2e)
	while left != 0:
		left -= 1
		var obj: int = memory.read_u32(read)
		read += 4
		var ctl: int = memory.read_u32(obj)
		if memory.read_u8(obj + 0x28) == 2 and ctl != 0 and memory.read_u16(memory.read_u32(ctl + 4)) == 0xf:
			moved.append(obj)
		else:
			memory.write_u32(write, obj)
			write += 4
	if a7 != 0:
		var buffer: int = a7 - 4 - 0x190
		for k in moved.size():
			memory.write_u32(buffer + 4 * k, moved[k])
	for obj in moved:
		memory.write_u32(write, obj)
		write += 4

## A hidden (+0x17 bit 1), unreferenced (+0x29 == 0) far object is freed (inlined twice in update_altitude_detail_bands):
## its controller (+0) loses the object link, runs its definition destroy hook (+0xa) and is freed (0x30 below), then the
## object. Returns whether a controller was freed.
func _free_far_object(slot: int, entry_a7: int) -> bool:
	assert(memory != null, "_free_far_object requires original global memory")
	var freed_ctl: bool = false
	var ctl: int = memory.read_u32(memory.read_u32(slot))
	a7 = entry_a7 - 0x30
	if ctl != 0:
		memory.write_u32(ctl, 0)
		var hook: int = memory.read_u32(memory.read_u32(ctl + 4) + 0xa)
		if hook != 0:
			_jsr(hook, [ctl])
		heap_free_coalesce(ctl)
		freed_ctl = true
	heap_free_coalesce(memory.read_u32(slot))
	a7 = entry_a7
	return freed_ctl

## 0x6882 (1.0.3's update_altitude_detail_bands 0x59f4, ten saved registers, no frame), 1.0.3's code on Leyte's
## addresses and layout (land outline flag byte +0x16 bit 4, hidden byte +0x17 bit 1, reference byte +0x29, range +0x18,
## the controller at +0), the loops testing their counts first, and two Leyte routines after a reference move:
## leyte_order_tracked_controllers before the lists are re-sorted, leyte_move_type0f_shapes_last at the end. The altitude
## band (0x23c3c, log word 0x23c34) follows the camera height; its value (table 0x2043c, interpolated) is the y of the
## horizon objects' vertices (0x21594 vertices 0..3, 0x21598 +0x64 / +0x70). The near reference (x / y / z 0x23c54 /
## 0x23c50 / 0x23c4c) moves to the camera beyond a metric of 0xce40, the far one (0x23c48 / 0x23c44 / 0x23c40) beyond
## 0x80e80 (both move). A band change across 0xff or a move rebuilds the land outlines at the band's tolerance (table
## 0x2048c); after a move the very far list (0x22d2c, 0x219c0; on a far move) and the far list (0x233bc, 0x219c4) are
## re-sorted, freeing hidden unreferenced objects, and freed controllers are compacted out of controller_list.
func update_altitude_detail_bands() -> void:
	assert(memory != null, "update_altitude_detail_bands requires original global memory")
	var entry_a7: int = a7
	var near_moved: bool = false
	var far_moved: bool = false
	var rebuild: bool = false
	var cam_y: int = memory.read_s32(0x23d64)
	var band: int = memory.read_s32(0x23c3c)
	if cam_y < band or not (C68kScript.s32(C68kScript.lsl32(band, 1)) > cam_y):
		var old_band: int = band
		var bits: int = 0
		var y: int = cam_y
		while y != 0:
			y = C68kScript.asr32(y, 1)
			bits += 1
		if bits != 0:
			memory.write_u32(0x23c3c, C68kScript.u32(C68kScript.lsl32(1, bits - 1)))
			memory.write_u16(0x23c34, C68kScript.u16(bits - 1))
		else:
			memory.write_u32(0x23c3c, 0)
			memory.write_u16(0x23c34, 0)
		if memory.read_s32(0x23c3c) > 0xff or old_band > 0xff:
			rebuild = true
	var log2: int = memory.read_s16(0x23c34)
	var value: int = memory.read_s32(C68kScript.u32(0x2043c + log2 * 4))
	if memory.read_u32(0x23c3c) != 0:
		value = C68kScript.s32(value + C68kScript.asr32(C68kScript.s32(C68kScript.s32(memory.read_u32(0x23d64) - memory.read_u32(0x23c3c)) * value), log2))
	for k in 4:
		memory.write_u32(memory.read_u32(memory.read_u32(0x21594) + 0x46) + 4 + 12 * k, C68kScript.u32(value))
	memory.write_u32(memory.read_u32(memory.read_u32(0x21598) + 0x46) + 0x64, C68kScript.u32(value))
	memory.write_u32(memory.read_u32(memory.read_u32(0x21598) + 0x46) + 0x70, C68kScript.u32(value))
	a7 = entry_a7 - 0x38
	var m: int = compute_camera_abs_metric(memory.read_s32(0x23c54), memory.read_s32(0x23c50), memory.read_s32(0x23c4c))
	a7 = entry_a7
	if C68kScript.s32(m + C68kScript.asr32(m, 3)) > 0xce40:
		near_moved = true
		rebuild = true
		memory.write_u32(0x23c54, memory.read_u32(0x23d68))
		memory.write_u32(0x23c50, memory.read_u32(0x23d64))
		memory.write_u32(0x23c4c, memory.read_u32(0x23d60))
	a7 = entry_a7 - 0x38
	m = compute_camera_abs_metric(memory.read_s32(0x23c48), memory.read_s32(0x23c44), memory.read_s32(0x23c40))
	a7 = entry_a7
	if C68kScript.s32(m + C68kScript.asr32(m, 3)) > 0x80e80:
		far_moved = true
		near_moved = true
		rebuild = true
		for k in 3:
			var c: int = memory.read_u32([0x23d68, 0x23d64, 0x23d60][k])
			memory.write_u32(0x23c54 - 4 * k, c)
			memory.write_u32(0x23c48 - 4 * k, c)
	if rebuild:
		var v: int = memory.read_s32(C68kScript.u32(0x2048c + memory.read_s16(0x23c34) * 4))
		var halvings: int = 0
		var w: int = v
		while w > 0x7fff:
			halvings += 1
			w = C68kScript.asr32(w, 1)
		var base: int = C68kScript.asr32(v, halvings)
		var limit: int = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(base * 9), 10))
		var tol: int = C68kScript.s32(base * 0x16a1)
		if tol < 0:
			tol = C68kScript.s32(tol + 0xfff)
		tol = C68kScript.s32(C68kScript.lsl32(C68kScript.asr32(tol, 12), halvings))
		var slot: int = 0x20b44
		var left: int = C68kScript.s32(memory.read_u32(0x20f2c))
		while true:
			left = C68kScript.s32(left - 1)
			if left == -1:
				break
			var obj: int = memory.read_u32(slot)
			slot += 4
			if (memory.read_u8(obj + 0x16) & 0x10) == 0:
				continue
			a7 = entry_a7 - 0x30
			reset_object_to_template_pose(obj)
			a7 = entry_a7 - 0x40
			build_land_outline_at_detail(obj, tol, C68kScript.s16(halvings), limit)
			a7 = entry_a7
	if not near_moved:
		return
	a7 = entry_a7 - 0x2c
	leyte_order_tracked_controllers()
	a7 = entry_a7
	var freed: bool = false
	if far_moved:
		var write: int = 0x22d2c
		var read: int = 0x22d2c
		var count3: int = memory.read_s32(0x219c4)
		var i: int = 0
		while i < memory.read_s32(0x219c0):
			var obj: int = memory.read_u32(read)
			if (memory.read_u8(obj + 0x17) & 2) != 0:
				if memory.read_u8(obj + 0x29) != 0:
					memory.write_u32(write, obj)
					write += 4
				elif _free_far_object(read, entry_a7):
					freed = true
				read += 4
			else:
				a7 = entry_a7 - 0x30
				var d: int = C68kScript.s32(update_object_camera_metric(obj) - memory.read_u32(memory.read_u32(read) + 0x18))
				a7 = entry_a7
				obj = memory.read_u32(read)
				if d > 0x80e7f:
					memory.write_u32(write, obj)
					write += 4
				else:
					memory.write_u32(C68kScript.u32(0x233bc + count3 * 4), obj)
					count3 += 1
				read += 4
			i += 1
		memory.write_u32(0x219c0, C68kScript.u32(C68kScript.asr32(write - 0x22d2c, 2)))
		memory.write_u32(0x219c4, C68kScript.u32(count3))
	var write3: int = 0x233bc
	var read3: int = 0x233bc
	var j: int = 0
	while j < memory.read_s32(0x219c4):
		var obj: int = memory.read_u32(read3)
		if (memory.read_u8(obj + 0x17) & 2) != 0:
			if memory.read_u8(obj + 0x29) != 0:
				memory.write_u32(write3, obj)
				write3 += 4
			elif _free_far_object(read3, entry_a7):
				freed = true
			read3 += 4
		else:
			a7 = entry_a7 - 0x30
			var d: int = C68kScript.s32(update_object_camera_metric(obj) - memory.read_u32(memory.read_u32(read3) + 0x18))
			a7 = entry_a7
			obj = memory.read_u32(read3)
			if d > 0x80e7f:
				memory.write_u32(C68kScript.u32(0x22d2c + memory.read_s32(0x219c0) * 4), obj)
				memory.write_u32(0x219c0, C68kScript.u32(memory.read_u32(0x219c0) + 1))
			elif d > 0xce3f:
				memory.write_u32(write3, obj)
				write3 += 4
			else:
				a7 = entry_a7 - 0x30
				activate_near_range_object(obj)
				a7 = entry_a7
			read3 += 4
		j += 1
	memory.write_u32(0x219c4, C68kScript.u32(C68kScript.asr32(write3 - 0x233bc, 2)))
	if freed:
		var dst: int = 0x21bd4
		var src: int = 0x21bd4
		var n: int = C68kScript.s32(memory.read_u32(0x219b4))
		while true:
			n = C68kScript.s32(n - 1)
			if n == -1:
				break
			var ctl: int = memory.read_u32(src)
			src += 4
			if memory.read_u32(ctl) != 0:
				memory.write_u32(dst, ctl)
				dst += 4
		memory.write_u32(0x219b4, C68kScript.u32(C68kScript.asr32(dst - 0x21bd4, 2)))
	a7 = entry_a7 - 0x2c
	leyte_move_type0f_shapes_last()
	a7 = entry_a7

## 0x6c9a (1.0.3's update_ground_scatter_objects 0x5e08; link.w a6,#-0x38c, nine saved registers: body 0x3b4 below the
## entry), written from Leyte's listing on 1.0.3's structure. Leyte's layout: object flags long +0x14 (for +4; byte +0x15
## bit 4, word +0x16), class word +0x10 and scene index word +0x12 (1.0.3's bytes +1 / +2), metric +0x1c, vertex / face
## counts +0x38 / +0x3c, vertices +0x46, fractal reference +0x2e; the template descriptors from the mission root's +0x2a
## list + 0x1a. Off unless host word +0xac (scatter_started 0x2b228 cleared); h = camera y, minus the player's (0x20b28)
## +0x2e in views <= 5; above 0x26ac the slots are cleared once (clear_pending_entity_slots). First use (or view_flag
## 0x23d86) resets the 64 points (0x2110c) to x = z = 1000000000 and the 7 slots' (0x2144c) +0x14 bit 2. A point beyond
## 0x4d58 of the camera on x or z is re-placed (views <= 5 without the flag: on the 0x4d58 circle at the player heading
## +0x22 +-0x600; otherwise in the square), a slot it named loses +0x14 bit 2, its state byte (0x210cc) becomes 0x3f. The
## points are taken to view space (frame a6 - 0x300) and projected; newly on-screen ones get bit 7 and join the visible
## list (a6 - 0x380, words). Each slot k: a live object (+0x14 & 6 == 0) whose point (0x2107c) no longer reads 0xc0 | k
## detaches and is hidden (+0x14 |= 0x40) when nothing is newly visible, else unhidden and moved; an empty slot takes a
## point when one is. Points are popped until one's surface (cached at 0x2108c with state bit 6 - zero-extended - else
## surface_type_at_point(x, 0, z), its word) has a template (0x2140c, surface asr.w 4); heading 0x540 on surface 0x10,
## else ((r & 0xfff) << 6) >> 4. A same-class object with vertices is re-placed in place; another is cleaned up and
## respawned; an empty slot spawns spawn_mission_pool_object(template, x, y, z, 0, heading, 0, 0).
func update_ground_scatter_objects() -> void:
	assert(memory != null, "update_ground_scatter_objects requires original global memory")
	assert(a7 != 0, "update_ground_scatter_objects needs a7 (its frame holds the view points)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x3b4
	var view_buf: int = a6 - 0x300
	var visible: int = a6 - 0x380
	if memory.read_u16(memory.read_u32(0x207fc) + 0xac) == 0:
		memory.write_u32(0x2b228, 0)
		return
	var h: int = memory.read_s32(0x23d64)
	if memory.read_s16(0x23d88) <= 5:
		h = C68kScript.s32(h - memory.read_u32(memory.read_u32(0x20b28) + 0x2e))
	if h > 0x26ac:
		if memory.read_u32(0x2b228) == 0:
			return
		a7 = body - 4
		clear_pending_entity_slots()
		a7 = entry_a7
		memory.write_u32(0x2b228, 0)
		return
	if memory.read_u32(0x2b228) == 0:
		memory.write_u16(0x23d86, 1)
		memory.write_u32(0x2b228, 1)
	if memory.read_u16(0x23d86) != 0:
		for i in 0x40:
			memory.write_u32(0x2110c + 12 * i, 1000000000)
			memory.write_u32(0x2110c + 12 * i + 8, 1000000000)
		for k in 7:
			var slot_obj: int = memory.read_u32(0x2144c + 4 * k)
			if slot_obj != 0:
				memory.write_u32(slot_obj + 0x14, memory.read_u32(slot_obj + 0x14) & 0xfffffffb)
	var tmpl_base: int = C68kScript.u32(memory.read_u32(memory.read_u32(0x23c2c) + 0x2a) + 0x1a)
	for i in 0x40:
		var pt: int = 0x2110c + 12 * i
		a7 = body - 8
		var far: bool = abs_int(C68kScript.s32(memory.read_u32(pt) - memory.read_u32(0x23d68))) > 0x4d58
		if not far:
			far = abs_int(C68kScript.s32(memory.read_u32(pt + 8) - memory.read_u32(0x23d60))) > 0x4d58
		a7 = entry_a7
		if not far:
			continue
		if memory.read_s16(0x23d88) <= 5 and memory.read_u16(0x23d86) == 0:
			a7 = body - 4
			var r: int = overlay_rng_next()
			var bearing: int = (C68kScript.asr32((r & 0xfff) * 0xc00, 12) + memory.read_u16(memory.read_u32(0x20b28) + 0x22) + 0xfa00) & 0x3fff
			a7 = body - 8
			var sin_v: int = sin_fixed14(bearing)
			a7 = body - 0xc
			var cos_v: int = cos_fixed14(bearing)
			a7 = entry_a7
			memory.write_u32(pt, C68kScript.u32(C68kScript.asr32(C68kScript.s16(sin_v) * -0x4d58, 12) + memory.read_s32(0x23d68)))
			memory.write_u32(pt + 4, 0)
			memory.write_u32(pt + 8, C68kScript.u32(C68kScript.asr32(C68kScript.s16(cos_v) * 0x4d58, 12) + memory.read_s32(0x23d60)))
		else:
			a7 = body - 4
			var rx: int = overlay_rng_next()
			a7 = entry_a7
			memory.write_u32(pt, C68kScript.u32(C68kScript.asr32((rx & 0xfff) * 0x9ab0, 12) + memory.read_s32(0x23d68) - 0x4d58))
			memory.write_u32(pt + 4, 0)
			a7 = body - 4
			var rz: int = overlay_rng_next()
			a7 = entry_a7
			memory.write_u32(pt + 8, C68kScript.u32(C68kScript.asr32((rz & 0xfff) * 0x9ab0, 12) + memory.read_s32(0x23d60) - 0x4d58))
		var state: int = memory.read_u8(0x210cc + i)
		if (state & 0xc0) == 0xc0 and (state & 0x3f) != 0x3f:
			var named: int = memory.read_u32(0x2144c + 4 * (state & 0x3f))
			if named != 0:
				memory.write_u32(named + 0x14, memory.read_u32(named + 0x14) & 0xfffffffb)
		memory.write_u8(0x210cc + i, 0x3f)
	a7 = body - 0x1c
	translate_int3_vecs(0x40, 0x2110c, view_buf, C68kScript.s32(-memory.read_s32(0x23d68)), C68kScript.s32(-h), C68kScript.s32(-memory.read_s32(0x23d60)))
	a7 = body - 0x2c
	transform_vecs12_by_matrix3(0x40, view_buf, view_buf, 0x23c74)
	a7 = entry_a7
	var n_visible: int = 0
	for i in 0x40:
		var v: int = view_buf + 12 * i
		var vz: int = memory.read_s16(v + 0xa)
		var on_screen: bool = false
		if vz > 4:
			var sx: int = C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(v + 2) * memory.read_s16(0x27050)), vz)
			var sy: int = C68kScript.divs_trunc(C68kScript.s32(-memory.read_s16(v + 6) * memory.read_s16(0x2704e)), vz)
			on_screen = not (memory.read_s16(0x2701a) > sx or memory.read_s16(0x27018) < sx
					or memory.read_s16(0x27016) > sy or memory.read_s16(0x20b2e) < sy)
		if not on_screen:
			memory.write_u8(0x210cc + i, memory.read_u8(0x210cc + i) & 0x7f)
		elif memory.read_u8(0x210cc + i) < 0x80:
			memory.write_u8(0x210cc + i, memory.read_u8(0x210cc + i) | 0x80)
			memory.write_u16(visible + 2 * n_visible, i)
			n_visible += 1
	for k in 7:
		var obj: int = memory.read_u32(0x2144c + 4 * k)
		if obj != 0:
			if (memory.read_u32(obj + 0x14) & 6) != 0:
				continue
			var own: int = memory.read_s16(0x2107c + 2 * k)
			var own_state: int = memory.read_u8(C68kScript.u32(0x210cc + own))
			if own_state == (k | 0xc0):
				continue
			if (own_state & 0x3f) == k:
				memory.write_u8(C68kScript.u32(0x210cc + own), own_state | 0x3f)
			if n_visible == 0:
				memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x40)
				continue
			memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbf)
		var point: int = 0
		var surface: int = 0
		var template: int = 0
		while n_visible != 0:
			n_visible -= 1
			point = memory.read_s16(visible + 2 * n_visible)
			if (memory.read_u8(0x210cc + point) & 0x40) != 0:
				surface = memory.read_u8(0x2108c + point)
			else:
				var pt0: int = 0x2110c + 12 * point
				a7 = body - 0x10
				surface = C68kScript.s16(surface_type_at_point(memory.read_s32(pt0), 0, memory.read_s32(pt0 + 8)))
				a7 = entry_a7
				memory.write_u8(0x2108c + point, surface & 0xff)
				memory.write_u8(0x210cc + point, memory.read_u8(0x210cc + point) | 0x40)
			template = memory.read_u32(C68kScript.u32(0x2140c + (surface >> 4) * 4))
			if template != 0:
				break
		if template == 0:
			continue
		var pt: int = 0x2110c + 12 * point
		memory.write_u8(0x210cc + point, (memory.read_u8(0x210cc + point) & 0xc0) | k)
		memory.write_u16(0x2107c + 2 * k, point)
		var heading: int
		if surface == 0x10:
			heading = 0x540
		else:
			a7 = body - 4
			heading = ((overlay_rng_next() & 0xfff) << 6) >> 4
			a7 = entry_a7
		if obj == 0:
			a7 = body - 0x24
			memory.write_u32(0x2144c + 4 * k, C68kScript.u32(spawn_mission_pool_object(template, memory.read_s32(pt), memory.read_s32(pt + 4), memory.read_s32(pt + 8), 0, C68kScript.s16(heading), 0, 0)))
			a7 = entry_a7
		elif memory.read_u16(template + 0x10) == memory.read_u16(obj + 0x10) and memory.read_u32(obj + 0x46) != 0:
			if memory.read_u32(obj + 0x2e) != 0:
				a7 = body - 8
				release_fractal_reference(obj)
				a7 = entry_a7
			memory.write_u16(obj + 0x16, memory.read_u16(obj + 0x16) | 0x800)
			var rec: int = C68kScript.u32(memory.read_u32(memory.read_u32(0x23c2c) + 0x32) + memory.read_u16(obj + 0x12) * 0x18)
			memory.write_u32(rec + 8, memory.read_u32(pt))
			memory.write_u32(rec + 0xc, 0)
			memory.write_u32(rec + 0x10, memory.read_u32(pt + 8))
			memory.write_u16(rec + 4, C68kScript.u16(heading))
			var desc: int = C68kScript.u32(memory.read_u32(C68kScript.u32(tmpl_base + 0x14 + memory.read_u16(obj + 0x10) * 0x18)) + memory.read_u32(memory.read_u32(0x207fc) + 0x8c))
			memory.write_u32(obj + 4, C68kScript.u32(memory.read_u32(pt) + memory.read_u32(desc + 2)))
			memory.write_u32(obj + 8, memory.read_u32(desc + 6))
			memory.write_u32(obj + 0xc, C68kScript.u32(memory.read_u32(pt + 8) + memory.read_u32(desc + 0xa)))
			var n: int = C68kScript.s32(memory.read_s16(obj + 0x38) + memory.read_s16(obj + 0x3c))
			if (memory.read_u8(obj + 0x15) & 0x10) != 0:
				a7 = body - 0x1c
				translate_int3_vecs(n, C68kScript.u32(desc + 0x22), memory.read_u32(obj + 0x46), 0, 0, 0)
			else:
				a7 = body - 0x14
				apply_template_chain_xform(obj, n, C68kScript.u32(desc + 0x22), memory.read_u32(obj + 0x46))
			a7 = body - 8
			update_object_camera_metric(obj)
			a7 = body - 0x10
			update_object_distance_band(obj, memory.read_s32(obj + 0x1c))
			a7 = body - 0x14
			shade_object_faces(obj)
			a7 = entry_a7
		else:
			a7 = body - 8
			dispatch_entity_cleanup(obj)
			a7 = body - 0x28
			memory.write_u32(0x2144c + 4 * k, C68kScript.u32(spawn_mission_pool_object(template, memory.read_s32(pt), memory.read_s32(pt + 4), memory.read_s32(pt + 8), 0, C68kScript.s16(heading), 0, 0)))
			a7 = entry_a7

## Camera-matrix transform of a word vector (x, y, z) into a 12-byte view record's words +2 / +6 / +0xa ((sum of muls.w +
## 0x800) asr 12 through the camera matrix 0x23c74; inlined in draw_distant_object_dots).
func _camera_transform_words(x: int, y: int, z: int, out: int) -> void:
	for c in 3:
		var acc: int = C68kScript.s32(x * memory.read_s16(0x23c74 + 2 * c) + y * memory.read_s16(0x23c74 + 6 + 2 * c)
				+ z * memory.read_s16(0x23c74 + 12 + 2 * c) + 0x800)
		memory.write_u16(out + 2 + 4 * c, C68kScript.u16(C68kScript.asr32(acc, 12)))

## 0x1a296 (1.0.3's draw_distant_object_dots 0x1629a; link.w a6,#-0x30, ten saved registers: body 0x5c below the
## entry), 1.0.3's code on Leyte's layout and addresses: the controller at +0 (for +8), faces +0x4a, shift word +0x34 and
## scale word +0x36 (for +0x32 / +0x34), position +4 / +8 / +0xc (for +0x18 / +0x1c / +0x20), vertices +0x46; the queue
## 0x2cb86 (count word 0x2cb84), the dot records 0x2c83c (count word 0x23fbc), the colour tally 0x23fba and colour word
## 0x23fb8, the erase / plot routines (0x23fb4 / 0x23fb0), the screen bounds 0x2701a / 0x27018 / 0x27016 / 0x27014. A
## trail (controller byte +0x3b bit 1) transforms its first two vertices: Leyte tests the count first, where 1.0.3 ran a
## third record. Each record in front and on screen gets a dot (size 4 / 3 / 2 / 1 by distance against 5000 shifted).
func draw_distant_object_dots() -> void:
	assert(memory != null, "draw_distant_object_dots requires original global memory")
	assert(a7 != 0, "draw_distant_object_dots needs a7 (its frame holds the view records)")
	var entry_a7: int = a7
	var records: int = entry_a7 - 4 - 0x18
	if memory.read_u16(memory.read_u32(0x207fc) + 0x9e) != 0:
		memory.write_u16(0x2cb84, 0)
		memory.write_u16(0x23fbc, 0)
	a7 = entry_a7 - 0x60
	_jsr(memory.read_u32(0x23fb4), [])
	a7 = entry_a7
	var queue: int = 0x2cb86
	var out: int = 0x2c83c
	var left: int = memory.read_u16(0x2cb84)
	while true:
		left = (left - 1) & 0xffff
		if left == 0xffff:
			break
		var obj: int = memory.read_u32(queue)
		queue += 4
		var ctl: int = memory.read_u32(obj)
		var face: int = memory.read_u32(obj + 0x4a)
		var shift: int = memory.read_s16(obj + 0x34)
		var tally_used: bool = false
		if memory.read_u8(face + 0xf) != 0xff:
			if memory.read_u16(0x23fba) != 0:
				memory.write_u8(face + 0xf, memory.read_u8(0x23fbb))
				tally_used = true
			else:
				a7 = entry_a7 - 0x64
				var failed: int = assign_face_colour(face)
				a7 = entry_a7
				if failed != 0:
					return
				memory.write_u16(0x23fba, memory.read_u8(face + 0xf))
		if not tally_used:
			memory.write_u16(0x23fb8, memory.read_u16(face + 6))
		var near: int
		var count: int
		if (memory.read_u8(ctl + 0x3b) & 2) != 0:
			var d5: int = C68kScript.s16(memory.read_u16(obj + 0x36) - shift)
			var rel: Array = []
			for k in 3:
				rel.append(_shift_signed(C68kScript.s32(memory.read_u32([0x23d68, 0x23d64, 0x23d60][k]) - memory.read_u32(obj + 4 + 4 * k)), d5))
			near = _shift_signed(5000, d5)
			var v: int = memory.read_u32(obj + 0x46)
			for k in 2:
				var x: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v), shift) - rel[0])
				var y: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v + 4), shift) - rel[1])
				var z: int = C68kScript.s16(C68kScript.asr32(memory.read_s32(v + 8), shift) - rel[2])
				_camera_transform_words(x, y, z, records + 12 * k)
				v += 12
			count = 2
		else:
			var px: int = C68kScript.s16(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 4) - memory.read_u32(0x23d68)), shift))
			var py: int = C68kScript.s16(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 8) - memory.read_u32(0x23d64)), shift))
			var pz: int = C68kScript.s16(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0xc) - memory.read_u32(0x23d60)), shift))
			near = C68kScript.asr32(5000, shift)
			_camera_transform_words(px, py, pz, records)
			count = 1
		for k in count:
			var rec: int = records + 12 * k
			var z2: int = memory.read_s16(rec + 0xa)
			if z2 <= 4:
				continue
			var sx: int = C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(rec + 2) * memory.read_s16(0x27050)), z2)
			var sy: int = C68kScript.divs_trunc(C68kScript.s32(-memory.read_s16(rec + 6) * memory.read_s16(0x2704e)), z2)
			if memory.read_s16(0x2701a) > sx or C68kScript.s32(memory.read_s16(0x27018) - 1) < sx:
				continue
			if memory.read_s16(0x27016) > sy or C68kScript.s32(memory.read_s16(0x27014) - 1) < sy:
				continue
			var size: int
			if z2 <= near:
				size = 4
			else:
				var half: int = near
				if half < 0:
					half += 1
				var mid: int = C68kScript.s32((half >> 1) + near)
				if mid >= z2:
					size = 3
				elif C68kScript.s32(C68kScript.lsl32(near, 1)) >= z2:
					size = 2
				else:
					size = 1
			memory.write_u16(out + 6, size)
			memory.write_u8(out + 8, memory.read_u8(0x23fb9))
			a7 = entry_a7 - 0x70
			_jsr(memory.read_u32(0x23fb0), [out, sx, sy, size])
			a7 = entry_a7
			out += 0xe
	memory.write_u16(0x2cb84, 0)
	memory.write_u16(0x23fbc, C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(out - 0x2c83c), 0xe)))

## overlay_frame_step's queueing (inlined): obj at visible_list (0x22994) [visible_count (0x219bc)++], its flags long +0x14
## bit 2 set.
func _queue_visible(obj: int) -> void:
	var count: int = memory.read_u32(0x219bc)
	memory.write_u32(C68kScript.u32(0x22994 + count * 4), obj)
	memory.write_u32(0x219bc, C68kScript.u32(count + 1))
	memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 4)

## overlay_frame_step's far-object sort (inlined; calls entered 8 below the body): the far objects (0x20804, long count
## 0x20b24) are insertion-sorted by descending metric +0x1c (stable). Without carried pairs (word 0x21046) each key is the
## fresh update_object_camera_metric; with them all metrics update first, each rider's +0x1c is saved in +0x2a and biased
## by its carrier's +0x20: with the carrier's level (its controller's word +0x696) below the camera height, added when the
## level is above the rider's y + 100, else subtracted; at or above it, added unless the level is above the rider's y +
## 100. The sort runs on the biased keys, the saved metrics come back and the pair count is cleared. (1.0.3: metric +0x10,
## carrier +0x14, level controller +0x54, rider y without the 100.)
func _sort_far_objects(body: int) -> void:
	var entry: int = a7
	var count: int = memory.read_s32(0x20b24)
	var pairs: int = memory.read_s16(0x21046)
	a7 = body - 8
	if pairs != 0:
		for i in range(maxi(count, 0)):
			update_object_camera_metric(memory.read_u32(0x20804 + 4 * i))
		var pair: int = memory.read_u32(0x21048)
		for k in range(maxi(pairs, 0)):
			var carrier: int = memory.read_u32(pair + 8 * k)
			var rider: int = memory.read_u32(pair + 8 * k + 4)
			memory.write_u32(rider + 0x2a, memory.read_u32(rider + 0x1c))
			var level: int = memory.read_s16(memory.read_u32(carrier) + 0x696)
			var above: bool = level > C68kScript.s32(memory.read_u32(rider + 8) + 100)
			var delta: int = 0
			if level < memory.read_s32(0x23d64):
				delta = 1 if above else -1
			elif not above:
				delta = 1
			if delta != 0:
				memory.write_u32(rider + 0x1c, C68kScript.u32(memory.read_u32(rider + 0x1c) + delta * memory.read_s32(carrier + 0x20)))
	for i in range(maxi(count, 0)):
		var obj: int = memory.read_u32(0x20804 + 4 * i)
		var key: int
		if pairs != 0:
			key = memory.read_s32(obj + 0x1c)
		else:
			key = C68kScript.s32(update_object_camera_metric(obj))
		var j: int = i - 1
		while j >= 0 and key > memory.read_s32(memory.read_u32(0x20804 + 4 * j) + 0x1c):
			memory.write_u32(0x20804 + 4 * (j + 1), memory.read_u32(0x20804 + 4 * j))
			j -= 1
		memory.write_u32(0x20804 + 4 * (j + 1), obj)
	if pairs != 0:
		var pair2: int = memory.read_u32(0x21048)
		for k in range(maxi(pairs, 0)):
			var rider2: int = memory.read_u32(pair2 + 8 * k + 4)
			memory.write_u32(rider2 + 0x1c, memory.read_u32(rider2 + 0x2a))
		memory.write_u16(0x21046, 0)
	a7 = entry

## overlay_frame_step: a host routine (host_world + offset), entered 4 + 4 * args below the body.
func _frame_host(body: int, offset: int, args: Array) -> void:
	var saved: int = a7
	a7 = body - 4 - 4 * args.size()
	_jsr(memory.read_u32(memory.read_u32(0x207fc) + offset), args)
	a7 = saved

## overlay_frame_step's panel layout (inlined, view 1 with the panels not drawn: 0x27010 clear):
## reset_overlay_display_state; the panel rows from the y scale 0x2704e (0x20b2e = * 0x78 / 0x1e0, 0x27014 = * 0x43 /
## 0x1e0, 0x27012 = 0x20b2e + half of 0x21010). step_mode 0 re-creates the session buffers around host +0x2a2 (unlock,
## then allocate / lock both, the panels-drawn flag kept from the host's call: when the host clears it the flag stays
## set below). For new frame 1 the host's four handle pointers (+0x196 / +0x1b0 / +0x1b8 / +0xdc) are read into +0x19a /
## +0x1b4 / +0x1bc / +0xe0 (masked to 24 bits with host word +0x76). Then draw_sky_and_ground and update_heading_tape
## (both with the hooks flag), host +0x2a6, update_radar_range_buttons; the cursor hook (+0x2fe) around each step when
## the hooks are on. Leyte's +0x2fe / +0x2a2 / +0x2a6 are 1.0.3's +0x2c2 / +0x26a / +0x26e.
func _lay_out_view_panels(body: int, step_mode: int, new_frame: int, hooks: bool) -> void:
	var saved: int = a7
	a7 = body - 4
	reset_overlay_display_state()
	a7 = saved
	var scale_y: int = memory.read_s16(0x2704e)
	memory.write_u16(0x20b2e, C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(scale_y * 0x78), 0x1e0)))
	memory.write_u16(0x27014, C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(scale_y * 0x43), 0x1e0)))
	var half: int = memory.read_s16(0x21010)
	if half < 0:
		half += 1
	memory.write_u16(0x27012, C68kScript.u16((half >> 1) + memory.read_u16(0x20b2e)))
	var kept: bool = false
	if C68kScript.u32(step_mode) == 0:
		if hooks:
			_frame_host(body, 0x2fe, [])
		a7 = body - 4
		unlock_session_buffers()
		a7 = saved
		memory.write_u16(0x27010, 1)
		_frame_host(body, 0x2a2, [])
		if memory.read_u16(0x27010) == 0:
			kept = true
		memory.write_u16(0x27010, 0)
		a7 = body - 4
		allocate_session_buffers()
		lock_session_buffers()
		lock_overlay_buffers()
		a7 = saved
		if hooks:
			_frame_host(body, 0x2fe, [])
	if C68kScript.u32(new_frame) == 1:
		var host: int = memory.read_u32(0x207fc)
		var mask: int = 0xffffff if memory.read_u16(host + 0x76) != 0 else 0xffffffff
		for pair in [[0x196, 0x19a], [0x1b0, 0x1b4], [0x1b8, 0x1bc], [0xdc, 0xe0]]:
			memory.write_u32(host + pair[1], memory.read_u32(memory.read_u32(host + pair[0])) & mask)
	a7 = body - 8
	draw_sky_and_ground(1 if hooks else 0)
	a7 = body - 0xc
	update_heading_tape(1 if hooks else 0)
	a7 = saved
	if hooks:
		_frame_host(body, 0x2fe, [])
	_frame_host(body, 0x2a6, [])
	a7 = body - 4
	update_radar_range_buttons()
	a7 = saved
	if hooks:
		_frame_host(body, 0x2fe, [])
	if not kept:
		memory.write_u16(0x27010, 1)

## 0x741e (hook 10; 1.0.3's overlay_frame_step 0x6578; link.w a6,#-0x38, ten saved registers: body 0x64 below the entry),
## written from Leyte's listing. One overlay frame for window new_frame (1 the main view):
## - new frame 1 swaps the slot usage maps (0x23d94 / 0x23d90; the new current one, 64 longs, cleared) and counts the
##   colour tally slot (word 0x23fba); 20 retirements (0x2b22c) reset the distance bases; the altitude bands update;
## - Leyte's side views (new_frame != 1): the screen bounds, view mode, palette face (0x21590, cleared) and bottom rows are
##   saved and set for view mode 2 (0x27014 = 0x20b2e = 0x20b30, 0x27012 = 0x21010), the heading (0x23d30) turned by
##   -0xd43 for window 2 (+0xd43 otherwise), all restored at the end;
## - the window's colour owners (0x23f9c[n] -> 0x23fac), span buffers (0x20f6c / 0x20f5c[n] -> 0x20f7c / 0x20f80), and
##   host record (host + 0x1a + 0x16 n: +4 -> 0x23d8c, word +8 -> 0x23d8a); the fill colour follows the depth;
## - the inverse camera matrix (view mode 2: six angles and the vector (0, 0, 0x1000) in the frame; otherwise the folded
##   fourth angle times the third's sine), the horizon objects queued (0x21598 also re-banded and shaded with haze, else
##   0x2159c) and transformed, then the forward matrix;
## - the camera metrics of entity_array_1, the ground scatter, the visible split, the far sort (_sort_far_objects), the
##   near and far lists (overlap collisions only every 16th frame: 0x21020 & 0xf, flag 0x219a4), the contacts, the
##   visible list, the rendering, the presentation (depth 1: the host or present_bw_frame; otherwise present_spans_to_window
##   or the span filler 0x26ff0, the span buffers swapped);
## - in view 1 with the panels not drawn, _lay_out_view_panels; then the readouts, gauges, radar buttons, bomb and state
##   lamps; the cursor hook (+0x2fe) around them when host word +0x76 is set and +0x9e clear;
## - the window's span buffers stored back.
func overlay_frame_step(step_mode: int, new_frame: int) -> void:
	assert(memory != null, "overlay_frame_step requires original global memory")
	assert(a7 != 0, "overlay_frame_step needs a7 (its frame holds the matrix, vector and span cursors)")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x64
	var a6: int = entry_a7 - 4
	var host: int = memory.read_u32(0x207fc)
	var hooks: bool = memory.read_u16(host + 0x76) != 0 and memory.read_u16(host + 0x9e) == 0
	var frame: int = C68kScript.s32(new_frame)
	if C68kScript.u32(new_frame) == 1:
		var old_cur: int = memory.read_u32(0x23d94)
		memory.write_u32(0x23d94, memory.read_u32(0x23d90))
		memory.write_u32(0x23d90, old_cur)
		var clear: int = memory.read_u32(0x23d94)
		for i in range(0x40):
			memory.write_u32(C68kScript.u32(clear + 4 * i), 0)
		var slot: int = C68kScript.u32(memory.read_u32(0x23d94) + memory.read_s16(0x23fba))
		memory.write_u8(slot, (memory.read_u8(slot) + 1) & 0xff)
	a7 = body - 4
	if memory.read_s32(0x2b22c) > 0x13:
		reset_distance_base_globals()
		memory.write_u32(0x2b22c, 0)
	update_altitude_detail_bands()
	a7 = entry_a7
	var saved: Array = []
	if C68kScript.u32(new_frame) != 1:
		saved = [memory.read_u16(0x20b2e), memory.read_u16(0x2701a), memory.read_u16(0x27018), memory.read_u16(0x27016),
				memory.read_u16(0x27014), memory.read_u16(0x27012), memory.read_u16(0x23d88), memory.read_u32(0x21590)]
		memory.write_u32(0x21590, 0)
		var bottom: int = memory.read_u16(0x20b30)
		memory.write_u16(0x27014, bottom)
		memory.write_u16(0x20b2e, bottom)
		memory.write_u16(0x27012, memory.read_u16(0x21010))
		memory.write_u16(0x23d88, 2)
		var turn: int = -0xd43 if C68kScript.u32(new_frame) == 2 else 0xd43
		memory.write_u32(0x23d30, C68kScript.u32(memory.read_u32(0x23d30) + turn) & 0x3fff)
	memory.write_u32(0x23fac, memory.read_u32(C68kScript.u32(0x23f9c + frame * 4)))
	memory.write_u32(0x20f7c, memory.read_u32(C68kScript.u32(0x20f6c + frame * 4)))
	memory.write_u32(0x20f80, memory.read_u32(C68kScript.u32(0x20f5c + frame * 4)))
	var record: int = C68kScript.u32(frame * 0x16 + memory.read_u32(0x207fc) + 0x1a)
	memory.write_u32(0x23d8c, memory.read_u32(record + 4))
	memory.write_u16(0x23d8a, memory.read_u16(record + 8))
	var depth: int = memory.read_s16(0x2100e)
	if depth == 8:
		memory.write_u16(0x23fd0, memory.read_u16(memory.read_u32(0x23d8c) + 0x1a0))
	elif depth == 4:
		memory.write_u16(0x23fd0, memory.read_u16(memory.read_u32(0x23d8c) + 0x10))
	if memory.read_s16(0x23d88) == 2:
		var matrix: int = a6 - 0x1e
		var vec: int = a6 - 0xc
		a7 = body - 0x20
		build_attitude_matrix_6ang(-memory.read_s16(0x23d40) & 0x3fff, 0, -memory.read_s16(0x23d3c) & 0x3fff,
				-memory.read_s32(0x23d38) & 0x3fff, -memory.read_s32(0x23d34) & 0x3fff, -memory.read_s32(0x23d30) & 0x3fff, matrix)
		memory.write_u16(vec + 6, 0)
		memory.write_u16(a6 - 0xa, 0)
		memory.write_u16(a6 - 2, 0x1000)
		a7 = body - 0x30
		transform_vecs12_by_matrix3(1, vec, vec, matrix)
		memory.write_u16(0x23d28, memory.read_u16(a6 - 0xa))
		memory.write_u16(0x23d26, memory.read_u16(a6 - 2))
		a7 = body - 8
		invoke_tagged_attitude_compose_neg_sins6(0x23c74)
	else:
		a7 = body - 8
		var folded: int = C68kScript.s16(fold_angle_pm_0x2000(memory.read_u32(0x23d38)))
		a7 = body - 0xc
		var sin: int = C68kScript.s16(sin_fixed14(memory.read_s16(0x23d3c)))
		a7 = body - 0x20
		build_attitude_matrix_x_alt(memory.read_s16(0x23d40), C68kScript.asr32(folded * sin, 12) & 0x3fff,
				memory.read_s16(0x23d3c), memory.read_s32(0x23d38), 0x23c74)
	a7 = entry_a7
	if memory.read_u16(memory.read_u32(0x207fc) + 0xaa) != 0:
		var child1: int = memory.read_u32(0x21598)
		_queue_visible(child1)
		a7 = body - 0x14
		transform_vecs12_by_matrix3(memory.read_s16(child1 + 0x38), memory.read_u32(child1 + 0x46), memory.read_u32(child1 + 0x4e), 0x23c74)
		a7 = body - 0x1c
		var banded: int = update_object_distance_band(memory.read_u32(0x21594), C68kScript.u32(C68kScript.lsl32(memory.read_u32(0x23d64), 1)))
		if C68kScript.u32(banded) != 0:
			a7 = body - 8
			shade_object_faces(memory.read_u32(0x21594))
		a7 = entry_a7
	else:
		_queue_visible(memory.read_u32(0x2159c))
	var child: int = memory.read_u32(0x21594)
	_queue_visible(child)
	a7 = body - 0x14
	transform_vecs12_by_matrix3(memory.read_s16(child + 0x38), memory.read_u32(child + 0x46), memory.read_u32(child + 0x4e), 0x23c74)
	if memory.read_s16(0x23d88) == 2:
		a7 = body - 0x20
		build_attitude_matrix_6ang_alt(memory.read_s16(0x23d40), memory.read_s16(0x23d3e), memory.read_s16(0x23d3c),
				memory.read_s32(0x23d38), memory.read_s32(0x23d34), memory.read_s32(0x23d30), 0x23c74)
	else:
		a7 = body - 0x18
		build_attitude_matrix_x_alt(memory.read_s16(0x23d40), memory.read_s16(0x23d3e), memory.read_s16(0x23d3c),
				memory.read_s32(0x23d38), 0x23c74)
	a7 = body - 8
	var n: int = C68kScript.s32(memory.read_u32(0x20f2c))
	var cursor: int = 0x20b44
	while true:
		n = C68kScript.s32(n - 1)
		if n == -1:
			break
		update_object_camera_metric(memory.read_u32(cursor))
		cursor += 4
	a7 = body - 4
	update_ground_scatter_objects()
	a7 = entry_a7
	memory.write_u32(0x215a0, memory.read_u32(0x219bc))
	_sort_far_objects(body)
	a7 = body - 0xc
	collect_near_objects(0x20b44, 0x20f2c)
	memory.write_u32(0x219ac, 0)
	memory.write_u16(0x219aa, memory.read_u16(0x219be))
	a7 = body - 0x14
	collect_far_objects(0x20804, 0x20b24)
	if (memory.read_u32(0x21020) & 0xf) == 0:
		memory.write_u16(0x219a4, 1)
	if memory.read_u16(0x219a4) != 0:
		a7 = body - 0xc
		dispatch_overlap_collisions(0x219cc, 0x219ac)
		memory.write_u16(0x219a4, 0)
	a7 = body - 4
	age_contact_records()
	build_visible_object_list()
	a7 = entry_a7
	if memory.read_s16(0x2100e) == 1:
		var bottom2: int = memory.read_s16(0x27014) if memory.read_u16(0x27010) != 0 else memory.read_s16(0x20b2e)
		_frame_host(body, 0x84, [C68kScript.u32(bottom2 - memory.read_s16(0x27016))])
	a7 = body - 4
	render_visible_objects()
	if memory.read_u16(0x23fd2) != 0:
		noop_stub_2e12()
		memory.write_u16(0x23fd2, 0)
	if memory.read_s16(0x2100e) != 1:
		scan_convert_edge_rows()
	a7 = entry_a7
	memory.write_u32(0x219bc, 0)
	memory.write_u16(0x219a6, 0)
	memory.write_u16(0x23d86, 0)
	host = memory.read_u32(0x207fc)
	if memory.read_u16(memory.read_u32(host + 0x72) + 6) != 0:
		_frame_host(body, 0x29e, [])
	if hooks:
		_frame_host(body, 0x2fe, [])
	if C68kScript.u32(new_frame) == 1:
		a7 = body - 4
		draw_distant_object_dots()
		a7 = entry_a7
	if memory.read_s16(0x2100e) == 1:
		if memory.read_u16(memory.read_u32(0x207fc) + 0x9e) != 0:
			a7 = body - 8
			present_bw_frame(C68kScript.u32(new_frame))
			a7 = entry_a7
		else:
			_frame_host(body, 0x80, [0])
		_frame_host(body, 0x78, [])
	else:
		host = memory.read_u32(0x207fc)
		var width: int = memory.read_s16(host + 0x10)
		var ratio: int = C68kScript.s16(C68kScript.divs_trunc(8, memory.read_s16(0x2100e)))
		var offset: int = C68kScript.s32(width - C68kScript.s16(C68kScript.divs_trunc(memory.read_s16(0x21012), ratio)))
		if memory.read_u32(0x2101c) != 0:
			offset = C68kScript.s32(offset + width)
		if memory.read_u16(host + 0x9e) != 0:
			a7 = body - 0x1c
			present_spans_to_window(memory.read_u32(0x20f7c), memory.read_u32(0x20f80), C68kScript.u32(memory.read_s16(0x27012)),
					memory.read_u32(host + 6), C68kScript.u32(offset), C68kScript.u32(new_frame))
		else:
			memory.write_u32(a6 - 0x22, memory.read_u32(0x20f7c))
			memory.write_u32(a6 - 0x26, memory.read_u32(0x20f80))
			a7 = body - 0x18
			_jsr(memory.read_u32(0x26ff0), [a6 - 0x22, a6 - 0x26, C68kScript.u32(memory.read_s16(0x27012)), memory.read_u32(host + 6), C68kScript.u32(offset)])
		a7 = entry_a7
		var swap: int = memory.read_u32(0x20f7c)
		memory.write_u32(0x20f7c, memory.read_u32(0x20f80))
		memory.write_u32(0x20f80, swap)
	if memory.read_u16(0x27010) == 0 and memory.read_s16(0x23d88) == 1:
		_lay_out_view_panels(body, step_mode, new_frame, hooks)
	if C68kScript.u32(step_mode) == 0:
		if hooks:
			_frame_host(body, 0x2fe, [])
	elif memory.read_u16(0x27010) == 0:
		if hooks:
			_frame_host(body, 0x2fe, [])
		a7 = body - 8
		update_view_readouts_and_overlays(0)
		a7 = entry_a7
	else:
		if memory.read_s16(0x2100e) == 1:
			if hooks:
				_frame_host(body, 0x2fe, [])
			a7 = body - 8
			update_view_readouts_and_overlays(0)
		else:
			a7 = body - 8
			update_view_readouts_and_overlays(1 if hooks else 0)
			a7 = entry_a7
			if hooks:
				_frame_host(body, 0x2fe, [])
		a7 = body - 4
		update_throttle_gauge()
		update_radar_range_buttons()
		update_bomb_indicator()
		update_state_lamp()
		mark_view_overlays_drawn()
		a7 = entry_a7
	if C68kScript.u32(new_frame) != 1:
		memory.write_u16(0x20b2e, saved[0])
		memory.write_u16(0x2701a, saved[1])
		memory.write_u16(0x27018, saved[2])
		memory.write_u16(0x27016, saved[3])
		memory.write_u16(0x27014, saved[4])
		memory.write_u16(0x23d88, saved[6])
		memory.write_u16(0x27012, saved[5])
		memory.write_u32(0x21590, saved[7])
		var back: int = 0xd43 if C68kScript.u32(new_frame) == 2 else -0xd43
		memory.write_u32(0x23d30, C68kScript.u32(memory.read_u32(0x23d30) + back) & 0x3fff)
	memory.write_u32(C68kScript.u32(0x20f6c + frame * 4), memory.read_u32(0x20f7c))
	memory.write_u32(C68kScript.u32(0x20f5c + frame * 4), memory.read_u32(0x20f80))

## 0x16784 (1.0.3's test_proximity_collision 0x13e2e on Leyte's layout: flags long +0x14, position +4 / +8 / +0xc, radius
## +0x20): two objects both flagged 0x100000 collide when 3/4 of their distance (asr 1 + asr 2, 0x24 below) is inside
## either one's radius: dispatch_collision(a, b) (0x14 below).
func test_proximity_collision(obj_a: int, obj_b: int) -> void:
	assert(memory != null, "test_proximity_collision requires original global memory")
	var entry_a7: int = a7
	if (memory.read_u32(obj_a + 0x14) & memory.read_u32(obj_b + 0x14) & 0x100000) == 0:
		return
	a7 = entry_a7 - 0x24
	var dist: int = approx_dist3_xyz(memory.read_s32(obj_a + 4), memory.read_s32(obj_a + 8), memory.read_s32(obj_a + 0xc),
			memory.read_s32(obj_b + 4), memory.read_s32(obj_b + 8), memory.read_s32(obj_b + 0xc))
	a7 = entry_a7
	var near: int = C68kScript.s32(C68kScript.asr32(dist, 1) + C68kScript.asr32(dist, 2))
	if near < memory.read_s32(obj_a + 0x20) or near < memory.read_s32(obj_b + 0x20):
		a7 = entry_a7 - 0x14
		dispatch_collision(obj_a, obj_b, C68kScript.asr32(dist, 2))
		a7 = entry_a7

## 0x167e4 (1.0.3's test_player_vs_type0e_proximity 0x13e8e): only the player (a's controller at +0 == player_object
## 0x20b28) against an object whose controller's definition type is 0x14 (1.0.3: 0xe): test_proximity_collision (0x10
## below).
func test_player_vs_type0e_proximity(obj_a: int, obj_b: int) -> void:
	assert(memory != null, "test_player_vs_type0e_proximity requires original global memory")
	var entry_a7: int = a7
	if memory.read_u32(obj_a) != memory.read_u32(0x20b28):
		return
	if memory.read_u16(memory.read_u32(memory.read_u32(obj_b) + 4)) != 0x14:
		return
	a7 = entry_a7 - 0x10
	test_proximity_collision(obj_a, obj_b)
	a7 = entry_a7

## 0x1d4f6 (1.0.3's turret_pre_render 0x19374, the same 10 instructions on the turret controller): an aimed turret
## (+0x36) not firing (word +0x46 == 0) shows its aim heading (+0x4e & 0x3fff in +0x22). Returns 0.
func turret_pre_render(ctl: int, _phase: int = 0) -> int:
	assert(memory != null, "turret_pre_render requires original global memory")
	if memory.read_u32(ctl + 0x36) != 0 and memory.read_u16(ctl + 0x46) == 0:
		memory.write_u16(ctl + 0x22, memory.read_u16(ctl + 0x4e) & 0x3fff)
	return 0

## 0x15ec0 (1.0.3's find_contact_record 0x13580; Leyte tests the probe count first): the contact record of (a, b) in the
## 256-entry table 0x215a4, probing from contact_pair_hash(a, b) over at most 256 slots; 0 when absent.
func find_contact_record(obj_a: int, obj_b: int) -> int:
	assert(memory != null, "find_contact_record requires original global memory")
	var slot: int = contact_pair_hash(obj_a, obj_b)
	for _k in 0x100:
		var rec: int = memory.read_u32(0x215a4 + slot * 4)
		if rec != 0 and memory.read_u32(rec) == C68kScript.u32(obj_a) and memory.read_u32(rec + 4) == C68kScript.u32(obj_b):
			return rec
		slot = (slot + 1) & 0xff
	return 0

## 0x15f0e (1.0.3's create_shape_contact_record 0x135c8; link a6 -0xc and eight registers, body 0x30 below) on Leyte's
## layout: b's metric +0x1c (for +0x10), faces +0x4a (word +0x3c of them, for +0x3a), radius +0x20 (for +0x14), scale
## word +0x36 (for +0x34), vertices +0x46 and the face normals after word +0x38 of them; the table 0x215a4. The faces of
## b whose extent (+0x1c) reaches half its metric (toward zero) are selected; the record (heap_alloc_cleared(6 * count +
## 0x10), 8 below) holds a, b and the count (+0xa) and takes the first free slot from contact_pair_hash(a, b) (0x10
## below). +0xe = the asr steps bringing (2 * b +0x20 + a +0x20) asl b's scale under 0x8000, +0xc = b's scale - that.
## From +0x10: the selected face indices, then (after count words left 0) one axis word per face from its normal minus
## vertex 0 (abs_int 8 / 0xc / 0x10 below): 0 when |x| is the largest, 1 for |y|, else 2 (ties to the later axis).
func create_shape_contact_record(obj_a: int, obj_b: int) -> int:
	assert(memory != null, "create_shape_contact_record requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x30
	var half: int = memory.read_s32(obj_b + 0x1c)
	if half < 0:
		half += 1
	half = half >> 1
	var selected: Array = []
	var face: int = memory.read_u32(obj_b + 0x4a)
	for f in memory.read_u16(obj_b + 0x3c):
		if half <= memory.read_s32(face + 0x1c):
			selected.append(f)
		face = C68kScript.u32(face + 0x20)
	var count: int = C68kScript.s16(selected.size())
	a7 = body - 8
	var record: int = heap_alloc_cleared(C68kScript.s32(count * 6 + 0x10))
	memory.write_u16(record + 0xa, C68kScript.u16(count))
	memory.write_u32(record, C68kScript.u32(obj_a))
	memory.write_u32(record + 4, C68kScript.u32(obj_b))
	a7 = body - 0x10
	var slot: int = contact_pair_hash(obj_a, obj_b)
	a7 = entry_a7
	while memory.read_u32(0x215a4 + slot * 4) != 0:
		slot = (slot + 1) & 0xff
	memory.write_u32(0x215a4 + slot * 4, record)
	var extent: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(C68kScript.lsl32(memory.read_u32(obj_b + 0x20), 1) + memory.read_u32(obj_a + 0x20)), memory.read_s16(obj_b + 0x36)))
	var shift: int = 0
	while (C68kScript.u32(extent) & 0xffff8000) != 0:
		shift = C68kScript.s16(shift + 1)
		extent = C68kScript.asr32(extent, 1)
	memory.write_u16(record + 0xe, C68kScript.u16(shift))
	memory.write_u16(record + 0xc, C68kScript.u16(memory.read_u16(obj_b + 0x36) - shift))
	for k in selected.size():
		memory.write_u16(record + 0x10 + 2 * k, C68kScript.u16(selected[k]))
	var verts: int = memory.read_u32(obj_b + 0x46)
	var normals: int = C68kScript.u32(verts + memory.read_s16(obj_b + 0x38) * 12)
	var axis_p: int = record + 0x10 + count * 4
	for k in selected.size():
		var n: int = C68kScript.u32(normals + memory.read_s16(record + 0x10 + 2 * k) * 12)
		a7 = body - 8
		var ax: int = C68kScript.s16(abs_int(C68kScript.s32(memory.read_u32(n) - memory.read_u32(verts))))
		a7 = body - 0xc
		var ay: int = C68kScript.s16(abs_int(C68kScript.s32(memory.read_u32(n + 4) - memory.read_u32(verts + 4))))
		a7 = body - 0x10
		var az: int = C68kScript.s16(abs_int(C68kScript.s32(memory.read_u32(n + 8) - memory.read_u32(verts + 8))))
		a7 = entry_a7
		var axis: int
		if ay < ax:
			axis = 0 if az < ax else 2
		else:
			axis = 1 if az < ay else 2
		memory.write_u16(axis_p, axis)
		axis_p += 2
	return record

## 0x16be2 (1.0.3's handle_player_contact 0x1427c; four saved registers) on Leyte's layout: the player (a's controller at
## +0 == player_object 0x20b28) touching b - within the radii sum (+0x20) of 3/4 of their distance (0x2c below): terrain
## (b byte +0x16 bit 1) goes to handle_terrain_contact (0x1c below); otherwise the contact record is found or created
## (0x1c below), its live word +8 = 1, and with b byte +0x16 bit 6 b's collision hook (its controller's definition +0x12)
## runs as hook(b, a, record) (0x20 below).
func handle_player_contact(obj_a: int, obj_b: int) -> void:
	assert(memory != null, "handle_player_contact requires original global memory")
	var entry_a7: int = a7
	if memory.read_u32(obj_a) != memory.read_u32(0x20b28):
		return
	a7 = entry_a7 - 0x2c
	var dist: int = approx_dist3_xyz(memory.read_s32(obj_a + 4), memory.read_s32(obj_a + 8), memory.read_s32(obj_a + 0xc),
			memory.read_s32(obj_b + 4), memory.read_s32(obj_b + 8), memory.read_s32(obj_b + 0xc))
	a7 = entry_a7
	if C68kScript.s32(memory.read_u32(obj_a + 0x20) + memory.read_u32(obj_b + 0x20)) < C68kScript.s32(dist - C68kScript.asr32(dist, 2)):
		return
	if (memory.read_u8(obj_b + 0x16) & 2) != 0:
		a7 = entry_a7 - 0x1c
		handle_terrain_contact(obj_a, obj_b)
		a7 = entry_a7
		return
	a7 = entry_a7 - 0x1c
	var record: int = find_contact_record(obj_a, obj_b)
	if record == 0:
		record = create_shape_contact_record(obj_a, obj_b)
	a7 = entry_a7
	memory.write_u16(record + 8, 1)
	if (memory.read_u8(obj_b + 0x16) & 0x40) != 0:
		_shape_collision_hook(obj_a, obj_b, record, 0x20)

## 0x1617e (1.0.3's create_fractal_contact_record 0x1381c; link a6 -0x100 and six registers, body 0x11c below) on Leyte's
## layout: radii +0x20 (for +0x14), b's scale word +0x36 (for +0x34), the part index word +0x12 (1.0.3's byte +2) along the
## parent chain (+0x24) into each parent's part table (+0x32, 0x18-byte entries, angle word +4); the table 0x215a4. A
## contact record (0x3e bytes, heap_alloc - not cleared; 8 below) for object a over the fractal terrain b, in the contact
## table from contact_pair_hash (0x10 below); +0xc = the asr steps bringing (2 * b +0x20 + a +0x20) asl b's scale under
## 0x8000, +0xa = b's scale - that; with a non-zero heading (the part angles summed, & 0x3fff) the cell rotation +0xe =
## cos + (-sin) / 2, +0x10 = -sin, +0x12 = sin + cos / 2, +0x14 = cos (cos 8 / sin 0xc below), else +0xe = +0x14 = 0; the
## cell size +0x16 = (0x19c80 asl b's scale) asr +0xc; the cell bounds +0x18..+0x1e empty (1, -1, -1, 1).
func create_fractal_contact_record(obj_a: int, obj_b: int) -> int:
	assert(memory != null, "create_fractal_contact_record requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x11c
	a7 = body - 8
	var record: int = heap_alloc(0x3e)
	memory.write_u32(record, C68kScript.u32(obj_a))
	memory.write_u32(record + 4, C68kScript.u32(obj_b))
	a7 = body - 0x10
	var slot: int = contact_pair_hash(obj_a, obj_b)
	a7 = entry_a7
	while memory.read_u32(0x215a4 + slot * 4) != 0:
		slot = (slot + 1) & 0xff
	memory.write_u32(0x215a4 + slot * 4, record)
	var extent: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(C68kScript.lsl32(memory.read_u32(obj_b + 0x20), 1) + memory.read_u32(obj_a + 0x20)), memory.read_s16(obj_b + 0x36)))
	var steps: int = 0
	while (C68kScript.u32(extent) & 0xffff8000) != 0:
		steps = C68kScript.s16(steps + 1)
		extent = C68kScript.asr32(extent, 1)
	memory.write_u16(record + 0xc, C68kScript.u16(steps))
	memory.write_u16(record + 0xa, C68kScript.u16(memory.read_u16(obj_b + 0x36) - steps))
	var angle: int = 0
	var part: int = memory.read_s16(obj_b + 0x12)
	var link: int = memory.read_u32(obj_b + 0x24)
	while link != 0:
		angle = (angle + memory.read_u16(C68kScript.u32(memory.read_u32(link + 0x32) + part * 0x18 + 4))) & 0x3fff
		part = memory.read_s16(link + 0x12)
		link = memory.read_u32(link + 0x24)
	if angle != 0:
		a7 = body - 8
		var c: int = C68kScript.s16(cos_fixed14(angle))
		a7 = body - 0xc
		var sn: int = C68kScript.s16(sin_fixed14(angle))
		a7 = entry_a7
		var neg_sin: int = C68kScript.s16(-sn)
		memory.write_u16(record + 0xe, C68kScript.u16(((neg_sin + 1 if neg_sin < 0 else neg_sin) >> 1) + c))
		memory.write_u16(record + 0x10, C68kScript.u16(neg_sin))
		memory.write_u16(record + 0x12, C68kScript.u16(((c + 1 if c < 0 else c) >> 1) + sn))
		memory.write_u16(record + 0x14, C68kScript.u16(c))
	else:
		memory.write_u16(record + 0x14, 0)
		memory.write_u16(record + 0xe, 0)
	memory.write_u16(record + 0x16, C68kScript.u16(C68kScript.asr32(C68kScript.s32(C68kScript.lsl32(0x19c80, memory.read_s16(obj_b + 0x36))), memory.read_u16(record + 0xc))))
	memory.write_u16(record + 0x18, 1)
	memory.write_u16(record + 0x1a, 0xffff)
	memory.write_u16(record + 0x1c, 0xffff)
	memory.write_u16(record + 0x1e, 1)
	return record

## 0x162b8 (1.0.3's fractal_height_at 0x13960; link a6 -0x14 and ten registers, body 0x40 below), 1.0.3's code on Leyte's
## layout and addresses: the terrain's class word +0x10 (1.0.3's byte +1) picks its template entry (mission root +0x2a list
## + 0x1a), its vertex / face-centre counts words +0x38 / +0x3a, vertices +0x46, faces +0x4a, position +4 / +8 / +0xc (for
## +0x18 / +0x1c / +0x20); the face pair 0x2707c / 0x2707e and the plane pointer 0x27080. Leyte tests the centre loop's
## count first. Ground height of the fractal terrain at (x, z) relative to it, as 1.0.3: the point in cell coordinates,
## the cell recomputed (and its two faces looked up) outside the cached bounds, the triangle remembered in +0x20 (a change
## sets *changed = 1), height = p[5] + ((z - p[6]) * -p[3] - (x - p[4]) * p[1]) / p[2], 0 when p[2] is 0 or the height
## negative.
func fractal_height_at(rec: int, x: int, z: int, changed: int) -> int:
	assert(memory != null, "fractal_height_at requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x40
	x = C68kScript.s16(x)
	z = C68kScript.s16(z)
	var obj: int = memory.read_u32(rec + 4)
	var u: int
	var v: int
	if memory.read_u16(rec + 0xe) != 0 or memory.read_u16(rec + 0x14) != 0:
		u = C68kScript.s16(C68kScript.asr32(C68kScript.s32(x * memory.read_s16(rec + 0xe) + z * memory.read_s16(rec + 0x12)), 12))
		v = C68kScript.s16(C68kScript.asr32(C68kScript.s32(x * memory.read_s16(rec + 0x10) + z * memory.read_s16(rec + 0x14)), 12))
	else:
		u = C68kScript.s16(x + ((z + 1 if z < 0 else z) >> 1))
		v = z
	if u < memory.read_s16(rec + 0x18) or u >= memory.read_s16(rec + 0x1a) or memory.read_s16(rec + 0x1c) > v or not (memory.read_s16(rec + 0x1e) > v):
		memory.write_u16(rec + 0x20, 0xffff)
		var n: int = memory.read_s16(rec + 0x16)
		var half: int = (n + 1 if n < 0 else n) >> 1
		var cu: int = C68kScript.s16(_cell_floor(u, n) * n)
		var cv: int = C68kScript.s16(_cell_floor(v, n) * n)
		memory.write_u16(rec + 0x18, C68kScript.u16(cu))
		memory.write_u16(rec + 0x1a, C68kScript.u16(cu + n))
		memory.write_u16(rec + 0x1c, C68kScript.u16(cv))
		memory.write_u16(rec + 0x1e, C68kScript.u16(cv + n))
		var centre_z: int = C68kScript.s32(half + cv)
		var halved: int = (centre_z + 1 if centre_z < 0 else centre_z) >> 1
		var centre_x: int = C68kScript.s32(half + cu - halved)
		var shift: int = memory.read_s16(rec + 0xa)
		if shift < 0:
			centre_z = C68kScript.s32(C68kScript.lsl32(centre_z, -shift))
			centre_x = C68kScript.s32(C68kScript.lsl32(centre_x, -shift))
		else:
			centre_x = C68kScript.asr32(centre_x, shift)
			centre_z = C68kScript.asr32(centre_z, shift)
		var entry: int = C68kScript.u32(memory.read_u32(C68kScript.u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x1a + 0x14 + memory.read_u16(obj + 0x10) * 0x18)) + memory.read_u32(memory.read_u32(0x207fc) + 0x8c))
		var centres: int = C68kScript.u32(entry + 0x22 + C68kScript.s32(memory.read_s16(obj + 0x38) - memory.read_s16(obj + 0x3a)) * 12)
		memory.write_u16(0x2707c + 2, 0xffff)
		memory.write_u16(0x2707c, 0xffff)
		var centre: int = centres
		for i in memory.read_u16(obj + 0x3a):
			a7 = body - 8
			var dx: int = abs_int(C68kScript.s32(memory.read_u32(centre) - centre_x))
			a7 = body - 0xc
			var dz: int = abs_int(C68kScript.s32(memory.read_u32(centre + 8) - centre_z))
			a7 = entry_a7
			if C68kScript.s32(dx + dz) <= 0xad3f:
				var index: int = C68kScript.u16(C68kScript.divs_trunc(C68kScript.s32(centre - centres), 12))
				if centre_z >= memory.read_s32(centre + 8):
					memory.write_u16(0x2707c, index)
				else:
					memory.write_u16(0x2707c + 2, index)
			centre = C68kScript.u32(centre + 12)
		memory.write_u16(rec + 0x34, 0)
		memory.write_u16(rec + 0x26, 0)
		var verts: int = memory.read_u32(obj + 0x46)
		for k in 2:
			var face_index: int = memory.read_s16(0x2707c + 2 * k)
			if face_index == -1:
				continue
			var v0: int = C68kScript.u32(verts + memory.read_s16(memory.read_u32(C68kScript.u32(memory.read_u32(obj + 0x4a) + face_index * 0x20) + 0x18)) * 12)
			var nrm: int = C68kScript.u32(verts + memory.read_s16(obj + 0x38) * 12 + face_index * 12)
			var plane: int = rec + 0x22 + 0xe * k
			memory.write_u16(plane, C68kScript.u16(face_index))
			memory.write_u16(plane + 2, C68kScript.u16(memory.read_u16(nrm + 2) - memory.read_u16(verts + 2)))
			memory.write_u16(plane + 4, C68kScript.u16(memory.read_u16(nrm + 6) - memory.read_u16(verts + 6)))
			memory.write_u16(plane + 6, C68kScript.u16(memory.read_u16(nrm + 0xa) - memory.read_u16(verts + 0xa)))
			for j in 3:
				memory.write_u16(plane + 8 + 2 * j, C68kScript.u16(C68kScript.asr32(C68kScript.s32(memory.read_u32(v0 + 4 * j) - memory.read_u32(obj + 4 + 4 * j)), memory.read_u16(rec + 0xc))))
	var plane_used: int
	if C68kScript.s32(v - memory.read_s16(rec + 0x1c)) < C68kScript.s32(u - memory.read_s16(rec + 0x18)):
		plane_used = rec + 0x22
		if memory.read_u16(rec + 0x20) != 0:
			memory.write_u16(changed, 1)
			memory.write_u16(rec + 0x20, 0)
	else:
		plane_used = rec + 0x30
		if memory.read_s16(rec + 0x20) != 1:
			memory.write_u16(changed, 1)
			memory.write_u16(rec + 0x20, 1)
	memory.write_u32(0x27080, C68kScript.u32(plane_used))
	var p2: int = memory.read_s16(plane_used + 4)
	if p2 == 0:
		return 0
	var num: int = C68kScript.s32(C68kScript.s32(-memory.read_s16(plane_used + 6) * C68kScript.s16(z - memory.read_u16(plane_used + 0xc))) - C68kScript.s16(x - memory.read_u16(plane_used + 8)) * memory.read_s16(plane_used + 2))
	var height: int = C68kScript.s32(C68kScript.divs_trunc(num, p2) + memory.read_s16(plane_used + 0xa))
	return 0 if height < 0 else height

## 0x16ae6 (1.0.3's handle_terrain_contact 0x14178; link a6 -4 and five registers, body 0x1c below) on Leyte's layout: the
## controller at +0 (for +8), b's terrain flag byte +0x16 bit 1, a's fractal reference +0x2e (for +0x2c), positions +4 /
## +0xc (for +0x18 / +0x20), the live-play flag 0x21058. The contact record is found (0xc below) or, unless b is terrain
## while a already holds a fractal reference, created (create_fractal_contact_record, 0xc below); its live word +8 = 1.
## The ground height at a's position relative to b (shifted by +0xa; fractal_height_at, 0x14 below, its changed flag in
## the frame at a6 - 2) goes to a's controller +0x2e (unshifted) when a has a controller and the height is non-zero, live
## play also clearing its surface byte +0x2c; a's fractal reference is released (8 below) and the record's triangle
## forgotten (+0x20 = -1).
func handle_terrain_contact(obj_a: int, obj_b: int) -> void:
	assert(memory != null, "handle_terrain_contact requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x1c
	a7 = body - 0xc
	var record: int = find_contact_record(obj_a, obj_b)
	if record == 0:
		if (memory.read_u8(obj_b + 0x16) & 2) != 0 and memory.read_u32(obj_a + 0x2e) != 0:
			a7 = entry_a7
			return
		record = create_fractal_contact_record(obj_a, obj_b)
	a7 = entry_a7
	memory.write_u16(record + 8, 1)
	var shift: int = memory.read_s16(record + 0xa)
	var rx: int
	var rz: int
	if shift < 0:
		rx = C68kScript.s16(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj_a + 4) - memory.read_u32(obj_b + 4)), -shift))
		rz = C68kScript.s16(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj_a + 0xc) - memory.read_u32(obj_b + 0xc)), -shift))
	else:
		rx = C68kScript.s16(C68kScript.lsl32(C68kScript.s32(memory.read_u32(obj_a + 4) - memory.read_u32(obj_b + 4)), shift))
		rz = C68kScript.s16(C68kScript.lsl32(C68kScript.s32(memory.read_u32(obj_a + 0xc) - memory.read_u32(obj_b + 0xc)), shift))
	var changed: int = entry_a7 - 4 - 2
	memory.write_u16(changed, 0)
	a7 = body - 0x14
	var ground: int = fractal_height_at(record, rx, rz, changed)
	a7 = entry_a7
	_store_terrain_ground(obj_a, record, ground)
	if memory.read_u32(obj_a + 0x2e) != 0:
		a7 = body - 8
		release_fractal_reference(obj_a)
		a7 = entry_a7
	memory.write_u16(record + 0x20, 0xffff)

## 0x15d8c (1.0.3's point_in_face_by_axis 0x1344c, the same 101 instructions with Leyte's position +4 / +8 / +0xc and a
## larger frame: link a6 -0x708, ten registers, body 0x734 below): is object a's position (shifted by record word +0xc:
## asr by its negation when negative, else asl) inside the face projected on the plane the axis word names (0 y/z, 1 x/z,
## 2 x/y; others -> 0)? The face of b is projected with the record's vertex shift (+0xe) into the frame (a6 - 0x708;
## project 0x1c below) and tested at (0, 0) (0x30 below). Returns 1 or 0.
func point_in_face_by_axis(rec: int, obj_a: int, obj_b: int, face: int, _dot: int, _nx: int, _ny: int, _nz: int, axis: int) -> int:
	assert(memory != null, "point_in_face_by_axis requires original global memory")
	assert(a7 != 0, "point_in_face_by_axis needs a7 (its frame holds the projected vertices)")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x734
	var projected: int = entry_a7 - 4 - 0x708
	var shift: int = memory.read_s16(rec + 0xc)
	var p: Array = []
	for off in [4, 8, 0xc]:
		if shift < 0:
			p.append(C68kScript.asr32(memory.read_u32(obj_a + off), -shift))
		else:
			p.append(C68kScript.s32(C68kScript.lsl32(memory.read_u32(obj_a + off), shift)))
	axis = C68kScript.s16(axis)
	var vertex_shift: int = memory.read_s16(rec + 0xe)
	var count: int = memory.read_s16(face + 0x14)
	var idx: int = memory.read_u32(face + 0x18)
	var inside: int
	a7 = body - 0x1c
	if axis == 0:
		project_face_vertices_yz(obj_b, vertex_shift, face, p[1], p[2], projected)
		a7 = body - 0x30
		inside = point_in_polygon_yz(0, 0, projected, count, idx)
	elif axis == 1:
		project_face_vertices_xz(obj_b, vertex_shift, face, p[0], p[2], projected)
		a7 = body - 0x30
		inside = point_in_polygon_xz(0, 0, projected, count, idx)
	elif axis == 2:
		project_face_vertices_xy(obj_b, vertex_shift, face, p[0], p[1], projected)
		a7 = body - 0x30
		inside = point_in_polygon_xy(0, 0, projected, count, idx)
	else:
		a7 = entry_a7
		return 0
	a7 = entry_a7
	return 1 if inside != 0 else 0

## 0x16812 (1.0.3's test_object_collision 0x13ec0; link a6 -0x2c and ten registers, body 0x58 below), written from Leyte's
## listing (registers reallocated): moving object a against shape b on Leyte's layout - positions +4 / +8 / +0xc, radii
## +0x20, b's flag bytes +0x16 bits 1 (terrain) / 2 (bullet target) / 6 (collision hook), +0x15 bit 0 (no faces test),
## the controller at +0, faces +0x4a, vertices +0x46 and the normals after word +0x38 of them. Beyond the radii sum of 3/4
## of their distance (0x74 below) nothing happens. Terrain goes to test_terrain_collision (0x64 below); a bullet (a's
## controller's definition type 0x14 - 1.0.3's 0xe - outside view mode 8) to bullet_hits_shape (0x64 below). Otherwise the
## contact record is found or created (0x64 below; live word +8 = 1), b's collision hook runs as hook(b, a, record) (0x68
## below) and, unless either object has +0x15 bit 0, each recorded face is checked - Leyte skips a face whose flags byte
## +0x13 has bit 1 (its side word kept) -: a's position (shifted by +0xc) relative to the face's first vertex (asr +0xe),
## dotted with the face normal minus vertex 0 (words, muls.w), gives the side (1 when <= 0). On an old record a side change
## asks point_in_face_by_axis (0x80 below); inside -> dispatch_collision(a, b) (0x64 below) and return. The side is kept.
## Returns D0 as the bytes leave it in the bullet and dispatch cases (callers ignore it).
func test_object_collision(obj_a: int, obj_b: int) -> int:
	assert(memory != null, "test_object_collision requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x58
	a7 = body - 0x1c
	var dist: int = approx_dist3_xyz(memory.read_s32(obj_a + 4), memory.read_s32(obj_a + 8), memory.read_s32(obj_a + 0xc),
			memory.read_s32(obj_b + 4), memory.read_s32(obj_b + 8), memory.read_s32(obj_b + 0xc))
	a7 = entry_a7
	if C68kScript.s32(memory.read_u32(obj_a + 0x20) + memory.read_u32(obj_b + 0x20)) < C68kScript.s32(dist - C68kScript.asr32(dist, 2)):
		return 0
	if (memory.read_u8(obj_b + 0x16) & 2) != 0:
		a7 = body - 0xc
		test_terrain_collision(obj_a, obj_b)
		a7 = entry_a7
		return 0
	if (memory.read_u8(obj_b + 0x16) & 4) != 0 and memory.read_u32(obj_a) != 0 \
			and memory.read_u16(memory.read_u32(memory.read_u32(obj_a) + 4)) == 0x14 and memory.read_s16(0x23d88) != 8:
		a7 = body - 0xc
		var hit: int = bullet_hits_shape(obj_a, obj_b)
		a7 = entry_a7
		return hit
	a7 = body - 0xc
	var record: int = find_contact_record(obj_a, obj_b)
	var created: bool = record == 0
	if created:
		record = create_shape_contact_record(obj_a, obj_b)
	a7 = entry_a7
	memory.write_u16(record + 8, 1)
	if (memory.read_u8(obj_b + 0x16) & 0x40) != 0:
		_shape_collision_hook(obj_a, obj_b, record, 0x68)
	if (memory.read_u8(obj_a + 0x15) & 1) != 0 or (memory.read_u8(obj_b + 0x15) & 1) != 0:
		return 0
	var faces: int = memory.read_u32(obj_b + 0x4a)
	var verts: int = memory.read_u32(obj_b + 0x46)
	var normals: int = C68kScript.u32(verts + memory.read_s16(obj_b + 0x38) * 12)
	var shift: int = memory.read_s16(record + 0xc)
	var p: Array = []
	for off in [4, 8, 0xc]:
		if shift < 0:
			p.append(C68kScript.s16(C68kScript.asr32(memory.read_u32(obj_a + off), -shift)))
		else:
			p.append(C68kScript.s16(C68kScript.lsl32(memory.read_u32(obj_a + off), shift)))
	var count: int = memory.read_u16(record + 0xa)
	var idx_p: int = record + 0x10
	var side_p: int = idx_p + C68kScript.s16(count) * 2
	var axis_p: int = side_p + C68kScript.s16(count) * 2
	var vshift: int = memory.read_s16(record + 0xe)
	while count != 0:
		count -= 1
		var fi: int = memory.read_s16(idx_p)
		var face: int = C68kScript.u32(faces + fi * 0x20)
		if (memory.read_u8(face + 0x13) & 2) == 0:
			var v0: int = C68kScript.u32(verts + memory.read_s16(memory.read_u32(face + 0x18)) * 12)
			var n: int = C68kScript.u32(normals + fi * 12)
			var nx: int = C68kScript.s16(memory.read_u16(n + 2) - memory.read_u16(verts + 2))
			var ny: int = C68kScript.s16(memory.read_u16(n + 6) - memory.read_u16(verts + 6))
			var nz: int = C68kScript.s16(memory.read_u16(n + 0xa) - memory.read_u16(verts + 0xa))
			var dx: int = C68kScript.s16(p[0] - C68kScript.asr32(memory.read_u32(v0), vshift))
			var dy: int = C68kScript.s16(p[1] - C68kScript.asr32(memory.read_u32(v0 + 4), vshift))
			var dz: int = C68kScript.s16(p[2] - C68kScript.asr32(memory.read_u32(v0 + 8), vshift))
			var dot: int = C68kScript.s32(C68kScript.s32(dx * nx + dy * ny) + dz * nz)
			var side: int = 1 if dot <= 0 else 0
			if not created and (memory.read_u16(side_p) ^ side) != 0:
				a7 = body - 0x28
				var inside: int = point_in_face_by_axis(record, obj_a, obj_b, face, dot, nx, ny, nz, memory.read_s16(axis_p))
				a7 = entry_a7
				if inside != 0:
					a7 = body - 0xc
					var d0: int = dispatch_collision(obj_a, obj_b, inside)
					a7 = entry_a7
					return d0
			memory.write_u16(side_p, side)
		idx_p += 2
		side_p += 2
		axis_p += 2
	return 0

## 0x48e8 (1.0.3's segment_point_min_distance 0x3caa; Leyte tests the bisection count first, the same four steps):
## approximate distance from the point to the segment start .. start + (word dx, dy, dz) (only the low words of the
## deltas, sign-extended): both end distances (word results), then four bisection steps replacing the farther end by the
## midpoint (add.l, asr.l #1); returns the smaller end distance as a sign-extended word (ties keep the far end).
static func segment_point_min_distance(x0: int, y0: int, z0: int, dx: int, dy: int, dz: int, px: int, py: int, pz: int) -> int:
	var sx: int = C68kScript.s32(x0)
	var sy: int = C68kScript.s32(y0)
	var sz: int = C68kScript.s32(z0)
	var ex: int = C68kScript.s32(sx + C68kScript.s16(dx))
	var ey: int = C68kScript.s32(sy + C68kScript.s16(dy))
	var ez: int = C68kScript.s32(sz + C68kScript.s16(dz))
	var dist_start: int = C68kScript.s16(approx_dist3_xyz(sx, sy, sz, px, py, pz))
	var dist_end: int = C68kScript.s16(approx_dist3_xyz(ex, ey, ez, px, py, pz))
	for _step in 4:
		var mx: int = C68kScript.asr32(sx + ex, 1)
		var my: int = C68kScript.asr32(sy + ey, 1)
		var mz: int = C68kScript.asr32(sz + ez, 1)
		var dist_mid: int = C68kScript.s16(approx_dist3_xyz(mx, my, mz, px, py, pz))
		if dist_end < dist_start:
			sx = mx
			sy = my
			sz = mz
			dist_start = dist_mid
		else:
			ex = mx
			ey = my
			ez = mz
			dist_end = dist_mid
	if dist_start < dist_end:
		dist_end = dist_start
	return dist_end

## bullet_hits_shape's segment (Leyte: the bullet's position +4 / +8 / +0xc less half its controller's velocity +8 / +0xc /
## +0x10, C truncation; the target's position +4 / +8 / +0xc).
func _bullet_segment_distance(bullet: int, bullet_ctl: int, target: int) -> int:
	var vx: int = memory.read_s32(bullet_ctl + 8)
	var vy: int = memory.read_s32(bullet_ctl + 0xc)
	var vz: int = memory.read_s32(bullet_ctl + 0x10)
	return segment_point_min_distance(
			C68kScript.s32(memory.read_s32(bullet + 4) - C68kScript.divs_trunc(vx, 2)),
			C68kScript.s32(memory.read_s32(bullet + 8) - C68kScript.divs_trunc(vy, 2)),
			C68kScript.s32(memory.read_s32(bullet + 0xc) - C68kScript.divs_trunc(vz, 2)),
			vx, vy, vz,
			memory.read_s32(target + 4), memory.read_s32(target + 8), memory.read_s32(target + 0xc))

## 0x1a64a (1.0.3's bullet_hits_shape 0x1663a; three saved registers) on Leyte's layout: the bullet's controller at +0
## (for +8), the shape's radius +0x20 (for +0x14), flag byte +0x16 bit 2 (for +6), controller at +0; the target types 0x14
## / 0x24 (1.0.3's 0xe / 0x1b). Within range of the bullet's path (0x34 below): a flagged shape latches into the bullet
## controller's +0x3c 1 time in 8 (rng & 7 == 0, 0x10 below); else a type-0x14 target is ignored, a type-0x24 one gets its
## controller word +0x46 = 1 when rng & 0xf == 0, and the bullet is retired (0x14 below). Returns D0 of each path.
func bullet_hits_shape(bullet: int, shape: int) -> int:
	assert(memory != null, "bullet_hits_shape requires original global memory")
	var entry_a7: int = a7
	var bullet_ctl: int = memory.read_u32(bullet)
	a7 = entry_a7 - 0x34
	var d0: int = _bullet_segment_distance(bullet, bullet_ctl, shape)
	a7 = entry_a7
	if d0 > memory.read_s32(shape + 0x20):
		return d0
	if (memory.read_u8(shape + 0x16) & 4) != 0:
		a7 = entry_a7 - 0x10
		var r: int = overlay_rng_next()
		a7 = entry_a7
		d0 = (r & 0xffff0000) | (r & 7)
		if (r & 7) == 0:
			memory.write_u32(bullet_ctl + 0x3c, shape)
		return d0
	var ctl: int = memory.read_u32(shape)
	if ctl != 0:
		var kind: int = memory.read_u16(memory.read_u32(ctl + 4))
		d0 = (d0 & 0xffff0000) | kind
		if kind == 0x14:
			return d0
		if kind == 0x24:
			a7 = entry_a7 - 0x10
			var r2: int = overlay_rng_next()
			a7 = entry_a7
			d0 = (r2 & 0xffff0000) | (r2 & 0xf)
			if (r2 & 0xf) == 0:
				memory.write_u16(ctl + 0x46, 1)
	a7 = entry_a7 - 0x14
	var result: int = retire_entity(bullet, d0)
	a7 = entry_a7
	return result

## 0x171ba (1.0.3's lift_object_onto_terrain 0x14810; link a6 -4 and nine registers, body 0x2c below), written from
## Leyte's listing on Leyte's layout: obj's y +8, range +0x18, metric +0x1c, flag byte +0x15 bits 3 / 4, scale word +0x36,
## counts words +0x38 / +0x3c, vertices +0x46, faces +0x4a, support point +0x2e; the terrain's position +4 / +0xc, counts
## +0x38 / +0x3a, vertices +0x46, support list +0x2e; deferred_free_flag 0x219a6 and the plane pointer 0x27080. An object
## below the ground (height < ground) without a support point is raised by the ground (unscaled by the record shift
## +0xa): y, and - unless +0x15 bit 4 - its vertices (translate_int3_vecs, dy asl the scale, 0x1c below); unless +0x15
## bit 3 its range follows the new camera metric (update_object_camera_metric, 8 below) and its face extents +0x1c rise by
## dy; +0x15 bit 3 is set and the height becomes the ground. The support point: with a usable plane (+4 of the plane
## 0x27080 points to) the face centre of the record's triangle in the terrain's live vertices; otherwise a cell corner mix
## ((2 corner + other) / 3 per axis, by triangle), unrotated u - v / 2, unscaled and offset by the terrain position. The
## terrain's support list (count, capacity, pointers; created with capacity 5, 8 below) gets the point, matched by x / z or
## created (heap_alloc_cleared 0x10, 8 below; the list grown by 5 through heap_realloc_grow_only, 0xc below); obj leaves
## its old point (count - 1, deferred_free_flag) and holds the new one. Returns the height (word).
func lift_object_onto_terrain(rec: int, ground: int, height: int, obj: int, terrain: int) -> int:
	assert(memory != null, "lift_object_onto_terrain requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x2c
	ground = C68kScript.s16(ground)
	height = C68kScript.s32(height)
	var shift: int = memory.read_s16(rec + 0xa)
	if height < ground and memory.read_u32(obj + 0x2e) == 0:
		var dy: int = C68kScript.s32(C68kScript.lsl32(ground, -shift)) if shift < 0 else C68kScript.asr32(ground, shift)
		memory.write_u32(obj + 8, C68kScript.u32(memory.read_u32(obj + 8) + dy))
		if (memory.read_u8(obj + 0x15) & 0x10) == 0:
			a7 = body - 0x1c
			translate_int3_vecs(C68kScript.s32(memory.read_s16(obj + 0x38) + memory.read_s16(obj + 0x3c)), memory.read_u32(obj + 0x46),
					memory.read_u32(obj + 0x46), 0, C68kScript.s32(C68kScript.lsl32(dy, memory.read_s16(obj + 0x36))), 0)
			a7 = entry_a7
		if (memory.read_u8(obj + 0x15) & 8) == 0:
			a7 = body - 8
			var metric: int = update_object_camera_metric(obj)
			a7 = entry_a7
			memory.write_u32(obj + 0x18, C68kScript.u32(C68kScript.s32(metric - memory.read_u32(obj + 0x1c)) + memory.read_u32(obj + 0x18)))
			var face: int = C68kScript.u32(memory.read_u32(obj + 0x4a) + 0x1c)
			for _i in memory.read_u16(obj + 0x3c):
				memory.write_u32(face, C68kScript.u32(memory.read_u32(face) + dy))
				face = C68kScript.u32(face + 0x20)
		memory.write_u8(obj + 0x15, memory.read_u8(obj + 0x15) | 8)
		height = ground
	var sx: int
	var sz: int
	if memory.read_u16(memory.read_u32(0x27080) + 4) != 0:
		var centres: int = C68kScript.u32(memory.read_u32(terrain + 0x46) + C68kScript.s32(memory.read_s16(terrain + 0x38) - memory.read_s16(terrain + 0x3a)) * 12)
		var fi: int = memory.read_s16(C68kScript.u32(rec + 0x22 + memory.read_s16(rec + 0x20) * 0xe))
		sx = memory.read_u32(C68kScript.u32(centres + fi * 12))
		sz = memory.read_u32(C68kScript.u32(centres + fi * 12 + 8))
	else:
		var su: int
		var sv: int
		if memory.read_u16(rec + 0x20) != 0:
			su = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(rec + 0x1a) + 2 * memory.read_s16(rec + 0x18)), 3))
			sv = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(rec + 0x1c) + 2 * memory.read_s16(rec + 0x1e)), 3))
		else:
			su = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(rec + 0x18) + 2 * memory.read_s16(rec + 0x1a)), 3))
			sv = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32(memory.read_s16(rec + 0x1e) + 2 * memory.read_s16(rec + 0x1c)), 3))
		if memory.read_u16(rec + 0xe) == 0 and memory.read_u16(rec + 0x14) == 0:
			su = C68kScript.s16(su - ((sv + 1 if sv < 0 else sv) >> 1))
		if shift < 0:
			sx = C68kScript.u32(C68kScript.lsl32(su, -shift) + memory.read_u32(terrain + 4))
			sz = C68kScript.u32(C68kScript.lsl32(sv, -shift) + memory.read_u32(terrain + 0xc))
		else:
			sx = C68kScript.u32(C68kScript.asr32(su, shift) + memory.read_u32(terrain + 4))
			sz = C68kScript.u32(C68kScript.asr32(sv, shift) + memory.read_u32(terrain + 0xc))
	if memory.read_u32(terrain + 0x2e) == 0:
		a7 = body - 8
		var fresh: int = heap_alloc(0x1c)
		a7 = entry_a7
		memory.write_u16(fresh + 2, 5)
		memory.write_u16(fresh, 0)
		memory.write_u32(terrain + 0x2e, fresh)
	var old: int = memory.read_u32(obj + 0x2e)
	if old != 0:
		memory.write_u16(old, C68kScript.u16(memory.read_u16(old) - 1))
		memory.write_u16(0x219a6, 1)
	var list: int = memory.read_u32(terrain + 0x2e)
	var support: int = 0
	var cursor: int = list + 4
	for _i in memory.read_u16(list):
		var candidate: int = memory.read_u32(cursor)
		cursor += 4
		if memory.read_u32(candidate + 4) == sx and memory.read_u32(candidate + 8) == sz:
			support = candidate
			break
	if support == 0:
		a7 = body - 8
		support = heap_alloc_cleared(0x10)
		a7 = entry_a7
		memory.write_u32(support + 4, sx)
		memory.write_u32(support + 8, sz)
		if memory.read_u16(list + 2) == memory.read_u16(list):
			memory.write_u16(list + 2, C68kScript.u16(memory.read_u16(list + 2) + 5))
			a7 = body - 0xc
			list = heap_realloc_grow_only(list, C68kScript.s32(memory.read_s16(list + 2) * 4 + 8))
			a7 = entry_a7
			memory.write_u32(terrain + 0x2e, list)
		memory.write_u32(C68kScript.u32(list + 4 + memory.read_s16(list) * 4), support)
		memory.write_u16(list, C68kScript.u16(memory.read_u16(list) + 1))
	memory.write_u16(support, C68kScript.u16(memory.read_u16(support) + 1))
	memory.write_u32(obj + 0x2e, support)
	return C68kScript.s16(height)

## 0x1661a (1.0.3's test_terrain_collision 0x13cbc; link a6 -4 and six registers, body 0x20 below) on Leyte's layout:
## moving object a against fractal terrain b - positions +4 / +8 / +0xc, b's terrain flag +0x16 bit 1, a's support point
## +0x2e, the controller at +0, live-play flag 0x21058. Only within 0x33900 of b (z, and x skewed by z / 2) - then as
## handle_terrain_contact: record found or created (0xc below), height looked up (0x14 below) and stored through a's
## controller. When the triangle changed, lift_object_onto_terrain (0x18 below) settles a and gives its height; a below
## the ground (word compare) collides (dispatch_collision, 0xc below).
func test_terrain_collision(obj_a: int, obj_b: int) -> void:
	assert(memory != null, "test_terrain_collision requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x20
	var dz: int = C68kScript.s32(memory.read_u32(obj_a + 0xc) - memory.read_u32(obj_b + 0xc))
	var skew: int = C68kScript.s32(((dz + 1 if dz < 0 else dz) >> 1) + memory.read_u32(obj_a + 4) - memory.read_u32(obj_b + 4))
	if skew < -0x33900 or skew > 0x33900 or dz < -0x33900 or dz > 0x33900:
		return
	a7 = body - 0xc
	var record: int = find_contact_record(obj_a, obj_b)
	if record == 0:
		if (memory.read_u8(obj_b + 0x16) & 2) != 0 and memory.read_u32(obj_a + 0x2e) != 0:
			a7 = entry_a7
			return
		record = create_fractal_contact_record(obj_a, obj_b)
	a7 = entry_a7
	memory.write_u16(record + 8, 1)
	var shift: int = memory.read_s16(record + 0xa)
	var rx: int
	var ry: int
	var rz: int
	if shift < 0:
		rx = C68kScript.s16(C68kScript.asr32(C68kScript.s32(memory.read_u32(obj_a + 4) - memory.read_u32(obj_b + 4)), -shift))
		ry = C68kScript.s16(C68kScript.asr32(memory.read_u32(obj_a + 8), -shift))
		rz = C68kScript.s16(C68kScript.asr32(dz, -shift))
	else:
		rx = C68kScript.s16(C68kScript.lsl32(C68kScript.s32(memory.read_u32(obj_a + 4) - memory.read_u32(obj_b + 4)), shift))
		ry = C68kScript.s16(C68kScript.lsl32(memory.read_u32(obj_a + 8), shift))
		rz = C68kScript.s16(C68kScript.lsl32(dz, shift))
	var changed: int = entry_a7 - 4 - 2
	memory.write_u16(changed, 0)
	a7 = body - 0x14
	var ground: int = C68kScript.s16(fractal_height_at(record, rx, rz, changed))
	a7 = entry_a7
	_store_terrain_ground(obj_a, record, ground)
	if memory.read_u16(changed) != 0:
		a7 = body - 0x18
		ry = C68kScript.s16(lift_object_onto_terrain(record, ground, ry, obj_a, obj_b))
		a7 = entry_a7
	if ry < ground:
		a7 = body - 0xc
		dispatch_collision(obj_a, obj_b)
		a7 = entry_a7

## The ground height word written back to object a's controller (Leyte: at +0; its +0x2e, unscaled by the record's +0xa
## shift) when a has a controller and the height is non-zero; live play (0x21058 clear) also clears its surface byte +0x2c
## (inlined in handle_terrain_contact and test_terrain_collision).
func _store_terrain_ground(obj_a: int, rec: int, ground: int) -> void:
	var ctl: int = memory.read_u32(obj_a)
	if ctl == 0 or C68kScript.s16(ground) == 0:
		return
	var shift: int = memory.read_s16(rec + 0xa)
	var value: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s16(ground), -shift)) if shift < 0 else C68kScript.asr32(C68kScript.s16(ground), shift)
	memory.write_u32(ctl + 0x2e, C68kScript.u32(value))
	if memory.read_u16(0x21058) == 0:
		memory.write_u8(ctl + 0x2c, 0)

## b's collision hook (its controller's - Leyte: at +0 - definition +0x12) as hook(b, a, record), entered depth below the
## caller's entry a7 (inlined in handle_player_contact, 0x20, and test_object_collision, 0x68).
func _shape_collision_hook(obj_a: int, obj_b: int, rec: int, depth: int) -> int:
	var entry_a7: int = a7
	a7 = entry_a7 - depth
	var r = _jsr(memory.read_u32(memory.read_u32(memory.read_u32(obj_b) + 4) + 0x12), [obj_b, obj_a, rec])
	a7 = entry_a7
	return 0 if r == null else int(r)

## 0x177d0 (Leyte only; six saved registers): the sink of obj at (ang1, ang2 + its controller's heading word +0x654,
## ang3) (words; & 0x3fff on the second) through leyte_sink_body with the controller's cached composed chain (+0x630):
## composed once (compose_template_chain, 0x24 below; byte +0x61b marks it), the body then entered 0x38 below (the
## composer's arguments still pushed), else 0x30 below. Byte +0x61a is set first. Returns the body's D0
## (step_sinking_object reads it: 0 means gone under).
func leyte_sink_with_cached_chain(obj: int, ang1: int, ang2: int, ang3: int) -> int:
	assert(memory != null, "leyte_sink_with_cached_chain requires original global memory")
	var entry_a7: int = a7
	var ctl: int = memory.read_u32(obj)
	memory.write_u8(ctl + 0x61a, 1)
	var a1: int = C68kScript.s16(ang1)
	var a2: int = C68kScript.s16((ang2 + memory.read_u16(ctl + 0x654)) & 0x3fff)
	var a3: int = C68kScript.s16(ang3)
	var chain: int = ctl + 0x630
	var result: int
	if memory.read_u8(ctl + 0x61b) != 0:
		a7 = entry_a7 - 0x30
		result = leyte_sink_body(obj, a1, a2, a3, chain)
	else:
		a7 = entry_a7 - 0x24
		compose_template_chain(obj, chain)
		memory.write_u8(ctl + 0x61b, 1)
		a7 = entry_a7 - 0x38
		result = leyte_sink_body(obj, a1, a2, a3, chain)
	a7 = entry_a7
	return result

## 0x17d8c (Leyte only; one saved register): a controller's pending pose (byte +0x619) applied to its object (+0): with
## +0x628 set the object is sunk again level (leyte_sink_with_cached_chain(obj, 0, 0, 0), 0x18 below), otherwise its
## vertices (+0x46, words +0x38 + +0x3c of them) move by (+0x624, 0, +0x62c) shifted left by its scale word +0x36
## (translate_int3_vecs, 0x20 below); then +0x619 / +0x624 / +0x628 / +0x62c are cleared.
func leyte_apply_pending_pose(ctl: int) -> void:
	assert(memory != null, "leyte_apply_pending_pose requires original global memory")
	if memory.read_u8(ctl + 0x619) == 0:
		return
	var entry_a7: int = a7
	var obj: int = memory.read_u32(ctl)
	var scale: int = memory.read_s16(obj + 0x36)
	if memory.read_u32(ctl + 0x628) != 0:
		a7 = entry_a7 - 0x18
		leyte_sink_with_cached_chain(obj, 0, 0, 0)
	else:
		a7 = entry_a7 - 0x20
		translate_int3_vecs(C68kScript.s32(memory.read_s16(obj + 0x38) + memory.read_s16(obj + 0x3c)), memory.read_u32(obj + 0x46),
				memory.read_u32(obj + 0x46), C68kScript.s32(C68kScript.lsl32(memory.read_u32(ctl + 0x624), scale)), 0,
				C68kScript.s32(C68kScript.lsl32(memory.read_u32(ctl + 0x62c), scale)))
	a7 = entry_a7
	memory.write_u8(ctl + 0x619, 0)
	memory.write_u32(ctl + 0x624, 0)
	memory.write_u32(ctl + 0x628, 0)
	memory.write_u32(ctl + 0x62c, 0)

## 0x17dfa (Leyte only): a sinking ship's pre-render hook (controller definition +0xe, called (ctl, phase) by
## transform_and_project_object): a pending pose (byte +0x619) is applied (leyte_apply_pending_pose, 8 below). Returns 0.
func leyte_pending_pose_pre_render(ctl: int, _phase: int = 0) -> int:
	assert(memory != null, "leyte_pending_pose_pre_render requires original global memory")
	if memory.read_u8(ctl + 0x619) != 0:
		var entry_a7: int = a7
		a7 = entry_a7 - 8
		leyte_apply_pending_pose(ctl)
		a7 = entry_a7
	return 0

## 0x15bae (1.0.3's point_over_face_xz 0x132a8, the same 42 instructions with Leyte's position x +4 / z +0xc and frame:
## link a6 -0x708 and four registers, body 0x71c below): is the record's object over the face (xz)? The origin is its x /
## z shifted by word rec +0xc (asr by its negation when negative, else asl); the face is projected into the frame at a6 -
## 0x708 (project_face_vertices_xz(rec +4 model, word rec +0xe, face, ...), 0x1c below) and tested at (0, 0)
## (point_in_polygon_xz, 0x30 below), whose D0 is returned.
func point_over_face_xz(rec: int, face: int) -> int:
	assert(memory != null, "point_over_face_xz requires original global memory")
	assert(a7 != 0, "point_over_face_xz needs a7 (its frame holds the projected vertices)")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x71c
	var projected: int = entry_a7 - 4 - 0x708
	var obj: int = memory.read_u32(rec)
	var shift: int = memory.read_s16(rec + 0xc)
	var origin_x: int
	var origin_z: int
	if shift < 0:
		origin_x = C68kScript.asr32(memory.read_u32(obj + 4), -shift)
		origin_z = C68kScript.asr32(memory.read_u32(obj + 0xc), -shift)
	else:
		origin_x = C68kScript.s32(C68kScript.lsl32(memory.read_u32(obj + 4), shift))
		origin_z = C68kScript.s32(C68kScript.lsl32(memory.read_u32(obj + 0xc), shift))
	a7 = body - 0x1c
	project_face_vertices_xz(memory.read_u32(rec + 4), memory.read_s16(rec + 0xe), face, origin_x, origin_z, projected)
	a7 = body - 0x30
	var inside: int = point_in_polygon_xz(0, 0, projected, memory.read_s16(face + 0x14), memory.read_u32(face + 0x18))
	a7 = entry_a7
	return inside

## 0x17e0e (Leyte only): a ship's collision hook (ship, other, record): with the other object's controller (+0) and a
## record, a pending pose of the ship's controller (byte +0x619) is applied (leyte_apply_pending_pose, 8 below). D0 is
## left as the record.
func leyte_ship_collision_hook(ship: int, other: int, record: int) -> int:
	assert(memory != null, "leyte_ship_collision_hook requires original global memory")
	var ctl: int = memory.read_u32(ship)
	if memory.read_u32(other) != 0 and record != 0 and memory.read_u8(ctl + 0x619) != 0:
		var entry_a7: int = a7
		a7 = entry_a7 - 8
		leyte_apply_pending_pose(ctl)
		a7 = entry_a7
	return record

## 0x17e54 (Leyte only; link a6 -4 and seven registers, body 0x24 below): a carrier's collision hook (carrier, other,
## record). With the other object's controller (+0) and a record: a pending pose of the carrier's controller is applied (8
## below); its deck faces are found once (face kind byte +0x17: 1 -> controller word +0x698, 3 -> +0x69a; no deck tag ->
## overlay_printf "Can't find deck tag on carrier", 8 below) and the deck height kept (+0x696: the first deck vertex's y
## asr the carrier's scale word +0x36). When the carrier's tilt (its controller word +0x20, folded, abs; 8 / 0xc below) is
## within 0x4ff and it is over the deck (point_over_face_xz, 0xc below): for a kind <= 7 (its controller's definition
## type), with the carrier moving (word +0x32) outside a replay (0x21058), the carrier's motion is recorded in the other's
## controller (+0x632 = cos(folded heading difference) * word +0x676 asr 12, 8 / 0xc below; +0x636 / +0x638 = words +0xa /
## +0x12, +0x63a = (+0x22 - +0x28) & 0x3fff); the other's ground (+0x2e) becomes the deck height and, live, its surface
## byte +0x2c the deck face's +0x16. A kind <= 7 over the kind-3 face gets byte +0x64d = 1. With a positive deck height the
## pair (carrier, other) joins the carried pairs (0x21048, count word 0x21046, capacity word 0x21044 grown by 4:
## heap_alloc 8 below, heap_realloc_grow_only 0xc below).
func leyte_carrier_deck_hook(carrier: int, other: int, record: int) -> int:
	assert(memory != null, "leyte_carrier_deck_hook requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x24
	var ctl: int = memory.read_u32(carrier)
	var octl: int = memory.read_u32(other)
	if octl == 0 or record == 0:
		return record
	if memory.read_u8(ctl + 0x619) != 0:
		a7 = body - 8
		leyte_apply_pending_pose(ctl)
		a7 = entry_a7
	if memory.read_s16(ctl + 0x698) == -1:
		var face: int = memory.read_u32(carrier + 0x4a)
		for _i in memory.read_u16(carrier + 0x3c):
			var kind_byte: int = memory.read_u8(face + 0x17)
			var index: int = C68kScript.u16(C68kScript.asr32(C68kScript.s32(face - memory.read_u32(carrier + 0x4a)), 5))
			if kind_byte == 1:
				memory.write_u16(ctl + 0x698, index)
			if kind_byte == 3:
				memory.write_u16(ctl + 0x69a, index)
			face = C68kScript.u32(face + 0x20)
		var first: int = C68kScript.u32(memory.read_s16(ctl + 0x698) * 0x20 + memory.read_u32(carrier + 0x4a))
		if memory.read_s16(ctl + 0x698) == -1:
			a7 = body - 8
			overlay_printf(0x17e34, 0, 0, 0, 0, 0)   # only the format is pushed: the rest is whatever the stack holds
			a7 = entry_a7
		var v: int = C68kScript.u32(memory.read_u32(carrier + 0x46) + memory.read_s16(memory.read_u32(first + 0x18)) * 12)
		memory.write_u16(ctl + 0x696, C68kScript.u16(C68kScript.asr32(memory.read_u32(v + 4), memory.read_s16(carrier + 0x36))))
	var deck: int = C68kScript.u32(memory.read_s16(ctl + 0x698) * 0x20 + memory.read_u32(carrier + 0x4a))
	a7 = body - 8
	var folded: int = C68kScript.s16(fold_angle_pm_0x2000(memory.read_s16(ctl + 0x20)))
	a7 = body - 0xc
	var turned: int = abs_int(folded)
	a7 = entry_a7
	if turned <= 0x4ff:
		a7 = body - 0xc
		var over: int = point_over_face_xz(record, deck)
		a7 = entry_a7
		if over != 0:
			var kind: int = memory.read_s16(memory.read_u32(octl + 4))
			if memory.read_u16(ctl + 0x32) != 0 and memory.read_u16(0x21058) == 0 and kind <= 7:
				a7 = body - 8
				var diff: int = fold_angle_pm_0x2000(C68kScript.s32(memory.read_s16(ctl + 0x22) - memory.read_s16(octl + 0x22)))
				a7 = body - 0xc
				var c: int = cos_fixed14(diff & 0x3fff)
				a7 = entry_a7
				memory.write_u32(octl + 0x632, C68kScript.u32(C68kScript.asr32(C68kScript.s16(c) * memory.read_s16(ctl + 0x676), 12)))
				memory.write_u16(octl + 0x636, memory.read_u16(ctl + 0xa))
				memory.write_u16(octl + 0x638, memory.read_u16(ctl + 0x12))
				memory.write_u16(octl + 0x63a, (memory.read_u16(ctl + 0x22) - memory.read_u16(ctl + 0x28)) & 0x3fff)
			memory.write_u32(octl + 0x2e, C68kScript.u32(memory.read_s16(ctl + 0x696)))
			if memory.read_u16(0x21058) == 0:
				memory.write_u8(octl + 0x2c, memory.read_u8(deck + 0x16))
	if memory.read_s16(memory.read_u32(octl + 4)) <= 7 and memory.read_s16(ctl + 0x69a) != -1:
		a7 = body - 0xc
		var over2: int = point_over_face_xz(record, C68kScript.u32(memory.read_s16(ctl + 0x69a) * 0x20 + memory.read_u32(carrier + 0x4a)))
		a7 = entry_a7
		if over2 != 0:
			memory.write_u8(octl + 0x64d, 1)
	if memory.read_s16(ctl + 0x696) > 0:
		if memory.read_s16(0x21044) <= memory.read_s16(0x21046):
			memory.write_u16(0x21044, C68kScript.u16(memory.read_u16(0x21044) + 4))
			if memory.read_u32(0x21048) == 0:
				a7 = body - 8
				memory.write_u32(0x21048, C68kScript.u32(heap_alloc(C68kScript.s32(memory.read_s16(0x21044) * 8))))
				a7 = entry_a7
			else:
				a7 = body - 0xc
				memory.write_u32(0x21048, C68kScript.u32(heap_realloc_grow_only(memory.read_u32(0x21048), C68kScript.s32(memory.read_s16(0x21044) * 8))))
				a7 = entry_a7
		var at: int = C68kScript.u32(memory.read_u32(0x21048) + memory.read_s16(0x21046) * 8)
		memory.write_u32(at + 4, C68kScript.u32(other))
		memory.write_u32(at, C68kScript.u32(carrier))
		memory.write_u16(0x21046, C68kScript.u16(memory.read_u16(0x21046) + 1))
	return record

## 0x13d60 (1.0.3's spawn_effect 0x11c38, on Leyte's addresses: the controller at +0 for +8; Leyte tests the debris loop's
## count first, the same three calls, and pushes a fourth argument, 0, that spawn_debris_fragment does not read).
## Effect spawn: live play (word 0x21058 == 0) logs the call in the 16-entry ring 0x2c69a + 24 * index (index word
## 0x2c698); while replaying only state -1 or kind 1 continue, else 0. Kind != 1 in view modes <= 2 or 8 sets the
## explosion sound timers by compute_camera_abs_metric (<= 9999 -> word 0x21034 = 0x1e, else <= 39999 -> word 0x21032 =
## 0xf). Spawns effect_templates[kind] (0x2c81a) at (x, y, z) with spawn_mission_pool_object (null -> 0), stores the
## arguments in its controller (+0x32, +0x34, +0x36, +0x39, +0x38), calls effect_init_table[kind] (0x205bc) on the object
## and, with debris, spawn_debris_fragment three times; returns the controller. Frame link.w a6,#0 + nine saved
## registers: the pool spawn is entered 0x4c below, the initialiser 0x30, each debris call 0x3c.
func spawn_effect(kind: int, word_34: int, word_36: int, byte_38: int, debris: int, x: int, y: int, z: int) -> int:
	assert(memory != null, "spawn_effect requires original global memory")
	var entry_a7: int = a7
	var entry: int
	var tmp: int
	var ctl: int
	var left: int

	kind = C68kScript.s32(kind)
	if memory.read_s16(0x21058) == 0:
		entry = 0x2c69a + memory.read_s16(0x2c698) * 0x18
		memory.write_u8(entry, C68kScript.u8(kind))
		memory.write_u16(entry + 4, C68kScript.u16(word_34))
		memory.write_u16(entry + 6, C68kScript.u16(word_36))
		memory.write_u8(entry + 2, C68kScript.u8(debris))
		memory.write_u8(entry + 1, C68kScript.u8(byte_38))
		memory.write_u32(entry + 8, C68kScript.u32(x))
		memory.write_u32(entry + 0xc, C68kScript.u32(y))
		memory.write_u32(entry + 0x10, C68kScript.u32(z))
		memory.write_u32(entry + 0x14, memory.read_u32(0x21020))
		memory.write_u16(0x2c698, (memory.read_u16(0x2c698) + 1) & 0xf)
	elif memory.read_s16(0x21058) != -1 and kind != 1:
		return 0
	if kind != 1 and (memory.read_s16(0x23d88) <= 2 or memory.read_s16(0x23d88) == 8):
		tmp = compute_camera_abs_metric(x, y, z)
		if tmp <= 9999:
			memory.write_u16(0x21034, 0x1e)
		elif tmp <= 39999:
			memory.write_u16(0x21032, 0xf)
	a7 = entry_a7 - 0x4c
	tmp = spawn_mission_pool_object(memory.read_u32(0x2c81a + kind * 4), x, y, z, 0, 0, 0, 0)
	a7 = entry_a7
	if tmp == 0:
		return 0
	ctl = memory.read_u32(tmp)
	memory.write_u8(ctl + 0x32, C68kScript.u8(kind))
	memory.write_u16(ctl + 0x34, C68kScript.u16(word_34))
	memory.write_u16(ctl + 0x36, C68kScript.u16(word_36))
	memory.write_u8(ctl + 0x39, C68kScript.u8(debris))
	memory.write_u8(ctl + 0x38, C68kScript.u8(byte_38))
	a7 = entry_a7 - 0x30
	_jsr(memory.read_u32(0x205bc + kind * 4), [tmp])
	a7 = entry_a7
	if debris != 0:
		left = 3
		while true:
			left = left - 1
			if left == -1:
				break
			a7 = entry_a7 - 0x3c
			spawn_debris_fragment(x, y, z, 0)
			a7 = entry_a7
	return ctl

## 0x1b582 (1.0.3's spawn_debris_fragment 0x173de with the controller at +0): spawns the debris template (0x2cc10) at (x,
## y, z); its controller gets +0x32 = frame + 0x2d, velocities +8 / +0xc / +0x10 = (rng & 0xff) - 0x7f, word +0x36 = word
## +0xe asl 4 and angles +0x20 / +0x22 / +0x24 = (rng & 0xff) asl 6 (words). Returns the controller or 0. Frame link.w
## a6,#0 + three saved registers: the pool spawn is entered 0x34 below. (Leyte's spawn_effect pushes a fourth argument,
## not read.)
func spawn_debris_fragment(x: int, y: int, z: int, _unused: int = 0) -> int:
	assert(memory != null, "spawn_debris_fragment requires original global memory")
	var entry_a7: int = a7
	var obj: int
	var ctl: int

	a7 = entry_a7 - 0x34
	obj = spawn_mission_pool_object(memory.read_u32(0x2cc10), x, y, z, 0, 0, 0, 0)
	a7 = entry_a7
	if obj == 0:
		return 0
	ctl = memory.read_u32(obj)
	memory.write_u32(ctl + 0x32, C68kScript.u32(memory.read_u32(0x21020) + 0x2d))
	memory.write_u32(ctl + 8, C68kScript.u32((overlay_rng_next() & 0xff) - 0x7f))
	memory.write_u32(ctl + 0xc, C68kScript.u32((overlay_rng_next() & 0xff) - 0x7f))
	memory.write_u32(ctl + 0x10, C68kScript.u32((overlay_rng_next() & 0xff) - 0x7f))
	memory.write_u16(ctl + 0x36, C68kScript.u16(memory.read_u16(ctl + 0xe) << 4))
	memory.write_u16(ctl + 0x20, C68kScript.u16((overlay_rng_next() & 0xff) << 6))
	memory.write_u16(ctl + 0x22, C68kScript.u16((overlay_rng_next() & 0xff) << 6))
	memory.write_u16(ctl + 0x24, C68kScript.u16((overlay_rng_next() & 0xff) << 6))
	return ctl

## 0x3e8 (1.0.3's any_mission_kills_recorded 0x314; Leyte's kill-count words are host +0xbe .. +0xd4, 1.0.3's +0xba ..
## +0xd0): 1 when any of the twelve is non-zero, else 0.
func any_mission_kills_recorded() -> int:
	assert(memory != null, "any_mission_kills_recorded requires original global memory")
	var world: int = memory.read_u32(0x207fc)
	var last: int = C68kScript.u32(world + 0xd4)
	var a0: int = C68kScript.u32(world + 0xbe)
	while true:
		if memory.read_u16(a0) != 0:
			return 1
		a0 = C68kScript.u32(a0 + 2)
		if not (C68kScript.s32(last) >= C68kScript.s32(a0)):
			return 0
	return 0

## 0x2a5a (Leyte's finalize_attitude_matrix_3ang_alt, 1.0.3's 0x2408 rewritten: no rotation bases and products, the
## matrix written in closed form from the sines / cosines load_attitude_angle_sincos leaves - s1 / c1 0x23d2c / 0x23d2a,
## s2 / c2 0x23d28 / 0x23d26, s3 / c3 0x23d24 / 0x23d22). With a = s1 s2 >> 12 and b = s1 c2 >> 12 (words): out = [c2 c3 -
## a s3, a c3 + c2 s3, c1 s2 ; -c1 s3, c1 c3, -s1 ; -c3 s2 - b s3, b c3 - s2 s3, c1 c2], each product sum asr 12 (muls.w,
## word results).
func finalize_attitude_matrix_3ang_alt(out: int) -> void:
	assert(memory != null, "finalize_attitude_matrix_3ang_alt requires original global memory")
	var s1: int = memory.read_s16(0x23d2c)
	var c1: int = memory.read_s16(0x23d2a)
	var s2: int = memory.read_s16(0x23d28)
	var c2: int = memory.read_s16(0x23d26)
	var s3: int = memory.read_s16(0x23d24)
	var c3: int = memory.read_s16(0x23d22)
	var a: int = C68kScript.s16(C68kScript.asr32(s1 * s2, 12))
	var b: int = C68kScript.s16(C68kScript.asr32(s1 * c2, 12))
	var m: Array = [
		C68kScript.asr32(C68kScript.s32(c2 * c3 - a * s3), 12),
		C68kScript.asr32(C68kScript.s32(a * c3 + c2 * s3), 12),
		C68kScript.asr32(c1 * s2, 12),
		C68kScript.asr32(C68kScript.s32(-(c1 * s3)), 12),
		C68kScript.asr32(c1 * c3, 12),
		-s1,
		C68kScript.asr32(C68kScript.s32(-(c3 * s2) - b * s3), 12),
		C68kScript.asr32(C68kScript.s32(b * c3 - s2 * s3), 12),
		C68kScript.asr32(c1 * c2, 12)]
	for k in 9:
		memory.write_u16(out + 2 * k, C68kScript.u16(m[k]))

## 0x2b52 (1.0.3's build_attitude_matrix_3ang_alt 0x2444, the same 11 instructions): load_attitude_angle_sincos(angle1,
## angle2, angle3, 0), then finalize_attitude_matrix_3ang_alt(out) (entered 0x1c below: link 4, four arguments + out, bsr).
func build_attitude_matrix_3ang_alt(angle1: int, angle2: int, angle3: int, out: int) -> void:
	assert(memory != null, "build_attitude_matrix_3ang_alt requires original global memory")
	load_attitude_angle_sincos(angle1, angle2, angle3, 0)
	var entry_a7: int = a7
	a7 = entry_a7 - 0x1c
	finalize_attitude_matrix_3ang_alt(out)
	a7 = entry_a7

## The forward vector (0, 0, 0x4000) turned by a controller's attitude words +0x20 / +0x22 / +0x24 (build_attitude_matrix_3ang
## into a6 - 0x1e, build_depth below the body; transform_vecs12_by_matrix3 0x24 below), read back as words (inlined twice in
## update_tracking_camera; the controller layout is unchanged in Leyte).
func _tracking_forward(ctl: int, a6: int, body: int, build_depth: int) -> Array:
	var entry_a7: int = a7
	memory.write_u16(a6 - 0xa, 0)
	memory.write_u16(a6 - 6, 0)
	memory.write_u16(a6 - 2, 0x4000)
	a7 = body - build_depth
	build_attitude_matrix_3ang(memory.read_s16(ctl + 0x20), memory.read_s16(ctl + 0x22), memory.read_s16(ctl + 0x24), a6 - 0x1e)
	a7 = body - 0x24
	transform_vecs12_by_matrix3(1, a6 - 0xc, a6 - 0xc, a6 - 0x1e)
	a7 = entry_a7
	return [memory.read_s16(a6 - 0xa), memory.read_s16(a6 - 6), memory.read_s16(a6 - 2)]

## 0x9d52 (1.0.3's update_tracking_camera 0x8c10, the same 249 instructions; held back only because its displacement 0x20
## pairs two ways: the objects' z (+0x20 -> +0xc) and the controllers' first attitude word (+0x20, unchanged, in
## _tracking_forward). Leyte's layout otherwise: positions +4 / +8 / +0xc, the shot's start frame +0x42, the player
## controller's word +0x698 (for +0x684), host +0xd8 (for +0xd4); addresses through the map. The replay / end-sequence
## camera, as 1.0.3: looking at a live player shot, else at the midpoint between the player and a designated or held
## aircraft within 0x9ab0 (behind the player or, when the aircraft heads within 0x1000 of the player and is ahead, behind
## it), else at the player with the camera gliding; replay_frames_left (0x21056) counts down to state 3 and the outcome.
func update_tracking_camera() -> int:
	assert(memory != null, "update_tracking_camera requires original global memory")
	assert(a7 != 0, "update_tracking_camera needs a7 (its frame holds the forward vector)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x58
	var result: int = 0
	var look_obj: int = -1
	var tracked: bool = false
	var t: Array = [0, 0, 0]
	var shot: int = memory.read_u32(0x21068)
	if shot != 0 and memory.read_s32(memory.read_u32(shot) + 0x42) < memory.read_s32(0x21020):
		look_obj = memory.read_u32(shot)
	elif (memory.read_u32(0x21070) != 0 or memory.read_u32(0x2b258) != 0) and memory.read_s16(0x21058) != 3:
		if memory.read_u32(0x2b258) == 0:
			memory.write_u32(0x2106c, memory.read_u32(0x21070))
			memory.write_u32(0x2b258, 0x14)
		var follow: int = memory.read_u32(0x2106c)
		if follow != 0:
			var player_obj: int = memory.read_u32(memory.read_u32(0x20b28))
			var target: int = memory.read_u32(follow)
			if target != 0:
				a7 = body - 0x1c
				var dist: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(player_obj + 0x4), memory.read_s32(player_obj + 0x8), memory.read_s32(player_obj + 0xc), memory.read_s32(target + 0x4), memory.read_s32(target + 0x8), memory.read_s32(target + 0xc)))
				a7 = entry_a7
				if dist <= 0x9ab0:
					tracked = true
					for k in 3:
						var m: int = C68kScript.s32(memory.read_u32(target + 0x4 + 4 * k) + memory.read_u32(player_obj + 0x4 + 4 * k))
						t[k] = C68kScript.u32((m + 1 if m < 0 else m) >> 1)
					var shift: int = 0
					while (C68kScript.u32(dist) & 0xffff8000) != 0:
						shift = C68kScript.s16(shift + 1)
						dist = C68kScript.asr32(dist, 1)
					var player_ctl: int = memory.read_u32(0x20b28)
					a7 = body - 8
					var turn: int = fold_angle_pm_0x2000(C68kScript.s32(memory.read_s16(follow + 0x22) - memory.read_s16(player_ctl + 0x22)))
					a7 = entry_a7
					var anchor: int = player_obj
					if memory.read_u32(0x21070) != 0 and abs_int(turn) <= 0x1000:
						var f: Array = _tracking_forward(player_ctl, a6, body, 0x14)
						a7 = body - 0x1c
						var player_ahead: int = C68kScript.s32(approx_dist3_xyz(C68kScript.s32(f[0] + memory.read_u32(player_obj + 0x4)), C68kScript.s32(f[1] + memory.read_u32(player_obj + 0x8)), C68kScript.s32(f[2] + memory.read_u32(player_obj + 0xc)), memory.read_s32(target + 0x4), memory.read_s32(target + 0x8), memory.read_s32(target + 0xc)))
						a7 = entry_a7
						var g: Array = _tracking_forward(memory.read_u32(0x2106c), a6, body, 0x2c)
						a7 = body - 0x2c
						var target_ahead: int = C68kScript.s32(approx_dist3_xyz(C68kScript.s32(g[0] + memory.read_u32(target + 0x4)), C68kScript.s32(g[1] + memory.read_u32(target + 0x8)), C68kScript.s32(g[2] + memory.read_u32(target + 0xc)), memory.read_s32(player_obj + 0x4), memory.read_s32(player_obj + 0x8), memory.read_s32(player_obj + 0xc)))
						a7 = entry_a7
						if target_ahead < player_ahead:
							anchor = target
					a7 = body - 0x18
					place_camera_behind_object(anchor, t[0], t[1], t[2], shift)
					a7 = entry_a7
					if memory.read_u32(0x21070) != memory.read_u32(0x2106c):
						memory.write_u32(0x2b258, C68kScript.u32(memory.read_u32(0x2b258) - 1))
	if not tracked:
		if look_obj == -1:
			look_obj = memory.read_u32(memory.read_u32(0x20b28))
		t = [memory.read_u32(look_obj + 0x4), memory.read_u32(look_obj + 0x8), memory.read_u32(look_obj + 0xc)]
		if memory.read_s16(0x21058) != 3:
			if memory.read_u32(0x2106c) != 0:
				var frames: int = C68kScript.s32(memory.read_s16(0x21056) - 0x1e)
				assert(frames != 0, "update_tracking_camera: divs.l by zero traps")
				memory.write_u32(0x23d44 + 8, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(0x2b25c + 8) - memory.read_u32(0x23d68)), frames)))
				memory.write_u32(0x23d44 + 4, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(0x2b25c + 4) - memory.read_u32(0x23d64)), frames)))
				memory.write_u32(0x23d44, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(0x2b25c) - memory.read_u32(0x23d60)), frames)))
				memory.write_u32(0x2106c, 0)
				memory.write_u32(0x2b258, 0)
			memory.write_u32(0x23d68, C68kScript.u32(memory.read_u32(0x23d68) + memory.read_u32(0x23d44 + 8)))
			memory.write_u32(0x23d64, C68kScript.u32(memory.read_u32(0x23d64) + memory.read_u32(0x23d44 + 4)))
			memory.write_u32(0x23d60, C68kScript.u32(memory.read_u32(0x23d60) + memory.read_u32(0x23d44)))
	memory.write_u16(0x21056, C68kScript.u16(memory.read_u16(0x21056) - 1))
	if memory.read_s16(0x21056) <= 0x1e:
		memory.write_u16(0x21058, 3)
		if memory.read_u16(0x21056) == 0:
			var host: int = memory.read_u32(0x207fc)
			if memory.read_s16(host + 0x98) != 2:
				if (memory.read_u8(memory.read_u32(0x20b28) + 0x698) & 4) == 0:
					result = -1
				elif memory.read_u32(0x21064) == 0:
					memory.write_u16(host + 0xd8, 1)
					result = 1
				else:
					a7 = body - 4
					result = C68kScript.s16(resolve_bailout_outcome())
					a7 = entry_a7
			memory.write_u16(memory.read_u32(0x207fc) + 0x98, 0)
	a7 = body - 0x10
	turn_camera_toward_point(t[0], t[1], t[2])
	a7 = entry_a7
	return C68kScript.s16(result)

## A replay camera position: pose ring entry + the offset (vx, vy, vz) turned by the entry's heading word +0xe
## (build_attitude_matrix_3ang(0, heading, 0) 0x14 below, transformed in the frame - vector a6 - 0xc, matrix a6 - 0x1e -
## 0x24 below; inlined twice in begin_replay_view, the controller's pose ring unchanged in Leyte).
func _replay_offset(pose: int, a6: int, body: int, vx: int, vy: int, vz: int) -> Array:
	var entry_a7: int = a7
	memory.write_u16(a6 - 6, C68kScript.u16(vy))
	memory.write_u16(a6 - 0xa, C68kScript.u16(vx))
	memory.write_u16(a6 - 2, C68kScript.u16(vz))
	a7 = body - 0x14
	build_attitude_matrix_3ang(0, memory.read_s16(pose + 0xe), 0, a6 - 0x1e)
	a7 = body - 0x24
	transform_vecs12_by_matrix3(1, a6 - 0xc, a6 - 0xc, a6 - 0x1e)
	a7 = entry_a7
	return [C68kScript.u32(memory.read_s16(a6 - 0xa) + memory.read_u32(pose)), C68kScript.u32(memory.read_s16(a6 - 6) + memory.read_u32(pose + 4)), C68kScript.u32(memory.read_s16(a6 - 2) + memory.read_u32(pose + 8))]

## 0xa116 (1.0.3's begin_replay_view 0x8fd4, the same 144 instructions; held back only by the ambiguous displacement 4:
## the player object's flags long +0x14 - its byte +0x17 bit 6, contact-tracked - for 1.0.3's +4 / +7, while the pose ring
## entries keep their +4). Controller word +0x69c (for +0x688); addresses through the map. Start the instant replay, as
## 1.0.3: view mode 8, the player untracked, the clip bounds, the camera 1000 behind the current pose entry and gliding in
## 75 steps to the previous one offset to the side, the replay counters, the follow target designated, all sound stopped.
func begin_replay_view() -> void:
	assert(memory != null, "begin_replay_view requires original global memory")
	assert(a7 != 0, "begin_replay_view needs a7 (its frame holds the offset and the matrix)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x38
	memory.write_u16(0x23d88, 8)
	var ctl: int = memory.read_u32(0x20b28)
	var obj: int = memory.read_u32(ctl)
	if (memory.read_u8(obj + 0x17) & 0x40) != 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbf)
		a7 = body - 8
		drop_contact_records_for_object(obj)
		a7 = entry_a7
	a7 = body - 4
	update_view_clip_bounds()
	a7 = entry_a7
	ctl = memory.read_u32(0x20b28)
	var index: int = memory.read_s16(ctl + 0x612)
	var cam: Array = _replay_offset(C68kScript.u32(ctl + 0x36 + index * 0x14), a6, body, 0, 0, -1000)
	memory.write_u32(0x23d68, cam[0])
	memory.write_u32(0x23d64, cam[1])
	memory.write_u32(0x23d60, cam[2])
	ctl = memory.read_u32(0x20b28)
	var prev: int = C68kScript.s16(memory.read_s16(ctl + 0x612) - 1)
	if prev < 0:
		prev = 0x4a
	var side: int = -0x513 if memory.read_s16(0x21054) == 1 else 0x513
	var goal: Array = _replay_offset(C68kScript.u32(ctl + 0x36 + prev * 0x14), a6, body, -0x2ee, 0x183, side)
	memory.write_u32(0x2b25c + 8, goal[0])
	memory.write_u32(0x2b25c + 4, goal[1])
	memory.write_u32(0x2b25c, goal[2])
	var floor_y: int = C68kScript.s32(memory.read_u32(memory.read_u32(0x20b28) + 0x2e) + 0x280)
	if floor_y > memory.read_s32(0x2b25c + 4):
		memory.write_u32(0x2b25c + 4, C68kScript.u32(floor_y))
	memory.write_u32(0x23d44 + 8, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(0x2b25c + 8) - memory.read_u32(0x23d68)), 0x4b)))
	memory.write_u32(0x23d44 + 4, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(0x2b25c + 4) - memory.read_u32(0x23d64)), 0x4b)))
	memory.write_u32(0x23d44, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(0x2b25c) - memory.read_u32(0x23d60)), 0x4b)))
	memory.write_u16(memory.read_u32(0x20b28) + 0x69c, 0)
	memory.write_u16(0x23d88, 8)
	memory.write_u16(0x2b254, 0xc001)
	memory.write_u16(0x2b256, 0xc001)
	memory.write_u16(0x21054, 0)
	memory.write_u16(0x21056, 0x69)
	memory.write_u16(0x21058, 1)
	memory.write_u16(memory.read_u32(0x207fc) + 0x98, 1)
	var follow: int = memory.read_u32(0x2106c)
	if follow != 0 and memory.read_u32(follow) != 0:
		memory.write_u32(0x21070, follow)
	memory.write_u32(0x2106c, 0)
	memory.write_u32(0x2b258, 0)
	a7 = body - 4
	stop_all_sound_channels()
	a7 = entry_a7

## 0x40c (Leyte's probe_nearest_facility_kind, 1.0.3's 0x334 rewritten; seven saved registers): the facility kind of the
## nearest object of the near list (0x20804, count word 0x20b26; objects with a controller at +0) to obj, by
## approx_dist3_xyz (positions +4 / +8 / +0xc), over these controller definition types: a carrier 0x1b (unless its deck
## face +0x698 is known and its kind-3 face +0x69a is not) within 0x671f -> 0x200; types 0x1c / 0x20 within 0x671f ->
## 0x100; types 0x18 / 0x19 within their range +0x18 -> 0x100 / 0x200. Nearer wins (signed); returns the kind (word,
## sign-extended), 0 when none.
func probe_nearest_facility_kind(obj: int) -> int:
	assert(memory != null, "probe_nearest_facility_kind requires original global memory")
	var best_dist: int = 0x7fffffff
	var kind: int = 0
	var slot: int = 0x20804
	var left: int = memory.read_u16(0x20b26)
	while left != 0:
		left -= 1
		var facility: int = memory.read_u32(slot)
		slot += 4
		var ctl: int = memory.read_u32(facility)
		if ctl == 0:
			continue
		var t: int = memory.read_s16(memory.read_u32(ctl + 4))
		if t != 0x1b and t != 0x1c and t != 0x20 and t != 0x19 and t != 0x18:
			continue
		if t == 0x1b and memory.read_s16(ctl + 0x698) != -1 and memory.read_s16(ctl + 0x69a) == -1:
			continue
		var dist: int = approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc),
				memory.read_s32(facility + 4), memory.read_s32(facility + 8), memory.read_s32(facility + 0xc))
		if t == 0x18 or t == 0x19:
			if dist >= memory.read_s32(facility + 0x18):
				continue
		elif dist > 0x671f:
			continue
		if dist >= best_dist:
			continue
		best_dist = dist
		if t == 0x1c or t == 0x20 or t == 0x18:
			kind = 0x100
		else:
			kind = 0x200
	return C68kScript.s16(kind)

## 0xce0 (Leyte only; link a6 -0x720 and six registers, body 0x73c below): a scene visitor (ctx, node, face, path, depth)
## that collects the centroids of the type-0x80 faces (byte +9 & 0xf0) of nodes whose byte +1 is 0 into the cached table
## 0x20b38 (count word 0x20b34, capacity word 0x20b32 grown by 10 through heap_realloc_grow_only, 0xc below): the node's
## vertices placed through the path (transform_through_node_chain into the frame, a6 - 0x708, origins a6 - 0x718 / -0x71c /
## -0x720; 0x20 below), the face's vertices (word indices from +0xe, count byte +8) asr.l word node +0x18 averaged
## (divs.l, truncating; a zero count would trap) and 100 added to y. (1.0.3's nearest_type80_centroid_visitor kept only
## the nearest.)
func leyte_type80_centroid_visitor(_ctx: int, node: int, face: int, path: int, depth: int) -> void:
	assert(memory != null, "leyte_type80_centroid_visitor requires original global memory")
	assert(a7 != 0, "leyte_type80_centroid_visitor needs a7 (its frame holds the vertex buffer)")
	if (memory.read_u8(face + 9) & 0xf0) != 0x80 or memory.read_u8(node + 1) != 0:
		return
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x73c
	var verts: int = a6 - 0x708
	a7 = body - 0x20
	transform_through_node_chain(path, C68kScript.s16(depth), node, a6 - 0x718, a6 - 0x71c, a6 - 0x720, verts)
	a7 = entry_a7
	var sums: Array = [0, 0, 0]
	var count: int = memory.read_u8(face + 8)
	var shift: int = memory.read_s16(node + 0x18)
	for i in count:
		var v: int = C68kScript.u32(verts + memory.read_s16(face + 0xe + 2 * i) * 0xc)
		for k in 3:
			sums[k] = C68kScript.s32(sums[k] + C68kScript.asr32(memory.read_u32(v + 4 * k), shift))
	assert(count != 0, "leyte_type80_centroid_visitor: divs.l by zero traps")
	var centroid: Array = [C68kScript.divs_trunc(sums[0], count), C68kScript.s32(C68kScript.divs_trunc(sums[1], count) + 100),
			C68kScript.divs_trunc(sums[2], count)]
	if memory.read_s16(0x20b32) == memory.read_s16(0x20b34):
		memory.write_u16(0x20b32, C68kScript.u16(memory.read_u16(0x20b32) + 10))
		a7 = body - 0xc
		memory.write_u32(0x20b38, C68kScript.u32(heap_realloc_grow_only(memory.read_u32(0x20b38), C68kScript.s32(memory.read_s16(0x20b32) * 12))))
		a7 = entry_a7
	var at: int = C68kScript.u32(memory.read_u32(0x20b38) + memory.read_s16(0x20b34) * 12)
	for k in 3:
		memory.write_u32(at + 4 * k, C68kScript.u32(centroid[k]))
	memory.write_u16(0x20b34, C68kScript.u16(memory.read_u16(0x20b34) + 1))

## 0xe52 (Leyte only; six saved registers): the centroid of an object's first type-0x80 face (surface byte +0x16) into
## out: none for an object hidden (+0x17 bit 1), for a face with +0x13 bit 1 or with a centroid sum y <= 0 (returns 0).
## A ship (controller definition type 0x1b..0x22) applies its pending pose first (leyte_apply_pending_pose, 0x20 below).
## The face's vertices (word indices from +0x18, count word +0x14) asr word +0x36 are averaged (divs.l) and 100 added to y.
## Returns 1 with a centroid.
func leyte_object_type80_centroid(obj: int, out: int) -> int:
	assert(memory != null, "leyte_object_type80_centroid requires original global memory")
	if (memory.read_u8(obj + 0x17) & 2) != 0:
		return 0
	var ctl: int = memory.read_u32(obj)
	if ctl != 0:
		var t: int = memory.read_s16(memory.read_u32(ctl + 4))
		if t > 0x1a and t <= 0x22 and memory.read_u8(ctl + 0x619) != 0:
			var entry_a7: int = a7
			a7 = entry_a7 - 0x20
			leyte_apply_pending_pose(ctl)
			a7 = entry_a7
	var face: int = memory.read_u32(obj + 0x4a)
	var f: int = 0
	while f < memory.read_s16(obj + 0x3c):
		if memory.read_u8(face + 0x16) == 0x80:
			if (memory.read_u8(face + 0x13) & 2) != 0:
				return 0
			memory.write_u32(out + 8, 0)
			memory.write_u32(out + 4, 0)
			memory.write_u32(out, 0)
			var count: int = memory.read_s16(face + 0x14)
			var shift: int = memory.read_s16(obj + 0x36)
			for i in maxi(count, 0):
				var v: int = C68kScript.u32(memory.read_u32(obj + 0x46) + memory.read_s16(memory.read_u32(face + 0x18) + 2 * i) * 12)
				for k in 3:
					memory.write_u32(out + 4 * k, C68kScript.u32(memory.read_s32(out + 4 * k) + C68kScript.asr32(memory.read_u32(v + 4 * k), shift)))
			if memory.read_s32(out + 4) <= 0:
				return 0
			memory.write_u32(out, C68kScript.u32(C68kScript.divs_trunc(memory.read_s32(out), count)))
			memory.write_u32(out + 4, C68kScript.u32(C68kScript.divs_trunc(memory.read_s32(out + 4), count) + 100))
			memory.write_u32(out + 8, C68kScript.u32(C68kScript.divs_trunc(memory.read_s32(out + 8), count)))
			return 1
		face = C68kScript.u32(face + 0x20)
		f += 1
	return 0

## 0xf7c (Leyte's point_near_camera_or_type80_object, 1.0.3's 0xbca rewritten; link a6 -0xc and seven registers, body 0x2c
## below): the nearest "type 0x80" point to the camera goes into point: the mission scene's type-0x80 face centroids,
## cached once (0x20b38, capacity 10 from heap_alloc 8 below, filled by for_each_mission_scene_object with
## leyte_type80_centroid_visitor, 0x10 below), and the centroid of each near object's first type-0x80 face (0x20804, count
## 0x20b24; leyte_object_type80_centroid 0xc below), whose object is remembered in 0x20b3c (cleared first). Nearer by
## compute_camera_abs_metric (signed, from 1000000000); point is left as it was when nothing is nearer. Returns 1 when the
## best metric is <= 0x4073f, else 0.
func point_near_camera_or_type80_object(point: int) -> int:
	assert(memory != null, "point_near_camera_or_type80_object requires original global memory")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x2c
	if memory.read_u32(0x20b38) == 0:
		memory.write_u16(0x20b32, 10)
		a7 = body - 8
		memory.write_u32(0x20b38, C68kScript.u32(heap_alloc(C68kScript.s32(memory.read_s16(0x20b32) * 12))))
		a7 = body - 0x10
		for_each_mission_scene_object(0xce0, point)
		a7 = entry_a7
	var best: int = 1000000000
	var table: int = memory.read_u32(0x20b38)
	var i: int = 0
	while i < memory.read_s16(0x20b34):
		var e: int = C68kScript.u32(table + 12 * i)
		var m: int = compute_camera_abs_metric(memory.read_s32(e), memory.read_s32(e + 4), memory.read_s32(e + 8))
		if m < best:
			for k in 3:
				memory.write_u32(point + 4 * k, memory.read_u32(e + 4 * k))
			best = m
		i += 1
	memory.write_u32(0x20b3c, 0)
	var tmp: int = entry_a7 - 4 - 0xc
	var j: int = 0
	while j < memory.read_s32(0x20b24):
		var obj: int = memory.read_u32(0x20804 + 4 * j)
		a7 = body - 0xc
		var found: int = leyte_object_type80_centroid(obj, tmp)
		a7 = entry_a7
		if found != 0:
			var m2: int = compute_camera_abs_metric(memory.read_s32(tmp), memory.read_s32(tmp + 4), memory.read_s32(tmp + 8))
			if m2 < best:
				for k in 3:
					memory.write_u32(point + 4 * k, memory.read_u32(tmp + 4 * k))
				best = m2
				memory.write_u32(0x20b3c, obj)
		j = C68kScript.s16(j + 1)
	return 1 if best <= 0x4073f else 0

## 0x123be (1.0.3's enqueue_control_inputs_avg4 0x10538 on Leyte's controller layout: the ring index +0x650, the aileron /
## elevator / rudder slots +0x652 / +0x65a / +0x662, running sums +0x66a / +0x66c / +0x66e, outputs +0x674 / +0x670 / +0x672,
## the damage byte +0x699; the aileron limit is the controller definition's word +0x4c, 1.0.3's constant 0x140): control
## inputs into the 4-entry ring, clamped (aileron +-limit, elevator +-0x200, rudder +-0x40, by abs_int), the new values
## added, the next slot's (written 3 calls ago) subtracted, and the outputs sum / 4 toward zero. Nothing when the damage
## byte has bit 0.
func enqueue_control_inputs_avg4(ctl: int, rudder: int, elevator: int, aileron: int) -> void:
	assert(memory != null, "enqueue_control_inputs_avg4 requires original global memory")
	if (memory.read_u8(ctl + 0x699) & 1) != 0:
		return
	var limit: int = memory.read_s16(memory.read_u32(ctl + 4) + 0x4c)
	aileron = C68kScript.s16(aileron)
	if abs_int(aileron) > limit:
		aileron = C68kScript.s16(-limit) if aileron < 0 else limit
	rudder = C68kScript.s16(rudder)
	if abs_int(rudder) > 0x40:
		rudder = -0x40 if rudder < 0 else 0x40
	elevator = C68kScript.s16(elevator)
	if abs_int(elevator) > 0x200:
		elevator = -0x200 if elevator < 0 else 0x200
	var inputs := [[aileron, 0x652, 0x66a, 0x674], [elevator, 0x65a, 0x66c, 0x670], [rudder, 0x662, 0x66e, 0x672]]
	var ring: int = memory.read_s16(ctl + 0x650)
	for c in inputs:
		memory.write_u16(ctl + c[1] + ring * 2, C68kScript.u16(c[0]))
		memory.write_u16(ctl + c[2], C68kScript.u16(memory.read_u16(ctl + c[2]) + c[0]))
	ring = C68kScript.s16(ring + 1)
	if ring == 4:
		ring = 0
	for c in inputs:
		memory.write_u16(ctl + c[2], C68kScript.u16(memory.read_u16(ctl + c[2]) - memory.read_u16(ctl + c[1] + ring * 2)))
		var sum: int = memory.read_s16(ctl + c[2])
		if sum < 0:
			sum += 3
		memory.write_u16(ctl + c[3], C68kScript.u16(sum >> 2))
	memory.write_u16(ctl + 0x650, C68kScript.u16(ring))

## Replay visibility of one object (inlined twice in advance_frame_and_tick_controllers; Leyte's born +0x42 / dead +0x3e for
## 1.0.3's +0x40 / +0x3c, flags long +0x14 with byte +0x17): not yet born (born after the frame) or already dead (dead set
## and before the frame) -> hidden (+0x14 |= 0x40, 0x42 when dead); otherwise a hidden-by-flag one (+0x17 bit 1) loses
## 0x42, and one born this frame loses 0x40.
func _replay_visibility(obj: int, frame: int) -> void:
	var born: int = memory.read_s32(obj + 0x42)
	var dead: int = memory.read_s32(obj + 0x3e)
	if born > frame or (dead != 0 and dead < frame):
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | (0x42 if dead != 0 and dead < frame else 0x40))
	elif (memory.read_u8(obj + 0x17) & 2) != 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbd)
	elif born == frame:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbf)

## 0xa406 (1.0.3's advance_frame_and_tick_controllers 0x92c4 on Leyte's addresses; five saved registers, calls 0x18 below,
## ticks 0x1c): the next frame. The frame counter (0x21020) advances (a replay start, state 1 in 0x21058, steps back 0x4b
## frames and marks the effect log end). While replaying, the pool objects (0x20804, count word 0x20b26) and the shapes
## (type byte +0x28 == 2) of entity_array_1 (0x20b44, count word 0x20f2e) get their replay visibility, and the logged effects
## of this frame come back. Leyte then clears designated_entity_2 (0x21070) every frame. Every controller of
## controller_list (0x21bd4, count word 0x219b6) still holding an object runs its definition tick (+2) while the list is
## compacted (controllers appended by the ticks move down when some were dropped); live_controller_count (0x219b0) = the
## kept count; apply_controller_velocities.
func advance_frame_and_tick_controllers() -> void:
	assert(memory != null, "advance_frame_and_tick_controllers requires original global memory")
	var entry_a7: int = a7
	memory.write_u32(0x21020, C68kScript.u32(memory.read_u32(0x21020) + 1))
	if memory.read_s16(0x21058) == 1:
		memory.write_u32(0x21020, C68kScript.u32(memory.read_u32(0x21020) - 0x4b))
		a7 = entry_a7 - 0x18
		mark_effect_log_end()
		a7 = entry_a7
	var old: int = memory.read_s16(0x219b6)
	if memory.read_u16(0x21058) != 0:
		var frame: int = memory.read_s32(0x21020)
		for i in memory.read_u16(0x20b26):
			_replay_visibility(memory.read_u32(0x20804 + 4 * i), frame)
		for i in memory.read_u16(0x20f2e):
			var obj: int = memory.read_u32(0x20b44 + 4 * i)
			if memory.read_u8(obj + 0x28) == 2:
				_replay_visibility(obj, frame)
		a7 = entry_a7 - 0x18
		replay_logged_effects()
		a7 = entry_a7
	memory.write_u32(0x21070, 0)
	var dst: int = 0x21bd4
	var src: int = 0x21bd4
	for i in old & 0xffff:
		var ctl: int = memory.read_u32(src)
		src += 4
		if memory.read_u32(ctl) == 0:
			continue
		memory.write_u32(dst, ctl)
		dst += 4
		var tick: int = memory.read_u32(memory.read_u32(ctl + 4) + 2)
		if tick != 0:
			a7 = entry_a7 - 0x1c
			_jsr(tick, [ctl])
			a7 = entry_a7
	var kept: int = C68kScript.s16(C68kScript.asr32(dst - 0x21bd4, 2))
	if old > kept:
		if old < memory.read_s32(0x219b4):
			for i in (memory.read_u16(0x219b6) - old) & 0xffff:
				memory.write_u32(dst, memory.read_u32(src))
				dst += 4
				src += 4
		memory.write_u32(0x219b4, C68kScript.u32(C68kScript.asr32(dst - 0x21bd4, 2)))
	memory.write_u32(0x219b0, C68kScript.u32(kept))
	a7 = entry_a7 - 0x18
	apply_controller_velocities()
	a7 = entry_a7

## 0xa700 (1.0.3's play_game_sound 0x958c; Leyte decides the kind of play through a jump table over ids 1..100): play
## sound id on channel 1 unless a sound of the same or higher priority (lower id, channel1_sound 0x2b24c) is playing. Ids up
## to 99 need their record (*(host +0x296))[id]; id 100 plays the record at table +0x1c from offset 0x48c0 (a missing one
## only claims the channel). Ids 1..4, 6, 8, 9, 12 and - Leyte's addition - 14 play once; the others (<= 0, 5, 7, 10, 11,
## 13, 15..99, > 100) with the record's loop points (+0xc / +0x10). Host +0x2e6(1, record + 0x16, offset, size +4 - 0x172,
## loop a, loop b) (0x24 below); channel1_sound = id. Ids above 100 never load the record: A1 is the caller's (a1_in).
func play_game_sound(sound_id: int, a1_in: int = 0) -> void:
	assert(memory != null, "play_game_sound requires original global memory")
	var entry_a7: int = a7
	sound_id = C68kScript.s32(sound_id)
	var current: int = memory.read_s32(0x2b24c)
	if current != 0 and sound_id >= current:
		return
	var host: int = memory.read_u32(0x207fc)
	var rec: int = a1_in
	if sound_id <= 99:
		rec = memory.read_u32(C68kScript.u32(memory.read_u32(host + 0x296) + sound_id * 4))
		if rec == 0:
			return
	var args: Array
	if sound_id == 100:
		rec = memory.read_u32(memory.read_u32(host + 0x296) + 0x1c)
		if rec == 0:
			memory.write_u32(0x2b24c, C68kScript.u32(sound_id))
			return
		args = [1, C68kScript.u32(rec + 0x16), 0x48c0, C68kScript.u32(memory.read_u32(rec + 4) - 0x172), 0, 0]
	elif sound_id in [1, 2, 3, 4, 6, 8, 9, 12, 14]:
		args = [1, C68kScript.u32(rec + 0x16), 0, C68kScript.u32(memory.read_u32(rec + 4) - 0x172), 0, 0]
	else:
		args = [1, C68kScript.u32(rec + 0x16), 0, C68kScript.u32(memory.read_u32(rec + 4) - 0x172), memory.read_u32(rec + 0xc), memory.read_u32(rec + 0x10)]
	a7 = entry_a7 - 0x24
	_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2e6), args)
	a7 = entry_a7
	memory.write_u32(0x2b24c, C68kScript.u32(sound_id))

## A host sound routine (host_world + offset: Leyte's +0x2e6 play, +0x2ee rate, +0x2f2 status, +0x2fa stop) called from
## update_game_sounds (0x14 below its entry for one argument, 0x28 for six); returns its long result.
func _sound_host(offset: int, args: Array) -> int:
	var entry_a7: int = a7
	a7 = entry_a7 - (0x28 if args.size() == 6 else 0x14)
	var r = _jsr(memory.read_u32(memory.read_u32(0x207fc) + offset), args)
	a7 = entry_a7
	return C68kScript.u32(0 if r == null else int(r))

## update_game_sounds: a pending near (8, word 0x21034) / distant (9, word 0x21032) explosion plays and its timer is cleared.
func _play_pending_explosions() -> void:
	var entry_a7: int = a7
	for pair in [[0x21034, 8], [0x21032, 9]]:
		if memory.read_u16(pair[0]) != 0:
			a7 = entry_a7 - 0x14
			play_game_sound(pair[1])
			a7 = entry_a7
			memory.write_u16(pair[0], 0)

## update_game_sounds: play the sound when on, else stop it (0x14 below).
func _play_or_stop(on: bool, sound_id: int) -> void:
	var entry_a7: int = a7
	a7 = entry_a7 - 0x14
	if on:
		play_game_sound(sound_id)
	else:
		stop_game_sound(sound_id)
	a7 = entry_a7

## update_game_sounds for the views that follow an aircraft (0..2, 5, 7), on Leyte's controller layout (gear +0x68a, flaps
## +0x68c, damage byte +0x699, stalled byte +0x677, engine rate +0x678) and host routines. The one-shot sounds are now 100
## (word 0x21030), 14 (0x2102e: channel 1 freed first when it already holds 14, so it restarts), 1 (0x2102c), 2 (0x2102a),
## 3 (0x21024) and 4 (0x21026).
func _update_view_sounds(entry_a7: int, view: int) -> void:
	var ctl: int
	if view == 7:
		ctl = memory.read_u32(0x21070)
	else:
		ctl = memory.read_u32(0x20b28)
		if memory.read_u16(0x21034) != 0:
			memory.write_u16(0x21032, memory.read_u16(0x21034))
			memory.write_u16(0x21034, 0)
		var gear: int = abs_int(memory.read_s8(ctl + 0x68a))
		memory.write_u16(0x21038, 1 if gear != 0 and gear != 0x2d and (memory.read_u8(ctl + 0x699) & 0x20) == 0 else 0)
		var flaps: int = abs_int(memory.read_s8(ctl + 0x68c))
		memory.write_u16(0x21036, 1 if flaps != 0 and flaps != 0xf else 0)
	a7 = entry_a7
	_play_or_stop(memory.read_u16(0x2103c) != 0, 5)
	if memory.read_u16(0x21040) != 0:
		a7 = entry_a7 - 0x14
		play_game_sound(6)
		a7 = entry_a7
	_play_or_stop(memory.read_u16(0x21038) != 0 or memory.read_u16(0x21036) != 0, 7)
	_play_or_stop(memory.read_u16(0x2103a) != 0, 0xb)
	_play_or_stop(memory.read_u16(0x2103e) != 0, 0xc)
	_play_pending_explosions()
	a7 = entry_a7 - 0x14
	if memory.read_u16(0x21030) != 0:
		play_game_sound(100)
	if memory.read_u16(0x2102e) != 0:
		if memory.read_u32(0x2b24c) == 0xe:
			memory.write_u32(0x2b24c, 0)
		play_game_sound(0xe)
	for pair in [[0x2102c, 1], [0x2102a, 2], [0x21024, 3], [0x21026, 4]]:
		if memory.read_u16(pair[0]) != 0:
			play_game_sound(pair[1])
	a7 = entry_a7
	var channel1: int = memory.read_u32(0x2b24c)
	var host: int = memory.read_u32(0x207fc)
	if (channel1 == 0 or (channel1 != 1 and channel1 != 3 and channel1 != 4)) and memory.read_u16(host + 0x9c) != 0:
		var rate: int = memory.read_s32(ctl + 0x678)
		var choice: int
		var record: int
		var stalled: bool
		if view == 7:
			stalled = memory.read_u8(memory.read_u32(0x21070) + 0x677) != 0
		else:
			stalled = memory.read_u8(memory.read_u32(0x20b28) + 0x677) != 0
		if stalled:
			choice = 0
			record = memory.read_u32(memory.read_u32(host + 0x296))
			rate = C68kScript.s32(rate + 0x10000)
		elif rate > 0x2710:
			choice = 10
			record = memory.read_u32(memory.read_u32(host + 0x296) + 0x28)
			rate = C68kScript.s32(C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(rate, 2)), 3) + 0x4000)
		else:
			choice = -1
			record = 0
		if record != 0:
			if _sound_host(0x2f2, [0]) == 0 or C68kScript.u32(choice) != memory.read_u32(0x2b250):
				var size: int = memory.read_u32(record + 4)
				_sound_host(0x2e6, [0, C68kScript.u32(record + 0x16), 0, size, 0, size])
			_sound_host(0x2ee, [C68kScript.u32(rate)])
		elif _sound_host(0x2f2, [0]) != 0:
			_sound_host(0x2fa, [0])
		memory.write_u32(0x2b250, C68kScript.u32(choice))
	else:
		if _sound_host(0x2f2, [0]) != 0:
			_sound_host(0x2fa, [0])
		memory.write_u32(0x2b250, 0xffffffff)

## 0xa8ea (1.0.3's update_game_sounds 0x96d4 on Leyte's addresses; three saved registers): the per-frame sound update.
## Without sound (host word +0x9a) nothing plays; in views other than 0..2, 5, 7 and 8 all channels stop. Otherwise
## channel 1 idle (host +0x2f2(1)) clears channel1_sound. View 8 plays the pending explosion sounds and - Leyte - sound 14
## when its flag is up; the follow views run _update_view_sounds. The one-frame flags (now also 0x21024) are cleared and the
## explosion timers count down.
func update_game_sounds() -> void:
	assert(memory != null, "update_game_sounds requires original global memory")
	var entry_a7: int = a7
	var view: int = memory.read_s16(0x23d88)
	if memory.read_u16(memory.read_u32(0x207fc) + 0x9a) != 0:
		if view <= 2 or view == 5 or view == 7 or view == 8:
			if _sound_host(0x2f2, [1]) == 0:
				memory.write_u32(0x2b24c, 0)
			if view == 8 or view == 9:
				_play_pending_explosions()
				if memory.read_u16(0x2102e) != 0:
					a7 = entry_a7 - 0x14
					play_game_sound(0xe)
					a7 = entry_a7
			else:
				_update_view_sounds(entry_a7, view)
		else:
			a7 = entry_a7 - 0x10
			stop_all_sound_channels()
			a7 = entry_a7
	for flag in [0x21040, 0x2103c, 0x2103a, 0x2103e]:
		memory.write_u16(flag, 0)
	for timer in [0x21032, 0x21034]:
		if memory.read_u16(timer) != 0:
			memory.write_u16(timer, (memory.read_u16(timer) - 1) & 0xffff)
	for flag in [0x21030, 0x2102e, 0x2102c, 0x2102a, 0x21026, 0x21024]:
		memory.write_u16(flag, 0)

## 0x1ddbe (1.0.3's find_nearest_runway_approach_ends 0x19c3e; link a6 -0x7c and ten registers, body 0xa8 below), written
## from Leyte's listing on its layout: the aircraft's and objects' positions +4 / +8 / +0xc, the shape type byte +0x28, the
## faces +0x4a (word +0x3c of them, 0x20 bytes each, surface byte +0x16, count word +0x14, index list +0x18), the vertices
## +0x46 and the scale word +0x36. The object pool (0x20804, count words 0x20b24 / 0x20b26) then entity_array_1 (0x20b44,
## 0x20f2c / 0x20f2e) are walked from the end (the count tested first). For a shape near a friendly facility
## (probe_nearest_facility_kind 0x200, 8 below) nearer than the best object so far: each runway face (surface 0x70, four
## corners) has its corners (store_xyz_asr by the scale) put in the frame (a6 - 0xc / - 0x18 / - 0x24 / - 0x30) and the best
## object distance taken; when the second side (horizontal) is shorter than the first the corners rotate by one; the
## midpoints of corners 1-2 and 3-4 are measured from the aircraft and the nearer one, beating the best end, goes to
## *near_end and the other to *far_end. Returns 1 when nothing was found within 0x40740, else 0.
func find_nearest_runway_approach_ends(own: int, near_end: int, far_end: int) -> int:
	assert(memory != null, "find_nearest_runway_approach_ends requires original global memory")
	assert(a7 != 0, "find_nearest_runway_approach_ends needs a7 (its frame holds the corners)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0xa8
	var best_end: int = 0x7fffffff
	var best_obj: int = 0x7fffffff
	var ox: int = memory.read_s32(own + 4)
	var oy: int = memory.read_s32(own + 8)
	var oz: int = memory.read_s32(own + 0xc)
	var corners: Array = [a6 - 0xc, a6 - 0x18, a6 - 0x24, a6 - 0x30]
	var tmp: int = a6 - 0x3c
	var m12: int = a6 - 0x48
	var m34: int = a6 - 0x54
	for pass_index in 2:
		var list: int = 0x20804 if pass_index == 0 else 0x20b44
		var count_addr: int = 0x20b24 if pass_index == 0 else 0x20f2c
		var slot: int = C68kScript.u32(memory.read_u32(count_addr) * 4 + list)
		var count: int = memory.read_u16(count_addr + 2)
		while count != 0:
			count -= 1
			slot = C68kScript.u32(slot - 4)
			var cand: int = memory.read_u32(slot)
			if memory.read_u8(cand + 0x28) != 2:
				continue
			a7 = body - 8
			var kind: int = probe_nearest_facility_kind(cand)
			a7 = entry_a7
			if kind != 0x200:
				continue
			var obj_dist: int = approx_dist3_xyz(ox, oy, oz, memory.read_s32(cand + 4), memory.read_s32(cand + 8), memory.read_s32(cand + 0xc))
			if obj_dist >= best_obj:
				continue
			var face: int = memory.read_u32(cand + 0x4a)
			for _f in memory.read_u16(cand + 0x3c):
				if memory.read_u8(face + 0x16) == 0x70 and memory.read_s16(face + 0x14) == 4:
					var verts: int = memory.read_u32(cand + 0x46)
					var vidx: int = memory.read_u32(face + 0x18)
					best_obj = obj_dist
					var shift: int = memory.read_s16(cand + 0x36)
					for k in 4:
						var v: int = C68kScript.u32(verts + memory.read_s16(vidx + 2 * k) * 12)
						store_xyz_asr(memory.read_u32(v), memory.read_u32(v + 4), memory.read_u32(v + 8), shift, corners[k])
					var side12: int = approx_dist3_xyz(memory.read_s32(a6 - 0xc), 0, memory.read_s32(a6 - 0x4), memory.read_s32(a6 - 0x18), 0, memory.read_s32(a6 - 0x10))
					var side23: int = approx_dist3_xyz(memory.read_s32(a6 - 0x18), 0, memory.read_s32(a6 - 0x10), memory.read_s32(a6 - 0x24), 0, memory.read_s32(a6 - 0x1c))
					if side23 < side12:
						_copy_xyz(corners[0], tmp)
						_copy_xyz(corners[1], corners[0])
						_copy_xyz(corners[2], corners[1])
						_copy_xyz(corners[3], corners[2])
						_copy_xyz(tmp, corners[3])
					_runway_midpoint(a6 - 0xc, a6 - 0x18, m12)
					_runway_midpoint(a6 - 0x24, a6 - 0x30, m34)
					var near_dist: int = approx_dist3_xyz(ox, oy, oz, memory.read_s32(m12), memory.read_s32(m12 + 4), memory.read_s32(m12 + 8))
					var other: int = approx_dist3_xyz(ox, oy, oz, memory.read_s32(m34), memory.read_s32(m34 + 4), memory.read_s32(m34 + 8))
					if near_dist > other:
						near_dist = other
						_copy_xyz(m12, tmp)
						_copy_xyz(m34, m12)
						_copy_xyz(tmp, m34)
					if near_dist < best_end:
						best_end = near_dist
						_copy_xyz(m12, near_end)
						_copy_xyz(m34, far_end)
				face = C68kScript.u32(face + 0x20)
	a7 = entry_a7
	return 1 if best_end == 0x7fffffff or best_end > 0x40740 else 0

## 0x121c4 (1.0.3's build_relative_steer_sample 0x10340, the same 171 instructions with registers reallocated; held back by
## the displacement 0x20 pairing two ways: the object's z +0x20 -> +0xc, the controller's attitude words +0x20.. unchanged).
## The target seen from the aircraft, as 1.0.3: height = obj y + 0x2d * vertical speed (+0xc) - ground (+0x2e); below 4000
## the target is raised (and at or below 1000 the AI opens up fully), returning 1; far targets limit the climb; the vector
## fits 15 bits, is turned by the inverse attitude and aimed at (pitch / heading errors, words).
func build_relative_steer_sample(ctl: int, x: int, y: int, z: int, height_out: int, vec: int, pitch_err: int, heading_err: int) -> int:
	assert(memory != null, "build_relative_steer_sample requires original global memory")
	assert(a7 != 0, "build_relative_steer_sample needs a7 (its frame holds the matrix and angles)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var obj: int = memory.read_u32(ctl)
	var height: int = C68kScript.s32(C68kScript.s32(0x2d * memory.read_s32(ctl + 0xc)) + memory.read_u32(obj + 0x8) - memory.read_u32(ctl + 0x2e))
	memory.write_u32(height_out, C68kScript.u32(height))
	var low: int = 0
	var ty: int = C68kScript.s32(y)
	if height <= 0xf9f:
		ty = C68kScript.s32(ty + 0xfa0 - height)
		if height <= 0x3e8:
			a7 = entry_a7 - 0x4c
			max_ai_throttle_maybe_invert_679(ctl)
			a7 = entry_a7
		low = 1
	var dx: int = C68kScript.s32(C68kScript.s32(x) - memory.read_u32(obj + 0x4))
	var horiz: int = abs_int(dx)
	var dy: int = C68kScript.s32(ty - memory.read_u32(obj + 0x8))
	var abs_dy: int = abs_int(dy)
	var dz: int = C68kScript.s32(C68kScript.s32(z) - memory.read_u32(obj + 0xc))
	horiz = C68kScript.s32(horiz + abs_int(dz))
	if horiz > 0x8000:
		var aim: int
		if abs_dy > 0x800:
			aim = horiz
			if aim < 0:
				aim += 3
			aim = aim >> 2
		else:
			var h: int = horiz
			if h < 0:
				h += 0xff
			aim = C68kScript.s32((h >> 8) * abs_dy)
			if aim < 0:
				aim += 0x1f
			aim = aim >> 5
		if dy < 0:
			aim = -aim
		if horiz <= 0xffff:
			var t: int = C68kScript.s32((aim - dy) * (horiz - 0x8000))
			if t < 0:
				t += 0x7fff
			dy = C68kScript.s32(dy + (t >> 15))
		else:
			dy = aim
		abs_dy = abs_int(dy)
	var span: int = C68kScript.u32(horiz + abs_dy)
	var shift: int = 0
	while (span & 0xffff8000) != 0:
		shift += 1
		span = C68kScript.u32(C68kScript.asr32(span, 1))
	memory.write_u16(vec + 2, C68kScript.u16(C68kScript.asr32(dx, shift)))
	memory.write_u16(vec + 6, C68kScript.u16(C68kScript.asr32(dy, shift)))
	memory.write_u16(vec + 10, C68kScript.u16(C68kScript.asr32(dz, shift)))
	var matrix: int = a6 - 0x12
	a7 = entry_a7 - 0x58
	build_attitude_matrix_3ang_alt((-memory.read_s16(ctl + 0x20)) & 0x3fff, (-memory.read_s16(ctl + 0x22)) & 0x3fff, (-memory.read_s16(ctl + 0x24)) & 0x3fff, matrix)
	a7 = entry_a7
	transform_vecs12_by_matrix3(1, vec, vec, matrix)
	var sq: int = 0
	for off in [2, 6, 10]:
		sq = C68kScript.s32(sq + memory.read_s16(vec + off) * memory.read_s16(vec + off))
	var length: int = isqrt_floor(sq)
	attitude_from_velocity(memory.read_s16(vec + 2), memory.read_s16(vec + 6), memory.read_s16(vec + 10), C68kScript.s16(length), sq, a6 - 0x14, a6 - 0x16)
	memory.write_u16(pitch_err, C68kScript.u16(fold_angle_pm_0x2000(memory.read_s16(a6 - 0x14))))
	memory.write_u16(heading_err, C68kScript.u16(fold_angle_pm_0x2000(memory.read_s16(a6 - 0x16))))
	return low

## The caller's a5 when the host calls a hook (the overlay loader sets it; CODE 2 runs with A5). Leyte's
## process_flight_input_and_outcome keeps the target 0x21070 in a5 (saved with movem) and stores a5 back
## on every return, so a result taken before its controller tick stores this value there.
var caller_a5: int = 0

## process_flight_input_and_outcome (Leyte; 1.0.3's with the object flags long at +0x14): hide the player
## object (long +0x14 |= 0x40, drop_contact_records_for_object, 8 below) unless already hidden (+0x17 bit 6).
func _hide_player_object(body: int) -> void:
	var saved: int = a7
	var ctl: int = memory.read_u32(0x20b28)
	var obj: int = memory.read_u32(ctl)
	if (memory.read_u8(obj + 0x17) & 0x40) == 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) | 0x40)
		a7 = body - 8
		drop_contact_records_for_object(memory.read_u32(ctl))
	a7 = saved

## process_flight_input_and_outcome 0x8bd2: show the player object again (when hidden: long +0x14 &=
## ~0x40 and contact records dropped), then update_view_clip_bounds.
func _show_player_object(body: int) -> void:
	var saved: int = a7
	var ctl: int = memory.read_u32(0x20b28)
	var obj: int = memory.read_u32(ctl)
	if (memory.read_u8(obj + 0x17) & 0x40) != 0:
		memory.write_u32(obj + 0x14, memory.read_u32(obj + 0x14) & 0xffffffbf)
		a7 = body - 8
		drop_contact_records_for_object(memory.read_u32(ctl))
	a7 = body - 4
	update_view_clip_bounds()
	a7 = saved

## process_flight_input_and_outcome 0x8a40 ('V'): view 1, look offsets and targets cleared, the camera
## on the player object (+4 / +8 / +0xc), the object hidden, update_view_clip_bounds.
func _view_key_reset(body: int) -> void:
	var saved: int = a7
	memory.write_u16(0x23d88, 1)
	for addr in [0x2b234, 0x23d34, 0x2b238, 0x23d30]:
		memory.write_u32(addr, 0)
	var obj: int = memory.read_u32(memory.read_u32(0x20b28))
	memory.write_u32(0x23d68, memory.read_u32(obj + 4))
	memory.write_u32(0x23d64, memory.read_u32(obj + 8))
	memory.write_u32(0x23d60, memory.read_u32(obj + 0xc))
	_hide_player_object(body)
	a7 = body - 4
	update_view_clip_bounds()
	a7 = saved

## process_flight_input_and_outcome: the key cases of the jump table at 0x86cc (key - 9, 0x76 words).
## Leyte's are 1.0.3's at Leyte's addresses (look-down limit 0x3580, 1.0.3 0x3040) plus 'N' / 'n'
## (0x8d2c: word +0x682 + 1 while its low two bits are <= 2). Returns 3 for ESC / '`' / '~' and 4 for
## 'P', else 0.
func _process_flight_key(body: int, key: int, event_kind: int) -> int:
	var saved: int = a7
	var down: bool = event_kind == 3
	var view: int = memory.read_s16(0x23d88)
	var ctl: int = memory.read_u32(0x20b28)
	var bailed: bool = memory.read_u16(0x2105c) != 0
	var slew: bool = memory.read_u32(ctl + 0x32) == 0x112a4
	match key:
		9:
			a7 = body - 4
			cycle_view_distance()
		0x13:
			memory.write_u8(ctl + 0x676, 0)
			memory.write_u8(ctl + 0x689, 0)
			memory.write_u32(ctl + 0x62a, 0)
			memory.write_u32(ctl + 0x32, 0x112a4 if memory.read_u32(ctl + 0x32) == 0xfcf6 else 0xfcf6)
		0x18:
			var target: int = memory.read_u32(0x21070)
			if target != 0:
				memory.write_u16(target + 0x698, memory.read_u16(target + 0x698) | 0x100)
		0x1b, 0x60, 0x7e:
			return 3
		0x1c, 0x1d:
			if view <= 2:
				var step: int = -0x800 if key == 0x1c else 0x800
				memory.write_u32(0x2b238, C68kScript.u32(memory.read_u32(0x2b238) + step) & 0x3fff)
		0x1e, 0x1f:
			var limit: int = 0x3580 if key == 0x1e else 0xa80
			if view <= 2 and memory.read_u32(0x2b234) != limit:
				var step2: int = -0x540 if key == 0x1e else 0x540
				memory.write_u32(0x2b234, C68kScript.u32(memory.read_u32(0x2b234) + step2) & 0x3fff)
		0x20:
			if not bailed:
				if memory.read_u8(ctl + 0x64c) != 0:
					memory.write_u8(ctl + 0x64f, 1 if down else 0)
				elif memory.read_u16(0x2b270) != 0:
					memory.write_u16(0x2b230, 1 if down else 0)
		0x2b, 0x3d:
			if view == 6 and memory.read_s16(0x27020) <= 7:
				if down:
					var i: int = memory.read_s16(0x27020)
					memory.write_u16(C68kScript.u32(0x2703c + 2 * i), memory.read_u16(0x27050))
					memory.write_u16(C68kScript.u32(0x27024 + 2 * i), memory.read_u16(0x2704e))
					var sx: int = C68kScript.u16((memory.read_s16(0x27050) >> 1) + memory.read_u16(0x27050))
					memory.write_u16(0x27050, sx)
					var sy: int = C68kScript.u16((memory.read_s16(0x2704e) >> 1) + memory.read_u16(0x2704e))
					memory.write_u16(0x2704e, sy)
					memory.write_u16(0x27020, C68kScript.u16(memory.read_u16(0x27020) + 1))
					i = memory.read_s16(0x27020)
					memory.write_u16(C68kScript.u32(0x2703c + 2 * i), sx)
					memory.write_u16(C68kScript.u32(0x27024 + 2 * i), sy)
			elif view <= 5 and not bailed:
				memory.write_u16(0x2b232, 1 if down else 0)
		0x2d, 0x5f:
			if view == 6 and memory.read_u16(0x27020) != 0:
				if down:
					memory.write_u16(0x27020, C68kScript.u16(memory.read_u16(0x27020) - 1))
					var j: int = memory.read_s16(0x27020)
					memory.write_u16(0x27050, memory.read_u16(C68kScript.u32(0x2703c + 2 * j)))
					memory.write_u16(0x2704e, memory.read_u16(C68kScript.u32(0x27024 + 2 * j)))
			elif view <= 5 and not bailed:
				memory.write_u16(0x2b232, 0xffff if down else 0)
		0x2e, 0x3e, 0x5a, 0x7a:
			memory.write_u16(0x21042, 0x40 if down and (key == 0x5a or key == 0x3e) else 0)
		0x2f, 0x3f, 0x58, 0x78:
			memory.write_u16(0x21042, 0xffc0 if down and (key == 0x58 or key == 0x3f) else 0)
		0x30, 0x31, 0x33, 0x37, 0x39:
			if slew:
				_slew_throttle_key(key)
		0x32, 0x34, 0x36, 0x38:
			if slew:
				_slew_throttle_key(key)
			else:
				if key == 0x32 or key == 0x38:
					memory.write_u16(0x2b268, (1 if key == 0x32 else 0xffff) if down else 0)
				else:
					memory.write_u16(0x2b26a, (1 if key == 0x36 else 0xffff) if down else 0)
				memory.write_u16(0x2b270, 1)
		0x35:
			if slew:
				_slew_throttle_key(key)
			else:
				var host: int = memory.read_u32(0x207fc)
				memory.write_u16(host + 0x37a, memory.read_u16(0x27052))
				memory.write_u16(0x2b26e, memory.read_u16(0x27052))
				memory.write_u16(host + 0x37c, memory.read_u16(0x27054))
				memory.write_u16(0x2b26c, memory.read_u16(0x27054))
				memory.write_u16(0x2b268, 0)
				memory.write_u16(0x2b26a, 0)
				memory.write_u16(0x2b270, 1)
		0x41, 0x61:
			memory.write_u16(0x2105a, 1 if memory.read_u32(0x21070) != 0 and down else 0)
		0x42, 0x62:
			if not bailed and C68kScript.s32(memory.read_u32(memory.read_u32(ctl) + 8) - memory.read_u32(ctl + 0x2e)) > 0x43:
				memory.write_u8(ctl + 0x684, 1)
		0x43, 0x63:
			if view == 5:
				_view_key_reset(body)
			else:
				memory.write_u16(0x23d88, 5)
				_show_player_object(body)
		0x45, 0x65:
			if memory.read_u32(0x21070) != 0 and view != 7 and memory.read_u8(ctl + 0x64c) == 0:
				memory.write_u16(0x23d88, 7)
				_show_player_object(body)
			else:
				_view_key_reset(body)
		0x56, 0x76:
			_view_key_reset(body)
		0x46, 0x66:
			if not bailed:
				var flaps: int = memory.read_u8(ctl + 0x68c)
				memory.write_u8(ctl + 0x68c, (-flaps) & 0xff if flaps != 0 else 1)
		0x47, 0x67:
			if not bailed and memory.read_u8(ctl + 0x64c) == 0:
				var gear: int = memory.read_u8(ctl + 0x68a)
				memory.write_u8(ctl + 0x68a, (-gear) & 0xff if gear != 0 else 1)
				ctl = memory.read_u32(0x20b28)
				if memory.read_s8(ctl + 0x68a) > 0 and memory.read_u16(memory.read_u32(0x207fc) + 0xa4) != 0:
					a7 = body - 8
					if C68kScript.u32(try_begin_landing_approach(ctl)) != 0:
						memory.write_u16(0x21024, 1)
					else:
						memory.write_u16(0x21026, 1)
				else:
					memory.write_u32(ctl + 0x32, 0xfcf6)
		0x4a, 0x6a:
			if not bailed and memory.read_u8(ctl + 0x64c) == 0 and memory.read_u8(ctl + 0x688) == 0:
				memory.write_u8(ctl + 0x688, 1)
		0x4d, 0x6d:
			memory.write_u8(ctl + 0x676, 0)
			memory.write_u16(ctl + 0x698, memory.read_u16(ctl + 0x698) | 0x4000)
		0x4e, 0x6e:
			if not bailed and (memory.read_u16(ctl + 0x682) & 3) <= 2:
				memory.write_u16(ctl + 0x682, C68kScript.u16(memory.read_u16(ctl + 0x682) + 1))
		0x50, 0x70:
			return 4
		0x52, 0x72:
			if view <= 2:
				memory.write_u32(0x2b238, C68kScript.u32(memory.read_u32(0x2b238) + 0x2000) & 0x3fff)
			else:
				memory.write_u32(0x2b238, 0x2000)
				memory.write_u32(0x23d30, 0x2000)
				memory.write_u16(0x23d88, 1)
		0x54, 0x74:
			if view == 6:
				_view_key_reset(body)
			else:
				a7 = body - 8
				var found: int = C68kScript.u32(point_near_camera_or_type80_object(0x23d54))
				a7 = saved
				if found != 0:
					memory.write_u16(0x23d88, 6)
					memory.write_u32(0x23d68, memory.read_u32(0x23d54))
					memory.write_u32(0x23d64, memory.read_u32(0x23d58))
					memory.write_u32(0x23d60, memory.read_u32(0x23d5c))
					_show_player_object(body)
	a7 = saved
	return 0

## process_flight_input_and_outcome 0x89d6: in slew mode a digit sets the throttle byte +0x676 = key -
## '0' and switches the engine on (+0x698 &= 0xbfff); keyboard steering (0x2b270) goes off.
func _slew_throttle_key(key: int) -> void:
	var ctl: int = memory.read_u32(0x20b28)
	memory.write_u16(0x2b270, 0)
	memory.write_u8(ctl + 0x676, (key + 0xd0) & 0xff)
	memory.write_u16(ctl + 0x698, memory.read_u16(ctl + 0x698) & 0xbfff)

## process_flight_input_and_outcome 0x8e2a: the throttle ramp 0x2b232 (|step| 1: engine off -> throttle
## 0 and on again, else throttle +0x676 + the step's low byte, a clamp to 9 or 0 also ending the ramp
## (Leyte; 1.0.3 kept it); then step <<= 1; else one step toward 0), the keyboard-steered mouse point
## (as 1.0.3's: host +0x37a / +0x37c against the centre 0x27052 / 0x27054) and the gun trigger.
func _update_keyboard_flight(body: int) -> void:
	var saved: int = a7
	if memory.read_u16(0x2b232) != 0:
		a7 = body - 8
		var mag: int = abs_int(memory.read_s16(0x2b232))
		var ctl: int = memory.read_u32(0x20b28)
		if mag == 1:
			if (memory.read_u8(ctl + 0x698) & 0x40) != 0:
				memory.write_u8(ctl + 0x676, 0)
				memory.write_u16(ctl + 0x698, memory.read_u16(ctl + 0x698) & 0xbfff)
			else:
				memory.write_u8(ctl + 0x676, (memory.read_u8(ctl + 0x676) + memory.read_u8(0x2b233)) & 0xff)
				if memory.read_s8(ctl + 0x676) > 9:
					memory.write_u8(ctl + 0x676, 9)
					memory.write_u16(0x2b232, 0)
				if memory.read_s8(ctl + 0x676) < 0:
					memory.write_u8(ctl + 0x676, 0)
					memory.write_u16(0x2b232, 0)
			memory.write_u16(0x2b232, C68kScript.u16(memory.read_s16(0x2b232) << 1))
		elif memory.read_s16(0x2b232) > 0:
			memory.write_u16(0x2b232, C68kScript.u16(memory.read_u16(0x2b232) - 1))
		else:
			memory.write_u16(0x2b232, C68kScript.u16(memory.read_u16(0x2b232) + 1))
	var host: int = memory.read_u32(0x207fc)
	if memory.read_u16(0x2b26e) != memory.read_u16(host + 0x37a) or memory.read_u16(0x2b26c) != memory.read_u16(host + 0x37c):
		memory.write_u16(0x2b270, 0)
	if memory.read_u16(0x2b270) != 0:
		var dh: int = C68kScript.s16(memory.read_u16(0x27052) - memory.read_u16(host + 0x37a))
		var dv: int = C68kScript.s16(memory.read_u16(host + 0x37c) - memory.read_u16(0x27054))
		var step_h: int
		var dir_h: int = memory.read_s16(0x2b26a)
		a7 = body - 8
		if dir_h > 0:
			step_h = 0x1e
		elif dir_h < 0:
			step_h = -0x1e
		elif abs_int(dh) <= 0x1e:
			step_h = dh
		else:
			step_h = 0x1e if dh > 0 else -0x1e
		if abs_int(dh) > 0xd5:
			memory.write_u16(host + 0x37a, C68kScript.u16(memory.read_u16(0x27052) + (-0xd5 if dh > 0 else 0xd5)))
		var h: int = C68kScript.u16(step_h + memory.read_u16(host + 0x37a))
		memory.write_u16(host + 0x37a, h)
		memory.write_u16(0x2b26e, h)
		var step_v: int
		var dir_v: int = memory.read_s16(0x2b268)
		if dir_v > 0:
			step_v = 0x18
		elif dir_v < 0:
			step_v = -0x18
		elif abs_int(dv) <= 0x18:
			step_v = -dv
		else:
			step_v = -0x18 if dv > 0 else 0x18
		if abs_int(dv) > 0xab:
			memory.write_u16(host + 0x37c, C68kScript.u16(memory.read_u16(0x27054) + (0xab if dv > 0 else -0xab)))
		var v: int = C68kScript.u16(step_v + memory.read_u16(host + 0x37c))
		memory.write_u16(host + 0x37c, v)
		memory.write_u16(0x2b26c, v)
	else:
		memory.write_u16(0x2b230, memory.read_u16(host + 0x37e))
	var ctl2: int = memory.read_u32(0x20b28)
	if memory.read_u16(0x2b230) != 0 and memory.read_s16(ctl2 + 0x696) > 1 and (memory.read_u8(ctl2 + 0x699) & 0x10) == 0 \
			and memory.read_u16(0x2105c) == 0:
		if memory.read_u8(ctl2 + 0x687) == 0:
			memory.write_u8(ctl2 + 0x687, 1)
		memory.write_u16(0x2103c, 1)
	a7 = saved

## 0x1330 (Leyte only; six saved registers): the nearest of an object's runway-deck points. A type-2 object
## (+0x28) that probe_nearest_facility_kind places at a friendly facility (0x200): for each face (+0x4a,
## +0x3c of them, 0x20 apart) with surface byte +0x16 0x70, each index word of its list (+0x18, +0x14 of
## them) names a view vertex (+0x4e, 12 apart); best = min(best, approx_dist3_xyz(object +4 / +8 / +0xc,
## vertex)). Returns best.
func leyte_deck_point_min_distance(obj: int, best: int) -> int:
	assert(memory != null, "leyte_deck_point_min_distance requires original global memory")
	var entry_a7: int = a7
	best = C68kScript.s32(best)
	if memory.read_u8(obj + 0x28) != 2:
		return C68kScript.u32(best)
	a7 = entry_a7 - 0x20
	var kind: int = C68kScript.u32(probe_nearest_facility_kind(obj))
	a7 = entry_a7
	if kind != 0x200:
		return C68kScript.u32(best)
	var face: int = memory.read_u32(obj + 0x4a)
	var faces: int = memory.read_u16(obj + 0x3c)
	while faces != 0:
		faces -= 1
		if memory.read_u8(face + 0x16) == 0x70:
			var list: int = memory.read_u32(face + 0x18)
			var n: int = memory.read_u16(face + 0x14)
			while n != 0:
				n -= 1
				var vertex: int = C68kScript.u32(memory.read_s16(list) * 12 + memory.read_u32(obj + 0x4e))
				var d: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc),
						memory.read_s32(vertex), memory.read_s32(vertex + 4), memory.read_s32(vertex + 8)))
				if best > d:
					best = d
				list += 2
		face += 0x20
	return C68kScript.u32(best)

## 0x13c8 (Leyte only): the nearest runway-deck point of every object in the pools 0x20804 (count word
## 0x20b26) then 0x20b44 (count word 0x20f2e), from 0x7fffffff (leyte_deck_point_min_distance, 0x14 below).
## Its argument (the player object) is not read.
func leyte_nearest_deck_point_distance(_obj: int) -> int:
	assert(memory != null, "leyte_nearest_deck_point_distance requires original global memory")
	var entry_a7: int = a7
	var best: int = 0x7fffffff
	for pool in [[0x20804, 0x20b26], [0x20b44, 0x20f2e]]:
		var slot: int = pool[0]
		var n: int = memory.read_u16(pool[1])
		while n != 0:
			n -= 1
			a7 = entry_a7 - 0x14
			best = C68kScript.u32(leyte_deck_point_min_distance(memory.read_u32(slot), best))
			a7 = entry_a7
			slot += 4
	return C68kScript.u32(best)

## process_flight_input_and_outcome 0x90e2: a stopped aircraft on the ground (1.0.3's, host status at
## +0xd8, RPM word +0x680), except one off the runway that is damaged or empty near a friendly facility
## (kind 0x200): status 5 when (0x339000 / (leyte_nearest_deck_point_distance or 1)) < rng & 0x7f (1.0.3: rng
## & 7 == 0). Returns [result, classified] (classified false -> the camera runs with result 0).
func _classify_stopped_aircraft(body: int) -> Array:
	var saved: int = a7
	var ctl: int = memory.read_u32(0x20b28)
	a7 = body - 8
	var kind: int = C68kScript.u32(probe_nearest_facility_kind(memory.read_u32(ctl)))
	a7 = saved
	var host: int = memory.read_u32(0x207fc)
	if memory.read_s16(host + 0xd8) == 1:
		return [kind | 1, true]
	ctl = memory.read_u32(0x20b28)
	if (memory.read_u8(ctl + 0x698) & 0x10) != 0:
		if (memory.read_u16(ctl + 0x698) & 0x3fe) == 0 and (memory.read_u8(ctl + 0x691) != 0 or memory.read_u8(ctl + 0x692) != 0):
			memory.write_u16(memory.read_u32(0x207fc) + 0xd8, 4)
		else:
			_leyte_random_status(body, kind, 1, false)
		return [kind | 6, true]
	if memory.read_u16(ctl + 0x680) != 0:
		return [0, false]
	var runway: bool = memory.read_u8(ctl + 0x2c) in [0x30, 0x60, 0x70]
	if memory.read_s16(0x21050) == 2:
		memory.write_u16(0x21050, 0)
		memory.write_u16(0x20b2c, 0)
		host = memory.read_u32(0x207fc)
		if memory.read_s16(host + 0xd2) > 0 and kind == 0x200 and runway and memory.read_u16(0x21052) == 0:
			memory.write_u16(0x21052, 1)
			return [kind | 1, true]
		if runway and kind == 0x100:
			memory.write_u16(memory.read_u32(0x207fc) + 0xd8, 3)
			return [kind | 0xa, true]
		if runway:
			return [kind | 9, true]
		if (memory.read_u16(ctl + 0x698) & 0x3fe) != 0 or (memory.read_u8(ctl + 0x691) == 0 and memory.read_u8(ctl + 0x692) == 0):
			_leyte_random_status(body, kind, 5, true)
			return [kind | 8, true]
		if kind == 0x100:
			a7 = body - 4
			var r: int = overlay_rng_next()
			a7 = saved
			if (r & 3) == 0:
				memory.write_u16(memory.read_u32(0x207fc) + 0xd8, 3)
			return [kind | 9, true]
		a7 = body - 4
		var kills: int = C68kScript.u32(any_mission_kills_recorded())
		a7 = saved
		if kills == 0:
			return [0, false]
		return [kind | 9, true]
	if runway and memory.read_u16(0x20b2c) == 0 and (memory.read_u8(ctl + 0x698) & 0x40) != 0:
		var fuel: int = C68kScript.s32((memory.read_s8(ctl + 0x691) + memory.read_s8(ctl + 0x692)) * 100)
		var capacity: int = memory.read_s16(memory.read_u32(ctl + 4) + 0x1c)
		memory.write_u16(memory.read_u32(0x207fc) + 0xb6, C68kScript.u16(C68kScript.divs_trunc(fuel, capacity)))
		memory.write_u16(memory.read_u32(0x207fc) + 0xb8, C68kScript.u16(memory.read_s8(ctl + 0x68d)))
		memory.write_u16(0x20b2c, 1)
		return [kind | 2, true]
	return [0, false]

## _classify_stopped_aircraft: host +0xd8 status odds. kind 0x100: rng bit 0 clear -> 3, else rng & 3 ==
## 0 -> `first`; kind 0x200: rng & 7 == 0 -> `first` (the ditching odds), or with `by_distance` (0x92ba)
## `first` when 0x339000 / (leyte_nearest_deck_point_distance or 1) < rng & 0x7f; neither: rng & 3 == 0 ->
## `first`, else rng & 3 == 0 -> 3.
func _leyte_random_status(body: int, kind: int, first: int, by_distance: bool) -> void:
	var saved: int = a7
	a7 = body - 4
	var status: int = 0
	if (kind & 0x100) != 0:
		if (overlay_rng_next() & 1) == 0:
			status = 3
		elif (overlay_rng_next() & 3) == 0:
			status = first
	elif (kind & 0x200) != 0:
		if by_distance:
			a7 = body - 8
			var near: int = C68kScript.s32(leyte_nearest_deck_point_distance(memory.read_u32(memory.read_u32(0x20b28))))
			if near == 0:
				near = 1
			var ratio: int = C68kScript.divs_trunc(0x339000, near)
			a7 = body - 4
			if ratio < (overlay_rng_next() & 0x7f):
				status = first
		elif (overlay_rng_next() & 7) == 0:
			status = first
	elif (overlay_rng_next() & 3) == 0:
		status = first
	elif (overlay_rng_next() & 3) == 0:
		status = 3
	a7 = saved
	if status != 0:
		memory.write_u16(memory.read_u32(0x207fc) + 0xd8, status)

## process_flight_input_and_outcome 0x93fa: the camera for the view (table at 0x9414). Views 1 / 2 and 6
## as 1.0.3's; views 5 and 7 both take the pose ring entry 7 frames back, of the player (5) or of the
## designated aircraft 0x21070 (7; 1.0.3 used its current pose); views 8 / 9: update_tracking_camera /
## update_bailout_view, a non-zero value is the result.
func _update_view_camera(body: int, a6: int) -> int:
	var saved: int = a7
	var result: int = 0
	var view: int = memory.read_s16(0x23d88)
	match view:
		1, 2:
			a7 = body - 4
			copy_player_pose_to_camera()
			a7 = saved
			if memory.read_s16(0x23d88) == 1 and (memory.read_u32(0x2b234) != 0 or memory.read_u32(0x2b238) != 0):
				memory.write_u16(0x23d88, 2)
				_hide_player_object(body)
				a7 = body - 4
				update_view_clip_bounds()
				a7 = saved
			if memory.read_s16(0x23d88) == 2:
				if memory.read_u16(memory.read_u32(0x207fc) + 0xa0) != 0:
					a7 = body - 8
					var d: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(memory.read_u32(0x2b234) - memory.read_u32(0x23d34))))
					if d < 0:
						d += 3
					memory.write_u32(0x23d34, C68kScript.u32(C68kScript.s16(d >> 2) + memory.read_u32(0x23d34)) & 0x3fff)
					a7 = body - 0xc
					var e: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(memory.read_u32(0x2b238) - memory.read_u32(0x23d30))))
					if e < 0:
						e += 3
					memory.write_u32(0x23d30, C68kScript.u32(C68kScript.s16(e >> 2) + memory.read_u32(0x23d30)) & 0x3fff)
					a7 = saved
				else:
					memory.write_u32(0x23d34, memory.read_u32(0x2b234))
					memory.write_u32(0x23d30, memory.read_u32(0x2b238))
				var near_a: bool = memory.read_s32(0x23d34) <= 0x20 or memory.read_s32(0x23d34) > 0x3fdf
				var near_b: bool = memory.read_s32(0x23d30) <= 0x20 or memory.read_s32(0x23d30) > 0x3fdf
				if memory.read_u32(0x2b234) == 0 and memory.read_u32(0x2b238) == 0 and near_a and near_b:
					memory.write_u16(0x23d88, 1)
					_hide_player_object(body)
					a7 = body - 4
					update_view_clip_bounds()
					a7 = saved
		5, 7:
			var ctl: int = memory.read_u32(0x20b28 if view == 5 else 0x21070)
			var i: int = memory.read_s16(ctl + 0x612) - 7
			if i < 0:
				i += 0x4b
			var rec: int = C68kScript.u32(ctl + i * 0x14 + 0x36)
			memory.write_u32(0x23d68, memory.read_u32(rec))
			memory.write_u32(0x23d64, memory.read_u32(rec + 4))
			memory.write_u32(0x23d60, memory.read_u32(rec + 8))
			memory.write_u16(0x23d40, (-memory.read_u16(rec + 0xc)) & 0x3fff)
			memory.write_u16(0x23d3e, (-memory.read_u16(rec + 0xe)) & 0x3fff)
			memory.write_u16(0x23d3c, (-memory.read_u16(rec + 0x10)) & 0x3fff)
			memory.write_u32(0x23d38, 0)
		6:
			_update_external_camera(body, a6)
		8, 9:
			a7 = body - 4
			var r: int = C68kScript.u32(update_tracking_camera() if view == 8 else update_bailout_view())
			a7 = saved
			if r != 0:
				result = r
	a7 = saved
	return result

## _update_view_camera, view 6 (0x967a): first, with an object remembered in 0x20b3c, the camera moves to
## its type-0x80 face centroid (leyte_object_type80_centroid into 0x23d54; none: 0x20b3c cleared). Then
## 1.0.3's: k = 1 + the asr steps while update_object_camera_metric(object) + object +0x20 has a bit
## above bit 14 (andi.w #$8000 keeps the high word: the test is reach & 0xffff8000); d = (object -
## camera) asr k; attitude_from_velocity(d, isqrt_floor(|d|^2), |d|^2, a6 - 0x20, a6 - 0x22); the
## stored angles 0x2b23c / 0x2b240 snap when a folded delta exceeds 0x1000, else ease by a quarter; the
## camera takes their negated low words, roll 0.
func _update_external_camera(body: int, a6: int) -> void:
	var saved: int = a7
	var obj: int = memory.read_u32(memory.read_u32(0x20b28))
	if memory.read_u32(0x20b3c) != 0:
		a7 = body - 0xc
		var found: int = C68kScript.u32(leyte_object_type80_centroid(memory.read_u32(0x20b3c), 0x23d54))
		a7 = saved
		if found != 0:
			memory.write_u32(0x23d68, memory.read_u32(0x23d54))
			memory.write_u32(0x23d64, memory.read_u32(0x23d58))
			memory.write_u32(0x23d60, memory.read_u32(0x23d5c))
		else:
			memory.write_u32(0x20b3c, 0)
	a7 = body - 8
	var reach: int = C68kScript.s32(update_object_camera_metric(obj) + memory.read_u32(obj + 0x20))
	var shift: int = 0
	while (reach & 0xffff8000) != 0:
		shift += 1
		reach = C68kScript.asr32(reach, 1)
	shift += 1
	var dx: int = C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 4) - memory.read_u32(0x23d68)), shift & 0x3f)
	var dy: int = C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 8) - memory.read_u32(0x23d64)), shift & 0x3f)
	var dz: int = C68kScript.asr32(C68kScript.s32(memory.read_u32(obj + 0xc) - memory.read_u32(0x23d60)), shift & 0x3f)
	var sq: int = C68kScript.s32(C68kScript.s32(C68kScript.s32(dx * dx) + C68kScript.s32(dy * dy)) + C68kScript.s32(dz * dz))
	a7 = body - 8
	var dist: int = C68kScript.s16(isqrt_floor(C68kScript.u32(sq)))
	a7 = body - 0x24
	attitude_from_velocity(C68kScript.u32(dx), C68kScript.u32(dy), C68kScript.u32(dz), C68kScript.u32(dist), C68kScript.u32(sq), a6 - 0x20, a6 - 0x22)
	a7 = body - 8
	var dp: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(memory.read_s16(a6 - 0x20) - memory.read_u32(0x2b23c))))
	a7 = body - 0xc
	var dh: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(memory.read_s16(a6 - 0x22) - memory.read_u32(0x2b240))))
	a7 = saved
	if abs_int(dp) > 0x1000 or abs_int(dh) > 0x1000:
		memory.write_u32(0x2b23c, memory.read_u16(a6 - 0x20) & 0x3fff)
		memory.write_u32(0x2b240, memory.read_u16(a6 - 0x22) & 0x3fff)
	else:
		if dp < 0:
			dp += 3
		memory.write_u32(0x2b23c, C68kScript.u32(C68kScript.asr32(dp, 2) + memory.read_u32(0x2b23c)) & 0x3fff)
		if dh < 0:
			dh += 3
		memory.write_u32(0x2b240, C68kScript.u32(C68kScript.asr32(dh, 2) + memory.read_u32(0x2b240)) & 0x3fff)
	memory.write_u16(0x23d40, (-memory.read_u16(0x2b23e)) & 0x3fff)
	memory.write_u16(0x23d3e, (-memory.read_u16(0x2b242)) & 0x3fff)
	memory.write_u16(0x23d3c, 0)
	memory.write_u32(0x23d38, 0)
	a7 = saved

## 0x844e (hook 14; link -0x24, ten saved registers: the body runs 0x50 below the entry): one frame of
## player input and its outcome, 1.0.3's 0x73ea with Leyte's addresses and these differences: the crash
## replay camera turns by the controller's word +0x22 and sits on the object's +4 / +8 / +0xc; the key
## table has 'N' (_process_flight_key); the controller tick may clear the target 0x21070, which is put
## back for update_game_sounds and stored as the tick left it on return (a result taken before the tick
## stores the caller's a5 there instead, as the original does); a stopped aircraft is classified on the
## ground (+0x64c) when +0x62a is 0, or when +0x62e is 0 and the word +0x30 read before the tick is > 0.
func process_flight_input_and_outcome(key: int, event_kind: int) -> int:
	assert(memory != null, "process_flight_input_and_outcome requires original global memory")
	assert(a7 != 0, "process_flight_input_and_outcome needs a7 (its frame holds the matrix and vectors)")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x50
	var a6: int = entry_a7 - 4
	var a5_target: int = C68kScript.u32(caller_a5)
	key = C68kScript.u32(key)
	event_kind = C68kScript.u32(event_kind)
	var result: int = 0
	var old_view: int = memory.read_u16(0x23d88)
	var check_view8: bool = true
	if memory.read_u16(0x21058) != 0:
		if memory.read_u16(0x21056) != 0:
			check_view8 = false
			if memory.read_s16(0x21058) == 1:
				memory.write_u16(0x21058, 2)
		else:
			memory.write_u16(0x21058, 0)
	if check_view8 and memory.read_s16(0x23d88) == 8:
		if memory.read_u32(0x21064) != 0:
			memory.write_u16(0x23d88, 9)
		else:
			memory.write_u16(0x23d88, 1)
			_hide_player_object(body)
		a7 = body - 4
		update_view_clip_bounds()
		a7 = entry_a7
		memory.write_u32(0x2106c, 0)
	var host: int = memory.read_u32(0x207fc)
	if memory.read_u16(host + 0x98) != 0:
		if memory.read_s16(host + 0x98) == 2 and memory.read_u16(0x21058) == 0:
			if memory.read_u32(host + 0x296) != 0:
				a7 = body - 8
				var playing: int = C68kScript.u32(_jsr(memory.read_u32(host + 0x2f2), [0]))
				if playing != 0:
					_jsr(memory.read_u32(memory.read_u32(0x207fc) + 0x2fa), [0])
				a7 = entry_a7
			a7 = body - 4
			update_view_clip_bounds()
			a7 = entry_a7
			memory.write_u16(0x23d88, 8)
			memory.write_u16(0x21058, 3)
			memory.write_u16(0x21056, 0x1e)
			var vec: int = a6 - 0xc
			var matrix: int = a6 - 0x1e
			memory.write_u16(a6 - 6, 0x183)
			memory.write_u16(a6 - 0xa, 0xfd12)
			memory.write_u16(a6 - 2, 0xfaed)
			a7 = body - 0x14
			build_attitude_matrix_3ang(0, memory.read_s16(memory.read_u32(0x20b28) + 0x22), 0, matrix)
			a7 = body - 0x24
			transform_vecs12_by_matrix3(1, vec, vec, matrix)
			a7 = entry_a7
			var obj: int = memory.read_u32(memory.read_u32(0x20b28))
			memory.write_u32(0x23d68, C68kScript.u32(memory.read_s16(a6 - 0xa) + memory.read_u32(obj + 4)))
			memory.write_u32(0x23d64, C68kScript.u32(memory.read_s16(a6 - 6) + memory.read_u32(obj + 8)))
			memory.write_u32(0x23d60, C68kScript.u32(memory.read_s16(a6 - 2) + memory.read_u32(obj + 0xc)))
		if memory.read_u16(0x21054) == 0 and memory.read_u16(0x21056) == 0:
			a7 = body - 8
			request_instant_replay(0 if memory.read_u8(memory.read_u32(0x20b28) + 0x64c) != 0 else 1)
			a7 = entry_a7
	if memory.read_u16(0x21054) != 0 and memory.read_u16(0x21056) == 0:
		a7 = body - 4
		begin_replay_view()
		a7 = entry_a7
	var stage: int = 0   # 0 key filter, 1 keyboard flight (0x8e2a), 2 per-frame calls (0x9078), 3 tail (0x9820)
	var view: int = memory.read_s16(0x23d88)
	if view == 7 and (memory.read_u32(0x21070) == 0 or memory.read_u8(memory.read_u32(0x20b28) + 0x64c) != 0):
		_view_key_reset(body)
		stage = 1
	elif view == 8 or view == 9:
		if event_kind == 3 and (key == 0x22 or key == 0x60 or key == 0x1b):
			result = 3
			stage = 3
		else:
			stage = 2
	if stage == 0:
		var passes: bool = event_kind == 3 or (event_kind == 4 and key == 0x20) or key in [0x61, 0x41, 0x2b, 0x2d, 0x3d, 0x5f,
				0x32, 0x34, 0x36, 0x38, 0x5a, 0x7a, 0x3e, 0x2e, 0x58, 0x78, 0x3f, 0x2f]
		if passes and C68kScript.u32(key - 9) <= 0x75:
			result = _process_flight_key(body, key, event_kind)
			stage = 3 if result != 0 else 1
		else:
			stage = 1
	if stage == 1:
		_update_keyboard_flight(body)
		stage = 2
	if stage == 2:
		a7 = body - 4
		apply_player_mouse_controls()
		var ctl: int = memory.read_u32(0x20b28)
		var old_30: int = memory.read_s16(ctl + 0x30)
		var target: int = memory.read_u32(0x21070)
		if memory.read_s16(0x21058) == 3:
			tick_flagged_object_controllers()
		else:
			advance_frame_and_tick_controllers()
		a5_target = memory.read_u32(0x21070)
		if a5_target == 0:
			memory.write_u32(0x21070, target)
		update_game_sounds()
		a7 = entry_a7
		ctl = memory.read_u32(0x20b28)
		var classified: bool = false
		if memory.read_s16(0x23d88) <= 7 and memory.read_u8(ctl + 0x64c) != 0 \
				and (memory.read_u32(ctl + 0x62a) == 0 or (memory.read_u32(ctl + 0x62e) == 0 and old_30 > 0)):
			var outcome: Array = _classify_stopped_aircraft(body)
			result = outcome[0]
			classified = outcome[1]
		if not classified:
			result = _update_view_camera(body, a6)
	if memory.read_s16(0x23d40) > 0x1000 and memory.read_s16(0x23d40) <= 0x2fff:
		var mirror: int = 0x2000 if memory.read_s16(0x23d40) <= 0x2000 else 0x6000
		memory.write_u16(0x23d40, C68kScript.u16(mirror - memory.read_u16(0x23d40)))
		memory.write_u16(0x23d3e, (memory.read_u16(0x23d3e) + 0x2000) & 0x3fff)
	if result != 0:
		memory.write_u8(memory.read_u32(0x20b28) + 0x64f, 0)
		memory.write_u16(0x2105a, 0)
		memory.write_u16(0x2b232, 0)
	if memory.read_u16(0x23d88) != old_view:
		memory.write_u16(0x23d86, 1)
	memory.write_u32(0x21070, a5_target)
	return C68kScript.u32(result)

## 0x9a94 (eight saved registers): the bail-out camera, 1.0.3's 0x8a30 with Leyte's offsets (the
## parachute object's flag byte +0x17, its birth frame +0x42). Without a parachute controller 0x21064,
## with its object gone (+0x17 bit 1) or its landing timer +0x61e running past +0x614 > 0x5a0:
## resolve_bailout_outcome (0x24 below), whose result is returned. Else the camera looks at the
## parachute: with a player controller and either the chute born within 0x4b frames or 0x373 above
## the controller's ground +0x2e, within 0x9ab0 of the player's object the target is the midpoint and
## the camera goes behind the chute (place_camera_behind_object with the distance's shift, 0x38 below);
## otherwise the camera takes the chute's pose ring entry after +0x60e (0x14 apart from +0x32). Then
## turn_camera_toward_point(target) (0x30 below); returns 0.
func update_bailout_view() -> int:
	assert(memory != null, "update_bailout_view requires original global memory")
	var entry_a7: int = a7
	var chute_ctl: int = memory.read_u32(0x21064)
	if chute_ctl == 0 or (memory.read_u8(memory.read_u32(chute_ctl) + 0x17) & 2) != 0 \
			or (memory.read_u16(chute_ctl + 0x61e) != 0 and memory.read_s16(chute_ctl + 0x614) > 0x5a0):
		a7 = entry_a7 - 0x24
		var outcome: int = resolve_bailout_outcome()
		a7 = entry_a7
		return outcome
	var chute: int = memory.read_u32(chute_ctl)
	var target: Array = [memory.read_u32(chute + 4), memory.read_u32(chute + 8), memory.read_u32(chute + 0xc)]
	var placed: bool = false
	if memory.read_u32(0x20b28) != 0 and (C68kScript.s32(memory.read_u32(0x21020) - 0x4b) <= memory.read_s32(chute + 0x42)
			or C68kScript.s32(memory.read_u32(chute + 8) - memory.read_u32(chute_ctl + 0x2e)) > 0x373):
		var plane: int = memory.read_u32(memory.read_u32(0x20b28))
		var dist: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(plane + 4), memory.read_s32(plane + 8), memory.read_s32(plane + 0xc),
				memory.read_s32(chute + 4), memory.read_s32(chute + 8), memory.read_s32(chute + 0xc)))
		if dist <= 0x9ab0:
			for k in 3:
				var m: int = C68kScript.s32(memory.read_u32(plane + 4 + 4 * k) + memory.read_u32(chute + 4 + 4 * k))
				target[k] = C68kScript.u32(C68kScript.asr32(m + 1 if m < 0 else m, 1))
			var shift: int = 0
			while (C68kScript.u32(dist) & 0xffff8000) != 0:
				shift = C68kScript.s16(shift + 1)
				dist = C68kScript.asr32(dist, 1)
			a7 = entry_a7 - 0x38
			place_camera_behind_object(chute, target[0], target[1], target[2], shift)
			a7 = entry_a7
			placed = true
	if not placed:
		var slot: int = (memory.read_u16(chute_ctl + 0x60e) + 1) & 0xffff
		if slot > 0x4a:
			slot = (slot - 0x4b) & 0xffff
		var pose: int = C68kScript.u32(chute_ctl + 0x32 + C68kScript.s16(slot) * 0x14)
		memory.write_u32(0x23d68, memory.read_u32(pose))
		memory.write_u32(0x23d64, memory.read_u32(pose + 4))
		memory.write_u32(0x23d60, memory.read_u32(pose + 8))
	a7 = entry_a7 - 0x30
	turn_camera_toward_point(target[0], target[1], target[2])
	a7 = entry_a7
	return 0

## 0x1cb04 (three saved registers): a shadow follows its owner (+0x32): x / z = owner position + owner
## controller +8 / +0x10, y = owner controller +0x2e (ground height), word +0x22 = owner controller word
## +0x22. Leyte's owner controller is the owner's +0 (1.0.3: +8), its flags byte +0x17 and reference count
## +0x29. When the owner is retired (+0x17 bit 1) and its controller's +0 cleared, the shadow is retired
## (0x14 below), the owner's reference count drops and +0x32 = 0.
func tick_shadow(ctl: int, d0_in: int = 0) -> void:
	assert(memory != null, "tick_shadow requires original global memory")
	var obj: int = memory.read_u32(ctl)
	var owner: int = memory.read_u32(ctl + 0x32)
	var owner_ctl: int = memory.read_u32(owner)
	if (memory.read_u8(owner + 0x17) & 2) != 0 and memory.read_u32(owner_ctl) == 0:
		var entry_a7: int = a7
		a7 = entry_a7 - 0x14
		retire_entity(obj, d0_in)
		a7 = entry_a7
		memory.write_u8(owner + 0x29, (memory.read_u8(owner + 0x29) - 1) & 0xff)
		memory.write_u32(ctl + 0x32, 0)
		return
	memory.write_u32(obj + 4, C68kScript.u32(memory.read_u32(memory.read_u32(ctl + 0x32) + 4) + memory.read_u32(owner_ctl + 8)))
	memory.write_u32(obj + 8, memory.read_u32(owner_ctl + 0x2e))
	memory.write_u32(obj + 0xc, C68kScript.u32(memory.read_u32(memory.read_u32(ctl + 0x32) + 0xc) + memory.read_u32(owner_ctl + 0x10)))
	memory.write_u16(ctl + 0x22, memory.read_u16(owner_ctl + 0x22))

## 0x1dd1c: controller +8 / +0xc / +0x10 = camera - (object position +4 / +8 / +0xc + (0xdff, -0x133e,
## 0x161d)); words +0x22 / +0x20 = the negated camera heading 0x23d3e / pitch 0x23d40 & 0x3fff.
func tick_view_attached_object(ctl: int) -> void:
	assert(memory != null, "tick_view_attached_object requires original global memory")
	var obj: int = memory.read_u32(ctl)
	memory.write_u32(ctl + 8, C68kScript.u32(memory.read_u32(0x23d68) - (memory.read_u32(obj + 4) + 0xdff)))
	memory.write_u32(ctl + 0xc, C68kScript.u32(memory.read_u32(0x23d64) - (memory.read_u32(obj + 8) - 0x133e)))
	memory.write_u32(ctl + 0x10, C68kScript.u32(memory.read_u32(0x23d60) - (memory.read_u32(obj + 0xc) + 0x161d)))
	memory.write_u16(ctl + 0x22, (-memory.read_u16(0x23d3e)) & 0x3fff)
	memory.write_u16(ctl + 0x20, (-memory.read_u16(0x23d40)) & 0x3fff)

## 0x19bce (link a6 -0xc, two saved registers): 1.0.3's spawn_ordnance_scattered with the bullet's
## controller at the object's +0 (1.0.3: +8). The velocity words (0, 0, word((speed asr 4) + 0x2015)) at
## a6 - 0xc are turned by the matrix (transform_vecs12_by_matrix3, 0x2c below) and scattered by (rng & 0x3f)
## - 0x20 each; spawn_mission_pool_object(0x2cbfe's template, position + v asr.w 2, 0, 0, 0, 0) (0x4c below);
## null -> 0. The controller: +0x32 = ext(vy) << 4, +8 / +0xc / +0x10 = v asr.w 2, word +0x38 = 0, word
## +0x3a = 2 with a mode, else 1, the 0x12-byte matrix copied to +0x40 (memmove_block, 0x28 below).
func spawn_ordnance_scattered(speed: int, mode: int, x: int, y: int, z: int, matrix: int) -> int:
	assert(memory != null, "spawn_ordnance_scattered requires original global memory")
	assert(a7 != 0, "spawn_ordnance_scattered needs a7 (its frame holds the velocity record)")
	var entry_a7: int = a7
	var vel: int = entry_a7 - 4 - 0xc
	memory.write_u16(vel + 6, 0)
	memory.write_u16(vel + 2, 0)
	memory.write_u16(vel + 10, C68kScript.u16(C68kScript.asr32(C68kScript.s32(speed), 4) + 0x2015))
	a7 = entry_a7 - 0x2c
	transform_vecs12_by_matrix3(1, vel, vel, matrix)
	a7 = entry_a7
	for off in [2, 6, 10]:
		memory.write_u16(vel + off, C68kScript.u16(memory.read_u16(vel + off) - 0x20 + (overlay_rng_next() & 0x3f)))
	var q := []
	for off in [2, 6, 10]:
		q.append(memory.read_s16(vel + off) >> 2)
	a7 = entry_a7 - 0x4c
	var obj: int = spawn_mission_pool_object(memory.read_u32(0x2cbfe), C68kScript.u32(x + q[0]), C68kScript.u32(y + q[1]), C68kScript.u32(z + q[2]), 0, 0, 0, 0)
	a7 = entry_a7
	if obj == 0:
		return 0
	var ctl: int = memory.read_u32(obj)
	memory.write_u32(ctl + 0x32, C68kScript.u32(memory.read_s16(vel + 6) << 4))
	memory.write_u32(ctl + 8, C68kScript.u32(q[0]))
	memory.write_u32(ctl + 0xc, C68kScript.u32(q[1]))
	memory.write_u32(ctl + 0x10, C68kScript.u32(q[2]))
	memory.write_u16(ctl + 0x38, 0)
	memory.write_u16(ctl + 0x3a, 2 if C68kScript.u32(mode) != 0 else 1)
	a7 = entry_a7 - 0x28
	memmove_block(matrix, ctl + 0x40, 0x12)
	a7 = entry_a7
	return ctl

## 0x1d2e2 (eight saved registers): 1.0.3's tick_turret with Leyte's object layout (flags byte +0x17,
## reference count +0x29, vertices +0x46, the carrier's controller at its +0 and that controller's burning
## bit at +0x699 bit 4). Once (word +0x40), word +0x3e = probe_nearest_facility_kind (0x28 below). A
## carrier (+0x32) that is retired, has no vertices or is burning is released and the turret cleaned up
## (0x28 below); otherwise the turret takes its position. A live turret with a target (+0x36): a retired or
## vertex-less target is released. Else, unless word +0x46 is set, +0x3a = approx_dist3_xyz(turret, target);
## within the signed range +0x42 it aims (isqrt 0x28, attitude_from_velocity into +0x4c / +0x4e 0x44 below)
## and, in live play, runs its bursts (spawn_ordnance_oriented_3ang, 0x44 below).
func tick_turret(ctl: int) -> void:
	assert(memory != null, "tick_turret requires original global memory")
	var entry_a7: int = a7
	var obj: int = memory.read_u32(ctl)
	if memory.read_u16(ctl + 0x40) == 0:
		a7 = entry_a7 - 0x28
		memory.write_u16(ctl + 0x3e, C68kScript.u16(probe_nearest_facility_kind(obj)))
		a7 = entry_a7
		memory.write_u16(ctl + 0x40, 1)
	var carrier: int = memory.read_u32(ctl + 0x32)
	if carrier != 0:
		var release: bool = (memory.read_u8(carrier + 0x17) & 2) != 0 or memory.read_u32(carrier + 0x46) == 0
		if not release and memory.read_u32(carrier) != 0:
			release = (memory.read_u8(memory.read_u32(carrier) + 0x699) & 0x10) != 0
		if release:
			memory.write_u8(carrier + 0x29, (memory.read_u8(carrier + 0x29) - 1) & 0xff)
			memory.write_u32(ctl + 0x32, 0)
			a7 = entry_a7 - 0x28
			dispatch_entity_cleanup(obj)
			a7 = entry_a7
		else:
			for k in range(3):
				memory.write_u32(obj + 4 + 4 * k, memory.read_u32(carrier + 4 + 4 * k))
	if (memory.read_u8(obj + 0x17) & 2) != 0 or memory.read_u32(ctl + 0x36) == 0:
		return
	var target: int = memory.read_u32(ctl + 0x36)
	if (memory.read_u8(target + 0x17) & 2) != 0 or memory.read_u32(target + 0x46) == 0:
		memory.write_u8(target + 0x29, (memory.read_u8(target + 0x29) - 1) & 0xff)
		memory.write_u32(ctl + 0x36, 0)
		return
	if memory.read_u16(ctl + 0x46) != 0:
		return
	var dist: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc),
			memory.read_s32(target + 4), memory.read_s32(target + 8), memory.read_s32(target + 0xc)))
	memory.write_u32(ctl + 0x3a, C68kScript.u32(dist))
	if dist > memory.read_s32(ctl + 0x42):
		return
	var shift: int = 0
	var d: int = C68kScript.u32(dist)
	while (d & 0xffff8000) != 0:
		shift = C68kScript.s16(shift + 1)
		d = C68kScript.u32(C68kScript.asr32(C68kScript.s32(d), 1))
	shift = C68kScript.s16(shift + 1)
	var rel := []
	for k in range(3):
		rel.append(C68kScript.asr32(C68kScript.s32(memory.read_u32(target + 4 + 4 * k) - memory.read_u32(obj + 4 + 4 * k)), shift & 0x3f))
	var sq: int = C68kScript.s32(C68kScript.s32(C68kScript.s32(rel[0] * rel[0]) + C68kScript.s32(rel[1] * rel[1])) + C68kScript.s32(rel[2] * rel[2]))
	a7 = entry_a7 - 0x28
	var len: int = isqrt_floor(C68kScript.u32(sq))
	a7 = entry_a7 - 0x44
	attitude_from_velocity(C68kScript.u32(rel[0]), C68kScript.u32(rel[1]), C68kScript.u32(rel[2]), C68kScript.u32(C68kScript.s16(len)),
			C68kScript.u32(sq), ctl + 0x4c, ctl + 0x4e)
	a7 = entry_a7
	if memory.read_u16(0x21058) != 0:
		return
	if memory.read_u16(ctl + 0x4a) == 0:
		if (overlay_rng_next() & 0xf) == 0:
			memory.write_u16(ctl + 0x4a, (overlay_rng_next() & 7) + 7)
			memory.write_u16(ctl + 0x50, C68kScript.u16(0x80 - (overlay_rng_next() & 0xff)))
			memory.write_u16(ctl + 0x52, C68kScript.u16(0x80 - (overlay_rng_next() & 0xff)))
		return
	memory.write_u16(ctl + 0x48, C68kScript.u16(memory.read_u16(ctl + 0x48) - 1))
	if memory.read_s16(ctl + 0x48) <= 0:
		a7 = entry_a7 - 0x44
		spawn_ordnance_oriented_3ang(0, 0, memory.read_u32(obj + 4), memory.read_u32(obj + 8), memory.read_u32(obj + 0xc),
				C68kScript.u32(memory.read_s16(ctl + 0x4c) + memory.read_s16(ctl + 0x50)) & 0x3fff,
				C68kScript.u32(memory.read_s16(ctl + 0x4e) + memory.read_s16(ctl + 0x52)) & 0x3fff, 0)
		a7 = entry_a7
		for off in [0x50, 0x52]:
			var v: int = memory.read_s16(ctl + off) * 7
			if v < 0:
				v += 7
			memory.write_u16(ctl + off, C68kScript.u16(v >> 3))
		memory.write_u16(ctl + 0x48, 2)
	memory.write_u16(ctl + 0x4a, C68kScript.u16(memory.read_u16(ctl + 0x4a) - 1))

## 0xf692 (link 0, three saved registers): spawn_mission_pool_object(0x2c5ae's fighter template, the seven
## arguments) (0x34 below); returns the new object's controller (its +0), or 0. Leyte also stores the three
## angle arguments as the controller's words +0x60a / +0x60c / +0x60e.
func spawn_enemy_fighter(x: int, y: int, z: int, angle_a: int, angle_b: int, angle_c: int, arg_word: int) -> int:
	assert(memory != null, "spawn_enemy_fighter requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x34
	var spawned: int = spawn_mission_pool_object(memory.read_u32(0x2c5ae), x, y, z, angle_a, angle_b, angle_c, arg_word)
	a7 = entry_a7
	if spawned == 0:
		return 0
	var ctl: int = memory.read_u32(spawned)
	memory.write_u16(ctl + 0x60a, C68kScript.u16(angle_a))
	memory.write_u16(ctl + 0x60c, C68kScript.u16(angle_b))
	memory.write_u16(ctl + 0x60e, C68kScript.u16(angle_c))
	return ctl

## 0x1bf64 (link -0xc, ten saved registers): 1.0.3's tick_aircraft_spawner with Leyte's layout (flags byte
## +0x17, reference count +0x29, controllers at the object's +0, the damage word +0x698, positions +4 / +0xc,
## the spawner's range +0x18). Live play with aircraft left (word +0x32): slots (+0x34 + 4i, five) holding a
## retired aircraft or one whose controller's +0 is clear are released; with no undamaged aircraft tracked
## and one draw in 512, a group of (rng & 0xffff) rem min(left, 3) + 1 (divsl.l) launches toward the player
## from the spawner's bearing (attitude_from_velocity, heading into a6 - 2, 0x5c below), each fighter
## (spawn_enemy_fighter, 0x58 below) taking the next empty slot.
func tick_aircraft_spawner(ctl: int) -> void:
	assert(memory != null, "tick_aircraft_spawner requires original global memory")
	assert(a7 != 0, "tick_aircraft_spawner needs a7 (its frame holds the heading word)")
	if memory.read_u16(ctl + 0x32) == 0 or memory.read_u16(0x21058) != 0:
		return
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var alive: int = 0
	for slot in range(5):
		var obj: int = memory.read_u32(ctl + 0x34 + 4 * slot)
		if obj == 0:
			continue
		if (memory.read_u8(obj + 0x17) & 2) != 0 or memory.read_u32(memory.read_u32(obj)) == 0:
			memory.write_u8(obj + 0x29, (memory.read_u8(obj + 0x29) - 1) & 0xff)
			memory.write_u32(ctl + 0x34 + 4 * slot, 0)
		elif (memory.read_u16(memory.read_u32(obj) + 0x698) & 0x3fe) == 0:
			alive += 1
	if alive != 0 or (overlay_rng_next() & 0x1ff) != 0:
		return
	var spawner: int = memory.read_u32(ctl)
	var player: int = memory.read_u32(memory.read_u32(0x20b28))
	var group: int = memory.read_s16(ctl + 0x32) if memory.read_s16(ctl + 0x32) <= 2 else 3
	group = C68kScript.s16(C68kScript.s32(overlay_rng_next() & 0xffff) % group + 1)
	var dx: int = C68kScript.s32(memory.read_u32(spawner + 4) - memory.read_u32(player + 4))
	var dz: int = C68kScript.s32(memory.read_u32(spawner + 0xc) - memory.read_u32(player + 0xc))
	var span: int = C68kScript.u32(abs_int(dx) + abs_int(dz))
	var shift: int = 0
	while (span & 0xffff8000) != 0:
		shift = C68kScript.s16(shift + 1)
		span = C68kScript.u32(C68kScript.asr32(C68kScript.s32(span), 1))
	a7 = entry_a7 - 0x5c
	attitude_from_velocity(C68kScript.u32(C68kScript.asr32(dx, shift & 0x3f)), 0, C68kScript.u32(C68kScript.asr32(dz, shift & 0x3f)), 0, 0, 0, a6 - 2)
	a7 = entry_a7
	memory.write_u16(a6 - 2, C68kScript.u16(memory.read_u16(a6 - 2) + (((overlay_rng_next() & 0x1f) - 0x10) << 6)) & 0x3fff)
	var r: int = C68kScript.asr32(C68kScript.divs_trunc(C68kScript.s32(memory.read_u32(spawner + 0x18) << 1), 3), 5)
	var heading: int = memory.read_s16(a6 - 2)
	var x: int = C68kScript.s32(memory.read_u32(player + 4) - C68kScript.asr32(C68kScript.s16(r) * C68kScript.s16(sin_fixed14(heading)), 7))
	var altitude: int = (overlay_rng_next() & 0xfff) * 10 + 30000
	var z: int = C68kScript.s32(C68kScript.asr32(C68kScript.s16(r) * C68kScript.s16(cos_fixed14(heading)), 7) + memory.read_u32(player + 0xc))
	memory.write_u16(a6 - 2, C68kScript.u16(memory.read_u16(a6 - 2) + (((overlay_rng_next() & 0x1f) - 0x10) << 6) + 0x2000) & 0x3fff)
	var slot2: int = 0
	var k: int = 0
	while k < group:
		while memory.read_u32(ctl + 0x34 + 4 * slot2) != 0:
			slot2 = C68kScript.s16(slot2 + 1)
		x = C68kScript.s32(x - 0x800 + (overlay_rng_next() & 0xfff))
		z = C68kScript.s32(z - 0x800 + (overlay_rng_next() & 0xfff))
		a7 = entry_a7 - 0x58
		var fighter: int = spawn_enemy_fighter(C68kScript.u32(x), altitude, C68kScript.u32(z), 0, C68kScript.u32(memory.read_s16(a6 - 2)), 0, 1)
		a7 = entry_a7
		if fighter != 0:
			memory.write_u16(ctl + 0x32, C68kScript.u16(memory.read_u16(ctl + 0x32) - 1))
			var fobj: int = memory.read_u32(fighter)
			memory.write_u32(ctl + 0x34 + 4 * slot2, fobj)
			memory.write_u8(fobj + 0x29, (memory.read_u8(fobj + 0x29) + 1) & 0xff)
		k += 1

## 0x1cb66 (link a6 -8, ten saved registers; definition +0xe pre-render of the shadow). A retired owner
## (+0x32, flags byte +0x17 bit 1) -> 4. An aircraft owner (its controller's +0, definition type word <= 7;
## 1.0.3: <= 4) gets the first face's vertices stretched along x by |cos(owner bank +0x24)| (cos, abs): each
## whose template x (host +0x8c + the scene entry +0x2e + 24 * word object +0x10, +0x22 + 12 i) exceeds the
## half size word(0x32 << word +0x36) in magnitude takes word(x * cos asr 12), at least the half size with
## its sign. Returns 0.
func shadow_pre_render(ctl: int) -> int:
	assert(memory != null, "shadow_pre_render requires original global memory")
	var obj: int = memory.read_u32(ctl)
	var owner: int = memory.read_u32(ctl + 0x32)
	if (memory.read_u8(owner + 0x17) & 2) != 0:
		return 4
	if memory.read_s16(memory.read_u32(memory.read_u32(owner) + 4)) > 7:
		return 0
	var face: int = memory.read_u32(obj + 0x4a)
	var entry: int = C68kScript.u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x2e + memory.read_u16(obj + 0x10) * 0x18)
	var template: int = C68kScript.u32(memory.read_u32(entry) + memory.read_u32(memory.read_u32(0x207fc) + 0x8c) + 0x22)
	var verts: int = memory.read_u32(obj + 0x46)
	var half: int = C68kScript.s16(C68kScript.lsl32(0x32, memory.read_u16(obj + 0x36) & 0x3f))
	var stretch: int = C68kScript.s16(abs_int(C68kScript.s16(cos_fixed14(memory.read_s16(memory.read_u32(owner) + 0x24)))))
	var idx: int = memory.read_u32(face + 0x18)
	var left: int = memory.read_u16(face + 0x14)
	while left != 0:
		left -= 1
		var vi: int = memory.read_s16(idx)
		var tx: int = memory.read_s32(C68kScript.u32(template + vi * 12))
		if abs_int(tx) > half:
			var x: int = C68kScript.s16(C68kScript.asr32(C68kScript.s32(stretch * tx), 12))
			var vert: int = C68kScript.u32(verts + vi * 12)
			if abs_int(x) > half:
				memory.write_u32(vert, C68kScript.u32(x))
			else:
				memory.write_u32(vert, C68kScript.u32(-half if x < 0 else half))
		idx += 2
	return 0

## 0x5e2 (a2 saved): the kill tally, Leyte's. Word +0x52 < 0 -> host word +0xd6 + 1. Else
## probe_nearest_facility_kind(entity) (0xc below): near a friendly base (0x200) the kill counts at +0xd6
## unless the entity's controller (+0) definition type is 0x10 / 0x11 / 0x12 / 0x15 (no controller: it
## counts); otherwise type word +0x52 == 0 counts nothing and type t adds 1 to host word +0xbc + 2t. Returns
## D0 as the code leaves it (the caller's, the probe's kind, the type word, or 2t).
func tally_destroyed_entity(entity: int, d0_in: int = 0) -> int:
	assert(memory != null, "tally_destroyed_entity requires original global memory")
	var entry_a7: int = a7
	var d0: int = C68kScript.u32(d0_in)
	var host: int = memory.read_u32(0x207fc)
	if memory.read_s16(entity + 0x52) >= 0:
		a7 = entry_a7 - 0xc
		d0 = C68kScript.u32(probe_nearest_facility_kind(entity))
		a7 = entry_a7
		if d0 != 0x200:
			if memory.read_s16(entity + 0x52) == 0:
				return d0
			d0 = C68kScript.u32(memory.read_s16(entity + 0x52) << 1)
			var counter: int = C68kScript.u32(host + C68kScript.s32(d0) + 0xbc)
			memory.write_u16(counter, C68kScript.u16(memory.read_u16(counter) + 1))
			return d0
		var ctl: int = memory.read_u32(entity)
		if ctl != 0:
			var kind: int = memory.read_u16(memory.read_u32(ctl + 4))
			d0 = (d0 & 0xffff0000) | kind
			if kind in [0x10, 0x11, 0x12, 0x15]:
				return d0
	memory.write_u16(host + 0xd6, C68kScript.u16(memory.read_u16(host + 0xd6) + 1))
	return d0

## 0x1a70c (link 0, a2-a3 saved): Leyte's random aircraft damage. A first draw above the definition's word
## +0x50 (unsigned) leaves the aircraft unharmed (1.0.3 spared types 1 and 3 three times in four and played
## the hit sound for the player). Else a roll (rng & 0xff) sets one damage bit of word +0x698: <= 5 bit 0,
## <= 0x2a bit 1, <= 0x37 bit 2, <= 0x44 bit 3, <= 0x50 bit 4, <= 0x5d bit 5; above, bit 6 when bits 6..9 are
## clear, otherwise (bit 9 -> bit 8 when byte +0x698 bit 1) word +0x69c = 0. by_player sets bit 15. Returns
## D0: the first draw when spared, else the roll (or the masked bits).
func apply_random_aircraft_damage(ctl: int, by_player: int) -> int:
	assert(memory != null, "apply_random_aircraft_damage requires original global memory")
	var flags: int = ctl + 0x698
	var d0: int = C68kScript.u32(overlay_rng_next())
	if (d0 & 0xffff) > memory.read_u16(memory.read_u32(ctl + 4) + 0x50):
		return d0
	d0 = overlay_rng_next() & 0xff
	if d0 <= 5:
		memory.write_u16(flags, memory.read_u16(flags) | 1)
	elif d0 <= 0x2a:
		memory.write_u16(flags, memory.read_u16(flags) | 2)
	elif d0 <= 0x37:
		memory.write_u16(flags, memory.read_u16(flags) | 4)
	elif d0 <= 0x44:
		memory.write_u16(flags, memory.read_u16(flags) | 8)
	elif d0 <= 0x50:
		memory.write_u16(flags, memory.read_u16(flags) | 0x10)
	elif d0 <= 0x5d:
		memory.write_u16(flags, memory.read_u16(flags) | 0x20)
	else:
		d0 = memory.read_u16(flags) & 0x3c0
		if d0 == 0:
			memory.write_u16(flags, memory.read_u16(flags) | 0x40)
		else:
			if (memory.read_u8(flags) & 2) != 0:
				memory.write_u16(flags, memory.read_u16(flags) & 0xfdff)
				memory.write_u16(flags, memory.read_u16(flags) | 0x100)
			memory.write_u16(ctl + 0x69c, 0)
	if C68kScript.u32(by_player) != 0:
		memory.write_u16(flags, memory.read_u16(flags) | 0x8000)
	return d0

## 0x13ec0 (eight saved registers): the destruction effect for an object, Leyte's. With a controller (+0):
## the effect point is the object position (+4 / +8 / +0xc) + the controller's +8 / +0xc / +0x10; near the
## ground when that y is within 0x63 of the controller's ground +0x2e (signed); surface = sext byte +0x2c, or
## surface_type_under_object (0x28 below) when the ground height is 0; near, y becomes the ground height on
## surfaces 0x30 / 0x60 / 0x70, else 0; not near, the surface word is 0. A type 0x23 / 0x24 definition whose
## first face (+0x4a) has byte +0x13 bit 1 passes 0 as the effect's byte argument (1.0.3: always 1). Without
## a controller: near, y = 0, the surface from surface_type_under_object. Airborne -> spawn_effect(2, size, 5,
## 0, 1); surface 0x10 -> (0, size, 0x4b, 0, 0); otherwise (3, 3 * size / 4 truncating, 0x2d, flag, 0) (0x44
## below). Returns the near flag.
func spawn_destruction_effect(obj: int, size: int) -> int:
	assert(memory != null, "spawn_destruction_effect requires original global memory")
	var entry_a7: int = a7
	size = C68kScript.s32(size)
	var ctl: int = memory.read_u32(obj)
	var flag: int = 1
	var near: int
	var surface: int
	var x: int
	var y: int
	var z: int
	if ctl != 0:
		x = C68kScript.s32(memory.read_s32(obj + 4) + memory.read_s32(ctl + 8))
		y = C68kScript.s32(memory.read_s32(obj + 8) + memory.read_s32(ctl + 0xc))
		z = C68kScript.s32(memory.read_s32(obj + 0xc) + memory.read_s32(ctl + 0x10))
		near = 1 if C68kScript.s32(y - memory.read_s32(ctl + 0x2e)) <= 0x63 else 0
		surface = memory.read_s8(ctl + 0x2c)
		if near != 0:
			if memory.read_u32(ctl + 0x2e) == 0:
				a7 = entry_a7 - 0x28
				surface = C68kScript.s16(surface_type_under_object(obj))
				a7 = entry_a7
			if surface == 0x30 or surface == 0x60 or surface == 0x70:
				y = memory.read_s32(ctl + 0x2e)
			else:
				y = 0
		else:
			surface = 0
		var kind: int = memory.read_s16(memory.read_u32(ctl + 4))
		if (kind == 0x24 or kind == 0x23) and (memory.read_u8(memory.read_u32(obj + 0x4a) + 0x13) & 2) != 0:
			flag = 0
	else:
		near = 1
		x = memory.read_s32(obj + 4)
		y = 0
		z = memory.read_s32(obj + 0xc)
		a7 = entry_a7 - 0x28
		surface = C68kScript.s16(surface_type_under_object(obj))
		a7 = entry_a7
	a7 = entry_a7 - 0x44
	if near == 0:
		spawn_effect(2, C68kScript.u32(size), 5, 0, 1, C68kScript.u32(x), C68kScript.u32(y), C68kScript.u32(z))
	elif surface == 0x10:
		spawn_effect(0, C68kScript.u32(size), 0x4b, 0, 0, C68kScript.u32(x), C68kScript.u32(y), C68kScript.u32(z))
	else:
		spawn_effect(3, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(size * 3), 4)), 0x2d, flag, 0, C68kScript.u32(x), C68kScript.u32(y), C68kScript.u32(z))
	a7 = entry_a7
	return near

## 0x17a2a (six saved registers): a hit on a sinkable ship, Leyte's. The anchor word 0x23d84 is cleared when it
## is 2 and this controller is 0x2104c's (1.0.3 tested the distance); credit sets word +0x672. With hits left
## (word +0x656) one is used up and the new count picks the sinking motion (draws from overlay_rng_next):
## 0 (the last): a = (rng & 0xffff) remu 0x140 + 0x200 (1.0.3: + 0x500), negated when the folded word +0x658 is
## negative, drop = rng remu 200 + 1000, and the c roll unless sinking started (word +0x670); 1: a = (rng & 0x3f)
## + 0x100 (negated on rng bit 0), drop = rng remu 100 + 100, and the c roll unless started; 2: the c roll:
## c = +-((rng & 0x7f) + 0x200), drop = rng remu 100 + 100. Other counts keep the drop register as the caller
## left it (D1, d1_in). A carrier (type 0x1b) whose deck word +0x69a is 0xffff takes a quarter of a (asr.w,
## toward zero). Then over 15 steps (+0x66a): rates +0x65e / +0x660 / +0x662 / +0x666 = a / 0 / c / drop
## divs.w 15, next frame +0x66c = frame + 0x1e, started +0x670 = 1.
func apply_hit_to_sinkable_object(ctl: int, credit: int, d1_in: int = 0) -> void:
	assert(memory != null, "apply_hit_to_sinkable_object requires original global memory")
	var entry_a7: int = a7
	if ctl == memory.read_u32(0x2104c) and memory.read_u16(0x23d84) == 2:
		memory.write_u16(0x23d84, 0)
	if C68kScript.u32(credit) != 0:
		memory.write_u16(ctl + 0x672, 1)
	if memory.read_u16(ctl + 0x656) == 0:
		return
	memory.write_u16(ctl + 0x656, C68kScript.u16(memory.read_u16(ctl + 0x656) - 1))
	var a: int = 0
	var c: int = 0
	var drop: int = C68kScript.s16(d1_in)
	var hits: int = memory.read_s16(ctl + 0x656)
	var roll_c: bool = false
	if hits == 0:
		a = C68kScript.s16(((overlay_rng_next() & 0xffff) % 0x140) + 0x200)
		a7 = entry_a7 - 0x20
		var folded: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(memory.read_s16(ctl + 0x658))))
		a7 = entry_a7
		if folded < 0:
			a = C68kScript.s16(-a)
		drop = C68kScript.s16(((overlay_rng_next() & 0xffff) % 200) + 1000)
		roll_c = memory.read_u16(ctl + 0x670) == 0
	elif hits == 1:
		a = (overlay_rng_next() & 0x3f) + 0x100
		if (overlay_rng_next() & 1) != 0:
			a = -a
		drop = C68kScript.s16(((overlay_rng_next() & 0xffff) % 100) + 100)
		roll_c = memory.read_u16(ctl + 0x670) == 0
	elif hits == 2:
		roll_c = true
	if roll_c:
		c = (overlay_rng_next() & 0x7f) + 0x200
		if (overlay_rng_next() & 1) != 0:
			c = -c
		drop = C68kScript.s16(((overlay_rng_next() & 0xffff) % 100) + 100)
	if memory.read_s16(memory.read_u32(ctl + 4)) == 0x1b and memory.read_u16(ctl + 0x69a) == 0xffff:
		if a < 0:
			a += 3
		a = C68kScript.s16(a) >> 2
	memory.write_u16(ctl + 0x66a, 0xf)
	memory.write_u16(ctl + 0x65e, C68kScript.u16(C68kScript.divs_trunc(a, 15)))
	memory.write_u16(ctl + 0x660, 0)
	memory.write_u16(ctl + 0x662, C68kScript.u16(C68kScript.divs_trunc(c, 15)))
	memory.write_u16(ctl + 0x666, C68kScript.u16(C68kScript.divs_trunc(drop, 15)))
	memory.write_u32(ctl + 0x66c, C68kScript.u32(memory.read_u32(0x21020) + 0x1e))
	memory.write_u16(ctl + 0x670, 1)

## approx_dist3_xyz (0x4886) leaves D1 = |dz|, or the larger of |dx| / |dy| when |dz| is larger still (its
## swap); apply_blast_damage hands that register on to apply_hit_to_sinkable_object.
static func _approx_dist3_d1(ax: int, ay: int, az: int, bx: int, by: int, bz: int) -> int:
	var d2: int = abs_int(C68kScript.s32(bx - ax))
	var d3: int = abs_int(C68kScript.s32(by - ay))
	var d1: int = abs_int(C68kScript.s32(bz - az))
	if C68kScript.s32(d3) > C68kScript.s32(d2):
		d2 = d3
	if C68kScript.s32(d1) > C68kScript.s32(d2):
		d1 = d2
	return C68kScript.u32(d1)

## 0x14fe6 (Leyte only; a2 saved): an entity destroyed with (credit, entity). In live play a credited kill is
## tallied (tally_destroyed_entity, 0xc below). Mission objectives: a type-10 entity (word +0x52) in mission 2
## (host word +0x90) counts once per target point it lies within 0x63 of (approx_dist3_xyz on x / z, the points
## (0x498eb4, -0x1d34a4), (0x4991d4, -0x1d324c), (0x4995ee, -0x1d33dc)) into word 0x27062, three of them setting
## host +0xd2 = 1 when 0; in mission 3 one within 0x63 of (0x5cf903, -0x3e0e) sets +0xd2 = 1 when 0. Other
## entities in mission 6: a type 0x28 controller definition counts into 0x27058 (the fifth sets +0xd2 = 1 when
## 0), a type 0x27 into 0x2705a (the fourth sets +0xd2 = -1 when not negative). Then dispatch_entity_cleanup
## (0xc below).
func leyte_destroy_entity(credit: int, entity: int) -> void:
	assert(memory != null, "leyte_destroy_entity requires original global memory")
	var entry_a7: int = a7
	if memory.read_u16(0x21058) == 0 and C68kScript.u32(credit) != 0:
		a7 = entry_a7 - 0xc
		tally_destroyed_entity(entity)
		a7 = entry_a7
	var host: int = memory.read_u32(0x207fc)
	var x: int = memory.read_s32(entity + 4)
	var z: int = memory.read_s32(entity + 0xc)
	if memory.read_s16(entity + 0x52) == 0xa:
		if memory.read_s16(host + 0x90) == 2:
			for p in [[0x498eb4, -0x1d34a4], [0x4991d4, -0x1d324c], [0x4995ee, -0x1d33dc]]:
				if C68kScript.s32(approx_dist3_xyz(x, 0, z, p[0], 0, p[1])) <= 0x63:
					memory.write_u16(0x27062, C68kScript.u16(memory.read_u16(0x27062) + 1))
			if memory.read_s16(0x27062) == 3 and memory.read_u16(host + 0xd2) == 0:
				memory.write_u16(host + 0xd2, 1)
		if memory.read_s16(host + 0x90) == 3:
			if C68kScript.s32(approx_dist3_xyz(x, 0, z, 0x5cf903, 0, -0x3e0e)) <= 0x63 and memory.read_u16(host + 0xd2) == 0:
				memory.write_u16(host + 0xd2, 1)
	else:
		var ctl: int = memory.read_u32(entity)
		var kind: int = memory.read_s16(memory.read_u32(ctl + 4)) if ctl != 0 else -1
		if kind == 0x28:
			if memory.read_s16(host + 0x90) == 6:
				memory.write_u16(0x27058, C68kScript.u16(memory.read_u16(0x27058) + 1))
				if memory.read_s16(0x27058) == 5 and memory.read_u16(host + 0xd2) == 0:
					memory.write_u16(host + 0xd2, 1)
		elif kind == 0x27:
			if memory.read_s16(host + 0x90) == 6:
				memory.write_u16(0x2705a, C68kScript.u16(memory.read_u16(0x2705a) + 1))
				if memory.read_s16(0x2705a) == 4 and memory.read_s16(host + 0xd2) >= 0:
					memory.write_u16(host + 0xd2, 0xffff)
	a7 = entry_a7 - 0xc
	dispatch_entity_cleanup(entity)
	a7 = entry_a7

## 0x15192 (link 0, ten saved registers): a blast at (x, y, z), Leyte's. For each object_pool object (0x20804,
## count word 0x20b26): its controller (+0) definition type word is kept in D3, which an object without a
## controller leaves as the previous one set it (d3_in before the first). A flagged object (long +0x14 &
## 0x10242) is considered only when it is the player's, or has the bit 0x10000 and a type 0x23 / 0x24 controller.
## Range (long +0x20) above the radius: only ships with hit_ships (types 0x1b..0x22; mode 2 only a carrier with
## its deck tag unknown, 0x1d, 0x20 or 0x21) within 1.5 times the range take apply_hit_to_sinkable_object. Else,
## within range + 0x7cf (approx_dist3_xyz from the position +4 / +8 / +0xc): inside the range a hit; aircraft
## (type <= 7) are hit when not sure_hit and the overshoot is within rng & 0x3ff, else they take
## apply_random_aircraft_damage; type 0x12 always; types 0x27 / 0x28 within 0x5db unless sure_hit; others within
## 0x257. A hit ship (0x1b..0x22) takes apply_hit_to_sinkable_object (its drop register the D1 approx_dist3_xyz
## left), an aircraft damage word +0x698 |= 0x400 (| 0x8000 when credited), type 0x12 word +0x638 |= 0x400;
## anything else spawn_destruction_effect(range / 8 on the ground (y 0), else / 4) and leyte_destroy_entity.
## Calls are entered 0x38 below.
func apply_blast_damage(x: int, y: int, z: int, radius: int, credit: int, sure_hit: int, hit_ships: int, d3_in: int = 0) -> void:
	assert(memory != null, "apply_blast_damage requires original global memory")
	var entry_a7: int = a7
	x = C68kScript.s32(x)
	y = C68kScript.s32(y)
	z = C68kScript.s32(z)
	var d3: int = C68kScript.s16(d3_in)
	var left: int = memory.read_u16(0x20b26)
	var slot: int = 0x20804
	while left != 0:
		left -= 1
		var obj: int = memory.read_u32(slot)
		slot += 4
		var ctl: int = memory.read_u32(obj)
		if ctl != 0:
			d3 = memory.read_s16(memory.read_u32(ctl + 4))
		if (memory.read_u32(obj + 0x14) & 0x10242) != 0:
			var flagged_wreck: bool = (memory.read_u8(obj + 0x15) & 1) != 0 and ctl != 0 and (d3 == 0x24 or d3 == 0x23)
			if not flagged_wreck and (ctl == 0 or ctl != memory.read_u32(0x20b28)):
				continue
		var rng: int = memory.read_s32(obj + 0x20)
		var ox: int = memory.read_s32(obj + 4)
		var oy: int = memory.read_s32(obj + 8)
		var oz: int = memory.read_s32(obj + 0xc)
		if rng > C68kScript.s32(radius):
			if C68kScript.u32(hit_ships) == 0 or ctl == 0 or d3 <= 0x1a or d3 > 0x22:
				continue
			if (hit_ships & 0xffff) == 0:
				continue                         # the mode is tested as a word (move.w d6, d0 / tst.w d0)
			if (hit_ships & 0xffff) == 2:
				var ok: bool = (d3 == 0x1b and memory.read_u16(ctl + 0x69a) == 0xffff) or d3 == 0x1d or d3 == 0x20 or d3 == 0x21
				if not ok:
					continue
			var d2: int = C68kScript.s32(approx_dist3_xyz(ox, oy, oz, x, y, z))
			var half: int = rng + 1 if rng < 0 else rng
			if C68kScript.s32((half >> 1) + rng) <= d2:
				continue
			a7 = entry_a7 - 0x38
			apply_hit_to_sinkable_object(ctl, credit, _approx_dist3_d1(ox, oy, oz, x, y, z))
			a7 = entry_a7
			continue
		var dist: int = C68kScript.s32(approx_dist3_xyz(ox, oy, oz, x, y, z))
		var over: int = C68kScript.s32(dist - rng)
		if over > 0x7cf:
			continue
		var hit: bool
		if dist < rng:
			hit = true
		elif ctl != 0 and d3 <= 7:
			hit = false
			if C68kScript.u32(sure_hit) == 0 and over <= (overlay_rng_next() & 0x3ff):
				hit = true
			else:
				a7 = entry_a7 - 0x38
				apply_random_aircraft_damage(ctl, credit)
				a7 = entry_a7
		elif ctl != 0 and d3 == 0x12:
			hit = over <= 0x7cf
		elif ctl != 0 and (d3 == 0x27 or d3 == 0x28):
			hit = C68kScript.u32(sure_hit) == 0 and over <= 0x5db
		else:
			hit = over <= 0x257
		if not hit:
			continue
		if ctl != 0 and d3 > 0x1a and d3 <= 0x22:
			a7 = entry_a7 - 0x38
			apply_hit_to_sinkable_object(ctl, credit, _approx_dist3_d1(ox, oy, oz, x, y, z))
			a7 = entry_a7
		elif ctl != 0 and d3 <= 7:
			if C68kScript.u32(credit) != 0:
				memory.write_u16(ctl + 0x698, memory.read_u16(ctl + 0x698) | 0x8000)
			memory.write_u16(ctl + 0x698, memory.read_u16(ctl + 0x698) | 0x400)
		elif ctl != 0 and d3 == 0x12:
			memory.write_u16(ctl + 0x638, memory.read_u16(ctl + 0x638) | 0x400)
		else:
			var size: int = rng
			if memory.read_u32(obj + 8) == 0:
				if size < 0:
					size += 7
				size = size >> 3
			else:
				if size < 0:
					size += 3
				size = size >> 2
			a7 = entry_a7 - 0x38
			spawn_destruction_effect(obj, C68kScript.u32(size))
			leyte_destroy_entity(credit, obj)
			a7 = entry_a7

## 0x1cdce (link a6 -0x1c, ten saved registers): the flak gun, Leyte's (1.0.3's 0x18c76 with Leyte's object
## layout). A retired gun (flags byte +0x17 bit 1) releases its target (+0x36; reference count +0x29 - 1).
## Once (word +0x40): a gun object with long +0x14 bit 20 aims along its controller heading (+0x54 = 0, +0x56 =
## word +0x22, +0x58 = 0) from its position (+0x48.. = object +4 / +8 / +0xc); otherwise the first face (+0x4a,
## +0x3c of them, 0x20 apart) whose byte +0x17 is 1 gives the barrel: the mean of its vertices (+0x46, each asr
## word +0x36; divs.l by the word count +0x14) at +0x48 / +0x4c / +0x50, and the aim of its normal (the normals
## follow the +0x38 vertices) minus vertex 0 (attitude_from_velocity with speed 0x4000 and 0x10000000 into a6 - 2
## / a6 - 4, 0x68 below) at +0x54 / +0x56, +0x58 = 0; then word +0x3e = probe_nearest_facility_kind (0x50 below).
## With a target in live play every due burst slot (long +0xd2 + 4i, 0..9, 0 < due <= frame) is cleared and
## bursts at +0x5a + 12i: spawn_effect(2, 100, 0x1e, 0, 0) (0x6c below), apply_blast_damage(500, 0, 1, 0) (0x68
## below; its D3 is this routine's, d3_in unless the barrel search above ran). No shells (word +0x46) or a retired
## / vertex-less target releases it. Else one draw in 64 (& 0x3f == 0x15) tries a shot: distance +0x3a within
## range +0x42; the offset, shifted below 0x8000 + 1, aimed from the low words' squares (isqrt 0x50 below,
## attitude into a6 - 6 / a6 - 8, 0x68 below); a pitch above 0x3f and a heading within 0x1000 of word +0x56 fire
## into the first free slot: shells - 1, due = frame + word(distance / 1024 toward zero), and the burst point =
## target position + lead * the target controller's +8 / +0xc / +0x10 - 0x800 + (rng & 0xfff) each, lead = due
## offset + (rng & 0x3f), y at least the target controller's ground +0x2e + 500.
func tick_flak_gun(ctl: int, d3_in: int = 0) -> void:
	assert(memory != null, "tick_flak_gun requires original global memory")
	assert(a7 != 0, "tick_flak_gun needs a7 (its frame holds the attitude words)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var d3: int = C68kScript.u32(d3_in)
	var obj: int = memory.read_u32(ctl)
	if (memory.read_u8(obj + 0x17) & 2) != 0:
		var t: int = memory.read_u32(ctl + 0x36)
		if t != 0:
			memory.write_u8(t + 0x29, (memory.read_u8(t + 0x29) - 1) & 0xff)
			memory.write_u32(ctl + 0x36, 0)
		return
	if memory.read_u16(ctl + 0x40) == 0:
		if (memory.read_u8(obj + 0x15) & 0x10) != 0:
			memory.write_u16(ctl + 0x54, 0)
			memory.write_u16(ctl + 0x56, memory.read_u16(ctl + 0x22))
			memory.write_u16(ctl + 0x58, 0)
			memory.write_u32(ctl + 0x48, memory.read_u32(obj + 4))
			memory.write_u32(ctl + 0x4c, memory.read_u32(obj + 8))
			memory.write_u32(ctl + 0x50, memory.read_u32(obj + 0xc))
		else:
			var normals: int = C68kScript.u32(memory.read_s16(obj + 0x38) * 12 + memory.read_u32(obj + 0x46))
			var face: int = memory.read_u32(obj + 0x4a)
			var i: int = 0
			while i < memory.read_s16(obj + 0x3c):
				if memory.read_u8(face + 0x17) == 1:
					var sums := [0, 0, 0]
					var idx: int = memory.read_u32(face + 0x18)
					var left: int = memory.read_u16(face + 0x14)
					var shift: int = memory.read_s16(obj + 0x36) & 0x3f
					while left != 0:
						left -= 1
						var vert: int = C68kScript.u32(memory.read_u32(obj + 0x46) + memory.read_s16(idx) * 12)
						idx += 2
						for k in range(3):
							sums[k] = C68kScript.s32(sums[k] + C68kScript.asr32(memory.read_s32(vert + 4 * k), shift))
					d3 = (d3 & 0xffff0000) | 0xffff
					var count: int = memory.read_s16(face + 0x14)
					for k in range(3):
						memory.write_u32(ctl + 0x48 + 4 * k, C68kScript.u32(C68kScript.divs_trunc(sums[k], count)))
					var v0: int = memory.read_u32(obj + 0x46)
					var rel := []
					for k in range(3):
						rel.append(C68kScript.s32(memory.read_u32(normals + i * 12 + 4 * k) - memory.read_u32(v0 + 4 * k)))
					a7 = entry_a7 - 0x68
					attitude_from_velocity(C68kScript.u32(rel[0]), C68kScript.u32(rel[1]), C68kScript.u32(rel[2]), 0x4000, 0x10000000, a6 - 2, a6 - 4)
					a7 = entry_a7
					memory.write_u16(ctl + 0x54, memory.read_u16(a6 - 2))
					memory.write_u16(ctl + 0x56, memory.read_u16(a6 - 4))
					memory.write_u16(ctl + 0x58, 0)
					break
				i += 1
				face += 0x20
		a7 = entry_a7 - 0x50
		memory.write_u16(ctl + 0x3e, C68kScript.u16(probe_nearest_facility_kind(obj)))
		a7 = entry_a7
		memory.write_u16(ctl + 0x40, 1)
	if memory.read_u32(ctl + 0x36) == 0 or memory.read_u16(0x21058) != 0:
		return
	for slot in range(10):
		var due: int = memory.read_s32(ctl + 0xd2 + 4 * slot)
		if due != 0 and due <= memory.read_s32(0x21020):
			memory.write_u32(ctl + 0xd2 + 4 * slot, 0)
			var p: int = ctl + 0x5a + 12 * slot
			a7 = entry_a7 - 0x6c
			spawn_effect(2, 100, 0x1e, 0, 0, memory.read_u32(p), memory.read_u32(p + 4), memory.read_u32(p + 8))
			a7 = entry_a7 - 0x68
			apply_blast_damage(memory.read_u32(p), memory.read_u32(p + 4), memory.read_u32(p + 8), 500, 0, 1, 0, d3 & 0xffff)
			a7 = entry_a7
	var target: int = memory.read_u32(ctl + 0x36)
	if memory.read_u16(ctl + 0x46) == 0 or (memory.read_u8(target + 0x17) & 2) != 0 or memory.read_u32(target + 0x46) == 0:
		memory.write_u8(target + 0x29, (memory.read_u8(target + 0x29) - 1) & 0xff)
		memory.write_u32(ctl + 0x36, 0)
		return
	if (overlay_rng_next() & 0x3f) != 0x15:
		return
	var dist: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc),
			memory.read_s32(target + 4), memory.read_s32(target + 8), memory.read_s32(target + 0xc)))
	memory.write_u32(ctl + 0x3a, C68kScript.u32(dist))
	if dist > memory.read_s32(ctl + 0x42):
		return
	var shift2: int = 0
	var dd: int = C68kScript.u32(dist)
	while (dd & 0xffff8000) != 0:
		shift2 = C68kScript.s16(shift2 + 1)
		dd = C68kScript.u32(C68kScript.asr32(C68kScript.s32(dd), 1))
	shift2 = C68kScript.s16(shift2 + 1)
	var off := []
	for k in range(3):
		off.append(C68kScript.asr32(C68kScript.s32(memory.read_u32(target + 4 + 4 * k) - memory.read_u32(obj + 4 + 4 * k)), shift2 & 0x3f))
	var sq: int = 0
	for k in range(3):
		sq = C68kScript.s32(sq + C68kScript.s16(off[k]) * C68kScript.s16(off[k]))
	a7 = entry_a7 - 0x50
	var len: int = isqrt_floor(C68kScript.u32(sq))
	a7 = entry_a7 - 0x68
	attitude_from_velocity(C68kScript.u32(C68kScript.s16(off[0])), C68kScript.u32(C68kScript.s16(off[1])), C68kScript.u32(C68kScript.s16(off[2])),
			C68kScript.u32(C68kScript.s16(len)), C68kScript.u32(sq), a6 - 6, a6 - 8)
	a7 = entry_a7
	if memory.read_s16(a6 - 6) <= 0x3f:
		return
	a7 = entry_a7 - 0x50
	var turn: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(memory.read_s16(a6 - 8) - memory.read_s16(ctl + 0x56))))
	a7 = entry_a7
	if abs_int(turn) > 0x1000:
		return
	var free_slot: int = -1
	for slot2 in range(10):
		if memory.read_u32(ctl + 0xd2 + 4 * slot2) == 0:
			free_slot = slot2
			break
	if free_slot == -1:
		return
	memory.write_u16(ctl + 0x46, C68kScript.u16(memory.read_u16(ctl + 0x46) - 1))
	var fuse_src: int = memory.read_s32(ctl + 0x3a)
	if fuse_src < 0:
		fuse_src = C68kScript.s32(fuse_src + 0x3ff)
	var fuse: int = C68kScript.s16(fuse_src >> 10)
	memory.write_u32(ctl + 0xd2 + 4 * free_slot, C68kScript.u32(fuse + memory.read_u32(0x21020)))
	var lead: int = C68kScript.s16(fuse + (overlay_rng_next() & 0x3f))
	var target_ctl: int = memory.read_u32(target)
	var burst: int = ctl + 0x5a + 12 * free_slot
	for k in range(3):
		var jitter: int = overlay_rng_next() & 0xfff
		memory.write_u32(burst + 4 * k, C68kScript.u32(C68kScript.s32(lead * memory.read_s32(target_ctl + 8 + 4 * k)) + memory.read_u32(target + 4 + 4 * k) - 0x800 + jitter))
	if C68kScript.s32(memory.read_u32(burst + 4) - 500) < memory.read_s32(target_ctl + 0x2e):
		memory.write_u32(burst + 4, C68kScript.u32(memory.read_u32(target_ctl + 0x2e) + 500))

## 0x1ec2e (Leyte only; link 0): spawn_mission_pool_object(0x2cdd4[cls], x, y, z, a1, a2, a3, 0) (0x28 below);
## the new object's long +0x18 = 1000000000; returns its controller (+0), 0 when nothing was spawned.
func leyte_spawn_wake_object(x: int, y: int, z: int, a1: int, a2: int, a3: int, cls: int) -> int:
	assert(memory != null, "leyte_spawn_wake_object requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x28
	var obj: int = C68kScript.u32(spawn_mission_pool_object(memory.read_u32(C68kScript.u32(0x2cdd4 + C68kScript.s32(cls) * 4)), x, y, z, a1, a2, a3, 0))
	a7 = entry_a7
	if obj == 0:
		return 0
	memory.write_u32(obj + 0x18, 1000000000)
	return memory.read_u32(obj)

## 0x1eda0 (Leyte only; link 0): spawn_mission_pool_object(0x2cd68[cls], the six long arguments, 0) (0x28 below);
## its controller's word +0x62a = the word argument; returns the controller, 0 when nothing was spawned.
func leyte_spawn_tagged_object(cls: int, tag: int, x: int, y: int, z: int, a1: int, a2: int, a3: int) -> int:
	assert(memory != null, "leyte_spawn_tagged_object requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x28
	var obj: int = C68kScript.u32(spawn_mission_pool_object(memory.read_u32(C68kScript.u32(0x2cd68 + C68kScript.s32(cls) * 4)), x, y, z, a1, a2, a3, 0))
	a7 = entry_a7
	if obj == 0:
		return 0
	var ctl: int = memory.read_u32(obj)
	memory.write_u16(ctl + 0x62a, C68kScript.u16(tag))
	return ctl

## 0x18f08 (Leyte only; three saved registers): a moving ship (word +0x32 set) keeps its wake object (+0x68e) at its
## position: the wake controller's heading word +0x22 = the heading word, +8 / +0x10 = ship - wake x / z, +0xc = 0
## (a wake without a controller is left alone). Without one, leyte_spawn_wake_object(ship x / y / z, word +0x20, the
## heading, word +0x24, definition type - 0x1b) (0x2c below) gives it; its object's reference count +0x29 + 1. (A
## failed spawn returns 0, and the code then reads the long at address 0 as the object, as the original does.)
func leyte_update_ship_wake(ctl: int, heading: int) -> void:
	assert(memory != null, "leyte_update_ship_wake requires original global memory")
	var entry_a7: int = a7
	if memory.read_u16(ctl + 0x32) == 0:
		return
	var obj: int = memory.read_u32(ctl)
	var wake: int = memory.read_u32(ctl + 0x68e)
	if wake != 0:
		var wake_ctl: int = memory.read_u32(wake)
		if wake_ctl == 0:
			return
		memory.write_u16(wake_ctl + 0x22, heading & 0xffff)
		memory.write_u32(wake_ctl + 8, C68kScript.u32(memory.read_u32(obj + 4) - memory.read_u32(wake + 4)))
		memory.write_u32(wake_ctl + 0xc, 0)
		memory.write_u32(wake_ctl + 0x10, C68kScript.u32(memory.read_u32(obj + 0xc) - memory.read_u32(wake + 0xc)))
		return
	a7 = entry_a7 - 0x2c
	var spawned: int = leyte_spawn_wake_object(memory.read_u32(obj + 4), memory.read_u32(obj + 8), memory.read_u32(obj + 0xc),
			C68kScript.u32(memory.read_s16(ctl + 0x20)), C68kScript.u32(C68kScript.s16(heading)), C68kScript.u32(memory.read_s16(ctl + 0x24)),
			C68kScript.u32(memory.read_s16(memory.read_u32(ctl + 4)) - 0x1b))
	a7 = entry_a7
	memory.write_u32(ctl + 0x68e, memory.read_u32(spawned))
	var count: int = C68kScript.u32(memory.read_u32(ctl + 0x68e) + 0x29)
	memory.write_u8(count, (memory.read_u8(count) + 1) & 0xff)

## 0x18f94 (Leyte only; five saved registers): a ship's controller tick. The pose ring at +0x3c (75 records of 0x14:
## x, y, z, heading word +0xe, word +0x12; cursor byte +0x618) advances. Live play: the mover (+0x38, jsr, 0x1c
## below), the wake (leyte_update_ship_wake with heading word +0x22, 0x24 below), then the record takes word +0x654,
## the object position and word +0x692. Replaying (and not sinking, word +0x670): the record's x / z drift goes
## into +0x624 / +0x62c (byte +0x619 = 1) and the object moves there; a heading change from the previous record
## reshapes the ship (0x18784, 0x20 below) and clears that drift; word +0x692 = the record's +0x12, the wake follows
## heading (+0x22 - +0x654 + the record's) & 0x3fff (0x20 below), word +0x694 = that +0x692 too.
func leyte_ship_tick(ctl: int) -> void:
	assert(memory != null, "leyte_ship_tick requires original global memory")
	var entry_a7: int = a7
	var obj: int = memory.read_u32(ctl)
	var idx: int = memory.read_u8(ctl + 0x618)
	var rec: int = ctl + 0x3c + idx * 0x14
	memory.write_u8(ctl + 0x618, (idx + 1) & 0xff)
	if memory.read_u8(ctl + 0x618) == 0x4b:
		memory.write_u8(ctl + 0x618, 0)
	if memory.read_u16(0x21058) != 0:
		if memory.read_u16(ctl + 0x670) != 0:
			return
		memory.write_u32(ctl + 0x624, C68kScript.u32(memory.read_u32(ctl + 0x624) + memory.read_u32(rec) - memory.read_u32(obj + 4)))
		memory.write_u32(ctl + 0x62c, C68kScript.u32(memory.read_u32(ctl + 0x62c) + memory.read_u32(rec + 8) - memory.read_u32(obj + 0xc)))
		memory.write_u8(ctl + 0x619, 1)
		memory.write_u32(obj + 4, memory.read_u32(rec))
		memory.write_u32(obj + 0xc, memory.read_u32(rec + 8))
		var prev: int = ctl + 0x604 if rec == ctl + 0x3c else rec - 0x14
		if memory.read_u16(prev + 0xe) != memory.read_u16(rec + 0xe):
			a7 = entry_a7 - 0x20
			leyte_reshape_ship(ctl, C68kScript.u32(memory.read_s16(rec + 0xe)))
			a7 = entry_a7
			memory.write_u32(ctl + 0x624, 0)
			memory.write_u32(ctl + 0x628, 0)
			memory.write_u32(ctl + 0x62c, 0)
			memory.write_u8(ctl + 0x619, 0)
		memory.write_u16(ctl + 0x692, memory.read_u16(rec + 0x12))
		var tag: int = memory.read_u16(ctl + 0x692)
		a7 = entry_a7 - 0x20
		leyte_update_ship_wake(ctl, C68kScript.u32(memory.read_s16(ctl + 0x22) - memory.read_s16(ctl + 0x654) + memory.read_s16(rec + 0xe)) & 0x3fff)
		a7 = entry_a7
		memory.write_u16(ctl + 0x694, tag)
		return
	a7 = entry_a7 - 0x1c
	_jsr(memory.read_u32(ctl + 0x38), [ctl])
	a7 = entry_a7 - 0x24
	leyte_update_ship_wake(ctl, C68kScript.u32(memory.read_s16(ctl + 0x22)))
	a7 = entry_a7
	memory.write_u16(rec + 0xe, memory.read_u16(ctl + 0x654))
	memory.write_u32(rec, memory.read_u32(obj + 4))
	memory.write_u32(rec + 4, memory.read_u32(obj + 8))
	memory.write_u32(rec + 8, memory.read_u32(obj + 0xc))
	memory.write_u16(rec + 0x12, memory.read_u16(ctl + 0x692))

## 0x18784 (Leyte only; link a6 -0x24, eight saved registers): reshape a ship for a heading word (a replay heading
## change). The template (+0x620; vertex + normal count = words +0x1a + +0x1e, vectors from +0x22) is turned by
## (build_attitude_matrix_3ang(0, heading, 0) into a6 - 0x12, 0x5c below) * the cached chain +0x630 (matmul3x3_s16
## into a6 - 0x24, 0x68 below) into the object's vertices +0x46 (transform_vecs12_to_int3_by_matrix3, 0x78 below),
## then translated by (object position - controller +0x14 / +0x18 / +0x1c + +0x642 / +0x646 / +0x64a) << word +0x36
## (translate_int3_vecs, 0x64 below). With byte +0x61a set, the object's buffers +0x4e / +0x46 grow to 12 per vector
## (heap_realloc_grow_only, 0x54 / 0x5c below) and each of the template's +0x1e face records (after the vectors;
## count byte +8, 0xe-byte head) resizes its face's list (+0x4a + 32 i: +0x18 to 2 * count, 0x54 below; +0x14 =
## count) and fills it with the word at the record's list + 2 * count (D1 holds the count, not the index), the next
## record lying 2 * the byte +8 of the advanced pointer further; word +0x38 = the vertex count, +0x61a = 0. Then
## shade_object_faces when host +0xb2 (0x50 below) and drop_contact_records_for_object (0x50 below).
func leyte_reshape_ship(ctl: int, heading: int) -> void:
	assert(memory != null, "leyte_reshape_ship requires original global memory")
	assert(a7 != 0, "leyte_reshape_ship needs a7 (its frame holds the matrices)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var obj: int = memory.read_u32(ctl)
	var tmpl: int = memory.read_u32(ctl + 0x620)
	var count: int = C68kScript.s16(memory.read_u16(tmpl + 0x1a) + memory.read_u16(tmpl + 0x1e))
	var turn: int = a6 - 0x12
	var frame: int = a6 - 0x24
	a7 = entry_a7 - 0x5c
	build_attitude_matrix_3ang(0, C68kScript.s16(heading), 0, turn)
	a7 = entry_a7 - 0x68
	matmul3x3_s16(ctl + 0x630, turn, frame)
	a7 = entry_a7 - 0x78
	transform_vecs12_to_int3_by_matrix3(C68kScript.u32(count), C68kScript.u32(tmpl + 0x22), memory.read_u32(obj + 0x46), frame)
	var shift: int = memory.read_s16(obj + 0x36) & 0x3f
	var tx: int = C68kScript.u32(C68kScript.lsl32(C68kScript.u32(memory.read_u32(obj + 4) - memory.read_u32(ctl + 0x14) + memory.read_u32(ctl + 0x642)), shift))
	var ty: int = C68kScript.u32(C68kScript.lsl32(C68kScript.u32(memory.read_u32(obj + 8) - memory.read_u32(ctl + 0x18) + memory.read_u32(ctl + 0x646)), shift))
	var tz: int = C68kScript.u32(C68kScript.lsl32(C68kScript.u32(memory.read_u32(obj + 0xc) - memory.read_u32(ctl + 0x1c) + memory.read_u32(ctl + 0x64a)), shift))
	a7 = entry_a7 - 0x64
	translate_int3_vecs(C68kScript.u32(count), memory.read_u32(obj + 0x46), memory.read_u32(obj + 0x46), tx, ty, tz)
	a7 = entry_a7
	if memory.read_u8(ctl + 0x61a) != 0:
		var size: int = C68kScript.u32(count * 12)
		a7 = entry_a7 - 0x54
		memory.write_u32(obj + 0x4e, C68kScript.u32(heap_realloc_grow_only(memory.read_u32(obj + 0x4e), size)))
		a7 = entry_a7 - 0x5c
		memory.write_u32(obj + 0x46, C68kScript.u32(heap_realloc_grow_only(memory.read_u32(obj + 0x46), size)))
		a7 = entry_a7
		var rec: int = C68kScript.u32(tmpl + size + 0x22)
		var i: int = 0
		while i < memory.read_s16(memory.read_u32(ctl + 0x620) + 0x1e):
			var n: int = memory.read_u8(rec + 8)
			var face: int = C68kScript.u32(memory.read_u32(obj + 0x4a) + i * 32)
			a7 = entry_a7 - 0x54
			memory.write_u32(face + 0x18, C68kScript.u32(heap_realloc_grow_only(memory.read_u32(face + 0x18), n * 2)))
			a7 = entry_a7
			memory.write_u16(face + 0x14, n)
			rec += 0xe
			for k in range(n):
				memory.write_u16(memory.read_u32(face + 0x18) + 2 * k, memory.read_u16(rec + 2 * n))
			rec += memory.read_u8(rec + 8) * 2
			i += 1
		memory.write_u16(obj + 0x38, memory.read_u16(memory.read_u32(ctl + 0x620) + 0x1a))
		memory.write_u8(ctl + 0x61a, 0)
	if memory.read_u16(memory.read_u32(0x207fc) + 0xb2) != 0:
		a7 = entry_a7 - 0x50
		shade_object_faces(obj)
		a7 = entry_a7
	a7 = entry_a7 - 0x50
	drop_contact_records_for_object(obj)
	a7 = entry_a7

## 0x2d08 (link a6 -0x20, six saved registers): Leyte's finalize_attitude_matrix_4ang, a closed form over the
## sine / cosine words load_attitude_angle_sincos leaves at 0x23d1e..0x23d2c (1.0.3 multiplied three basis
## matrices). Products are muls.w of the registers' low words, sums 32-bit, each term asr.l 12; the two
## intermediates kept in the frame are re-read as their low words (a6 - 0x14 / - 0x1a).
func finalize_attitude_matrix_4ang(out: int) -> void:
	assert(memory != null, "finalize_attitude_matrix_4ang requires original global memory")
	var g1e: int = memory.read_s16(0x23d1e)
	var g20: int = memory.read_s16(0x23d20)
	var g22: int = memory.read_s16(0x23d22)
	var g24: int = memory.read_s16(0x23d24)
	var g26: int = memory.read_s16(0x23d26)
	var g28: int = memory.read_s16(0x23d28)
	var g2a: int = memory.read_s16(0x23d2a)
	var g2c: int = memory.read_s16(0x23d2c)
	var d1: int = C68kScript.asr32(g2c * g28, 12)
	var t14: int = C68kScript.s16(C68kScript.asr32(g20 * g28, 12))
	var d0: int = C68kScript.asr32(g1e * g26, 12)
	var d2: int = C68kScript.asr32(g2c * g24, 12)
	var d5: int = C68kScript.asr32(g1e * g2a, 12)
	var t1a: int = C68kScript.s16(C68kScript.asr32(g20 * g24, 12))
	var v: int = C68kScript.s32(C68kScript.s16(d0) * g22 - t14 * g2a)
	v = C68kScript.s32(v + C68kScript.s16(C68kScript.asr32(g1e * g24, 12)) * C68kScript.s16(d1))
	memory.write_u16(out, C68kScript.u16(C68kScript.asr32(v, 12)))
	v = C68kScript.s32(g20 * g2c + C68kScript.s16(d5) * g24)
	memory.write_u16(out + 2, C68kScript.u16(C68kScript.asr32(v, 12)))
	v = C68kScript.s32(C68kScript.s16(C68kScript.asr32(g2a * g26, 12)) * g20 - C68kScript.s16(d0) * C68kScript.s16(d2))
	v = C68kScript.s32(v + C68kScript.s16(C68kScript.asr32(g1e * g22, 12)) * g28)
	memory.write_u16(out + 4, C68kScript.u16(C68kScript.asr32(v, 12)))
	v = C68kScript.s32(C68kScript.s16(d1) * g22 - g26 * g24)
	memory.write_u16(out + 6, C68kScript.u16(C68kScript.asr32(v, 12)))
	memory.write_u16(out + 8, C68kScript.u16(C68kScript.asr32(g2a * g22, 12)))
	var d6: int = C68kScript.asr32(g26 * g22, 12)
	v = C68kScript.s32(-(C68kScript.s16(d6) * g2c) - g28 * g24)
	memory.write_u16(out + 0xa, C68kScript.u16(C68kScript.asr32(v, 12)))
	v = C68kScript.s32(-(C68kScript.s16(d1) * t1a) - C68kScript.s16(d6) * g20)
	v = C68kScript.s32(v - C68kScript.s16(d5) * g28)
	memory.write_u16(out + 0xc, C68kScript.u16(C68kScript.asr32(v, 12)))
	v = C68kScript.s32(g1e * g2c - t1a * g2a)
	memory.write_u16(out + 0xe, C68kScript.u16(C68kScript.asr32(v, 12)))
	v = C68kScript.s32(C68kScript.s16(d0) * g2a - t14 * g22)
	v = C68kScript.s32(v + C68kScript.s16(C68kScript.asr32(C68kScript.s16(d2) * g26, 12)) * g20)
	memory.write_u16(out + 0x10, C68kScript.u16(C68kScript.asr32(v, 12)))

## 0x2ec4 (link 0): Leyte's build_attitude_matrix(angle4, angle1, angle2, angle3, out):
## load_attitude_angle_sincos(angle1, angle2, angle3, angle4) (0x18 below), then finalize_attitude_matrix_4ang(out)
## (0x1c below); 1.0.3's also filled the rotation bases in between.
func build_attitude_matrix(angle4: int, angle1: int, angle2: int, angle3: int, out: int) -> void:
	assert(a7 != 0, "build_attitude_matrix needs a7 (its callees are entered below it)")
	var entry_a7: int = a7
	a7 = entry_a7 - 0x18
	load_attitude_angle_sincos(angle1, angle2, angle3, angle4)
	a7 = entry_a7 - 0x1c
	finalize_attitude_matrix_4ang(out)
	a7 = entry_a7

## 0x1756a (link a6 -0x3c, ten saved registers): Leyte's carry_attached_objects(ctl, a1, a2, a3, remove), one argument fewer than 1.0.3's. With h =
## the ship's heading word +0x22: matrix = build_attitude_matrix(-h & 0x3fff, a1, (a2 + h) & 0x3fff, a3) in a6 - 0x12
## (0x80 below). Every live (long +0x14 & 0x202 clear) object_pool object within the ship object's range (+0x20;
## approx_dist3_xyz from (ship x, controller +0x18, ship z)) whose controller's definition type is 0xa or 0xb, then
## every live one in the controller's list (+0x67a, word count +0x678) within that range, is cleaned up when
## removing (dispatch_entity_cleanup, 0x70 below), else placed again: position cleared, apply_template_chain_xform(
## obj, 1, 0, 0) (0x7c below), the offset words (its position's low words - the ship's x / +0x18 / z low words, for the
## list objects the controller's +0x16 / +0x1a / +0x1e) turned in the frame record (a6 - 0x1e, list: a6 - 0x2a; for
## the list first by build_attitude_matrix_3ang(0, word +0x654, 0) into a6 - 0x3c, 0x8c below) by
## transform_vecs12_by_matrix3 (0x8c / 0x7c below); x / z = ship + sext offset, y = sext offset + ship y - controller
## +0x18; a negative y cleans the object up.
func leyte_carry_attached_objects(ctl: int, a1: int, a2: int, a3: int, remove: int) -> void:
	assert(memory != null, "leyte_carry_attached_objects requires original global memory")
	assert(a7 != 0, "leyte_carry_attached_objects needs a7 (its frame holds the matrices)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var ship: int = memory.read_u32(ctl)
	var sx: int = memory.read_u32(ship + 4)
	var sy: int = memory.read_u32(ctl + 0x18)
	var sz: int = memory.read_u32(ship + 0xc)
	var h: int = memory.read_s16(ctl + 0x22)
	var matrix: int = a6 - 0x12
	a7 = entry_a7 - 0x80
	build_attitude_matrix(C68kScript.u32(-h) & 0x3fff, C68kScript.u32(C68kScript.s16(a1)), C68kScript.u32(C68kScript.s16(a2) + h) & 0x3fff,
			C68kScript.u32(C68kScript.s16(a3)), matrix)
	a7 = entry_a7
	var reach: int = memory.read_s32(ship + 0x20)
	var left: int = memory.read_u16(0x20b26)
	var slot: int = 0x20804
	var rec: int = a6 - 0x1e
	while left != 0:
		left -= 1
		var obj: int = memory.read_u32(slot)
		slot += 4
		if (memory.read_u32(obj + 0x14) & 0x202) != 0:
			continue
		if C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc),
				C68kScript.s32(sx), C68kScript.s32(sy), C68kScript.s32(sz))) >= reach:
			continue
		var octl: int = memory.read_u32(obj)
		if octl == 0:
			continue
		var kind: int = memory.read_u16(memory.read_u32(octl + 4))
		if kind != 0xa and kind != 0xb:
			continue
		if C68kScript.u32(remove) != 0:
			a7 = entry_a7 - 0x70
			dispatch_entity_cleanup(obj)
			a7 = entry_a7
			continue
		_carry_one(obj, entry_a7, rec, [sx, sy, sz], [sx, sy, sz], -1, matrix, ship)
	var i: int = 0
	var rec2: int = a6 - 0x2a
	while i < memory.read_s16(ctl + 0x678):
		var obj2: int = memory.read_u32(ctl + 0x67a + 4 * i)
		i += 1
		if obj2 == 0 or (memory.read_u32(obj2 + 0x14) & 0x202) != 0:
			continue
		if C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj2 + 4), memory.read_s32(obj2 + 8), memory.read_s32(obj2 + 0xc),
				C68kScript.s32(sx), C68kScript.s32(sy), C68kScript.s32(sz))) >= reach:
			continue
		if C68kScript.u32(remove) != 0:
			a7 = entry_a7 - 0x70
			dispatch_entity_cleanup(obj2)
			a7 = entry_a7
			continue
		_carry_one(obj2, entry_a7, rec2, [memory.read_u32(ctl + 0x14), memory.read_u32(ctl + 0x18), memory.read_u32(ctl + 0x1c)],
				[sx, sy, sz], memory.read_s16(ctl + 0x654), matrix, ship)

## carry_attached_objects: one object placed again. The offsets are low words (suba.w) of its chain position
## less `base`; a heading >= 0 (the controller list) first turns them by build_attitude_matrix_3ang(0, heading, 0)
## into a6 - 0x3c.
func _carry_one(obj: int, entry_a7: int, rec: int, base: Array, ship_xyz: Array, heading: int, matrix: int, ship: int) -> void:
	memory.write_u32(obj + 0xc, 0)
	memory.write_u32(obj + 8, 0)
	memory.write_u32(obj + 4, 0)
	a7 = entry_a7 - 0x7c
	apply_template_chain_xform(obj, 1, 0, 0)
	for k in range(3):
		memory.write_u16(rec + 2 + 4 * k, C68kScript.u16(memory.read_s16(obj + 6 + 4 * k) - C68kScript.s16(base[k] & 0xffff)))
	if heading != -1:
		var turn: int = entry_a7 - 4 - 0x3c
		a7 = entry_a7 - 0x8c
		build_attitude_matrix_3ang(0, heading, 0, turn)
		a7 = entry_a7 - 0x7c
		transform_vecs12_by_matrix3(1, rec, rec, turn)
		a7 = entry_a7 - 0x8c
		transform_vecs12_by_matrix3(1, rec, rec, matrix)
	else:
		a7 = entry_a7 - 0x8c
		transform_vecs12_by_matrix3(1, rec, rec, matrix)
	a7 = entry_a7
	memory.write_u32(obj + 4, C68kScript.u32(memory.read_s16(rec + 2) + ship_xyz[0]))
	memory.write_u32(obj + 8, C68kScript.u32(memory.read_s16(rec + 6) + memory.read_u32(ship + 8) - ship_xyz[1]))
	memory.write_u32(obj + 0xc, C68kScript.u32(memory.read_s16(rec + 0xa) + ship_xyz[2]))
	if memory.read_s32(obj + 8) < 0:
		a7 = entry_a7 - 0x70
		dispatch_entity_cleanup(obj)
		a7 = entry_a7

## 0x17850 (three saved registers): step_sinking_object, Leyte's. When due (frame >= long +0x66c, signed) and the
## object live (+0x17 bit 1 clear): angle words +0x658 / +0x65a / +0x65c += rates +0x65e / +0x660 / +0x662 (& 0x3fff),
## +0x664 += +0x666 and the object sinks by it (y -= sext +0x666); types 0x1b..0x1c also lower their deck height
## +0x696. Steps left +0x66a - 1: none -> next frame 1000000000, else frame + word +0x668. leyte_sink_with_cached_chain
## (object, the three angles) (0x20 below) nonzero: carry_attached_objects(ctl, the angles, 0) (0x24 below); zero (gone
## under): carry_attached_objects(ctl, 0, 0, 0, 1), a credited kill tallied (word +0x672; 0x14 below), the mission
## outcomes (host +0x90 / +0xd2: a carrier (0x1b) lost in missions 8 / 2 -> -1 unless negative; in mission 4 the third
## non-carrier sunk (0x27060) -> 1; type 0x20 adds its deck word +0x696 to 0x27056, 0xc of it -> 1; in mission 9 a
## carrier whose deck tag +0x69a is unknown -> 1; each only from 0), and dispatch_entity_cleanup (0x14 below). With no
## hits left (+0x656 == 0) the step count goes back up by one.
func step_sinking_object(ctl: int) -> void:
	assert(memory != null, "step_sinking_object requires original global memory")
	var entry_a7: int = a7
	var frame: int = memory.read_s32(0x21020)
	if frame < memory.read_s32(ctl + 0x66c):
		return
	var obj: int = memory.read_u32(ctl)
	if (memory.read_u8(obj + 0x17) & 2) != 0:
		return
	for k in range(3):
		var at: int = ctl + 0x658 + 2 * k
		memory.write_u16(at, (memory.read_u16(at) + memory.read_u16(ctl + 0x65e + 2 * k)) & 0x3fff)
	memory.write_u16(ctl + 0x664, C68kScript.u16(memory.read_u16(ctl + 0x664) + memory.read_u16(ctl + 0x666)))
	memory.write_u32(obj + 8, C68kScript.u32(memory.read_u32(obj + 8) - memory.read_s16(ctl + 0x666)))
	var kind: int = memory.read_s16(memory.read_u32(ctl + 4))
	if kind > 0x1a and kind <= 0x1c:
		memory.write_u16(ctl + 0x696, C68kScript.u16(memory.read_u16(ctl + 0x696) - memory.read_u16(ctl + 0x666)))
	memory.write_u16(ctl + 0x66a, C68kScript.u16(memory.read_u16(ctl + 0x66a) - 1))
	if memory.read_u16(ctl + 0x66a) == 0:
		memory.write_u32(ctl + 0x66c, 1000000000)
	else:
		memory.write_u32(ctl + 0x66c, C68kScript.u32(memory.read_s16(ctl + 0x668) + memory.read_u32(0x21020)))
	a7 = entry_a7 - 0x20
	var afloat: int = C68kScript.u32(leyte_sink_with_cached_chain(obj, memory.read_s16(ctl + 0x658), memory.read_s16(ctl + 0x65a), memory.read_s16(ctl + 0x65c)))
	a7 = entry_a7
	if afloat != 0:
		a7 = entry_a7 - 0x24
		leyte_carry_attached_objects(ctl, C68kScript.u32(memory.read_s16(ctl + 0x658)), C68kScript.u32(memory.read_s16(ctl + 0x65a)),
				C68kScript.u32(memory.read_s16(ctl + 0x65c)), 0)
		a7 = entry_a7
	else:
		a7 = entry_a7 - 0x24
		leyte_carry_attached_objects(ctl, 0, 0, 0, 1)
		a7 = entry_a7
		if memory.read_u16(ctl + 0x672) != 0:
			a7 = entry_a7 - 0x14
			tally_destroyed_entity(obj, afloat)
			a7 = entry_a7
		var host: int = memory.read_u32(0x207fc)
		kind = memory.read_s16(memory.read_u32(ctl + 4))
		var mission: int = memory.read_s16(host + 0x90)
		if kind == 0x1b and (mission == 8 or mission == 2) and memory.read_s16(host + 0xd2) >= 0:
			memory.write_u16(host + 0xd2, 0xffff)
		if memory.read_s16(host + 0x90) == 4 and kind != 0x1b:
			memory.write_u16(0x27060, C68kScript.u16(memory.read_u16(0x27060) + 1))
			if memory.read_s16(0x27060) == 3 and memory.read_u16(host + 0xd2) == 0:
				memory.write_u16(host + 0xd2, 1)
		if kind == 0x20:
			memory.write_u16(0x27056, C68kScript.u16(memory.read_u16(0x27056) + memory.read_u16(ctl + 0x696)))
			if memory.read_s16(0x27056) == 0xc and memory.read_u16(host + 0xd2) == 0:
				memory.write_u16(host + 0xd2, 1)
		if memory.read_s16(host + 0x90) == 9 and kind == 0x1b and memory.read_u16(ctl + 0x69a) == 0xffff and memory.read_u16(host + 0xd2) == 0:
			memory.write_u16(host + 0xd2, 1)
		a7 = entry_a7 - 0x14
		dispatch_entity_cleanup(obj)
		a7 = entry_a7
	if memory.read_u16(ctl + 0x656) == 0:
		memory.write_u16(ctl + 0x66a, C68kScript.u16(memory.read_u16(ctl + 0x66a) + 1))

## 0x182e (Leyte; no frame): copy the format into the static buffer 0x27084 (1.0.3: 0x20b4c), widening "%d" to "%ld"
## and "%x" to "%lx"; returns the buffer.
func widen_printf_format(fmt: int) -> int:
	assert(memory != null, "widen_printf_format requires original global memory")
	var src: int = C68kScript.u32(fmt)
	var dst: int = 0x27084
	while memory.read_u8(src) != 0:
		var c: int = memory.read_u8(src)
		var n: int = memory.read_u8(C68kScript.u32(src + 1))
		if c == 0x25 and (n == 0x64 or n == 0x78):
			memory.write_u8(dst, 0x25)
			memory.write_u8(dst + 1, 0x6c)
			memory.write_u8(dst + 2, n)
			dst += 3
			src = C68kScript.u32(src + 2)
		else:
			memory.write_u8(dst, c)
			dst += 1
			src = C68kScript.u32(src + 1)
	memory.write_u8(dst, 0)
	return 0x27084

## 0x189c (Leyte; link 0, a2 saved): the host printf (host world +0x29a; 1.0.3 +0x262) with the widened format and
## the five following arguments (widen_printf_format and the printf both 0x24 below); returns its D0.
func overlay_printf(fmt: int, a1: int, a2: int, a3_arg: int, a4: int, a5: int) -> int:
	assert(memory != null, "overlay_printf requires original global memory")
	var entry_a7: int = a7
	var host: int = memory.read_u32(0x207fc)
	a7 = entry_a7 - 0x24
	var widened: int = widen_printf_format(fmt)
	var r = _jsr(memory.read_u32(host + 0x29a), [widened, a1, a2, a3_arg, a4, a5])
	a7 = entry_a7
	return 0 if r == null else C68kScript.u32(int(r))

## 0x17cee (Leyte only; two saved registers): attach an object to a ship's list (+0x67a, count word +0x678): with
## fewer than four, it goes in (its reference count +0x29 + 1); a fifth prints "Too many AA guns on a ship" (0x17cd2,
## overlay_printf, 0x10 below).
func leyte_attach_to_ship(ctl: int, obj: int) -> void:
	assert(memory != null, "leyte_attach_to_ship requires original global memory")
	var entry_a7: int = a7
	if memory.read_s16(ctl + 0x678) > 3:
		a7 = entry_a7 - 0x10
		overlay_printf(0x17cd2, 0, 0, 0, 0, 0)
		a7 = entry_a7
		return
	var n: int = memory.read_s16(ctl + 0x678)
	memory.write_u32(C68kScript.u32(ctl + 0x67a + n * 4), C68kScript.u32(obj))
	memory.write_u16(ctl + 0x678, C68kScript.u16(memory.read_u16(ctl + 0x678) + 1))
	memory.write_u8(obj + 0x29, (memory.read_u8(obj + 0x29) + 1) & 0xff)

## 0x18d54 (Leyte only; six saved registers): a ship's first mover call. Its scene root's part table (+0x24 -> +0x32,
## word count +0x30, 0x18 apart, the object at +0x14): the first part of type 3 (+0x28) with a table of its own is
## searched, and each of that table's parts whose controller definition type is 0x23 / 0x24 attaches to the ship
## (leyte_attach_to_ship, 0x24 below). Then +0x61c = the root's +0x2a record base + 0x1a, +0x620 = the host's +0x8c +
## the template offset at +0x14 + 24 * word +0x10 of that base, byte +0x61b = 1, and the chain cache +0x630 is
## composed (compose_template_chain, 0x24 below).
func leyte_ship_first_move(ctl: int) -> void:
	assert(memory != null, "leyte_ship_first_move requires original global memory")
	var entry_a7: int = a7
	var obj: int = memory.read_u32(ctl)
	var group: int = memory.read_u32(obj + 0x24)
	var i: int = 0
	while i < memory.read_s16(group + 0x30):
		var part: int = memory.read_u32(memory.read_u32(group + 0x32) + i * 0x18 + 0x14)
		if part != 0 and memory.read_u8(part + 0x28) == 3:
			group = part
			if memory.read_u32(group + 0x32) == 0:
				break
			var k: int = 0
			while k < memory.read_s16(group + 0x30):
				var p: int = memory.read_u32(memory.read_u32(group + 0x32) + k * 0x18 + 0x14)
				if p != 0 and memory.read_u32(p) != 0:
					var kind: int = memory.read_s16(memory.read_u32(memory.read_u32(p) + 4))
					if kind == 0x24 or kind == 0x23:
						a7 = entry_a7 - 0x24
						leyte_attach_to_ship(ctl, p)
						a7 = entry_a7
				k += 1
			break
		i += 1
	var base: int = C68kScript.u32(memory.read_u32(memory.read_u32(obj + 0x24) + 0x2a) + 0x1a)
	memory.write_u32(ctl + 0x61c, base)
	memory.write_u32(ctl + 0x620, C68kScript.u32(memory.read_u32(base + 0x14 + memory.read_u16(obj + 0x10) * 0x18) + memory.read_u32(memory.read_u32(0x207fc) + 0x8c)))
	memory.write_u8(ctl + 0x61b, 1)
	a7 = entry_a7 - 0x24
	compose_template_chain(obj, ctl + 0x630)
	a7 = entry_a7

## 0x3604 (link a6 -0x20, eight saved registers): apply_template_chain_xform_always, 1.0.3's 0x2cf0 with Leyte's layout
## (the slot index is word +0x12, 1.0.3 byte +2; positions +4 / +8 / +0xc; the part table +0x32): every slot up the
## parent chain (+0x24) rotates (build_attitude_matrix_3ang of the slot's words +2 / +4 / +6 into a6 - 0x12, 0x58 below)
## and translates by its longs +8 / +0xc / +0x10, unshifted: the vectors src -> dst (then dst in place), or without both
## the object's position (a6 - 0x1e), written back at the end.
func apply_template_chain_xform_always(obj: int, vert_count: int, src: int, dst: int) -> void:
	assert(memory != null, "apply_template_chain_xform_always requires original global memory")
	assert(a7 != 0, "apply_template_chain_xform_always needs a7 (its frame holds pos and the matrix)")
	var entry_a7: int = a7
	var pos: int = entry_a7 - 0x22
	var matrix: int = entry_a7 - 0x16
	memory.write_u32(pos, memory.read_u32(obj + 4))
	memory.write_u32(pos + 4, memory.read_u32(obj + 8))
	memory.write_u32(pos + 8, memory.read_u32(obj + 0xc))
	var parent: int = memory.read_u32(obj + 0x24)
	var index: int = memory.read_u16(obj + 0x12)
	while parent != 0:
		var slot: int = C68kScript.u32(memory.read_u32(parent + 0x32) + index * 0x18)
		a7 = entry_a7 - 0x58
		build_attitude_matrix_3ang(memory.read_s16(slot + 2), memory.read_s16(slot + 4), memory.read_s16(slot + 6), matrix)
		if src != 0 and dst != 0:
			a7 = entry_a7 - 0x58
			transform_int3_vecs_by_matrix3(vert_count, src, dst, matrix)
			src = dst
			a7 = entry_a7 - 0x70
			translate_int3_vecs(vert_count, dst, dst, memory.read_s32(slot + 8), memory.read_s32(slot + 0xc), memory.read_s32(slot + 0x10))
		else:
			a7 = entry_a7 - 0x58
			transform_int3_vecs_by_matrix3(1, pos, pos, matrix)
			a7 = entry_a7 - 0x70
			translate_int3_vecs(1, pos, pos, memory.read_s32(slot + 8), memory.read_s32(slot + 0xc), memory.read_s32(slot + 0x10))
		a7 = entry_a7
		index = memory.read_u16(parent + 0x12)
		parent = memory.read_u32(parent + 0x24)
	if src == 0 and dst == 0:
		memory.write_u32(obj + 4, memory.read_u32(pos))
		memory.write_u32(obj + 8, memory.read_u32(pos + 4))
		memory.write_u32(obj + 0xc, memory.read_u32(pos + 8))

## 0x1a8a2 (Leyte only; link 0, four saved registers): a route block of `count` points: heap_alloc_cleared(header + 12 *
## count) (0x1c below), the count word at header - 2, and the points `src` placed through the object's chain into the
## block after the header (apply_template_chain_xform_always, 0x2c below). Returns the block.
func leyte_make_route(obj: int, header: int, count: int, src: int) -> int:
	assert(memory != null, "leyte_make_route requires original global memory")
	var entry_a7: int = a7
	var n: int = C68kScript.s16(count)
	a7 = entry_a7 - 0x1c
	var block: int = C68kScript.u32(heap_alloc_cleared(C68kScript.u32(header + n * 12)))
	a7 = entry_a7
	memory.write_u16(C68kScript.u32(header + block - 2), n & 0xffff)
	a7 = entry_a7 - 0x2c
	apply_template_chain_xform_always(obj, C68kScript.u32(n), src, C68kScript.u32(header + block))
	a7 = entry_a7
	return block

## 0x1a936 (Leyte only; link a6 -0xc, ten saved registers): a route from the scene. Up the object's groups (+0x24 and
## their own +0x24), the first part of type 2 (+0x28) with a single face (+0x3c == 1), no controller and a face (+0x4a)
## flagged at byte +0x13 bit 1 gives it: heap_alloc_cleared(header + 12 * the face's index count +0x14) (0x40 below),
## the count word at header - 2, each indexed vertex (+0x46, asr the part's word +0x36) copied after the header, and
## the index of the vertex nearest the object (approx_dist3_xyz) stored at `nearest`. Returns the block, else 0.
func leyte_route_from_scene(obj: int, header: int, nearest: int) -> int:
	assert(memory != null, "leyte_route_from_scene requires original global memory")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var group: int = memory.read_u32(obj + 0x24)
	while group != 0:
		var i: int = 0
		while i < memory.read_s16(group + 0x30):
			var part: int = memory.read_u32(memory.read_u32(group + 0x32) + i * 0x18 + 0x14)
			i += 1
			if part == 0 or memory.read_u8(part + 0x28) != 2 or memory.read_u16(part + 0x3c) != 1 or memory.read_u32(part) != 0:
				continue
			var face: int = memory.read_u32(part + 0x4a)
			if face == 0 or (memory.read_u8(face + 0x13) & 2) == 0:
				continue
			var count: int = memory.read_s16(face + 0x14)
			var shift: int = memory.read_s16(part + 0x36) & 0x3f
			a7 = entry_a7 - 0x40
			var block: int = C68kScript.u32(heap_alloc_cleared(C68kScript.u32(header + count * 12)))
			a7 = entry_a7
			memory.write_u16(C68kScript.u32(header + block - 2), count & 0xffff)
			var best: int = 0x7fffffff
			var points: int = C68kScript.u32(block + header)
			var tmp: int = a6 - 0xc
			for k in range(max(count, 0)):
				var vi: int = memory.read_s16(memory.read_u32(face + 0x18) + 2 * k)
				var v: int = C68kScript.u32(memory.read_u32(part + 0x46) + vi * 12)
				for m in range(3):
					memory.write_u32(tmp + 4 * m, C68kScript.u32(C68kScript.asr32(memory.read_s32(v + 4 * m), shift)))
				var d: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj + 4), memory.read_s32(obj + 8), memory.read_s32(obj + 0xc),
						memory.read_s32(tmp), memory.read_s32(tmp + 4), memory.read_s32(tmp + 8)))
				if d < best:
					best = d
					memory.write_u16(nearest, k)
				for m in range(3):
					memory.write_u32(points + 12 * k + 4 * m, memory.read_u32(tmp + 4 * m))
			return block
		group = memory.read_u32(group + 0x24)
	return 0

## 0x1869c (Leyte only; five saved registers): a ship's next route point. `state` points at the route's index word
## (the count word follows, the points at +4); within 10000 (speed word > 0x870) or 4320 of the current point (x / z,
## approx_dist3_xyz) the index advances (wrapping at the count), *out = the new point and 1 is returned; otherwise *out =
## the current point and 0.
func leyte_next_route_point(obj: int, state: int, out: int, speed: int) -> int:
	assert(memory != null, "leyte_next_route_point requires original global memory")
	var point: int = C68kScript.u32(state + 4 + memory.read_s16(state) * 12)
	var reach: int = 10000 if C68kScript.s16(speed) > 0x870 else 4320
	var d: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(obj + 4), 0, memory.read_s32(obj + 0xc), memory.read_s32(point), 0, memory.read_s32(point + 8)))
	if d < reach:
		memory.write_u16(state, C68kScript.u16(memory.read_u16(state) + 1))
		if memory.read_s16(state + 2) <= memory.read_s16(state):
			memory.write_u16(state, 0)
		memory.write_u32(out, C68kScript.u32(state + 4 + memory.read_s16(state) * 12))
		return 1
	memory.write_u32(out, point)
	return 0

## 0x17d2a (Leyte only; four saved registers): the objects a ship carries (+0x67a, count word +0x678, from the last)
## move by (dx, dy, dz); one retired (+0x17 bit 1) whose controller's +0 is clear is released (+0x29 - 1, slot 0).
func leyte_move_ship_attachments(ctl: int, dx: int, dy: int, dz: int) -> void:
	assert(memory != null, "leyte_move_ship_attachments requires original global memory")
	var k: int = memory.read_s16(ctl + 0x678) - 1
	while k != -1:
		var slot: int = C68kScript.u32(ctl + 0x67a + k * 4)
		var obj: int = memory.read_u32(slot)
		if obj != 0:
			if (memory.read_u8(obj + 0x17) & 2) != 0 and memory.read_u32(memory.read_u32(obj)) == 0:
				memory.write_u8(obj + 0x29, (memory.read_u8(obj + 0x29) - 1) & 0xff)
				memory.write_u32(slot, 0)
			else:
				memory.write_u32(obj + 4, C68kScript.u32(memory.read_u32(obj + 4) + dx))
				memory.write_u32(obj + 8, C68kScript.u32(memory.read_u32(obj + 8) + dy))
				memory.write_u32(obj + 0xc, C68kScript.u32(memory.read_u32(obj + 0xc) + dz))
		k -= 1

## 0x18908 (Leyte only; link a6 -0x30, eight saved registers): turn a ship by a heading step. Word +0x654 += step; the
## matrix build_attitude_matrix_3ang(0, step & 0x3fff, 0) (a6 - 0x12, 0x68 below) turns the carried objects' word offsets
## from the ship (frame record a6 - 0x30; transform_vecs12_by_matrix3, 0x68 below) and, for the two aircraft of 0x20674
## (their controllers via the pointers there) standing on deck (+0x64c) within the ship's range, the x / z offsets (the
## record's y word left as it was) and their heading +0x22. Then the ship is sunk again level (leyte_sink_with_cached_chain,
## 0x68 below) when its y is not 0, else reshaped for +0x654 (0x60 below); the carried objects move by the speed words
## +0x64e / +0x650 / +0x652 (0x68 below), which also become +0x624 / +0x628 / +0x62c and move the ship; heading +0x22 +=
## step; velocity +8 = (-speed +0x674 * sin) asr 18, +0x10 = (cos * speed) asr 18.
func leyte_turn_ship(ctl: int, step: int) -> void:
	assert(memory != null, "leyte_turn_ship requires original global memory")
	assert(a7 != 0, "leyte_turn_ship needs a7 (its frame holds the matrix and the record)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var ship: int = memory.read_u32(ctl)
	var d4: int = step & 0xffff
	memory.write_u16(ctl + 0x654, (d4 + memory.read_u16(ctl + 0x654)) & 0x3fff)
	var matrix: int = a6 - 0x12
	var rec: int = a6 - 0x30
	a7 = entry_a7 - 0x68
	build_attitude_matrix_3ang(0, C68kScript.u32(step) & 0x3fff, 0, matrix)
	var k: int = memory.read_s16(ctl + 0x678) - 1
	while k >= 0:
		var obj: int = memory.read_u32(C68kScript.u32(ctl + 0x67a + k * 4))
		if obj != 0:
			for m in range(3):
				memory.write_u16(rec + 2 + 4 * m, C68kScript.u16(memory.read_u16(obj + 6 + 4 * m) - memory.read_u16(ship + 6 + 4 * m)))
			a7 = entry_a7 - 0x68
			transform_vecs12_by_matrix3(1, rec, rec, matrix)
			for m in range(3):
				memory.write_u32(obj + 4 + 4 * m, C68kScript.u32(memory.read_s16(rec + 2 + 4 * m) + memory.read_u32(ship + 4 + 4 * m)))
		k -= 1
	for j in range(2):
		var actl: int = memory.read_u32(memory.read_u32(0x20674 + 4 * j))
		if actl == 0 or memory.read_u8(actl + 0x64c) == 0:
			continue
		var plane: int = memory.read_u32(actl)
		var d: int = C68kScript.s32(approx_dist3_xyz(memory.read_s32(plane + 4), memory.read_s32(plane + 8), memory.read_s32(plane + 0xc),
				memory.read_s32(ship + 4), memory.read_s32(ship + 8), memory.read_s32(ship + 0xc)))
		if d >= memory.read_s32(ship + 0x20):
			continue
		memory.write_u16(rec + 2, C68kScript.u16(memory.read_u16(plane + 6) - memory.read_u16(ship + 6)))
		memory.write_u16(rec + 0xa, C68kScript.u16(memory.read_u16(plane + 0xe) - memory.read_u16(ship + 0xe)))
		a7 = entry_a7 - 0x68
		transform_vecs12_by_matrix3(1, rec, rec, matrix)
		memory.write_u32(plane + 4, C68kScript.u32(memory.read_s16(rec + 2) + memory.read_u32(ship + 4)))
		memory.write_u32(plane + 0xc, C68kScript.u32(memory.read_s16(rec + 0xa) + memory.read_u32(ship + 0xc)))
		memory.write_u16(actl + 0x22, (d4 + memory.read_u16(actl + 0x22)) & 0x3fff)
	if memory.read_u32(ship + 8) != 0:
		a7 = entry_a7 - 0x68
		leyte_sink_with_cached_chain(ship, 0, 0, 0)
	else:
		a7 = entry_a7 - 0x60
		leyte_reshape_ship(ctl, C68kScript.u32(memory.read_s16(ctl + 0x654)))
	a7 = entry_a7 - 0x68
	var vx: int = memory.read_s16(ctl + 0x64e)
	var vy: int = memory.read_s16(ctl + 0x650)
	var vz: int = memory.read_s16(ctl + 0x652)
	leyte_move_ship_attachments(ctl, C68kScript.u32(vx), C68kScript.u32(vy), C68kScript.u32(vz))
	a7 = entry_a7
	memory.write_u32(ctl + 0x624, C68kScript.u32(vx))
	memory.write_u32(ctl + 0x628, C68kScript.u32(vy))
	memory.write_u32(ctl + 0x62c, C68kScript.u32(vz))
	memory.write_u32(ship + 4, C68kScript.u32(vx + memory.read_u32(ship + 4)))
	memory.write_u32(ship + 8, C68kScript.u32(vy + memory.read_u32(ship + 8)))
	memory.write_u32(ship + 0xc, C68kScript.u32(vz + memory.read_u32(ship + 0xc)))
	memory.write_u16(ctl + 0x22, (d4 + memory.read_u16(ctl + 0x22)) & 0x3fff)
	_ship_velocity(ctl)

## The ship's velocity from its heading +0x22 and speed +0x674: +8 = (-speed * sext sin) asr 18, +0x10 = (sext cos *
## speed) asr 18 (muls.l, 32-bit products).
func _ship_velocity(ctl: int) -> void:
	var speed: int = memory.read_s32(ctl + 0x674)
	var h: int = memory.read_s16(ctl + 0x22)
	memory.write_u32(ctl + 8, C68kScript.u32(C68kScript.asr32(C68kScript.s32(-speed * C68kScript.s16(sin_fixed14(h))), 18)))
	memory.write_u32(ctl + 0x10, C68kScript.u32(C68kScript.asr32(C68kScript.s32(C68kScript.s16(cos_fixed14(h)) * speed), 18)))

## 0x18b34 (Leyte only; link a6 -4, five saved registers): steer a ship along its route (+0x34; its state word first).
## The speed words +0x64e / +0x650 / +0x652 take the velocity's low words and add to +0x624 / +0x628 / +0x62c (byte
## +0x619 = 1). State 0: speed +0x674 = 0x708 for type 0x21, else 0x980; the velocity from the heading; the next point
## (leyte_next_route_point into a6 - 4); state 3. State 2: the ship and its carried objects move by the speed words;
## reaching the point -> 3. State 3: aim at the point (leyte_heading_to_point, fold_angle_pm_0x2000 of the difference
## with +0x22): beyond +-0x40 the turn is clamped and the state becomes 8, else 2; leyte_turn_ship. Other states count
## down by one while moving.
func leyte_steer_ship(ctl: int) -> void:
	assert(memory != null, "leyte_steer_ship requires original global memory")
	assert(a7 != 0, "leyte_steer_ship needs a7 (its frame holds the point pointer)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var ship: int = memory.read_u32(ctl)
	memory.write_u16(ctl + 0x64e, memory.read_u16(ctl + 0xa))
	memory.write_u16(ctl + 0x650, memory.read_u16(ctl + 0xe))
	memory.write_u16(ctl + 0x652, memory.read_u16(ctl + 0x12))
	for k in range(3):
		memory.write_u32(ctl + 0x624 + 4 * k, C68kScript.u32(memory.read_s16(ctl + 0x64e + 2 * k) + memory.read_u32(ctl + 0x624 + 4 * k)))
	memory.write_u8(ctl + 0x619, 1)
	var route: int = memory.read_u32(ctl + 0x34)
	var st: int = memory.read_s16(route)
	if st == 0:
		memory.write_u32(ctl + 0x674, 0x708 if memory.read_s16(memory.read_u32(ctl + 4)) == 0x21 else 0x980)
		_ship_velocity(ctl)
		a7 = entry_a7 - 0x38
		leyte_next_route_point(ship, route + 4, a6 - 4, memory.read_u32(ctl + 0x674))
		a7 = entry_a7
		memory.write_u16(route, 3)
	elif st == 2:
		_move_ship(ctl, ship, entry_a7)
		a7 = entry_a7 - 0x40
		if leyte_next_route_point(ship, route + 4, a6 - 4, memory.read_u32(ctl + 0x674)) != 0:
			memory.write_u16(route, 3)
		a7 = entry_a7
	elif st == 3:
		a7 = entry_a7 - 0x30
		leyte_next_route_point(ship, route + 4, a6 - 4, memory.read_u32(ctl + 0x674))
		var p: int = memory.read_u32(a6 - 4)
		a7 = entry_a7 - 0x40
		var h: int = C68kScript.s16(leyte_heading_to_point(ship, memory.read_u32(p), memory.read_u32(p + 4), memory.read_u32(p + 8)))
		a7 = entry_a7 - 0x24
		var turn: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.u32(h - memory.read_s16(ctl + 0x22))))
		a7 = entry_a7
		if abs_int(turn) > 0x40:
			turn = -0x40 if turn < 0 else 0x40
			memory.write_u16(route, 8)
		else:
			memory.write_u16(route, 2)
		a7 = entry_a7 - 0x28
		leyte_turn_ship(ctl, C68kScript.u32(turn))
		a7 = entry_a7
	else:
		memory.write_u16(route, C68kScript.u16(memory.read_u16(route) - 1))
		_move_ship(ctl, ship, entry_a7)

## leyte_steer_ship: the ship and its carried objects move by the speed words (leyte_move_ship_attachments, 0x30 below).
func _move_ship(ctl: int, ship: int, entry_a7: int) -> void:
	var vx: int = memory.read_s16(ctl + 0x64e)
	var vy: int = memory.read_s16(ctl + 0x650)
	var vz: int = memory.read_s16(ctl + 0x652)
	memory.write_u32(ship + 4, C68kScript.u32(vx + memory.read_u32(ship + 4)))
	memory.write_u32(ship + 8, C68kScript.u32(vy + memory.read_u32(ship + 8)))
	memory.write_u32(ship + 0xc, C68kScript.u32(vz + memory.read_u32(ship + 0xc)))
	a7 = entry_a7 - 0x30
	leyte_move_ship_attachments(ctl, C68kScript.u32(vx), C68kScript.u32(vy), C68kScript.u32(vz))
	a7 = entry_a7

## 0x18d1c (Leyte only; a2 saved; the kind-1 handler of 0x206ac): a ship without a route (+0x34) gets one of the four
## points at 0x2067c placed through its chain (leyte_make_route(object, 8, 4, 0x2067c), 0x18 below), then steers (0x10
## below).
func leyte_ship_kind1(ctl: int) -> void:
	assert(memory != null, "leyte_ship_kind1 requires original global memory")
	var entry_a7: int = a7
	if memory.read_u32(ctl + 0x34) == 0:
		a7 = entry_a7 - 0x18
		memory.write_u32(ctl + 0x34, C68kScript.u32(leyte_make_route(memory.read_u32(ctl), 8, 4, 0x2067c)))
		a7 = entry_a7
	a7 = entry_a7 - 0x10
	leyte_steer_ship(ctl)
	a7 = entry_a7

## 0x18e20 (Leyte only; link a6 -4, three saved registers; a ship controller's +0x38): sinking (+0x670) ->
## step_sinking_object (0x1c below), the velocity / speed words / speed cleared and the wake cleaned up. Otherwise the
## first call fills the ship's links (leyte_ship_first_move unless byte +0x61b); kind word +0x32: 0 stays; 0x63 follows
## the scene's route (leyte_route_from_scene(object, 8, a6 - 2), 0x24 below, made once: none -> kind 0; the nearest
## index into the block's +4 and the pose ring filled from the ship (fill_pose_history_angles / fill_pose_history, 0x28 /
## 0x38 below)) by leyte_steer_ship (0x1c below); other kinds run the handler 0x206ac[kind] when set (0x1c below).
func leyte_ship_mover(ctl: int) -> void:
	assert(memory != null, "leyte_ship_mover requires original global memory")
	assert(a7 != 0, "leyte_ship_mover needs a7 (its frame holds the nearest index)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var ship: int = memory.read_u32(ctl)
	if memory.read_u16(ctl + 0x670) != 0:
		a7 = entry_a7 - 0x1c
		step_sinking_object(ctl)
		a7 = entry_a7
		memory.write_u32(ctl + 8, 0)
		memory.write_u16(ctl + 0x64e, 0)
		memory.write_u32(ctl + 0x10, 0)
		memory.write_u16(ctl + 0x652, 0)
		memory.write_u32(ctl + 0x674, 0)
		if memory.read_u32(ctl + 0x68e) != 0:
			a7 = entry_a7 - 0x1c
			dispatch_entity_cleanup(memory.read_u32(ctl + 0x68e))
			a7 = entry_a7
		return
	if memory.read_u8(ctl + 0x61b) == 0:
		a7 = entry_a7 - 0x1c
		leyte_ship_first_move(ctl)
		a7 = entry_a7
	var kind: int = memory.read_s16(ctl + 0x32)
	if kind == 0:
		return
	if kind == 0x63:
		if memory.read_u32(ctl + 0x34) == 0:
			a7 = entry_a7 - 0x24
			var block: int = C68kScript.u32(leyte_route_from_scene(ship, 8, a6 - 2))
			a7 = entry_a7
			memory.write_u32(ctl + 0x34, block)
			if block == 0:
				memory.write_u16(ctl + 0x32, 0)
				return
			memory.write_u16(block + 4, memory.read_u16(a6 - 2))
			a7 = entry_a7 - 0x28
			fill_pose_history_angles(ctl + 0x3c, 0, 0, 0)
			a7 = entry_a7 - 0x38
			fill_pose_history(ctl + 0x3c, memory.read_u32(ship + 4), memory.read_u32(ship + 8), memory.read_u32(ship + 0xc))
			a7 = entry_a7
		a7 = entry_a7 - 0x1c
		leyte_steer_ship(ctl)
		a7 = entry_a7
		return
	var handler: int = memory.read_u32(C68kScript.u32(0x206ac + kind * 4))
	if handler != 0:
		a7 = entry_a7 - 0x1c
		_jsr(handler, [ctl])
		a7 = entry_a7

## 0x18376 (Leyte only; seven saved registers; a landing ship's +0x38). Afloat (+0x698 == 0): with the countdown +0x69a
## > 0 it counts down and moves (leyte_ship_mover, 0x24 below); else it moves, then probes the surface ahead
## (surface_type_at_point, 0x2c below): countdown negative -> at the bow offset (+0x64e / +0x652); land (not 0x10) beaches
## it (+0x698 = 1, the wake cleaned up, each type-1 face's byte +0x13 bit 1 toggled); countdown 0 -> 0x2d steps ahead:
## water -> 0x2d frames, land -> -1. Beached: sinking -> step_sinking_object (0x24 below) with the motion cleared; else
## with troops left (+0x696) and due (frame >= +0x69c): up to 20 bearings (rng & 0x4ff + heading - 0x280) find dry land
## 100 ahead (surface 0x34 below) where leyte_spawn_tagged_object(0x27, bearing, x, 0, z, 0, bearing, 0) lands one (0x40
## below); one fewer either way, next due frame + 0x3c + (rng & 0x3f).
func leyte_landing_ship_mover(ctl: int) -> void:
	assert(memory != null, "leyte_landing_ship_mover requires original global memory")
	var entry_a7: int = a7
	var ship: int = memory.read_u32(ctl)
	if memory.read_u16(ctl + 0x698) == 0:
		if memory.read_s16(ctl + 0x69a) > 0:
			memory.write_u16(ctl + 0x69a, C68kScript.u16(memory.read_u16(ctl + 0x69a) - 1))
			a7 = entry_a7 - 0x24
			leyte_ship_mover(ctl)
			a7 = entry_a7
			return
		a7 = entry_a7 - 0x24
		leyte_ship_mover(ctl)
		a7 = entry_a7
		if memory.read_s16(ctl + 0x69a) < 0:
			a7 = entry_a7 - 0x2c
			var s: int = C68kScript.u32(surface_type_at_point(C68kScript.u32(memory.read_s16(ctl + 0x64e) + memory.read_u32(ship + 4)), 0,
					C68kScript.u32(memory.read_s16(ctl + 0x652) + memory.read_u32(ship + 0xc))))
			a7 = entry_a7
			if s == 0x10:
				return
			memory.write_u16(ctl + 0x698, 1)
			if memory.read_u32(ctl + 0x68e) != 0:
				a7 = entry_a7 - 0x24
				dispatch_entity_cleanup(memory.read_u32(ctl + 0x68e))
				a7 = entry_a7
			var face: int = memory.read_u32(ship + 0x4a)
			for k in range(memory.read_u16(ship + 0x3c)):
				if memory.read_u8(face + 0x17) == 1:
					memory.write_u8(face + 0x13, memory.read_u8(face + 0x13) ^ 2)
				face += 0x20
			return
		a7 = entry_a7 - 0x2c
		var ahead: int = C68kScript.u32(surface_type_at_point(C68kScript.u32(memory.read_s16(ctl + 0x64e) * 0x2d + memory.read_u32(ship + 4)), 0,
				C68kScript.u32(memory.read_s16(ctl + 0x652) * 0x2d + memory.read_u32(ship + 0xc))))
		a7 = entry_a7
		memory.write_u16(ctl + 0x69a, 0x2d if ahead == 0x10 else 0xffff)
		return
	if memory.read_u16(ctl + 0x670) != 0:
		a7 = entry_a7 - 0x24
		step_sinking_object(ctl)
		a7 = entry_a7
		memory.write_u32(ctl + 8, 0)
		memory.write_u16(ctl + 0x64e, 0)
		memory.write_u32(ctl + 0x10, 0)
		memory.write_u16(ctl + 0x652, 0)
		memory.write_u32(ctl + 0x674, 0)
		return
	if memory.read_u16(ctl + 0x696) == 0 or memory.read_s32(0x21020) < memory.read_s32(ctl + 0x69c):
		return
	for tries in range(20):
		var bearing: int = ((overlay_rng_next() & 0x4ff) + memory.read_u16(ctl + 0x22) + 0xfd80) & 0x3fff
		var x: int = C68kScript.s32(memory.read_u32(ship + 4) - C68kScript.asr32(C68kScript.s16(sin_fixed14(bearing)) * 100, 12))
		var z: int = C68kScript.s32(C68kScript.asr32(C68kScript.s16(cos_fixed14(bearing)) * 100, 12) + memory.read_u32(ship + 0xc))
		a7 = entry_a7 - 0x34
		var s2: int = C68kScript.u32(surface_type_at_point(C68kScript.u32(x), 0, C68kScript.u32(z)))
		a7 = entry_a7
		if s2 != 0x10:
			a7 = entry_a7 - 0x40
			leyte_spawn_tagged_object(0x27, bearing, C68kScript.u32(x), 0, C68kScript.u32(z), 0, bearing, 0)
			a7 = entry_a7
			break
	memory.write_u16(ctl + 0x696, C68kScript.u16(memory.read_u16(ctl + 0x696) - 1))
	memory.write_u32(ctl + 0x69c, C68kScript.u32((overlay_rng_next() & 0x3f) + 0x3c + memory.read_u32(0x21020)))
