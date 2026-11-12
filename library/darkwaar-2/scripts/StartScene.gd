extends Control

# StartScene - Equivalent to Phaser StartScene
# Welcome screen with start button

func _ready():
	# Connect the start button
	var start_button = $StartButton
	start_button.pressed.connect(_on_start_button_pressed)

func _on_start_button_pressed():
	# Change to card choice scene
	get_tree().change_scene_to_file("res://scenes/CardChoiceScene.tscn")

