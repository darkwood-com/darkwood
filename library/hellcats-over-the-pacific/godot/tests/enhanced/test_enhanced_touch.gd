extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Controls := preload("res://native/input/controls.gd")
const TouchController := preload("res://enhanced/touch_controller.gd")
const EnhancedGame := preload("res://enhanced/enhanced_game.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")

## The Enhanced on-screen controller: its layout on phone and tablet landscape sizes (inside the
## screen, no overlaps, the stick pad left and the actions right, the centre of the picture clear),
## its touch semantics (tap = one down and one up, holds, several touches at once, a touch dragged
## off a button, focus loss), that each button sends exactly the Mac key its action does from the
## keyboard (through the Event Manager, character included), and that desktop runs without it.

const SIZES := [Vector2(1280, 720), Vector2(2400, 1080), Vector2(1920, 1080), Vector2(2048, 1536), Vector2(1334, 750)]

func _controller(size: Vector2) -> Array:
	var c = TouchController.new()
	var sent: Array = []
	c.emit_event = func(e: InputEvent) -> void: sent.append([e.keycode, e.pressed, e.echo])
	c.size = size
	c.layout()
	return [c, sent]

func _centre(c, action: String) -> Vector2:
	for b in c.buttons:
		if b["action"] == action:
			return b["rect"].get_center()
	return Vector2(-1, -1)

func _key(action: String) -> int:
	return InputMap.action_get_events(action)[0].keycode

func run() -> bool:
	Controls.ensure_actions()
	var ok := _layouts()
	ok = _touches() and ok
	ok = _equivalence() and ok
	ok = Assertions.assert_true(not EnhancedGame.touch_wanted(PackedStringArray()) or DisplayServer.is_touchscreen_available(),
			"desktop: no controller without a touch screen") and ok
	ok = Assertions.assert_true(EnhancedGame.touch_wanted(PackedStringArray(["--touch"])), "--touch previews it on a desktop") and ok
	return ok

## An iPhone in landscape as the Simulator measures it (iPhone 18 Pro, 3x): its size and safe area
## (the Dynamic Island's inset both sides, the home indicator's below).
const PHONE_SIZE := Vector2(2622, 1206)
const PHONE_SAFE := Rect2(186, 0, 2250, 1146)

func _layouts() -> bool:
	var bad: Array = []
	for size in SIZES + [PHONE_SIZE]:
		var c = TouchController.new()
		c.emit_event = func(_e: InputEvent) -> void: pass
		c.size = size
		if size == PHONE_SIZE:
			c.safe_override = PHONE_SAFE
		c.layout()
		if size == PHONE_SIZE:
			for b in c.buttons:
				var r: Rect2 = b["rect"]
				if r.end.y > PHONE_SAFE.end.y or r.position.y < PHONE_SAFE.position.y:
					bad.append([size, b["action"], "outside the safe area vertically"])
		var full := Rect2(Vector2.ZERO, size)
		var centre := Rect2(size * Vector2(0.3, 0.15), size * Vector2(0.4, 0.45))
		for i in c.buttons.size():
			var r: Rect2 = c.buttons[i]["rect"]
			if not full.encloses(r):
				bad.append([size, c.buttons[i]["action"], "outside"])
			if r.intersects(centre):
				bad.append([size, c.buttons[i]["action"], "over the centre of the picture"])
			if size.x / size.y >= 2.0 and r.intersects(c.picture_rect().grow(-1)):   # phones: 19.5:9 / 20:9 bars fit the controls
				bad.append([size, c.buttons[i]["action"], "over the picture on a wide screen"])
			for j in range(i + 1, c.buttons.size()):
				if r.intersects(c.buttons[j]["rect"]):
					bad.append([size, c.buttons[i]["action"], c.buttons[j]["action"], "overlap"])
			var action: String = c.buttons[i]["action"]
			if action.begins_with("hellcats_stick") and r.get_center().x > size.x / 2:
				bad.append([size, action, "stick not on the left"])
			if action in ["hellcats_fire", "hellcats_bomb", "hellcats_throttle_up", "hellcats_throttle_down"] and r.get_center().x < size.x / 2:
				bad.append([size, action, "action not on the right"])
		c.free()
	return Assertions.assert_equal(bad.slice(0, 6), [], "layouts: inside, no overlaps, pad left, actions right, centre clear, off the picture on wide screens")

func _touches() -> bool:
	var pair := _controller(Vector2(1280, 720))
	var c = pair[0]
	var sent: Array = pair[1]
	var plus := _centre(c, "hellcats_throttle_up")
	var left := _centre(c, "hellcats_stick_left")
	var fire := _centre(c, "hellcats_fire")
	c.touch(0, plus, true)
	c.touch(0, plus, false)
	var ok := Assertions.assert_equal(sent, [[_key("hellcats_throttle_up"), true, false], [_key("hellcats_throttle_up"), false, false]],
			"a tap: one key down and one up, no repeat")
	sent.clear()
	c.touch(1, left, true)
	c.touch(2, fire, true)
	ok = Assertions.assert_true(c.is_down("hellcats_stick_left") and c.is_down("hellcats_fire"), "two buttons held together") and ok
	c.touch(1, left, false)
	ok = Assertions.assert_equal(sent, [[_key("hellcats_stick_left"), true, false], [_key("hellcats_fire"), true, false],
			[_key("hellcats_stick_left"), false, false]], "releasing one leaves the other held") and ok
	sent.clear()
	c.drag(2, fire + Vector2(400, 0))
	ok = Assertions.assert_equal(sent, [[_key("hellcats_fire"), false, false]], "a touch dragged off its button releases it") and ok
	sent.clear()
	c.touch(3, left, true)
	c.touch(4, left, true)
	c.touch(3, left, false)
	ok = Assertions.assert_equal(sent, [[_key("hellcats_stick_left"), true, false]], "two touches on a button: down once, still held") and ok
	sent.clear()
	c.notification(Node.NOTIFICATION_APPLICATION_FOCUS_OUT)
	ok = Assertions.assert_equal(sent, [[_key("hellcats_stick_left"), false, false]], "focus loss releases every held button") and ok
	sent.clear()
	c.touch(5, Vector2(640, 300), true)
	c.touch(5, Vector2(640, 300), false)
	ok = Assertions.assert_equal(sent, [], "a touch on the picture sends nothing") and ok
	c.free()
	return ok

## Every button: its key event, taken as the Native keyboard path takes keys (Controls.mac_key) and
## posted to the Event Manager, gives the action's Mac key and character, down and up.
func _equivalence() -> bool:
	var pair := _controller(Vector2(1280, 720))
	var c = pair[0]
	var machine = MacMachine.new()
	var events = machine.toolbox.events
	machine.segments.stand_ins[0x1fa].call([0xffff])
	var bad: Array = []
	for b in c.buttons:
		var action: String = b["action"]
		for pressed in [true, false]:
			var k: InputEventKey = c.key_event(action, pressed)
			var mapped: Array = Controls.mac_key(k)
			if mapped != [Controls.ACTIONS[action][0], Controls.ACTIONS[action][1]]:
				bad.append([action, pressed, mapped])
				continue
			events.post_key(mapped[0], mapped[1], k.pressed, k.echo, 0)
			var e: Dictionary = events.next_event(0xffff, true)
			if int(e["what"]) != (3 if pressed else 4) or int(e["message"]) & 0xffff != (mapped[0] << 8) | mapped[1]:
				bad.append([action, pressed, e])
	c.free()
	return Assertions.assert_equal(bad.slice(0, 6), [], "each button posts its action's Mac key and character, down and up")
