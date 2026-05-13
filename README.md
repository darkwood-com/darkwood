# NoLife Models

Local explorer and comparator for AI models: merged **catalog** (models.dev-inspired metadata, no upstream dependency), **Ollama** inventory and `/api/generate` runs, lightweight **benchmarks**, and **JSON / CSV / Markdown** exports. Built for **Symfony 8**, **PHP 8.4+**, **DDD / hexagonal** layering under `src/NoLifeModels/`.

## Requirements

- PHP 8.4 or newer (project `composer.json` allows `>=8.4`)
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

Set `APP_SECRET` to a non-empty random string in `.env` or `.env.local` for non-dev environments.

### Ollama

| Variable | Default | Purpose |
|----------|---------|---------|
| `OLLAMA_BASE_URL` | `http://127.0.0.1:11434` | Base URL for Ollama HTTP API |
| `OLLAMA_TIMEOUT` | `120` | Request timeout (seconds) |
| `OLLAMA_VERIFY_PEER` | `1` | TLS verify (`0` to disable for dev-only custom certs) |

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
4. **Catalog** (`/{locale}/catalog`): merged **in-memory** rows + [`data/catalog.json`](data/catalog.json); filters update live.
5. **Benchmarks** (`/{locale}/benchmark`): run EN/FR micro-cases against two models; matrix on result page; CSV/MD/JSON export.

## Catalog file

Editable JSON (optional): [`data/catalog.json`](data/catalog.json). Entries override in-memory seeds when their `id` matches; otherwise both sources appear in the merged list.

## Tests

```bash
php bin/phpunit -c phpunit.dist.xml
```

## Architecture (short)

- **Domain** — value objects, `PromptComparisonResult`, ports (`LocalModelRuntimeInterface`, `ModelCatalogRepositoryInterface`, `ResultExporterInterface`, …).
- **Application** — use cases (`RunPromptComparisonHandler`, `SearchCatalogModelsHandler`, `RunBenchmarkSuiteHandler`, …).
- **Infrastructure** — `OllamaLocalModelRuntime` (Symfony HttpClient), catalog repositories, `MultiFormatResultExporter`, `ComparisonSessionStore` (session persistence for last runs).
- **UserInterface** — Twig, HTTP controllers, forms, `CatalogExplorer` live component.

Future: additional `LocalModelRuntimeInterface` implementations (OpenAI-compatible, LM Studio, vLLM) without changing domain contracts.

## License

Proprietary (see `composer.json`).
