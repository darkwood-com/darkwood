extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Files := preload("res://assets/ui/resource_files.gd")

## Version-aware resources: 1.0.3 by default; with version 1.0.4 the resources its updater changes or
## adds (sources/hellcats-1-0-4) come from godot/assets/hellcats-1.0.4, every other one from the
## 1.0.3 mirror.
func run() -> bool:
	var saved: String = Files.version
	Files.version = "1.0.3"
	var gun_103: PackedByteArray = Files.load_bytes("snds", 25233)
	var ok := Assertions.assert_equal(gun_103.size(), 2947, "1.0.3 Gun")
	ok = Assertions.assert_equal(Files.exists("SIZE", 1), false, "1.0.3 has no SIZE 1") and ok
	var dlog_103: PackedByteArray = Files.load_bytes("DLOG", 128)
	Files.version = "1.0.4"
	ok = Assertions.assert_equal(Files.load_bytes("snds", 25233).size(), 6226, "1.0.4 Gun") and ok
	ok = Assertions.assert_equal(Files.exists("SIZE", 1), true, "1.0.4 adds SIZE 1") and ok
	ok = Assertions.assert_equal(Files.load_bytes("DLOG", 128), dlog_103, "unchanged resources shared") and ok
	var vers: PackedByteArray = Files.load_bytes("vers", 1)
	ok = Assertions.assert_equal([vers[0], vers[1]], [1, 0x04], "vers 1 says 1.0.4") and ok
	Files.version = saved
	ok = Assertions.assert_equal(Files.load_bytes("snds", 25233), gun_103, "back to 1.0.3") and ok
	return ok
