extends Node

## The main scene: runs one edition of the game, chosen on the command line after `--`
## (`godot --path godot -- --edition=original`). Original is the default. Native runs its first
## slices around Original (godot/native/README.md); Enhanced is not implemented and says so.
## `--version=1.0.4` runs Hellcats 1.0.4 instead of 1.0.3 (resource_files.gd's VERSIONS).

const EDITIONS := ["original", "native", "enhanced"]
const Files := preload("res://assets/ui/resource_files.gd")
const NOT_YET := {"enhanced": "The Enhanced edition is not implemented yet (roadmap phase 9)."}

## The Original edition's scene (original/Game.tscn) and the Native edition's (native/Game.tscn).
@export var original: PackedScene
@export var native: PackedScene

## The edition the arguments ask for: "original" without --edition, "" for an unknown one.
static func edition_from(args: PackedStringArray) -> String:
	for arg in args:
		if arg.begins_with("--edition="):
			var name := arg.trim_prefix("--edition=")
			return name if name in EDITIONS else ""
	return "original"

## The application version the arguments ask for: "1.0.3" without --version, "" for an unknown one.
static func version_from(args: PackedStringArray) -> String:
	for arg in args:
		if arg.begins_with("--version="):
			var v := arg.trim_prefix("--version=")
			return v if v in Files.VERSIONS else ""
	return "1.0.3"

func _ready() -> void:
	var args := OS.get_cmdline_user_args()
	var edition := edition_from(args)
	var version := version_from(args)
	if edition in ["original", "native"] and version == "":
		edition = "unknown version"
	if edition in ["original", "native"]:
		Files.version = version
		add_child((original if edition == "original" else native).instantiate())
		return
	var message: String = NOT_YET.get(edition, "Unknown version; choose one of: %s." % ", ".join(Files.VERSIONS)
			if edition == "unknown version" else "Unknown edition; choose one of: %s." % ", ".join(EDITIONS))
	push_warning(message)
	var label := Label.new()
	label.text = message
	label.set_anchors_and_offsets_preset(Control.PRESET_CENTER)
	add_child(label)
