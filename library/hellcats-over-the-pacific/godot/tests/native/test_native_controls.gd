extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Controls := preload("res://native/input/controls.gd")
const MacKeys := preload("res://original/mac/machine/mac_keys.gd")

## Native controls: by default every action posts the same Mac key Original posts for its key; a
## rebound action posts its command's Mac key from the new key; Command keys and keys outside the
## actions are left to Original's mapping.
func run() -> bool:
	Controls.ensure_actions()
	var ok := true
	for action in Controls.ACTIONS:
		var e := _key(Controls.ACTIONS[action][2])
		ok = Assertions.assert_equal(Controls.mac_key(e), [MacKeys.code(e.keycode), MacKeys.character(e)], action) and ok
	InputMap.action_erase_events("hellcats_gear")
	InputMap.action_add_event("hellcats_gear", _key(KEY_K))
	ok = Assertions.assert_equal(Controls.mac_key(_key(KEY_K)), [0x05, 0x67], "gear rebound to K posts 'g'") and ok
	ok = Assertions.assert_equal(Controls.mac_key(_key(KEY_G)), [], "G no longer an action") and ok
	var command := _key(KEY_Q)
	command.meta_pressed = true
	ok = Assertions.assert_equal(Controls.mac_key(command), [], "Command-Q left to the menus") and ok
	InputMap.action_erase_events("hellcats_gear")
	InputMap.action_add_event("hellcats_gear", _key(KEY_G))
	return ok

## What a US keyboard types with the printable keys used here.
const TYPED := {KEY_EQUAL: 0x3d, KEY_MINUS: 0x2d, KEY_G: 0x67, KEY_F: 0x66, KEY_B: 0x62, KEY_A: 0x61, KEY_J: 0x6a,
	KEY_P: 0x70, KEY_V: 0x76, KEY_C: 0x63, KEY_T: 0x74, KEY_K: 0x6b, KEY_Q: 0x71}

func _key(keycode: Key) -> InputEventKey:
	var e := InputEventKey.new()
	e.keycode = keycode
	e.unicode = TYPED.get(keycode, 0)
	e.pressed = true
	return e
