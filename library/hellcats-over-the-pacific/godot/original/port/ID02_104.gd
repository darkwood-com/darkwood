class_name StrictID02v104
extends RefCounted
## Hellcats 1.0.4's sound driver: the code its updater appends to CODE 2 (0x4e1a..0x6100), translated
## from sources/hellcats-1-0-4/CODE/ID02.delta.decomp.c (each function checked on the 68020
## interpreter against the 1.0.4 bytes by tools/original/check_1_0_4_sound.py). In 1.0.4 the eight
## 1.0.3 entries init_sound_system .. shutdown_sound jump here; every other ID02 function is 1.0.3's
## (ID02.gd); ID02_v1_0_4.gd routes them here when the runtime runs 1.0.4.
##
## The driver's variables live in CODE 2 itself, from `code_base` + 0x4e1a (the segment loaded as in
## ID02.gd); traps as ID02.gd's, the stack order lowest address first. Register traps take [D0] or
## [A0]; _SoundDispatch takes its stack arguments then its selector: [header, channel, 0x00200008].

const C68kScript := preload("res://original/compat/c68k.gd")

const DRIVER: int = 0x4e1a
const ACTIVE: int = 0x81c
const STEP_INT: int = 0x81e
const STEP_FRAC: int = 0x822
const SILENCE: int = 0x826
const RECORDS: int = 0x82a
const RECORD_SIZE: int = 0x2a8
const CHANNEL: int = 0x12ca
const HEADER: int = 0x12ce
const BUFFER_0: int = 0x12da
const BUFFER_1: int = 0x12de
const SILENCE_LENGTH: int = 0xe74
const FRAME: int = 0x73a
const TRAP_NEW_PTR_CLEAR: int = 0xa31e
const TRAP_DISPOSE_PTR: int = 0xa01f
const TRAP_SND_NEW_CHANNEL: int = 0xa807
const TRAP_SND_DISPOSE_CHANNEL: int = 0xa801
const TRAP_SOUND_DISPATCH: int = 0xa800
const SND_PLAY_DOUBLE_BUFFER: int = 0x00200008

var memory
var code_base: int = 0
var traps: Dictionary = {}

func _init(memory_68k = null, code_base_value: int = 0) -> void:
	memory = memory_68k
	code_base = code_base_value

func _trap(word: int, args: Array) -> int:
	assert(traps.has(word), "A-line trap %04x has no routine" % word)
	return traps[word].call(args)

func _var(offset: int) -> int:
	return code_base + DRIVER + offset

func _record(channel: int) -> int:
	return _var(RECORDS) + C68kScript.u32(channel) * RECORD_SIZE

func _guard(channel: int) -> bool:
	return memory.read_u16(_var(ACTIVE)) != 0 and C68kScript.s32(channel) >= 0 and C68kScript.s32(channel) < 4

## 0x4fa6: pops the head of the channel's free list (record + 4) and fills it; advance 0 loops it.
func alloc_sound_buffer_node_104(channel: int, buffer: int, start: int, end: int, advance: int) -> int:
	var rec: int = _record(channel)
	var node: int = memory.read_u32(rec + 4)
	memory.write_u32(rec + 4, memory.read_u32(node + 0x14))
	memory.write_u32(node, C68kScript.u32(start))
	memory.write_u32(node + 4, 0)
	memory.write_u32(node + 8, C68kScript.u32(buffer))
	memory.write_u32(node + 0xc, C68kScript.u32(start))
	memory.write_u32(node + 0x10, C68kScript.u32(end))
	memory.write_u32(node + 0x18, memory.read_u32(node + 0x14) if (advance & 0xffff) != 0 else node)
	return node

## The chain play / enqueue queue: without a loop of 370 bytes or more (unsigned), the sample then
## looping silence; otherwise attack, looping sustain, release. Returns the first node.
func _queue_sample(channel: int, buffer: int, start: int, end: int, loop_start: int, loop_end: int) -> int:
	var first: int
	if C68kScript.u32(loop_end - loop_start) < 0x172:
		first = alloc_sound_buffer_node_104(channel, buffer, start, end, 1)
		alloc_sound_buffer_node_104(channel, memory.read_u32(_var(SILENCE)), 0, SILENCE_LENGTH, 0)
	else:
		first = alloc_sound_buffer_node_104(channel, buffer, start, loop_start, 1)
		alloc_sound_buffer_node_104(channel, buffer, loop_start, loop_end, 0)
		alloc_sound_buffer_node_104(channel, buffer, loop_end, end, 1)
	return first

## 0x51fe: channels but 0 drop the last FRAME bytes; the chain becomes the head. 1, or 0 refused.
func play_sample_on_channel_104(channel: int, buffer: int, start: int, end: int, loop_start: int, loop_end: int) -> int:
	if not _guard(channel):
		return 0
	if C68kScript.u32(channel) != 0:
		end = C68kScript.u32(end - FRAME)
	memory.write_u32(_record(channel), _queue_sample(channel, buffer, start, end, loop_start, loop_end))
	return 1

## 0x52f4: the chain queued behind what plays: the nodes up to the one before the free head chain on.
func enqueue_sample_on_channel_104(channel: int, buffer: int, start: int, end: int, loop_start: int, loop_end: int) -> int:
	if not _guard(channel):
		return 0
	if C68kScript.u32(channel) != 0:
		end = C68kScript.u32(end - FRAME)
	_queue_sample(channel, buffer, start, end, loop_start, loop_end)
	var free_head: int = memory.read_u32(_record(channel) + 4)
	var node: int = memory.read_u32(_record(channel))
	while memory.read_u32(node + 0x14) != free_head:
		memory.write_u32(node + 0x18, memory.read_u32(node + 0x14))
		node = memory.read_u32(node + 0x14)
	return 1

## 0x541e: the rate clamped (unsigned) to 0.5 .. 3.0, stored as the integer and fraction step.
func set_sound_playback_rate_104(rate: int) -> int:
	if memory.read_u16(_var(ACTIVE)) == 0:
		return 0
	var r: int = C68kScript.u32(rate)
	if r < 0x8000:
		r = 0x8000
	elif r > 0x30000:
		r = 0x30000
	memory.write_u32(_var(STEP_FRAC), C68kScript.lsl32(r, 16))
	memory.write_u32(_var(STEP_INT), r >> 16)
	return 0

## 0x547c: queue silence, then every node from the head to it chains on (+0x18 = +0x14).
func release_channel_loop_104(channel: int) -> int:
	if not _guard(channel):
		return 0
	var silence: int = alloc_sound_buffer_node_104(channel, memory.read_u32(_var(SILENCE)), 0, SILENCE_LENGTH, 0)
	memory.write_u32(_record(channel) + 4, memory.read_u32(silence + 0x14))
	var node: int = memory.read_u32(_record(channel))
	while node != silence:
		memory.write_u32(node + 0x18, memory.read_u32(node + 0x14))
		node = memory.read_u32(node + 0x14)
	return 0

## 0x5506: the head becomes a new looping silence node.
func silence_sound_channel_104(channel: int) -> int:
	if not _guard(channel):
		return 0
	memory.write_u32(_record(channel), alloc_sound_buffer_node_104(channel, memory.read_u32(_var(SILENCE)), 0, SILENCE_LENGTH, 0))
	return 0

## 0x5568: 1 when the head node plays something other than silence.
func is_channel_playing_104(channel: int) -> int:
	if not _guard(channel):
		return 0
	var node: int = memory.read_u32(_record(channel))
	return 1 if memory.read_u32(node + 8) != memory.read_u32(_var(SILENCE)) else 0

## 0x5006: records (24 nodes in a ring each), silence, header, channel, both buffers primed, then
## SndPlayDoubleBuffer. 0 at the first failure, 1 once active.
func init_sound_system_104() -> int:
	if memory.read_u16(_var(ACTIVE)) != 0:
		shutdown_sound_104()
	for channel in 4:
		var nodes: int = _record(channel) + 8
		memory.write_u32(_record(channel) + 0x2a0, nodes)
		memory.write_u32(_record(channel) + 4, nodes)
		memory.write_u32(_record(channel), nodes)
		for i in 0x17:
			memory.write_u32(nodes + i * 0x1c + 0x14, nodes + (i + 1) * 0x1c)
	memory.write_u32(_var(STEP_INT), 1)
	memory.write_u32(_var(STEP_FRAC), 0)
	var silence: int = C68kScript.u32(_trap(TRAP_NEW_PTR_CLEAR, [SILENCE_LENGTH]))
	memory.write_u32(_var(SILENCE), silence)
	if silence == 0:
		return 0
	for k in SILENCE_LENGTH:
		memory.write_u8(silence + k, 0x80)
	for channel in 4:
		alloc_sound_buffer_node_104(channel, silence, 0, SILENCE_LENGTH, 0)
	memory.write_u16(_var(HEADER), 1)
	memory.write_u16(_var(HEADER) + 2, 8)
	memory.write_u16(_var(HEADER) + 4, 0)
	memory.write_u16(_var(HEADER) + 6, 0)
	memory.write_u32(_var(HEADER) + 8, 0x56ee8ba3)
	memory.write_u32(_var(HEADER) + 0x14, code_base + DRIVER)
	if (_trap(TRAP_SND_NEW_CHANNEL, [0, 0x8c, 5, _var(CHANNEL)]) & 0xffff) != 0:
		return 0
	for slot in [BUFFER_0, BUFFER_1]:
		var buffer: int = C68kScript.u32(_trap(TRAP_NEW_PTR_CLEAR, [0x74c]))
		memory.write_u32(_var(slot), buffer)
		if buffer == 0:
			return 0
		sound_double_back_104(memory.read_u32(_var(CHANNEL)), buffer)
	if (_trap(TRAP_SOUND_DISPATCH, [_var(HEADER), memory.read_u32(_var(CHANNEL)), SND_PLAY_DOUBLE_BUFFER]) & 0xffff) != 0:
		return 0
	memory.write_u16(_var(ACTIVE), 1)
	return 1

## 0x55ba: channels 0..2 silenced, the channel disposed (quietNow), the buffers then the silence freed.
func shutdown_sound_104() -> int:
	if memory.read_u16(_var(ACTIVE)) == 0:
		return 0
	for channel in 3:
		silence_sound_channel_104(channel)
	_trap(TRAP_SND_DISPOSE_CHANNEL, [1, memory.read_u32(_var(CHANNEL))])
	memory.write_u32(_var(CHANNEL), 0)
	for slot in [BUFFER_0, BUFFER_1]:
		if memory.read_u32(_var(slot)) != 0:
			_trap(TRAP_DISPOSE_PTR, [memory.read_u32(_var(slot))])
			memory.write_u32(_var(slot), 0)
	_trap(TRAP_DISPOSE_PTR, [memory.read_u32(_var(SILENCE))])
	memory.write_u32(_var(SILENCE), 0)
	memory.write_u16(_var(ACTIVE), 0)
	return 0

## Channels 1..3: where to read FRAME bytes from; the cursor moves on, to the continuation's loop
## start once at or past the node's end.
func _advance_fixed_channel(channel: int) -> int:
	var node: int = memory.read_u32(_record(channel))
	var cursor: int = memory.read_u32(node)
	var from: int = C68kScript.u32(memory.read_u32(node + 8) + cursor)
	cursor = C68kScript.u32(cursor + FRAME)
	if C68kScript.s32(cursor) >= C68kScript.s32(memory.read_u32(node + 0x10)):
		node = memory.read_u32(node + 0x18)
		cursor = memory.read_u32(node + 0xc)
		memory.write_u32(_record(channel), node)
	memory.write_u32(node, cursor)
	return from

## 0x4e1a (Pascal: channel, buffer): FRAME samples into buffer + 0x10, each the four channels' bytes
## summed minus 0x180 (low byte); channel 0 resampled at the 32.32 step, its end checked per sample
## only when 0xe74 bytes or fewer were left at the start; then dbBufferReady and dbNumFrames = FRAME.
func sound_double_back_104(_channel: int, buffer: int) -> void:
	var out: int = buffer + 0x10
	var reads: Array = [_advance_fixed_channel(1), _advance_fixed_channel(2), _advance_fixed_channel(3)]
	var node: int = memory.read_u32(_record(0))
	var position: int = memory.read_u32(node)
	var fraction: int = memory.read_u32(node + 4)
	var end: int = memory.read_u32(node + 0x10)
	var samples: int = memory.read_u32(node + 8)
	var step_int: int = memory.read_u32(_var(STEP_INT))
	var step_frac: int = memory.read_u32(_var(STEP_FRAC))
	var checked: bool = C68kScript.s32(end - position) <= 0xe74
	for n in FRAME:
		if checked and C68kScript.s32(end) <= C68kScript.s32(position):
			node = memory.read_u32(node + 0x18)
			samples = memory.read_u32(node + 8)
			position = memory.read_u32(node + 0xc)
			fraction = 0
			end = memory.read_u32(node + 0x10)
			memory.write_u32(_record(0), node)
		var v: int = memory.read_u8(C68kScript.u32(samples + position))
		for k in 3:
			v += memory.read_u8(reads[k] + n)
		memory.write_u8(out + n, (v - 0x180) & 0xff)
		var total: int = fraction + step_frac
		position = C68kScript.u32(position + step_int + (total >> 32))
		fraction = C68kScript.u32(total)
	memory.write_u32(node, position)
	memory.write_u32(node + 4, fraction)
	memory.write_u16(buffer + 6, memory.read_u16(buffer + 6) | 1)
	memory.write_u32(buffer, FRAME)
