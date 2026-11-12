# Sokoban Mobile Game

A mobile-first Sokoban puzzle game built with Godot 4.3.

## Features

- **Mobile-First Design**: Optimized for touch controls and mobile screens
- **Simple Controls**: Touch the screen to move your character
- **Multiple Levels**: 5 progressively challenging levels
- **Clean UI**: Modern, minimalist interface
- **Responsive**: Works on various screen sizes

## How to Play

1. **Objective**: Push all boxes onto the red target squares
2. **Movement**: Touch the screen to move your character
   - Touch left side of screen: Move left
   - Touch right side of screen: Move right
   - Touch top of screen: Move up
   - Touch bottom of screen: Move down
3. **Pushing Boxes**: Walk into boxes to push them
4. **Winning**: Complete all levels to win the game

## Controls

- **Touch Controls**: Tap different areas of the screen to move
- **Keyboard Controls** (for testing):
  - WASD or Arrow Keys: Move
  - R: Restart level

## Project Structure

```
sokoban/
├── scenes/
│   ├── MainMenu.tscn      # Main menu scene
│   └── Main.tscn          # Game scene
├── scripts/
│   ├── GameManager.gd     # Game state management
│   ├── GameScene.gd       # Main game logic
│   ├── Player.gd          # Player movement and interaction
│   ├── MobileUI.gd        # Mobile UI and touch controls
│   └── MainMenu.gd        # Main menu logic
├── levels/
│   ├── level_1.txt        # Level data files
│   ├── level_2.txt
│   └── level_3.txt
└── project.godot          # Godot project configuration
```

## Running the Game

1. Open the project in Godot 4.3+
2. Run the main scene (MainMenu.tscn)
3. The game will start with the main menu
4. Tap "Start Game" to begin playing

## Mobile Optimization

- **Viewport Size**: 1080x1920 (mobile portrait)
- **Touch Controls**: Large touch areas for easy interaction
- **UI Scaling**: Responsive UI that adapts to screen size
- **Performance**: Optimized for mobile devices

## Level Format

Levels are defined using simple text files with the following characters:
- `#`: Wall
- `@`: Player starting position
- `$`: Box
- `.`: Target (where boxes need to go)
- `*`: Box already on target
- ` `: Empty space

## Future Enhancements

- More levels
- Sound effects and music
- Particle effects
- Save/load progress
- Level editor
- Multiplayer support
