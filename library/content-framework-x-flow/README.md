# Framework X + Darkwood Flow

Experimental demo: **Framework X** as the asynchronous HTTP runtime, **darkwood/flow** as the workflow orchestration layer.

Thesis explored: the agent is not necessarily the bottleneck — the laptop, the implicit human checklist, and the absence of shared orchestration are. This project turns a private engineering checklist into an explicit, observable, reproducible workflow exposed over HTTP.

It does **not** claim Framework X is a cloud agent platform.

## Requirements

- PHP **8.5+** (required by `darkwood/flow` ^8.1)
- Composer

## Installation

```bash
cd library/content-framework-x-flow
composer install
```

Packages used:

- `clue/framework-x` ^0.17
- `darkwood/flow` ^8.1
- `react/async` ^4.3

## Run the server

```bash
php public/index.php
```

Listens on `http://127.0.0.1:8080` by default (`X_LISTEN` overrides).

## Curl examples

```bash
curl -i http://127.0.0.1:8080/health

curl -i http://127.0.0.1:8080/workflows/change-review

curl -i \
  -X POST \
  -H 'Content-Type: application/json' \
  --data '{
    "changeId": "CHANGE-42",
    "title": "Add workflow execution endpoint",
    "files": ["src/Http/RunWorkflowHandler.php"],
    "risk": "medium"
  }' \
  http://127.0.0.1:8080/workflows/change-review/runs

# Replace RUN_ID from the previous response
curl -i http://127.0.0.1:8080/workflows/change-review/runs/RUN_ID

curl -i http://127.0.0.1:8080/analysis/framework-x-flow

curl -i http://127.0.0.1:8080/experiments/sequential-vs-concurrent
```

Controlled failures (POST body flags):

| `fail` value | Behaviour |
| --- | --- |
| `exception` | Synchronous throw in validate |
| `promise` | Throw inside concurrent quality job |
| `async` | Async failure during tests check |
| `security` | Soft-fail security check; continues to retrospective |

```bash
curl -s -X POST -H 'Content-Type: application/json' \
  --data '{"changeId":"C1","title":"t","files":["a.php"],"risk":"high","fail":"security"}' \
  http://127.0.0.1:8080/workflows/change-review/runs
```

## Async experiment (CLI, preferred)

```bash
php bin/experiment
```

Compares `MaxIpStrategy(1)` vs `MaxIpStrategy(3)` with native `ReactDriver::await()` (script owns the event loop). See [docs/async-experiment.md](docs/async-experiment.md).

## How Framework X and Flow interact

```text
HTTP request
  → Framework X routing + JsonErrorHandler
  → RunWorkflowHandler (returns Promise)
  → ChangeReviewWorkflow (FlowFactory job chain)
  → EmbeddedReactDriver (adapter: no Loop::stop())
  → WorkflowRunStore (atomic JSON under var/runs/)
  → JSON run report
```

| Layer | Responsibility |
| --- | --- |
| Framework X | HTTP runtime, routes, middleware, Promise/Fiber request handling |
| darkwood/flow | Ordered jobs, Ip data pipeline, drivers, MaxIpStrategy |
| This demo | Embedded loop adapter, run persistence, retrospective, JSON errors |

## Project structure

```text
public/index.php          # Framework X entrypoint
bin/experiment            # CLI MaxIpStrategy timing experiment
src/AppFactory.php
src/Http/                 # Route handlers + JsonErrorHandler
src/Workflow/             # Change-review flow, context, store, steps
src/Flow/                 # EmbeddedReactDriver / Runner (integration adapter)
src/Experiment/           # Sequential vs concurrent Ip experiment
var/runs/                 # Persisted run JSON
var/log/                  # Local error log (not returned to clients)
docs/                     # Source-based analysis + article notes
```

## Known limitations

- `EmbeddedReactDriver` is a **local adapter**, not upstream Flow API. Native `ReactDriver::await()` calls `Loop::stop()` and must not run inside Framework X’s HTTP server.
- Flow concurrency is **Ip-level** (`MaxIpStrategy`). Intra-run parallel quality checks use a ReactPHP `Promise::all` adapter inside one job.
- No secure cloud sandbox, credential broker, multi-tenant isolation, or durable queue.
- Framework X is `0.x`; APIs may change.
- Production credentials / agent tooling are **out of scope** (see article notes).

## Tests

```bash
vendor/bin/phpunit
```
