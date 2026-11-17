extends "res://original/mac/toolbox/resource_manager.gd"
## Leyte Gulf's Resource Manager: the runtime's (resource_manager.gd, one map for Hellcats) with the
## resource files Leyte opens, each its own map, searched as the Resource Manager searches its chain.
##
## Files, by reference number:
## - APPLICATION_FILE (1), "Leyte Gulf": Leyte's own fork (resource_files.gd, title "leyte-gulf"),
##   with what the program writes to it saved under the title's root (save_root/application),
##   never in Hellcats' user://resources;
## - "Hellcats" (when the volume shows one): Hellcats' application fork as the import source,
##   read-only: its saved resources (hellcats_saved_root, Hellcats' user://resources) over 1.0.3's
##   shipped fork. Nothing is ever written there: AddResource / RmveResource / WriteResource on it fail
##   (addResFailed -194, rmvResFailed -196, wrPermErr -61), and DetachResource gives the program its
##   own copy of a resource's bytes;
## - the files the program creates or opens on its volume (leyte_volume.gd): a folder per file on disk
##   (<name>.rsrc/<type>/<id>.bin, map.json for the names), read and written there.
##
## OpenResFile makes the file current (and returns the reference it already has when open);
## CreateResFile never replaces an existing file (dupFNErr -48). GetResource searches from the current
## file down to the application; Get1Resource the current file only. AddResource adds to the current
## file; ChangedResource marks a handle; WriteResource / UpdateResFile / CloseResFile write a file's
## changed resources; CloseResFile then drops the file's handles from its map (a detached one stays
## the program's). UseResFile picks the current file, HomeResFile names a handle's.

const LeyteVolume := preload("res://original/leyte/machine/leyte_volume.gd")
const ResourceFiles := preload("res://assets/ui/resource_files.gd")
const HELLCATS_FORK := "res://assets"           ## 1.0.3's shipped fork (resource_files.gd ROOT)
const FIRST_FILE := 0x40
const ADD_RES_FAILED := -194
const RMV_RES_FAILED := -196
const WR_PERM_ERR := -61
const DUP_FN_ERR := -48
const RES_F_NOT_FOUND := -193
const RES_NOT_FOUND := -192
const FNF_ERR := -43
const DIR_NF_ERR := -120

## The import source's saved resources (Hellcats' own saves). Read only. Tests set a synthetic one.
static var hellcats_saved_root: String = "user://resources"

var volume
var file_manager
## reference number -> {name, dir, kind ("application" | "hellcats" | "disk"), handles {key: h}, names, changed {h: true}}
var files: Dictionary = {}
var chain: Array = []           ## reference numbers, oldest first (the application at the bottom)
var current: int = APPLICATION_FILE
var _next_ref: int = FIRST_FILE

## Leyte's own resources and their names (tools/leyte/extract_resources.py's mirror).
const LEYTE_RESOURCES := "res://assets/leyte-gulf-1.0/resources.json"

func _init(owner, memory_manager, leyte_volume = null, files_manager = null) -> void:
	super(owner, memory_manager)
	# GetNamedResource resolves the application's names from Leyte's resources, not 1.0.3's
	# (resource_names.json): a Leyte-only name (its 'snd ' "Missle") is found, as on the Mac, without case.
	_names.clear()
	for e in JSON.parse_string(FileAccess.get_file_as_string(LEYTE_RESOURCES))["resources"]:
		if e.has("name"):
			_names["%s:%s" % [e["type"], String(e["name"]).to_lower()]] = int(e["id"])
	volume = leyte_volume
	file_manager = files_manager
	files[APPLICATION_FILE] = {"name": LeyteVolume.APPLICATION, "dir": LeyteVolume.ROOT_ID, "kind": "application",
			"handles": {}, "names": {}, "changed": {}}
	chain = [APPLICATION_FILE]

func install(toolbox) -> void:
	super.install(toolbox)
	toolbox.install(0xa9a0, func(a: Array) -> int: return get_in_chain(fourcc(a[1]), a[0], false))   ## GetResource(type, id)
	toolbox.install(0xa81f, func(a: Array) -> int: return get_in_chain(fourcc(a[1]), a[0], true))    ## Get1Resource
	toolbox.install(0xa994, func(_a: Array) -> int: return current)                                  ## CurResFile
	toolbox.install(0xa998, func(a: Array) -> int: return use_res_file(a[0]))                       ## UseResFile(ref)
	toolbox.install(0xa9a4, func(a: Array) -> int: return home_res_file(a[0]))                      ## HomeResFile(h)
	toolbox.install(0xa997, func(a: Array) -> int: return open_res_file(a[0]))                      ## OpenResFile(name)
	toolbox.install(0xa9b1, func(a: Array) -> int: return create_res_file(a[0]))                    ## CreateResFile(name)
	toolbox.install(0xa99a, func(a: Array) -> int: return close_res_file(a[0]))                     ## CloseResFile(ref)
	toolbox.install(0xa999, func(a: Array) -> int: return update_res_file(a[0]))                    ## UpdateResFile(ref)
	toolbox.install(0xa9ab, func(a: Array) -> int: return add_resource(a[3], fourcc(a[2]), a[1], a[0]))  ## AddResource(h, type, id, name)
	toolbox.install(0xa9aa, func(a: Array) -> int: return changed_resource(a[0]))                   ## ChangedResource(h)
	toolbox.install(0xa9b0, func(a: Array) -> int: return write_resource(a[0]))                     ## WriteResource(h)
	toolbox.install(0xa992, func(a: Array) -> int: return detach_resource(a[0]))                    ## DetachResource(h)
	toolbox.install(0xa9ad, func(a: Array) -> int: return remove_resource(a[0]))                    ## RmveResource(h)

static func application_saved_root() -> String:
	return "%s/application" % LeyteVolume.save_root

func _disk_dir(ref: int) -> String:
	return "%s/%s.rsrc" % [volume.disk_path(files[ref]["dir"]), files[ref]["name"]]

static func _type_dir(type_code: String) -> String:
	return type_code.strip_edges().to_lower().uri_encode()

## A resource's bytes in one file, or null when the file does not hold it.
func _bytes_in(ref: int, type_code: String, id: int):
	var f: Dictionary = files[ref]
	var tail: String = "%s/%d.bin" % [_type_dir(type_code), id]
	match f["kind"]:
		"application":
			var saved: String = "%s/%s" % [application_saved_root(), tail]
			if FileAccess.file_exists(saved):
				return FileAccess.get_file_as_bytes(saved)
			var folder: String = FOLDERS.get(type_code, type_code)
			if ResourceFiles.exists(folder, id):
				return ResourceFiles.load_bytes(folder, id)
		"hellcats":
			var saved: String = "%s/%s" % [hellcats_saved_root, tail]
			if FileAccess.file_exists(saved):
				return FileAccess.get_file_as_bytes(saved)
			var shipped: String = "%s/%s/%d.bin" % [HELLCATS_FORK, String(FOLDERS.get(type_code, type_code)).to_lower(), id]
			if FileAccess.file_exists(shipped):
				return FileAccess.get_file_as_bytes(shipped)
		"disk":
			var path: String = "%s/%s" % [_disk_dir(ref), tail]
			if FileAccess.file_exists(path):
				return FileAccess.get_file_as_bytes(path)
	return null

func _load(ref: int, type_code: String, id: int) -> int:
	var key := "%s:%d" % [type_code, id]
	if files[ref]["handles"].has(key):
		return files[ref]["handles"][key]
	var bytes = _bytes_in(ref, type_code, id)
	if bytes == null:
		return 0
	var h: int = heap.handle_with(bytes)
	heap.mark_resource(h)
	files[ref]["handles"][key] = h
	return h

func get_in_chain(type_code: String, id: int, one_file: bool) -> int:
	id = _s16(id)
	var top: int = chain.find(current)
	for k in range(top, -1, -1):
		var h: int = _load(chain[k], type_code, id)
		if h != 0:
			res_error = 0
			return h
		if one_file:
			break
	res_error = RES_NOT_FOUND
	return 0

func _owner_of(h: int) -> Array:
	h &= 0xffffffff
	for ref in files:
		for key in files[ref]["handles"]:
			if files[ref]["handles"][key] == h:
				return [ref, key]
	return []

func use_res_file(ref: int) -> int:
	ref &= 0xffff
	if not files.has(ref):
		res_error = RES_F_NOT_FOUND
		return 0
	current = ref
	res_error = 0
	return 0

func home_res_file(h: int) -> int:
	var o: Array = _owner_of(h)
	if o.is_empty():
		res_error = RES_NOT_FOUND
		return 0xffff
	res_error = 0
	return o[0]

## OpenResFile(name) in the File Manager's default directory: the file made current; -1 with fnfErr
## when there is none.
func open_res_file(name_ptr: int) -> int:
	var name: String = volume.pstring(name_ptr)
	var dir_id: int = volume.dir_of(file_manager.default_vref if file_manager != null else LeyteVolume.VREF)
	if dir_id < 0:
		res_error = DIR_NF_ERR
		return 0xffff
	for ref in files:
		if files[ref]["dir"] == dir_id and String(files[ref]["name"]).to_lower() == name.to_lower():
			current = ref
			res_error = 0
			return ref
	var e: Array = volume.find(dir_id, name)
	if e.is_empty() or e[1]:
		res_error = FNF_ERR
		return 0xffff
	var kind: String = "disk"
	if dir_id == LeyteVolume.ROOT_ID and String(e[0]).to_lower() == LeyteVolume.HELLCATS.to_lower():
		kind = "hellcats"
	var ref: int = _next_ref
	_next_ref += 1
	var names := {}
	if kind == "disk":
		var map_path: String = "%s/%s.rsrc/map.json" % [volume.disk_path(dir_id), e[0]]
		if FileAccess.file_exists(map_path):
			names = JSON.parse_string(FileAccess.get_file_as_string(map_path))
	files[ref] = {"name": e[0], "dir": dir_id, "kind": kind, "handles": {}, "names": names, "changed": {}}
	chain.append(ref)
	current = ref
	res_error = 0
	return ref

## CreateResFile(name) in the default directory: an empty resource file; never over an existing file.
func create_res_file(name_ptr: int) -> int:
	var name: String = volume.pstring(name_ptr)
	var dir_id: int = volume.dir_of(file_manager.default_vref if file_manager != null else LeyteVolume.VREF)
	if dir_id < 0:
		res_error = DIR_NF_ERR
		return 0
	if not volume.find(dir_id, name).is_empty():
		res_error = DUP_FN_ERR
		return 0
	var path: String = "%s/%s.rsrc" % [volume.disk_path(dir_id), name]
	if DirAccess.make_dir_recursive_absolute(path) != OK:
		res_error = -34
		return 0
	var f := FileAccess.open("%s/map.json" % path, FileAccess.WRITE)
	f.store_string("{}")
	f.close()
	res_error = 0
	return 0

func _writable(ref: int) -> bool:
	return files[ref]["kind"] != "hellcats"

func add_resource(h: int, type_code: String, id: int, name_ptr: int) -> int:
	h &= 0xffffffff
	if not _writable(current):
		res_error = ADD_RES_FAILED
		return 0
	var key := "%s:%d" % [type_code, _s16(id)]
	files[current]["handles"][key] = h
	files[current]["names"][key] = volume.pstring(name_ptr) if name_ptr != 0 else ""
	files[current]["changed"][h] = true
	heap.mark_resource(h)
	res_error = 0
	return 0

func changed_resource(h: int) -> int:
	var o: Array = _owner_of(h)
	if o.is_empty():
		res_error = RES_NOT_FOUND
		return 0
	if not _writable(o[0]):
		res_error = WR_PERM_ERR
		return 0
	files[o[0]]["changed"][h & 0xffffffff] = true
	res_error = 0
	return 0

func _write_one(ref: int, key: String, h: int) -> int:
	var dir: String
	match files[ref]["kind"]:
		"application":
			dir = application_saved_root()
		"disk":
			dir = _disk_dir(ref)
		_:
			return WR_PERM_ERR
	var type_code: String = key.substr(0, 4)
	var path: String = "%s/%s/%s.bin" % [dir, _type_dir(type_code), key.substr(5)]
	DirAccess.make_dir_recursive_absolute(path.get_base_dir())
	var f := FileAccess.open(path, FileAccess.WRITE)
	if f == null:
		return -34
	f.store_buffer(_bytes_at(memory.read_u32(h), heap.handle_size(h)))
	f.close()
	if files[ref]["kind"] == "disk":
		var m := FileAccess.open("%s/map.json" % dir, FileAccess.WRITE)
		m.store_string(JSON.stringify(files[ref]["names"]))
		m.close()
	files[ref]["changed"].erase(h)
	return 0

func write_resource(h: int) -> int:
	var o: Array = _owner_of(h)
	if o.is_empty():
		res_error = RES_NOT_FOUND
		return 0
	if not files[o[0]]["changed"].has(h & 0xffffffff):
		res_error = 0
		return 0
	res_error = _write_one(o[0], o[1], h & 0xffffffff)
	return 0

func update_res_file(ref: int) -> int:
	ref &= 0xffff
	if not files.has(ref):
		res_error = RES_F_NOT_FOUND
		return 0
	res_error = 0
	for key in files[ref]["handles"]:
		var h: int = files[ref]["handles"][key]
		if files[ref]["changed"].has(h):
			var err: int = _write_one(ref, key, h)
			if err != 0:
				res_error = err
	return 0

func close_res_file(ref: int) -> int:
	ref &= 0xffff
	if not files.has(ref) or ref == APPLICATION_FILE:
		res_error = RES_F_NOT_FOUND if not files.has(ref) else 0
		return 0
	update_res_file(ref)
	files.erase(ref)
	chain.erase(ref)
	if current == ref:
		current = chain[chain.size() - 1]
	return 0

## DetachResource: the handle the program's own (its bytes already a copy in the heap), no longer in
## its file's map.
func detach_resource(h: int) -> int:
	var o: Array = _owner_of(h)
	if o.is_empty():
		res_error = RES_NOT_FOUND
		return 0
	files[o[0]]["handles"].erase(o[1])
	files[o[0]]["changed"].erase(h & 0xffffffff)
	res_error = 0
	return 0

func remove_resource(h: int) -> int:
	var o: Array = _owner_of(h)
	if o.is_empty():
		res_error = RES_NOT_FOUND
		return 0
	if not _writable(o[0]):
		res_error = RMV_RES_FAILED
		return 0
	files[o[0]]["handles"].erase(o[1])
	files[o[0]]["changed"].erase(h & 0xffffffff)
	res_error = 0
	return 0
