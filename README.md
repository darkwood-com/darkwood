# Navi

Navi is the public architectural layer of a larger private system.

This repository exposes the structural core used to run composed workflows through explicit boundaries between Domain, Application, and Infrastructure. It contains a minimal executable flow so the public surface stays concrete, while business rules, workflow definitions, decision logic, and production integrations remain private.

The public code focuses on:

- structured workflow execution
- composable actions over an immutable context
- maintainable boundaries that can evolve with the private system

What is intentionally absent:

- business-specific use cases
- scoring or matching logic
- connector implementations
- real production workflows

## Structure

```text
src/
  Domain/
  Application/
  Infrastructure/
  Command/
tests/
```

## Minimal flow

1. A `Context` enters the system.
2. `WorkflowRunner` executes a sequence of `Action` instances.
3. The workflow returns a `WorkflowResult` with the final `ExecutionState`.

## Run

```bash
php bin/console navi:workflow:run
php tests/WorkflowRunnerTest.php
```
