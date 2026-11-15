extends RefCounted

const SegmentCases := preload("res://tests/original/port/helpers/segment_cases.gd")
const LeyteID02 := preload("res://original/leyte/port/ID02.gd")

## Leyte Gulf's ID02 port ("partial": the 84 functions proven by instruction comparison,
## tools/leyte/gen_host_ports.py; the others refuse) against Leyte's own CODE 2 run on the 68000
## emulator with 1.0.3's case generators (tools/leyte/gen_leyte_host_cases.py ID02): only the verified
## functions' cases are kept; a case whose Leyte run reached an unverified routine is listed in its
## fixture's "not_run", not replayed.

const FIXTURES := ["trapfree", "crt", "glue", "memory", "draw", "palette", "toolbox", "dialog"]

func run() -> bool:
	var ok := true
	for name in FIXTURES:
		ok = SegmentCases.replay("res://tests/leyte/port/fixtures/id02_%s_cases.json" % name, LeyteID02) and ok
	return ok
