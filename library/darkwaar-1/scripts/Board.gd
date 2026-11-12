extends Node2D
## Gestionnaire principal du plateau de jeu
## Charge les niveaux, gère les déplacements et l'état du jeu

@onready var tilemap: TileMap = $TileMap
@onready var player: Node2D = $Player
@onready var swipe: Control = $Swipe

var level_index := 0
var levels: Array = []
var move_limit := 0
var moves := 0
var start_pos := Vector2i()
var goal_pos := Vector2i()
var history: Array[Vector2i] = []

# Constantes pour les types de tuiles
const L_FLOOR := 0
const L_WALL := 1
const L_GOAL := 2

func _ready():
	levels = Levels.all()
	load_level(0)
	swipe.connect("swiped", Callable(self, "_on_swipe"))

## Charge un niveau depuis son index
func load_level(idx: int):
	if idx < 0 or idx >= levels.size():
		print("Niveau invalide: ", idx)
		return
	
	level_index = idx
	moves = 0
	history.clear()
	tilemap.clear()
	
	var data: Dictionary = levels[idx]
	move_limit = int(data.limit)
	var map: Array = data.map
	
	# Charger la carte ASCII dans le TileMap
	for y in map.size():
		for x in map[y].length():
			var ch: String = map[y][x]
			var cell := Vector2i(x, y)
			match ch:
				"#":  # Mur
					tilemap.set_cell(0, cell, L_WALL, Vector2i.ZERO)
				"G":  # Objectif
					tilemap.set_cell(0, cell, L_GOAL, Vector2i.ZERO)
					goal_pos = cell
				"P":  # Position de départ du joueur
					tilemap.set_cell(0, cell, L_FLOOR, Vector2i.ZERO)
					start_pos = cell
				".":  # Sol
					tilemap.set_cell(0, cell, L_FLOOR, Vector2i.ZERO)
	
	# Positionner le joueur
	player.position = tilemap.map_to_local(start_pos)

## Gère les swipes du joueur
func _on_swipe(dir: Vector2):
	var from := tilemap.local_to_map(player.position)
	var to := from + Vector2i(int(dir.x), int(dir.y))
	
	if _is_walkable(to):
		# Sauvegarder la position pour l'undo
		history.append(from)
		# Déplacer le joueur
		player.position = tilemap.map_to_local(to)
		moves += 1
		# Vérifier l'état du jeu
		_check_state()

## Vérifie si une case est traversable
func _is_walkable(cell: Vector2i) -> bool:
	var td := tilemap.get_cell_tile_data(0, cell)
	if td == null:
		return false
	var id := tilemap.get_cell_source_id(0, cell)
	return id != L_WALL

## Annule le dernier mouvement
func undo():
	if history.is_empty():
		return
	
	var prev: Vector2i = history.pop_back()
	player.position = tilemap.map_to_local(prev)
	moves = max(0, moves - 1)

## Remet le niveau à zéro
func reset():
	load_level(level_index)

## Passe au niveau suivant
func next_level():
	if level_index + 1 < levels.size():
		load_level(level_index + 1)

## Vérifie l'état actuel du jeu (victoire/échec)
func _check_state():
	var current_pos := tilemap.local_to_map(player.position)
	
	if current_pos == goal_pos:
		# Victoire !
		get_parent().call_deferred("on_level_complete", moves, move_limit)
	elif moves > move_limit:
		# Échec - limite de coups dépassée
		get_parent().call_deferred("on_level_failed")

## Retourne le nombre de coups actuels
func get_moves() -> int:
	return moves

## Retourne la limite de coups
func get_move_limit() -> int:
	return move_limit

## Retourne l'index du niveau actuel
func get_level_index() -> int:
	return level_index
