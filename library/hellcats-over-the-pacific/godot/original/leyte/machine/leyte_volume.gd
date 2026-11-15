extends RefCounted
## Leyte Gulf's volume: the one disk the runtime's Leyte profile shows the program, sandboxed under a
## title-specific save root (save_root, "user://leyte-gulf" by default; tests use a temporary one). Its
## hierarchical layout is what Leyte's code walks (CODE 2 0x2f42, open_preferences_file): the volume
## root (dirID 2) holds the application "Leyte Gulf" and, when an import source is given, "Hellcats"
## (both read-only, never written); the root's "System Folder" (dirID 3) holds "Preferences" (dirID 4),
## as a System 7 Mac's does. Files the program creates there live on disk under the save root.
##
## Working directories are as HFS keeps them: a reference number for a (directory, procID) pair, one
## reused when the same pair is opened again; the System Folder's is SysEnvirons' sysVRefNum. The three
## HFS calls Leyte makes (PBGetWDInfo, PBGetCatInfo, PBOpenWD; $A260 selectors 7 / 9 / 1) read and write
## their parameter blocks at the offsets Inside Macintosh gives, set ioResult (+0x10) and return the
## result: nsvErr (-35) for an unknown volume or working directory, dirNFErr (-120) for an unknown
## directory, fnfErr (-43) for a name not in the directory.

const VREF := 0xffff               ## the volume's reference number (the File Manager's VREF)
const ROOT_ID := 2                 ## fsRtDirID
const SYSTEM_ID := 3
const PREFERENCES_ID := 4
const FIRST_WD := 0x8001
const NO_ERR := 0
const NSV_ERR := -35
const FNF_ERR := -43
const DIR_NF_ERR := -120
const APPLICATION := "Leyte Gulf"
const HELLCATS := "Hellcats"

static var save_root: String = "user://leyte-gulf"

var memory
var volume_name: String
## dirID -> {name, parent, path (relative to the volume's disk folder)}
var dirs: Dictionary = {}
## Read-only files in the root, served by the Resource Manager (not on disk): lower-cased name -> name.
var read_only: Dictionary = {}
## Working directories: reference number -> {dir, proc}; shared with the File Manager (SetVol).
var wds: Dictionary = {}
var _next_wd: int = FIRST_WD
var sys_vref: int = 0

func _init(memory_68k, name: String = "Leyte", with_hellcats: bool = true) -> void:
	memory = memory_68k
	volume_name = name
	dirs[ROOT_ID] = {"name": name, "parent": 1, "path": ""}
	dirs[SYSTEM_ID] = {"name": "System Folder", "parent": ROOT_ID, "path": "System Folder"}
	dirs[PREFERENCES_ID] = {"name": "Preferences", "parent": SYSTEM_ID, "path": "System Folder/Preferences"}
	read_only[APPLICATION.to_lower()] = APPLICATION
	if with_hellcats:
		read_only[HELLCATS.to_lower()] = HELLCATS
	DirAccess.make_dir_recursive_absolute(disk_path(PREFERENCES_ID))
	sys_vref = open_wd(SYSTEM_ID, 0x45524b4e)          # the System Folder's ('ERKN': the Finder's own procID)

## The volume's folder on disk, and a directory's.
static func volume_root() -> String:
	return "%s/volume" % save_root

func disk_path(dir_id: int) -> String:
	var rel: String = dirs[dir_id]["path"]
	return volume_root() if rel == "" else "%s/%s" % [volume_root(), rel]

## The directory a (vRefNum, dirID) pair names: dirID when nonzero, else the working directory's, else
## the root for the volume itself; -1 when the volume or directory is unknown.
func dir_of(vref: int, dir_id: int = 0) -> int:
	vref &= 0xffff
	if dir_id != 0:
		return dir_id if dirs.has(dir_id) else -1
	if wds.has(vref):
		return wds[vref]["dir"]
	if vref == VREF or vref == 0:
		return ROOT_ID
	return -1

func known_volume(vref: int) -> bool:
	vref &= 0xffff
	return vref == VREF or vref == 0 or wds.has(vref)

## A working directory on dir for procID: the existing one for that pair, else a new reference.
func open_wd(dir_id: int, proc: int) -> int:
	for ref in wds:
		if wds[ref]["dir"] == dir_id and wds[ref]["proc"] == proc:
			return ref
	var ref: int = _next_wd
	_next_wd += 1
	wds[ref] = {"dir": dir_id, "proc": proc}
	return ref

## A directory's entries, sorted by name: [name, is_directory, dirID or 0].
func entries(dir_id: int) -> Array:
	var out: Array = []
	for id in dirs:
		if dirs[id]["parent"] == dir_id:
			out.append([dirs[id]["name"], true, id])
	if dir_id == ROOT_ID:
		for key in read_only:
			out.append([read_only[key], false, 0])
	var d := DirAccess.open(disk_path(dir_id))
	if d != null:
		for f in d.get_directories():
			if f.ends_with(".rsrc"):
				out.append([f.trim_suffix(".rsrc"), false, 0])
	out.sort_custom(func(a, b): return String(a[0]).to_lower() < String(b[0]).to_lower())
	return out

func find(dir_id: int, name: String) -> Array:
	for e in entries(dir_id):
		if String(e[0]).to_lower() == name.to_lower():
			return e
	return []

func pstring(at: int) -> String:
	var n: int = memory.read_u8(at)
	var bytes := PackedByteArray()
	for k in n:
		bytes.append(memory.read_u8(at + 1 + k))
	return bytes.get_string_from_ascii()

func _put_pstring(at: int, text: String) -> void:
	memory.write_u8(at, text.length())
	for k in text.length():
		memory.write_u8(at + 1 + k, text.unicode_at(k))

func _done(pb: int, err: int) -> int:
	memory.write_u16(pb + 0x10, err & 0xffff)
	return err & 0xffff

## PBGetWDInfo: ioVRefNum (+0x16) a working directory (or the volume), ioWDIndex (+0x1a) 0; or the
## index-th working directory. Out: ioWDVRefNum (+0x20) the volume, ioWDDirID (+0x30) the directory,
## ioWDProcID (+0x1c), ioVRefNum for an indexed call, and the volume's name through a non-nil ioNamePtr.
func pb_get_wd_info(pb: int) -> int:
	var index: int = memory.read_u16(pb + 0x1a)
	var ref: int = memory.read_u16(pb + 0x16)
	var dir_id: int = ROOT_ID
	var proc: int = 0
	if index > 0:
		var refs: Array = wds.keys()
		refs.sort()
		if index > refs.size():
			return _done(pb, NSV_ERR)
		ref = refs[index - 1]
		memory.write_u16(pb + 0x16, ref)
	if wds.has(ref):
		dir_id = wds[ref]["dir"]
		proc = wds[ref]["proc"]
	elif not known_volume(ref):
		return _done(pb, NSV_ERR)
	memory.write_u16(pb + 0x20, VREF)
	memory.write_u32(pb + 0x30, dir_id)
	memory.write_u32(pb + 0x1c, proc)
	var name_ptr: int = memory.read_u32(pb + 0x12)
	if name_ptr != 0:
		_put_pstring(name_ptr, volume_name)
	return _done(pb, NO_ERR)

## PBGetCatInfo: in ioVRefNum (+0x16), ioDirID (+0x30), ioFDirIndex (+0x1c, a word): 0 the entry named
## by ioNamePtr (+0x12), above 0 the index-th entry (its name returned through ioNamePtr), below 0
## the directory itself. Out: ioFlAttrib (+0x1e, bit 4 for a directory), and for a directory ioDrDirID
## (+0x30) and ioDrNmFls (+0x34), for a file ioFlNum (+0x30, 0 here); ioDrParID / ioFlParID (+0x64).
func pb_get_cat_info(pb: int) -> int:
	var dir_id: int = dir_of(memory.read_u16(pb + 0x16), memory.read_u32(pb + 0x30))
	if dir_id < 0:
		return _done(pb, DIR_NF_ERR if known_volume(memory.read_u16(pb + 0x16)) else NSV_ERR)
	var index: int = memory.read_u16(pb + 0x1c)
	index = index - 0x10000 if index >= 0x8000 else index
	var name_ptr: int = memory.read_u32(pb + 0x12)
	var entry: Array
	var parent: int = dir_id
	if index < 0:
		entry = [dirs[dir_id]["name"], true, dir_id]
		parent = dirs[dir_id]["parent"]
	elif index > 0:
		var all: Array = entries(dir_id)
		if index > all.size():
			return _done(pb, FNF_ERR)
		entry = all[index - 1]
		if name_ptr != 0:
			_put_pstring(name_ptr, entry[0])
	else:
		if name_ptr == 0:
			return _done(pb, FNF_ERR)
		entry = find(dir_id, pstring(name_ptr))
		if entry.is_empty():
			return _done(pb, FNF_ERR)
	if entry[1]:
		memory.write_u8(pb + 0x1e, 0x10)
		memory.write_u32(pb + 0x30, entry[2])
		memory.write_u16(pb + 0x34, entries(entry[2]).size())
	else:
		memory.write_u8(pb + 0x1e, 0)
		memory.write_u32(pb + 0x30, 0)
	memory.write_u32(pb + 0x64, parent)
	return _done(pb, NO_ERR)

## PBOpenWD: ioVRefNum (+0x16), ioWDDirID (+0x30), ioWDProcID (+0x1c), ioNamePtr (+0x12: nil, or a
## directory's name inside that one). Out: ioVRefNum = the working directory's reference number.
func pb_open_wd(pb: int) -> int:
	var vref: int = memory.read_u16(pb + 0x16)
	if not known_volume(vref):
		return _done(pb, NSV_ERR)
	var dir_id: int = dir_of(vref, memory.read_u32(pb + 0x30))
	if dir_id < 0:
		return _done(pb, DIR_NF_ERR)
	var name_ptr: int = memory.read_u32(pb + 0x12)
	if name_ptr != 0 and memory.read_u8(name_ptr) != 0:
		var e: Array = find(dir_id, pstring(name_ptr))
		if e.is_empty() or not e[1]:
			return _done(pb, DIR_NF_ERR)
		dir_id = e[2]
	memory.write_u16(pb + 0x16, open_wd(dir_id, memory.read_u32(pb + 0x1c)))
	return _done(pb, NO_ERR)
