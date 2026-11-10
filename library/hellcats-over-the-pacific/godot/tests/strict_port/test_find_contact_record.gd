extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68kScript := preload("res://strict_port/compat/memory_68k.gd")
const PacificConflictScript := preload("res://strict_port/code/pacific_conflict.gd")

const MEMORY_SIZE := 0x30000
const TABLE := 0x1bdbc

func _write_record(memory, record: int, param_1: int, param_2: int) -> void:
	memory.write_u32(record, param_1)
	memory.write_u32(record + 4, param_2)

func run() -> bool:
	var ok := true
	var memory = Memory68kScript.new(MEMORY_SIZE)
	var strict_port = PacificConflictScript.new(memory)

	var param_1 := 0x10203040
	var param_2 := 0x50607080
	var slot: int = PacificConflictScript.contact_pair_hash(param_1, param_2)
	var wrong_record := 0x20000
	var wanted_record := 0x20020
	_write_record(memory, wrong_record, param_1, 0x11111111)
	_write_record(memory, wanted_record, param_1, param_2)
	memory.write_u32(TABLE + slot * 4, wrong_record)
	memory.write_u32(TABLE + ((slot + 1) & 0xff) * 4, wanted_record)
	ok = Assertions.assert_equal(strict_port.find_contact_record(param_1, param_2), wanted_record, "linear probe finds matching pair") and ok

	var wrap_a := 0xff
	var wrap_b := 0
	var wrap_record := 0x20040
	_write_record(memory, wrap_record, wrap_a, wrap_b)
	memory.write_u32(TABLE + 0xff * 4, wrong_record)
	memory.write_u32(TABLE, wrap_record)
	ok = Assertions.assert_equal(strict_port.find_contact_record(wrap_a, wrap_b), wrap_record, "probe wraps slot 255 to zero") and ok

	var full_memory = Memory68kScript.new(MEMORY_SIZE)
	var full_port = PacificConflictScript.new(full_memory)
	for index in range(256):
		full_memory.write_u32(TABLE + index * 4, wrong_record)
	_write_record(full_memory, wrong_record, 1, 2)
	ok = Assertions.assert_equal(full_port.find_contact_record(3, 4), 0, "256 misses return null pointer") and ok
	return ok
