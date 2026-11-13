extends Control

@onready var game_scene = get_parent()
@onready var level_complete_panel = $LevelCompletePanel
@onready var game_complete_panel = $GameCompletePanel
@onready var level_label = $TopPanel/LevelLabel
@onready var moves_label = $TopPanel/MovesLabel

var moves_count = 0

func _ready():
	update_level_display()
	hide_all_panels()

func _input(event):
	if event is InputEventScreenTouch and event.pressed:
		handle_touch_input(event.position)

func handle_touch_input(pos: Vector2):
	var screen_size = get_viewport().get_visible_rect().size
	var center_x = screen_size.x / 2
	var center_y = screen_size.y / 2
	
	# Calculate direction based on touch position relative to center
	var direction = Vector2.ZERO
	
	if pos.x < center_x - 100:
		direction.x = -1  # Left
	elif pos.x > center_x + 100:
		direction.x = 1   # Right
	
	if pos.y < center_y - 100:
		direction.y = -1  # Up
	elif pos.y > center_y + 100:
		direction.y = 1   # Down
	
	if direction != Vector2.ZERO:
		move_player(direction)

func move_player(direction: Vector2):
	if game_scene.has_method("get_game_objects"):
		var player = game_scene.get_node("Player")
		if player and player.has_method("move_direction"):
			player.move_direction(direction)
			moves_count += 1
			update_moves_display()

func update_level_display():
	level_label.text = "Level: " + str(GameManager.current_level + 1)

func update_moves_display():
	moves_label.text = "Moves: " + str(moves_count)

func show_level_complete():
	level_complete_panel.visible = true

func hide_level_complete():
	level_complete_panel.visible = false

func show_game_complete():
	game_complete_panel.visible = true

func hide_game_complete():
	game_complete_panel.visible = false

func hide_all_panels():
	level_complete_panel.visible = false
	game_complete_panel.visible = false

func _on_next_level_pressed():
	hide_level_complete()
	moves_count = 0
	update_moves_display()

func _on_restart_pressed():
	hide_all_panels()
	moves_count = 0
	update_moves_display()
	game_scene.restart_level()

func _on_main_menu_pressed():
	get_tree().change_scene_to_file("res://scenes/MainMenu.tscn")
