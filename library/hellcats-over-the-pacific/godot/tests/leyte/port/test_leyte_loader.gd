extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const Memory68kScript := preload("res://original/compat/memory_68k.gd")
const LeyteID08 := preload("res://original/leyte/port/ID08.gd")

## Leyte Gulf's overlay loader port (original/leyte/port/ID08.gd load_overlay_from_data_fork) run on
## the application's real data fork against what Leyte's own CODE 8 loader built on the 68000
## emulator (fixtures/loader_image.json, tools/leyte/gen_loader_fixture.py): the same header, image
## base, heap blocks, File Manager calls, and image and payload bytes (SHA-256), up to JT $51a, where
## the emulator run stops. The stand-ins serve the fork as the harness's File Manager does
## (tools/leyte/loader_harness.py: one file, "Leyte Gulf", read at the mark; fnfErr / rfNumErr /
## eofErr), allocate as its heap (4-aligned from 0x300000). And an external relocation record must
## take the loader's error path (the message A5 - 0xe2c) and drop the image.

const FORK := "../sources/leyte-gulf-1-0/Leyte Gulf overlay"
const FIXTURE := "res://tests/leyte/port/fixtures/loader_image.json"
const A5 := 0x200000
const STACK := 0x1f0000
const HEAP := 0x300000

class Machine:
	var memory
	var fork: PackedByteArray
	var marks: Dictionary = {}
	var log: Array = []
	var heap_next: int = HEAP
	var heap: Array = []
	var calls: Array = []
	var after_load: Dictionary = {}

	func _init(bytes: PackedByteArray) -> void:
		memory = Memory68kScript.new(0x400000)
		fork = bytes
		var name := "Leyte Gulf".to_ascii_buffer()     # low memory CurApName, as the harness sets it
		memory.write_u8(0x910, name.size())
		for k in name.size():
			memory.write_u8(0x911 + k, name[k])
		memory.write_u32(0x904, A5)

	func pstring(at: int) -> String:
		var n: int = memory.read_u8(at)
		var s := PackedByteArray()
		for k in n:
			s.append(memory.read_u8(at + 1 + k))
		return s.get_string_from_ascii()

	func fs_open(args: Array) -> int:      # [VAR refNum, vRefNum, name]
		var name := pstring(args[2])
		if name != "Leyte Gulf":
			log.append(["open", name, null, -43])
			return -43 & 0xffff
		marks[3] = 0
		memory.write_u16(args[0], 3)
		log.append(["open", name, 3, 0])
		return 0

	func fs_read(args: Array) -> int:      # [buffer, VAR count, refNum]
		var ref: int = args[2]
		var count: int = memory.read_u32(args[1])
		if not marks.has(ref):
			log.append(["read", ref, null, count, 0, -51])
			return -51 & 0xffff
		var mark: int = marks[ref]
		var got: int = mini(count, fork.size() - mark)
		for k in got:
			memory.write_u8(args[0] + k, fork[mark + k])
		marks[ref] = mark + got
		memory.write_u32(args[1], got)
		var err: int = 0 if got == count else -39
		log.append(["read", ref, mark, count, got, err])
		return err & 0xffff

	func get_fpos(args: Array) -> int:     # [VAR pos, refNum]
		var ref: int = args[1]
		memory.write_u32(args[0], marks.get(ref, 0))
		log.append(["getfpos", ref, marks.get(ref), 0 if marks.has(ref) else -51])
		return 0 if marks.has(ref) else (-51 & 0xffff)

	func get_eof(args: Array) -> int:      # [VAR eof, refNum]
		var ref: int = args[1]
		memory.write_u32(args[0], fork.size())
		log.append(["geteof", ref, fork.size(), 0 if marks.has(ref) else -51])
		return 0 if marks.has(ref) else (-51 & 0xffff)

	func new_ptr(args: Array, clear: bool) -> int:
		var size: int = args[0]
		var at: int = (heap_next + 3) & ~3
		heap_next = at + size
		heap.append([at, size, "NewPtrClear" if clear else "NewPtr"])
		return at                         # the heap's bytes are zero already (fresh memory)

	func image_hash(at: int, size: int) -> String:
		var ctx := HashingContext.new()
		ctx.start(HashingContext.HASH_SHA256)
		var chunk := PackedByteArray()
		chunk.resize(size)
		for k in size:
			chunk[k] = memory.read_u8(at + k)
		ctx.update(chunk)
		return ctx.finish().hex_encode()

	func snapshot(_args: Array) -> int:
		var base: int = memory.read_u32(A5 - 0x1048)
		var header := PackedByteArray()
		for k in 0x20:
			header.append(memory.read_u8(A5 - 0x1068 + k))
		var image_size: int = memory.read_u32(A5 - 0x1064) + memory.read_u32(A5 - 0x1060)
		var payload: int = memory.read_u32(A5 - 0x137a)
		var payload_size: int = 0
		for b in heap:
			if b[0] == payload:
				payload_size = b[1]
		after_load = {"base": base, "header": header.hex_encode(), "image_size": image_size,
			"image_sha256": image_hash(base, image_size), "payload_ptr": payload,
			"payload_size": payload_size, "payload_sha256": image_hash(payload, payload_size),
			"heap": heap.duplicate(true), "files": log.duplicate(true)}
		return 0

	func port():
		var p = LeyteID08.new(memory, A5)
		p.a7 = STACK
		var jt := {0x2c2: fs_open, 0x2d2: fs_read, 0x2f2: get_fpos, 0x2ea: get_eof,
			0x23a: func(a): return new_ptr(a, false), 0x242: func(a): return new_ptr(a, true),
			0x51a: snapshot}
		for slot in jt:
			p.jt_routines[slot] = jt[slot]
		for slot in [0x24a, 0x2ca, 0x2da, 0x33a, 0x22a, 0x4e2, 0x17a, 0x4da, 0x5ba, 0x562, 0x4c2,
				0x592, 0x59a, 0x5c2, 0x57a, 0x5a2, 0x58a, 0x5ca, 0x582, 0x5b2]:
			if not p.jt_routines.has(slot):
				p.jt_routines[slot] = (func(s): return func(a): calls.append(["jt", s, a]); return 1 if s == 0x562 else 0).call(slot)
		for word in [0xa9f4, 0xa997, 0xa9af, 0xa99a, 0xa998, 0xa975, 0xa986, 0xa9c8]:
			p.traps[word] = (func(w): return func(a): calls.append(["trap", w, a]); return 0).call(word)
		return p

static func _norm(v: Variant) -> Variant:
	if v is Array:
		return (v as Array).map(func(x): return _norm(x))
	if v is float:
		return int(v)
	return v

func run() -> bool:
	var path := ProjectSettings.globalize_path("res://").path_join(FORK)
	var bytes := FileAccess.get_file_as_bytes(path)
	var ok := Assertions.assert_true(bytes.size() > 0, "Leyte data fork read (%s)" % path)
	var fixture: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(FIXTURE))
	var m := Machine.new(bytes)
	var result: int = m.port().load_overlay_from_data_fork()
	ok = Assertions.assert_equal(result, 1, "loader returns 1") and ok
	ok = Assertions.assert_true(not m.after_load.is_empty(), "loader reached JT $51a") and ok
	for key in ["base", "header", "image_size", "image_sha256", "payload_ptr", "payload_size", "payload_sha256", "heap", "files"]:
		ok = Assertions.assert_equal(_norm(m.after_load.get(key)), _norm(fixture[key]), "Leyte loader %s as Leyte's own on the emulator" % key) and ok
	# An external record (r_extern, bit 4) in the first applied text relocation: the error path.
	var bad := bytes.duplicate()
	var record := 32 + 0x1f758 + 0xff4 + 8 * 24
	bad[record + 7] = bad[record + 7] | 0x10
	var e := Machine.new(bad)
	e.memory.write_u32(A5 - 0xe2c, 0xe2c)
	e.port().load_overlay_from_data_fork()
	var shown: Array = e.calls.filter(func(c): return c[0] == "jt" and c[1] == 0x4da)
	ok = Assertions.assert_equal(_norm(shown), [["jt", 0x4da, [0xe2c, 0]]], "external relocation: error alert A5 - 0xe2c") and ok
	ok = Assertions.assert_true(e.after_load.is_empty() and e.memory.read_u32(A5 - 0x1048) == 0, "external relocation: image dropped, JT $51a not reached") and ok
	return ok
