# From No-Life Testing to Behavioral Testing

Companion repository for the Darkwood article on unit testing in the age of AI agents.

**Thesis:** AI made weak tests cheap. Jobs are business behavior. Drivers are runtime furniture. Application suites should pin the former and ignore the latter.

```
DocumentRequest
      ↓
   FetchJob
      ↓
   ParseJob
      ↓
  ExcerptJob
      ↓
  ValidateJob
      ↓
 ExcerptResult
```

Same chain. Swap `FiberDriver` for `AmpDriver`. The **Good** tests stay identical.

## Requirements

- PHP ≥ 8.5
- Composer

## Setup

```bash
composer install
```

## Run

```bash
# Full suite (Good + Bad educational examples)
composer test

# Only behavioral tests (what you want in a real project)
composer test:good

# Only no-life examples (green, low value — for the article)
composer test:bad

# Manual demo
php bin/excerpt.php fiber
php bin/excerpt.php amp
```

## Layout

| Path | Role |
|------|------|
| `src/Job/` | Pure(ish) transforms — **test these** |
| `src/Port/` | I/O boundary (`DocumentSource`) — stub here |
| `src/Workflow/` | Flow composition + `FlowCollector` |
| `tests/Good/` | Behavioral oracles |
| `tests/Bad/` | Deliberately worthless green tests |
| `fixtures/article.html` | Sample document |
| `skills/` | Agent skills for pressure-testing decisions (see below) |

## Good tests vs Bad tests

### Bad — no-life / false insurance

| Test | What it “proves” | Why it lies |
|------|------------------|-------------|
| `ImplementationCoupledTest` | Mocked jobs were called in order | Never checks excerpt meaning; survives a broken real `ParseJob` |
| `RuntimeCoupledTest` | `await()` was called once | Tests the event loop wrapper, not the product |
| `CoverageTheaterTest` | Result is non-null / right class | High coverage theatre; wrong excerpt still greens |

### Good — behavioral

| Test | Oracle |
|------|--------|
| `JobBehaviorTest` | Parse / excerpt / validate I/O without Flow |
| `WorkflowBehaviorTest` | Full chain → faithful title + excerpt; thin docs fail |
| `MultiDriverBehaviorTest` | **Same assertions** under Fiber and Amp |

If you only keep one idea from this repo: **a test that still passes after you swap the runtime is a business test; a test that breaks when you swap the runtime was testing furniture.**

## Observing Flow results

`Ip::$data` is readonly. Flow does not mutate your original packet. `FlowCollector` appends a terminal job that stores the final payload — the honest pattern used in Flow’s own tests (assert in a last stage, don’t pretend the input `Ip` changed).

Optional Flow wishlist (not required here): a sync `run(Ip): mixed` helper for tests.

## What this does **not** prove

- That every app should abandon mocks (stubs at ports remain useful).
- That driver libraries need no tests (Flow’s package CI may smoke drivers; **your** app should not).
- That Amp vs Fiber performance was measured.
- That AI cannot write good tests — only that volume without oracles is false insurance ([Bergmann](https://phpunit.expert/articles/seeing-the-truth-test-oracles.html)).

## Agent skills — pressure-test the testing decisions

PHPUnit shows what is green. Skills challenge whether that green is insurance.

This repo vendors Guillaume Moigneu's [`pressure-test-decisions`](skills/pressure-test-decisions/) skill and adds [`pressure-test-testing-decisions`](skills/pressure-test-testing-decisions/) for keep / rewrite / delete / experiment calls on tests, mocks, and oracles.

```bash
# List skills defined in this repository
npx skills add . --list
```

Example prompts:

```text
Use $pressure-test-testing-decisions on tests/Bad/ImplementationCoupledTest.php —
should we keep, rewrite, or delete it?
```

```text
Use $pressure-test-testing-decisions: stub DocumentSource or mock FetchJob?
```

Full catalog, layout, and transcripts: [`skills/README.md`](skills/README.md).

## License

MIT (project). Vendored `skills/pressure-test-decisions` remains Apache-2.0 — see its `NOTICE`.
