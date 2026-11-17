extends "res://tests/original/mac/test_unexported_routines.gd"

## The routines the runtime writes for Leyte Gulf (original/leyte/machine/leyte_runtime.gd), checked
## against Leyte's original bytes: tools/leyte/gen_leyte_runtime_cases.py runs each on the m68k
## interpreter (fixtures/runtime_cases.json); here the same memory goes into a machine the Leyte
## profile builds, and the runtime's routine must return, leave and call the same, as
## test_unexported_routines.gd checks 1.0.3's (its call logging is used as it is). The profile's
## storage is a temporary root of this test's own (never user://leyte-gulf or user://resources),
## removed afterwards.

const LEYTE_FIXTURE := "res://tests/leyte/fixtures/runtime_cases.json"
const LeyteProfile := preload("res://original/leyte/machine/leyte_profile.gd")
const LeyteVolume := preload("res://original/leyte/machine/leyte_volume.gd")
const LeyteResources := preload("res://original/leyte/machine/leyte_resources.gd")
const TEST_ROOT := "user://leyte-runtime-test"

static func _wipe(path: String) -> void:
	var d := DirAccess.open(path)
	if d == null:
		return
	for f in d.get_files():
		d.remove(f)
	for sub in d.get_directories():
		_wipe("%s/%s" % [path, sub])
		d.remove(sub)

func run() -> bool:
	var saved_root: String = LeyteVolume.save_root
	var saved_hellcats: String = LeyteResources.hellcats_saved_root
	_wipe(TEST_ROOT)
	LeyteVolume.save_root = TEST_ROOT + "/leyte-gulf"
	LeyteResources.hellcats_saved_root = TEST_ROOT + "/hellcats-saved"
	LeyteProfile.apply(true)
	var ok := true
	var machine = load("res://original/mac/machine/mac_machine.gd").new()
	var runtime = machine.get_meta("leyte_runtime")
	var fixture: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(LEYTE_FIXTURE))
	var mismatches: int = 0
	for c in fixture["cases"]:
		if c.get("zero_stack", false):
			var top: int = machine.free_stack()
			for at in range(top - 0x1000, top, 4):
				machine.memory.write_u32(at, 0)
		for w in c["writes"]:
			match int(w[1]):
				1: machine.memory.write_u8(int(w[0]), int(w[2]))
				2: machine.memory.write_u16(int(w[0]), int(w[2]))
				_: machine.memory.write_u32(int(w[0]), int(w[2]))
		var args: Array = c["args"].map(func(v): return int(v))
		var calls: Array = []
		var restore: Dictionary = _log_calls(machine, c, calls)
		var d0_mask: int = {1: 0xff, 2: 0xffff}.get(int(c.get("d0_size", 4)), 0xffffffff)
		var got: int = int(runtime.callv(String(c["routine"]), args)) & d0_mask
		_unlog_calls(machine, restore)
		var expected: Array = c.get("calls", []).map(func(e):
			var row: Array = [String(e[0])]
			for k in range(1, e.size()):
				row.append(int(e[k]))
			return row)
		if c.has("calls") and calls != expected:
			mismatches += 1
			print("  %s: runtime calls %s, original %s" % [c["name"], calls, expected])
		if c.has("d0") and got != int(c["d0"]):
			mismatches += 1
			print("  %s: runtime D0 %x, original %x" % [c["name"], got, int(c["d0"])])
		for a in c["after"]:
			var at: int = int(a[0])
			var value: int
			match int(a[1]):
				1: value = machine.memory.read_u8(at)
				2: value = machine.memory.read_u16(at)
				_: value = machine.memory.read_u32(at)
			if value != int(a[2]):
				mismatches += 1
				print("  %s: runtime leaves %x at %x, original %x" % [c["name"], value, at, int(a[2])])
	ok = Assertions.assert_equal(mismatches, 0, "%d Leyte runtime routine cases match the original" % fixture["cases"].size()) and ok
	LeyteProfile.clear()
	LeyteVolume.save_root = saved_root
	LeyteResources.hellcats_saved_root = saved_hellcats
	_wipe(TEST_ROOT)
	DirAccess.remove_absolute(TEST_ROOT)
	return ok
