extends RefCounted

const SegmentCases := preload("res://tests/original/port/helpers/segment_cases.gd")
const LeyteReconstructed := preload("res://original/leyte/port/ID02_reconstructed.gd")

## Leyte Gulf's CODE 2 functions written from Leyte's bytes (original/leyte/port/ID02_reconstructed.gd)
## against Leyte's own code on the 68000 emulator (fixtures/id02_reconstructed_cases.json,
## tools/leyte/gen_leyte_id02_reconstructed_cases.py).

func run() -> bool:
	return SegmentCases.replay("res://tests/leyte/port/fixtures/id02_reconstructed_cases.json", LeyteReconstructed)
