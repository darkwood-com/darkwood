# Leyte overlay: hand-written routines the generated port superseded

On 2026-11-17 `tools/leyte/gen_overlay_port.py` began keeping every 1.0.3 overlay function it proves
instruction for instruction against Leyte's overlay whose own body is sound, whether or not everything it
calls is verified (a callee that is not kept stays a refusing stub). 36 routines that had been written by
hand in `godot/original/leyte/port/pacific_conflict_reconstructed.gd` became duplicates of generated,
proven functions and were removed from it, so each routine has one implementation.

Their emulator cases stay in `godot/tests/leyte/port/fixtures/overlay_reconstructed_cases.json` and now
run against the generated versions; all of them pass. That cross-check is what exposed three defects in
the generator's first version of the rule (a proof cut short at a false entry inside a routine, an
offset no instruction holds, a helper kept without evidence), all closed before the removal.

The removed code is kept below as it was, with its comments (Leyte addresses, moved fields), in case a
later change to the generator's rules refuses one of these functions again. It is reference text, not
part of the port: the generated function is the one that runs.

Removed: `unlock_session_buffers`, `update_view_clip_bounds`, `lock_handle_nopurge`, `lock_session_buffers`, `rotate_int3_vecs_by_3ang`, `abs_int`, `_dbra_word_count`, `clear_pending_entity_slots`, `update_object_camera_metric`, `attitude_from_velocity`, `assign_face_colour`, `approx_dist3_xyz`, `fold_angle_pm_0x2000`, `copy_player_pose_to_camera`, `configure_renderer_for_display`, `fill_span_rows_8bit`, `_span8_write`, `_line_depth8`, `draw_line_depth8`, `restore_line_depth8`, `plot_dot_depth8`, `erase_radar_blip_depth8`, `output_face_colour`, `_fill_span_rows_4bit`, `_span4_write`, `fill_span_rows_4bit_a`, `fill_span_rows_4bit_c`, `_line_depth4`, `draw_line_depth4`, `restore_line_depth4`, `plot_dot_depth4`, `_blip_column4`, `draw_radar_blip_depth4`, `erase_radar_blip_depth4`, `erase_radar_blip_bw`, `draw_radar_blip_bw`, `instantiate_object_from_desc`, `fill_span_rows_4bit_b`, `register_parachute_template`.

```gdscript
## 0x16dc (hook 11; 1.0.3's unlock_session_buffers 0x1074 with Leyte's addresses): the session buffers
## unlocked (0x149e) and their data pointers cleared: the entry handle 0x20f30's (0x20f54), both groups'
## slots 1..3 that have a handle (0x20f34 -> 0x20f5c, 0x20f44 -> 0x20f6c), and 0x20f7c / 0x20f80; the
## handles stay.
func unlock_session_buffers() -> void:
	assert(memory != null, "unlock_session_buffers requires original global memory")
	var entry_a7: int = a7
	if memory.read_u32(0x20f30) != 0:
		a7 = entry_a7 - 0x18
		unlock_handle_purgeable(memory.read_u32(0x20f30))
		a7 = entry_a7
		memory.write_u32(0x20f54, 0)
	for k in range(1, 4):
		for pair in [[0x20f34, 0x20f5c], [0x20f44, 0x20f6c]]:
			if memory.read_u32(pair[0] + 4 * k) != 0:
				a7 = entry_a7 - 0x18
				unlock_handle_purgeable(memory.read_u32(pair[0] + 4 * k))
				a7 = entry_a7
				memory.write_u32(pair[1] + 4 * k, 0)
	memory.write_u32(0x20f7c, 0)
	memory.write_u32(0x20f80, 0)

## 0x7f12 (hook 1; 1.0.3's update_view_clip_bounds 0x6ed6, the same instructions with Leyte's
## addresses): reset_span_row_lists(0) (8 below), palette_changed (0x23fd2) = 1, 0x23fbc = 0; the
## clip y max / view half height (0x20b2e / 0x27014) = view rows (0x21010) / 2 toward zero and the
## last scan row (0x27012) = view rows, both 0x14 less with an inset (0x2700e = 4) when the view mode
## (0x23d88) is neither 1 nor 8 and host word +0xa2 is set (else inset 0); 0x27010 / 0x2700c = 0; the
## projection scales (0x27050 / 0x2704e) from the tables at 0x2703c / 0x27024 - entry (word 0x27020)
## in view mode 6 when it is non-zero, else entry 0; 0x23fba = 0.
func update_view_clip_bounds() -> void:
	assert(memory != null, "update_view_clip_bounds requires original global memory")
	var entry_a7: int = a7
	a7 = entry_a7 - 8
	reset_span_row_lists(0)
	a7 = entry_a7
	memory.write_u16(0x23fd2, 1)
	memory.write_u16(0x23fbc, 0)
	var rows: int = memory.read_s16(0x21010)
	var half: int = C68kScript.s16((rows + 1 if rows < 0 else rows) >> 1)
	memory.write_u16(0x20b2e, C68kScript.u16(half))
	memory.write_u16(0x27014, C68kScript.u16(half))
	memory.write_u16(0x27012, C68kScript.u16(rows))
	var view: int = memory.read_s16(0x23d88)
	if view != 1 and view != 8 and memory.read_u16(memory.read_u32(0x207fc) + 0xa2) != 0:
		var inset_half: int = C68kScript.u16(half - 0x14)
		memory.write_u16(0x20b2e, inset_half)
		memory.write_u16(0x27014, inset_half)
		memory.write_u16(0x27012, C68kScript.u16(memory.read_u16(0x27012) - 0x14))
		memory.write_u16(0x2700e, 4)
	else:
		memory.write_u16(0x2700e, 0)
	memory.write_u16(0x27010, 0)
	memory.write_u16(0x2700c, 0)
	var index: int = 0
	if view == 6 and memory.read_u16(0x27020) != 0:
		index = memory.read_s16(0x27020)
	memory.write_u16(0x27050, memory.read_u16(0x2703c + index * 2))
	memory.write_u16(0x2704e, memory.read_u16(0x27024 + index * 2))
	memory.write_u16(0x23fba, 0)

## 0x1476 (1.0.3's lock_handle_nopurge 0xe0a, the same instructions with Leyte's host-world slots):
## lock the handle (host +0x30e, 0xc below) and make it unpurgeable (+0x31a, 0x10 below).
func lock_handle_nopurge(handle: int) -> void:
	assert(memory != null, "lock_handle_nopurge requires original global memory")
	var entry_a7: int = a7
	_host_memory_call(entry_a7, 0xc, 0x30e, [handle])
	_host_memory_call(entry_a7, 0x10, 0x31a, [handle])

## 0x15da (hook 9; 1.0.3's lock_session_buffers 0xf72 with Leyte's addresses; six saved registers):
## lock the session buffers (0x1476, 0x20 below) and keep their data pointers: with host byte +0 the
## entry handle 0x20f30's into 0x20f54; unless host word +0xe == 1, the groups 0x20f34 / 0x20f44's
## slots (1, and 2..3 with the multi-screen flag, host word +0xb4) into 0x20f5c / 0x20f6c, then
## 0x20f7c / 0x20f80 = the slot-1 pointers of the second / first group. Then update_view_clip_bounds
## (0x7f12, 0x1c below), also at depth 1.
func lock_session_buffers() -> void:
	assert(memory != null, "lock_session_buffers requires original global memory")
	var entry_a7: int = a7
	if memory.read_u8(memory.read_u32(0x207fc)) != 0:
		a7 = entry_a7 - 0x20
		lock_handle_nopurge(memory.read_u32(0x20f30))
		a7 = entry_a7
		memory.write_u32(0x20f54, _leyte_locked_pointer(memory.read_u32(0x20f30)))
	if memory.read_s16(memory.read_u32(0x207fc) + 0xe) != 1:
		for k in range(1, 4):
			for pair in [[0x20f34, 0x20f5c], [0x20f44, 0x20f6c]]:
				if k == 1 or memory.read_u16(memory.read_u32(0x207fc) + 0xb4) != 0:
					a7 = entry_a7 - 0x20
					lock_handle_nopurge(memory.read_u32(pair[0] + 4 * k))
					a7 = entry_a7
					memory.write_u32(pair[1] + 4 * k, _leyte_locked_pointer(memory.read_u32(pair[0] + 4 * k)))
		memory.write_u32(0x20f7c, memory.read_u32(0x20f70))
		memory.write_u32(0x20f80, memory.read_u32(0x20f60))
	a7 = entry_a7 - 0x1c
	update_view_clip_bounds()
	a7 = entry_a7

## 0x31ca (hook 17; 1.0.3's rotate_int3_vecs_by_3ang 0x2886, the same instructions): the matrix
## (0x2ce2, entered 0x30 below, into a6 - 0x12) then the vectors through it (0x3418, 0x40 below).
func rotate_int3_vecs_by_3ang(angle1: int, angle2: int, angle3: int, count: int, src: int, dst: int) -> int:
	assert(a7 != 0, "rotate_int3_vecs_by_3ang needs a7 (its frame holds the matrix)")
	var entry_a7: int = a7
	var matrix: int = entry_a7 - 4 - 0x12
	a7 = entry_a7 - 0x30
	build_attitude_matrix_3ang(angle1, angle2, angle3, matrix)
	a7 = entry_a7 - 0x40
	var d0: int = transform_int3_vecs_by_matrix3(count, src, dst, matrix)
	a7 = entry_a7
	return d0

## 0x38e8 (1.0.3's abs_int 0x2de8, the same four instructions; the correspondence left it unverified):
## move.l 4(a7),d0 / bge / neg.l: NEG.L leaves 0x80000000 unchanged, so abs_int(INT_MIN) is INT_MIN.
static func abs_int(value: int) -> int:
	value = C68kScript.s32(value)
	if value < 0:
		value = C68kScript.s32(-value)
	return value

## dbra over a word counter loaded with n - 1: n iterations for 1..0x10000 (n = 0 runs 0x10000).
func _dbra_word_count(n: int) -> int:
	return ((n - 1) & 0xffff) + 1

## 0x6c6a (1.0.3's clear_pending_entity_slots 0x5dd8, the same instructions): each of the 7 scatter slots
## (0x2144c) still holding an object sends it through dispatch_entity_cleanup (0x60dc, 0x14 below) and is
## cleared.
func clear_pending_entity_slots() -> void:
	assert(memory != null, "clear_pending_entity_slots requires original global memory")
	var entry_a7: int = a7
	for k in 7:
		var obj: int = memory.read_u32(0x2144c + 4 * k)
		if obj != 0:
			a7 = entry_a7 - 0x14
			dispatch_entity_cleanup(obj)
			a7 = entry_a7
			memory.write_u32(0x2144c + 4 * k, 0)

## 0x47b6 (1.0.3's update_object_camera_metric 0x3b78 on Leyte's object layout): the object position
## +4 / +8 / +0xc (1.0.3 +0x18 / +0x1c / +0x20) minus the camera (0x23d68 / 0x23d64 / 0x23d60), each
## through abs_int (0x38e8); the metric max + (the other two) / 4 (asr.l) is stored at +0x1c (1.0.3 +0x10)
## and returned in D0.
func update_object_camera_metric(obj: int) -> int:
	assert(memory != null, "update_object_camera_metric requires original global memory")
	var d2: int = abs_int(C68kScript.s32(memory.read_s32(obj + 4) - memory.read_s32(0x23d68)))
	var d3: int = abs_int(C68kScript.s32(memory.read_s32(obj + 8) - memory.read_s32(0x23d64)))
	var d1: int = abs_int(C68kScript.s32(memory.read_s32(obj + 0xc) - memory.read_s32(0x23d60)))
	if d3 > d2:
		var t: int = d2
		d2 = d3
		d3 = t
	if d1 > d2:
		var t2: int = d2
		d2 = d1
		d1 = t2
	var metric: int = C68kScript.s32(C68kScript.asr32(C68kScript.s32(d3 + d1), 2) + d2)
	memory.write_u32(obj + 0x1c, C68kScript.u32(metric))
	return metric

## 0x2554 (hook 19; 1.0.3's attitude_from_velocity 0x1f02, the same 100 instructions): heading and pitch
## (0x4000 per turn) of a velocity given as words. |x| / |z| are the low words of abs_int. |z| < |x|:
## heading = 0x1000 - atan((vz asl 12) / |x|), negated when vx > 0, & 0x3fff. Else vz == 0 leaves the
## heading unwritten; otherwise heading = atan((-vx asl 12) / |z|), and for vz < 0 (0x2000 - that) &
## 0x3fff. Speed 0 leaves the pitch unwritten. Steep (|vy| > |x| + |z|): pitch = acos((isqrt(speed_sq -
## vy * vy) asl 12) / speed), negated & 0x3fff for vy < 0; else pitch = (0x1000 - acos((vy asl 12) /
## speed)) & 0x3fff. Divisions are divs.l (truncating). The outputs are words at the two pointers.
func attitude_from_velocity(vx: int, vy: int, vz: int, speed: int, speed_sq: int, pitch: int, heading: int) -> void:
	assert(memory != null, "attitude_from_velocity requires original global memory")
	vx = C68kScript.s16(vx)
	vy = C68kScript.s16(vy)
	vz = C68kScript.s16(vz)
	var abs_x: int = C68kScript.s16(abs_int(vx))
	var abs_z: int = C68kScript.s16(abs_int(vz))
	if abs_z < abs_x:
		var angle: int = C68kScript.u16(0x1000 - atan_fixed14(C68kScript.divs_trunc(C68kScript.s32(vz << 12), abs_x)))
		if vx > 0:
			angle = C68kScript.u16(-angle)
		memory.write_u16(heading, angle & 0x3fff)
	elif vz != 0:
		var angle2: int = atan_fixed14(C68kScript.divs_trunc(C68kScript.s32((-vx) << 12), abs_z))
		if vz < 0:
			angle2 = C68kScript.u16(0x2000 - angle2) & 0x3fff
		memory.write_u16(heading, angle2)
	speed = C68kScript.s32(speed)
	if speed == 0:
		return
	var p: int
	if abs_int(vy) > abs_z + abs_x:
		var len: int = isqrt_floor(C68kScript.s32(C68kScript.s32(speed_sq) - vy * vy))
		p = C68kScript.u16(acos_fixed14(C68kScript.divs_trunc(C68kScript.s32(C68kScript.s16(len) << 12), speed)))
		if vy < 0:
			p = C68kScript.u16(-p) & 0x3fff
	else:
		p = C68kScript.u16(0x1000 - acos_fixed14(C68kScript.divs_trunc(C68kScript.s32(vy << 12), speed))) & 0x3fff
	memory.write_u16(pitch, p)

## 0x1be6 (1.0.3's assign_face_colour 0x157e, the same 94 instructions, Leyte's globals: depth 0x2100e,
## owner table 0x23fac, usage maps 0x23d90 / 0x23d94, cursor 0x21568, palette_changed 0x23fd2). Face colour
## for the view depth; returns 1 when no palette slot is free, else 0.
func assign_face_colour(face: int) -> int:
	assert(memory != null, "assign_face_colour requires original global memory")
	var depth: int = memory.read_s16(0x2100e)
	if depth == 1:
		var luma: int = C68kScript.divs_trunc(memory.read_u8(face + 0xc) * 0x13 + memory.read_u8(face + 0xd) * 0x26 + memory.read_u8(face + 0xe) * 7, 64)
		var work: int = C68kScript.divs_trunc((luma - 0x80) << 4, 0xc) + 0x80
		if work > 0xff:
			work = 0xff
		elif work < 0:
			work = 0
		memory.write_u32(face + 4, C68kScript.u32(memory.read_s16(memory.read_u32(memory.read_u32(0x207fc) + 0x88) + work * 2)))
		memory.write_u8(face + 0xf, 0xff)
		return 0
	if depth == 4:
		var index: int = memory.read_u8(face + 0xc) * 0x19 + memory.read_u8(face + 0xd) * 5 + memory.read_u8(face + 0xe)
		memory.write_u32(face + 4, memory.read_u32(memory.read_u32(0x23fac) + index * 4))
		memory.write_u8(face + 0xf, 0xff)
		return 0
	if memory.read_u32(memory.read_u32(0x23fac) + memory.read_u8(face + 0xf) * 4) == C68kScript.u32(-face):
		queue_face_palette_entry(face)
		return 0
	var tried: int = 0
	while true:
		var cursor: int = memory.read_u32(0x21568)
		if memory.read_u8(memory.read_u32(0x23d90) + cursor) == 0 and memory.read_u8(memory.read_u32(0x23d94) + cursor) == 0:
			memory.write_u8(face + 0xf, C68kScript.u8(cursor))
			write_face_palette_slot(face)
			memory.write_u16(0x23fd2, 1)
			return 0
		memory.write_u32(0x21568, C68kScript.u32(cursor + 1))
		if memory.read_u32(0x21568) == 0xc8:
			memory.write_u32(0x21568, 0)
		tried += 1
		if tried > 0xc7:
			return 1
	return 1 ## not reached

## 0x4886 (1.0.3's approx_dist3_xyz 0x3c48, the same 36 instructions; the generated port held an asserting
## stub): suba.l, signed cmp.l, add.l and asr.l #2 all wrap or shift at 32 bits.
static func approx_dist3_xyz(ax: int, ay: int, az: int, bx: int, by: int, bz: int) -> int:
	var d1: int
	var d2: int
	var d3: int
	var dmax: int

	d1 = abs_int(C68kScript.s32(bx - ax))
	d2 = abs_int(C68kScript.s32(by - ay))
	d3 = abs_int(C68kScript.s32(bz - az))
	dmax = d1
	if d1 < d2:
		dmax = d2
		d2 = d1
	d1 = d3
	if dmax < d3:
		d1 = dmax
		dmax = d3
	return C68kScript.s32(dmax + C68kScript.asr32(d1 + d2, 2))

## 0xf85c (1.0.3's fold_angle_pm_0x2000 0xe3ec, the same 10 instructions; the generated port held a stub for
## its 0x2000 / 0x4000 constants): the word angle folded into -0x2000..0x2000 (+-0x4000 once), sign-extended.
func fold_angle_pm_0x2000(angle: int) -> int:
	var a: int = C68kScript.s16(angle)
	if a > 0x2000:
		a = C68kScript.s16(a - 0x4000)
	elif a < -0x2000:
		a = C68kScript.s16(a + 0x4000)
	return a

## 0x83de (1.0.3's copy_player_pose_to_camera 0x737a): the camera sits on the player: the position of the
## player's object (+4 / +8 / +0xc; 1.0.3 +0x18..) into 0x23d68 / 0x23d64 / 0x23d60, negated pitch / heading /
## bank (0x23d40 / 0x23d3e / 0x23d3c) and camera_angle4 (0x23d38) = (type record +0x46, 1.0.3 +0x44 -
## controller +0x626) & 0x3fff (long).
func copy_player_pose_to_camera() -> void:
	assert(memory != null, "copy_player_pose_to_camera requires original global memory")
	var ctl: int = memory.read_u32(0x20b28)
	for k in 3:
		memory.write_u32([0x23d68, 0x23d64, 0x23d60][k], memory.read_u32(memory.read_u32(ctl) + 4 + 4 * k))
	memory.write_u16(0x23d40, (-memory.read_u16(ctl + 0x20)) & 0x3fff)
	memory.write_u16(0x23d3e, (-memory.read_u16(ctl + 0x22)) & 0x3fff)
	memory.write_u16(0x23d3c, (-memory.read_u16(ctl + 0x24)) & 0x3fff)
	memory.write_u32(0x23d38, C68kScript.s32(memory.read_s16(memory.read_u32(ctl + 4) + 0x46) - memory.read_s16(ctl + 0x626)) & 0x3fff)

## 0x8032 (hook 0, registered with the renderer routines it installs; 1.0.3's configure_renderer_for_display 0x6fd6,
## the same 157 instructions, derived from its port by the pairing's table: Leyte's globals, the hook tables,
## host +0x2ae for 1.0.3's +0x276, mission child 4's faces +0x4a, the depth-1 face output 0xb482): view geometry
## from the host; on a depth / multi-screen change the colour state (0x1948 / 0x1964), the renderer hooks, the
## mission build when absent (0x1d2a, 0xc below), the template words (0x0) and child 4's face flags; then the
## clip bounds and update_view_clip_bounds (0x7f12).
func configure_renderer_for_display() -> void:
	assert(memory != null, "configure_renderer_for_display requires original global memory")
	var entry_a7: int = a7
	var world: int = memory.read_u32(0x207fc)
	memory.write_u16(0x21010, memory.read_u16(world + 0x18))
	memory.write_u16(0x27012, memory.read_u16(0x21010))
	var width: int = memory.read_s16(world + 0x16)
	memory.write_u16(0x27052, C68kScript.u16(width))
	memory.write_u16(0x21012, C68kScript.u16(width))
	memory.write_u16(0x21016, memory.read_u16(world + 0x12))
	memory.write_u16(0x21014, memory.read_u16(world + 0x14))
	memory.write_u16(0x27054, C68kScript.u16(memory.read_s16(0x21010) >> 1))
	var half_w: int = width >> 1
	memory.write_u16(0x27052, C68kScript.u16(half_w))
	memory.write_u32(0x2701c, C68kScript.u32(C68kScript.lsl32(C68kScript.s32(half_w * 2 - 1), 16)) | 0x8000)
	var half_toward: int = width
	if half_toward < 0:
		half_toward += 1
	var scale: int = C68kScript.s32(width + C68kScript.s16(half_toward >> 1))
	if scale < 0:
		scale += 1
	scale = C68kScript.u16(scale >> 1)
	for g in [0x27050, 0x2704e, 0x2703c, 0x27024]:
		memory.write_u16(g, scale)
	world = memory.read_u32(0x207fc)
	var depth: int = memory.read_s16(world + 0xe)
	if not (memory.read_u16(0x2100e) == memory.read_u16(world + 0xe) and memory.read_u16(0x2100c) == memory.read_u16(world + 0xb4)):
		if depth == 8:
			a7 = entry_a7 - 8
			init_depth8_colour_state()
			a7 = entry_a7
			for h in LEYTE_RENDERER_HOOKS_8:
				memory.write_u32(h[0], h[1])
		if memory.read_s16(memory.read_u32(0x207fc) + 0xe) == 4:
			a7 = entry_a7 - 8
			init_depth4_colour_state()
			a7 = entry_a7
			for h in LEYTE_RENDERER_HOOKS_4:
				memory.write_u32(h[0], h[1])
		world = memory.read_u32(0x207fc)
		if memory.read_s16(world + 0xe) == 1:
			memory.write_u32(0x26fe4, 0xb482)
			memory.write_u32(0x23fcc, memory.read_u32(world + 0x2ae))
			memory.write_u32(0x23fc8, memory.read_u32(world + 0x2ae))
			for h in LEYTE_RENDERER_HOOKS_1:
				memory.write_u32(h[0], h[1])
		world = memory.read_u32(0x207fc)
		memory.write_u16(0x2100e, memory.read_u16(world + 0xe))
		memory.write_u16(0x2100c, memory.read_u16(world + 0xb4))
		memory.write_u32(0x23d8c, memory.read_u32(world + 0x34))
		memory.write_u16(0x23d8a, memory.read_u16(world + 0x38))
		if memory.read_u32(0x2159c) == 0:
			a7 = entry_a7 - 0xc
			init_mission_object_tables()
			a7 = entry_a7
		var bw: bool = memory.read_s16(memory.read_u32(0x207fc) + 0xe) == 1
		a7 = entry_a7 - 0xc
		overlay_patch_template_u16_pair(0x195 if bw else 0x1d5)
		a7 = entry_a7
		var faces: int = memory.read_u32(memory.read_u32(0x2158c) + 0x4a)
		for k in range(1, 5):
			var flag: int = faces + 0x20 * k + 0x13
			memory.write_u8(flag, (memory.read_u8(flag) & 0xfd) if bw else (memory.read_u8(flag) | 2))
	world = memory.read_u32(0x207fc)
	memory.write_u32(0x23d8c, memory.read_u32(world + 0x34))
	memory.write_u16(0x23d8a, memory.read_u16(world + 0x38))
	var w2: int = memory.read_s16(0x21012)
	var neg_w: int = -w2
	if neg_w < 0:
		neg_w += 1
	memory.write_u16(0x2701a, C68kScript.u16(neg_w >> 1))
	var pw: int = w2
	if pw < 0:
		pw += 1
	memory.write_u16(0x27018, C68kScript.u16((pw >> 1) - 1))
	var rows: int = memory.read_s16(0x21010)
	var pr: int = rows
	if pr < 0:
		pr += 1
	pr = C68kScript.u16(pr >> 1)
	memory.write_u16(0x27014, pr)
	memory.write_u16(0x20b30, pr)
	memory.write_u16(0x20b2e, pr)
	var neg_r: int = -rows
	if neg_r < 0:
		neg_r += 1
	memory.write_u16(0x27016, C68kScript.u16(neg_r >> 1))
	a7 = entry_a7 - 8
	update_view_clip_bounds()
	a7 = entry_a7

## Leyte's depth-8 renderer routines (hook 0's renderer hooks), each the same instructions as 1.0.3's - the
## pairing found no real difference - derived from 1.0.3's port: Leyte's globals, the span writer's long-run
## labels moved with fill_span_rows_8bit (+0xb00: 0x4338 / 0x4396; the tables 0x27224 / 0x29224 and the short
## writers are the generated port's), the line routine's labels (comments) moved with it (+0x13e8), the blip
## routines' host fields (+0xe0 / +0xe4 for 1.0.3's +0xdc / +0xe0, +0x1eb for +0x1e7). Addresses: span filler
## 0x4294, draw / restore line 0xe784 / 0xece2, dot plotter 0x19f12, blip drawers 0x1b992 / 0x1b902.
## 0x37b4: redraw the background rows on an 8-bit screen by difference. For each of rows rows the
## new row record (*new_ptr) and the previous one (*old_ptr) are walked together (header long =
## span count - 1, then (x << 16 | colour) spans ending at x); each stretch where the new colour
## differs from the old is written at the screen cursor through the run-writer tables (the table
## by the cursor's byte parity, swapped after an odd-length run; 0x3838 writes words to a long
## boundary then longs), equal stretches are skipped. The cursor then skips skip bytes. The record
## words are read one long ahead, so at the end both pointers are stored back one long before
## their read position (the next row's header). The table entries are code addresses (run lengths
## index them as words).
func fill_span_rows_8bit(new_ptr: int, old_ptr: int, rows: int, screen: int, skip: int) -> void:
	assert(memory != null, "fill_span_rows_8bit requires original global memory")
	var a0: int = memory.read_u32(new_ptr)
	var a1: int = memory.read_u32(old_ptr)
	var cur: int = SPAN8_TABLE_EVEN
	var other: int = SPAN8_TABLE_ODD
	var a3: int = C68kScript.u32(screen)
	var left: int = C68kScript.u32(rows)
	var d0: int = memory.read_u32(a0)
	a0 += 4
	var d1: int = memory.read_u32(a1)
	a1 += 4
	while true:
		var d7: int = C68kScript.u32(d0 + d1)
		d7 = (d7 & 0xffff0000) | ((d7 - 1) & 0xffff)
		var d2: int = 0
		d0 = memory.read_u32(a0)
		a0 += 4
		d1 = memory.read_u32(a1)
		a1 += 4
		var row_done: bool = false
		while not row_done:
			var colour: int = d0 & 0xffff
			var d0x: int = (d0 & 0xffff0000) | (d1 & 0xffff)
			var run: int
			var write: bool
			if C68kScript.s32(d1) < C68kScript.s32(d0x):
				# The old span ends first.
				d0 = (d0 & 0xffff0000) | colour
				run = C68kScript.u16((d1 >> 16) - d2)
				d2 = C68kScript.u16(d2 + run)
				write = colour != (d1 & 0xffff)
				d1 = memory.read_u32(a1)
				a1 += 4
			elif C68kScript.s32(d1) == C68kScript.s32(d0x):
				run = C68kScript.u16((d0 >> 16) - d2)
				d2 = C68kScript.u16(d2 + run)
				d0 = memory.read_u32(a0)
				a0 += 4
				write = colour != (d1 & 0xffff)
				d1 = memory.read_u32(a1)
				a1 += 4
				d7 = (d7 & 0xffff0000) | ((d7 - 1) & 0xffff)
			else:
				# The new span ends first.
				run = C68kScript.u16((d0 >> 16) - d2)
				d2 = C68kScript.u16(d2 + run)
				d0 = memory.read_u32(a0)
				a0 += 4
				write = colour != (d1 & 0xffff)
			if write:
				var label: int = memory.read_u32(C68kScript.u32(cur + C68kScript.s16(run) * 4))
				var res: Array = _span8_write(label, run, colour, a3, cur, other)
				a3 = res[0]
				cur = res[1]
				other = res[2]
			else:
				a3 = C68kScript.u32(a3 + C68kScript.s16(run))
				if (run & 1) != 0:
					var t: int = cur
					cur = other
					other = t
			d7 = (d7 & 0xffff0000) | ((d7 - 1) & 0xffff)
			if (d7 & 0xffff) == 0xffff:
				row_done = true
		a3 = C68kScript.u32(a3 + skip)
		left = C68kScript.u32(left - 1)
		if left == 0:
			break
	memory.write_u32(new_ptr, C68kScript.u32(a0 - 4))
	memory.write_u32(old_ptr, C68kScript.u32(a1 - 4))

func _span8_write(label: int, run: int, colour: int, a3: int, cur: int, other: int) -> Array:
	if SPAN8_WRITERS.has(label):
		for op in String(SPAN8_WRITERS[label]):
			match op:
				"b":
					memory.write_u8(a3, colour & 0xff)
					a3 = C68kScript.u32(a3 + 1)
				"w":
					memory.write_u16(a3, colour)
					a3 = C68kScript.u32(a3 + 2)
				"s":
					var t: int = cur
					cur = other
					other = t
		return [a3, cur, other]
	assert(label == 0x4338 or label == 0x4396, "fill_span_rows_8bit: unknown run writer 0x%x" % label)
	var d3: int = run
	if label == 0x4396:
		memory.write_u8(a3, colour & 0xff)
		a3 = C68kScript.u32(a3 + 1)
		var t0: int = cur
		cur = other
		other = t0
		d3 = C68kScript.u16(d3 - 1)
	if (a3 & 2) != 0:
		memory.write_u16(a3, colour)
		a3 = C68kScript.u32(a3 + 2)
		d3 = C68kScript.u16(d3 - 2)
	var longs: int = _dbra_word_count((d3 & 0xffff) >> 2)
	var pair: int = ((colour & 0xffff) << 16) | (colour & 0xffff)
	for k in longs:
		memory.write_u32(a3, pair)
		a3 = C68kScript.u32(a3 + 4)
	d3 = d3 & 3
	if d3 != 0:
		return _span8_write(memory.read_u32(C68kScript.u32(cur + d3 * 4)), d3, colour, a3, cur, other)
	return [a3, cur, other]

## The shared two-pixel-thick Bresenham of draw_line_depth8 (0xd3bc) and restore_line_depth8
## (0xd91a), block for block. Draw writes the colour byte / word (c | c << 8); restore copies the
## same bytes / words from the backup bitmap (src base / row bytes), its pointers (a3 / a4) moving
## in step with the screen ones (a0 / a1). X-major lines (|dx| >= |dy|) run left to right two rows
## thick (a0 on the row, a1 one below), y-major ones top to bottom two columns wide; words are
## written on even addresses and the odd pixel of a diagonal step by byte. err starts at 2 minor -
## major (incs 2 minor / 2 (minor - major)); the dbra counter is the major delta - 1 (word). Row
## offsets are mulu.w (row bytes, y). The bgt tests after a jump use the flags of the last
## add / tst of err, i.e. err > 0.
func _line_depth8(restore: bool, x0: int, y0: int, x1: int, y1: int, colour: int, base: int, row: int, src_base: int, src_row: int) -> void:
	var cb: int = colour & 0xff
	var cw: int = ((colour << 8) | colour) & 0xffff
	var put_b := func(d: int, sp: int) -> void:
		memory.write_u8(C68kScript.u32(d), memory.read_u8(C68kScript.u32(sp)) if restore else cb)
	var put_w := func(d: int, sp: int) -> void:
		memory.write_u16(C68kScript.u32(d), memory.read_u16(C68kScript.u32(sp)) if restore else cw)
	var dx: int = abs_int(C68kScript.s32(x0 - x1))
	var dy: int = abs_int(C68kScript.s32(y0 - y1))
	var d7: int
	var err: int
	var straight: int
	var diag: int
	var a0: int
	var a1: int
	var a3: int = 0
	var a4: int = 0
	var label: int
	if not (dy > dx):
		if x1 < x0:
			var t: int = x0
			x0 = x1
			x1 = t
			t = y0
			y0 = y1
			y1 = t
		d7 = (dx - 1) & 0xffff
		a0 = C68kScript.u32(base + x0 + (row & 0xffff) * (y0 & 0xffff))
		a1 = C68kScript.u32(a0 + row)
		if restore:
			a3 = C68kScript.u32(src_base + x0 + (src_row & 0xffff) * (y0 & 0xffff))
			a4 = C68kScript.u32(a3 + src_row)
		err = C68kScript.s32(2 * dy - dx)
		diag = C68kScript.s32(2 * (dy - dx))
		straight = C68kScript.s32(dx + err)
		var dir: int = 1 if y1 > y0 else -1
		# Labels: 0 start, 1 word step (0xe81a), 2 err check (0xe83e), 3 end (0xe8b0).
		label = 0
		while true:
			match label:
				0:
					if (a0 & 1) != 0:
						put_b.call(a0, a3)
						put_b.call(a1, a4)
						a0 += 1
						a1 += 1
						a3 += 1
						a4 += 1
						label = 2
					else:
						label = 1
				1:
					put_w.call(a0, a3)
					put_w.call(a1, a4)
					a0 += 2
					a1 += 2
					a3 += 2
					a4 += 2
					if err > 0:
						a0 += row * dir
						a1 += row * dir
						a3 += src_row * dir
						a4 += src_row * dir
						if dir > 0:
							put_b.call(a1 - 1, a4 - 1)
						else:
							put_b.call(a0 - 1, a3 - 1)
						err = C68kScript.s32(err + diag)
					else:
						err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					label = 2 if d7 != 0xffff else 3
				2:
					if err > 0:
						if dir > 0:
							put_b.call(a0, a3)
						else:
							put_b.call(a1, a4)
						a0 += row * dir
						a1 += row * dir
						a3 += src_row * dir
						a4 += src_row * dir
						err = C68kScript.s32(err + diag)
					else:
						err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					if d7 != 0xffff:
						label = 1
					else:
						put_b.call(a0, a3)
						put_b.call(a1, a4)
						a0 += 1
						a1 += 1
						a3 += 1
						a4 += 1
						label = 3
				3:
					put_b.call(a0, a3)
					put_b.call(a1, a4)
					return
	# Y-major: two columns wide, top to bottom.
	if y1 < y0:
		var t2: int = x0
		x0 = x1
		x1 = t2
		t2 = y0
		y0 = y1
		y1 = t2
	d7 = (dy - 1) & 0xffff
	a0 = C68kScript.u32(base + x0 + (row & 0xffff) * (y0 & 0xffff))
	a1 = C68kScript.s32(row - 1)
	if restore:
		a3 = C68kScript.u32(src_base + x0 + (src_row & 0xffff) * (y0 & 0xffff))
		a4 = C68kScript.s32(src_row - 1)
	err = C68kScript.s32(2 * dx - dy)
	diag = C68kScript.s32(2 * (dx - dy))
	straight = C68kScript.s32(dy + err)
	var right: bool = x1 > x0
	# Labels: 0 start, 1 byte-pair step (0xe8fa / 0xe94c), 2 word step (0xe920 / 0xe974), 3 end.
	label = 0
	while true:
		match label:
			0:
				if (a0 & 1) != 0:
					put_b.call(a0, a3)
					a0 += 1
					a3 += 1
					put_b.call(a0, a3)
					a0 += a1
					a3 += a4
					label = 1
				else:
					put_w.call(a0, a3)
					a0 += row
					a3 += src_row
					label = 2
			1:
				if err > 0:
					if right:
						put_b.call(a0, a3)
						a0 += 1
						a3 += 1
						put_w.call(a0, a3)
					else:
						put_b.call(a0 + 1, a3 + 1)
						a0 -= 1
						a3 -= 1
						put_w.call(a0, a3)
					a0 += row
					a3 += src_row
					err = C68kScript.s32(err + diag)
					d7 = (d7 - 1) & 0xffff
					label = 2 if d7 != 0xffff else 3
				else:
					put_b.call(a0, a3)
					a0 += 1
					a3 += 1
					put_b.call(a0, a3)
					a0 += a1
					a3 += a4
					err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					label = 1 if d7 != 0xffff else 3
			2:
				if err > 0:
					if right:
						put_w.call(a0, a3)
						a0 += 2
						a3 += 2
						put_b.call(a0, a3)
						a0 += a1
						a3 += a4
					else:
						put_w.call(a0, a3)
						a0 -= 1
						a3 -= 1
						put_b.call(a0, a3)
						a0 += row
						a3 += src_row
					err = C68kScript.s32(err + diag)
					d7 = (d7 - 1) & 0xffff
					label = 1 if d7 != 0xffff else 3
				else:
					put_w.call(a0, a3)
					a0 += row
					a3 += src_row
					err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					label = 2 if d7 != 0xffff else 3
			3:
				put_b.call(a0, a3)
				a0 += 1
				a3 += 1
				put_b.call(a0, a3)
				return

## 0xd3bc: a two-pixel-thick line in colour (low byte, doubled for words) on an 8-bit screen at
## base with row bytes (_line_depth8).
func draw_line_depth8(x0: int, y0: int, x1: int, y1: int, colour: int, base: int, row: int) -> void:
	assert(memory != null, "draw_line_depth8 requires original global memory")
	_line_depth8(false, x0, y0, x1, y1, colour, base, row, 0, 0)

## 0xd91a: the pixels draw_line_depth8 would set, copied back from the backup bitmap (src base /
## row bytes) onto the screen (base / row bytes) (_line_depth8).
func restore_line_depth8(x0: int, y0: int, x1: int, y1: int, base: int, row: int, src_base: int, src_row: int) -> void:
	assert(memory != null, "restore_line_depth8 requires original global memory")
	_line_depth8(true, x0, y0, x1, y1, 0, base, row, src_base, src_row)

## 0x15f3e: plot a dot of size 1..4 on an 8-bit screen at (view_half_width + sx, view_half_rows +
## sy): the pixel address goes to rec +0, each covered byte is saved (+9..+0xc as in
## erase_dots_depth8) and set to the low byte of bw_pattern_first.
func plot_dot_depth8(rec: int, sx: int, sy: int, size: int) -> void:
	assert(memory != null, "plot_dot_depth8 requires original global memory")
	size = C68kScript.s16(size)
	var row: int = memory.read_s16(memory.read_u32(0x207fc) + 0x10)
	var colour: int = memory.read_u8(0x23fb8 + 1)
	var pix: int = C68kScript.u32(_dot_row_base(sy) + C68kScript.s32(sx) + memory.read_s16(0x27052))
	memory.write_u32(rec, pix)
	memory.write_u8(rec + 9, memory.read_u8(pix))
	memory.write_u8(pix, colour)
	if size > 2:
		memory.write_u8(rec + 0xa, memory.read_u8(pix + 1))
		memory.write_u8(pix + 1, colour)
	pix = C68kScript.u32(pix + row)
	if size > 1:
		memory.write_u8(rec + 0xb, memory.read_u8(pix))
		memory.write_u8(pix, colour)
	if size > 3:
		memory.write_u8(rec + 0xc, memory.read_u8(pix + 1))
		memory.write_u8(pix + 1, colour)

## 0x17780 (depth-8 *0x1da90): erase a radar blip on an 8-bit screen: the blip's 12 bytes come back
## from the saved panel.
func erase_radar_blip_depth8(x: int, y: int) -> void:
	assert(memory != null, "erase_radar_blip_depth8 requires original global memory")
	var host: int = memory.read_u32(0x207fc)
	var at: Array = _blip_addresses(C68kScript.s16(x), y)
	for cell in _blip_cross8(x):
		memory.write_u8(at[0] + cell[0] * memory.read_s16(host + 0x10) + cell[1], memory.read_u8(at[1] + cell[0] * memory.read_s16(host + 0xe4) + cell[1]))

## 0xb3d0 (1.0.3's output_face_colour 0xa14e, identical apart from moved globals and callees: the four clip
## passes 0xc458 / 0xc50a / 0xc5bc / 0xc672, the horizon tracker 0xb2e0, add_face_edges_to_rows 0xaddc):
## clip the screen polygon (_clip_face_polygon) and rasterise it into the row buckets. Returns 1, or 0 when
## clipped away.
func output_face_colour(face: int) -> int:
	assert(memory != null, "output_face_colour requires original global memory")
	var entry_a7: int = a7
	if not _clip_face_polygon(face, entry_a7, 0x18, 0x10):
		return 0
	a7 = entry_a7 - 0x10
	add_face_edges_to_rows(face)
	a7 = entry_a7
	return 1

func _fill_span_rows_4bit(new_arg: int, old_arg: int, rows: int, screen: int, skip: int, table: int, l0: int, handles: bool) -> void:
	var a0: int = memory.read_u32(new_arg) if handles else C68kScript.u32(new_arg)
	var a1: int = memory.read_u32(old_arg) if handles else C68kScript.u32(old_arg)
	var a3: int = C68kScript.u32(screen)
	var left: int = C68kScript.u32(rows)
	var d0: int = memory.read_u32(a0)
	a0 += 4
	var d1: int = memory.read_u32(a1)
	a1 += 4
	var d5: int = 0
	while true:
		var d7: int = C68kScript.u32(d0 + d1)
		d7 = (d7 & 0xffff0000) | ((d7 - 1) & 0xffff)
		var d2: int = 0
		d0 = memory.read_u32(a0)
		a0 += 4
		d1 = memory.read_u32(a1)
		a1 += 4
		while true:
			var colour: int = d0 & 0xffff
			var changed: bool = colour != (d1 & 0xffff)
			var d0x: int = (d0 & 0xffff0000) | (d1 & 0xffff)
			var d3: int
			if C68kScript.s32(d1) < C68kScript.s32(d0x):
				d0 = (d0 & 0xffff0000) | colour
				d3 = C68kScript.u32(d1 - d2)
				d2 = d1
				d1 = memory.read_u32(a1)
				a1 += 4
			elif d1 == d0x:
				d3 = C68kScript.u32(d0x - d2)
				d2 = d0x
				d0 = memory.read_u32(a0)
				a0 += 4
				d1 = memory.read_u32(a1)
				a1 += 4
				d7 = (d7 & 0xffff0000) | ((d7 - 1) & 0xffff)
			else:
				d3 = C68kScript.u32(d0x - d2)
				d2 = d0x
				d0 = memory.read_u32(a0)
				a0 += 4
			d2 = d2 & 0xffff0000
			var run: int = (d3 >> 16) & 0xffff
			if handles and C68kScript.s32(d5) < 0:
				colour = colour >> 8
			d5 = d5 & 0xffff0001
			if (d5 & 0xffff) != 0:
				if run != 0:
					d5 = (d5 & 0xffff0000) | ((d5 + run) & 0xffff)
					if not changed:
						a3 = C68kScript.u32(a3 + C68kScript.s16((((run + 1) & 0xffff) >> 1)))
					else:
						memory.write_u8(a3, (memory.read_u8(a3) & 0xf0) | (colour & 0x0f))
						a3 = C68kScript.u32(a3 + 1)
						run = C68kScript.u16(run - 1)
						if C68kScript.s16(run) > 0:
							a3 = _span4_write(run, colour, a3, table, l0)
			else:
				d5 = (d5 & 0xffff0000) | ((d5 + run) & 0xffff)
				if not changed:
					a3 = C68kScript.u32(a3 + C68kScript.s16(run >> 1))
				else:
					a3 = _span4_write(run, colour, a3, table, l0)
			d7 = (d7 & 0xffff0000) | ((d7 - 1) & 0xffff)
			if (d7 & 0xffff) == 0xffff:
				break
		a3 = C68kScript.u32(a3 + skip)
		left = C68kScript.u32(left - 1)
		if left == 0:
			break
		d5 = C68kScript.u32(-C68kScript.s32(d5)) & 0xffff0000
	memory.write_u32(new_arg, C68kScript.u32(a0 - 4))
	memory.write_u32(old_arg, C68kScript.u32(a1 - 4))

func _span4_write(run: int, colour: int, a3: int, table: int, l0: int) -> int:
	if C68kScript.s16(run) > 8:
		colour = colour & 0xff
		var quad: int = colour * 0x01010101
		for k in _dbra_word_count(run >> 3):
			memory.write_u32(a3, quad)
			a3 = C68kScript.u32(a3 + 4)
		run = run & 7
	while true:
		var role: int = memory.read_u32(C68kScript.u32(table + C68kScript.s16(run) * 4)) - l0
		match role:
			0:
				return a3
			6:
				memory.write_u8(a3, (memory.read_u8(a3) & 0x0f) | (colour & 0xf0))
				return a3
			0x18:
				memory.write_u8(a3, colour & 0xff)
				return C68kScript.u32(a3 + 1)
			0x20:
				memory.write_u8(a3, colour & 0xff)
				a3 = C68kScript.u32(a3 + 1)
				memory.write_u8(a3, (memory.read_u8(a3) & 0x0f) | (colour & 0xf0))
				return a3
			0x24:
				memory.write_u8(a3, colour & 0xff)
				memory.write_u8(C68kScript.u32(a3 + 1), colour & 0xff)
				return C68kScript.u32(a3 + 2)
			0x50:
				for k in _dbra_word_count(run >> 2):
					memory.write_u8(a3, colour & 0xff)
					memory.write_u8(C68kScript.u32(a3 + 1), colour & 0xff)
					a3 = C68kScript.u32(a3 + 2)
				run = run & 3
			_:
				assert(false, "4-bit span filler: unknown run writer 0x%x" % (role + l0))
				return a3
	return a3

## 0x3d9a (1.0.3 0x329a): variant A (record handles, alternate-row colour byte).
func fill_span_rows_4bit_a(new_ptr: int, old_ptr: int, rows: int, screen: int, skip: int) -> void:
	assert(memory != null, "fill_span_rows_4bit_a requires original global memory")
	_fill_span_rows_4bit(new_ptr, old_ptr, rows, screen, skip, 0x3d22, 0x3e34, true)

## 0x40e8 (1.0.3 0x35e8): variant C (as B, its own table).
func fill_span_rows_4bit_c(new_rows: int, old_rows: int, rows: int, screen: int, skip: int) -> void:
	assert(memory != null, "fill_span_rows_4bit_c requires original global memory")
	_fill_span_rows_4bit(new_rows, old_rows, rows, screen, skip, 0x4070, 0x4178, false)

## 0xe978 / 0xef28 (1.0.3 0xd590 / 0xdb40): the depth-4 line draw and restore; labels +0x13e8.
func _line_depth4(restore: bool, x0: int, y0: int, x1: int, y1: int, colour: int, base: int, row: int, src_base: int, src_row: int) -> void:
	var cb: int = colour & 0xff
	var val := func(sp: int) -> int:
		return memory.read_u8(C68kScript.u32(sp)) if restore else cb
	var full := func(d: int, sp: int) -> void:
		memory.write_u8(C68kScript.u32(d), val.call(sp))
	var lo := func(d: int, sp: int) -> void:
		memory.write_u8(C68kScript.u32(d), (memory.read_u8(C68kScript.u32(d)) & 0xf0) | (val.call(sp) & 0x0f))
	var hi := func(d: int, sp: int) -> void:
		memory.write_u8(C68kScript.u32(d), (memory.read_u8(C68kScript.u32(d)) & 0x0f) | (val.call(sp) & 0xf0))
	var dx: int = abs_int(C68kScript.s32(x0 - x1))
	var dy: int = abs_int(C68kScript.s32(y0 - y1))
	var d7: int
	var err: int
	var straight: int
	var diag: int
	var a0: int
	var a1: int
	var a3: int = 0
	var a4: int = 0
	var label: int
	if not (dy > dx):
		if x1 < x0:
			var t: int = x0
			x0 = x1
			x1 = t
			t = y0
			y0 = y1
			y1 = t
		d7 = (dx - 1) & 0xffff
		a0 = C68kScript.u32(base + (C68kScript.u32(x0) >> 1) + (row & 0xffff) * (y0 & 0xffff))
		a1 = C68kScript.u32(a0 + row)
		if restore:
			a3 = C68kScript.u32(src_base + (C68kScript.u32(x0) >> 1) + (src_row & 0xffff) * (y0 & 0xffff))
			a4 = C68kScript.u32(a3 + src_row)
		err = C68kScript.s32(2 * dy - dx)
		diag = C68kScript.s32(2 * (dy - dx))
		straight = C68kScript.s32(dx + err)
		var dir: int = 1 if y1 > y0 else -1
		# Labels: 0 start, 1 pair step (0xea26), 2 err check (0xea58), 3 end high (0xeb6e),
		# 4 end low (0xeb52).
		label = 0
		while true:
			match label:
				0:
					if (x0 & 1) != 0:
						lo.call(a0, a3)
						lo.call(a1, a4)
						a0 += 1
						a1 += 1
						a3 += 1
						a4 += 1
						label = 2
					else:
						label = 1
				1:
					full.call(a0, a3)
					full.call(a1, a4)
					a0 += 1
					a1 += 1
					a3 += 1
					a4 += 1
					if err > 0:
						a0 += row * dir
						a1 += row * dir
						a3 += src_row * dir
						a4 += src_row * dir
						if dir > 0:
							lo.call(a1 - 1, a4 - 1)
						else:
							lo.call(a0 - 1, a3 - 1)
						err = C68kScript.s32(err + diag)
					else:
						err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					label = 2 if d7 != 0xffff else 3
				2:
					if err > 0:
						if dir > 0:
							hi.call(a0, a3)
						else:
							hi.call(a1, a4)
						a0 += row * dir
						a1 += row * dir
						a3 += src_row * dir
						a4 += src_row * dir
						err = C68kScript.s32(err + diag)
					else:
						err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					if d7 != 0xffff:
						label = 1
					else:
						hi.call(a0, a3)
						hi.call(a1, a4)
						label = 4
				3:
					hi.call(a0, a3)
					hi.call(a1, a4)
					return
				4:
					lo.call(a0, a3)
					lo.call(a1, a4)
					return
	if y1 < y0:
		var t2: int = x0
		x0 = x1
		x1 = t2
		t2 = y0
		y0 = y1
		y1 = t2
	d7 = (dy - 1) & 0xffff
	a0 = C68kScript.u32(base + (C68kScript.u32(x0) >> 1) + (row & 0xffff) * (y0 & 0xffff))
	a1 = C68kScript.s32(row - 1)
	if restore:
		a3 = C68kScript.u32(src_base + (C68kScript.u32(x0) >> 1) + (src_row & 0xffff) * (y0 & 0xffff))
		a4 = C68kScript.s32(src_row - 1)
	err = C68kScript.s32(2 * dx - dy)
	diag = C68kScript.s32(2 * (dx - dy))
	straight = C68kScript.s32(dy + err)
	var right: bool = x1 > x0
	# Labels: 0 start, 1 pair column (0xebe4 / 0xec76), 2 whole byte (0xec2a / 0xecbc),
	# 3 end pair (0xece0), 4 end byte (0xecfa).
	label = 0
	while true:
		match label:
			0:
				if (x0 & 1) != 0:
					lo.call(a0, a3)
					a0 += 1
					a3 += 1
					hi.call(a0, a3)
					a0 += a1
					a3 += a4
					label = 1
				else:
					full.call(a0, a3)
					a0 += row
					a3 += src_row
					label = 2
			1:
				if err > 0:
					if right:
						lo.call(a0, a3)
						a0 += 1
						a3 += 1
						full.call(a0, a3)
					else:
						hi.call(a0 + 1, a3 + 1)
						full.call(a0, a3)
					a0 += row
					a3 += src_row
					err = C68kScript.s32(err + diag)
					d7 = (d7 - 1) & 0xffff
					label = 2 if d7 != 0xffff else 4
				else:
					lo.call(a0, a3)
					a0 += 1
					a3 += 1
					hi.call(a0, a3)
					a0 += a1
					a3 += a4
					err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					label = 1 if d7 != 0xffff else 3
			2:
				if err > 0:
					if right:
						full.call(a0, a3)
						a0 += 1
						a3 += 1
						hi.call(a0, a3)
						a0 += a1
						a3 += a4
					else:
						full.call(a0, a3)
						a0 -= 1
						a3 -= 1
						lo.call(a0, a3)
						a0 += row
						a3 += src_row
					err = C68kScript.s32(err + diag)
					d7 = (d7 - 1) & 0xffff
					label = 1 if d7 != 0xffff else 3
				else:
					full.call(a0, a3)
					a0 += row
					a3 += src_row
					err = C68kScript.s32(err + straight)
					d7 = (d7 - 1) & 0xffff
					label = 2 if d7 != 0xffff else 4
			3:
				lo.call(a0, a3)
				a0 += 1
				a3 += 1
				hi.call(a0, a3)
				return
			4:
				full.call(a0, a3)
				return

func draw_line_depth4(x0: int, y0: int, x1: int, y1: int, colour: int, base: int, row: int) -> void:
	assert(memory != null, "draw_line_depth4 requires original global memory")
	_line_depth4(false, x0, y0, x1, y1, colour, base, row, 0, 0)

func restore_line_depth4(x0: int, y0: int, x1: int, y1: int, base: int, row: int, src_base: int, src_row: int) -> void:
	assert(memory != null, "restore_line_depth4 requires original global memory")
	_line_depth4(true, x0, y0, x1, y1, 0, base, row, src_base, src_row)

## 0x1a104 (1.0.3 0x16108): plot a dot on a 4-bit screen (see 1.0.3's port).
func plot_dot_depth4(rec: int, sx: int, sy: int, size: int) -> void:
	assert(memory != null, "plot_dot_depth4 requires original global memory")
	size = C68kScript.s16(size)
	var row: int = memory.read_s16(memory.read_u32(0x207fc) + 0x10)
	var colour: int = memory.read_u8(0x23fb8 + 1)
	var px: int = C68kScript.s16(sx + memory.read_s16(0x27052))
	var rounded: int = C68kScript.s16(px + 1) if px < 0 else px
	var pix: int = C68kScript.u32(_dot_row_base(sy) + (rounded >> 1))
	memory.write_u16(rec + 4, C68kScript.u16(px - C68kScript.s16(rounded & 0xfffe)))
	memory.write_u32(rec, pix)
	if memory.read_u16(rec + 4) == 0:
		memory.write_u8(rec + 9, memory.read_u8(pix))
		if size > 2:
			memory.write_u8(pix, colour)
		else:
			_plot_nibble(pix, colour, 0x0f)
		pix = C68kScript.u32(pix + row)
		memory.write_u8(rec + 0xb, memory.read_u8(pix))
		if size > 3:
			memory.write_u8(pix, colour)
		elif size > 1:
			_plot_nibble(pix, colour, 0x0f)
	else:
		memory.write_u8(rec + 9, memory.read_u8(pix))
		_plot_nibble(pix, colour, 0xf0)
		if size > 2:
			memory.write_u8(rec + 0xa, memory.read_u8(pix + 1))
			_plot_nibble(pix + 1, colour, 0x0f)
		pix = C68kScript.u32(pix + row)
		if size > 1:
			memory.write_u8(rec + 0xb, memory.read_u8(pix))
			_plot_nibble(pix, colour, 0xf0)
		if size > 3:
			memory.write_u8(rec + 0xc, memory.read_u8(pix + 1))
			_plot_nibble(pix + 1, colour, 0x0f)

func _blip_column4(x: int) -> Array:
	x = C68kScript.s16(x)
	var rounded: int = x + 1 if x < 0 else x
	return [C68kScript.s16(rounded) >> 1, C68kScript.s16(x - C68kScript.s16(rounded & 0xfffe))]

## 0x1baca (1.0.3 0x17928): draw a 4-bit blip in the low byte of host word +0x1ea.
func draw_radar_blip_depth4(x: int, y: int) -> void:
	assert(memory != null, "draw_radar_blip_depth4 requires original global memory")
	var host: int = memory.read_u32(0x207fc)
	var row: int = memory.read_s16(host + 0x10)
	var col: Array = _blip_column4(x)
	var frame: int = _blip_addresses(col[0], y)[0]
	var colour: int = memory.read_u8(host + 0x1eb)
	var cells: Array
	if col[1] != 0:
		cells = [[0, 0, 0xf0], [0, 1, 0x0f], [1, 0, 0], [1, 1, 0], [2, 0, 0], [2, 1, 0], [3, 0, 0xf0], [3, 1, 0x0f]]
	else:
		cells = [[0, 0, 0], [1, -1, 0xf0], [1, 0, 0], [1, 1, 0x0f], [2, -1, 0xf0], [2, 0, 0], [2, 1, 0x0f], [3, 0, 0]]
	for cell in cells:
		var at: int = frame + cell[0] * row + cell[1]
		if cell[2] == 0:
			memory.write_u8(at, colour)
		else:
			memory.write_u8(at, ((memory.read_u8(at) ^ colour) & cell[2]) ^ colour)

## 0x1b9ea (1.0.3 0x17848): erase a 4-bit blip from the saved panel (row bytes host +0xe4).
func erase_radar_blip_depth4(x: int, y: int) -> void:
	assert(memory != null, "erase_radar_blip_depth4 requires original global memory")
	var host: int = memory.read_u32(0x207fc)
	var col: Array = _blip_column4(x)
	var at: Array = _blip_addresses(col[0], y)
	var cells: Array
	if col[1] != 0:
		cells = [[0, 0], [0, 1], [1, 0], [1, 1], [2, 0], [2, 1], [3, 0], [3, 1]]
	else:
		cells = [[0, 0], [1, -1], [1, 0], [1, 1], [2, -1], [2, 0], [2, 1], [3, 0]]
	for cell in cells:
		memory.write_u8(at[0] + cell[0] * memory.read_s16(host + 0x10) + cell[1], memory.read_u8(at[1] + cell[0] * memory.read_s16(host + 0xe4) + cell[1]))

## 0x1bbb6 / 0x1bbe2 (1.0.3 0x17a14 / 0x17a40): a 1-bit blip is the host rectangle call +0x2ae(x, y, x + 1,
## y + 1) (0x18 below), for erase and draw alike.
func erase_radar_blip_bw(x: int, y: int) -> void:
	assert(memory != null, "erase_radar_blip_bw requires original global memory")
	_blip_rect_bw(x, y)

func draw_radar_blip_bw(x: int, y: int) -> void:
	assert(memory != null, "draw_radar_blip_bw requires original global memory")
	_blip_rect_bw(x, y)

## 0x5408 (1.0.3's instantiate_object_from_desc 0x4670; the controller at the object's +0): alloc_object_from_desc
## (0x532e), then the class factory with (obj, key, 0) (0x18 below) when the descriptor has a key. Returns the
## object.
func instantiate_object_from_desc(desc: int) -> int:
	assert(memory != null, "instantiate_object_from_desc requires original global memory")
	var obj: int = alloc_object_from_desc(desc)
	if memory.read_u8(desc + 1) != 0:
		_leyte_call_class_factory(obj, desc, 0, 0x18)
	return obj

## 0x3f46 (1.0.3 0x3446): variant B (records passed directly).
func fill_span_rows_4bit_b(new_rows: int, old_rows: int, rows: int, screen: int, skip: int) -> void:
	assert(memory != null, "fill_span_rows_4bit_b requires original global memory")
	_fill_span_rows_4bit(new_rows, old_rows, rows, screen, skip, 0x3ece, 0x3fd6, false)

## 0x1d56e (1.0.3 0x193ee, identical): parachute_template 0x2cd8c; factory 0x1d516.
func register_parachute_template(tmpl: int, def_key: int) -> int:
	assert(memory != null, "register_parachute_template requires original global memory")
	return _register_template(0x2cd8c, tmpl, def_key, 0x1d516)
```
