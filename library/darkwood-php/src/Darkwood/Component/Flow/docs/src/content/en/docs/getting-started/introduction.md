---
title: "Introduction"
description: "Introduction."
lead: "Introduction."
date: 2020-10-13T15:21:01+02:00
lastmod: 2020-10-13T15:21:01+02:00
draft: false
images: []
menu:
  docs:
    parent: "getting-started"
weight: 10
toc: true
---

# Darkwood Flow

Darkwood Flow composes jobs into asynchronous pipelines in PHP. Jobs pass data through the pipeline, while the driver handles execution and waiting. PHP 8.6 partial function application lets you bind arguments and use the resulting closures as jobs.

Start with [FiberDriver or PollDriver](../driver/), depending on how the pipeline needs to wait for I/O. For designing and running application workflows, see [Uniflow](https://uniflow.io/).

## Installation

Flow requires PHP `^8.6`. Install the library with [Composer](https://getcomposer.org/).

```bash
composer require darkwood/flow
```

## Usage

A working script is available in the bundled `examples` directory

- Run Flow : `php examples/flow.php`
- Start Server : `php examples/server.php`
- Start Client(s) : `php examples/client.php`

The messaging example requires a configured transport. The two examples below
run locally without any service.

- `php examples/flow-partial-application.php` chains native PHP closures.
- `php examples/flow-poll.php` demonstrates socket readiness, a timeout and a structured stream diagnostic.

For development, the monorepo uses the locked Navi Nix shell. With Castor
available, `composer env -- --with-open-swoole` includes the real OpenSwoole
extension needed by the dev-only IDE helper. For a standalone checkout, set
`DARKWOOD_NAVI_ENV_DIR` to the absolute `tools/env` path of a pinned Navi
checkout. Library installation via Composer does not need Nix or Navi.

## Upgrade from Flow 8.1

Keep `darkwood/flow: ^8.1.6` and its lockfile for PHP 8.5 deployments. Flow
`v8.1.6` is the latest published release compatible with PHP 8.5. The PHP 8.6
code is intended for a new major release.

Validate PHP and extension requirements on the target machine with
`composer check-platform-reqs`. Existing pipelines can keep their jobs and
drivers. When switching to `PollDriver`, confirm the underlying streams expose
pollable descriptors. Generator driver `tick()` intervals are measured in
seconds. If an aggregate replaces `darkwood/flow`, check that its PHP
constraint matches the included Flow version before publishing.

## Build a pipeline with PHP 8.6

```php
use Flow\FlowFactory;
use Flow\Ip;

$pipeline = (new FlowFactory())->create(static function () {
    yield substr(?, offset: 1);
    yield strtoupper(...);
    yield static function (string $output): void {
        echo $output, PHP_EOL;
    };
});
$pipeline(new Ip('flow'));
$pipeline->await(); // LOW
```

Each job receives one argument, the payload of the current `Ip`. Native PHP
partial applications accept positional and named placeholders and produce
closures with the signature PHP derives from the unbound arguments.

## Documentation

[https://flow.darkwood.com/](https://flow.darkwood.com/)

## License

_Flow_ is released under the MIT License.
