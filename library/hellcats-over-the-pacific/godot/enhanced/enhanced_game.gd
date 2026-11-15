extends "res://native/native_game.gd"

## The Enhanced edition: the Native edition with the on-screen controller (touch_controller.gd) for
## touch screens in landscape. The controller sends the game's own keys through Godot's input, so the
## game is the Native one; desktop keyboards are unchanged. Shown on touch screens, or on a desktop
## with --touch (a preview), hidden otherwise.

const TouchController := preload("res://enhanced/touch_controller.gd")
const EnhancedMenus := preload("res://enhanced/menu_manager.gd")

var touch: Control

## Whether the controller is shown: a touch screen, or --touch on the command line.
static func touch_wanted(args: PackedStringArray) -> bool:
	return args.has("--touch") or DisplayServer.is_touchscreen_available()

func _ready() -> void:
	super()
	DisplayServer.screen_set_orientation(DisplayServer.SCREEN_SENSOR_LANDSCAPE)
	touch = TouchController.new()
	touch.name = "TouchController"
	add_child(touch)
	touch.visible = touch_wanted(OS.get_cmdline_user_args())

func menu_manager():
	return EnhancedMenus

## The application clock (app_clock.gd), or null before the machine exists.
func clock():
	return machine.toolbox.menus.clock if machine != null else null

## The application's background and return (mobile): the controller lets go of its keys, the Mac
## sleeps until the application resumes.
func _notification(what: int) -> void:
	super(what)
	if what == NOTIFICATION_APPLICATION_PAUSED:
		if touch != null:
			touch.release_all()
		if clock() != null:
			clock().pause()
			machine.log_line("application paused: the Mac sleeps")
	elif what == NOTIFICATION_APPLICATION_RESUMED:
		if clock() != null:
			clock().resume()
			machine.log_line("application resumed: %d ticks slept in all" % clock().slept_ticks())
