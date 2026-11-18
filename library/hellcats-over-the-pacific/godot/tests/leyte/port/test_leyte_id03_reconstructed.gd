extends RefCounted

const SegmentCases := preload("res://tests/original/port/helpers/segment_cases.gd")
const LeyteReconstructed := preload("res://original/leyte/port/ID03_reconstructed.gd")

## Leyte Gulf's CODE 3 functions written from Leyte's bytes (original/leyte/port/ID03_reconstructed.gd)
## against Leyte's own code on the 68000 emulator (fixtures/id03_reconstructed_cases.json,
## tools/leyte/gen_leyte_id03_reconstructed_cases.py).

func run() -> bool:
	return SegmentCases.replay("res://tests/leyte/port/fixtures/id03_reconstructed_cases.json", LeyteReconstructed)
