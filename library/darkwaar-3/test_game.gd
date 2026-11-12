extends Node

# Simple test script to verify the game works
func _ready():
	print("Sokoban Mobile Game Test")
	print("========================")
	print("Game Manager loaded: ", GameManager != null)
	print("Total levels: ", GameManager.total_levels)
	print("Current level: ", GameManager.current_level)
	
	var level = GameManager.get_current_level()
	if level:
		print("Level 1 data:")
		for line in level:
			print(line)
	
	print("\nGame is ready to play!")
	print("Controls:")
	print("- Touch screen to move")
	print("- Push boxes onto red targets")
	print("- Complete all levels to win")
