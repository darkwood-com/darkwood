class_name StrictID02At104
extends "res://original/port/ID02.gd"
## CODE 2 as Hellcats 1.0.4 has it: 1.0.3's (ID02.gd) with the eight sound-driver entries jumping into
## the code the updater appends (sources/hellcats-1-0-4; RESOURCE_ANALYSIS.md "Hellcats 1.0.4"), so
## every caller, through the jump table or directly, reaches the 1.0.4 driver (ID02_104.gd). The
## runtime uses this segment when the selected version is 1.0.4 (resource_files.gd `version`).

const Driver104 := preload("res://original/port/ID02_104.gd")

var driver

func _init(memory_68k = null, a5_value: int = 0) -> void:
	super(memory_68k, a5_value)
	driver = Driver104.new(memory_68k, 0)

## The driver shares the segment's traps and its code base (its variables live in CODE 2).
func _driver():
	driver.traps = traps
	driver.code_base = code_base
	return driver

func init_sound_system(_flag: int) -> int:
	return _driver().init_sound_system_104()

func play_sample_on_channel(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	return _driver().play_sample_on_channel_104(channel, sample, start, end, loop_a, loop_b)

func enqueue_sample_on_channel(channel: int, sample: int, start: int, end: int, loop_a: int, loop_b: int) -> int:
	return _driver().enqueue_sample_on_channel_104(channel, sample, start, end, loop_a, loop_b)

func set_sound_playback_rate(rate: int) -> void:
	_driver().set_sound_playback_rate_104(rate)

func release_channel_loop(channel: int) -> void:
	_driver().release_channel_loop_104(channel)

func silence_sound_channel(channel: int) -> void:
	_driver().silence_sound_channel_104(channel)

func is_channel_playing(channel: int) -> int:
	return _driver().is_channel_playing_104(channel)

func shutdown_sound() -> void:
	_driver().shutdown_sound_104()
