# CardConfig - Equivalent to the Phaser CardConfig
# Contains all card definitions and game configuration

class_name CardConfig

# Card definitions
static var card_info = [
	{
		"name": "fishman_baby",
		"fight": "fishman_baby_fight",
		"cn_name": "Murloc Baby",
		"fee": 1,
		"attack": 1,
		"hp": 1,
		"id": 1,
		"type": "entourage",
		"quality": "1"
	},
	{
		"name": "freshwater_crocodile",
		"fight": "freshwater_crocodile_fight",
		"cn_name": "Freshwater Crocodile",
		"fee": 2,
		"attack": 2,
		"hp": 3,
		"id": 2,
		"type": "entourage",
		"quality": "1"
	},
	{
		"name": "ogre",
		"fight": "ogre_fight",
		"cn_name": "Ogre Mage",
		"fee": 4,
		"attack": 4,
		"hp": 4,
		"id": 3,
		"type": "entourage",
		"quality": "1"
	},
	{
		"name": "dead_wing",
		"fight": "dead_wing_fight",
		"cn_name": "Deathwing",
		"fee": 9,
		"attack": 9,
		"hp": 9,
		"id": 4,
		"type": "entourage",
		"quality": "3"
	},
	{
		"name": "rose",
		"fight": "rose_fight",
		"cn_name": "Ragnaros",
		"fee": 8,
		"attack": 8,
		"hp": 8,
		"id": 5,
		"type": "entourage",
		"quality": "3"
	},
	{
		"name": "velociraptor",
		"fight": "velociraptor_fight",
		"cn_name": "Super Raptor",
		"fee": 4,
		"attack": 4,
		"hp": 5,
		"id": 6,
		"type": "entourage",
		"quality": "1"
	},
	{
		"name": "arcaneWisdom",
		"fight": "",
		"cn_name": "Arcane Wisdom",
		"fee": 3,
		"attack": 0,
		"hp": 0,
		"id": 7,
		"type": "magic",
		"quality": "1"
	}
]

# Game configuration
static var card_length: int = 30
static var card_initial_length: int = 4

# Get card by ID
static func get_card_by_id(id: int) -> Dictionary:
	for card in card_info:
		if card.id == id:
			return card
	return {}

# Get all cards of a specific type
static func get_cards_by_type(type: String) -> Array:
	var result = []
	for card in card_info:
		if card.type == type:
			result.append(card)
	return result

