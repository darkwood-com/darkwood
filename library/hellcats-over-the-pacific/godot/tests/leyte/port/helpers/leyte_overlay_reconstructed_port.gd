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
		match int(entry[2]):
			0:
				code_table[int(entry[0])] = func() -> int: return log_call(base, [], di, dn, di2, dn2)
			1:
				code_table[int(entry[0])] = func(a: int) -> int: return log_call(base, [a], di, dn, di2, dn2)
			2:
				code_table[int(entry[0])] = func(a: int, b: int) -> int: return log_call(base, [a, b], di, dn, di2, dn2)
			3:
				code_table[int(entry[0])] = func(a: int, b: int, c: int) -> int: return log_call(base, [a, b, c], di, dn, di2, dn2)
			4:
				code_table[int(entry[0])] = func(a: int, b: int, c: int, d: int) -> int: return log_call(base, [a, b, c, d], di, dn, di2, dn2)
			5:
				code_table[int(entry[0])] = func(a: int, b: int, c: int, d: int, e: int) -> int: return log_call(base, [a, b, c, d, e], di, dn, di2, dn2)
			6:
				code_table[int(entry[0])] = func(a: int, b: int, c: int, d: int, e: int, f: int) -> int: return log_call(base, [a, b, c, d, e, f], di, dn, di2, dn2)
			7:
				code_table[int(entry[0])] = func(a: int, b: int, c: int, d: int, e: int, f: int, g: int) -> int: return log_call(base, [a, b, c, d, e, f, g], di, dn, di2, dn2)
			8:
				code_table[int(entry[0])] = func(a: int, b: int, c: int, d: int, e: int, f: int, g: int, h: int) -> int: return log_call(base, [a, b, c, d, e, f, g, h], di, dn, di2, dn2)
			9:
				code_table[int(entry[0])] = func(a: int, b: int, c: int, d: int, e: int, f: int, g: int, h: int, i: int) -> int: return log_call(base, [a, b, c, d, e, f, g, h, i], di, dn, di2, dn2)

## Appends the arguments to the log at out (long call count, then the longs of each call).
