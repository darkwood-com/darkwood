extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68kScript := preload("res://original/compat/memory_68k.gd")
const MemoryManagerScript := preload("res://original/mac/toolbox/memory_manager.gd")
const FileManagerScript := preload("res://original/mac/toolbox/file_manager.gd")
const LeyteGlue := preload("res://original/leyte/machine/leyte_glue.gd")

## Leyte Gulf's CODE 9 glue on the runtime's managers (original/leyte/machine/leyte_glue.gd): each
## Leyte slot glue_slots.json pairs gets exactly the 1.0.3 stand-in the managers installed; the new
## slots answered here (GetHandleSize, BlockMove with overlapping areas both ways, SetVol) behave as
## the Toolbox does; the rest are reported, not faked.

class Owner:
	var memory
	var HEAP_START: int = 0x100000
	var HEAP_END: int = 0x300000
	var volume_name: String = "Leyte"
	func _init() -> void:
		memory = Memory68kScript.new(0x400000)

class Traps:
	var traps := {}
	func install(word: int, fn: Callable) -> void:
		traps[word] = fn

func run() -> bool:
	var owner := Owner.new()
	var mm = MemoryManagerScript.new(owner)
	var fm = FileManagerScript.new(owner)
	var hellcats := {}
	mm.install(Traps.new(), hellcats)
	fm.install(hellcats)
	var leyte: Dictionary = LeyteGlue.rekey(hellcats, mm, fm)
	var doc: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(LeyteGlue.GLUE_SLOTS))
	var ok := true
	var paired := 0
	for key in doc["same"]:
		var h: int = String(doc["same"][key]).hex_to_int()
		if hellcats.has(h):
			paired += 1
			ok = Assertions.assert_true(leyte[String(key).hex_to_int()] == hellcats[h], "Leyte %s answered by 1.0.3's %s" % [key, doc["same"][key]]) and ok
	ok = Assertions.assert_true(paired > 20, "%d paired Leyte glue slots answered by the managers' stand-ins" % paired) and ok
	# NewPtrClear through Leyte's $242 (1.0.3's $232).
	var p: int = int(leyte[0x242].call([0x40]))
	ok = Assertions.assert_true(p != 0 and int(leyte[0x252].call([p])) == 0x40, "NewPtrClear / GetPtrSize through Leyte's $242 / $252") and ok
	# GetHandleSize ($272).
	var h: int = mm.new_handle(0x123, true)
	ok = Assertions.assert_equal(int(leyte[0x272].call([h])), 0x123, "GetHandleSize through Leyte's $272") and ok
	# BlockMove ($2b2: [count, dst, src]), overlapping both ways.
	var m = owner.memory
	for k in 8:
		m.write_u8(0x200000 + k, k + 1)
	leyte[0x2b2].call([6, 0x200002, 0x200000])
	var up := []
	for k in 8:
		up.append(m.read_u8(0x200000 + k))
	ok = Assertions.assert_equal(up, [1, 2, 1, 2, 3, 4, 5, 6], "BlockMove up over itself") and ok
	for k in 8:
		m.write_u8(0x200000 + k, k + 1)
	leyte[0x2b2].call([6, 0x200000, 0x200002])
	var down := []
	for k in 8:
		down.append(m.read_u8(0x200000 + k))
	ok = Assertions.assert_equal(down, [3, 4, 5, 6, 7, 8, 7, 8], "BlockMove down over itself") and ok
	# SetVol ($2e2: [vRefNum, name]).
	ok = Assertions.assert_equal(int(leyte[0x2e2].call([0xffff, 0])), 0, "SetVol on the volume") and ok
	ok = Assertions.assert_equal(int(leyte[0x2e2].call([0x1234, 0])), (-35) & 0xffff, "SetVol on an unknown reference: nsvErr") and ok
	var left: Array = LeyteGlue.unanswered().keys()
	left.sort()
	ok = Assertions.assert_equal(left, ["0x2fa", "0x302", "0x30a"], "unanswered: PBOpenWD, PBGetWDInfo, PBGetCatInfo") and ok
	return ok
