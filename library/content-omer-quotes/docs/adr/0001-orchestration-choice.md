# ADR 0001 - Messenger vs Navi Flow

## Status
Accepted for MVP comparison.

## Context
The project must compare two orchestration styles while keeping one shared business core.

## Decision
- Keep all NLP/math and repository processing in shared application services.
- Implement Messenger orchestration with explicit message handler.
- Implement data-oriented orchestration with `Navi\Application\Workflow\WorkflowRunner` and ordered actions over `Context`.
- Persist outputs and run reports in the same storage format.

## Consequences
- Comparability is preserved.
- Branch-specific overhead is isolated in orchestration adapters.
- MVP remains deterministic and inspectable.
