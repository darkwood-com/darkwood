extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const SegmentLoader := preload("res://original/mac/machine/segment_loader.gd")
const NativeID02 := preload("res://native/sound/native_id02.gd")

const SAMPLE_A := 0x200000
const SAMPLE_B := 0x210000
const TICKS := 240

## The Native sound driver against the Original one: the same calls, made at the same ticks, on two
## machines; every tick's output samples (Original: the Sound Manager's reading of SoundBase; Native:
## the driver's own queue, which the Native speaker plays) and every is_channel_playing
## answer must be equal. The script covers a looping sample at several rates, a one-shot, channel 1
## mixed in and running out, an enqueued sample, a released loop and silencing.
func run() -> bool:
	var original: Array = _run(false)
	var native: Array = _run(true)
	var ok := Assertions.assert_equal(native[1], original[1], "is_channel_playing answers")
	var first := -1
	for i in mini(native[0].size(), original[0].size()):
		if native[0][i] != original[0][i]:
			first = i
			break
	ok = Assertions.assert_equal([native[0].size(), first], [original[0].size(), -1], "output samples (count, first difference)") and ok
	ok = Assertions.assert_true(original[0].size() >= (TICKS - 10) * 370 and _varied(original[0]), "the script makes sound") and ok
	# Native kept its state out of the A5 world (ready word A5 - 0xc38) and mixed through its own hook.
	ok = Assertions.assert_equal([native[2], original[2] != NativeID02.NATIVE_HOOK], [[0, NativeID02.NATIVE_HOOK], true], "native driver in use") and ok
	return ok

func _varied(samples: PackedFloat32Array) -> bool:
	for s in samples:
		if absf(s) > 0.1:
			return true
	return false

func _run(native: bool) -> Array:
	SegmentLoader.overrides = {2: NativeID02} if native else {}
	var m = MacMachine.new()
	SegmentLoader.overrides = {}
	for k in 0x8000:
		m.memory.write_u8(SAMPLE_A + k, (k * 7 + (k >> 6)) & 0xff)
		m.memory.write_u8(SAMPLE_B + k, (0x80 + ((k * 13) & 0x3f) - 0x20) & 0xff)
	var p = m.segments.instances[2]
	m.stepped_ticks = 0
	var sounds = m.toolbox.sounds
	var playing: Array = []
	var out := PackedFloat32Array()
	p.init_sound_system(0)
	for t in TICKS:
		match t:
			2: p.play_sample_on_channel(0, SAMPLE_A, 0, 0x2000, 0x400, 0x1400)
			20: p.set_sound_playback_rate(0x18000)
			30: p.play_sample_on_channel(1, SAMPLE_B, 0, 0xa00, 0, 0)
			50: p.set_sound_playback_rate(0x30000)
			60: p.release_channel_loop(0)
			80: p.set_sound_playback_rate(0x9000)
			90: p.play_sample_on_channel(0, SAMPLE_A, 0x100, 0x900, 0, 0)
			95: p.enqueue_sample_on_channel(0, SAMPLE_B, 0, 0x1800, 0x200, 0x800)
			110: p.play_sample_on_channel(1, SAMPLE_A, 0x40, 0x3000, 0x500, 0x2000)
			150: p.release_channel_loop(1)
			170: p.silence_sound_channel(0)
			180: p.play_sample_on_channel(0, SAMPLE_B, 0, 0x7000, 0x1000, 0x6000)
			200: p.set_sound_playback_rate(0x4000)
			220: p.shutdown_sound()
			225: p.init_sound_system(0)
			226: p.play_sample_on_channel(0, SAMPLE_A, 0, 0x500, 0, 0)
		m.stepped_ticks += 1
		sounds.service()
		var heard: PackedFloat32Array = sounds.take()
		out.append_array(p.take() if native else heard)
		playing.append([p.is_channel_playing(0), p.is_channel_playing(1)])
	return [out, playing, [m.memory.read_u16(m.A5 - 0xc38), m.memory.read_u32(0x196)] if native else m.memory.read_u32(0x196)]
