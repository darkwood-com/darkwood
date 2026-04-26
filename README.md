# llm-game-of-life

Prototype Symfony 8 / PHP 8.5, CLI-first, qui reconstruit des briques conceptuelles d'un LLM dans un univers volontairement petit et visible: Game of Life, mini-langage lambda en JSON AST, algorithme genetique, tokenisation visuelle, preferences pairwise, benchmarks, logs NDJSON et visualisation SSE.

L'objectif n'est pas de produire un vrai LLM. C'est une demonstration pedagogique pour un article: "I rebuilt the conceptual building blocks of an LLM in PHP using Game of Life, lambda calculus, and genetic algorithms."

## What It Is Not

- Pas de reseau neuronal.
- Pas de backpropagation.
- Pas de pretraining massif.
- Pas de tokenizer BPE.
- Pas de RLHF/DPO reel.
- Pas de promesse de qualite ou de generalisation d'un LLM industriel.

Le projet transpose des idees: sequence, tokens, fitness, optimisation, preferences, streaming, reproductibilite.

## Transposition des concepts LLM

| Current LLM concept | Pedagogical version in the project |
| ------------------------------------ | -------------------------------------- |
| Token | Cell / color / lambda statement |
| Token sequence | Cell / frame sequence |
| Next token prediction | Next cell/frame prediction |
| Cross entropy | Supervised fitness |
| Perplexity | Visual perplexity |
| Pretraining | Pattern corpus generation |
| SFT | Target pattern optimization |
| RLHF | Animation preferences |
| DPO | Simplified pairwise reinforcement |
| Inference | Streamed animation generation |
| Systems | Streaming, logs, benchmarking |

Notes d'honnetete:

- `Cross entropy` est ici une distance supervisee entre frames, pas une vraie cross-entropy.
- `Visual perplexity` est une notion pedagogique documentable a partir des scores, pas une metrique NLP standard.
- `DPO` est une comparaison pairwise stockee en JSON, pas une optimisation de politique neuronale.

## Installation

Prerequis:

- PHP `>= 8.5`
- Composer

```bash
composer install
```

Validation:

```bash
php bin/phpunit
```


## Structure technique du projet
Le depot suit une organisation simple, proche DDD, avec une regle pratique: le coeur du modele reste dans `src/Domain`, les cas d'usage dans `src/Application`, les details Symfony/IO dans `src/Command`, `src/Controller` et `src/Infrastructure`.
```txt
src/
  Domain/
    CellularAutomaton/   Grille, cellules, frames, stepper Game of Life
    LambdaLanguage/      AST, validation, contexte d'execution, primitives
    Evolution/           Mutation, crossover, selection, population
    Tokenization/        Tokens visuels de frames et de programmes
  Application/

## Lambda AST et generation

Les programmes sont des AST JSON valides par `ProgramValidator`. Le noeud racine est une `sequence`.

Primitives disponibles:

- `birth`: rend une cellule vivante.
- `kill`: tue une cellule.
- `paint`: peint une cellule; dans l'automate actuel, toute couleur non noire rend la cellule vivante.
- `next`: avance d'une frame Game of Life.
- `mirror`: reflete la grille courante sur l'axe `horizontal` ou `vertical`.

Structures:

- `sequence`
- `repeat`
- `compose`

Exemple:

```bash
php bin/console app:lambda:run \
  --size=5 \
  --max-frames=5 \
  --ast='{"type":"sequence","nodes":[{"type":"birth","x":1,"y":1},{"type":"birth","x":1,"y":2},{"type":"mirror","axis":"vertical"},{"type":"next"}]}' \
  --output=var/runs/lambda-run.ndjson
```

Sortie: NDJSON de frames, affichable par le viewer web si le fichier est dans `var/runs/`.

## Visual Tokenization

Deux commandes exposent la tokenisation pedagogique:

```bash
php bin/console app:tokens:frame --input=frame.json
```

```bash
php bin/console app:tokens:program \
  --size=5 \
  --ast='{"type":"sequence","nodes":[{"type":"birth","x":1,"y":1},{"type":"next"}]}'
```

Tokens de frames:

- `FRAME_START`
- `ALIVE`
- `DEAD`
- `FRAME_END`
- `NEXT_FRAME` entre plusieurs frames

Tokens de programmes:

- `BIRTH_X_Y`
- `KILL_X_Y`
- `PAINT_X_Y_COLOR`
- `MIRROR_HORIZONTAL`
- `MIRROR_VERTICAL`
- `NEXT_FRAME`

## Entrainement non supervise

Le mode non supervise cherche des programmes qui produisent des animations visuellement interessantes sans cible explicite.

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

Fitness V2 combine:

- non-effondrement: penalise all-dead / all-alive,
- mouvement moyen entre frames,
- complexite de bords,
- symetrie,
- variation temporelle,
- duree de vie plafonnee.

La formule reste volontairement lisible et peu couteuse pour des grilles `16x16` ou `32x32`.

## Entrainement supervise

Le mode supervise optimise des programmes vers une animation cible. Les datasets sont de petits JSON lisibles dans `data/patterns/`.

Datasets disponibles:

- `glider`
- `blinker`
- `block`
- `beacon`

```bash
php bin/console app:train:supervised \
  --dataset=beacon \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/runs/supervised-beacon.ndjson
```

Sorties:

- stream NDJSON sur stdout,
- `var/runs/supervised-beacon.ndjson`,
- `var/runs/supervised-beacon.summary.json`.

Metrique actuelle: distance cellule par cellule entre frames generees et `target_frames`, avec une petite penalite de taille de programme. Les couleurs ne sont pas encore comparees comme couleurs; la grille serialisee est binaire.

## Preferences pairwise / DPO simplifie

`app:preferences:record` enregistre une preference entre deux programmes AST dans `data/preferences/`.

```bash
php bin/console app:preferences:record \
  --preferred-ast-file=preferred.json \
  --rejected-ast-file=rejected.json \
  --preferred-score=0.8 \
  --rejected-score=0.3 \
  --mode=supervised \
  --dataset=block \
  --seed=42 \
  --size=16
```

Le record JSON contient:

- `preferred_ast`,
- `rejected_ast`,
- `score_preferred`,
- `score_rejected`,
- `margin`,
- `objective`,
- `token_digest`.

La formule pedagogique est:

```txt
maximize(score_preferred - score_rejected)
```

Ce n'est pas du RLHF ou DPO reel: il n'y a pas de modele neuronal ni de policy update.

## Benchmarks reproductibles

Le benchmark execute deux runs avec les memes parametres et compare les hashes de sequence.

Supervise:

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

Non supervise:

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

Variante dataset:

```bash
php bin/console app:benchmark:run \
  --mode=supervised \
  --dataset=beacon \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/benchmarks/supervised_beacon.json
```

Champs JSON principaux:

- `schema_version`
- `case`
- `mode`
- `dataset` ou `size`
- `seed`
- `hyperparameters`
- `php_version`
- `duration_ms`
- `peak_memory_mb`
- `final_fitness`
- `final_average_fitness`
- `program_size`
- `sequence_hash`
- `reproducible`
- `reproducibility`
- `stability`
- `diversity`
- `visual_perplexity`

`reproducible` signifie: meme hash pour deux executions dans le meme processus avec les memes parametres. Ce n'est pas une garantie absolue entre versions de PHP ou machines differentes.

## Streaming Web

Routes:

- `GET /runs`
- `GET /runs/{id}/stream`

Le viewer lit les fichiers `var/runs/*.ndjson` et les rejoue en SSE avec Stimulus + canvas.

Affichage:

- canvas Game of Life,
- grille optionnelle,
- pause/play,
- reset,
- vitesse de lecture,
- generation,
- index de frame,
- fitness,
- fitness moyenne,
- seed,
- mode,
- dataset,
- programme lambda gagnant quand disponible.

Le web est volontairement secondaire: la demo reste CLI-first. Le viewer sert surtout aux captures de l'article.

## Logs Navi

Fichier:

```txt
var/log/life_llm_runs.ndjson
```

Evenements:

- `training_started`
- `generation_completed`
- `chunk_recorded`
- `training_completed`
- `training_failed`
- `benchmark_completed`
- `benchmark_failed`

Chaque ligne contient:

- `schema_version`
- `at`
- `run_id`
- `channel`
- `event`
- `payload`
- champs indexes utiles quand disponibles: `mode`, `dataset`, `seed`, `generation`, `fitness`, `program_size`, `sequence_hash`

Les chunks sont resumes par metriques et hashes pour eviter de dupliquer de grosses matrices ou AST dans les logs.

## Scenario complet pour l'article

1. Installer et valider:

```bash
composer install
php bin/phpunit
```

2. Executer un programme lambda simple:

```bash
php bin/console app:lambda:run \
  --size=5 \
  --max-frames=5 \
  --ast='{"type":"sequence","nodes":[{"type":"birth","x":1,"y":1},{"type":"birth","x":1,"y":2},{"type":"mirror","axis":"vertical"},{"type":"next"}]}' \
  --output=var/runs/article-lambda.ndjson
```

3. Tokeniser ce programme:

```bash
php bin/console app:tokens:program \
  --size=5 \
  --ast='{"type":"sequence","nodes":[{"type":"birth","x":1,"y":1},{"type":"birth","x":1,"y":2},{"type":"mirror","axis":"vertical"},{"type":"next"}]}'
```

4. Lancer un run supervise:

```bash
php bin/console app:train:supervised \
  --dataset=beacon \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/runs/article-supervised-beacon.ndjson
```

5. Lancer un run non supervise:

```bash
php bin/console app:train:unsupervised \
  --size=16 \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/runs/article-unsupervised.ndjson
```

6. Ouvrir `/runs`, choisir `article-supervised-beacon` ou `article-unsupervised`, puis capturer le canvas et les metadonnees.

7. Produire les benchmarks:

```bash
php bin/console app:benchmark:run \
  --mode=supervised \
  --dataset=beacon \
  --population=40 \
  --generations=25 \
  --seed=42 \
  --max-frames=30 \
  --output=var/benchmarks/supervised_beacon.json
```

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

8. Integrer uniquement les valeurs reelles produites dans `var/benchmarks/*.json`.

## Tests

```bash
php bin/phpunit
```

La suite doit rester rapide et couvrir les comportements principaux: CLI, lambda AST, tokenisation, datasets, fitness, preferences, benchmarks, Navi et normalisation SSE.
