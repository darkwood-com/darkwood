extends RefCounted

const SegmentCases := preload("res://tests/original/port/helpers/segment_cases.gd")
const LeyteID05 := preload("res://original/leyte/port/ID05.gd")

## Leyte Gulf's generated host ports (tools/leyte/gen_host_ports.py) against Leyte's own code run on
## the 68000 emulator with 1.0.3's case generators (tools/leyte/gen_leyte_host_cases.py): ID05's
## protection routines and code dialog. ID06's cases are not built yet (its scenes are recorded runs).

func run() -> bool:
	var ok := SegmentCases.replay("res://tests/leyte/port/fixtures/id05_protection_cases.json", LeyteID05)
	return SegmentCases.replay("res://tests/leyte/port/fixtures/id05_dialog_cases.json", LeyteID05) and ok
