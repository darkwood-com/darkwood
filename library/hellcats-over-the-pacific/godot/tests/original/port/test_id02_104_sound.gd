extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68k := preload("res://original/compat/memory_68k.gd")
const Driver := preload("res://original/port/ID02_104.gd")
const FIXTURE := "res://tests/original/port/fixtures/id02_104_sound_cases.json"

## The 1.0.4 sound driver's port (ID02_104.gd) against what the 1.0.4 bytes did on the interpreter
## (tools/original/gen_id02_104_sound_cases.py): same set-up, same trap answers; D0, the trap calls,
## every watched long and the mixer's bytes must match.
func run() -> bool:
	var doc: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(FIXTURE))
	var ok := true
	var failures := 0
	for c in doc["cases"]:
		if not _case(c):
			failures += 1
			ok = false
	return Assertions.assert_equal(failures, 0, "%d cases replayed" % doc["cases"].size()) and ok

func _case(c: Dictionary) -> bool:
	var m = Memory68k.new(0x40000)
	var d = Driver.new(m, 0)
	for w in c["writes"]:
		if int(w[1]) == 2:
			m.write_u16(int(w[0]), int(w[2]))
		else:
			m.write_u32(int(w[0]), int(w[2]))
	for b in c["blocks"]:
		var at: int = int(b[0])
		var seed: int = int(b[2])
		for i in int(b[1]):
			m.write_u8(at + i, (i * 37 + seed * 101 + (i >> 5)) & 0xff)
	var answers: Dictionary = c["answers"]
	var ptrs: Array = answers.get("NewPtrClear", []).duplicate()
	var log: Array = []
	d.traps[0xa31e] = func(a: Array) -> int:
		log.append(["NewPtrClear", a[0]])
		return int(ptrs.pop_front()) if not ptrs.is_empty() else 0
	d.traps[0xa807] = func(a: Array) -> int:
		log.append(["SndNewChannel", a[3], a[2], a[1], a[0]])
		m.write_u32(a[3], 0x35000)
		return int(answers.get("SndNewChannel", 0))
	d.traps[0xa800] = func(a: Array) -> int:
		log.append(["SoundDispatch", a[2], a[1], a[0]])
		return int(answers.get("SoundDispatch", 0))
	d.traps[0xa801] = func(a: Array) -> int:
		log.append(["SndDisposeChannel", a[1], a[0]])
		return 0
	d.traps[0xa01f] = func(a: Array) -> int:
		log.append(["DisposePtr", a[0]])
		return 0
	var args: Array = []
	for v in c["args"]:
		args.append(int(v))
	var d0 = d.callv(c["routine"], args)
	var ok := true
	if c["d0"] != null:
		ok = Assertions.assert_equal(int(d0) & 0xffffffff, int(c["d0"]), "%s: D0" % c["name"]) and ok
	var want_log: Array = []
	for entry in c["log"]:
		var e: Array = [entry[0]]
		for v in entry.slice(1):
			e.append(int(v))
		want_log.append(e)
	ok = Assertions.assert_equal(log, want_log, "%s: traps" % c["name"]) and ok
	for r in c["reads"]:
		var got: int = m.read_u16(int(r[0])) if int(r[1]) == 2 else m.read_u32(int(r[0]))
		if got != int(r[2]):
			ok = Assertions.assert_equal(got, int(r[2]), "%s: %x" % [c["name"], int(r[0])]) and ok
			break
	for b in c["bytes"]:
		var got_bytes: String = m._bytes.slice(int(b[0]), int(b[0]) + int(b[1])).hex_encode()
		ok = Assertions.assert_equal(got_bytes, b[2], "%s: bytes at %x" % [c["name"], int(b[0])]) and ok
	return ok
