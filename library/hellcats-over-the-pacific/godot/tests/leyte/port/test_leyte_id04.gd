extends RefCounted

const SegmentCases := preload("res://tests/original/port/helpers/segment_cases.gd")
const LeyteID04 := preload("res://original/leyte/port/ID04.gd")

## Leyte Gulf's ID04 port ("partial": the functions proven by instruction comparison,
## tools/leyte/gen_host_ports.py; the others refuse) against Leyte's own CODE 4 run on the 68000
## emulator with 1.0.3's case generators (tools/leyte/gen_leyte_host_cases.py ID04): only the verified
## functions' cases kept; a case whose Leyte run stopped is listed in its fixture's "not_run".

const FIXTURES := ["about","dialog","draw","filter","scroll"]

func run() -> bool:
	var ok := true
	for name in FIXTURES:
		ok = SegmentCases.replay("res://tests/leyte/port/fixtures/id04_%s_cases.json" % name, LeyteID04) and ok
	return ok
