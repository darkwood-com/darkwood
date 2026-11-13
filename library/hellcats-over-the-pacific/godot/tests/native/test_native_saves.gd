extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const ResourceManager := preload("res://original/mac/toolbox/resource_manager.gd")
const NativeResources := preload("res://native/saves/resource_manager.gd")
const Files := preload("res://assets/ui/resource_files.gd")

const ROOT := "user://test_native_saves"
const ORIGINAL := ROOT + "/original"
const STORE := ROOT + "/native.cfg"

## Native saves, in isolated storage: Original save files (a roster with a Mac Roman name and stray
## bytes after a name, changed settings, a panel cache) are imported into the Native store, read back
## as the same bytes Original reads, written and read again exactly, kept as readable pilot fields,
## and never modified.
func run() -> bool:
	var saved_root: String = ResourceManager.saved_root
	var saved_store: String = NativeResources.store_path
	_clear(ROOT)
	ResourceManager.saved_root = ORIGINAL
	NativeResources.store_path = STORE
	var files := {"HEL1:128": _roster(), "HEL1:127": _settings(), "HEL2:200": _cache()}
	for key in files:
		var path := "%s/%s/%s.bin" % [ORIGINAL, key.substr(0, 4).to_lower(), key.substr(5)]
		DirAccess.make_dir_recursive_absolute(path.get_base_dir())
		var f := FileAccess.open(path, FileAccess.WRITE)
		f.store_buffer(files[key])
		f.close()
	var before: Dictionary = _hashes()
	var original = _machine(false)
	var native = _machine(true)
	var ok := Assertions.assert_true(FileAccess.file_exists(STORE), "the store is created by importing")
	for key in files:
		var want: PackedByteArray = _read(original, key)
		ok = Assertions.assert_equal(want, files[key], "%s: Original reads its save" % key) and ok
		ok = Assertions.assert_equal(_read(native, key), want, "%s: Native reads the same bytes" % key) and ok
	var cfg := ConfigFile.new()
	cfg.load(STORE)
	ok = Assertions.assert_equal([cfg.get_value("HEL1:128/pilot 1", "name"), cfg.get_value("HEL1:128/pilot 1", "rank"),
		cfg.get_value("HEL1:128/pilot 3", "name")], ["ace", 3, "Pérez"], "pilots stored as fields") and ok
	# The game changes pilot 2's first tally and writes the roster.
	var h: int = native.toolbox.resources.get_resource("HEL1", 128)
	var p: int = native.memory.read_u32(h)
	native.memory.write_u16(p + 0x40 + 0x28, 0x1234)
	native.toolbox.resources._changed[h] = true
	native.toolbox.resources.write(h)
	var again = _machine(true)
	var changed: PackedByteArray = files["HEL1:128"].duplicate()
	changed[0x40 + 0x28] = 0x12
	changed[0x40 + 0x29] = 0x34
	ok = Assertions.assert_equal(_read(again, "HEL1:128"), changed, "the written roster read back exactly") and ok
	ok = Assertions.assert_equal(_hashes(), before, "the Original save files are untouched") and ok
	ResourceManager.saved_root = saved_root
	NativeResources.store_path = saved_store
	_clear(ROOT)
	return ok

func _machine(native: bool):
	Toolbox.overrides = {"resources": NativeResources} if native else {}
	var m = MacMachine.new()
	Toolbox.overrides = {}
	return m

func _read(m, key: String) -> PackedByteArray:
	var h: int = m.toolbox.resources.get_resource(key.substr(0, 4), int(key.substr(5)))
	return m.memory._bytes.slice(m.memory.read_u32(h), m.memory.read_u32(h) + m.toolbox.heap.handle_size(h))

func _roster() -> PackedByteArray:
	var r := PackedByteArray()
	r.resize(0x500)
	_pilot(r, 0, "ace".to_ascii_buffer(), [0, 3, 0, 5], 7)
	_pilot(r, 1, "bob".to_ascii_buffer(), [0, 1, 1, 0], 2)
	_pilot(r, 2, PackedByteArray([0x50, 0x8e, 0x72, 0x65, 0x7a]), [2, 0, 0, 0], 0)
	r[2 * 0x40 + 0x1f] = 0x99          # stray byte after the name, kept exactly
	return r

func _pilot(r: PackedByteArray, i: int, name: PackedByteArray, words: Array, tally: int) -> void:
	r[i * 0x40] = name.size()
	for k in name.size():
		r[i * 0x40 + 1 + k] = name[k]
	for w in 4:
		r[i * 0x40 + 0x21 + 2 * w] = words[w]
	r[i * 0x40 + 0x29] = tally

func _settings() -> PackedByteArray:
	var s: PackedByteArray = Files.load_bytes("HEL1", 127)
	s[0x0b] = 1
	return s

func _cache() -> PackedByteArray:
	var c := PackedByteArray()
	for k in 300:
		c.append((k * 31) & 0xff)
	return c

func _hashes() -> Dictionary:
	var out := {}
	for folder in DirAccess.get_directories_at(ORIGINAL):
		for f in DirAccess.get_files_at(ORIGINAL.path_join(folder)):
			out[folder + "/" + f] = FileAccess.get_md5(ORIGINAL.path_join(folder).path_join(f))
	return out

func _clear(path: String) -> void:
	var dir := DirAccess.open(path)
	if dir == null:
		return
	for sub in dir.get_directories():
		_clear(path.path_join(sub))
	for file in dir.get_files():
		dir.remove(file)
	DirAccess.remove_absolute(path)
