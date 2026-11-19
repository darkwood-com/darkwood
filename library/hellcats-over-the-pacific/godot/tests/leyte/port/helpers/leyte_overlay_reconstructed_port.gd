extends "res://original/leyte/port/pacific_conflict_reconstructed.gd"

## Leyte's overlay port with the fixtures' code pointers logging their calls (count, then each call's
## longs), as tests/original/port/helpers/stubbed_port.gd does for 1.0.3's (that part copied; its
## spawn stubs are 1.0.3's port's and left out).

func log_call(base: int, args: Array, deref_index: int = -1, deref_n: int = 0, deref2_index: int = -1, deref2_n: int = 0) -> int:
	var values: Array = args.duplicate()
	for k in deref_n:
		values.append(memory.read_u32(C68kScript.u32(args[deref_index]) + 4 * k))
	for k in deref2_n:
		values.append(memory.read_u32(C68kScript.u32(args[deref2_index]) + 4 * k))
	var count: int = memory.read_u32(base)
	for i in values.size():
		memory.write_u32(base + 4 + (count * values.size() + i) * 4, C68kScript.u32(values[i]))
	memory.write_u32(base, count + 1)
	return memory.read_u32(base - 4)

func install_call_logs(logs: Array) -> void:
	for entry in logs:
		var base: int = int(entry[1])
		var di: int = int(entry[3]) if entry.size() > 3 else -1
		var dn: int = int(entry[4]) if entry.size() > 4 else 0
		var di2: int = int(entry[5]) if entry.size() > 5 else -1
		var dn2: int = int(entry[6]) if entry.size() > 6 else 0
		var n: int = int(entry[2])
		# a stand-in logs its first n longs; a caller may push more (the Leyte printf reads six longs whatever the
		# caller pushed), which the original's stand-in never sees
		code_table[int(entry[0])] = func(a: int = 0, b: int = 0, c: int = 0, d: int = 0, e: int = 0, f: int = 0, g: int = 0, h: int = 0, i: int = 0) -> int:
			return log_call(base, [a, b, c, d, e, f, g, h, i].slice(0, n), di, dn, di2, dn2)

## Appends the arguments to the log at out (long call count, then the longs of each call).
