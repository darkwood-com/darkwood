class_name Memory68k
extends RefCounted
## Bounds-checked flat Macintosh address space with explicit big-endian access.

const C68kScript := preload("res://strict_port/compat/c68k.gd")

var _bytes := PackedByteArray()
var _base_address: int

func _init(size: int, base_address: int = 0) -> void:
	assert(size >= 0, "memory size must not be negative")
	_base_address = base_address
	_bytes.resize(size)

func contains(address: int, width: int = 1) -> bool:
	var offset := address - _base_address
	return width >= 0 and offset >= 0 and offset + width <= _bytes.size()

func _offset(address: int, width: int) -> int:
	assert(contains(address, width), "68000 memory access out of bounds")
	return address - _base_address

func read_u8(address: int) -> int:
	return _bytes[_offset(address, 1)]

func read_s8(address: int) -> int:
	return C68kScript.s8(read_u8(address))

func read_u16(address: int) -> int:
	var offset := _offset(address, 2)
	return (_bytes[offset] << 8) | _bytes[offset + 1]

func read_s16(address: int) -> int:
	return C68kScript.s16(read_u16(address))

func read_u32(address: int) -> int:
	var offset := _offset(address, 4)
	return ((_bytes[offset] << 24) | (_bytes[offset + 1] << 16)
		| (_bytes[offset + 2] << 8) | _bytes[offset + 3]) & 0xffffffff

func read_s32(address: int) -> int:
	return C68kScript.s32(read_u32(address))

func write_u8(address: int, value: int) -> void:
	_bytes[_offset(address, 1)] = C68kScript.u8(value)

func write_u16(address: int, value: int) -> void:
	var offset := _offset(address, 2)
	value = C68kScript.u16(value)
	_bytes[offset] = (value >> 8) & 0xff
	_bytes[offset + 1] = value & 0xff

func write_u32(address: int, value: int) -> void:
	var offset := _offset(address, 4)
	value = C68kScript.u32(value)
	_bytes[offset] = (value >> 24) & 0xff
	_bytes[offset + 1] = (value >> 16) & 0xff
	_bytes[offset + 2] = (value >> 8) & 0xff
	_bytes[offset + 3] = value & 0xff
