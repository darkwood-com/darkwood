extends Node
## Scène principale du jeu
## Gère l'interface utilisateur et la communication avec le plateau de jeu

@onready var board = $Board
@onready var lbl_moves = $CanvasLayer/HUD/Moves
@onready var lbl_limit = $CanvasLayer/HUD/Limit
@onready var btn_undo = $CanvasLayer/HUD/Buttons/Undo
@onready var btn_reset = $CanvasLayer/HUD/Buttons/Reset
@onready var btn_next = $CanvasLayer/HUD/Buttons/Next
@onready var modal = $CanvasLayer/Modal

func _ready():
	# Connecter les boutons aux fonctions du plateau
	btn_undo.pressed.connect(board.undo)
	btn_reset.pressed.connect(board.reset)
	btn_next.pressed.connect(board.next_level)
	
	# Désactiver le bouton Next au début
	btn_next.disabled = true
	
	# Masquer le modal au début
	modal.visible = false

func _process(_dt):
	# Mettre à jour l'affichage des coups en temps réel
	lbl_moves.text = "Coups: %d" % board.get_moves()
	lbl_limit.text = "Limite: %d" % board.get_move_limit()

## Appelé quand le niveau est complété avec succès
func on_level_complete(moves: int, limit: int):
	btn_next.disabled = false
	_show_modal("Bravo ! %d/%d coups" % [moves, limit])

## Appelé quand le niveau échoue
func on_level_failed():
	_show_modal("Échec : limite dépassée")
	# Attendre un peu avant de reset automatiquement
	await get_tree().create_timer(0.6).timeout
	board.reset()

## Affiche un message modal
func _show_modal(text: String):
	modal.get_node("Label").text = text
	modal.visible = true
	
	# Masquer automatiquement après 2 secondes
	await get_tree().create_timer(2.0).timeout
	modal.visible = false
