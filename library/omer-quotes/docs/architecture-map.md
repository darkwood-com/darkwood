# Architecture Inventory

- **Domain**: repository document, feature, similarity, classification, clustering, processing report.
- **Application**: shared `PipelineService` and small focused NLP/math services.
- **Infrastructure**:
  - `JsonRepositoryCatalog` (`data/repos.json` source of truth)
  - `GitRepositoryContentReader` (provider-neutral content acquisition)
  - `JsonFileStorage` (`var/app` inspectable artifacts)
  - Orchestration adapters:
    - Messenger handler/message
    - Navi flow actions + workflow runner
- **UI**: Symfony console commands only.

## Dependency Delta

- Added `symfony/messenger`
- Added `symfony/process`
- Added `phpunit/phpunit` (dev)

## Risks / Assumptions

- Some repositories may fail clone/auth; ingestion continues and stores partial states.
- Readme/document extraction is intentionally lightweight for MVP comparability.
- Clustering is minimal deterministic assignment in iteration 1.
