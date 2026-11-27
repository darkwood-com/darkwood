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

```php
<?php

use Flow\FlowFactory;
use Flow\Ip;

class D1 {
    public function __construct(public int $n1) {}
}

class D2 {
    public function __construct(public int $n2) {}
}

class D3 {
    public function __construct(public int $n3) {}
}

class D4 {
    public function __construct(public int $n4) {}
}

function increment(D1 $data, int $by): D2
{
    return new D2($data->n1 + $by);
}

function multiply(D2 $data, int $factor): D3
{
    return new D3($data->n2 * $factor);
}

$flow = (new FlowFactory())->create(static function () {
    yield increment(?, by: 1);
    yield multiply(?, factor: 2);
    yield static function (D3 $data): D4 {
        printf("my number %d\n", $data->n3);

        return new D4($data->n3);
    };
});

$flow(new Ip(new D1(4)));
$flow->await(); // my number 10
```
