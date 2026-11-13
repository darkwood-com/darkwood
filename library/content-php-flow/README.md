# PHP Flow — Profile Demo

Two-layer ETL demo inspired by [flow-php/etl](https://github.com/flow-php/flow) (`Flow\ETL\Extractor`, `Transformer`, `Loader`, `Pipeline`).

## Architecture

**Layer 1 — ETL abstraction** (`App\Etl\FlowInterface`):

- `Extractor` → `Transformer` → `Loader`
- `Pipeline::process()` chains stages like flow-php's internal pipeline
- `OrderEtlFlow` configures the ETL flow for order batches

**Layer 2 — Orchestration** (`darkwood/flow`):

- `PrepareEtlExecutionJob` → `RunEtlExtractJob` → `RunEtlTransformJob` → `RunEtlLoadJob`
- Each darkwood/flow job invokes one ETL stage — visible as separate Blackfire frames

## Run

```bash
bin/console app:flow:profile-demo --limit=1 -vv
bin/console app:flow:profile-demo -vv
```

## Data

- Input: `data/orders.csv` (100 rows, 10 batches via `InMemoryQueue`)
- Output: `var/output/orders-batch-*.json`

## Profiling with Blackfire

```bash
composer require --dev blackfire/php-sdk
```

`.env.local`:

```
BLACKFIRE_CLIENT_ID=…
BLACKFIRE_CLIENT_TOKEN=…
```

```bash
bin/console app:flow:profile-demo -vv
kill -USR2 <pid>   # start
kill -USR2 <pid>   # stop → profile URL in logs
```

### What to look for in Blackfire

- **Layer 2**: `RunEtlTransformJob` orchestration frame
- **Layer 1**: `NormalizeOrderTransformer::transform` CPU hotspot (SHA-256 ×1000)
- **Layer 1**: `OrderBatchExtractor::extract`, `JsonOrderLoader::load` (I/O + `json_encode`)
