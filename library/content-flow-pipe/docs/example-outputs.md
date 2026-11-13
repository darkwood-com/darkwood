# Example Command Outputs

Captured runs from `app:flow-token-demo` for article reuse.

## flow-engine-log (default pipeline)

```bash
php bin/console app:flow-token-demo --input=flow-engine-log --show-chunks
```

```
Flow Token Demo
===============

Pipeline: source |> strip_ansi |> remove_noise |> normalize_whitespace |> chunk:300 |> compress |> budget:1000 |> sink
Input: flow-engine-log
Orchestrator: darkwood/flow (full pipeline)

Before
------

 * Characters: 17,398
 * Estimated tokens: 4,488

After
-----

 * Characters: 334
 * Estimated tokens: 88
 * Reduction: 98.0%

Executed steps
--------------

  1. source
  2. strip_ansi
  3. remove_noise
  4. normalize_whitespace
  5. chunk:300
  6. compress
  7. budget:1000
  8. sink

Final streamed output
---------------------

[chunk 1] [INFO] FlowEngine boot — node=graph-main edge=entry
[WARN] Buffer high-water mark: 8192 bytes in channel=stdout
[INFO] Executing cascade wave=1 nodes=6
[INFO] StripAnsi removed 128 escape sequences
[INFO] ApplyBudget limit=1000 estimated_before=3105 estimated_after=820
[INFO] FlowEngine complete — delta_tokens=-2285 reduction=73.6%
[chunk 2] [DEBUG] Cascade wave=3 node=ChunkText edge=chunk|transform buffer=4096 [×39 collapsed]
```

The lexicon fixture shows the opposite: word-level content resists compression because it carries signal, not noise.

## flow-lexicon

```bash
php bin/console app:flow-token-demo --input=flow-lexicon
```

```
Before
------

 * Characters: 1,381
 * Estimated tokens: 371

After
-----

 * Characters: 1,314
 * Estimated tokens: 354
 * Reduction: 4.6%
```

Minimal reduction — abbreviating `waterfall` to `wf` is not the strategy.

## stream-processing-trace (with debug)

```bash
php bin/console app:flow-token-demo --input=stream-processing-trace --show-debug
```

```
Before
------

 * Characters: 1,187
 * Estimated tokens: 313

After
-----

 * Characters: 864
 * Estimated tokens: 229
 * Reduction: 26.8%

Debug trace
-----------

  source loaded fixture=stream-processing-trace chars=1187
  strip_ansi removed 0 escape bytes
  remove_noise dropped 4 lines
  normalize_whitespace collapsed horizontal and blank runs
  chunk split into 3 chunks size=300
  compress applied deterministic local algorithm
  budget:1000 kept 3 chunks (~228 estimated tokens)
  sink flushed 864 chars (~229 estimated tokens)
```

## rag-context-dump (custom pipeline)

```bash
php bin/console app:flow-token-demo --input=rag-context-dump \
  --pipeline="source |> strip_ansi |> remove_noise |> chunk:200 |> compress |> budget:800 |> sink" \
  --show-chunks
```

```
Before
------

 * Characters: 1,766
 * Estimated tokens: 470

After
-----

 * Characters: 1,214
 * Estimated tokens: 324
 * Reduction: 31.1%

Executed steps
--------------

  1. source
  2. strip_ansi
  3. remove_noise
  4. chunk:200
  5. compress
  6. budget:800
  7. sink
```

Noise sections (disclaimers, copyright, duplicate footers) are stripped; actionable stream-orchestration content survives.
