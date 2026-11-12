extends Control
## Détecteur de gestes de swipe pour mobile
## Émet un signal 'swiped' avec la direction du swipe

signal swiped(dir: Vector2)

var start_pos := Vector2.ZERO
const MIN_DRAG := 24.0  # Distance minimale pour considérer un swipe

func _ready():
	# S'assurer que le contrôle couvre tout l'écran
	set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)

func _gui_input(event):
	if event is InputEventScreenTouch and event.pressed:
		# Début du touch
		start_pos = event.position
	elif event is InputEventScreenDrag:
		# Pendant le drag, vérifier si c'est assez long pour être un swipe
		var delta: Vector2 = event.position - start_pos
		if delta.length() > MIN_DRAG:
			# Déterminer la direction principale du swipe
			var dir := Vector2.ZERO
			if abs(delta.x) > abs(delta.y):
				# Mouvement horizontal
				dir = Vector2(sign(delta.x), 0)
			else:
				# Mouvement vertical
				dir = Vector2(0, sign(delta.y))
			
			# Émettre le signal avec la direction
			emit_signal("swiped", dir)
			# Mettre à jour la position de départ pour éviter les swipes multiples
			start_pos = event.position
	elif event is InputEventScreenTouch and not event.pressed:
		# Fin du touch, réinitialiser
		start_pos = Vector2.ZERO
