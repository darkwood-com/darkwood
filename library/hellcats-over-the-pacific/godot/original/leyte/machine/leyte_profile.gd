extends RefCounted
## Leyte Gulf's title profile for the Original runtime: the one machine (mac_machine.gd) and its Toolbox,
## with Leyte's tables, ports, files and glue. apply() sets it before a machine is made; clear() puts
## Hellcats back. Nothing of 1.0.3 / 1.0.4 changes while it is clear.

const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const SegmentLoader := preload("res://original/mac/machine/segment_loader.gd")
const Toolbox := preload("res://original/mac/toolbox/toolbox.gd")
const Files := preload("res://assets/ui/resource_files.gd")
const LeyteVolume := preload("res://original/leyte/machine/leyte_volume.gd")
const LeyteResources := preload("res://original/leyte/machine/leyte_resources.gd")
const LeyteGlue := preload("res://original/leyte/machine/leyte_glue.gd")
const LeyteRuntime := preload("res://original/leyte/machine/leyte_runtime.gd")
const LeyteOverlayLoader := preload("res://original/leyte/machine/leyte_overlay_loader.gd")

const CUR_AP_NAME := 0x910

## The A5 world below A5 (Leyte's CODE 0 header; 1.0.3's is 0x225e).
var below_a5: int = 0x2322
var with_hellcats: bool = true
var volume

static func apply(with_hellcats: bool = true):
	var p = load("res://original/leyte/machine/leyte_profile.gd").new()
	p.with_hellcats = with_hellcats
	Files.title = "leyte-gulf"
	Toolbox.overrides["resources"] = LeyteResources
	SegmentLoader.profile = {
		"jump_table": "res://original/leyte/data/jump_table.json",
		"segment_methods": "res://original/leyte/data/segment_methods.json",
		# CODE 1 (the C runtime: byte-identical to 1.0.3's) is not a segment port, as in 1.0.3's loader.
		"ports": {
			2: preload("res://original/leyte/port/ID02_reconstructed.gd"),
			3: preload("res://original/leyte/port/ID03_reconstructed.gd"), 4: preload("res://original/leyte/port/ID04_reconstructed.gd"),
			5: preload("res://original/leyte/port/ID05.gd"), 6: preload("res://original/leyte/port/ID06.gd"),
			7: preload("res://original/leyte/port/ID07.gd"), 8: preload("res://original/leyte/port/ID08.gd"),
			9: preload("res://original/leyte/port/ID09.gd")}}
	MacMachine.profile = p
	return p

static func clear() -> void:
	Files.title = "hellcats"
	Toolbox.overrides.erase("resources")
	SegmentLoader.profile = {}
	MacMachine.profile = null

## After the Toolbox: the sandboxed volume, given to the File and Resource Managers, and SysEnvirons'
## sysVRefNum (the System Folder's working directory).
func setup(machine) -> void:
	volume = LeyteVolume.new(machine.memory, "Leyte", with_hellcats)
	machine.toolbox.files.wd_refs = volume.wds
	machine.toolbox.resources.volume = volume
	machine.toolbox.resources.file_manager = machine.toolbox.files
	machine.toolbox.sys_vref = volume.sys_vref
	# The application's own data fork (its overlay: the CODE 8 loader opens it by CurApName), read-only.
	machine.toolbox.files.title_files[LeyteVolume.APPLICATION.to_lower()] = "res://assets/leyte-gulf-1.0/data_fork.bin"
	# Low-memory CurApName (0x910): the application's name, as the Finder sets it.
	machine.memory.write_u8(CUR_AP_NAME, LeyteVolume.APPLICATION.length())
	for k in LeyteVolume.APPLICATION.length():
		machine.memory.write_u8(CUR_AP_NAME + 1 + k, LeyteVolume.APPLICATION.unicode_at(k))

## After the segments: the glue's stand-ins at Leyte's offsets (only those: a 1.0.3 offset means
## another routine in Leyte), with the volume's HFS calls.
func link(machine) -> void:
	var tb = machine.toolbox
	var leyte: Dictionary = LeyteGlue.rekey(machine.segments.stand_ins, tb.heap, tb.files, volume)
	machine.segments.stand_ins.clear()
	for k in leyte:
		machine.segments.stand_ins[k] = leyte[k]
	# Leyte's runtime-written routines (CODE 5's protection helpers), at Leyte's offsets.
	machine.set_meta("leyte_runtime", LeyteRuntime.new(machine))
	machine.overlay_loader = LeyteOverlayLoader.new(machine)
