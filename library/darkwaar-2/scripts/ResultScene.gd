extends Control

# ResultScene - Equivalent to Phaser ResultScene
# Shows game result (win/loss)

func _ready():
	var data_manager = DataManager.get_instance()
	var result_label = $ResultLabel
	var restart_button = $RestartButton
	
	# Set result text based on game outcome
	if data_manager.result == 0:
		result_label.text = "You Loss"
		result_label.modulate = Color.RED
	else:
		result_label.text = "You Win"
		result_label.modulate = Color.GREEN
	
	# Connect restart button
	restart_button.pressed.connect(_on_restart_button_pressed)

func _on_restart_button_pressed():
	# Reset game state
	var data_manager = DataManager.get_instance()
	data_manager.hero_hand_card_id_list.clear()
	data_manager.hero_hand_cards.clear()
	data_manager.enemy_hand_cards.clear()
	data_manager.hero_fighters.clear()
	data_manager.enemy_fighters.clear()
	
	# Go back to start scene
	get_tree().change_scene_to_file("res://scenes/StartScene.tscn")

