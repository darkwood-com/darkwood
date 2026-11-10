# Omer Quotes Orchestration MVP

Minimal comparative MVP between:
- Message-oriented orchestration (`Symfony Messenger`)
- Data-oriented orchestration (`darkwood/navi` flow semantics)

## Commands

- `php bin/console app:repos:ingest`
- `php bin/console app:corpus:preprocess`
- `php bin/console app:features:build`
- `php bin/console app:similarity:top <repoId> [top]`
- `php bin/console app:classification:run`
- `php bin/console app:clustering:run`
- `php bin/console app:pipeline:messenger:run`
- `php bin/console app:pipeline:flow:run`
- `php bin/console app:pipeline:compare`

## Storage Artifacts

Generated under `var/app/`:
- `ingested/`
- `documents/`
- `corpus/`
- `vectors/`
- `classification/`
- `clustering/`
- `reports/`

## Test

- `php bin/phpunit`

## Design Notes

Business logic is centralized in `PipelineService` and supporting services.
Messenger and Flow are orchestration surfaces only.
See `docs/architecture-map.md`, `docs/adr/0001-orchestration-choice.md`, and `docs/comparison/orchestration-comparison.md`.
