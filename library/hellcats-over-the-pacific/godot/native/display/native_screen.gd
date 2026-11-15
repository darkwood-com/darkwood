extends "res://original/mac/machine/mac_screen.gd"

## The Native edition's presenter: the Original's (a palette-indexed texture through the colour
## table), reading the Godot-owned screen (screen_memory.gd) instead of the Mac memory. attach()
## runs before the program's thread starts (game.gd), so it installs the screen memory first.

const ScreenMemory := preload("res://native/display/screen_memory.gd")

var _screen = null

func attach(owner) -> void:
	_screen = ScreenMemory.install(owner)
	super(owner)

func _process(_delta: float) -> void:
	if machine == null:
		return
	if _screen == null:
		super(_delta)
		return
	_update_palette()
	_image.set_data(machine.SCREEN_WIDTH, machine.SCREEN_HEIGHT, false, Image.FORMAT_L8, _screen.screen_bytes())
	_texture.update(_image)
