# EnemyHandCard - Manages enemy's hand cards
# Equivalent to Phaser EnemyHandCard class

class_name EnemyHandCard
extends Node

var game_scene: Control
var hand_container: HBoxContainer
var cards: Array = []

func _init(scene: Control):
	game_scene = scene
	hand_container = scene.get_node("EnemyArea/EnemyHand")

func add_card():
	# Draw a random card for the enemy
	var random_card_data = CardConfig.card_info[randi() % CardConfig.card_info.size()]
	var card = DataManager.Card.new(random_card_data)
	cards.append(card)
	_create_card_back()

func _create_card_back():
	# Create a card back (hidden card)
	var button = TextureButton.new()
	button.custom_minimum_size = Vector2(60, 80)
	
	# Load card back texture
	var texture_path = "res://assets/card_back.png"
	if ResourceLoader.exists(texture_path):
		var texture = load(texture_path)
		button.texture_normal = texture
	
	hand_container.add_child(button)

