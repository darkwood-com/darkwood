extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68kScript := preload("res://original/compat/memory_68k.gd")
const MemoryManagerScript := preload("res://original/mac/toolbox/memory_manager.gd")
const FileManagerScript := preload("res://original/mac/toolbox/file_manager.gd")
const LeyteVolume := preload("res://original/leyte/machine/leyte_volume.gd")
const LeyteResources := preload("res://original/leyte/machine/leyte_resources.gd")
const ResourceFiles := preload("res://assets/ui/resource_files.gd")

## Leyte Gulf's files on the runtime (original/leyte/machine/leyte_volume.gd, leyte_resources.gd), on a
## temporary save root and a synthetic Hellcats save only: never the player's user://leyte-gulf or
## user://resources. The HFS calls (PBGetWDInfo, PBGetCatInfo, PBOpenWD) and their errors; the
## preferences file created (and never replaced: dupFNErr), written, closed and read back after a
## restart; Leyte's writes kept in its files; the Hellcats file read-only (adds, removals and writes
## refused, its bytes unchanged on disk); a detached import surviving CloseResFile.

const ROOT := "user://leyte-files-test"
const HELL := 0x48454c4c
const HEL1 := 0x48454c31

class Owner:
	var memory
	var HEAP_START: int = 0x100000
	var HEAP_END: int = 0x380000
	var volume_name: String = "Leyte"
	func _init() -> void:
		memory = Memory68kScript.new(0x400000)
	func note_missing(_key: String, _detail: String) -> void:
		pass

class Traps:
	var traps := {}
	func install(word: int, fn: Callable) -> void:
		traps[word] = fn
	func call_trap(word: int, args: Array) -> int:
		return int(traps[word].call(args))

static func _wipe(path: String) -> void:
	var d := DirAccess.open(path)
	if d == null:
		return
	for f in d.get_files():
		d.remove(f)
	for sub in d.get_directories():
		_wipe("%s/%s" % [path, sub])
		d.remove(sub)

var owner
var traps
var volume
var resources
var files_manager

func _boot(with_hellcats: bool = true) -> void:
	owner = Owner.new()
	var mm = MemoryManagerScript.new(owner)
	files_manager = FileManagerScript.new(owner)
	volume = LeyteVolume.new(owner.memory, "Leyte", with_hellcats)
	files_manager.wd_refs = volume.wds
	resources = LeyteResources.new(owner, mm, volume, files_manager)
	traps = Traps.new()
	mm.install(traps, {})
	resources.install(traps)

func _pstr(at: int, text: String) -> int:
	owner.memory.write_u8(at, text.length())
	for k in text.length():
		owner.memory.write_u8(at + 1 + k, text.unicode_at(k))
	return at

func run() -> bool:
	var saved_root: String = LeyteVolume.save_root
	var saved_hellcats: String = LeyteResources.hellcats_saved_root
	var saved_title: String = ResourceFiles.title
	_wipe(ROOT)
	LeyteVolume.save_root = ROOT + "/leyte-gulf"
	LeyteResources.hellcats_saved_root = ROOT + "/hellcats-saved"
	ResourceFiles.title = "leyte-gulf"
	# A synthetic Hellcats save: 'HEL1' 127 of 0x12a bytes.
	var hel1_127 := PackedByteArray()
	for k in 0x12a:
		hel1_127.append((k * 5 + 1) & 0xff)
	DirAccess.make_dir_recursive_absolute(ROOT + "/hellcats-saved/hel1")
	var f := FileAccess.open(ROOT + "/hellcats-saved/hel1/127.bin", FileAccess.WRITE)
	f.store_buffer(hel1_127)
	f.close()
	var ok := _hfs() and true
	ok = _first_start() and ok
	ok = _restart() and ok
	ok = _no_hellcats() and ok
	var after := FileAccess.get_file_as_bytes(ROOT + "/hellcats-saved/hel1/127.bin")
	ok = Assertions.assert_true(after == hel1_127, "the Hellcats save unchanged on disk") and ok
	var stray := DirAccess.get_directories_at(ROOT + "/hellcats-saved")
	ok = Assertions.assert_equal(Array(stray), ["hel1"], "nothing new written in the Hellcats save") and ok
	LeyteVolume.save_root = saved_root
	LeyteResources.hellcats_saved_root = saved_hellcats
	ResourceFiles.title = saved_title
	_wipe(ROOT)
	DirAccess.remove_absolute(ROOT)                    # the temporary root itself, now empty
	return ok

func _hfs() -> bool:
	_boot()
	var m = owner.memory
	var ok := true
	var pb := 0x200000
	# PBGetWDInfo on SysEnvirons' sysVRefNum: the System Folder.
	m.write_u16(pb + 0x16, volume.sys_vref)
	m.write_u16(pb + 0x1a, 0)
	m.write_u32(pb + 0x12, 0)
	ok = Assertions.assert_equal(volume.pb_get_wd_info(pb), 0, "PBGetWDInfo(sysVRefNum)") and ok
	ok = Assertions.assert_equal([m.read_u16(pb + 0x20), m.read_u32(pb + 0x30), m.read_u16(pb + 0x10)], [0xffff, 3, 0], "the System Folder's volume and dirID, ioResult") and ok
	m.write_u16(pb + 0x16, 0x1234)
	ok = Assertions.assert_equal(volume.pb_get_wd_info(pb), (-35) & 0xffff, "PBGetWDInfo(unknown): nsvErr") and ok
	# PBGetCatInfo "Preferences" in the System Folder.
	var cpb := 0x200100
	m.write_u32(cpb + 0x12, _pstr(0x200400, "Preferences"))
	m.write_u16(cpb + 0x16, 0xffff)
	m.write_u16(cpb + 0x1c, 0)
	m.write_u32(cpb + 0x30, 3)
	ok = Assertions.assert_equal(volume.pb_get_cat_info(cpb), 0, "PBGetCatInfo(Preferences)") and ok
	ok = Assertions.assert_equal([m.read_u8(cpb + 0x1e), m.read_u32(cpb + 0x30), m.read_u32(cpb + 0x64)], [0x10, 4, 3], "a directory, dirID 4, parent 3") and ok
	m.write_u32(cpb + 0x12, _pstr(0x200400, "Extensions"))
	m.write_u32(cpb + 0x30, 3)
	ok = Assertions.assert_equal(volume.pb_get_cat_info(cpb), (-43) & 0xffff, "PBGetCatInfo(missing): fnfErr") and ok
	m.write_u32(cpb + 0x30, 99)
	ok = Assertions.assert_equal(volume.pb_get_cat_info(cpb), (-120) & 0xffff, "PBGetCatInfo(unknown directory): dirNFErr") and ok
	m.write_u32(cpb + 0x12, 0x200500)
	m.write_u16(cpb + 0x1c, 1)
	m.write_u32(cpb + 0x30, 2)
	ok = Assertions.assert_equal(volume.pb_get_cat_info(cpb), 0, "PBGetCatInfo by index") and ok
	ok = Assertions.assert_equal(volume.pstring(0x200500), "Hellcats", "the root's first entry by name") and ok
	# PBOpenWD on Preferences for 'HELL', twice: one reference.
	var wpb := 0x200200
	m.write_u32(wpb + 0x12, 0)
	m.write_u16(wpb + 0x16, 0xffff)
	m.write_u32(wpb + 0x1c, HELL)
	m.write_u32(wpb + 0x30, 4)
	ok = Assertions.assert_equal(volume.pb_open_wd(wpb), 0, "PBOpenWD(Preferences)") and ok
	var wd: int = m.read_u16(wpb + 0x16)
	m.write_u16(wpb + 0x16, 0xffff)
	volume.pb_open_wd(wpb)
	ok = Assertions.assert_equal(m.read_u16(wpb + 0x16), wd, "the same (directory, procID): the same working directory") and ok
	m.write_u16(wpb + 0x16, 0xffff)
	m.write_u32(wpb + 0x30, 77)
	ok = Assertions.assert_equal(volume.pb_open_wd(wpb), (-120) & 0xffff, "PBOpenWD(unknown directory): dirNFErr") and ok
	ok = Assertions.assert_equal(files_manager.set_vol(0, wd), 0, "SetVol accepts the working directory") and ok
	return ok

## Leyte's first start, as 0x2f42 / 0x32d8 / 0x3082 / 0x31d8 do it.
func _first_start() -> bool:
	_boot()
	var m = owner.memory
	var ok := true
	var wpb := 0x200200
	m.write_u32(wpb + 0x12, 0)
	m.write_u16(wpb + 0x16, 0xffff)
	m.write_u32(wpb + 0x1c, HELL)
	m.write_u32(wpb + 0x30, 4)
	volume.pb_open_wd(wpb)
	files_manager.set_vol(0, m.read_u16(wpb + 0x16))
	var name: int = _pstr(0x200400, "Hellcats Preferences")
	ok = Assertions.assert_equal(traps.call_trap(0xa997, [name]), 0xffff, "OpenResFile before the file exists: -1") and ok
	ok = Assertions.assert_equal(resources.res_error, -43, "ResError fnfErr") and ok
	traps.call_trap(0xa9b1, [name])
	ok = Assertions.assert_equal(resources.res_error, 0, "CreateResFile") and ok
	traps.call_trap(0xa9b1, [name])
	ok = Assertions.assert_equal(resources.res_error, -48, "CreateResFile again: dupFNErr, the file kept") and ok
	var prefs: int = traps.call_trap(0xa997, [name])
	ok = Assertions.assert_true(prefs >= 0x40 and prefs != 0xffff, "OpenResFile: a reference") and ok
	ok = Assertions.assert_equal(traps.call_trap(0xa994, []), prefs, "the preferences file current") and ok
	ok = Assertions.assert_equal(traps.call_trap(0xa9a0, [227, HEL1]), 0, "no 'HEL1' 227 yet (Leyte's fork has none)") and ok
	# The settings made and added to the preferences file.
	var settings: int = resources.heap.new_handle(0x12e, true)
	m.write_u16(m.read_u32(settings) + 0x28, 6)
	traps.call_trap(0xa9ab, [_pstr(0x200500, "Hellcats Saved Settings"), 227, HEL1, settings])
	ok = Assertions.assert_equal(resources.res_error, 0, "AddResource to the preferences file") and ok
	# The import (0x3082): the Hellcats file opened in the application's folder.
	files_manager.set_vol(0, 0xffff)
	var hellcats: int = traps.call_trap(0xa997, [_pstr(0x200600, "Hellcats")])
	ok = Assertions.assert_true(hellcats != 0xffff and hellcats != prefs, "OpenResFile(Hellcats): a file of its own") and ok
	var source: int = traps.call_trap(0xa9a0, [127, HEL1])
	ok = Assertions.assert_equal(m.read_u8(m.read_u32(source) + 2), (2 * 5 + 1) & 0xff, "'HEL1' 127 read from the Hellcats save") and ok
	var roster: int = traps.call_trap(0xa9a0, [128, HEL1])
	ok = Assertions.assert_true(roster != 0, "'HEL1' 128 found (Hellcats' shipped fork when the save has none)") and ok
	m.write_u8(m.read_u32(source) + 2, 0xee)
	traps.call_trap(0xa9aa, [source])
	ok = Assertions.assert_equal(resources.res_error, -61, "ChangedResource on the Hellcats file: wrPermErr") and ok
	traps.call_trap(0xa9ad, [source])
	ok = Assertions.assert_equal(resources.res_error, -196, "RmveResource on the Hellcats file: rmvResFailed") and ok
	traps.call_trap(0xa9ab, [_pstr(0x200700, "x"), 300, HEL1, resources.heap.new_handle(4, true)])
	ok = Assertions.assert_equal(resources.res_error, -194, "AddResource to the Hellcats file: addResFailed") and ok
	traps.call_trap(0xa992, [roster])
	traps.call_trap(0xa99a, [hellcats])
	ok = Assertions.assert_true(resources.heap.handle_size(roster) > 0, "the detached roster survives CloseResFile") and ok
	ok = Assertions.assert_equal(traps.call_trap(0xa9a0, [127, HEL1]), 0, "the Hellcats file closed: out of the chain") and ok
	# Back in the preferences file: the roster copied in as 'HEL1' 228, everything written.
	traps.call_trap(0xa998, [prefs])
	var table: int = resources.heap.new_handle(0x500, true)
	resources.heap.block_move(m.read_u32(roster), m.read_u32(table), 0x40)
	traps.call_trap(0xa9ab, [_pstr(0x200800, "Player and Score Data"), 228, HEL1, table])
	traps.call_trap(0xa9b0, [settings])
	traps.call_trap(0xa9b0, [table])
	traps.call_trap(0xa99a, [prefs])
	var on_disk: String = LeyteVolume.save_root + "/volume/System Folder/Preferences/Hellcats Preferences.rsrc/hel1"
	ok = Assertions.assert_true(FileAccess.file_exists(on_disk + "/227.bin") and FileAccess.file_exists(on_disk + "/228.bin"),
			"'HEL1' 227 / 228 written in Leyte's own preferences file") and ok
	return ok

## A restart: the preferences file found, its resources read back, nothing created again.
func _restart() -> bool:
	_boot()
	var m = owner.memory
	var ok := true
	var wpb := 0x200200
	m.write_u32(wpb + 0x12, 0)
	m.write_u16(wpb + 0x16, 0xffff)
	m.write_u32(wpb + 0x1c, HELL)
	m.write_u32(wpb + 0x30, 4)
	volume.pb_open_wd(wpb)
	files_manager.set_vol(0, m.read_u16(wpb + 0x16))
	var prefs: int = traps.call_trap(0xa997, [_pstr(0x200400, "Hellcats Preferences")])
	ok = Assertions.assert_true(prefs != 0xffff, "the preferences file opens after a restart") and ok
	var settings: int = traps.call_trap(0xa9a0, [227, HEL1])
	ok = Assertions.assert_true(settings != 0 and m.read_u16(m.read_u32(settings) + 0x28) == 6, "'HEL1' 227 read back as written") and ok
	ok = Assertions.assert_equal(traps.call_trap(0xa9a4, [settings]), prefs, "HomeResFile: the preferences file") and ok
	m.write_u16(m.read_u32(settings) + 0x28, 7)
	traps.call_trap(0xa9aa, [settings])
	traps.call_trap(0xa999, [prefs])
	_boot()
	m = owner.memory                                   # the restarted machine's memory
	m.write_u32(wpb + 0x12, 0)
	m.write_u16(wpb + 0x16, 0xffff)
	m.write_u32(wpb + 0x1c, HELL)
	m.write_u32(wpb + 0x30, 4)
	volume.pb_open_wd(wpb)
	files_manager.set_vol(0, m.read_u16(wpb + 0x16))
	traps.call_trap(0xa997, [_pstr(0x200400, "Hellcats Preferences")])
	var again: int = traps.call_trap(0xa9a0, [227, HEL1])
	ok = Assertions.assert_equal(owner.memory.read_u16(owner.memory.read_u32(again) + 0x28), 7, "an update persists across a restart") and ok
	return ok

## No Hellcats on the volume: the import finds no file (OpenResFile -1), as 0x3082 expects.
func _no_hellcats() -> bool:
	_boot(false)
	var ok := Assertions.assert_equal(traps.call_trap(0xa997, [_pstr(0x200600, "Hellcats")]), 0xffff, "no Hellcats: OpenResFile -1") and true
	return Assertions.assert_equal(resources.res_error, -43, "ResError fnfErr") and ok
