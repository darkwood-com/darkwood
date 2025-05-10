<?php

namespace App;

use PhpMcp\Server\Attributes\McpTool;

class MyMcpStuff
{
    /**
     * A simple tool to add two numbers.
     *
     * @param int $a The first number.
     * @param int $b The second number.
     * @return int The sum of the two numbers.
     */
    #[McpTool(name: 'adder')]
    public function addNumbers(int $a, int $b): int
    {
        return $a + $b;
    }
}