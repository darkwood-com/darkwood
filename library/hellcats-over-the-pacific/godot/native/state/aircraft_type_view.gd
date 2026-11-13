extends RefCounted

## A typed view over the aircraft type record of the aircraft being updated (the definition
## record DAT_0001b524[key]; update_aircraft_entity points DAT_00026004 at it, controller +4, each
## tick) in the shared Mac memory, no copy (docs/reference/STRUCTS.md, "Aircraft type record":
## signed words but the long +0x38). Only the fields of the leaves migrated so far are typed, under
## the roles STRUCTS.md gives them; the record's other words stay raw.

const CURRENT := 0x26004            ## aircraft_type_rec pointer (long)

var memory
var address: int

func _init(memory_, address_: int) -> void:
	memory = memory_
	address = address_

## The record update_aircraft_entity selected for this tick.
static func current(memory_):
	return new(memory_, memory_.read_u32(CURRENT))

func lift_base() -> int: return memory.read_s16(address + 0x26)          ## clean
func lift_slope() -> int: return memory.read_s16(address + 0x28)
func lift_base_flaps() -> int: return memory.read_s16(address + 0x2a)    ## full flaps
func lift_slope_flaps() -> int: return memory.read_s16(address + 0x2c)
func stall_angle() -> int: return memory.read_s16(address + 0x34)
func rated_power() -> int: return memory.read_s32(address + 0x38)        ## long

func raw_u16(offset: int) -> int: return memory.read_u16(address + offset)
func raw_u32(offset: int) -> int: return memory.read_u32(address + offset)
