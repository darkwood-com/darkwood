extends Control

# CardChoiceScene - Equivalent to Phaser CardChoiseScene
# Card selection screen where players choose their deck

var selected_cards: Array = []
var card_buttons: Array = []
var selected_card_buttons: Array = []

func _ready():
	# Initialize DataManager
	var data_manager = DataManager.get_instance()
	
	# Connect confirm button
	var confirm_button = $ConfirmButton
	confirm_button.pressed.connect(_on_confirm_button_pressed)
	
	# Add label to confirm button
	_add_button_label(confirm_button, "Confirm")
	
	# Build card selection interface
	_build_card_selection()

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

func _build_card_selection():
	var card_container = $CardContainer
	var selected_container = $SelectedCardsContainer
	
	# Clear existing children
	for child in card_container.get_children():
		child.queue_free()
	for child in selected_container.get_children():
		child.queue_free()
	
	# Create buttons for each card
	for card_data in CardConfig.card_info:
		var button = TextureButton.new()
		button.custom_minimum_size = Vector2(80, 100)
		button.pressed.connect(_on_card_selected.bind(card_data))
		
		# Load card texture
		var texture_path = "res://assets/" + card_data.name + ".png"
		if ResourceLoader.exists(texture_path):
			var texture = load(texture_path)
			button.texture_normal = texture
		
		# Add text label for card info
		var label = Label.new()
		label.text = card_data.cn_name + "\nCost:" + str(card_data.fee)
		label.add_theme_color_override("font_color", Color.WHITE)
		label.add_theme_color_override("font_shadow_color", Color.BLACK)
		label.add_theme_constant_override("shadow_offset_x", 1)
		label.add_theme_constant_override("shadow_offset_y", 1)
		button.add_child(label)
		
		card_container.add_child(button)
		card_buttons.append(button)

func _on_card_selected(card_data: Dictionary):
	var data_manager = DataManager.get_instance()
	
	# Check if card is already selected
	for selected_card in data_manager.hero_hand_card_id_list:
		if selected_card.id == card_data.id:
			return  # Card already selected
	
	# Add to selected cards
	data_manager.hero_hand_card_id_list.append(DataManager.Card.new(card_data))
	
	# Update selected cards display
	_update_selected_cards_display()

func _update_selected_cards_display():
	var selected_container = $SelectedCardsContainer
	var data_manager = DataManager.get_instance()
	
	# Clear existing selected card buttons
	for button in selected_card_buttons:
		button.queue_free()
	selected_card_buttons.clear()
	
	# Create buttons for selected cards
	for card in data_manager.hero_hand_card_id_list:
		var button = TextureButton.new()
		button.custom_minimum_size = Vector2(80, 100)
		button.pressed.connect(_on_selected_card_removed.bind(card))
		
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
		
		selected_container.add_child(button)
		selected_card_buttons.append(button)

func _on_selected_card_removed(card: DataManager.Card):
	var data_manager = DataManager.get_instance()
	
	# Remove from selected cards
	for i in range(data_manager.hero_hand_card_id_list.size()):
		if data_manager.hero_hand_card_id_list[i].id == card.id:
			data_manager.hero_hand_card_id_list.remove_at(i)
			break
	
	# Update display
	_update_selected_cards_display()

func _on_confirm_button_pressed():
	# Proceed to game scene
	get_tree().change_scene_to_file("res://scenes/GameScene.tscn")

