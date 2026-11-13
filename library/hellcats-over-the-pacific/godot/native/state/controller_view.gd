extends RefCounted

## A typed view over one controller record (pointed to by world object +0x08) in the shared Mac
## memory: no copy, original offsets and widths (docs/reference/STRUCTS.md, "Controller"). The
## common header is typed; of the kind-specific layouts only the aircraft fields are, from
## update_aircraft_entity 0xe792 and its helpers. Signedness follows the Original port's reads;
## setters wrap to the width. Other kinds' fields (effect, ordnance, ship, spinner beyond +0x22)
## share these offsets with other meanings and stay raw, unresolved.
##
## Word +0x684 is big-endian: byte +0x684 is its bits 8..15 (a `btst #n,$684` is word bit n + 8).

var memory
var address: int

func _init(memory_, address_: int) -> void:
	memory = memory_
	address = address_

func owner() -> int: return memory.read_u32(address)                 ## +0x00: the world object
func pitch() -> int: return memory.read_s16(address + 0x20)          ## +0x20 attitude words
func heading() -> int: return memory.read_s16(address + 0x22)        ## +0x22
func bank() -> int: return memory.read_s16(address + 0x24)           ## +0x24
func set_pitch(v: int) -> void: memory.write_u16(address + 0x20, v)
func set_heading(v: int) -> void: memory.write_u16(address + 0x22, v)
func set_bank(v: int) -> void: memory.write_u16(address + 0x24, v)
func ground_height() -> int: return memory.read_s32(address + 0x2e)  ## +0x2e (ordnance)

# Aircraft controller.
func pose_ring_index() -> int: return memory.read_s16(address + 0x612)
func pose_buffer() -> int: return memory.read_u32(address + 0x616)
func angle_of_attack() -> int: return memory.read_s16(address + 0x626)   ## angle units (0x4000 a turn): the lift curve's input (stall at type +0x34), stick-driven toward stick + type +0x44, capped by type +0x42
func movement() -> int: return memory.read_s32(address + 0x62a)
func bombing_stage() -> int: return memory.read_s8(address + 0x675)
func gear_position() -> int: return memory.read_s8(address + 0x679)
func gear_bits() -> int: return memory.read_u8(address + 0x67a)
func flaps() -> int: return memory.read_s8(address + 0x67b)
func bombs() -> int: return memory.read_s8(address + 0x67c)
func damage() -> int: return memory.read_u16(address + 0x684)
func damage_previous() -> int: return memory.read_u16(address + 0x686)
func damage_timer() -> int: return memory.read_u16(address + 0x688)
func ai_target() -> int: return memory.read_u32(address + 0x692)
func release_frame() -> int: return memory.read_s32(address + 0x6ac)
func set_gear_position(v: int) -> void: memory.write_u8(address + 0x679, v)
func set_flaps(v: int) -> void: memory.write_u8(address + 0x67b, v)  ## the AI's bias byte (0x10cd8)
func set_bombs(v: int) -> void: memory.write_u8(address + 0x67c, v)
func set_throttle_byte(v: int) -> void: memory.write_u8(address + 0x668, v)
func pitch_error_previous() -> int: return memory.read_s16(address + 0x61a)   ## steering errors
func set_pitch_error_previous(v: int) -> void: memory.write_u16(address + 0x61a, v)
func bomb_phase() -> int: return memory.read_u8(address + 0x674)   ## 0x10e28 section check
func set_bomb_phase(v: int) -> void: memory.write_u8(address + 0x674, v)

# Aircraft AI throttle and control smoothing (adjust_ai_throttle_vs_speed 0x10c2c,
# max_ai_throttle_maybe_invert_679 0x10cb2, enqueue_control_inputs_avg4 0x10558: byte-checked).
const CONTROL_RING := [0x644, 0x64c, 0x654]   ## aileron / elevator / rudder: 4 words each
const CONTROL_SUM := [0x65c, 0x65e, 0x660]    ## running sums, words
const CONTROL_OUT := [0x666, 0x662, 0x664]    ## sum / 4, words
func speed_error_previous() -> int: return memory.read_s16(address + 0x620)
func set_speed_error_previous(v: int) -> void: memory.write_u16(address + 0x620, v)
func throttle_remainder() -> int: return memory.read_s16(address + 0x63c)
func set_throttle_remainder(v: int) -> void: memory.write_u16(address + 0x63c, v)
func ai_throttle() -> int: return memory.read_s8(address + 0x668)   ## 0..9 once clamped
func set_ai_throttle(v: int) -> void: memory.write_u8(address + 0x668, v)
func control_ring_index() -> int: return memory.read_s16(address + 0x642)
func set_control_ring_index(v: int) -> void: memory.write_u16(address + 0x642, v)
func control_slot(channel: int, i: int) -> int: return memory.read_u16(address + CONTROL_RING[channel] + i * 2)
func set_control_slot(channel: int, i: int, v: int) -> void: memory.write_u16(address + CONTROL_RING[channel] + i * 2, v)
func control_sum(channel: int) -> int: return memory.read_s16(address + CONTROL_SUM[channel])
func set_control_sum(channel: int, v: int) -> void: memory.write_u16(address + CONTROL_SUM[channel], v)
func set_control_output(channel: int, v: int) -> void: memory.write_u16(address + CONTROL_OUT[channel], v)

## Unresolved fields: raw access at an offset.
func raw_u8(offset: int) -> int: return memory.read_u8(address + offset)
func raw_u16(offset: int) -> int: return memory.read_u16(address + offset)
func raw_u32(offset: int) -> int: return memory.read_u32(address + offset)
func raw_write_u8(offset: int, v: int) -> void: memory.write_u8(address + offset, v)
func raw_write_u16(offset: int, v: int) -> void: memory.write_u16(address + offset, v)
func raw_write_u32(offset: int, v: int) -> void: memory.write_u32(address + offset, v)
