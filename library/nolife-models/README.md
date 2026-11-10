# NoLife Models

Local explorer and comparator for AI models: merged **catalog** from the live **[models.dev `api.json`](https://models.dev/api.json)** (cached), optional local overrides in [`data/catalog.json`](data/catalog.json), **Ollama** inventory and `/api/generate` runs, lightweight **benchmarks**, and **JSON / CSV / Markdown** exports. Built for **Symfony 8.1**, **PHP 8.5+**, **DDD / hexagonal** layering under `src/NoLifeModels/`.

## Requirements

- PHP 8.5 or newer
- [Composer](https://getcomposer.org/)
- [Ollama](https://ollama.com/) running locally when you use inventory or comparisons

## Install

```bash
composer install
```

Copy environment defaults and adjust secrets:

```bash
cp .env .env.local   # optional
```

Set `APP_SECRET` to a non-empty random string in `.env.local` or through the
runtime environment for non-development environments. Keep `.env` limited to
public defaults.

### Ollama

| Variable | Default | Purpose |
|----------|---------|---------|
| `OLLAMA_BASE_URL` | `http://127.0.0.1:11434` | Base URL for Ollama HTTP API |
| `OLLAMA_TIMEOUT` | `120` | Request timeout (seconds) |
| `OLLAMA_VERIFY_PEER` | `1` | TLS verify (`0` to disable for dev-only custom certs) |

### models.dev catalog API

| Variable | Default | Purpose |
|----------|---------|---------|
| `MODELS_DEV_API_URL` | `https://models.dev/api.json` | JSON document (nested providers → models) |
| `MODELS_DEV_ENABLED` | `1` | Set to `0` to skip HTTP fetch (offline / tests) |
| `MODELS_DEV_CACHE_TTL` | `3600` | `cache.app` TTL for the **parsed** catalog (JSON rows in cache; first fill may take ~1–2 minutes: HTTP + decode + map) |

## Run (development)

```bash
symfony server:start
# or
php -S 127.0.0.1:8000 -t public
```

Open `http://127.0.0.1:8000` — you are redirected to `/en/dashboard`. Use **EN | FR** in the header for locale.

Asset pipeline uses **AssetMapper** + **Stimulus**; **Turbo** is enabled via UX Turbo (Stimulus controller). Live catalog filters use **Symfony UX LiveComponent**.

## Manual test checklist (MVP)

1. **Ollama** page (`/{locale}/ollama`) lists tags from `GET /api/tags`.
2. **Compare** (`/{locale}/compare`): two different model tags, a prompt → **Results** shows side-by-side text and metrics (`wall`, `total_duration`, `load_duration`, `prompt_eval_count`, `eval_count`, `tokens/s`, `done_reason` when Ollama returns them).
3. **Exports** page or result actions → download JSON (stable `schema` + `version` fields).
4. **Catalog** (`/{locale}/catalog`): merged **models.dev API** + [`data/catalog.json`](data/catalog.json) overrides + in-memory rows only for ids missing from both; filters update live.
5. **Benchmarks** (`/{locale}/benchmark`): run EN/FR micro-cases against two models; matrix on result page; CSV/MD/JSON export.

## Catalog file

Editable JSON (optional): [`data/catalog.json`](data/catalog.json). Merge order: **models.dev** (primary), then **`data/catalog.json`** replaces any matching `id`, then **in-memory** seeds fill ids still absent (handy for demos when the API is disabled).

## Tests

```bash
php bin/phpunit -c phpunit.dist.xml
```

## Architecture (short)

- **Domain** — value objects, `PromptComparisonResult`, ports (`LocalModelRuntimeInterface`, `ModelCatalogRepositoryInterface`, `ResultExporterInterface`, …).
- **Application** — use cases (`RunPromptComparisonHandler`, `SearchCatalogModelsHandler`, `RunBenchmarkSuiteHandler`, …).
- **Infrastructure** — `OllamaLocalModelRuntime` (Symfony HttpClient), `ModelsDevApiCatalogRepository` + `MergedModelCatalogRepository`, `MultiFormatResultExporter`, `ComparisonSessionStore` (session persistence for last runs).
- **UserInterface** — Twig, HTTP controllers, forms, `CatalogExplorer` live component.

Future: additional `LocalModelRuntimeInterface` implementations (OpenAI-compatible, LM Studio, vLLM) without changing domain contracts.

## License

Proprietary (see `composer.json`).
