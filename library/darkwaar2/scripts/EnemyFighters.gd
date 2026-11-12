# EnemyFighters - Manages enemy's battlefield minions
# Equivalent to Phaser EnemyFighter class

class_name EnemyFighters
extends Node

var game_scene: Control
var board_container: HBoxContainer
var fighters: Array = []

func _init(scene: Control):
	game_scene = scene
	board_container = scene.get_node("EnemyArea/EnemyBoard")

func get_fighter_count() -> int:
	return fighters.size()

func summon_fighter(card: DataManager.Card):
	# Create a new fighter on the board
	var fighter = HeroFighters.Fighter.new(card, false)  # false = enemy's fighter
	fighters.append(fighter)
	_create_fighter_ui(fighter)

func _create_fighter_ui(fighter: HeroFighters.Fighter):
	var button = TextureButton.new()
	button.custom_minimum_size = Vector2(100, 50)
	
	# Load fighter texture (use fight version if available)
	var texture_path = "res://assets/" + fighter.card.fight_texture + ".png"
	if ResourceLoader.exists(texture_path):
		var texture = load(texture_path)
		button.texture_normal = texture
	else:
		# Fallback to regular card texture
		texture_path = "res://assets/" + fighter.card.name + ".png"
		if ResourceLoader.exists(texture_path):
			var texture = load(texture_path)
			button.texture_normal = texture
	
	# Add text label for fighter stats
	var label = Label.new()
	label.text = fighter.card.cn_name + "\nAttack:" + str(fighter.attack) + " Health:" + str(fighter.hp)
	label.add_theme_color_override("font_color", Color.WHITE)
	label.add_theme_color_override("font_shadow_color", Color.BLACK)
	label.add_theme_constant_override("shadow_offset_x", 1)
	label.add_theme_constant_override("shadow_offset_y", 1)
	button.add_child(label)
	
	board_container.add_child(button)

func awake_fighters():
	# Remove sleep state from all fighters (can attack)
	for fighter in fighters:
		fighter.can_attack = true

