extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const EnhancedMenus := preload("res://enhanced/menu_manager.gd")
const TouchController := preload("res://enhanced/touch_controller.gd")
const Controls := preload("res://native/input/controls.gd")

## The Enhanced application clock (app_clock.gd) across a background: TickCount is the clock's from
## the Toolbox's build (before any program thread), it does not advance while the application is
## paused, a program thread reading it waits until the resume (or the machine halts), and the time
## away is not the program's afterwards; the touch controller lets go of its keys on the pause.

func _machine():
	Toolbox.overrides = {"menus": EnhancedMenus}
	var m = MacMachine.new()
	Toolbox.overrides = {}
	m.use_stepped_clock()
	return m

func _tick(m) -> int:
	return m.toolbox.traps[0xa975].call([])

func run() -> bool:
	var m = _machine()
	var clock = m.toolbox.menus.clock
	var ok := Assertions.assert_true(clock != null, "Enhanced menus install the application clock")
	m.stepped_ticks = 100
	ok = Assertions.assert_equal(_tick(m), 100, "TickCount: the host's while never paused") and ok
	clock.pause()
	m.stepped_ticks = 100 + 3600            # a minute away
	clock.resume()
	ok = Assertions.assert_equal(clock.slept_ticks(), 3600, "the time away counted as slept") and ok
	ok = Assertions.assert_equal(_tick(m), 100, "TickCount after the resume: as if no time passed") and ok
	m.stepped_ticks += 10
	ok = Assertions.assert_equal(_tick(m), 110, "TickCount advances again from there") and ok

	# A program thread reading TickCount while paused waits for the resume.
	clock.pause()
	var t := Thread.new()
	t.start(func() -> int: return _tick(m))
	OS.delay_msec(150)
	ok = Assertions.assert_true(t.is_alive(), "paused: the program's TickCount waits") and ok
	m.stepped_ticks += 60
	clock.resume()
	var seen: int = t.wait_to_finish()
	ok = Assertions.assert_equal(seen, 110, "resumed: the thread goes on, without the paused second") and ok

	# A halt (quitting while paused) releases the wait.
	clock.pause()
	t = Thread.new()
	t.start(func() -> int: return clock.tick_count())
	OS.delay_msec(60)
	m.halting = true
	t.wait_to_finish()
	ok = Assertions.assert_true(not t.is_alive(), "a halting machine releases a paused TickCount") and ok
	m.halting = false

	# The touch controller releases its keys when the application pauses.
	Controls.ensure_actions()
	var c = TouchController.new()
	var sent: Array = []
	c.emit_event = func(e: InputEvent) -> void: sent.append(e.pressed)
	c.size = Vector2(2400, 1080)
	c.layout()
	c.touch(0, c.buttons[0]["rect"].get_center(), true)
	c._notification(Node.NOTIFICATION_APPLICATION_PAUSED)
	ok = Assertions.assert_equal(sent, [true, false], "application paused: a held button is released") and ok
	c.free()
	print(("PASS" if ok else "FAIL") + " Enhanced application clock")
	return ok
