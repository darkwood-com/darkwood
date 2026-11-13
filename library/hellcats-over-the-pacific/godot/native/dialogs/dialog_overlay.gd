extends Control

## The Native edition's dialogs on screen: the front dialog's standard items (dialog_manager.gd's
## snapshot) as Godot controls over the Mac screen, each on its item's rectangle, at the scale the
## screen is shown. They present; they decide nothing: a click goes to the program through the game's
## own mouse handling (game.gd), so ModalDialog and the game see exactly the Original edition's
## events. Keyboard focus stays with the game.

const BUTTON := 4
const CHECKBOX := 5
const RADIO := 6
const STATIC_TEXT := 8
const EDIT_TEXT := 16

var machine
var screen: Control
var _version := -1
var _items: Array = []
var _controls: Array = []

func attach(owner_machine, mac_screen: Control) -> void:
	machine = owner_machine
	screen = mac_screen
	set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	mouse_filter = Control.MOUSE_FILTER_IGNORE

## A point of the Mac screen in this control's coordinates (the inverse of game.gd's to_screen).
func to_godot(p: Vector2) -> Vector2:
	var area: Rect2 = screen.get_global_rect()
	var scale: float = minf(area.size.x / machine.SCREEN_WIDTH, area.size.y / machine.SCREEN_HEIGHT)
	var offset: Vector2 = area.position + (area.size - Vector2(machine.SCREEN_WIDTH, machine.SCREEN_HEIGHT) * scale) / 2.0
	return offset + p * scale - get_global_rect().position

func _process(_delta: float) -> void:
	if machine == null or not machine.toolbox.dialogs.has_method("snapshot"):
		return
	var snap: Dictionary = machine.toolbox.dialogs.snapshot()
	if int(snap["version"]) != _version:
		_version = int(snap["version"])
		_rebuild(snap["items"])
	_place()

func _rebuild(items: Array) -> void:
	for c in _controls:
		c.queue_free()
	_controls.clear()
	_items = items
	for e in items:
		var c: Control = build_control(e)
		add_child(c)
		_controls.append(c)

## The Godot control showing one snapshot item.
static func build_control(e: Dictionary) -> Control:
	var c: Control
	match int(e["type"]):
		BUTTON:
			var b := Button.new()
			b.text = e["text"]
			b.disabled = not e["active"]
			for state in ["normal", "hover", "focus", "disabled"]:
				b.add_theme_stylebox_override(state, _box(Color.WHITE, 2))
			b.add_theme_stylebox_override("pressed", _box(Color.BLACK, 2))
			for state in ["font_color", "font_hover_color", "font_focus_color"]:
				b.add_theme_color_override(state, Color.BLACK)
			b.add_theme_color_override("font_pressed_color", Color.WHITE)
			b.add_theme_color_override("font_disabled_color", Color(0.5, 0.5, 0.5))
			c = b
		CHECKBOX, RADIO:
			var b := CheckBox.new()
			b.text = e["text"]
			b.set_pressed_no_signal(int(e["value"]) != 0)
			b.disabled = not e["active"]
			c = b
		EDIT_TEXT:
			var l := LineEdit.new()
			l.text = e["text"]
			l.editable = false
			var selected: bool = e.get("selected", false) and String(e["text"]).length() > 0
			l.add_theme_stylebox_override("read_only", _box(Color.BLACK if selected else Color.WHITE, 1))
			l.add_theme_color_override("font_uneditable_color", Color.WHITE if selected else Color.BLACK)
			if e.get("focused", false) and not selected:
				var caret := ColorRect.new()
				caret.name = "Caret"
				caret.color = Color.BLACK
				caret.mouse_filter = Control.MOUSE_FILTER_IGNORE
				l.add_child(caret)
			c = l
		_:
			var l := Label.new()
			l.text = e["text"]
			l.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
			l.add_theme_stylebox_override("normal", _box(Color.WHITE, 0))
			l.add_theme_color_override("font_color", Color.BLACK)
			c = l
	c.focus_mode = Control.FOCUS_NONE
	c.mouse_filter = Control.MOUSE_FILTER_PASS
	c.clip_contents = true
	c.set_meta("item", e)
	return c

## An opaque box: `fill`, a black border `border` pixels wide, rounded as Mac buttons are when it has one.
static func _box(fill: Color, border: int) -> StyleBoxFlat:
	var s := StyleBoxFlat.new()
	s.bg_color = fill
	s.border_color = Color.BLACK
	s.set_border_width_all(border)
	s.set_corner_radius_all(6 if border > 1 else 0)
	return s

func _place() -> void:
	for i in _controls.size():
		var r: Rect2i = _items[i]["rect"]
		var a: Vector2 = to_godot(Vector2(r.position))
		var b: Vector2 = to_godot(Vector2(r.end))
		var c: Control = _controls[i]
		c.position = a
		c.size = b - a
		var font_size: int = maxi(8, int(round((b - a).y / maxf(r.size.y, 1) * 11.0)))
		c.add_theme_font_size_override("font_size", font_size)
		var caret: ColorRect = c.get_node_or_null("Caret")
		if caret != null:
			_place_caret(c as LineEdit, caret, font_size)

## The caret after the edit field's text, blinking at the Mac's rate (GetCaretTime: 32 ticks, a half
## second each way).
func _place_caret(field: LineEdit, caret: ColorRect, font_size: int) -> void:
	var font: Font = field.get_theme_font("font")
	var margin: float = field.get_theme_stylebox("read_only").get_margin(SIDE_LEFT)
	var width: float = font.get_string_size(field.text, HORIZONTAL_ALIGNMENT_LEFT, -1, font_size).x
	caret.position = Vector2(margin + width + 1, field.size.y * 0.2)
	caret.size = Vector2(maxf(1, font_size / 11.0), field.size.y * 0.6)
	caret.visible = int(Time.get_ticks_msec() / 533) % 2 == 0
