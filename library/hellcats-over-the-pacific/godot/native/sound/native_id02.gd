extends "res://original/port/ID02.gd"

## CODE 2 for the Native edition: the Original port (ID02.gd) with its eight sound-driver entries
## on the native driver (sound_driver.gd, typed state). The driver's tick is installed as the sound
## hook (a code address of this segment's routine table), so the runtime's Sound Manager runs it
## every tick as it runs the original VBL handlers. Its 370 samples go straight to the Native speaker
## (take()), not through SoundBase, in the order the Original speaker reads them from that buffer:
## the handlers put sample k at buffer slot k + 2, the last two at slots 0 and 1.

const Driver := preload("res://native/sound/sound_driver.gd")
## Where the native tick is registered (beside the runtime's empty VBL task at 0x6f8000).
const NATIVE_HOOK := 0x6f8100

var driver
var _saved_hook: int = 0
var _out_lock := Mutex.new()
var _out := PackedFloat32Array()
const OUT_LIMIT := 4 * 370 * 6

func _init(memory_68k = null, a5_value: int = 0) -> void:
	super(memory_68k, a5_value)
	driver = Driver.new(memory_68k)

func _tick(_args: Array) -> int:
	var frame: PackedByteArray = driver.mix()
	var n: int = frame.size()
	var samples := PackedFloat32Array()
	samples.resize(n)
	for j in n:
		samples[j] = (frame[(j - 2 + n) % n] - 0x80) / 128.0
	_out_lock.lock()
	_out.append_array(samples)
	if _out.size() > OUT_LIMIT:
		_out = _out.slice(_out.size() - OUT_LIMIT)
	_out_lock.unlock()
	return 0

## The samples mixed since the last call (the Native speaker plays them).
func take() -> PackedFloat32Array:
	_out_lock.lock()
	var out := _out
	_out = PackedFloat32Array()
	_out_lock.unlock()
	return out

func init_sound_system(flag: int) -> int:
	if driver.ready:
		shutdown_sound()
	driver.init(flag)
	clear_sound_buffer_to_silence()
	code_routines[NATIVE_HOOK] = _tick
	_saved_hook = memory.read_u32(LM_SOUND_HOOK)
	memory.write_u32(LM_SOUND_HOOK, NATIVE_HOOK)
	memory.write_u32(C68kScript.u32(a5 - 0x356), 0)
	if (_trap(TRAP_SND_NEW_CHANNEL, [0, 0, 5, C68kScript.u32(a5 - 0x356)]) & 0xffff) != 0:
		return 0
	driver.ready = true
	return 1

func shutdown_sound() -> void:
	if not driver.ready:
		return
	driver.silence(0)
	driver.silence(1)
	memory.write_u32(LM_SOUND_HOOK, _saved_hook)
	clear_sound_buffer_to_silence()
	_trap(TRAP_SND_DISPOSE_CHANNEL, [1, memory.read_u32(C68kScript.u32(a5 - 0x356))])
	driver.ready = false

func play_sample_on_channel(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	return driver.play(channel, sample, start, end, loop_a, loop_b)

func enqueue_sample_on_channel(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	return driver.enqueue(channel, sample, start, end, loop_a, loop_b)

func set_sound_playback_rate(rate: int) -> void:
	driver.set_rate(rate)

func release_channel_loop(channel: int) -> void:
	driver.release(channel)

func silence_sound_channel(channel: int) -> void:
	driver.silence(channel)

func is_channel_playing(channel: int) -> int:
	return driver.is_playing(channel)
