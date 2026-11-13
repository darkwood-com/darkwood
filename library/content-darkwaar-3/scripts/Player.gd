extends CharacterBody2D

@export var speed = 200.0
@export var push_force = 300.0

# GameManager is now an autoload singleton
var boxes: Array = []
var walls: Array = []
var targets: Array = []

func _ready():
	# Connect to the game scene to get references
	if get_parent().has_method("get_game_objects"):
		var objects = get_parent().get_game_objects()
		boxes = objects.boxes
		walls = objects.walls
		targets = objects.targets

func _physics_process(delta):
	handle_input()
	move_and_slide()

func handle_input():
	var input_vector = Vector2.ZERO
	
	# Keyboard input
	if Input.is_action_pressed("move_right"):
		input_vector.x += 1
	if Input.is_action_pressed("move_left"):
		input_vector.x -= 1
	if Input.is_action_pressed("move_up"):
		input_vector.y -= 1
	if Input.is_action_pressed("move_down"):
		input_vector.y += 1
	
	# Touch input (handled by UI)
	
	if input_vector != Vector2.ZERO:
		try_move(input_vector)

func try_move(direction: Vector2):
	var new_position = global_position + direction * 32
	
	# Check for walls
	for wall in walls:
		if wall.global_position.distance_to(new_position) < 32:
			return
	
	# Check for boxes
	for box in boxes:
		if box.global_position.distance_to(new_position) < 32:
			# Try to push the box
			var box_new_position = box.global_position + direction * 32
			
			# Check if box can be pushed (no wall or other box in the way)
			var can_push = true
			
			# Check walls
			for wall in walls:
				if wall.global_position.distance_to(box_new_position) < 32:
					can_push = false
					break
			
			# Check other boxes
			if can_push:
				for other_box in boxes:
					if other_box != box and other_box.global_position.distance_to(box_new_position) < 32:
						can_push = false
						break
			
			if can_push:
				# Move the box
				box.global_position = box_new_position
				# Move the player
				global_position = new_position
				
				# Check if level is complete
				if GameManager.is_level_complete(boxes, targets):
					GameManager.level_completed.emit()
			return
	
	# No obstacle, just move
	global_position = new_position

func move_direction(direction: Vector2):
	try_move(direction)
