# UIManager - Equivalent to Phaser UIManager
# Manages all UI components in the game

class_name UIManager
extends Node

var game_scene: Control
var data_manager: DataManager

# UI Components
var hero_hand_card: HeroHandCard
var enemy_hand_card: EnemyHandCard
var hero_fighters: HeroFighters
var enemy_fighters: EnemyFighters
var ai: AI

func _init(scene: Control):
	game_scene = scene
	data_manager = DataManager.get_instance()

func _ready():
	# Initialize all UI components
	hero_hand_card = HeroHandCard.new(game_scene)
	enemy_hand_card = EnemyHandCard.new(game_scene)
	hero_fighters = HeroFighters.new(game_scene)
	enemy_fighters = EnemyFighters.new(game_scene)
	ai = AI.new()
	
	# Add components to scene
	game_scene.add_child(hero_hand_card)
	game_scene.add_child(enemy_hand_card)
	game_scene.add_child(hero_fighters)
	game_scene.add_child(enemy_fighters)
	game_scene.add_child(ai)

