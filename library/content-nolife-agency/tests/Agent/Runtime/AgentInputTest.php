<?php

namespace App\Tests\Agent\Runtime;

use App\Agent\Runtime\AgentInput;
use PHPUnit\Framework\TestCase;

final class AgentInputTest extends TestCase
{
    public function testIsSerializable(): void
    {
        $input = new AgentInput(['a' => 1], ['b' => 2]);
        $json = json_encode($input, \JSON_THROW_ON_ERROR);

        self::assertIsString($json);
    }
}
