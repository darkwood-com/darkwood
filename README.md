# llm-game-of-life

Prototype Symfony 8 (CLI-first) d'un "LLM exoterique" base sur:

- un mini langage lambda (AST JSON),
- un automate cellulaire type Game of Life,
- un algorithme genetique,
- du streaming NDJSON/SSE de frames.

Le projet est pedagogique: il simule une generation sequentielle visible, sans pretendre etre un vrai LLM industriel.

## Prerequis

- PHP >= 8.5
- Composer

## Installation

```bash
composer install
```

## Commandes principales (MVP)

### 1) Executer un programme lambda AST

```bash
php bin/console app:lambda:run \
  --size=5 \
  --max-frames=5 \
  --ast='{"type":"sequence","nodes":[{"type":"birth","x":1,"y":1},{"type":"birth","x":1,"y":2},{"type":"birth","x":2,"y":1},{"type":"birth","x":2,"y":2},{"type":"next"}]}' \
  --output=var/runs/lambda-run.ndjson
```

### 2) Entrainement non supervise

```bash
php bin/console app:train:unsupervised \
  --size=16 \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/runs/unsupervised.ndjson
```

Sorties:

- stream NDJSON sur stdout,
- `var/runs/unsupervised.ndjson`,
- `var/runs/unsupervised.summary.json`.

Fitness V2: score pedagogique leger combinant non-effondrement, mouvement moyen, complexite de bords, symetrie, variation temporelle et duree de vie. Les grilles entierement mortes ou entierement vivantes sont ramenees a `0`.

### 3) Entrainement supervise

Datasets disponibles: `glider`, `blinker`, `block`, `beacon`.

```bash
php bin/console app:train:supervised \
  --dataset=block \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/runs/supervised-block.ndjson
```

Sorties:

- stream NDJSON sur stdout,
- `var/runs/supervised-block.ndjson`,
- `var/runs/supervised-block.summary.json`.

### 4) Preferences pairwise / DPO simplifie

`app:preferences:record` enregistre une comparaison entre deux programmes AST dans `data/preferences/`.
C'est une analogie educative de preference pairwise: le projet stocke `score_preferred - score_rejected`, mais ne fait pas de RLHF/DPO reel.

```bash
php bin/console app:preferences:record \
  --preferred-ast-file=preferred.json \
  --rejected-ast-file=rejected.json \
  --preferred-score=0.8 \
  --rejected-score=0.3 \
  --mode=supervised \
  --dataset=block \
  --seed=42
```

### 5) Benchmark reproductible

```bash
php bin/console app:benchmark:run \
  --mode=supervised \
  --dataset=block \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/benchmarks/supervised_block.json
```

Ou en non supervise:

```bash
php bin/console app:benchmark:run \
  --mode=unsupervised \
  --size=16 \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/benchmarks/unsupervised_16.json
```

Le benchmark produit notamment:

- `duration_ms`,
- `peak_memory_mb`,
- `final_fitness`,
- `sequence_hash`,
- `reproducible` (double run, meme seed).

## Streaming Web (SSE + canvas)

Routes:

- `GET /runs` : interface web Twig + Stimulus
- `GET /runs/{id}/stream` : endpoint SSE

Le frontend consomme les chunks SSE et affiche:

- la matrice dans un canvas,
- `fitness`,
- `generation`,
- `seed`.

## Logs Navi (NDJSON)

Fichier:

- `var/log/life_llm_runs.ndjson`

Evenements journalises:

- run/training start,
- generation,
- chunk,
- training completed/failed,
- benchmark.

## Tests

```bash
php bin/phpunit
```

## Notes de scope

Ce prototype:

- n'est pas un vrai LLM,
- n'utilise pas de pretraining massif,
- n'implemente pas de modeles de deep learning,
- vise surtout une analogie pedagogique et reproductible.
