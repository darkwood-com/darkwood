extends RefCounted
## Leyte Gulf's routines the runtime writes (as unexported.gd does for 1.0.3), registered where Leyte's
## ports call them: CODE 5's protection helpers Ghidra did not export. Leyte's CODE 5 is 1.0.3's
## (host_ports.json "remap"), so each is 1.0.3's (unexported.gd) with Leyte's A5 operands, read from
## Leyte's bytes (sources/leyte-gulf-1-0/CODE/ID05.bin).

var machine
var memory
var a5: int
var id05
var id02
var id06

func _init(owner) -> void:
	machine = owner
	memory = owner.memory
	a5 = owner.A5
	var segments = owner.segments
	id05 = segments.instances[5]
	id02 = segments.instances[2]
	segments.register_local(5, 0x3ba, func(a: Array) -> int: return id05_03ba(a[0], a[1], a[2], a[3]))
	# CODE 5 +0x4 (jump-table slot $5ba, called by the CODE 8 loader after FSClose): RTS, as in 1.0.3.
	segments.stand_ins[0x5ba] = func(_a: Array) -> int: return 0
	# CODE 5 +0x8b2 (slot $58a; 1.0.3's $532, arm_restore_gate): ADDQ.W #1, -$bc(A5); RTS.
	segments.stand_ins[0x58a] = func(_a: Array) -> int: return id05_08b2()
	# CODE 6 +0x858 (slot $1da; 1.0.3's $1b2, release_render_buffers).
	id06 = segments.instances[6]
	segments.stand_ins[0x1da] = func(_a: Array) -> int: return id06_0858()

func _g(offset: int) -> int:
	return (a5 + offset) & 0xffffffff

func _run(port, method: String, args: Array = []) -> int:
	var saved: int = port.a7
	port.a7 = machine.free_stack()
	var r = port.callv(method, args)
	port.a7 = saved
	return 0 if r == null else int(r)

func _jt(offset: int, args: Array = []) -> int:
	return id02.jt_routines[offset].call(args)

## CODE 5 0x3ba (build_protection_code_strings; 1.0.3's id05_03ba): the same instructions with Leyte's
## operands: GetVol JT $2da, the C string JT $33a, strcmp JT $562, the string copy JT $572, the equal
## case's string at A5 - 0xda4 (1.0.3 - 0xd66); A5 - 0x2c8 and - 0xca are where 1.0.3 has them.
func id05_03ba(code: int, code_text: int, other: int, other_text: int) -> int:
	var frame: int = machine.free_stack()
	var a6: int = frame - 4
	for k in 0xfe:
		memory.write_u8(a6 - 0x10c + k, memory.read_u8(_g(-0x2c8) + k))
	_run(id05, "get_machine_screen_fingerprint", [a6 - 2, a6 - 4, a6 - 6, a6 - 8, a6 - 0xa])
	_run(id05, "read_TEXT128_pair", [code, other])
	if memory.read_u32(code) == memory.read_u32(other):
		_jt(0x2da, [a6 - 0xc, a6 - 0x10c])
		var name: int = _jt(0x33a, [a6 - 0x10c])
		if _jt(0x562, [name, _g(-0xca)]) & 0xffff == 0:
			_run(id05, "write_TEXT128_pair", [0, _run(id05, "random_28bit_value")])
	_run(id05, "read_TEXT128_pair", [code, other])
	if memory.read_u32(other) == memory.read_u32(code):
		_jt(0x572, [other_text, memory.read_u32(_g(-0xda4))])
	else:
		var fields: Array = []
		for off in [2, 4, 6, 8, 0xa]:
			fields.append(memory.read_u16(a6 - off))
		_jt(0x572, [other_text, _run(id05, "pack_fields_to_code_buf68", fields)])
		_jt(0x572, [other_text + 6, _run(id05, "encode_code28_to_buf54", [memory.read_u32(other)])])
	_jt(0x572, [code_text, _run(id05, "encode_long_to_buf40", [memory.read_u32(code)])])
	return 0

## CODE 5 0x8b2 (arm_restore_gate): A5 - 0xbc counts up.
func id05_08b2() -> int:
	memory.write_u16(_g(-0xbc), (memory.read_u16(_g(-0xbc)) + 1) & 0xffff)
	return 0

## CODE 6 0x858 (release_render_buffers; 1.0.3's id06_0858), as Leyte's bytes read: 0x15a frees the
## pattern tables A5 - 0x1f60 then - 0x1f64 (free_aligned, each cleared); 0x582 the edge pool A5 -
## 0x1d5c when set (cleared); free_row_table (0x512); the raster target A5 - 0x1d54 when set (cleared).
func id06_0858() -> int:
	for slot in [-0x1f60, -0x1f64]:
		_run(id06, "free_aligned", [memory.read_u32(_g(slot))])
		memory.write_u32(_g(slot), 0)
	if memory.read_u32(_g(-0x1d5c)) != 0:
		_run(id06, "free_aligned", [memory.read_u32(_g(-0x1d5c))])
	memory.write_u32(_g(-0x1d5c), 0)
	_run(id06, "free_row_table")
	if memory.read_u32(_g(-0x1d54)) != 0:
		_run(id06, "free_aligned", [memory.read_u32(_g(-0x1d54))])
	memory.write_u32(_g(-0x1d54), 0)
	return 0
