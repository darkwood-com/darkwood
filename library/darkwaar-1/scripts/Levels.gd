extends Resource
class_name Levels

## Classe statique contenant tous les niveaux du jeu
## Chaque niveau contient un nom, une limite de coups et une carte ASCII

static func all() -> Array:
	return [
		{
			"name": "Init",
			"limit": 4,
			"map": [
				"######",
				"#P...#",
				"#.##.#",
				"#...G#",
				"######",
			]
		},
		{
			"name": "Corner",
			"limit": 3,
			"map": [
				"#####",
				"#P..#",
				"###.#",
				"#..G#",
				"#####",
			]
		},
		{
			"name": "Weave",
			"limit": 6,
			"map": [
				"#######",
				"#P#...#",
				"#.#.#.#",
				"#...#G#",
				"#######",
			]
		},
		{
			"name": "Tight",
			"limit": 5,
			"map": [
				"######",
				"#P#..#",
				"#.#..#",
				"#..#G#",
				"######",
			]
		},
		{
			"name": "Split",
			"limit": 7,
			"map": [
				"########",
				"#P.....#",
				"###.####",
				"#.....G#",
				"########",
			]
		},
	]

## Retourne le nombre total de niveaux
static func count() -> int:
	return all().size()

## Retourne un niveau par son index
static func get_level(index: int) -> Dictionary:
	var levels = all()
	if index >= 0 and index < levels.size():
		return levels[index]
	return {}

## Légende des caractères de la carte :
## # = Mur (impassable)
## . = Sol (passable)
## P = Position de départ du joueur
## G = Objectif (case à atteindre)
