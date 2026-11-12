extends RefCounted

const Assertions := preload("res://tests/helpers/assertions.gd")
const MacMachine := preload("res://original/mac/machine/mac_machine.gd")
const GameThread := preload("res://original/mac/machine/game_thread.gd")
const ResourceManager := preload("res://original/mac/toolbox/resource_manager.gd")

const StateTrace := preload("res://tests/helpers/state_trace.gd")

const SAVES := "user://stepped_run_resources"
const FIXTURE := "res://tests/original/mac/fixtures/stepped_trace.json"
const STOP := 600
const EVERY := 30
const RETURN_KEY := [0x24, 13]

## Deterministic stepping (MacMachine.use_stepped_clock): the original program run twice from start
## on the stepped clock, with the same scripted keys (Return at the copy protection), traces the same
## state (StateTrace) every EVERY ticks, and that trace is the recorded one (FIXTURE: the Original
## edition's baseline; rewrite it with HELLCATS_RECORD_TRACE=1 after a reviewed behaviour change).
## The comparator reports the first diverging tick and field.
func run() -> bool:
	var first: Array = _trace()
	var second: Array = _trace()
	var ok := Assertions.assert_equal(first.size(), STOP / EVERY, "the run reached tick %d" % STOP)
	ok = Assertions.assert_equal(StateTrace.first_divergence(first, second), {}, "two stepped runs trace the same state") and ok
	ok = Assertions.assert_true(first.size() > 1 and first[0]["globals"] != first[-1]["globals"], "the state changed during the run") and ok
	if OS.get_environment("HELLCATS_RECORD_TRACE") == "1":
		var f := FileAccess.open(FIXTURE, FileAccess.WRITE)
		f.store_string(JSON.stringify({"stop": STOP, "every": EVERY, "trace": first}, "  ") + "\n")
		f.close()
	var recorded: Dictionary = JSON.parse_string(FileAccess.get_file_as_string(FIXTURE))
	ok = Assertions.assert_equal(StateTrace.first_divergence(recorded["trace"], first), {}, "the recorded Original trace") and ok
	return _comparator(first) and ok

## first_divergence names the first changed tick and field, and a missing entry.
func _comparator(trace: Array) -> bool:
	var changed: Array = trace.duplicate(true)
	changed[3]["rng"] = "00"
	changed[5]["globals"] = "x"
	var ok := Assertions.assert_equal(StateTrace.first_divergence(trace, changed),
		{"tick": int(trace[3]["tick"]), "field": "rng", "expected": trace[3]["rng"], "actual": "00"}, "first changed field")
	var missing := StateTrace.first_divergence(trace, trace.slice(0, 4))
	ok = Assertions.assert_equal([missing["tick"], missing["field"]], [int(trace[4]["tick"]), "tick"], "missing entry") and ok
	return ok

func _trace() -> Array:
	_clear(SAVES)
	var saved: String = ResourceManager.saved_root
	ResourceManager.saved_root = SAVES
	var machine = MacMachine.new()
	machine.toolbox.quickdraw.glyphs.prepare()
	var trace: Array = []
	machine.on_tick = func(m) -> void:
		if m.stepped_ticks % EVERY == 0:
			trace.append(StateTrace.capture(m))
	machine.use_stepped_clock([
		[120, func(m) -> void: m.toolbox.events.post_key(RETURN_KEY[0], RETURN_KEY[1], true, false, 0)],
		[122, func(m) -> void: m.toolbox.events.post_key(RETURN_KEY[0], RETURN_KEY[1], false, false, 0)]], STOP)
	var runner = GameThread.new(machine)
	runner.start()
	var waited: int = 0
	while not machine.parked and runner.is_running() and waited < 120000:
		OS.delay_msec(5)
		waited += 5
	ResourceManager.saved_root = saved
	_clear(SAVES)
	return trace

func _clear(path: String) -> void:
	var dir := DirAccess.open(path)
	if dir == null:
		return
	for sub in dir.get_directories():
		_clear(path.path_join(sub))
	for file in dir.get_files():
		dir.remove(file)
	DirAccess.remove_absolute(path)
