extends RefCounted

const SegmentCases := preload("res://tests/original/port/helpers/segment_cases.gd")
const LeyteID07 := preload("res://original/leyte/port/ID07.gd")

## Leyte Gulf's ID07 (the STR# 129 string table): the port generated from 1.0.3's with its A5
## constants remapped (tools/leyte/gen_host_ports.py) against Leyte's own CODE 7 run on the 68000
## emulator (fixtures/id07_cases.json, tools/leyte/gen_leyte_id07_cases.py): the same memory changes.

func run() -> bool:
	return SegmentCases.replay("res://tests/leyte/port/fixtures/id07_cases.json", LeyteID07)
