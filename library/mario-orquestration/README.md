# Mario Multi-Agent Demo (Symfony AI)

## But de la démo

Cette démo montre comment orchestrer plusieurs agents spécialisés pour produire un niveau de platformer 2D "mario-like" (sans cloner officiellement Mario, sans assets Nintendo).

Plutôt que de demander "fais tout le jeu", on découpe le raisonnement:

- orchestrator: route la demande vers le bon spécialiste
- layout: structure terrain/progression
- gameplay: objets et mécaniques
- godot_export: JSON exploitable côté Godot
- qa: validation jouabilité / cohérence / sécurité copyright
- fallback: réponse de secours hors spécialité

## Commande de démo

```bash
php bin/console app:mario:orchestrate
```

Options utiles:

```bash
php bin/console app:mario:orchestrate --prompt="Generate a Mario-like platformer level"
php bin/console app:mario:orchestrate --output=var/mario-orchestration/level.json
php bin/console app:mario:orchestrate --dry-run
php bin/console app:mario:orchestrate --fake
```

## Mode fake (déterministe)

Le mode fake évite tout appel API et génère toujours une sortie stable:

- activé explicitement via `--fake`
- activé automatiquement si `OPENAI_API_KEY` est absent

Exemple recommandé pour la vidéo:

```bash
php bin/console app:mario:orchestrate --fake
```

Résultat attendu:

- orchestration affichée en console
- agent sélectionné affiché
- JSON exporté dans `var/mario-orchestration/level.json`

## Mode OpenAI réel

Configurer la clé:

```bash
export OPENAI_API_KEY="sk-..."
```

Puis lancer:

```bash
php bin/console app:mario:orchestrate
```

La factory utilise ce pattern Symfony AI:

- `EventDispatcher`
- `PlatformSubscriber`
- `Factory::createPlatform(...)`
- `Agent`
- `Handoff`
- `MultiAgent`
- `MessageBag`
- `Message::ofUser()`

## Différence avec le vibe coding classique

Approche "un seul prompt": rapide, mais souvent opaque et difficile a contrôler.

Approche orchestration multi-agents:

- responsabilités explicites par agent
- routage observable (qui a traité quoi)
- validation dédiée QA
- meilleure reproductibilité (mode fake)
- plus simple à faire évoluer en production

## Pourquoi cette approche donne plus de contrôle

- prompts spécialisés plus faciles à maintenir
- diagnostics plus clairs (agent sélectionné, résultat, validation)
- possibilité de brancher de nouveaux agents sans réécrire toute la chaîne
- séparation nette entre génération, export et vérification
