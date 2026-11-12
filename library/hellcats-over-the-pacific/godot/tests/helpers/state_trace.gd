extends RefCounted

## State traces of a stepped run (MacMachine.use_stepped_clock) and their comparison: the
## differential check Native will be held to against Original. A trace is one entry per traced tick:
## the stepped tick, MD5s of the A5 world and of the overlay's image (its data: the world's objects,
## at base 0), and the overlay's random-number state exactly (overlay_rng_next's slot pointer at
## 0x26008 and its table up to 0x260e8). The screen is left out: its text comes from the host's fonts.
## Two fields leave out what an edition may keep elsewhere: "game_globals" is the A5 world with the
## sound driver's block (A5 - 0xc38 .. A5 - 0x34e) zeroed, "world" the overlay's image above the
## low-memory globals (from 0x2800); the Native edition is compared on those and "rng".

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
		"rng": bytes.slice(RNG_START, RNG_END).hex_encode(),
		"game_globals": _without_sound(bytes.slice(m.A5 - m.BELOW_A5, m.A5), m.BELOW_A5).hex_encode().md5_text(),
		"world": bytes.slice(LOW_MEMORY_END, OVERLAY_END).hex_encode().md5_text()}

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
