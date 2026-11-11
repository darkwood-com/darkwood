<?php

declare(strict_types=1);

namespace Flow\Test\Driver;

use Flow\Driver\SpatieDriver;
use Flow\DriverInterface;

class SpatieDriverTest extends DriverTestCase
{
    protected function createDriver(): DriverInterface
    {
        return new SpatieDriver();
    }
}
