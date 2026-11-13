extends RefCounted

## A host segment's memory on the Native edition (temporary compatibility adapter, until the host
## program is Native): the overlay's routed view (routed_memory.gd) when a Native overlay is loaded,
## the Mac memory otherwise. For host code that reads or writes the overlay's state in place (record
## watch, loop 7: ID06's 1-bit polygon renderer reads the overlay's screen polygon 0x24df2..).

const RoutedPort := preload("res://native/state/routed_port.gd")

var memory

func _init(memory_) -> void:
	memory = memory_

func _view():
	return RoutedPort.active_router if RoutedPort.active_router != null else memory

func read_u8(address: int) -> int: return _view().read_u8(address)
func read_s8(address: int) -> int: return _view().read_s8(address)
func read_u16(address: int) -> int: return _view().read_u16(address)
func read_s16(address: int) -> int: return _view().read_s16(address)
func read_u32(address: int) -> int: return _view().read_u32(address)
func read_s32(address: int) -> int: return _view().read_s32(address)
func write_u8(address: int, v: int) -> void: _view().write_u8(address, v)
func write_u16(address: int, v: int) -> void: _view().write_u16(address, v)
func write_u32(address: int, v: int) -> void: _view().write_u32(address, v)
