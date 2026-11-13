extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const NativeDialogs := preload("res://native/dialogs/dialog_manager.gd")
const DialogOverlay := preload("res://native/dialogs/dialog_overlay.gd")
const Templates := preload("res://assets/ui/dialog_templates.gd")
const Files := preload("res://assets/ui/resource_files.gd")

const CLASSES := {4: "Button", 5: "CheckBox", 6: "CheckBox", 8: "Label", 16: "LineEdit"}

## The Native dialogs: every DLOG of the game opened (and shown, drawn, a check box set, a click
## handled) on an Original and a Native machine leaves the same Mac memory, byte for byte; the Native
## snapshot lists the front dialog's standard items on their DITL rectangles (moved by the window's
## origin) with their text, and the overlay builds the matching Godot control for each.
func run() -> bool:
	var original = _machine(false)
	var native = _machine(true)
	var ok := true
	var ids: Array = []
	for f in DirAccess.get_files_at(Files.folder("DLOG")):
		if f.ends_with(".bin"):
			ids.append(int(f.get_basename()))
	ids.sort()
	for id in ids:
		var pair: Array = []
		for m in [original, native]:
			var d: int = m.toolbox.dialogs.get_new_dialog(id, 0, 0xffffffff)
			m.toolbox.windows.show(d, true)
			m.toolbox.dialogs.draw_dialog(d)
			pair.append(d)
			_exercise(m, d)
		ok = Assertions.assert_equal(pair[1], pair[0], "DLOG %d: the same window" % id) and ok
		ok = _check_snapshot(native, pair[1], id) and ok
		for i in pair.size():
			[original, native][i].toolbox.dialogs.dispose(pair[i])
	ok = Assertions.assert_true(native.memory._bytes == original.memory._bytes, "the Mac memory is the same, byte for byte") and ok
	ok = Assertions.assert_equal(native.toolbox.dialogs.snapshot()["items"], [], "no dialog left, nothing shown") and ok
	return _check_edit_focus(native) and ok

## The edit field typing goes to: the first one when the dialog opens (a caret in the overlay), then
## one selected whole by SelIText (shown inverted, no caret).
func _check_edit_focus(m) -> bool:
	var dm = m.toolbox.dialogs
	var d: int = dm.get_new_dialog(130, 0, 0xffffffff)
	m.toolbox.windows.show(d, true)
	dm.draw_dialog(d)
	var edits: Array = dm.snapshot()["items"].filter(func(e): return e["type"] == 16)
	var ok := Assertions.assert_true(edits.size() >= 2, "DLOG 130 has edit fields")
	ok = Assertions.assert_equal(edits.map(func(e): return e["focused"]), [true] + edits.slice(1).map(func(_e): return false),
		"the first edit field has the focus") and ok
	var first: Control = DialogOverlay.build_control(edits[0])
	ok = Assertions.assert_true(first.get_node_or_null("Caret") != null, "a caret in the focused field") and ok
	first.free()
	dm.set_edit(d, int(edits[1]["index"]) - 1, true)
	var second: Dictionary = dm.snapshot()["items"].filter(func(e): return e["index"] == edits[1]["index"])[0]
	ok = Assertions.assert_equal([second["focused"], second["selected"]], [true, true], "SelIText selects the second field") and ok
	dm.dispose(d)
	return ok

func _machine(native: bool):
	Toolbox.overrides = {"dialogs": NativeDialogs} if native else {}
	var m = MacMachine.new()
	Toolbox.overrides = {}
	m.toolbox.quickdraw.glyphs.prepare()
	return m

## A check box set (if any) and a click on the first enabled button, as ModalDialog handles it.
func _exercise(m, d: int) -> void:
	var dm = m.toolbox.dialogs
	for e in dm.dialogs[d]["items"]:
		if e["type"] == 5:
			dm._set_ctl(e["handle"], 0x12, 1)
			break
	for i in dm.dialogs[d]["items"].size():
		var e: Dictionary = dm.dialogs[d]["items"][i]
		if e["type"] == 4 and e["enabled"]:
			var origin: Vector2i = -m.toolbox.quickdraw.port_bits(d)[2].position
			var p: Vector2i = (e["box"] as Rect2i).get_center() + origin
			var event: int = m.toolbox.heap.new_ptr(16, true)
			m.memory.write_u16(event, 1)
			m.memory.write_u16(event + 0xa, p.y)
			m.memory.write_u16(event + 0xc, p.x)
			m.toolbox.events.post_mouse(p, false)
			dm._handle(d, event)
			break

func _check_snapshot(m, d: int, id: int) -> bool:
	var snap: Array = m.toolbox.dialogs.snapshot()["items"]
	var t: Dictionary = Templates.decode_dlog(Files.load_bytes("DLOG", id))
	var items: Array = Templates.decode_ditl(Files.load_bytes("DITL", t["ditl_id"]))["items"]
	var origin: Vector2i = -m.toolbox.quickdraw.port_bits(d)[2].position
	var want: Array = []
	for i in items.size():
		if items[i]["type"] in CLASSES:
			want.append([i + 1, items[i]["type"], Rect2i((items[i]["bounds"] as Rect2i).position + origin, (items[i]["bounds"] as Rect2i).size)])
	var got: Array = []
	var ok := true
	for e in snap:
		got.append([e["index"], e["type"], e["rect"]])
		var c: Control = DialogOverlay.build_control(e)
		ok = Assertions.assert_equal(c.get_class(), CLASSES[int(e["type"])], "DLOG %d item %d control" % [id, e["index"]]) and ok
		if e["type"] == 8 and not String(e["text"]).contains("^"):
			ok = Assertions.assert_true(String(e["text"]).length() > 0 or c is Label, "DLOG %d item %d text" % [id, e["index"]]) and ok
		c.free()
	return Assertions.assert_equal(got, want, "DLOG %d: the snapshot's items" % id) and ok
