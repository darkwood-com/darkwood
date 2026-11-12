extends Node2D

@onready var player = $Player
@onready var ui = $UI

var boxes: Array = []
var walls: Array = []
var targets: Array = []

func _ready():
	GameManager.level_completed.connect(_on_level_completed)
	GameManager.game_completed.connect(_on_game_completed)
	load_level()

func load_level():
	clear_level()
	
	var level_data = GameManager.get_current_level()
	if level_data == null:
		return
	
	var start_x = 100
	var start_y = 200
	var tile_size = 64
	
	for y in range(level_data.size()):
		for x in range(level_data[y].length()):
			var char = level_data[y][x]
			var pos = Vector2(start_x + x * tile_size, start_y + y * tile_size)
			
			match char:
				"#":  # Wall
					create_wall(pos)
				"@":  # Player
					player.global_position = pos
				"$":  # Box
					create_box(pos)
				".":  # Target
					create_target(pos)
				"*":  # Box on target
					create_box(pos)
					create_target(pos)

func create_wall(pos: Vector2):
	var wall = ColorRect.new()
	wall.size = Vector2(64, 64)
	wall.color = Color.BROWN
	wall.position = pos - Vector2(32, 32)
	add_child(wall)
	walls.append(wall)

func create_box(pos: Vector2):
	var box = ColorRect.new()
	box.size = Vector2(48, 48)
	box.color = Color.YELLOW
	box.position = pos - Vector2(24, 24)
	add_child(box)
	boxes.append(box)

func create_target(pos: Vector2):
	var target = ColorRect.new()
	target.size = Vector2(32, 32)
	target.color = Color.RED
	target.position = pos - Vector2(16, 16)
	add_child(target)
	targets.append(target)

func clear_level():
	for box in boxes:
		box.queue_free()
	for wall in walls:
		wall.queue_free()
	for target in targets:
		target.queue_free()
	
	boxes.clear()
	walls.clear()
	targets.clear()

func get_game_objects():
	return {
		"boxes": boxes,
		"walls": walls,
		"targets": targets
	}

func _on_level_completed():
	ui.show_level_complete()
	await get_tree().create_timer(1.0).timeout
	if GameManager.next_level():
		load_level()
		ui.hide_level_complete()
	else:
		ui.show_game_complete()

func _on_game_completed():
	ui.show_game_complete()

func restart_level():
	load_level()
	ui.hide_level_complete()
	ui.hide_game_complete()
