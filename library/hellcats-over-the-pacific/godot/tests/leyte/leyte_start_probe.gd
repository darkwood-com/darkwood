extends SceneTree
## Leyte Gulf's first start on the Original runtime (original/leyte/machine/leyte_profile.gd): its main
## (CODE 2 0x37d2) run for one event-loop pass, on a temporary save root and an empty synthetic
## Hellcats save (never the player's user://leyte-gulf or user://resources). Prints what the runtime
## noted missing, in order, and the log. A refused port function stops the run with its name.
##
##   Godot --headless --path godot --script res://tests/leyte/leyte_start_probe.gd

const LeyteProfile := preload("res://original/leyte/machine/leyte_profile.gd")
const LeyteVolume := preload("res://original/leyte/machine/leyte_volume.gd")
const LeyteResources := preload("res://original/leyte/machine/leyte_resources.gd")
const ROOT := "user://leyte-start-probe"

static func _wipe(path: String) -> void:
	var d := DirAccess.open(path)
	if d == null:
		return
	for f in d.get_files():
		d.remove(f)
	for sub in d.get_directories():
		_wipe("%s/%s" % [path, sub])
		d.remove(sub)

func _init() -> void:
	_wipe(ROOT)                                        # each start a first start, on the probe's own root
	LeyteVolume.save_root = ROOT + "/leyte-gulf"
	LeyteResources.hellcats_saved_root = ROOT + "/hellcats-saved"
	LeyteProfile.apply(true)
	var MacMachine = load("res://original/mac/machine/mac_machine.gd")
	var machine = MacMachine.new()
	# Trace the copy protection: its dialog traps and CODE 5 slots, as the program reaches them.
	for word in [0xa97c, 0xa991, 0xa982, 0xa98b]:
		var inner: Callable = machine.toolbox.traps[word]
		machine.toolbox.traps[word] = func(a: Array) -> int:
			var r: int = int(inner.call(a))
			print("[probe] trap %04x -> %d" % [word, r])
			return r
	for slot in [0x592, 0x59a, 0x5c2, 0x57a, 0x5a2, 0x58a, 0x5ca, 0x582, 0x5b2]:
		var routine: Callable = machine.segments.instances[2].jt_routines[slot]
		var traced := func(a: Array) -> int:
			var r: int = int(routine.call(a))
			print("[probe] jt $%x %s -> %d" % [slot, str(a), r])
			return r
		for seg in machine.segments.instances:
			machine.segments.instances[seg].jt_routines[slot] = traced
	var program = machine.segments.instances[2]
	program.a7 = machine.STACK_TOP
	program.event_loop_passes = 1
	print("[probe] main")
	program.main()
	print("[probe] main returned; missing: ", machine.missing.size())
	for k in machine.missing:
		print("[probe] missing ", k, " ", machine.missing[k])
	LeyteProfile.clear()
	quit()
