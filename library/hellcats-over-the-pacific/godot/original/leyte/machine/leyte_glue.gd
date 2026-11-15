extends RefCounted
## Leyte Gulf's CODE 9 Toolbox glue on the runtime's own Toolbox managers: no second Macintosh, only a
## re-keying. The managers install their stand-ins by 1.0.3's jump-table offsets (memory_manager.gd,
## file_manager.gd, standard_file.gd ...); godot/original/leyte/data/glue_slots.json
## (tools/leyte/build_glue_slots.py) pairs each Leyte glue slot whose bytes are identical to one 1.0.3
## slot's ("same"), and lists the ones that are not ("new", with their traps). rekey() gives Leyte's
## offsets the paired stand-ins and adds the new slots the managers can answer; a new slot it cannot
## answer is left out, so a call to it is reported missing by the segment loader, never faked.

const GLUE_SLOTS := "res://original/leyte/data/glue_slots.json"

## Leyte offset -> Callable(args) from 1.0.3-keyed stand-ins, plus the new slots answered here.
## Arguments come as the ports pass them, last-pushed first.
static func rekey(hellcats: Dictionary, memory_manager, file_manager, volume = null) -> Dictionary:
	var doc: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(GLUE_SLOTS))
	var out := {}
	for leyte_key in doc["same"]:
		var h: int = String(doc["same"][leyte_key]).hex_to_int()
		if hellcats.has(h):
			out[String(leyte_key).hex_to_int()] = hellcats[h]
	if memory_manager != null:
		out[0x272] = func(a: Array) -> int: return memory_manager.handle_size(a[0])           ## GetHandleSize(h) ($A025)
		out[0x2b2] = func(a: Array) -> int: return memory_manager.block_move(a[2], a[1], a[0])  ## BlockMove(src, dst, count) ($A02E)
	if file_manager != null:
		out[0x2e2] = func(a: Array) -> int: return file_manager.set_vol(a[1], a[0])          ## SetVol(name, vRefNum) ($A015)
	if volume != null:
		# The HFS calls ($A260 selectors 1 / 7 / 9: [async, pb], OSErr) on Leyte's sandboxed volume.
		out[0x2fa] = func(a: Array) -> int: return volume.pb_open_wd(a[1])       ## PBOpenWD
		out[0x302] = func(a: Array) -> int: return volume.pb_get_wd_info(a[1])   ## PBGetWDInfo
		out[0x30a] = func(a: Array) -> int: return volume.pb_get_cat_info(a[1])  ## PBGetCatInfo
	return out

## The new slots rekey() does not answer yet (Leyte offset -> traps), for a profile to report.
static func unanswered(with_volume: bool = false) -> Dictionary:
	var doc: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(GLUE_SLOTS))
	var out := {}
	for key in doc["new"]:
		var answered: Array = [0x272, 0x2b2, 0x2e2] + ([0x2fa, 0x302, 0x30a] if with_volume else [])
		if not (String(key).hex_to_int() in answered):
			out[key] = doc["new"][key]["traps"]
	return out
