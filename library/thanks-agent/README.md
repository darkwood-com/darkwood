# Thanks Agent

Symfony 8 + Darkwood Flow demo for the Darkwood Weekly article.

**Thesis:** systems that reward talk over production can be reproduced in a multi-agent runtime. Flow measures **value produced vs resources consumed**.

## Quick start

```bash
composer install
php bin/console thanks-agent:compare --both --save
php bin/console thanks-agent:bench-io --save
php bin/console thanks-agent:run --simulate-error --with-thanks
```

## Commands

| Command | Purpose |
|---|---|
| `thanks-agent:compare` | 5 useful agents vs 5+ThanksAgent scoreboard |
| `thanks-agent:bench-io` | sequential vs `stream_select` vs PHP 8.6 Poll vs Flow |
| `thanks-agent:run` | single pipeline run (`--simulate-error` meters failure path) |

## Agents

| Agent | Value | Role |
|---|---|---|
| FetchDocumentationAgent | +25 | Load source excerpts |
| GithubAgent | +40 | PR / git metadata |
| PHPAgent | +30 | PHP 8.6 capability probe |
| MozillaAgent | +20 | Open-source AI principles |
| CostAgent | +15 | Outcome-cost framing |
| SummaryAgent | +15 | Fan-in synthesis |
| **ThanksAgent** | **+0** | Always `"Thanks."` / `"Looks good."` |

ThanksAgent still consumes scheduling, context, logging, observability, and WIP slots.

## Architecture

- Scatter: one Flow `Ip` per agent, `MaxIpStrategy` concurrency
- Drivers: `FiberDriver` (default) or `StreamSelectDriver`
- Fan-in: `RunStore` + `SummaryAgent`
- Instrumentation: `App\Instrumentation\*` + demo-local `App\Flow\*` (outcome ledger, budget, pollers, terminal events)
- JSON reports: `var/reports/`

Uses published `darkwood/flow` (`^8.1`). Demo instrumentation stays in this repo under `App\Flow\*`.

## Article outlines

See [`docs/articles/`](docs/articles/).
