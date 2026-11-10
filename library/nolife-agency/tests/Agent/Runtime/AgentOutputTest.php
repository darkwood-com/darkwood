<?php

namespace App\Tests\Agent\Runtime;

use App\Agent\Runtime\AgentOutput;
use PHPUnit\Framework\TestCase;

final class AgentOutputTest extends TestCase
{
    public function testIsSerializable(): void
    {
        $output = new AgentOutput(['ok' => true], ['artifact.md'], ['meta' => 1]);
        $json = json_encode($output, \JSON_THROW_ON_ERROR);

        self::assertIsString($json);
    }
}
