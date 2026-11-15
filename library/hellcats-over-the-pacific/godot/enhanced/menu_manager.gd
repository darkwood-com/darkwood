extends "res://native/menus/menu_manager.gd"

## The Enhanced edition's Menu Manager: the Native one, plus the application clock (app_clock.gd).
## The clock is installed here because this is the Toolbox's last manager built by the edition, its
## install running after the Event Manager's (TickCount's) and before the program's thread starts:
## the trap is replaced before the program can call it, so no thread race.

const AppClock := preload("res://enhanced/app_clock.gd")

var clock

func install(toolbox) -> void:
	super(toolbox)
	clock = AppClock.new(machine)
	clock.install(toolbox)
