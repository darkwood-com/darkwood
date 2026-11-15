extends "res://original/mac/machine/overlay_loader.gd"

## Leyte Gulf's overlay, loaded by its own CODE 8 (ID08 load_overlay_from_resource, the same loader
## as 1.0.3's with Leyte's jump table) from the application's data fork. The image, its relocation
## tables and the 0x01000000 base are read and undone as overlay_loader.gd does for Pacific
## Conflict; what differs is linked here:
## - the slot ID08 calls once the image is read: 0x51a (CODE 2 +0x245a; 0x4c2, 1.0.3's, is
##   position_alert in Leyte);
## - the port: pacific_conflict_reconstructed.gd (the generated Leyte port plus the routines written
##   from Leyte's bytes), with no method map of its own: its code_table holds the kept routines at
##   their Leyte addresses;
## - the entry (image +0x14 = 0x175a) takes (host world, -1, TickCount), one argument more than 1.0.3's;
## - the 22 hooks it stores in the host world (LEYTE_OVERLAY_HOOKS, position for position 1.0.3's
##   OVERLAY_HOOKS). A hook whose routine is not ported for Leyte yet is reported missing when the
##   host calls it (note_missing), never answered for it; none of 1.0.3's runtime-written routines
##   (WRITTEN, 0x16208, 0x15134) apply: their addresses are 1.0.3's.

const LeytePort := preload("res://original/leyte/port/pacific_conflict_reconstructed.gd")
const LEYTE_AFTER_LOAD := 0x51a

func _init(owner) -> void:
	super(owner, "", LEYTE_AFTER_LOAD)

func _attach(entry: int) -> void:
	port = LeytePort.new(machine.memory)
	var own: Array = port.code_table.keys()
	var segments = machine.segments
	segments.link_overlay(port)
	segments.register_address(IMAGE_BASE + entry, func(args: Array) -> int:
		var list: Array = args.duplicate()
		list.resize(3)
		var saved: int = port.a7
		port.a7 = machine.free_stack()
		port.leyte_overlay_main_entry(int(list[0] if list[0] != null else 0), int(list[1] if list[1] != null else 0), int(list[2] if list[2] != null else 0))
		port.a7 = saved
		return 0)
	for k in LeytePort.LEYTE_OVERLAY_HOOKS.size():
		var hook: int = LeytePort.LEYTE_OVERLAY_HOOKS[k]
		segments.register_address(hook, _leyte_hook(hook, k))
	for address in port.code_table.keys():
		port.code_table[address] = _with_result(port.code_table[address], own.has(address))

func _leyte_hook(address: int, index: int) -> Callable:
	if not port.code_table.has(address):
		return func(args: Array) -> int:
			machine.note_missing("leyte-overlay:0x%x" % address, "Leyte overlay hook %d (1.0.3 0x%x) not ported, %d args" % [index, Overlay.OVERLAY_HOOKS[index], args.size()])
			return 0
	return func(args: Array) -> int:
		var saved: int = port.a7
		port.a7 = machine.free_stack()
		var r = port.code_table[address].callv(args)
		port.a7 = saved
		return 0 if r == null else int(r)
