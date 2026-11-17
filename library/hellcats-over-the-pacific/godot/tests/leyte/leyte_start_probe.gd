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
	for word in [0xa97c, 0xa991, 0xa982, 0xa98b, 0xa983]:
		var inner: Callable = machine.toolbox.traps[word]
		machine.toolbox.traps[word] = func(a: Array) -> int:
			var r: int = int(inner.call(a))
			print("[probe] trap %04x %s -> %d" % [word, str(a), r])
			# The sound error dialog (DLOG 139) waits for a click (Button down, then up after DisposDialog):
			# the mouse is pressed when it opens and let go when it is disposed.
			if word == 0xa97c and int(a[a.size() - 1]) == 139:
				machine.toolbox.events.post_mouse(Vector2i(100, 100), true)
			if word == 0xa983:
				machine.toolbox.events.post_mouse(Vector2i(100, 100), false)
			return r
	for slot in [0x592, 0x59a, 0x5c2, 0x57a, 0x5a2, 0x58a, 0x5ca, 0x582, 0x5b2]:
		var routine: Callable = machine.segments.instances[2].jt_routines[slot]
		var traced := func(a: Array) -> int:
			var r: int = int(routine.call(a))
			print("[probe] jt $%x %s -> %d" % [slot, str(a), r])
			return r
		for seg in machine.segments.instances:
			machine.segments.instances[seg].jt_routines[slot] = traced
	# Synthetic input only: each ModalDialog (the Duty Roster, then whatever follows) is answered with
	# Return (its default item), at most five times; the item each call ends with is printed.
	var modal: Callable = machine.toolbox.traps[0xa991]
	var answered := [0]
	machine.toolbox.traps[0xa991] = func(a: Array) -> int:
		if answered[0] < 5:
			answered[0] += 1
			machine.toolbox.events.post_key(0x24, 13, true, false, 0)
			machine.toolbox.events.post_key(0x24, 13, false, false, 0)
		var r: int = int(modal.call(a))
		print("[probe] ModalDialog -> item %d" % machine.memory.read_u16(int(a[0])))
		return r
	# Then, once both dialogs are answered, Command-Q (down and up) at the flight loop's first
	# HiliteMenu(0): the loop's own quit key, so a session with the overlay's hooks missing still ends.
	var hilite: Callable = machine.toolbox.traps[0xa938]
	var quit_posted := [false]
	machine.toolbox.traps[0xa938] = func(a: Array) -> int:
		if answered[0] >= 2 and not quit_posted[0]:
			quit_posted[0] = true
			print("[probe] flight loop entered: Command-Q posted")
			machine.toolbox.events.post_key(0x0c, 0x51, true, false, 0x100)
			machine.toolbox.events.post_key(0x0c, 0x51, false, false, 0x100)
		return int(hilite.call(a))
	# Alerts (Alert, StopAlert, NoteAlert, CautionAlert) answered with Return too, their default item.
	for word in [0xa985, 0xa986, 0xa987, 0xa988]:
		var alert: Callable = machine.toolbox.traps[word]
		machine.toolbox.traps[word] = func(a: Array) -> int:
			machine.toolbox.events.post_key(0x24, 13, true, false, 0)
			machine.toolbox.events.post_key(0x24, 13, false, false, 0)
			var r: int = int(alert.call(a))
			print("[probe] alert %04x %s -> item %d" % [word, str(a), r])
			return r
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
