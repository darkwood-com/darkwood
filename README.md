# Navi

Public architectural surface of a production system whose business core stays private.

This repository is not a sample app or a learning scaffold. It exists so the structural layer, how workflows are composed and executed, can be inspected, versioned, and evolved in the open while rules, scoring, integrations, and real workflow definitions remain on the private side.

The shape of the system:

- **Structured workflows** — execution proceeds as an ordered set of steps with an explicit `Context` and recorded `Event`s.
- **Composable execution** — each unit of work is an `Action`; the runner wires them without embedding domain meaning in the public names.
- **Maintainable boundaries** — Domain, Application, and Infrastructure stay separated so the private codebase can grow without collapsing this contract.

## Public contract

The public surface is expected to stay stable around:

- execution primitives such as `Context`, `Action`, `Event`, and `ExecutionState`
- the application-level runner that composes actions into a workflow
- a minimal framework entry point that exercises wiring without exposing private workflow definitions

## Private boundary

Business use cases, decision logic, matching or scoring, domain-specific naming beyond generic execution terms, connectors, and production workflow graphs remain private by design.

## Layout

```text
src/
  Domain/Execution/
    Action.php              # contract for one step
    ActionName.php          # value object for step identity
    ActionResult.php        # context + emitted events from a step
    Context.php             # immutable bag carried through the run
    ExecutionId.php         # value object for one execution identity
    Event.php               # named record from a step
    ExecutionState.php      # execution id + context + completed steps + event log
  Application/Workflow/
    WorkflowRunner.php      # executes actions in sequence
    WorkflowResult.php      # wraps final execution state
  Infrastructure/Action/
    MergeContextAction.php  # structural action: merge payload into context
  Command/
    InspectWorkflowCommand.php
docs/
  architecture.md
tests/
  Application/Workflow/WorkflowRunnerTest.php
```

## Run

```bash
composer install
composer test
composer inspect
```

The CLI command emits a structural execution snapshot. It is a wiring check for the public surface, not a stand-in for private workflows.

Deeper notes on the public boundary are in [docs/architecture.md](docs/architecture.md).
