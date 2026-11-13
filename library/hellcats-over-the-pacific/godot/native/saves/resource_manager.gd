extends "res://original/mac/toolbox/resource_manager.gd"

## The Native edition's saves: the Original Resource Manager (lookups, handles, the mirror) with the
## resources the game changes kept in one Godot ConfigFile (STORE) instead of Mac resource files in
## user://resources. The pilots (HEL1 128, docs/topics/PILOT_ROSTER.md) are stored as fields; the
## settings (HEL1 127, only partly mapped) and anything else the game writes (the HEL2 panel caches)
## as their exact bytes. Each converts back to the very bytes the game wrote, so the game reads
## what Original would.
##
## The player's Original saves are never written: the first time Native runs without a store, each
## resource saved there (saved_root, user://resources) is imported, read only.

const PILOTS := "HEL1:128"
const PILOT_SIZE := 0x40
const ROSTER_SIZE := 20 * 0x40
## Where the store is (tests put it elsewhere).
static var store_path: String = "user://native/saves.cfg"

var store := ConfigFile.new()

func _init(owner, memory_manager) -> void:
	super(owner, memory_manager)
	if store.load(store_path) != OK:
		import_original()

## The Original saves (saved_root) copied into a new store, which is then written; returns how many.
func import_original() -> int:
	store = ConfigFile.new()
	var count := 0
	var root := DirAccess.open(saved_root)
	if root != null:
		for folder in root.get_directories():
			var type_code: String = folder.uri_decode()
			type_code += " ".repeat(4 - type_code.length())
			for file in DirAccess.get_files_at(saved_root.path_join(folder)):
				if file.ends_with(".bin"):
					var key := "%s:%d" % [_upper_type(type_code), int(file.get_basename())]
					put(key, FileAccess.get_file_as_bytes(saved_root.path_join(folder).path_join(file)))
					count += 1
	store.set_value("store", "imported_from", saved_root if count > 0 else "")
	_save_store()
	return count

## Saved type folders are lower case; the game's types are 'HEL1', 'HEL2'.
static func _upper_type(type_code: String) -> String:
	return type_code.to_upper() if type_code.to_lower() == type_code and type_code.begins_with("hel") else type_code

func _save_store() -> void:
	DirAccess.make_dir_recursive_absolute(store_path.get_base_dir())
	store.save(store_path)

## A resource's bytes into the store: the roster as pilots, anything else as its bytes.
func put(key: String, bytes: PackedByteArray) -> void:
	if store.has_section(key):
		store.erase_section(key)
	for s in store.get_sections():
		if s.begins_with(key + "/"):
			store.erase_section(s)
	if key == PILOTS and bytes.size() == ROSTER_SIZE:
		store.set_value(key, "format", "pilots")
		for i in 20:
			var record := bytes.slice(i * PILOT_SIZE, (i + 1) * PILOT_SIZE)
			if record.count(0) == PILOT_SIZE:
				continue
			_put_pilot("%s/pilot %d" % [key, i + 1], record)
	else:
		store.set_value(key, "format", "bytes")
		store.set_value(key, "bytes", bytes.hex_encode())

func _put_pilot(section: String, r: PackedByteArray) -> void:
	var length: int = mini(r[0], 31)
	var name := ""
	for k in length:
		name += String.chr(r[1 + k] if r[1 + k] < 0x80 else Files.MAC_ROMAN_HIGH[r[1 + k] - 0x80])
	store.set_value(section, "name", name)
	var tail := r.slice(0, 0x20)
	tail[0] = 0
	for k in length:
		tail[1 + k] = 0
	if tail.count(0) != 0x20:
		store.set_value(section, "name_bytes", r.slice(0, 0x20).hex_encode())
	store.set_value(section, "status", _word(r, 0x20))
	store.set_value(section, "rank", _word(r, 0x22))
	store.set_value(section, "offences", _word(r, 0x24))
	store.set_value(section, "awards", _word(r, 0x26))
	var tallies: Array = []
	for t in 12:
		tallies.append(_word(r, 0x28 + 2 * t))
	store.set_value(section, "tallies", tallies)

static func _word(r: PackedByteArray, at: int) -> int:
	return (r[at] << 8) | r[at + 1]

## The bytes the store holds for a resource (empty when it holds none).
func get_stored(key: String) -> PackedByteArray:
	if not store.has_section(key):
		return PackedByteArray()
	if store.get_value(key, "format") == "bytes":
		return String(store.get_value(key, "bytes")).hex_decode()
	var out := PackedByteArray()
	out.resize(ROSTER_SIZE)
	for i in 20:
		var section := "%s/pilot %d" % [key, i + 1]
		if store.has_section(section):
			_get_pilot(section, out, i * PILOT_SIZE)
	return out

func _get_pilot(section: String, out: PackedByteArray, at: int) -> void:
	if store.has_section_key(section, "name_bytes"):
		var raw: PackedByteArray = String(store.get_value(section, "name_bytes")).hex_decode()
		for k in 0x20:
			out[at + k] = raw[k]
	else:
		var name: String = store.get_value(section, "name")
		out[at] = name.length()
		for k in name.length():
			var c: int = name.unicode_at(k)
			out[at + 1 + k] = c if c < 0x80 else 0x80 + Files.MAC_ROMAN_HIGH.find(c)
	var words: Array = [store.get_value(section, "status"), store.get_value(section, "rank"),
		store.get_value(section, "offences"), store.get_value(section, "awards")] + store.get_value(section, "tallies")
	for w in words.size():
		out[at + 0x20 + 2 * w] = (int(words[w]) >> 8) & 0xff
		out[at + 0x21 + 2 * w] = int(words[w]) & 0xff

## As Original's, with the store in place of the saved resource files.
func get_resource(type_code: String, id: int) -> int:
	id = _s16(id)
	var key := "%s:%d" % [type_code, id]
	if not _handles.has(key):
		var stored: PackedByteArray = get_stored(key)
		if not stored.is_empty() or store.has_section(key):
			var h: int = heap.handle_with(stored)
			heap.mark_resource(h)
			_handles[key] = h
			res_error = 0
			return h
		if FileAccess.file_exists(_saved_path(type_code, id)):
			# Never the player's Original saves: only the shipped resource.
			var folder: String = FOLDERS.get(type_code, type_code)
			if not Files.exists(folder, id):
				res_error = -192
				return 0
			var h: int = heap.handle_with(Files.load_bytes(folder, id))
			heap.mark_resource(h)
			_handles[key] = h
			res_error = 0
			return h
	return super(type_code, id)

func write(h: int) -> int:
	if not _changed.has(h):
		return 0
	for key in _handles:
		if _handles[key] == h:
			put(key, _bytes_at(memory.read_u32(h), heap.handle_size(h)))
			_save_store()
			_changed.erase(h)
			return 0
	return 0

func remove(h: int) -> int:
	for key in _handles.keys():
		if _handles[key] != h:
			continue
		put(key, PackedByteArray())
		store.erase_section(key)
		_save_store()
		_handles.erase(key)
		_changed.erase(h)
		res_error = 0
		return 0
	res_error = -192
	return 0
