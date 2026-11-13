extends RefCounted

## The Native overlay keeps some blocks in Godot instead of the Mac memory (its native_state_ranges:
## the replay camera, the camera angles and position). A differential test seeds those blocks from the scenario's
## bytes, overwrites their Mac bytes with 0xa5 (so a read falling back to the memory shows), and then
## compares the Original's memory with the Native memory plus the Godot-held values; the Native Mac
## bytes of the blocks must still be 0xa5 (nothing written there).

static func seed_and_poison(native, memory) -> void:
	if native.replay_camera_storage_godot:
		native.replay_camera().seed_from_memory_for_test()
	for b in native.native_blocks():
		b.seed_from_memory_for_test()
	var poisoned: Array = native.native_state_ranges()
	for part in poisoned:
		for i in part[1].size():
			memory.write_u8(part[0] + i, 0xa5)
	native.set_meta("poisoned", poisoned.map(func(p) -> Array: return [p[0], p[1].size()]))

## "" when they agree, else what differs.
static func compare(native, original_memory, native_memory) -> String:
	for r in native.get_meta("poisoned", []):
		for i in r[1]:
			if native_memory.read_u8(r[0] + i) != 0xa5:
				return "Mac bytes of a Godot-held block written at 0x%x" % (r[0] + i)
	var held: PackedByteArray = native_memory._bytes.duplicate()
	for part in native.native_state_ranges():
		for i in part[1].size():
			held[part[0] + i] = part[1][i]
	return "" if held == original_memory._bytes else "memory or Godot-held state"
