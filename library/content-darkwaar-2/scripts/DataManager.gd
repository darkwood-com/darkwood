# DataManager - Equivalent to the Phaser DataManager class
# Manages all game state and data
# Fixed compilation issues

class_name DataManager

# Game state
var turn: int = 0  # 0 = player turn, 1 = enemy turn
var fee: int = 1   # Current mana/fee
var result: int = 0  # 0 = player loss, 1 = player win

# Player data
var hero_choice_card: Card = null
var hero_fighters: Array = []
var hero_hand_cards: Array = []
var hero_current_fee: int = 1
var hero_hand_card_id_list: Array = []

# Enemy data
var enemy_hand_cards: Array = []
var enemy_fighters: Array = []
var enemy_current_fee: int = 1

# UI references
var turn_over_button: Button = null
var remain_card: Node = null

# AI
var ai: AI = null

# Static instance for global access
static var instance: DataManager

static func get_instance() -> DataManager:
	if instance == null:
		instance = DataManager.new()
	return instance

# Card class definition
class Card:
	var name: String
	var fight_texture: String
	var cn_name: String
	var fee: int
	var attack: int
	var hp: int
	var id: int
	var type: String  # "entourage" or "magic"
	var quality: String
	
	func _init(card_data: Dictionary):
		name = card_data.get("name", "")
		fight_texture = card_data.get("fight", "")
		cn_name = card_data.get("cn_name", "")
		fee = card_data.get("fee", 0)
		attack = card_data.get("attack", 0)
		hp = card_data.get("hp", 0)
		id = card_data.get("id", 0)
		type = card_data.get("type", "")
		quality = card_data.get("quality", "")

