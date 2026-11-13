extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const NativeMenus := preload("res://native/menus/menu_manager.gd")
const MenuOverlay := preload("res://native/menus/menu_overlay.gd")
const Files := preload("res://assets/ui/resource_files.gd")

## The Native menus: the game's MENUs inserted, the bar drawn and each menu pulled down (its second
## row chosen) and closed on an Original and a Native machine leave the same Mac memory; the Native
## snapshot has the bar's titles where the Original lays them out and the open menu's rows, and the
## overlay builds a label per title (the Apple menu left to the Mac) and the panel and rows.
func run() -> bool:
	var ids: Array = []
	for f in DirAccess.get_files_at(Files.folder("MENU")):
		if f.ends_with(".bin"):
			ids.append(int(f.get_basename()))
	ids.sort()
	var machines: Array = [_machine(false), _machine(true)]
	var ok := true
	for m in machines:
		for id in ids:
			m.toolbox.menus.menus.append(m.toolbox.resources.get_resource("MENU", id))
		m.toolbox.menus.draw_menu_bar()
	var native = machines[1]
	var snap: Dictionary = native.toolbox.menus.snapshot()
	var layout: Array = native.toolbox.menus._layout()
	ok = Assertions.assert_equal(snap["bar"].map(func(t): return t["rect"].position.x), layout.map(func(t): return t[1]),
		"the bar's titles where the Original lays them out") and ok
	ok = Assertions.assert_equal(MenuOverlay.build_bar(snap["bar"]).size(), snap["bar"].filter(func(t): return not t["apple"]).size(),
		"a label per title but the Apple menu's") and ok
	for i in layout.size():
		for m in machines:
			var t: Array = m.toolbox.menus._layout()[i]
			var box: Rect2i = m.toolbox.menus._menu_box(t)
			var saved: PackedByteArray = m.toolbox.menus._save(box)
			m.toolbox.menus._draw_menu(t[0], box, 2)
			if m == native:
				var open: Dictionary = native.toolbox.menus.snapshot()["open"]
				var rows: int = native.toolbox.menus._items(t[0]).size()
				ok = Assertions.assert_equal([open["rect"], open["rows"].size()], [box, rows], "menu %d: its box and rows" % i) and ok
				ok = Assertions.assert_equal(MenuOverlay.build_menu(open).size(), rows + 1, "menu %d: panel and rows" % i) and ok
			m.toolbox.menus._restore(saved, box)
	ok = Assertions.assert_equal(native.toolbox.menus.snapshot()["open"], {}, "no menu left open") and ok
	ok = Assertions.assert_true(machines[1].memory._bytes == machines[0].memory._bytes, "the Mac memory is the same, byte for byte") and ok
	return ok

func _machine(native: bool):
	Toolbox.overrides = {"menus": NativeMenus} if native else {}
	var m = MacMachine.new()
	Toolbox.overrides = {}
	m.toolbox.quickdraw.glyphs.prepare()
	return m
