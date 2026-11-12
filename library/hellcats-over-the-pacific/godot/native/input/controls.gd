extends RefCounted

## The Native edition's controls: each command of the game is a Godot InputMap action (remappable in
## Project Settings > Input Map or at run time with InputMap) that posts the Macintosh key the
## original program reads, so the game's own logic is unchanged. Keys bound to no action still reach
## the program as Original sends them (typing a pilot's name, menu key equivalents with Ctrl / Meta).
## The defaults are the original's keys (README, "Running the game").

const MacKeys := preload("res://original/mac/machine/mac_keys.gd")

## action -> [Mac key code, character, default Godot key]
const ACTIONS := {
	"hellcats_throttle_up": [0x18, 0x3d, KEY_EQUAL],
	"hellcats_throttle_down": [0x1b, 0x2d, KEY_MINUS],
	"hellcats_gear": [0x05, 0x67, KEY_G],
	"hellcats_flaps": [0x03, 0x66, KEY_F],
	"hellcats_bomb": [0x0b, 0x62, KEY_B],
	"hellcats_target_lock": [0x00, 0x61, KEY_A],
	"hellcats_eject": [0x26, 0x6a, KEY_J],
	"hellcats_pause": [0x23, 0x70, KEY_P],
	"hellcats_view_cockpit": [0x09, 0x76, KEY_V],
	"hellcats_view_chase": [0x08, 0x63, KEY_C],
	"hellcats_view_tower": [0x11, 0x74, KEY_T],
	"hellcats_view_distance": [0x30, 0x09, KEY_TAB],
	"hellcats_look_left": [0x7b, 0x1c, KEY_LEFT],
	"hellcats_look_right": [0x7c, 0x1d, KEY_RIGHT],
	"hellcats_look_up": [0x7e, 0x1e, KEY_UP],
	"hellcats_look_down": [0x7d, 0x1f, KEY_DOWN],
	"hellcats_interrupt": [0x35, 0x1b, KEY_ESCAPE],
	"hellcats_confirm": [0x24, 0x0d, KEY_ENTER],
}

## Adds every action not defined yet, bound to its default key (a project or player binding stays).
static func ensure_actions() -> void:
	for action in ACTIONS:
		if InputMap.has_action(action):
			continue
		InputMap.add_action(action)
		var key := InputEventKey.new()
		key.keycode = ACTIONS[action][2]
		InputMap.action_add_event(action, key)

## [Mac key code, character] of the action a key event triggers, or [] when it triggers none.
## Command-modified keys are menu equivalents and always pass through unmapped.
static func mac_key(event: InputEventKey) -> Array:
	if event.ctrl_pressed or event.meta_pressed:
		return []
	for action in ACTIONS:
		if InputMap.has_action(action) and event.is_action(action, true):
			return [ACTIONS[action][0], ACTIONS[action][1]]
	return []
