# Darkwaar1 - MVP Puzzle Mobile

Un puzzle minimaliste développé avec Godot 4.x, optimisé pour mobile en mode portrait.

## 🎮 Description du jeu

**Darkwaar1** est un puzzle simple où l'objectif est d'atteindre la case objectif en un nombre limité de coups. Le joueur se déplace par des gestes de swipe sur l'écran tactile.

### Mécaniques
- **Déplacement** : Swipe haut/bas/gauche/droite pour se déplacer d'une case
- **Objectif** : Atteindre la case rouge (objectif) 
- **Limite** : Chaque niveau a une limite de coups maximum
- **Contrôles** : Undo, Reset, Next Level

### Niveaux
Le jeu contient 5 niveaux prédéfinis avec des difficultés progressives :
1. **Init** - 4 coups max
2. **Corner** - 3 coups max  
3. **Weave** - 6 coups max
4. **Tight** - 5 coups max
5. **Split** - 7 coups max

## 🛠️ Structure du projet

```
res://
  scenes/
	Main.tscn          # Scène principale avec HUD
	Board.tscn         # Plateau de jeu avec TileMap
  scripts/
	Main.gd            # Gestion de l'interface
    Board.gd           # Logique du jeu
    Swipe.gd           # Détection des gestes tactiles
    Levels.gd          # Définition des niveaux
  assets/
    tileset.png        # Sprites des tuiles (sol/mur/objectif)
	icon_app.png       # Icône de l'application
  project.godot        # Configuration du projet
```

## 🚀 Installation et lancement

### Prérequis
- Godot 4.3 ou supérieur
- Android SDK (pour l'export mobile)

### Lancement dans l'éditeur
1. Ouvrir le projet dans Godot 4.x
2. Charger la scène `scenes/Main.tscn`
3. Appuyer sur F5 pour lancer le jeu

### Test mobile
- Le jeu est configuré pour fonctionner en mode portrait (1080x1920)
- Les contrôles tactiles sont optimisés pour mobile
- Testez les swipes sur l'écran pour déplacer le joueur

## 📱 Export Android

### Configuration de l'export
1. Aller dans **Project > Export**
2. Sélectionner **Android**
3. Configurer les paramètres :
   - **Package Name** : `com.darkwood.darkwaar1`
   - **Version Name** : `0.1.0`
   - **Version Code** : `1`
   - **Orientation** : Portrait uniquement

### Création du keystore
```bash
keytool -genkey -v -keystore darkwaar1.keystore -alias darkwaar1 -keyalg RSA -keysize 2048 -validity 10000
```

### Export APK
1. Configurer le keystore dans les paramètres d'export
2. Cliquer sur **Export Project**
3. Choisir le nom : `darkwaar1-v0.1.0.apk`

## 🎯 Fonctionnalités implémentées

### ✅ MVP Complet
- [x] 5 niveaux scriptés avec cartes ASCII
- [x] Contrôles tactiles (swipe)
- [x] Système de coups avec limite
- [x] Boutons Undo/Reset/Next
- [x] Interface HUD minimale
- [x] Détection victoire/échec
- [x] Export Android configuré
- [x] Optimisations mobile (60 FPS)

### 🎨 Assets
- **Tileset** : 3 tuiles (sol gris, mur foncé, objectif rouge #E41544)
- **Icône** : Design minimaliste avec carré central et bordure rouge
- **UI** : Interface épurée adaptée au mobile

## 🔧 Personnalisation

### Ajouter un niveau
Modifier `scripts/Levels.gd` et ajouter un nouvel objet dans le tableau :

```gdscript
{
    "name": "MonNiveau",
    "limit": 5,
    "map": [
        "######",
        "#P...#",
        "#.##.#", 
        "#...G#",
        "######",
    ]
}
```

### Modifier les couleurs
Les couleurs sont définies dans les assets PNG. Utilisez un éditeur d'image pour modifier :
- `assets/tileset.png` : Couleurs des tuiles
- `assets/icon_app.png` : Icône de l'application

## 🐛 Dépannage

### Problèmes courants
- **Swipes non détectés** : Vérifier que le contrôle Swipe couvre tout l'écran
- **Performance** : Le jeu est optimisé pour 60 FPS sur mobile
- **Export Android** : S'assurer que le keystore est correctement configuré

### Logs de debug
Les messages de debug sont affichés dans la console Godot pour diagnostiquer les problèmes.

## 📄 Licence

Projet développé pour Darkwood - Version MVP 0.1.0

---

**Build Status** : ✅ Prêt pour export Android  
**Version** : 0.1.0  
**Plateforme** : Android (Portrait uniquement)
