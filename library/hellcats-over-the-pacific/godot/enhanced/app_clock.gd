extends RefCounted

## The Enhanced edition's clock across the application's background (a phone's app switch, a call):
## while the application is paused the Mac sleeps, as a Macintosh put to sleep does. TickCount (the
## program's clock: ID02 paces its flight frames on it) does not advance, and the program's thread,
## at its next TickCount, waits until the application resumes, so a flight neither goes on unseen
## nor jumps by the time away. Only TickCount is shifted: event timestamps and the Sound Manager's
## durations keep the host clock (the touch controller releases its keys when the application
## pauses). No gameplay change: the program sees an uninterrupted run.

const TRAP_TICK_COUNT := 0xa975

var machine
var _lock := Mutex.new()
var _paused := false
var _paused_at := 0       ## the host tick count when the sleep began
var _offset := 0          ## host ticks slept so far

func _init(machine_) -> void:
	machine = machine_

## Installs TickCount (after the Event Manager's, before the program's thread starts).
func install(toolbox) -> void:
	toolbox.install(TRAP_TICK_COUNT, func(_a: Array) -> int: return tick_count())

## TickCount as the program reads it: the host's (stepped or not) less the time slept; waits while
## the application is paused (or until the machine halts).
func tick_count() -> int:
	while is_paused() and not machine.halting:
		OS.delay_msec(20)
	var ticks: int = machine.read_ticks()
	_lock.lock()
	var offset: int = _offset
	_lock.unlock()
	return (ticks - offset) & 0xffffffff

func is_paused() -> bool:
	_lock.lock()
	var p := _paused
	_lock.unlock()
	return p

## The application went to the background (main thread).
func pause() -> void:
	_lock.lock()
	if not _paused:
		_paused = true
		_paused_at = machine.ticks()
	_lock.unlock()

## The application came back (main thread): the time away is not the program's.
func resume() -> void:
	_lock.lock()
	if _paused:
		_paused = false
		_offset += machine.ticks() - _paused_at
	_lock.unlock()

func slept_ticks() -> int:
	_lock.lock()
	var o := _offset
	_lock.unlock()
	return o
