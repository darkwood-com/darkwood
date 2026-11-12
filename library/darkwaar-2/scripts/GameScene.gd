extends Control

# GameScene - Equivalent to Phaser GameScene
# Main game scene with UI management

var data_manager: DataManager
var ui_manager: UIManager

func _ready():
	# Initialize data manager
	data_manager = DataManager.get_instance()
	
	# Initialize UI manager
	ui_manager = UIManager.new(self)
	add_child(ui_manager)
	
	# Connect UI buttons
	$TurnButton.pressed.connect(_on_turn_button_pressed)
	$PlayCardButton.pressed.connect(_on_play_card_button_pressed)
	
	# Add labels to TextureButtons
	_add_button_label($TurnButton, "End Turn")
	_add_button_label($PlayCardButton, "Play Card")
	
	# Initialize game state
	_initialize_game()

func _add_button_label(button: TextureButton, text: String):
	# Create a label for the TextureButton
	var label = Label.new()
	label.name = "Label"
	label.text = text
	label.add_theme_color_override("font_color", Color.WHITE)
	label.add_theme_color_override("font_shadow_color", Color.BLACK)
	label.add_theme_constant_override("shadow_offset_x", 1)
	label.add_theme_constant_override("shadow_offset_y", 1)
	label.horizontal_alignment = HORIZONTAL_ALIGNMENT_CENTER
	label.vertical_alignment = VERTICAL_ALIGNMENT_CENTER
	button.add_child(label)

func _initialize_game():
	# Set initial game state
	data_manager.turn = 0
	data_manager.fee = 1
	data_manager.hero_current_fee = 1
	data_manager.enemy_current_fee = 1
	
	# Update UI
	_update_mana_display()
	_update_turn_button()

func _update_mana_display():
	$PlayerArea/PlayerMana.text = str(data_manager.hero_current_fee) + "/" + str(data_manager.fee)
	$EnemyArea/EnemyMana.text = str(data_manager.enemy_current_fee) + "/" + str(data_manager.fee)

func _update_turn_button():
	# Update the label text based on turn
	var turn_label = $TurnButton.get_node("Label")
	if data_manager.turn == 0:
		turn_label.text = "End Turn"
	else:
		turn_label.text = "Enemy Turn"

func _on_turn_button_pressed():
	if data_manager.turn == 0:
		# End player turn, start enemy turn
		data_manager.turn = 1
		_update_turn_button()
		
		# Enemy turn logic
		_enemy_turn()
	else:
		# End enemy turn, start player turn
		data_manager.turn = 0
		_update_turn_button()
		
		# Player turn logic
		_player_turn()

func _enemy_turn():
	# Enemy draws card
	ui_manager.enemy_hand_card.add_card()
	
	# Enemy AI plays cards
	if ui_manager.ai:
		ui_manager.ai.play_cards()
	
	# Enemy attacks
	if ui_manager.ai:
		ui_manager.ai.choose_attacks()
	
	# End enemy turn after delay
	await get_tree().create_timer(2.0).timeout
	_on_turn_button_pressed()

func _player_turn():
	# Increase mana
	if data_manager.fee < 9:
		data_manager.fee += 1
	data_manager.hero_current_fee = data_manager.fee
	
	# Player draws card
	ui_manager.hero_hand_card.add_card()
	
	# Update UI
	_update_mana_display()

func _on_play_card_button_pressed():
	if data_manager.turn != 0:
		return
	
	if data_manager.hero_choice_card:
		# Check if player has enough mana
		if data_manager.hero_current_fee < data_manager.hero_choice_card.fee:
			_add_log("Not enough mana to play this card")
			return
		
		# Play the card
		_play_card(data_manager.hero_choice_card)
		
		# Remove from hand
		ui_manager.hero_hand_card.remove_card(data_manager.hero_choice_card)
		data_manager.hero_choice_card = null
	else:
		_add_log("Please select a card first")

func _play_card(card: DataManager.Card):
	# Deduct mana
	data_manager.hero_current_fee -= card.fee
	_update_mana_display()
	
	# Play card based on type
	if card.type == "magic":
		_play_magic_card(card)
	elif card.type == "entourage":
		_play_entourage_card(card)

func _play_magic_card(card: DataManager.Card):
	match card.cn_name:
		"Arcane Wisdom":
			ui_manager.hero_hand_card.add_card()
			ui_manager.hero_hand_card.add_card()
			_add_log("Used magic: Arcane Wisdom")

func _play_entourage_card(card: DataManager.Card):
	# Check board space
	if ui_manager.hero_fighters.get_fighter_count() >= 5:
		_add_log("Your battlefield is full")
		return
	
	# Summon minion
	ui_manager.hero_fighters.summon_fighter(card)
	_add_log("Summoned " + card.cn_name)

func _add_log(message: String):
	$LogArea.text += message + "\n"
	# Auto-scroll to bottom
	$LogArea.scroll_vertical = $LogArea.get_line_count()
