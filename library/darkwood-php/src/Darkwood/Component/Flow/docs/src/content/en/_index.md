---
title : "Flow"
description: "Compose and run asynchronous pipelines in PHP with Darkwood Flow."
lead: "Compose and run asynchronous pipelines in PHP with Darkwood Flow."
date: 2020-10-06T08:47:36+00:00
lastmod: 2020-10-06T08:47:36+00:00
draft: false
images: []
---

## Usage

**Partial function application in PHP 8.6** lets you use existing PHP functions as pipeline stages while fixing some of their arguments. In `substr(?, offset: 1)`, the `?` holds the string Flow supplies, and `offset: 1` skips its first character. The next stage takes the result and converts it to uppercase.

```php
<?php

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
