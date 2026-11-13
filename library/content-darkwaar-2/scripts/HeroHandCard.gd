# HeroHandCard - Manages player's hand cards
# Equivalent to Phaser HeroHandCard class

class_name HeroHandCard
extends Node

var game_scene: Control
var hand_container: HBoxContainer
var cards: Array = []

func _init(scene: Control):
	game_scene = scene
	hand_container = scene.get_node("PlayerArea/PlayerHand")

func add_card():
	# Draw a random card from the player's deck
	var data_manager = DataManager.get_instance()
	if data_manager.hero_hand_card_id_list.size() > 0:
		var random_card_data = data_manager.hero_hand_card_id_list[randi() % data_manager.hero_hand_card_id_list.size()]
		var card = DataManager.Card.new(random_card_data)
		cards.append(card)
		_create_card_button(card)

func _create_card_button(card: DataManager.Card):
	var button = TextureButton.new()
	button.custom_minimum_size = Vector2(80, 100)
	button.pressed.connect(_on_card_clicked.bind(card))
	
	# Load card texture
	var texture_path = "res://assets/" + card.name + ".png"
	if ResourceLoader.exists(texture_path):
		var texture = load(texture_path)
		button.texture_normal = texture
	
	# Add text label for card info
	var label = Label.new()
	label.text = card.cn_name + "\nCost:" + str(card.fee)
	label.add_theme_color_override("font_color", Color.WHITE)
	label.add_theme_color_override("font_shadow_color", Color.BLACK)
	label.add_theme_constant_override("shadow_offset_x", 1)
	label.add_theme_constant_override("shadow_offset_y", 1)
	button.add_child(label)
	
	hand_container.add_child(button)

func _on_card_clicked(card: DataManager.Card):
	var data_manager = DataManager.get_instance()
	data_manager.hero_choice_card = card

func remove_card(card: DataManager.Card):
	# Remove card from hand
	for i in range(cards.size()):
		if cards[i].id == card.id:
			cards.remove_at(i)
			break
	
	# Remove button from UI
	for child in hand_container.get_children():
		if child.text.begins_with(card.cn_name):
			child.queue_free()
			break

