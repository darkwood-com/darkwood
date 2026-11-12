extends RefCounted

## The Native edition's sound driver: Hellcats 1.0.3's (ID02 0x3d50..0x4318 and the VBL handlers
## 0x39a8 / 0x3aae / 0x3bc4, SOUND_SYSTEM.md) on typed GDScript state instead of the A5 globals and
## node records in the emulated memory. Same algorithm, same results: each tick's 370 output samples
## equal the Original edition's byte for byte (tests/native/test_native_sound.gd).
##
## Still read from the Macintosh memory: the sample bytes (the game's locked 'snd ' handles) and
## SoundBase, the sound buffer the runtime's speaker plays (written as the Original handlers write it).

const C68kScript := preload("res://original/compat/c68k.gd")

const FRAME := 0x172                 ## samples a tick
const NODES := 20                    ## each channel's ring of nodes
const SILENCE := -1                  ## the silence buffer (bytes of 0x80)
const SILENCE_LENGTH := 0x172

class SoundNode:
	var pos: int = 0
	var frac: int = 0
	var sample: int = 0              ## an address in the Mac memory, or SILENCE
	var loop_start: int = 0
	var end: int = 0
	var next: int = 0                ## index of the node played after it (+0x18)
	var link: int = 0                ## index of the following node in the ring (+0x14)

class Channel:
	var nodes: Array = []            ## NODES SoundNode
	var playing: int = 0             ## index (record +0)
	var free: int = 0                ## index (record +4)

var memory
var ready := false
var channels: Array = []
var step_int: int = 1
var step_frac: int = 0
var frame_span: int = FRAME
## Which handler mixes: "unrolled" / "variant" for channel 0 alone (init's flag), "two_channel"
## while channel 1 plays.
var single_handler := "unrolled"
var handler := ""

func _init(memory_68k) -> void:
	memory = memory_68k
	for ch in 2:
		var c := Channel.new()
		for i in NODES:
			var n := SoundNode.new()
			n.link = (i + 1) % NODES
			c.nodes.append(n)
		channels.append(c)

func _byte(sample: int, offset: int) -> int:
	if sample == SILENCE:
		return 0x80
	return memory.read_u8(C68kScript.u32(sample + offset))

## alloc_sound_buffer_node: the channel's free node, filled; advance 0 makes it loop on itself.
func _alloc(channel: int, sample: int, start: int, end: int, advance: bool) -> int:
	var c: Channel = channels[channel]
	var index: int = c.free
	var n: SoundNode = c.nodes[index]
	c.free = n.link
	n.pos = C68kScript.u32(start)
	n.frac = 0
	n.sample = sample
	n.loop_start = C68kScript.u32(start)
	n.end = C68kScript.u32(end)
	n.next = n.link if advance else index
	return index

func _ok(channel: int) -> bool:
	return ready and C68kScript.s32(channel) >= 0 and C68kScript.s32(channel) < 2

func _queue(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	var first: int
	if not (C68kScript.u32(loop_b) > C68kScript.u32(loop_a)):
		first = _alloc(channel, sample, start, end, true)
		_alloc(channel, SILENCE, 0, SILENCE_LENGTH, false)
	else:
		first = _alloc(channel, sample, start, loop_a, true)
		_alloc(channel, sample, loop_a, loop_b, false)
		_alloc(channel, sample, loop_b, end, true)
	if C68kScript.u32(channel) == 1:
		handler = "two_channel"
	return first

## init_sound_system's driver part: nodes reset, rate 1.0, silence playing on both channels.
func init(flag: int) -> void:
	for c in channels:
		for i in NODES:
			c.nodes[i].link = (i + 1) % NODES
		c.playing = 0
		c.free = 0
	step_int = 1
	step_frac = 0
	frame_span = FRAME
	_alloc(0, SILENCE, 0, SILENCE_LENGTH, false)
	_alloc(1, SILENCE, 0, SILENCE_LENGTH, false)
	single_handler = "variant" if C68kScript.u32(flag) != 0 else "unrolled"
	handler = single_handler

func play(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	if not _ok(channel):
		return 0
	channels[channel].playing = _queue(channel, sample, start, end, loop_a, loop_b)
	return 1

func enqueue(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	if not _ok(channel):
		return 0
	_queue(channel, sample, start, end, loop_a, loop_b)
	return 1

func set_rate(rate: int) -> void:
	if not ready:
		return
	var r: int = C68kScript.u32(rate)
	r = clampi(r, 0x8000, 0x30000)
	frame_span = C68kScript.u32(r * FRAME) >> 16
	step_frac = C68kScript.lsl32(r, 16)
	step_int = r >> 16

func release(channel: int) -> void:
	if not _ok(channel):
		return
	var c: Channel = channels[channel]
	var silence: int = _alloc(channel, SILENCE, 0, SILENCE_LENGTH, false)
	c.free = c.nodes[silence].link
	var i: int = c.playing
	while i != silence:
		c.nodes[i].next = c.nodes[i].link
		i = c.nodes[i].link

func silence(channel: int) -> void:
	if not _ok(channel):
		return
	channels[channel].playing = _alloc(channel, SILENCE, 0, SILENCE_LENGTH, false)

func is_playing(channel: int) -> int:
	if not _ok(channel):
		return 0
	var c: Channel = channels[channel]
	return 1 if c.nodes[c.playing].sample != SILENCE else 0

## One tick (a VBL handler): the 370 samples, in the order the handlers write them to the buffer.
func mix() -> PackedByteArray:
	var out := PackedByteArray()
	out.resize(FRAME)
	var c0: Channel = channels[0]
	var v: SoundNode = c0.nodes[c0.playing]
	var vi: int = c0.playing
	var pos: int = v.pos
	var frac: int = v.frac
	var fast: bool = C68kScript.s32(v.end - pos) > C68kScript.s32(frame_span)
	if handler == "variant" and fast:
		for k in FRAME:
			out[k] = _byte(v.sample, pos + k)
		var src: int = C68kScript.u32(v.sample + pos) if v.sample != SILENCE else 0
		frac = _variant_fraction(v.sample, pos, src)
		pos = C68kScript.u32(pos + FRAME)
	else:
		var other: SoundNode = null
		var src_sample: int = 0
		var src_pos: int = 0
		if handler == "two_channel":
			other = channels[1].nodes[channels[1].playing]
			src_sample = other.sample
			src_pos = other.pos
		var check: bool = handler == "variant" or not fast
		for k in FRAME:
			if check and not (C68kScript.s32(v.end) > C68kScript.s32(pos)):
				vi = v.next
				v = c0.nodes[vi]
				pos = v.loop_start
				frac = 0
				c0.playing = vi
			var b: int = _byte(v.sample, pos)
			var sum: int = frac + step_frac
			frac = C68kScript.u32(sum)
			pos = C68kScript.u32(pos + step_int + (1 if sum > 0xffffffff else 0))
			if other != null:
				b = (b + _byte(src_sample, src_pos + k) - 0x80) & 0xff
			out[k] = b
		if other != null:
			_advance_channel_1(other)
	v.pos = pos
	v.frac = frac
	return out

## The variant handler's fraction after a straight copy: the last long its MOVEP loop read.
func _variant_fraction(sample: int, pos: int, src: int) -> int:
	var at: int = 0x169 if (src & 1) != 0 else 0x16c
	var value: int = 0
	for k in 4:
		value = (value << 8) | _byte(sample, pos + at + k)
	return value

## Channel 1 after a two-channel tick: 370 on; at or past its end its next node from that node's
## loop start; reaching silence hands the buffer back to the single-channel handler.
func _advance_channel_1(other: SoundNode) -> void:
	var c1: Channel = channels[1]
	var pos: int = C68kScript.u32(other.pos + FRAME)
	if C68kScript.s32(pos) >= C68kScript.s32(other.end):
		c1.playing = other.next
		other = c1.nodes[c1.playing]
		pos = other.loop_start
		if other.sample == SILENCE:
			handler = single_handler
	other.pos = pos
