extends RefCounted

## State traces of a stepped run (MacMachine.use_stepped_clock) and their comparison: the
## differential check Native will be held to against Original. A trace is one entry per traced tick:
## the stepped tick, MD5s of the A5 world and of the overlay's image (its data: the world's objects,
## at base 0), and the overlay's random-number state exactly (overlay_rng_next's slot pointer at
## 0x26008 and its table up to 0x260e8). The screen is left out: its text comes from the host's fonts.
## Two fields leave out what an edition may keep elsewhere: "game_globals" is the A5 world with the
## sound driver's block (A5 - 0xc38 .. A5 - 0x34e) zeroed, "world" the overlay's image above the
## low-memory globals (from 0x2800) without the RNG; the Native edition is compared on those and
## "rng" (taken from the overlay port when it keeps the RNG as typed state); "world" takes the blocks
## the port holds in Godot (native_state_ranges) from the port, not from the memory.

const RNG_START := 0x26008
const RNG_END := 0x260ec
const OVERLAY_END := 0x03e700
const FIELDS := ["globals", "overlay", "rng", "game_globals", "world"]
const NATIVE_FIELDS := ["game_globals", "world", "rng"]
const SOUND_BLOCK := [-0xc38, -0x34e]
const LOW_MEMORY_END := 0x2800

static func capture(m) -> Dictionary:
	var bytes: PackedByteArray = m.memory._bytes
	return {"tick": m.stepped_ticks,
		"globals": bytes.slice(m.A5 - m.BELOW_A5, m.A5).hex_encode().md5_text(),
		"overlay": bytes.slice(0, OVERLAY_END).hex_encode().md5_text(),
		"rng": _rng(m, bytes),
		"game_globals": _without_sound(bytes.slice(m.A5 - m.BELOW_A5, m.A5), m.BELOW_A5).hex_encode().md5_text(),
		"world": _without_rng(_with_native_state(m, bytes.slice(LOW_MEMORY_END, OVERLAY_END), LOW_MEMORY_END)).hex_encode().md5_text(),
		"heap": _heap(m, bytes)}

## The overlay's heap arena (host world +0x348, up to heap_break 0x20c50: heap_sbrk's arena) with
## the records an edition keeps in Godot in place of the memory's bytes; "" before the overlay has
## one. Not in FIELDS / NATIVE_FIELDS (the recorded fixtures predate it): full-session Original /
## Native comparisons check it (GAME_RUNTIME.md).
static func _heap(m, bytes: PackedByteArray) -> String:
	# The port's view of the memory (Native keeps the overlay globals in Godot).
	var port = m.overlay_loader.port if m.overlay_loader != null else null
	var mem = port._routed if port != null and port.get("_routed") != null else m.memory
	var host: int = mem.read_u32(0x1b5a0) & 0xffffff
	if host == 0 or host + 0x34c > bytes.size():
		return ""
	var base: int = mem.read_u32(host + 0x348) & 0xffffff
	var size: int = mem.read_u32(0x20c50)
	if base == 0 or size <= 0 or base + size > bytes.size():
		return ""
	return _with_native_state(m, bytes.slice(base, base + size), base).hex_encode().md5_text()

## The world with the blocks an edition keeps outside the Mac memory (the port's native_state_ranges:
## [address, bytes] as the memory would hold them) put in place of the memory's bytes there, so the
## trace hashes the state the edition actually holds (Native's Godot-backed replay camera).
static func _with_native_state(m, world: PackedByteArray, base: int) -> PackedByteArray:
	var port = m.overlay_loader.port if m.overlay_loader != null else null
	if port == null or not port.has_method("native_state_ranges"):
		return world
	for part in port.native_state_ranges():
		for i in part[1].size():
			var at: int = part[0] - base + i
			if at >= 0 and at < world.size():
				world[at] = part[1][i]
	return world

## The RNG as traced: the Mac memory's, or the overlay port's typed state when it keeps one (Native).
static func _rng(m, bytes: PackedByteArray) -> String:
	var port = m.overlay_loader.port if m.overlay_loader != null else null
	if port != null and port.has_method("rng_bytes"):
		# The cursor and ring from the port; the long after the ring (0x260e8, smoke_template) from memory.
		var tail: PackedByteArray = bytes.slice(port.RING_END, RNG_END)
		if port.get("_routed") != null:   # Native keeps the overlay globals in Godot
			tail = PackedByteArray()
			for k in range(port.RING_END, RNG_END):
				tail.append(port._routed.read_u8(k))
		return (port.rng_bytes() + tail).hex_encode()
	return bytes.slice(RNG_START, RNG_END).hex_encode()

## The world without the RNG's bytes, which "rng" records.
static func _without_rng(world: PackedByteArray) -> PackedByteArray:
	for k in range(RNG_START - LOW_MEMORY_END, RNG_END - LOW_MEMORY_END):
		world[k] = 0
	return world

static func _without_sound(globals: PackedByteArray, below_a5: int) -> PackedByteArray:
	for k in range(below_a5 + SOUND_BLOCK[0], below_a5 + SOUND_BLOCK[1]):
		globals[k] = 0
	return globals

## The first difference between two traces, in tick order: {tick, field, expected, actual}, or {}
## when they agree. A missing entry is reported as field "tick".
static func first_divergence(expected: Array, actual: Array, fields: Array = FIELDS) -> Dictionary:
	for i in maxi(expected.size(), actual.size()):
		if i >= expected.size() or i >= actual.size():
			var have: Dictionary = expected[i] if i < expected.size() else actual[i]
			return {"tick": int(have["tick"]), "field": "tick",
				"expected": expected[i]["tick"] if i < expected.size() else null,
				"actual": actual[i]["tick"] if i < actual.size() else null}
		for field in ["tick"] + fields:
			var want = int(expected[i][field]) if field == "tick" else str(expected[i][field])
			var got = int(actual[i][field]) if field == "tick" else str(actual[i][field])
			if want != got:
				return {"tick": int(expected[i]["tick"]), "field": field,
					"expected": expected[i][field], "actual": actual[i][field]}
	return {}
