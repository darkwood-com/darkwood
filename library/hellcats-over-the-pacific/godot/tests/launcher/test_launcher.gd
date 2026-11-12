extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Launcher := preload("res://launcher/launcher.gd")

## Edition selection: Original by default, the three names accepted, anything else refused; the
## main scene carries the Original edition's scene.
func run() -> bool:
	var ok := Assertions.assert_equal(Launcher.edition_from(PackedStringArray()), "original", "default edition")
	for name in ["original", "native", "enhanced"]:
		ok = Assertions.assert_equal(Launcher.edition_from(PackedStringArray(["--edition=" + name])), name, name) and ok
	ok = Assertions.assert_equal(Launcher.edition_from(PackedStringArray(["--edition=strict"])), "", "unknown edition") and ok
	ok = Assertions.assert_equal(Launcher.version_from(PackedStringArray()), "1.0.3", "default version") and ok
	ok = Assertions.assert_equal(Launcher.version_from(PackedStringArray(["--version=1.0.4"])), "1.0.4", "1.0.4") and ok
	ok = Assertions.assert_equal(Launcher.version_from(PackedStringArray(["--version=2.0"])), "", "unknown version") and ok
	var main: Node = load("res://launcher/Main.tscn").instantiate()
	ok = Assertions.assert_equal(main.original.resource_path, "res://original/Game.tscn", "Original scene") and ok
	ok = Assertions.assert_equal(main.native.resource_path, "res://native/Game.tscn", "Native scene") and ok
	main.free()
	return ok
