extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const PacificConflict := preload("res://strict_port/code/pacific_conflict.gd")

## Expected vectors are derived from the independently verified 68000 sequence:
## MOVE/shift produces (b << 2) ^ a, then ANDI.L #$ff.
const VECTORS := [
	[0x00, 0x00, 0x00],
	[0x01, 0x01, 0x05],
	[0xff, 0x00, 0xff],
	[0x00, 0x40, 0x00],
	[0x5a, 0x7f, 0xa6],
	[0x5a, 0x80, 0x5a],
	[0x12, 0xff, 0xee],
]

func run() -> bool:
	var ok := true
	for vector in VECTORS:
		var actual: int = PacificConflict.contact_pair_hash(vector[0], vector[1])
		ok = Assertions.assert_equal(actual, vector[2], "contact_pair_hash(%d,%d)" % [vector[0], vector[1]]) and ok
	return ok
