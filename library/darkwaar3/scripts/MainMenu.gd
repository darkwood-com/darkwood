extends Control

@onready var instructions_panel = $InstructionsPanel

func _ready():
	hide_instructions()

func _on_start_button_pressed():
	get_tree().change_scene_to_file("res://scenes/Main.tscn")

func _on_instructions_button_pressed():
	show_instructions()

func _on_quit_button_pressed():
	get_tree().quit()

func _on_close_instructions_button_pressed():
	hide_instructions()

func show_instructions():
	instructions_panel.visible = true

func hide_instructions():
	instructions_panel.visible = false
