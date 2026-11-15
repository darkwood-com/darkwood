extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Files := preload("res://assets/ui/resource_files.gd")

## The Leyte Gulf title's resources (the first stage of its profile): with the title "leyte-gulf"
## every resource of Leyte's fork loads with the bytes extracted from its disk image (the mirror's
## SHA-256), and a resource only Hellcats 1.0.3 has is absent (no fallback); the title restored
## afterwards leaves Hellcats' resources as they were.

static func _sha256(bytes: PackedByteArray) -> String:
	var ctx := HashingContext.new()
	ctx.start(HashingContext.HASH_SHA256)
	ctx.update(bytes)
	return ctx.finish().hex_encode()

func run() -> bool:
	var manifest: Dictionary = JSON.parse_string(FileAccess.get_file_as_string("res://assets/leyte-gulf-1.0/resources.json"))
	var hellcats_hel1: PackedByteArray = Files.load_bytes("HEL1", 128)
	Files.title = "leyte-gulf"
	var bad: Array = []
	for r in manifest["resources"]:
		var bytes: PackedByteArray = Files.load_bytes(String(r["folder"]), int(r["id"]))
		if bytes.is_empty() or _sha256(bytes) != String(r["sha256"]):
			bad.append("%s %d" % [r["type"], r["id"]])
	var ok := Assertions.assert_equal(bad, [], "every Leyte resource loads with its extracted bytes (%d)" % manifest["resources"].size())
	ok = Assertions.assert_true(Files.load_bytes("HELL", 0).is_empty() and not Files.exists("HELL", 0),
			"a resource only Hellcats has (HELL 0) is absent under Leyte") and ok
	ok = Assertions.assert_true(Files.load_bytes("DLOG", 128).size() > 0, "Leyte's DLOG 128 loads") and ok
	Files.title = "hellcats"
	ok = Assertions.assert_equal(Files.load_bytes("HEL1", 128), hellcats_hel1, "the title restored: Hellcats' HEL1 128 as before") and ok
	print(("PASS" if ok else "FAIL") + " Leyte Gulf resources")
	return ok
