extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const Overlay := preload("res://original/port/pacific_conflict.gd")

## The keyboard throttle (tester report, loop 8: one +/- press ran the throttle to 0 or 9).
## Event Manager: a key-up carries its key's character as the key-down did, also when the host gives
## none (Godot's key releases have no unicode); auto-key repeats keep it.
## Original flight code (process_flight_input_and_outcome's key cases '+' / '=' and '-' / '_', and
## the per-frame ramp of 0x7dbe): key-down sets throttle_step 0x24cf6 to +-1; each frame |step| 1
## moves the throttle byte +0x668 one notch (clamped 0..9) and doubles the step, any other step moves
## one toward 0, so the throttle changes every second frame while the key is down; the key-up (or an
## auto-key event, which the game does not count as down) clears the step. A key-up that arrived
## without its character missed the case and left the ramp running to the bound.

const CTL := 0x30000
const BODY := 0x3e000

func run() -> bool:
	var ok := _event_manager()
	ok = _ramp() and ok
	return ok

func _event_manager() -> bool:
	var machine = MacMachine.new()
	var events = machine.toolbox.events
	machine.segments.stand_ins[0x1fa].call([0xffff])
	events.post_key(0x18, 0x2b, true, false, 0)          # '+' down
	events.post_key(0x18, 0x2b, true, true, 0)           # auto-key repeat
	events.post_key(0x18, 0, false, false, 0)            # Godot's release: no character
	events.post_key(0x1b, 0x2d, true, false, 0)          # '-' down
	events.post_key(0x1b, 0x2d, false, false, 0)         # a release with its character
	events.post_key(0x1b, 0, false, false, 0)            # a stray release: nothing to recall
	var got: Array = []
	while true:
		var e: Dictionary = events.next_event(0xffff, true)
		if int(e["what"]) == 0 or int(e["what"]) >= 6:
			break
		got.append([int(e["what"]), int(e["message"]) & 0xffff])
	return Assertions.assert_equal(got, [[3, 0x182b], [5, 0x182b], [4, 0x182b], [3, 0x1b2d], [4, 0x1b2d], [4, 0x1b00]],
			"key-up and auto-key carry the key-down's character")

func _port(throttle: int) -> Array:
	var m = Memory68k.new(0x40000)
	var p = Overlay.new(m)
	p.a7 = BODY
	m.write_u32(0x1b738, CTL)
	m.write_u16(0x1d858, 1)               # cockpit view
	m.write_u8(CTL + 0x668, throttle)
	m.write_u32(0x1b5a0, 0x38000)         # host world (its mouse words read by the ramp's caller)
	return [p, m]

## Frames of the ramp with the key going down at frame 0 and up at frame `up` (character as given).
func _hold(throttle: int, key: int, frames: int, up: int, up_char: int) -> Array:
	var pm := _port(throttle)
	var p = pm[0]
	var m = pm[1]
	var seen: Array = []
	p._process_flight_key(BODY, key, 3)
	for f in frames:
		if f == up:
			p._process_flight_key(BODY, up_char, 4)
		p._update_keyboard_flight(BODY)
		seen.append(m.read_s8(CTL + 0x668))
	return seen

func _ramp() -> bool:
	var ok := Assertions.assert_equal(_hold(4, 0x2b, 6, 1, 0x2b), [5, 5, 5, 5, 5, 5], "a press over one frame: one notch up")
	ok = Assertions.assert_equal(_hold(4, 0x2d, 6, 1, 0x2d), [3, 3, 3, 3, 3, 3], "a press over one frame: one notch down") and ok
	ok = Assertions.assert_equal(_hold(4, 0x3d, 8, 4, 0x3d), [5, 5, 6, 6, 6, 6, 6, 6], "'=' held four frames: a notch every second frame") and ok
	ok = Assertions.assert_equal(_hold(4, 0x2b, 30, 30, 0x2b).slice(-1)[0], 9, "held: clamped at 9") and ok
	ok = Assertions.assert_equal(_hold(4, 0x5f, 30, 30, 0x5f).slice(-1)[0], 0, "'_' held: clamped at 0") and ok
	ok = Assertions.assert_equal(_hold(4, 0x2b, 30, 1, 0).slice(-1)[0], 9, "the reported defect: a key-up without its character leaves the ramp running") and ok
	return ok
