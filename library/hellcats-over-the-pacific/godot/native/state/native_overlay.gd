extends "res://native/state/routed_port.gd"

## The Native edition's overlay (Pacific Conflict): the Original port with pieces of the game's state
## typed instead of read from or kept in the Mac memory (Phase 7):
## - the random-number generator, its 55-long ring and cursor (0x2600c..0x260e7, cursor at 0x26008),
##   starting as the overlay image has it, read once, never written back;
## - the trigonometry tables sin_fixed14 / cos_fixed14 / acos_fixed14 / atan_fixed14 read (sine and
##   cosine 256 words, tangent 128 longs), copied once from the image.
## - the altitude curve table_lerp_1b2c0 reads (9 longs at 0x1b2c0), copied once.
## Same arithmetic, same results.

const RING_START := 0x2600c
const RING_END := 0x260e8
const RING_LONGS := 55

var _ring := PackedInt64Array()
var _cursor: int = 0           ## an address, kept as the original keeps it

func _ensure_ring() -> void:
	if _ring.size() == RING_LONGS:
		return
	_ring.resize(RING_LONGS)
	for j in RING_LONGS:
		_ring[j] = _routed.read_u32(RING_START + 4 * j)
	_cursor = _routed.read_u32(DAT_00026008)

func _read(address: int) -> int:
	if address >= RING_START and address < RING_END and (address - RING_START) % 4 == 0:
		return _ring[(address - RING_START) / 4]
	return _routed.read_u32(address)        # only a cursor the program never set points elsewhere

## 0x1150a: ring[0] = seed, ring[j] = ring[j - 1] * 0x1df5e0d + 1, cursor at the last long, then 10000
## outputs discarded.
func overlay_rng_init(seed: int) -> void:
	_ensure_ring()
	_ring[0] = C68kScript.u32(seed)
	for j in range(1, RING_LONGS):
		_ring[j] = C68kScript.u32(overlay_rng_mul_mix(0x1df5e0d, _ring[j - 1]) + 1)
	_cursor = RING_START + 4 * (RING_LONGS - 1)
	for i in 10000:
		overlay_rng_next()

## 0x11564: the lagged-Fibonacci step (taps 23 and 54 longs on, the ring wrapping at 0x260e8).
func overlay_rng_next() -> int:
	_ensure_ring()
	var slot: int = _cursor + 4
	if not (RING_END > slot):
		slot -= 0xdc
	_cursor = slot
	var tap_a: int = slot + 0x5c
	if not (RING_END > tap_a):
		tap_a -= 0xdc
	var tap_b: int = slot + 0xd8
	if not (RING_END > tap_b):
		tap_b -= 0xdc
	var sum: int = C68kScript.u32(_read(tap_a) + _read(tap_b))
	_ring[(slot - RING_START) / 4] = sum
	return sum >> 0x10

## The ring and cursor as the Mac memory would hold them (for traces): the cursor long, then the ring.
func rng_bytes() -> PackedByteArray:
	_ensure_ring()
	var out := PackedByteArray()
	for v in [_cursor] + Array(_ring):
		out.append_array(PackedByteArray([(v >> 24) & 0xff, (v >> 16) & 0xff, (v >> 8) & 0xff, v & 0xff]))
	return out

# ---- trigonometry tables ----------------------------------------------------------------------

const SIN_TABLE := 0x1ab6c
const COS_TABLE := 0x1ad6c
const TAN_TABLE := 0x1af6c

var _sin := PackedInt32Array()
var _cos := PackedInt32Array()
var _tan := PackedInt32Array()

func _ensure_tables() -> void:
	if not _sin.is_empty():
		return
	_sin.resize(256)
	_cos.resize(256)
	_tan.resize(128)
	for i in 256:
		_sin[i] = _routed.read_s16(SIN_TABLE + 2 * i)
		_cos[i] = _routed.read_s16(COS_TABLE + 2 * i)
	for i in 128:
		_tan[i] = _routed.read_s32(TAN_TABLE + 4 * i)

## A table word; an index outside the table reads the image's word there, as the original's unmasked
## index does.
func _word(table: PackedInt32Array, base: int, index: int) -> int:
	if index >= 0 and index < table.size():
		return table[index]
	return _routed.read_s16(C68kScript.u32(base + index * 2))

## sin_fixed14 / cos_fixed14's shared code on one table (ID02 0x1d56 / 0x1dd8 in the overlay's copy).
func _interpolate(table: PackedInt32Array, base: int, angle: int) -> int:
	angle = C68kScript.s32(angle)
	var src: int = C68kScript.s32(angle + 0x3f) if angle < 0 else angle
	var index: int = C68kScript.asr32(src, 6)
	var here: int = _word(table, base, index)
	var delta: int = C68kScript.s32((_word(table, base, (index + 1) & 0xff) - here) * C68kScript.s32(-C68kScript.s32((src & 0xffffffc0) - angle)))
	if delta < 0:
		delta = C68kScript.s32(delta + 0x3f)
	return C68kScript.s16(here + C68kScript.s16(C68kScript.asr32(delta, 6)))

func sin_fixed14(angle: int) -> int:
	_ensure_tables()
	return _interpolate(_sin, SIN_TABLE, angle)

func cos_fixed14(angle: int) -> int:
	_ensure_tables()
	return _interpolate(_cos, COS_TABLE, angle)

func acos_fixed14(x: int) -> int:
	_ensure_tables()
	x = clampi(C68kScript.s32(x), -0x1000, 0x1000)
	var lo: int = 0
	var hi: int = 0x80
	while true:
		var sum: int = hi + lo
		if sum < 0:
			sum += 1
		var mid: int = C68kScript.s16(sum >> 1)
		if x > _cos[mid]:
			hi = mid
		else:
			lo = mid
		if not (hi - 1 > lo):
			break
	var c0: int = _cos[lo]
	var c1: int = _cos[lo + 1]
	var q: int = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(C68kScript.s32(-(x - c0)), 6)), c0 - c1)
	return C68kScript.s16((lo << 6) + q)

func atan_fixed14(ratio: int) -> int:
	_ensure_tables()
	ratio = C68kScript.s32(ratio)
	var lo: int = 0
	var hi: int = 0x7f
	while true:
		var sum: int = hi + lo
		if sum < 0:
			sum += 1
		var mid: int = C68kScript.s16(sum >> 1)
		if ratio < _tan[mid]:
			hi = mid
		else:
			lo = mid
		if not (hi - 1 > lo):
			break
	var t0: int = _tan[lo]
	var t1: int = _tan[lo + 1]
	var q: int = C68kScript.divs_trunc(C68kScript.s32(C68kScript.lsl32(C68kScript.s32(t0 - ratio), 6)), C68kScript.s32(t0 - t1))
	return C68kScript.u16(((lo - 0x3f) << 6) + q) & 0x3fff

# ---- altitude curve ---------------------------------------------------------------------------

const ALTITUDE_TABLE := 0x1b2c0
var _altitude := PackedInt64Array()

## A long of the curve; a band outside it (a negative altitude) reads the image there, as the original.
func _altitude_long(index: int) -> int:
	if _altitude.is_empty():
		_altitude.resize(9)
		for i in 9:
			_altitude[i] = _routed.read_u32(ALTITUDE_TABLE + 4 * i)
	if index >= 0 and index < 9:
		return _altitude[index]
	return _routed.read_u32(C68kScript.u32(ALTITUDE_TABLE + 4 * index))

## 0x1b2c0's curve at an altitude (16.16): the band's long plus (next - this) * fraction >> 16, the
## band's low word as the base (move.w 2(a0)), bands above 7 clamped.
func table_lerp_1b2c0(altitude: int) -> int:
	var band: int = mini(C68kScript.asr32(altitude, 16), 7)
	var here: int = _altitude_long(band)
	var delta: int = C68kScript.s32(_altitude_long(band + 1) - here)
	var step: int = C68kScript.asr32(C68kScript.s32(delta * (C68kScript.u32(altitude) & 0xffff)), 16)
	return C68kScript.s16(step + (here & 0xffff))

# ---- world objects and controllers through typed views (Stage D, first consumers) -------------

const WorldObjectView := preload("res://native/state/world_object_view.gd")
const ControllerView := preload("res://native/state/controller_view.gd")
const SceneView := preload("res://native/state/scene_view.gd")

## The scene view on this overlay's camera angle storage.
func _scene() -> SceneView:
	return SceneView.new(_routed, camera_angles(), camera_position(), view_state())

## 0x3b98 on the object and scene views: |position - camera| per axis (32-bit), metric = max +
## (mid + min) / 4 at +0x10, returned as the long read back.
func update_object_camera_metric(obj: int) -> int:
	var o := WorldObjectView.new(_routed, obj)
	var scene := _scene()
	var d1: int = abs_int(C68kScript.s32(o.x() - scene.camera_x()))
	var d2: int = abs_int(C68kScript.s32(o.y() - scene.camera_y()))
	var d3: int = abs_int(C68kScript.s32(o.z() - scene.camera_z()))
	var dmax: int = d1
	if d1 < d2:
		dmax = d2
		d2 = d1
	d1 = d3
	if dmax < d3:
		d1 = dmax
		dmax = d3
	o.set_camera_metric(dmax + C68kScript.asr32(d1 + d2, 2))
	return o.camera_metric()

## 0x1c04 on the object view: vertex_count + face_count 12-byte vertices from +0x44 to +0x4c, both
## pointers read again for every long.
func copy_object_vertex_buffer(obj: int) -> void:
	var o := WorldObjectView.new(_routed, obj)
	var count: int = o.vertex_count() + o.face_count()
	if count <= 0:
		return
	var off: int = 0
	while true:
		for k in [0, 4, 8]:
			_routed.write_u32(o.vertices2() + off + k, _routed.read_u32(o.vertices() + off + k))
		off += WorldObjectView.STRIDE_VERTEX
		if not (count * WorldObjectView.STRIDE_VERTEX > off):
			break

## 0x114b4 on the controller view: heading += 0x40, kept in 14 bits.
func spin_object_heading(ctl: int) -> void:
	var c := ControllerView.new(_routed, ctl)
	c.set_heading((c.heading() + 0x40) & 0x3fff)

## 0x11ae6 on the controller and scene views: heading = -neg_camera_heading & 0x3fff; returns 0.
func face_camera_heading(ctl: int) -> int:
	ControllerView.new(_routed, ctl).set_heading(-_scene().neg_camera_heading() & 0x3fff)
	return 0

## 0x3da8 on the object and scene views: the band word +0x30 against band_limits (index b - 1
## read when stepping down, as the original): up while limit[b] <= d, down from b - 2 while
## d < limit[b] then b + 1; returns 0 when the band is kept (word untouched), else 1.
func update_object_distance_band(obj: int, dist: int) -> int:
	var o := WorldObjectView.new(_routed, obj)
	var scene := _scene()
	var band: int = o.distance_band()
	dist = C68kScript.s32(dist)
	if dist < scene.band_limit(band):
		if scene.band_limit(band - 1) <= dist:
			return 0
		band = C68kScript.s16(band - 2)
		while dist < scene.band_limit(band):
			band = C68kScript.s16(band - 1)
		band = C68kScript.s16(band + 1)
	else:
		band = C68kScript.s16(band + 1)
		while scene.band_limit(band) <= dist:
			band = C68kScript.s16(band + 1)
	o.set_distance_band(band)
	return 1

## 0x53f8 on the views: the near list sorted in place (count long, its low word counted down). In
## range and not hidden (list bit 1): a visible shape (kind 2, long +4 bit 0x40 clear) gets its
## detail shift (+0x32) and, without long +4 bit 2, a camera-relative view point, and joins the
## visible list. Out of range: kept within 0x19c7f unless hidden, or a shape active in the last
## 0x4b frames; otherwise retired (shape with +0x3c set), deactivated (the Original's functions,
## 0x30 below) and moved to the far list. Long +4 is unresolved (raw).
func collect_near_objects(list: int, count_ptr: int) -> void:
	var entry_a7: int = a7
	var scene := _scene()
	var point: int = C68kScript.u32(scene.view_point_address(scene.view_point_count()))
	var keep: int = list
	var read: int = list
	var left: int = _routed.read_u16(count_ptr + 2)
	while left != 0:
		left -= 1
		var obj: int = _routed.read_u32(read)
		read += 4
		var o := WorldObjectView.new(_routed, obj)
		var hidden: bool = (o.list_bits() & 2) != 0
		var kept: bool = true
		if o.camera_metric() > o.active_range() or hidden:
			if C68kScript.s32(o.camera_metric() - o.active_range()) > 0x19c7f or hidden:
				if not (o.kind() == 2 and C68kScript.s32(scene.frame_counter() - o.last_active_frame()) <= 0x4b):
					a7 = entry_a7 - 0x30
					if o.kind() == 2 and o.last_active_frame() != 0:
						retire_entity(obj)
					deactivate_object(obj)
					a7 = entry_a7
					scene.append_far(obj)
					kept = false
		elif o.kind() == 2 and (o.flags_long() & 0x40) == 0:
			var metric: int = o.camera_metric()
			var size: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(C68kScript.asr32(metric, 2) + metric + o.extent()), o.scale_shift()))
			var detail: int = 0
			while (C68kScript.u32(size) & 0xffff8000) != 0:
				detail = C68kScript.s16(detail + 1)
				size = C68kScript.asr32(size, 1)
			o.set_projection_shift(detail)
			if (o.flags_long() & 4) == 0:
				_routed.write_u32(point, C68kScript.asr32(C68kScript.s32(o.x() - scene.camera_x()), detail))
				_routed.write_u32(point + 4, C68kScript.asr32(C68kScript.s32(o.y() - scene.camera_y()), detail))
				_routed.write_u32(point + 8, C68kScript.asr32(C68kScript.s32(o.z() - scene.camera_z()), detail))
				point += SceneView.VIEW_POINT_STRIDE
			scene.append_visible(obj)
		if kept:
			_routed.write_u32(keep, obj)
			keep += 4
	scene.set_view_point_count(C68kScript.divs_trunc(C68kScript.s32(point - SceneView.VIEW_POINTS), SceneView.VIEW_POINT_STRIDE))
	_routed.write_u32(count_ptr, C68kScript.asr32(C68kScript.s32(keep - list), 2))

## overlay_frame_step part 4 on the views: object_pool insertion-sorted by descending camera metric
## (stable). With carried pairs, every metric updates first, each rider's metric is saved at +0x28
## (unresolved field, raw) and biased by its carrier's extent toward the camera height side (the
## carrier controller's word +0x54, unresolved, raw), the sort runs on the biased keys, the saved
## metrics come back and the pair count is cleared.
func _sort_far_objects(body: int) -> void:
	var entry: int = a7
	var scene := _scene()
	var count: int = scene.object_count()
	var pairs: int = scene.carried_pair_count()
	a7 = body - 8
	if pairs != 0:
		for i in range(maxi(count, 0)):
			update_object_camera_metric(scene.pool_object(i))
		for k in range(pairs):
			var carrier := WorldObjectView.new(_routed, scene.carrier(k))
			var rider := WorldObjectView.new(_routed, scene.rider(k))
			rider.set_saved_min_start(rider.camera_metric())
			var level: int = C68kScript.s16(ControllerView.new(_routed, carrier.controller()).raw_u16(0x54))
			var add: bool
			if level < scene.camera_y():
				add = level > rider.y()
				if not add:
					rider.set_camera_metric(rider.camera_metric() - carrier.extent())
			else:
				add = level <= rider.y()
			if add:
				rider.set_camera_metric(rider.camera_metric() + carrier.extent())
	for i in range(maxi(count, 0)):
		var obj: int = scene.pool_object(i)
		var key: int
		if pairs != 0:
			key = WorldObjectView.new(_routed, obj).camera_metric()
		else:
			key = C68kScript.s32(update_object_camera_metric(obj))
		var j: int = i - 1
		while j >= 0:
			var other: int = scene.pool_object(j)
			if key <= WorldObjectView.new(_routed, other).camera_metric():
				break
			scene.set_pool_object(j + 1, other)
			j -= 1
		scene.set_pool_object(j + 1, obj)
	if pairs != 0:
		for k in range(pairs):
			var rider := WorldObjectView.new(_routed, scene.rider(k))
			rider.set_camera_metric(rider.saved_min_start())
		scene.set_carried_pair_count(0)
	a7 = entry

## 0x555a on the views: the object pool's pass, as collect_near_objects for range, view points and
## the visible list (no kind test; long +4 bit 0x40 alone hides, bit 2 the hidden bit), plus the
## overlap list (long +4 bit 0x8000) and the broad-phase collisions (_far_collision, the Original's
## tests, 0xc below): an active object (long +4 bits 0x2800; word +6 loses 0x800) against the earlier
## kept, unhidden objects it reaches, scanning back until one's running minimum (+0x28) passes its
## reach; a passive one against the active objects the same way. Each kept object stores the running
## minimum of metric - extent at +0x28. The active list lives in the caller's frame (a6 - 0x370).
func collect_far_objects(list: int, count_ptr: int) -> void:
	assert(a7 != 0, "collect_far_objects needs a7 (its frame holds the active list)")
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x3a8
	var active: int = entry_a7 - 4 - 0x370
	var scene := _scene()
	var point: int = C68kScript.u32(scene.view_point_address(scene.view_point_count()))
	var keep: int = list
	var read: int = list
	var left: int = _routed.read_u16(count_ptr + 2)
	var min_start: int = 0x7fffffff
	var active_count: int = 0
	while left != 0:
		left -= 1
		var obj: int = _routed.read_u32(read)
		read += 4
		var o := WorldObjectView.new(_routed, obj)
		var flags: int = o.flags_long()
		var normal: bool = true
		if o.camera_metric() > o.active_range() or (flags & 2) != 0:
			if C68kScript.s32(o.camera_metric() - o.active_range()) > 0x19c7f or (flags & 2) != 0:
				if C68kScript.s32(scene.frame_counter() - o.last_active_frame()) <= 0x4b:
					normal = false
				else:
					a7 = body - 8
					if o.last_active_frame() != 0:
						retire_entity(obj)
					deactivate_object(obj)
					a7 = entry_a7
					scene.append_far(obj)
					continue
		elif (flags & 0x40) == 0:
			var metric: int = o.camera_metric()
			var size: int = C68kScript.s32(C68kScript.lsl32(C68kScript.s32(C68kScript.asr32(metric, 2) + metric + o.extent()), o.scale_shift()))
			var detail: int = 0
			while (C68kScript.u32(size) & 0xffff8000) != 0:
				detail = C68kScript.s16(detail + 1)
				size = C68kScript.asr32(size, 1)
			o.set_projection_shift(detail)
			if (flags & 4) == 0:
				_routed.write_u32(point, C68kScript.asr32(C68kScript.s32(o.x() - scene.camera_x()), detail))
				_routed.write_u32(point + 4, C68kScript.asr32(C68kScript.s32(o.y() - scene.camera_y()), detail))
				_routed.write_u32(point + 8, C68kScript.asr32(C68kScript.s32(o.z() - scene.camera_z()), detail))
				point += SceneView.VIEW_POINT_STRIDE
			scene.append_visible(obj)
		if normal:
			if (flags & 0x8000) != 0:
				scene.append_overlap(obj)
			var reach: int = C68kScript.s32(o.camera_metric() + o.extent())
			if (flags & 0x2800) != 0:
				o.set_flags6(o.flags6() & 0xf7ff)
				if min_start < reach:
					var scan: int = keep
					var n: int = C68kScript.asr32(keep - list, 2) & 0xffff
					while n != 0:
						n -= 1
						scan -= 4
						var other := WorldObjectView.new(_routed, _routed.read_u32(scan))
						if (other.list_bits() & 2) != 0:
							continue
						if reach > C68kScript.s32(other.camera_metric() - other.extent()):
							a7 = body - 0xc
							_far_collision((other.flags5() & 0x10) != 0, (flags & 0x40) != 0, obj, other.address)
							a7 = entry_a7
						elif reach < other.saved_min_start():
							break
				_routed.write_u32(active + active_count * 4, obj)
				active_count += 1
			elif min_start < reach:
				var n: int = active_count
				var scan: int = active + active_count * 4
				while n != 0:
					n -= 1
					scan -= 4
					var other := WorldObjectView.new(_routed, _routed.read_u32(scan))
					if reach > C68kScript.s32(other.camera_metric() - other.extent()):
						a7 = body - 0xc
						_far_collision((flags & 0x100000) != 0, (other.list_bits() & 0x40) != 0, other.address, obj)
						a7 = entry_a7
					elif reach < other.saved_min_start():
						break
			var start: int = C68kScript.s32(o.camera_metric() - o.extent())
			if min_start > start:
				min_start = start
			o.set_saved_min_start(min_start)
		_routed.write_u32(keep, obj)
		keep += 4
	scene.set_view_point_count(C68kScript.divs_trunc(C68kScript.s32(point - SceneView.VIEW_POINTS), SceneView.VIEW_POINT_STRIDE))
	_routed.write_u32(count_ptr, C68kScript.asr32(C68kScript.s32(keep - list), 2))

## 0x58ec on the views: the view points go to camera space (the Original's
## transform_int3_vecs_asr_by_1d744, 0x44 below); two passes (near entries up to visible_first_pass,
## then the rest, after which visible_first_pass = kept) keep objects without a view point (byte +7
## bit 2), objects the camera is inside (metric - metric / 4 < extent; long +4 |= 8), and objects in
## front (z > 0) whose max(|x|, |y|) - r (r = extent asr projection shift, words), times 1.5, is
## under z + r. visible_count = kept, view_point_count = 0.
func build_visible_object_list() -> void:
	var entry_a7: int = a7
	var body: int = entry_a7 - 0x30
	var scene := _scene()
	a7 = body - 0x14
	transform_int3_vecs_asr_by_1d744(_routed.read_u32(SceneView.VIEW_POINT_COUNT), SceneView.VIEW_POINTS, SceneView.VIEW_POINTS, 0)
	a7 = entry_a7
	var keep: int = SceneView.VISIBLE_LIST
	var read: int = SceneView.VISIBLE_LIST
	var point: int = SceneView.VIEW_POINTS
	for pass_index in 2:
		var left: int
		if pass_index == 0:
			left = scene.visible_first_pass()
		else:
			left = (scene.visible_count_word() - scene.visible_first_pass()) & 0xffff
			scene.set_visible_first_pass(C68kScript.asr32(keep - SceneView.VISIBLE_LIST, 2))
		while left != 0:
			left -= 1
			var o := WorldObjectView.new(_routed, _routed.read_u32(read))
			read += 4
			if (o.list_bits() & 4) != 0:
				_routed.write_u32(keep, o.address)
				keep += 4
				continue
			var metric: int = o.camera_metric()
			var inner: int = C68kScript.s32(-C68kScript.s32(C68kScript.asr32(metric, 2) - metric))
			if inner < o.extent():
				_routed.write_u32(keep, o.address)
				keep += 4
				o.raw_write_u32(4, o.flags_long() | 8)
			elif _routed.read_s32(point + 8) > 0:
				a7 = body - 8
				var ax: int = C68kScript.s16(abs_int(_routed.read_s32(point)))
				var ay: int = C68kScript.s16(abs_int(_routed.read_s32(point + 4)))
				a7 = entry_a7
				var r: int = C68kScript.s16(C68kScript.asr32(o.extent(), o.projection_shift() & 0xffff))
				var edge: int = C68kScript.s16((ax if ay < ax else ay) - r)
				var reach: int = (C68kScript.s16(edge) >> 1) + edge
				if C68kScript.s32(r + _routed.read_s32(point + 8)) > reach:
					_routed.write_u32(keep, o.address)
					keep += 4
			point += SceneView.VIEW_POINT_STRIDE
	scene.set_visible_count(C68kScript.asr32(keep - SceneView.VISIBLE_LIST, 2))
	scene.set_view_point_count(0)

# ---- aircraft AI control helpers on the controller view ---------------------------------------

## 0x10c2c: error = target - word(airspeed (+0x62a) asr 2); acc = error + remainder + 32 * (error -
## previous error) (words); remainder = acc % 256 and throttle += acc / 256 (toward zero), clamped
## 0..9; acc < -0x438 lowers a raised gear (0 -> 1), acc > 0x1c2 opens up fully.
func adjust_ai_throttle_vs_speed(ctl: int, target_speed: int) -> void:
	var c := ControllerView.new(_routed, ctl)
	var err: int = C68kScript.s16(C68kScript.s16(target_speed) - C68kScript.asr32(c.movement(), 2))
	var delta: int = C68kScript.s16(err - c.speed_error_previous())
	c.set_speed_error_previous(err)
	var acc: int = C68kScript.s16(err + c.throttle_remainder() + C68kScript.s16(delta << 5))
	var rounded: int = C68kScript.s16(acc + 0xff) if acc < 0 else acc
	c.set_throttle_remainder(acc - C68kScript.s16(rounded & 0xff00))
	c.set_ai_throttle(c.ai_throttle() + (rounded >> 8))
	if c.ai_throttle() < 0:
		c.set_ai_throttle(0)
	elif c.ai_throttle() > 9:
		c.set_ai_throttle(9)
	if acc < -0x438:
		if c.gear_position() == 0:
			c.set_gear_position(1)
	elif acc > 0x1c2:
		max_ai_throttle_maybe_invert_679(ctl)

## 0x10cb2: throttle = 9; a gear moving down (> 0) more than 100 above the ground (owner y - +0x2e)
## goes back up (negated).
func max_ai_throttle_maybe_invert_679(ctl: int) -> void:
	var c := ControllerView.new(_routed, ctl)
	c.set_ai_throttle(9)
	var gear: int = c.gear_position()
	if gear > 0 and C68kScript.s32(WorldObjectView.new(_routed, c.owner()).y() - c.ground_height()) > 100:
		c.set_gear_position(-gear)

## 0x10cd8: a positive bias byte +0x67b is negated.
func invert_ai_bias_67b_if_positive(ctl: int) -> void:
	var c := ControllerView.new(_routed, ctl)
	if c.flaps() > 0:
		c.set_flaps(-c.flaps())

## 0x10558: aileron / elevator / rudder clamped to +-0x140 / +-0x200 / +-0x40 into the 4-entry
## rings; each running sum gains the new value and loses the slot after it; outputs = sum / 4
## (toward zero). Nothing when byte +0x685 bit 0 is set: the damage word's bit 0 (byte +0x685 is
## its low byte).
func enqueue_control_inputs_avg4(ctl: int, rudder: int, elevator: int, aileron: int) -> void:
	var c := ControllerView.new(_routed, ctl)
	if (c.damage() & 1) != 0:
		return
	var values := [C68kScript.s16(aileron), C68kScript.s16(elevator), C68kScript.s16(rudder)]
	var limits := [0x140, 0x200, 0x40]
	for ch in 3:
		if abs_int(values[ch]) > limits[ch]:
			values[ch] = -limits[ch] if values[ch] < 0 else limits[ch]
	var ring: int = c.control_ring_index()
	for ch in 3:
		c.set_control_slot(ch, ring, values[ch])
		c.set_control_sum(ch, c.control_sum(ch) + values[ch])
	ring = C68kScript.s16(ring + 1)
	if ring == 4:
		ring = 0
	for ch in 3:
		c.set_control_sum(ch, c.control_sum(ch) - c.control_slot(ch, ring))
		var sum: int = c.control_sum(ch)
		if sum < 0:
			sum += 3
		c.set_control_output(ch, sum >> 2)
	c.set_control_ring_index(ring)

# ---- aircraft AI pursuit helpers on the controller view ---------------------------------------

## 0x10b84: the take-off climb. Flaps 0 -> 1; more than 200 above the ground a lowering gear (> 0)
## is raised (negated); throttle 9; pitch error = fold(0x2c0 - pitch) (0x14 below), elevator =
## error + error asr 1 - previous (+0x61a, then = error), clamped to +-0x200;
## enqueue_control_inputs_avg4(ctl, 0, elevator, 0) (0x20 below). Above 2000: flaps negated, 1.
func ai_bootstrap_climb_phase(ctl: int) -> int:
	var entry_a7: int = a7
	var c := ControllerView.new(_routed, ctl)
	var o := WorldObjectView.new(_routed, c.owner())
	if c.flaps() == 0:
		c.set_flaps(1)
	if C68kScript.s32(o.y() - c.ground_height()) > 200 and c.gear_position() > 0:
		c.set_gear_position(-c.gear_position())
	c.set_ai_throttle(9)
	a7 = entry_a7 - 0x14
	var err: int = C68kScript.s16(fold_angle_pm_0x2000(C68kScript.s32(0x2c0 - c.pitch())))
	a7 = entry_a7
	var elevator: int = C68kScript.s16(C68kScript.s16((err >> 1) + err) - c.pitch_error_previous())
	c.set_pitch_error_previous(err)
	if abs_int(elevator) > 0x200:
		elevator = -0x200 if elevator < 0 else 0x200
	a7 = entry_a7 - 0x20
	enqueue_control_inputs_avg4(ctl, 0, elevator, 0)
	a7 = entry_a7
	if C68kScript.s32(o.y() - c.ground_height()) > 0x7d0:
		c.set_flaps(-c.flaps())
		return 1
	return 0

## 0x10e28: before frame +0x6ac nothing (0); no bombs -> 1; with the bomb phase idle and rng & 7
## == 0 (0x10 below): phase 2, heading = (heading - 0x80 + (rng & 0xff)) & 0x3fff (0x14 below),
## pitch = (pitch + (rng & 0x7f)) & 0x3fff (0x18 below), spawn_weapon_entity(+0x62a, 0, own
## position, pitch, heading, bank) (0x30 below), one bomb less, word +0x634 (unresolved, raw) - 6.
func ai_script_try_fire_burst(ctl: int) -> int:
	var entry_a7: int = a7
	var c := ControllerView.new(_routed, ctl)
	if _scene().frame_counter() < c.release_frame():
		return 0
	if c.bombs() == 0:
		return 1
	if c.bomb_phase() != 0:
		return 0
	a7 = entry_a7 - 0x10
	var gate: int = overlay_rng_next()
	a7 = entry_a7
	if (gate & 7) != 0:
		return 0
	var o := WorldObjectView.new(_routed, c.owner())
	c.set_bomb_phase(2)
	var bank: int = c.bank()
	a7 = entry_a7 - 0x14
	var heading: int = C68kScript.s32(c.heading() - 0x80 + (overlay_rng_next() & 0xff)) & 0x3fff
	a7 = entry_a7 - 0x18
	var pitch: int = C68kScript.s32(c.pitch() + (overlay_rng_next() & 0x7f)) & 0x3fff
	a7 = entry_a7 - 0x30
	spawn_weapon_entity(C68kScript.u32(c.movement()), 0, C68kScript.u32(o.x()), C68kScript.u32(o.y()), C68kScript.u32(o.z()), pitch, heading, bank)
	a7 = entry_a7
	c.set_bombs(c.bombs() - 1)
	c.raw_write_u16(0x634, c.raw_u16(0x634) - 6)
	return 0

## 0x10360: the target seen from the aircraft. height = owner y + 0x2d * long +0xc (unresolved,
## raw) - ground goes to *height_out; below 4000 the target is raised by (4000 - height) (at or
## below 1000 also max_ai_throttle_maybe_invert_679, 0x4c below) and 1 is returned, else 0. Far
## away (|dx| + |dz| > 0x8000) the climb is limited and blended back while horiz <= 0xffff; the
## vector, shifted into 15 bits, goes to words +2 / +6 / +10 of vec, is rotated by the inverse
## attitude (the Original's build_attitude_matrix_3ang_alt in the frame, 0x58 below, and
## transform_vecs12_by_matrix3) and aimed at (isqrt_floor, attitude_from_velocity into a6 - 0x14 /
## - 0x16); the folded pitch / heading go to *pitch_err / *heading_err (words).
func build_relative_steer_sample(ctl: int, x: int, y: int, z: int, height_out: int, vec: int, pitch_err: int, heading_err: int) -> int:
	assert(a7 != 0, "build_relative_steer_sample needs a7 (its frame holds the matrix and angles)")
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var c := ControllerView.new(_routed, ctl)
	var o := WorldObjectView.new(_routed, c.owner())
	var height: int = C68kScript.s32(C68kScript.s32(0x2d * C68kScript.s32(c.raw_u32(0xc))) + o.y() - c.ground_height())
	_routed.write_u32(height_out, height)
	var low: int = 0
	var ty: int = C68kScript.s32(y)
	if height <= 0xf9f:
		ty = C68kScript.s32(ty + 0xfa0 - height)
		if height <= 0x3e8:
			a7 = entry_a7 - 0x4c
			max_ai_throttle_maybe_invert_679(ctl)
			a7 = entry_a7
		low = 1
	var dx: int = C68kScript.s32(C68kScript.s32(x) - o.x())
	var horiz: int = abs_int(dx)
	var dy: int = C68kScript.s32(ty - o.y())
	var abs_dy: int = abs_int(dy)
	var dz: int = C68kScript.s32(C68kScript.s32(z) - o.z())
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
	_routed.write_u16(vec + 2, C68kScript.asr32(dx, shift))
	_routed.write_u16(vec + 6, C68kScript.asr32(dy, shift))
	_routed.write_u16(vec + 10, C68kScript.asr32(dz, shift))
	var matrix: int = a6 - 0x12
	a7 = entry_a7 - 0x58
	build_attitude_matrix_3ang_alt((-c.pitch()) & 0x3fff, (-c.heading()) & 0x3fff, (-c.bank()) & 0x3fff, matrix)
	a7 = entry_a7
	transform_vecs12_by_matrix3(1, vec, vec, matrix)
	var sq: int = 0
	for off in [2, 6, 10]:
		sq = C68kScript.s32(sq + _routed.read_s16(vec + off) * _routed.read_s16(vec + off))
	var length: int = isqrt_floor(sq)
	attitude_from_velocity(_routed.read_s16(vec + 2), _routed.read_s16(vec + 6), _routed.read_s16(vec + 10), C68kScript.s16(length), sq, a6 - 0x14, a6 - 0x16)
	_routed.write_u16(pitch_err, fold_angle_pm_0x2000(_routed.read_s16(a6 - 0x14)))
	_routed.write_u16(heading_err, fold_angle_pm_0x2000(_routed.read_s16(a6 - 0x16)))
	return low

# ---- aircraft tick: flight-model leaves (update_aircraft_entity blocks 2 / 4) -----------------

const AircraftTypeView := preload("res://native/state/aircraft_type_view.gd")

## 0xe468: below 0x200 above the ground the density word loses ((0x200 - height) * density) asr 12
## (toward zero); the word, sign-extended.
func damp_short_if_below_0x200(height: int, density: int) -> int:
	var d: int = C68kScript.s16(density)
	height = C68kScript.s32(height)
	if height <= 0x1ff:
		var r: int = C68kScript.s32(d * C68kScript.s32(-(height - 0x200)))
		if r < 0:
			r = C68kScript.s32(r + 0xfff)
		d = C68kScript.s16(d - (r >> 12))
	return d

## 0xe4a4 on the aircraft type view: lift from the angle of attack and flaps: base / slope blended
## toward the full-flaps pair by |flaps| / 15; up to the stall angle base + slope * aoa / 64; past
## it *smooth_frames = 0xc and the lift at the stall fades over 0x20 more (/ 2048). Word, signed.
func compute_template_force_from_626(aoa: int, flaps: int, smooth_frames: int) -> int:
	var rec = AircraftTypeView.current(_routed)
	aoa = C68kScript.s16(aoa)
	flaps = C68kScript.s16(flaps)
	var lift: int
	var slope: int
	if flaps == 0:
		lift = rec.lift_base()
		slope = rec.lift_slope()
	else:
		var f: int = C68kScript.s16(abs_int(flaps))
		lift = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32((rec.lift_base_flaps() - rec.lift_base()) * f), 15)) + rec.lift_base()
		slope = C68kScript.s16(C68kScript.divs_trunc(C68kScript.s32((rec.lift_slope_flaps() - rec.lift_slope()) * f), 15) + rec.lift_slope())
	var stall: int = rec.stall_angle()
	if aoa <= stall:
		var t: int = C68kScript.s32(C68kScript.s16(slope) * aoa)
		if t < 0:
			t = C68kScript.s32(t + 0x3f)
		return C68kScript.s16((t >> 6) + C68kScript.s16(lift))
	_routed.write_u8(smooth_frames, 0xc)
	var fade: int = C68kScript.s32(0x20 - (aoa - stall))
	if fade < 0:
		fade = 0
	var peak: int = C68kScript.s32(C68kScript.s32(stall * C68kScript.s16(slope)) + C68kScript.s16(lift))
	var t2: int = C68kScript.s32(peak * fade)
	if t2 < 0:
		t2 = C68kScript.s32(t2 + 0x7ff)
	return C68kScript.s16(t2 >> 11)

## 0xe570 on the aircraft type view: *out = (rated power * word(0x551a divs.w word((density +
## 0x105) asr 3))) asr 8.
func scale_template_field_38(density: int, out: int) -> void:
	var divisor: int = C68kScript.s16(C68kScript.asr32(C68kScript.s16(density) + 0x105, 3))
	var q: int = C68kScript.s16(C68kScript.divs_trunc(0x551a, divisor))
	_routed.write_u32(out, C68kScript.asr32(C68kScript.s32(AircraftTypeView.current(_routed).rated_power() * q), 8))

# ---- object retirement, release, contact ageing and the player camera -------------------------

## 0x51ec on the views: clears player_shot_ctl when it is the object's controller; when bytes +1 /
## +2 differ, clears the scene root's child entry (+0x14 of record +2) and, with +1 inside the
## child window (signed words), removes the object from the seven scatter slots; then x =
## 1000000000, long +4 |= 2, +0x3c = 0, the controller's object link = 0, retire_count + 1. D0 as
## the bytes leave it: d0_in, byte +1, or 7 after the slot scrub.
func retire_entity(obj: int, d0_in: int = 0) -> int:
	var o := WorldObjectView.new(_routed, obj)
	var scene := _scene()
	var d0: int = d0_in
	if o.controller() == scene.player_shot_ctl():
		scene.set_player_shot_ctl(0)
	if o.child_index() != o.slot():
		_routed.write_u32(WorldObjectView.new(_routed, scene.scene_root()).child_table() + 0x14 + o.slot() * 0x18, 0)
		d0 = o.child_index()
		if scene.child_window_lo() <= o.child_index() and o.child_index() <= scene.child_window_hi():
			var k: int = 0
			while true:
				if obj == scene.scatter_slot(k):
					scene.set_scatter_slot(k, 0)
				k += 1
				if not (k < 7):
					break
			d0 = k
	o.set_x(1000000000)
	o.set_flags_long(o.flags_long() | 2)
	o.set_last_active_frame(0)
	if o.controller() != 0:
		_routed.write_u32(o.controller(), 0)
	scene.count_retire()
	return d0

## 0x52dc on the views. A shape: template pose back when byte +6 bit 4; vertex buffers (+0x4c, then
## +0x44, cleared) freed, then every face's index list and the face table; the controller's
## object link cleared; bytes +1 / +2 differing -> dispatch_entity_cleanup; a fractal reference
## released; long +4 loses 0x80000. A group: each child releases its fractal reference (shapes),
## gets long +4 |= 2, one reference less, its controller link cleared; the child table freed and
## cleared, the group's controller link cleared. Callees the Original's (0x18 / 0x1c below).
func deactivate_object(obj: int) -> void:
	var entry_a7: int = a7
	var o := WorldObjectView.new(_routed, obj)
	var kind: int = C68kScript.s8(o.kind())
	if kind == 2:
		a7 = entry_a7 - 0x18
		if ((o.flags6() >> 8) & 0x10) != 0:
			reset_object_to_template_pose(obj)
		heap_free_coalesce(o.vertices2())
		a7 = entry_a7 - 0x1c
		heap_free_coalesce(o.vertices())
		o.set_vertices(0)
		a7 = entry_a7 - 0x18
		var i: int = 0
		while o.face_count() > i:
			heap_free_coalesce(_routed.read_u32(o.face_address(i) + 0x18))
			i += 1
		heap_free_coalesce(o.faces())
		if o.controller() != 0:
			_routed.write_u32(o.controller(), 0)
		if o.child_index() != o.slot():
			dispatch_entity_cleanup(obj)
		if o.support_list() != 0:
			release_fractal_reference(obj)
		o.set_flags_long(o.flags_long() & 0xfff7ffff)
		a7 = entry_a7
	elif kind == 3:
		a7 = entry_a7 - 0x18
		var i: int = 0
		while o.child_template_count() > i:
			var child := WorldObjectView.new(_routed, _routed.read_u32(o.child_table() + 0x14 + i * 0x18))
			if child.kind() == 2 and child.support_list() != 0:
				release_fractal_reference(child.address)
			child.set_flags_long(child.flags_long() | 2)
			child.set_refcount(child.refcount() - 1)
			if child.controller() != 0:
				_routed.write_u32(child.controller(), 0)
			i += 1
		heap_free_coalesce(o.child_table())
		o.set_child_table(0)
		if o.controller() != 0:
			_routed.write_u32(o.controller(), 0)
		a7 = entry_a7

## 0x1377a: the 256 contact records (+0 object a, +4 object b, word +8 age): an untouched one
## (+8 == 0) is freed and its slot cleared, first releasing a's fractal reference when b is
## terrain (byte +6 bit 1) and a has byte +6 bit 5 and a support list (0x14 below); a touched
## one counts +8 down.
func age_contact_records() -> void:
	var entry_a7: int = a7
	var scene := _scene()
	for k in 0x100:
		var slot: int = scene.contact_slot_address(k)
		var rec: int = _routed.read_u32(slot)
		if rec == 0:
			continue
		if _routed.read_u16(rec + 8) != 0:
			_routed.write_u16(rec + 8, _routed.read_u16(rec + 8) - 1)
			continue
		a7 = entry_a7 - 0x14
		var a := WorldObjectView.new(_routed, _routed.read_u32(rec))
		var b := WorldObjectView.new(_routed, _routed.read_u32(rec + 4))
		if ((b.flags6() >> 8) & 2) != 0 and ((a.flags6() >> 8) & 0x20) != 0 and a.support_list() != 0:
			release_fractal_reference(a.address)
		heap_free_coalesce(rec)
		a7 = entry_a7
		_routed.write_u32(slot, 0)

## 0x739a: the camera on the player: the player object's position, the negated attitude words
## (& 0x3fff) and camera_angle4 = (type record +0x44 - controller +0x626) & 0x3fff (long).
func copy_player_pose_to_camera() -> void:
	var scene := _scene()
	var c := ControllerView.new(_routed, scene.player_controller())
	var o := WorldObjectView.new(_routed, c.owner())
	scene.set_camera_x(o.x())
	scene.set_camera_y(o.y())
	scene.set_camera_z(o.z())
	scene.set_neg_camera_pitch((-(c.pitch() & 0xffff)) & 0x3fff)
	scene.set_neg_camera_heading((-(c.heading() & 0xffff)) & 0x3fff)
	scene.set_neg_camera_bank((-(c.bank() & 0xffff)) & 0x3fff)
	scene.set_camera_angle4(C68kScript.s32(C68kScript.s16(AircraftTypeView.new(_routed, c.raw_u32(4)).raw_u16(0x44)) - c.angle_of_attack()) & 0x3fff)

## 0x5832 on the views: designated_entity_2 cleared unless replay state 3; a sweep over the list
## (count = word header +2) by metric +0x10 and range +0xc: an object whose reach passes the
## smallest start so far scans back; each earlier object starting below the reach is a pair, the
## definition's +0x16 notice handler of each (with a controller and a handler) jsr'd as (self,
## other), this object first (0x30 below); one that does not reach ends the scan once its running
## minimum (+0x28) is above the reach. Every object stores the running minimum start at +0x28.
func dispatch_overlap_collisions(list: int, header: int) -> void:
	var entry_a7: int = a7
	var scene := _scene()
	if scene.replay_state() != 3:
		scene.set_designated_entity_2(0)
	var left: int = _routed.read_u16(header + 2)
	var min_start: int = 0x7fffffff
	var cursor: int = list
	while true:
		left = C68kScript.u16(left - 1)
		if left == 0xffff:
			break
		var o := WorldObjectView.new(_routed, _routed.read_u32(cursor))
		cursor += 4
		var reach: int = C68kScript.s32(o.camera_metric() + o.active_range())
		if min_start < reach:
			var back: int = cursor - 4
			var back_left: int = C68kScript.u16(C68kScript.asr32(back - list, 2))
			while true:
				back_left = C68kScript.u16(back_left - 1)
				if back_left == 0xffff:
					break
				back -= 4
				var other := WorldObjectView.new(_routed, _routed.read_u32(back))
				var start: int = C68kScript.s32(other.camera_metric() - other.active_range())
				if reach <= start:
					if reach < other.saved_min_start():
						break
					continue
				for pair in [[o, other], [other, o]]:
					var ctl: int = pair[0].controller()
					if ctl == 0:
						continue
					var notice: int = _routed.read_u32(ControllerView.new(_routed, ctl).raw_u32(4) + 0x16)
					if notice == 0:
						continue
					a7 = entry_a7 - 0x30
					_jsr(notice, [pair[0].address, pair[1].address])
					a7 = entry_a7
		var own_start: int = C68kScript.s32(o.camera_metric() - o.active_range())
		if own_start < min_start:
			min_start = own_start
		o.set_saved_min_start(min_start)

# ---- ground scatter --------------------------------------------------------------------------

## 0x5e28 on the scene and object views (the Original's comment has the whole behaviour): the 64
## scatter points kept within 0x4d58 of the camera (re-placed on the player-heading circle in the
## cockpit views, else in the square; RNG draws in the original order), taken to view space and
## projected (translate_int3_vecs / transform_vecs12_by_matrix3 on the camera matrix, the
## Original's), newly visible points stacked, and the seven slots moved, re-placed in place or
## respawned. Every callee is the Original's, entered at the same a7 (frame 0x3b0); the camera,
## the matrix and the scene record are read where the original reads them.
func update_ground_scatter_objects() -> void:
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x3b0
	var view_buf: int = a6 - 0x300
	var visible: int = a6 - 0x380
	var scene := _scene()
	if scene.scatter_enabled() == 0:
		scene.set_scatter_started(0)
		return
	var h: int = scene.camera_y()
	if scene.view_mode() <= 5:
		h = C68kScript.s32(h - ControllerView.new(_routed, scene.player_controller()).ground_height())
	if h > 0x26ac:
		if scene.scatter_started() == 0:
			return
		a7 = body - 4
		clear_pending_entity_slots()
		a7 = entry_a7
		scene.set_scatter_started(0)
		return
	if scene.scatter_started() == 0:
		scene.set_view_reset(1)
		scene.set_scatter_started(1)
	if scene.view_reset() != 0:
		for i in 0x40:
			_routed.write_u32(scene.scatter_point_address(i), 1000000000)
			_routed.write_u32(scene.scatter_point_address(i) + 8, 1000000000)
		for k in 7:
			if scene.scatter_slot(k) != 0:
				var so := WorldObjectView.new(_routed, scene.scatter_slot(k))
				so.set_flags_long(so.flags_long() & 0xfffffffb)
	var tmpl_base: int = C68kScript.u32(WorldObjectView.new(_routed, scene.scene_root()).raw_u32(0x28) + 0x1a)
	for i in 0x40:
		var pt: int = scene.scatter_point_address(i)
		a7 = body - 8
		var far: bool = abs_int(C68kScript.s32(_routed.read_u32(pt) - scene.camera_x())) > 0x4d58
		if not far:
			far = abs_int(C68kScript.s32(_routed.read_u32(pt + 8) - scene.camera_z())) > 0x4d58
		a7 = entry_a7
		if not far:
			continue
		if scene.view_mode() <= 5 and scene.view_reset() == 0:
			a7 = body - 4
			var r: int = overlay_rng_next()
			var player_heading: int = ControllerView.new(_routed, scene.player_controller()).heading() & 0xffff
			var bearing: int = C68kScript.u16(C68kScript.asr32(((r & 0xfff) * 0xc00) & 0xffffffff, 12) + player_heading + 0xfa00) & 0x3fff
			a7 = body - 8
			var sin_v: int = sin_fixed14(bearing)
			a7 = body - 0xc
			var cos_v: int = cos_fixed14(bearing)
			a7 = entry_a7
			_routed.write_u32(pt, C68kScript.u32(C68kScript.asr32(C68kScript.s16(sin_v) * -0x4d58, 12) + scene.camera_x()))
			_routed.write_u32(pt + 4, 0)
			_routed.write_u32(pt + 8, C68kScript.u32(C68kScript.asr32(C68kScript.s16(cos_v) * 0x4d58, 12) + scene.camera_z()))
		else:
			a7 = body - 4
			var rx: int = overlay_rng_next()
			a7 = entry_a7
			_routed.write_u32(pt, C68kScript.u32(C68kScript.asr32((rx & 0xfff) * 0x9ab0, 12) + scene.camera_x() - 0x4d58))
			_routed.write_u32(pt + 4, 0)
			a7 = body - 4
			var rz: int = overlay_rng_next()
			a7 = entry_a7
			_routed.write_u32(pt + 8, C68kScript.u32(C68kScript.asr32((rz & 0xfff) * 0x9ab0, 12) + scene.camera_z() - 0x4d58))
		var state: int = scene.scatter_state(i)
		if (state & 0xc0) == 0xc0 and (state & 0x3f) != 0x3f:
			var named: int = scene.scatter_slot(state & 0x3f)
			if named != 0:
				var no := WorldObjectView.new(_routed, named)
				no.set_flags_long(no.flags_long() & 0xfffffffb)
		scene.set_scatter_state(i, 0x3f)
	a7 = body - 0x1c
	translate_int3_vecs(0x40, SceneView.SCATTER_POINTS, view_buf, C68kScript.s32(-scene.camera_x()), C68kScript.s32(-h), C68kScript.s32(-scene.camera_z()))
	a7 = body - 0x2c
	transform_vecs12_by_matrix3(0x40, view_buf, view_buf, SceneView.CAMERA_MATRIX)
	a7 = entry_a7
	var n_visible: int = 0
	for i in 0x40:
		var v: int = view_buf + 12 * i
		var vz: int = _routed.read_s16(v + 0xa)
		var on_screen: bool = false
		if vz > 4:
			var sx: int = C68kScript.divs_trunc(C68kScript.s32(_routed.read_s16(v + 2) * scene.projection_x()), vz)
			var sy: int = C68kScript.divs_trunc(C68kScript.s32(-_routed.read_s16(v + 6) * scene.projection_y()), vz)
			on_screen = not (scene.screen_left() > sx or scene.screen_right() < sx
					or scene.screen_top() > sy or scene.screen_bottom() < sy)
		if not on_screen:
			scene.set_scatter_state(i, scene.scatter_state(i) & 0x7f)
		elif scene.scatter_state(i) < 0x80:
			scene.set_scatter_state(i, scene.scatter_state(i) | 0x80)
			_routed.write_u16(visible + 2 * n_visible, i)
			n_visible += 1
	for k in 7:
		var obj: int = scene.scatter_slot(k)
		var o := WorldObjectView.new(_routed, obj)
		if obj != 0:
			if (o.flags_long() & 6) != 0:
				continue
			var own: int = scene.scatter_slot_point(k)
			var own_state: int = scene.scatter_state(own)
			if own_state == (k | 0xc0):
				continue
			if (own_state & 0x3f) == k:
				scene.set_scatter_state(own, own_state | 0x3f)
			if n_visible == 0:
				o.set_flags_long(o.flags_long() | 0x40)
				continue
			o.set_flags_long(o.flags_long() & 0xffffffbf)
		# Pop newly visible points until one's surface has a template.
		var point: int = 0
		var surface: int = 0
		var template: int = 0
		while n_visible != 0:
			n_visible -= 1
			point = _routed.read_s16(visible + 2 * n_visible)
			if (scene.scatter_state(point) & 0x40) != 0:
				surface = scene.scatter_surface(point)
			else:
				var pt0: int = scene.scatter_point_address(point)
				a7 = body - 0x10
				surface = C68kScript.s16(surface_type_at_point(_routed.read_s32(pt0), 0, _routed.read_s32(pt0 + 8)))
				a7 = entry_a7
				scene.set_scatter_surface(point, surface & 0xff)
				scene.set_scatter_state(point, scene.scatter_state(point) | 0x40)
			template = scene.scatter_template(surface)
			if template != 0:
				break
		if template == 0:
			continue
		var pt: int = scene.scatter_point_address(point)
		scene.set_scatter_state(point, (scene.scatter_state(point) & 0xc0) | k)
		scene.set_scatter_slot_point(k, point)
		var heading: int
		if C68kScript.s16(surface) == 0x10:
			heading = 0x540
		else:
			a7 = body - 4
			heading = C68kScript.asr32(C68kScript.lsl32(overlay_rng_next() & 0xfff, 6), 4)
			a7 = entry_a7
		var tv := WorldObjectView.new(_routed, template)
		if obj == 0:
			a7 = body - 0x24
			scene.set_scatter_slot(k, C68kScript.u32(spawn_mission_pool_object(template, _routed.read_s32(pt), _routed.read_s32(pt + 4), _routed.read_s32(pt + 8), 0, C68kScript.s16(heading), 0, 0)))
			a7 = entry_a7
		elif o.child_index() == tv.child_index() and o.vertices() != 0:
			if o.support_list() != 0:
				a7 = body - 8
				release_fractal_reference(obj)
				a7 = entry_a7
			o.set_flags6(o.flags6() | 0x800)
			var rec: int = C68kScript.u32(WorldObjectView.new(_routed, scene.scene_root()).child_table() + o.slot() * 0x18)
			_routed.write_u32(rec + 8, _routed.read_u32(pt))
			_routed.write_u32(rec + 0xc, 0)
			_routed.write_u32(rec + 0x10, _routed.read_u32(pt + 8))
			_routed.write_u16(rec + 4, C68kScript.u16(heading))
			var desc: int = C68kScript.u32(_routed.read_u32(C68kScript.u32(tmpl_base + 0x14 + o.child_index() * 0x18)) + _routed.read_u32(scene.host_world() + 0x8c))
			o.set_x(C68kScript.u32(_routed.read_u32(pt) + _routed.read_u32(desc + 2)))
			o.set_y(_routed.read_u32(desc + 6))
			o.set_z(C68kScript.u32(_routed.read_u32(pt + 8) + _routed.read_u32(desc + 0xa)))
			var n: int = C68kScript.s32(o.vertex_count() + o.face_count())
			if (o.flags5() & 0x10) != 0:
				a7 = body - 0x1c
				translate_int3_vecs(n, C68kScript.u32(desc + 0x22), o.vertices(), 0, 0, 0)
			else:
				a7 = body - 0x14
				apply_template_chain_xform(obj, n, C68kScript.u32(desc + 0x22), o.vertices())
			a7 = body - 8
			update_object_camera_metric(obj)
			a7 = body - 0x10
			update_object_distance_band(obj, o.camera_metric())
			a7 = entry_a7
			_call_shade_object_faces(obj, 0x3b0 + 0x14)
		else:
			a7 = body - 8
			dispatch_entity_cleanup(obj)
			a7 = body - 0x28
			scene.set_scatter_slot(k, C68kScript.u32(spawn_mission_pool_object(template, _routed.read_s32(pt), _routed.read_s32(pt + 4), _routed.read_s32(pt + 8), 0, C68kScript.s16(heading), 0, 0)))
			a7 = entry_a7

# ---- the replay camera path (Godot-backed camera state) ---------------------------------------

const ReplayCameraState := preload("res://native/state/replay_camera_state.gd")

## Rollback switch: true keeps the replay camera block (camera_step, camera_heading / pitch,
## target_hold_frames, camera_goal) in Godot; false keeps it in the Mac memory (the reference).
var replay_camera_storage_godot := true
var _replay_camera = null

func replay_camera():
	if _replay_camera == null:
		_replay_camera = ReplayCameraState.new(_routed, replay_camera_storage_godot)
	return _replay_camera

## 0x1112: the host enters the overlay only from reset_overlay_state (ID02 0x212a), right after
## zeroing the overlay's bss, which holds the replay camera block: the Godot-held block is zeroed
## with it, then the Original's entry runs (routed copy).
func overlay_main_entry(host: int, ticks: int) -> void:
	_take_over_written_routines()
	replay_camera().reset()
	camera_angles().reset()
	camera_position().reset()
	view_state().reset()
	if records() != null:
		records().drop_all(_globals_record)   # the host has just cleared the heap arena
		_arena = -1
		_enter_globals_record()
	routed_overlay_main_entry(host, ticks)

## jsr: overlay routine 0x15134 (carrier_deck_contact, outside the 740: the runtime implements it,
## overlay_loader.gd _15134, on the Mac memory) runs here on the routed memory, so the carrier and
## player controllers it reads and writes are this edition's records. Every other address as the
## Original's.
func _jsr(address: int, args: Array, d0: int = 0):
	if address == 0x15134:
		var a: Array = args.duplicate()
		a.resize(3)
		return _carrier_deck_contact(int(a[0] if a[0] != null else 0), int(a[1] if a[1] != null else 0), int(a[2] if a[2] != null else 0))
	return routed__jsr(address, args, d0)

## overlay_loader.gd _15134, line for line on the routed memory (its comment has the behaviour).
func _carrier_deck_contact(carrier: int, player: int, contact_record: int) -> int:
	var m = _routed
	var ctrl: int = m.read_u32(carrier + 8)
	var pc: int = m.read_u32(player + 8)
	if pc == 0:
		return 0
	var faces: int = m.read_u32(carrier + 0x48)
	if m.read_u16(ctrl + 0x50) == 0xffff:
		for i in maxi(m.read_s16(carrier + 0x3a), 0):
			var tag: int = m.read_u8(faces + i * 0x20 + 0x17)
			if tag == 1:
				m.write_u16(ctrl + 0x50, i)
			if tag == 3:
				m.write_u16(ctrl + 0x52, i)
		if m.read_u16(ctrl + 0x50) == 0xffff:
			_positional_call("overlay_printf", [0x15114, 0, 0, 0, 0, 0])
		var deck_face: int = (faces + (m.read_s16(ctrl + 0x50) << 5)) & 0xffffffff
		var w: int = m.read_s16(m.read_u32(deck_face + 0x18))
		var y: int = m.read_s32(m.read_u32(carrier + 0x44) + 12 * w + 4)
		m.write_u16(ctrl + 0x54, (y >> (m.read_u16(carrier + 0x34) & 63)) & 0xffff)
	var deck: int = (faces + (m.read_s16(ctrl + 0x50) << 5)) & 0xffffffff
	var folded: int = C68kScript.s16(_positional_call("fold_angle_pm_0x2000", [m.read_s16(ctrl + 0x20)]))
	if absi(folded) <= 0x4ff and _positional_call("point_over_face_xz", [contact_record, deck]) != 0:
		m.write_u32(pc + 0x2e, m.read_s16(ctrl + 0x54) & 0xffffffff)
		if m.read_u16(0x1b870) == 0:
			m.write_u8(pc + 0x2c, m.read_u8(deck + 0x16))
	if m.read_s16(m.read_u32(pc + 4)) <= 4 and m.read_u16(ctrl + 0x52) != 0xffff:
		if _positional_call("point_over_face_xz", [contact_record, (faces + (m.read_s16(ctrl + 0x52) << 5)) & 0xffffffff]) != 0:
			m.write_u8(pc + 0x63f, 1)
	if m.read_s16(ctrl + 0x54) > 0:
		if m.read_s16(0x1b85a) <= m.read_s16(0x1b85c):
			m.write_u16(0x1b85a, (m.read_u16(0x1b85a) + 4) & 0xffff)
			var size: int = m.read_s16(0x1b85a) * 8
			if m.read_u32(0x1b860) == 0:
				m.write_u32(0x1b860, _positional_call("heap_alloc", [size]) & 0xffffffff)
			else:
				m.write_u32(0x1b860, _positional_call("heap_realloc_grow_only", [m.read_u32(0x1b860), size]) & 0xffffffff)
		var entry: int = m.read_u32(0x1b860) + m.read_s16(0x1b85c) * 8
		m.write_u32(entry, carrier & 0xffffffff)
		m.write_u32(entry + 4, player & 0xffffffff)
		m.write_u16(0x1b85c, (m.read_u16(0x1b85c) + 1) & 0xffff)
	return 0

## Overlay hook 0x4b0 (player_left_ground_zone, outside the 740), the runtime's overlay_loader.gd
## _04b0 on the routed memory: 0 while the player is within 0x1387 above its ground (object y +0x1c
## less the controller's +0x2e), else 1 (also without a player).
func _04b0() -> int:
	var m = _routed
	var ctl: int = m.read_u32(0x1b738)
	if ctl == 0:
		return 1
	var d: int = (m.read_u32(m.read_u32(ctl) + 0x1c) - m.read_u32(ctl + 0x2e)) & 0xffffffff
	if d >= 0x80000000:
		d -= 0x100000000
	return 0 if d <= 0x1387 else 1

## Overlay hook 0xd23a (redraw_view_readouts, outside the 740), the runtime's _d23a on the routed
## memory: the view readouts, then with the panel flag 0x20ae4 the throttle gauge, radar range
## buttons, bomb indicator and the overlays marked drawn.
func _d23a() -> int:
	update_view_readouts_and_overlays(0)
	if _routed.read_u16(0x20ae4) != 0:
		update_throttle_gauge()
		update_radar_range_buttons()
		update_bomb_indicator()
		mark_view_overlays_drawn()
	return 0

## Overlay routine 0x16208 (the 1-bit renderer's erase hook), the runtime's _16208 on the routed
## memory: CLR.W $1da8c.
func _16208() -> int:
	_routed.write_u16(0x1da8c, 0)
	return 0

## Temporary compatibility adapter (until the runtime's written overlay routines are Native): the
## host calls hook 0x4b0 through the segment loader, which runs the runtime's _04b0 on the Mac
## memory, where this edition's controllers are not. The host-slot entries link_overlay put in
## code_table include lambdas of the overlay loader (0x16208, the positional routines): through one,
## the segment loader's own register_address installs this edition's _04b0 for the hook (no Original file changed). Runs at every overlay entry
## (each image load attaches a new port and registers the hooks again before entering it).
func _take_over_written_routines() -> void:
	for f in code_table.values():
		if f is Callable and f.is_valid():
			var loader = f.get_object()
			if loader != null and loader.has_method("_attach") and loader.get("machine") != null:
				var machine = loader.machine
				# As the loader's hook: the routine runs on the machine's free stack.
				var hook := func(routine: Callable) -> Callable:
					return func(_args: Array) -> int:
						var saved: int = a7
						a7 = machine.free_stack()
						var r: int = routine.call()
						a7 = saved
						return r
				machine.segments.register_address(0x4b0, hook.call(_04b0))
				machine.segments.register_address(0xd23a, hook.call(_d23a))
				code_table[0x16208] = func(..._args: Array) -> int: return _16208()
				return
	push_error("Native overlay: the overlay loader was not found; hook 0x4b0 stays the runtime's")

## The runtime's _run for a port method (callv with the arguments as given).
func _positional_call(method: String, args: Array) -> int:
	var r = callv(method, args)
	return 0 if r == null else int(r)

## The overlay's data and bss (image header: data 0x1a75c..0x1b524, bss ..0x267c8) as one Godot-owned
## record: the Native blocks (camera, view state) take their fields first, the rest of the overlay's
## globals live here. Created at the first overlay entry from the image as loaded (data initialised,
## bss zeroed by the host); at every later entry its bss part is zeroed as the host's memset zeroes
## the Mac bss, its data part kept (the host does not reload it).
const GLOBALS_START := 0x1a75c
const BSS_START := 0x1b524
const GLOBALS_END := 0x267c8
var globals_storage_godot := true
var _globals_record: int = -1

func _enter_globals_record() -> void:
	if not globals_storage_godot:
		return
	if _globals_record < 0:
		records().create_from_memory(GLOBALS_START, GLOBALS_END - GLOBALS_START)
		_globals_record = GLOBALS_START
	else:
		records().zero(GLOBALS_START, BSS_START, GLOBALS_END)

## Blocks the overlay hands to the emulated Toolbox (QuickDraw reads them in the Mac memory) are
## holes in the arena record while they live: allocations made with this above 0. Record watch
## (loop 7): present_spans_to_window's 0x2000-byte CopyBits buffer (and present_bw_frame's, the same
## for the 1-bit frame routine, by reading). Until QuickDraw is Native.
var _toolbox_shared: int = 0
## The heap arena record's handle (its base, host world +0x348), or -1 before the first heap_sbrk.
var _arena: int = -1

## 0x1938: the break advances (routed copy); the arena record covers the arena up to it, created at
## the first call from the arena's bytes (the host has cleared its block), then grown.
func heap_sbrk(nbytes: int) -> int:
	var old_break: int = routed_heap_sbrk(nbytes)
	if records() != null:
		var base: int = _routed.read_u32(_routed.read_u32(DAT_0001b5a0) + 0x348) & 0xffffff
		var size: int = _routed.read_u32(DAT_00020c50)
		if _arena < 0:
			records().create_from_memory(base, size)
			_arena = base
		else:
			assert(base == _arena, "the heap arena moved")
			records().grow(base, size)
	return old_break

## 0x1a48 (routed copy): a block allocated for the Toolbox becomes a hole in the arena record.
func heap_alloc(nbytes: int) -> int:
	var block: int = routed_heap_alloc(nbytes)
	if block != 0 and records() != null and _toolbox_shared > 0:
		var size: int = (_routed.read_u32(block - 8) - 1) * 8 if _routed.read_u8(block - 4) == 0xff else (1 << _routed.read_u8(block - 4)) - 4
		records().open_hole(block, block + size)
	return block

## present_spans_to_window (routed copy, routed_port.gd): its heap buffer goes to QuickDraw's CopyBits.
func present_spans_to_window(new_rows: int, old_rows: int, rows: int, arg4: int, arg5: int, slot: int) -> void:
	_toolbox_shared += 1
	routed_present_spans_to_window(new_rows, old_rows, rows, arg4, arg5, slot)
	_toolbox_shared -= 1

## present_bw_frame (routed copy): its heap buffer goes to the host's 1-bit frame routine (host +0x80).
func present_bw_frame(arg: int) -> void:
	_toolbox_shared += 1
	routed_present_bw_frame(arg)
	_toolbox_shared -= 1

## 0x197a (routed copy): a Toolbox block's hole closes first (its bytes back into the arena record).
func heap_free_coalesce(payload: int) -> void:
	if records() != null and records().is_hole(payload):
		records().close_hole(payload)
	routed_heap_free_coalesce(payload)

## The Native-held blocks as the Mac memory would hold them ([address, bytes]), for traces.
func native_state_ranges() -> Array:
	var ranges: Array = []
	if records() != null:
		ranges.append_array(records().memory_image())   # first: the blocks below override the globals record
	if replay_camera_storage_godot:
		ranges.append_array(replay_camera().memory_image())
	for b in native_blocks():
		ranges.append_array(b.memory_image())
	return ranges

## 0x88a0 on the views: the camera 1000 from the object toward (px, py, pz) (offsets shifted, isqrt
## 0x24 below), x - 0xfa / y + 0xfa / z; at the point itself when the length is 0; y at least 0xfa.
func place_camera_behind_object(obj: int, px: int, py: int, pz: int, shift: int) -> void:
	var entry_a7: int = a7
	var scene := _scene()
	var o := WorldObjectView.new(_routed, obj)
	shift = C68kScript.s16(shift)
	var pos: Array = [o.x(), o.y(), o.z()]
	var p: Array = [px, py, pz]
	var d: Array = []
	for k in 3:
		d.append(C68kScript.s16(C68kScript.asr32(C68kScript.s32(pos[k] - p[k]), shift)))
	a7 = entry_a7 - 0x24
	var length: int = C68kScript.s16(isqrt_floor(C68kScript.s32(d[0] * d[0] + d[1] * d[1] + d[2] * d[2])))
	a7 = entry_a7
	if length == 0:
		scene.set_camera_x(C68kScript.u32(px))
		scene.set_camera_y(C68kScript.u32(py))
		scene.set_camera_z(C68kScript.u32(pz))
	else:
		scene.set_camera_x(C68kScript.u32(C68kScript.divs_trunc(d[0] * 1000, length) + o.x() - 0xfa))
		scene.set_camera_y(C68kScript.u32(C68kScript.divs_trunc(d[1] * 1000, length) + o.y() + 0xfa))
		scene.set_camera_z(C68kScript.u32(C68kScript.divs_trunc(d[2] * 1000, length) + o.z()))
	if scene.camera_y() <= 0xf9:
		scene.set_camera_y(0xfa)

## 0x8abe on the scene view and the replay camera state (camera_pitch / camera_heading there):
## the new angles toward the point (metric shift, isqrt, attitude_from_velocity into the frame),
## snapped beyond 0x1000, else a quarter of the way; their negations to 0x1d810 / 0x1d80e, bank and
## camera_angle4 cleared. Callees the Original's at the same a7.
func turn_camera_toward_point(x: int, y: int, z: int) -> void:
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x20
	var scene := _scene()
	var rc = replay_camera()
	a7 = body - 0x10
	var metric: int = compute_camera_abs_metric(x, y, z)
	a7 = entry_a7
	var shift: int = 0
	while (C68kScript.u32(metric) & 0xffff8000) != 0:
		shift = C68kScript.s16(shift + 1)
		metric = C68kScript.asr32(metric, 1)
	shift = C68kScript.s16(shift + 1)
	var dx: int = C68kScript.asr32(C68kScript.s32(x - scene.camera_x()), shift)
	var dy: int = C68kScript.asr32(C68kScript.s32(y - scene.camera_y()), shift)
	var dz: int = C68kScript.asr32(C68kScript.s32(z - scene.camera_z()), shift)
	var sq: int = C68kScript.s32(C68kScript.s32(C68kScript.s32(dx * dx) + C68kScript.s32(dy * dy)) + C68kScript.s32(dz * dz))
	a7 = body - 8
	var length: int = C68kScript.s16(isqrt_floor(sq))
	a7 = body - 0x24
	attitude_from_velocity(dx, dy, dz, length, sq, a6 - 2, a6 - 4)
	a7 = body - 8
	var dp: int = fold_angle_pm_0x2000(C68kScript.s32(_routed.read_s16(a6 - 2) - C68kScript.s16(rc.pitch_u16())))
	a7 = body - 0xc
	var dh: int = fold_angle_pm_0x2000(C68kScript.s32(_routed.read_s16(a6 - 4) - C68kScript.s16(rc.heading_u16())))
	a7 = entry_a7
	if abs_int(dp) > 0x1000 or abs_int(dh) > 0x1000:
		rc.set_pitch(_routed.read_u16(a6 - 2) & 0x3fff)
		rc.set_heading(_routed.read_u16(a6 - 4) & 0x3fff)
	else:
		rc.set_pitch((((dp + 3 if dp < 0 else dp) >> 2) + rc.pitch_u16()) & 0x3fff)
		rc.set_heading((((dh + 3 if dh < 0 else dh) >> 2) + rc.heading_u16()) & 0x3fff)
	scene.set_neg_camera_pitch((-rc.pitch_u16()) & 0x3fff)
	scene.set_neg_camera_heading((-rc.heading_u16()) & 0x3fff)
	scene.set_neg_camera_bank(0)
	scene.set_camera_angle4(0)

## 0x8972 on the views: the bail-out camera. Without a live parachute (or once it is down and past
## 0x5a0) the outcome is resolved (0x28 below). Near the player plane (within 0x9ab0, 0x3c below,
## while recent or high) behind the parachute toward the midpoint (0x38 below); else at the
## parachute's pose-ring entry after +0x60e. Then turned toward the target (0x30 below).
func update_bailout_view() -> int:
	var entry_a7: int = a7
	var scene := _scene()
	var chute_ctl: int = scene.parachute_object()
	if chute_ctl == 0:
		a7 = entry_a7 - 0x28
		var outcome0: int = resolve_bailout_outcome()
		a7 = entry_a7
		return outcome0
	var cc := ControllerView.new(_routed, chute_ctl)
	var ch := WorldObjectView.new(_routed, cc.owner())
	if (ch.list_bits() & 2) != 0 or (cc.raw_u16(0x61e) != 0 and C68kScript.s16(cc.raw_u16(0x614)) > 0x5a0):
		a7 = entry_a7 - 0x28
		var outcome: int = resolve_bailout_outcome()
		a7 = entry_a7
		return outcome
	var target: Array = [ch.raw_u32(0x18), ch.raw_u32(0x1c), ch.raw_u32(0x20)]
	var placed: bool = false
	if scene.player_controller() != 0 and (C68kScript.s32(scene.frame_counter() - 0x4b) <= C68kScript.s32(ch.raw_u32(0x40)) or C68kScript.s32(ch.raw_u32(0x1c) - cc.raw_u32(0x2e)) > 0x373):
		var plane := WorldObjectView.new(_routed, ControllerView.new(_routed, scene.player_controller()).owner())
		a7 = entry_a7 - 0x3c
		var dist: int = C68kScript.s32(approx_dist3_xyz(plane.x(), plane.y(), plane.z(), ch.x(), ch.y(), ch.z()))
		a7 = entry_a7
		if dist <= 0x9ab0:
			for k in 3:
				var m: int = C68kScript.s32(plane.raw_u32(0x18 + 4 * k) + ch.raw_u32(0x18 + 4 * k))
				target[k] = C68kScript.u32((m + 1 if m < 0 else m) >> 1)
			var shift: int = 0
			while (C68kScript.u32(dist) & 0xffff8000) != 0:
				shift = C68kScript.s16(shift + 1)
				dist = C68kScript.asr32(dist, 1)
			a7 = entry_a7 - 0x38
			place_camera_behind_object(ch.address, target[0], target[1], target[2], shift)
			a7 = entry_a7
			placed = true
	if not placed:
		var slot: int = (cc.raw_u16(0x60e) + 1) & 0xffff
		if slot > 0x4a:
			slot = (slot - 0x4b) & 0xffff
		var pose: int = C68kScript.u32(chute_ctl + 0x32 + C68kScript.s16(slot) * 0x14)
		scene.set_camera_x(_routed.read_u32(pose))
		scene.set_camera_y(_routed.read_u32(pose + 4))
		scene.set_camera_z(_routed.read_u32(pose + 8))
	a7 = entry_a7 - 0x30
	turn_camera_toward_point(target[0], target[1], target[2])
	a7 = entry_a7
	return 0

## 0x8ff4 on the views and the replay camera state: view mode 8, a tracked player object untracked
## (8 below), the clip bounds (4 below); the camera 1000 behind the player's pose-ring entry, the
## goal at the previous entry's offset (side by replay_request), at least 0x280 above the player's
## ground, camera_step = (goal - camera) / 75; then the replay globals, designated_entity_2 from a
## live follow target, the follow target and hold cleared, all sound stopped (4 below).
func begin_replay_view() -> void:
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x38
	var scene := _scene()
	var rc = replay_camera()
	scene.set_view_mode(8)
	var obj := WorldObjectView.new(_routed, ControllerView.new(_routed, scene.player_controller()).owner())
	if (obj.list_bits() & 0x40) != 0:
		obj.set_flags_long(obj.flags_long() & 0xffffffbf)
		a7 = body - 8
		drop_contact_records_for_object(obj.address)
		a7 = entry_a7
	a7 = body - 4
	update_view_clip_bounds()
	a7 = entry_a7
	var ctl := ControllerView.new(_routed, scene.player_controller())
	var cam: Array = _replay_offset(C68kScript.u32(ctl.address + 0x36 + ctl.pose_ring_index() * 0x14), a6, body, 0, 0, -1000)
	scene.set_camera_x(cam[0])
	scene.set_camera_y(cam[1])
	scene.set_camera_z(cam[2])
	ctl = ControllerView.new(_routed, scene.player_controller())
	var prev: int = C68kScript.s16(ctl.pose_ring_index() - 1)
	if prev < 0:
		prev = 0x4a
	var side: int = -0x513 if scene.replay_request() == 1 else 0x513
	var goal: Array = _replay_offset(C68kScript.u32(ctl.address + 0x36 + prev * 0x14), a6, body, -0x2ee, 0x183, side)
	rc.set_goal(2, goal[0])
	rc.set_goal(1, goal[1])
	rc.set_goal(0, goal[2])
	var floor_y: int = C68kScript.s32(ControllerView.new(_routed, scene.player_controller()).ground_height() + 0x280)
	if floor_y > C68kScript.s32(rc.goal_u32(1)):
		rc.set_goal(1, C68kScript.u32(floor_y))
	rc.set_step(2, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(rc.goal_u32(2) - scene.camera_x()), 0x4b)))
	rc.set_step(1, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(rc.goal_u32(1) - scene.camera_y()), 0x4b)))
	rc.set_step(0, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(rc.goal_u32(0) - scene.camera_z()), 0x4b)))
	ControllerView.new(_routed, scene.player_controller()).raw_write_u16(0x688, 0)
	scene.set_view_mode(8)
	rc.set_heading(0xc001)
	rc.set_pitch(0xc001)
	scene.set_replay_request(0)
	scene.set_replay_frames_left(0x69)
	scene.set_replay_state(1)
	_routed.write_u16(scene.host_world() + 0x98, 1)
	var follow: int = scene.view_follow_target()
	if follow != 0 and _routed.read_u32(follow) != 0:
		scene.set_designated_entity_2(follow)
	scene.set_view_follow_target(0)
	rc.set_hold(0)
	a7 = body - 4
	stop_all_sound_channels()
	a7 = entry_a7

## 0x8c30 on the views and the replay camera state (the Original's comment has the whole
## behaviour): look at a live player shot, else follow the designated aircraft within 0x9ab0
## (behind the player or the aircraft), else look at the player and glide by camera_step (re-aimed
## at the goal over frames_left - 0x1e frames when a follow ended); count the replay down and
## resolve the outcome at 0; turned toward the target. Callees the Original's at the same a7.
func update_tracking_camera() -> int:
	var entry_a7: int = a7
	var a6: int = entry_a7 - 4
	var body: int = entry_a7 - 0x58
	var scene := _scene()
	var rc = replay_camera()
	var result: int = 0
	var look_obj: int = -1
	var tracked: bool = false
	var t: Array = [0, 0, 0]
	var shot: int = scene.player_shot_ctl()
	if shot != 0 and _routed.read_s32(_routed.read_u32(shot) + 0x40) < scene.frame_counter():
		look_obj = _routed.read_u32(shot)
	elif (scene.designated_entity_2() != 0 or rc.hold_u32() != 0) and scene.replay_state() != 3:
		if rc.hold_u32() == 0:
			scene.set_view_follow_target(scene.designated_entity_2())
			rc.set_hold(0x14)
		var follow: int = scene.view_follow_target()
		if follow != 0:
			var player_obj := WorldObjectView.new(_routed, ControllerView.new(_routed, scene.player_controller()).owner())
			var target := WorldObjectView.new(_routed, _routed.read_u32(follow))
			if target.address != 0:
				a7 = body - 0x1c
				var dist: int = C68kScript.s32(approx_dist3_xyz(player_obj.x(), player_obj.y(), player_obj.z(), target.x(), target.y(), target.z()))
				a7 = entry_a7
				if dist <= 0x9ab0:
					tracked = true
					for k in 3:
						var m: int = C68kScript.s32(target.raw_u32(0x18 + 4 * k) + player_obj.raw_u32(0x18 + 4 * k))
						t[k] = C68kScript.u32((m + 1 if m < 0 else m) >> 1)
					var shift: int = 0
					while (C68kScript.u32(dist) & 0xffff8000) != 0:
						shift = C68kScript.s16(shift + 1)
						dist = C68kScript.asr32(dist, 1)
					var player_ctl := ControllerView.new(_routed, scene.player_controller())
					a7 = body - 8
					var turn: int = fold_angle_pm_0x2000(C68kScript.s32(ControllerView.new(_routed, follow).heading() - player_ctl.heading()))
					a7 = entry_a7
					var anchor: int = player_obj.address
					if scene.designated_entity_2() != 0 and abs_int(turn) <= 0x1000:
						var f: Array = _tracking_forward(player_ctl.address, a6, body, 0x14)
						a7 = body - 0x1c
						var player_ahead: int = C68kScript.s32(approx_dist3_xyz(C68kScript.s32(f[0] + player_obj.raw_u32(0x18)), C68kScript.s32(f[1] + player_obj.raw_u32(0x1c)), C68kScript.s32(f[2] + player_obj.raw_u32(0x20)), target.x(), target.y(), target.z()))
						a7 = entry_a7
						var g: Array = _tracking_forward(scene.view_follow_target(), a6, body, 0x2c)
						a7 = body - 0x2c
						var target_ahead: int = C68kScript.s32(approx_dist3_xyz(C68kScript.s32(g[0] + target.raw_u32(0x18)), C68kScript.s32(g[1] + target.raw_u32(0x1c)), C68kScript.s32(g[2] + target.raw_u32(0x20)), player_obj.x(), player_obj.y(), player_obj.z()))
						a7 = entry_a7
						if target_ahead < player_ahead:
							anchor = target.address
					a7 = body - 0x18
					place_camera_behind_object(anchor, t[0], t[1], t[2], shift)
					a7 = entry_a7
					if scene.designated_entity_2() != scene.view_follow_target():
						rc.set_hold(C68kScript.u32(rc.hold_u32() - 1))
	if not tracked:
		if look_obj == -1:
			look_obj = ControllerView.new(_routed, scene.player_controller()).owner()
		var lo := WorldObjectView.new(_routed, look_obj)
		t = [lo.raw_u32(0x18), lo.raw_u32(0x1c), lo.raw_u32(0x20)]
		if scene.replay_state() != 3:
			if scene.view_follow_target() != 0:
				var frames: int = C68kScript.s32(scene.replay_frames_left() - 0x1e)
				assert(frames != 0, "update_tracking_camera: divs.l by zero traps")
				rc.set_step(2, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(rc.goal_u32(2) - scene.camera_x()), frames)))
				rc.set_step(1, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(rc.goal_u32(1) - scene.camera_y()), frames)))
				rc.set_step(0, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(rc.goal_u32(0) - scene.camera_z()), frames)))
				scene.set_view_follow_target(0)
				rc.set_hold(0)
			scene.set_camera_x(C68kScript.u32(scene.camera_x() + rc.step_u32(2)))
			scene.set_camera_y(C68kScript.u32(scene.camera_y() + rc.step_u32(1)))
			scene.set_camera_z(C68kScript.u32(scene.camera_z() + rc.step_u32(0)))
	scene.set_replay_frames_left(C68kScript.u16(scene.replay_frames_left() - 1))
	if scene.replay_frames_left() <= 0x1e:
		scene.set_replay_state(3)
		if scene.replay_frames_left() == 0:
			var host: int = scene.host_world()
			if _routed.read_s16(host + 0x98) != 2:
				if (ControllerView.new(_routed, scene.player_controller()).raw_u8(0x684) & 4) == 0:
					result = -1
				elif scene.parachute_object() == 0:
					_routed.write_u16(host + 0xd4, 1)
					result = 1
				else:
					a7 = body - 4
					result = C68kScript.s16(resolve_bailout_outcome())
					a7 = entry_a7
			_routed.write_u16(scene.host_world() + 0x98, 0)
	a7 = body - 0x10
	turn_camera_toward_point(t[0], t[1], t[2])
	a7 = entry_a7
	return C68kScript.s16(result)

# ---- camera-angle readers ---------------------------------------------------------------------

## 0x19bbc on the views: controller +8 / +0xc / +0x10 = camera - (object position + (0xdff,
## -0x133e, 0x161d)); +0x22 / +0x20 = -neg_camera_heading / -neg_camera_pitch & 0x3fff.
func tick_view_attached_object(ctl: int) -> void:
	var scene := _scene()
	var c := ControllerView.new(_routed, ctl)
	var o := WorldObjectView.new(_routed, c.owner())
	c.raw_write_u32(8, C68kScript.u32(scene.camera_x() - (o.x() + 0xdff)))
	c.raw_write_u32(0xc, C68kScript.u32(scene.camera_y() - (o.y() - 0x133e)))
	c.raw_write_u32(0x10, C68kScript.u32(scene.camera_z() - (o.z() + 0x161d)))
	c.set_heading(C68kScript.u16(-(scene.neg_camera_heading() & 0xffff)) & 0x3fff)
	c.set_pitch(C68kScript.u16(-(scene.neg_camera_pitch() & 0xffff)) & 0x3fff)

## 0x12738 on the views: the effect faces the camera (heading, then pitch). Returns 0.
func face_object_to_camera(ctl: int) -> int:
	var scene := _scene()
	var c := ControllerView.new(_routed, ctl)
	c.set_heading((-(scene.neg_camera_heading() & 0xffff)) & 0x3fff)
	c.set_pitch((-(scene.neg_camera_pitch() & 0xffff)) & 0x3fff)
	return 0

## 0x11796 on the views: a free plume facing the camera (word +0x32) takes heading
## -neg_camera_heading; the width layout, the per-frame jitter (RNG 0x18 below) and the age scaling
## as the Original's comment has them. Returns 0.
func shape_free_plume(ctl: int) -> int:
	var entry_a7: int = a7
	var scene := _scene()
	var c := ControllerView.new(_routed, ctl)
	var o := WorldObjectView.new(_routed, c.owner())
	var verts: int = o.vertices()
	if c.raw_u16(0x32) != 0:
		c.set_heading((-(scene.neg_camera_heading() & 0xffff)) & 0x3fff)
	if c.raw_u16(0x36) != 0:
		var width: int = C68kScript.s16(c.raw_u16(0x36))
		for spec in [[0, -width, 1, 1], [0xc, -3 * width, 7, 3], [0x18, -width, 3, 2], [0x30, width, 3, 2], [0x3c, 3 * width, 7, 3], [0x48, width, 1, 1]]:
			var v: int = spec[1]
			if v < 0:
				v += spec[2]
			_routed.write_u32(verts + spec[0], C68kScript.u32(v >> spec[3]))
		c.raw_write_u16(0x36, 0)
	if c.raw_u32(0x38) == C68kScript.u32(scene.frame_counter()):
		return 0
	var height: int = C68kScript.s16(c.raw_u16(0x34))
	a7 = entry_a7 - 0x18
	_routed.write_u32(verts + 0x10, C68kScript.u32(_plume_jitter(height)))
	var rnd: int = overlay_rng_next()
	var half: int = C68kScript.s16(c.raw_u16(0x34))
	if half < 0:
		half += 1
	half = C68kScript.s16(half) >> 1
	var v28: int = C68kScript.s32((rnd & 0xfff) * C68kScript.s16(c.raw_u16(0x34)))
	if v28 < 0:
		v28 = C68kScript.s32(v28 + 0x1fff)
	_routed.write_u32(verts + 0x28, C68kScript.u32(C68kScript.asr32(v28, 13) + half))
	_routed.write_u32(verts + 0x40, C68kScript.u32(_plume_jitter(C68kScript.s16(c.raw_u16(0x34)))))
	_routed.write_u32(verts + 0x58, C68kScript.u32(_plume_jitter(C68kScript.s16(c.raw_u16(0x34)))))
	a7 = entry_a7
	var age: int = C68kScript.s16(abs_int(C68kScript.s32(scene.frame_counter() - o.raw_u32(0x40))))
	if age <= 0x2c:
		for off in [0x10, 0x28, 0x40, 0x58]:
			_routed.write_u32(verts + off, C68kScript.u32(C68kScript.divs_trunc(C68kScript.s32(age * _routed.read_s32(verts + off)), 0x2d)))
	return 0

## 0xc8d6 on the scene view: the direction needle (type 6) at angle neg_camera_heading + 0xf000.
func update_direction_needle() -> void:
	_simple_needle(a7, C68kScript.u16((_scene().neg_camera_heading() & 0xffff) + 0xf000) & 0x3fff, 0x150, 0x18, 6)
