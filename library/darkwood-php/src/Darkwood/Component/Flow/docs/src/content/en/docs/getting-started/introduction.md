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

# Flow

## Why ?

Flow concept aims to solve

- Adopt asynchronous as native implementation
- Build your code with functional programming and monoids
- Assemble your code visually

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
