extends "res://original/mac/machine/game.gd"

## The Native edition so far: the Original edition's program, machine and screen (game.gd), with the
## keyboard read through Godot's InputMap (input/controls.gd), for 1.0.3 the sound driver on typed
## state (sound/native_id02.gd), the dialogs' standard items shown as Godot controls (dialogs/) and
## the saves in a Godot ConfigFile (saves/), the menus shown as Godot controls (menus/) and the overlay's
## RNG as typed state (state/). Everything else is Original's until later slices replace it.

const Controls := preload("res://native/input/controls.gd")
const SegmentLoader := preload("res://original/mac/machine/segment_loader.gd")
const NativeID02 := preload("res://native/sound/native_id02.gd")
const NativeID06 := preload("res://native/state/native_id06.gd")
const Files := preload("res://assets/ui/resource_files.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const NativeDialogs := preload("res://native/dialogs/dialog_manager.gd")
const NativeResources := preload("res://native/saves/resource_manager.gd")
const DialogOverlay := preload("res://native/dialogs/dialog_overlay.gd")
const NativeMenus := preload("res://native/menus/menu_manager.gd")
const MenuOverlay := preload("res://native/menus/menu_overlay.gd")
const OverlayLoader := preload("res://original/mac/machine/overlay_loader.gd")
const NativeOverlay := preload("res://native/state/native_overlay.gd")

func _ready() -> void:
	Controls.ensure_actions()
	SegmentLoader.overrides = {6: NativeID06}
	if Files.version == "1.0.3":
		SegmentLoader.overrides[2] = NativeID02
	Toolbox.overrides = {"dialogs": NativeDialogs, "resources": NativeResources, "menus": NativeMenus}
	OverlayLoader.port_override = NativeOverlay      # stays: the overlay loads later, on the program's thread
	super()
	SegmentLoader.overrides = {}
	Toolbox.overrides = {}
	var overlay := DialogOverlay.new()
	add_child(overlay)
	overlay.attach(machine, screen)
	var menus := MenuOverlay.new()
	add_child(menus)
	menus.attach(machine, screen)

func _input(event: InputEvent) -> void:
	if machine != null and event is InputEventKey:
		var mapped: Array = Controls.mac_key(event)
		if not mapped.is_empty():
			machine.toolbox.events.post_key(mapped[0], mapped[1], event.pressed, event.echo, MacKeys.modifiers(event))
			return
	super(event)
