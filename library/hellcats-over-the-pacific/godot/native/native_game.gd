extends "res://original/mac/machine/game.gd"

## The Native edition so far: the Original edition's program, machine and screen (game.gd), with the
## keyboard read through Godot's InputMap (input/controls.gd), for 1.0.3 the sound driver on typed
## state (sound/native_id02.gd), and the dialogs' standard items shown as Godot controls
## (dialogs/). Everything else is Original's until later slices replace it.

const Controls := preload("res://native/input/controls.gd")
const SegmentLoader := preload("res://original/mac/machine/segment_loader.gd")
const NativeID02 := preload("res://native/sound/native_id02.gd")
const Files := preload("res://assets/ui/resource_files.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const NativeDialogs := preload("res://native/dialogs/dialog_manager.gd")
const DialogOverlay := preload("res://native/dialogs/dialog_overlay.gd")

func _ready() -> void:
	Controls.ensure_actions()
	if Files.version == "1.0.3":
		SegmentLoader.overrides = {2: NativeID02}
	Toolbox.overrides = {"dialogs": NativeDialogs}
	super()
	SegmentLoader.overrides = {}
	Toolbox.overrides = {}
	var overlay := DialogOverlay.new()
	add_child(overlay)
	overlay.attach(machine, screen)

func _input(event: InputEvent) -> void:
	if machine != null and event is InputEventKey:
		var mapped: Array = Controls.mac_key(event)
		if not mapped.is_empty():
			machine.toolbox.events.post_key(mapped[0], mapped[1], event.pressed, event.echo, MacKeys.modifiers(event))
			return
	super(event)
