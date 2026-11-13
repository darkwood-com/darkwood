# Darkwaar2 - HearthStone-like Card Game

A Godot 4.5 implementation of a HearthStone-like card game, transcribed from the original Phaser.js version.

## Project Structure

### Scenes
- **Main.tscn** - Main entry point
- **StartScene.tscn** - Welcome screen with start button
- **CardChoiceScene.tscn** - Card deck selection screen
- **GameScene.tscn** - Main game board
- **ResultScene.tscn** - Game result screen (win/loss)

### Scripts
- **DataManager.gd** - Global game state management
- **CardConfig.gd** - Card definitions and game configuration
- **UIManager.gd** - UI component management
- **HeroHandCard.gd** - Player's hand card management
- **EnemyHandCard.gd** - Enemy's hand card management
- **HeroFighters.gd** - Player's battlefield minions
- **EnemyFighters.gd** - Enemy's battlefield minions
- **AI.gd** - Enemy AI logic

## Game Features

### Card Types
- **Entourage (随从)** - Minions that can be summoned to the battlefield
- **Magic (魔法)** - Spell cards with special effects

### Available Cards
1. **鱼人宝宝** (Fishman Baby) - 1 mana, 1/1
2. **淡水鳄** (Freshwater Crocodile) - 2 mana, 2/3
3. **食人魔法师** (Ogre) - 4 mana, 4/4
4. **死亡之翼** (Deathwing) - 9 mana, 9/9
5. **拉格纳罗斯** (Ragnaros) - 8 mana, 8/8
6. **超级迅猛龙** (Super Raptor) - 4 mana, 4/5
7. **奥术智慧** (Arcane Wisdom) - 3 mana magic card

### Game Flow
1. **Start Screen** - Welcome message and start button
2. **Card Selection** - Choose cards for your deck
3. **Main Game** - Turn-based card battle
4. **Result Screen** - Win/loss display

## How to Play

1. Click "开始游戏" (Start Game) on the welcome screen
2. Select cards for your deck by clicking on available cards
3. Click "确认" (Confirm) to proceed to the game
4. In the main game:
   - Click on cards in your hand to select them
   - Click "出牌" (Play Card) to play the selected card
   - Click "结束回合" (End Turn) to end your turn
   - The enemy AI will automatically play cards and attack

## Technical Details

### Phaser to Godot Mapping
- **Phaser Scenes** → **Godot Scenes** (.tscn files)
- **Phaser States** → **Godot Scene Management**
- **Phaser Game Objects** → **Godot Nodes**
- **Phaser Input Events** → **Godot Signal System**
- **Phaser Tweens** → **Godot Tween Nodes**
- **JavaScript Classes** → **GDScript Classes**

### Key Differences
- **Scene Management**: Godot uses scene files instead of Phaser's state system
- **UI System**: Godot's Control nodes instead of Phaser's display objects
- **Input Handling**: Godot's signal system instead of Phaser's event listeners
- **Asset Management**: Godot's resource system instead of Phaser's loader

## Running the Game

1. Open the project in Godot 4.5
2. Set the main scene to `res://scenes/Main.tscn`
3. Run the project (F5)

## Future Enhancements

- Add card artwork and animations
- Implement more complex AI strategies
- Add sound effects and music
- Implement hero abilities
- Add more card types and effects
- Improve UI/UX design
- Add multiplayer support

