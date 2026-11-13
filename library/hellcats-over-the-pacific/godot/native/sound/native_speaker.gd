extends "res://original/mac/machine/mac_speaker.gd"

## The Native edition's speaker: the Original speaker (a Godot AudioStreamGenerator at 22254.5 Hz, and
## a player per SndPlay sound), fed with the native driver's samples (native_id02.gd take()) instead of
## the Sound Manager's reading of SoundBase. Without the native driver (1.0.4) it is the Original's.

func _process(delta: float) -> void:
	if machine == null:
		return
	var program = machine.segments.instances[2]
	if not program.has_method("take"):
		super(delta)
		return
	machine.toolbox.sounds.take()               # the buffer the native driver no longer fills
	var samples: PackedFloat32Array = program.take()
	var room: int = _playback.get_frames_available()
	for k in mini(samples.size(), room):
		_playback.push_frame(Vector2(samples[k], samples[k]))
	var sounds = machine.toolbox.sounds
	for played in sounds.take_played():
		var player := AudioStreamPlayer.new()
		player.stream = played[0]
		player.set_meta("channel", played[1])
		player.finished.connect(player.queue_free)
		add_child(player)
		player.play()
	for channel in sounds.take_stopped():
		for player in get_children():
			if player.get_meta("channel", -1) == channel:
				player.stop()
				player.queue_free()
