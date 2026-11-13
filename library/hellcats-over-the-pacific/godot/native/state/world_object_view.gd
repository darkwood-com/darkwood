extends RefCounted

## A typed view over one world object record (pointer arrays DAT_0001d4a4 / DAT_0001b5a4 /
## DAT_0001d0e4) in the shared Mac memory: no copy, every access a read or write of the record's
## bytes at the original offset and width (docs/reference/STRUCTS.md, "World object"). Only the
## [PROVEN] fields are typed; signedness follows the Original port's reads (longs signed, pointers
## unsigned, words signed); setters wrap to the field's width as the 68000 stores do. Everything
## else (+0x04 long, +0x28, +0x40, kind-specific use of the overlapping words) stays raw: `raw_*`,
## unresolved.
##
## Overlaps, as in the record: byte +0x06 is the high byte of word +0x06 and byte +0x07 its low
## byte (`list_bits`); word +0x2e is the low half of long +0x2c; +0x30 is a long (a root's child
## table) or a word (other objects' distance band), and word +0x32 is the long's low half.
## Long +0x04 (unresolved as a whole, raw) spans bytes +4..+7: byte +5 is its bits 16..23 (`flags5`
## bit 0x10 = long bit 0x100000), word +6 its low half (`flags6`), byte +7 its bits 0..7
## (`list_bits` bit 1 = long bit 2, the hidden bit the far pass tests on the long).
func flags_long() -> int: return memory.read_u32(address + 4)        ## raw, unresolved as a whole
func set_flags6(v: int) -> void: memory.write_u16(address + 6, v)
func set_flags_long(v: int) -> void: memory.write_u32(address + 4, v)
func set_vertices(v: int) -> void: memory.write_u32(address + 0x44, v)
func set_child_table(v: int) -> void: memory.write_u32(address + 0x30, v)
func saved_min_start() -> int: return memory.read_s32(address + 0x28)  ## unresolved: far pass running minimum / far sort save
func set_saved_min_start(v: int) -> void: memory.write_u32(address + 0x28, v)

const STRIDE_VERTEX := 0xc
const STRIDE_FACE := 0x20

var memory
var address: int

func _init(memory_, address_: int) -> void:
	memory = memory_
	address = address_

func kind() -> int: return memory.read_u8(address)                  ## +0x00: 2 shape, 3 group
func child_index() -> int: return memory.read_u8(address + 1)        ## +0x01
func slot() -> int: return memory.read_u8(address + 2)               ## +0x02
func refcount() -> int: return memory.read_u8(address + 3)           ## +0x03
func set_refcount(v: int) -> void: memory.write_u8(address + 3, v)
func flags5() -> int: return memory.read_u8(address + 5)             ## +0x05 (bit 0x10 camera-facing)
func flags6() -> int: return memory.read_u16(address + 6)            ## +0x06 word
func list_bits() -> int: return memory.read_u8(address + 7)          ## +0x07: bit 0 far list, bit 1 skip
func controller() -> int: return memory.read_u32(address + 8)        ## +0x08
func set_controller(v: int) -> void: memory.write_u32(address + 8, v)
func active_range() -> int: return memory.read_s32(address + 0xc)    ## +0x0c
func camera_metric() -> int: return memory.read_s32(address + 0x10) ## +0x10
func set_camera_metric(v: int) -> void: memory.write_u32(address + 0x10, v)
func extent() -> int: return memory.read_s32(address + 0x14)         ## +0x14
func x() -> int: return memory.read_s32(address + 0x18)              ## +0x18
func y() -> int: return memory.read_s32(address + 0x1c)              ## +0x1c
func z() -> int: return memory.read_s32(address + 0x20)              ## +0x20
func set_x(v: int) -> void: memory.write_u32(address + 0x18, v)
func set_y(v: int) -> void: memory.write_u32(address + 0x1c, v)
func set_z(v: int) -> void: memory.write_u32(address + 0x20, v)
func parent() -> int: return memory.read_u32(address + 0x24)         ## +0x24
func support_list() -> int: return memory.read_u32(address + 0x2c)   ## +0x2c
func child_template_count() -> int: return memory.read_s16(address + 0x2e)  ## +0x2e (root)
func child_table() -> int: return memory.read_u32(address + 0x30)    ## +0x30 long (root)
func distance_band() -> int: return memory.read_s16(address + 0x30) ## +0x30 word (other objects)
func set_distance_band(v: int) -> void: memory.write_u16(address + 0x30, v)
func projection_shift() -> int: return memory.read_s16(address + 0x32)
func set_projection_shift(v: int) -> void: memory.write_u16(address + 0x32, v)
func scale_shift() -> int: return memory.read_s16(address + 0x34)
func vertex_count() -> int: return memory.read_s16(address + 0x36)
func part_count() -> int: return memory.read_s16(address + 0x38)
func face_count() -> int: return memory.read_s16(address + 0x3a)
func last_active_frame() -> int: return memory.read_s32(address + 0x3c)
func set_last_active_frame(v: int) -> void: memory.write_u32(address + 0x3c, v)
func vertices() -> int: return memory.read_u32(address + 0x44)       ## 12-byte x / y / z
func faces() -> int: return memory.read_u32(address + 0x48)          ## 0x20-byte records
func vertices2() -> int: return memory.read_u32(address + 0x4c)      ## transformed copy

## Addresses, as the original indexes them (pointer read again on every call).
func vertex_address(i: int) -> int: return vertices() + i * STRIDE_VERTEX
func vertex2_address(i: int) -> int: return vertices2() + i * STRIDE_VERTEX
func face_address(i: int) -> int: return faces() + i * STRIDE_FACE

## Unresolved fields: raw access at an offset.
func raw_u8(offset: int) -> int: return memory.read_u8(address + offset)
func raw_u16(offset: int) -> int: return memory.read_u16(address + offset)
func raw_u32(offset: int) -> int: return memory.read_u32(address + offset)
func raw_write_u8(offset: int, v: int) -> void: memory.write_u8(address + offset, v)
func raw_write_u16(offset: int, v: int) -> void: memory.write_u16(address + offset, v)
func raw_write_u32(offset: int, v: int) -> void: memory.write_u32(address + offset, v)
