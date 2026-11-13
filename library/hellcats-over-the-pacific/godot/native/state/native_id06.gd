extends "res://original/port/ID06.gd"

## The Native edition's CODE 6 (the host's 1-bit renderer): the Original port, its memory the host
## routed view (host_routed_memory.gd), because it reads the overlay's screen polygon and edge
## state in place, which the Native overlay keeps in Godot.

const HostRoutedMemory := preload("res://native/state/host_routed_memory.gd")

func _init(memory_68k = null, a5_value: int = 0) -> void:
	super(HostRoutedMemory.new(memory_68k) if memory_68k != null else null, a5_value)
