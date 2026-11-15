extends Control

## The Enhanced edition's on-screen controller (mobile, landscape): touch buttons for the game's own
## keyboard commands (native/input/controls.gd's actions; no new keys). A button sends the key its
## action is bound to, as a key event through Godot's input (Input.parse_input_event), so the Native
## edition's keyboard path takes it like a key: same Mac key, same character, down and up.
##
## Touch semantics: a button is down while a touch that began on it stays on it; lifting the touch,
## dragging it off the button, losing the application's focus or hiding the controller sends the key's
## up. Every touch is tracked by its index, so buttons work together (steering with one hand, the
## throttle or the trigger with the other). A held button is a held key without auto-repeat, as on a
## Mac with key repeat off: the game steers, or ramps the throttle (one notch every second frame), for
## the whole hold, and a tap is one press and one release.
##
## Layout (landscape): the stick pad bottom left, the trigger, bomb and throttle bottom right, flaps /
## gear above them, views and pause top right; inside the display's safe area, sized from the
## viewport's height times `ui_scale`. On screens wider than the 4:3 picture the controls sit in the
## bars beside it (shrunk to fit), off the picture; on 4:3 screens they overlay its edges.

const Controls := preload("res://native/input/controls.gd")

## [action, label, column, row] per group; columns / rows are in button units within the group.
const STICK := [["hellcats_stick_forward", "8", 1, 0], ["hellcats_stick_left", "4", 0, 1],
		["hellcats_stick_centre", "5", 1, 1], ["hellcats_stick_right", "6", 2, 1],
		["hellcats_stick_back", "2", 1, 2]]
const ACTIONS_RIGHT := [["hellcats_flaps", "FLAPS", 0, 0], ["hellcats_gear", "GEAR", 1, 0],
		["hellcats_throttle_up", "+", 2, 0], ["hellcats_bomb", "BOMB", 0, 1],
		["hellcats_fire", "FIRE", 1, 1], ["hellcats_throttle_down", "-", 2, 1]]
const TOP := [["hellcats_view_cockpit", "V", 0, 0], ["hellcats_view_chase", "C", 1, 0],
		["hellcats_view_tower", "T", 2, 0], ["hellcats_target_lock", "A", 3, 0],
		["hellcats_pause", "P", 4, 0], ["hellcats_confirm", "OK", 5, 0]]

## Scale of the controls (1.0: a button is 1/7 of the viewport's height).
@export var ui_scale: float = 1.0
## Sends one input event (tests replace it to record the events).
var emit_event: Callable = func(e: InputEvent) -> void: Input.parse_input_event(e)

var buttons: Array = []       ## [{action, label, rect}], rebuilt by layout()
var _held := {}               ## touch index -> button index
var _down := {}               ## button index -> number of touches holding it

func _ready() -> void:
	mouse_filter = Control.MOUSE_FILTER_IGNORE
	set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	resized.connect(layout)
	layout()

## A safe area to use instead of the display's (tests: a phone's measured geometry), or empty.
var safe_override := Rect2()

## The viewport's safe area in this control's coordinates (the whole control when the display has no
## cut-outs or runs windowed).
func safe_rect() -> Rect2:
	var full := Rect2(Vector2.ZERO, size)
	if safe_override.has_area():
		return safe_override.intersection(full)
	var screen := DisplayServer.screen_get_size()
	var safe := DisplayServer.get_display_safe_area()
	if screen.x <= 0 or screen.y <= 0 or safe.size.x <= 0 or not DisplayServer.is_touchscreen_available():
		return full
	var k := size / Vector2(screen)
	return Rect2(Vector2(safe.position) * k, Vector2(safe.size) * k).intersection(full)

## The game picture as shown: the 640 x 480 Mac screen fitted and centred (4:3).
func picture_rect() -> Rect2:
	var w: float = minf(size.x, size.y * 4.0 / 3.0)
	var h: float = w * 3.0 / 4.0
	return Rect2((size - Vector2(w, h)) / 2.0, Vector2(w, h))

func layout() -> void:
	release_all()
	buttons.clear()
	var area := safe_rect()
	var pic := picture_rect()
	var unit: float = area.size.y / 7.0 * ui_scale
	var margin: float = unit * 0.25
	var bar: float = minf(pic.position.x - area.position.x, area.end.x - pic.end.x)
	# Wide screens: the controls go in the bars beside the picture, as large as the bars allow.
	var in_bars: bool = bar >= 3.0 * unit * 0.6 + 2.0 * margin * 0.5
	if not in_bars:
		# A phone whose side insets (iPhone landscape: the Dynamic Island's, both sides) leave the
		# bars too narrow: the bars run to the screen's edges instead, the safe area kept vertically
		# (the controls sit at the top and bottom of the bars, the island at mid-height).
		var edge_bar: float = minf(pic.position.x, size.x - pic.end.x)
		if edge_bar >= 3.0 * unit * 0.6 + 2.0 * margin * 0.5:
			area = Rect2(0.0, area.position.y, size.x, area.size.y)
			bar = edge_bar
			in_bars = true
	if in_bars:
		margin = minf(margin, bar * 0.06)
		unit = minf(unit, (bar - 2.0 * margin) / 3.0)
	var gap: float = unit * 0.12
	var b: float = unit - gap
	# Stick pad: bottom left (in the left bar), 3 x 3.
	var left: float = area.position.x + margin if not in_bars else area.position.x + (bar - 3.0 * unit) / 2.0
	var right: float = area.end.x - margin - 3.0 * unit if not in_bars else area.end.x - (bar + 3.0 * unit) / 2.0
	var origin := Vector2(left, area.end.y - margin - 3 * unit)
	for e in STICK:
		_add(e, origin, unit, b)
	# Actions: bottom right (in the right bar), 3 x 2.
	origin = Vector2(right, area.end.y - margin - 2 * unit)
	for e in ACTIONS_RIGHT:
		_add(e, origin, unit, b)
	# Views and pause: top right, small buttons: a row of six, or two rows of three in the bar.
	var small: float = unit * 0.75 if not in_bars else unit
	if in_bars:
		for i in TOP.size():
			var e: Array = TOP[i]
			_add([e[0], e[1], i % 3, i / 3], Vector2(right, area.position.y + margin), small, small - gap)
	else:
		origin = Vector2(area.end.x - margin - TOP.size() * small, area.position.y + margin)
		for e in TOP:
			_add(e, origin, small, small - gap)
	queue_redraw()

func _add(e: Array, origin: Vector2, unit: float, side: float) -> void:
	buttons.append({"action": e[0], "label": e[1],
		"rect": Rect2(origin + Vector2(e[2], e[3]) * unit, Vector2(side, side))})

func _button_at(p: Vector2) -> int:
	for i in buttons.size():
		if buttons[i]["rect"].has_point(p):
			return i
	return -1

## The key event of an action (its first key binding), or null without one.
func key_event(action: String, pressed: bool) -> InputEventKey:
	if not InputMap.has_action(action):
		return null
	for ev in InputMap.action_get_events(action):
		if ev is InputEventKey:
			var k := InputEventKey.new()
			k.keycode = ev.keycode if ev.keycode != KEY_NONE else ev.physical_keycode
			k.physical_keycode = ev.physical_keycode
			k.pressed = pressed
			k.echo = false
			return k
	return null

func _set_down(i: int, pressed: bool) -> void:
	var n: int = _down.get(i, 0) + (1 if pressed else -1)
	if n < 0:
		return
	if n == 0:
		_down.erase(i)
	else:
		_down[i] = n
	# The key goes down with the first touch on the button and up when the last one leaves.
	if (pressed and n == 1) or (not pressed and n == 0):
		var k := key_event(buttons[i]["action"], pressed)
		if k != null:
			emit_event.call(k)
	queue_redraw()

## A touch (index) at a point, down / moved / up.
func touch(index: int, p: Vector2, pressed: bool) -> void:
	if pressed:
		if _held.has(index):
			return
		var i := _button_at(p)
		if i >= 0:
			_held[index] = i
			_set_down(i, true)
	elif _held.has(index):
		_set_down(_held[index], false)
		_held.erase(index)

func drag(index: int, p: Vector2) -> void:
	if _held.has(index) and not buttons[_held[index]]["rect"].has_point(p):
		_set_down(_held[index], false)   # dragged off: released (no sliding onto another button)
		_held.erase(index)

func release_all() -> void:
	for index in _held.keys():
		_set_down(_held[index], false)
	_held.clear()

func is_down(action: String) -> bool:
	for i in _down:
		if buttons[i]["action"] == action:
			return true
	return false

func _input(event: InputEvent) -> void:
	if not visible:
		return
	if event is InputEventScreenTouch:
		var was := _held.has(event.index)
		touch(event.index, get_global_transform().affine_inverse() * event.position, event.pressed)
		if was or _held.has(event.index):
			get_viewport().set_input_as_handled()
	elif event is InputEventScreenDrag:
		drag(event.index, get_global_transform().affine_inverse() * event.position)

func _notification(what: int) -> void:
	if what == NOTIFICATION_APPLICATION_FOCUS_OUT or what == NOTIFICATION_WM_WINDOW_FOCUS_OUT \
			or what == NOTIFICATION_APPLICATION_PAUSED \
			or what == NOTIFICATION_VISIBILITY_CHANGED or what == NOTIFICATION_EXIT_TREE:
		release_all()

func _draw() -> void:
	var font := get_theme_default_font()
	for i in buttons.size():
		var r: Rect2 = buttons[i]["rect"]
		var down: bool = _down.has(i)
		draw_rect(r, Color(1, 1, 1, 0.35 if down else 0.18), true)
		draw_rect(r, Color(1, 1, 1, 0.6), false, 2.0)
		var text: String = buttons[i]["label"]
		var fs: int = int(r.size.y * 0.3)
		var w: float = font.get_string_size(text, HORIZONTAL_ALIGNMENT_LEFT, -1, fs).x
		if w > r.size.x * 0.8:   # long labels shrink to fit
			fs = maxi(8, int(fs * r.size.x * 0.8 / w))
			w = font.get_string_size(text, HORIZONTAL_ALIGNMENT_LEFT, -1, fs).x
		draw_string(font, r.position + Vector2((r.size.x - w) / 2.0, r.size.y * 0.62), text, HORIZONTAL_ALIGNMENT_LEFT, -1, fs, Color(1, 1, 1, 0.9))
