extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const NativeState := preload("res://tests/helpers/native_state.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const OriginalOverlay := preload("res://original/port/pacific_conflict.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")
const WorldObjectView := preload("res://native/state/world_object_view.gd")
const ControllerView := preload("res://native/state/controller_view.gd")

const OBJ := 0x30000
const CTL := 0x31000

## The typed world object and controller views against the record bytes, and the Native consumers
## built on them against the Original port.
## Views: every typed field equals the port's read at its offset and width over patterned bytes;
## setters wrap (0x1_0000_0005, -1, -0x8000, 0x10000) and store big-endian; the overlapping fields
## agree (word +6 / bytes +6 +7, long +0x2c / word +0x2e, long +0x30 / words +0x30 +0x32, word
## +0x684 / byte +0x684); vertex and face addresses follow the pointers.
## Consumers: update_object_camera_metric, copy_object_vertex_buffer, spin_object_heading and
## face_camera_heading in both overlays from the same bytes (positions and camera at the 32-bit
## extremes, heading words across the sign boundary): same results, same memory, byte for byte.
func run() -> bool:
	var ok := true
	var m = Memory68k.new(0x40000)
	for k in 0x800:
		m.write_u8(OBJ + k, (k * 151 + 7) & 0xff)
		m.write_u8(CTL + k, (k * 89 + 3) & 0xff)
	var o := WorldObjectView.new(m, OBJ)
	var c := ControllerView.new(m, CTL)
	var reads := [
		[o.kind(), m.read_u8(OBJ)], [o.child_index(), m.read_u8(OBJ + 1)], [o.slot(), m.read_u8(OBJ + 2)],
		[o.refcount(), m.read_u8(OBJ + 3)], [o.flags5(), m.read_u8(OBJ + 5)], [o.flags6(), m.read_u16(OBJ + 6)],
		[o.list_bits(), m.read_u8(OBJ + 7)], [o.controller(), m.read_u32(OBJ + 8)],
		[o.active_range(), m.read_s32(OBJ + 0xc)], [o.camera_metric(), m.read_s32(OBJ + 0x10)],
		[o.extent(), m.read_s32(OBJ + 0x14)], [o.x(), m.read_s32(OBJ + 0x18)], [o.y(), m.read_s32(OBJ + 0x1c)],
		[o.z(), m.read_s32(OBJ + 0x20)], [o.parent(), m.read_u32(OBJ + 0x24)], [o.support_list(), m.read_u32(OBJ + 0x2c)],
		[o.child_template_count(), m.read_s16(OBJ + 0x2e)], [o.child_table(), m.read_u32(OBJ + 0x30)],
		[o.distance_band(), m.read_s16(OBJ + 0x30)], [o.projection_shift(), m.read_s16(OBJ + 0x32)],
		[o.scale_shift(), m.read_s16(OBJ + 0x34)], [o.vertex_count(), m.read_s16(OBJ + 0x36)],
		[o.part_count(), m.read_s16(OBJ + 0x38)], [o.face_count(), m.read_s16(OBJ + 0x3a)],
		[o.last_active_frame(), m.read_s32(OBJ + 0x3c)], [o.vertices(), m.read_u32(OBJ + 0x44)],
		[o.faces(), m.read_u32(OBJ + 0x48)], [o.vertices2(), m.read_u32(OBJ + 0x4c)],
		[c.owner(), m.read_u32(CTL)], [c.pitch(), m.read_s16(CTL + 0x20)], [c.heading(), m.read_s16(CTL + 0x22)],
		[c.bank(), m.read_s16(CTL + 0x24)], [c.ground_height(), m.read_s32(CTL + 0x2e)],
		[c.pose_ring_index(), m.read_s16(CTL + 0x612)], [c.pose_buffer(), m.read_u32(CTL + 0x616)],
		[c.angle_of_attack(), m.read_s16(CTL + 0x626)], [c.movement(), m.read_s32(CTL + 0x62a)],
		[c.bombing_stage(), m.read_s8(CTL + 0x675)], [c.gear_position(), m.read_s8(CTL + 0x679)],
		[c.gear_bits(), m.read_u8(CTL + 0x67a)], [c.flaps(), m.read_s8(CTL + 0x67b)], [c.bombs(), m.read_s8(CTL + 0x67c)],
		[c.damage(), m.read_u16(CTL + 0x684)], [c.damage_previous(), m.read_u16(CTL + 0x686)],
		[c.damage_timer(), m.read_u16(CTL + 0x688)], [c.ai_target(), m.read_u32(CTL + 0x692)],
		[c.release_frame(), m.read_s32(CTL + 0x6ac)]]
	var bad: Array = []
	for i in reads.size():
		if reads[i][0] != reads[i][1]:
			bad.append(i)
	ok = Assertions.assert_equal(bad, [], "every typed field reads as the port reads it") and ok
	# Overlaps.
	ok = Assertions.assert_equal(o.flags6(), (m.read_u8(OBJ + 6) << 8) | o.list_bits(), "word +6 holds bytes +6 +7") and ok
	ok = Assertions.assert_equal(o.child_template_count() & 0xffff, o.support_list() & 0xffff, "word +0x2e is long +0x2c's low half") and ok
	ok = Assertions.assert_equal([o.distance_band() & 0xffff, o.projection_shift() & 0xffff],
			[o.child_table() >> 16, o.child_table() & 0xffff], "long +0x30 holds words +0x30 +0x32") and ok
	ok = Assertions.assert_equal(c.damage() >> 8, c.raw_u8(0x684), "byte +0x684 is word +0x684's bits 8..15") and ok
	# Setters wrap to the width, big-endian.
	o.set_x(0x100000005)
	o.set_y(-1)
	o.set_z(-0x80000000)
	ok = Assertions.assert_equal([o.x(), o.y(), o.z(), m.read_u8(OBJ + 0x18), m.read_u8(OBJ + 0x1b), m.read_u8(OBJ + 0x20)],
			[5, -1, -0x80000000, 0, 5, 0x80], "position longs wrap and store big-endian") and ok
	c.set_heading(0x10000 + 0x7fff)
	c.set_pitch(0x8000)
	c.set_bank(-1)
	ok = Assertions.assert_equal([c.heading(), c.pitch(), c.bank(), m.read_u16(CTL + 0x24)], [0x7fff, -0x8000, -1, 0xffff],
			"attitude words wrap to 16 bits, signed") and ok
	o.set_refcount(0x1ff)
	o.set_camera_metric(0xffffffff)
	ok = Assertions.assert_equal([o.refcount(), o.camera_metric()], [0xff, -1], "byte and long setters wrap") and ok
	# Array indexing.
	m.write_u32(OBJ + 0x44, 0x32000)
	m.write_u32(OBJ + 0x48, 0x33000)
	m.write_u32(OBJ + 0x4c, 0x34000)
	ok = Assertions.assert_equal([o.vertex_address(3), o.face_address(5), o.vertex2_address(0)],
			[0x32000 + 36, 0x33000 + 0xa0, 0x34000], "vertex and face addresses follow the pointers") and ok
	return _consumers() and ok

func _setup(m, i: int) -> void:
	var values := [0, 1, -1, 0x7fffffff, -0x80000000, 0x12345, -0x54321, 0x40000000]
	for k in 0x400:
		m.write_u8(OBJ + k, (k * 151 + i * 13) & 0xff)
		m.write_u8(CTL + k, (k * 89 + i * 7) & 0xff)
		m.write_u8(0x32000 + k, (k * 31 + i) & 0xff)
	for axis in 3:
		m.write_u32(OBJ + 0x18 + 4 * axis, values[(i + axis) % values.size()])
		m.write_u32(0x1d830 + 4 * axis, values[(i * 3 + axis + 1) % values.size()])
	m.write_u16(OBJ + 0x36, [3, 0, -2, 20, 1][i % 5])
	m.write_u16(OBJ + 0x3a, [4, 0, 1, 30, -1][i % 5])
	m.write_u32(OBJ + 0x44, 0x32000)
	m.write_u32(OBJ + 0x4c, 0x32200)
	m.write_u16(CTL + 0x22, [0, 0x3fc0, 0x7fff, 0x8000, 0xffff, 0x1234][i % 6])
	m.write_u16(0x1d80e, [0, 1, 0x2000, 0x8000, 0xffff, 0x3fff][i % 6])

func _consumers() -> bool:
	var ok := true
	var bad: Array = []
	for i in 40:
		var mo = Memory68k.new(0x40000)
		var mn = Memory68k.new(0x40000)
		_setup(mo, i)
		_setup(mn, i)
		var a = OriginalOverlay.new(mo)
		var b = NativeOverlay.new(mn)
		NativeState.seed_and_poison(b, mn)
		var ra := [a.update_object_camera_metric(OBJ), a.face_camera_heading(CTL)]
		a.copy_object_vertex_buffer(OBJ)
		a.spin_object_heading(CTL)
		var rb := [b.update_object_camera_metric(OBJ), b.face_camera_heading(CTL)]
		b.copy_object_vertex_buffer(OBJ)
		b.spin_object_heading(CTL)
		if ra != rb or NativeState.compare(b, mo, mn) != "":
			bad.append(i)
	ok = Assertions.assert_equal(bad, [], "Native view consumers match the Original byte for byte") and ok
	return ok
