# Orchestration Comparison

## Workflow Mapping

Both orchestrations execute:
1) ingest
2) preprocess
3) feature build
4) classification
5) clustering

## Sequence Snapshot

- **Messenger**: command -> message bus -> `RunMessengerPipelineHandler` -> shared pipeline stages.
- **Navi Flow**: command -> `WorkflowRunner` -> ordered `PipelineStageAction` instances -> shared pipeline stages.

## Concepts Introduced by Branch

- **Messenger only**: message class, message handler, bus dispatch concerns.
- **Navi only**: action wrappers, `Context` progression, workflow action list.

## Operational Notes

- Messenger debugging naturally aligns with message envelopes and middleware.
- Navi flow debugging aligns with ordered action names and immutable context snapshots.

## Evidence / Measurement

Use:
- `php bin/console app:pipeline:messenger:run`
- `php bin/console app:pipeline:flow:run`
- `php bin/console app:pipeline:compare`

Comparison artifact is written to `var/app/reports/comparison_latest.json`.
