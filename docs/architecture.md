# Architecture

Navi publishes the execution surface of a larger system, not its business behavior.

## Public contract

The stable public contract is intentionally small:

- `Context`, `Action`, `ActionResult`, `Event`, `ExecutionId`, and `ExecutionState` define the execution vocabulary
- `WorkflowRunner` coordinates ordered action execution without embedding private decision logic
- the Symfony command exposes a minimal runtime path to verify integration and wiring

This contract is structural. It describes how execution is shaped, not why a specific workflow exists.

## Private boundary

The following stay private:

- workflow definitions with business meaning
- decision and scoring rules
- integrations and connectors
- production naming and domain language beyond the generic execution vocabulary

## Minimal execution slice

The published runtime path is deliberately narrow:

1. Create a `Context`
2. Start a workflow execution
3. Apply a small sequence of `Action`s
4. Return a final `ExecutionState`

That is enough to make the public surface executable and verifiable without exposing real behavior.
