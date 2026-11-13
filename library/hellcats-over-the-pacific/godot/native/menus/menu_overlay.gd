extends Control

## The Native edition's menus on screen: the menu bar's titles and the menu pulled down (the Native
## Menu Manager's snapshot) as Godot labels and panels over the Mac screen, at its scale. They only
## present: the mouse reaches the Original Menu Manager through the game's own input, which tracks
## and chooses as Original does. The Apple menu's title is the Mac's own drawing (no Apple logo in
## Godot's font).

const ROW := 16

var machine
var screen: Control
var _version := -1
var _nodes: Array = []            ## [Control, Rect2i (Mac)]

func attach(owner_machine, mac_screen: Control) -> void:
	machine = owner_machine
	screen = mac_screen
	set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	mouse_filter = Control.MOUSE_FILTER_IGNORE

func to_godot(p: Vector2) -> Vector2:
	var area: Rect2 = screen.get_global_rect()
	var scale: float = minf(area.size.x / machine.SCREEN_WIDTH, area.size.y / machine.SCREEN_HEIGHT)
	var offset: Vector2 = area.position + (area.size - Vector2(machine.SCREEN_WIDTH, machine.SCREEN_HEIGHT) * scale) / 2.0
	return offset + p * scale - get_global_rect().position

func _process(_delta: float) -> void:
	if machine == null or not machine.toolbox.menus.has_method("snapshot"):
		return
	var snap: Dictionary = machine.toolbox.menus.snapshot()
	if int(snap["version"]) != _version:
		_version = int(snap["version"])
		_rebuild(snap)
	for n in _nodes:
		var r: Rect2i = n[1]
		var a: Vector2 = to_godot(Vector2(r.position))
		var b: Vector2 = to_godot(Vector2(r.end))
		n[0].position = a
		n[0].size = b - a
		var font_size: int = maxi(8, int(round((b - a).y / maxf(r.size.y, 1) * 12.0)))
		n[0].add_theme_font_size_override("font_size", font_size)
		for child in n[0].get_children():
			child.add_theme_font_size_override("font_size", font_size)

func _rebuild(snap: Dictionary) -> void:
	for n in _nodes:
		n[0].queue_free()
	_nodes.clear()
	for t in build_bar(snap["bar"]) + build_menu(snap["open"]):
		add_child(t[0])
		_nodes.append(t)

## The bar's titles: [Control, Mac rect] each (the Apple menu left to the Mac's drawing).
static func build_bar(bar: Array) -> Array:
	var out: Array = []
	for t in bar:
		if t["apple"]:
			continue
		var fg: Color = Color.WHITE if t["hilited"] else (Color(0.5, 0.5, 0.5) if t["dimmed"] else Color.BLACK)
		out.append([_label(t["title"], Color.BLACK if t["hilited"] else Color.WHITE, fg, HORIZONTAL_ALIGNMENT_CENTER), t["rect"]])
	return out

## The menu pulled down: its panel, then a row per item (mark, text, key equivalent); separators dotted.
static func build_menu(open: Dictionary) -> Array:
	if open.is_empty():
		return []
	var box: Rect2i = open["rect"]
	var panel := Panel.new()
	var frame := StyleBoxFlat.new()
	frame.bg_color = Color.WHITE
	frame.border_color = Color.BLACK
	frame.set_border_width_all(1)
	frame.shadow_color = Color.BLACK
	frame.shadow_size = 1
	frame.shadow_offset = Vector2(1, 1)
	panel.add_theme_stylebox_override("panel", frame)
	panel.mouse_filter = Control.MOUSE_FILTER_IGNORE
	var out: Array = [[panel, box]]
	var y: int = box.position.y + 1
	for row in open["rows"]:
		var r := Rect2i(box.position.x + 1, y, box.size.x - 2, ROW)
		if row["separator"]:
			var line := HSeparator.new()
			line.mouse_filter = Control.MOUSE_FILTER_IGNORE
			out.append([line, r])
		else:
			var fg: Color = Color.WHITE if row["chosen"] else (Color.BLACK if row["enabled"] else Color(0.5, 0.5, 0.5))
			var bg: Color = Color.BLACK if row["chosen"] else Color.WHITE
			var text: String = ("%s " % row["mark"] if row["mark"] != "" else "   ") + row["text"]
			var label: Label = _label(text, bg, fg, HORIZONTAL_ALIGNMENT_LEFT)
			if row["key"] != "":
				var key: Label = _label(row["key"], Color(0, 0, 0, 0), fg, HORIZONTAL_ALIGNMENT_RIGHT)
				key.set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
				label.add_child(key)
			out.append([label, r])
		y += ROW
	return out

static func _label(text: String, bg: Color, fg: Color, align: HorizontalAlignment) -> Label:
	var l := Label.new()
	l.text = text
	l.horizontal_alignment = align
	l.vertical_alignment = VERTICAL_ALIGNMENT_CENTER
	var box := StyleBoxFlat.new()
	box.bg_color = bg
	box.content_margin_left = 4
	box.content_margin_right = 6
	l.add_theme_stylebox_override("normal", box)
	l.add_theme_color_override("font_color", fg)
	l.mouse_filter = Control.MOUSE_FILTER_IGNORE
	l.clip_contents = true
	return l
