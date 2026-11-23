extends SceneTree
## Leyte Gulf's first start on the Original runtime (original/leyte/machine/leyte_profile.gd): its main
## (CODE 2 0x37d2) run for one event-loop pass, on a temporary save root and an empty synthetic
## Hellcats save (never the player's user://leyte-gulf or user://resources). Prints what the runtime
## noted missing, in order, and the log. A refused port function stops the run with its name.
##
##   Godot --headless --path godot --script res://tests/leyte/leyte_start_probe.gd

const LeyteProfile := preload("res://original/leyte/machine/leyte_profile.gd")
const LeyteVolume := preload("res://original/leyte/machine/leyte_volume.gd")
const LeyteResources := preload("res://original/leyte/machine/leyte_resources.gd")
const ROOT := "user://leyte-start-probe"

static func _s14(v: int) -> int:
	return ((v + 0x2000) & 0x3fff) - 0x2000

static func _wipe(path: String) -> void:
	var d := DirAccess.open(path)
	if d == null:
		return
	for f in d.get_files():
		d.remove(f)
	for sub in d.get_directories():
		_wipe("%s/%s" % [path, sub])
		d.remove(sub)

func _init() -> void:
	_wipe(ROOT)                                        # each start a first start, on the probe's own root
	LeyteVolume.save_root = ROOT + "/leyte-gulf"
	LeyteResources.hellcats_saved_root = ROOT + "/hellcats-saved"
	LeyteProfile.apply(true)
	var MacMachine = load("res://original/mac/machine/mac_machine.gd")
	var machine = MacMachine.new()
	# Trace the copy protection: its dialog traps and CODE 5 slots, as the program reaches them.
	for word in [0xa97c, 0xa991, 0xa982, 0xa98b, 0xa983]:
		var inner: Callable = machine.toolbox.traps[word]
		machine.toolbox.traps[word] = func(a: Array) -> int:
			var r: int = int(inner.call(a))
			print("[probe] trap %04x %s -> %d" % [word, str(a), r])
			# The sound error dialog (DLOG 139) waits for a click (Button down, then up after DisposDialog):
			# the mouse is pressed when it opens and let go when it is disposed.
			if word == 0xa97c and int(a[a.size() - 1]) == 139:
				machine.toolbox.events.post_mouse(Vector2i(100, 100), true)
			if word == 0xa983:
				machine.toolbox.events.post_mouse(Vector2i(100, 100), false)
			return r
	for slot in [0x592, 0x59a, 0x5c2, 0x57a, 0x5a2, 0x58a, 0x5ca, 0x582, 0x5b2]:
		var routine: Callable = machine.segments.instances[2].jt_routines[slot]
		var traced := func(a: Array) -> int:
			var r: int = int(routine.call(a))
			print("[probe] jt $%x %s -> %d" % [slot, str(a), r])
			return r
		for seg in machine.segments.instances:
			machine.segments.instances[seg].jt_routines[slot] = traced
	# Synthetic input only: each ModalDialog (the Duty Roster, then whatever follows) is answered with
	# Return (its default item), at most five times; the item each call ends with is printed.
	# The mission summary (DLOG 133) is read to its end first, as a player would: its text advances on the
	# filter's idle passes (TickCount), and Return is pressed once the text's stage (A5 - 0x22a2) reaches 100.
	# Every line the text receives (TEInsert) is printed.
	var summary := [false, false]                     # open, waiting for the text's end
	var new_dialog: Callable = machine.toolbox.traps[0xa97c]
	# LEYTE_PROBE_SCENARIO = n: in the scenario dialog (DLOG 129) the scenario's radio button (item n + 7; DITL 129's
	# boxes) is clicked at its centre, then OK is pressed with Return on the next pass. A player's choice, by a click.
	var scenario_wanted: int = int(OS.get_environment("LEYTE_PROBE_SCENARIO")) if OS.has_environment("LEYTE_PROBE_SCENARIO") else 0
	var scenario_boxes := {9: [73, 58, 97, 184], 10: [97, 58, 121, 242], 11: [121, 58, 145, 242], 12: [145, 58, 169, 242],
			13: [73, 257, 97, 444], 14: [97, 257, 121, 423], 15: [121, 257, 145, 441], 16: [143, 257, 167, 441]}
	var last_dialog := [0, 0, false]                  # id, dialog, clicked
	machine.toolbox.traps[0xa97c] = func(a: Array) -> int:
		if int(a[a.size() - 1]) == 133:
			summary[0] = true
		var d: int = int(new_dialog.call(a))
		last_dialog[0] = int(a[a.size() - 1])
		last_dialog[1] = d
		return d
	var ticks: Callable = machine.toolbox.traps[0xa975]
	machine.toolbox.traps[0xa975] = func(a: Array) -> int:
		if summary[1] and machine.memory.read_s16((machine.A5 - 0x22a2) & 0xffffffff) == 100:
			summary[1] = false
			summary[0] = false
			print("[probe] summary text complete: Return")
			machine.toolbox.events.post_key(0x24, 13, true, false, 0)
			machine.toolbox.events.post_key(0x24, 13, false, false, 0)
		return int(ticks.call(a))
	var te_insert: Callable = machine.toolbox.traps[0xa9de]
	machine.toolbox.traps[0xa9de] = func(a: Array) -> int:
		var line := PackedByteArray()
		for k in int(a[1]):
			line.append(machine.memory.read_u8((int(a[2]) + k) & 0xffffffff))
		print("[probe] text: %s" % line.get_string_from_ascii().strip_edges())
		return int(te_insert.call(a))
	var modal: Callable = machine.toolbox.traps[0xa991]
	var answered := [0]
	machine.toolbox.traps[0xa991] = func(a: Array) -> int:
		if scenario_wanted > 0 and last_dialog[0] == 129 and not last_dialog[2]:
			last_dialog[2] = true
			var box: Array = scenario_boxes[scenario_wanted + 7]
			var origin: Vector2i = machine.toolbox.quickdraw.port_bits(last_dialog[1])[2].position
			var where := Vector2i((box[1] + box[3]) / 2, (box[0] + box[2]) / 2) - origin
			print("[probe] scenario %d: item %d clicked at %s" % [scenario_wanted, scenario_wanted + 7, str(where)])
			machine.toolbox.events.post_mouse(where, true)
			machine.toolbox.events.post_mouse(where, false)
		elif summary[0] and not summary[1]:
			summary[1] = true
			print("[probe] mission summary up: reading it")
		elif summary[1]:
			pass
		elif answered[0] < 5:
			answered[0] += 1
			machine.toolbox.events.post_key(0x24, 13, true, false, 0)
			machine.toolbox.events.post_key(0x24, 13, false, false, 0)
		var r: int = int(modal.call(a))
		print("[probe] ModalDialog -> item %d" % machine.memory.read_u16(int(a[0])))
		return r
	# Then, once both dialogs are answered, Command-Q (down and up) at the flight loop's first
	# HiliteMenu(0): the loop's own quit key, so a session with the overlay's hooks missing still ends.
	# LEYTE_PROBE_FRAMES = n lets the flight loop run n frames first (counted at its per-frame GetMouse), printing the
	# player aircraft (controller 0x20b28: object position, speed +0x62a, attitude, damage +0x698, fuel) every 10
	# frames: observation only. Without it Command-Q is posted at the loop's first HiliteMenu(0), as before.
	var hilite: Callable = machine.toolbox.traps[0xa938]
	var quit_posted := [false]
	var frames_wanted: int = int(OS.get_environment("LEYTE_PROBE_FRAMES")) if OS.has_environment("LEYTE_PROBE_FRAMES") else 0
	var frames := [0]
	var throttle_test: bool = OS.has_environment("LEYTE_PROBE_THROTTLE")
	var centre_stick: bool = OS.has_environment("LEYTE_PROBE_CENTRE")
	var survey: bool = OS.has_environment("LEYTE_PROBE_SURVEY")
	var pull: Array = Array(OS.get_environment("LEYTE_PROBE_PULL").split(",", false)).map(func(v): return int(v))
	var aileron: Array = Array(OS.get_environment("LEYTE_PROBE_AILERON").split(",", false)).map(func(v): return int(v))
	var autopilot: bool = OS.has_environment("LEYTE_PROBE_AUTOPILOT")
	# LEYTE_PROBE_AUTOPILOT = bank,climb,straight (optional): the hand's bank and climb-pitch limits (14-bit angles,
	# default 0x300 / 0x300) and the frame until which it climbs straight ahead before turning (default 240)
	var ap: Array = Array(OS.get_environment("LEYTE_PROBE_AUTOPILOT").split(",", false)).map(func(v): return int(v))
	var ap_bank: int = ap[0] if ap.size() > 0 else 0x300
	var ap_climb: int = ap[1] if ap.size() > 1 else 0x300
	var ap_straight: int = ap[2] if ap.size() > 2 else 240
	var ap_flee: int = ap[3] if ap.size() > 3 else 0       # until this frame: along the target's course, climbing
	var firing := [false]
	var locked := [0]
	var bearing_err := [0x2000]
	var last_seen := [0, 0, 0, 0]                      # the target's position last frame (lead pursuit)
	var stick_char: int = OS.get_environment("LEYTE_PROBE_STICK").unicode_at(0) if OS.get_environment("LEYTE_PROBE_STICK") != "" else 0
	var get_mouse: Callable = machine.toolbox.traps[0xa972]
	machine.toolbox.traps[0xa972] = func(a: Array) -> int:
		if frames_wanted > 0 and answered[0] >= 2 and not quit_posted[0]:
			# LEYTE_PROBE_SURVEY: at frame 5 the scenario (host +0x90), the objective word (+0xd2), the status (+0xd8)
			# and every entity (entity_array_1 0x20b44, count 0x20f2c): its controller's definition type and its
			# distance from the player. Observation only.
			if survey and frames[0] % 50 == 5:
				var mm = machine.memory
				var host: int = mm.read_u32(0x207fc)
				var player_obj: int = mm.read_u32(mm.read_u32(0x20b28))
				var pctl: int = mm.read_u32(0x20b28)
				print("[probe] survey: scenario %d objective %d status %d entities %d; player bombs %d rockets %d rounds %d" % [mm.read_s16(host + 0x90),
						mm.read_s16(host + 0xd2), mm.read_s16(host + 0xd8), mm.read_s32(0x20f2c),
						mm.read_s8(pctl + 0x68d) if pctl != 0 else -1, mm.read_s8(pctl + 0x68e) if pctl != 0 else -1,
						mm.read_s16(pctl + 0x696) if pctl != 0 else -1])
				var listed: Array = []
				for k in mm.read_s32(0x20f2c):
					listed.append(mm.read_u32(0x20b44 + 4 * k))
				for k in mm.read_s32(0x20b24):
					listed.append(mm.read_u32(0x20804 + 4 * k))
				for k in listed.size():
					var obj: int = listed[k]
					var c: int = mm.read_u32(obj)
					var kind: int = mm.read_s16(mm.read_u32(c + 4)) if c != 0 else -1
					if kind == -1 or kind == 42 or kind == 43:
						continue
					var dx: int = mm.read_s32(obj + 4) - mm.read_s32(player_obj + 4)
					var dz: int = mm.read_s32(obj + 0xc) - mm.read_s32(player_obj + 0xc)
					print("[probe]   entity %d: type %d at (%d, %d, %d) d %d flags %x radius %d" % [k, kind, mm.read_s32(obj + 4), mm.read_s32(obj + 8),
							mm.read_s32(obj + 0xc), int(sqrt(float(dx) * dx + float(dz) * dz)), mm.read_u8(obj + 0x16), mm.read_s32(obj + 0x20)])
			if frames[0] % 10 == 0:
				var m = machine.memory
				var ctl: int = m.read_u32(0x20b28)
				if ctl != 0:
					var obj: int = m.read_u32(ctl)
					print("[probe] frame %d: player %x at (%d, %d, %d) speed %d attitude %x/%x/%x damage %x fuel %d/%d throttle %d power %d ground %d view %d" % [frames[0], ctl,
							m.read_s32(obj + 4), m.read_s32(obj + 8), m.read_s32(obj + 0xc), m.read_s32(ctl + 0x62a),
							m.read_u16(ctl + 0x20), m.read_u16(ctl + 0x22), m.read_u16(ctl + 0x24), m.read_u16(ctl + 0x698),
							m.read_u8(ctl + 0x691), m.read_u8(ctl + 0x692), m.read_s8(ctl + 0x676), m.read_s32(ctl + 0x67c),
							m.read_u8(ctl + 0x64c), m.read_s16(0x23d88)])
				else:
					print("[probe] frame %d: no player aircraft" % frames[0])
			# LEYTE_PROBE_CENTRE: synthetic input only, the mouse moved each frame by the stick's offset (host words +0x37a /
			# +0x37c against the view centre 0x27052 / 0x27054), a hand holding the stick neutral
			var host: int = machine.memory.read_u32(0x207fc)
			var dx: int = machine.memory.read_s16(0x27052) - machine.memory.read_s16(host + 0x37a)
			var dy: int = machine.memory.read_s16(0x27054) - machine.memory.read_s16(host + 0x37c)
			if frames[0] % 10 == 0:
				print("[probe] frame %d: stick offset (%d, %d) mouse %s" % [frames[0], -dx, -dy, str(machine.toolbox.events.mouse)])
			# LEYTE_PROBE_PULL = from,to,dy: between those frames the hand holds the stick dy rows off centre instead
			if pull.size() == 3 and frames[0] >= pull[0] and frames[0] < pull[1]:
				dy += pull[2]
			# LEYTE_PROBE_AILERON = from,to,dx: between those frames the hand holds the stick dx columns off centre (the
			# headless mouse's left is negative: left aileron)
			if aileron.size() == 3 and frames[0] >= aileron[0] and frames[0] < aileron[1]:
				dx += aileron[2]
			# LEYTE_PROBE_AUTOPILOT: synthetic input only, a hand flying the aircraft from frame 240 as a player watching the
			# screen would: toward the nearest live bomber (definition type 5), banking to turn (left stick = positive roll
			# = the heading rising, measured in a characterisation flight) and climbing to its height; near it the nose is
			# put on it, and the mouse button (the guns, read through Button) is held only while it is close and in a
			# small cone ahead. No game variable is written.
			if autopilot and frames[0] >= 240:
				var am = machine.memory
				var actl: int = am.read_u32(0x20b28)
				if actl != 0:
					var aobj: int = am.read_u32(actl)
					var pitch: int = _s14(am.read_u16(actl + 0x20))
					var heading: int = am.read_u16(actl + 0x22)
					var roll: int = _s14(am.read_u16(actl + 0x24))
					var px: int = am.read_s32(aobj + 4)
					var py: int = am.read_s32(aobj + 8)
					var pz: int = am.read_s32(aobj + 0xc)
					var best := [0, 1.0e18]
					var pool: Array = []
					for k in am.read_s32(0x20f2c):
						pool.append(am.read_u32(0x20b44 + 4 * k))
					for k in am.read_s32(0x20b24):
						pool.append(am.read_u32(0x20804 + 4 * k))
					for o in pool:
						var oc: int = am.read_u32(o)
						if oc == 0 or (am.read_u8(o + 0x17) & 2) != 0 or am.read_s16(am.read_u32(oc + 4)) != 5:
							continue
						var d2: float = pow(am.read_s32(o + 4) - px, 2.0) + pow(am.read_s32(o + 0xc) - pz, 2.0) + pow(am.read_s32(o + 8) - py, 2.0)
						if d2 < best[1]:
							best = [o, d2]
					# a target within 60000 is kept (no switching to its wingman mid-pass) while it lives
					if locked[0] != 0 and (am.read_u8(locked[0] + 0x17) & 2) == 0 and am.read_u32(locked[0]) != 0:
						var lx: int = am.read_s32(locked[0] + 4) - px
						var lz: int = am.read_s32(locked[0] + 0xc) - pz
						var ly: int = am.read_s32(locked[0] + 8) - py
						best = [locked[0], float(lx) * lx + float(ly) * ly + float(lz) * lz]
					locked[0] = best[0] if best[0] != 0 and best[1] < 3.6e9 else 0
					var err: int = 0
					var want_pitch: int = clampi((3000 - py) / 8, -0x100, ap_climb)
					var dist: float = sqrt(best[1])
					var aim: int = 0
					if best[0] != 0:
						var bx: int = am.read_s32(best[0] + 4)
						var by: int = am.read_s32(best[0] + 8)
						var bz: int = am.read_s32(best[0] + 0xc)
						# the target's motion since last frame gives its course
						var vx: int = bx - last_seen[1] if last_seen[0] == best[0] else 0
						var vz: int = bz - last_seen[3] if last_seen[0] == best[0] else 0
						last_seen[0] = best[0]
						last_seen[1] = bx
						last_seen[2] = by
						last_seen[3] = bz
						var ax: int = bx - px
						var ay: int = by - py
						var az: int = bz - pz
						var tx: int = ax
						var tz: int = az
						# the pass is flown head-on along the target's ground track: the steering point is the track's
						# point a sixth of the range (2000..20000) toward the target from the aircraft's projection onto
						# it; inside 15000 the nose goes on the target itself
						var speed_h: float = sqrt(float(vx) * vx + float(vz) * vz)
						if dist > 15000.0 and speed_h > 20.0:
							var ux: float = vx / speed_h
							var uz: float = vz / speed_h
							var along: float = (px - bx) * ux + (pz - bz) * uz
							var ahead: float = clampf(dist / 6.0, 2000.0, 20000.0)
							tx = int(bx + ux * (along - ahead)) - px
							tz = int(bz + uz * (along - ahead)) - pz
							# early on (LEYTE_PROBE_AUTOPILOT's fourth value) the hand gains height on the track flying
							# the target's course, away from it, to turn and meet it head-on later
							if frames[0] < ap_flee:
								tx = int(bx + ux * (along + 30000.0)) - px
								tz = int(bz + uz * (along + 30000.0)) - pz
						var want_heading: int = int(round(atan2(-float(tx), float(tz)) / TAU * 0x4000)) & 0x3fff
						err = _s14(want_heading - heading)
						var elevation: int = int(round(atan2(float(ay), sqrt(float(ax) * ax + float(az) * az)) / TAU * 0x4000))
						want_pitch = clampi((ay + 800) / 6, -0x100, ap_climb) if dist > 30000.0 else clampi(elevation, -0x300, 0x300)
						aim = elevation - pitch
						# the guns: the target's own bearing, not the steering point's
						bearing_err[0] = _s14((int(round(atan2(-float(ax), float(az)) / TAU * 0x4000)) & 0x3fff) - heading)
					var want_roll: int = clampi(err * (4 if dist > 30000.0 or dist < 15000.0 else 8), -ap_bank, ap_bank) if frames[0] >= ap_straight else 0
					var sx: int = clampi(-(want_roll - roll) / 4, -0x60, 0x60)
					var sy: int = clampi((want_pitch - pitch) / (3 if dist > 20000.0 else 1) + absi(roll) / 8, -0xab, 0xab)
					dx += sx
					dy += sy
					var fire: bool = best[0] != 0 and dist < 15000.0 and absi(bearing_err[0]) < 0x100 and absi(aim) < 0x100
					if fire != firing[0]:
						firing[0] = fire
						machine.toolbox.events.post_mouse(machine.toolbox.events.mouse, fire)
						print("[probe] frame %d: guns %s, target %x at %d, its damage %x radius %d, rounds %d" % [frames[0], "held" if fire else "released", best[0],
								int(dist), am.read_u16(am.read_u32(best[0]) + 0x698) if best[0] != 0 and am.read_u32(best[0]) != 0 else -1,
								am.read_s32(best[0] + 0x20) if best[0] != 0 else -1, am.read_s16(actl + 0x696)])
					if frames[0] % 10 == 0 or (best[0] != 0 and dist < 20000.0):
						print("[probe] frame %d: autopilot target %x dist %d heading err %d (bearing %d) aim %d pitch %d/%d roll %d/%d stick (%d, %d)" % [
								frames[0], best[0], int(dist), err, bearing_err[0], aim, pitch, want_pitch, roll, want_roll, sx, sy])
			if centre_stick and (dx != 0 or dy != 0):
				machine.toolbox.events.mouse += Vector2i(dx, dy)
			# LEYTE_PROBE_THROTTLE: synthetic input only, '=' (key code 0x18) held from frame 20 to 80, the throttle-up key
			if throttle_test and frames[0] == 20:
				print("[probe] frame 20: '=' down")
				machine.toolbox.events.post_key(0x18, 0x3d, true, false, 0)
			if throttle_test and frames[0] == 80:
				print("[probe] frame 80: '=' up")
				machine.toolbox.events.post_key(0x18, 0x3d, false, false, 0)
			# LEYTE_PROBE_STICK = 2 / 8: synthetic input only, that digit (the keyboard steering's vertical) held 85..115
			if stick_char != 0 and frames[0] == 85:
				print("[probe] frame 85: '%c' down" % stick_char)
				machine.toolbox.events.post_key(0x13 if stick_char == 0x32 else 0x1c, stick_char, true, false, 0)
			if stick_char != 0 and frames[0] == 115:
				print("[probe] frame 115: '%c' up" % stick_char)
				machine.toolbox.events.post_key(0x13 if stick_char == 0x32 else 0x1c, stick_char, false, false, 0)
			frames[0] += 1
			if frames[0] >= frames_wanted:
				quit_posted[0] = true
				print("[probe] %d frames flown: Command-Q posted" % frames[0])
				machine.toolbox.events.post_key(0x0c, 0x51, true, false, 0x100)
				machine.toolbox.events.post_key(0x0c, 0x51, false, false, 0x100)
		return int(get_mouse.call(a))
	machine.toolbox.traps[0xa938] = func(a: Array) -> int:
		if frames_wanted == 0 and answered[0] >= 2 and not quit_posted[0]:
			quit_posted[0] = true
			print("[probe] flight loop entered: Command-Q posted")
			machine.toolbox.events.post_key(0x0c, 0x51, true, false, 0x100)
			machine.toolbox.events.post_key(0x0c, 0x51, false, false, 0x100)
		return int(hilite.call(a))
	# Alerts (Alert, StopAlert, NoteAlert, CautionAlert) answered with Return too, their default item.
	for word in [0xa985, 0xa986, 0xa987, 0xa988]:
		var alert: Callable = machine.toolbox.traps[word]
		machine.toolbox.traps[word] = func(a: Array) -> int:
			machine.toolbox.events.post_key(0x24, 13, true, false, 0)
			machine.toolbox.events.post_key(0x24, 13, false, false, 0)
			var r: int = int(alert.call(a))
			print("[probe] alert %04x %s -> item %d" % [word, str(a), r])
			return r
	# ExitToShell parks the game thread for the scene to end (the program ends there on the Mac); the probe
	# has no scene, so it reports there and ends the process.
	machine.toolbox.traps[0xa9f4] = func(_a: Array) -> int:
		machine.log_line("ExitToShell")
		print("[probe] ExitToShell; missing: ", machine.missing.size())
		for k in machine.missing:
			print("[probe] missing ", k, " ", machine.missing[k])
		LeyteProfile.clear()
		OS.kill(OS.get_process_id())
		return 0
	var program = machine.segments.instances[2]
	program.a7 = machine.STACK_TOP
	program.event_loop_passes = 1
	print("[probe] main")
	program.main()
	print("[probe] main returned; missing: ", machine.missing.size())
	for k in machine.missing:
		print("[probe] missing ", k, " ", machine.missing[k])
	LeyteProfile.clear()
	quit()
