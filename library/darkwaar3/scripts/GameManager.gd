extends Node

signal level_completed
signal game_completed

var current_level = 0
var total_levels = 5
var levels = []

func _ready():
	load_levels()

func load_levels():
	# Level 1 - Simple 3x3 with one box and target
	levels.append([
		"#####",
		"#.$.#",
		"#.@.#",
		"#...#",
		"#####"
	])
	
	# Level 2 - 4x4 with boxes and targets
	levels.append([
		"######",
		"#.$..#",
		"#.@..#",
		"#.$..#",
		"######"
	])
	
	# Level 3 - More complex
	levels.append([
		"#######",
		"#.$...#",
		"#.@...#",
		"#.$...#",
		"#######"
	])
	
	# Level 4 - With boxes and targets
	levels.append([
		"########",
		"#.$....#",
		"#.@....#",
		"#.$....#",
		"########"
	])
	
	# Level 5 - Final level
	levels.append([
		"##########",
		"#.$......#",
		"#.@......#",
		"#.$......#",
		"##########"
	])

func get_current_level():
	if current_level < levels.size():
		return levels[current_level]
	return null

func next_level():
	current_level += 1
	if current_level >= total_levels:
		game_completed.emit()
		return false
	return true

func restart_level():
	# Level will be restarted by the game scene
	pass

func is_level_complete(boxes, targets):
	if targets.size() == 0:
		return false
		
	for target in targets:
		var has_box = false
		for box in boxes:
			if box.global_position.distance_to(target.global_position) < 32:
				has_box = true
				break
		if not has_box:
			return false
	return true