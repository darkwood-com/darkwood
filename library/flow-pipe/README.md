# Flow Token Pipeline Demo

A Symfony Console demonstration of **stream discipline** for context optimization.

Token optimization is not about shortening names or words manually. It is about how data moves through a pipeline: as a **stream**, in **chunks**, with measurable and **budgeted** context.

This project shows how [`darkwood/flow`](https://github.com/darkwood-com/flow) orchestrates a chunk pipeline that simulates LLM token/context optimization — deterministically, locally, with no external API.

## Concept

The demo reads pseudo Flow engine output, tool traces, or RAG context dumps and runs them through a configurable pipeline:

```
source |> strip_ansi |> remove_noise |> normalize_whitespace |> chunk:300 |> compress |> budget:1000 |> sink
```

Each step is a registered operation — not a hardcoded branch in one giant parser. The parser splits on `|>`, resolves step names through a registry, and hands the resulting chain to `TokenPipelineFlowRunner`, which opens the pipeline with `createFlow()` and appends stages via PHP 8.5's pipe operator.

**Estimated tokens** use a simple heuristic (~1 token per 4 characters). This is not a real tokenizer and not a benchmark.

## Install

Requires **PHP 8.5+** (required by `darkwood/flow` v8.1 and the native pipe operator).

```bash
composer install
```

To add Flow explicitly:

```bash
composer require darkwood/flow
```

## Run

Default pipeline with the verbose Flow engine log:

```bash
php bin/console app:flow-token-demo --input=flow-engine-log --show-chunks
```

All built-in fixtures:

```bash
# Lexical comparison — word shortening vs stream discipline
php bin/console app:flow-token-demo --input=flow-lexicon

# Verbose engine log with ANSI noise and debug traces
php bin/console app:flow-token-demo --input=flow-engine-log --show-chunks

# Process-pipe-style stream trace
php bin/console app:flow-token-demo --input=stream-processing-trace --show-debug

# RAG context dump with useful and useless sections
php bin/console app:flow-token-demo --input=rag-context-dump \
  --pipeline="source |> strip_ansi |> remove_noise |> chunk:200 |> compress |> budget:800 |> sink" \
  --show-chunks
```

Custom pipeline:

```bash
php bin/console app:flow-token-demo \
  --pipeline="source |> strip_ansi |> remove_noise |> normalize_whitespace |> chunk:300 |> compress |> budget:1000 |> sink" \
  --input=flow-engine-log \
  --show-chunks \
  --show-debug
```

## Pipeline expression syntax

Expressions are chains of registered step names separated by `|>`:

```
source |> strip_ansi |> remove_noise |> normalize_whitespace |> chunk:300 |> compress |> budget:1000 |> sink
```

| Syntax | Meaning |
|--------|---------|
| `source` | Load a fixture into the stream |
| `strip_ansi` | Remove ANSI escape sequences |
| `remove_noise` | Drop debug/trace lines, duplicates, boilerplate |
| `normalize_whitespace` | Collapse blank runs and horizontal space |
| `chunk:300` | Split stream into ~300-character chunks |
| `compress` | Deterministic local compression per chunk |
| `budget:1000` | Keep chunks until estimated token budget is reached |
| `estimate_tokens` | Record estimated token count (passthrough) |
| `sink` | Flush final stream and compute output metrics |

Each step registers itself in `PipelineStepRegistry`. The parser delegates name resolution to the registry — the same extensibility pattern used by expression parsers (Pratt parsing, Twig token registration): **add a step class, register it, parse it**.

## Two layers: expression `|>` vs stage append

| Kind | Where | Example |
|------|-------|---------|
| **Expression `\|>`** | String DSL, `PipelineExpressionParser` | `"source \|> strip_ansi \|> sink"` |
| **PHP 8.5 `\|>`** | Append stages in `TokenPipelineFlowRunner` | `$flow \|> $this->appendStage($job)` |

The expression DSL uses `|>` between step names. At runtime, the first step opens the Flow graph; each subsequent step is appended through the pipe operator:

```php
$flow = (new FlowFactory())->createFlow($this->stepToJob($first), ['driver' => $driver]);

foreach ($remaining as $step) {
    $flow = $flow |> $this->appendStage($this->stepToJob($step));
}

$flow(new Ip($context));
$flow->await();
```

`appendStage()` returns a single-arg callable — the shape PHP 8.5's `|>` expects. PHP's native pipe does not fit stage wiring any other way without wrapper noise; it belongs inside job bodies when transforming data:

```php
fn (int $index) => $index |> $this->fetchImage(...) |> $this->normalize(...)
```

`ChunkCompressFlowRunner` (in `Infrastructure/Flow/`) demonstrates a second Flow pattern: fan-out compression with `MaxIpStrategy(4)` per chunk. The default CLI path runs the full pipeline in one FlowFactory graph; the chunk runner is available for parallel compress experiments.

## Why this matters for Flow

Flow is not only about running tasks. It is about controlling **how data moves between tasks**:

- as a **pipe** (machine-to-machine, process output captured as a stream)
- as a **stream** (line-by-line or chunk-by-chunk, not one huge string)
- as **chunks** (bounded segments crossing nodes and edges)
- as **measurable context** (estimated tokens at each stage)
- as **budgeted context** (hard limits before the sink)
- as input for **humans, machines, or agents**

This demo makes that concrete: the same verbose log that floods an agent context becomes a compact signal after stream discipline — without mangling domain vocabulary.

## What would become asynchronous later

This demo runs synchronously from the CLI, but the architecture points toward:

- **Non-blocking streams** — read process stdout as it arrives, not after `wait()`
- **`stream_select`** — multiplex multiple pipe handles in one loop
- **Fibers** — suspend while waiting for I/O (`FiberDriver` is already in use)
- **Process pipes** — Symfony Process with stdin/stdout wired as Flow sources and sinks
- **PTY/TTY modes** — interactive terminal streams with ANSI handling
- **Event loops** — Amp, ReactPHP, or Swoole drivers in `darkwood/flow`
- **Async workers** — Messenger transport for distributed chunk processing
- **Flow as orchestration layer** — routing Ips across nodes, edges, and channels above these primitives

The `stream-processing-trace` fixture models what process-pipe output looks like today. The Flow runtime is where non-blocking evolution lands tomorrow.

## Project structure

```
src/
  Application/Pipeline/
    TokenPipelineService.php          # facade: parse, run, return PipelineContext
  Command/
    FlowTokenDemoCommand.php          # thin CLI, formatting only
  Infrastructure/Flow/
    TokenPipelineFlowRunner.php       # full pipeline via createFlow + pipe append (primary)
    ChunkCompressFlowRunner.php       # chunk Ip fan-out + MaxIpStrategy(4)
    PipelineRunnerInterface.php
    Model/
      ChunkTask.php
      ChunkMergeCollector.php
  TokenFlow/                          # domain: steps, parser, fixtures, context
    PipelineContext.php
    PipelineExpressionParser.php
    PipelineStepRegistry.php
    TokenEstimator.php
    Fixture/FlowFixtureProvider.php
    Step/
      StreamSourceStep.php
      StripAnsiStep.php
      RemoveNoiseStep.php
      ...
```

## Example outputs

See [docs/example-outputs.md](docs/example-outputs.md) for captured command runs suitable for article screenshots.

## License

Proprietary — Darkwood article demo.
