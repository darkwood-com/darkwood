# AI - Enemy AI logic
# Equivalent to Phaser AI class

class_name AI
extends Node

var data_manager: DataManager

func _init():
	data_manager = DataManager.get_instance()

func play_cards():
	# Simple AI: play cards if mana allows
	var available_cards = []
	
	# Get cards that can be played with current mana
	for card in data_manager.enemy_hand_cards:
		if card.fee <= data_manager.enemy_current_fee:
			available_cards.append(card)
	
	# Play a random available card
	if available_cards.size() > 0:
		var card_to_play = available_cards[randi() % available_cards.size()]
		_play_card(card_to_play)

func _play_card(card: DataManager.Card):
	# Deduct mana
	data_manager.enemy_current_fee -= card.fee
	
	# Play card based on type
	if card.type == "entourage":
		# Summon minion
		if data_manager.enemy_fighters.size() < 5:
			data_manager.enemy_fighters.append(card)
	
	# Remove from hand
	for i in range(data_manager.enemy_hand_cards.size()):
		if data_manager.enemy_hand_cards[i].id == card.id:
			data_manager.enemy_hand_cards.remove_at(i)
			break

func choose_attacks():
	# Simple AI: attack with all available fighters
	for fighter in data_manager.enemy_fighters:
		if fighter.can_attack:
			# Attack player hero (simplified)
			_attack_hero(fighter)

func _attack_hero(fighter):
	# Reduce player health
	var player_health = 30  # This should be tracked in DataManager
	player_health -= fighter.attack
	fighter.can_attack = false
	
	# Check for game end
	if player_health <= 0:
		data_manager.result = 0  # Player loses
		get_tree().change_scene_to_file("res://scenes/ResultScene.tscn")

