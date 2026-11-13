extends Control

# Main scene manager for the HearthStone-like game
# Equivalent to the main.js entry point in Phaser

func _ready():
	# Start with the start scene
	get_tree().change_scene_to_file("res://scenes/StartScene.tscn")
