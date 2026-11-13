# Flow x RxJS — Study Demo

Architectural study of **RxJS** ideas mapped onto **Darkwood Flow**.

This project is a **consumer of `darkwood/flow`**, not a second workflow engine.

Reusable execution lives in Flow (`StreamSelectDriver`, `MaxIpStrategy`, Jobs).  
This repo keeps fixtures, Jobs for the image pipeline, CLI, and documentation.

`StreamSelectDriver` is experimental: Generator jobs yield `waitReadable` / `waitWritable` tokens; the Driver multiplexes with `stream_select()` (no Fibers).

## Requirements

- PHP >= 8.5
- Composer

## Installation

```bash
composer install
```

Darkwood Flow is resolved through Composer; no adjacent checkout is required.

## Pipeline

```text
URLs
→ FetchImageJob     (Generator job; HTTP via yield waitReadable/waitWritable + stream_select)
→ HashBinaryJob
→ SaveFileJob
→ ResultCollector
```

Concurrency: `MaxIpStrategy($n)` on the fetch stage.

## CLI

```bash
php bin/console app:fetch-images --concurrency=4 --timeout=5
```

## Repository layout

```text
rxjs-flow/                          # demo only
  fixtures/                         # PNGs + router.php
  src/Command/FetchImagesCommand.php
  src/Demo/
    FlowPipelineRunner.php          # FlowFactory + StreamSelectDriver
    Trace.php                       # demo observability
    LocalFixtureServer.php
    Flow/                           # application Jobs
    Model/
  README.md

darkwood/flow/                      # the one execution engine
  src/Driver/StreamSelectDriver.php
  src/IpStrategy/MaxIpStrategy.php
```

## Example output

```text
[flow.started] items=6 concurrency=2
[item.started] url=…/image-a.png
[item.started] url=…/image-b.png
[item.started] url=…/image-c.png
[item.completed] url=…/image-a.png checksum=… path=…
…
[item.failed] url=…/fail.png error=HTTP 404
[flow.completed] ok=5 failed=1 elapsed_ms=…
```

## Architectural recommendation

```text
Keep from RxJS: lazy compose vs execute, concurrency-limited map, teardown discipline
Simplify in Flow: Job + Ip + MaxIpStrategy + Driver + await
Implement in Flow: StreamSelectDriver (experimental: Generators + stream_select)
Keep in demo: HTTP Job, fixtures, Trace, CLI
Reject: NativeFlow as a competing engine; HTTP inside the Driver; Execution API for now
```

**One engine (`darkwood/flow`). One demonstration project (`rxjs-flow`).**
